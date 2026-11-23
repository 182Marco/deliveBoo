<?php

namespace App\Http\Controllers\Platform;

use App\AuditLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreUserRequest;
use App\Http\Requests\Platform\UpdateUserRequest;
use App\Order;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

// tab "Users" of the platform backoffice. Every action is authorized by UserPolicy (on top of the
// role:admin group) and every change leaves a row in the audit log.
class UserController extends Controller
{
    public const SORTS = ['created_at', 'name', 'email', 'role'];
    public const STATUSES = ['active', 'banned'];
    public const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $role = in_array($request->query('role'), User::ROLES, true) ? $request->query('role') : null;
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'created_at';
        $dir = in_array($request->query('dir'), ['asc', 'desc'], true) ? $request->query('dir') : ($sort === 'created_at' ? 'desc' : 'asc');
        // every word must match the name, last name, email or company ("mario rossi" finds Mario Rossi)
        $words = array_slice(preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY), 0, 5);

        $users = User::query()
            ->when($words, function (Builder $b) use ($words) {
                foreach ($words as $word) {
                    $like = '%'.addcslashes($word, '%_\\').'%';
                    $b->where(fn (Builder $w) => $w->where('name', 'like', $like)->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)->orWhere('company_name', 'like', $like));
                }
            })
            ->when($role, fn (Builder $b, string $r) => $b->where('role', $r))
            ->when($status === 'banned', fn (Builder $b) => $b->whereNotNull('banned_at'))
            ->when($status === 'active', fn (Builder $b) => $b->whereNull('banned_at'))
            ->withCount('restaurants')
            ->orderBy($sort === 'name' ? 'last_name' : $sort, $dir)->orderBy('id')
            ->paginate(self::PER_PAGE)->withQueryString();

        return view('platform.users.index', [
            'users' => $users, 'q' => $q, 'role' => $role, 'status' => $status, 'sort' => $sort, 'dir' => $dir,
            'counts' => User::query()->selectRaw('role, COUNT(*) AS n, SUM(CASE WHEN banned_at IS NULL THEN 0 ELSE 1 END) AS banned')->groupBy('role')->get()->keyBy('role'),
            'recent' => AuditLog::with('actor:id,name,last_name', 'targetUser:id,name,last_name,email')->latest('id')->limit(8)->get(),
        ]);
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        return view('platform.users.show', [
            'user' => $user->load('bannedBy:id,name,last_name')->loadCount('restaurants'),
            'restaurants' => $user->restaurants()->orderBy('name')->get(['id', 'name', 'city']),
            'orders' => DB::table('orders')->where('customer_email', $user->email)->whereIn('status', [Order::PAID, Order::FAILED])
                ->selectRaw("COUNT(*) AS n, SUM(CASE WHEN status = 'paid' AND (fulfillment_status IS NULL OR fulfillment_status <> 'cancelled') THEN price ELSE 0 END) AS gross")->first(),
            'audit' => AuditLog::with('actor:id,name,last_name')->where('target_type', 'User')->where('target_id', $user->id)->latest('id')->limit(20)->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('platform.users.form', ['user' => null]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = new User([...collect($data)->only(['name', 'last_name', 'email', 'phone', 'company_name'])->all(), 'password' => Hash::make($data['password'])]);
        // role and VAT number are set here, from the validated whitelist (never admin)
        $user->role = $data['role'];
        $user->vat_number = $data['vat_number'] ?? null;
        $user->save();

        AuditLog::record(AuditLog::USER_CREATED, $user, ['role' => $user->role, 'email' => $user->email]);

        return redirect()->route('platform.users.show', $user)->with('status', __('platform.users.flash.created'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('platform.users.form', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $user->fill(collect($data)->only(['name', 'last_name', 'email', 'phone', 'company_name'])->all());
        if (array_key_exists('vat_number', $data)) {
            $user->vat_number = $data['vat_number'];
        }
        // old → new of every changed field, for the audit log
        $changes = collect($user->getDirty())->mapWithKeys(fn ($new, $field) => [$field => [$user->getOriginal($field), $new]])->all();
        $user->save();

        if ($changes !== []) {
            AuditLog::record(AuditLog::USER_UPDATED, $user, ['changes' => $changes]);
        }

        return redirect()->route('platform.users.show', $user)->with('status', __($changes ? 'platform.users.flash.updated' : 'platform.users.flash.unchanged'));
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $this->authorize('ban', $user);
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        $user->banned_at = now();
        $user->ban_reason = $data['reason'];
        $user->banned_by = $request->user()->id;
        $user->save();
        // the sessions of the banned account end on their next request (middleware "active")

        AuditLog::record(AuditLog::USER_BANNED, $user, ['reason' => $data['reason']]);

        return redirect()->route('platform.users.show', $user)->with('status', __('platform.users.flash.banned'));
    }

    public function unban(Request $request, User $user): RedirectResponse
    {
        $this->authorize('unban', $user);

        AuditLog::record(AuditLog::USER_UNBANNED, $user, ['previous_reason' => $user->ban_reason, 'banned_at' => $user->banned_at?->toDateTimeString()]);
        $user->banned_at = null;
        $user->ban_reason = null;
        $user->banned_by = null;
        $user->save();

        return redirect()->route('platform.users.show', $user)->with('status', __('platform.users.flash.unbanned'));
    }
}

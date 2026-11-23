<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

// tab "Users" of the platform backoffice: every action is also checked by UserPolicy
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        return view('platform.users');
    }
}

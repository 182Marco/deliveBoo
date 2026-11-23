{{-- Demo accounts of the portfolio (config/demo.php), on the login page while the demo is on:
     the "Use test users" button (inside the login form, under the submit) opens a dialog with the three
     accounts; choosing one fills email and password (resources/js/jsBack/demoAccounts.js). The button
     keeps its room while hidden, so the page never moves. --}}
@if (config('demo.enabled'))
    @php($icons = ['admin' => 'chart', 'restaurateur' => 'store', 'customer' => 'bag'])
    <button type="button" class="auth-demo-trigger" data-demo-open aria-haspopup="dialog" aria-controls="demoDialog">
        <x-icon name="users" size="18" class="auth-demo-trigger-icon" />
        {{ __('partner.auth.demo.trigger') }}
    </button>

    <dialog id="demoDialog" class="auth-dialog" aria-labelledby="demoTitle">
        <div class="auth-dialog-head">
            <div class="auth-dialog-intro">
                <p class="ui-eyebrow">{{ __('partner.auth.demo.eyebrow') }}</p>
                <h2 id="demoTitle" class="auth-dialog-title">{{ __('partner.auth.demo.title') }}</h2>
            </div>
            <button type="button" class="ui-btn ui-btn--ghost ui-btn--icon auth-dialog-close" data-demo-close aria-label="{{ __('partner.auth.demo.close') }}">
                <x-icon name="close" size="20" />
            </button>
        </div>
        <p class="auth-dialog-text">{{ __('partner.auth.demo.text') }}</p>

        <ul class="auth-demo-list">
            @foreach (config('demo.accounts') as $account)
                @php($role = __('partner.auth.demo.roles.'.$account['role']))
                <li class="auth-demo-item">
                    <button type="button" class="auth-demo-account" data-demo-email="{{ $account['email'] }}" data-demo-password="{{ $account['password'] }}"
                        aria-label="{{ __('partner.auth.demo.choose') }}: {{ $role }}, {{ $account['name'] }} {{ $account['last_name'] }}">
                        <span class="auth-demo-icon"><x-icon :name="$icons[$account['role']] ?? 'user'" size="22" /></span>
                        <span class="auth-demo-who">
                            <span class="auth-demo-role">{{ $role }}</span>
                            <span class="auth-demo-name">{{ $account['name'] }} {{ $account['last_name'] }}</span>
                        </span>
                        <span class="auth-demo-about">{{ __('partner.auth.demo.about.'.$account['role']) }}</span>
                        <span class="auth-demo-foot">
                            <span class="auth-demo-creds">
                                <span class="auth-demo-cred"><x-icon name="mail" size="14" />{{ $account['email'] }}</span>
                                <span class="auth-demo-cred"><x-icon name="lock" size="14" />{{ $account['password'] }}</span>
                            </span>
                            <span class="auth-demo-go">{{ __('partner.auth.demo.choose') }} <x-icon name="arrow-right" size="16" /></span>
                        </span>
                    </button>
                </li>
            @endforeach
        </ul>
    </dialog>
    @vite('resources/js/jsBack/demoAccounts.js')
@endif

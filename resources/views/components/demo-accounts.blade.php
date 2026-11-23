{{-- Demo accounts of the portfolio (config/demo.php), on the login page while the demo is on.
     Every row shows the real credentials and logs in with one click (a normal POST /login). --}}
@if (config('demo.enabled'))
    <section class="auth-demo" aria-labelledby="demo-title">
        <p class="ui-eyebrow">{{ __('partner.auth.demo.eyebrow') }}</p>
        <h2 id="demo-title" class="auth-demo-title">{{ __('partner.auth.demo.title') }}</h2>
        <p class="auth-demo-text">{{ __('partner.auth.demo.text') }}</p>

        <ul class="auth-demo-list">
            @foreach (config('demo.accounts') as $key => $account)
                <li class="auth-demo-account">
                    <div class="auth-demo-who">
                        <span class="ui-badge">{{ __('partner.auth.demo.roles.'.$account['role']) }}</span>
                        <p class="auth-demo-name">{{ $account['name'] }} {{ $account['last_name'] }}</p>
                        <p class="auth-demo-about">{{ __('partner.auth.demo.about.'.$account['role']) }}</p>
                    </div>
                    <dl class="auth-demo-creds">
                        <dt class="auth-demo-label">{{ __('partner.auth.email') }}</dt>
                        <dd class="auth-demo-value">{{ $account['email'] }}</dd>
                        <dt class="auth-demo-label">{{ __('partner.auth.password') }}</dt>
                        <dd class="auth-demo-value">{{ $account['password'] }}</dd>
                    </dl>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $account['email'] }}">
                        <input type="hidden" name="password" value="{{ $account['password'] }}">
                        <button type="submit" class="ui-btn ui-btn--outline ui-btn--sm ui-btn--block">
                            {{ __('partner.auth.demo.use', ['name' => $account['name']]) }}
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </section>
@endif

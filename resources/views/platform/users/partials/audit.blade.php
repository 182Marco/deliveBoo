{{-- audit log entries: when, what, by whom (and on whom in the global list), changed fields or reason --}}
@if ($entries->isEmpty())
    <p class="plat-note">{{ __('platform.users.audit.none') }}</p>
@else
    <ol class="plat-audit">
        @foreach ($entries as $entry)
            <li class="plat-audit-item">
                <span class="plat-audit-when">{{ $entry->created_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
                <span class="plat-audit-what">
                    {{ __('platform.users.audit.action.'.$entry->action) }}
                    @if (($showTarget ?? false) && $entry->targetUser && $entry->action !== \App\AuditLog::ADMIN_LOGIN)
                        · <a class="ui-link" href="{{ route('platform.users.show', $entry->targetUser) }}">{{ $entry->targetUser->name }} {{ $entry->targetUser->last_name }}</a>
                    @endif
                </span>
                <span class="plat-audit-meta">
                    {{ $entry->actor ? __('platform.users.audit.by', ['admin' => $entry->actor->name.' '.$entry->actor->last_name]) : '' }}
                    @if (! empty($entry->meta['changes']))
                        · {{ __('platform.users.audit.changed', ['fields' => implode(', ', array_keys($entry->meta['changes']))]) }}
                    @endif
                    @if (! empty($entry->meta['reason']))
                        · {{ __('platform.users.reason', ['reason' => $entry->meta['reason']]) }}
                    @endif
                </span>
            </li>
        @endforeach
    </ol>
@endif

{{-- confirmation before every ".delete-post-form" submit (resources/js/jsBack/confDelete.js) --}}
<div class="ui-modal-backdrop" id="deleteConfirm" hidden>
    <section class="ui-modal" role="alertdialog" aria-modal="true" aria-labelledby="deleteConfirmTitle" aria-describedby="deleteConfirmText">
        <h2 id="deleteConfirmTitle" class="ui-subtitle">{{ __('partner.dialog.title') }}</h2>
        <p id="deleteConfirmText" class="ui-muted">{{ __('partner.dialog.text') }}</p>
        <div class="ui-modal-actions">
            <button type="button" class="ui-btn ui-btn--danger ui-btn--block" id="deleteConfermed">{{ __('partner.dialog.confirm') }}</button>
            <button type="button" class="ui-btn ui-btn--outline ui-btn--block" id="deleteAbort">{{ __('partner.dialog.keep') }}</button>
        </div>
    </section>
</div>
@vite('resources/js/jsBack/confDelete.js')

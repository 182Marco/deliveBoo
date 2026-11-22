{{-- confirmation before every ".delete-post-form" submit (resources/js/jsBack/confDelete.js) --}}
<div class="ui-modal-backdrop" id="deleteConfirm" hidden>
    <section class="ui-modal" role="alertdialog" aria-modal="true" aria-labelledby="deleteConfirmTitle" aria-describedby="deleteConfirmText">
        <h2 id="deleteConfirmTitle" class="ui-subtitle">Delete this item?</h2>
        <p id="deleteConfirmText" class="ui-muted">It will be removed for good: this cannot be undone.</p>
        <div class="ui-modal-actions">
            <button type="button" class="ui-btn ui-btn--danger ui-btn--block" id="deleteConfermed">Yes, delete it</button>
            <button type="button" class="ui-btn ui-btn--outline ui-btn--block" id="deleteAbort">Keep it</button>
        </div>
    </section>
</div>
@vite('resources/js/jsBack/confDelete.js')

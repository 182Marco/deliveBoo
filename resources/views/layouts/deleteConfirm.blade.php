{{-- confirm box to avoid user clicks by mistake--}}
<section class="del-Container no-show">
    <div class="conf-del-box p-5">
        <h2 class="mb-5">Are you sure yuo really want to delete this item? (recovering will be impossible)</h2>
        <div class="d-flex justify-content-center">
            <button class="btn btn-success mr-3" id="deleteAbort">No, do not delete it</button>
            <button class="btn btn-danger ml-3" id="deleteConfermed">Yes, I want to delete it</button>
        </div>
    </div> 
</section>
 {{-- the releted script --}}
 <script type='text/javascript' src="{{asset('js/confDelete.js')}}"></script>

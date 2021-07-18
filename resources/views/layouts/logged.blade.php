<h3 id="logged" class="logged timeout">
    You successfully logged in! 
</h3>
<style>
    /* // style message you logged in */
.logged {
    margin-top: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 60px;
    width: 370px;
    color: green;
    background-color: #38c17152;
}

.timeout {
    animation: fade-out 0.4s ease-in 3.5s forwards;
}

@keyframes fade-out {
        0% {
          opacity: 1;
        }

        100% {
          opacity: 0;
        }
      }
</style>
{{-- script to manage above --}}
{{-- <script type='text/javascript' src="{{asset('js/loggedconf.js')}}"></script> --}}
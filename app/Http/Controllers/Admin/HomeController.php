<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\OrderedMessage;

class HomeController extends Controller
{
    // Admin homepage
    public function index()
    {

        //Mail (where do you want to send the email?)
        // Mail::to(Auth::user()->email)->send(new OrderedMessage());
        // Mail::to('sarahproietti1@gmail.com')->send(new OrderedMessage());

        return view('admin.home'); //Admin homepage
    }
}

<?php

use App\Cart;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// abandoned carts (Cart::prunable, dev/PIANO_CARRELLO.md): needs the scheduler (`php artisan schedule:run` every minute)
Schedule::command('model:prune', ['--model' => [Cart::class]])->daily();

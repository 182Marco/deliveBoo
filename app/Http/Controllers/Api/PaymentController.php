<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Payments\PaymentGateway;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    // what the Payment page needs to mount the card form: the driver, its client token
    // and whether it is a test environment (then the page shows how to simulate payments)
    public function config(PaymentGateway $gateway): JsonResponse
    {
        return response()->json([
            'driver' => $gateway->driver(),
            'testMode' => $gateway->testMode(),
            'authorization' => $gateway->clientToken(),
        ]);
    }
}

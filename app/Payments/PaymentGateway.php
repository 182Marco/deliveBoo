<?php

namespace App\Payments;

// Contract of the payment provider: the order flow only talks to this interface,
// the driver is chosen in AppServiceProvider from config('services.payments.driver').
interface PaymentGateway
{
    // name sent to the front, so it knows which card form to mount ("braintree" | "fake")
    public function driver(): string;

    // true when no real money can move (offline driver, Braintree sandbox): the checkout then shows the test cards
    public function testMode(): bool;

    // authorization for the client SDK (Braintree client token); null when not needed
    public function clientToken(): ?string;

    // charges the amount (computed server side) with the single-use nonce made by the client
    public function charge(float $amount, string $nonce, string $orderReference): PaymentResult;
}

<?php

namespace App\Payments;

use Illuminate\Support\Str;

// Offline driver for the demo and the tests: it understands the same test nonces of the
// Braintree sandbox (https://developer.paypal.com/braintree/docs/reference/general/testing),
// so switching PAYMENT_DRIVER to "braintree" changes nothing in the order flow.
class FakeGateway implements PaymentGateway
{
    public const VALID_NONCE = 'fake-valid-nonce';
    public const DECLINED_NONCE = 'fake-processor-declined-visa-nonce';

    public function driver(): string
    {
        return 'fake';
    }

    public function testMode(): bool
    {
        return true;
    }

    public function clientToken(): ?string
    {
        return null;
    }

    public function charge(float $amount, string $nonce, string $orderReference): PaymentResult
    {
        return match ($nonce) {
            self::VALID_NONCE => PaymentResult::success('fake_' . Str::lower(Str::random(8))),
            self::DECLINED_NONCE => PaymentResult::failure('Do Not Honor'),
            default => PaymentResult::failure('Unknown payment method nonce.'),
        };
    }
}

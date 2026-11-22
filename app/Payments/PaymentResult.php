<?php

namespace App\Payments;

// Outcome of a charge, independent of the provider
final readonly class PaymentResult
{
    private function __construct(
        public bool $successful,
        public ?string $transactionId,
        public ?string $message,
    ) {
    }

    public static function success(string $transactionId): self
    {
        return new self(true, $transactionId, null);
    }

    public static function failure(string $message): self
    {
        return new self(false, null, $message);
    }
}

<?php

namespace App\Payments;

use Braintree\Gateway;

// Real driver: client token for the hosted fields + server side Transaction::sale.
// Sandbox credentials from https://sandbox.braintreegateway.com (BRAINTREE_* in .env).
class BraintreeGateway implements PaymentGateway
{
    public function __construct(private Gateway $gateway, private string $environment = 'sandbox')
    {
    }

    public static function fromConfig(array $config): self
    {
        return new self(new Gateway([
            'environment' => $config['environment'],
            'merchantId' => $config['merchant_id'],
            'publicKey' => $config['public_key'],
            'privateKey' => $config['private_key'],
        ]), $config['environment']);
    }

    public function driver(): string
    {
        return 'braintree';
    }

    public function testMode(): bool
    {
        return $this->environment !== 'production';
    }

    public function clientToken(): ?string
    {
        return $this->gateway->clientToken()->generate();
    }

    public function charge(float $amount, string $nonce, string $orderReference): PaymentResult
    {
        $result = $this->gateway->transaction()->sale([
            'amount' => number_format($amount, 2, '.', ''),
            'paymentMethodNonce' => $nonce,
            // shown in the Braintree dashboard, unique per order: duplicates are flagged
            'orderId' => $orderReference,
            'options' => ['submitForSettlement' => true],
        ]);

        if ($result->success) {
            return PaymentResult::success($result->transaction->id);
        }

        // processor decline (transaction present) or validation error (e.g. nonce already used)
        $transaction = $result->transaction;

        return PaymentResult::failure($transaction ? $transaction->processorResponseText : $result->message);
    }
}

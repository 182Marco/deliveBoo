<?php

namespace App\Carts;

use RuntimeException;

// A cart write refused for a business reason (other restaurant, plate not orderable, limits):
// Api\CartController answers with the HTTP status, the code and the current cart.
class CartException extends RuntimeException
{
    public const OTHER_RESTAURANT = 'other_restaurant';

    public const UNAVAILABLE = 'unavailable';

    public const NOT_ACCEPTING = 'not_accepting';

    public const TOO_MANY = 'too_many';

    public function __construct(public readonly string $errorCode, public readonly int $status, string $message)
    {
        parent::__construct($message);
    }
}

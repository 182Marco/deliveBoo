<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One line of a cart: a plate and its number of portions (unique per cart).
#[Fillable(['plate_id', 'quantity'])]
class CartItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    // withTrashed: a plate deleted by the restaurant stays visible in the cart as "no longer available"
    public function plate(): BelongsTo
    {
        return $this->belongsTo(Plate::class)->withTrashed();
    }
}

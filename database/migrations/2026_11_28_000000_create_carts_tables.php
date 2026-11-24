<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Step 3 of dev/PIANO_CARRELLO.md: the cart lives in the database, for accounts and for guests.
// A cart belongs to exactly one of user_id or guest_token (checked by App\Carts\CartService):
// guest_token is the SHA-256 of the random token kept in the guest's cookie, never the token itself.
// No prices here: they are always read from plates (the price paid stays in order_plate.unit_price).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            // unique: one cart per account (also stops two tabs from creating two carts at once)
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->char('guest_token', 64)->nullable()->unique();
            // restaurant of the lines (one restaurant per order); null for an empty cart
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            // +1 at every write: the site ignores answers older than what it already shows
            $table->unsignedInteger('version')->default(0);
            // notices to show once (merge at login, plates removed), consumed by GET /api/cart
            $table->json('notice')->nullable();
            // updated_at = last activity, used to prune abandoned carts
            $table->timestamps();
            $table->index('updated_at');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plate_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->timestamps();
            // one line per plate: concurrent writes upsert the same row
            $table->unique(['cart_id', 'plate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};

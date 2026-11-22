<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// delivery progress of a paid order (status stays the payment state):
// received -> preparing -> out_for_delivery -> delivered, or cancelled
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_status', 20)->nullable()->after('payment_error')->index();
            $table->timestamp('delivered_at')->nullable()->after('fulfillment_status');
            $table->index('customer_email');
            $table->index(['restaurant_id', 'created_at']);
        });

        // the paid orders saved so far are history: delivered
        DB::table('orders')->where('status', 'paid')->update([
            'fulfillment_status' => 'delivered',
            'delivered_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['fulfillment_status']);
            $table->dropIndex(['customer_email']);
            // MySQL uses the composite index for the restaurant_id foreign key: give it its own first
            $table->index('restaurant_id');
            $table->dropIndex(['restaurant_id', 'created_at']);
            $table->dropColumn(['fulfillment_status', 'delivered_at']);
        });
    }
};

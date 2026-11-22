<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// payment state of an order: "pending" while charging, then "paid" or "failed"
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('price')->index();
            $table->string('payment_driver', 20)->nullable()->after('status');
            $table->string('transaction_id')->nullable()->after('payment_driver');
            $table->string('payment_error')->nullable()->after('transaction_id');
        });

        // orders saved before this migration are the paid ones of the seed
        DB::table('orders')->update(['status' => 'paid']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'payment_driver', 'transaction_id', 'payment_error']);
        });
    }
};

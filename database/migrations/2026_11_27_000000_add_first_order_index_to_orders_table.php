<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "First order of every customer" (App\Analytics\OrderMetrics::customers, new vs returning):
// WHERE status = 'paid' GROUP BY customer_email with MIN(created_at) is read from this index alone
// instead of a temporary table over all the orders (dev/PIANO_STATISTICHE.md, step A.5).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'customer_email', 'created_at'], 'orders_status_customer_email_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_customer_email_created_at_index');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// one row per plate of an order with its quantity and the unit price paid,
// so the history does not change when the restaurateur edits the price later.
// Before this, two portions were two identical rows: they are merged into quantity = 2.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_plate', function (Blueprint $table) {
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 8, 2)->default(0);
        });

        $groups = DB::table('order_plate')
            ->selectRaw('order_id, plate_id, min(id) as keep_id, count(*) as portions')
            ->groupBy('order_id', 'plate_id')
            ->havingRaw('count(*) > 1')
            ->get();
        foreach ($groups as $group) {
            DB::table('order_plate')->where('id', $group->keep_id)->update(['quantity' => $group->portions]);
            DB::table('order_plate')
                ->where('order_id', $group->order_id)->where('plate_id', $group->plate_id)
                ->where('id', '!=', $group->keep_id)->delete();
        }

        // the best known price of the past orders is the current one
        DB::table('order_plate')->update([
            'unit_price' => DB::raw('(select plates.price from plates where plates.id = order_plate.plate_id)'),
        ]);

        Schema::table('order_plate', function (Blueprint $table) {
            $table->unique(['order_id', 'plate_id']);
            $table->index(['plate_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('order_plate', function (Blueprint $table) {
            // MySQL uses the composite indexes for the foreign keys: give them their own first
            $table->index('order_id');
            $table->index('plate_id');
            $table->dropUnique(['order_id', 'plate_id']);
            $table->dropIndex(['plate_id', 'order_id']);
            $table->dropColumn(['quantity', 'unit_price']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// commercial profile of a restaurant (positioning, location, delivery terms) and unique slug.
// Every new column is nullable: the restaurants created from the admin keep working without them.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            // budget | fast_food | street_food | trattoria | traditional | ethnic | premium | fine_dining | starred
            $table->string('segment', 20)->nullable()->after('description')->index();
            // 1 = cheap … 4 = very expensive
            $table->unsignedTinyInteger('price_range')->nullable()->after('segment')->index();
            $table->string('email')->nullable()->after('phone');
            $table->string('postal_code', 10)->nullable()->after('address');
            $table->string('province', 2)->nullable()->after('postal_code');
            $table->decimal('latitude', 9, 6)->nullable()->after('province');
            $table->decimal('longitude', 9, 6)->nullable()->after('latitude');
            $table->decimal('delivery_fee', 5, 2)->nullable()->after('longitude');
            $table->decimal('min_order', 6, 2)->nullable()->after('delivery_fee');
            $table->unsignedSmallInteger('prep_minutes')->nullable()->after('min_order');
            $table->boolean('accepting_orders')->default(true)->after('prep_minutes');
            $table->index('city');
        });

        // the slug becomes UNIQUE: fix the duplicates first, if any (the later rows get "-{id}")
        $duplicates = DB::table('restaurants')->select('slug')->groupBy('slug')->havingRaw('count(*) > 1')->pluck('slug');
        foreach ($duplicates as $slug) {
            DB::table('restaurants')->where('slug', $slug)->orderBy('id')->skip(1)->take(PHP_INT_MAX)->get(['id'])
                ->each(fn ($row) => DB::table('restaurants')->where('id', $row->id)->update(['slug' => "{$slug}-{$row->id}"]));
        }

        Schema::table('restaurants', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['city']);
            $table->dropIndex(['segment']);
            $table->dropIndex(['price_range']);
            $table->dropColumn([
                'description', 'segment', 'price_range', 'email', 'postal_code', 'province',
                'latitude', 'longitude', 'delivery_fee', 'min_order', 'prep_minutes', 'accepting_orders',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// menu structure of a plate: course, availability, seasonality, portions, calories, allergen
// declaration and soft deletes (a deleted plate stays in the history of its orders).
// "visible" stays for the site and the API: the model keeps it equal to availability = available.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plates', function (Blueprint $table) {
            $table->string('name', 80)->change();
            $table->string('course', 20)->nullable()->after('description')->index();
            // available | sold_out (temporarily, e.g. today) | unavailable
            $table->string('availability', 12)->default('available')->after('visible');
            // seasonal plates: months 1-12, both null = all year; from > to wraps the year (Nov-Feb)
            $table->unsignedTinyInteger('available_from_month')->nullable()->after('availability');
            $table->unsignedTinyInteger('available_to_month')->nullable()->after('available_from_month');
            $table->unsignedTinyInteger('serves')->default(1)->after('price');
            $table->unsignedSmallInteger('calories')->nullable()->after('serves');
            // the RESTAURANT declaration of the allergens: complete | partial, null = not declared.
            // Only "complete" means that an allergen without a row was checked as absent.
            $table->string('allergen_info', 10)->nullable()->after('calories');
            $table->softDeletes();
            $table->index(['restaurant_id', 'availability']);
        });

        DB::table('plates')->where('visible', false)->update(['availability' => 'unavailable']);

        // the slug becomes UNIQUE (the admin opens plates by slug): fix the duplicates first
        $duplicates = DB::table('plates')->select('slug')->groupBy('slug')->havingRaw('count(*) > 1')->pluck('slug');
        foreach ($duplicates as $slug) {
            DB::table('plates')->where('slug', $slug)->orderBy('id')->skip(1)->take(PHP_INT_MAX)->get(['id'])
                ->each(fn ($row) => DB::table('plates')->where('id', $row->id)->update(['slug' => "{$slug}-{$row->id}"]));
        }

        Schema::table('plates', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('plates', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            // MySQL uses the composite index for the restaurant_id foreign key: give it its own first
            $table->index('restaurant_id');
            $table->dropIndex(['restaurant_id', 'availability']);
            $table->dropIndex(['course']);
            $table->dropSoftDeletes();
            $table->dropColumn(['course', 'availability', 'available_from_month', 'available_to_month', 'serves', 'calories', 'allergen_info']);
        });
    }
};

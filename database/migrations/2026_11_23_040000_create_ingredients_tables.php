<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// structured ingredients: a registry shared by every plate and the plate_ingredient pivot.
// The reference data (origin, lactose, nickel) is trusted only when info_status = verified:
// an ingredient typed by a restaurateur in the admin is created as "unverified".
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            // vegetable, fruit, grain, dairy, meat, fish, shellfish, mollusc, egg, legume, nut, seed, …
            $table->string('category', 20)->nullable()->index();
            // plant | meat | fish | shellfish | mollusc | dairy | egg | honey | other: drives vegan / vegetarian
            $table->string('origin', 20)->nullable();
            // null = unknown. Lactose is not the milk allergen: aged cheeses contain milk but no lactose
            $table->boolean('contains_lactose')->nullable();
            // low | medium | high, null = unknown. INDICATIVE dataset information only:
            // never a medical guarantee or a certification of compatibility
            $table->string('nickel_level', 10)->nullable();
            // verified | unverified
            $table->string('info_status', 12)->default('unverified');
            $table->timestamps();
        });

        Schema::create('plate_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plate_id')->constrained()->cascadeOnDelete();
            // an ingredient used by a plate cannot be deleted from the registry
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            // the ingredients that define the dish (used to find alternatives)
            $table->boolean('is_main')->default(false);
            // order of display in the ingredient list
            $table->unsignedSmallInteger('position')->default(0);
            $table->unique(['plate_id', 'ingredient_id']);
            $table->index(['ingredient_id', 'plate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plate_ingredient');
        Schema::dropIfExists('ingredients');
    }
};

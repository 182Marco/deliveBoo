<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The 14 allergens of Annex II of Regulation (EU) 1169/2011 on two separate levels:
// - allergen_ingredient: reference data, which allergens an ingredient contains
//   (the allergens of a plate DERIVED from its ingredients are a query, never copied);
// - allergen_plate: what the RESTAURANT declares for the plate: "contains" or
//   "may_contain" (cross-contact, known only to the kitchen, never derived).
// plates.allergen_info (other migration) says whether that declaration is complete.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allergens', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 80);
            $table->unsignedTinyInteger('annex_number')->unique();
            $table->string('description');
        });

        Schema::create('allergen_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->unique(['ingredient_id', 'allergen_id']);
            $table->index(['allergen_id', 'ingredient_id']);
        });

        Schema::create('allergen_plate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plate_id')->constrained()->cascadeOnDelete();
            // contains | may_contain
            $table->string('status', 12);
            $table->unique(['plate_id', 'allergen_id']);
            $table->index(['allergen_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allergen_plate');
        Schema::dropIfExists('allergen_ingredient');
        Schema::dropIfExists('allergens');
    }
};

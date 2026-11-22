<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Dietary characteristics (vegan, gluten free, low nickel, …) of a plate, with four states:
// no row = not declared · status unknown = declared as not verified · yes / no = stated.
// Never a default: a missing row is NOT a "no". None of them is a certification.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dietary_tags', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 80);
            // diet | free_from | restriction
            $table->string('kind', 20);
            $table->string('description');
        });

        Schema::create('dietary_tag_plate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dietary_tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plate_id')->constrained()->cascadeOnDelete();
            // yes | no | unknown
            $table->string('status', 10);
            // restaurant (declared in the admin) | ingredients (derived from verified reference data)
            $table->string('source', 12);
            $table->unique(['plate_id', 'dietary_tag_id']);
            $table->index(['dietary_tag_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dietary_tag_plate');
        Schema::dropIfExists('dietary_tags');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// weekly opening hours: one row per service, so lunch and dinner are two rows of the same day.
// A service that ends after midnight has closes_at < opens_at.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            // 0 = Monday … 6 = Sunday
            $table->unsignedTinyInteger('weekday');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->unique(['restaurant_id', 'weekday', 'opens_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};

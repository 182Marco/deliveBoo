<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plates', function (Blueprint $table) {
            $table->id();
            $table->string('plate_name', 50); 
            $table->text('ingredients'); 
            $table->boolean('visible')->default(1); 
            $table->float('price', 5, 2); 
            $table->boolean('vegan')->default(0); // può un valore booleano essere nullable?? 
            $table->boolean('gluten_free')->default(0); 
            $table->boolean('dairy_ingredients')->default(0); 
            $table->string('plate_img')->nullable(); 
            // ci servirà uno slug??? 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plates');
    }
}

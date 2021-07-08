<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePlatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plates', function (Blueprint $table) {
            // define a new column
            // it needs to be nullable otherwise it will throw an error
            $table->unsignedBigInteger('restaurant_id')->nullable()->after('id');
            // FK
            $table->foreign('restaurant_id')
                  ->references('id')
                  ->on('restaurants')
                //   to have uncategorized if I delete a restaurant
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plates', function (Blueprint $table) {
            // remove relation before dropping the column
            $table->dropForeign('plates_restaurant_id_foreign');
            // remove the column
            $table->dropColumn('restaurant_id');
        });
    }
}

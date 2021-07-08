<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRestaurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
        // define a new column
        // it needs to be nullable otherwise it will throw an error
        $table->unsignedBigInteger('user_id')->nullable()->after('id');
        // FK
        $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::table('restaurants', function (Blueprint $table) {
        // remove relation before dropping the column
        $table->dropForeign('plates_user_id_foreign');
        // remove the column
        $table->dropColumn('user_id');
        });
    }
}

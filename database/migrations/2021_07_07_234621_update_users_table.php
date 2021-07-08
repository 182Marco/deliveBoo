<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            //Column definition
            $table->unsignedBigInteger('plate_id')->nullable()->after('password');

            //Foreign key definition
            $table->foreign('plate_id')->references('id')->on('plates')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            //relation's removal
            $table->dropForeign('users_plate_id_foreign');

            //column's removal
            $table->dropColumn('plate_id');            
        });
    }
}

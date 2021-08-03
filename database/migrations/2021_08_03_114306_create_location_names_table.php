<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_names', function (Blueprint $table) {
            $table->id();
            $table->string('ru_name');
            $table->string('ua_name');
            $table->timestamps();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->unsignedBigInteger('location_name_id')->after('client_id');
            $table->string('sub1')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('location_name_id');
            $table->string('name')->after('client_id');
            $table->string('sub1')->nullable(false)->change();
        });

        Schema::dropIfExists('location_names');
    }
}

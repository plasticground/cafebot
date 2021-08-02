<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafes', function (Blueprint $table) {
            $table->id();
            $table->string('ru_name');
            $table->string('ua_name');
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cafes');
        Schema::dropIfExists('menus');

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('menu_id');
        });
    }
}

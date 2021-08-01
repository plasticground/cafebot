<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortingPositionToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('sorting_position')->after('price')->nullable();
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('sorting_position')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sorting_position');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('sorting_position');
        });
    }
}

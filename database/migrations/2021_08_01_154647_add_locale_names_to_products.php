<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocaleNamesToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name', 'ru_name');
            $table->string('ua_name');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->renameColumn('name', 'ru_name');
            $table->string('ua_name');
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
            $table->renameColumn('ru_name', 'name');
            $table->dropColumn('ua_name');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->renameColumn('ru_name', 'name');
            $table->dropColumn('ua_name');
        });
    }
}

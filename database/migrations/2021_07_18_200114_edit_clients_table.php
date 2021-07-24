<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('chats');

        Schema::table('clients', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->dropColumn('telegram');
            $table->string('phone')->nullable()->change();
            $table->string('locale')->nullable()->change();

            $table->unsignedBigInteger('telegram_id');
            $table->string('telegram_username')->nullable();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->string('sub2')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('locale')->nullable(false)->change();
            $table->string('telegram');

            $table->dropColumn('telegram_id');
            $table->dropColumn('telegram_username');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->string('sub2')->nullable(false)->change();
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYandexToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('yandex_access_token')->default('');
            $table->string('yandex_refresh_token')->default('');
            $table->string('yandex_expires_in')->default('');
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
            $table->dropColumn('yandex_access_token');
            $table->dropColumn('yandex_refresh_token');
            $table->dropColumn('yandex_expires_in');
        });
    }
}

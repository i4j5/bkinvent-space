<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAsanaToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('asana_access_token')->default('');
            $table->string('asana_refresh_token')->default('');
            $table->string('asana_expires_in')->default('');
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
            $table->dropColumn('asana_access_token');
            $table->dropColumn('asana_refresh_token');
            $table->dropColumn('asana_expires_in');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('amo_user_id');
            $table->string('asana_user_id');
            $table->string('uis_user_id');
            $table->string('extension_phone_number');

            $table->string('google_calendar_id');
            $table->string('google_access_token');
            $table->string('google_refresh_token');
            $table->string('google_expires_in');

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
            $table->dropColumn('amo_user_id');
            $table->dropColumn('asana_user_id');
            $table->dropColumn('uis_user_id');
            $table->dropColumn('extension_phone_number');

            $table->dropColumn('google_calendar_id');
            $table->dropColumn('google_access_token');
            $table->dropColumn('google_refresh_token');
            $table->dropColumn('google_expires_in');
        });
    }
}

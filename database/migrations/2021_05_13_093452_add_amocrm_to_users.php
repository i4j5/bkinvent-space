<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmocrmToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('amocrm_access_token')->default(null);
            $table->text('amocrm_refresh_token')->default(null);
            $table->string('amocrm_expires_in')->default('');
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
            $table->dropColumn('amocrm_access_token');
            $table->dropColumn('amocrm_refresh_token');
            $table->dropColumn('amocrm_expires_in');
        });
    }
}

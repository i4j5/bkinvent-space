<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('first_visit');

            $table->text('amocrm_visitor_uid');

            $table->text('metrika_client_id');
            $table->text('google_client_id');
            
            $table->text('landing_page');
            $table->text('referrer');
            
            $table->text('utm_source');
            $table->text('utm_medium');
            $table->text('utm_campaign');
            $table->text('utm_term');
            $table->text('utm_content');
            $table->text('utm_referrer');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits');
    }
}

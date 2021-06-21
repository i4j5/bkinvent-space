<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallTrackerPhoneNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_tracker_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('tag');
            $table->integer('visit_id')->default(0);
            $table->text('default_source')->default(null);
            $table->boolean('static')->default(0);
            $table->timestamp('reservation_at');
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
        Schema::dropIfExists('call_tracker_phone_numbers');
    }
}

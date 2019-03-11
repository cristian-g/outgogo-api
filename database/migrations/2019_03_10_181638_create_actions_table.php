<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->uuid('id'); $table->primary('id');

            // Vehicle
            $table->uuid('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');

            // Outgo
            $table->uuid('outgo_id')->nullable();
            $table->foreign('outgo_id')->references('id')->on('outgoes');

            // Payment
            $table->uuid('payment_id')->nullable();
            $table->foreign('payment_id')->references('id')->on('payments');

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
        Schema::dropIfExists('actions');
    }
}

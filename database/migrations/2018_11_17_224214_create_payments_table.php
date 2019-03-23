<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id'); $table->primary('id');

            // User
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // User
            $table->uuid('receiver_id');
            $table->foreign('receiver_id')->references('id')->on('users');

            // Vehicle
            $table->uuid('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');

            $table->unsignedDecimal('quantity', 8, 2);

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
        Schema::dropIfExists('payments');
    }
}

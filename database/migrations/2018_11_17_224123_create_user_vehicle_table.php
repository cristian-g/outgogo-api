<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_vehicle', function (Blueprint $table) {
            //$table->uuid('id'); $table->primary('id');

            // User
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // Vehicle
            $table->uuid('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');

            $table->boolean('is_owner');
            $table->string('public_key');
            $table->unique(['vehicle_id', 'public_key']);

            $table->timestamps();
        });
        $first_user = \App\User::first();
        $first_vehicle = \App\Vehicle::first();
        $first_vehicle->users()->attach($first_user, [
            'public_key' => '2f4c',
            'is_owner' => false
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_vehicle');
    }
}

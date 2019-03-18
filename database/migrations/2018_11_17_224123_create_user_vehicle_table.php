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
        $user1 = \App\User::create([
            'name' => 'Àngela',
            'surnames' => 'Brunet',
            'email' => 'angela.brunet@pentech.io',
            'auth0id' => 'sample_id1',
            'password' => null,
        ]);
        $user2 = \App\User::create([
            'name' => 'Albert',
            'surnames' => 'Martínez',
            'email' => 'albert.martinez@pentech.io',
            'auth0id' => 'sample_id2',
            'password' => null,
        ]);
        $user3 = \App\User::create([
            'name' => 'Marc',
            'surnames' => 'Segura',
            'email' => 'marc.segura@pentech.io',
            'auth0id' => 'sample_id3',
            'password' => null,
        ]);
        $user4 = \App\User::create([
            'name' => 'Pol',
            'surnames' => 'Vales',
            'email' => 'pol.vales@pentech.io',
            'auth0id' => 'sample_id4',
            'password' => null,
        ]);
        $user5 = \App\User::create([
            'name' => 'Cristian',
            'surnames' => 'González',
            'email' => 'cristian.gonzalez@pentech.io',
            'auth0id' => 'sample_id5',
            'password' => null,
        ]);
        $first_vehicle = \App\Vehicle::first();
        $first_vehicle->users()->attach($user1, [
            'public_key' => 1,
            'is_owner' => true,
        ]);
        $first_vehicle->users()->attach($user2, [
            'public_key' => 2,
            'is_owner' => false,
        ]);
        $first_vehicle->users()->attach($user3, [
            'public_key' => 3,
            'is_owner' => false,
        ]);
        $first_vehicle->users()->attach($user4, [
            'public_key' => 4,
            'is_owner' => false,
        ]);
        $first_vehicle->users()->attach($user5, [
            'public_key' => 5,
            'is_owner' => false,
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

<?php

use App\Vehicle;
use App\Outgo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutgoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoes', function (Blueprint $table) {
            $table->uuid('id'); $table->primary('id');

            // User
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // Vehicle
            $table->uuid('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');

            // Outgo category
            $table->uuid('outgo_category_id');
            $table->foreign('outgo_category_id')->references('id')->on('outgo_categories');

            $table->string('description');
            $table->unsignedDecimal('quantity', 8, 2);

            $table->text('notes');
            $table->boolean('share_outgo');

            $table->unsignedInteger('points');

            $table->timestamps();
        });

        /*$firstVehicle = Vehicle::first();

        $outgo1 = new Outgo([
            'description' => 'Fuel',
            'quantity' => 52.40,
        ]);
        $outgo1->vehicle()->associate($firstVehicle);
        $outgo1->save();

        $outgo2 = new Outgo([
            'description' => 'Insurance',
            'quantity' => 130.15,
        ]);
        $outgo2->vehicle()->associate($firstVehicle);
        $outgo2->save();*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoes');
    }
}

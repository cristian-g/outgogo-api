<?php

use App\Car;
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
            $table->bigIncrements('id');
            $table->bigInteger('car_id')->unsigned();
            $table->foreign('car_id')->references('id')->on('cars');
            $table->string('description');
            $table->float('quantity');
            $table->timestamps();
        });

        $firstCar = Car::first();

        $outgo1 = new Outgo([
            'description' => 'Fuel',
            'quantity' => 52.40,
        ]);
        $outgo1->car()->associate($firstCar);
        $outgo1->save();

        $outgo2 = new Outgo([
            'description' => 'Insurance',
            'quantity' => 130.15,
        ]);
        $outgo2->car()->associate($firstCar);
        $outgo2->save();
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

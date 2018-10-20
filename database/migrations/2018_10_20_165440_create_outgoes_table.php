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
            $table->float('quantity');
            $table->timestamps();
        });

        $firstCar = Car::first();

        $outgo1 = new Outgo([
            'quantity' => 12.40,
        ]);
        $outgo1->car()->associate($firstCar);
        $outgo1->save();
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

<?php

use App\Vehicle;
use App\Outgo;
use Illuminate\Support\Facades\DB;
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

            // Receiver user
            $table->uuid('receiver_id')->nullable();
            $table->foreign('receiver_id')->references('id')->on('users');

            // Vehicle
            $table->uuid('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');

            // Original outgo
            $table->uuid('original_outgo')->nullable();
            $table->foreign('original_outgo')->references('id')->on('outgoes');

            // Outgo category
            $table->uuid('outgo_category_id');
            $table->foreign('outgo_category_id')->references('id')->on('outgo_categories');

            $table->string('description');
            $table->decimal('quantity', 8, 2);

            $table->float('gas_liters')->nullable()->default(null);
            $table->float('gas_price')->nullable()->default(null);
            $table->timestamp('finished_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->string('notes', 500)->default('');
            $table->boolean('share_outgo')->default(true);

            $table->unsignedInteger('points')->default(0);

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

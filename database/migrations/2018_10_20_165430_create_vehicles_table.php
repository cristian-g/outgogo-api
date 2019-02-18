<?php

use App\Vehicle;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id'); $table->primary('id');
            $table->string('brand');
            $table->string('model');
            $table->string('private_key')->unique();
            $table->string('public_key')->unique();
            $table->year('purchase_year');
            $table->unsignedDecimal('purchase_price', 8, 2);
            $table->timestamps();
        });
        $bytes = 70;
        Vehicle::create([
            'brand' => 'A',
            'model' => 'A',
            'private_key' => "add133ccccef4569f93cd5c963ff8c47f43153c811705c91c3aa16a4263bcadd8919482e29fefa9e8cd3dad1ae2bd57523d81323cc906d1448b34a8db99a9ab664273b506d89",//bin2hex(openssl_random_pseudo_bytes($bytes)),// will generate a random string of alphanumeric characters of length = $bytes * 2
            'public_key' => 'a39u',
            'purchase_year' => 2014,
            'purchase_price' => 12392.29,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}

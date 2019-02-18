<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutgoCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgo_categories', function (Blueprint $table) {
            $table->uuid('id'); $table->primary('id');

            $table->string('key_name');

            $table->timestamps();
        });
        \App\OutgoCategory::create([
            'key_name' => 'drive'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgo_categories');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id'); $table->primary('id');
            $table->string('name');
            $table->string('auth0id')->unique();
            $table->string('surnames')->default('');
            $table->string('email')->unique();
            $table->string('password')->nullable()->default(null);
            $table->timestamp('last_tos_acceptance')->useCurrent();
            $table->timestamps();
        });
        \App\User::create([
            'name' => 'A',
            'surnames' => 'A',
            'email' => 'A',
            'auth0id' => 'sample_id',
            'password' => 'A',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

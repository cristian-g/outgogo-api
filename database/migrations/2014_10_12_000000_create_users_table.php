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
            $table->string('surnames');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('last_tos_acceptance')->useCurrent();
            $table->timestamps();
        });
        \App\User::create([
            'name' => 'A',
            'surnames' => 'A',
            'email' => 'A',
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('register_log', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('user_agent');
            $table->string('header')->nullable();
            $table->string('query_params')->nullable();
            $table->foreignId('redirect_id');
            $table->foreign('redirect_id')->references('id')->on('register');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('register_log');
    }
};

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
            $table->string('id')->primary()->notNullable()->unique();
            $table->string('ip');
            $table->string('user_agent');
            $table->string('header');
            $table->string('query_params');
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

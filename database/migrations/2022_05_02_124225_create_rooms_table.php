<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->text('description');
            $table->unsignedInteger('guest_count');
            $table->unsignedTinyInteger('meals_id');
            $table->unsignedInteger('price');
            $table->unsignedInteger('price_weekend');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}

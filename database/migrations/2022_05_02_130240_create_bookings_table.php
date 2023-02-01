<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedTinyInteger('status_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedInteger('adult_count');
            $table->unsignedInteger('child_count')->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('count_nights');
            $table->unsignedInteger('discount');
            $table->unsignedInteger('total_price');
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}

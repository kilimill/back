<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('status_id');
            $table->unsignedTinyInteger('type_id')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('custom_lake')->nullable();
            $table->unsignedInteger('distance_city')->nullable();
            $table->text('detailed_route')->nullable();
            $table->text('conditions')->nullable();
            $table->unsignedTinyInteger('season_id')->nullable();
            $table->unsignedInteger('min_days')->nullable();
            $table->unsignedTinyInteger('check_in_hour')->nullable();
            $table->unsignedTinyInteger('check_out_hour')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
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
        Schema::dropIfExists('hotels');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->integer('role_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('first_name_owner')->nullable();
            $table->string('last_name_owner')->nullable();
            $table->string('phone_owner')->nullable();
            $table->string('email_owner')->nullable();
            $table->string('inn_owner')->nullable();
            $table->string('kpp_owner')->nullable();
            $table->integer('legal_form_id')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}

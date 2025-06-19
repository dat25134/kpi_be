<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfoTable extends Migration
{
    public function up()
    {
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('birth_date')->nullable();
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->string('education')->nullable();
            $table->string('experience')->nullable();
            $table->text('skills')->nullable(); // Stored as JSON array
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_info');
    }
} 
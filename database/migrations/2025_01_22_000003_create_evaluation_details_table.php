<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluation_id');
            $table->unsignedBigInteger('criteria_id');
            $table->decimal('self_score', 5, 2)->nullable();
            $table->text('self_comment')->nullable();
            $table->decimal('level1_score', 5, 2)->nullable();
            $table->text('level1_comment')->nullable();
            $table->decimal('level2_score', 5, 2)->nullable();
            $table->text('level2_comment')->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('cascade');
            $table->foreign('criteria_id')->references('id')->on('evaluation_criteria')->onDelete('cascade');
            $table->unique(['evaluation_id', 'criteria_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_details');
    }
}; 
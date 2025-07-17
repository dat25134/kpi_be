<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('department')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('self_score', 5, 2)->nullable();
            $table->text('self_comment')->nullable();
            $table->decimal('manager_score', 5, 2)->nullable();
            $table->text('manager_comment')->nullable();
            $table->decimal('director_score', 5, 2)->nullable();
            $table->text('director_comment')->nullable();
            $table->enum('status', [
                'pending',
                'self_evaluated',
                'manager_evaluated',
                'director_evaluated',
                'completed'
            ])->default('pending');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure one evaluation per user per month/year (kể cả soft delete)
            $table->unique(['user_id', 'month', 'year', 'deleted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
} 
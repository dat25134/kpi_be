<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->date('start_date');
            $table->date('due_date');
            $table->date('completed_at')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedTinyInteger('weight')->nullable();
            $table->unsignedBigInteger('assigner_id');
            $table->unsignedBigInteger('main_assignee_id');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->foreign('assigner_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('main_assignee_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
} 
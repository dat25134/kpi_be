<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluation_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluation_id');
            $table->unsignedBigInteger('approver_id');
            $table->integer('level')->comment('Cấp phê duyệt (1, 2)');
            $table->enum('action', ['approve', 'reject', 'comment']);
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['evaluation_id', 'level']);
            $table->index(['evaluation_id', 'approver_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_approvals');
    }
}; 
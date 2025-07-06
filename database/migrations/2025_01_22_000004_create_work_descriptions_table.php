<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('work_descriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluation_id');
            $table->unsignedBigInteger('task_id')->nullable()->comment('Liên kết với task từ hệ thống quản lý công việc');
            
            // Snapshot data của task tại thời điểm tạo evaluation
            $table->string('task_title')->nullable()->comment('Tiêu đề task tại thời điểm tạo evaluation');
            $table->text('task_description')->nullable()->comment('Mô tả task tại thời điểm tạo evaluation');
            $table->string('task_status')->nullable()->comment('Trạng thái task tại thời điểm tạo evaluation');
            $table->date('task_start_date')->nullable()->comment('Ngày bắt đầu task tại thời điểm tạo evaluation');
            $table->date('task_due_date')->nullable()->comment('Ngày hạn task tại thời điểm tạo evaluation');
            $table->integer('task_weight')->nullable()->comment('Trọng số task tại thời điểm tạo evaluation');
            
            $table->string('unit')->nullable();
            $table->text('target');
            $table->integer('complexity_weight')->comment('Trọng số phức tạp (1-4)');
            $table->integer('quality_weight')->comment('Trọng số chất lượng (1-5)');
            $table->integer('result_level')->comment('Kết quả đạt được (1-4)');
            $table->decimal('result_score', 8, 4)->nullable()->comment('Điểm có trọng số chất lượng');
            $table->decimal('final_score', 8, 4)->nullable()->comment('Điểm có tính đến độ phức tạp và chất lượng');
            $table->text('explanation')->nullable()->comment('Diễn giải kết quả');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->index(['evaluation_id', 'order']);
            $table->index(['task_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_descriptions');
    }
}; 
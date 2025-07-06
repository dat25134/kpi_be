<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->enum('role_type', ['truongphong', 'phophong', 'nhanvien']);
            $table->enum('category', [
                'chinh_tri', 
                'dao_duc', 
                'tac_phong', 
                'y_thuc', 
                'chuyen_doi_so', 
                'lanh_dao', 
                'ket_qua'
            ]);
            $table->string('name');
            $table->text('description');
            $table->decimal('max_score', 5, 2);
            $table->decimal('weight', 3, 2)->default(1.00);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['role_type', 'category']);
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_criteria');
    }
}; 
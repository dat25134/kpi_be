<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEvaluationCriteriaTable extends Migration
{
    public function up()
    {
        Schema::table('evaluation_criteria', function (Blueprint $table) {
            // Xóa các trường cũ
            $table->dropColumn(['role_type', 'category']);
            // Thêm trường mới
            $table->unsignedBigInteger('role_id')->after('id');
            $table->unsignedBigInteger('category_criteria_id')->after('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('category_criteria_id')->references('id')->on('category_criteria')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('evaluation_criteria', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['category_criteria_id']);
            $table->dropColumn(['role_id', 'category_criteria_id']);
            $table->enum('role_type', ['truongphong', 'phophong', 'nhanvien']);
            $table->enum('category', [
                'chinh_tri', 'dao_duc', 'tac_phong', 'y_thuc', 'chuyen_doi_so', 'lanh_dao', 'ket_qua'
            ]);
        });
    }
} 
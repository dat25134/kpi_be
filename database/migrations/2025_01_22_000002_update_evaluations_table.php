<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            // Xóa các cột cũ không cần thiết
            $table->dropColumn([
                'self_score', 'self_comment', 
                'manager_score', 'manager_comment',
                'director_score', 'director_comment'
            ]);
            
            // Thêm các cột mới (không có role_type)
            $table->decimal('total_score', 5, 2)->nullable()->after('department');
            $table->enum('final_grade', ['A', 'B', 'C', 'D'])->nullable()->after('total_score');
            $table->enum('status', [
                'draft',
                'submitted', 
                'level1_approved',
                'level2_approved',
                'completed'
            ])->default('draft')->change();
            $table->string('creator_role')->nullable()->after('status');
            $table->string('level1_approver_role')->nullable()->after('creator_role');
            $table->string('level2_approver_role')->nullable()->after('level1_approver_role');
        });
    }

    public function down()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn(['total_score', 'final_grade']);
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
            ])->default('pending')->change();
        });
    }
}; 
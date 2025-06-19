<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->unique()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->unsignedBigInteger('department_id')->nullable()->after('phone');
            $table->enum('position', ['employee','specialist', 'manager', 'director'])->default('employee')->after('department_id');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('position');
            $table->date('join_date')->nullable()->after('status');
            $table->softDeletes();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'employee_id',
                'phone',
                'department_id',
                'position',
                'status',
                'join_date',
                'deleted_at'
            ]);
        });
    }
} 
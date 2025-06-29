<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('position', ['employee','specialist', 'manager', 'director'])->default('employee')->after('department_id');
        });
    }
}; 
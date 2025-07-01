<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('module_permission_id')->nullable()->after('id');
            $table->foreign('module_permission_id')->references('id')->on('module_permissions')->onDelete('set null');
            $table->string('module')->nullable()->after('display_name');
            $table->string('category')->nullable()->after('module');
            $table->text('description')->nullable()->after('category');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['module_permission_id']);
            $table->dropColumn(['module_permission_id', 'display_name', 'module', 'category', 'description']);
        });
    }
}; 
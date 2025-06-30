<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // HR, Department, ...
            $table->string('display_name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_permissions');
    }
}; 
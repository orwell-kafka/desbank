<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->string('headerTemplate', 30)->default('classic')->change();
            $table->string('footerTemplate', 30)->default('simple')->change();
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->string('headerTemplate', 30)->default('centered')->change();
            $table->string('footerTemplate', 30)->default('centered')->change();
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->string('headerTemplate', 30)->default('centered')->after('headerImagePath');
            $table->string('footerTemplate', 30)->default('centered')->after('footerImagePath');
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn(['headerTemplate', 'footerTemplate']);
        });
    }
};
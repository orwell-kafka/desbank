<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn(['headerImagePath', 'footerImagePath']);
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->string('headerImagePath', 255)->nullable();
            $table->string('footerImagePath', 255)->nullable();
        });
    }
};
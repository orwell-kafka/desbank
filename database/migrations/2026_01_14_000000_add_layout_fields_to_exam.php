<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->unsignedSmallInteger('headerTop')->default(50);
            $table->string('fontFamily', 40)->default('Arial');
            $table->unsignedTinyInteger('fontSize')->default(13);
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn(['headerTop', 'fontFamily', 'fontSize']);
        });
    }
};

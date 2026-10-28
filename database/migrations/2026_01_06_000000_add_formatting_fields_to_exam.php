<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->string('paperSize', 20)->nullable()->after('created');
            $table->string('schoolName', 150)->nullable()->after('paperSize');
            $table->string('department', 150)->nullable()->after('schoolName');
            $table->string('footerText', 255)->nullable()->after('department');
            $table->string('watermarkText', 100)->nullable()->after('footerText');
            $table->unsignedTinyInteger('columns')->nullable()->after('watermarkText');
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn(['paperSize', 'schoolName', 'department', 'footerText', 'watermarkText', 'columns']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->text('columnsPerPart')->nullable()->after('columns');
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn('columnsPerPart');
        });
    }
};
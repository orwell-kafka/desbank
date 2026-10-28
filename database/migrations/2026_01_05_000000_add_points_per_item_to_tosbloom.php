<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tosbloom', function (Blueprint $table) {
            $table->decimal('pointsPerItem', 5, 2)->default(0)->after('questionType');
        });
    }

    public function down(): void
    {
        Schema::table('tosbloom', function (Blueprint $table) {
            $table->dropColumn('pointsPerItem');
        });
    }
};
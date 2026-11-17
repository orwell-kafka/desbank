<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-exam edit to the "General Instructions" printed under the student's details.
     * NULL means "use the default wording".
     */
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->text('generalInstructions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn('generalInstructions');
        });
    }
};

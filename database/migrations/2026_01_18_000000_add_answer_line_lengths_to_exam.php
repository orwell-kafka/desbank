<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-exam length (in inches) of the blank answer line printed before the item number
     * for Enumeration, True/False and Matching Type. JSON object of { "Question Type": 1.0 }.
     * NULL (or a missing type) means "use the default length". 0 hides the line for that part.
     */
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->text('answerLineLengths')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn('answerLineLengths');
        });
    }
};

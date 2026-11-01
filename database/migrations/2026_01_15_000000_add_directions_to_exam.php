<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-exam edits to the printed "Direction:" line of each part.
     * JSON object of { "Question Type": "custom text" }. Only edited parts are stored;
     * NULL (or a missing type) means "use the default wording".
     */
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->text('directions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn('directions');
        });
    }
};

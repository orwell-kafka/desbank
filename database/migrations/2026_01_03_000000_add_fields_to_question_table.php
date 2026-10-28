<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question', function (Blueprint $table) {
            $table->string('questionType', 50)->after('bloomLevel');
            $table->text('choices')->nullable()->after('content'); // comma-separated, for Multiple Choice
            $table->text('correctAnswer')->nullable()->after('choices');
        });
    }

    public function down(): void
    {
        Schema::table('question', function (Blueprint $table) {
            $table->dropColumn(['questionType', 'choices', 'correctAnswer']);
        });
    }
};
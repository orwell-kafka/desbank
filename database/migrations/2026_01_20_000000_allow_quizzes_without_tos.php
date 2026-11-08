<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A quiz is an exam with no ToS: tosId is NULL, and it carries its own course, title,
     * and a points value per question (ToS exams take points from the ToS instead).
     */
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropForeign(['tosId']);
        });

        Schema::table('exam', function (Blueprint $table) {
            $table->integer('tosId')->nullable()->change();
            $table->integer('courseId')->nullable();
            $table->string('title', 150)->nullable();
        });

        Schema::table('exam', function (Blueprint $table) {
            $table->foreign('tosId')->references('tosId')->on('tos')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('examquestion', function (Blueprint $table) {
            $table->decimal('points', 6, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('examquestion', function (Blueprint $table) {
            $table->dropColumn('points');
        });

        // Quizzes cannot exist without the nullable tosId, so remove them first.
        $quizIds = \Illuminate\Support\Facades\DB::table('exam')->whereNull('tosId')->pluck('examId');
        \Illuminate\Support\Facades\DB::table('examquestion')->whereIn('examId', $quizIds)->delete();
        \Illuminate\Support\Facades\DB::table('exambloomconfig')->whereIn('examId', $quizIds)->delete();
        \Illuminate\Support\Facades\DB::table('exam')->whereIn('examId', $quizIds)->delete();

        Schema::table('exam', function (Blueprint $table) {
            $table->dropForeign(['tosId']);
        });

        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn(['courseId', 'title']);
            $table->integer('tosId')->nullable(false)->change();
        });

        Schema::table('exam', function (Blueprint $table) {
            $table->foreign('tosId')->references('tosId')->on('tos')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};

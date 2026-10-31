<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Base tables the app uses. Every id is a signed INT so foreign keys line up
 * (the later tosbloom migration uses a signed integer tosId).
 * Each table is guarded with hasTable(), so this is a no-op on a database
 * that already has them.
 *
 * Columns added by the later 2026_01_* migrations are intentionally NOT here.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->integer('accountId', true);
                $table->string('name', 100);
                $table->string('email', 100)->unique();
                $table->string('password', 255);
                $table->enum('role', ['Admin', 'Instructor'])->default('Instructor');
                // 'status' is added by 2026_01_02_000000_add_status_to_accounts_table
            });
        }

        if (! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->integer('courseId', true);
                $table->integer('accountId');
                $table->string('courseName', 150);
                $table->string('courseCode', 20)->unique();
                $table->foreign('accountId')->references('accountId')->on('accounts')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('topic')) {
            Schema::create('topic', function (Blueprint $table) {
                $table->integer('topicId', true);
                $table->integer('courseId');
                $table->string('topicName', 150);
                $table->foreign('courseId')->references('courseId')->on('courses')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('question')) {
            Schema::create('question', function (Blueprint $table) {
                $table->integer('questionId', true);
                $table->integer('topicId');
                $table->string('bloomLevel', 50);
                $table->text('content');
                // questionType, choices, correctAnswer: 2026_01_03 ; timestamps: 2026_01_12
                $table->foreign('topicId')->references('topicId')->on('topic')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('tos')) {
            Schema::create('tos', function (Blueprint $table) {
                $table->integer('tosId', true);
                $table->integer('courseId');
                $table->string('title', 150);
                $table->string('examType', 50);
                $table->decimal('totalScore', 8, 2);
                $table->decimal('contactHours', 8, 2)->default(0);
                $table->decimal('generatedWeight', 8, 2)->default(0);
                $table->foreign('courseId')->references('courseId')->on('courses')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('tostopic')) {
            Schema::create('tostopic', function (Blueprint $table) {
                $table->integer('tosId');
                $table->integer('topicId');
                $table->decimal('contactHours', 8, 2)->default(0);
                $table->decimal('generatedWeight', 8, 2)->default(0);
                $table->decimal('assignedPoints', 8, 2)->default(0);
                $table->primary(['tosId', 'topicId']);
                $table->foreign('tosId')->references('tosId')->on('tos')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('topicId')->references('topicId')->on('topic')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('exam')) {
            Schema::create('exam', function (Blueprint $table) {
                $table->integer('examId', true);
                $table->integer('tosId');
                $table->integer('totalItems')->default(0);
                $table->decimal('totalScore', 8, 2)->default(0);
                $table->dateTime('created')->nullable();
                // formatting columns: 2026_01_06 onwards
                $table->foreign('tosId')->references('tosId')->on('tos')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('examquestion')) {
            Schema::create('examquestion', function (Blueprint $table) {
                $table->integer('examId');
                $table->integer('questionId');
                $table->primary(['examId', 'questionId']);
                $table->foreign('examId')->references('examId')->on('exam')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('questionId')->references('questionId')->on('question')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('exambloomconfig')) {
            Schema::create('exambloomconfig', function (Blueprint $table) {
                $table->integer('examId');
                $table->string('bloomLevel', 50);
                $table->integer('numberItems')->default(0);
                $table->decimal('numScore', 8, 2)->default(0);
                $table->primary(['examId', 'bloomLevel']);
                $table->foreign('examId')->references('examId')->on('exam')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        foreach (['exambloomconfig', 'examquestion', 'exam', 'tostopic', 'tos', 'question', 'topic', 'courses', 'accounts'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};

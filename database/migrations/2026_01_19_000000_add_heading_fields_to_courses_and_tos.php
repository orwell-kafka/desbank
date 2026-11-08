<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The ToS heading ("Subject Code/ Title, Credit Units, Hours, Academic Year, Semester, exam type")
     * is generated, so the pieces it needs are stored: credit units on the course, the rest on the ToS.
     * All nullable so existing rows keep working (their heading simply shows blanks until edited).
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('creditUnits', 4, 1)->nullable();
        });

        Schema::table('tos', function (Blueprint $table) {
            $table->string('academicYear', 20)->nullable();
            $table->string('semester', 30)->nullable();
            $table->decimal('hoursPerWeek', 5, 1)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tos', function (Blueprint $table) {
            $table->dropColumn(['academicYear', 'semester', 'hoursPerWeek']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('creditUnits');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Optional cover picture: the file name only, the file lives in public/uploads/course-covers. */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('coverImage', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('coverImage');
        });
    }
};

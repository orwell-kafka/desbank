<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            // One line per row. Used by the MarSU letterhead; empty means "use the built-in wording".
            $table->text('headerLines')->nullable();
            $table->text('footerLines')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropColumn(['headerLines', 'footerLines']);
        });
    }
};

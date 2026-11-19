<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('tosPreparedName', 150)->nullable();
            $table->string('tosPreparedRole', 150)->nullable();
            $table->string('tosApprovedName', 150)->nullable();
            $table->string('tosApprovedRole', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['tosPreparedName', 'tosPreparedRole', 'tosApprovedName', 'tosApprovedRole']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tosbloom', function (Blueprint $table) {
            $table->integer('tosId');
            $table->string('bloomLevel', 50);
            $table->string('questionType', 50);
            $table->decimal('generatedItemCount', 6, 2)->nullable();
            $table->decimal('generatedScore', 6, 2)->nullable();
            $table->primary(['tosId', 'bloomLevel', 'questionType']);
            $table->foreign('tosId')->references('tosId')->on('tos')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tosbloom');
    }
};
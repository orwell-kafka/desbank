<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How many items of each Bloom level / question type belong to each topic of a ToS.
     * Filled automatically when a ToS is saved; older ToS have none and get one computed on demand.
     */
    public function up(): void
    {
        if (Schema::hasTable('tostopicbloom')) {
            return;
        }

        Schema::create('tostopicbloom', function (Blueprint $table) {
            $table->integer('tosId');
            $table->integer('topicId');
            $table->string('bloomLevel', 50);
            $table->string('questionType', 50);
            $table->integer('itemCount')->default(0);
            $table->primary(['tosId', 'topicId', 'bloomLevel', 'questionType']);
            $table->foreign('tosId')->references('tosId')->on('tos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('topicId')->references('topicId')->on('topic')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tostopicbloom');
    }
};

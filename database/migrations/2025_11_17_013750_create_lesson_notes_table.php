<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $lessonTable = Schema::hasTable('content_lessons') ? 'content_lessons' : (Schema::hasTable('course_lessons') ? 'course_lessons' : null);

        // If a previous attempt partially created the table, complete missing FK and exit.
        if (Schema::hasTable('lesson_notes')) {
            if ($lessonTable) {
                try {
                    Schema::table('lesson_notes', function (Blueprint $table) use ($lessonTable) {
                        $table->foreign('lesson_id')->references('id')->on($lessonTable)->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // ignore if FK already exists / cannot be created
                }
            }
            return;
        }

        Schema::create('lesson_notes', function (Blueprint $table) use ($lessonTable) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained($lessonTable ?: 'content_lessons')->onDelete('cascade');
            $table->text('content');
            $table->integer('timestamp')->nullable()->comment('Timestamp in seconds for video lessons');
            $table->timestamps();

            $table->index(['user_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_notes');
    }
};

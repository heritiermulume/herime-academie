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
        if (Schema::hasTable('lesson_discussions')) {
            return;
        }

        $lessonTable = Schema::hasTable('content_lessons') ? 'content_lessons' : (Schema::hasTable('course_lessons') ? 'course_lessons' : 'content_lessons');

        Schema::create('lesson_discussions', function (Blueprint $table) use ($lessonTable) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained($lessonTable)->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('lesson_discussions')->onDelete('cascade');
            $table->text('content');
            $table->integer('likes_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_answered')->default(false);
            $table->timestamps();

            $table->index(['lesson_id', 'parent_id']);
            $table->index(['user_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_discussions');
    }
};

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
        if (Schema::hasTable('lesson_resources')) {
            return;
        }

        $lessonTable = Schema::hasTable('content_lessons') ? 'content_lessons' : (Schema::hasTable('course_lessons') ? 'course_lessons' : 'content_lessons');

        Schema::create('lesson_resources', function (Blueprint $table) use ($lessonTable) {
            $table->id();
            $table->foreignId('lesson_id')->constrained($lessonTable)->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('external_url')->nullable();
            $table->enum('type', ['file', 'link'])->default('file');
            $table->integer('download_count')->default(0);
            $table->boolean('is_downloadable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_resources');
    }
};

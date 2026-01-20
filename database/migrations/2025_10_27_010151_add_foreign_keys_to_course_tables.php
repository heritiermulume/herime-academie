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
        // Course sections foreign keys
        Schema::table('course_sections', function (Blueprint $table) {
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Course lessons foreign keys
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
        });

        // Courses foreign keys
        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        // Enrollments foreign keys
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Certificates foreign keys
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Reviews foreign keys
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Messages foreign keys
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Cart items foreign keys
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Lesson progress foreign keys
        Schema::table('lesson_progress', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('course_lessons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['content_id']);
            $table->dropForeign(['lesson_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['content_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->dropForeign(['content_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['content_id']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['content_id']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['content_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropForeign(['content_id']);
            $table->dropForeign(['section_id']);
        });

        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropForeign(['content_id']);
        });
    }
};

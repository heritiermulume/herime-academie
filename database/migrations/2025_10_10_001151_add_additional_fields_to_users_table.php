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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('remember_token');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->text('bio')->nullable()->after('gender');
            $table->string('avatar')->nullable()->after('bio');
            $table->string('cover_image')->nullable()->after('avatar');
            $table->string('website')->nullable()->after('cover_image');
            $table->string('linkedin')->nullable()->after('website');
            $table->string('twitter')->nullable()->after('linkedin');
            $table->string('youtube')->nullable()->after('twitter');
            $table->enum('role', ['student', 'instructor', 'admin', 'affiliate'])->default('student')->after('youtube');
            $table->boolean('is_verified')->default(false)->after('role');
            $table->boolean('is_active')->default(true)->after('is_verified');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->json('preferences')->nullable()->after('last_login_at');
            $table->string('sso_id')->nullable()->unique()->after('preferences');
            $table->string('sso_provider', 100)->default('herime')->after('sso_id');
            $table->json('sso_metadata')->nullable()->after('sso_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'date_of_birth',
                'gender',
                'bio',
                'avatar',
                'cover_image',
                'website',
                'linkedin',
                'twitter',
                'youtube',
                'role',
                'is_verified',
                'is_active',
                'last_login_at',
                'preferences',
                'sso_id',
                'sso_provider',
                'sso_metadata',
            ]);
        });
    }
};

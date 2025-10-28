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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            
            // Identifiant unique du fichier (clé)
            $table->string('file_id', 32)->unique()->index();
            
            // Informations de base
            $table->string('filename'); // Nom original
            $table->string('mime_type', 100);
            $table->enum('media_type', ['image', 'video', 'audio', 'document'])->index();
            $table->bigInteger('size'); // Taille en bytes
            
            // Stockage
            $table->string('storage_bucket', 100); // bucket/container
            $table->string('storage_path'); // Chemin complet dans le bucket
            $table->string('storage_driver', 50)->default('local'); // local, s3, gcs, etc.
            
            // Checksums pour intégrité
            $table->string('checksum_md5', 32)->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            
            // Métadonnées spécifiques au type
            $table->json('metadata')->nullable(); // Stockage flexible
            
            // Relations
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type')->nullable(); // Course, Banner, User, etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // ID de l'entité
            
            // Statut et traitement
            $table->enum('status', ['uploading', 'processing', 'ready', 'failed', 'deleted'])->default('uploading');
            $table->text('processing_error')->nullable();
            
            // Timestamps
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index composés pour performance
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'media_type']);
            $table->index('status');
        });
        
        // Table pour les variantes (résolutions, formats)
        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->constrained()->cascadeOnDelete();
            
            // Type de variante
            $table->string('variant_type', 50); // thumbnail, 360p, 720p, 1080p, etc.
            $table->string('format', 20); // mp4, webm, jpg, webp, etc.
            
            // Stockage
            $table->string('storage_path');
            $table->bigInteger('size');
            
            // Caractéristiques
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('bitrate')->nullable(); // Pour vidéo/audio
            $table->string('codec', 50)->nullable();
            
            // Métadonnées additionnelles
            $table->json('metadata')->nullable();
            
            // Statut
            $table->enum('status', ['pending', 'processing', 'ready', 'failed'])->default('pending');
            
            $table->timestamps();
            
            $table->index(['media_file_id', 'variant_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_variants');
        Schema::dropIfExists('media_files');
    }
};


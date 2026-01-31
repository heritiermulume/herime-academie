<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_event_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_event_id')->constrained('meta_events')->cascadeOnDelete();

            // Déclencheur côté client
            // - page_load: au chargement (success page inclus si match route/path)
            // - click: clic sur un élément matching css_selector
            // - form_submit: submit d'un form matching css_selector
            $table->string('trigger_type')->index();

            // Ciblage de page (optionnel)
            $table->string('match_route_name')->nullable()->index();
            $table->string('match_path_pattern')->nullable()->index(); // ex: cart/*, moneroo/success*

            // Ciblage d'élément (optionnel pour click/form_submit)
            $table->string('css_selector')->nullable();

            // Conditions (optionnelles)
            $table->json('country_codes')->nullable();
            $table->json('funnel_keys')->nullable();

            // Pixels ciblés: null => tous les pixels actifs, sinon liste d'IDs (strings)
            $table->json('pixel_ids')->nullable();

            // Payload spécifique au trigger (optionnel, override + templating côté client)
            $table->json('payload')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->boolean('once_per_page')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_event_triggers');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plan_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['subscription_plan_id', 'content_id'], 'sp_content_unique');
        });

        $rows = DB::table('subscription_plans')
            ->whereNotNull('content_id')
            ->select(['id as subscription_plan_id', 'content_id'])
            ->get()
            ->map(fn ($row) => [
                'subscription_plan_id' => $row->subscription_plan_id,
                'content_id' => $row->content_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if (!empty($rows)) {
            DB::table('subscription_plan_content')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_content');
    }
};


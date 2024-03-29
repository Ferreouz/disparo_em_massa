<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaign_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('campaign_id')->constrained();

            $table->timestamp('start_at')->default(new Expression('(NOW())'));;
            $table->json('contacts_processed')->nullable();

            $table->enum('status', ['scheduled', 'running', 'stopping', 'finished'])->default('scheduled');
            $table->enum('finished_reason', ['stopped', 'completed', 'failed'])->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_jobs');
    }
};

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
        Schema::create('dispatch_schedulers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained();

            $table->time('start');
            $table->boolean('finished');
            $table->string('finished_reason');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_schedulers');
    }
};

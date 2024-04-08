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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('number_id')->constrained();
            $table->foreignId('contact_list')->constrained();
            $table->foreignId('message_list')->constrained();

            $table->string('cron')->nullable();
            $table->boolean('running')->default(0);
            $table->timestamp('last_runned_at')->default('1945-01-01 14:52:38+00');
            
            $table->integer('delay')->nullable();
            $table->boolean('unique_contact_link')->default(false);
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};

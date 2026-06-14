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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
            $table->string('title');
            $table->text('description');
            $table->dateTime('event_date');
            $table->integer('capacity');

            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            $table->boolean('is_online')->default(false);
            $table->string('meeting_link')->nullable();

            $table->string('poster_path')->nullable();
            $table->string('proposal_path')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending');
            $table->text('reject_reason')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
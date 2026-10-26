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
        Schema::create('viewing_appointments', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->date('viewing_date');
            $table->time('viewing_time');
            $table->text('client_feedback')->nullable();
            $table->string('viewing_status')->default('Scheduled');
            $table->timestamps();

            $table->unique(
                ['agent_id', 'viewing_date', 'viewing_time'],
                'unique_agent_viewing_slot'
            );

            $table->index(['property_id', 'viewing_date']);

        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viewing_appointments');
    }
};
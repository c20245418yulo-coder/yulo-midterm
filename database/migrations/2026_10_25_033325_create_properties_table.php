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
        Schema::create('properties', function (Blueprint $table) {

            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->string('property_code')->unique();
            $table->string('property_type');
            $table->decimal('listing_price', 12, 2);
            $table->unsignedInteger('viewing_count')->default(0);
            $table->timestamps();

            $table->index(['property_type', 'listing_price']);

        });
    }

    /**
     * Reverse the migrations.
     */
       public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
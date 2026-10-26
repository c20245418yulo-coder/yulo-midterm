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
        Schema::create('property_inquiries', function (Blueprint $table) {

            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->text('inquiry_message');
            $table->timestamps();

            $table->index(['client_id', 'property_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
       public function down(): void
    {
        Schema::dropIfExists('property_inquiries');
    }
};
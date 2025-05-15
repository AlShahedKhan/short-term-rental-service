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
        Schema::create('property_listing_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_listing_id')->constrained('property_listings')->onDelete('cascade');  // Foreign key to property_listings table
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_listing_photos');
    }
};

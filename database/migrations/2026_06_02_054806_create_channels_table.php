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
        Schema::create('channels', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lobby_id')->constrained()->onDelete('cascade');
    $table->string('name'); // <-- Make sure this is exactly 'name'
    $table->string('description')->nullable();
    $table->timestamps();
    
    $table->unique(['lobby_id', 'name']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};

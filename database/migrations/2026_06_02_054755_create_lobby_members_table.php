<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lobby_members', function (Blueprint $table) {
            $table->id();
            // Connects the member row to a specific lobby room instance
            $table->foreignId('lobby_id')->constrained('lobbies')->onDelete('cascade');
            // Connects the member row directly to an operative user record
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('role_in_team')->default('Mercenary'); // Dev, Designer, Marketer, etc.
            $table->string('status')->default('pending');        // pending, approved, rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lobby_members');
    }
};
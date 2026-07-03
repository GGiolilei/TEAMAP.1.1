<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id();
            // Connects the lobby directly back to a user instance acting as the leader
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('project_goal')->nullable();
            $table->string('required_roles')->nullable();
            $table->integer('max_members')->default(5);
            $table->string('status')->default('active'); // active, full, completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lobbies');
    }
};
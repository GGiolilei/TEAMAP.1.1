<?php

namespace App\Models;

// CHANGE THIS LINE: Swap standard Model for the Pivot class
use Illuminate\Database\Eloquent\Relations\Pivot; 

class LobbyMember extends Pivot // CHANGE THIS LINE
{
    // Explicitly set the table name since it doesn't follow standard pivot naming
    protected $table = 'lobby_members';

    // If your lobby_members table has an auto-incrementing 'id' column, keep this true:
    public $incrementing = true;

    protected $fillable = ['lobby_id', 'user_id', 'role_in_team', 'status'];

    public function lobby()
    {
        return $this->belongsTo(Lobby::class, 'lobby_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lobby extends Model
{
    protected $fillable = [
        'name', 
        'description', 
        'owner_id', 
        'max_members', 
        'status', 
        'project_goal', 
        'required_roles'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'lobby_interest');
    }

    /**
     * FIXED: Points directly to User model via intermediate pivot table
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lobby_members', 'lobby_id', 'user_id')
                    ->using(LobbyMember::class)
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
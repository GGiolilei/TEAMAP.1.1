<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lobby_id',
        'name',
        'description',
    ];

    /**
     * Get the lobby that owns this text channel.
     */
    public function lobby()
    {
        return $this->belongsTo(Lobby::class);
    }
    public function messages()
{
    return $this->hasMany(Message::class);
}
}
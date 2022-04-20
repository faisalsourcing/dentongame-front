<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use SoftDeletes;
    /**
     * @return BelongsTo
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    /**
     * The Players that belong to the game.
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_games');
    }

    /**
     * Get the setting for the Game.
     */
    public function settings(): HasMany
    {
        return $this->hasMany(GameSetting::class);
    }

    /**
     * Get the setting for the Game.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(GameActivity::class);
    }
}

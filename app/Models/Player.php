<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use SoftDeletes;
    /**
     * Get the user that is Player.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }


    /**
     * The roles that belong to the user.
     */
    public function games()
    {
        return $this->belongsToMany(Game::class, 'player_games');
    }

    /**
     * Get the setting for the Game.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(GameActivity::class);
    }

    /**
     * Get the career card.
     */
    public function careerCard(): BelongsTo
    {
        return $this->belongsTo(CareerCard::class);
    }

    /**
     * Get the present expenses card.
     */
    public function presentExpenseCard(): BelongsTo
    {
        return $this->belongsTo(PresentExpenseCard::class);
    }

    /**
     * Get the past expenses card.
     */
    public function pastExpenseCard(): BelongsTo
    {
        return $this->belongsTo(PastExpenseCard::class);
    }

    /**
     * Get the life happened card.
     */
    public function lifeHappenCard(): BelongsTo
    {
        return $this->belongsTo(LifeHappenCard::class);
    }

    /**
     * @return BelongsTo
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }
}

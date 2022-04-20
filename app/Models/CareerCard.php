<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerCard extends Model
{
    use SoftDeletes;
    /**
     * Get the players for the Career Card.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}

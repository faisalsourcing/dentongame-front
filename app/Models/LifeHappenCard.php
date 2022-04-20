<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LifeHappenCard extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public $table = 'life_happens_cards';

    /**
     * Get the players for the Life Happen Card.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}

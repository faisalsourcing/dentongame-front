<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Realestate extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public $table = 'realestates';

    /**
     * Get the players for the Present Expense Card.
     */

    protected $fillable = [
        'game_id',
        'round_number'
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(RealEstateBids::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number_of_players', 'game_id', 'minimum_balance', 'maximum_balance', 'game_timer',
        'lottery_amount', 'insurance_cost', 'number_of_years', 'inflation_rate',
        'inflation_increase', 'real_estate_max', 'property_appreciation', 'tax_setup',
        'next_round'
    ];
    /**
     * Get the game that owns the setting.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class GameActivity extends Model
{

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public $table = 'game_activities';
    /**
     * Get the game that owns the setting.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the game that owns the setting.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

}

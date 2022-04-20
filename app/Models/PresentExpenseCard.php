<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresentExpenseCard extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public $table = 'present_expenses_cards';

    /**
     * Get the players for the Present Expense Card.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}

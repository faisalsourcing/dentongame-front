<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PastExpenseCard extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public $table = 'past_expenses_cards';

    /**
     * Get the players for the Past Expense Card.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Players::class);
    }
}

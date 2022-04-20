<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

use App\Models\GameActivity;
use App\Models\Game;
use App\Models\User;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('teamscorecard.{gameSlug}', function ($gameSlug) {
    if($gameSlug) {
        $game = Game::where('slug','=',$gameSlug)->where('status','=','1')->first();
        if($game) {
            $activity = GameActivity::where('game_id','=',$game->id)->where('player_id','=',User::find(Auth::id())->player->id);
            if($activity) {
                return true;
            }
        }
    }
    return false;
});
<?php

namespace App\Http\Controllers;


use App\Models\GameSetting;
use App\Models\CareerCard;
use App\Models\GameActivity;
use App\Models\LifeHappenCard;
use App\Models\Game;
use App\Models\User;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $slug = session('gameSlug');
        if($slug) {
            $player = $this->player_detail();
            if($player) {
                $game =  $this->game_detail($slug,$player->player_id);
                if($game) {
                    Session::put('game', $game);
                    $game_setting = GameSetting::where('game_id','=',$game->id)->first();
                    if($game_setting) {
                        Session::put('setting', $game_setting);
                        $find_activities = DB::table('game_activities')->where('game_id','=',$game->id)->where('player_id','=',$player->player_id)->orderBy('id', 'desc')->first();
                        if(!$find_activities) {
                            $amount = rand($game_setting->minimum_balance,$game_setting->maximum_balance);
                            $game_activity = new GameActivity();
                            $game_activity->game_id = $game->id;
                            $game_activity->player_id = $player->player_id;
                            $game_activity->round_number = 1;
                            $game_activity->amount = $amount;
                            $game_activity->round_balance = $amount;
                            $game_activity->save();
                            $new_game_activity = GameActivity::find($game_activity->id);
                            $activity = $new_game_activity;
                            updateScorecard($game,$activity);
                            activityTrigger($activity);
                        }
                        else {
                            $activity = $find_activities;
                            Session::put('current_round', $find_activities->round_number);
                            activityTrigger($activity);
                        }
                        $players = getAllPlayers($game->id,$activity->round_number);
                        $balance = balance($activity);
                        $cashBalance = cashBalance($activity);
                        $realestates = realEstate($activity);
                        $notifications = notifications($activity);
                        $partnerBalance = partnerBalance($activity);
                        return view('dashboard', ['game'=>$game,'activity'=>$activity,'players' => $players, 'balance' => $balance, 'cashBalance' => $cashBalance, 'realestates' => $realestates,'notifications' => $notifications,'partnerBalance'=>$partnerBalance]);
                    }
                    else {
                        // game setting doesn't exist
                    }
                }
                else {
                    $game = DB::table('games AS g')->where('g.slug','=',$slug)->first();
                    $alert = '';
                    if($game->deleted_at != '') {
                        $alert = 'Game has been Deleted!';
                    }
                    elseif($game->status == 2) {
                        $alert = 'Game has been Paused!';
                    }
                    elseif($game->status == 3) {
                        $alert = 'Game has been Completed!';
                    }
                    return view('alert', ['game'=>$game,'player'=>$player,'alert'=>$alert]);
                }
            }
            else {
                // player doesn't exist
            }
        }
        else {
            // no slug given
            return response('There is no game, Please join from Game URL like BASELURL/home/{gameSlug}');
        }
    }
    public function getActivity($gameId){
        $user = Auth::user();
        $activity = User::select('game_activities.id')
                    ->join('players','users.id','=','players.user_id')
                    ->join('game_activities','game_activities.player_id','=','players.id')
                    ->where('game_activities.game_id','=',$gameId)
                    ->where('users.id','=',$user->id)
                    ->orderBy('game_activities.id', 'desc')
                    ->first();
        return $activity;
    }

    /**
     * @return bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    private function player_detail() {
        $user = Auth::user();
        $player = DB::table('users AS u')
            ->select('u.*','p.id as player_id')
            ->join('role_user AS ru','ru.user_id','=','u.id')
            ->join('roles AS r','r.id','=','ru.role_id')
            ->join('players AS p','p.user_id','=','u.id')
            ->where('u.id','=',$user->id)
            ->where('u.status','=',1)
            ->where('r.name','=',strtoupper('ROLE_PLAYER'))
            ->whereNull('u.deleted_at')
            ->first();
        return isset($player) ? $player : false;
    }

    /**
     * @param $slug
     * @param $player_id
     * @return bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    private function game_detail($slug,$player_id) {
        if($slug && $player_id) {
            $game = DB::table('games AS g')
                ->join('player_games AS pg','pg.game_id','=','g.id')
                ->where('pg.player_id','=',$player_id)
                ->where('g.status','=','1')
                ->where('g.slug','=',$slug)
                ->whereNull('g.deleted_at')
                ->first();
            return isset($game) ? $game : false;
        }
        return false;
    }

    public function nextRound() {
        decideNextRound(Session::get('game'));
    }

    public function roll(Request $request) {
        if($request->get('activity_id') && $request->get('area')) {
            $activity = GameActivity::find($request->get('activity_id'));
            if($activity) {
                $dice = new \stdClass();
                $dice->name = 'Roll a Dice!';
                $dice->activity_id = $activity->id;
                $dice->area = $request->get('area');
                $html = view('partials.roll', ['data'=>$dice])->render();
                return response()->json(["status" => 200, "success" => true, "html" => $html]);
            }
            else {
                return response()->json(["status" => 404, "success" => false, "data" => 'No data found to roll!']);
            }
        }
    }

    public function rollDecision(Request $request) {
        if($request->get('activity_id') && $request->get('area') && $request->get('num')) {
            $activity = GameActivity::find($request->get('activity_id'));
            if($activity) {
                $double = false;
                $html = 'Sorry! you did not get any award.';
                if($request->get('num') >= 1 && $request->get('num') <= 4) {
                    $double = true;
                }
                if($double) {
                    if($request->get('area') == 'realestate') {
                        if($activity->realestate_amount) {
                            $add_amount = $activity->realestate_amount*2;
                            $activity->round_balance = $activity->round_balance + $add_amount;
                            $activity->save();
                            updateDashboard(Session::get('game'),$activity);
                            $html = 'Hurray! you got $'.$add_amount.' added in your balance';
                        }
                    }
                }
                return response()->json(["status" => 200, "success" => true, "html" => $html]);
            }
            return response()->json(["status" => 404, "success" => false, "data" => 'Nothing!']);
        }
    }

    public function test() {
        dd(property_appreciation(1));
    }
}

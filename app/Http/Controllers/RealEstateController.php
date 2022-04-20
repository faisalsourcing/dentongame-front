<?php

namespace App\Http\Controllers;

use App\Events\RealEstateBid;
use App\Events\RealEstateWinner;
use App\Models\Game;
use App\Models\Player;
use App\Models\Realestate;
use App\Models\GameActivity;
use App\Models\RealEstateBids;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RealEstateController extends Controller
{
    var $type = 'realestate';
    /**
     * @param $activity_id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function get($activity_id) {
        if($activity_id) {
            $found_activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($found_activity) {
                $owned_properties = GameActivity::select('game_activities.id','game_activities.realestate_id','game_activities.round_number','game_activities.realestate_amount','realestates.round_number as realestate_round')
                    ->join('realestates','realestates.id','=','game_activities.realestate_id')
                    ->where('game_activities.game_id','=',$found_activity->game_id)
                    ->where('game_activities.player_id','=',$found_activity->player_id)
                    ->where('game_activities.realestate_sold','!=','1')
                    ->whereNotNull('game_activities.realestate_id')->count();
                if($owned_properties < Session::get('setting')->real_estate_max) {
                    $real_estate = Realestate::firstOrNew(array('game_id'=>$found_activity->game_id,'round_number'=>$found_activity->round_number));
                    $real_estate->save();
                    $card = new \stdClass();
                    $card->name = 'Real Estate Auction';
                    $card->amount = 500;
                    //$card->amount = inflation($card->amount,$found_activity);
                    $card->image = asset('assets/images/popups/other/property.png');
                    $card->id = $real_estate->id;
                    if($card) {
                        $html = view('partials.'.$this->type.'_bid', ['activity'=>$found_activity,'data'=>$card,'type'=>$this->type])->render();
                    }
                    return response()->json(["status" => 200, "success" => true, "type" => $this->type, "html" => $html]);
                }
                return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Already owned maximum properties']);
            }
            return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Activity Id is not valid']);
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Please provide the activity id']);
    }

    public function save($activity_id, Request $request) {
        if($activity_id) {
            $activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($activity) {
                if($request->get('amount') < $activity->round_balance) {
                    $bid = new RealEstateBids();
                    $bid->realestate_id = $request->get('id');
                    $bid->player_id = $activity->player_id;
                    $bid->amount = $request->get('amount');
                    $bid->save();
                    $bids = DB::table('realestate_bid')
                        ->select('users.name','realestate_bid.amount')
                        ->join('players','players.id','=','realestate_bid.player_id')
                        ->join('users','users.id','=','players.user_id')
                        ->where('realestate_bid.realestate_id','=',$request->get('id'))
                        ->orderBy('realestate_bid.id', 'DESC')->get();
                    $bid_html = '';
                    if($bids) {
                        $bid_html = view('partials.bids', compact('bids'))->render();
                    }
                    event(new RealEstateBid(array('id'=>$request->get('id'),'html'=>$bid_html,'amount'=>$request->get('amount')+500)));
                    return response()->json(["status" => 200, "success" => true, "type" => $this->type, "html" => 'saved!']);
                }
                else {
                    return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'You do not have sufficient balance']);
                }
            }
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Something went wrong!']);
    }

    public function assign($realestate_id,$activity_id) {
        if($realestate_id && $activity_id) {
            $activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($activity) {
                $highest_bid = DB::table('realestate_bid')
                    ->where('realestate_id','=',$realestate_id)
                    ->orderBy('amount','DESC')
                    ->first();
                if($highest_bid) {
                    $find_activity = GameActivity::where('game_id','=',$activity->game_id)->where('player_id','=',$highest_bid->player_id)->first();
                    if($find_activity) {
                        $find_activity->realestate_id = $highest_bid->realestate_id;
                        $find_activity->realestate_amount = $highest_bid->amount;
                        $find_activity->round_balance = calculate_balance($find_activity->round_balance,$find_activity->realestate_amount,true);
                        $find_activity->save();
                        updateDashboard(Session::get('game'),$find_activity);
                        if($find_activity->id == $activity->id) {
                            event(new RealEstateWinner(array('id'=>$find_activity->id,'game_id'=>$find_activity->game_id,'player_id'=>$highest_bid->player_id)));
                        }
                        else {
                            event(new RealEstateWinner(array('id'=>$activity->id,'game_id'=>$activity->game_id,'player_id'=>$highest_bid->player_id)));
                        }
                        return response()->json(["status" => 200, "success" => true, "type" => 'realestate', "html" => 'saved!']);
                    }
                }
                else {
                    return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'No Bid Found!']);
                }
            }
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Something went wrong!']);
    }

    public function bidWinner(Request $request) {
        if($request->get('activity_id') && $request->get('game_id') && $request->get('player_id')) {
            $activity = GameActivity::find($request->get('activity_id'));
            if($activity) {
                $card = new \stdClass();
                $card->activity_id = $activity->id;
                $card->name = 'Bidder';
                $card->image = asset('assets/images/popups/other/property.png');
                $user = Player::find($request->get('player_id'))->user;
                $card->winner_name = $user->name;
                $card->roll_dice = false;
                if($activity->player_id == $request->get('player_id')) {
                    $card->roll_dice = true;
                }
                $html = view('partials.realestate_winner', ['data'=>$card])->render();
                return response()->json(["status" => 200, "success" => true, "roll_dice" => $card->roll_dice, "html" => $html]);
            }
        }
        else {
            return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'No Bid Found!']);
        }
    }

    public function sell(Request $request) {
        if($request->get('activity_id') && $request->get('realestate_id')) {
            $activity = GameActivity::find($request->get('activity_id'));
            if($activity) {
                if($activity->realestate_id == $request->get('realestate_id')) {
                    $latest_activity = GameActivity::where('game_id','=',$activity->game_id)->where('player_id','=',$activity->player_id)->orderBy('id','DESC')->first();
                    if($latest_activity) {
                        $latest_activity->round_balance = $latest_activity->round_balance+$activity->realestate_amount;
                        $activity->realestate_sold = 1;
                        $activity->save();
                        $latest_activity->save();
                        updateDashboard(Session::get('game'),$latest_activity);
                        return response()->json(["status" => 200, "success" => true, "html" => 'Sold!']);
                    }
                }
                else {
                    return response()->json(["status" => 200, "success" => true, "html" => 'Nothing happened!']);
                }
            }
            return response()->json(["status" => 200, "success" => true, "html" => 'No activity found!']);
        }
    }
}


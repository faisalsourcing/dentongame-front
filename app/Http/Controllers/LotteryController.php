<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\GameActivity;
use Illuminate\Support\Facades\Session;

class LotteryController extends Controller
{
    var $type = 'lottery';
    /**
     * @param $activity_id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function get($activity_id) {
        if($activity_id) {
            $found_activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($found_activity) {
                $card = new \stdClass();
                $card->name = 'Want to purchase a Lottery?';
                $card->amount = Session::get('setting')->lottery_amount;
                //$card->amount = inflation($card->amount,$found_activity);
                $card->image = asset('assets/images/popups/other/lottery.png');
                //$card->id = Session::get('setting')->id;
                $card->id = $found_activity->id;
                if($card) {
                    $html = view('partials.'.$this->type, ['activity'=>$found_activity,'data'=>$card,'type'=>$this->type])->render();
                }
                return response()->json(["status" => 200, "success" => true, "type" => $this->type, "html" => $html]);
            }
            return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Activity Id is not valid']);
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Please provide the activity id']);
    }

    public function save($activity_id, Request $request) {
        if($activity_id) {
            $activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($activity) {
                if($activity->round_balance > $request->get('amount')) {
                    $activity->lottery = 1;
                    $activity->lottery_cost = $request->get('amount');
                    $activity->round_balance = calculate_balance($activity->round_balance, $activity->lottery_cost, true);
                    $activity->save();
                    updateDashboard(Session::get('game'), $activity);
                    return response()->json(["status" => 200, "success" => true, "type" => $this->type, "html" => 'saved!']);
                }
                else {
                    return response()->json(["status" => 404, "success" => false, "type" => $this->type, "html" => 'You do not have sufficient balance to purchase lottery.']);
                }
            }
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Something went wrong!']);
    }

    public function saveOpt(Request $request) {
        if($request->get('activity_id')) {
            $activity = GameActivity::where('id','=',$request->get('activity_id'))->where('game_id','=',Session::get('setting')->game_id)->first();
            if($activity) {
                $activity->lottery = 0;
                $activity->save();
                return response()->json(["status" => 200, "success" => true, "type" => $this->type, "html" => 'saved!']);
            }
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Something went wrong!']);
    }
}

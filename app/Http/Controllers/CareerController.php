<?php

namespace App\Http\Controllers;

use App\Events\TeamScoreCard;
use App\Events\Balance;
use App\Events\CashBalance;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CareerCard;
use App\Models\GameActivity;
use Illuminate\Support\Facades\Session;

class CareerController extends Controller
{
    var $type = 'career';
    /**
     * @param $activity_id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function get($activity_id) {
        if($activity_id) {
            $found_activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($found_activity) {
                if($found_activity->career_cards_id) {
                    $card = $this->getCard($found_activity->career_cards_id);
                }
                else {
                    $card = $this->getCard();
                    $found_activity->career_cards_id = $card->id;
                    $found_activity->save();
                }

                $html = '';
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
            if($activity && $request->get('id')) {
                $career = CareerCard::where('id','=',$request->get('id'))->first();
                $activity->career_cards_id = $career->id;
                $activity->career_cards_amount = $career->amount;
                $activity->round_balance = calculate_balance($activity->round_balance,$career->amount,false);
                $activity->save();
                updateDashboard(Session::get('game'),$activity);
                return response()->json(["status" => 200, "success" => true, "type" => $this->type, "html" => 'saved!']);
            }
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Something went wrong!']);
    }

    /**
     * @return JsonResponse
     */
    public function getCard($id = '') {
        if($id) {
            $card = CareerCard::select('id','amount','name','slug')->where('id',$id)->first();
        }
        else {
            $card = CareerCard::inRandomOrder()->select('id','amount','name','slug')->first();
        }
        if($card) {
            if(file_exists(public_path('assets/images/popups/careers/'.$card->slug.'.png'))) {
                $card->image = asset('assets/images/popups/careers/'.$card->slug.'.png');
            }
            else {
                $card->image = '';
            }
        }
        return $card;
    }
}

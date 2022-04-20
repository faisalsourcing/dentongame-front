<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PastExpenseCard;
use App\Models\GameActivity;
use Illuminate\Support\Facades\Session;

class PastChoiceController extends Controller
{
    var $type = 'past_choice';
    /**
     * @param $activity_id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function get($activity_id) {
        if($activity_id) {
            $found_activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($found_activity) {
                if($found_activity->past_expenses_cards_id) {
                    $card = $this->getCard($found_activity->past_expenses_cards_id);
                }
                else {
                    $card = $this->getCard();
                    $found_activity->past_expenses_cards_id = $card->id;
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
                $data = PastExpenseCard::where('id','=',$request->get('id'))->first();
                $activity->past_expenses_cards_id = $data->id;
                $activity->past_expenses_cards_amount = $data->amount;
                $activity->round_balance = calculate_balance($activity->round_balance,$activity->past_expenses_cards_amount,true);
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
            $card = PastExpenseCard::select('id','amount','name')->where('id',$id)->first();
        }
        else {
            $card = PastExpenseCard::inRandomOrder()->select('id','amount','name')->first();
        }
        if($card) {
            $card->image = asset('assets/images/popups/other/past-choice.png');
        }
        return $card;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\LifeHappenCard;
use App\Models\GameActivity;
use App\Models\Notifications;
use App\Events\SendNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LifeHappenController extends Controller
{
    var $type = 'lifehappens';
    /**
     * @param $activity_id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function get($activity_id,$slug = null) {
        if($activity_id) {
            $found_activity = GameActivity::where('id','=',$activity_id)->where('game_id','=',Session::get('setting')->game_id)->first();
            if($found_activity) {
                $card = $this->getCard($found_activity,$slug);
                $html = '';
                if($card) {
                    if($found_activity->insurance == 1) {
                        $card->valid_amount = $card->amount;
                    }
                    else {
                        $card->valid_amount = $card->no_insurance;
                    }
                    if($card->valid_amount == 0 && $card->no_insurance) {
                        $card->valid_amount = $card->no_insurance;
                    }
                    if($card->valid_amount == 0 && $card->amount) {
                        $card->valid_amount = $card->amount;
                    }
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
                $data = LifeHappenCard::where('id','=',$request->get('id'))->first();
                $activity->life_happens_id = $data->id;
                $activity->life_happens_amount = $data->amount;
                $custom_cards = array('bankruptcy','married','divorced','career-change','business-grant','career-change-1','salary-cut');
                if(!in_array($data->slug,$custom_cards)) {
                    if($data->impact == 'n') {
                        $activity->round_balance = calculate_balance($activity->round_balance,$activity->life_happens_amount,true);
                    }
                    else {
                        $activity->round_balance = calculate_balance($activity->round_balance,$activity->life_happens_amount,false);
                    }
                }
                else {
                    if($request->get('players')) {
                        $activity->life_happens_request_to = json_encode($request->get('players'));
                    }
                    if($data->slug == 'bankruptcy') {
                        $activity->round_balance = calculate_balance($activity->round_balance,$activity->round_balance,true);
                    }
                    elseif($data->slug == 'married') {
                        $user = Player::find($activity->player_id)->user;
                        $username = $user->name;
                        foreach ($request->get('players') as $player) {
                            sendNotification($activity,$player,$username.' has proposed you.',$data->slug);
                        }
                    }
                    elseif($data->slug == 'business-grant') {
                        if($request->get('players')) {
                            $user = Player::find($activity->player_id)->user;
                            $username = $user->name;
                            $granted_player = $request->get('players');
                            $granted_activity = GameActivity::where('player_id','=',$granted_player)->where('round_number','=',$activity->round_number)->where('game_id','=',$activity->game_id)->first();
                            if($granted_activity) {
                                $granted_activity->round_balance = $granted_activity->round_balance+10000;
                                $granted_activity->save();
                                sendNotification($activity,$request->get('players'),$username.' has granted you $10000',$data->slug);
                            }
                        }
                    }
                }
                $activity->save();
                updateDashboard(Session::get('game'),$activity);
                $game = Game::where('id','=',$activity->game_id)->first();
                decideNextRound($game);
                return response()->json(["status" => 200, "success" => true, "type" => $this->type, "html" => 'saved!']);
            }
        }
        return response()->json(["status" => 404, "success" => false, "type" => $this->type, "data" => 'Something went wrong!']);
    }

    /**
     * @param $game_activity
     * @return mixed
     */
    public function getCard($game_activity,$slug) {
        if($slug) {
            $card = LifeHappenCard::inRandomOrder()->select('id','heading','occurance','slug','impact','amount','no_insurance')->where('slug','=',$slug)->first();
        }
        else {
            if($game_activity->life_happens_id) {
                $card = LifeHappenCard::where('id',$game_activity->life_happens_id)->first();
            }
            else {
                if(!$game_activity->is_married) {
                    $card = LifeHappenCard::inRandomOrder()->select('id','heading','occurance','slug','impact','amount','no_insurance')->where('slug','<>','divorced')->first();
                }
                else {
                    $card = LifeHappenCard::inRandomOrder()->select('id','heading','occurance','slug','impact','amount','no_insurance')->where('slug','<>','married')->first();
                }
                $game_activity->life_happens_id = $card->id;
                $game_activity->save();
            }
        }
        if($card) {
            $card->class = '';
            $card->notation = '';
            $card->image = '';
            $card->players = array();
            $card->button_text = '';
            $card->get_input = true;
            $card->realestates = array();
            if($card->impact == 'n') {
                if($card->amount && $card->no_insurance) {
                    if($card->amount == $card->no_insurance) {
                        $card->notation = 'Insurance cannot save you. You owe $'.$card->amount;
                    }
                    else {
                        $card->notation = 'No Insurance = pay $'.$card->no_insurance.', Insured = pay $'.$card->amount;
                    }
                }
                if(!$card->amount && $card->no_insurance) {
                    $card->notation = 'No Insurance = pay $'.$card->no_insurance.', Insured = pay $'.$card->amount;
                }
            }
            if(file_exists(public_path('assets/images/popups/life-happens/'.$card->slug.'.png'))) {
                $card->image = asset('assets/images/popups/life-happens/'.$card->slug.'.png');
            }
            else {
                if($card->impact == 'n') {
                    $card->image = asset('assets/images/popups/life-happens/n.png');
                }
                else {
                    $card->image = asset('assets/images/popups/life-happens/p.png');
                }
            }
            $special_popups = array(
                'bankruptcy' => 'blue',
                'career-change' => 'blue',
                'career-change-1' => 'blue',
                'got-bonus' => 'blue',
                'married' => 'blue',
                'new-baby-on-board' => 'blue',
                'new-sports-car' => 'orange',
                'twin' => 'orange',
                'nothing' => 'blue',
                'won-trial' => 'blue',
                'died' => 'black'
            );
            if(array_key_exists($card->slug,$special_popups)) {
                $card->class = $special_popups[$card->slug];
            }
            else {
                if($card->impact == 'n') {
                    $card->class = 'pink';
                }
                else {
                    $card->class = 'green';
                }
            }
            $hide_text = array(
                'bankruptcy',
                'career-change',
                'career-change-1',
                'married',
                'divorced',
                'nothing',
                'died',
                'business-grant'
            );
            if(in_array($card->slug,$hide_text)) {
                $card->get_input = false;
            }
            $players_info = array();
            $button_text = 'Submit';
            $button_class = 'btn-secondary';
            if($card->slug == 'business-grant' || $card->slug == 'married') {
                if(isset($game_activity->game_id)) {
                    $all_players = GameActivity::select('player_id')->where('game_id','=',$game_activity->game_id)->distinct()->get();
                    if(count($all_players)) {
                        foreach($all_players as $player) {
                            if($player->player_id != $game_activity->player_id) {
                                array_push($players_info,array('id'=>$player->player_id,'info'=>Player::find($player->player_id)->user));
                            }
                        }
                    }
                }
                if($card->slug == 'business-grant') {
                    $button_text = 'Send';
                    $button_class = 'btn-primary';
                }
                else {
                    $button_text = 'Send Proposal';
                }
            }
            if($card->slug == 'bankruptcy') {
                $owned_realestates = GameActivity::select('game_activities.id','game_activities.realestate_id','game_activities.round_number','game_activities.realestate_amount','realestates.round_number as realestate_round')
                    ->join('realestates','realestates.id','=','game_activities.realestate_id')
                    ->where('game_activities.game_id','=',$game_activity->game_id)
                    ->where('game_activities.player_id','=',$game_activity->player_id)
                    ->where('game_activities.realestate_sold','!=','1')
                    ->whereNotNull('game_activities.realestate_id')->get();
                $card->realestates = $owned_realestates;
                if(count($owned_realestates)) {
                    $card->notation = 'Sell your properties to stay in the game.';
                }
                else {
                    $card->notation = '';
                }

            }
            $card->players = $players_info;
            $card->button_text = $button_text;
            $card->button_class = $button_class;
            //$card->amount = $card->amount,$game_activity;
            //$card->no_insurance = $card->no_insurance,$game_activity;
        }
        return $card;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function action(Request $request) {
        if($request->get('id')) {
            $id = $request->get('id');
            if($id) {
                $notification = Notifications::find($id);
                if($notification) {
                    $notification->status = 1;
                    $notification->save();
                    $activity = GameActivity::find($notification->activity_id);
                    $invited_players = array();
                    if($activity->life_happens_request_to) {
                        $invited_players = json_decode($activity->life_happens_request_to);
                    }
                    if(count($invited_players) && in_array(Auth::user()->player->id,$invited_players)) {
                        if($request->get('action') && $request->get('action') == 'married'){
                            $activity->is_married = Auth::user()->player->id;
                        }
                        $activity->save();
                        if($activity->is_married) {
                            $partner_activity = GameActivity::where('game_id','=',$activity->game_id)->where('player_id','=',Auth::user()->player->id)->where('round_number','=',$activity->round_number)->first();
                            if($partner_activity) {
                                $partner_activity->is_married = $activity->player_id;
                                $partner_activity->save();
                                updatePartnerBalance($partner_activity,$activity->player_id);
                                updatePartnerBalance($activity,$partner_activity->player_id);
                            }
                        }
                        return true;
                    }
                    else {
                        $employee_activity = $activity;
                        $user = Player::find($activity->player_id)->user;
                        $employee_activity->username = $user->name;
                        $employer_activity = GameActivity::where('game_id','=',$activity->game_id)->where('player_id','=',Auth::user()->player->id)->where('round_number','=',$activity->round_number)->first();
                        if($employee_activity && $employer_activity) {
                            $html = view('partials.bankruptcy', ['employee'=>$employee_activity,'employer'=>$employer_activity])->render();
                        }

                        //return response()->json(["status" => 200, "success" => true, "html" => $html]);
                    }
                }
            }
        }
        else {
            if($request->get('activity_id') && $request->get('action')) {
                $activity = GameActivity::find($request->get('activity_id'));
                if($activity) {
                    if($request->get('action') == 'divorced') {
                        $partner_activity = GameActivity::where('game_id','=',$activity->game_id)->where('player_id','=',$activity->is_married)->where('round_number','=',$activity->round_number)->first();
                        if($partner_activity) {
                            $activity->is_married = 0;
                            $activity->save();
                            $partner_activity->is_married = 0;
                            $partner_activity->save();
                            $user = Player::find($activity->player_id)->user;
                            $username = $user->name;
                            sendNotification($activity,$partner_activity,'You and '.$username.' got divorced','divorced');
                            return true;
                        }
                    }
                    elseif ($request->get('action') == 'died') {
                        if($activity->is_married) {
                            $partner_activity = GameActivity::where('game_id','=',$activity->game_id)->where('player_id','=',$activity->is_married)->where('round_number','=',$activity->round_number)->first();
                            $partner_activity->round_balance = $partner_activity->round_balance + $activity->round_balance;
                        }
                        $activity->amount = rand(Session::get('setting')->minimum_balance,Session::get('setting')->maximum_balance);
                        $activity->career_cards_id = null;
                        $activity->career_cards_amount = null;
                        $activity->present_expenses_cards_id = null;
                        $activity->present_expenses_cards_amount = null;
                        $activity->past_expenses_cards_id = null;
                        $activity->past_expenses_cards_amount = null;
                        $activity->insurance = null;
                        $activity->insurance_cost = null;
                        $activity->lottery = null;
                        $activity->lottery_cost = null;
                        $activity->life_happens_id = null;
                        $activity->life_happens_amount = null;
                        $activity->life_happens_request_to = null;
                        $activity->is_married = null;
                        $activity->emp_of = null;
                        $activity->realestate_id = null;
                        $activity->realestate_amount = null;
                        $activity->round_balance = $activity->amount;
                        $activity->save();
                        return true;
                    }
                    elseif ($request->get('action') == 'change-career') {
                        $amount = $request->get('amount');
                        if($amount) {
                            $activity->round_balance = $activity->round_balance-$amount-$activity->career_cards_amount;
                            $activity->career_cards_id = null;
                            $activity->career_cards_amount = null;
                            $activity->save();
                            return true;
                        }
                    }
                    elseif ($request->get('action') == 'bankruptcy') {
                        $activity->round_balance = 0;
                        $activity->career_cards_amount = 0;
                        $activity->save();
                        $user = Player::find($activity->player_id)->user;
                        $username = $user->name;
                        $game_activities = GameActivity::where('game_id','=',$activity->game_id)->where('round_number','=',$activity->round_number)->where('player_id','!=',$activity->player_id)->get();
                        foreach($game_activities as $other_activity) {
                            sendNotification($activity,$other_activity,$username.' got bankrupted','bankruptcy');
                        }

                    }
                }
            }
        }
        return false;
    }
}

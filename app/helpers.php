<?php

use App\Events\Balance;
use App\Events\CashBalance;
use App\Events\SendNotification;
use App\Events\TeamScoreCard;
use App\Events\RealEstate;
use App\Events\NextRound;
use App\Events\PartnerBalance;
use App\Events\ActivityTrigger;
use App\Models\CareerCard;
use App\Models\LifeHappenCard;
use App\Models\GameActivity;
use App\Models\User;
use App\Models\Notifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

if(!function_exists('getAllPlayers')) {
    function getAllPlayers($game_id,$round_number)
    {
        if($game_id && $round_number) {
            $online_players = User::select('players.id','users.name','users.image_url','game_activities.round_balance')
                ->join('players','users.id','=','players.user_id')
                ->join('game_activities','game_activities.player_id','=','players.id')
                ->where('game_activities.game_id','=',$game_id)
                ->where('game_activities.round_number','=',$round_number)
                ->get();
            return $online_players;
        }
        return false;
    }
}

if(!function_exists('balance')) {
     function balance($game_activity) {
        $spending = array();
        if($game_activity) {
            $expenses = 0;
            $earnings = 0;
            $cash_balance = $game_activity->round_balance;
            if($game_activity->amount) {
                $spending['Revenue']['amount'] = '$'.$game_activity->amount;
            }
            if($game_activity->career_cards_amount) {
                $spending['Employee Earnings']['amount'] = '$'.$game_activity->career_cards_amount;

                $career = CareerCard::find($game_activity->career_cards_id);
                $spending['Employee Earnings']['data'] = array($career->name=>'$'.$game_activity->career_cards_amount);
                $earnings = $game_activity->career_cards_amount;
            }

            if($game_activity->present_expenses_cards_amount) {
                $spending['Expenses']['data']['Present Expense'] = '$'.$game_activity->present_expenses_cards_amount;
                $expenses = $expenses + $game_activity->present_expenses_cards_amount;
            }
            if($game_activity->past_expenses_cards_amount) {
                $spending['Expenses']['data']['Past Expense'] = '$'.$game_activity->past_expenses_cards_amount;
                $expenses = $expenses + $game_activity->past_expenses_cards_amount;
            }
            $spending['Expenses']['amount'] = '$'.$expenses;
            if($game_activity->insurance_cost) {
                $spending['Insurance']['amount'] = '$'.$game_activity->insurance_cost;
                $expenses = $expenses + $game_activity->insurance_cost;
            }
            if($game_activity->lottery_cost) {
                $spending['Lottery']['amount'] = '$'.$game_activity->lottery_cost;
                $expenses = $expenses + $game_activity->lottery_cost;
            }
            if($game_activity->lottery_win) {
                $spending['Lottery Win']['amount'] = '$'.$game_activity->lottery_win;
                $earnings = $earnings + $game_activity->lottery_win;
            }
            if($game_activity->life_happens_amount) {
                $spending['Life Happens']['amount'] = '$'.$game_activity->life_happens_amount;
                $life_happens = LifeHappenCard::find($game_activity->life_happens_id)->first();
                if($life_happens) {
                    if($life_happens->impact == 'p') {
                        $earnings = $earnings + $game_activity->life_happens_amount;
                        $spending['Life Happens']['data'] = array($life_happens->heading => $game_activity->life_happens_amount);
                    }
                    else {
                        $expenses = $expenses + $game_activity->life_happens_amount;
                        $spending['Life Happens']['data'] = array($life_happens->heading => $game_activity->life_happens_amount);
                    }
                }
            }
        }
        return $spending;
    }
}

if(!function_exists('cashBalance')) {
    function cashBalance($game_activity) {
        $response = array();
        if ($game_activity) {
            $response['Starting Balance'] = array('amount'=>'$'.$game_activity->amount);
            if($game_activity->career_cards_amount) {
                $response['Occupation'] = array('amount'=>'$'.$game_activity->career_cards_amount);
            }
            if($game_activity->life_happens_amount) {
                $response['Annual Life Expense'] = array('amount'=>'$'.$game_activity->life_happens_amount);
            }
        }
        return $response;
    }
}

if(!function_exists('partnerBalance')) {
    function partnerBalance($activity,$showPartnerBalance = true) {
        $response = array();
        if($activity) {
            if($activity->is_married) {
                if($showPartnerBalance) {
                    $partner_player_id = $activity->is_married;
                    $partner_activity = GameActivity::where('game_id','=',$activity->game_id)->where('player_id','=',$partner_player_id)->where('round_number','=',$activity->round_number)->first();
                    if($partner_activity) {
                        $response['Cash Balance'] = array('amount'=>'$'.$partner_activity->round_balance);
                    }
                }
                else {
                    $response['Cash Balance'] = array('amount'=>'$'.$activity->round_balance);
                }
            }
        }
        return $response;
    }
}

if(!function_exists('realEstate')) {
    function realEstate($game_activity) {
        $response = array();
        if ($game_activity) {
            $game_id = $game_activity->game_id;
            $player_id = $game_activity->player_id;
            $realestates = GameActivity::select('game_activities.id','game_activities.realestate_id','game_activities.realestate_amount','game_activities.round_number','realestates.round_number as realestate_round')
                ->join('realestates','realestates.id','=','game_activities.realestate_id')
                ->where('game_activities.game_id','=',$game_id)
                ->where('game_activities.player_id','=',$player_id)
                ->where('game_activities.realestate_sold','!=','1')
                ->whereNotNull('game_activities.realestate_id')->get();
            return $realestates;
        }
        return $response;
    }
}

if(!function_exists('calculate_balance')) {
    function calculate_balance($balance,$payment,$expense = true) {
        if($balance && $payment) {
            if($expense) {
                return $balance - $payment;
            }
            else {
                return $balance + $payment;
            }
        }
        return $balance;
    }
}

if(!function_exists('notifications')) {
    function notifications($activity) {
        if($activity) {
            $notifications = Notifications::where('to_player_id','=',$activity->player_id)->where('status','=','0')->orderBy('id','DESC')->get();
            return $notifications;
        }
    }
}

if(!function_exists('updateDashboard')) {
    function updateDashboard($game,$activity) {
        if($game && $activity) {
            updateScorecard($game,$activity);
            updateBalance($game, $activity);
            updateCashBalance($game, $activity);
            updateRealEstate($game,$activity);
            activityTrigger($activity);
        }
    }
}

if(!function_exists('updateScorecard')) {
    function updateScorecard($game,$activity) {
        if($game) {
            $players = getAllPlayers($game->id,$activity->round_number);
            $scorecard_html = view('partials.team_scorecard', compact('players'))->render();
            //event(new TeamScoreCard(array('slug'=>$game->slug,'id'=>$activity->player_id,'html'=>$scorecard_html)));
            event(new TeamScoreCard(array('slug'=>$game->slug,'html'=>$scorecard_html)));
        }
    }
}

if(!function_exists('updateBalance')) {
    function updateBalance($game,$activity) {
        if($game && $activity) {
            $balance = balance($activity);
            $balance_html = view('partials.balance', compact('balance'))->render();
            event(new Balance(array('slug'=>$game->slug,'id'=>$activity->player_id,'html'=>$balance_html)));
        }
    }
}

if(!function_exists('updateCashBalance')) {
    function updateCashBalance($game,$activity) {
        if($game && $activity) {
            $cashBalance = cashBalance($activity);
            $balance_sheet_html = view('partials.balance_sheet', compact('cashBalance'))->render();
            event(new CashBalance(array('slug'=>$game->slug,'id'=>$activity->player_id,'html'=>$balance_sheet_html)));
        }
    }
}

if(!function_exists('updatePartnerBalance')) {
    function updatePartnerBalance($activity,$partner_id) {
        if($activity && $partner_id) {
            $partnerBalance = partnerBalance($activity,false);
            $balance_sheet_html = view('partials.partner_balance', compact('partnerBalance'))->render();
            event(new PartnerBalance(array('slug'=>Session::get('game')->slug,'id'=>$partner_id,'html'=>$balance_sheet_html)));
        }
    }
}

if(!function_exists('updateRealEstate')) {
    function updateRealEstate($game,$activity) {
        if($game && $activity) {
            $realestates = realEstate($activity);
            $realestate_html = view('partials.realestate', compact('realestates'))->render();
            event(new RealEstate(array('slug'=>$game->slug,'id'=>$activity->player_id,'html'=>$realestate_html)));
        }
    }
}

if(!function_exists('activityTrigger')) {
    function activityTrigger($activity) {
        if($activity) {
            event(new ActivityTrigger($activity));
        }
    }
}


if(!function_exists('decideNextRound')) {
    function decideNextRound($game) {
        if($game) {
            $get_max_round = GameActivity::select('round_number')->where('game_id','=',$game->id)->orderBy('id','DESC')->first();
            $max_round = $get_max_round->round_number;
            if($max_round) {
                $activities = GameActivity::where('game_id','=',$game->id)->where('round_number','=',$max_round)->get();
                if($activities) {
                    $next_round = true;
                    foreach($activities as $activity) {
                        if(!$activity->career_cards_id || !$activity->present_expenses_cards_id || !$activity->past_expenses_cards_id || !$activity->life_happens_id ) {
                            $next_round = false;
                        }
                        else {
                            if($activity->life_happens_id) {
                                if($activity->life_happens_request_to != '' && (!$activity->is_married || !$activity->emp_of)) {
                                    $next_round = false;
                                }
                            }
                        }
                        /*if(!$activity->round_complete) {
                            $next_round = false;
                        }*/
                    }
                    if($next_round) {
                        foreach($activities as $activity) {
                            $new_game_activity = new GameActivity();
                            $new_game_activity->game_id = $activity->game_id;
                            $new_game_activity->player_id = $activity->player_id;
                            $new_game_activity->round_number = $activity->round_number+1;
                            $new_game_activity->amount = $activity->round_balance;
                            $new_game_activity->round_balance = $activity->round_balance;
                            $new_game_activity->career_cards_id = $activity->career_cards_id;
                            $new_game_activity->career_cards_amount = income_tax($activity->career_cards_id);
                            $new_game_activity->past_expenses_cards_id = $activity->past_expenses_cards_id;
                            $new_game_activity->past_expenses_cards_amount = $activity->past_expenses_cards_amount;
                            $new_game_activity->present_expenses_cards_id = $activity->present_expenses_cards_id;
                            $new_game_activity->present_expenses_cards_amount = $activity->present_expenses_cards_amount;
                            $new_game_activity->is_married = $activity->is_married;
                            $new_game_activity->emp_of = $activity->emp_of;
                            $new_game_activity->save();
                            event(new NextRound(array('slug'=>$game->slug,'next_round'=>$next_round)));
                        }
                    }
                }
            }
        }
    }
}

if(!function_exists('inflation')) {
    function inflation($amount,$activity) {
        if($amount) {
            $round_number = $activity->round_number;
            if($round_number == 1) {
                return $amount;
            }
            else {
                $inflation = Session::get('setting')->inflation_increase;
                for($loop = 1; $loop < $round_number; $loop++) {
                    $amount = $amount+($amount*$inflation)/100;
                }
            }
            return $amount;
        }
    }
}

if(!function_exists('property_appreciation')) {
    function property_appreciation($amount,$activity) {
        $appreciation = unserialize(Session::get('setting')->property_appreciation);
        $appreciation_percentage = 0;
        if($amount) {
            $round_number = $activity->round_number;
            if($round_number == 1) {
                return $amount;
            }
            else {
                if(is_array($appreciation)) {
                    if(isset($appreciation['option']) && $appreciation['option'] == 'yes') {
                        if(isset($appreciation['answer']) && $appreciation['answer']) {
                            $appreciation_percentage = $appreciation['answer'];
                        }
                    }
                }
                if($appreciation_percentage) {
                    for($loop = 1; $loop < $round_number; $loop++) {
                        $amount = $amount+($amount*$appreciation_percentage)/100;
                    }
                }
            }
            return $amount;
        }
    }
}

if(!function_exists('income_tax')) {
    function income_tax($career_card_id) {
        if($career_card_id) {
            $career = CareerCard::find($career_card_id);
            if($career) {
                $salary = $career->amount;
                if(Session::get('setting')->tax_setup == 'straight') {
                    $salary = $salary - 10000;
                }
                else {
                   if($salary <= 10000) {
                       $salary = $salary - (($salary*10)/100);
                   }
                   elseif($salary > 10000 && $salary <= 30000) {
                       $salary = $salary - (($salary*15)/100);
                   }
                   elseif($salary > 30000 && $salary <= 70000) {
                       $salary = $salary - (($salary*25)/100);
                   }
                   elseif($salary > 70000 && $salary <= 150000) {
                       $salary = $salary - (($salary*35)/100);
                   }
                   elseif($salary > 150000) {
                       $salary = $salary - (($salary*40)/100);
                   }
                }
                return $salary;
            }
        }
    }
}

if(!function_exists('sendNotification')) {
    function sendNotification($from_player,$to_player,$text,$action) {
        $notification = new Notifications();
        $notification->game_id = $from_player->game_id;
        $notification->activity_id = $from_player->id;
        $notification->from_player_id = $from_player->player_id;
        if(is_object($to_player)) {
            $notification->to_player_id = $to_player->player_id;
        }
        else {
            $notification->to_player_id = $to_player;
        }
        $notification->text = $text;
        $notification->status = 0;
        $notification->action = $action;
        $notification->save();
        $notifications = array();
        $notifications[0] = $notification;
        $notification_html = view('partials.notification', compact('notifications'))->render();
        $notification_count = Notifications::where('to_player_id','=',$notification->to_player_id)->where('status','=','0')->count();
        event(new SendNotification(array('slug'=>Session::get('game')->slug,'id'=>$notification->to_player_id,'html'=>$notification_html,'count'=>$notification_count,'action'=>$action)));
        return true;
    }
}


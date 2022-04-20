<div class="popup-{{ $data->class }}">
    <div class="inner">
        <div class="main-hd {{ $data->class }}-tab">{{ $data->heading }}</div>
        <div class="detail life-happens">
            <h3>{{ $data->occurance }}</h3>
            @if($data->notation)
                <h4>{{ $data->notation }}</h4>
            @endif
            <div class="col-12">
                @if($data->get_input == true)
                <form id="popupForm" action="{{ url('lifehappens', [ 'activity_id' => $activity->id ]) }}" method="post">
                    <div class="form-group">
                        {{ method_field('POST') }}
                        {{ csrf_field() }}
                        <input type="text" placeholder="Enter Amount" id="amount" name="amount" class="form-control" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <input type="hidden" id="hid_amount" value="{{ $data->valid_amount }}" />
                        <input type="hidden" name="id" value="{{ $data->id }}" />
                        <input type="submit" class="btn" value="Submit" />
                    </div>
                </form>
                @endif
                @if(count($data->players))
                    <form id="popupForm" action="{{ url('lifehappens', [ 'activity_id' => $activity->id ]) }}" method="post">
                       <div class="form-row">
                           <ul>
                               @foreach($data->players as $player)
                                   <li>
                                       @if($data->slug == 'married')
                                           <div class="form-check form-check-inline">
                                               <input class="form-check-input" type="checkbox" name="players[]" value="{{ $player['id'] }}">
                                               <label class="form-check-label" for="inlineCheckbox1">{{ $player['info']->name }}</label>
                                           </div>
                                       @else
                                           <div class="form-check form-check-inline">
                                               <input class="form-check-input" type="radio" name="players" value="{{ $player['id'] }}">
                                               <label class="form-check-label" for="inlineCheckbox1">{{ $player['info']->name }}</label>
                                           </div>
                                       @endif
                                   </li>
                               @endforeach
                           </ul>
                       </div>
                        <div class="form-row">
                            <input type="hidden" name="id" value="{{ $data->id }}">
                            <input type="submit" class="btn {{ $data->button_class }}" value="{{ $data->button_text }}" />
                        </div>
                    </form>
                @endif
                @if(count($data->realestates) > 0)
                    <form id="popupForm" action="{{ url('lifehappens', [ 'activity_id' => $activity->id ]) }}" method="post">
                        <div class="form-row">
                            <ul id="lifehappens-sell">
                                @foreach($data->realestates as $realestate)
                                    <li>
                                        <div class="left-col">
                                            <h4>Property {{ $realestate->realestate_id }}</h4>
                                        </div>
                                        <div class="right-col">
                                            <h5>${{ $realestate->realestate_amount }}</h5>
                                        </div>
                                        <div class="last-col">
                                            <a href="javascript:void(0);" class="realestate-btn {{ (session()->get('current_round') == $realestate->realestate_round) ? 'disabled' : '' }}" data-area="from-lifehappens" data-id="{{ $realestate->realestate_id }}" data-activity="{{ $realestate->id }}">SELL</a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </form>
                @endif
                @if($data->slug == 'died')
                    <input type="button" class="btn" value="Reset Game" id="died" data-id="{{ $activity->id }}" />
                @endif
                @if($data->slug == 'divorced')
                    <input type="button" class="btn" value="Okay" id="divorced" data-id="{{ $activity->id }}" />
                @endif
                @if($data->slug == 'career-change' || $data->slug == 'career-change-1')
                    <input type="button" class="btn" value="Pick Career" id="change-career" data-id="{{ $activity->id }}" data-amount="{{ $data->amount }}" />
                @endif
                @if($data->slug == 'bankruptcy' && $data->notation == '')
                        <input type="button" class="btn" value="Okay" id="bankruptcy" data-id="{{ $activity->id }}" />
                @endif
            </div>
        </div>
        <div class="avatar-right life-happens {{ $data->slug }}">
            <img src="{{ $data->image }}" alt="{{ $data->heading }}">
        </div>
    </div>
</div>
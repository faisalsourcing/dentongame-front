<ul style="width:90%;">
    @isset($realestates)
        @foreach ($realestates as $realestate)
            <li>
                <div class="left-col">
                    <h4>Property {{ $realestate->realestate_id }}</h4>
                </div>
                <div class="right-col">
                    <h5>${{ $realestate->realestate_amount }}</h5>
                </div>
                <div class="last-col">
                    <a href="javascript:void(0);" class="realestate-btn {{ (session()->get('current_round') == $realestate->realestate_round) ? 'disabled' : '' }}"  data-id="{{ $realestate->realestate_id }}" data-activity="{{ $realestate->id }}">SELL</a>
                </div>
            </li>
        @endforeach
    @endisset
</ul>
<div class="clearfix"></div>
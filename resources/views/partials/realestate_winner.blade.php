<div class="popup-orange">
    <div class="inner">
        <div class="main-hd orange-tab">{{ $data->name }}</div>
        <div class="detail">
            <div class="col-12 text-center">
                @if($data->roll_dice == 1)
                    <h2>Congratulations!</h2><br /><p>You are the winner of the auction. You can now roll a dice to try your luck.</p>
                @else
                    <p>The winner of the auction is {{ $data->winner_name }}</p>
                @endif
            </div>
            <br />
            @if($data->roll_dice == 1)
                <input type="button" class="btn" value="Okay" id="prepare_dice" data-activity="{{ $data->activity_id }}" data-rollfor="realestate" />
            @else
                <input type="button" class="btn" value="Okay" id="close_popup" />
            @endif
        </div>
        <div class="avatar-left realestate-img">
            <img src="{{ $data->image }}" alt="{{ $data->name }}">
        </div>
    </div>
</div>
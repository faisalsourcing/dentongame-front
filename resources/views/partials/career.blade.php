<div class="popup-green">
    <div class="inner">
        <div class="main-hd green-tab">Career</div>
        <div class="detail">
            <h2>Congratulations! You are a {{ $data->name }} and your annual salary is ${{ $data->amount }}</h2>
            <div class="col-12">
                <form id="popupForm" action="{{ url('career', [ 'activity_id' => $activity->id ]) }}" method="post">
                    <div class="form-group">
                        {{ method_field('POST') }}
                        {{ csrf_field() }}
                        <input type="text" placeholder="Enter Amount" id="amount" name="amount" class="form-control" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <input type="hidden" id="hid_amount" value="{{ $data->amount }}">
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <input type="submit" class="btn" value="Submit"></input>
                    </div>
                </form>
            </div>

        </div>
        <div class="avatar-right {{ $data->slug }}">
            <img src="{{ $data->image }}" alt="{{ $data->name }}">
        </div>
    </div>
</div>
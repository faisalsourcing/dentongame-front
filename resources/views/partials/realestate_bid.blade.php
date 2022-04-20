<div class="popup-pink">
    <div class="inner">
        <div class="main-hd pink-tab">REAL ESTATE</div>
        <div class="detail">
            <h3>Timer: <label id="timer">02:00</label></h3>
            <h2>{{ $data->name }}</h2>
            <div id="auctions" class="col-12">

            </div>
            <div class="col-12 mt-3"><h3>Enter your Bid: <label id="bid_amount">${{ $data->amount }}</label></h3></div>
            <div class="col-12">
                <form id="popupForm" action="{{ url('realestate', [ 'activity_id' => $activity->id ]) }}" method="post">
                    <div class="form-group">
                        {{ method_field('POST') }}
                        {{ csrf_field() }}
                        <input type="text" placeholder="Enter Amount" id="amount" name="amount" class="form-control" autocomplete="off" />
                    </div>
                    <div class="form-inline">
                        <div class="form-group mb-2 ml-5 mr-5">
                            <input type="hidden" id="hid_amount" value="{{ $data->amount }}">
                            <input type="hidden" id="realestate_id" name="id" value="{{ $data->id }}">
                            <input type="submit" class="btn" value="Submit" />
                        </div>
                        <div class="form-group mb-2">
                            <input type="reset" class="btn optout" value="Opt Out" />
                        </div>
                    </div>
                </form>
            </div>

        </div>
        <div class="avatar-left realestate-img">
            <img src="{{ $data->image }}" alt="{{ $data->name }}">
        </div>
    </div>
</div>
@section('popup-modal-script')
    <script type="text/javascript">
        Echo.channel('realestate.{{ $data->id }}')
            .listen('RealEstateBid', function(e) {
                $('.modal-body').find('#auctions').html(e.html);
                $('.modal-body').find('#bid_amount').html(e.next_amount);
                $('.modal-body').find('#hid_amount').val(e.next_amount);
            });
    </script>
@endsection
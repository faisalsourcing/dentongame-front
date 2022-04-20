<div class="popup-pink">
    <div class="inner">
        <div class="main-hd pink-tab">INSURANCE</div>
        <div class="detail">
            <h2>{{ $data->name }}<br /><br />${{ $data->amount }}</h2>
            <div class="col-12">
                <form id="popupForm" action="{{ url('insurance', [ 'activity_id' => $activity->id ]) }}" method="post">
                    <div class="form-group">
                        {{ method_field('POST') }}
                        {{ csrf_field() }}
                        <input type="text" placeholder="Enter Amount" id="amount" name="amount" class="form-control" autocomplete="off" />
                        <p class="text-danger"></p>
                    </div>
                    <div class="form-inline">
                        <div class="form-group mb-2 ml-5 mr-5">
                            <input type="hidden" id="hid_amount" value="{{ $data->amount }}">
                            <input type="hidden" name="id" id="id" value="{{ $data->id }}">
                            <input type="submit" class="btn" value="Submit" />
                        </div>
                        <div class="form-group mb-2">
                            <input type="reset" class="btn optout" value="Opt Out" data-action="insurance" />
                        </div>
                    </div>
                </form>
            </div>

        </div>
        <div class="avatar-left insurance">
            <img src="{{ $data->image }}" alt="{{ $data->name }}">
        </div>
    </div>
</div>
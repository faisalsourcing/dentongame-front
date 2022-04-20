<div class="popup-orange">
    <div class="inner">
        <div class="main-hd orange-tab">Send Offer</div>
        <div class="detail">
            <h2>Send offer to {{ $employee->name }} to hire.</h2>
            <div class="col-12">
                <form id="popupForm" action="{{ url('past_choice', [ 'activity_id' => $employee->id ]) }}" method="post">
                    <div class="form-group">
                        {{ method_field('POST') }}
                        {{ csrf_field() }}
                        <input type="text" placeholder="Enter Amount" id="amount" name="amount" class="form-control" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="id" value="{{ $employee->id }}">
                        <input type="submit" class="btn" value="Submit"></input>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@isset($notifications)
    @foreach($notifications as $notification)
    <div class="notifications-item" style="width:100%;">
        <div class="text">
            <h4>{{ $notification->text }}</h4>
            @if($notification->action == 'married')
                <div class="btn-group" data-id="{{ $notification->id }}">
                    <input type="button" data-action="accept" data-slug="married" value="Accept" class="btn btn-sm btn-primary mr-2 rounded notification-btn" />
                    <input type="button" data-action="reject" data-slug="married" value="Reject" class="btn btn-sm btn-danger mr-2 rounded notification-btn" />
                </div>
            @endif
            @if($notification->action == 'bankruptcy')
                <div class="btn-group" data-id="{{ $notification->id }}">
                    <input type="button" data-action="accept" data-slug="bankruptcy" value="Send Offer" class="btn btn-sm btn-primary mr-2 rounded notification-btn" />
                </div>
            @endif
        </div>
    </div>
    @endforeach
@endisset
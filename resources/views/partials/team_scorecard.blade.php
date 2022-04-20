<ul>
    @foreach ($players as $key => $player)
        <li>
            <div class="number">#{{ ++$key }}</div>
            <div class="team-name">
                <a href="#">
                    <span>
                        @if( $player->nameimage_url)
                            <img class="avatar" src="assets/images/avatar-2.png" alt="">
                        @else
                            <img class="avatar" src="assets/images/avatar-2.png" alt="">
                        @endif
                    </span>
                    <div class="info">
                        <h3>{{ $player->name }} </h3>
                    </div>
                </a>
            </div>
            <div class="team-price">
                <h5>${{ $player->round_balance }}</h5>
            </div>
        </li>
    @endforeach
</ul>
<div class="clearfix"></div>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>What's My Balance - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css?'.time()) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;400;500;600;700;900&display=swap" rel="stylesheet">
</head>

<body>
@include('layouts.header')
<main>
    <div class="container">
        <div class="main-content">
            @include('layouts.topbar',['notifications' => $notifications])
            <div class="content-area">
                <div class="row">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</main>
@include('modals.common-modal');
<script src="{{ asset('assets/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="//js.pusher.com/5.0/pusher.min.js"></script>
<script src="{{ asset('assets/js/echo.iife.js') }}"></script>
<script src="{{ asset('assets/js/echo.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
<script type="text/javascript">
    var APP_URL = {!! json_encode(url('/')) !!}

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '2e2822a1643f12845bb2',
        cluster: 'us3',
        forceTLS: true
    });
    Echo.channel('teamscorecard.{{ $game->slug }}')
        .listen('TeamScoreCard', function(e) {
            $('#team-scorecard').find('#teamscorecard').html(e.html);
        });
    Echo.channel('balance.{{ $game->slug }}.{{ $activity->player_id }}')
        .listen('Balance', function(e) {
            $('#balance').find('#yourbalance').html(e.html);
        });
    Echo.channel('cashbalance.{{ $game->slug }}.{{ $activity->player_id }}')
        .listen('CashBalance', function(e) {
            $('#balance-sheet').find('#cashbalance').html(e.html);
        });
    Echo.channel('realestates.{{ $game->slug }}.{{ $activity->player_id }}')
        .listen('RealEstate', function(e) {
            $('#realstate').find('#realstates').html(e.html);
        });
    Echo.channel('notification.{{ $game->slug }}.{{ $activity->player_id }}')
        .listen('SendNotification', function(e) {
            $('#notification_count').html(e.count);
            $('#box').prepend(e.html);
            if(e.action == 'divorced') {
                $('.partner-balance').hide();
            }
        });
    Echo.channel('partner.{{ $game->slug }}.{{ $activity->player_id }}')
        .listen('PartnerBalance', function(e) {
            $('#partner-sheet').find('#partnerbalance').html(e.html);
            $('#partner-sheet').closest('.partner-balance').show();
        });
    Echo.channel('nextround.{{ $game->slug }}')
        .listen('NextRound', function(e) {
            if(e.round) {
                location.reload();
            }
        });
    Echo.channel('refreshgame.{{ $game->slug }}')
        .listen('RefreshGame', function(e) {
            if(e.round) {
                location.reload();
            }
        });
    Echo.channel('triggerestate.{{ $game->slug }}.{{ $activity->round_number }}')
        .listen('TriggerEstate', function(e) {
            $(this).showRealestate();
            next_round = 'lifehappens';
        });
    Echo.channel('realestatebidwinner.{{ $game->id }}.{{ $activity->id }}')
        .listen('RealEstateWinner', function(e) {
            $(this).showBiddingResult(e.activity_id,e.game_id,e.player_id);
        });

</script>
@yield('script')
<script type="text/javascript" src="{{ asset('assets/js/popups.js?'.time()) }}"></script>
@yield('popup-modal-script')
</body>
</html>


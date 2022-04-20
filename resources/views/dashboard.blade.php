@extends('layouts.dashboard',['notifications' => $notifications])

@section('title', 'Dashboard')

@section('content')
    <div class="col-12 col-md-6">
        <div class="your-balance">
            <div id="balance">
                <h2 data-toggle="collapse" data-target="#yourbalance" aria-expanded="false" aria-controls="yourbalance">Your Balance</h2>
                <div class="faq-body" id="yourbalance">
                    @include('partials.balance')
                </div>
            </div>
        </div>
        <div class="balance-sheet">
            <div id="balance-sheet">
                <h2 data-toggle="collapse" data-target="#cashbalance" aria-expanded="false" aria-controls="cashbalance">Cash Balance Sheet</h2>
                <div class="faq-body" id="cashbalance">
                    @include('partials.balance_sheet')
                </div>
            </div>
        </div>
        <div class="partner-balance" style="display:{{ (!$activity->is_married) ? 'none;' : '' }}">
            <div id="partner-sheet">
                <h2 data-toggle="collapse" data-target="#partnerbalance" aria-expanded="false" aria-controls="partnerbalance">Partner Cash Balance</h2>
                <div class="faq-body" id="partnerbalance">
                    @include('partials.partner_balance')
                </div>
            </div>
        </div>
        @if($activity->is_married)

        @endif
    </div>
    <div class="col-12 col-md-6">
        <div class="team-scorecard">
            <div id="team-scorecard">
                <h2 data-toggle="collapse" data-target="#teamscorecard" aria-expanded="false" aria-controls="teamscorecard">team Scorecard</h2>
                <div class="faq-body" id="teamscorecard">
                    @include('partials.team_scorecard')
                </div>
            </div>
        </div>
        <div class="realstate">
            <div id="realstate">
                <h2 data-toggle="collapse" data-target="#realstates" aria-expanded="false" aria-controls="collapserealstate">Real Estate</h2>
                <div class="faq-body" id="realstates">
                    @include('partials.realestate')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        var ACTIVITY = {!! $activity->id !!}
        var round = {!! $activity->round_number !!}
        var next_round = 'lifehappens';
        var trigger_career = {{ ($activity->career_cards_id && $activity->career_cards_amount) ? '0' : '1' }};
        var trigger_present_choice = {{ ($activity->present_expenses_cards_id && $activity->present_expenses_cards_amount) ? '0' : '1' }};
        var trigger_past_choice = {{ ($activity->past_expenses_cards_id && $activity->past_expenses_cards_amount) ? '0' : '1' }};
        var trigger_insurance = {{ !is_int($activity->insurance) ? '1' : '0' }};
        var trigger_lottery = {{ !is_int($activity->lottery) ? '1' : '0' }};
        var trigger_lifehappens = {{ $activity->life_happens_id ? '1' : '0' }};
    </script>
@endsection
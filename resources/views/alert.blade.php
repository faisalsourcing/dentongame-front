@extends('layouts.alert',['player' => $player])

@section('title', 'Dashboard')

@section('content')
    <div class="col-12 col-md-12">
        <h1>{{ $alert }}</h1>
    </div>
@endsection
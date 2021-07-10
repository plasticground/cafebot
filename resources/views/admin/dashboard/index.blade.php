@extends('layouts.app')

@section('title', 'Control panel')

@section('content')
    <div class="container">
        <h3>Dashboard</h3>

        <a href="{{ route('admin.bot.index') }}">Bot info</a>
    </div>
@endsection

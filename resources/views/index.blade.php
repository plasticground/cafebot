@extends('layouts.app')

@section('title', 'Main page')

@section('content')
    <div class="container">
        @auth()
            <div class="text-block">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            </div>
            <div class="text-block">
                <a href="{{ route('logout') }}">Logout</a>
            </div>
        @endauth

        @guest()
        <form action="{{ route('login') }}" method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Name">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password">
            </div>

            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
        @endguest
    </div>
@endsection

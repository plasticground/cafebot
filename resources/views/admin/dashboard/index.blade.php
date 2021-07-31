@extends('layouts.app')

@section('title', 'Control panel')

@section('content')
    <div class="container">
        <h3>Dashboard</h3>

        <ul>
            <li>
                <a href="{{ route('admin.bot.index') }}">Bot info</a>
            </li>
            <li>
                <a href="{{ route('admin.productCategories.index') }}">Product categories</a>
            </li>
        </ul>
    </div>
@endsection

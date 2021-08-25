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
                <a href="{{ route('admin.feedback.index') }}">Feedback</a>
            </li>
            <li>
                <a href="{{ route('admin.clients.index') }}">Clients</a>
            </li>
            <li>
                <a href="{{ route('admin.orders.index') }}">Orders</a>
            </li>
            <li>
                <a href="{{ route('admin.locations.index') }}">Locations</a>
            </li>
            <li>
                <a href="{{ route('admin.cafes.index') }}">Cafes</a>
            </li>
            <li>
                <a href="{{ route('admin.menus.index') }}">Menus</a>
            </li>
            <li>
                <a href="{{ route('admin.productCategories.index') }}">Product categories</a>
            </li>
            <li>
                <a href="{{ route('admin.products.index') }}">Products</a>
            </li>
        </ul>
    </div>
@endsection

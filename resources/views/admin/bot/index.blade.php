@extends('layouts.app')

@section('title', 'Control panel - Bot')

@section('content')
    <div class="container">
        <h3>Bot info</h3>

        <div class="text-block">
            <form action="{{ route('admin.bot.setWebhook') }}" method="POST">
                <h4>Set webhook</h4>
                <a class="link-muted" href=https://core.telegram.org/bots/api#setwebhook">
                    https://core.telegram.org/bots/api#setwebhook
                </a>

                <div class="form-group">
                    <label for="url">url</label>
                    <input type="text" id="url" name="url" placeholder="url" value="{{ $webhookInfo->url }}">
                </div>

                <div class="form-group">
                    <label for="max_connections">max_connections (1-100)</label>
                    <input type="number" id="max_connections" name="max_connections" placeholder="max_connections" value="{{ $webhookInfo->max_connections }}">
                </div>

                <div class="form-group">
                    <button type="submit">Set webhook</button>
                </div>
            </form>
        </div>
        <div class="text-block">
            <h4>Webhook info:</h4>
            <a class="link-muted" href=https://core.telegram.org/bots/api#webhookinfo">
                https://core.telegram.org/bots/api#webhookinfo
            </a>
            <pre class="json">{{ var_export(json_decode($webhookInfo), 1) }}</pre>
        </div>

        <div class="text-block">
            <h4>Update:</h4>
            <a class="link-muted" href="https://core.telegram.org/bots/api#update">
                https://core.telegram.org/bots/api#update
            </a>
            <pre class="json">{{ var_export(json_decode($bot->getWebhookUpdate(), 1)) }}</pre>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Control panel - Feedback')

@section('content')
    <div class="container">
        <h3>Feedback</h3>

        <div class="text-block">
            <form action="{{ route('admin.feedback.update') }}" method="POST">
                <input type="hidden" name="_method" value="PUT">

                <div class="form-group">
                    <label for="ru_text">RU Feedback Text</label>
                    <textarea name="ru_text" id="ru_text" cols="30" rows="10">{{ $feedback->ru_text }}</textarea>
                </div>

                <div class="form-group">
                    <label for="ua_text">UA Feedback Text</label>
                    <textarea name="ua_text" id="ua_text" cols="30" rows="10">{{ $feedback->ua_text }}</textarea>
                </div>

                <div class="form-group">
                    <button type="submit">Save</button>
                </div>
            </form>
        </div>

        <a class="link-muted" href="https://core.telegram.org/bots/api#markdown-style">
            https://core.telegram.org/bots/api#markdown-style
        </a>
    </div>
@endsection

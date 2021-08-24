<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $feedback = Feedback::first();

        if (!$feedback) {
            $feedback = Feedback::create([
                'ru_text' => 'Фидбек текст RU',
                'ua_text' => 'Фидбек текст UA'
            ]);
        }

        return view('admin.feedback.index', compact('feedback'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function update(Request $request)
    {
        $feedback = Feedback::first();
        $feedback->update($request->all());

        return redirect(route('admin.feedback.index'));
    }
}

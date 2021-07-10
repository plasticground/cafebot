<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function login(Request $request)
    {
        if (Auth::attempt($request->only('name', 'password'))) {
            return redirect(route('admin.dashboard'));
        }

        return redirect(route('home'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect(route('home'));
    }
}

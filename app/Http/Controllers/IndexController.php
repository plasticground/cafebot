<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        return view('index');
    }

    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function dashboard()
    {
        return view('admin.dashboard.index');
    }
}

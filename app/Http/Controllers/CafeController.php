<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use Illuminate\Http\Request;

class CafeController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $cafes = Cafe::all();

        return view('admin.cafes.index', compact('cafes'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function store(Request $request)
    {
        Cafe::create($request->all());

        return redirect(route('admin.cafes.index'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function update($id, Request $request)
    {
        $cafe = Cafe::findOrFail($id);

        $cafe->update($request->all());

        return redirect(route('admin.cafes.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function destroy($id)
    {
        $cafe = Cafe::findOrFail($id);

        $cafe->delete();

        return redirect(route('admin.cafes.index'));
    }
}

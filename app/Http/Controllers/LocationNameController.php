<?php

namespace App\Http\Controllers;

use App\Models\LocationName;
use Illuminate\Http\Request;

class LocationNameController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {

        $locations = LocationName::all();

        return view('admin.locations.index', compact('locations'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function store(Request $request)
    {
        LocationName::create($request->all());

        return redirect(route('admin.locations.index'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function update($id, Request $request)
    {
        $location = LocationName::findOrFail($id);

        $location->update($request->all());

        return redirect(route('admin.locations.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function destroy($id)
    {
        $location = LocationName::findOrFail($id);

        $location->delete();

        return redirect(route('admin.locations.index'));
    }
}

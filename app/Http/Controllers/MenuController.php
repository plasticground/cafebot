<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $cafes = Cafe::with('menu')->get();

        return view('admin.menus.index', compact('cafes'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function store(Request $request)
    {
        $cafe = Cafe::findOrFail($request->get('cafe_id'));
        $menu = Menu::create($request->all());
        $cafe->update(['menu_id' => $menu->id]);

        return redirect(route('admin.menus.index'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function update($id, Request $request)
    {
        $menu = Menu::findOrFail($id);

        $menu->update($request->all());

        return redirect(route('admin.menus.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $cafe = Cafe::findOrFail($menu->cafe->id);

        $cafe->update(['menu_id' => null]);
        $menu->delete();

        return redirect(route('admin.menus.index'));
    }
}

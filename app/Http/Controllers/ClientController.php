<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $clients = Client::all();

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function update($id, Request $request)
    {
        $client = Client::findOrFail($id);

        $client->update($request->all());

        return redirect(route('admin.clients.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        if ($client->location) {
            $client->location->delete();
        }

        if ($client->botState) {
            $client->botState->delete();
        }

        $client->delete();

        return redirect(route('admin.clients.index'));
    }
}

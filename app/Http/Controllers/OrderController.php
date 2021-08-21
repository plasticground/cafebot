<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $orders = Order::whereHas('client')->with('products')->get();//TODO: pagination
        $orders = $orders->each(
            fn(Order $order) => $order->product_list = $order->products
                ->map(fn(Product $product) => $product->getDisplayNamePriceWithAmount('ru'))
        );

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function update($id, Request $request)
    {
        $order = Order::findOrFail($id);

        $order->update($request->all());

        return redirect(route('admin.orders.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        if ($order->products) {
            $order->products()->detach($order->products);
        }

        $order->delete();

        return redirect(route('admin.orders.index'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $cafes = Cafe::with(['menu', 'menu.categories', 'menu.categories.products'])
            ->whereNotNull('menu_id')
            ->get();

//        $categories = ProductCategory::with('products')
//            ->orderBy('sorting_position')
//            ->get();

        return view('admin.products.index', compact('cafes'));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function store(Request $request)
    {
        $sortingPosition = $request->get('sorting_position') + 1;

        DB::table('products')
            ->where('category_id', $request->get('category_id'))
            ->where('sorting_position', '>=', $sortingPosition)
            ->increment('sorting_position');

        Product::create(['sorting_position' => $sortingPosition] + $request->all());

        return redirect(route('admin.products.index'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);

        $sortingPosition = $request->get('sorting_position');

        Product::whereSortingPosition($sortingPosition)
            ->get()
            ->each(fn(Product $c) => $c->update(['sorting_position' => $product->sorting_position]));

        $product->update(['sorting_position' => $sortingPosition] + $request->all());

        return redirect(route('admin.products.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        DB::table('products')
            ->where('category_id', $product->category_id)
            ->where('sorting_position', '>', $product->sorting_position)
            ->decrement('sorting_position');

        $product->delete();

        return redirect(route('admin.products.index'));
    }
}

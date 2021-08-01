<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $categories = ProductCategory::orderBy('sorting_position')
            ->get();

        return view('admin.productCategories.index', compact('categories'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function store(Request $request)
    {
        $sortingPosition = $request->get('sorting_position') + 1;

        DB::table('product_categories')
            ->where('sorting_position', '>=', $sortingPosition)
            ->increment('sorting_position');

        ProductCategory::create(['sorting_position' => $sortingPosition] + $request->all());

        return redirect(route('admin.productCategories.index'));
    }

    public function update($id, Request $request)
    {
        $category = ProductCategory::findOrFail($id);

        $sortingPosition = $request->get('sorting_position');

        ProductCategory::whereSortingPosition($sortingPosition)
            ->get()
            ->each(fn(ProductCategory $c) => $c->update(['sorting_position' => $category->sorting_position]));

        $category->update(['sorting_position' => $sortingPosition] + $request->all());

        return redirect(route('admin.productCategories.index'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);

        DB::table('product_categories')
            ->where('sorting_position', '>', $category->sorting_position)
            ->decrement('sorting_position');

        $category->delete();

        return redirect(route('admin.productCategories.index'));
    }
}

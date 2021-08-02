@extends('layouts.app')

@section('title', 'Control panel - Products')

@section('content')
    <div class="container">
        <h3>Products</h3>

        @foreach($cafes as $cafe)
            <h4>{{ 'Cafe: (id: ' . $cafe->id . ') ' . $cafe->ru_name . ' | ' . $cafe->ua_name . ' - Menu: (id: ' . $cafe->menu->id . ') ' . $cafe->menu->name }}</h4>

            <div class="text-block" style="overflow-x: auto">
                @forelse($cafe->menu->categories as $category)
                    <h4>{{ $category->sorting_position . '#: (id: ' . $category->id . ') ' . $category->ru_name . ' | ' . $category->ua_name }}</h4>

                    @if($category->products->isNotEmpty())
                        <table>
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th width="50">ID</th>
                                    <th width="400">RU</th>
                                    <th width="400">UA</th>
                                    <th width="300">Price, ₴</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @each('admin.products.list.item', $category->products, 'product', 'admin.products.list.empty')
                            </tbody>
                        </table>
                    @else
                        @include('admin.products.list.empty')
                    @endif

                    <button id="toggle_form_btn_{{ $category->id }}" type="button" onclick="toggleNewProductForm({{ $category->id }}, this);">Add new product</button>
                    <form id="new_product_form_{{ $category->id }}" action="{{ route('admin.products.store') }}" method="POST" style="display: none">
                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                        <h4>New product</h4>

                        <div class="form-group">
                            <label for="ru_name">Name RU</label>
                            <input type="text" id="ru_name" name="ru_name" placeholder="Enter product russian name">
                        </div>

                        <div class="form-group">
                            <label for="ua_name">Name UA</label>
                            <input type="text" id="ua_name" name="ua_name" placeholder="Enter product ukrainian name">
                        </div>

                        <div class="form-group">
                            <label for="price">Price, ₴</label>
                            <input type="number" step="0.01" id="price" name="price" placeholder="Enter product price (₴)">
                        </div>

                        @if($category->products->isNotEmpty())
                            <div class="form-group">
                                <label for="sorting_position">Place after:</label>
                                <select name="sorting_position" id="sorting_position">
                                    <option value="0">Set first</option>
                                    @foreach($category->products as $product)
                                        <option value="{{ $product->sorting_position }}">{{ $product->ru_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-group">
                            <button type="submit">Create</button>
                            <button type="button" onclick="toggleNewProductForm({{ $category->id }}, document.getElementById('toggle_form_btn_{{ $category->id }}'));">&times;</button>
                        </div>
                    </form>
                @empty
                    <li>
                        <a href="{{ route('admin.productCategories.index') }}">Create category</a>
                    </li>
                @endforelse
            </div>

            <hr>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        function toggleNewProductForm(category_id, btn) {
            let form = document.getElementById(`new_product_form_${category_id}`);

            btn.style.display = btn.style.display === 'none' ? '' : 'none';
            form.style.display = form.style.display === 'none' ? '' : 'none';
        }
    </script>

    <script>
        function toggleForm(product_id) {
            let tr = document.getElementById(`product_tr_${product_id}`);
            let inputs = tr.getElementsByClassName('__input');

            for (let e of inputs) {
                e.style.display = e.style.display === 'none' ? '' : 'none';
            }
        }
    </script>
@endpush

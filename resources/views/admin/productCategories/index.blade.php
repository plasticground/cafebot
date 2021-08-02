@extends('layouts.app')

@section('title', 'Control panel - Product Categories')

@section('content')
    <div class="container">
        <h3>Product categories</h3>

        @foreach($menus as $menu)
            <div class="text-block" style="overflow-x: auto">
                <h4>{{ 'Cafe: (id: ' . $menu->cafe->id . ') ' . $menu->cafe->ru_name . ' | ' . $menu->cafe->ua_name . ' - Menu: (id: ' . $menu->id . ') ' . $menu->name }}</h4>

                @if($menu->categories->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th width="50">ID</th>
                            <th width="400">RU</th>
                            <th width="400">UA</th>
                            <th width="100"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @each('admin.productCategories.list.item', $menu->categories, 'category', 'admin.productCategories.list.empty')
                    </tbody>
                </table>
                @endif
            </div>

            <div class="text-block">
                <form action="{{ route('admin.productCategories.store') }}" method="POST">
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">

                    <h4>New category</h4>

                    <div class="form-group">
                        <label for="ru_name">Name RU</label>
                        <input type="text" id="ru_name" name="ru_name" placeholder="Enter category russian name">
                    </div>

                    <div class="form-group">
                        <label for="ua_name">Name UA</label>
                        <input type="text" id="ua_name" name="ua_name" placeholder="Enter category ukrainian name">
                    </div>

                    @if($menu->categories->isNotEmpty())
                        <div class="form-group">
                            <label for="sorting_position">Place after:</label>
                            <select name="sorting_position" id="sorting_position">
                                <option value="0">Set first</option>
                                @foreach($menu->categories as $category)
                                    <option value="{{ $category->sorting_position }}">{{ $category->ru_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group">
                        <button type="submit">Create</button>
                    </div>
                </form>
            </div>
            <hr>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        function toggleForm(category_id) {
            let tr = document.getElementById(`category_tr_${category_id}`);
            let inputs = tr.getElementsByClassName('__input');

            for (let e of inputs) {
                e.style.display = e.style.display === 'none' ? '' : 'none';
            }
        }
    </script>
@endpush

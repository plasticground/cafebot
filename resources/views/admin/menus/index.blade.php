@extends('layouts.app')

@section('title', 'Control panel - Menus')

@section('content')
    <div class="container">
        <h3>Menus</h3>

        @forelse($cafes as $cafe)

                <div class="text-block" style="overflow-x: auto">
                    <h4>{{ 'Cafe: (id: ' . $cafe->id . ') ' . $cafe->ru_name . ' | ' . $cafe->ua_name }}</h4>

                    @if($cafe->menu)
                        <table>
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th width="600">Name</th>
                                    <th width="100"></th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr id="menu_tr_{{ $cafe->menu->id }}">
                                <td>{{ $cafe->menu->id }}</td>
                                <td>
                                    <div class="__input">
                                        {{ $cafe->menu->name }}
                                    </div>
                                    <div class="__input" style="display: none">
                                        <input type="text"
                                               value="{{ $cafe->menu->name }}"
                                               oninput="document.getElementById('input_name_{{ $cafe->menu->id }}').value = this.value;"
                                        >
                                    </div>
                                </td>
                                <td style="text-align: right">
                                    <div class="__input">
                                        <a href="#" onclick="toggleForm({{ $cafe->menu->id }});">Edit</a>
                                        <a href="#" onclick="confirm('Delete {{ $cafe->menu->id . '#: ' . $cafe->menu->name }} ?') ? document.getElementById('deleteMenuForm_{{ $cafe->menu->id }}').submit() : false">Delete</a>
                                        <form id="deleteMenuForm_{{ $cafe->menu->id }}" action="{{ route('admin.menus.destroy', ['id' => $cafe->menu->id]) }}" method="POST">
                                            <input type="hidden" name="_method" value="DELETE">
                                        </form>
                                    </div>
                                    <div class="__input" style="display: none">
                                        <form id="edit_form_{{ $cafe->menu->id }}" action="{{ route('admin.menus.update', ['id' => $cafe->menu->id]) }}" method="POST">
                                            <input type="hidden" name="_method" value="PUT">
                                            <input id="input_name_{{ $cafe->menu->id }}" type="hidden" name="name" value="{{ $cafe->menu->name }}">
                                            <button type="submit">Save</button>
                                            <button type="button" onclick="toggleForm({{ $cafe->menu->id }});">&times;</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    @else
                </div>
                <div class="text-block">
                    <form action="{{ route('admin.menus.store') }}" method="POST">
                        <input type="hidden" name="cafe_id" value="{{ $cafe->id }}">

                        <h4>New menu</h4>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter menu name">
                        </div>

                        <div class="form-group">
                            <button type="submit">Create</button>
                        </div>
                    </form>
                </div>
            @endif
            <hr>
        @empty
            <li>
                <a href="{{ route('admin.cafes.index') }}">Create cafe</a>
            </li>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        function toggleForm(menu_id) {
            let tr = document.getElementById(`menu_tr_${menu_id}`);
            let inputs = tr.getElementsByClassName('__input');

            for (let e of inputs) {
                e.style.display = e.style.display === 'none' ? '' : 'none';
            }
        }
    </script>
@endpush

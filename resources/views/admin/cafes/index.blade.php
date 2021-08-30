@extends('layouts.app')

@section('title', 'Control panel - Cafes')

@section('content')
    <div class="container">
        <h3>Cafes</h3>

        @foreach($cafes as $cafe)
            <div class="text-block" style="overflow-x: auto">
                <table>
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th width="400">RU</th>
                            <th width="400">UA</th>
                            <th width="400">Menu</th>
                            <th width="100"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr id="cafe_tr_{{ $cafe->id }}">
                        <td>{{ $cafe->id }}</td>
                        <td>
                            <div class="__input">
                                {{ $cafe->ru_name }}
                            </div>
                            <div class="__input" style="display: none">
                                <input type="text"
                                       value="{{ $cafe->ru_name }}"
                                       oninput="document.getElementById('input_ru_name_{{ $cafe->id }}').value = this.value;"
                                >
                            </div>
                        </td>
                        <td>
                            <div class="__input">
                                {{ $cafe->ua_name }}
                            </div>
                            <div class="__input" style="display: none">
                                <input type="text"
                                       value="{{ $cafe->ua_name }}"
                                       oninput="document.getElementById('input_ua_name_{{ $cafe->id }}').value = this.value;"
                                >
                            </div>
                        </td>
                        <td>
                            @if($cafe->menu)
                                {{ '(id: ' . $cafe->menu->id . ') ' . $cafe->menu->name }}
                            @else
                                <a href="{{ route('admin.menus.index') }}">Create</a>
                            @endif
                        </td>
                        <td style="text-align: right">
                            <div class="__input">
                                <a href="#" onclick="toggleForm({{ $cafe->id }});">Edit</a>
                                <a href="#" onclick="confirm('Delete {{ $cafe->id . '#: ' . $cafe->ru_name }} ?') ? document.getElementById('deleteCafeForm_{{ $cafe->id }}').submit() : false">Delete</a>
                                <form id="deleteCafeForm_{{ $cafe->id }}" action="{{ route('admin.cafes.destroy', ['id' => $cafe->id]) }}" method="POST">
                                    <input type="hidden" name="_method" value="DELETE">
                                </form>
                            </div>
                            <div class="__input" style="display: none">
                                <form id="edit_form_{{ $cafe->id }}" action="{{ route('admin.cafes.update', ['id' => $cafe->id]) }}" method="POST">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input id="input_ru_name_{{ $cafe->id }}" type="hidden" name="ru_name" value="{{ $cafe->ru_name }}">
                                    <input id="input_ua_name_{{ $cafe->id }}" type="hidden" name="ua_name" value="{{ $cafe->ua_name }}">
                                    <button type="submit">Save</button>
                                    <button type="button" onclick="toggleForm({{ $cafe->id }});">&times;</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
        <div class="text-block">
            <form action="{{ route('admin.cafes.store') }}" method="POST">
                <h4>New cafe</h4>

                <div class="form-group">
                    <label for="ru_name">Name RU</label>
                    <input type="text" id="ru_name" name="ru_name" placeholder="Enter cafe russian name">
                </div>

                <div class="form-group">
                    <label for="ua_name">Name UA</label>
                    <input type="text" id="ua_name" name="ua_name" placeholder="Enter cafe ukrainian name">
                </div>

                <div class="form-group">
                    <button type="submit">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleForm(cafe_id) {
            let tr = document.getElementById(`cafe_tr_${cafe_id}`);
            let inputs = tr.getElementsByClassName('__input');

            for (let e of inputs) {
                e.style.display = e.style.display === 'none' ? '' : 'none';
            }
        }
    </script>
@endpush

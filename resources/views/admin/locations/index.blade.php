@extends('layouts.app')

@section('title', 'Control panel - Locations')

@section('content')
    <div class="container">
        <h3>Locations</h3>

        <div class="text-block" style="overflow-x: auto">
            <table>
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="400">RU</th>
                        <th width="400">UA</th>
                        <th width="100"></th>
                    </tr>
                </thead>
                <tbody>
                @each('admin.locations.list.item', $locations, 'location', 'admin.locations.list.empty')
                </tbody>
            </table>
        </div>

        <div class="text-block">
            <form action="{{ route('admin.locations.store') }}" method="POST">
                <h4>New location</h4>

                <div class="form-group">
                    <label for="ru_name">Name RU</label>
                    <input type="text" id="ru_name" name="ru_name" placeholder="Enter location russian name">
                </div>

                <div class="form-group">
                    <label for="ua_name">Name UA</label>
                    <input type="text" id="ua_name" name="ua_name" placeholder="Enter location ukrainian name">
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
        function toggleForm(location_id) {
            let tr = document.getElementById(`location_tr_${location_id}`);
            let inputs = tr.getElementsByClassName('__input');

            for (let e of inputs) {
                e.style.display = e.style.display === 'none' ? '' : 'none';
            }
        }
    </script>
@endpush

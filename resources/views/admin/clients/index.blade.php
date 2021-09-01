@extends('layouts.app')

@section('title', 'Control panel - Clients')

@section('content')
    <div class="container">
        <h3>Clients</h3>

        <div class="text-block" style="overflow-x: auto">
            <table>
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="200">Name</th>
                        <th width="150">Phone</th>
                        <th width="50">Locale</th>
                        <th width="150">Telegram ID</th>
                        <th width="200">Telegram Username</th>
                        <th width="100">Created</th>
                        <th width="100">Updated</th>
                        <th width="100"></th>
                    </tr>
                </thead>
                <tbody>
                @each('admin.clients.list.item', $clients, 'client', 'admin.clients.list.empty')
                </tbody>
            </table>
        </div>

        {{ $clients->appends(request()->all())->links('widgets.pagination', ['paginator' => $clients]) }}
    </div>
@endsection

@push('scripts')
    <script>
        function toggleForm(client_id) {
            let tr = document.getElementById(`client_tr_${client_id}`);
            let inputs = tr.getElementsByClassName('__input');

            for (let e of inputs) {
                e.style.display = e.style.display === 'none' ? '' : 'none';
            }
        }
    </script>
@endpush

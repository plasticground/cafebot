@extends('layouts.app')

@section('title', 'Control panel - Orders')

@section('content')
    <div class="container">
        <h3>Orders</h3>

        <div class="text-block" style="overflow-x: auto">
            <table>
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="150">Client</th>
                        <th width="50">Message ID</th>
                        <th width="200">Products</th>
                        <th width="50">Price</th>
                        <th width="150">Comment</th>
                        <th width="100">Status</th>
                        <th width="100">Created</th>
                        <th width="100">Updated</th>
                        <th width="100"></th>
                    </tr>
                </thead>
                <tbody>
                @each('admin.orders.list.item', $orders, 'order', 'admin.orders.list.empty')
                </tbody>
            </table>
        </div>

        {{ $orders->appends(request()->all())->links('widgets.pagination', ['paginator' => $orders]) }}
    </div>
@endsection

@push('scripts')
    <script>
        function toggleForm(order_id) {
            let tr = document.getElementById(`order_tr_${order_id}`);
            let inputs = tr.getElementsByClassName('__input');

            for (let e of inputs) {
                e.style.display = e.style.display === 'none' ? '' : 'none';
            }
        }
    </script>
@endpush

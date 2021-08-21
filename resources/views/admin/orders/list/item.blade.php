<tr id="order_tr_{{ $order->id }}">
    <td>{{ $order->id }}</td>
    <td>#{{ $order->client->id . ': ' . $order->client->name }}</td>
    <td>{{ $order->message_id }}</td>
    <td>
        <select multiple style="width: 100%; overflow: auto;">
            @foreach($order->product_list as $product)
                <option>{{ $product }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <div class="__input">
            {{ $order->price }}
        </div>
        <div class="__input" style="display: none">
            <input type="number"
                   step="0.01"
                   value="{{ $order->price }}"
                   oninput="document.getElementById('input_price_{{ $order->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $order->comment }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $order->comment }}"
                   oninput="document.getElementById('input_comment_{{ $order->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ \App\Models\Order::getVerbalStatus($order->status) }}
        </div>
        <div class="__input" style="display: none">
            <select name="status"
                    id="status"
                    onchange="document.getElementById('input_status_{{ $order->id }}').value = this.options[this.selectedIndex].value;"
            >
                @foreach(\App\Models\Order::getVerbalStatues() as $statusId => $statusName)
                <option value="{{ $statusId }}" {{ $order->status === $statusId ? 'selected' : '' }}>{{ $statusName }}</option>
                @endforeach
            </select>
        </div>
    </td>
    <td>{{ $order->created_at }}</td>
    <td>{{ $order->updated_at }}</td>
    <td style="text-align: right">
        <div class="__input">
            <a href="#" onclick="toggleForm({{ $order->id }});">Edit</a>
            <a href="#" onclick="confirm('Delete {{ $order->id . '#: ' . $order->client->name }}\'s order?') ? document.getElementById('deleteOrderForm_{{ $order->id }}').submit() : false">Delete</a>
            <form id="deleteOrderForm_{{ $order->id }}" action="{{ route('admin.orders.destroy', ['id' => $order->id]) }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
            </form>
        </div>
        <div class="__input" style="display: none">
            <form id="edit_form_{{ $order->id }}" action="{{ route('admin.orders.update', ['id' => $order->id]) }}" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input id="input_status_{{ $order->id }}" type="hidden" name="status" value="{{ $order->status }}">
                <input id="input_price_{{ $order->id }}" type="hidden" name="price" value="{{ $order->price }}">
                <input id="input_comment_{{ $order->id }}" type="hidden" name="comment" value="{{ $order->comment }}">
                <button type="submit">Save</button>
                <button type="button" onclick="toggleForm({{ $order->id }});">&times;</button>
            </form>
        </div>
    </td>
</tr>

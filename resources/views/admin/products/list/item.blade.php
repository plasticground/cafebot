<tr id="product_tr_{{ $product->id }}">
    <td>
        <div class="__input">
            <b>{{ $product->sorting_position }}</b>
        </div>
        <div class="__input" style="display: none">
            <input type="number"
                   value="{{ $product->sorting_position }}"
                   oninput="document.getElementById('input_sorting_position_{{ $product->id }}').value = this.value;"
                   style="width: 4em;"
            >
        </div>
    </td>
    <td>{{ $product->id }}</td>
    <td>
        <div class="__input">
            {{ $product->ru_name }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $product->ru_name }}"
                   oninput="document.getElementById('input_ru_name_{{ $product->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $product->ua_name }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $product->ua_name }}"
                   oninput="document.getElementById('input_ua_name_{{ $product->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $product->price }}
        </div>
        <div class="__input" style="display: none">
            <input type="number"
                   step="0.01"
                   value="{{ $product->price }}"
                   oninput="document.getElementById('input_price_{{ $product->id }}').value = this.value;"
            >
        </div>
    </td>
    <td style="text-align: right">
        <div class="__input">
            <a href="#" onclick="toggleForm({{ $product->id }});">Edit</a>
            <a href="#" onclick="confirm('Delete {{ $product->id . '#: ' . $product->ru_name ?? $product->ua_name }} ?') ? document.getElementById('deleteProductForm_{{ $product->id }}').submit() : false">Delete</a>
            <form id="deleteProductForm_{{ $product->id }}" action="{{ route('admin.products.destroy', ['id' => $product->id]) }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
            </form>
        </div>
        <div class="__input" style="display: none">
            <form id="edit_form_{{ $product->id }}" action="{{ route('admin.products.update', ['id' => $product->id]) }}" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input id="input_sorting_position_{{ $product->id }}" type="hidden" name="sorting_position" value="{{ $product->sorting_position }}">
                <input id="input_ru_name_{{ $product->id }}" type="hidden" name="ru_name" value="{{ $product->ru_name }}">
                <input id="input_ua_name_{{ $product->id }}" type="hidden" name="ua_name" value="{{ $product->ua_name }}">
                <input id="input_price_{{ $product->id }}" type="hidden" name="price" value="{{ $product->price }}">
                <button type="submit">Save</button>
                <button type="button" onclick="toggleForm({{ $product->id }});">&times;</button>
            </form>
        </div>
    </td>
</tr>

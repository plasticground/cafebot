<tr id="category_tr_{{ $category->id }}">
    <td>
        <div class="__input">
            <b>{{ $category->sorting_position }}</b>
        </div>
        <div class="__input" style="display: none">
            <input type="number"
                   value="{{ $category->sorting_position }}"
                   oninput="document.getElementById('input_sorting_position_{{ $category->id }}').value = this.value;"
                   style="width: 4em;"
            >
        </div>
    </td>
    <td>{{ $category->id }}</td>
    <td>
        <div class="__input">
            {{ $category->name }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $category->name }}"
                   oninput="document.getElementById('input_name_{{ $category->id }}').value = this.value;"
            >
        </div>
    </td>
    <td style="text-align: right">
        <div class="__input">
            <a href="#" onclick="toggleForm({{ $category->id }});">Edit</a>
            <a href="#" onclick="confirm('Delete {{ $category->id . '#: ' . $category->name }} ?') ? document.getElementById('deleteCategoryForm_{{ $category->id }}').submit() : false">Delete</a>
            <form id="deleteCategoryForm_{{ $category->id }}" action="{{ route('admin.productCategories.destroy', ['id' => $category->id]) }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
            </form>
        </div>
        <div class="__input" style="display: none">
            <form id="edit_form_{{ $category->id }}" action="{{ route('admin.productCategories.update', ['id' => $category->id]) }}" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input id="input_sorting_position_{{ $category->id }}" type="hidden" name="sorting_position" value="{{ $category->sorting_position }}">
                <input id="input_name_{{ $category->id }}" type="hidden" name="name" value="{{ $category->name }}">
                <button type="submit">Save</button>
                <button type="button" onclick="toggleForm({{ $category->id }});">&times;</button>
            </form>
        </div>
    </td>
</tr>

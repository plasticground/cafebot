<tr id="location_tr_{{ $location->id }}">
    <td>{{ $location->id }}</td>
    <td>
        <div class="__input">
            {{ $location->ru_name }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $location->ru_name }}"
                   oninput="document.getElementById('input_ru_name_{{ $location->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $location->ua_name }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $location->ua_name }}"
                   oninput="document.getElementById('input_ua_name_{{ $location->id }}').value = this.value;"
            >
        </div>
    </td>
    <td style="text-align: right">
        <div class="__input">
            <a href="#" onclick="toggleForm({{ $location->id }});">Edit</a>
            <a href="#" onclick="confirm('Delete {{ $location->id . '#: ' . $location->ru_name }} ?') ? document.getElementById('deleteLocationForm_{{ $location->id }}').submit() : false">Delete</a>
            <form id="deleteLocationForm_{{ $location->id }}" action="{{ route('admin.locations.destroy', ['id' => $location->id]) }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
            </form>
        </div>
        <div class="__input" style="display: none">
            <form id="edit_form_{{ $location->id }}" action="{{ route('admin.locations.update', ['id' => $location->id]) }}" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input id="input_ru_name_{{ $location->id }}" type="hidden" name="ru_name" value="{{ $location->ru_name }}">
                <input id="input_ua_name_{{ $location->id }}" type="hidden" name="ua_name" value="{{ $location->ua_name }}">
                <button type="submit">Save</button>
                <button type="button" onclick="toggleForm({{ $location->id }});">&times;</button>
            </form>
        </div>
    </td>
</tr>

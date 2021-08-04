<tr id="client_tr_{{ $client->id }}">
    <td>{{ $client->id }}</td>
    <td>
        <div class="__input">
            {{ $client->name }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $client->name }}"
                   oninput="document.getElementById('input_name_{{ $client->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $client->phone }}
        </div>
        <div class="__input" style="display: none">
            <input type="number"
                   value="{{ $client->phone }}"
                   oninput="document.getElementById('input_phone_{{ $client->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $client->locale }}
        </div>
        <div class="__input" style="display: none">
            <select name="locale"
                    id="locale"
                    onchange="document.getElementById('input_locale_{{ $client->id }}').value = this.options[this.selectedIndex].value;"
            >
                <option value="ru" {{ $client->locale === 'ru' ? 'selected' : '' }}>RU</option>
                <option value="ua" {{ $client->locale === 'ua' ? 'selected' : '' }}>UA</option>
            </select>
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $client->telegram_id }}
        </div>
        <div class="__input" style="display: none">
            <input type="number"
                   value="{{ $client->telegram_id }}"
                   oninput="document.getElementById('input_telegram_id_{{ $client->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>
        <div class="__input">
            {{ $client->telegram_username }}
        </div>
        <div class="__input" style="display: none">
            <input type="text"
                   value="{{ $client->telegram_username }}"
                   oninput="document.getElementById('input_telegram_username_{{ $client->id }}').value = this.value;"
            >
        </div>
    </td>
    <td>{{ $client->created_at }}</td>
    <td>{{ $client->updated_at }}</td>
    <td style="text-align: right">
        <div class="__input">
            <a href="#" onclick="toggleForm({{ $client->id }});">Edit</a>
            <a href="#" onclick="confirm('Delete {{ $client->id . '#: ' . $client->name }} ?') ? document.getElementById('deleteClientForm_{{ $client->id }}').submit() : false">Delete</a>
            <form id="deleteClientForm_{{ $client->id }}" action="{{ route('admin.clients.destroy', ['id' => $client->id]) }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
            </form>
        </div>
        <div class="__input" style="display: none">
            <form id="edit_form_{{ $client->id }}" action="{{ route('admin.clients.update', ['id' => $client->id]) }}" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input id="input_name_{{ $client->id }}" type="hidden" name="name" value="{{ $client->name }}">
                <input id="input_phone_{{ $client->id }}" type="hidden" name="phone" value="{{ $client->phone }}">
                <input id="input_locale_{{ $client->id }}" type="hidden" name="locale" value="{{ $client->locale }}">
                <input id="input_telegram_id_{{ $client->id }}" type="hidden" name="telegram_id" value="{{ $client->telegram_id }}">
                <input id="input_telegram_username_{{ $client->id }}" type="hidden" name="telegram_username" value="{{ $client->telegram_username }}">
                <button type="submit">Save</button>
                <button type="button" onclick="toggleForm({{ $client->id }});">&times;</button>
            </form>
        </div>
    </td>
</tr>

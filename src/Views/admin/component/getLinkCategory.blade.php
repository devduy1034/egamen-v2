<select id="" name ="" onchange="onchangeCategory($(this))" class="form-control filter-category select2">
    <option value="0">{{ $title }}</option>
    @foreach ($category as $v)
        <option value="{{ $v['id'] }}" $selected>{{ $v['namevi'] }}</option>
    @endforeach
</select>

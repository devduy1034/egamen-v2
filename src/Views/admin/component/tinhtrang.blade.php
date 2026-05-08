<div class="card-header">
    <h3 class="card-title">Tình trạng </h3>
</div>
<div class="card-body">
    <div class="form-group last:!mb-0">
        @php $status_array = !empty($item['status']) ? explode(',', $item['status']) : []; @endphp
        @if (!empty($status))
            @foreach ($status as $key => $value)
                <div class="form-group d-inline-block mb-2 me-5">
                    <label for="{{ $key }}-checkbox" class="d-inline-block align-middle mb-0 mr-2 form-label"><?= $value ?>:</label>
                    <label class="switch switch-success">
                        <input type="checkbox" name="status[{{ $key }}]" class="switch-input custom-control-input show-checkbox" id="{{ $key }}-checkbox" {{ (empty($status_array) && empty($item['id']) ? 'checked' : in_array($key, $status_array)) ? 'checked' : '' }}>
                        <span class="switch-toggle-slider">
                            <span class="switch-on">
                                <i class="ti ti-check"></i>
                            </span>
                            <span class="switch-off">
                                <i class="ti ti-x"></i>
                            </span>
                        </span>
                    </label>
                </div>
            @endforeach
        @endif
    </div>
    @if(!empty($stt))
    <div class="form-group last:!mb-0">
        <label for="numb" class="d-inline-block align-middle mb-0 mr-2 form-label">Số thứ tự:</label>
        <input type="number" class="form-control form-control-mini w-25 text-left d-inline-block align-middle text-sm" min="0" name="data[numb]" id="numb" placeholder="Số thứ tự" value="{{ isset($item['numb']) ? $item['numb'] : 1 }}">
    </div>
    @endif
    {!! $slot !!}
</div>
<input type="checkbox"
    class="switch-input custom-control-input show-checkbox {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.edit') ? 'pointer-events-none' : '' }}"
    {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.edit') ? 'disabled' : '' }}
    id="show-checkbox-{{ $keyC }}-{{ $idC }}" data-table="{{ $tableC }}"
    data-id="{{ $idC }}" data-attr="{{ $keyC }}" data-url="{{ url('status') }}"
    {{ in_array($keyC, $status_arrayC) ? 'checked' : '' }}>
<span class="switch-toggle-slider">
    <span class="switch-on"><i class="ti ti-check"></i></span>
    <span class="switch-off"><i class="ti ti-x"></i></span>
</span>

@php
    $kind = !empty($kind) ? '.' . $kind : '';
@endphp

<div class="card pd-15 bg-main mb-3 navbar-detached">
    <div class="d-flex gap-2">
        @if (Func::chekcPermission($tb . $kind . '.' . $type . '.add', $permissions))
            @if ($com != 'order')
                <a class="btn btn-secondary text-white {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.add') ? 'disabled' : '' }}"
                    href="{{ url('admin', ['com' => $com, 'act' => 'add', 'type' => $type], $params ?? []) }}"
                    title="Thêm mới"><i class="ti ti-plus mr-2"></i>Thêm mới</a>
            @endif
        @endif
        @if (Func::chekcPermission($tb . $kind . '.' . $type . '.delete', $permissions))
            <a class="btn btn-primary  text-white {{ !isPermissions(str_replace('-', '.', $com) . '.' . $type . '.delete') ? 'disabled' : '' }}"
                id="delete-all"
                data-url="{{ url('admin', ['com' => $com, 'act' => 'delete', 'type' => $type], $params ?? []) }}"
                title="Xóa tất cả"><i class="ti ti-trash mr-2"></i>Xóa tất cả</a>
        @endif

        @if (Func::chekcPermission($tb . '.export.' . $type . '.man', $permissions))
            @if (!empty($configMan->excel->export) && $com == 'product')
                <a class="btn btn-info text-white" href="product-export/man/{{ $type }}"
                    title="{{ $configMan->excel->excel->title_main_excel }}"><i class="ti ti-file-export"></i>
                    {{ $configMan->excel->export->title_main_excel }}</a>
            @endif
        @endif

        @if ($com == 'newsletters')
            <a class="btn btn-info text-white" id="send-email" title="Gửi email"><i class="ti ti-send mr-2"></i>Gửi
                email</a>
        @endif
    </div>
</div>

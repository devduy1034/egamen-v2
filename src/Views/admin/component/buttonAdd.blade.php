<div
    class="btn-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 bg-navbar-theme navbar-detached">
    <div class="d-flex align-content-center flex-wrap gap-2">
        <div class="d-flex gap-2">
            <button type="submit" name="button-out" value="out" class="btn btn-success submit-check waves-effect"><i
                    class="ti ti-device-floppy mr-2"></i>
                Lưu</button>
            @if ($tb == 'product' || $tb == 'news')
                <button type="submit" name="button-in" value="in"
                    class="btn btn-secondary submit-check waves-effect"><i class="ti ti-device-floppy mr-2"></i>
                    Lưu tại trang</button>
            @endif
            <button type="reset" class="btn btn-primary waves-effect"><i class="ti ti-repeat mr-2"></i> Làm
                lại</button>
            <a class="btn btn-danger color-white waves-effect"
                href="{{ url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], $params ?? []) }}"
                title="Thoát"><i class="ti ti-logout mr-2"></i>Thoát</a>
        </div>
    </div>
</div>

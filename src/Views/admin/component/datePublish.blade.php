<div class="form-group row mb-0 align-items-center">
    <label for="flatpickr-datetime" class="col-md-12 form-label mb-3">Ngày hiển thị:</label>
    <div class="col-md-8 flex-1">
        <input type="text" class="form-control" name="data[date_publish]"  placeholder="DD/MM/YYYY HH:MM" id="flatpickr-datetime" />
    </div>
    <a href="{{ url('slugweb', ['slug' => $item['slugvi']??''],['preview'=>1]) }}" target="_blank" class="btn btn-primary text-white col-md-4 form-label mb-md-0">Xem tr??c</a>
</div>
@pushonce('styles')
<link rel="stylesheet" href="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.css')" />
@endpushonce
@pushonce('scripts')
<script src="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.js')"></script>
    <script>
        const flatpickrDateTime = document.querySelector('#flatpickr-datetime');
        if (flatpickrDateTime) {
            flatpickrDateTime.flatpickr({
                enableTime: true,
                lang:'vi',
                @if(!empty($item['date_publish']))
                defaultDate:'{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item['date_publish'])->format('d/m/Y H:i')}}',
                @endif
                dateFormat: 'd/m/Y H:i',
                minDate: new Date()
            });
        }
    </script>
@endpushonce
@if (device() == 'mobile')
    @php
        $bottom = $opt['mobile']['bottom'] ?? '';
        $left = $opt['mobile']['left'] ?? '';
        $right = $opt['mobile']['right'] ?? '';
    @endphp
@else
    @php
        $bottom = $opt['destop']['bottom'] ?? '';
        $left = $opt['destop']['left'] ?? '';
        $right = $opt['destop']['right'] ?? '';
    @endphp
@endif

@php
    $background = $opt['background'] ?? '';
    $backgroundText = $opt['background-text'] ?? '';
    $color = $opt['color'] ?? '';
    $location = !empty($left) ? 'left' : 'right';
    $hotline = explode('-', $val['hotlinevi']);
    $destop = !empty($opt['destop']['device']) && device() == 'destop' ? true : false;
    $mobile = !empty($opt['mobile']['device']) && device() == 'mobile' ? true : false;
@endphp

@if (!empty($destop) || !empty($mobile))
    <div class="contact-group" id="hotline">
        <div class="icon active" style=" --background: #{{ $background }};">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="button-action-group  active">
            @foreach ($hotline as $hotline)
                <a class="text-decoration-none" href="tel:{{ $hotline }}"><i
                        class="fa fa-phone"></i>{{ preg_replace('/[^0-9]/', '', $hotline) }}</a>
            @endforeach
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="@asset('assets/css/hotline.css')" />
    @endpush

    @push('scripts')
        <script type="text/javascript">
            $('#hotline.contact-group').css({
                'bottom': '{{ $bottom . 'px' }}',
                '{{ $location }}': '{{ !empty($left) ? $left . 'px' : $right . 'px' }}',
            })
            $('.button-action-group a').css({
                'background': '#{{ $backgroundText }}',
                'color': '#{{ $color }}'
            })
            $('.button-action-group a i').css({
                'background': '#{{ $background }}',
            })
            $('#hotline').show(500);
            $('.contact-group').find('.icon').on('click', function() {
                $(this).toggleClass('active');
                $(this).next('.button-action-group').toggleClass('active');
            })
        </script>
    @endpush
@endif

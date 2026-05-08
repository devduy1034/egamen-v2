@if (!empty($extHotline))
    @php $optHotline = json_decode($extHotline['options'], true); @endphp
    @component('component.hotline.' . $optHotline['hotline'], ['val' => $extHotline, 'opt' => $optHotline])
    @endcomponent
@endif

@if (!empty($extSocial) && !empty($social))

    @php $optSocial = json_decode($extSocial['options'], true); @endphp

    @foreach ($optSocial as $k => $value)
        @if (!empty($value['status']))
            @component('component.social.' . $k, ['val' => $value, 'mxh' => $social])
            @endcomponent
        @endif
    @endforeach
@endif

@if (!empty($extPopup))
    @php
        $optPopup = json_decode($extPopup['options'], true);
        $template = $optPopup['type'] == 1 ? 'popup' : $optPopup['popup'];
    @endphp
    @component('component.popup.' . $template, ['val' => $extPopup, 'opt' => $optPopup])
    @endcomponent
@endif
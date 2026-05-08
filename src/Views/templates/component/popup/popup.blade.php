@if (!empty($val) && empty(session()->get('popup')))
    <div class="modal fade show" id="popup" tabindex="-1" aria-labelledby="popupLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title" id="popupLabel">{{ $val['namevi'] }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if (!empty($val['content'.$lang]))
                        <div class="mb-3">
                            {!! $val['content'.$lang] !!}
                        </div>
                    @endif
                    <a href="{{ $val['link'] }}">
                      @component('component.image', [
                            'class' => 'w-100',
                            'w' => 465,
                            'h' => 385,
                            'z' => 1,
                            'breakpoints' => [
                               
                            ],
                            'is_watermarks' => false,
                            'destination' => 'photo',
                            'image' => $val['photo'] ?? '',
                            'alt' => $val['name'.$lang] ?? '',
                        ])
                        @endcomponent
                       
                    </a>
                </div>
         
            </div>
        </div>
    </div>
@endif
@if (!empty(strpos($val['status'], 'repeat')))
    @php session()->unset('popup'); @endphp
@else
    @php session()->set('popup', true); @endphp
@endif

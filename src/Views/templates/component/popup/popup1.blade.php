@if (!empty($val) && empty(session()->get('popup')))
   
    @php
        $background = $opt['background'] ?? '';
        $colorTitle = $opt['color-title'] ?? '';
        $colorContent = $opt['color-content'] ?? '';
        $colorSend = $opt['color-send'] ?? '';
        $backgroundSend = $opt['background-send'] ?? '';
        $popupThumb = config('type.extensions.popup.thumb') ?? '800x500x1';
        $popupSrc = !empty($val['photo'])
            ? assets_photo('photo', $popupThumb, $val['photo'], 'thumbs')
            : assets('assets/images/noimage.png');
    @endphp

    <div class="modal fade show popup1" id="popup" tabindex="-1" aria-labelledby="popupLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <a href="{{ $val['link'] }}">
                        <img alt="{{ $val['namevi'] }}" class="w-100" src="{{ $popupSrc }}" decoding="async">
                    </a>
                    <div class="form-popup"
                        style="--background: #{{ $background }};--color: #{{ $colorTitle }};--color1: #{{ $colorContent }};">
                        <form class="popup-form validation-popup" novalidate method="post" action="{{ url('popup') }}"
                            enctype="multipart/form-data">

                            @if (!empty($val['namevi']))
                                <div class="title-popup mb-2">
                                    {!! $val['namevi'] !!}
                                </div>
                            @endif

                            @if (!empty($val['contentvi']))
                                <div class="content-popup mb-3">
                                    {!! Func::decodeHtmlChars($val['contentvi']) !!}
                                </div>
                            @endif

                            <div class="row-20 row">

                                @if (!empty($opt['fullname']))
                                    <div class="contact-input col-sm-12 col-20 mb-2">
                                        <div class=" ">
                                            <input type="text" name="dataContact[fullname]"
                                                class="form-control text-sm" placeholder="Ho va ten" value=""
                                                required>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($opt['hotline']))
                                    <div class="contact-input col-sm-12 col-20 mb-2">
                                        <div class=" ">
                                            <input type="number" name="dataContact[phone]" class="form-control text-sm"
                                                placeholder="Điện thoại" value="" required>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($opt['address']))
                                    <div class="contact-input col-sm-12 col-20 mb-2">
                                        <div class=" ">
                                            <input type="text" class="form-control text-sm"
                                                name="dataContact[address]" placeholder="Địa chỉ" value="" />
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($opt['email']))
                                    <div class="contact-input col-sm-12 col-20 mb-2">
                                        <div class=" ">
                                            <input type="email" class="form-control text-sm" name="email"
                                                placeholder="Email" value="" />
                                        </div>
                                    </div>
                                @endif

                            </div>

                            @if (!empty($opt['content']))
                                <div class="contact-input mb-2">
                                    <div class=" ">
                                        <textarea class="form-control text-sm" name="dataContact[content]" placeholder="Noi dung" /></textarea>
                                    </div>
                                </div>
                            @endif

                            <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                            <input style="--background: #{{ $backgroundSend }};--color: #{{ $colorSend }};"
                                type="submit" class="btn btn-popup w-100" name="submit-popup" value="Gui" />
                            <input type="hidden" name="recaptcha_response_popup" id="recaptchaResponsePopup">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="assets/css/popup.css" rel="stylesheet">
    @endpush

    @push('scripts')
    @endpush

@endif

@if (!empty(strpos($val['status'], 'repeat')))
    @php session()->unset('popup'); @endphp
@else
    @php session()->set('popup', true); @endphp
@endif

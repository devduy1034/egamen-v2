@extends('layout')
@section('content')
    <section>
        <div class="max-width py-3">
            <div class="title-detail ">
                <h1 class="text-center">{{ $titleMain }}</h1>
            </div>
            <div class="content-main">
                <div class="contact-article row">
                    <div class="col-12">
                        @if(!empty($contact['content'.$lang]))
                            <div class="contact-text">{!! Func::decodeHtmlChars($contact['content'.$lang] ?? '') !!}</div>
                        @endif
                    </div>
                    <div class="box-frm col-12">
                        <form class="contact-form validation-contact" id="contact-form" novalidate method="post"
                            action="{{ url('lien-he-post') }}" enctype="multipart/form-data">
                            <div class="row-20 row">
                                <div class="contact-input col-sm-6 col-20 mb-3">
                                    <div class="form-floating form-floating-cus">
                                        <input type="text" name="dataContact[fullname]"
                                            class="form-control text-sm reset-token" id="fullname-contact"
                                            placeholder="{{ __('web.hovaten') }}" value="" required>
                                        <label for="fullname-contact">{{ __('web.hovaten') }}</label>
                                    </div>
                                    <div class="invalid-feedback">{{ __('web.vuilongnhaphoten') }}</div>
                                </div>
                                <div class="contact-input col-sm-6 col-20 mb-3">
                                    <div class="form-floating form-floating-cus">
                                        <input type="number" name="dataContact[phone]" class="form-control text-sm"
                                            id="phone-contact" placeholder="{{ __('web.dienthoai') }}" value=""
                                            required>
                                        <label for="phone-contact">{{ __('web.dienthoai') }}</label>
                                    </div>
                                    <div class="invalid-feedback">{{ __('web.nhapdienthoai') }}</div>
                                </div>
                                <div class="contact-input col-sm-6 col-20 mb-3">
                                    <div class="form-floating form-floating-cus">
                                        <input type="text" class="form-control text-sm" id="address-contact"
                                            name="dataContact[address]" placeholder="{{ __('web.diachi') }}" value=""
                                            required>
                                        <label for="address-contact">{{ __('web.diachi') }}</label>
                                    </div>
                                    <div class="invalid-feedback">{{ __('web.nhapdiachi') }}</div>
                                </div>
                                <div class="contact-input col-sm-6 col-20 mb-3">
                                    <div class="form-floating form-floating-cus">
                                        <input type="email" class="form-control text-sm" id="email-contact"
                                            name="dataContact[email]" placeholder="Email" value="" required>
                                        <label for="email-contact">Email</label>
                                    </div>
                                    <div class="invalid-feedback">{{ __('web.nhapemail') }}</div>
                                </div>
                            </div>
                            <div class="contact-input mb-3">
                                <div class="form-floating form-floating-cus">
                                    <input type="text" class="form-control text-sm" id="subject-contact"
                                        name="dataContact[subject]" placeholder="{{ __('web.subject') }}" value=""
                                        required>
                                    <label for="subject-contact">{{ __('web.subject') }}</label>
                                </div>
                                <div class="invalid-feedback">{{ __('web.vuilongnhapchude') }}</div>
                            </div>
                            {{-- <div class="contact-input mb-3">
                            <input type="file" class="form-control text-sm" name="file" />
                        </div> --}}
                            <div class="contact-input mb-3">
                                <div class="form-floating form-floating-cus">
                                    <textarea class="form-control text-sm" id="content-contact" name="dataContact[content]"
                                        placeholder="{{ __('web.noidung') }}" required></textarea>
                                    <label for="content-contact">{{ __('web.noidung') }}</label>
                                </div>
                                <div class="invalid-feedback">{{ __('web.vuilongnhapnoidung') }}</div>
                            </div>
                            <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                            <input type="submit" class="btn btn-send btn-primary mr-2" name="submit-contact"
                                value="{{ __('web.guithongtin') }}" />
                            {{-- <input type="reset" class="btn btn-secondary" value="{{ __('web.nhaplai') }}" /> --}}
                            <input type="hidden" name="recaptcha_response_contact" id="recaptchaResponseContact">
                        </form>
                    </div>
                </div>

                <div class="contact-map py-3">{!! Func::decodeHtmlChars($optSetting['coords_iframe'] ?? '') !!}</div>
            </div>
        </div>
    </section>
@endsection

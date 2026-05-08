@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <div class="app-ecommerce">
        <form class="validation-form" novalidate method="post" action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['id' => $item['id'] ?? 0,'page' => $page]) }}" enctype="multipart/form-data">

            @component('component.buttonAdd')
            @endcomponent

            {!! Flash::getMessages('admin') !!}
            <div class="row">
                <div class="col-12 col-lg-12">

                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                            </h3>
                        </div>
                        <div class="card-body card-article">

                            @if (!empty($configMan->file))
                            <div class="form-group">
                                <div class="upload-file mb-2">
                                    @if (!empty($item['file_attach']))
                                    <div class="file-uploaded mb-2">
                                        <a class="btn btn-sm bg-gradient-primary text-white d-inline-block align-middle rounded p-2" href="{{upload('file', $item['file_attach']) }}" title="Download tệp tin"><i class="fas fa-download mr-2"></i>Download
                                            tệp tin</a>
                                    </div>
                                    @endif
                                    <strong class="d-block text-sm">{{ config('type.type_file') }}</strong>
                                </div>
                            </div>
                            @endif
                            <div class="row">
                                @if (!empty($configMan->fullname))
                                <div class="form-group col-4">
                                    <label class="form-label" for="fullname">Họ Tên:</label>
                                    <input type="text" class="form-control  text-sm" name="data[fullname]" id="name" placeholder="Họ Tên" value="{{ !empty(Flash::has('fullname')) ? Flash::get('fullname') : $item['fullname'] }}">
                                </div>
                                @endif

                                @if (!empty($configMan->email))
                                <div class="form-group col-4">
                                    <label class="form-label" for="email">Email:</label>
                                    <input type="text" class="form-control  text-sm" name="data[email]" id="name" placeholder="Email" value="{{ !empty(Flash::has('email')) ? Flash::get('email') : $item['email'] }}">
                                </div>
                                @endif

                                @if (!empty($configMan->phone))
                                <div class="form-group col-4">
                                    <label class="form-label" for="phone">Điện thoại:</label>
                                    <input type="text" class="form-control  text-sm" name="data[phone]" id="name" placeholder="Điện thoại" value="{{ !empty(Flash::has('phone')) ? Flash::get('phone') : $item['phone'] }}">
                                </div>
                                @endif

                                @if (!empty($configMan->address))
                                <div class="form-group col-4">
                                    <label class="form-label" for="address">Địa chỉ:</label>
                                    <input type="text" class="form-control  text-sm" name="data[address]" id="name" placeholder="Địa chỉ" value="{{ !empty(Flash::has('address')) ? Flash::get('address') : $item['address'] }}">
                                </div>
                                @endif

                                @if (!empty($configMan->subject))
                                <div class="form-group col-4">
                                    <label class="form-label" for="subject">Ch? d?:</label>
                                    <input type="text" class="form-control  text-sm" name="data[subject]" id="name" placeholder="Ch? d?" value="{{ !empty(Flash::has('subject')) ? Flash::get('subject') : $item['subject'] }}">
                                </div>
                                @endif

                                <div class="form-group col-4">
                                    <label class="form-label" for="subject">Tình trạng:</label>
                                    {{ $flashConfirmStatus = Flash::get('confirm_status') }}
                                    <select id="confirm_status" name="data[confirm_status]" class="form-control select2">
                                        @foreach (config('type.contact.' . $type . '.confirm_status') as $key => $value)
                                        <option {{ @$item['confirm_status'] == $key ? 'selected' : '' }} value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if (!empty($configMan->content))
                                <div class="form-group">
                                    <label class="form-label" for="content">Nội dung:</label>
                                    <textarea class="form-control  text-sm " name="data[content]" id="content" rows="5" placeholder="Nội dung">{{ !empty(Flash::has('content')) ? Flash::get('content') : @$item['content'] }}</textarea>
                                </div>
                                @endif

                                @if (!empty($configMan->notes))
                                <div class="form-group">
                                    <label class="form-label" for="notes">Ghi ch?:</label>
                                    <textarea class="form-control  text-sm " name="data[notes]" id="notes" rows="5" placeholder="Ghi ch?">{{ !empty(Flash::has('notes')) ? Flash::get('notes') : @$item['notes'] }}</textarea>
                                </div>
                                @endif

                                <div class="form-group">
                                    <label for="numb" class="d-inline-block align-middle mb-0 mr-2">S? th?
                                        t?:</label>
                                    <input type="number" class="form-control form-control-mini w-25 text-left d-inline-block align-middle text-sm" min="0" name="data[numb]" id="numb" placeholder="Số thứ tự" value="{{ !empty($item['numb']) ? $item['numb'] : 1 }}">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="id" value="{{!empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' }}">
            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">

            @component('component.buttonAdd')
            @endcomponent

        </form>
    </div>
</div>
@endsection
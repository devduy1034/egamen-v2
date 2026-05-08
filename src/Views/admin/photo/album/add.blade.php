@extends('layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y container-fluid">
    <div class="app-ecommerce">
        <form class="validation-form" novalidate method="post"
            action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['page'=>$page]) }}" enctype="multipart/form-data">
            @component('component.buttonAdd')
            @endcomponent
            {!! Flash::getMessages('admin') !!}
            @for ($i = 0; $i < $configMan->number ?? 5; $i++)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                        </h3>
                    </div>
                    <div class="card-body card-article">
                        <div class="card">
                            <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
                                @foreach (config('app.langs') as $k => $v)
                                <li class="nav-item">
                                    <a class="nav-link {{ $k ==  config('app.lang_default') ? 'active' : '' }}"
                                        id="tabs-lang-{{ $i }}" data-bs-toggle="tab"
                                        data-bs-target="#tabs-lang-{{ $i }}-{{ $k }}"
                                        role="tab" aria-controls="tabs-lang-{{ $k }}"
                                        aria-selected="true">{{ $v }}</a>
                                </li>
                                @endforeach
                            </ul>
                            <div class="tab-content py-2" id="custom-tabs-three-tabContent-lang">
                                @foreach (config('app.langs') as $k => $v)
                                <div class="tab-pane fade show {{ $k ==  config('app.lang_default') ? 'active' : '' }}"
                                    id="tabs-lang-{{ $i }}-{{ $k }}"
                                    role="tabpanel" aria-labelledby="tabs-lang">
                                    <div class="form-group last:!mb-0">
                                        <label class="form-label"
                                            for="name{{ $i }}{{ $k }}">Tiêu đề
                                            ({{ $k }})
                                            :</label>
                                        <input type="text" class="form-control for-seo text-sm"
                                            name="dataMultiTemp[{{ $i }}][name{{ $k }}]"
                                            id="name{{ $i }}{{ $k }}"
                                            placeholder="Tiêu đề ({{ $k }})"
                                            value="{{ !empty(Flash::has('namevi')) ? Flash::get('namevi') : $item['name' . $k] }}">
                                    </div>

                                    @if (!empty($configMan->desc))
                                    <div class="form-group last:!mb-0">
                                        <label class="form-label"
                                            for="desc{{ $k }}">Mô
                                            tả
                                            ({{ $k }})
                                            :</label>
                                        <textarea class="form-control for-seo text-sm {{ !empty($configMan->desc_cke) ? 'form-control-ckeditor' : '' }}"
                                            name="dataMultiTemp[{{ $i }}][desc{{ $k }}]" id="desc{{ $k }}" rows="5"
                                            placeholder="Mô tả ({{ $k }})">{{ !empty(Flash::has('desc' . $k)) ? Flash::get('desc' . $k) : @$item['desc' . $k] }}</textarea>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <div class="tab-content py-2">
                                @if (!empty($configMan->link))
                                <div class="form-group last:!mb-0">
                                    <label class="form-label" for="link">Link:</label>
                                    <input type="text" class="form-control  text-sm"
                                        name="dataMultiTemp[{{ $i }}][link]"
                                        id="name" placeholder="Link"
                                        value="{{ !empty(Flash::has('link')) ? Flash::get('link') : $item['link'] }}">
                                </div>
                                @endif

                                @if (!empty($configMan->link_video))
                                <div class="form-group last:!mb-0 mt-3">
                                    <label class="form-label" for="link_video">Link video:</label>
                                    <input type="text" class="form-control  text-sm"
                                        name="dataMultiTemp[{{ $i }}][link_video]"
                                        id="name" placeholder="Link video"
                                        value="{{ !empty(Flash::has('link_video')) ? Flash::get('link_video') : $item['link_video'] }}">
                                </div>
                                @endif


                                @if (!empty($configMan->images))

                                <div class="card-flex">
                                    @php
                                    /* Photo detail */
                                    $photoDetail = [];
                                    $photoAction = 'photo';
                                    $photoDetail['upload'] = 'photo';
                                    $photoDetail['image'] = !empty($item) ? $item . $i : '';
                                    $photoDetail['id'] = !empty($item) ? $item['id'] : '';
                                    $photoDetail['dimension'] =
                                    'Width: ' .
                                    $configMan->width .
                                    ' px - Height: ' .
                                    $configMan->height .
                                    ' px (' .
                                    config('type.type_img') .
                                    ')';
                                    @endphp
                                    @component('component.image', ['photoDetail' => $photoDetail, 'photoAction' => 'photo', 'key' => $i])
                                    @endcomponent
                                </div>
                                @endif

                                <div class="form-group last:!mb-0 mt-3">
                                    @php $status_array = !empty($item['status']) ? explode(',', $item['status']) : []; @endphp
                                    @if (!empty($configMan->status))
                                    @foreach ($configMan->status as $key => $value)
                                    <div class="form-group d-inline-block mb-2 me-5">
                                        <label for="{{ $key }}-checkbox" class="d-inline-block align-middle mb-0 mr-2 form-label"><?= $value ?>:</label>
                                        <label class="switch switch-success">
                                            <input type="checkbox" name="dataMultiTemp[{{ $i }}][status][{{ $key }}]" class="switch-input custom-control-input show-checkbox" id="{{ $key }}-checkbox" {{ (empty($status_array) && empty($item['id']) ? 'checked' : in_array($key, $status_array)) ? 'checked' : '' }}>
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on">
                                                    <i class="ti ti-check"></i>
                                                </span>
                                                <span class="switch-off">
                                                    <i class="ti ti-x"></i>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>

                                <div class="form-group last:!mb-0">
                                    <label for="numb" class="d-inline-block align-middle mb-0 mr-2 form-label">Số thứ tự:</label>
                                    <input type="number" class="form-control form-control-mini w-25 text-left d-inline-block align-middle text-sm" min="0" name="dataMultiTemp[{{ $i }}][numb]" id="numb" placeholder="Số thứ tự" value="{{ !empty($item['numb']) ? $item['numb'] : 1 }}">
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                @endfor
                <input type="hidden" name="id"
                    value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
                <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                @component('component.buttonAdd')
                @endcomponent
        </form>
    </div>
</div>
@endsection

@pushonce('scripts')
    <script src="@asset('assets/admin/ckeditor/ckeditor.js')"></script>
    <script src="@asset('assets/admin/ckeditor/config.js')"></script>
    <script>
        if ($('.form-control-ckeditor').length) {
            $('.form-control-ckeditor').each(function() {
                var id = $(this).attr('id');
                CKEDITOR.replace(id);
            });
        }
    </script>
@endpushonce

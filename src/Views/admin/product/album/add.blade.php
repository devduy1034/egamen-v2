@extends('layout')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce">
            <form class="validation-form" novalidate method="post"
                action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['page' => $page]) }}"
                enctype="multipart/form-data">

                @component('component.buttonAdd', ['params' => ['gallery' => $gallery, 'id_parent' => $id_parent]])
                @endcomponent
                {!! Flash::getMessages('admin') !!}
                <div class="row">
                    <div class="col-12 col-lg-12">

                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                                </h3>
                            </div>

                            @for ($i = 0; $i < 5; $i++)
                                <div class="card-body card-article">
                                    <div class="card">

                                        <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang-{{ $i }}"
                                            role="tablist">
                                            @foreach (config('app.langs') as $k => $v)
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $k == 'vi' ? 'active' : '' }}" id="tabs-lang"
                                                        data-bs-toggle="tab"
                                                        data-bs-target="#tabs-lang-{{ $i }}-{{ $k }}"
                                                        role="tab" aria-controls="tabs-lang-{{ $k }}"
                                                        aria-selected="true">{{ $v }}</a>
                                                </li>
                                            @endforeach
                                        </ul>

                                        <div class="tab-content" id="custom-tabs-three-tabContent-lang-{{ $i }}">
                                            @foreach (config('app.langs') as $k => $v)
                                                <div class="tab-pane fade show {{ $k == 'vi' ? 'active' : '' }}"
                                                    id="tabs-lang-{{ $i }}-{{ $k }}" role="tabpanel"
                                                    aria-labelledby="tabs-lang">
                                                    <div class="form-group last:!mb-0">
                                                        <label class="form-label" for="name{{ $k }}{{ $i }}">Tiêu
                                                            đề ({{ $k }}):</label>
                                                        <input type="text" class="form-control for-seo text-sm"
                                                            name="dataMultiTemp[{{ $i }}][name{{$k}}]"
                                                            id="name{{ $k }}{{ $i }}" placeholder="Tiêu đề"
                                                            value="{{ !empty(Flash::has('name')) ? Flash::get('name') : $item['name'] }}">
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if (!empty($configMan->gallery->$gallery->images_photo))
                                                <div class="form-group mt-3 last:!mb-0">
                                                    <input type="file" class="form-control text-sm"
                                                        name="file{{ $i }}" />
                                                </div>
                                            @endif

                                            <div class="form-group last:!mb-0">
                                                @foreach ($configMan->gallery->$gallery->status_photo as $key => $value)
                                                    <div class="form-group d-inline-block mb-2 me-5">
                                                        <label for="{{ $key }}-checkbox"
                                                            class="d-inline-block align-middle mb-0 mr-2 form-label"><?= $value ?>:</label>
                                                        <label class="switch switch-success">
                                                            <input type="checkbox"
                                                                name="dataMultiTemp[{{ $i }}][status][{{ $key }}]"
                                                                class="switch-input custom-control-input show-checkbox"
                                                                id="{{ $key }}-checkbox"
                                                                {{ (empty($status_array) && empty($item['id']) ? 'checked' : in_array($key, $status_array)) ? 'checked' : '' }}>
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
                                            </div>

                                            <div class="form-group last:!mb-0">
                                                <label for="numb"
                                                    class="d-inline-block align-middle mb-0 mr-2 form-label">S? th?
                                                    t?:</label>
                                                <input type="number"
                                                    class="form-control form-control-mini w-25 text-left d-inline-block align-middle text-sm"
                                                    min="0" name="dataMultiTemp[{{ $i }}][numb]"
                                                    id="numb" placeholder="Số thứ tự"
                                                    value="{{ !empty($item['numb']) ? $item['numb'] : 1 }}">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <input type="hidden" name="id"
                    value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : 0 ?>">
                <input type="hidden" name="gallery" value="<?= !empty($gallery) && $gallery > 0 ? $gallery : '' ?>">
                <input type="hidden" name="id_parent" value="<?= !empty($id_parent) && $id_parent > 0 ? $id_parent : 0 ?>">
                <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">

                @component('component.buttonAdd', ['params' => ['gallery' => $gallery, 'id_parent' => $id_parent]])
                @endcomponent

            </form>
        </div>
    </div>
@endsection

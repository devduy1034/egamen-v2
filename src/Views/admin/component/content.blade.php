<div class="card">
    <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
        @foreach (config('app.langs') as $k => $v)
            <li class="nav-item">
                <a class="nav-link {{ $k ==  config('app.lang_default') ? 'active' : '' }}" id="tabs-lang" data-bs-toggle="tab"
                    data-bs-target="#tabs-lang-{{ $k }}" role="tab"
                    aria-controls="tabs-lang-{{ $k }}" aria-selected="true">{{ $v }}</a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content" id="custom-tabs-three-tabContent-lang">
        @foreach (config('app.langs') as $k => $v)
            <div class="tab-pane fade show {{ $k ==  config('app.lang_default') ? 'active' : '' }}" id="tabs-lang-{{ $k }}"
                role="tabpanel" aria-labelledby="tabs-lang">
                @if (!empty($name))
                    <div class="form-group last:!mb-0">
                        <label class="form-label" for="name{{ $k }}">Tiêu đề ({{ $k }}) :</label>
                        <input type="text" class="form-control for-seo text-sm" name="data[name{{ $k }}]"
                            id="name{{ $k }}" placeholder="Tiêu đề ({{ $k }})"
                            value="{{ !empty(Flash::has('name' . $k)) ? Flash::get('name' . $k) : $item['name' . $k] }}"
                            required>
                    </div>
                @endif
                @if (!empty($office))
                    <div class="form-group last:!mb-0">
                        <label class="form-label" for="office{{ $k }}">Chức vụ ({{ $k }}) :</label>
                        <input type="text" class="form-control text-sm" name="data[office{{ $k }}]"
                            id="office{{ $k }}" placeholder="Chức vụ ({{ $k }})"
                            value="{{ !empty(Flash::has('office' . $k)) ? Flash::get('office' . $k) : $item['office' . $k] }}"
                            required>
                    </div>
                @endif
                @if (!empty($promotion))
                    <div class="form-group last:!mb-0">
                        <label class="form-label" for="promotion{{ $k }}">Khuyến mãi ({{ $k }})
                            :</label>
                        <textarea class="form-control for-seo text-sm {{ !empty($promotion_cke) ? 'form-control-ckeditor' : '' }}"
                            name="data[promotion{{ $k }}]" id="promotion{{ $k }}" rows="5"
                            placeholder="Khuyến mãi ({{ $k }})">{{ !empty(Flash::has('promotion' . $k)) ? Flash::get('promotion' . $k) : @$item['promotion' . $k] }}</textarea>
                    </div>
                @endif
                @if (!empty($incentives))
                    <div class="form-group last:!mb-0">
                        <label class="form-label" for="incentives{{ $k }}">Ưu đãi ({{ $k }})
                            :</label>
                        <textarea class="form-control for-seo text-sm {{ !empty($incentives_cke) ? 'form-control-ckeditor' : '' }}"
                            name="data[incentives{{ $k }}]" id="incentives{{ $k }}" rows="5"
                            placeholder="Ưu đãi ({{ $k }})">{{ !empty(Flash::has('incentives' . $k)) ? Flash::get('incentives' . $k) : @$item['incentives' . $k] }}</textarea>
                    </div>
                @endif
                @if (!empty($desc))
                    <div class="form-group last:!mb-0">
                        <label class="form-label" for="desc{{ $k }}">Mã tả ({{ $k }}) :</label>
                        <textarea class="form-control for-seo text-sm {{ !empty($desc_cke) ? 'form-control-ckeditor' : '' }}"
                            name="data[desc{{ $k }}]" id="desc{{ $k }}" rows="5"
                            placeholder="Mã tả ({{ $k }})">{{ !empty(Flash::has('desc' . $k)) ? Flash::get('desc' . $k) : @$item['desc' . $k] }}</textarea>
                    </div>
                @endif
                @if (!empty($content))
                    <div class="form-group last:!mb-0">
                        <label class="form-label" for="content{{ $k }}">Nội dung
                            ({{ $k }})
                            :</label>
                        <textarea class="form-control for-seo text-sm {{ !empty($content_cke) ? 'form-control-ckeditor' : '' }}"
                            name="data[content{{ $k }}]" id="content{{ $k }}" rows="5"
                            placeholder="Nội dung ({{ $k }})">{{ !empty(Flash::has('content' . $k)) ? Flash::get('content' . $k) : @$item['content' . $k] }}</textarea>
                    </div>
                @endif
                @if (!empty($parameter))
                    <div class="form-group last:!mb-0">
                        <label class="form-label" for="parameter{{ $k }}">Thông số kỹ thuật
                            ({{ $k }}):</label>
                        <textarea class="form-control for-seo text-sm {{ !empty($parameter_cke) ? 'form-control-ckeditor' : '' }}"
                            name="data[parameter{{ $k }}]" id="parameter{{ $k }}" rows="5"
                            placeholder="Nội dung ({{ $k }})">{{ !empty(Flash::has('parameter' . $k)) ? Flash::get('parameter' . $k) : @$item['parameter' . $k] }}</textarea>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@if (!empty($desc_cke) || !empty($content_cke))
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
@endif

@php
    $seoType = $item['type'] ?? request()->route('type') ?? request()->query('type') ?? '';
    $seoDB = Seo::getOnDB($item['id']??0, $com, 'save', $seoType);
    $seo_create = '';
@endphp
<div class="col-12 col-lg-12">
    <div class="card mb-4">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title flex-1">Schema JSON <a href="https://developers.google.com/search/docs/advanced/structured-data/search-gallery" target="_blank">Tài liệu tham khảo</a></h3>
            <button type="submit"
                    class="btn btn-primary !text-white waves-effect btn-schema submit-check"
                    name="build-schema"><i class="far fa-save mr-2"></i>Lưu và tạo tự động schema</button>
        </div>
        <div class="card-body">
            <div class="card-seo">
                <div class="card card-primary card-outline card-outline-tabs">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab-lang" role="tablist">
                            @foreach (config('app.slugs') as $k => $v)
                                @php
                                    $seo_create .= $k . ',';
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ $k ==  config('app.lang_default') ? 'active' : '' }}" id="tabs-lang-schema" data-bs-toggle="pill"
                                        data-bs-target="#tabs-schemalang-{{ $k }}" role="tab"
                                        aria-controls="tabs-schemalang-{{ $k }}" aria-selected="true">Schema JSON
                                        ({{ $v }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="">
                        <div class="tab-content" id="custom-tabs-one-tabContent-lang">
                            @foreach (config('app.slugs') as $k => $v)
                                <div class="tab-pane fade show {{ $k ==  config('app.lang_default') ? 'active' : '' }}"
                                    id="tabs-schemalang-{{ $k }}" role="tabpanel" aria-labelledby="tabs-lang">
                                    <div class="form-group mb-0">
                                        <textarea class="form-control schema-seo code-javascript" name="dataSchema[schema{{ $k }}]" id="schema{{ $k }}"
                                            rows="15" placeholder="Nội dung schema">{{ @$seoDB['schema' . $k] }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            {!! $slot !!}
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="@asset('assets/admin/codemirror/lib/codemirror.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/hint/show-hint.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/dialog/dialog.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/display/fullscreen.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/addon/search/matchesonscrollbar.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/theme/duotone-light.css')" />
<link rel="stylesheet" href="@asset('assets/admin/codemirror/theme/bespin.css')" />
@endpush
@push('scripts')
<script src="@asset('assets/admin/codemirror/lib/codemirror.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/mode/javascript/javascript.js')" type="text/javascript"></script>
{{-- <script src="@asset('assets/admin/codemirror/mode/clike/clike.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/hint/show-hint.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/hint/javascript-hint.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/scroll/annotatescrollbar.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/search/matchesonscrollbar.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/edit/closebrackets.js')" type="text/javascript"></script>
<script src="@asset('assets/admin/codemirror/addon/edit/matchbrackets.js')" type="text/javascript"></script> --}}
<script type="text/javascript">
var editor = {};
let codejavascript = document.getElementsByClassName('code-javascript');
let codephp = document.getElementsByClassName('code-php');
for (var index = 0; index < codejavascript.length; index++) {
    code(codejavascript[index], 'javascript');
}
for (var index = 0; index < codephp.length; index++) {
    code(codephp[index], 'php');
}

function code($element, $lang = 'javascript') {
    let isDarkStyle = window.Helpers.isDarkStyle();
    editor[$element.name] = CodeMirror.fromTextArea($element, {
        mode: $lang,
        theme: !isDarkStyle ? 'duotone-light' : 'bespin',
        tabSize: 2,
        lineNumbers: true,
        lineWrapping: true,
        styleActiveLine: true,
        styleSelectedText: true,
        matchBrackets: true,
        autoCloseBrackets: true
    });
}
</script>
@endpush

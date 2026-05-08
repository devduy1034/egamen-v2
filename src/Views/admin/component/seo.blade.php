@php
    $seoType = $item['type'] ?? request()->route('type') ?? request()->query('type') ?? '';
    $seoDB = Seo::getOnDB($item['id'] ?? 0, $com, 'save', $seoType);
    $slugurlArray = '';
    $seo_create = '';
    if (!empty(@$seoDB['meta'])) {
        $metaOrder = json_decode(@$seoDB['meta'], true)['metaorder'];
        $metaIndex = json_decode(@$seoDB['meta'], true)['metaindex'];
    } else {
        $metaOrder = '';
        $metaIndex = '';
    }
@endphp
<div class="col-12 col-lg-12">
    <div class="card mb-4">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title flex-1">Nội dung seo</h3>
            <a class="create-seo btn btn-primary !text-white waves-effect" title="Tạo seo">Tạo seo</a>
        </div>
        <div class="card-body">
            <div class="card-seo" @if (!empty(@$schema)) x-data="seoRankMath()" x-init="init()" @endif>
                <div class="card card-primary card-outline card-outline-tabs">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
                            @foreach (config('app.slugs') as $k => $v)
                                @php $seo_create .= $k . ","; @endphp
                                <li class="nav-item">
                                    <a @click="lang=`{{ $k }}`,seoRankMathGroup()"
                                        class="nav-link {{ $k == config('app.lang_default') ? 'active' : '' }}"
                                        id="tabs-lang" data-bs-toggle="tab"
                                        data-bs-target="#tabs-seolang-{{ $k }}" role="tab"
                                        aria-controls="tabs-seolang-{{ $k }}" aria-selected="true">SEO
                                        ({{ $v }})
                                    </a>
                                </li>
                            @endforeach
                            @if (in_array($com, ['product', 'news']))
                                <li class="nav-item">
                                    <a class="nav-link" id="tabs-lang" data-bs-toggle="tab"
                                        data-bs-target="#tabs-seolang-nangcao" role="tab"
                                        aria-controls="tabs-seolang-nangcao" aria-selected="true">Nâng cao</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="">
                        <div class="tab-content" id="custom-tabs-three-tabContent-lang">
                            @foreach (config('app.slugs') as $k => $v)
                                <div class="tab-pane fade show {{ $k == config('app.lang_default') ? 'active' : '' }}"
                                    id="tabs-seolang-{{ $k }}" role="tabpanel" aria-labelledby="tabs-lang">
                                    <div class="form-group last:!mb-0">
                                        <div class="label-seo">
                                            <label for="title{{ $k }}">SEO Title:</label>
                                            <strong
                                                class="count-seo"><span>{{ !empty($seoDB) ? mb_strlen(@$seoDB['title' . $k] ?? '') : 0 }}</span>/70
                                                ký tự</strong>
                                        </div>

                                        <input @keyup.debounce.50ms="seoRankMathGroup()" type="text"
                                            class="form-control check-seo title-seo text-sm"
                                            name="dataSeo[title{{ $k }}]" id="title{{ $k }}"
                                            placeholder="SEO Title ({{ $k }})"
                                            value="{{ @$seoDB['title' . $k] ?? '' }}">
                                    </div>
                                    <div class="form-group last:!mb-0">
                                        <div class="label-seo">
                                            <label for="keywords{{ $k }}">SEO Keywords:</label>
                                            <strong
                                                class="count-seo"><span>{{ !empty($seoDB) ? mb_strlen(@$seoDB['keywords' . $k] ?? '') : 0 }}</span>/70
                                                ký tự</strong>
                                        </div>
                                        <input type="text" class="form-control check-seo keywords-seo text-sm"
                                            name="dataSeo[keywords{{ $k }}]"
                                            id="keywords{{ $k }}"
                                            placeholder="SEO Keywords ({{ $k }})"
                                            value="{{ @$seoDB['keywords' . $k] ?? '' }}">
                                    </div>
                                    <div class="form-group last:!mb-0">
                                        <div class="label-seo">
                                            <label for="description{{ $k }}">SEO Description:</label>
                                            <strong
                                                class="count-seo"><span>{{ !empty($seoDB) ? mb_strlen(@$seoDB['description' . $k] ?? '') : 0 }}</span>/160
                                                ký tự</strong>
                                        </div>
                                        <textarea @keyup.debounce.50ms="seoRankMathGroup()"
                                            class="form-control check-seo description-seo text-sm" name="dataSeo[description{{ $k }}]"
                                            id="description{{ $k }}" rows="5" placeholder="SEO Description ({{ $k }})">{!! !empty($seoDB) ? Func::decodeHtmlChars(@$seoDB['description' . $k] ?? '') : '' !!}</textarea>
                                    </div>
                                    @if (!empty(@$schema))
                                        <div class="seo-panel-group mb-0 seo-general"
                                            id="seo-general-{{ $k }}">
                                            <div class="form-group">
                                                <label for=""><strong>Keyword chính</strong></label>
                                                <div class="input-group">
                                                    <input x-model="keyword['{{ $k }}'].value"
                                                        x-on:change.debounce.50ms="seoRankMathGroup()" type="text"
                                                        class="form-control seo_focus_keyword"
                                                        value="{{ @$seoDB['seo_focus' . $k] }}"
                                                        placeholder="Chọn từ khóa bạn muốn xếp hạng"
                                                        id="seo_focus_keyword_{{ $k }}"
                                                        name="dataSeo[seo_focus{{ $k }}]">
                                                    <span class="input-group-text"><span class="seo_point"
                                                            id="seo_point_{{ $k }}">0</span>/100</span>
                                                </div>
                                                <p style="margin: 5px 0; color: #ccc;">Chọn từ khóa bạn muốn xếp hạng.
                                                </p>
                                            </div>
                                            <ul>
                                                @foreach (Seo::listCriteria() as $key => $label)
                                                    <li key="{{ $key }}"
                                                        class="seo-check-{{ $key }} test-fail">
                                                        <span class="icon"><i class="ti ti-x"></i></span>
                                                        <span class="txt">{{ $label }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            @if (in_array($com, ['product', 'news']))
                                <div class="tab-pane fade show " id="tabs-seolang-nangcao" role="tabpanel"
                                    aria-labelledby="tabs-lang">
                                    <div class="form-group mb-0">
                                        <div class="row">
                                            <div class="col-md-3 col-lg-2">
                                                <div class="label-seo">
                                                    <label><strong>Robots Meta:</strong></label>
                                                </div>
                                            </div>
                                            <div class="col-md-9 col-lg-10">
                                                <div class="label-seo">
                                                    <label><b>Index meta</b></label>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input name="metaindex" class="form-check-input"
                                                            type="radio" value="noindex" id="noindex"
                                                            {{ $metaIndex == 'noindex' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="noindex"> No Index
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input name="metaindex" class="form-check-input"
                                                            type="radio" value="index" id="index"
                                                            {{ ($metaIndex == 'index' or $metaIndex == '') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="index"> Index </label>
                                                    </div>
                                                </div>
                                                @if (!empty(@$schema))
                                                    <div class="label-seo">
                                                        <label><b>Meta order</b></label>
                                                        <div>
                                                            <div class="form-check form-check-primary">
                                                                <input class="form-check-input" name="metaorder[]"
                                                                    type="checkbox"
                                                                    {{ in_array('nofollow', explode(',', $metaOrder)) ? 'checked' : '' }}
                                                                    value="nofollow" id="nofollow">
                                                                <label class="form-check-label" for="nofollow">No
                                                                    Follow</label>
                                                            </div>
                                                            <div class="form-check form-check-primary">
                                                                <input class="form-check-input" name="metaorder[]"
                                                                    type="checkbox"
                                                                    {{ in_array('noarchive', explode(',', $metaOrder)) ? 'checked' : '' }}
                                                                    value="noarchive" id="noarchive">
                                                                <label class="form-check-label" for="noarchive">No
                                                                    Archive</label>
                                                            </div>
                                                            <div class="form-check form-check-primary">
                                                                <input class="form-check-input" name="metaorder[]"
                                                                    type="checkbox"
                                                                    {{ in_array('noimageindex', explode(',', $metaOrder)) ? 'checked' : '' }}
                                                                    value="noimageindex" id="noimageindex">
                                                                <label class="form-check-label" for="noimageindex">No
                                                                    Image Index</label>
                                                            </div>
                                                            <div class="form-check form-check-primary">
                                                                <input class="form-check-input" name="metaorder[]"
                                                                    type="checkbox"
                                                                    {{ in_array('nosnippet', explode(',', $metaOrder)) ? 'checked' : '' }}
                                                                    value="nosnippet" id="nosnippet">
                                                                <label class="form-check-label" for="nosnippet">No
                                                                    Snippet</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <input type="hidden" id="seo-create"
                        value="<?= isset($seo_create) ? rtrim($seo_create, ',') : '' ?>">
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        /* SEO */
        function seoExist() {
            var inputs = $('.card-seo input.check-seo');
            var textareas = $('.card-seo textarea.check-seo');
            var flag = false;
            if (!flag) {
                inputs.each(function(index) {
                    var input = $(this).attr('id');
                    value = $('#' + input).val();
                    if (value) {
                        flag = true;
                        return false;
                    }
                });
            }

            if (!flag) {
                textareas.each(function(index) {
                    var textarea = $(this).attr('id');
                    value = $('#' + textarea).val();
                    if (value) {
                        flag = true;
                        return false;
                    }
                });
            }
            return flag;
        }

        function seoCreate() {
            var flag = true;
            var seolang = $('#seo-create').val();

            var seolangArray = seolang.split(',');
            var seolangCount = seolangArray.length;
            var inputArticle = $('.card-article input.for-seo');
            var textareaArticle = $('.card-article textarea.for-seo');
            var textareaArticleCount = textareaArticle.length;
            var count = 0;
            var inputSeo = $('.card-seo input.check-seo');
            var textareaSeo = $('.card-seo textarea.check-seo');

            /* SEO Create - Input */
            inputArticle.each(function(index) {
                var input = $(this).attr('id');
                var lang = input.substr(input.length - 2);
                if (seolang.indexOf(lang) >= 0) {
                    name = $('#' + input).val();
                    name = name.substr(0, 70);
                    name = name.trim();
                    $('#title' + lang + ', #keywords' + lang).val(name);
                    seoCount($('#title' + lang));
                    seoCount($('#keywords' + lang));
                }
            });
            for (var i = 0; i < seolangArray.length; i++)
                if (seolangArray[i]) {
                    seoPreview(seolangArray[i]);
                }
        }

        function seoPreview(lang) {
            var titlePreview = '#title-seo-preview' + lang;
            var descriptionPreview = '#description-seo-preview' + lang;
            var title = $('#title' + lang).val();
            var description = $('#description' + lang).val();

            if ($(titlePreview).length) {
                if (title) $(titlePreview).html(title);
                else $(titlePreview).html('Title');
            }
            if ($(descriptionPreview).length) {
                if (description) $(descriptionPreview).html(description);
                else $(descriptionPreview).html('Description');
            }
        }

        function seoCount(obj) {
            if (obj.length) {
                var countseo = parseInt(obj.val().toString().length);
                countseo = countseo ? countseo++ : 0;

                obj.parents('div.form-group').children('div.label-seo').find('.count-seo span').html(countseo);
            }
        }

        function seoChange() {
            var seolang = 'vi,en';
            var elementSeo = $('.card-seo .check-seo');

            elementSeo.each(function(index) {
                var element = $(this).attr('id');
                var lang = element.substr(element.length - 2);
                if (seolang.indexOf(lang) >= 0) {
                    if ($('#' + element).length) {
                        $('body').on('keyup', '#' + element, function() {
                            seoPreview(lang);
                        });
                    }
                }
            });
        }
        seoChange();
        if ($('.create-seo').length) {
            $('body').on('click', '.create-seo', function() {
                if (seoExist()) confirmDialog('create-seo', 'Bạn muốn tạo lại nội dung seo', '');
                else seoCreate();
            });
        }
        if ($('.title-seo').length && $('.keywords-seo').length && $('.description-seo').length) {
            $('body').on('keyup', '.title-seo, .keywords-seo, .description-seo', function() {
                seoCount($(this));
            });
        }
    </script>
@endpush
@if (!empty(@$schema))
    @push('styles')
        <style>
            .seo-panel-group ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .seo-panel-group ul li {
                font-size: 15px;
                line-height: 28px;
                position: relative;
                clear: both;
                color: #5a6065;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
            }

            .seo-panel-group ul li:last-child {
                margin-bottom: 0;
            }

            li span.icon {
                color: #fff;
                width: 25px;
                height: 25px;
                line-height: 25px;
                text-align: center;
                list-style: none;
                border-radius: 50%;
                margin-right: 5px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            li.test-fail span.icon {
                background-color: rgb(222 55 48);
            }

            li.test-success span.icon {
                background-color: var(--bs-success);
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            function seoRankMath() {
                return {
                    icon: {
                        'error': '<i class="ti ti-x"></i>',
                        'success': '<i class="ti ti-check"></i>'
                    },
                    messageError: {
                        'keywordInTitle': 'Thêm Từ khóa chính vào tiêu đề SEO.'
                    },
                    messageSuccess: {
                        'keywordInTitle': 'Tuyệt vời! Bạn đang sử dụng Keyword chính trong tiêu đề SEO.',
                        'lengthTitle': 'Tuyệt vời! Tiêu đề của bạn đã có độ dài tối ưu',
                        'lengthMetaDescription': 'Tuyệt vời! Mô tả meta seo của bạn đã có độ dài tối ưu'
                    },
                    domain: `{{ request()->root() }}`,
                    lang: `{{ config('app.seo_default') }}`,
                    content: {
                        @foreach (config('app.slugs') as $k => $v)
                            '{{ $k }}': '',
                        @endforeach
                    },

                    get keyword() {
                        return {
                            @foreach (config('app.slugs') as $k => $v)
                                '{{ $k }}': {
                                    'this': $('#seo_focus_keyword_{{ $k }}'),
                                    'value': $('#seo_focus_keyword_{{ $k }}').val()
                                },
                            @endforeach
                        }
                    },
                    get title() {
                        return {
                            @foreach (config('app.slugs') as $k => $v)
                                '{{ $k }}': {
                                    'this': $('#title{{ $k }}'),
                                    'value': $('#title{{ $k }}').val()
                                },
                            @endforeach
                        };
                    },
                    get description() {
                        return {
                            @foreach (config('app.slugs') as $k => $v)
                                '{{ $k }}': {
                                    'this': $('#description{{ $k }}'),
                                    'value': $('#description{{ $k }}').val()
                                },
                            @endforeach
                        }
                    },
                    get slug() {
                        return {
                            @foreach (config('app.slugs') as $k => $v)
                                '{{ $k }}': $('#slug{{ $k }}').val(),
                            @endforeach
                        }
                    },
                    init() {
                        var root = this;
                        setTimeout(function() {
                            for (var editorId in CKEDITOR.instances) {
                                let subID = editorId.substring(7);
                                root.content[subID] = CKEDITOR.instances[editorId].getData();
                            }
                        }, 100);
                        @foreach (config('app.slugs') as $k => $v)
                            this.title[`{{ $k }}`].value = this.title[`{{ $k }}`].value.toLowerCase();
                            this.keyword[`{{ $k }}`].value = this.keyword[`{{ $k }}`].value
                            .toLowerCase();
                            this.description[`{{ $k }}`].value = this.description[`{{ $k }}`].value
                                .toLowerCase();
                            let target{{ $k }} = document.getElementById('slugurlpreview{{ $k }}')
                                .firstElementChild;
                            let observer{{ $k }} = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    root.slug['{{ $k }}'] = mutation.target.textContent
                                    .toLowerCase();
                                });
                            });
                            observer{{ $k }}.observe(target{{ $k }}, {
                                childList: true
                            });
                        @endforeach
                        setTimeout(function() {
                            for (var editorId in CKEDITOR.instances) {
                                let subID = editorId.substring(7);
                                var editor = CKEDITOR.instances[editorId];
                                if (editor) {
                                    editor.on('change', function(evt) {
                                        var nnn = evt.editor.getData();
                                        root.setContent(subID, nnn);
                                        root.seoRankMathGroup();
                                    }, this);
                                }
                            }
                        }, 100);
                        setTimeout(function() {
                            root.seoRankMathGroup();
                        }, 200);
                    },
                    setContent(key, value) {
                        this.content[key] = value;
                    },
                    getContent(key) {
                        return this.content[key];
                    },
                    async seoRankMathGroup() {
                        let point = 0;
                        let root = this;
                        let seoPanel = $(`#seo-general-` + this.lang);
                        let regex = new RegExp(root.keyword[root.lang].value, "i");
                        if (this.keyword[this.lang].value.length !== 0) {
                            if (root.title[root.lang].value.search(regex) !== -1) {
                                point++;
                                root.seoRankMathChangeStatus(`keywordInTitle`, `success`);
                            } else root.seoRankMathChangeStatus(`keywordInTitle`, `error`);
                            let beginTitle = root.title[root.lang].value.search(regex);
                            if (beginTitle === 0) {
                                point++;
                                root.seoRankMathChangeStatus(`titleStartWithKeyword`, `success`);
                            } else if (beginTitle == -1) {
                                root.seoRankMathChangeStatus(`titleStartWithKeyword`, `error`);
                            } else {
                                let endTitle = beginTitle + root.keyword[root.lang].value.length;
                                if (endTitle === root.title[root.lang].value.length) {
                                    root.seoRankMathChangeStatus(`titleStartWithKeyword`, `error`);
                                } else {
                                    point++;
                                    root.seoRankMathChangeStatus(`titleStartWithKeyword`, `success`);
                                }
                            }
                        }
                        let titleLength = root.title[root.lang].value.length;
                        if (titleLength >= 10 && titleLength <= 70) {
                            point++;
                            root.seoRankMathChangeStatus('lengthTitle', 'success');
                        } else {
                            if (titleLength > 70) mess = 'Tiêu đề c? ' + titleLength + ' ký tự. Hãy xem xét rút ngắn nó.';
                            if (titleLength < 10) mess = 'Tiêu đề ' + titleLength +
                                ' ký tự (ngắn). Cố gắng có được 70 ký tự';
                            seoPanel.find('li[key="lengthTitle"]').removeClass('test-success').addClass('test-fail');
                            seoPanel.find('li[key="lengthTitle"]').find('span.txt').html(mess);
                            seoPanel.find('li[key="lengthTitle"]').find('span.icon').html(this.icon.error);
                        }
                        if (root.keyword[root.lang].value.length !== 0) {
                            if (root.description[root.lang].value.search(regex) !== -1) {
                                point++;
                                root.seoRankMathChangeStatus('keywordInMetaDescription', 'success');
                            } else root.seoRankMathChangeStatus('keywordInMetaDescription', 'error');
                        }
                        let descriptionLength = root.description[root.lang].value.length;
                        if (descriptionLength >= 160 && descriptionLength <= 300) {
                            point++;
                            root.seoRankMathChangeStatus('lengthMetaDescription', 'success');
                        } else {
                            if (descriptionLength > 300) mess = 'Mã t? meta SEO c? ' + descriptionLength +
                                ' ký tự. Hãy xem xét rút ngắn nó.';
                            if (descriptionLength < 160) mess = 'Mã t? meta SEO c? ' + descriptionLength +
                                ' ký tự (ngắn). Cố gắng thành 160 ký tự';
                            seoPanel.find('li[key="lengthMetaDescription"]').removeClass('test-success').addClass(
                                'test-fail');
                            seoPanel.find('li[key="lengthMetaDescription"]').find('span.txt').html(mess);
                            seoPanel.find('li[key="lengthMetaDescription"]').find('span.icon').html(root.icon.error);
                        }
                        if (root.keyword[root.lang].value.length !== 0) {
                            if (root.slug[root.lang].search(root.ChangeToSlug(root.keyword[root.lang].value)) !== -1) {
                                point++;
                                root.seoRankMathChangeStatus('keywordInPermalink', 'success');
                            }
                        }
                        let object = seoPanel.find('li[key="lengthPermalink"]');
                        let slugLength = root.slug[root.lang].length + root.domain.length - 8;
                        if (slugLength > 75 || slugLength < 35) {
                            if (slugLength > 75) mess = 'Url có ' + slugLength + ' ký tự (dài). Hãy xem xét rút ngắn nó.';
                            if (slugLength < 35) mess = 'Url có ' + slugLength + ' ký tự (ngắn).';
                            object.removeClass('test-success').addClass('test-fail');
                            object.find('span.txt').html(mess);
                            object.find('span.icon').html(root.icon.error);
                        } else {
                            point++;
                            mess = 'Url có ' + slugLength + ' ký tự. Tuyệt vời!';
                            object.removeClass('test-fail').addClass('test-success');
                            object.find('span.txt').html(mess);
                            object.find('span.icon').html(root.icon.success);
                        }
                        let contentRemoveHtml = await root.stripHtml(root.content[root.lang]).toLowerCase();
                        if (root.keyword[root.lang].value.length !== 0) {
                            let regex = new RegExp(root.keyword[root.lang].value, "i");
                            let searchKey = contentRemoveHtml.search(regex);
                            if (searchKey !== -1) {
                                point++;
                                root.seoRankMathChangeStatus('keywordInContent', 'success');
                                let firstKeyword = contentRemoveHtml.substr(0, root.keyword[root.lang].value.length)
                                    .toLowerCase();
                                if (root.keyword[root.lang].value.toLowerCase() === firstKeyword) {
                                    point++;
                                    root.seoRankMathChangeStatus('keywordIn10Percent', 'success');
                                }
                            }
                        }
                        let contentWord = contentRemoveHtml.split(/[\s.,;]+/).length;
                        if (contentWord >= 600 && contentWord <= 2500) {
                            point++;
                            root.seoRankMathChangeStatus('lengthContent', 'success');
                        } else root.seoRankMathChangeStatus('lengthContent', 'error');
                        let tmp = document.createElement('div');
                        tmp.innerHTML = root.content[root.lang];
                        let internalLinks = tmp.getElementsByTagName("a");
                        if (internalLinks.length === 0) root.seoRankMathChangeStatus('linksHasInternal', 'error');
                        else {
                            let linksHasInternal = false;
                            $.each(internalLinks, function(index, value) {
                                if (internalLinks[index].href.toLowerCase().search(root.domain) !== -1) {
                                    point++;
                                    root.seoRankMathChangeStatus('linksHasInternal', 'success');
                                    linksHasInternal = true;
                                    return true;
                                }
                            });
                            if (linksHasInternal === false) root.seoRankMathChangeStatus('linksHasInternal', 'error');
                        }
                        if (root.keyword[root.lang].value.length !== 0) {
                            let regex = new RegExp(root.keyword[root.lang].value, "i");
                            let keywordInSubheadings = false;
                            let headingH2 = tmp.getElementsByTagName('h2');
                            if (headingH2.length !== 0) {
                                $.each(headingH2, function(index, value) {

                                    if (headingH2[index].innerText.toLowerCase().search(regex) !== -1) {
                                        point++;
                                        root.seoRankMathChangeStatus('keywordInSubheadings', 'success');
                                        keywordInSubheadings = true;
                                        return true;
                                    }
                                });
                            }
                            let headingH3 = tmp.getElementsByTagName('h3');
                            if (keywordInSubheadings === false && headingH3.length !== 0) {
                                $.each(headingH3, function(index, value) {
                                    if (headingH3[index].innerText.toLowerCase().search(regex) !== -1) {
                                        point++;
                                        root.seoRankMathChangeStatus('keywordInSubheadings', 'success');
                                        keywordInSubheadings = true;
                                        return true;
                                    }
                                });
                            }
                            let headingH4 = tmp.getElementsByTagName('h4');
                            if (keywordInSubheadings === false && headingH4.length !== 0) {
                                $.each(headingH4, function(index, value) {
                                    if (headingH4[index].innerText.toLowerCase().search(regex) !== -1) {
                                        point++;
                                        root.seoRankMathChangeStatus('keywordInSubheadings', 'success');
                                        keywordInSubheadings = true;
                                        return true;
                                    }
                                });
                            }
                            let headingH5 = tmp.getElementsByTagName('h5');
                            if (keywordInSubheadings === false && headingH5.length !== 0) {
                                $.each(headingH5, function(index, value) {
                                    if (headingH5[index].innerText.toLowerCase().search(regex) !== -1) {
                                        point++;
                                        root.seoRankMathChangeStatus('keywordInSubheadings', 'success');
                                        keywordInSubheadings = true;
                                        return true;
                                    }
                                });
                            }
                            let headingH6 = tmp.getElementsByTagName('h5');
                            if (keywordInSubheadings === false && headingH6.length !== 0) {
                                $.each(headingH6, function(index, value) {
                                    if (headingH6[index].innerText.toLowerCase().search(regex) !== -1) {
                                        point++;
                                        root.seoRankMathChangeStatus('keywordInSubheadings', 'success');
                                        keywordInSubheadings = true;
                                        return true;
                                    }
                                });
                            }
                            if (keywordInSubheadings === false) root.seoRankMathChangeStatus('keywordInSubheadings',
                                'error');
                        }
                        let img = tmp.getElementsByTagName('img');
                        if (img.length === 0) {
                            root.seoRankMathChangeStatus('keywordInImageAlt', 'error');
                            root.seoRankMathChangeStatus('contentHasAssets', 'error');
                        } else {
                            if (root.keyword[root.lang].value.length !== 0) {
                                let keywordInImageAlt = false;
                                if (img.length >= 2) {
                                    point++;
                                    root.seoRankMathChangeStatus('contentHasAssets', 'success');
                                } else root.seoRankMathChangeStatus('contentHasAssets', 'error');
                                $.each(img, function(index, value) {
                                    let regex = new RegExp(root.keyword[root.lang].value, "i");
                                    if (img[index].alt.toLowerCase().search(regex) !== -1) {
                                        point++;
                                        root.seoRankMathChangeStatus('keywordInImageAlt', 'success');
                                        keywordInImageAlt = true;
                                        return true;
                                    }
                                });
                                if (keywordInImageAlt === false) root.seoRankMathChangeStatus('keywordInImageAlt', 'error');
                            }
                        }
                        if (root.keyword[root.lang].value.length !== 0) {
                            object = seoPanel.find('li[key="keywordDensity"]');
                            let mess;
                            let contentRemoveHtml = await root.stripHtml(root.content[root.lang]).toLowerCase();
                            let contentWord = contentRemoveHtml.split(/[\s.,;]+/).length;
                            let nkr = root.occurrences(contentRemoveHtml, root.keyword[root.lang].value.toLowerCase());
                            let keywordDensity = (nkr / contentWord) * 100;
                            keywordDensity = keywordDensity.toFixed(2);
                            if (keywordDensity > 2.5 || keywordDensity < 0.75) {
                                if (keywordDensity > 2.5) mess = 'Mật độ từ khóa là ' + keywordDensity +
                                    ' (cao). Số lần từ khóa xuất hiện là ' + nkr + '.';
                                if (keywordDensity < 0.75) mess = 'Mật độ từ khóa là ' + keywordDensity +
                                    ' (thấp). Số lần từ khóa xuất hiện là ' + nkr + '.';
                                object.removeClass('test-success').addClass('test-fail');
                                object.find('span.txt').html(mess);
                                object.find('span.icon').html(root.icon.error);
                            } else {
                                point++;
                                mess = 'Mật độ từ khóa là ' + keywordDensity + '. Số lần từ khóa xuất hiện là ' + nkr + '.';
                                object.removeClass('test-fail').addClass('test-success');
                                object.find('span.txt').html(mess);
                                object.find('span.icon').html(root.icon.success);
                            }
                        }
                        let tagP = tmp.getElementsByTagName('p');
                        if (tagP.length >= 2) {
                            point++;
                            root.seoRankMathChangeStatus('contentHasShortParagraphs', 'success');
                        } else root.seoRankMathChangeStatus('contentHasShortParagraphs', 'error');
                        if (root.keyword[root.lang].value.length === 0) {
                            root.seoRankMathChangeStatus('keywordNotUsed', 'error');
                            root.seoRankMathChangeStatus('keywordInTitle', 'error');
                            root.seoRankMathChangeStatus('titleStartWithKeyword', 'error');
                            root.seoRankMathChangeStatus('keywordInMetaDescription', 'error');
                            root.seoRankMathChangeStatus('keywordInPermalink', 'error');
                            root.seoRankMathChangeStatus('keywordInContent', 'error');
                            root.seoRankMathChangeStatus('keywordIn10Percent', 'error');
                            root.seoRankMathChangeStatus('keywordInImageAlt', 'error');
                            root.seoRankMathChangeStatus('keywordDensity', 'error');
                            root.seoRankMathChangeStatus('keywordInSubheadings', 'error');
                        } else {
                            point++;
                            root.seoRankMathChangeStatus('keywordNotUsed', 'success');
                        }
                        point = ((point > 17 ? 17 : point) / 17) * 100;
                        $('#seo_point_' + root.lang).html(Math.ceil(point));
                    },
                    seoRankMathChangeStatus(key, status) {
                        let object = $(`#seo-general-` + this.lang).find('li[key="' + key + '"]');
                        if (status === 'success') {
                            object.removeClass('test-fail').addClass('test-success');
                            object.find('span.txt').html(this.messageSuccess[key]);
                            object.find('span.icon').html(this.icon.success);
                        } else {
                            object.removeClass('test-success').addClass('test-fail');
                            object.find('span.txt').html(this.messageError[key]);
                            object.find('span.icon').html(this.icon.error);
                        }
                    },
                    stripHtml(html) {
                        let tmp = document.createElement("DIV");
                        tmp.innerHTML = html;
                        return tmp.textContent || tmp.innerText || "";
                    },
                    ChangeToSlug(title) {
                        let slug = title.toLowerCase();
                        slug = slug.replace(/?|?|?|?|?|a|?|?|?|?|?|?|?|?|?|?|?/gi, 'a');
                        slug = slug.replace(/?|?|?|?|?|?|?|?|?|?|?/gi, 'e');
                        slug = slug.replace(/i|?|?|?|i|?/gi, 'i');
                        slug = slug.replace(/?|?|?|?|?|?|?|?|?|?|?|o|?|?|?|?|?/gi, 'o');
                        slug = slug.replace(/?|?|?|u|?|u|?|?|?|?|?/gi, 'u');
                        slug = slug.replace(/?|?|?|?|?/gi, 'y');
                        slug = slug.replace(/d/gi, 'd');
                        slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi,
                            '');
                        slug = slug.replace(/ /gi, "-");
                        slug = slug.replace(/\-\-\-\-\-/gi, '-');
                        slug = slug.replace(/\-\-\-\-/gi, '-');
                        slug = slug.replace(/\-\-\-/gi, '-');
                        slug = slug.replace(/\-\-/gi, '-');
                        slug = '@' + slug + '@';
                        slug = slug.replace(/\@\-|\-\@|\@/gi, '');
                        return slug;
                    },
                    occurrences(string, subString, allowOverlapping) {
                        string += "";
                        subString += "";
                        if (subString.length <= 0) return (string.length + 1);
                        var n = 0,
                            pos = 0,
                            step = allowOverlapping ? 1 : subString.length;
                        while (true) {
                            pos = string.indexOf(subString, pos);
                            if (pos >= 0) {
                                ++n;
                                pos += step;
                            } else break;
                        }
                        return n;
                    }
                }
            }
        </script>
    @endpush
@endif

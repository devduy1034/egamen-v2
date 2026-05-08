<div class="card card-primary card-outline text-sm mb-4">
    <div class="card-header">
        <h3 class="card-title">Đường dẫn</h3>
        <span class="ms-2 text-danger">(Vui lòng không nhập trùng tiêu đề)</span>
    </div>
    <div class="card-body card-slug">

        @if (isset($slugchange) && $slugchange == 1)
            <div class="form-group mb-2 flex items-center">
                <label for="slugchange" class="d-inline-block align-middle text-info mb-0 mr-2">Thay đổi đường dẫn theo
                    tiêu đề mới:</label>
                <div class="d-inline-block form-check form-check-success">
                    <input type="checkbox" class="form-check-input" name="slugchange" id="slugchange">
                </div>
            </div>
        @endif

        <input type="hidden" class="slug-id" value="{{ request()->query('id')??'' }}">
        <input type="hidden" class="slug-copy" value="{{ isset($copy) && $copy == true ? 1 : 0 }}">

        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
                    @foreach (config('app.slugs') as $k => $v)
                        <li class="nav-item">
                            <a class="nav-link {{ $k ==  config('app.lang_default') ? 'active' : '' }}" id="tabs-lang" data-bs-toggle="tab"
                                data-bs-target="#tabs-sluglang-{{ $k }}" role="tab"
                                aria-controls="tabs-sluglang-{{ $k }}"
                                aria-selected="true">{{ $v }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="">
                <div class="tab-content" id="custom-tabs-slug-tabContent-lang">
                    @foreach (config('app.slugs')??[] as $k => $v)
                        <div class="tab-pane fade show {{ $k ==  config('app.lang_default') ? 'active' : '' }}"
                            id="tabs-sluglang-{{ $k }}" role="tabpanel" aria-labelledby="tabs-lang">
                            <div class="form-gourp mb-0">
                                <label class="d-block form-label">Đường dẫn mẫu ({{ $k }}):<span
                                        class="mx-2 font-weight-normal"
                                        id="slugurlpreview{{ $k }}">{{ config('app.asset') }}<strong
                                            class="text-info">{{ @$item['slug' . $k]??'' }}</strong></span></label>
                                <input type="text" class="form-control slug-input no-validate text-sm"
                                    name="slug{{ $k }}" id="slug{{ $k }}"
                                    placeholder="Đường dẫn ({{ $k }})"
                                    value="{{ @$item['slug' . $k]??'' }}" required>
                                <input type="hidden" id="slug-default{{ $k }}"
                                    value="{{  @$item['slug' . $k]??'' }}">
                                <p class="alert-slug{{ $k }} text-danger d-none mt-2 mb-0"
                                    id="alert-slug-danger{{ $k }}">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <span>Đường dẫn đã tồn tại, vui lòng nhập đường dẫn khác.</span>
                                </p>
                                <p class="alert-slug{{ $k }} text-success d-none mt-2 mb-0"
                                    id="alert-slug-success{{ $k }}">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <span>Đường dẫn hợp lệ.</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        function slugStatus(status) {
            var sluglang = SLUG_LANG;
            var inputArticle = $('.card-article input.for-seo');

            inputArticle.each(function (index) {
                var name = $(this).attr('id');
                var lang = name.substr(name.length - 2);
                if (sluglang.indexOf(lang) >= 0) {
                    var title = '';
                    if (status == 1) {
                        if ($('#' + name).length) {
                            title = $('#' + name).val();

                            /* Slug preivew */
                            slugPreview(title, lang);

                            /* Slug preivew title seo */
                            slugPreviewTitleSeo(title, lang);
                        }
                    } else if (status == 0) {
                        if ($('#slug-default' + lang).length) {
                            title = $('#slug-default' + lang).val();

                            /* Slug preivew */
                            slugPreview(title, lang);

                            /* Slug preivew title seo */
                            slugPreviewTitleSeo(title, lang);
                        }
                    }
                }
            });
        }
        function slugPress() {
            const sluglang = SLUG_LANG;
            const inputArticle = $('.card-article input.for-seo');
            const id = $('.slug-id').val();
            const seourlstatic = true;
            inputArticle.each(function (index) {
                const name = $(this).attr('id');
                const lang = name.substr(name.length - 2);

                if (sluglang.indexOf(lang) >= 0) {
                    if ($('#' + name).length) {
                        $('body').on('keyup', '#' + name, function (e) {
                            const keyCode = e.keyCode || e.which;
                            const title = $('#' + name).val();

                            if (keyCode != 13) {
                                if ((!id || id == 0 || $('#slugchange').prop('checked')) && seourlstatic) {
                                    /* Slug preivew */
                                    slugPreview(title, lang);
                                }

                                /* Slug preivew title seo */
                                slugPreviewTitleSeo(title, lang);

                                /* slug Alert */
                                slugAlert(2, lang);
                            }
                        });
                    }

                    if ($('#slug' + lang).length) {
                        $('body').on('keyup', '#slug' + lang, function (e) {
                            const keyCode = e.keyCode || e.which;
                            const title = $('#slug' + lang).val();

                            if (keyCode != 13) {
                                /* Slug preivew */
                                slugPreview(title, lang, true);

                                /* slug Alert */
                                slugAlert(2, lang);
                            }
                        });
                    }
                }
            });
        }
        function slugChange(obj) {
            if (obj.is(':checked')) {
                slugStatus(1);
                $('.slug-input').attr('readonly', true);
            } else {
                slugStatus(0);
                $('.slug-input').attr('readonly', false);
            }
        }
        if ($('#slugchange').length) {
            $('body').on('click', '#slugchange', function () {
                slugChange($(this));
            });
        }
        slugPress();
    </script>
@endpush
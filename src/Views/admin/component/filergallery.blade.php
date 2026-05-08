
<div class="col-12 col-lg-12">
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Bộ sưu tập {{ $title_main }}</h3>
        </div>
        <div class="card-body">
            <div class="form-group last:!mb-0">
                <label for="filer-gallery" class="label-filer-gallery bold mb-3">Album ảnh:  ({{ config('type.type_img') }})</label>
                <input type="file" name="files[]" id="filer-gallery" multiple="multiple">
                <input type="hidden" class="col-filer" value="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-6">
                <input type="hidden" class="act-filer" value={{ $act }}>
                <input type="hidden" class="folder-filer" value="{{$folder}}">
            </div>
            @if (!empty($gallery) && count($gallery) > 0)
                <div class="form-group form-group-gallery last:!mb-0">
                    <label class="label-filer">Album hiện tại:</label>
                    <div class="action-filer mb-3">
                        <a class="btn bg-primary text-white check-all-filer mr-1"><i
                                class="ti ti-square mr-2"></i>Chọn tất cả</a>
                        <button type="button"
                            class="btn  bg-info text-white sort-filer mr-1"><i
                                class="ti ti-arrows-shuffle-2 mr-2"></i>Sắp xếp</button>
                        <a class="btn  bg-danger text-white delete-all-filer"><i
                                class="ti ti-trash mr-2"></i>Xóa tất cả</a>
                    </div>
                    <div
                        class="alert my-alert alert-sort-filer alert-info text-sm text-white bg-info">
                        <i class="ti ti-info-square mr-2"></i>Có thể chọn nhiều hình để di chuyển
                    </div>
                    <div class="jFiler-items my-jFiler-items jFiler-row">
                        <ul class="jFiler-items-list jFiler-items-grid row scroll-bar"
                            id="jFilerSortable">
                            @foreach ($gallery as $v)
                                @component('component.gallery', [
                                    'value' => $v,
                                    'folder' => $folder,
                                    'col' => 'col-xl-2 col-lg-3 col-md-3 col-sm-4 col-6',
                                ])
                                @endcomponent
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="@asset('assets/admin/filer/jquery.filer.css')">
<link rel="stylesheet" href="@asset('assets/admin/filer/jquery.filer-dragdropbox-theme.css')">
@endpush
@push('scripts')
 <script src="@asset('assets/admin/sortable/Sortable.js')"></script>
<script src="@asset('assets/admin/filer/jquery.filer.js')"></script>
<script type="text/javascript">
    const ACTIVE_GALLERY_CHECK = {{ !empty($gallery) ? 'true' : 'false' }};
    if ($('#filer-gallery').length) {
        var files = [];
        $('#filer-gallery').filer({
            limit: null,
            maxSize: null,
            extensions: ['jpg', 'png', 'jpeg', 'JPG', 'PNG', 'JPEG', 'Png', 'webp', 'WEBP'],
            changeInput:
                '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner"><div class="jFiler-input-icon"><i class="icon-jfi-cloud-up-o"></i></div><div class="jFiler-input-text"><h3>' +
                'Kéo và thả hình vào đây' +
                '</h3> <span style="display:inline-block; margin: 15px 0">' +
                'hoặc' +
                '</span></div><a class="jFiler-input-choose-btn red">' +
                'chọn hình' +
                '</a></div></div>',
            theme: 'dragdropbox',
            showThumbs: true,
            addMore: true,
            allowDuplicates: false,
            clipBoardPaste: false,
            dragDrop: {
                dragEnter: null,
                dragLeave: null,
                drop: null,
                dragContainer: null,
            },
            captions: {
                button: 'Thêm hình',
                feedback: 'Vui lòng chọn hình ảnh',
                feedback2: 'Những hình đã được chọn',
                drop: 'Kéo hình vào đây để upload',
                removeConfirmation: 'Bạn muốn loại bỏ hình này',
                errors: {
                    filesLimit: 'Chỉ được upload mỗi lần @{{fi-limit}} ' + 'hình ảnh',
                    filesType: 'Chỉ hỗ trợ tệp tin hình ảnh có định dạng' + ': @{{fi-extensions}}',
                    filesSize:
                        'Hình ảnh' +
                        ' @{{fi-name}} ' +
                        'vui lòng upload hình ảnh có kích thước tối đa' +
                        ' @{{fi-maxSize}} MB.',
                    filesSizeAll: 'vui lòng upload hình ảnh có kích thước tối đa' + ' @{{fi-maxSize}} MB.'
                }
            },
            onSelect: function (file) {
                var $fileInputs = $('input[type="file"][name="files[]"]');
                if ($fileInputs.length > 1) {
                    $fileInputs.not(':first').remove();
                }
                if (!fileExists(file)) {
                    files.push(file);
                }
                updateInputFiles();
            },
            onRemove(fileEl, file) {
                var fileName = file.name;
                files = files.filter(function (f) {
                    return f.name !== fileName;
                });
                updateInputFiles();
            },
            afterShow: function () {
                var jFilerItems = $('.my-jFiler-items .jFiler-items-list li.jFiler-item');
                var jFilerItemsLength = 0;
                var jFilerItemsLast = 0;
                if (jFilerItems.length) {
                    jFilerItemsLength = jFilerItems.length;
                    jFilerItemsLast = parseInt(jFilerItems.last().find('input[type=number]').val());
                }
                $('.jFiler-items-list li.jFiler-item').each(function (index) {
                    var colClass = $('.col-filer').val();
                    var parent = $(this).parent();
                    if (!parent.is('#jFilerSortable')) {
                        jFilerItemsLast += 1;
                        $(this).find('input[type=number]').val(jFilerItemsLast);
                    }
                    if (!$(this).hasClass(colClass)) $('li.jFiler-item').addClass(colClass);
                });
            },
            templates: {
                box: '<ul class="jFiler-items-list jFiler-items-grid row scroll-bar"></ul>',
                item:
                    '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-thumb-overlay">\
                                            <div class="jFiler-item-info">\
                                                <div style="display:table-cell;vertical-align: middle;">\
                                                    <span class="jFiler-item-title"><b title="@{{fi-name}}">@{{fi-name}}</b></span>\
                                                    <span class="jFiler-item-others">@{{fi-size2}}</span>\
                                                </div>\
                                            </div>\
                                        </div>\
                                        @{{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li>@{{fi-progressBar}}</li>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                    <input type="number" class="form-control form-control-sm mb-1" name="numb-filer[]" placeholder="' + 'Số thứ tự' + '"/>\
                                    <input type="text" class="form-control form-control-sm" name="name-filer[]" placeholder="Tiêu đề"/>\
                                </div>\
                            </div>\
                        </li>',
                itemAppend:
                    '<li class="jFiler-item">\
                                <div class="jFiler-item-container">\
                                    <div class="jFiler-item-inner">\
                                        <div class="jFiler-item-thumb">\
                                            <div class="jFiler-item-status"></div>\
                                            <div class="jFiler-item-thumb-overlay">\
                                                <div class="jFiler-item-info">\
                                                    <div style="display:table-cell;vertical-align: middle;">\
                                                        <span class="jFiler-item-title"><b title="@{{fi-name}}">@{{fi-name}}</b></span>\
                                                        <span class="jFiler-item-others">@{{fi-size2}}</span>\
                                                    </div>\
                                                </div>\
                                            </div>\
                                            @{{fi-image}}\
                                        </div>\
                                        <div class="jFiler-item-assets jFiler-row">\
                                            <ul class="list-inline pull-left">\
                                                <li><span class="jFiler-item-others">@{{fi-icon}}</span></li>\
                                            </ul>\
                                            <ul class="list-inline pull-right">\
                                                <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                            </ul>\
                                        </div>\
                                        <input type="number" class="form-control form-control-sm mb-1" name="numb-filer[]" placeholder="' + 'Số thứ tự' + '"/>\
                                        <input type="text" class="form-control form-control-sm" name="name-filer[]" placeholder="Tiêu đề"/>\
                                    </div>\
                                </div>\
                            </li>',
                progressBar: '<div class="bar"></div>',
                itemAppendToEnd: true,
                canvasImage: false,
                removeConfirmation: true,
                _selectors: {
                    list: '.jFiler-items-list',
                    item: '.jFiler-item',
                    progressBar: '.bar',
                    remove: '.jFiler-item-trash-action'
                }
            }
        });
    }
    function updateInputFiles() {
        const dataTransfer = new DataTransfer();
        files.forEach(file => {
            dataTransfer.items.add(file);
        });
        const fileInput = document.getElementById('filer-gallery');
        fileInput.files = dataTransfer.files;
        
    }

    function fileExists(file) {
        return files.some(f => f.name === file.name && f.size === file.size);
    }

    var sortable;

    function createSortFiler() {
        if ($('#jFilerSortable').length) {
            sortable = new Sortable.create(document.getElementById('jFilerSortable'), {
                animation: 600,
                swap: true,
                disabled: true,
                // swapThreshold: 0.25,
                ghostClass: 'ghostclass',
                multiDrag: true,
                selectedClass: 'selected',
                forceFallback: false,
                fallbackTolerance: 3,
                onEnd: function () {
                    /* Get all filer sort */
                    listid = new Array();
                    jFilerItems = $('#jFilerSortable').find('.my-jFiler-item');
                    jFilerItems.each(function (index) {
                        listid.push($(this).data('id'));
                    });

                    /* Update number */
                    var id_parent = ID;
                    var com = COM;
                    var type = TYPE;
                    $.ajax({
                        url: 'filer',
                        type: 'POST',
                        dataType: 'json',
                        async: false,
                        data: {
                            id_parent: id_parent,
                            listid: listid,
                            com: com,
                            type: type,
                            cmd: 'updateNumb',
                            csrf_token: CSRF_TOKEN
                        },
                        success: function (result) {
                            var arrid = result.id;
                            var arrnumb = result.numb;
                            for (var i = 0; i < arrid.length; i++)
                                $('.my-jFiler-item-' + arrid[i])
                                    .find('input[type=number]')
                                    .val(arrnumb[i]);
                        }
                    });
                }
            });
        }
    }

    /* Destroy sort filer */
    function destroySortFiler() {
        try {
            var destroy = sortable.destroy();
        } catch (e) {}
    }

    /* Refresh filer when complete action */
    function refreshFiler() {
        $('.sort-filer, .check-all-filer').removeClass('active');
        $('.sort-filer').attr('disabled', false);
        $('.alert-sort-filer').hide();

        if ($('.check-all-filer').find('i').hasClass('fas fa-check-square')) {
            $('.check-all-filer').find('i').toggleClass('far fa-square fas fa-check-square');
        }

        $('.my-jFiler-items .jFiler-items-list')
            .find('input.filer-checkbox')
            .each(function () {
                $(this).prop('checked', false);
            });
    }

    /* Refresh filer if empty */
    function refreshFilerIfEmpty() {
        var id_parent = ID;
        var com = COM;
        var type = TYPE;
        var colfiler = $('.col-filer').val();
        var actfiler = $('.act-filer').val();
        var cmd = 'refresh';

        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: 'filer',
            async: false,
            data: {
                id_parent: id_parent,
                com: com,
                kind: actfiler,
                type: type,
                colfiler: colfiler,
                cmd: cmd,
                hash: HASH
            },
            success: function (result) {
                $('.jFiler-items-list').first().find('.jFiler-item').remove();
                destroySortFiler();
                $tmp =
                    '<div class="form-group form-group-gallery">' +
                    '<label class="label-filer">' +
                    'Album hiện tại' +
                    ':</label>' +
                    '<div class="action-filer mb-3">' +
                    '<a class="btn  bg-primary text-white check-all-filer mr-1"><i class="far fa-square mr-2"></i>' +
                    'Chọn tất cả' +
                    '</a>' +
                    '<button type="button" class="btn  bg-success text-white sort-filer mr-1"><i class="fas fa-random mr-2"></i>' +
                    'Sắp xếp' +
                    '</button>' +
                    '<a class="btn  bg-danger text-white delete-all-filer"><i class="far fa-trash-alt mr-2"></i>' +
                    'Xóa tất cả' +
                    '</a>' +
                    '</div>' +
                    '<div class="alert my-alert alert-sort-filer alert-info text-sm text-white bg-info"><i class="fas fa-info-circle mr-2"></i>' +
                    'Có thể chọn nhiều hình để di chuyển' +
                    '</div>' +
                    '<div class="jFiler-items my-jFiler-items jFiler-row">' +
                    '<ul class="jFiler-items-list jFiler-items-grid row scroll-bar" id="jFilerSortable">' +
                    result +
                    '</ul></div></div>';
                $('#filer-gallery').parents('.form-group').after($tmp);
                createSortFiler();
            }
        });
    }

    /* Delete filer */
    function deleteFiler(string) {
        var str = string.split(',');
        var id = str[0];
        var folder = str[1];
        var cmd = 'delete';

        $.ajax({
            type: 'POST',
            url: 'filer',
            data: {
                csrf_token: CSRF_TOKEN,
                id: id,
                folder: folder,
                cmd: cmd
            }
        });

        $('.my-jFiler-item-' + id).remove();

        if ($('.my-jFiler-items ul li').length == 0) {
            $('.form-group-gallery').remove();
        }
    }

    /* Delete all filer */
    function deleteAllFiler(folder) {
        var listid = '';
        var cmd = 'delete-all';

        $('input.filer-checkbox').each(function () {
            if (this.checked) listid = listid + ',' + this.value;
        });

        listid = listid.substr(1);

        if (listid == '') {
            notifyDialog('Bạn phải chọn ít nhất 1 mục để xóa');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: 'filer',
            data: {
                csrf_token: CSRF_TOKEN,
                listid: listid,
                folder: folder,
                cmd: cmd
            }
        });

        listid = listid.split(',');

        for (var i = 0; i < listid.length; i++) {
            $('.my-jFiler-item-' + listid[i]).remove();
        }

        if ($('.my-jFiler-items ul li').length == 0) {
            $('.form-group-gallery').remove();
        }

        refreshFiler();
    }
    /* Sort filer */
    if (ACTIVE_GALLERY_CHECK) {
        createSortFiler();
    }

    /* Check all filer */
    $('body').on('click', '.check-all-filer', function () {
        var parentFiler = $('.my-jFiler-items .jFiler-items-list');
        var input = parentFiler.find('input.filer-checkbox');
        var jFilerItems = $('#jFilerSortable').find('.my-jFiler-item');

        $(this).find('i').toggleClass('ti ti-square ti ti-square-check');
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $('.sort-filer').removeClass('active');
            $('.sort-filer').attr('disabled', false);
            input.each(function () {
                $(this).prop('checked', false);
            });
        } else {
            sortable.option('disabled', true);
            $(this).addClass('active');
            $('.sort-filer').attr('disabled', true);
            $('.alert-sort-filer').hide();
            $('.my-jFiler-item-trash').show();
            input.each(function () {
                $(this).prop('checked', true);
            });
            jFilerItems.each(function () {
                $(this).find('input').attr('disabled', false);
            });
            jFilerItems.each(function () {
                $(this).removeClass('moved');
            });
        }
    });

    /* Check filer */
    $('body').on('click', '.filer-checkbox', function () {
        var input = $('.my-jFiler-items .jFiler-items-list').find('input.filer-checkbox:checked');

        if (input.length) $('.sort-filer').attr('disabled', true);
        else $('.sort-filer').attr('disabled', false);
    });

    /* Sort filer */
    $('body').on('click', '.sort-filer', function () {
        var jFilerItems = $('#jFilerSortable').find('.my-jFiler-item');

        if ($(this).hasClass('active')) {
            sortable.option('disabled', true);
            $(this).removeClass('active');
            $('.alert-sort-filer').hide();
            $('.my-jFiler-item-trash').show();
            jFilerItems.each(function () {
                $(this).find('input').attr('disabled', false);
                $(this).removeClass('moved');
            });
        } else {
            sortable.option('disabled', false);
            $(this).addClass('active');
            $('.alert-sort-filer').show();
            $('.my-jFiler-item-trash').hide();
            jFilerItems.each(function () {
                $(this).find('input').attr('disabled', true);
                $(this).addClass('moved');
            });
        }
    });

    /* Delete filer */
    $('body').on('click', '.my-jFiler-item-trash', function () {
        var id = $(this).data('id');
        var folder = $(this).data('folder');
        var str = id + ',' + folder;
        confirmDialog('delete-filer', 'Bạn muốn xóa hình ảnh này', str);
    });

    /* Delete all filer */
    $('body').on('click', '.delete-all-filer', function () {
        var folder = $('.folder-filer').val();
        confirmDialog('delete-all-filer', 'Bạn muốn xóa các hình ảnh đã chọn', folder);
    });

    /* Change info filer */
    $('body').on('change', '.my-jFiler-item-info', function () {
        var id = $(this).data('id');
        var info = $(this).data('info');
        var value = $(this).val();
        var cmd = 'edit';

        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: 'filer',
            async: false,
            data: {
                id: id,
                info: info,
                value: value,
                cmd: cmd,
                csrf_token: CSRF_TOKEN
            },
            success: function (result) {
                // destroySortFiler();
                // $('#jFilerSortable').html(result);
            }
        });

        return false;
    });
</script>
@endpush
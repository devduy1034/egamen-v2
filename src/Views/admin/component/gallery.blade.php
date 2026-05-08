@php
    $galleryThumb = $thumb ?? '300x300x2';
    $fallbackImage = assets('assets/images/noimage.png');
    $galleryImage = !empty($value['photo']) ? assets_photo($folder, $galleryThumb, $value['photo'], 'thumbs') : $fallbackImage;
@endphp

<li class="jFiler-item my-jFiler-item my-jFiler-item-<?= $value['id'] ?> <?= $col ?>" data-id="<?= $value['id'] ?>">
    <div class="jFiler-item-container">
        <div class="jFiler-item-inner">
            <div class="jFiler-item-thumb">
                <div class="jFiler-item-thumb-image">
                    <img class="img-preview" src="{{ $galleryImage }}" loading="lazy" decoding="async"
                        onerror="this.src='{{ $fallbackImage }}';" alt="{{ $value['namevi'] }}"
                        title="{{ $value['namevi'] }}" />
                    <i class="fas fa-arrows-alt"></i>
                </div>
            </div>
            <div class="jFiler-item-assets jFiler-row">
                <ul class="list-inline pull-right d-flex align-items-center justify-content-between">
                    <li class="ml-1">
                        <a class="icon-jfi-trash jFiler-item-trash-action my-jFiler-item-trash"
                            data-id="<?= $value['id'] ?>" data-folder="<?= $folder ?>"></a>
                    </li>
                    <li class="mr-1">
                        <div class="custom-control custom-checkbox d-inline-block align-middle text-md">
                            <input type="checkbox" class="custom-control-input filer-checkbox"
                                id="filer-checkbox-<?= $value['id'] ?>" value="<?= $value['id'] ?>">
                            <label for="filer-checkbox-<?= $value['id'] ?>"
                                class="custom-control-label font-weight-normal">Chọn</label>
                        </div>
                    </li>
                </ul>
            </div>
            <input type="number" class="form-control form-control-sm my-jFiler-item-info rounded mb-1 text-sm"
                value="<?= $value['numb'] ?>" placeholder="Số thứ tự" data-info="numb" data-id="<?= $value['id'] ?>" />
            <input type="text" class="form-control form-control-sm my-jFiler-item-info rounded text-sm"
                value="<?= $value['namevi'] ?>" placeholder="Tiêu đề" data-info="namevi" data-id="<?= $value['id'] ?>" />
        </div>
    </div>
</li>

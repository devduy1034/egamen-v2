<div class="product">
    @php
        $gallery = $product->getPhotos ?? collect();
        if ($gallery instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            $gallery = $gallery->where('type', 'san-pham')->orderBy('numb', 'asc')->get();
        }
        $gallery =
            $gallery instanceof \Illuminate\Support\Collection ? $gallery->values() : collect($gallery)->values();
        $photoW = config('type.product.' . $product['type'] . '.images.photo.width');
        $photoH = config('type.product.' . $product['type'] . '.images.photo.height');
        $photoZ = config('type.product.' . $product['type'] . '.images.photo.opt');
        $photoSize = $photoW . 'x' . $photoH . 'x' . $photoZ;
        $thumb = config('type.product.' . $product['type'] . '.images.photo.thumb');
        $galleryThumb = config('type.product.' . $product['type'] . '.gallery.san-pham.photo_thumb') ?? $thumb;

        $colorItems = collect();
        $firstColor = null;
        if (!empty($product['properties'])) {
            $colorList = \LARAVEL\Models\PropertiesListModel::where('slugvi', 'mau')->first();
            if (!empty($colorList)) {
                $propIds = array_filter(explode(',', $product['properties']));
                $colors = \LARAVEL\Models\PropertiesModel::where('id_list', $colorList->id)
                    ->whereIn('id', $propIds)
                    ->orderBy('numb', 'asc')
                    ->get();
                $propertiesRows = \LARAVEL\Models\ProductPropertiesModel::where('id_parent', $product['id'])->get();
                foreach ($colors as $color) {
                    $row = $propertiesRows->first(function ($item) use ($color) {
                        $ids = !empty($item->id_properties) ? explode(',', $item->id_properties) : [];
                        return in_array($color->id, $ids);
                    });
                    $photo = null;
                    if (!empty($row?->id_photo)) {
                        $photo = \LARAVEL\Models\GalleryModel::find($row->id_photo);
                    }
                    if (!empty($photo?->photo)) {
                        $colorItems->push([
                            'color' => $color,
                            'photo' => $photo->photo,
                        ]);
                    }
                }
            }
        }
        $firstColor = $colorItems->first();
        $mainPhoto = $firstColor['photo'] ?? ($product['photo'] ?? '');
        $hoverPhoto = $product['icon'] ?? '';
    @endphp
    <div class="pic-product">
        <a class="scale-img img block" href="{{ $product[$sluglang] }}" title="{{ $product['name' . $lang] }}">
            @component('component.image', [
                'class' => 'w-100 product-main-img' . (!empty($hoverPhoto) ? ' first_img' : ''),
                'w' => $photoW,
                'h' => $photoH,
                'z' => $photoZ,
                'loading' => 'lazy',
                'fetchpriority' => 'low',
                'decoding' => 'async',
                'is_watermarks' => true,
                'destination' => 'product',
                'image' => $mainPhoto,
                'alt' => $product['name' . $lang] ?? '',
            ])
            @endcomponent
            @if (!empty($hoverPhoto))
                @component('component.image', [
                    'class' => 'w-100 second_img',
                    'w' => config('type.product.' . $product['type'] . '.images.icon.width'),
                    'h' => config('type.product.' . $product['type'] . '.images.icon.height'),
                    'z' => config('type.product.' . $product['type'] . '.images.icon.opt'),
                    'loading' => 'lazy',
                    'fetchpriority' => 'low',
                    'decoding' => 'async',
                    'is_watermarks' => true,
                    'destination' => 'product',
                    'image' => $hoverPhoto ?? '',
                    'alt' => $product['name' . $lang] ?? '',
                ])
                @endcomponent
            @endif
        </a>
        <div class="product-actions">
            <a class="product-action" href="{{ $product[$sluglang] }}" type="button" aria-label="Them vao gio">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
            <a class="product-action js-quick-view" href="{{ $product[$sluglang] }}"
                data-url="{{ $product[$sluglang] }}" aria-label="Xem nhanh sản phẩm">
                <i class="fa-regular fa-eye"></i>
            </a>
            <button class="product-action js-wishlist-toggle" type="button" data-product-id="{{ (int) $product['id'] }}"
                data-variant-id="" aria-label="Yeu thich">
                <i class="bi bi-heart"></i>
            </button>
        </div>
    </div>
    <div class="info-product">
        <h3 class="name-product">
            <a class="text-split text-decoration-none" href="{{ $product[$sluglang] }}"
                title="{{ $product['name' . $lang] }}">{{ $product['name' . $lang] }}</a>
        </h3>
        <div class="price-product">
            @if (empty($product['sale_price']))
                @if (empty($product['regular_price']))
                    <span class="price-new">Liên hệ</span>
                @else
                    <span class="price-new">{{ Func::formatMoney($product['regular_price']) }}</span>
                @endif
            @else
                <span class="price-new">{{ Func::formatMoney($product['sale_price']) }}</span>
                <span class="price-old">{{ Func::formatMoney($product['regular_price']) }}</span>
                <span class="price-per">-{{ $product->discount }}%</span>
            @endif
        </div>
    </div>
    @if ($colorItems->isNotEmpty())
        <div class="product-colors">
            @foreach ($colorItems->take(6) as $k => $item)
                <button class="product-color {{ $k === 0 ? 'active' : '' }}" type="button"
                    data-image="{{ assets_photo('product', $photoSize, $item['photo'] ?? '', 'watermarks') }}"
                    aria-label="{{ $product['name' . $lang] }}">
                    <span class="product-color__dot"
                        style="background-image: url('{{ assets_photo('product', $galleryThumb, $item['photo'] ?? '', 'thumbs') }}');"></span>
                </button>
            @endforeach
        </div>
    @endif
</div>


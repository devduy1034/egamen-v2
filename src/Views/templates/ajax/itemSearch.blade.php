@if (!empty($productAjax) && count($productAjax) > 0)
    <div class="nav-search-result-list">
    @foreach ($productAjax as $product)
        @php
            $name = $product['namevi'] ?? '';
            $kw = $keyword ?? '';
            $safeName = e($name);
            $highlightedName = $safeName;
            if ($kw !== '') {
                $replaced = preg_replace(
                    '/(' . preg_quote($kw, '/') . ')/iu',
                    '<span class="nav-search-highlight">$1</span>',
                    $safeName
                );
                $highlightedName = is_string($replaced) ? $replaced : $safeName;
            }
        @endphp
        <a href="{{ url('slugweb', ['slug' => $product['slugvi']]) }}" class="nav-search-result-item" data-product-id="{{ (int) ($product['id'] ?? 0) }}" data-position="{{ $loop->iteration }}" data-query="{{ e($kw) }}">
            <div class="nav-search-result-thumb">
                <img onerror="this.src='{{ thumbs('thumbs/60x60x1/assets/images/noimage.png') }}';"
                     src="{{ assets_photo('product', '60x60x1', $product['photo'], 'thumbs') }}"
                     alt="{{ $name }}">
            </div>
            <div class="nav-search-result-name">
                <span class="nav-search-result-name-text">{!! $highlightedName !!}</span>
            </div>
            <div class="nav-search-result-arrow" aria-hidden="true">
                <i class="bi bi-arrow-up-left"></i>
            </div>
        </a>
    @endforeach
    </div>
@else
    <div class="nav-search-result-empty">
        Không tìm thấy sản phẩm phù hợp.
    </div>
@endif

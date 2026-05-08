<section class="account-panel {{ $activeSection === 'wishlist' ? 'is-active' : '' }}">
    <div class="account-panel__head">
        <h2 class="account-panel__title">Sản phẩm yêu thích</h2>
    </div>

    <div class="account-wishlist-list">
        @forelse (($wishlistItems ?? []) as $item)
            <article class="account-wishlist-card {{ empty($item['exists']) ? 'is-missing' : '' }}">
                <div class="account-wishlist-thumb">
                    @if (!empty($item['url']) && !empty($item['photo_url']))
                        <a href="{{ $item['url'] }}" title="{{ $item['name'] ?? '' }}">
                            <img src="{{ $item['photo_url'] }}" alt="{{ $item['name'] ?? '' }}">
                        </a>
                    @elseif (!empty($item['photo_url']))
                        <img src="{{ $item['photo_url'] }}" alt="{{ $item['name'] ?? '' }}">
                    @else
                        <span class="account-wishlist-thumb__empty">N/A</span>
                    @endif
                </div>
                <div class="account-wishlist-content">
                    <p class="account-wishlist-title">
                        @if (!empty($item['url']))
                            <a href="{{ $item['url'] }}">{{ $item['name'] ?? 'Sản phẩm' }}</a>
                        @else
                            {{ $item['name'] ?? 'Sản phẩm không còn tồn tại' }}
                        @endif
                    </p>
                    @if (!empty($item['variant_name']))
                        <p class="account-wishlist-meta">Phân loại: {{ $item['variant_name'] }}</p>
                    @endif

                    @if (empty($item['exists']))
                        <p class="account-wishlist-state account-wishlist-state--missing">Sản phẩm không còn tồn tại</p>
                    @else
                        <div class="account-wishlist-price">
                            <strong>{{ $item['price_current_text'] ?? 'Liên hệ' }}</strong>
                            @if (!empty($item['price_sale']) && !empty($item['price_regular']) && (float) $item['price_regular'] > (float) $item['price_sale'])
                                <span>{{ $item['price_regular_text'] ?? '' }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="account-wishlist-actions">
                        @if (!empty($item['can_add_to_cart']))
                            <button type="button" class="btn account-btn account-btn--outline js-wishlist-add-cart"
                                data-product-id="{{ (int) ($item['product_id'] ?? 0) }}"
                                data-variant-id="{{ $item['variant_id'] ?? '' }}"
                                data-product-name="{{ $item['name'] ?? '' }}"
                                data-product-photo="{{ $item['photo_url'] ?? '' }}">
                                Thêm vào giỏ
                            </button>
                        @endif
                        <button type="button" class="btn account-btn account-btn--outline js-wishlist-remove"
                            data-wishlist-id="{{ (int) ($item['wishlist_id'] ?? 0) }}"
                            data-product-id="{{ (int) ($item['product_id'] ?? 0) }}"
                            data-variant-id="{{ $item['variant_id'] ?? '' }}">
                            Xóa
                        </button>
                    </div>
                    @if (!empty($item['exists']) && empty($item['can_add_to_cart']))
                        <p class="account-wishlist-state account-wishlist-state--missing js-wishlist-out-of-stock">Sản phẩm đã hết hàng</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="alert alert-info">Bạn chưa có sản phẩm yêu thích nào.</div>
        @endforelse
    </div>
</section>

@php
    $orderDetailApiUrl = url('user.account.orders.detail');
    $orderCancelApiUrl = url('user.account.orders.cancel');
    $commentReviewApiUrl = rtrim((string) config('app.site_path'), '/') . '/comment/add-comment';
    $csrfToken = csrf_token();
    $orderKeywordValue = trim((string) ($orderKeyword ?? ''));
    $orderCurrentPage = is_object($orders ?? null) && method_exists($orders, 'currentPage') ? (int) $orders->currentPage() : 1;
    $deliveredOrderStatusId = (int) ($deliveredOrderStatusId ?? 0);
@endphp

<section class="account-panel {{ $activeSection === 'orders' ? 'is-active' : '' }}">
    <div class="account-panel__head">
        <h2 class="account-panel__title">Quản lý đơn hàng</h2>
    </div>

    <div class="account-order-tabs">
        <a class="{{ (int) ($orderStatusFilter ?? 0) === 0 ? 'is-active' : '' }}"
            href="{{ url('user.account', null, ['section' => 'orders', 'order_code' => $orderKeywordValue]) }}">Tất cả</a>
        @foreach ($orderStatuses ?? [] as $status)
            <a class="{{ (int) ($orderStatusFilter ?? 0) === (int) $status->id ? 'is-active' : '' }}"
                href="{{ url('user.account', null, ['section' => 'orders', 'order_status' => (int) $status->id, 'order_code' => $orderKeywordValue]) }}">
                {{ $status->namevi ?? 'Trạng thái ' . $status->id }}
            </a>
        @endforeach
    </div>

    <form class="account-order-search" method="get" action="{{ url('user.account') }}">
        <input type="hidden" name="section" value="orders">
        @if ((int) ($orderStatusFilter ?? 0) > 0)
            <input type="hidden" name="order_status" value="{{ (int) $orderStatusFilter }}">
        @endif
        <input type="text" class="form-control account-order-search__input" name="order_code"
            value="{{ $orderKeywordValue }}" placeholder="Tìm mã đơn hàng">
        <button type="submit" class="btn account-btn">Tìm</button>
        @if ($orderKeywordValue !== '')
            <a class="btn account-btn account-btn--outline"
                href="{{ url('user.account', null, ['section' => 'orders', 'order_status' => (int) ($orderStatusFilter ?? 0)]) }}">
                Xóa lọc
            </a>
        @endif
    </form>

    @forelse (($orders ?? []) as $order)
        @php
            $items = is_array($order->order_detail ?? null) ? array_values($order->order_detail) : [];
            $firstItem = $items[0] ?? null;
            $firstPhoto = !empty($firstItem)
                ? (string) (data_get($firstItem, 'options.variantPhoto') ??
                    (data_get($firstItem, 'options.itemProduct.photo') ?? ''))
                : '';
            $firstPhotoUrl = !empty($firstPhoto) ? assets_photo('product', '100x100x1', $firstPhoto, 'thumbs') : '';
            $firstProperties = data_get($firstItem, 'options.properties', []);
            if ($firstProperties instanceof \Illuminate\Support\Collection) {
                $firstProperties = $firstProperties->toArray();
            }
            if (!is_array($firstProperties)) {
                $firstProperties = [];
            }
            $firstPropertyNames = collect($firstProperties)
                ->map(function ($property) {
                    return trim((string) (data_get($property, 'namevi') ?? (data_get($property, 'name') ?? '')));
                })
                ->filter()
                ->values()
                ->all();
            $firstProductCode = '';
            $firstPropertyIds = collect($firstProperties)
                ->map(function ($property) {
                    return (int) (data_get($property, 'id') ?? 0);
                })
                ->filter()
                ->values()
                ->all();
            $firstProductId = (int) (data_get($firstItem, 'options.itemProduct.id') ?? 0);
            $firstProductType = trim((string) (data_get($firstItem, 'options.itemProduct.type') ?? 'san-pham'));
            if ($firstProductType === '') {
                $firstProductType = 'san-pham';
            }
            $firstProductName = trim((string) ($firstItem['name'] ?? 'Sản phẩm'));
            if ($firstProductId > 0 && !empty($firstPropertyIds)) {
                $variantCodeQuery = \LARAVEL\Models\ProductPropertiesModel::select('code')->where(
                    'id_parent',
                    $firstProductId,
                );
                foreach ($firstPropertyIds as $propertyId) {
                    $variantCodeQuery->whereRaw('FIND_IN_SET(?, id_properties)', [$propertyId]);
                }
                $variantCodeQuery->whereRaw(
                    "(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?",
                    [count($firstPropertyIds)],
                );
                $variantCodeRow = $variantCodeQuery->first();
                if (!empty($variantCodeRow?->code)) {
                    $firstProductCode = trim((string) $variantCodeRow->code);
                }
            }
            if ($firstProductCode === '') {
                $firstProductCode = trim((string) (data_get($firstItem, 'options.productCode') ?? ''));
            }
            if ($firstProductCode === '') {
                $firstProductCode = trim((string) (data_get($firstItem, 'options.itemProduct.code') ?? ''));
            }
            if ($firstProductCode === '' && $firstProductId > 0) {
                $firstProductCode = trim(
                    (string) (\LARAVEL\Models\ProductModel::where('id', $firstProductId)->value('code') ?? ''),
                );
            }
            $firstPropsAndCode = [];
            if (!empty($firstPropertyNames)) {
                $firstPropsAndCode[] = implode(', ', $firstPropertyNames);
            }
            if (!empty($firstProductCode)) {
                $firstPropsAndCode[] = __('web.code') . ': ' . $firstProductCode;
            }
            $createdAt = is_numeric($order->created_at ?? null)
                ? date('d/m/Y H:i', (int) $order->created_at)
                : (!empty($order->created_at)
                    ? date('d/m/Y H:i', strtotime((string) $order->created_at))
                    : '-');
            $isSelectedOrder = !empty($selectedOrder) && (int) ($selectedOrder->id ?? 0) === (int) ($order->id ?? 0);
            $canCancelOrder = (int) ($order->order_status ?? 0) === 1;
            $isDeliveredOrder = $deliveredOrderStatusId > 0 && (int) ($order->order_status ?? 0) === $deliveredOrderStatusId;
            $paymentMethodName = trim((string) ($order->getPayment->namevi ?? ''));
            $detailAnchor = '#order-card-' . (int) ($order->id ?? 0);
            $detailFallbackUrl =
                ($isSelectedOrder
                    ? url('user.account', null, [
                        'section' => 'orders',
                        'order_status' => (int) ($orderStatusFilter ?? 0),
                        'order_code' => $orderKeywordValue,
                        'page' => $orderCurrentPage,
                    ])
                    : url('user.account', null, [
                        'section' => 'orders',
                        'order_status' => (int) ($orderStatusFilter ?? 0),
                        'order_code' => $orderKeywordValue,
                        'order_id' => (int) $order->id,
                        'page' => $orderCurrentPage,
                    ])) . $detailAnchor;
        @endphp
        <article class="account-order-card {{ $isSelectedOrder ? 'is-expanded' : '' }}"
            id="order-card-{{ (int) ($order->id ?? 0) }}" data-order-id="{{ (int) ($order->id ?? 0) }}">
            <div class="account-order-card__head">
                <strong>Đơn hàng #{{ $order->code ?? $order->id }}</strong>
                <span class="account-order-status">{{ $order->getStatus->namevi ?? 'Đang xử lý' }}</span>
            </div>
            <p>Ngày đặt: {{ $createdAt }} -
                @if ($paymentMethodName !== '')
                    Hình thức thanh toán: {{ $paymentMethodName }}
                @endif
            </p>
            @if (!empty($firstItem))
                <div class="account-order-item">
                    <div class="account-order-thumb">
                        @if (!empty($firstPhotoUrl))
                            <img src="{{ $firstPhotoUrl }}" alt="{{ $firstItem['name'] ?? 'Sản phẩm' }}"
                                loading="lazy" onerror="this.remove()">
                        @endif
                    </div>
                    <div>
                        <p class="mb-0 fw-bold">{{ $firstItem['name'] ?? 'Sản phẩm' }}</p>
                        @if (!empty($firstPropsAndCode))
                            <p class="account-order-props">{{ implode(' | ', $firstPropsAndCode) }}</p>
                        @endif
                        <p>SL: {{ $firstItem['qty'] ?? 1 }} - Giá:
                            {{ \Func::formatMoney((float) ($firstItem['price'] ?? 0)) }}</p>
                        @if ($isDeliveredOrder && $firstProductId > 0)
                            <button type="button"
                                class="btn account-btn account-btn--outline account-order-review-trigger js-order-review-trigger"
                                data-product-id="{{ $firstProductId }}" data-product-type="{{ $firstProductType }}"
                                data-product-name="{{ $firstProductName }}" data-product-photo="{{ $firstPhotoUrl }}">
                                Đánh giá sản phẩm
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <div class="account-order-card__foot account-order-summary">
                <strong>Tổng tiền: {{ \Func::formatMoney((float) ($order->total_price ?? 0)) }}</strong>
                <a class="btn account-btn account-btn--outline js-order-toggle {{ $isSelectedOrder ? 'is-active' : '' }}"
                    href="{{ $detailFallbackUrl }}" data-api-url="{{ $orderDetailApiUrl }}"
                    data-order-id="{{ (int) ($order->id ?? 0) }}" data-label-open="Ẩn bớt"
                    data-label-close="Xem chi tiết" aria-expanded="{{ $isSelectedOrder ? 'true' : 'false' }}">
                    {{ $isSelectedOrder ? 'Ẩn bớt' : 'Xem chi tiết' }}
                </a>
            </div>

            <div class="account-order-detail js-order-detail {{ $isSelectedOrder ? 'is-open' : '' }}"
                id="order-detail-{{ (int) ($order->id ?? 0) }}" data-loaded="{{ $isSelectedOrder ? '1' : '0' }}"
                @if (!$isSelectedOrder) hidden @endif>
                <div class="account-order-detail__content js-order-detail-content">
                    @if ($isSelectedOrder && !empty($selectedOrder) && (int) ($selectedOrder->id ?? 0) === (int) ($order->id ?? 0))
                        @include('account.partials.order-detail', [
                            'selectedOrder' => $selectedOrder,
                            'deliveredOrderStatusId' => $deliveredOrderStatusId,
                        ])
                    @endif
                </div>
                <div class="account-order-detail__tools">
                    @if ($canCancelOrder)
                        <button type="button"
                            class="btn account-btn account-btn--outline account-btn--danger js-order-cancel"
                            data-api-url="{{ $orderCancelApiUrl }}" data-order-id="{{ (int) ($order->id ?? 0) }}"
                            data-csrf-token="{{ $csrfToken }}">
                            Hủy đơn
                        </button>
                    @endif
                    <a class="btn account-btn account-btn--outline js-order-toggle {{ $isSelectedOrder ? 'is-active' : '' }}"
                        href="{{ $detailFallbackUrl }}" data-api-url="{{ $orderDetailApiUrl }}"
                        data-order-id="{{ (int) ($order->id ?? 0) }}" data-label-open="Ẩn bớt"
                        data-label-close="Xem chi tiết" aria-expanded="{{ $isSelectedOrder ? 'true' : 'false' }}">
                        {{ $isSelectedOrder ? 'Ẩn bớt' : 'Xem chi tiết' }}
                    </a>
                </div>
            </div>
        </article>
    @empty
        <div class="alert alert-info">
            {{ $orderKeywordValue !== '' ? 'Không tìm thấy đơn hàng phù hợp.' : 'Bạn chưa có đơn hàng nào.' }}
        </div>
    @endforelse

    @if (is_object($orders ?? null) && method_exists($orders, 'lastPage') && (int) $orders->lastPage() > 1)
        @php
            $currentPage = max(1, (int) $orders->currentPage());
            $lastPage = max(1, (int) $orders->lastPage());
            $startPage = max(1, $currentPage - 2);
            $endPage = min($lastPage, $currentPage + 2);
        @endphp
        <nav class="account-order-pagination" aria-label="Order pagination">
            @if (!$orders->onFirstPage())
                <a href="{{ $orders->url($currentPage - 1) }}" class="account-order-pagination__item" aria-label="Prev page">&laquo;</a>
            @else
                <span class="account-order-pagination__item is-disabled" aria-hidden="true">&laquo;</span>
            @endif

            @for ($page = $startPage; $page <= $endPage; $page++)
                <a href="{{ $orders->url($page) }}"
                    class="account-order-pagination__item {{ $page === $currentPage ? 'is-active' : '' }}">{{ $page }}</a>
            @endfor

            @if ($orders->hasMorePages())
                <a href="{{ $orders->url($currentPage + 1) }}" class="account-order-pagination__item" aria-label="Next page">&raquo;</a>
            @else
                <span class="account-order-pagination__item is-disabled" aria-hidden="true">&raquo;</span>
            @endif
        </nav>
    @endif

    <div class="account-review-modal js-order-review-modal" hidden>
        <div class="account-review-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="review-order-title">
            <button type="button" class="account-review-modal__close js-review-close" aria-label="Đóng">&times;</button>
            <h3 id="review-order-title" class="account-review-modal__title">Đánh giá sản phẩm</h3>
            <form class="account-review-form js-order-review-form" data-api-url="{{ $commentReviewApiUrl }}"
                enctype="multipart/form-data">
                <div class="account-review-product">
                    <div class="account-review-product__thumb js-review-product-thumb" hidden>
                        <img src="" alt="" class="js-review-product-image" loading="lazy">
                    </div>
                    <div>
                        <p class="account-review-product__label">Sản phẩm đang đánh giá</p>
                        <p class="account-review-product__name js-review-product-name">-</p>
                    </div>
                </div>

                <div class="account-review-stars">
                    <p class="account-review-field__label">Chọn số sao</p>
                    <div class="account-review-stars__list">
                        <button type="button" class="account-review-stars__star js-review-star" data-star-value="1"
                            aria-label="1 sao">★</button>
                        <button type="button" class="account-review-stars__star js-review-star" data-star-value="2"
                            aria-label="2 sao">★</button>
                        <button type="button" class="account-review-stars__star js-review-star" data-star-value="3"
                            aria-label="3 sao">★</button>
                        <button type="button" class="account-review-stars__star js-review-star" data-star-value="4"
                            aria-label="4 sao">★</button>
                        <button type="button" class="account-review-stars__star js-review-star" data-star-value="5"
                            aria-label="5 sao">★</button>
                    </div>
                    <input type="hidden" name="dataReview[star]" class="js-review-star-input" value="">
                </div>

                <div class="account-review-field">
                    <label class="account-review-field__label" for="account-review-content">Nội dung đánh giá</label>
                    <textarea id="account-review-content" class="form-control js-review-content" name="dataReview[content]" rows="5"
                        placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này"></textarea>
                </div>

                <div class="account-review-field">
                    <label class="account-review-field__label" for="account-review-photo">Hình ảnh (tối đa 3 hình)</label>
                    <input id="account-review-photo" class="form-control js-review-photo-input" type="file"
                        name="review-file-photo[]" accept="image/*" multiple>
                </div>

                <p class="account-review-form__error js-review-error" hidden></p>

                <input type="hidden" name="dataReview[id_variant]" class="js-review-product-id" value="">
                <input type="hidden" name="dataReview[type]" class="js-review-product-type" value="">
                <input type="hidden" name="csrf_token" value="{{ $csrfToken }}">

                <div class="account-review-form__actions">
                    <button type="button" class="btn account-btn account-btn--outline js-review-close">Đóng</button>
                    <button type="submit" class="btn account-btn js-review-submit">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>

    <div class="account-cancel-modal js-order-cancel-modal" hidden>
        <div class="account-cancel-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="cancel-order-title">
            <h3 id="cancel-order-title">Hủy đơn hàng</h3>
            <p>Chọn lý do hủy đơn</p>
            <select class="form-control js-cancel-reason-select">
                <option value="">Chọn lý do</option>
                <option value="Đặt nhầm đơn">Đặt nhầm đơn</option>
                <option value="Muốn đổi sản phẩm/kích thước">Muốn đổi sản phẩm/kích thước</option>
                <option value="Thời gian giao hàng quá lâu">Thời gian giao hàng quá lâu</option>
                <option value="Đổi ý, chưa còn nhu cầu">Đổi ý, chưa còn nhu cầu</option>
                <option value="other">Khác (nhập lý do)</option>
            </select>
            <textarea class="form-control js-cancel-reason-text" rows="3" placeholder="Nhập lý do hủy đơn" hidden></textarea>
            <p class="account-cancel-modal__error js-cancel-error" hidden></p>
            <div class="account-cancel-modal__actions">
                <button type="button" class="btn account-btn account-btn--outline js-cancel-close">Đóng</button>
                <button type="button" class="btn account-btn js-cancel-submit">Xác nhận hủy</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var panel = document.querySelector('.account-panel.is-active');
            if (!panel) return;

            var transitionMs = 260;
            var labelOpen = 'Ẩn bớt';
            var labelClose = 'Xem chi tiết';
            var cancelModal = panel.querySelector('.js-order-cancel-modal');
            var reviewModal = panel.querySelector('.js-order-review-modal');
            var reviewForm = reviewModal ? reviewModal.querySelector('.js-order-review-form') : null;
            var reviewError = reviewModal ? reviewModal.querySelector('.js-review-error') : null;
            var reviewProductName = reviewModal ? reviewModal.querySelector('.js-review-product-name') : null;
            var reviewProductId = reviewModal ? reviewModal.querySelector('.js-review-product-id') : null;
            var reviewProductType = reviewModal ? reviewModal.querySelector('.js-review-product-type') : null;
            var reviewStarInput = reviewModal ? reviewModal.querySelector('.js-review-star-input') : null;
            var reviewPhotoInput = reviewModal ? reviewModal.querySelector('.js-review-photo-input') : null;
            var reviewProductThumb = reviewModal ? reviewModal.querySelector('.js-review-product-thumb') : null;
            var reviewProductImage = reviewModal ? reviewModal.querySelector('.js-review-product-image') : null;
            var reviewSubmitBtn = reviewModal ? reviewModal.querySelector('.js-review-submit') : null;
            var cancelReasonSelect = cancelModal ? cancelModal.querySelector('.js-cancel-reason-select') : null;
            var cancelReasonText = cancelModal ? cancelModal.querySelector('.js-cancel-reason-text') : null;
            var cancelError = cancelModal ? cancelModal.querySelector('.js-cancel-error') : null;
            var cancelCloseBtn = cancelModal ? cancelModal.querySelector('.js-cancel-close') : null;
            var cancelSubmitBtn = cancelModal ? cancelModal.querySelector('.js-cancel-submit') : null;
            var cancelState = {
                card: null,
                button: null
            };

            function notifyAccount(message, status) {
                var text = String(message || '').trim();
                if (!text) return;
                if (typeof showNotify === 'function') {
                    showNotify(text, 'Thông báo', status || 'success');
                    return;
                }
                window.alert(text);
            }

            function getButtons(card) {
                if (!card) return [];
                return Array.prototype.slice.call(card.querySelectorAll('.js-order-toggle'));
            }

            function getDetail(card) {
                return card ? card.querySelector('.js-order-detail') : null;
            }

            function getDetailContent(card) {
                var detail = getDetail(card);
                return detail ? detail.querySelector('.js-order-detail-content') : null;
            }

            function setButtonState(buttons, isOpen) {
                if (!buttons || !buttons.length) return;
                buttons.forEach(function(button) {
                    button.classList.toggle('is-active', isOpen);
                    button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    button.textContent = isOpen ? (button.dataset.labelOpen || labelOpen) : (button.dataset
                        .labelClose || labelClose);
                });
            }

            function collapseCard(card) {
                var detail = getDetail(card);
                var buttons = getButtons(card);
                if (!detail) return;

                card.classList.remove('is-expanded');
                setButtonState(buttons, false);

                detail.classList.remove('is-open');
                window.setTimeout(function() {
                    if (!detail.classList.contains('is-open')) {
                        detail.hidden = true;
                    }
                }, transitionMs);
            }

            function expandCard(card) {
                var detail = getDetail(card);
                var buttons = getButtons(card);
                if (!detail) return;

                card.classList.add('is-expanded');
                detail.hidden = false;
                requestAnimationFrame(function() {
                    detail.classList.add('is-open');
                });
                setButtonState(buttons, true);
                window.setTimeout(function() {
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }, 40);
            }

            function collapseOthers(currentCard) {
                panel.querySelectorAll('.account-order-card.is-expanded').forEach(function(card) {
                    if (card !== currentCard) {
                        collapseCard(card);
                    }
                });
            }

            function showLoadError(content) {
                if (!content) return;
                content.innerHTML =
                    '<p class="account-order-loading text-danger">Không thể tải chi tiết đơn hàng.</p>';
            }

            function showCancelError(message) {
                if (!cancelError) return;
                var text = String(message || '').trim();
                cancelError.textContent = text;
                cancelError.hidden = text === '';
            }

            function showReviewError(message) {
                if (!reviewError) return;
                var text = String(message || '').trim();
                reviewError.textContent = text;
                reviewError.hidden = text === '';
            }

            function setReviewStars(value) {
                var starValue = Number(value || 0);
                if (reviewStarInput) {
                    reviewStarInput.value = starValue > 0 ? String(starValue) : '';
                }
                if (!reviewModal) return;
                reviewModal.querySelectorAll('.js-review-star').forEach(function(button) {
                    var currentValue = Number(button.dataset.starValue || 0);
                    button.classList.toggle('is-active', currentValue > 0 && currentValue <= starValue);
                });
            }

            function resetReviewForm() {
                if (reviewForm) {
                    reviewForm.reset();
                }
                if (reviewProductName) {
                    reviewProductName.textContent = '-';
                }
                if (reviewProductId) {
                    reviewProductId.value = '';
                }
                if (reviewProductType) {
                    reviewProductType.value = '';
                }
                if (reviewProductImage) {
                    reviewProductImage.setAttribute('src', '');
                    reviewProductImage.setAttribute('alt', '');
                }
                if (reviewProductThumb) {
                    reviewProductThumb.hidden = true;
                }
                setReviewStars(0);
                showReviewError('');
            }

            function closeReviewModal() {
                if (!reviewModal) return;
                reviewModal.hidden = true;
                document.documentElement.classList.remove('account-review-modal-open');
                document.body.classList.remove('account-review-modal-open');
                resetReviewForm();
            }

            function openReviewModal(button) {
                if (!reviewModal || !button) return;
                resetReviewForm();

                var productId = String(button.dataset.productId || '').trim();
                var productType = String(button.dataset.productType || 'san-pham').trim() || 'san-pham';
                var productName = String(button.dataset.productName || 'Sản phẩm').trim() || 'Sản phẩm';
                var productPhoto = String(button.dataset.productPhoto || '').trim();

                if (reviewProductId) {
                    reviewProductId.value = productId;
                }
                if (reviewProductType) {
                    reviewProductType.value = productType;
                }
                if (reviewProductName) {
                    reviewProductName.textContent = productName;
                }
                if (reviewProductImage) {
                    reviewProductImage.setAttribute('alt', productName);
                }
                if (reviewProductThumb) {
                    reviewProductThumb.hidden = productPhoto === '';
                }
                if (reviewProductImage && productPhoto !== '') {
                    reviewProductImage.setAttribute('src', productPhoto);
                }

                reviewModal.hidden = false;
                document.documentElement.classList.add('account-review-modal-open');
                document.body.classList.add('account-review-modal-open');

                var contentField = reviewForm ? reviewForm.querySelector('.js-review-content') : null;
                if (contentField) {
                    contentField.focus();
                }
            }

            function submitReviewForm(event) {
                event.preventDefault();
                if (!reviewForm) return;

                var apiUrl = String(reviewForm.dataset.apiUrl || '').trim();
                var productId = reviewProductId ? String(reviewProductId.value || '').trim() : '';
                var productType = reviewProductType ? String(reviewProductType.value || '').trim() : '';
                var starValue = reviewStarInput ? String(reviewStarInput.value || '').trim() : '';
                var contentField = reviewForm.querySelector('.js-review-content');
                var contentValue = contentField ? String(contentField.value || '').trim() : '';
                var totalFiles = reviewPhotoInput && reviewPhotoInput.files ? reviewPhotoInput.files.length : 0;

                if (!productId || !productType || !apiUrl) {
                    showReviewError('Không thể xác định sản phẩm cần đánh giá.');
                    return;
                }
                if (!starValue) {
                    showReviewError('Vui lòng chọn số sao đánh giá.');
                    return;
                }
                if (!contentValue) {
                    showReviewError('Vui lòng nhập nội dung đánh giá.');
                    return;
                }
                if (totalFiles > 3) {
                    showReviewError('Bạn chỉ có thể tải lên tối đa 3 hình ảnh.');
                    return;
                }

                if (reviewSubmitBtn) {
                    reviewSubmitBtn.disabled = true;
                }
                showReviewError('');

                var formData = new FormData(reviewForm);

                if (typeof holdonOpen === 'function') {
                    holdonOpen();
                }

                fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(function(response) {
                        return response.json().then(function(payload) {
                            return {
                                ok: response.ok,
                                payload: payload
                            };
                        }).catch(function() {
                            return {
                                ok: response.ok,
                                payload: null
                            };
                        });
                    })
                    .then(function(result) {
                        if (!result.payload) {
                            throw new Error('Không thể gửi đánh giá. Vui lòng thử lại.');
                        }
                        if (!result.ok) {
                            throw new Error(result.payload.message || 'Không thể gửi đánh giá. Vui lòng thử lại.');
                        }

                        if (result.payload.errors) {
                            throw new Error(Array.isArray(result.payload.errors) ? result.payload.errors.join('\n') : String(result.payload.errors));
                        }

                        var autoApproved = !!(result.payload.result && result.payload.result.auto_approved);
                        var successMessage = autoApproved ?
                            (result.payload.result.message || 'Đánh giá của bạn đã được hiển thị.') :
                            'Đánh giá của bạn đã được gửi và đang chờ duyệt.';

                        closeReviewModal();
                        notifyAccount(successMessage, 'success');

                        if (autoApproved) {
                            window.setTimeout(function() {
                                window.location.reload();
                            }, 600);
                        }
                    })
                    .catch(function(error) {
                        showReviewError(error && error.message ? error.message : 'Không thể gửi đánh giá.');
                    })
                    .then(function() {
                        if (reviewSubmitBtn) {
                            reviewSubmitBtn.disabled = false;
                        }
                        if (typeof holdonClose === 'function') {
                            holdonClose();
                        }
                    });
            }

            function toggleCancelReasonText() {
                if (!cancelReasonSelect || !cancelReasonText) return;
                var isOther = String(cancelReasonSelect.value || '') === 'other';
                cancelReasonText.hidden = !isOther;
                if (!isOther) {
                    cancelReasonText.value = '';
                }
            }

            function resetCancelForm() {
                if (cancelReasonSelect) cancelReasonSelect.value = '';
                if (cancelReasonText) {
                    cancelReasonText.value = '';
                    cancelReasonText.hidden = true;
                }
                showCancelError('');
            }

            function closeCancelModal() {
                if (!cancelModal) return;
                cancelModal.hidden = true;
                cancelState.card = null;
                cancelState.button = null;
                resetCancelForm();
            }

            function openCancelModal(button) {
                if (!cancelModal || !button) return;
                cancelState.card = button.closest('.account-order-card');
                cancelState.button = button;
                resetCancelForm();
                cancelModal.hidden = false;
                if (cancelReasonSelect) {
                    cancelReasonSelect.focus();
                }
            }

            function collectCancelReason() {
                if (!cancelReasonSelect) return '';
                var selected = String(cancelReasonSelect.value || '').trim();
                if (!selected) return '';
                if (selected === 'other') {
                    return cancelReasonText ? String(cancelReasonText.value || '').trim() : '';
                }
                return selected;
            }

            function markOrderCanceled(card, statusName) {
                if (!card) return;
                card.querySelectorAll('.js-order-cancel').forEach(function(button) {
                    button.remove();
                });
                var status = card.querySelector('.account-order-status');
                if (status) {
                    status.textContent = statusName || 'Đã hủy';
                }
            }

            function submitCancelOrder() {
                if (!cancelState.button || !cancelState.card) return;
                var reason = collectCancelReason();
                if (!reason) {
                    showCancelError('Vui lòng chọn hoặc nhập lý do hủy đơn.');
                    return;
                }

                var apiUrl = String(cancelState.button.dataset.apiUrl || '').trim();
                var orderId = String(cancelState.button.dataset.orderId || '').trim();
                var csrfToken = String(cancelState.button.dataset.csrfToken || '').trim();
                if (!apiUrl || !orderId || !csrfToken) {
                    showCancelError('Thiếu dữ liệu hủy đơn.');
                    return;
                }

                if (cancelSubmitBtn) cancelSubmitBtn.disabled = true;
                showCancelError('');

                var body = new URLSearchParams();
                body.append('order_id', orderId);
                body.append('reason', reason);
                body.append('csrf_token', csrfToken);

                fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: body.toString()
                    })
                    .then(function(response) {
                        return response.json().then(function(payload) {
                            return {
                                ok: response.ok,
                                payload: payload
                            };
                        }).catch(function() {
                            return {
                                ok: response.ok,
                                payload: null
                            };
                        });
                    })
                    .then(function(result) {
                        if (!result.ok || !result.payload || !result.payload.status) {
                            var message = result.payload && result.payload.message ?
                                result.payload.message :
                                'Không thể hủy đơn hàng.';
                            throw new Error(message);
                        }

                        markOrderCanceled(cancelState.card, result.payload.order_status_name || '');
                        closeCancelModal();
                    })
                    .catch(function(error) {
                        showCancelError(error && error.message ? error.message : 'Không thể hủy đơn hàng.');
                    })
                    .then(function() {
                        if (cancelSubmitBtn) cancelSubmitBtn.disabled = false;
                    });
            }

            function fetchOrderDetail(button, card, detail) {
                var orderId = String(button.dataset.orderId || '').trim();
                var apiUrl = String(button.dataset.apiUrl || '').trim();
                if (!orderId || !apiUrl) return;
                var content = getDetailContent(card);
                if (!content) return;

                detail.hidden = false;
                detail.classList.add('is-loading');
                detail.classList.remove('is-open');
                content.innerHTML = '<p class="account-order-loading">Đang tải chi tiết đơn hàng...</p>';

                fetch(apiUrl + '?order_id=' + encodeURIComponent(orderId), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        if (!response.ok) throw new Error('http_' + response.status);
                        return response.json();
                    })
                    .then(function(payload) {
                        if (!payload || !payload.status || !payload.html) throw new Error('invalid_payload');
                        content.innerHTML = payload.html;
                        detail.setAttribute('data-loaded', '1');
                        detail.classList.remove('is-loading');
                        expandCard(card);
                    })
                    .catch(function() {
                        detail.classList.remove('is-loading');
                        showLoadError(content);
                        detail.setAttribute('data-loaded', '0');
                        expandCard(card);
                    });
            }

            panel.addEventListener('click', function(event) {
                var reviewTrigger = event.target.closest('.js-order-review-trigger');
                if (reviewTrigger) {
                    event.preventDefault();
                    openReviewModal(reviewTrigger);
                    return;
                }

                var reviewStar = event.target.closest('.js-review-star');
                if (reviewStar && reviewModal && !reviewModal.hidden) {
                    event.preventDefault();
                    setReviewStars(reviewStar.dataset.starValue || 0);
                    return;
                }

                var reviewClose = event.target.closest('.js-review-close');
                if (reviewClose) {
                    event.preventDefault();
                    closeReviewModal();
                    return;
                }

                var cancelButton = event.target.closest('.js-order-cancel');
                if (cancelButton) {
                    event.preventDefault();
                    openCancelModal(cancelButton);
                    return;
                }

                var button = event.target.closest('.js-order-toggle');
                if (!button) return;
                event.preventDefault();

                var card = button.closest('.account-order-card');
                var detail = getDetail(card);
                if (!card || !detail) return;

                var isOpen = detail.classList.contains('is-open');
                if (isOpen) {
                    collapseCard(card);
                    return;
                }

                collapseOthers(card);
                if (detail.getAttribute('data-loaded') === '1') {
                    expandCard(card);
                    return;
                }

                fetchOrderDetail(button, card, detail);
            });

            panel.querySelectorAll('.account-order-card').forEach(function(card) {
                var detail = getDetail(card);
                var buttons = getButtons(card);
                var isOpen = !!detail && detail.classList.contains('is-open');
                setButtonState(buttons, isOpen);
            });

            if (cancelReasonSelect) {
                cancelReasonSelect.addEventListener('change', toggleCancelReasonText);
            }
            if (reviewForm) {
                reviewForm.addEventListener('submit', submitReviewForm);
            }
            if (cancelCloseBtn) {
                cancelCloseBtn.addEventListener('click', closeCancelModal);
            }
            if (cancelSubmitBtn) {
                cancelSubmitBtn.addEventListener('click', submitCancelOrder);
            }
            if (cancelModal) {
                cancelModal.addEventListener('click', function(event) {
                    if (event.target === cancelModal) {
                        closeCancelModal();
                    }
                });
            }
            if (reviewModal) {
                reviewModal.addEventListener('click', function(event) {
                    if (event.target === reviewModal) {
                        closeReviewModal();
                    }
                });
            }
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    if (reviewModal && !reviewModal.hidden) {
                        closeReviewModal();
                    }
                    if (cancelModal && !cancelModal.hidden) {
                        closeCancelModal();
                    }
                }
            });

            var hash = window.location.hash || '';
            if (!hash || hash.indexOf('#order-card-') !== 0) return;
            var target = document.querySelector(hash);
            if (!target) return;
            setTimeout(function() {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 60);
        });
    </script>
</section>

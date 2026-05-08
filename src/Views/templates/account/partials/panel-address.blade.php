<section class="account-panel {{ $activeSection === 'address' ? 'is-active' : '' }}"
    data-wards-url="{{ url('user.account.wards') }}">
    <div class="account-panel__head">
        <h2 class="account-panel__title">Địa chỉ giao hàng</h2>
    </div>

    <form class="account-form account-address-form mb-4 js-account-address-form" method="post" action="{{ url('user.account.address.save') }}">
        <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
        <input type="hidden" name="address_id" value="" class="js-address-id">
        <div class="alert alert-warning py-2 px-3 mb-3 js-address-editing-indicator" style="display:none;"></div>

        <div class="account-address-grid">
            <div class="mb-3">
                <label class="form-label">Tên người nhận</label>
                <input type="text" class="form-control js-address-recipient-name" name="recipient_name" value="{{ $user->fullname ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" class="form-control js-address-recipient-phone" name="recipient_phone" value="{{ $user->phone ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tỉnh/Thành</label>
                <select class="form-select js-account-city js-address-select2" name="city" required>
                    <option value="">Chọn tỉnh/thành</option>
                    @foreach (($cities ?? []) as $city)
                        <option value="{{ (int) $city->id }}" data-city-name="{{ $city->namevi }}">
                            {{ $city->namevi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Phường/Xã</label>
                <select class="form-select js-account-ward js-address-select2" name="ward" required disabled>
                    <option value="">Chọn phường/xã</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Địa chỉ chi tiết</label>
            <input type="text" class="form-control js-address-line" name="address_line" placeholder="Số nhà, tên đường..." required>
        </div>

        <div class="account-address-form__foot">
            <label class="account-switch">
                <input type="checkbox" name="is_default" value="1" class="js-address-default">
                <span class="account-switch__track" aria-hidden="true"></span>
                <span class="account-switch__text">Đặt làm mặc định</span>
            </label>
            <div class="account-address-form__actions">
                <button type="submit" class="btn account-btn js-address-submit">+ Thêm địa chỉ mới</button>
                <button type="button" class="btn account-btn account-btn--outline js-address-cancel-edit" style="display:none;">Hủy sửa</button>
            </div>
        </div>
    </form>

    <div class="account-address-list">
        @forelse (($addresses ?? []) as $address)
            <article class="account-address-card js-address-card">
                <div class="account-address-card__head">
                    <p class="account-address-card__name">{{ $address['recipient_name'] ?? '-' }}</p>
                    @if (!empty($address['is_default']))
                        <span class="account-badge">Mặc định</span>
                    @endif
                </div>
                <p class="account-address-card__phone">{{ $address['recipient_phone'] ?? '-' }}</p>
                <p class="account-address-card__line">
                    {{ $address['address_line'] ?? '' }}{{ !empty($address['ward']) ? ', ' . $address['ward'] : '' }}{{ !empty($address['city']) ? ', ' . $address['city'] : '' }}
                </p>

                <form method="post" action="{{ url('user.account.address.delete') }}" class="account-address-card__actions">
                    <button class="btn account-btn account-btn--outline js-address-edit" type="button"
                        data-address-id="{{ $address['id'] ?? '' }}"
                        data-recipient-name="{{ $address['recipient_name'] ?? '' }}"
                        data-recipient-phone="{{ $address['recipient_phone'] ?? '' }}"
                        data-address-line="{{ $address['address_line'] ?? '' }}"
                        data-city="{{ $address['city'] ?? '' }}"
                        data-ward="{{ $address['ward'] ?? '' }}"
                        data-is-default="{{ !empty($address['is_default']) ? '1' : '0' }}">
                        Sửa
                    </button>
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="address_id" value="{{ $address['id'] ?? '' }}">
                    <button class="btn account-btn account-btn--outline" type="submit">Xóa</button>
                </form>
            </article>
        @empty
            <div class="alert alert-info account-address-empty">Chưa có địa chỉ đã lưu.</div>
        @endforelse
    </div>
</section>

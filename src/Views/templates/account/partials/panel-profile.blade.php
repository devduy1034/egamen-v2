<section class="account-panel {{ $activeSection === 'profile' ? 'is-active' : '' }}">
    <div class="account-panel__head">
        <h1 class="account-panel__title">{{ $titleMain ?? 'Thông tin tài khoản' }}</h1>
    </div>

    <div class="account-profile">
        <form class="account-form" method="post" action="{{ url('user.account.profile') }}" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
            <input type="hidden" name="remove_avatar" id="account-avatar-remove" value="0">
            <input type="file" class="d-none" id="account-avatar-input" name="avatar" accept=".jpg,.jpeg,.png,.webp,.gif">

            <div class="mb-3">
                <label class="form-label">Họ tên</label>
                <input type="text" class="form-control" name="fullname" value="{{ $user->fullname ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ $user->email ?? '' }}" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" name="phone" value="{{ $user->phone ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ngày sinh</label>
                <input type="text" class="form-control js-birthday-picker" name="birthday" placeholder="Chọn ngày sinh" value="{{ $birthdayValue }}" autocomplete="off">
            </div>
            <div class="mb-3">
                <label class="form-label d-block">Giới tính</label>
                <label class="me-3"><input type="radio" name="gender" value="1" {{ (int) ($user->gender ?? 0) === 1 ? 'checked' : '' }} required> Nam</label>
                <label class="me-3"><input type="radio" name="gender" value="2" {{ (int) ($user->gender ?? 0) === 2 ? 'checked' : '' }} required> Nữ</label>
            </div>
            <button type="submit" class="btn account-btn">Lưu thay đổi</button>
        </form>

        <div class="account-avatar-box">
            <p class="account-avatar-box__title">Hình ảnh</p>
            <div class="account-uploader js-account-uploader" data-default-src="{{ $noImageUrl }}">
                <div class="account-uploader__preview js-avatar-preview">
                    @if (!empty($user->avatar))
                        <img src="{{ assets_photo('user', '300x300x1', $user->avatar, 'thumbs') }}" alt="{{ $user->fullname ?? 'Avatar' }}">
                    @else
                        <img src="{{ $noImageUrl }}" alt="No image">
                    @endif
                </div>
                <div class="account-uploader__actions">
                    <button type="button" class="btn account-btn account-btn--outline js-avatar-edit">Chỉnh sửa ảnh</button>
                    <button type="button" class="btn account-btn account-btn--outline js-avatar-delete">Xóa ảnh</button>
                </div>
                <label class="account-uploader__dropzone js-avatar-dropzone" for="account-avatar-input">
                    <span class="account-uploader__icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <span class="account-uploader__text">Kéo và thả hình vào đây</span>
                    <span class="account-uploader__or">Hoặc</span>
                    <span class="account-uploader__pick">Chọn hình</span>
                    <small>Width: 300 px - Height: 300 px (jpg,gif,png,jpeg,webp)</small>
                </label>
            </div>
        </div>
    </div>
</section>

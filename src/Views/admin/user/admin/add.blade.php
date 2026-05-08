@extends('layout')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce">
            <form class="{{ empty($changepass) ? 'validation-form' : '' }}" novalidate method="post"
                action="{{ url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['changepass' => $changepass ?? '']) }}"
                enctype="multipart/form-data">
                <ul class="nav nav-pills flex-column flex-md-row mb-4 mt-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $act == 'man' && empty($changepass) && empty($history) ? 'active' : '' }}"
                            href="{{ url('admin', ['com' => 'user-admin', 'act' => 'man', 'type' => 'tai-khoan']) }}"><i
                                class="ti-xs ti ti-users me-1"></i> Tài khoản</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $act == 'man' && !empty($changepass) ? 'active' : '' }}"
                            href="{{ url('admin', ['com' => 'user-admin', 'act' => 'man', 'type' => 'tai-khoan']) }}?changepass=1"><i
                                class="ti-xs ti ti-lock me-1"></i> Đổi mật khẩu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $act == 'man' && !empty($history) ? 'active' : '' }}"
                            href="{{ url('admin', ['com' => 'log', 'act' => 'man', 'type' => 'history']) }}"><i
                                class="ti-xs ti ti-file-description me-1"></i> Lịch sử truy cập</a>
                    </li>
                </ul>
                {!! Flash::getMessages('admin') !!}
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">
                                    @if (empty($changepass))
                                        Thông tin tài khoản
                                    @else
                                        Cập nhật mật khẩu đăng nhập
                                    @endif
                                </h3>
                            </div>

                            @if (empty($changepass))
                                <div class="card-body">
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        <img src="{{ !empty($item['avatar']) ? upload('user', $item['avatar']) : assets('assets/admin/img/avatars/14.png') }} "
                                            alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar">
                                        <div class="button-wrapper">
                                            <label for="upload" class="btn btn-primary me-2 mb-3 waves-effect waves-light"
                                                tabindex="0">
                                                <span class="d-none d-sm-block">Chọn hình ảnh</span>
                                                <i class="ti ti-upload d-block d-sm-none"></i>
                                                <input type="file" name="avatar" id="upload"
                                                    class="account-file-input" hidden=""
                                                    accept="image/png, image/jpeg">
                                            </label>
                                            <button type="button"
                                                class="btn btn-label-secondary account-image-reset mb-3 waves-effect">
                                                <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Đặt lại</span>
                                            </button>
                                            <div class="text-muted">Allowed JPG, PNG. Max size of 800K</div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-0">
                            @endif
                            <div class="card-body card-article">
                                <div class="{{ empty($changepass) ? 'row' : '' }}">
                                    @if (!empty($changepass))
                                        <div class="row" x-data="changePassword()">
                                            <div class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container">
                                                <label class="form-label" for="oldpassword">Mật khẩu hiện tại</label>
                                                <div class="input-group input-group-merge has-validation">
                                                    <input class="form-control" x-model="oldpass" type="password"
                                                        name="oldpassword" id="oldpassword" placeholder="">
                                                    <span class="input-group-text cursor-pointer"><i
                                                            class="ti ti-eye-off"></i></span>
                                                </div>
                                                <div
                                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-6"></div>
                                            <div class="mb-3 col-md-6 fv-plugins-icon-container" x-data="{ view: false }">
                                                <label class="form-label" for="newpassword">Mật khẩu mới</label>
                                                <div class="input-group input-group-merge ">
                                                    <input class="form-control"
                                                        @input="changePass.newpassword = removeVietnamese(changePass.newpassword)"
                                                        x-model="changePass.newpassword"
                                                        x-bind:type="view ? 'text' : 'password'" id="newpassword"
                                                        name="newpassword" placeholder="">
                                                    <span class="input-group-text cursor-pointer pe-0"
                                                        @click="generateRandomPassword()"><i class="ti ti-arrows-shuffle"
                                                            data-bs-toggle="tooltip" data-bs-trigger="hover"
                                                            data-bs-placement="bottom"
                                                            title="Tạo mật khẩu ngẫu nhiên"></i></span>
                                                    <span class="input-group-text cursor-pointer"><i class="ti"
                                                            @click="view=!view"
                                                            :class="!view ? 'ti-eye-off' : 'ti-eye'"></i></span>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-6 form-password-toggle fv-plugins-icon-container">
                                                <label class="form-label" for="renewpassword">Nhập lại mật khẩu mới</label>
                                                <div class="input-group input-group-merge ">
                                                    <input class="form-control" type="password"
                                                        @input="changePass.renewpassword = removeVietnamese(changePass.renewpassword)"
                                                        x-model="changePass.renewpassword" name="renewpassword"
                                                        id="renewpassword" placeholder="">
                                                    <span class="input-group-text cursor-pointer"><i
                                                            class="ti ti-eye-off"></i></span>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-4">
                                                <h6>Yêu cầu về mật khẩu:</h6>
                                                <ul class="ps-3 mb-0">
                                                    <li class="mb-1 text-gray-950/60 dark:text-[#b6bee3]"
                                                        :class="{ 'text-gray-950/60 dark:text-[#b6bee3]': !isValidLength(
                                                                changePass.newpassword
                                                                ), 'text-green-500': isValidLength(changePass.newpassword) }">
                                                        Dài tối thiểu 8 ký tự - càng nhiều càng tốt</li>
                                                    <li class="mb-1 text-gray-950/60 dark:text-[#b6bee3]"
                                                        :class="{ 'text-gray-950/60 dark:text-[#b6bee3]': !hasSpecialCharacter(
                                                                changePass.newpassword
                                                                ), 'text-green-500': hasSpecialCharacter(changePass
                                                                    .newpassword) }">
                                                        Ít nhất 1 ký tự đặc biệt.</li>
                                                    <li class="mb-1 text-gray-950/60 dark:text-[#b6bee3]"
                                                        :class="{ 'text-gray-950/60 dark:text-[#b6bee3]': !hasLowerAndUpperCase(
                                                                changePass.newpassword
                                                                ), 'text-green-500': hasLowerAndUpperCase(changePass
                                                                    .newpassword) }">
                                                        Ít nhất 1 chữ thường và 1 chữ in hoa.</li>
                                                    <li class="text-gray-950/60 dark:text-[#b6bee3]"
                                                        :class="{ 'text-gray-950/60 dark:text-[#b6bee3]': !
                                                            doPasswordsMatch, 'text-green-500': doPasswordsMatch }">
                                                        Nhập lại mật khẩu mới không chính xác.</li>
                                                </ul>
                                            </div>
                                            <div>
                                                <button
                                                    x-bind:disabled="(oldpass.trim() === '' || changePass.newpassword
                                                    .trim() === '' || !isValidLength(changePass.newpassword) ||
                                                        !hasSpecialCharacter(changePass.newpassword) || !
                                                        hasLowerAndUpperCase(changePass.newpassword) || !
                                                        doPasswordsMatch)"
                                                    type="submit"
                                                    class="btn btn-primary me-2 waves-effect waves-light">Cập nhật mật
                                                    khẩu</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="form-group col-4">
                                            <label class="form-label" for="username">Tài khoản:</label>
                                            <input type="text" {{ !empty($item['id']) ? 'disabled' : '' }}
                                                class="form-control  text-sm" name="data[username]" id="name"
                                                placeholder="Tài khoản"
                                                value="{{ !empty(Flash::has('username')) ? Flash::get('username') : $item['username'] }}">
                                        </div>

                                        <div class="form-group col-4">
                                            <label class="form-label" for="fullname">Họ Tên:</label>
                                            <input type="text" class="form-control  text-sm" name="data[fullname]"
                                                id="name" placeholder="Họ Tên"
                                                value="{{ !empty(Flash::has('fullname')) ? Flash::get('fullname') : $item['fullname'] }}">
                                        </div>

                                        <div class="form-group col-4">
                                            <label class="form-label" for="gender">Giới tính:</label>

                                            <select class="form-control custom-select text-sm" name="data[gender]"
                                                id="gender" required>
                                                <option value="">Chọn giới tính</option>
                                                <option {{ @$item['gender'] == 1 ? 'selected' : '' }} value="1">Nam
                                                </option>
                                                <option {{ @$item['gender'] == 2 ? 'selected' : '' }} value="2">Nữ
                                                </option>
                                            </select>
                                        </div>

                                        <div class="form-group col-4">
                                            <label class="form-label" for="birthday">Ngày sinh:</label>
                                            <input type="text" class="form-control  text-sm" id="flatpickr"
                                                name="data[birthday]" id="birthday" placeholder="Ngày sinh"
                                                value="{{ !empty(Flash::has('birthday')) ? date('d/m/Y', Flash::get('birthday')) : (!empty($item['birthday']) ? date('d/m/Y', $item['birthday']) : '') }}"
                                                required="" autocomplete="off">
                                        </div>

                                        <div class="form-group col-4">
                                            <label class="form-label" for="email">Email:</label>
                                            <input type="text" class="form-control  text-sm" name="data[email]"
                                                id="email" placeholder="Email"
                                                value="{{ !empty(Flash::has('email')) ? Flash::get('email') : $item['email'] }}">
                                        </div>

                                        <div class="form-group col-4">
                                            <label class="form-label" for="phone">điện thoại:</label>
                                            <input type="text" class="form-control  text-sm" name="data[phone]"
                                                id="phone" placeholder="điện thoại"
                                                value="{{ !empty(Flash::has('phone')) ? Flash::get('phone') : $item['phone'] }}">
                                        </div>

                                        <div class="form-group col-4">
                                            <label class="form-label" for="address">địa chỉ:</label>
                                            <input type="text" class="form-control  text-sm" name="data[address]"
                                                id="address" placeholder="địa chỉ"
                                                value="{{ !empty(Flash::has('address')) ? Flash::get('address') : $item['address'] }}">
                                        </div>
                                        <div class="form-group last:!mb-0">
                                            <label for="numb" class="d-inline-block align-middle mb-0 mr-2">Số thứ
                                                tự:</label>
                                            <input type="number"
                                                class="form-control form-control-mini w-25 text-left d-inline-block align-middle text-sm"
                                                min="0" name="data[numb]" id="numb" placeholder="Số thứ tự"
                                                value="{{ !empty($item['numb']) ? $item['numb'] : 1 }}">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="id"
                    value="<?= !empty($item['id']) && $item['id'] > 0 ? $item['id'] : '' ?>">
                <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                @if (empty($changepass))
                    @component('component.buttonAdd')
                    @endcomponent
                @endif
            </form>
        </div>
    </div>
@endsection
@pushonce('styles')
    <link rel="stylesheet" href="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.css')" />
@endpushonce
@pushonce('scripts')
    <script src="@asset('assets/admin/vendor/libs/flatpickr/flatpickr.js')"></script>
    <script>
        const flatpickr = document.querySelector('#flatpickr');
        if (typeof flatpickr != undefined) {
            flatpickr.flatpickr({
                dateFormat: 'd/m/Y',
                @if (!empty($item['birthday']))
                    defaultDate: '{{ \Carbon\Carbon::createFromTimestamp($item['birthday'])->format('d/m/Y') }}'
                @endif
            });
        }
        document.addEventListener('DOMContentLoaded', function(e) {
            (function() {
                let accountUserImage = document.getElementById('uploadedAvatar');
                const fileInput = document.querySelector('.account-file-input'),
                    resetFileInput = document.querySelector('.account-image-reset');
                if (accountUserImage) {
                    const resetImage = accountUserImage.src;
                    fileInput.onchange = () => {
                        if (fileInput.files[0]) {
                            accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
                        }
                    };
                    resetFileInput.onclick = () => {
                        fileInput.value = '';
                        accountUserImage.src = resetImage;
                    };
                }
            })
            ();
        });

        function changePassword() {
            return {
                oldpass: '',
                changePass: {
                    newpassword: '',
                    renewpassword: ''
                },
                get isValidLength() {
                    return password => password.length >= 8;
                },
                get hasSpecialCharacter() {
                    return password => /[!@#$%^&*(),.?":{}|<>]/.test(password);
                },
                get hasLowerAndUpperCase() {
                    return password => /[a-z]/.test(password) && /[A-Z]/.test(password);
                },
                get doPasswordsMatch() {
                    return (this.changePass.newpassword === this.changePass.renewpassword) && this.changePass
                        .newpassword !== '' && this.changePass.renewpassword !== '';
                },
                removeVietnamese(str) {
                    const vietnameseMap = {
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        'a': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'a',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'e',
                        '?': 'i',
                        '?': 'i',
                        '?': 'i',
                        '?': 'i',
                        'i': 'i',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        'o': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'o',
                        '?': 'u',
                        '?': 'u',
                        '?': 'u',
                        '?': 'u',
                        'u': 'u',
                        'u': 'u',
                        '?': 'u',
                        '?': 'u',
                        '?': 'u',
                        '?': 'u',
                        '?': 'u',
                        '?': 'y',
                        '?': 'y',
                        '?': 'y',
                        '?': 'y',
                        '?': 'y',
                        'd': 'd',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        'A': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'A',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'E',
                        '?': 'I',
                        '?': 'I',
                        '?': 'I',
                        '?': 'I',
                        'I': 'I',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        'O': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'O',
                        '?': 'U',
                        '?': 'U',
                        '?': 'U',
                        '?': 'U',
                        'U': 'U',
                        'U': 'U',
                        '?': 'U',
                        '?': 'U',
                        '?': 'U',
                        '?': 'U',
                        '?': 'U',
                        '?': 'Y',
                        '?': 'Y',
                        '?': 'Y',
                        '?': 'Y',
                        '?': 'Y',
                        '?': 'D'
                    };
                    return str.split('').map(char => vietnameseMap[char] || char).join('');
                },
                generateRandomPassword(length = 12) {
                    const lowerCaseLetters = 'abcdefghijklmnopqrstuvwxyz';
                    const upperCaseLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    const specialCharacters = '!@#$%^&*(),.?":{}|<>';
                    const allCharacters = lowerCaseLetters + upperCaseLetters + specialCharacters + '0123456789';
                    let password = '';
                    password += lowerCaseLetters[Math.floor(Math.random() * lowerCaseLetters.length)];
                    password += upperCaseLetters[Math.floor(Math.random() * upperCaseLetters.length)];
                    password += specialCharacters[Math.floor(Math.random() * specialCharacters.length)];
                    for (let i = password.length; i < length; i++) {
                        password += allCharacters[Math.floor(Math.random() * allCharacters.length)];
                    }
                    return this.shuffleString(password);
                },
                shuffleString(str) {
                    const array = str.split('');
                    for (let i = array.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [array[i], array[j]] = [array[j], array[i]];
                    }
                    this.changePass.newpassword = this.changePass.renewpassword = array.join('');
                }
            }
        }
    </script>
@endpushonce

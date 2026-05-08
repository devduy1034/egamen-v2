@extends('layout')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce" x-data="$store.changePassword">
            <form class="validation-form" novalidate=""  method="post" action="{{url('user.save')}}" enctype="multipart/form-data">
                <div class="btn-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                    <div class="d-flex align-content-center flex-wrap gap-2">

                        <div class="d-flex gap-2">
                            @if(!empty(request()->query('action')) || empty($item['id']))
                                <button :class="(($store.changePassword.changePass.newpassword.trim() === '' || !$store.changePassword.isValidLength($store.changePassword.changePass.newpassword) || !$store.changePassword.hasSpecialCharacter($store.changePassword.changePass.newpassword) || !$store.changePassword.hasLowerAndUpperCase($store.changePassword.changePass.newpassword) || !doPasswordsMatch))?'disabled':''" type="submit" class="btn btn-primary submit-check"><i class="far fa-save mr-2"></i> Luu</button>
                            @else
                                <button :class="(!$store.changePassword.isEdit && ($store.changePassword.changePass.newpassword.trim() === '' || !$store.changePassword.isValidLength($store.changePassword.changePass.newpassword) || !$store.changePassword.hasSpecialCharacter($store.changePassword.changePass.newpassword) || !$store.changePassword.hasLowerAndUpperCase($store.changePassword.changePass.newpassword) || !doPasswordsMatch))?'disabled':''" type="submit" class="btn btn-primary submit-check"><i class="far fa-save mr-2"></i> Luu</button>
                            @endif
                            <button type="reset" class="btn btn-primary"><i class="fas fa-redo mr-2"></i> Làm lại</button>
                            <button class="btn btn-primary"><i class="fas fa-sign-out-alt mr-2"></i> Thoát</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @if(empty(request()->query('action')) || empty($item['id']))
                    <div class="col-12 col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Cập nhật thành viên</h3>
                            </div>
                            <div class="card-body card-article">
                                <div class="row">
                                    @if(config('type.users.permission'))
                                    <div class="form-group col-4">
                                        <label class="form-label" for="role">Nhóm quyền:</label>
                                        <div class="position-relative">
                                    
                                            <select class="select2 form-select form-select-lg" name="idrole">
                                                <option value="0">Chọn nhóm quyền</option>
                                                @foreach($roles as $k => $v)
                                               
                                                <option {{ (!empty($item->roles) && $item->roles()->first()?->id == $v['id'])?'selected':''}} value="{{$v['id']}}">{{$v['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="form-group col-4" x-data="{ isFocused: false }">
                                        <label class="form-label" for="username">Tài khoản: <span class="text-danger">*</span></label>
                                        <input x-on:focus="isFocused = true;
                                               let parentElement = $event.target.parentElement.parentElement.querySelector('.text-danger');
                                               if (parentElement) parentElement.classList.add('d-none')"
                                               type="text" class="form-control text-sm" {{!empty($item['username'])?'disabled':''}} required name="username" id="name" placeholder="Tài khoản" value="{{oldvalue('username')??($item['username']??'')}}">
                                        <div class="invalid-feedback">Vui lòng nhập tên tài khoản</div>
                                        @error('username')
                                            <div class="text-danger text-sm mt-1">{{$message}}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label" for="email">Email:</label>
                                        <input type="text" class="form-control  text-sm" name="email" id="email" placeholder="Email" value="{{oldvalue('email')??($item['email']??'')}}">
                                    </div>
                                    <div class="form-group col-4" x-data="{ isFocused: false }">
                                        <label class="form-label" for="fullname">Họ tên: <span class="text-danger">*</span></label>
                                        <input x-on:focus="isFocused = true;
                                               let parentElement = $event.target.parentElement.parentElement.querySelector('.text-danger');
                                               if (parentElement) parentElement.classList.add('d-none')"
                                               type="text" class="form-control  text-sm" required name="fullname" id="fullname" placeholder="Họ tên" value="{{oldvalue('fullname')??($item['fullname']??'')}}">
                                        <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                                        @error('fullname')
                                        <div class="text-danger text-sm mt-1">{{$message}}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label" for="address">địa chỉ:</label>
                                        <input type="text" class="form-control  text-sm" name="address" id="address" placeholder="địa chỉ" value="{{oldvalue('address')??($item['address']??'')}}">
                                    </div>
                                    <div class="form-group col-4">
                                        <label class="form-label" for="phone">Phone:</label>
                                        <input type="text" class="form-control  text-sm" name="phone" id="phone" placeholder="Phone" value="{{oldvalue('phone')??($item['phone']??'')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(!empty(request()->query('action')) || empty($item['id']))
                    <div class="col-12 col-lg-12 mt-2">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Cập nhật mật khẩu</h3>
                            </div>

                            <div class="card-body card-article" >
                                <div class="row">
                                    <div class="form-group col-6 form-password-toggle">
                                        <label class="form-label" for="basic-default-password">Mật khẩu</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" @input="$store.changePassword.changePass.newpassword = $store.changePassword.removeVietnamese($store.changePassword.changePass.newpassword)" x-model="changePass.newpassword" name="password" {{empty($item['id'])?'required':''}} value="" id="basic-default-password" class="form-control" placeholder="" aria-describedby="basic-default-password3">
                                            <span class="input-group-text cursor-pointer" id="basic-default-password3"><i class="ti ti-eye-off"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group col-6 form-password-toggle" x-data="{ isFocused: false }">
                                        <label class="form-label" for="basic-default-password1">Nhập lại mật khẩu</label>
                                        <div class="input-group input-group-merge">
                                            <input x-on:focus="isFocused = true;
                                                   let parentElement = $event.target.parentElement.parentElement.querySelector('.text-danger');
                                                   if (parentElement) parentElement.classList.add('d-none')"
                                                   @input="$store.changePassword.changePass.renewpassword = $store.changePassword.removeVietnamese($store.changePassword.changePass.renewpassword)" x-model="$store.changePassword.changePass.renewpassword"
                                                   type="password" name="re-password" value="" {{empty($item['id'])?'required':''}} id="basic-default-password1" class="form-control" placeholder="" aria-describedby="basic-default-password4" >
                                            <span class="input-group-text cursor-pointer" id="basic-default-password4"><i class="ti ti-eye-off"></i></span>
                                        </div>
                                        @error('re-password')
                                            <div class="text-danger text-sm mt-1">{{$message}}</div>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <h6>Yêu cầu về mật khẩu:</h6>
                                        <ul class="ps-3 mb-0">
                                            <li class="mb-1 text-gray-950/60 dark:text-[#b6bee3]" :class="{'text-gray-950/60 dark:text-[#b6bee3]': !$store.changePassword.isValidLength($store.changePassword.changePass.newpassword), 'text-green-500': isValidLength($store.changePassword.changePass.newpassword)}">Dài tối thiểu 8 ký tự - càng nhiều càng tốt</li>
                                            <li class="mb-1 text-gray-950/60 dark:text-[#b6bee3]" :class="{'text-gray-950/60 dark:text-[#b6bee3]': !$store.changePassword.hasSpecialCharacter($store.changePassword.changePass.newpassword), 'text-green-500': $store.changePassword.hasSpecialCharacter($store.changePassword.changePass.newpassword)}">Ít nhất 1 ký tự đặc biệt.</li>
                                            <li class="mb-1 text-gray-950/60 dark:text-[#b6bee3]" :class="{'text-gray-950/60 dark:text-[#b6bee3]': !$store.changePassword.hasLowerAndUpperCase($store.changePassword.changePass.newpassword), 'text-green-500': $store.changePassword.hasLowerAndUpperCase($store.changePassword.changePass.newpassword)}">Ít nhất 1 chữ thường và 1 chữ in hoa.</li>
                                            <li class="text-gray-950/60 dark:text-[#b6bee3]" :class="{'text-gray-950/60 dark:text-[#b6bee3]': !$store.changePassword.doPasswordsMatch, 'text-green-500': $store.changePassword.doPasswordsMatch}">Nhập lại mật khẩu không chính xác.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="btn-footer d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                    <div class="d-flex align-content-center flex-wrap gap-2">
                        <div class="d-flex gap-2">
                            @if(!empty(request()->query('action')) || empty($item['id']))
                                <button :class="(($store.changePassword.changePass.newpassword.trim() === '' || !$store.changePassword.isValidLength($store.changePassword.changePass.newpassword) || !$store.changePassword.hasSpecialCharacter($store.changePassword.changePass.newpassword) || !$store.changePassword.hasLowerAndUpperCase($store.changePassword.changePass.newpassword) || !doPasswordsMatch))?'disabled':''" type="submit" class="btn btn-primary submit-check"><i class="far fa-save mr-2"></i> Luu</button>
                            @else
                                <button :class="(!$store.changePassword.isEdit && ($store.changePassword.changePass.newpassword.trim() === '' || !$store.changePassword.isValidLength($store.changePassword.changePass.newpassword) || !$store.changePassword.hasSpecialCharacter($store.changePassword.changePass.newpassword) || !$store.changePassword.hasLowerAndUpperCase($store.changePassword.changePass.newpassword) || !doPasswordsMatch))?'disabled':''" type="submit" class="btn btn-primary submit-check"><i class="far fa-save mr-2"></i> Luu</button>
                            @endif
                            <button type="reset" class="btn btn-primary"><i class="fas fa-redo mr-2"></i> Làm lại</button>
                            <button class="btn btn-primary"><i class="fas fa-sign-out-alt mr-2"></i> Thoát</button>
                            <input type="hidden" name="id" value="{{$item['id']??0}}">
                            <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@pushonce('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('changePassword', {
                changePass:{newpassword:'',renewpassword:''},
                isEdit:{{!empty($item['id'])?'true':'false'}},
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
                    return (this.changePass.newpassword === this.changePass.renewpassword) && this.changePass.newpassword!=='' && this.changePass.renewpassword!=='';
                },
                removeVietnamese(str) {
                    const vietnameseMap = {
                        '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', 'a': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a', '?': 'a',
                        '?': 'e', '?': 'e', '?': 'e', '?': 'e', '?': 'e', '?': 'e', '?': 'e', '?': 'e', '?': 'e', '?': 'e', '?': 'e',
                        '?': 'i', '?': 'i', '?': 'i', '?': 'i', 'i': 'i',
                        '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', 'o': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o', '?': 'o',
                        '?': 'u', '?': 'u', '?': 'u', '?': 'u', 'u': 'u', 'u': 'u', '?': 'u', '?': 'u', '?': 'u', '?': 'u', '?': 'u',
                        '?': 'y', '?': 'y', '?': 'y', '?': 'y', '?': 'y',
                        'd': 'd',
                        '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', 'A': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A', '?': 'A',
                        '?': 'E', '?': 'E', '?': 'E', '?': 'E', '?': 'E', '?': 'E', '?': 'E', '?': 'E', '?': 'E', '?': 'E', '?': 'E',
                        '?': 'I', '?': 'I', '?': 'I', '?': 'I', 'I': 'I',
                        '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', 'O': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O', '?': 'O',
                        '?': 'U', '?': 'U', '?': 'U', '?': 'U', 'U': 'U', 'U': 'U', '?': 'U', '?': 'U', '?': 'U', '?': 'U', '?': 'U',
                        '?': 'Y', '?': 'Y', '?': 'Y', '?': 'Y', '?': 'Y',
                        '?': 'D'
                    };
                    return str.split('').map(char => vietnameseMap[char] || char).join('');
                },
            })
        })

    </script>

@endpushonce



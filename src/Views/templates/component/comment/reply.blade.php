@php
    $isLoggedInMember = session()->has('member');
    $memberName = session()->get('member_name');
@endphp

<form class="mt-3" id="form-reply" action="" method="post" enctype="multipart/form-data">
    <div class="response-reply"></div>
    <div class="form-group mb-2">
        <textarea class="form-control text-sm" placeholder="Viết câu trả lời của bạn" name="dataReview[content]" id="reply-content" data-name="@{{ $params['fullname'] }}:" rows="5"></textarea>
    </div>
    @if ($isLoggedInMember)
        <div class="form-group mb-2">
            <div class="alert alert-light border text-sm mb-0">
                Bạn đang trả lời với tài khoản <strong>{{ $memberName ?: 'đã đăng nhập' }}</strong>.
            </div>
        </div>
    @else
        <div class="form-group">
            <div class="row row-10">
                <div class="col-4 mg-col-10 mb-2">
                    <input type="text" class="form-control text-sm" name="dataReview[fullname]" id="reply-fullname" placeholder="Nhập họ tên liên hệ *" required>
                </div>
                <div class="col-4 mg-col-10 mb-2">
                    <input type="text" class="form-control text-sm" name="dataReview[phone]" id="reply-phone" placeholder="Nhập số điện thoại liên hệ *">
                </div>
                <div class="col-4 mg-col-10 mb-2">
                    <input type="text" class="form-control text-sm" name="dataReview[email]" id="reply-email" placeholder="Nhập email liên hệ *" required>
                </div>
            </div>
        </div>
    @endif
    <div class="text-right">
        <button type="submit" class="btn btn-sm btn-warning me-2 py-2 px-3">Gửi trả lời</button>
        <button type="button" class="btn btn-sm btn-secondary btn-cancel-reply py-2 px-3">Hủy bỏ</button>
    </div>
    <input type="hidden" name="dataReview[id_parent]" value="{{ $params['id'] }}">
    <input type="hidden" name="dataReview[id_variant]" value="{{ $params['id_variant'] }}">
    <input type="hidden" name="dataReview[type]" value="{{ $params['type'] }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
</form>

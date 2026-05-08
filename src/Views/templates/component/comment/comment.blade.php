@php
    $rowComment = $rowDetail->getComment()->skip(0)->take(2)->get();

    $countComment = $rowDetail->getComment()->count();
    $isLoggedInMember = session()->has('member');
    $memberName = session()->get('member_name');
@endphp

<div class="comment-page">
    <!-- Statistic comment -->
    <div class="comment-statistic mb-4">
        <div class="card">
            <div class="card-body" style="border: 0; padding: 0;">
                <h4 class="mb-4" style="font-size: 18px; font-weight: bold;">Đánh giá {{ $rowDetail['name' . $lang] }}</h4>
                <div class="row align-items-center py-3">
                    <div class="col-sm-6 col-lg-4 mb-4">
                        <div class="text-center">
                            <div class="comment-title"><strong>Đánh Giá Trung Bình</strong></div>
                            <div class="comment-point">
                                <strong>{{ Comment::avgPoint($rowDetail['id'], $rowDetail['type']) }}/5</strong>
                            </div>
                            <div class="comment-star">
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <span style="width: {{ Comment::avgStar($rowDetail['id'], $rowDetail['type']) }}%">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </span>
                            </div>
                            <div class="comment-count"><a>{{ $countComment }} đánh giá</a></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4 mb-4">
                        <div class="comment-progress rate-5">
                            <span class="progress-num">5</span>
                            <div class="progress">
                                <div class="progress-bar" id="has-rate"
                                    style="width: {{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 5) }}%">
                                    <span class="sr-only"></span>
                                </div>
                            </div>
                            <span
                                class="progress-total">{{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 5) }}%</span>
                        </div>
                        <div class="comment-progress rate-4">
                            <span class="progress-num">4</span>
                            <div class="progress">
                                <div class="progress-bar" id="has-rate"
                                    style="width: {{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 4) }}%">
                                    <span class="sr-only"></span>
                                </div>
                            </div>
                            <span
                                class="progress-total">{{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 4) }}%</span>
                        </div>
                        <div class="comment-progress rate-3">
                            <span class="progress-num">3</span>
                            <div class="progress">
                                <div class="progress-bar" id="has-rate"
                                    style="width: {{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 3) }}%">
                                    <span class="sr-only"></span>
                                </div>
                            </div>
                            <span
                                class="progress-total">{{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 3) }}%</span>
                        </div>
                        <div class="comment-progress rate-2">
                            <span class="progress-num">2</span>
                            <div class="progress">
                                <div class="progress-bar" id="has-rate"
                                    style="width: {{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 2) }}%">
                                    <span class="sr-only"></span>
                                </div>
                            </div>
                            <span
                                class="progress-total">{{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 2) }}%</span>
                        </div>
                        <div class="comment-progress rate-1">
                            <span class="progress-num">1</span>
                            <div class="progress">
                                <div class="progress-bar" id="has-rate"
                                    style="width: {{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 1) }}%">
                                    <span class="sr-only"></span>
                                </div>
                            </div>
                            <span
                                class="progress-total">{{ Comment::perScore($rowDetail['id'], $rowDetail['type'], 1) }}%</span>
                        </div>
                    </div>
                    <div class="col-sm-12 mt-3 text-center">
                        <button type="button" class="btn btn-primary btn-write-comment py-2 px-3">Viết đánh giá</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Write comment -->
    <div class="comment-write mb-4" id="comment-write">
        <div class="card">
            <div class="card-header text-uppercase"><strong>Đánh giá {{ $rowDetail['name' . $lang] }}</strong></div>
            <div class="card-body">
                <form id="form-comment" action="" method="post" enctype="multipart/form-data">
                    <div class="response-review"></div>
                    <div class="row">
                        <div class="col-12 col-lg-12">

                            <div class="form-group mb-2">
                                <label for="review-content" class="mb-2">Viết nhận xét của bạn vào bên dưới:</label>
                                <textarea class="form-control text-sm" name="dataReview[content]" id="review-content"
                                    placeholder="Nhận xét của bạn về sản phẩm này *" rows="11"></textarea>
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2"><strong>Hình ảnh: (Tối đa 3 hình)</strong></label>
                                <div class="review-file-uploader">
                                    <input type="file" id="review-file-photo" name="review-file-photo">
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Bạn cảm thấy thế nào về sản phẩm? (Chọn sao):</label>
                                <div class="review-rating-star mt-2 mb-4 text-center">
                                    <div class="review-rating-star-icon">
                                        <i class="fa fa-star star-empty" data-value="1"></i>
                                        <i class="fa fa-star star-empty" data-value="2"></i>
                                        <i class="fa fa-star star-empty" data-value="3"></i>
                                        <i class="fa fa-star star-empty" data-value="4"></i>
                                        <i class="fa fa-star star-empty" data-value="5"></i>
                                    </div>
                                    <input type="number" class="review-rating-star-input hidden"
                                        name="dataReview[star]" id="review-star" data-min="1" data-max="5">
                                </div>
                            </div>

                            @if ($isLoggedInMember)
                                <div class="form-group mb-2">
                                    <div class="alert alert-light border text-sm mb-0">
                                        Bạn đang đánh giá với tài khoản
                                        <strong>{{ $memberName ?: 'đã đăng nhập' }}</strong>.
                                    </div>
                                </div>
                            @else
                                <div class="form-group mb-2">
                                    <div class="row row-10">
                                        <div class="col-4 mg-col-10">
                                            <input type="text" class="form-control text-sm"
                                                name="dataReview[fullname]" id="review-fullname"
                                                placeholder="Họ tên *" required>
                                        </div>
                                        <div class="col-4 mg-col-10">
                                            <input type="text" class="form-control text-sm" name="dataReview[phone]"
                                                id="review-phone" placeholder="Số điện thoại *">
                                        </div>
                                        <div class="col-4 mg-col-10">
                                            <input type="text" class="form-control text-sm" name="dataReview[email]"
                                                id="review-email" placeholder="Email *" required>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-comment btn-warning text-capitalize py-2 px-3">Gửi đánh giá</button>
                    <input type="hidden" name="dataReview[id_variant]" value="{{ $rowDetail['id'] }}">
                    <input type="hidden" name="dataReview[type]" value="{{ $rowDetail['type'] }}">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>

    <!-- Lists comment -->
    @if ($rowComment->isNotEmpty())
        <div class="comment-lists">
            <div class="card">
                <div class="card-header text-uppercase"><strong>Các bình luận khác</strong></div>
                <div class="card-body pt-5 pb-3">
                    <div class="comment-load">
                        @foreach ($rowComment as $v_lists)
                            @php $v_lists['name'] = $rowDetail['namevi']; @endphp
                            @component('component.comment.lists', ['params' => $v_lists])
                            @endcomponent
                        @endforeach
                    </div>
                    @if ($countComment > 2)
                        <div class="comment-load-more-control text-center mt-4">
                            <form id="form-load-comment" action="" method="post"
                                enctype="multipart/form-data">
                                <input type="hidden" name="dataLoad[limit]" id="load-comment-limit" value="2">
                                <input type="hidden" name="dataLoad[id]" value="{{ $rowDetail['id'] }}">
                                <input type="hidden" name="dataLoad[type]" value="{{ $rowDetail['type'] }}">
                                <input type="hidden" name="dataLoad[count]" id="load-comment-count"
                                    value="{{ $countComment }}">
                                <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                                <button type="submit"
                                    class="comment-more btn btn-sm btn-primary rounded-0 w-100 font-weight-bold py-2 px-3"
                                    title="Tải thêm bình luận">Tải thêm bình luận</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
    <link href="assets/fileuploader/font-fileuploader.css" rel="stylesheet">
    <link href="assets/fileuploader/jquery.fileuploader.min.css" rel="stylesheet">
    <link href="assets/fileuploader/jquery.fileuploader-theme-dragdrop.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="assets/js/comment.js"></script>
    <script src="assets/fileuploader/jquery.fileuploader.min.js"></script>
@endpush

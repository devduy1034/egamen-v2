@php
    $rowComment = $rowDetail->getComment()->skip(0)->take(2)->get();
    $countComment = $rowDetail->getComment()->count();
@endphp

<div class="comment-page">
    <!-- Statistic comment -->
    <div class="comment-statistic mb-4">
        <div class="card">
            <div class="card-header text-uppercase"><strong>Đánh giá sản phẩm</strong></div>
            <div class="card-body">
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
                            <div class="comment-count"><a>({{ $countComment }} nhận xét)</a></div>
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
                    <div class="col-sm-12 col-lg-4">
                        <div class="text-center">
                            <p class="mb-2">Chia sẻ nhận xét về sản phẩm</p>
                            <button type="button" class="btn btn-sm btn-warning btn-write-comment py-2 px-3">Viết nhận
                                xét của
                                bạn</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Write comment -->
    <div class="comment-write mb-4" id="comment-write">
        <div class="card">
            <div class="card-header text-uppercase"><strong>Gửi nhận xét của bạn</strong></div>
            <div class="card-body">
                <form id="form-comment" action="" method="post" enctype="multipart/form-data">
                    <div class="response-review"></div>
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group mb-2">
                                <label class="mb-2">1. Đánh giá của bạn về sản phẩm này:</label>
                                <div class="review-rating-star">
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
                            <div class="form-group mb-2">
                                <label for="review-title" class="mb-2">2. Tiêu đề của nhận xét:</label>
                                <input type="text" class="form-control text-sm" name="dataReview[title]"
                                    id="review-title" placeholder="Nhập tiêu đề nhận xét *" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="review-content" class="mb-2">3. Viết nhận xét của bạn vào bên
                                    dưới:</label>
                                <textarea class="form-control text-sm" name="dataReview[content]" id="review-content"
                                    placeholder="Nhận xét của bạn về sản phẩm này *" rows="11"></textarea>
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-2">4. Thông tin liên hệ:</label>
                                <div class="row row-10">
                                    <div class="col-4 mg-col-10">
                                        <input type="text" class="form-control text-sm"
                                            name="dataReview[fullname]" id="review-fullname"
                                            placeholder="Nhập họ tên liên hệ *" required>
                                    </div>
                                    <div class="col-4 mg-col-10">
                                        <input type="text" class="form-control text-sm" name="dataReview[phone]"
                                            id="review-phone" placeholder="Nhập số điện thoại liên hệ *">
                                    </div>
                                    <div class="col-4 mg-col-10">
                                        <input type="text" class="form-control text-sm" name="dataReview[email]"
                                            id="review-email" placeholder="Nhập email liên hệ *" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mb-2 review-preview">
                                <div class="row">
                                    <div class="col-4">
                                        <img class="w-100" onerror="this.src='assets/images/noimage.png';"
                                            src="{{ assets_photo('product', '150x150x1', $rowDetail['photo'], 'thumbs') }}"
                                            alt="{{ $rowDetail['namevi'] }}" title="{{ $rowDetail['namevi'] }}" />
                                    </div>
                                    <div class="col-8">
                                        <h6 class="text-uppercase">{{ $rowDetail['name' . $lang] }}</h6>
                                        <div class="comment-star">
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <span style="width: {{ Comment::avgStar() }}%">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </span>
                                        </div>
                                        <div class="comment-count mb-2"><strong>({{ count($rowComment) }} nhận
                                                xét)</strong></div>
                                        <div class="text-split mb-0">
                                            {{ Func::decodeHtmlChars($rowDetail['desc' . $lang]) }}</div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="form-group mb-2">
                                <label class="mb-2"><strong>Hình ảnh: (Tối đa 3 hình)</strong></label>
                                <div class="review-file-uploader">
                                    <input type="file" id="review-file-photo" name="review-file-photo">
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-2"><strong>Video clip:</strong></label>
                                <div
                                    class="review-poster-video-avatar avatar d-flex align-items-center justify-content-start mb-3">
                                    <div class="avatar-zone me-3">
                                        <label class="avatar-label d-block mb-0" id="review-poster-video-label"
                                            for="review-poster-video">
                                            <div class="avatar-detail border rounded overflow-hidden"
                                                id="review-poster-video-preview">

                                                <img class="w-100" onerror="this.src='assets/images/noimage.png';"
                                                    src="assets/images/noimage.png" alt="{{ $rowDetail['namevi'] }}"
                                                    title="{{ $rowDetail['namevi'] }}" />
                                            </div>
                                            <input type="file" class="d-none" name="review-poster-video"
                                                id="review-poster-video">
                                        </label>
                                    </div>
                                    <div class="avatar-dimension">
                                        <p class="mb-0">Hình ảnh đại diện</p>
                                        <p class="mb-0">Định dạng:
                                            <strong>{{ config('upload.type_img') }}</strong>
                                        </p>
                                    </div>
                                </div>
                                <div class="custom-file mb-2">
                                    <div class="input-group mb-2">
                                        <input type="file" class="form-control" name="review-file-video"
                                            id="review-file-video" lang="vi">
                                        <label class="input-group-text" for="inputGroupFile02">Video</label>
                                    </div>

                                </div>

                                <div class="video-config">
                                    <span class="d-inline-block me-2">Định dạng:
                                        <strong>{{ config('upload.type_video') }}</strong></span>
                                    <span class="d-inline-block">Tối đa:
                                        <strong>100Mb</strong></span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-warning text-capitalize py-2 px-3">Gửi nhận
                        xét</button>
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
                                <input type="hidden" name="dataLoad[limit]" id="load-comment-limit"
                                    value="2">
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
    <link href="assets/css/comment.css" rel="stylesheet">
    <link href="assets/fileuploader/font-fileuploader.css" rel="stylesheet">
    <link href="assets/fileuploader/jquery.fileuploader.min.css" rel="stylesheet">
    <link href="assets/fileuploader/jquery.fileuploader-theme-dragdrop.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="assets/js/comment.js"></script>
    <script src="assets/fileuploader/jquery.fileuploader.min.js"></script>
@endpush

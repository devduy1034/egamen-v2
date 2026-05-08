@foreach ($params as $v_reply)
    <div class="comment-replies-item">
        <div class="comment-replies-letter {{ $v_reply['poster'] }}">{{ Comment::subName($v_reply['fullname']) }}</div>
        <div class="comment-replies-info">
            <div class="comment-replies-name mb-1">{{ $v_reply['fullname'] }}<span
                    class="font-weight-normal small text-muted mx-2">{{ Comment::timeAgo($v_reply['date_posted']) }}</span>{!! $v_reply['poster'] == 'admin'
                        ? '<span class="font-weight-normal text-info ml-2">(Phản hồi bởi Quản trị viên)</span>'
                        : '' !!}
            </div>
            <div class="comment-replies-content">{{ nl2br(Func::decodeHtmlChars($v_reply['content'])) }}</div>
        </div>
    </div>
@endforeach
@if (count($params) > 1)
    <p class="view-more-replies">&#10551; Xem tất cả {{ count($params) }} bình luận</p>
@endif

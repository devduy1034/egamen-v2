@foreach ($list as $params)
    @php
        $rowReplies = $params->replies()->get();
        $album = Comment::photo($params['id'], $params['type']);
    @endphp
    <div class="comment-item">
        <div class="comment-item-poster">
            <div class="comment-item-letter">{{ Comment::subName($params['fullname']) }}</div>
            <div class="comment-item-name">{{ $params['fullname'] }}</div>
            <div class="comment-item-posttime">{{ Comment::timeAgo($params['date_posted']) }}</div>
        </div>

        <div class="comment-item-information">
            <div class="comment-item-rating mb-2 w-clear">
                <div class="comment-item-star comment-star mb-0">
                    <i class="far fa-star"></i>
                    <i class="far fa-star"></i>
                    <i class="far fa-star"></i>
                    <i class="far fa-star"></i>
                    <i class="far fa-star"></i>
                    <span style="width: {{ Comment::scoreStar($params['star']) }}%;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </span>
                </div>
                <div class="comment-item-title">{{ Func::decodeHtmlChars($params['title']) }}</div>
            </div>

            <div class="comment-item-content mb-2">{{ nl2br(Func::decodeHtmlChars($params['content'])) }}
            </div>

            <a class="btn-reply-comment d-inline-block align-top text-decoration-none text-primary mb-2"
                href="javascript:void(0)" data-name="{{ $params['fullname'] }}">Trả lời</a>

            @if (!empty($params['photo']) || !empty($params['video']) || !empty($album))
                @component('component.comment.media', ['params' => $params, 'album' => $album])
                @endcomponent
            @endif

            @if ($rowReplies->isNotEmpty())
                <div class="comment-replies mt-3">
                    @component('component.comment.replies', ['params' => $rowReplies])
                    @endcomponent

                </div>
            @endif
            @component('component.comment.reply', ['params' => $params])
            @endcomponent
        </div>
    </div>
@endforeach

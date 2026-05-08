@extends('layout')

@section('content')
    @php
        $rowReplies = $item->replies('admin')->get();
        $album = Comment::photo($item['id'], $item['type']);
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce">
            <div class="row">
                <div class="col-12 col-lg-8">

                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin {{ $configMan->title_main }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group  last:!mb-0 md:!mb-0">
                                <div class="comment-item">
                                    <div class="comment-item-poster">
                                        <div class="comment-item-letter">{{ Comment::subName($item['fullname']) }}</div>
                                        <div class="comment-item-name">{{ $item['fullname'] }}</div>
                                        <div class="comment-item-posttime">{{ Comment::timeAgo($item['date_posted']) }}
                                        </div>
                                    </div>

                                    <div class="comment-item-information">
                                        <div class="comment-item-title mb-2">{{ Func::decodeHtmlChars($item['title']) }}
                                        </div>
                                        <div class="comment-item-content mb-2">
                                            {{ nl2br(Func::decodeHtmlChars($item['content'])) }}
                                        </div>

                                        <a class="btn-reply-comment d-inline-block align-top text-decoration-none text-primary mb-2"
                                            href="javascript:void(0)" data-name="{{ $item['fullname'] }}">Trả lời</a>

                                        @if (!empty($item['photo']) || !empty($item['video']) || !empty($album))
                                            @component('component.comment.media', ['params' => $item, 'album' => $album])
                                            @endcomponent
                                        @endif

                                        @if ($rowReplies->isNotEmpty())
                                            <!-- Replies -->
                                            <div class="comment-replies mt-3">
                                                @component('component.comment.replies', ['params' => $rowReplies])
                                                @endcomponent

                                            </div>
                                        @endif
                                        @component('component.comment.reply', ['params' => $item, 'linkSave' => url('admin', ['com' => $com, 'act' => 'save', 'type' => $type], ['id' => $item['id'] ?? 0])])
                                        @endcomponent
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card mb-4 form-group-category">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Số sao</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group last:!mb-0">
                                <div class="comment-item-rating mb-2 w-clear">
                                    <div class="comment-item-star comment-star mb-0">
                                        <i class="ti ti-star"></i>
                                        <i class="ti ti-star"></i>
                                        <i class="ti ti-star"></i>
                                        <i class="ti ti-star"></i>
                                        <i class="ti ti-star"></i>
                                        <span style="width: {{ Comment::scoreStar($item['star']) }}%;">
                                            <i class="ti ti-star"></i>
                                            <i class="ti ti-star"></i>
                                            <i class="ti ti-star"></i>
                                            <i class="ti ti-star"></i>
                                            <i class="ti ti-star"></i>
                                        </span>
                                    </div>
                                    <div class="comment-item-title">{{ Func::decodeHtmlChars($item['title']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Tình trạng
                                {{ $configMan->title_main }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group last:!mb-0">
                                @php $status_array = !empty($item['status']) ? explode(',', $item['status']) : []; @endphp
                                @if (!empty($configMan->status))
                                    @foreach ($configMan->status as $key => $value)
                                        <div class="form-group d-inline-block last:!mb-0 mb-2 me-5">
                                            <label for="{{ $key }}-checkbox"
                                                class="d-inline-block align-middle mb-0 mr-2"><?= $value ?>:</label>
                                            <label class="switch switch-success" data-bs-toggle="tooltip"
                                                data-bs-trigger="hover" data-bs-placement="bottom" aria-label="Duyệt tin"
                                                data-bs-original-title="Duyệt tin">
                                                @component('component.switchButton', [
                                                    'keyC' => $key,
                                                    'idC' => $item['id'],
                                                    'tableC' => 'comment',
                                                    'status_arrayC' => $status_array,
                                                ])
                                                @endcomponent
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@pushonce('styles')
<link rel="stylesheet" href="@asset('assets/admin/fancybox5/fancybox.css')">
@endpushonce
@pushonce('scripts')
<script src="@asset('assets/admin/fancybox5/fancybox.umd.js')"></script>
<script src="@asset('assets/admin/fancybox5/fancybox.umd.js')"></script>
<script type="text/javascript">
    Fancybox.bind('[data-fancybox]', {});
</script>
@endpushonce

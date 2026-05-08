<div class="response-reply-admin">
    <form class="mt-3" action="{{$linkSave}}" method="post" enctype="multipart/form-data">
        <div class="form-group mb-2">
            <textarea class="form-control text-sm" placeholder="Viết câu trả lời của bạn" name="data[content]" id="reply-content"
                data-name="@{{ $params['fullname'] }}:" rows="5"></textarea>
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-sm btn-warning me-2 py-2 px-3">Gửi trả lời</button>
            <button type="button" class="btn btn-sm btn-secondary btn-cancel-reply py-2 px-3">Hủy bỏ</button>
        </div>
        <input type="hidden" name="data[id_parent]" value="{{ $params['id'] }}">
        <input type="hidden" name="data[id_variant]" value="{{ $params['id_variant'] }}">
        <input type="hidden" name="data[type]" value="{{ $params['type'] }}">
        <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
    </form>
</div>

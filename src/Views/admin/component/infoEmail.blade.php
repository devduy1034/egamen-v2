<div class="card email-card-last mx-sm-4 mx-3 mt-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center mb-sm-0 mb-3">
            <img src="@asset('assets/admin/img/avatars/user.png')" alt="user-avatar" class="flex-shrink-0 rounded-circle me-3" height="40"
                width="40" />
            <div class="flex-grow-1 ms-1">
                @if (!empty($items['fullname']))
                    <h6 class="m-0">{{ $items['fullname'] }}</h6>
                @endif
                @if (!empty($items['email']))
                    <p class="text-muted mb-0">{{ $items['email'] }}</p>
                @endif
                @if (!empty($items['phone']))
                    <p class="text-muted mb-0">{{ $items['phone'] }}</p>
                @endif
                @if (!empty($items['address']))
                    <p class="text-muted mb-0">{{ $items['address'] }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        @if (!empty($items['subject']))
            <h4>{{ $items['subject'] }}</h4>
        @endif
        @if (!empty($items['content']))
            <div>{{ $items['content'] }}</div>
        @endif
        @if (!empty($items['file_attach']))
            <a class="btn btn-sm bg-gradient-primary text-white d-inline-block align-middle p-2 rounded mt-4" href="{{ upload('file', $items['file_attach']) }}"><i class="fas fa-download mr-2"></i> File</a>
        @endif
    </div>
</div>

@if ($productAjax->isNotEmpty())
    @if ($other == 1)
        <div class="p-relative">
            <div class="row">
                @foreach ($productAjax as $v)
                    <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                        @include('component.itemProduct', ['product' => $v])
                    </div>
                @endforeach
            </div>
            {!! $productAjax->appends(request()->query())->links('pagination.paging-ajax') !!}
        @elseif($other == 2)
        <div class="p-relative">
            <div class="row">
                @foreach ($productAjax as $v)
                    <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                        @include('component.itemProduct', ['product' => $v])
                    </div>
                @endforeach
            </div>
            <div class="xemthem-pro">
                <a class="text-decoration-none" href="{{ $slug }}">{{ __('web.xemthem') }}</a>
            </div>
        </div>
        @elseif($other == 3)
            <div class="p-relative" id="product-list-{{ $section }}">
                <div class="row">
                    @foreach ($productAjax as $v)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                            @include('component.itemProduct', ['product' => $v])
                        </div>
                    @endforeach
                    @if ($currentPage < $lastPage)
                        <div class="col-12 button">
                            <button id="load-more" data-section="{{ $section }}" data-page="{{ $currentPage + 1 }}"
                                class="btn btn-primary">{{ __('web.xemthem') }}</button>
                        </div>
                    @endif
                </div>
            </div>
    @endif
@else
    <div class="alert alert-warning w-100" role="alert">
        <strong>{{ __('web.dangcapnhatdulieu') }}</strong>
    </div>
@endif

@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-center">

        <div class="flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-center">
            <div>
                <ul class="pagination">
                    {{-- <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" aria-hidden="true">Page {{$paginator->currentPage()}}/{{$paginator->lastPage()}}</span>
                    </li> --}}
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true"><<</span>
                        </li>
                    @else

                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url(1) }}" rel="prev"><<</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">></a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" rel="next" >>></a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif

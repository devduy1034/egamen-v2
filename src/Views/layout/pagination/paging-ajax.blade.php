@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-center">


        <div class=" flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-center">


            <div>
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item page-item-ajax first disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&laquo;</span>
                        </li>
                        <li class="page-item page-item-ajax previous disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item page-item-ajax first">
                            <span class="page-link" onclick="loadPaging('{{ $paginator->url(1) }}','{{ request()->query('eShow') }}')" rel="prev" aria-label="@lang('pagination.previous')">&laquo;</span>
                        </li>
                        <li class="page-item page-item-ajax previous">
                            <span class="page-link" onclick="loadPaging('{{ $paginator->previousPageUrl() }}','{{ request()->query('eShow') }}')" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</span>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item page-item-ajax disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item page-item-ajax active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item page-item-ajax"><span onclick="loadPaging('{{ $url }}','{{request()->query('eShow')}}')" class="page-link" >{{ $page }}</span></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item page-item-ajax next">
                            <span class="page-link" onclick="loadPaging('{{ $paginator->nextPageUrl() }}','{{request()->query('eShow')}}')" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</span>
                        </li>
                        <li class="page-item page-item-ajax last">
                            <span class="page-link" onclick="loadPaging('{{ $paginator->url($paginator->lastPage()) }}','{{ request()->query('eShow') }}')" rel="next" aria-label="@lang('pagination.next')">&raquo;</span>
                        </li>
                    @else
                        <li class="page-item page-item-ajax next disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                        <li class="page-item page-item-ajax last disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&raquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif

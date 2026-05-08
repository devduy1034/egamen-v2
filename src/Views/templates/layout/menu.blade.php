{{-- <div class="menu-head-left" x-data="{ open: false }" @mouseover="open = true" @mouseleave="open = false">
    <span class="title-menu"><i class="fa-solid fa-bars me-2"></i> Tất cả danh mục <i
            class="fa-solid fa-caret-down ms-2"></i></span>
    <div class="menu-product-list" x-cloak x-show="open" x-transition>
        <ul>
            @foreach ($productListMenu ?? [] as $vlist)
            <li x-data="{ open: false }" @mouseover="open = true" @mouseleave="open = false"><a
                    class="transition group-hover:text-[#fed402]"
                    href="{{ url('slugweb', ['slug' => $vlist['slug'.$lang]]) }}" title="{!! $vlist['namevi'] !!}">{{
                    $vlist['namevi'] }}
                    {!! $vlist->getCategoryCats->isNotEmpty() ? '<span class="icon-down">&#8250;</span>' : '' !!}</a>
                @if ($vlist->getCategoryCats->isNotEmpty())
                <ul x-show="open" x-transition>
                    @foreach ($vlist->getCategoryCats ?? [] as $vcat)
                    <li>
                        <a class="transition group-hover:text-[#fed402]"
                            href="{{ url('slugweb', ['slug' => $vcat['slug'.$lang]]) }}"
                            title="{!! $vcat['namevi'] !!}">{!! $vcat['namevi'] !!} <span>Xem tất cả
                                &#8250;</span></a>
                        <ul>
                            @foreach ($vcat->getCategoryItems() ?? [] as $vitem)
                            <li>
                                <a class="transition group-hover:text-[#fed402]"
                                    href="{{ url('slugweb', ['slug' => $vitem['slug'.$lang]]) }}"
                                    title="{!! $vitem['namevi'] !!}">{!! $vitem['namevi'] !!}</a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</div> --}}

<nav class="menu">
    <ul class="flex flex-wrap items-center justify-between ulmn gap-10">
        <li><a class="transition active" href="{{ url('home') }}" title="Trang chủ">Trang chủ</a></li>
        <li><a class="transition {{ ($com ?? '') == 'gioi-thieu' ? 'active' : '' }}" href="{{ url('gioi-thieu') }}"
                title="Giới thiệu">Giới thiệu</a></li>
        <li><a class="transition {{ ($com ?? '') == 'hang' ? 'active' : '' }}" href="{{ url('hang') }}"
                title="Hãng">Hãng</a></li>
        <li><a class="transition {{ ($com ?? '') == 'san-pham' ? 'active' : '' }}" href="san-pham" title="Sản phẩm">Sản
                phẩm</a>
            <ul>
                @foreach ($productListMenu ?? [] as $vlist)
                    <li><a class="transition group-hover:text-[#fed402]"
                            href="{{ url('slugweb', ['slug' => $vlist['slug' . $lang]]) }}"
                            title="{!! $vlist['namevi'] !!}">{{ $vlist['namevi'] }}</a>
                        @if ($vlist->getCategoryCats->isNotEmpty())
                            <ul>
                                @foreach ($vlist->getCategoryCats ?? [] as $vcat)
                                    <li>
                                        <a class="transition group-hover:text-[#fed402]"
                                            href="{{ url('slugweb', ['slug' => $vcat['slug' . $lang]]) }}"
                                            title="{!! $vcat['namevi'] !!}">{!! $vcat['namevi'] !!}</a>
                                        <ul>
                                            @foreach ($vcat->getCategoryItems() ?? [] as $vitem)
                                                <li>
                                                    <a class="transition group-hover:text-[#fed402]"
                                                        href="{{ url('slugweb', ['slug' => $vitem['slug' . $lang]]) }}"
                                                        title="{!! $vitem['namevi'] !!}">{!! $vitem['namevi'] !!}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                                
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </li>
        <li class="group"><a class="transition  {{ ($com ?? '') == 'album' ? 'active' : '' }}"
                href="{{ url('album') }}" title="Album">Album</a></li>
        <li class="group"><a class="transition  {{ ($com ?? '') == 'video' ? 'active' : '' }}"
                href="{{ url('video') }}" title="Video">Video</a></li>
        <li class="group"><a class="transition  {{ ($com ?? '') == 'tin-tuc' ? 'active' : '' }}"
                href="{{ url('tin-tuc') }}" title="Tin tức">Tin tức</a></li>
        <li class="group"><a class="transition  {{ ($com ?? '') == 'lien-he' ? 'active' : '' }}"
                href="{{ url('lien-he') }}" title="Liên hệ">Liên hệ</a></li>
    </ul>
</nav>

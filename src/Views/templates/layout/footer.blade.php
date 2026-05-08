<footer>
    <div class="info-footer">
        <div class="wrap-content">
            <div class="flex-footer">
                <div class="box-footer">
                    <a class="logo-footer" href="">
                        @component('component.image', [
                            'class' => '',
                            'w' => config('type.static.' . $footer['type'] . '.images.photo.width'),
                            'h' => config('type.static.' . $footer['type'] . '.images.photo.height'),
                            'z' => config('type.static.' . $footer['type'] . '.images.photo.opt'),
                            'is_watermarks' => false,
                            'destination' => 'news',
                            'image' => $footer['photo'] ?? '',
                            'alt' => $setting['name' . $lang] ?? '',
                        ])
                        @endcomponent
                    </a>
                    <div class="title-footer">{{ $footer['name' . $lang] }}</div>
                    <div class="desc-footer">
                        {!! Func::decodeHtmlChars($footer['content' . $lang] ?? '') !!}
                    </div>
                </div>

                <div class="box-footer">
                    <div class="title-footer">Giới thiệu & Thông tin</div>
                    <ul class="footer-ul d-flex flex-wrap justify-content-between">
                        <li>
                            <a class="text-decoration-none" href="gioi-thieu" title="Giới thiệu">
                                <i class="fa-solid fa-angle-right"></i> Giới thiệu
                            </a>
                        </li>
                        <li>
                            <a class="text-decoration-none" href="he-thuong-cua-hang" title="Hệ thống cửa hàng">
                                <i class="fa-solid fa-angle-right"></i> Hệ thống cửa hàng
                            </a>
                        </li>
                        <li>
                            <a class="text-decoration-none" href="cau-hoi-thuong-gap" title="Câu hỏi thường gặp">
                                <i class="fa-solid fa-angle-right"></i> Câu hỏi thường gặp
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="box-footer">
                    <div class="title-footer">Hỗ trợ khách hàng</div>
                    <ul class="footer-ul d-flex flex-wrap justify-content-between">
                        <li>
                            <a class="text-decoration-none" href="lien-he" title="Liên hệ">
                                <i class="fa-solid fa-angle-right"></i> Liên hệ
                            </a>
                        </li>
                        @foreach ($support as $v)
                            <li>
                                <a class="text-decoration-none" href="{{ $v[$sluglang] }}"
                                    title="{{ $v['name' . $lang] }}">
                                    <i class="fa-solid fa-angle-right"></i> {{ $v['name' . $lang] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="box-footer">
                    <div class="title-footer">Fanpage</div>
                    @include('component.fanpage', ['fanpage' => $optSetting['fanpage']])
                </div>
            </div>
        </div>
    </div>

    <div class="footer-powered">
        <div class="wrap-content">
            <div class="flex-powered">
                <div class="copyright">
                    Copyright © 2026 - {{ $setting['name' . $lang] }}. All rights reserved.
                </div>
                <div class="statistic">
                    <span>{{ __('web.dangonline') }}: {{ Statistic::getOnline() }}</span>
                    <span>{{ __('web.truycapngay') }}: {{ Statistic::getTodayRecord() }}</span>
                    <span>{{ __('web.trongtuan') }}: {{ Statistic::getWeekRecord() }}</span>
                    <span>{{ __('web.trongthang') }}: {{ Statistic::getMonthRecord() }}</span>
                    <span>{{ __('web.tongtruycap') }}: {{ Statistic::getTotalRecord() }}</span>
                </div>
            </div>
        </div>
    </div>
</footer>

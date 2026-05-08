<div class="wrap-news-main padding-main">
    <div class="wrap-content">
        <div class="title-main-box ">
            <div class="title-main">
                <h4>
                    {!! $title !!}
                </h4>
            </div>
        </div>
        <div class="box-newsnb-main ">
            <div class="owl-page owl-carousel owl-theme"
                 data-items="screen:0|items:1|margin:10,screen:425|items:1|margin:10,screen:575|items:1|margin:25,screen:767|items:2|margin:25,screen:991|items:2|margin:25,screen:1199|items:4|margin:25"
                 data-rewind="1" data-autoplay="1" data-loop="0" data-lazyload="0" data-mousedrag="1" data-touchdrag="1"
                 data-smartspeed="300" data-autoplayspeed="500" data-autoplaytimeout="3500" data-dots="0" data-nav="1"
                 data-navcontainer="">
                @foreach ($newsnb as $k => $value)
                    <div class="newsnb-box hover-box">
                        <div class="newsnb-image">
                            <a class="scale-img hover-img" href="{{ url('slugweb',['slug'=>$value['slugvi']]) }}" title="{{ $value['namevi'] }}">
                                <img onerror="this.src='assets/images/noimage.png';"
                                     src="{{ assets_photo('news','500x500x1',$value['photo'],'thumbs')}}" alt="logo" title="logo"
                                     width="600" height="250" />
                            </a>
                        </div>
                        <div class="newsnb-info hover-info">
                            <a class="text-decoration-none" href="{{ url('slugweb',['slug'=>$value['slugvi']]) }}">
                                <h3 class="hover-main text-split">{{ $value['namevi'] }}</h3>
                                <p class="hover-main text-split">{{ $value['descvi'] }}</p>
                                <span>{{__('web.xemchitiet')}}</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

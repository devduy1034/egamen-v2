<HTML>

<HEAD>
    <TITLE>:: Thông báo ::</TITLE>
    <base href="{{ config('app.admin') }}" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="robots" content="noodp,noindex,nofollow" />
    <link rel="stylesheet" href='@asset("assets/admin/vendor/fonts/fontawesome.css")' />
    <link rel="stylesheet" href='@asset("assets/admin/vendor/css/rtl/core.css")' class="template-customizer-core-css" />
    <style type="text/css">
        body { background: #eee }
        #alert {
            background: #fff;
            padding: 20px;
            margin: 30px auto;
            border-radius: 3px;
            -webkit-box-shadow: 0px 0px 3px 0px rgba(50, 50, 50, 0.3);
            -moz-box-shadow: 0px 0px 3px 0px rgba(50, 50, 50, 0.3);
            box-shadow: 0px 0px 3px 0px rgba(50, 50, 50, 0.3);
            margin-top: 100px;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        #alert .fas {
            font-size: 40px;
            width: 50px;
            height: 50px;
            line-height: 50px;
            color: #fff;
            border-radius: 50%;
        }
        #alert .rlink { margin: 10px 0px; }
        #alert .title { text-transform: uppercase; font-weight: bold; margin: 10px; }
        .fasuccess, .success { background: #5cb85c; }
        .fadanger, .danger { background: #c02026; }
        #process-bar {
            width: 0%;
            -webkit-transition: all 0.3s !important;
            transition: all 0.3s !important;
        }
        .progress-bar.progress-bar-success { background: #5cb85c; }
    </style>
</HEAD>

<BODY>
    @php
        $transferUrl = !empty($page_transfer) ? $page_transfer : '';
    @endphp
    @if(!empty($transferUrl) && !empty($numb))
        <script type="text/javascript">
            window.location.replace(@json($transferUrl));
        </script>
    @endif
    <div id="alert">
        <i class="fas {{ $numb ? 'fa-solid fa-check fasuccess' : 'fa-solid fa-exclamation fadanger' }}"></i>
        <div class="title">Thông báo</div>
        <div class="message alert {{ $numb ? 'alert-success' : 'alert-danger' }}">{!! @$showtext !!}</div>
        <div class="rlink">(
            <a href="{{ $transferUrl ?: 'javascript:void(0)' }}" id="transfer-link"
                onclick="if('{{ $transferUrl }}'){window.location.href='{{ $transferUrl }}';}else if(window.history&&window.history.length>1){window.history.back();}return false;">
                Click vào đây nếu không muốn đợi lâu
            </a>)
        </div>
        <div class="progress">
            <div id="process-bar" class="progress-bar progress-bar-striped progress-bar-{{ $numb ? 'success' : 'danger' }} active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
    <script type="text/javascript">
        var elem = document.getElementById("process-bar");
        var pos = 0;
        setInterval(function() {
            pos += 1;
            elem.style.width = pos + '%';
        }, 40);
    </script>
</BODY>

</HTML>

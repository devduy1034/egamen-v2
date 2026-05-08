{!! cssminify()->set('css/animate.min.css');
cssminify()->set('css/style-tailwind.css');
cssminify()->set('swiper/swiper-bundle.min.css');
cssminify()->set('bootstrap/bootstrap.css');
cssminify()->set('confirm/confirm.css');
cssminify()->set('fontawesome640/all.css');
cssminify()->set('fotorama/fotorama.css');
cssminify()->set('fotorama/fotorama-style.css');
cssminify()->set('css/cart.css');
cssminify()->set('css/navigation.css');
cssminify()->set('css/style.css');
//cssminify()->set('css/main.css');
cssminify()->set('css/media.css');
echo cssminify()->get() !!}
@stack('styles')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,200..1000;1,200..1000&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">

<link rel="preload" href="assets/holdon/HoldOn.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript>
    <link rel="stylesheet" href="assets/holdon/HoldOn.css">
</noscript>
<link rel="preload" href="assets/holdon/HoldOn-style.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
<noscript>
    <link rel="stylesheet" href="assets/holdon/HoldOn-style.css">
</noscript>
<link rel="preload" href="assets/simplenotify/simple-notify.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
<noscript>
    <link rel="stylesheet" href="assets/simplenotify/simple-notify.css">
</noscript>
<link rel="preload" href="assets/fancybox5/fancybox.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
<noscript>
    <link rel="stylesheet" href="assets/fancybox5/fancybox.css">
</noscript>

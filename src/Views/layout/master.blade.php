<!DOCTYPE html>
<html lang="vi">
    <head>
        @include('layout.head')
        @include('layout.css')
    </head>
    <body>
        @yield('contentmaster')
        @include('layout.js')
        @include('layout.strucdata')
    </body>
</html>
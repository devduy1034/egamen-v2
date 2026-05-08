@extends('layout')
@section('content')
    <section class="account-page py-4">
        <div class="container">
            <div class="account-shell">
                @include('account.partials.sidebar')

                <div class="account-main">
                    @include('account.partials.hero')
                    @include('account.partials.alerts')
                    @include('account.partials.panel-profile')
                    @include('account.partials.panel-orders')
                    @include('account.partials.panel-address')
                    @include('account.partials.panel-wishlist')
                    @include('account.partials.panel-security')
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    @php
        cssminify()->set('css/account.css');
        echo cssminify()->get();
    @endphp
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @php
        jsminify()->set('js/account.js');
        echo jsminify()->get();
    @endphp
@endpush


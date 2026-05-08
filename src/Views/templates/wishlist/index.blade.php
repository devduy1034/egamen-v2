@extends('layout')
@section('content')
    <section class="account-page account-page--wishlist py-4">
        <div class="container">
            <div class="account-shell">
                <div class="account-main">
                    <div class="account-panel is-active">
                        <div class="account-panel__head">
                            <h2 class="account-panel__title">Sản phẩm yêu thích</h2>
                        </div>
                        <div class="account-wishlist-list js-wishlist-public-list">
                            <div class="alert alert-info">Đang tải danh sách yêu thích...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    @php
        cssminify()->set('css/account.css');
        echo cssminify()->get();
    @endphp
@endpush

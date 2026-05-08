@if (!empty($productAjax) && $productAjax->isNotEmpty())
    <div class="row row-product row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 home-recommend-grid">
        @foreach ($productAjax as $product)
            <div class="col">
                @component('component.itemProduct', ['product' => $product])
                @endcomponent
            </div>
        @endforeach
    </div>
@else
    <div class="home-recommend-empty">Chưa có gợi ý phù hợp lúc này.</div>
@endif

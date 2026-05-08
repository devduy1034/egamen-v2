<div class="row">
    @foreach ($propertieslist as $value)
        <div class="form-group col-6 col-lg-4">
            <label class="form-label d-block" for="id_list">{{ $value['namevi'] }}</label>
            {!! Func::getProperties(@$item['properties'], $value['id'], 'properties', 'san-pham', 'properties') !!}
        </div>
    @endforeach
    <div class="form-group col-6 col-lg-4">
        <label class="form-label d-block">Tổng số lượng sản phẩm</label>
        <input type="text" class="form-control properties-total-quantity" id="properties_total_quantity" value="0"
            readonly>
    </div>
</div>

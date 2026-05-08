@foreach ($propertiescard as $v)
    <div class="row">
        <label><span class="text-danger"></span></label>
        <div class="form-group col-4 col-lg-4">
            <div class="input-group">
                <input type="text" class="form-control" name="propertiescard[name_properties][]" placeholder="Tên"
                    value="{{ $v['namevi'] }}" readonly>
            </div>
        </div>
        <div class="form-group col-4 col-lg-4">
            <div class="input-group">
                <input type="text" class="form-control format-price price-origin-attr"
                    name="propertiescard[regular_price][]" placeholder="Giá bán" value="{{ $v['regular_price'] }}">
                <div class="input-group-append">
                    <div class="input-group-text"><strong>VNữ</strong></div>
                </div>
            </div>
        </div>
        <div class="form-group ">
            <div class="input-group">
                <input type="text" class="form-control format-price price-origin-attr"
                    name="propertiescard[sale_price][]" placeholder="Giá khuyến mãi" value="{{ $v['sale_price'] }}">
                <div class="input-group-append">
                    <div class="input-group-text"><strong>VNữ</strong></div>
                </div>
            </div>
        </div>
        <input type="hidden" class="form-control" name="propertiescard[id_properties][]"
            value='{{ $v['id_properties'] }}'>
    </div>
@endforeach

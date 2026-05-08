<div class="flex-propertiescard properties-card-grid">
    <div class="properties-card-left">
        <div class="form-group">
            <label class="form-label">Tên thuộc tính</label>
            <div class="input-group">
                <input type="text" class="form-control" name="propertiescard[name_properties][{{ $key }}]"
                    placeholder="Tên" value="{{ $value['namevi'] }}" readonly>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Giá bán</label>
            <div class="input-group">
                <input type="text" class="form-control format-price price-origin-attr regular_price"
                    id="regular_price_{{ $code }}" name="propertiescard[regular_price][{{ $key }}]"
                    placeholder="Giá bán" value="{{ $value['regular_price'] }}">
                <div class="input-group-text"><strong>VNĐ</strong></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Giá khuyến mãi</label>
            <div class="input-group">
                <input type="text" class="form-control format-price price-origin-attr sale_price"
                    id="sale_price_{{ $code }}" name="propertiescard[sale_price][{{ $key }}]"
                    placeholder="Giá khuyến mãi" value="{{ $value['sale_price'] }}">
                <div class="input-group-text"><strong>VNĐ</strong></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Chiết khấu</label>
            <div class="input-group">
                <input type="text" class="form-control format-price price-origin-attr discount"
                    id="discount_{{ $code }}" name="propertiescard[discount][{{ $key }}]"
                    placeholder="Giá khuyến mãi" value="{{ $value['discount'] }}" readonly>
                <div class="input-group-text"><strong>%</strong></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Number</label>
            <input type="text" class="form-control properties_number"
                name="propertiescard[number][{{ $key }}]" id="number_{{ $code }}"
                placeholder="Number" value="{{ !empty($value['number']) ? $value['number'] : $key + 1 }}">
        </div>
        <div class="form-group">
            <label class="form-label">Mã sản phẩm</label>
            <input type="text" class="form-control properties_code" name="propertiescard[code][{{ $key }}]"
                id="code_{{ $code }}" placeholder="Mã sản phẩm" value="{{ $value['code'] }}" readonly>
        </div>
        <div class="form-group">
            <label class="form-label">Số lượng</label>
            <input type="text" class="form-control properties_quantity"
                name="propertiescard[quantity][{{ $key }}]" id="quantity_{{ $code }}"
                placeholder="Số lượng" value="{{ $value['quantity'] }}">
        </div>



        <div class="form-group properties-status">
            <label class="form-label">Trạng thái</label>
            @php
                $statusValue = !empty($value['status']) ? $value['status'] : 'inactive';
            @endphp
            <select class="form-select select2 properties_status" name="propertiescard[status][{{ $key }}]"
                id="status_{{ $code }}" data-placeholder="Trạng thái">
                <option value="inactive" {{ $statusValue == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                <option value="active" {{ $statusValue == 'active' ? 'selected' : '' }}>Hoạt động</option>
            </select>
        </div>
        <input type="hidden" class="form-control" name="propertiescard[id_properties][{{ $key }}]"
            value='{{ $value['id_properties'] }}'>
    </div>

    <div class="properties-card-right">
        <div class="form-group properties-photo">
            <label class="form-label">Hình ảnh</label>
            <div class="d-flex gap-2 align-items-start w-100">
                <select class="form-select select2 properties_id_photo overflow-hidden"
                    name="propertiescard[id_photo][{{ $key }}]" id="id_photo_{{ $code }}"
                    data-placeholder="Chọn hình">
                    <option value="0">Chọn hình</option>
                    @if (!empty($gallery))
                        @foreach ($gallery as $g)
                            @php
                                $gid = is_array($g) ? $g['id'] ?? 0 : $g->id ?? 0;
                                $gphoto = is_array($g) ? $g['photo'] ?? '' : $g->photo ?? '';
                                $gthumb = !empty($gphoto) ? assets_photo('product', '70x70x1', $gphoto, 'thumbs') : '';
                                $gfull = !empty($gphoto) ? upload('product', $gphoto) : '';
                                if (empty($gthumb) && !empty($gfull)) {
                                    $gthumb = $gfull;
                                }
                            @endphp
                            <option value="{{ $gid }}" data-image="{{ $gthumb }}"
                                data-image-full="{{ $gfull }}"
                                {{ !empty($value['id_photo']) && (int) $value['id_photo'] == (int) $gid ? 'selected' : '' }}>
                                {{ !empty($gphoto) ? $gphoto : $gid }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <button type="button" class="btn btn-outline-secondary btn-sm refresh-properties-gallery"
                    data-target="#id_photo_{{ $code }}">Làm mới</button>
            </div>
            <div class="properties-photo-preview mt-2">
                @if (!empty($value['id_photo']) && !empty($gallery))
                    @php
                        $selectedThumb = '';
                        foreach ($gallery as $g) {
                            $gid = is_array($g) ? $g['id'] ?? 0 : $g->id ?? 0;
                            $gphoto = is_array($g) ? $g['photo'] ?? '' : $g->photo ?? '';
                            if ((int) $gid === (int) $value['id_photo'] && !empty($gphoto)) {
                                $selectedThumb = assets_photo('product', '70x70x1', $gphoto, 'thumbs');
                                break;
                            }
                        }
                    @endphp
                    @if (!empty($selectedThumb))
                        <img src="{{ $selectedThumb }}" class="img-thumbnail properties-photo-img" alt="photo">
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

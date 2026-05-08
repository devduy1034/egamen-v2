@extends('layout')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce">
            <form class="validation-form" novalidate method="post" action="{{ url('permission_save') }}"
                enctype="multipart/form-data">
                @component('component.buttonAdd')
                @endcomponent

                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="card mb-2">
                            <div class="card-header">
                                <h3 class="card-title text-capitalize"> Thông tin nhóm quyền </h3>
                            </div>
                            <div class="card-body card-article">
                                <div class="card">
                                    <ul class="nav nav-tabs" id="custom-tabs-three-tab-lang" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="tabs-lang" data-bs-toggle="tab"
                                                data-bs-target="#tabs-lang-active" role="tab"
                                                aria-controls="tabs-lang-active" aria-selected="true">Thông Tin Chung</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="custom-tabs-three-tabContent-lang">
                                        <div class="tab-pane fade show active" id="tabs-lang-active" role="tabpanel"
                                            aria-labelledby="tabs-lang">
                                            <div class="form-group">
                                                <label class="form-label" for="name">Tên nhóm quyền :</label>
                                                <input type="text" class="form-control for-seo text-sm" name="name"
                                                    id="name" placeholder="Tên nhóm quyền"
                                                    value="{{ $item['name'] ?? '' }}" required>
                                            </div>
                                            <div class="form-group d-inline-block mb-0">
                                                <label for="hienthi-checkbox"
                                                    class="d-inline-block align-middle mb-0 mr-2">Kích hoạt:</label>
                                                <label class="switch switch-success">
                                                    <input type="checkbox" name="status[hienthi]" value="hienthi"
                                                        {{ ($item['status'] ?? '') == 'hienthi' ? 'checked' : '' }}
                                                        class="switch-input custom-control-input show-checkbox"
                                                        id="hienthi-checkbox">
                                                    <span class="switch-toggle-slider">
                                                        <span class="switch-on">
                                                            <i class="ti ti-check"></i>
                                                        </span>
                                                        <span class="switch-off">
                                                            <i class="ti ti-x"></i>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="form-group d-inline-block ms-5 mb-0">
                                                <label for="numb" class="d-inline-block align-middle mb-0 mr-2">Số thứ
                                                    tự:</label>
                                                <input type="number"
                                                    class="form-control form-control-mini w-25 text-left d-inline-block align-middle text-sm"
                                                    min="0" name="numb" id="numb" placeholder="Số thứ tự"
                                                    value="{{ $item['numb'] ?? 1 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (!empty(config('type.product')))
                        @foreach (config('type.product') as $k => $v)
                            <div class="col-12 col-lg-12">
                                <div class="card mb-2">
                                    <div class="card-header">
                                        <h3 class="card-title text-capitalize"> Danh sách quyền {{ $v['title_main'] }}</h3>
                                    </div>
                                    <div class="card-body card-article pb-0">
                                        <div class="form-group row mb-0">
                                            @if (!empty($v['categories']['list']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_product_categories_list_{{ $k }}"><b>Danh
                                                            mục cấp 1:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_list_view_{{ $k }}"
                                                                value="product.list.{{ $k }}.man"
                                                                {{ in_array('product.list.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_list_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_list_add_{{ $k }}"
                                                                value="product.list.{{ $k }}.add"
                                                                {{ in_array('product.list.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_list_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_list_edit_{{ $k }}"
                                                                value="product.list.{{ $k }}.edit"
                                                                {{ in_array('product.list.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_list_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_list_delete_{{ $k }}"
                                                                value="product.list.{{ $k }}.delete"
                                                                {{ in_array('product.list.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_list_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($v['categories']['cat']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_product_categories_cat_{{ $k }}"><b>Danh
                                                            mục cấp 2:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_cat_view_{{ $k }}"
                                                                value="product.cat.{{ $k }}.man"
                                                                {{ in_array('product.cat.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_cat_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_cat_add_{{ $k }}"
                                                                value="product.cat.{{ $k }}.add"
                                                                {{ in_array('product.cat.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_cat_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_cat_edit_{{ $k }}"
                                                                value="product.cat.{{ $k }}.edit"
                                                                {{ in_array('product.cat.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_cat_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_cat_delete_{{ $k }}"
                                                                value="product.cat.{{ $k }}.delete"
                                                                {{ in_array('product.cat.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_cat_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($v['categories']['item']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_product_categories_cat_{{ $k }}"><b>Danh
                                                            mục cấp 3:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_item_view_{{ $k }}"
                                                                value="product.item.{{ $k }}.man"
                                                                {{ in_array('product.item.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_item_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_item_add_{{ $k }}"
                                                                value="product.item.{{ $k }}.add"
                                                                {{ in_array('product.item.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_item_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_item_edit_{{ $k }}"
                                                                value="product.item.{{ $k }}.edit"
                                                                {{ in_array('product.item.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_item_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_item_delete_{{ $k }}"
                                                                value="product.item.{{ $k }}.delete"
                                                                {{ in_array('product.item.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_item_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($v['categories']['sub']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_product_categories_sub_{{ $k }}"><b>Danh
                                                            mục cấp 4:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_sub_view_{{ $k }}"
                                                                value="product.sub.{{ $k }}.man"
                                                                {{ in_array('product.sub.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_sub_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_sub_add_{{ $k }}"
                                                                value="product.sub.{{ $k }}.add"
                                                                {{ in_array('product.sub.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_sub_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_sub_edit_{{ $k }}"
                                                                value="product.sub.{{ $k }}.edit"
                                                                {{ in_array('product.sub.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_sub_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_sub_delete_{{ $k }}"
                                                                value="product.sub.{{ $k }}.delete"
                                                                {{ in_array('product.sub.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_sub_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($v['brand']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_product_brand_{{ $k }}"><b>Danh mục
                                                            hàng:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_brand_view_{{ $k }}"
                                                                value="product.brand.{{ $k }}.man"
                                                                {{ in_array('product.brand.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label for="permission_product_brand_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_brand_add_{{ $k }}"
                                                                value="product.brand.{{ $k }}.add"
                                                                {{ in_array('product.brand.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label for="permission_product_brand_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_brand_edit_{{ $k }}"
                                                                value="product.brand.{{ $k }}.edit"
                                                                {{ in_array('product.brand.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label for="permission_product_brand_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_brand_delete_{{ $k }}"
                                                                value="product.brand.{{ $k }}.delete"
                                                                {{ in_array('product.brand.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_brand_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="form-group row last:!mb-0">
                                                <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                    for="permission_product_{{ $k }}"><b
                                                        class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                                <div class="col-md-7">
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_product_view_{{ $k }}"
                                                            value="product.{{ $k }}.man"
                                                            {{ in_array('product.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_product_view_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Xem danh
                                                            sách</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_product_add_{{ $k }}"
                                                            value="product.{{ $k }}.add"
                                                            {{ in_array('product.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_product_add_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Thêm
                                                            mới</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_product_edit_{{ $k }}"
                                                            value="product.{{ $k }}.edit"
                                                            {{ in_array('product.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_product_edit_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Chỉnh
                                                            sửa</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_product_delete_{{ $k }}"
                                                            value="product.{{ $k }}.delete"
                                                            {{ in_array('product.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_product_delete_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (!empty($v['gallery']))
                                                @foreach ($v['gallery'] as $k1 => $v1)
                                                    <div class="form-group row last:!mb-0">
                                                        <label
                                                            class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                            for="permission_product_gallery_{{ $k }}_{{ $k1 }}"><b
                                                                class="text-capitalize">{{ $v1['title_main_photo'] }}:</b></label>
                                                        <div class="col-md-7">
                                                            <div
                                                                class="form-check d-inline-block align-middle me-4 text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_product_gallery_view_{{ $k }}_{{ $k1 }}"
                                                                    value="product.{{ $k }}.{{ $k1 }}.gallery.man"
                                                                    {{ in_array('product.' . $k . '.' . $k1 . '.gallery.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_product_gallery_view_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Xem
                                                                    danh sách</label>
                                                            </div>
                                                            <div
                                                                class="form-check d-inline-block align-middle me-4 text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_product_gallery_add_{{ $k }}_{{ $k1 }}"
                                                                    value="product.{{ $k }}.{{ $k1 }}.gallery.add"
                                                                    {{ in_array('product.' . $k . '.' . $k1 . '.gallery.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_product_gallery_add_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Thêm
                                                                    mới</label>
                                                            </div>
                                                            <div
                                                                class="form-check d-inline-block align-middle me-4 text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_product_gallery_edit_{{ $k }}_{{ $k1 }}"
                                                                    value="product.{{ $k }}.{{ $k1 }}.gallery.edit"
                                                                    {{ in_array('product.' . $k . '.' . $k1 . '.gallery.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_product_gallery_edit_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                    sửa</label>
                                                            </div>
                                                            <div class="form-check d-inline-block align-middle text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_product_gallery_delete_{{ $k }}_{{ $k1 }}"
                                                                    value="product.{{ $k }}.{{ $k1 }}.gallery.delete"
                                                                    {{ in_array('product.' . $k . '.' . $k1 . '.gallery.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_product_gallery_delete_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                            @if (!empty($v['excel']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_product_categories_import_{{ $k }}"><b>Import sản phẩm:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_import_view_{{ $k }}"
                                                                value="product.import.{{ $k }}.man"
                                                                {{ in_array('product.import.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_import_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Import</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_product_categories_export_{{ $k }}"><b>Export sản phẩm:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_product_categories_export_view_{{ $k }}"
                                                                value="product.export.{{ $k }}.man"
                                                                {{ in_array('product.export.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_product_categories_export_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Export</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if (!empty(config('type.order')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Quản lý đơn hàng</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.order') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_order_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_order_view_{{ $k }}"
                                                        value="order.{{ $k }}.man"
                                                        {{ in_array('order.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_order_view_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_order_edit_{{ $k }}"
                                                        value="order.{{ $k }}.edit"
                                                        {{ in_array('order.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_order_edit_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_order_delete_{{ $k }}"
                                                        value="order.{{ $k }}.delete"
                                                        {{ in_array('order.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_order_delete_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_order_excel_{{ $k }}"
                                                        value="order.excel.{{ $k }}.man"
                                                        {{ in_array('order.excel.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_order_excel_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xuất
                                                        excel</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty(config('type.properties')))
                        @foreach (config('type.properties') as $k => $v)
                            <div class="col-12 col-lg-12">
                                <div class="card mb-2">
                                    <div class="card-header">
                                        <h3 class="card-title text-capitalize"> Danh sách quyền {{ $v['title_main'] }}
                                        </h3>
                                    </div>
                                    <div class="card-body card-article pb-0">
                                        <div class="form-group row mb-0">
                                            @if (!empty($v['categories']['list']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_properties_categories_list_{{ $k }}"><b>Danh
                                                            mục cấp 1:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_properties_categories_list_view_{{ $k }}"
                                                                value="properties.list.{{ $k }}.man"
                                                                {{ in_array('properties.list.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_properties_categories_list_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_properties_categories_list_add_{{ $k }}"
                                                                value="properties.list.{{ $k }}.add"
                                                                {{ in_array('properties.list.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_properties_categories_list_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_properties_categories_list_edit_{{ $k }}"
                                                                value="properties.list.{{ $k }}.edit"
                                                                {{ in_array('properties.list.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_properties_categories_list_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_properties_categories_list_delete_{{ $k }}"
                                                                value="properties.list.{{ $k }}.delete"
                                                                {{ in_array('properties.list.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_properties_categories_list_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group row last:!mb-0">
                                                <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                    for="permission_properties_{{ $k }}"><b
                                                        class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                                <div class="col-md-7">
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_properties_view_{{ $k }}"
                                                            value="properties.{{ $k }}.man"
                                                            {{ in_array('properties.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_properties_view_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Xem danh
                                                            sách</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_properties_add_{{ $k }}"
                                                            value="properties.{{ $k }}.add"
                                                            {{ in_array('properties.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_properties_add_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Thêm
                                                            mới</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_properties_edit_{{ $k }}"
                                                            value="properties.{{ $k }}.edit"
                                                            {{ in_array('properties.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_properties_edit_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Chỉnh
                                                            sửa</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_properties_delete_{{ $k }}"
                                                            value="properties.{{ $k }}.delete"
                                                            {{ in_array('properties.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_properties_delete_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if (!empty(config('type.news')))
                        @foreach (config('type.news') as $k => $v)
                            @if (empty($v['dropdown']))
                                @continue
                            @endif
                            <div class="col-12 col-lg-12">
                                <div class="card mb-2">
                                    <div class="card-header">
                                        <h3 class="card-title text-capitalize"> Danh sách quyền {{ $v['title_main'] }}
                                        </h3>
                                    </div>
                                    <div class="card-body card-article pb-0">
                                        <div class="form-group row mb-0">
                                            @if (!empty($v['categories']['list']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_new_categories_list_{{ $k }}"><b>Danh
                                                            mục cấp 1:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_list_view_{{ $k }}"
                                                                value="news.list.{{ $k }}.man"
                                                                {{ in_array('news.list.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_list_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_list_add_{{ $k }}"
                                                                value="news.list.{{ $k }}.add"
                                                                {{ in_array('news.list.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_list_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_list_edit_{{ $k }}"
                                                                value="news.list.{{ $k }}.edit"
                                                                {{ in_array('news.list.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_list_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_list_delete_{{ $k }}"
                                                                value="news.list.{{ $k }}.delete"
                                                                {{ in_array('news.list.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_list_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($v['categories']['cat']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_new_categories_cat_{{ $k }}"><b>Danh
                                                            mục cấp 2:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_cat_view_{{ $k }}"
                                                                value="news.cat.{{ $k }}.man"
                                                                {{ in_array('news.cat.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_cat_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_cat_add_{{ $k }}"
                                                                value="news.cat.{{ $k }}.add"
                                                                {{ in_array('news.cat.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_cat_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_cat_edit_{{ $k }}"
                                                                value="news.cat.{{ $k }}.edit"
                                                                {{ in_array('news.cat.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_cat_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_cat_delete_{{ $k }}"
                                                                value="news.cat.{{ $k }}.delete"
                                                                {{ in_array('news.cat.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_cat_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($v['categories']['item']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_new_categories_item_{{ $k }}"><b>Danh
                                                            mục cấp 3:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_item_view_{{ $k }}"
                                                                value="news.item.{{ $k }}.man"
                                                                {{ in_array('news.item.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_item_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_item_add_{{ $k }}"
                                                                value="news.item.{{ $k }}.add"
                                                                {{ in_array('news.item.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_item_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_item_edit_{{ $k }}"
                                                                value="news.item.{{ $k }}.edit"
                                                                {{ in_array('news.item.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_item_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_item_delete_{{ $k }}"
                                                                value="news.item.{{ $k }}.delete"
                                                                {{ in_array('news.item.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_item_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($v['categories']['sub']))
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_new_categories_sub_{{ $k }}"><b>Danh
                                                            mục cấp 4:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_sub_view_{{ $k }}"
                                                                value="news.sub.{{ $k }}.man"
                                                                {{ in_array('news.sub.' . $k . '.sub.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_sub_view_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_sub_add_{{ $k }}"
                                                                value="news.sub.{{ $k }}.add"
                                                                {{ in_array('news.sub.' . $k . '.sub.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_sub_add_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_sub_edit_{{ $k }}"
                                                                value="news.sub.{{ $k }}.edit"
                                                                {{ in_array('news.sub.' . $k . '.sub.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_sub_edit_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_categories_sub_delete_{{ $k }}"
                                                                value="news.sub.{{ $k }}.delete"
                                                                {{ in_array('news.sub.' . $k . '.sub.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_categories_sub_delete_{{ $k }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="form-group row last:!mb-0">
                                                <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                    for="permission_new_{{ $k }}"><b
                                                        class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                                <div class="col-md-7">
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_new_view_{{ $k }}"
                                                            value="news.{{ $k }}.man"
                                                            {{ in_array('news.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_new_view_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Xem danh
                                                            sách</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_new_add_{{ $k }}"
                                                            value="news.{{ $k }}.add"
                                                            {{ in_array('news.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_new_add_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Thêm
                                                            mới</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle me-4 text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_new_edit_{{ $k }}"
                                                            value="news.{{ $k }}.edit"
                                                            {{ in_array('news.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_new_edit_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Chỉnh
                                                            sửa</label>
                                                    </div>
                                                    <div class="form-check d-inline-block align-middle text-md">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="dataPermission[]"
                                                            id="permission_new_delete_{{ $k }}"
                                                            value="news.{{ $k }}.delete"
                                                            {{ in_array('news.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                        <label for="permission_new_delete_{{ $k }}"
                                                            class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (!empty($v['gallery']))
                                                @foreach ($v['gallery'] as $k1 => $v1)
                                                    <div class="form-group row last:!mb-0">
                                                        <label
                                                            class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                            for="permission_new_gallery_{{ $k }}_{{ $k1 }}"><b
                                                                class="text-capitalize">{{ $v1['title_main_photo'] }}:</b></label>
                                                        <div class="col-md-7">
                                                            <div
                                                                class="form-check d-inline-block align-middle me-4 text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_new_gallery_view_{{ $k }}_{{ $k1 }}"
                                                                    value="news.{{ $k }}.{{ $k1 }}.gallery.man"
                                                                    {{ in_array('news.' . $k . '.' . $k1 . '.gallery.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_new_gallery_view_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Xem
                                                                    danh sách</label>
                                                            </div>
                                                            <div
                                                                class="form-check d-inline-block align-middle me-4 text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_new_gallery_add_{{ $k }}_{{ $k1 }}"
                                                                    value="news.{{ $k }}.{{ $k1 }}.gallery.add"
                                                                    {{ in_array('news.' . $k . '.' . $k1 . '.gallery.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_new_gallery_add_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Thêm
                                                                    mới</label>
                                                            </div>
                                                            <div
                                                                class="form-check d-inline-block align-middle me-4 text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_new_gallery_edit_{{ $k }}_{{ $k1 }}"
                                                                    value="news.{{ $k }}.{{ $k1 }}.gallery.edit"
                                                                    {{ in_array('news.' . $k . '.' . $k1 . '.gallery.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_new_gallery_edit_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                    sửa</label>
                                                            </div>
                                                            <div class="form-check d-inline-block align-middle text-md">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="dataPermission[]"
                                                                    id="permission_new_gallery_delete_{{ $k }}_{{ $k1 }}"
                                                                    value="news.{{ $k }}.{{ $k1 }}.gallery.delete"
                                                                    {{ in_array('news.' . $k . '.' . $k1 . '.gallery.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                                <label
                                                                    for="permission_new_gallery_delete_{{ $k }}_{{ $k1 }}"
                                                                    class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if (!empty(config('type.comment')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền bình luận</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.comment') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_comment_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_comment_view_{{ $k }}"
                                                        value="comment.{{ $k }}.man"
                                                        {{ in_array('comment.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_comment_view_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_comment_add_{{ $k }}"
                                                        value="comment.{{ $k }}.add"
                                                        {{ in_array('comment.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_comment_add_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_comment_edit_{{ $k }}"
                                                        value="comment.{{ $k }}.edit"
                                                        {{ in_array('comment.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_comment_edit_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_comment_delete_{{ $k }}"
                                                        value="comment.{{ $k }}.delete"
                                                        {{ in_array('comment.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_comment_delete_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty(Func::checkShowNews(config('type.news'))) || !empty(config('type.static')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền bài viết</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.news') as $k => $v)
                                        @if (!empty($v['dropdown']))
                                            @continue
                                        @endif
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_new_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_new_view_{{ $k }}"
                                                        value="news.{{ $k }}.man"
                                                        {{ in_array('news.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_new_view_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_new_add_{{ $k }}"
                                                        value="news.{{ $k }}.add"
                                                        {{ in_array('news.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_new_add_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_new_edit_{{ $k }}"
                                                        value="news.{{ $k }}.edit"
                                                        {{ in_array('news.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_new_edit_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_new_delete_{{ $k }}"
                                                        value="news.{{ $k }}.delete"
                                                        {{ in_array('news.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_new_delete_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                        @if (!empty($v['gallery']))
                                            @foreach ($v['gallery'] as $k1 => $v1)
                                                <div class="form-group row last:!mb-0">
                                                    <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                        for="permission_new_gallery_{{ $k }}_{{ $k1 }}"><b
                                                            class="text-capitalize">{{ $v1['title_main_photo'] }}:</b></label>
                                                    <div class="col-md-7">
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_gallery_view_{{ $k }}_{{ $k1 }}"
                                                                value="news.{{ $k }}.{{ $k1 }}.gallery.man"
                                                                {{ in_array('news.' . $k . '.' . $k1 . '.gallery.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_gallery_view_{{ $k }}_{{ $k1 }}"
                                                                class="form-check-label font-weight-normal mb-0">Xem danh
                                                                sách</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_gallery_add_{{ $k }}_{{ $k1 }}"
                                                                value="news.{{ $k }}.{{ $k1 }}.gallery.add"
                                                                {{ in_array('news.' . $k . '.' . $k1 . '.gallery.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_gallery_add_{{ $k }}_{{ $k1 }}"
                                                                class="form-check-label font-weight-normal mb-0">Thêm
                                                                mới</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle me-4 text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_gallery_edit_{{ $k }}_{{ $k1 }}"
                                                                value="news.{{ $k }}.{{ $k1 }}.gallery.edit"
                                                                {{ in_array('news.' . $k . '.' . $k1 . '.gallery.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_gallery_edit_{{ $k }}_{{ $k1 }}"
                                                                class="form-check-label font-weight-normal mb-0">Chỉnh
                                                                sửa</label>
                                                        </div>
                                                        <div class="form-check d-inline-block align-middle text-md">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="dataPermission[]"
                                                                id="permission_new_gallery_delete_{{ $k }}_{{ $k1 }}"
                                                                value="news.{{ $k }}.{{ $k1 }}.gallery.delete"
                                                                {{ in_array('news.' . $k . '.' . $k1 . '.gallery.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                            <label
                                                                for="permission_new_gallery_delete_{{ $k }}_{{ $k1 }}"
                                                                class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    @foreach (config('type.static') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_static_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_static_man_{{ $k }}"
                                                        value="static.{{ $k }}.man"
                                                        {{ in_array('static.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_static_man_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem / Chỉnh
                                                        sửa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty(config('type.photo')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền hình ảnh / video</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.photo') as $k => $v)
                                        @if ($v['kind'] == 'album')
                                            @continue
                                        @endif
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_photo-{{ $v['kind'] }}_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_photo-{{ $v['kind'] }}_man_{{ $k }}"
                                                        value="photo.{{ $v['kind'] }}.{{ $k }}.man"
                                                        {{ in_array('photo.' . $v['kind'] . '.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label
                                                        for="permission_photo-{{ $v['kind'] }}_man_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem / Chỉnh
                                                        sửa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @foreach (config('type.photo') as $k => $v)
                                        @if ($v['kind'] == 'static')
                                            @continue
                                        @endif
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_photo-{{ $v['kind'] }}_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_photo-{{ $v['kind'] }}_view_{{ $k }}"
                                                        value="photo.{{ $v['kind'] }}.{{ $k }}.man"
                                                        {{ in_array('photo.' . $v['kind'] . '.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label
                                                        for="permission_photo-{{ $v['kind'] }}_view_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_photo-{{ $v['kind'] }}_add_{{ $k }}"
                                                        value="photo.{{ $v['kind'] }}.{{ $k }}.add"
                                                        {{ in_array('photo.' . $v['kind'] . '.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label
                                                        for="permission_photo-{{ $v['kind'] }}_add_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_photo-{{ $v['kind'] }}_edit_{{ $k }}"
                                                        value="photo.{{ $v['kind'] }}.{{ $k }}.edit"
                                                        {{ in_array('photo.' . $v['kind'] . '.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label
                                                        for="permission_photo-{{ $v['kind'] }}_edit_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_photo-{{ $v['kind'] }}_delete_{{ $k }}"
                                                        value="photo.{{ $v['kind'] }}.{{ $k }}.delete"
                                                        {{ in_array('photo.' . $v['kind'] . '.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label
                                                        for="permission_photo-{{ $v['kind'] }}_delete_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!empty(config('type.newsletters')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền thông báo email</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.newsletters') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_newsletters_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_newsletters_view_{{ $k }}"
                                                        value="newsletters.{{ $k }}.man"
                                                        {{ in_array('newsletters.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_newsletters_view_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_newsletters_add_{{ $k }}"
                                                        value="newsletters.{{ $k }}.add"
                                                        {{ in_array('newsletters.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_newsletters_add_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_newsletters_edit_{{ $k }}"
                                                        value="newsletters.{{ $k }}.edit"
                                                        {{ in_array('newsletters.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_newsletters_edit_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_newsletters_delete_{{ $k }}"
                                                        value="newsletters.{{ $k }}.delete"
                                                        {{ in_array('newsletters.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_newsletters_delete_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!empty(config('type.tags')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền tags</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.tags') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_tags_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_tags_view_{{ $k }}"
                                                        value="tags.{{ $k }}.man"
                                                        {{ in_array('tags.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_tags_view_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_tags_add_{{ $k }}"
                                                        value="tags.{{ $k }}.add"
                                                        {{ in_array('tags.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_tags_add_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_tags_edit_{{ $k }}"
                                                        value="tags.{{ $k }}.edit"
                                                        {{ in_array('tags.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_tags_edit_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_tags_delete_{{ $k }}"
                                                        value="tags.{{ $k }}.delete"
                                                        {{ in_array('tags.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_tags_delete_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!empty(config('type.seo')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền seo page</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.seo.page') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_seopage_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_seopage_man_{{ $k }}"
                                                        value="seopage.{{ $k }}.man"
                                                        {{ in_array('seopage.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_seopage_man_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem / Chỉnh
                                                        sửa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty(config('type.place')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền địa điểm</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.place') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_place_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_place_view_{{ $k }}"
                                                        value="place.{{ $k }}.man"
                                                        {{ in_array('place.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_place_view_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_place_add_{{ $k }}"
                                                        value="place.{{ $k }}.add"
                                                        {{ in_array('place.' . $k . '.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_place_add_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_place_edit_{{ $k }}"
                                                        value="place.{{ $k }}.edit"
                                                        {{ in_array('place.' . $k . '.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_place_edit_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_place_delete_{{ $k }}"
                                                        value="place.{{ $k }}.delete"
                                                        {{ in_array('place.' . $k . '.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_place_delete_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!empty(config('type.extensions')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền extensions</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.extensions') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_extensions_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_extensions_man_{{ $k }}"
                                                        value="extensions.{{ $k }}.man"
                                                        {{ in_array('extensions.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_extensions_man_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem / Chỉnh
                                                        sửa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!empty(config('type.users.active')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền User</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @if (!empty(config('type.users.admin')))
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_users"><b class="text-capitalize">User
                                                    admin:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id=" permission_users_view"
                                                        value="users.man"
                                                        {{ in_array('users.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_users_view"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_users_add"
                                                        value="users.add"
                                                        {{ in_array('users.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_users_add"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_users_edit"
                                                        value="users.edit"
                                                        {{ in_array('users.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_users_edit"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_users_delete"
                                                        value="users.delete"
                                                        {{ in_array('users.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_users_delete"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if (!empty(config('type.users.member')))
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_members"><b class="text-capitalize">User
                                                    member:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id=" permission_members_view"
                                                        value="members.man"
                                                        {{ in_array('members.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_members_view"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_members_add"
                                                        value="members.add"
                                                        {{ in_array('members.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_members_add"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_members_edit"
                                                        value="members.edit"
                                                        {{ in_array('members.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_members_edit"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_members_delete"
                                                        value="members.delete"
                                                        {{ in_array('members.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_members_delete"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if (!empty(config('type.users.permission')))
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_permission"><b class="text-capitalize">Phân
                                                    quyền:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id=" permission_permission_view"
                                                        value="permission.man"
                                                        {{ in_array('permission.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_permission_view"
                                                        class="form-check-label font-weight-normal mb-0">Xem danh
                                                        sách</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_permission_add"
                                                        value="permission.add"
                                                        {{ in_array('permission.add', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_permission_add"
                                                        class="form-check-label font-weight-normal mb-0">Thêm mới</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_permission_edit"
                                                        value="permission.edit"
                                                        {{ in_array('permission.edit', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_permission_edit"
                                                        class="form-check-label font-weight-normal mb-0">Chỉnh sửa</label>
                                                </div>
                                                <div class="form-check d-inline-block align-middle text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]" id="permission_permission_delete"
                                                        value="permission.delete"
                                                        {{ in_array('permission.delete', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_permission_delete"
                                                        class="form-check-label font-weight-normal mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty(config('type.setting')))
                        <div class="col-12 col-lg-12">
                            <div class="card mb-2">
                                <div class="card-header">
                                    <h3 class="card-title text-capitalize"> Danh sách quyền Setting</h3>
                                </div>
                                <div class="card-body card-article pb-0">
                                    @foreach (config('type.setting') as $k => $v)
                                        <div class="form-group row last:!mb-0">
                                            <label class="d-inline-block align-middle mb-2 mr-2 text-md col-md-3"
                                                for="permission_setting_{{ $k }}"><b
                                                    class="text-capitalize">{{ $v['title_main'] }}:</b></label>
                                            <div class="col-md-7">
                                                <div class="form-check d-inline-block align-middle me-4 text-md">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="dataPermission[]"
                                                        id="permission_setting_man_{{ $k }}"
                                                        value="setting.{{ $k }}.man"
                                                        {{ in_array('setting.' . $k . '.man', $listPermissionByRole) ? 'checked' : '' }}>
                                                    <label for="permission_setting_man_{{ $k }}"
                                                        class="form-check-label font-weight-normal mb-0">Xem / Chỉnh
                                                        sửa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <input type="hidden" name="id" value="{{ $item['id'] ?? 0 }}">
                <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">

                @component('component.buttonAdd')
                @endcomponent

            </form>
        </div>
    </div>
@endsection

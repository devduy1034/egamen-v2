@extends('layout')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <h4 class="py-3 mb-2">
            <span>Quản lý</span>/<span class="text-muted fw-light"></span>Hình ảnh thuộc tính
        </h4>
        <div class="app-ecommerce-category">
            <div class="card">
                <div class="card-datatable table-responsive">
                    <table class="datatables-category-list table border-top text-sm">
                        <thead>
                            <tr>
                                <th class="text-center w-[70px]">STT</th>
                                <th width="30%">Thuộc tính</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-lg-center text-center">Thao tác</th>
                            </tr>
                        </thead>
                        @if (empty($items))
                            <tbody>
                                <tr>
                                    <td colspan="100" class="text-center">Không có dữ liệu</td>
                                </tr>
                            </tbody>
                        @else
                            <tbody>
                                @foreach ($items as $key => $item)
                                    @php
                                        $name = Func::nameProper($item);
                                        $id_properties = implode(',', $item);
                                    @endphp

                                    <tr>
                                        <td class="text-center">
                                            <a>{{ $key + 1 }}</a>
                                        </td>
                                        <td class="align-middle">
                                            <a class="text-dark text-break">{{ $name }}</a>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ url('admin',['com'=>$com,'act'=>'edit','type'=>$type],['id_product'=>Request()->id_product,'id_properties'=>$id_properties]) }}"
                                                class="text-break text-success">({{ Func::numberGallery($id_product, $id_properties, $com, $type) }})
                                                hình ảnh</a>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a class="text-primary mr-2"
                                                href="{{ url('admin',['com'=>$com,'act'=>'edit','type'=>$type],['id_product'=>Request()->id_product,'id_properties'=>$id_properties]) }}"
                                                title="Thêm ảnh">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-photo-circle-plus" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M15 8h.01"></path>
                                                    <path
                                                        d="M20.964 12.806a9 9 0 0 0 -8.964 -9.806a9 9 0 0 0 -9 9a9 9 0 0 0 9.397 8.991">
                                                    </path>
                                                    <path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l4 4"></path>
                                                    <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0"></path>
                                                    <path d="M16 19.33h6"></path>
                                                    <path d="M19 16.33v6"></path>
                                                </svg>
                                            </a>
                                            <a class="text-primary mr-2"
                                                href="product/man/{{$type}}"
                                                title="quay lại">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-back-up">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M9 14l-4 -4l4 -4" />
                                                    <path d="M5 10h11a4 4 0 1 1 0 8h-1" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

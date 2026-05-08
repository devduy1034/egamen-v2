@extends('layout')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y container-fluid">
        <div class="app-ecommerce">
            <form class="validation-form" novalidate method="post" action="{{ url('admin',['com'=>$com,'act'=>'save','type'=>$type], ['page' => $page]) }}" enctype="multipart/form-data">
                <div
                    class="btn-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                    <div class="d-flex align-content-center flex-wrap gap-2">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn bg-success submit-check"><i class="far fa-save mr-2"></i>
                                Luu</button>
                            <button type="button"
                                onclick="location.href='{{ $com . '/man/' . $type . '?id_product=' . Request()->id_product }}'"
                                class="btn btn-primary"><i class="fas fa-redo mr-2"></i> Quay lại</button>
                        </div>

                    </div>
                </div>
                @component('component.filergallery', ['title_main'=>$configMan->title_main,'gallery'=>$gallery??[],'act'=>$act,'folder'=>'product']) @endcomponent
                
        </div>

        <div
            class="btn-footer d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
            <div class="d-flex align-content-center flex-wrap gap-2">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn bg-success submit-check"><i class="far fa-save mr-2"></i>
                        Luu</button>
                    <button type="button"
                        onclick="location.href='{{ $com . '/man/' . $type . '?id_product=' . Request()->id_product }}'"
                        class="btn btn-primary"><i class="fas fa-redo mr-2"></i> Quay lại</button>

                    <input type="hidden" name="id_product" value="{{ !empty(Request()->id_product) && Request()->id_product > 0 ? Request()->id_product : '' }}">
                    
                        <input type="hidden" name="id_properties"
                        value="{{ !empty(Request()->id_properties) && Request()->id_properties > 0 ? Request()->id_properties : '' }}">
                        
                    <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
                </div>
            </div>
        </div>

        </form>
    </div>
    </div>
@endsection

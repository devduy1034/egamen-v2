<div class="card pd-15 bg-main mb-3">
    <form method="post" action="{{$url}}" enctype="multipart/form-data">
        <div class="card card-primary card-outline text-sm mb-0">
            <div class="card-body">
                <label class="d-inline-block align-middle mb-2 mr-2">Upload tập tin: <strong class=" mt-2 mb-2 text-sm">Loại : .xls|.xlsx (Ms.Excel 2003 - 2007)</strong></label>
                <div class="row">
                    <div class="form-group col-12 col-md-9 col-lg-10 md:!mb-0">
                         <input class="form-control" name="file-excel" type="file" id="file-excel">
                    </div>
                    <div class="col-12 col-md-3 col-lg-2">
                        <button type="submit" class="btn bg-primary text-white w-100" name="{{$title}}"><i class="ti ti-upload mr-2"></i>{{$title}}</button>
                    </div>
                </div>
            </div>
        </div>
        <input name="csrf_token" type="hidden" value="{{ csrf_token() }}">
    </form>
</div>

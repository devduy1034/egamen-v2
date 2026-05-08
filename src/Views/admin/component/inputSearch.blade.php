<h5 class="card-title mb-2">{{$title}}</h5>
<div class="input-group input-group-sm">
    <input class="form-control form-control-navbar text-sm" type="search" id="keyword" placeholder="Tìm kiếm" aria-label="Tìm kiếm" value="{{ (isset($_GET['keyword'])) ? $_GET['keyword'] : '' }}" onkeypress="doEnter(event,'keyword','{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type]) }}')">
    <div class="input-group-append bg-primary rounded-right">
        <button class="btn btn-navbar text-white" type="button" onclick="onSearch('keyword','{{ url('admin',['com'=>$com,'act'=>'man','type'=>$type]) }}')">
            <i class="ti ti-search"></i>
        </button>
    </div>
</div>
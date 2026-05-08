<?php



namespace LARAVEL\Controllers\Admin;

use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVELPermission\Models\Permission;
use LARAVELPermission\Models\Role;
use LARAVELPermission\Repositories\PermissionRepository;
use LARAVELPermission\Repositories\RoleRepository;

class PermissionController extends Controller
{
    private $listPermission = [];
    private $permissionRepo;
    private $roleRepo;
    public function __construct()
    {
        $this->listPermission = Permission::pluck('name')->toArray();
        $this->permissionRepo = new PermissionRepository();
        $this->roleRepo = new RoleRepository();
    }
    public function index(){
        $items = Role::where('root',0)->orderBy('numb','asc')->paginate(10);
        $count = Role::count();
        return view('permission.index',compact('items','count'));
    }
    public function add(){
        $listPermissionByRole = [];
        return view('permission.update',['item'=>[],'listPermissionByRole'=>$listPermissionByRole]);
    }
    public function save(Request $request) {
        if(empty($request->input('id'))) $this->savePermission($request);
        else $this->editPermission($request);
    }
    public function edit(Request $request){
        $id = $request->get('id');
        $item = Role::find($id);
        $listPermissionByRole = $item->permissions()->pluck('name')->toArray();
        return view('permission.update',['item'=>$item,'listPermissionByRole'=>$listPermissionByRole]);
    }
    public function delete(Request $request){
        $this->roleRepo->deleteById($request->input('id'));
        response()->redirect(url('permission'));
    }
    protected function editPermission($request): void
    {
        $role = Role::find($request->input('id'));
        $name = $request->input('name');
        $numb = $request->input('numb');
        if (!empty($request->input('status'))) {
            $status = '';
            foreach ($request->input('status') as $attr_column => $attr_value) if ($attr_value != "") $status .= $attr_value . ',';
            $status = (!empty($status)) ? rtrim($status, ",") : "";
        } else {
            $status = "";
        }
        Role::where('id',$request->input('id'))->update(['name'=>$name,'numb'=>$numb,'status'=>$status]);
        $listPermissionCurrent = $role->permissions()->pluck('name')->toArray();
        foreach ($listPermissionCurrent??[] as $k => $v){
            $this->permissionRepo->where('name',$v)->terminateToRole($role);
        }
        $arrayOfPermissionNames = $request->input('dataPermission')??[];
        $arrPermissionNames = array_diff($arrayOfPermissionNames,$this->listPermission);
        if(!empty($arrPermissionNames)){
            foreach ($arrPermissionNames as $v){
                Permission::create(['name'=>$v]);
            }
            $this->listPermission = Permission::pluck('name')->toArray();
        }
        foreach ($arrayOfPermissionNames??[] as $v){
           $this->permissionRepo->where('name',$v)->assignRole($role);
        }
        transfer('Cập nhật nhóm quyền thành công', true,url('permission'));
    }
    protected function savePermission($request): void
    {
        $name = $request->input('name');
        $numb = $request->input('numb');
        if (!empty($request->input('status'))) {
            $status = '';
            foreach ($request->input('status') as $attr_column => $attr_value) if ($attr_value != "") $status .= $attr_value . ',';
            $status = (!empty($status)) ? rtrim($status, ",") : "";
        } else {
            $status = "";
        }
        $role = Role::create(['name'=>$name,'numb'=>$numb,'status'=>$status]);
        $arrayOfPermissionNames = $request->input('dataPermission')??[];
        $arrPermissionNames = array_diff($arrayOfPermissionNames,$this->listPermission);
        if(!empty($arrPermissionNames)){
            foreach ($arrPermissionNames as $v){
                Permission::create(['name'=>$v]);
            }
            $this->listPermission = Permission::pluck('name')->toArray();
        }
        foreach ($arrayOfPermissionNames??[] as $v){
            $this->permissionRepo->where('name',$v)->assignRole($role);
        }
        transfer('Tạo mới nhóm quyền thành công', true,url('permission'));
    }
}
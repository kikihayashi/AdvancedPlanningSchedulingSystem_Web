<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    use BaseTool, ValidatorTool;

    public function showRolePage()
    {
        //以下指令讓權限代號：A永遠在最上方，權限代號：N永遠在最下方
        $sqlCommand = "SELECT roles.* FROM roles ORDER BY
        CASE WHEN permission_code = 'A' AND name = '系統管理員' THEN 1 END DESC,
        CASE WHEN permission_code = 'N' AND name = '無權限' THEN 1 END ASC";

        $results = DB::select($sqlCommand);

        //將DB查詢到的資料轉成array
        $roles = array_map(function ($itemObject) {
            $itemArray = (array) $itemObject;
            //date轉換為Unix時間戳
            $timestamp = strtotime(date_format(date_create($itemArray['updated_at']), "Y/m/d H:i:s"));
            //轉成台灣時區
            $itemArray['updated_at_tw'] = date("Y-m-d H:i:s", ($timestamp + 8 * 3600));
            return $itemArray;
        }, $results);

        $permissions = Permission::all()->toArray();

        $permissionMap = array();
        foreach ($permissions as $permission) {
            $permissionMap[$permission['code']] = $permission;
        }

        $data['title'] = "角色";
        $data['role'] = $roles;
        $data['permissionArray'] = $permissions;
        $data['permissionMap'] = $permissionMap;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('control.identityMenu.role', [
            'selection' => 'control',
            'openMenu' => 'identityMenu',
            'visitedId' => 'role',
            'tableData' => $data]);
    }

    //建立&更新
    public function writeRole(Request $request, $id = 0)
    {
        //建立驗證器
        $columnArray['id'] = $id;
        $columnArray['name'] = 'name';
        $columnArray['value'] = $request->name;
        $columnArray['name2'] = 'permission_code';
        $columnArray['value2'] = $request->permissionCode;

        $validator = $this->checkInputValid('roles', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        $role = Role::where('id', $id)->first() ?? new Role();
        $role->name = $request->name;
        $role->permission_code = $request->permissionCode;
        $role->save();

        $message = ($id == 0) ? '成功，已新增資料！' : '成功，已修改資料！';

        return redirect(route('RoleController.showRolePage'))
            ->with('message', $message); //顯示成功訊息
    }

    public function deleteRole($id)
    {
        //找到此id的資料
        $role = Role::find($id);

        //刪除此id的資料
        $role->delete();

        return redirect(route('RoleController.showRolePage'))
            ->with('message', '成功，已刪除角色【' . $role->name . '】！'); //顯示成功訊息
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    use BaseTool, ValidatorTool;

    public function showPermissionPage()
    {
        //以下指令讓權限代號：A永遠在最上方，權限代號：N永遠在最下方
        $sqlCommand = "SELECT * FROM permission ORDER BY
         CASE WHEN code = 'A' THEN 1 END DESC,
         CASE WHEN code = 'N' THEN 1 END ASC";

        $results = DB::select($sqlCommand);

        //將DB查詢到的資料轉成array
        $permissions = array_map(function ($itemObject) {
            return (array) $itemObject;
        }, $results);

        $data['title'] = "權限";
        $data['permissionArray'] = $permissions;
        $data['permissionInfoArray'] = $this->permissionInfoArray;
        $data['initPermissionArray'] = $this->initPermissionArray;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('control.identityMenu.permission', [
            'selection' => 'control',
            'openMenu' => 'identityMenu',
            'visitedId' => 'permission',
            'tableData' => $data]);
    }

    //建立
    public function createPermission(Request $request)
    {
        if ((isset($request->identity_crud))) {
            return redirect()->back()
                ->with('errorMessage', '錯誤，只有系統管理員可擁有身分識別權限！'); //顯示錯誤訊息
        }

        //建立驗證器
        $columnArray['name'] = 'code';
        $columnArray['value'] = $request->code;

        $validator = $this->checkInputValid('permission', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        $permission = new Permission();
        $permission->code = $request->code;
        $permission->remark = $request->remark;
        foreach ($this->permissionInfoArray as $permissionInfo) {
            $permission->{$permissionInfo['name']} = (isset($request->{$permissionInfo['name']})) ? 'Y' : 'N';
        }
        $permission->save();

        return redirect(route('PermissionController.showPermissionPage'))
            ->with('message', '成功，已新增資料！'); //顯示成功訊息
    }

    //更新
    public function updatePermission(Request $request, $id)
    {
        if (($request->name == 'identity_crud')) {
            return redirect()->back()
                ->with('errorMessage', '錯誤，只有系統管理員可擁有身分識別權限！'); //顯示錯誤訊息
        }

        $permission = Permission::findOrFail($id);

        $permission->{$request->name} = (isset($request->allow)) ? 'Y' : 'N';

        $permission->save();

        return redirect(route('PermissionController.showPermissionPage'))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }

    //刪除
    public function deletePermission($code)
    {
        //找到此code的資料
        $permission = Permission::where('code', $code)->first();

        //刪除此code的資料
        $permission->delete();

        return redirect(route('PermissionController.showPermissionPage'))
            ->with('message', '成功，已刪除權限代號【' . $permission->code . '】！'); //顯示成功訊息
    }

}

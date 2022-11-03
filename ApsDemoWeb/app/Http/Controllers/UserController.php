<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use BaseTool, ValidatorTool;

    //讀取
    public function showUserPage()
    {
        $users = User::join('roles', 'users.role_id', '=', DB::raw("CAST(roles.id AS VARCHAR)"))
            ->selectRaw('users.* , roles.name AS roleName')
            ->where('roles.permission_code', '!=', 'A')
            ->orderBy('users.id', 'ASC')
            ->get()
            ->toArray();

        $dataArray = array();
        foreach ($users as $user) {
            //date轉換為Unix時間戳
            $timestamp = strtotime(date_format(date_create($user['updated_at']), "Y/m/d H:i:s"));
            $dataArray[] = array(
                $user['id'],
                $user['name'],
                $user['account'],
                $user['roleName'],
                date("Y-m-d H:i:s", ($timestamp + 8 * 3600)),//轉成台灣時區
            );
        }

        $roleArray = Role::where('permission_code', '!=', 'A')->get()->toArray();
        $roleMap = array();
        foreach ($roleArray as $role) {
            $roleMap[$role['name']] = $role['id'];
        }

        $data['title'] = "使用者";
        $data['user'] = $dataArray;
        $data['role'] = $roleArray;
        $data['roleMap'] = $roleMap;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('control.identityMenu.user', [
            'selection' => 'control',
            'openMenu' => 'identityMenu',
            'visitedId' => 'user',
            'tableData' => $data]);
    }

    //新增使用者
    public function createUser(Request $request, $type)
    {
        //建立驗證器
        $columnArray['name'] = 'name';
        $columnArray['value'] = $request->name;
        $columnArray['name2'] = 'account';
        $columnArray['value2'] = $request->account;
        $columnArray['name3'] = 'password';
        $columnArray['value3'] = $request->password;
        $columnArray['name4'] = 'password_confirmation';
        $columnArray['value4'] = $request->password_confirmation;
        $validator = $this->checkInputValid('users', $columnArray);

        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0])
                ->withInput($request->input()); //顯示錯誤訊息
        }

        $user = User::create([
            'role_id' => Role::where('permission_code', 'N')->first()->id, //對應的是無權限(權限最低)
            'name' => $request->name,
            'account' => $request->account,
            'password' => Hash::make($request->password),
        ]);

        switch ($type) {
            case 'HOME':
                //註冊成功後，自動登入
                Auth::login($user);
                return redirect(route('home'));

            case 'IDENTITY':
                return redirect(route('UserController.showUserPage'))
                    ->with('message', '成功，已新增使用者！'); //顯示成功訊息;
        }
    }

    //新增使用者頁面
    public function addUserPage()
    {
        //驗證操作是否合法
        $result = $this->checkOperationValid(0, 'identity_crud');
        if ($result['code'] != 200) {
            return view('errors.custom_error', $result);
        }

        return view('auth.register', ['type' => 'IDENTITY']);
    }

    //編輯使用者頁面(修改密碼用)
    public function editUserPage($id)
    {
        //驗證操作是否合法
        if ($id != Auth::id()) {
            return view('errors.custom_error', ['code' => 404, 'message' => '找不到此頁面！']);
        }

        $user = User::findOrFail($id)->toArray();
        $data['user'] = $user;

        return view('control.identityMenu.editDetail.user_edit', [
            'tableData' => $data,
            'selection' => 'control']);
    }

    //修改
    public function updateUser(Request $request, $id, $type)
    {
        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->account = $request->account;

        switch ($type) {
            case 'SELF':
                $old_password = $request->password;
                if (!Hash::check($old_password, $user->password)) {
                    return redirect()->back()
                        ->with('errorMessage', '錯誤，原密碼輸入錯誤，無法修改資料！'); //顯示錯誤訊息
                }
                $password = $request->new_password;
                $password_confirmation = $request->new_password_confirmation;
                $routeName = 'home';
                $user->password = Hash::make($password);
                break;

            case 'ADMIN':
                $password = $user->password;
                $password_confirmation = $password;
                $routeName = 'UserController.showUserPage';
                $user->role_id = $request->roleId;
                break;
        }

        //建立驗證器
        $columnArray['id'] = $id;
        $columnArray['name'] = 'name';
        $columnArray['value'] = $request->name;
        $columnArray['name2'] = 'account';
        $columnArray['value2'] = $request->account;
        $columnArray['name3'] = 'password';
        $columnArray['value3'] = $password;
        $columnArray['name4'] = 'password_confirmation';
        $columnArray['value4'] = $password_confirmation;
        $validator = $this->checkInputValid('users', $columnArray);

        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        $user->save();

        return redirect(route($routeName))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }

    //刪除
    public function deleteUser($id)
    {
        //找到此id的資料
        $user = User::find($id);

        //刪除此id的資料
        $user->delete();

        return redirect(route('UserController.showUserPage'))
            ->with('message', '成功，已刪除使用者！'); //顯示成功訊息
    }

}

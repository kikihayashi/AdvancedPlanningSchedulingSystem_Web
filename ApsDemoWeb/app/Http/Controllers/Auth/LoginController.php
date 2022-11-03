<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    // use AuthenticatesUsers;

    //laravelRedirectPath：如果要登入之後增加"登入成功"的提示，改成這樣
    //performLogout：如果要登出之後要到特定頁面，改成這樣
    use AuthenticatesUsers {
        redirectPath as laravelRedirectPath;
        logout as performLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    //這是新增的東西，讓登入不使用Email，使用帳號，一定要寫$username，不可以是別的
    protected $username;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        //這是新增的東西，讓登入不使用Email，使用帳號，一定要寫$username，不可以是別的，等號後面可以是別的(與User.php裡的對應)
        $this->username = 'account';
    }

    /**
     * Get account property.
     *
     * @return string
     */
    public function username()
    {
        //這是新增的東西，讓登入不使用Email，使用帳號，一定要寫$username，不可以是別的
        return $this->username;
    }

    //如果要登入之後增加"登入成功"的提示，加上以下function
    public function redirectPath()
    {
        // Do your logic to flash data to session...
        session()->flash('message', '登入成功！');

        // Return the results of the method we are overriding that we aliased.
        return $this->laravelRedirectPath();
    }

    //登出設計
    public function logout(Request $request)
    {
        $this->performLogout($request);
        return redirect()->route('login');
    }

}

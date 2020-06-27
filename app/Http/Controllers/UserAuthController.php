<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Hash;
use App\Shop\Entity\User; // 使用者 Eloquent Model
use Mail;
use Exception;

class UserAuthController extends Controller
{
    // 註冊
    public function signUpPage()
    {
        $binding = [
            'title' => '註冊',
        ];
        return view('auth.signup', $binding);
    }

    // 處理註冊資料
    public function signUpProcess()
    {
        // 接收輸入資料
        $input = request()->all();

        // 驗證規則
        $rules = [
            // 暱稱
            'nickname' => [
                'required',
                'max:50',
            ],
            // Email
            'email' => [
                'required',
                'max:150',
                'email',
            ],
            // 密碼
            'password' => [
                'required',
                'same:password_confirmation',
                'min:6',
            ],
            // 密碼驗證
            'password_confirmation' => [
                'required',    
                'min:6',
            ],
            // 帳號類型
            'type' => [
                'required',    
                'in:G,A'
            ],
        ];

        // 驗證資料
        $validator = Validator::make($input,$rules);

        // 資料驗證錯誤
        if ($validator->fails()) {
            return redirect('/user/auth/sign-up')->withErrors($validator)->withInput();
        }

        // 密碼加密
        $input['password'] = Hash::make($input['password']);

        // 新增會員資料
        $Users = User::create($input);

        // 寄送註冊通知信
        $mail_binding = [
            'nickname' => $input['nickname']
        ];

        Mail::send('email.signUpEmailNotification', $mail_binding, function ($mail) use($input) {
            $mail->to($input['email']);
            $mail->from('jackabula23@gmail.com');
            $mail->subject('註冊成功');
        });

        return redirect('/user/auth/sign-in');
    }

    // 登入
    public function signInPage()
    {
        $binding =[
            'title' => '登入',
        ];

        return view('auth.signIn',$binding);
    }

    // 處理登入資料
    public function signInProcess()
    {
        // 接收輸入資料
        $input =request()->all();

        // 驗證規則
        $rules = [
            // Email
            'email' => [
                'required',
                'max:150',
                'email',
            ],

            // 密碼
            'password' => [
                'required',
                'min:6',
            ],
        ];

        // 驗證資料
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            // 資料驗證錯誤
            return redirect('/user/auth/sign-in')->withErrors($validator)->withInput();
        }

        // 取得使用者資料
        $User = User::where('email', $input['email'])->firstOrFail();

        // 檢查密碼是否正確
        $is_password_correct = Hash::check($input['password'], $User->password);

        if(!$is_password_correct) {
            // 密碼錯誤回傳錯誤訊息
            $error_message = [
                'msg' => [
                    '密碼錯誤',
                ],
            ];
            return redirect('/user/auth/sign-in')->withErrors($error_message)->withInput();
        }

        // session 紀錄會員編號
        session()->put('user_id', $User->id);

        // 重新導向到原先使用者造訪頁面，沒有嘗試造訪頁則重新導向回首頁
        return redirect()->intended('/');
    }

    // 處理登出資料
    public function signOut()
    {
        // 清除session
        session()->forget('user_id');

        // 重新導回登入頁
        return redirect('/user/auth/sign-in');
    }

    public function facebookSignInProcess()
    {
        
    }

    public function facebookSignInCallbackProcess()
    {
        
    }

    public function githubSignInProcess()
    {

    }

    public function githubSignInCallbackProcess()
    {

    }
}

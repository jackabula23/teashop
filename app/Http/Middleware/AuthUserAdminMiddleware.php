<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class AuthUserAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // 處理請求
    public function handle($request, Closure $next)
    {
        // 預設不允許存取
        $is_allow_access = false;
        // 取得會員編號
        $user_id = session()->get('user_id');

        if(!is_null($user_id)){
            // session有會員編號，取得會員資料
            $User = User::findOrFail($user_id);
            
            if ($User->type == 'A') {
                // 是管理者，允許存取
                $is_allow_access = true;
            }
        }

        if (!$is_allow_access) {
            // 不允許存取，重新導向至首頁
            return redirect()->to('/');
        }

        // 允許請求，繼續做下個請求的處理
        return $next($request);
    }
}

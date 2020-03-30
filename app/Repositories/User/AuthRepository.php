<?php
namespace App\Repositories\User;

use App\Models\User\User;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository extends BaseRepository
{
    public function passLogin(Request $request)
    {
        $phone = $request->input('phone');
        $pass_word = $request->input('pass_word');
        $user_info = User::query()->where('phone', $phone)->first();
        if (empty($user_info)) return $this->failed('该用户不存在');

        $token = Auth::guard('api')->setTTL(604800)->fromUser($user_info);

        //让上一个token过期
        if ($user_info->last_token) JWTAuth::setToken($user_info->last_token)->invalidate();
        //将新的token存储到数据库
        $user_info->last_token = $token;
        $user_info->save();

         $info = [
            'token' => 'bearer ' . $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL()
        ];
        return $this->success($info);
    }
}
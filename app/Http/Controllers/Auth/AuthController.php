<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    public function register(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'name' => 'required',
            'account' => 'required|unique:App\Models\User,account',
            'email' => 'required|email|unique:App\Models\User,email',
            'password' => 'required',
        ], [
            'required' => '欄位沒有填寫完整!',
            'email.email' => '信箱格式錯誤',
            'account.unique' => '帳號已被使用',
            'email.unique' => '信箱已被使用',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 402);
        } else {
            $user = User::create([
                'name' => $data->name,
                'account' => $data->account,
                'email' => $data->email,
                'password' => bcrypt($data->password),
                'remember_token' => Str::random(10),
                'pic_url' => 'https://bakerychu.com/CodingForum/default_user.png'
            ]);
            $token = $user->createToken('Laravel9PassportAuth')->accessToken;
            return response()->json(['success' => $token], 200);
        }
    }

    public function login(Request $data)
    {
        $userdata = [
            'account' => $data->account,
            'password' => $data->password
        ];

        if (Auth::attempt($userdata)) {
            $token = auth()->user()->createToken('Laravel9PassportAuth')->accessToken;
            return response()->json(['success' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function user()
    {
        $user = Auth::user();
        return response()->json(['user' => $user], 200);

        //  return response()->json($request->user());
    }

    public function logout()
    {
        Auth::user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}

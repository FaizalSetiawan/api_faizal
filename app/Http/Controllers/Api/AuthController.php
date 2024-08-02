<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:8'
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ]);
        $res = [
            'success' => true,
            'message' => 'user berhasil dibuat',
            'data' => $user
        ];
        return response()->json($res, 201);
    }
    public function login(Request $request)
    {
        if(!Auth::attempt($request->only('email', 'password')))
        {
            $res = [
                'message' => 'unauthorize'
            ];
            return response()->json($res, 401);
        }
        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        $res = [
            'success' => true,
            'message' => 'login berhasil',
            'access_token' => $token,
            'type' => 'Bearer'
        ];
        return response()->json($res, 200);
    }
    public function logout()
    {
        Auth::user()->tokens->delete();
        $res = [
            'Message' => 'log out berhasil'
        ];
        return response()->json($res, 200);
    }
}


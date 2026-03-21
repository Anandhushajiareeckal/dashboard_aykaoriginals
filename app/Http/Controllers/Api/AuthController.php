<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
    public function login(Request $req) {
        $req->validate(['email'=>'required|email','password'=>'required']);
        if (!$token = JWTAuth::attempt($req->only('email','password'))) {
            return response()->json(['message'=>'Invalid credentials'], 401);
        }
        return response()->json(['token'=>$token,'user'=>auth()->user()]);
    }
    public function me() {
        return response()->json(auth()->user()->load('roles'));
    }
    public function logout() {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message'=>'Logged out']);
    }
}

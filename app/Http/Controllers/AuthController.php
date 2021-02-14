<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{

    public function login(Request $request) {

        $validator = Validator::make($request->all(), [
            "email" =>  "required|email",
            "password" =>  "required",
        ]);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors(), "status_code" => 400]);
        }

        $user = User::where("email", $request->email)->first();

        if(is_null($user)) {
            return response()->json(["status" => "failed", "message" => "Failed! email not found", "status_code" => 400]);
        }

        $credentials = $request->only('email', 'password');
        if(!Auth::attempt($credentials)){
            return response()->json(["status" => "Unauthorized", "success" => false, "message" => "Whoops! invalid password", "status_code" => 500]);
        }

        $user = User::where("email", $request->email)->first();
        $tokenResult = $user->createToken('authToken')->plainTextToken;
        
        return response()->json([
            'status_code' => 200,
            'token' => $tokenResult
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['data' => 'User logged out.'], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Controllers\BaseController;

class AuthController extends BaseController
{

    public function login(Request $request) {
        $this->validateHeaders($request);
        $validator = Validator::make($request->all(), [
            "email" =>  "required|email",
            "password" =>  "required",
        ]);

        if($validator->fails())
            return $this->sendError($validator->errors(),400);

        $user = User::where("email", $request->email)->first();

        if(is_null($user))
            return $this->sendError(["Failed! email not found"]);

        if(!Auth::attempt($request->only('email', 'password')))
            return $this->sendError(["invalid password"], 401);
        
        $tokenResult = User::where("email", $request->email)
                        ->first()
                        ->createToken('authToken')
                        ->plainTextToken;
        
        return $this->sendResponse($tokenResult);
    }

    public function logout(Request $request)
    {
        $this->validateHeaders($request);
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse(null);
    }
}

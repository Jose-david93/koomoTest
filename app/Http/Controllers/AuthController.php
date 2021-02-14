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

        $validator = Validator::make($request->all(), [
            "email" =>  "required|email",
            "password" =>  "required",
        ]);

        if($validator->fails())
            return $this->sendError("validation_errors",400,$validator->errors());

        $user = User::where("email", $request->email)->first();

        if(is_null($user))
            return $this->sendError("Failed! email not found");

        if(!Auth::attempt($request->only('email', 'password')))
            return $this->sendError("Whoops! invalid password", 401, []);
        
        $tokenResult = User::where("email", $request->email)
                        ->first()
                        ->createToken('authToken')
                        ->plainTextToken;
        
        return $this->sendResponse($tokenResult);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse("Token deleted successfully");
    }
}

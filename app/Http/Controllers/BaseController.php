<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($result, $httpResponseCode = 200)
    {
        return response()->json($result, $httpResponseCode);
    }

    public function sendError($error, $errorMessages = [], $httpResponseCode = 400)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $httpResponseCode);
    }

    public function isCurrentUserOwner($id){
        return auth('sanctum')->id() == $id;
    }
}

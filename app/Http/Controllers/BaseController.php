<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($result, $httpResponseCode = 200)
    {
        $response = [
            'data' => $result
        ];

        return response()->json($response, $httpResponseCode);
    }

    public function sendError($error, $httpResponseCode = 400)
    {
    	$response = [
            'success' => false,
            'errors' => $error,
        ];

        return response()->json($response, $httpResponseCode);
    }

    public function isCurrentUserOwner($id){
        return auth('sanctum')->id() == $id;
    }

    public function validateHeaders(Request $request) {
        $response['isValid'] = true;
        if($request->header("accept") == null)
        {
            $response['isValid'] = false;
            $response['message'] = "Not Acceptable";
            $response['code'] = 406;
        }
        else if($request->header("accept") !== "application/vnd.api+json")
        {
            $response['isValid'] = false;
            $response['message'] = "Unsupported Media Type";
            $response['code'] = 415;
        }
        
        return $response;
    }
}

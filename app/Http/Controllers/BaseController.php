<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($result, $httpResponseCode = Response::HTTP_OK)
    {
        $response = [
            'data' => $result
        ];

        return response()->json($response, $httpResponseCode);
    }

    public function sendError($error, $httpResponseCode = Response::HTTP_BAD_REQUEST)
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
            $response['code'] = Response::HTTP_NOT_ACCEPTABLE;
        }
        else if($request->header("accept") !== "application/vnd.api+json")
        {
            $response['isValid'] = false;
            $response['message'] = "Unsupported Media Type";
            $response['code'] = Response::HTTP_UNSUPPORTED_MEDIA_TYPE ;
        }
        
        return $response;
    }
}

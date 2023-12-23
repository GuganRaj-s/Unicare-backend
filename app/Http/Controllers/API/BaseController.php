<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($status, $code, $message, $parameter, $result = [])
    {
    	$response = [
            'status' => $status,
            'message' => $message,
        ];

        if(!empty($result)){
            $response[$parameter] = $result;
        }

        return response()->json($response, $code);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($status, $message, $errorMessages = [])
    {
    	$response = [
            'status' => $status,
            'message' => $message,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, 404);
    }
}
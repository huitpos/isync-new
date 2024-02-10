<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller as Controller;


class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message = null)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
            'code' => Config::get('app.status_codes')['success']
        ];


        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404, $errorCode = null)
    {
        $response = [
            'success' => false,
            'message' => $error,
            'code' => $errorCode ?? Config::get('app.status_codes')['failed']
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
}

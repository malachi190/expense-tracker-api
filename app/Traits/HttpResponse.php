<?php


namespace App\Traits;


trait HttpResponse {
    public function success($data, $message=null, $code=200){
        return response()->json([
            "status" => "Request successfull!",
            "message" => $message,
            "data" => $data
        ], $code);
    }


    public function error($data, $message=null, $code=500){
        return response()->json([
            "status" => "An error occured...",
            "message" => $message,
            "data" => $data
        ], $code);
    }
}

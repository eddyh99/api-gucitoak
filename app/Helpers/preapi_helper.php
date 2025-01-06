<?php
use App\Models\ValidateToken;

function getToken($token){
    if (is_null($token)){
        throw new Exception('API Key not found, contact administrator to get an API Key');
    }
    return explode(" ",$token)[1];
}

function validateKey($token){
    $validateToken = new ValidateToken();
    $validateToken->checkAPIkey($token);
}


function error_msg($header, $service, $err_code, $message){
   $error=array(
            "code"      => $header,
            "service"   => $service,
            "error"     => $err_code,
            "message"   => $message
        );
    return $error;
}

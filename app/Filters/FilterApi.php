<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use Exception;

class FilterApi implements FilterInterface
{
    use ResponseTrait;
    public function before(RequestInterface $request, $arguments=null){
        //membaca http authorization
        $method = $request->getMethod();
        if (($method!='GET') && ($method!='POST')){
            return \Config\Services::response()->setJSON([
                    'error' => 'Method Not Allowed'
                ])->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED);
        }
        $content_type=$request->getHeaderLine('content-type');
        if ($content_type!='application/json'){
            return \Config\Services::response()->setJSON([
                'error' => 'Unsupported Media Type'
            ])->setStatusCode(ResponseInterface::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }
        $header=$request->getServer('HTTP_AUTHORIZATION');
        try{
            //memanggil fungsi helper preapi
            helper('preapi');
            //membaca token dari header dengan fungsi yang ada di helper preapi
            $token=getToken($header);
            //memvalidasi token ke database
            validateKey($token);
            return $request;            
        } catch(Exception $e){
            return \Config\Services::response()->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        return $header;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments=null){
        return $this->failUnauthorized();
    }
}
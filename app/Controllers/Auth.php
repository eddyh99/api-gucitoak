<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->member  = model('App\Models\V1\Mdl_member');
        $this->subscription  = model('App\Models\V1\Mdl_subscription');
		$this->pengguna       = model('App\Models\V1\Mdl_pengguna');
		$this->sales  = model('App\Models\V1\Mdl_sales');
	}
	

	public function register(){
	    $validation = $this->validation;
        $validation->setRules([
					'email' => [
						'rules'  => 'required|valid_email',
						'errors' => [
							'required'      => 'Email is required',
							'valid_email'   => 'Invalid Email format'
						]
					],
					'password' => [
					    'rules'  => 'required|min_length[8]',
					    'errors' =>  [
					        'required'      => 'Password is required',
					        'min_length'    => 'Min length password is 8 character'
					    ]
					],
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User timezone is required',
					    ]
					]
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data           = $this->request->getJSON();

        $mdata = array(
    	        "email"     => filter_var($data->email,FILTER_VALIDATE_EMAIL) ,
    	        "passwd"    => htmlspecialchars($data->password),
    	        "timezone"  => $data->timezone
    	);


	    if (!empty($data->referral)) {
    	    $refmember = $this->member->getby_refcode($data->referral);
    	    if (@$refmember->code==400){
    	        return $this->respond(@$refmember,$member->code);
    	    }
	        $mdata["id_referral"] = $refmember->id;

	    }
	    
	    $result = $this->member->add($mdata);
	    if (@$result->code==1060){
    	   return $this->fail(@$result);
	    }
	    $response=[
	                    "token"   => $result->token,
	              ];
	   return $this->respond(error_msg(201,"auth",null,$response),201);
	}
	
	

    public function signin(){
	    $validation = $this->validation;
        $validation->setRules([
					'username' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'username is required',
						]
					],
					'password' => [
					    'rules'  => 'required|min_length[8]',
					    'errors' =>  [
					        'required'      => 'Password is required',
					        'min_length'    => 'Min length password is 8 character'
					    ]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data           = $this->request->getJSON();

        $member = $this->member->getby_id($data->username);
	    if (@$member->code==400){
            return $this->respond(error_msg($member->code,"auth","01",$member),$member->code);
	    }

        if ($data->password == $member->message->passwd) {
        	    $response=$member->message;
				$response->akses = $this->pengguna->getAkses_byId($response->id);
                return $this->respond(error_msg(200,"auth","02",$response),200);
        }else{
                $response= "Invalid username or password";
                return $this->respond(error_msg(400,"auth","02",$response),400);
}
    }

    public function resetpassword(){
	    $email = $this->request->getGet('email', FILTER_SANITIZE_STRING);
        $token=$this->member->resetToken($email);

	    if (@$token->code==400){
	        return $this->respond(@$token,$token->code);
	    }

	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                    "token"   => $token
	                ]
	        ];
	   return $this->respond($response);
	}    
	
	public function updatepassword(){
	    $validation = $this->validation;
        $validation->setRules([
                    'token' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Reset token is required',
						]
					],
					'password' => [
						'rules'  => 'required|min_length[40]',
						'errors' => [
							'required'      => 'Password is required',
							'min_length'    => 'Min length password is 40 characters'
						]
					]
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }

	    $data       = $this->request->getJSON();
	    $member     = $this->member->getby_token(htmlspecialchars($data->token));
	    if (@$member->code==400){
    	    return $this->respond($member,$member->code);
	    }

        $where=array(
            "email"     => $member->email,
            "token"     => $data->token
            );
        $mdata=array(
            "passwd"    => $data->password,
            "token"     => NULL
            );
            
        $result=$this->member->change_password($mdata,$where);
        if (@$result->code==400){
	        return $this->respond(@$result,$member->code);
	    }
	    $response=[
	            "code"      => "200",
	            "error"      => null,
	            "message"    => "Password successfully changed"
	        ];
	    return $this->respond($response);
        
	}

	public function signin_sales(){
	    $validation = $this->validation;
        $validation->setRules([
					'username' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'username is required',
						]
					],
					'password' => [
					    'rules'  => 'required|min_length[8]',
					    'errors' =>  [
					        'required'      => 'Password is required',
					        'min_length'    => 'Min length password is 8 character'
					    ]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data           = $this->request->getJSON();

        $sales = $this->sales->getby_id($data->username);
	    if (@$sales->code==400){
            return $this->respond(error_msg($sales->code,"auth","01",$sales->message),$sales->code);
	    }

		$member = $this->member->getby_Role('sales');
		if(@$member->code==400) {
			return $this->respond(error_msg(400,"auth","01",$member->message),400);
		}

        if ($data->password == $sales->message->password && $member->code==200) {
				$response=$member->message;
				$response->sales = $sales->message->namasales;
				$response->id_sales = $sales->message->id;
				$response->akses = $this->pengguna->getAkses_byId($response->id);
                return $this->respond(error_msg(200,"auth","02",$response),200);
        }else{
                $response= "Invalid username or password";
                return $this->respond(error_msg(400,"auth","02",$response),400);
}
    }
}

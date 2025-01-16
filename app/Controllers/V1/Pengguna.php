<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Pengguna extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->pengguna       = model('App\Models\V1\Mdl_pengguna');
	}
    
    public function getall_pengguna(){
        $result = $this->pengguna->get_pengguna();
        return $this->respond(error_msg(200,"pengguna",null,$result),200);
    }
    
    public function getpengguna_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->pengguna->get_penggunabyid($id);
        return $this->respond(error_msg(200,"pengguna",null,$result),200);
    }
    
    public function add_pengguna(){
        $validation = $this->validation;
        $validation->setRules([
					'username' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Username wajib',
						]
					],
					'password' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Password wajib',
						]
					],
					'role' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Role wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"pengguna","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'username'  => htmlspecialchars($data->username), 
            'passwd'    => htmlspecialchars($data->password), 
            'role'      => htmlspecialchars($data->role),
            'status'    => 'active',
    	    "created_at"    => date("Y-m-d H:i:s")
        );

	    $result = $this->pengguna->add($mdata);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"pengguna","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"pengguna",null,"Sukses menyimpan pengguna",201));
    }
    
    public function ubah_pengguna(){
        $validation = $this->validation;
        $validation->setRules([
					'username' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Username wajib',
						]
					],
					'password' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Password wajib',
						]
					],
					'role' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Role wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"pengguna","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    
        if (!empty($data->password)){
            $mdata = array(
                'passwd'  => htmlspecialchars($data->password), 
                'role'      => htmlspecialchars($data->role),
                'status'    => 'active',
        	    "update_at" => date("Y-m-d H:i:s")
            );
        }else{
            $mdata = array(
                'role'      => htmlspecialchars($data->role),
                'status'    => 'active',
        	    "update_at" => date("Y-m-d H:i:s")
            );
        }

        $id     = htmlspecialchars($this->request->getGet('id'));
	    $result = $this->pengguna->ubah($mdata,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"pengguna","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"pengguna",null,"Sukses mengubah pengguna",200));

    }
    
    public function hapus_pengguna(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->pengguna->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"pengguna","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"pengguna",null,"Sukses menghapus pengguna"),200);

    }

    public function hak_akses() {
        $data           = $this->request->getJSON();
        $mdata = array(
            'pengguna_id' => $data->pengguna_id,
            'akses' => $data->akses
        );
        $result = $this->pengguna->giveAkses($mdata);
        if (@$result->code!=201){
            return $this->respond(error_msg(400,"pengguna","01",$mdata),400);
	    }

        return $this->respond(error_msg(200,"pengguna",null,'Berhasil.'),200);
    }
}

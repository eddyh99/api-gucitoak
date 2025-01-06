<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Cabang extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->cabang       = model('App\Models\V1\Mdl_cabang');
	}
    
    public function getall_cabang(){
        $result = $this->cabang->get_cabang();
        return $this->respond(error_msg(200,"cabang",null,$result),200);
    }
    
    public function getcabang_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->cabang->get_cabangbyid($id);
        return $this->respond(error_msg(200,"cabang",null,$result),200);
    }
    
    public function add_cabang(){
        $validation = $this->validation;
        $validation->setRules([
					'namacabang' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama cabang wajib',
						]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"cabang","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $data = (object) array(
            'namacabang' => htmlspecialchars($data->namacabang), 
            'alamat'     => htmlspecialchars($data->alamat), 
            'lat'        => htmlspecialchars($data->lat), 
            'long'       => htmlspecialchars($data->long), 
        );

        $mdata = array(
    	        "namacabang" => $data->namacabang,
    	        "alamat"     => $data->alamat,
    	        "lat"        => $data->lat,
    	        "long"       => $data->long,
    	        "created_at" => date("Y-m-d H:i:s")
    	);

	    $result = $this->cabang->add($mdata);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"cabang","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"cabang",null,"Sukses menyimpan cabang",201));
    }
    
    public function ubah_cabang(){
        $validation = $this->validation;
        $validation->setRules([
					'namacabang' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama cabang wajib',
						]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"cabang","01",$validation->getErrors()),400);
        }
	    $data           = $this->request->getJSON();
	    

        $data = (object) array(
            'namacabang' => htmlspecialchars($data->namacabang), 
            'alamat'     => htmlspecialchars($data->alamat), 
            'lat'        => htmlspecialchars($data->lat), 
            'long'       => htmlspecialchars($data->long), 
        );

        $mdata = array(
    	        "namacabang" => $data->namacabang,
    	        "alamat"     => $data->alamat,
    	        "lat"        => $data->lat,
    	        "long"       => $data->long,
    	        "update_at"    => date("Y-m-d H:i:s")
    	);

        $id     = htmlspecialchars($this->request->getGet('id'));
	    $result = $this->cabang->ubah($mdata,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"cabang","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"cabang",null,"Sukses mengubah cabang",200));

    }
    
    public function hapus_cabang(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->cabang->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"cabang","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"cabang",null,"Sukses menghapus cabang"),200);

    }
}

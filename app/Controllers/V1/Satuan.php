<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Satuan extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->satuan       = model('App\Models\V1\Mdl_satuan');
	}
    
    public function getall_satuan(){
        $result = $this->satuan->get_satuan();
        return $this->respond(error_msg(200,"satuan",null,$result),200);
    }
    
    public function getsatuan_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->satuan->get_satuanbyid($id);
        return $this->respond(error_msg(200,"satuan",null,$result),200);
    }
    
    public function add_satuan(){
        $validation = $this->validation;
        $validation->setRules([
					'namasatuan' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama satuan wajib',
						]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"satuan","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $data = (object) array(
            'namasatuan'  => htmlspecialchars($data->namasatuan), 
        );

        $mdata = array(
    	        "namasatuan"  => $data->namasatuan,
    	        "created_at"    => date("Y-m-d H:i:s")
    	);

	    $result = $this->satuan->add($mdata);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"satuan","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"satuan",null,"Sukses menyimpan satuan",201));
    }
    
    public function ubah_satuan(){
        $validation = $this->validation;
        $validation->setRules([
					'namasatuan' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama satuan wajib',
						]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"satuan","01",$validation->getErrors()),400);
        }
	    $data           = $this->request->getJSON();
	    

        $data = (object) array(
            'namasatuan'  => htmlspecialchars($data->namasatuan), 
        );

        $mdata = array(
    	        "namasatuan"  => $data->namasatuan,
    	        "update_at"    => date("Y-m-d H:i:s")
    	);

        $id     = htmlspecialchars($this->request->getGet('id'));
	    $result = $this->satuan->ubah($mdata,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"satuan","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"satuan",null,"Sukses mengubah satuan",200));

    }
    
    public function hapus_satuan(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->satuan->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"satuan","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"satuan",null,"Sukses menghapus satuan"),200);

    }
}

<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Suplier extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->suplier       = model('App\Models\V1\Mdl_suplier');
	}
    
    public function getall_suplier(){
        $result = $this->suplier->get_suplier();
        return $this->respond(error_msg(200,"suplier",null,$result),200);
    }
    
    public function getsuplier_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->suplier->get_suplierbyid($id);
        return $this->respond(error_msg(200,"suplier",null,$result),200);
    }
    
    public function add_suplier(){
        $validation = $this->validation;
        $validation->setRules([
					'namasuplier' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama suplier wajib',
						]
					],
					'alamat' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Alamat wajib',
						]
					],
					'kota' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Kota wajib',
						]
					],
					'telp' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Telp wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"suplier","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'namasuplier' => htmlspecialchars($data->namasuplier), 
            'pemilik'   => htmlspecialchars($data->pemilik),
            'alamat'    => htmlspecialchars($data->alamat), 
            'kota'      => htmlspecialchars($data->kota), 
            'telp'      => htmlspecialchars($data->telp), 
            'norek'     => htmlspecialchars($data->norek), 
            'namabank'  => htmlspecialchars($data->namabank), 
            'anbank'    => htmlspecialchars($data->anbank), 
    	    "created_at"    => date("Y-m-d H:i:s")
        );

	    $result = $this->suplier->add($mdata);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"suplier","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"suplier",null,"Sukses menyimpan suplier",201));
    }
    
    public function ubah_suplier(){
        $validation = $this->validation;
        $validation->setRules([
					'namasuplier' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama suplier wajib',
						]
					],
					'alamat' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Alamat wajib',
						]
					],
					'kota' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Kota wajib',
						]
					],
					'telp' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Telp wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"suplier","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'namasuplier' => htmlspecialchars($data->namasuplier), 
            'pemilik'   => htmlspecialchars($data->pemilik),
            'alamat'    => htmlspecialchars($data->alamat), 
            'kota'      => htmlspecialchars($data->kota), 
            'telp'      => htmlspecialchars($data->telp), 
            'norek'     => htmlspecialchars($data->norek), 
            'namabank'  => htmlspecialchars($data->namabank), 
            'anbank'    => htmlspecialchars($data->anbank), 
	        "update_at"    => date("Y-m-d H:i:s")
        );

        $id     = htmlspecialchars($this->request->getGet('id'));
	    $result = $this->suplier->ubah($mdata,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"suplier","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"suplier",null,"Sukses mengubah suplier",200));

    }
    
    public function hapus_suplier(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->suplier->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"suplier","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"suplier",null,"Sukses menghapus suplier"),200);

    }
}

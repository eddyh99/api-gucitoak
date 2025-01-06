<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Kategori extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->kategori       = model('App\Models\V1\Mdl_kategori');
	}
    
    public function getall_kategori(){
        $result = $this->kategori->get_kategori();
        return $this->respond(error_msg(200,"kategori",null,$result),200);
    }
    
    public function getkategori_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->kategori->get_kategoribyid($id);
        return $this->respond(error_msg(200,"kategori",null,$result),200);
    }
    
    public function add_kategori(){
        $validation = $this->validation;
        $validation->setRules([
					'namakategori' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama kategori wajib',
						]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"kategori","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $data = (object) array(
            'namakategori'  => htmlspecialchars($data->namakategori), 
        );

        $mdata = array(
    	        "namakategori"  => $data->namakategori,
    	        "created_at"    => date("Y-m-d H:i:s")
    	);

	    $result = $this->kategori->add($mdata);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"kategori","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"kategori",null,"Sukses menyimpan kategori",201));
    }
    
    public function ubah_kategori(){
        $validation = $this->validation;
        $validation->setRules([
					'namakategori' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama kategori wajib',
						]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"kategori","01",$validation->getErrors()),400);
        }
	    $data           = $this->request->getJSON();
	    

        $data = (object) array(
            'namakategori'  => htmlspecialchars($data->namakategori), 
        );

        $mdata = array(
    	        "namakategori"  => $data->namakategori,
    	        "update_at"    => date("Y-m-d H:i:s")
    	);

        $id     = htmlspecialchars($this->request->getGet('id'));
	    $result = $this->kategori->ubah($mdata,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"kategori","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"kategori",null,"Sukses mengubah kategori",200));

    }
    
    public function hapus_kategori(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->kategori->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"kategori","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"kategori",null,"Sukses menghapus kategori"),200);

    }
}

<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Retur extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->retur       = model('App\Models\V1\Mdl_retur');
	}
    

    public function add_returpel(){
        $validation = $this->validation;
        $validation->setRules([
					'pelanggan_id' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Pelanggan wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"retur","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();

        $mdata = array(
            'tanggal'       => date("Y-m-d H:i:s"),
            'pelanggan_id'  => $data->pelanggan_id,
        );


	    $result = $this->retur->retur_pelanggan($mdata,$data->detail);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"retur","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"retur",null,"Sukses menyimpan retur",201));
    }
    
    public function add_retursup(){
        $validation = $this->validation;
        $validation->setRules([
					'id_suplier' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Suplier wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"retur","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();

        $mdata = array(
            'tanggal'       => date("Y-m-d H:i:s"),
            'id_suplier'  => $data->id_suplier,
        );


	    $result = $this->retur->retur_suplier($mdata,$data->detail);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"retur","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"retur",null,"Sukses menyimpan retur",201));
    }
    
    public function get_returpelanggan(){
        $awal   = $this->request->getGet('awal');
        $akhir  = $this->request->getGet('akhir');
        $result = $this->retur->getretur_pelanggan($awal,$akhir);
        return $this->respond(error_msg(200,"retur",null,$result),200);
    }

    public function get_retursuplier(){
        $awal   = $this->request->getGet('awal');
        $akhir  = $this->request->getGet('akhir');
        $result = $this->retur->getretur_suplier($awal,$akhir);
        return $this->respond(error_msg(200,"retur",null,$result),200);
    }
    
    public function getbarang_retursup(){
        $id     = $this->request->getGet('id');
        $result = $this->retur->barang_retursup($id);
        return $this->respond(error_msg(200,"retur",null,$result),200);
    }

    public function getbarang_returpel(){
        $id     = $this->request->getGet('id');
        $result = $this->retur->barang_returpel($id);
        return $this->respond(error_msg(200,"retur",null,$result),200);
    }

}

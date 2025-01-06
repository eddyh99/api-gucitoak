<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Pembelian extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->pembelian       = model('App\Models\V1\Mdl_pembelian');
	}
    
    public function get_allpembelian(){
        $awal   = $this->request->getGet('awal');
        $akhir  = $this->request->getGet('akhir');
        $result = $this->pembelian->get_pembelian($awal,$akhir);
        return $this->respond(error_msg(200,"pembelian",null,$result),200);
    }

    public function add_pembelian(){
        $validation = $this->validation;
        $validation->setRules([
					'nonota' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'No. Nota suplier wajib',
						]
					],
					'id_suplier' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Suplier wajib',
						]
					],
					'method' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Cara pembayaran wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"pembelian","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();

        $mdata = array(
            'nonota'        => $data->nonota,
            'tanggal'       => $data->tanggal,
            'id_suplier'    => $data->id_suplier, 
            'method'        => $data->method,
            'waktu'         => $data->waktu,
        );

        
        
	    $result = $this->pembelian->add($mdata,$data->detail);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"pembelian","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"pembelian",null,"Sukses menyimpan pembelian",201));
    }
    
    public function get_barangbeli(){
        $id     = $this->request->getGet('id');
        $result = $this->pembelian->getbarang_beli($id);
        return $this->respond(error_msg(200,"pembelian",null,$result),200);

    }
    

}

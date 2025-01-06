<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Penjualan extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->penjualan       = model('App\Models\V1\Mdl_penjualan');
	}
    
    public function get_allpenjualan(){
        $awal   = $this->request->getGet('awal');
        $akhir  = $this->request->getGet('akhir');
        $result = $this->penjualan->get_penjualan($awal,$akhir);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }
    
    public function add_penjualan(){
        $validation = $this->validation;
        $validation->setRules([
					'pelanggan_id' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Pelanggan wajib',
						]
					],
					'sales_id' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Sales wajib',
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
            return $this->respond(error_msg(400,"penjualan","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    $nonota         = $this->penjualan->getNota();

        $mdata = array(
            'nonota'        => $nonota,
            'tanggal'       => date("Y-m-d H:i:s"),
            'sales_id'      => $data->sales_id, 
            'pelanggan_id'  => $data->pelanggan_id,
            'method'        => $data->method,
            'waktu'         => $data->waktu,
        );

        $detail=array();
        foreach ($data->detail as $brg){
            $temp["nonota"]     = $nonota;
            $temp["barcode"]    = $brg->barcode;
            $temp["jumlah"]     = $brg->jml;
            array_push($detail,$temp);
        }
        
	    $result = $this->penjualan->add($mdata,$detail);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"penjualan","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"penjualan",null,"Sukses menyimpan penjualan",201));
    }
    
    public function get_barangjual(){
        $nonota = $this->request->getGet('nonota');
        $result = $this->penjualan->getbarang_jual($nonota);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);

    }
    

}

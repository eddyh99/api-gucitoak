<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Pelanggan extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->pelanggan       = model('App\Models\V1\Mdl_pelanggan');
	}
    
    public function getall_pelanggan(){
        $result = $this->pelanggan->get_pelanggan();
        return $this->respond(error_msg(200,"pelanggan",null,$result),200);
    }
    
    public function getpelanggan_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->pelanggan->get_pelangganbyid($id);
        return $this->respond(error_msg(200,"pelanggan",null,$result),200);
    }
    
    public function add_pelanggan(){
        $validation = $this->validation;
        $validation->setRules([
					'namapelanggan' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama pelanggan wajib',
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
            return $this->respond(error_msg(400,"pelanggan","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'namapelanggan' => htmlspecialchars($data->namapelanggan), 
            'pemilik'   => htmlspecialchars($data->pemilik), 
            'alamat'    => htmlspecialchars($data->alamat), 
            'kota'      => htmlspecialchars($data->kota), 
            'harga'     => htmlspecialchars($data->harga), 
            'telp'      => htmlspecialchars($data->telp), 
            'gmaps'     => htmlspecialchars($data->gmaps),
            'plafon'    => filter_var($data->plafon, FILTER_SANITIZE_NUMBER_INT), 
            'maxnota'   => filter_var($data->maxnota, FILTER_SANITIZE_NUMBER_INT), 
    	    "created_at"    => date("Y-m-d H:i:s")
        );

	    $result = $this->pelanggan->add($mdata);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"pelanggan","01",$result->message),400);
	    }
        return $this->respond(error_msg(200,"pelanggan",null,"Sukses menyimpan pelanggan",200));
    }
    
    public function ubah_pelanggan(){
        $validation = $this->validation;
        $validation->setRules([
					'namapelanggan' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama pelanggan wajib',
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
            return $this->respond(error_msg(400,"pelanggan","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'namapelanggan' => htmlspecialchars($data->namapelanggan), 
            'pemilik'   => htmlspecialchars($data->pemilik), 
            'alamat'    => htmlspecialchars($data->alamat), 
            'kota'      => htmlspecialchars($data->kota), 
            'telp'      => htmlspecialchars($data->telp), 
            'harga'     => htmlspecialchars($data->harga), 
            'gmaps'     => htmlspecialchars($data->gmaps),
            'plafon'    => filter_var($data->plafon, FILTER_SANITIZE_NUMBER_INT), 
            'maxnota'   => filter_var($data->maxnota, FILTER_SANITIZE_NUMBER_INT), 
	        "update_at"    => date("Y-m-d H:i:s")
        );

        $id     = htmlspecialchars($this->request->getGet('id'));
	    $result = $this->pelanggan->ubah($mdata,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"pelanggan","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"pelanggan",null,"Sukses mengubah pelanggan",200));

    }
    
    public function hapus_pelanggan(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->pelanggan->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"pelanggan","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"pelanggan",null,"Sukses menghapus pelanggan"),200);

    }
    
    public function get_detailpelanggan(){
        $result = $this->pelanggan->detail_pelanggan();
        return $this->respond(error_msg(200,"pelanggan",null,$result),200);
    }
}

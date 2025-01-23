<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Sales extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->sales       = model('App\Models\V1\Mdl_sales');
	}
    
    public function getall_sales(){
        $result = $this->sales->get_sales();
        return $this->respond(error_msg(200,"sales",null,$result),200);
    }
    
    public function getsales_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->sales->get_salesbyid($id);
        return $this->respond(error_msg(200,"sales",null,$result),200);
    }
    
    public function add_sales(){
        $validation = $this->validation;
        $validation->setRules([
					'namasales' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama sales wajib',
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
            return $this->respond(error_msg(400,"sales","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'avatar' => htmlspecialchars($data->avatar), 
            'namasales' => htmlspecialchars($data->namasales), 
            'alamat'    => htmlspecialchars($data->alamat), 
            'kota'      => htmlspecialchars($data->kota), 
            'telp'      => htmlspecialchars($data->telp),
            'omzet'     => filter_var($data->omzet,FILTER_SANITIZE_NUMBER_INT), 
            'gajipokok' => filter_var($data->gajipokok,FILTER_SANITIZE_NUMBER_INT), 
            'komisi'    => filter_var($data->komisi,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)/100, 
            'username' => htmlspecialchars($data->username),
            'password' => htmlspecialchars($data->password),
    	    "created_at"    => date("Y-m-d H:i:s")
        );

	    $result = $this->sales->add($mdata);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"sales","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"sales",null,"Sukses menyimpan sales",201));
    }
    
    public function ubah_sales(){
        $validation = $this->validation;
        $validation->setRules([
					'namasales' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama sales wajib',
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
            return $this->respond(error_msg(400,"sales","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'namasales' => htmlspecialchars($data->namasales), 
            'alamat'    => htmlspecialchars($data->alamat), 
            'kota'      => htmlspecialchars($data->kota), 
            'telp'      => htmlspecialchars($data->telp), 
            'omzet'     => filter_var($data->omzet,FILTER_SANITIZE_NUMBER_INT), 
            'gajipokok' => filter_var($data->gajipokok,FILTER_SANITIZE_NUMBER_INT), 
            'komisi'    => filter_var($data->komisi,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION)/100, 
            'username' => htmlspecialchars($data->username),
	        "update_at"    => date("Y-m-d H:i:s")
        );

        if ($data->avatar) {
            $mdata['avatar'] = htmlspecialchars($data->avatar); // Tambahkan avatar baru ke array
        }
        if ($data->password) {
            $mdata['password'] = htmlspecialchars($data->password); // ubah password
        }

        $id     = htmlspecialchars($this->request->getGet('id'));
	    $result = $this->sales->ubah($mdata,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"sales","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"sales",null,"Sukses mengubah sales",200));

    }
    
    public function hapus_sales(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->sales->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"sales","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"sales",null,"Sukses menghapus sales"),200);

    }
    
    public function add_produk(){
	    $data       = $this->request->getJSON();
        $result     = $this->sales->sales_produk($data);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"sales","04",$result->message),400);
	    }
        return $this->respond(error_msg(200,"sales",null,"Sukses assign produk ke sales"),200);

    }
    
    public function getall_salesbarang(){
        $result = $this->sales->get_salesbarang();
        return $this->respond(error_msg(200,"sales",null,$result),200);

    }

    public function getreport_sales(){
        $id = $this->request->getGet('id');
        $result = $this->sales->get_sales_report($id);
        return $this->respond(error_msg(200,"sales",null,$result),200);
    }
    
}

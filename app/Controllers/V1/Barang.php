<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Barang extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->barang       = model('App\Models\V1\Mdl_barang');
	}
    
    public function getall_barang(){
        $result = $this->barang->get_barang();
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }
    
    public function getstokmin(){
        $result = $this->barang->get_barangmin();
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }
    
    public function getbarang_byid(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->barang->get_barangbyid($id);
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }
    
    public function add_barang(){
        $validation = $this->validation;
        $validation->setRules([
					'namabarang' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama barang wajib',
						]
					],
					'idkategori' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Kategori wajib',
						]
					],
					'idsatuan' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Satuan wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"barang","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
	    

        $mdata = array(
            'foto'       => htmlspecialchars($data->foto),
            'namabarang' => htmlspecialchars($data->namabarang), 
            'id_kategori' => htmlspecialchars($data->idkategori), 
            'id_satuan'   => htmlspecialchars($data->idsatuan), 
            'stokmin'    => filter_var($data->stokmin,FILTER_SANITIZE_NUMBER_INT), 
    	    "created_at" => date("Y-m-d H:i:s")
        );
        
        $harga = array(
                "tanggal"   => date("Y-m-d H:i:s"),
                'harga1'    => filter_var($data->harga1,FILTER_SANITIZE_NUMBER_INT), 
                'harga2'    => filter_var($data->harga2,FILTER_SANITIZE_NUMBER_INT), 
                'harga3'    => filter_var($data->harga3,FILTER_SANITIZE_NUMBER_INT), 
                'disc_pct'  => filter_var($data->disc_pct, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION), 
                'disc_fxd'  => filter_var($data->disc_fxd,FILTER_SANITIZE_NUMBER_INT), 
            );

	    $result = $this->barang->add($mdata,$harga);
	    if (@$result->code!=201){
            return $this->respond(error_msg(400,"barang","01",$result->message),400);
	    }
        return $this->respond(error_msg(201,"barang",null,"Sukses menyimpan barang",201));
    }
    
    public function ubah_barang(){
        $validation = $this->validation;
        $validation->setRules([
					'namabarang' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Nama barang wajib',
						]
					],
					'idkategori' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Kategori wajib',
						]
					],
					'idsatuan' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Satuan wajib',
						]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->respond(error_msg(400,"barang","01",$validation->getErrors()),400);
        }
        
	    $data           = $this->request->getJSON();
        $id     = htmlspecialchars($this->request->getGet('id'));
	    

        $mdata = array(
            'namabarang' => htmlspecialchars($data->namabarang), 
            'id_kategori'=> htmlspecialchars($data->idkategori), 
            'id_satuan'  => htmlspecialchars($data->idsatuan), 
            'stokmin'    => filter_var($data->stokmin,FILTER_SANITIZE_NUMBER_INT), 
    	    "update_at"  => date("Y-m-d H:i:s")
        );

        if ($data->foto) {
            $mdata['foto'] = htmlspecialchars($data->foto); // Tambahkan foto baru ke array
        }
        
        $harga = array(
                "id_barang" => $id,
                "tanggal"   => date("Y-m-d H:i:s"),
                'harga1'    => filter_var($data->harga1,FILTER_SANITIZE_NUMBER_INT), 
                'harga2'    => filter_var($data->harga2,FILTER_SANITIZE_NUMBER_INT), 
                'harga3'    => filter_var($data->harga3,FILTER_SANITIZE_NUMBER_INT), 
                'disc_pct'  => filter_var($data->disc_pct, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION), 
                'disc_fxd'  => filter_var($data->disc_fxd,FILTER_SANITIZE_NUMBER_INT), 
            );

	    $result = $this->barang->ubah($mdata,$harga,$id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"barang","02",$result->message),400);
	    }
        return $this->respond(error_msg(200,"barang",null,"Sukses mengubah barang",200));

    }
    
    public function hapus_barang(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->barang->hapus($id);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"barang","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"barang",null,"Sukses menghapus barang"),200);

    }
    
    public function add_stok(){
	    $data           = $this->request->getJSON();

        $mdata = array();
        $barang= array();
        foreach ($data as $dt) {
            $temp["barang_id"]  = $dt->kodebrg;
            $temp["barcode"]    = $dt->barcode;
            $tgl = \DateTime::createFromFormat("d/m/y", $dt->expdate);
            if ($tgl) {
                $temp["expired"] = $tgl->format("Y-m-d");
            } else {
                $temp["expired"] = null; // or handle the error as needed
            }
            $temp["entry_date"] = date("Y-m-d H:i:s");
            
            $temp2["barcode"] = $dt->barcode;
            $temp2["tanggal"] = date("Y-m-d");
            $temp2["jumlah"]  = $dt->stok;
            $temp2["keterangan"]  = "Stok Awal";
            $temp2["approved"]    = 1;
            array_push($mdata, $temp);
            array_push($barang, $temp2);
        }
        $result = $this->barang->addstok($mdata,$barang);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"barang","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"barang",null,$result->message),200);

    }
    
    public function list_harga(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->barang->get_hargabyid($id);
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }
    
    public function getstok(){
        $result = $this->barang->get_stok();
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }
    
    public function detailstok(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->barang->detailbarcode($id);
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }
    
    public function getStokBy_barcode(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->barang->stok_bybarcode($id);
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }

    public function opname(){
	    $mdata  = $this->request->getJSON();
        $result = $this->barang->addopname($mdata);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"barang","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"barang",null,$result->message),200);

    }
    
    public function listopname(){
        $result = $this->barang->list_opname();
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }

    public function opname_barcode(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $result = $this->barang->barcode_opname($id);
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }

    public function add_dispose(){
	    $data           = $this->request->getJSON();

        $barang = array();
        foreach ($data as $dt) {
            $temp["barcode"]    = $dt->barcode;
            $temp["jumlah"]     = $dt->jml;
            $temp["tanggal"]    = date("Y-m-d");
            $temp["alasan"]     = $dt->alasan;
            array_push($barang, $temp);
        }

        $result = $this->barang->add_dispose($barang);
	    if (@$result->code!=200){
            return $this->respond(error_msg(400,"barang","03",$result->message),400);
	    }
        return $this->respond(error_msg(200,"barang",null,$result->message),200);

    }

    public function get_disposal(){
        $result = $this->barang->get_disposal();
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }

    public function setStatus_disposal(){
        $id     = htmlspecialchars($this->request->getGet('id'));
        $status     = htmlspecialchars($this->request->getGet('status'));
        
        $result = $this->barang->setStatus_disposal($status, $id);
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }

    public function setStatus_opname(){
        $id     = $this->request->getGet('id');
        $status     = $this->request->getGet('status');
        
        $result = $this->barang->setStatus_opname($status, $id);
        return $this->respond(error_msg(200,"barang",null,$result),200);
    }

}

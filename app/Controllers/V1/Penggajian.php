<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Penggajian extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {   
        $this->gaji       = model('App\Models\V1\Mdl_penggajian');
	}

    public function inputGaji_sales()
    {
        // add validasi input
        $data           = $this->request->getJSON();
        $mdata = array(
            'sales_id'  => $data->sales,
            'bulan'  => $data->bulan,
            'gajipokok'  => $data->gajipokok,
            'uangharian'  => $data->uangharian,
            'insentif'  => $data->insentif,
            'komisi'  => $data->komisi,
            'detailnota'  => $data->detailnota,
            'status'      => 'belum'
        );
        $result = $this->gaji->inputGaji_suplier($mdata);
        if (@$result->code != 201) {
            return $this->respond(error_msg(400, "penggajian", "01", $result->message), 400);
        }

        return $this->respond(error_msg(200, "penggajian", null, $result->message), 200);
    }

    public function listGaji_bulanan() {
        $bulan = $this->request->getGet('bulan');
        $result = $this->gaji->getList_gaji($bulan);
        return $this->respond(error_msg(200,"penggajian",null,$result),200);
    }

    public function getGaji_sales() {
        $id = $this->request->getGet('id');
        $tahun = $this->request->getGet('tahun');
        $result = $this->gaji->getGaji_sales($id, $tahun);
        return $this->respond(error_msg(200,"penggajian",null,$result),200);
    }
}

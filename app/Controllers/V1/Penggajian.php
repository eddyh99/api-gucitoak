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
}

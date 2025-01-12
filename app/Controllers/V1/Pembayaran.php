<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Pembayaran extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {   
        $this->penjualan       = model('App\Models\V1\Mdl_penjualan');
        $this->pembayaran       = model('App\Models\V1\Mdl_pembayaran');
	}
    public function pelanggan()
    {
        $awal   = $this->request->getGet('awal');
        $akhir  = $this->request->getGet('akhir');

        $result = $this->penjualan->getNota_belumLunas($awal, $akhir);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }
}

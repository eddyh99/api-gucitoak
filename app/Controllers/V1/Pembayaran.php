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

    public function cekNota_pelanggan() {
        $nota = $this->request->getGet('nota');
        $result = $this->pembayaran->getNota_pelanggan($nota);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function getCicilan_pelanggan() {
        $nota = $this->request->getGet('nota');
        $result = $this->pembayaran->getCicilan_pelanggan($nota);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function inputCicilan_pelanggan() {
        // add validasi input

        $data           = $this->request->getJSON();
        $mdata = array(
            'nonota'        => $data->nonota,
            'tanggal'       => date("Y-m-d"),
            'amount'        => $data->amount
        );
        $result = $this->pembayaran->addCicilan_pelanggan($mdata);
        if (@$result->code!=201){
            return $this->respond(error_msg(400,"penjualan","01",$mdata),400);
	    }

        return $this->respond(error_msg(200,"penjualan",null,'Berhasil menambahkan cicilan.'),200);
    }
}

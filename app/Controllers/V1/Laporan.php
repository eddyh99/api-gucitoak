<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Laporan extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {   
        $this->penjualan       = model('App\Models\V1\Mdl_penjualan');
        $this->pembelian       = model('App\Models\V1\Mdl_pembelian');
        $this->barang       = model('App\Models\V1\Mdl_barang');
	}

    public function barang() {
        $result = $this->barang->get_laporan_barang();
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function mutasi_penjualan()
    {
        $bulan     = htmlspecialchars($this->request->getGet('bulan'));
        $tahun     = htmlspecialchars($this->request->getGet('tahun'));

        $result = $this->penjualan->get_laporan_penjualan($bulan, $tahun);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function mutasi_pembelian()
    {
        $awal   = $this->request->getGet('awal');
        $akhir  = $this->request->getGet('akhir');
        $barang  = $this->request->getGet('barang');

        $result = $this->pembelian->get_laporan_pembelian($awal, $akhir, $barang);
        return $this->respond(error_msg(200,"pembelian",null,$result),200);
    }
}

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
        $this->retur       = model('App\Models\V1\Mdl_retur');
	}

    public function barang() {
        $result = $this->barang->get_laporan_barang();
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function mutasi_stok()
    {
        $bulan     = htmlspecialchars($this->request->getGet('bulan'));
        $tahun     = htmlspecialchars($this->request->getGet('tahun'));

        $result = $this->barang->get_mutasi_stok($bulan, $tahun);
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

    public function retursup() {
        $bulan     = htmlspecialchars($this->request->getGet('bulan'));
        $tahun     = htmlspecialchars($this->request->getGet('tahun'));
        $suplier  = $this->request->getGet('suplier');

        $result = $this->retur->get_laporan_retursup($bulan, $tahun, $suplier);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function returpel() {
        $bulan     = htmlspecialchars($this->request->getGet('bulan'));
        $tahun     = htmlspecialchars($this->request->getGet('tahun'));
        $pelanggan  = $this->request->getGet('pelanggan');

        $result = $this->retur->get_laporan_returpel($bulan, $tahun, $pelanggan);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }
}

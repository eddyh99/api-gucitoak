<?php

namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use DateTime;
use Google\Service\CloudSearch\Id;

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

    public function omzet_pelanggan() {
        $id = $this->request->getGet('id');
        $date = new DateTime(date('Y-m'));
        $bulan[] = $date->format('Y-m');
        
        // Menghitung bulan dari bulan saat ini hingga 12 bulan sebelumnya
        for ($i = 1; $i <= 12; $i++) {
            $bulan[] = $date->modify("-1 month")->format('Y-m');
        }
        
        $result = $this->penjualan->get_omzet_pelanggan(array_reverse($bulan), $id);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function outlet_idle() {
        $result = $this->penjualan->getOutlet_idle();
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function penjualan_outlet() {
        $id = $this->request->getGet('id');
        $result = $this->penjualan->get_penjualan_outlet($id);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

    public function get_katalog() {
        $kategori = $this->request->getGet('id');
        $result = $this->barang->get_katalog($kategori);
        return $this->respond(error_msg(200,"penjualan",null,$result),200);
    }

}

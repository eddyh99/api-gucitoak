<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_retur extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }


    public function retur_pelanggan($mdata,$detail)
    {
        try {
            // Start Transaction
            $this->db->transBegin();
        
            // Table initialization
            $retur = $this->db->table("retur_jual");
            $jual_detail = $this->db->table("retur_jual_detail");
            
            // Insert into 'pembelian'
            if (!$retur->insert($mdata)) {
                // Rollback if 'pembelian' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => $this->db->error()//"Gagal menyimpan data pembelian"
                ];
            }
            
            $id = $this->db->insertID();
            $detbrg=array();
            foreach ($detail as $brg){
                $temp["id"]         = $id;
                $temp["barcode"]    = $brg->barcode;
                $temp["jumlah"]     = $brg->jml;
                array_push($detbrg,$temp);
            }
        
            // InsertBatch into 'pembelian_detail'
            if (!$jual_detail->insertBatch($detbrg)) {
                // Rollback if 'pembelian_detail' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan detail pembelian"
                ];
            }
        
            // Commit the transaction
            $this->db->transCommit();
        
            return (object) [
                "code"    => 201,
                "message" => "pembelian berhasil ditambahkan"
            ];
        } catch (\Exception $e) {
            // Rollback the transaction in case of an exception
            $this->db->transRollback();
        
            // Handle exception
            return (object) [
                "code"    => 500,
                "message" => "Terjadi kesalahan pada server: " . $e->getMessage()
            ];
        }

    }

    public function retur_suplier($mdata,$detail)
    {
        try {
            // Start Transaction
            $this->db->transBegin();
        
            // Table initialization
            $retur = $this->db->table("retur_beli");
            $beli_detail = $this->db->table("retur_beli_detail");
            
            // Insert into 'pembelian'
            if (!$retur->insert($mdata)) {
                // Rollback if 'pembelian' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => $this->db->error()//"Gagal menyimpan data pembelian"
                ];
            }
            
            $id = $this->db->insertID();
            $detbrg=array();
            foreach ($detail as $brg){
                $temp["id"]         = $id;
                $temp["barcode"]    = $brg->barcode;
                $temp["jumlah"]     = $brg->jml;
                array_push($detbrg,$temp);
            }
        
            // InsertBatch into 'pembelian_detail'
            if (!$beli_detail->insertBatch($detbrg)) {
                // Rollback if 'pembelian_detail' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan detail pembelian"
                ];
            }
        
            // Commit the transaction
            $this->db->transCommit();
        
            return (object) [
                "code"    => 201,
                "message" => "pembelian berhasil ditambahkan"
            ];
        } catch (\Exception $e) {
            // Rollback the transaction in case of an exception
            $this->db->transRollback();
        
            // Handle exception
            return (object) [
                "code"    => 500,
                "message" => "Terjadi kesalahan pada server: " . $e->getMessage()
            ];
        }

    }
    
    public function getretur_suplier($awal,$akhir){
        $sql="SELECT 
            	a.id,
                b.namasuplier, 
                a.tanggal
            FROM 
                retur_beli a
            INNER JOIN 
                suplier b ON a.id_suplier = b.id
            INNER JOIN 
                retur_beli_detail c ON a.id = c.id";
        if ($awal==$akhir){
            $sql.=" WHERE date(a.tanggal)='$awal'";
        }else{
            $sql.=" WHERE date(a.tanggal) BETWEEN '$awal' AND '$akhir'";
        }
        $sql.=" GROUP BY b.namasuplier, a.tanggal;";
        return $this->db->query($sql)->getResult();
    }

    public function getretur_pelanggan($awal,$akhir){
        $sql="SELECT 
            	a.id,
                b.namapelanggan, 
                a.tanggal
            FROM 
                retur_jual a
            INNER JOIN 
                pelanggan b ON a.pelanggan_id = b.id
            INNER JOIN 
                retur_jual_detail c ON a.id = c.id";
        if ($awal==$akhir){
            $sql.=" WHERE date(a.tanggal)='$awal'";
        }else{
            $sql.=" WHERE date(a.tanggal) BETWEEN '$awal' AND '$akhir'";
        }
        $sql.=" GROUP BY b.namapelanggan, a.tanggal;";
        return $this->db->query($sql)->getResult();
    }
    
    public function barang_retursup($id){
        $sql="SELECT br.namabarang, sum(rbd.jumlah) as jumlah
                FROM
                    barang br 
                INNER JOIN 
                    barang_detail bd ON br.id=bd.barang_id
                INNER JOIN
                    retur_beli_detail rbd ON rbd.barcode=bd.barcode
                WHERE rbd.id=?
                GROUP BY br.id, br.namabarang
        ";
        return $this->db->query($sql,$id)->getResult();
    }

    public function barang_returpel($id){
        $sql="SELECT br.namabarang, sum(rjd.jumlah) as jumlah
                FROM
                    barang br 
                INNER JOIN 
                    barang_detail bd ON br.id=bd.barang_id
                INNER JOIN
                    retur_jual_detail rjd ON rjd.barcode=bd.barcode
                WHERE rjd.id=?
                GROUP BY br.id, br.namabarang
        ";
        return $this->db->query($sql,$id)->getResult();
    }

    public function get_laporan_retursup($bulan,$tahun, $suplier){
        $sql="SELECT 
            	a.id,
                b.namasuplier, 
                a.tanggal
            FROM 
                retur_beli a
            INNER JOIN 
                suplier b ON a.id_suplier = b.id
            INNER JOIN 
                retur_beli_detail c ON a.id = c.id";
        $sql.=" WHERE
                YEAR(a.tanggal) = $tahun AND MONTH(a.tanggal) = $bulan"
                . (!empty($suplier) ? " AND b.id = $suplier " : "")
                ." GROUP BY b.namasuplier, a.tanggal";
        return $this->db->query($sql)->getResult();
    }

    public function get_laporan_returpel($bulan,$tahun, $pelanggan){
        $sql="SELECT 
            	a.id,
                b.namapelanggan, 
                a.tanggal
            FROM 
                retur_jual a
            INNER JOIN 
                pelanggan b ON a.pelanggan_id = b.id
            INNER JOIN 
                retur_jual_detail c ON a.id = c.id";
        $sql.=" WHERE
                YEAR(a.tanggal) = $tahun AND MONTH(a.tanggal) = $bulan"
                . (!empty($pelanggan) ? " AND b.id = $pelanggan " : "")
                ." GROUP BY b.namapelanggan, a.tanggal";

        return $this->db->query($sql)->getResult();
    }

}

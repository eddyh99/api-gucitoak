<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_pembelian extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }


    public function add($mdata,$detail)
    {
        try {
            // Start Transaction
            $this->db->transBegin();
        
            // Table initialization
            $pembelian = $this->db->table("pembelian");
            $jual_detail = $this->db->table("pembelian_detail");
            
            // Insert into 'pembelian'
            if (!$pembelian->insert($mdata)) {
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
                $temp["harga"]      = $brg->harga;
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
    
    public function get_pembelian($awal,$akhir){
        $sql="SELECT 
                a.id,
            	a.nonota,
                b.namasuplier, 
                a.tanggal,
                SUM(c.jumlah * c.harga) AS amount,
                a.method
            FROM 
                pembelian a
            INNER JOIN 
                suplier b ON a.id_suplier = b.id
            INNER JOIN 
                pembelian_detail c ON a.id = c.id";
        if ($awal==$akhir){
            $sql.=" WHERE date(a.tanggal)='$awal'";
        }else{
            $sql.=" WHERE date(a.tanggal) BETWEEN '$awal' AND '$akhir'";
        }
        $sql.=" GROUP BY b.namasuplier, a.tanggal;";
        return $this->db->query($sql)->getResult();
    }
    
    public function getbarang_beli($id){
        $sql="SELECT sum(a.jumlah) as jumlah, a.harga, c.namabarang
                FROM 
                    pembelian_detail a
                INNER JOIN
                    barang_detail b ON a.barcode = b.barcode
                INNER JOIN
                    barang c ON b.barang_id = c.id
                WHERE 
                    a.id = ?
                GROUP BY 
                    c.id, c.namabarang;
            ";
        return $this->db->query($sql,$id)->getResult();
    }

}

<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_pembayaran extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getNota_pelanggan($nota)
    {
        $sql = "SELECT 
                    a.nonota,
                    p.tanggal,
                    pel.namapelanggan,
                    p.method,
                    status.totalcicilan,
                    status.notajual as notajual,
                    status.isLunas as isLunas
                FROM 
                    pembayaran a
                INNER JOIN
                    penjualan p ON p.nonota = a.nonota
                INNER JOIN
                    pelanggan pel ON pel.id = p.pelanggan_id
                LEFT JOIN (
                    SELECT
                    p2.cicilan as totalcicilan,
                    SUM(pd.jumlah * (
                    CASE 
                        WHEN pel.harga = 1 THEN h.harga1
                        WHEN pel.harga = 2 THEN h.harga2
                        WHEN pel.harga = 3 THEN h.harga3
                        ELSE 0
                    END
                    )) AS notajual,
                    pd.nonota,
                    CASE 
                        WHEN COALESCE(p2.cicilan, 0) < SUM(
                            pd.jumlah * (
                                CASE 
                                    WHEN pel.harga = 1 THEN h.harga1
                                    WHEN pel.harga = 2 THEN h.harga2
                                    WHEN pel.harga = 3 THEN h.harga3
                                    ELSE 0
                                END
                            )
                        ) THEN FALSE
                        ELSE TRUE
                    END AS isLunas
                FROM
                    penjualan_detail pd
                INNER JOIN barang_detail bd ON bd.barcode = pd.barcode
                INNER JOIN penjualan p ON p.nonota = pd.nonota
                INNER JOIN (
                    SELECT
                        h.id_barang, 
                        h.harga1, 
                        h.harga2, 
                        h.harga3, 
                        h.tanggal
                    FROM 
                        harga h
                ) h ON h.id_barang = bd.barang_id 
                AND h.tanggal = (
                    SELECT 
                        MAX(h2.tanggal) 
                    FROM 
                        harga h2 
                    WHERE 
                        h2.id_barang = h.id_barang 
                        AND h2.tanggal <= p.tanggal
                )
                INNER JOIN pelanggan pel ON pel.id = p.pelanggan_id
                LEFT JOIN (
                    SELECT
                        p.nonota,
                        SUM(pd.amount) AS cicilan
                    FROM
                        pembayaran p
                    INNER JOIN pembayaran_detail pd ON pd.bayar_id = p.id
                    GROUP BY 
                        p.nonota
                ) p2 ON p2.nonota = p.nonota
                WHERE 
                    pd.nonota = $nota

                ) status ON status.nonota = a.nonota
                WHERE
                    a.nonota = $nota
                GROUP BY a.nonota";

        return $this->db->query($sql)->getRow();
    }

    public function getCicilan_pelanggan($nota) {
        $sql = "SELECT
                    p.nonota,
                    pd.tanggal,
                    pd.amount
                FROM
                    pembayaran p
                    INNER JOIN pembayaran_detail pd ON pd.bayar_id = p.id
                WHERE
                    p.nonota = ?";

        return $this->db->query($sql,$nota)->getResult();
    }

    public function addCicilan_pelanggan($mdata) {
        try {
            // Start Transaction
            $this->db->transBegin();
        
            // Table initialization
            $pembayaran = $this->db->table("pembayaran");
            $pembayaran_detail = $this->db->table("pembayaran_detail");
        
            // Insert into 'penjualan'
            if (!$pembayaran->insert([
                'nonota' => $mdata['nonota']])) {
                // Rollback if 'penjualan' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan cicilan."
                ];
            }
            
            $mdata['bayar_id'] = $this->db->insertID();
            unset($mdata['nonota']);
            // InsertBatch into 'penjualan_detail'
            if (!$pembayaran_detail->insert($mdata)) {
                // Rollback if 'penjualan_detail' insertion fails
                $error = $this->db->error(); 
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan detail cicilan"
                ];
            }
        
            // Commit the transaction
            $this->db->transCommit();
        
            return (object) [
                "code"    => 201,
                "message" => "Cicilan berhasil ditambahkan"
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
}
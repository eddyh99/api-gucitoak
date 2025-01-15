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
                    COALESCE(status.totalcicilan, 0) as totalcicilan,
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
                    pd.amount,
                    pd.keterangan
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

    public function getNota_suplier($nota) {
        $sql = "SELECT
                    c.id,
                    c.nonota,
                    c.tanggal,
                    c.method,
                    e.namasuplier,
                    b.cicilan as totalcicilan,
                    d.notabeli,
                    CASE
                    WHEN b.cicilan < d.notabeli THEN FALSE
                    ELSE TRUE
                    END AS isLunas
                FROM
                    cicilansuplier a
                    INNER JOIN (
                    SELECT
                        csd.cicilan_id,
                        SUM(amount) as cicilan
                    FROM
                        cicilansuplier_detail csd
                        INNER JOIN cicilansuplier cs ON cs.id = csd.cicilan_id
                    GROUP BY
                        cs.id_nota
                    ) b ON b.cicilan_id = a.id
                    INNER JOIN pembelian c ON c.id = a.id_nota
                    INNER JOIN (
                    SELECT
                        id,
                        jumlah * harga as notabeli
                    FROM
                        pembelian_detail
                    ) d on d.id = c.id
                    INNER JOIN suplier e ON e.id = c.id_suplier
                WHERE
                    c.nonota = ?";

        return $this->db->query($sql, $nota)->getRow();
    }

    public function getCicilan_suplier($nota) {
        $sql = "SELECT
                    a.nonota,
                    c.tanggal,
                    c.amount,
                    c.keterangan
                FROM
                    pembelian a
                    INNER JOIN cicilansuplier b ON b.id_nota = a.id
                    INNER JOIN cicilansuplier_detail c ON c.cicilan_id = b.id

                WHERE a.nonota = ? ";
        return $this->db->query($sql,$nota)->getResult();
    }

    public function addCicilan_suplier($mdata) {
        try {
            // Start Transaction
            $this->db->transBegin();
        
            // Table initialization
            $cicilan = $this->db->table("cicilansuplier");
            $cicilan_detail = $this->db->table("cicilansuplier_detail");
        
            // Insert into 'penjualan'
            if (!$cicilan->insert([
                'id_nota' => $mdata['id_nota']])) {
                // Rollback if 'penjualan' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan cicilan."
                ];
            }
            
            $mdata['cicilan_id'] = $this->db->insertID();
            unset($mdata['id_nota']);
            // InsertBatch into 'penjualan_detail'
            if (!$cicilan_detail->insert($mdata)) {
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
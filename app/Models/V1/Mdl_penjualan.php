<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_penjualan extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_penjualan($awal,$akhir){
        $sql="SELECT 
                a.nonota,
                b.namapelanggan, 
                a.tanggal,
                e.namasales,
                SUM(c.jumlah * (
                    CASE 
                        WHEN b.harga = 1 THEN f.harga1
                        WHEN b.harga = 2 THEN f.harga2
                        WHEN b.harga = 3 THEN f.harga3
                        ELSE 0
                    END
                )) AS amount,
                a.method
            FROM 
                penjualan a
            INNER JOIN 
                pelanggan b ON a.pelanggan_id = b.id
            INNER JOIN 
                penjualan_detail c ON a.nonota = c.nonota
            INNER JOIN 
                barang_detail d ON c.barcode = d.barcode
            INNER JOIN 
                sales e ON a.sales_id = e.id
            LEFT JOIN (
                SELECT 
                    hr.id_barang, 
                    hr.harga1, 
                    hr.harga2, 
                    hr.harga3, 
                    hr.tanggal
                FROM 
                    harga hr
            ) f ON f.id_barang = d.barang_id AND f.tanggal = (
                SELECT MAX(hr2.tanggal)
                FROM harga hr2
                WHERE hr2.id_barang = f.id_barang AND hr2.tanggal <= a.tanggal
            )";
            if ($awal==$akhir){
                $sql.=" WHERE date(a.tanggal)='$awal'";
            }else{
                $sql.=" WHERE date(a.tanggal) BETWEEN '$awal' AND '$akhir'";
            }
            $sql.=" GROUP BY b.namapelanggan, a.tanggal;";
            return $this->db->query($sql)->getResult();
                
    }
    
    public function getNota(){
        $sql="SELECT LPAD(COALESCE(MAX(nonota) + 1, 1), 6, '0') AS nonota
                FROM penjualan";
        return $this->db->query($sql)->getRow()->nonota;
    }
    
    public function add($mdata,$detail)
    {
        try {
            // Start Transaction
            $this->db->transBegin();
        
            // Table initialization
            $penjualan = $this->db->table("penjualan");
            $jual_detail = $this->db->table("penjualan_detail");
        
            // Insert into 'penjualan'
            if (!$penjualan->insert($mdata)) {
                // Rollback if 'penjualan' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan data penjualan"
                ];
            }
        
            // InsertBatch into 'penjualan_detail'
            if (!$jual_detail->insertBatch($detail)) {
                // Rollback if 'penjualan_detail' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan detail penjualan"
                ];
            }
        
            // Commit the transaction
            $this->db->transCommit();
        
            return (object) [
                "code"    => 201,
                "message" => "Penjualan berhasil ditambahkan"
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

    public function getbarang_jual($nota){
        $sql="SELECT 
                    SUM(pd.jumlah) AS jumlah, 
                    br.namabarang, 
                    CASE 
                        WHEN pl.harga = 1 THEN hr.harga1
                        WHEN pl.harga = 2 THEN hr.harga2
                        WHEN pl.harga = 3 THEN hr.harga3
                        ELSE 0
                    END AS harga
                FROM 
                    penjualan pj
                INNER JOIN 
                    penjualan_detail pd ON pj.nonota = pd.nonota
                INNER JOIN 
                    barang_detail bd ON pd.barcode = bd.barcode
                INNER JOIN 
                    barang br ON bd.barang_id = br.id
                INNER JOIN 
                    pelanggan pl ON pl.id = pj.pelanggan_id
                INNER JOIN (
                    SELECT 
                        hr.id_barang, 
                        hr.harga1, 
                        hr.harga2, 
                        hr.harga3, 
                        hr.tanggal
                    FROM 
                        harga hr
                    INNER JOIN (
                        SELECT 
                            id_barang, MAX(tanggal) AS max_tanggal
                        FROM 
                            harga
                        GROUP BY 
                            id_barang
                    ) latest_harga ON hr.id_barang = latest_harga.id_barang AND hr.tanggal = latest_harga.max_tanggal
                ) hr ON hr.id_barang = bd.barang_id AND hr.tanggal <= pj.tanggal
                WHERE 
                    pj.nonota = ?
                GROUP BY 
                    br.id, br.namabarang, pl.harga;
            ";
        return $this->db->query($sql,$nota)->getResult();
    }

    public function get_laporan_penjualan($bulan, $tahun){
        $sql="SELECT 
            	a.nonota,
                b.namapelanggan, 
                a.tanggal,
                e.namasales,
                SUM(c.jumlah * (
                    CASE 
                        WHEN b.harga = 1 THEN f.harga1
                        WHEN b.harga = 2 THEN f.harga2
                        WHEN b.harga = 3 THEN f.harga3
                        ELSE 0
                    END
                )) AS amount
            FROM 
                penjualan a
            INNER JOIN 
                pelanggan b ON a.pelanggan_id = b.id
            INNER JOIN 
                penjualan_detail c ON a.nonota = c.nonota
            INNER JOIN 
                barang_detail d ON c.barcode = d.barcode
            INNER JOIN sales e ON a.sales_id = e.id
            LEFT JOIN (
                SELECT 
                    hr.id_barang, 
                    hr.harga1, 
                    hr.harga2, 
                    hr.harga3, 
                    hr.tanggal
                FROM 
                    harga hr
            ) f ON f.id_barang = d.barang_id AND f.tanggal = (
                SELECT MAX(hr2.tanggal)
                FROM harga hr2
                WHERE hr2.id_barang = f.id_barang AND hr2.tanggal <= a.tanggal)
            WHERE
                YEAR(a.tanggal) = $tahun AND MONTH(a.tanggal) = $bulan
            GROUP BY
                b.namapelanggan, a.tanggal";
            return $this->db->query($sql)->getResult();
                
    }

    public function getNota_belumLunas($awal, $akhir, $nota)
    {
        // get nota pelanggan belum lunas
        $sql_pel = "SELECT 
                    a.nonota,
                    b.namapelanggan, 
                    a.tanggal,
                    DATE_ADD(a.tanggal, INTERVAL a.waktu DAY) AS tempo,
                    COALESCE(e.cicilan, 0) as cicilan,
                    SUM(
                        c.jumlah * (
                            CASE 
                                WHEN b.harga = 1 THEN f.harga1
                                WHEN b.harga = 2 THEN f.harga2
                                WHEN b.harga = 3 THEN f.harga3
                                ELSE 0
                            END
                        )
                    ) as notajual
                FROM 
                    penjualan a
                INNER JOIN 
                    pelanggan b 
                    ON a.pelanggan_id = b.id
                INNER JOIN 
                    penjualan_detail c 
                    ON a.nonota = c.nonota
                INNER JOIN 
                    barang_detail d 
                    ON c.barcode = d.barcode
                -- LEFT JOIN
                --     pembayaran e ON e.nonota = a.nonota
                LEFT JOIN (
                    SELECT
                        p.nonota,
                        SUM(pd.amount) as cicilan
                        FROM pembayaran p
                        INNER JOIN pembayaran_detail pd ON pd.bayar_id = p.id
                        GROUP BY p.nonota
                ) e ON e.nonota = a.nonota
                LEFT JOIN (
                    SELECT 
                        hr.id_barang, 
                        hr.harga1, 
                        hr.harga2, 
                        hr.harga3, 
                        hr.tanggal
                    FROM 
                        harga hr
                ) f 
                    ON f.id_barang = d.barang_id 
                    AND f.tanggal = (
                        SELECT 
                            MAX(hr2.tanggal)
                        FROM 
                            harga hr2
                        WHERE 
                            hr2.id_barang = f.id_barang 
                            AND hr2.tanggal <= a.tanggal
                    )";

        if (!empty($awal) && !empty($akhir)) {
            $sql_pel .= ($awal == $akhir) 
                ? " WHERE DATE(a.tanggal) = '$awal'" 
                : " WHERE DATE(a.tanggal) BETWEEN '$awal' AND '$akhir'";
        }

        $sql_pel .= " GROUP BY
                    a.nonota, 
                    a.tanggal
                HAVING
                    notajual > cicilan
                    ";

        // get nota suplier belum lunas
        $sql_sup = "SELECT
                        a.nonota,
                        b.namasuplier,
                        a.tanggal,
                        DATE_ADD(a.tanggal, INTERVAL a.waktu DAY) AS tempo,
                        COALESCE(c.cicilan, 0) as cicilan,
                        COALESCE(d.notabeli, 0) as notabeli
                    FROM
                        pembelian a
                        INNER JOIN suplier b ON b.id = a.id_suplier
                        LEFT JOIN (
                        SELECT
                            id_nota,
                            SUM(csd.amount) as cicilan
                        FROM
                            cicilansuplier cs
                            INNER JOIN cicilansuplier_detail csd ON csd.cicilan_id = cs.id
                        GROUP BY
                            cs.id_nota
                        ) c ON c.id_nota = a.id
                        LEFT JOIN (
                        SELECT
                            id,
                            harga * jumlah as notabeli
                        FROM
                            pembelian_detail
                        ) d ON d.id = a.id";

        if (!empty($awal) && !empty($akhir)) {
            $sql_pel .= ($awal == $akhir) 
                ? " WHERE DATE(a.tanggal) = '$awal'" 
                : " WHERE DATE(a.tanggal) BETWEEN '$awal' AND '$akhir'";
        }
        $sql_sup.=" HAVING cicilan < notabeli";

        $sql = $nota === 'suplier' ? $sql_sup : $sql_pel;

        return $this->db->query($sql)->getResult();
    }
}

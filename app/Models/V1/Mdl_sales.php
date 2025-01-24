<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_sales extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_sales()
    {
        $sql = "SELECT * FROM sales WHERE is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_salesbyid($id)
    {
        $sql = "SELECT * FROM sales WHERE id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }

    public function add($mdata)
    {
        try {
            $sales = $this->db->table("sales");

            // Insert data into 'sales' table
            if (!$sales->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan sales"
                );
            }
        } catch (DatabaseException $e) {
            // For other database-related errors, return generic server error
            return (object) array(
                "code"      => 500,
                "message"   => "Terjadi kesalahan pada server"
            );
        } catch (\Exception $e) {
            // Handle any other general exceptions
            return (object) array(
                "code"      => 500,
                "message"   => "Terjadi kesalahan pada server"
            );
        }

        return (object) array(
            "code"      => 201,
            "message"   => "sales berhasil ditambahkan"
        );
    }

    public function ubah($mdata, $id)
    {
        try {
            $sales = $this->db->table("sales");
            $sales->where("id", $id);
    
            // Attempt to update the record
            if (!$sales->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah sales"
                );
            }
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "sales sudah ada, tidak boleh duplikat"
                );
            }
    
            // Handle any other database-related errors
            return (object) array(
                "code"      => 500,
                "message"   => "Terjadi kesalahan pada server"
            );
        } catch (\Exception $e) {
            // Handle any other general exceptions
            return (object) array(
                "code"      => 500,
                "message"   => "Terjadi kesalahan pada server"
            );
        }
    
        return (object) array(
            "code"      => 200,
            "message"   => "sales berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $sales = $this->db->table("sales");
        $sales->where("id", $id);
        $sales->set("is_delete", "yes");
        $sales->set("update_at", date("Y-m-d H:i:s"));

        if (!$sales->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus sales"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "sales berhasil dihapus"
        );
    }
    
    public function sales_produk($data){
        $barang = $this->db->table("assignsales");
        if (!$barang->insertBatch($data)) {
            return (object) array(
                "code"      => 400,
                "message"   => "Produk gagal ditambahkan ke sales"
            );
        }
        
        return (object) array(
            "code"      => 200,
            "message"   => "Produk berhasil ditambahkan ke sales"
        );
    }
    
    public function get_salesbarang(){
        $sql="SELECT namasales, namabarang FROM assignsales a INNER JOIN sales b ON a.id_sales=b.id INNER JOIN barang c ON a.id_barang=c.id WHERE b.is_delete='no' AND c.is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_sales_report($id) {
        $sql = "SELECT
                    a.namasales,
                    COALESCE(p.komisi, 0) as komisi,
                    a.gajipokok,
                    p.detailnota as detailnota
                FROM
                    sales a
                    LEFT JOIN (
                    SELECT
                        GROUP_CONCAT(a.nonota) AS detailnota,
                        a.sales_id,
                        a.tanggal,
                        SUM(
                        c.jumlah * (
                            CASE
                            WHEN b.harga = 1 THEN f.harga1
                            WHEN b.harga = 2 THEN f.harga2
                            WHEN b.harga = 3 THEN f.harga3
                            ELSE 0
                            END
                        )
                        ) * e.komisi AS komisi
                    FROM
                        penjualan a
                        INNER JOIN pelanggan b ON a.pelanggan_id = b.id
                        INNER JOIN penjualan_detail c ON a.nonota = c.nonota
                        INNER JOIN barang_detail d ON c.barcode = d.barcode
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
                        ) f ON f.id_barang = d.barang_id
                        AND f.tanggal = (
                        SELECT
                            MAX(hr2.tanggal)
                        FROM
                            harga hr2
                        WHERE
                            hr2.id_barang = f.id_barang
                            AND hr2.tanggal <= a.tanggal
                        )
                        WHERE STR_TO_DATE(a.tanggal, '%Y-%m-%d') BETWEEN DATE_FORMAT(
                            DATE_SUB(CURDATE(), INTERVAL 2 MONTH),
                            '%Y-%m-01')
                        AND LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
                    GROUP BY
                        b.namapelanggan,
                        e.id
                    ) p ON p.sales_id = a.id
                WHERE
                    a.is_delete = 'no'
                    AND a.id = ?";

        return $this->db->query($sql, $id)->getRow();
    }

    public function getby_id($username) {
        $sql = "SELECT * FROM sales WHERE username=?";
	    $query = $this->db->query($sql, $username)->getRow();

        if (!$query) {
	        $error=[
	            "code"       => "400",
	            "message"    => "Username not found"
	        ];
            return (object) $error;
        }

        $response=[
            "code"       => "200",
            "message"    => $query
        ];
        return (object) $response;
    }
}

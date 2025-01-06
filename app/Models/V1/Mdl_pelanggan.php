<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_pelanggan extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_pelanggan()
    {
        $sql = "SELECT * FROM pelanggan WHERE is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_pelangganbyid($id)
    {
        $sql = "SELECT * FROM pelanggan WHERE id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }

    public function add($mdata)
    {
        try {
            $pelanggan = $this->db->table("pelanggan");

            // Insert data into 'pelanggan' table
            if (!$pelanggan->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan pelanggan"
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
            "message"   => "pelanggan berhasil ditambahkan"
        );
    }

    public function ubah($mdata, $id)
    {
        try {
            $pelanggan = $this->db->table("pelanggan");
            $pelanggan->where("id", $id);
    
            // Attempt to update the record
            if (!$pelanggan->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah pelanggan"
                );
            }
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "pelanggan sudah ada, tidak boleh duplikat"
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
            "message"   => "pelanggan berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $pelanggan = $this->db->table("pelanggan");
        $pelanggan->where("id", $id);
        $pelanggan->set("is_delete", "yes");
        $pelanggan->set("update_at", date("Y-m-d H:i:s"));

        if (!$pelanggan->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus pelanggan"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "pelanggan berhasil dihapus"
        );
    }
    
    public function detail_pelanggan(){
        $sql="SELECT 
                pl.id AS pelanggan_id,
                pl.namapelanggan,
                pl.plafon,
                pl.maxnota,
                pl.harga,
                COUNT(p.nonota) AS total_nota_count,
                COALESCE(
                    SUM(
                        CASE 
                            WHEN pl.harga = 'Harga 1' THEN h.harga1
                            WHEN pl.harga = 'Harga 2' THEN h.harga2
                            WHEN pl.harga = 'Harga 3' THEN h.harga3
                            ELSE 0 
                        END * pd.jumlah
                    ), 0
                ) - COALESCE(
                    SUM(pd_bayar.amount), 
                    0
                ) AS total_nota_value
            FROM 
                pelanggan pl
            LEFT JOIN 
                penjualan p ON pl.id = p.pelanggan_id
            LEFT JOIN 
                penjualan_detail pd ON p.nonota = pd.nonota
            LEFT JOIN 
                barang_detail bd ON pd.barcode = bd.barcode
            LEFT JOIN 
                barang b ON bd.barang_id = b.id
            LEFT JOIN 
                harga h ON h.id_barang = b.id 
                AND h.tanggal = (
                    SELECT MAX(h2.tanggal)
                    FROM harga h2
                    WHERE h2.id_barang = h.id_barang AND h2.tanggal <= p.tanggal
                )
            LEFT JOIN 
                pembayaran bayar ON bayar.nonota = p.nonota 
            LEFT JOIN 
                pembayaran_detail pd_bayar ON pd_bayar.bayar_id = bayar.id
            WHERE 
                pl.is_delete = 'no'
                AND (b.is_delete = 'no' OR b.is_delete IS NULL) -- Exclude deleted items
            GROUP BY 
                pl.id, pl.namapelanggan, pl.plafon, pl.maxnota;
";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }
}

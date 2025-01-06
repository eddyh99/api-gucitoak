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
}

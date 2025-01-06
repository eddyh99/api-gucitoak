<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_kategori extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_kategori()
    {
        $sql = "SELECT * FROM kategori WHERE is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_kategoribyid($id)
    {
        $sql = "SELECT * FROM kategori WHERE id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }

    public function add($mdata)
    {
        try {
            $kategori = $this->db->table("kategori");

            // Insert data into 'kategori' table
            if (!$kategori->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan kategori"
                );
            }
        } catch (DatabaseException $e) {
            // Check for 'Duplicate entry' error using MySQL error code 1062
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "Kategori sudah ada, tidak boleh duplikat"
                );
            }

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
            "message"   => "Kategori berhasil ditambahkan"
        );
    }

    public function ubah($mdata, $id)
    {
        try {
            $kategori = $this->db->table("kategori");
            $kategori->where("id", $id);
    
            // Attempt to update the record
            if (!$kategori->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah kategori"
                );
            }
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "Kategori sudah ada, tidak boleh duplikat"
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
            "message"   => "Kategori berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $kategori = $this->db->table("kategori");
        $kategori->where("id", $id);
        $kategori->set("is_delete", "yes");
        $kategori->set("update_at", date("Y-m-d H:i:s"));

        if (!$kategori->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus kategori"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "Kategori berhasil dihapus"
        );
    }
}

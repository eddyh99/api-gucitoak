<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_satuan extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_satuan()
    {
        $sql = "SELECT * FROM satuan WHERE is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_satuanbyid($id)
    {
        $sql = "SELECT * FROM satuan WHERE id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }

    public function add($mdata)
    {
        try {
            $satuan = $this->db->table("satuan");

            // Insert data into 'satuan' table
            if (!$satuan->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan satuan"
                );
            }
        } catch (DatabaseException $e) {
            // Check for 'Duplicate entry' error using MySQL error code 1062
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "satuan sudah ada, tidak boleh duplikat"
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
            "message"   => "satuan berhasil ditambahkan"
        );
    }

    public function ubah($mdata, $id)
    {
        try {
            $satuan = $this->db->table("satuan");
            $satuan->where("id", $id);
    
            // Attempt to update the record
            if (!$satuan->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah satuan"
                );
            }
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "satuan sudah ada, tidak boleh duplikat"
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
            "message"   => "satuan berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $satuan = $this->db->table("satuan");
        $satuan->where("id", $id);
        $satuan->set("is_delete", "yes");
        $satuan->set("update_at", date("Y-m-d H:i:s"));

        if (!$satuan->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus satuan"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "satuan berhasil dihapus"
        );
    }
}

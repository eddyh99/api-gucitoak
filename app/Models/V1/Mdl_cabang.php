<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_cabang extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_cabang()
    {
        $sql = "SELECT * FROM cabang WHERE is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_cabangbyid($id)
    {
        $sql = "SELECT * FROM cabang WHERE id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }

    public function add($mdata)
    {
        try {
            $cabang = $this->db->table("cabang");

            // Insert data into 'cabang' table
            if (!$cabang->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan cabang"
                );
            }
        } catch (DatabaseException $e) {
            // Check for 'Duplicate entry' error using MySQL error code 1062
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "cabang sudah ada, tidak boleh duplikat"
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
            "message"   => "cabang berhasil ditambahkan"
        );
    }

    public function ubah($mdata, $id)
    {
        try {
            $cabang = $this->db->table("cabang");
            $cabang->where("id", $id);
    
            // Attempt to update the record
            if (!$cabang->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah cabang"
                );
            }
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "cabang sudah ada, tidak boleh duplikat"
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
            "message"   => "cabang berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $cabang = $this->db->table("cabang");
        $cabang->where("id", $id);
        $cabang->set("is_delete", "yes");
        $cabang->set("update_at", date("Y-m-d H:i:s"));

        if (!$cabang->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus cabang"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "cabang berhasil dihapus"
        );
    }
}

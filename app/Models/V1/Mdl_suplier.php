<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_suplier extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_suplier()
    {
        $sql = "SELECT * FROM suplier WHERE is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_suplierbyid($id)
    {
        $sql = "SELECT * FROM suplier WHERE id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }

    public function add($mdata)
    {
        try {
            $suplier = $this->db->table("suplier");

            // Insert data into 'suplier' table
            if (!$suplier->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan suplier"
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
            "message"   => "suplier berhasil ditambahkan"
        );
    }

    public function ubah($mdata, $id)
    {
        try {
            $suplier = $this->db->table("suplier");
            $suplier->where("id", $id);
    
            // Attempt to update the record
            if (!$suplier->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah suplier"
                );
            }
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "suplier sudah ada, tidak boleh duplikat"
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
            "message"   => "suplier berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $suplier = $this->db->table("suplier");
        $suplier->where("id", $id);
        $suplier->set("is_delete", "yes");
        $suplier->set("update_at", date("Y-m-d H:i:s"));

        if (!$suplier->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus suplier"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "suplier berhasil dihapus"
        );
    }
}

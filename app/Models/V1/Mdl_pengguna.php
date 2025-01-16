<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_pengguna extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_pengguna()
    {
        $sql = "SELECT * FROM pengguna WHERE status='active'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }

    public function get_penggunabyid($id)
    {
        $sql = "SELECT * FROM pengguna WHERE id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }

    public function add($mdata)
    {
        try {
            $pengguna = $this->db->table("pengguna");

            // Insert data into 'pengguna' table
            if (!$pengguna->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan pengguna"
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
            "message"   => "pengguna berhasil ditambahkan"
        );
    }

    public function ubah($mdata, $id)
    {
        try {
            $pengguna = $this->db->table("pengguna");
            $pengguna->where("id", $id);
    
            // Attempt to update the record
            if (!$pengguna->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah pengguna"
                );
            }
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "pengguna sudah ada, tidak boleh duplikat"
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
            "message"   => "pengguna berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $pengguna = $this->db->table("pengguna");
        $pengguna->where("id", $id);
        $pengguna->set("status", "disabled");
        $pengguna->set("update_at", date("Y-m-d H:i:s"));

        if (!$pengguna->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus pengguna"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "pengguna berhasil dihapus"
        );
    }

    public function giveAkses($mdata) {
        try {
            $akses = $this->db->table("user_role");

            // Insert akses into 'user_role' table
            if (!$akses->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan"
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
            "message"   => "Akses pengguna berhasil ditambahkan."
        );
    }
}

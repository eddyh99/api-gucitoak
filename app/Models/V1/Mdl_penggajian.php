<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_penggajian extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function inputGaji_suplier($mdata) {
        try {
            // Table initialization
            $penggajian = $this->db->table("gaji");
        
            // Insert into 'penggajian'
            if (!$penggajian->insert($mdata)) {
                // Rollback if 'penggajian' insertion fails
                $this->db->transRollback();
                return (object) [
                    "code"    => 500,
                    "message" => "Gagal menyimpan gaji."
                ];
            }
        
            return (object) [
                "code"    => 201,
                "message" => "Gaji berhasil diinput"
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
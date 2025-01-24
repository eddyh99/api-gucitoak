<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use Exception;

/*----------------------------------------------------------
    Modul Name  : Database Member
    Desc        : Menyimpan data member, proses member
    Sub fungsi  : 
        - getby_id          : Mendapatkan data user dari username
        - change_password   : Ubah password
------------------------------------------------------------*/


class Mdl_member extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    

    public function getby_id($username) {
        $sql = "SELECT * FROM pengguna WHERE username=? AND status='active'";
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

    public function getby_Role($role) {
        $sql = "SELECT * FROM pengguna WHERE role=? AND status='active'";
	    $query = $this->db->query($sql, $role)->getRow();

        if (!$query) {
	        $error=[
	            "code"       => "400",
	            "message"    => "Role not found"
	        ];
            return (object) $error;
        }

        $response=[
            "code"       => "200",
            "message"    => $query
        ];
        return (object) $response;
    }
    
    public function change_password($mdata, $where) {
        $member=$this->db->table("member");
        $member->where($where);
        $member->update($mdata);
        if ($this->db->affectedRows()==0){
	        $error=[
	            "code"       => "400",
	            "error"      => "08",
	            "message"    => "Failed to change password, please try again later"
	        ];
            return (object) $error;
        }
    }
    

}
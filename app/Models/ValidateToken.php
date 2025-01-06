<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ValidateToken extends Model
{

    protected $allowedFields = ['token'];
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    function checkAPIkey($token)
    {
        $sql="SELECT * FROM pengguna WHERE sha1(CONCAT(username,passwd))=?";
        $data=$this->db->query($sql,array($token))->getRow();
        if (!$data) {
            throw new Exception("Username Not found");
        }
        return $data;
    }
}
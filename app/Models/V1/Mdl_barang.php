<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Mdl_barang extends Model
{
    protected $server_tz = "Asia/Singapore";

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function get_barang()
    {
        $sql = "SELECT a.*,x.*, b.namakategori, c.namasatuan 
                FROM barang a INNER JOIN kategori b ON a.id_kategori=b.id 
                INNER JOIN satuan c ON a.id_satuan=c.id
                LEFT JOIN ( 
                    SELECT a.harga1, a.harga2, a.harga3, a.disc_fxd, a.disc_pct, a.id_barang 
                    FROM harga a INNER JOIN ( 
                        SELECT MAX(tanggal) as tanggal, id_barang FROM harga GROUP BY id_barang
                    ) x ON a.id_barang=x.id_barang AND a.tanggal=x.tanggal
                ) x ON a.id=x.id_barang WHERE a.is_delete='no'";
        $query = $this->db->query($sql)->getResult();
        return $query;
    }



    public function get_barangbyid($id)
    {
        $sql = "SELECT a.*,x.*,b.namakategori, c.namasatuan FROM barang a INNER JOIN kategori b ON a.id_kategori=b.id 
                INNER JOIN satuan c ON a.id_satuan=c.id  LEFT JOIN ( 
                    SELECT a.harga1, a.harga2, a.harga3, a.disc_fxd, a.disc_pct, a.id_barang 
                    FROM harga a INNER JOIN ( 
                        SELECT MAX(tanggal) as tanggal, id_barang FROM harga GROUP BY id_barang
                    ) x ON a.id_barang=x.id_barang AND a.tanggal=x.tanggal
                ) x ON a.id=x.id_barang WHERE a.id=?";
        $query = $this->db->query($sql, $id)->getRow();
        return $query;
    }
    


    public function add($mdata,$mharga)
    {
        try {
            $barang = $this->db->table("barang");
            $harga = $this->db->table("harga");

            // Insert data into 'barang' table
            if (!$barang->insert($mdata)) {
                // Handle case when insert fails (not due to exception)
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal menyimpan barang"
                );
            }
            
            $mharga["id_barang"]=$this->db->insertID();
            $harga->insert($mharga);
        } catch (DatabaseException $e) {
            // For other database-related errors, return generic server error
            return (object) array(
                "code"      => 500,
                "message"   =>"Terjadi kesalahan pada server"
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
            "message"   => "barang berhasil ditambahkan"
        );
    }

    public function ubah($mdata,$mharga,$id)
    {
        try {
            $barang = $this->db->table("barang");
            $harga = $this->db->table("harga");

            $barang->where("id", $id);
    
            // Attempt to update the record
            if (!$barang->update($mdata)) {
                return (object) array(
                    "code"      => 400,
                    "message"   => "Gagal mengubah barang"
                );
            }
            $harga->insert($mharga);
            
        } catch (DatabaseException $e) {
            // Check if the error is due to duplicate entry
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return (object) array(
                    "code"      => 409, // Conflict
                    "message"   => "barang sudah ada, tidak boleh duplikat"
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
            "message"   => "barang berhasil diubah"
        );
    }


    public function hapus($id)
    {
        $barang = $this->db->table("barang");
        $barang->where("id", $id);
        $barang->set("is_delete", "yes");
        $barang->set("update_at", date("Y-m-d H:i:s"));

        if (!$barang->update()) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menghapus barang"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "barang berhasil dihapus"
        );
    }
    
    public function get_hargabyid($id){
        $sql="SELECT * FROM harga WHERE id_barang=? ORDER BY tanggal DESC";
        $query = $this->db->query($sql,$id)->getResult();
        return $query;

    }
    
    public function addstok($mdata, $barang) {
        $barangTable = $this->db->table("barang_detail");
        $sesuaiTable = $this->db->table("penyesuaian");
        $existingBarcodes = [];
    
        // Collect all barcodes from $mdata
        foreach ($mdata as $data) {
            $existingBarcodes[] = $data['barcode'];
        }
    
        // Check if barcodes already exist in barang_detail
        $barangTable->whereIn('barcode', $existingBarcodes);
        $existingEntries = $barangTable->get()->getResult();
    
        if ($existingEntries) {
            // Barcodes already exist, proceed with inserting into penyesuaian
            if ($sesuaiTable->insertBatch($barang)) {
                return (object) array(
                    "code"    => 200,
                    "message" => "Sukses menambah atau memperbarui barang"
                );
            } else {
                return (object) array(
                    "code"    => 400,
                    "message" => "Gagal menambah atau memperbarui barang di penyesuaian"
                );
            }
        } else {
            // No existing barcodes found, insert into barang_detail first
            if ($barangTable->insertBatch($mdata)) {
                // After successfully inserting into barang_detail, insert into penyesuaian
                $sesuaiTable->insertBatch($barang);
                return (object) array(
                    "code"    => 200,
                    "message" => "Sukses menambah atau memperbarui barang"
                );
            } else {
                return (object) array(
                    "code"    => 400,
                    "message" => "Gagal menambah atau memperbarui barang di barang_detail"
                );
            }
        }
    }

    public function get_stok(){
            $sql='SELECT 
                    b.id AS kodebrg,
                    b.namabarang AS nama_barang,
                    k.namakategori AS kategori,
                    b.stokmin AS min,
                    (
                        COALESCE(SUM(DISTINCT pbd.total_pembelian), 0) + 
                        COALESCE(SUM(DISTINCT rj.total_retur_jual), 0) + 
                        COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0)
                    )
                    - (
                        COALESCE(SUM(DISTINCT rb.total_retur_beli), 0) + 
                        COALESCE(SUM(DISTINCT pjd.total_penjualan), 0) + 
                        COALESCE(SUM(DISTINCT dd.total_disposal), 0)
                    ) AS stok
                FROM barang b
                    INNER JOIN kategori k ON b.id_kategori = k.id
                    LEFT JOIN barang_detail bd ON b.id = bd.barang_id
                    
                    -- "In" part: Subquery for total pembelian
                    LEFT JOIN (
                        SELECT barcode, SUM(jumlah) AS total_pembelian
                        FROM pembelian_detail
                        GROUP BY barcode
                    ) pbd ON bd.barcode = pbd.barcode
                    
                    -- "In" part: Subquery for total retur_jual
                    LEFT JOIN (
                        SELECT barcode, SUM(jumlah) AS total_retur_jual
                        FROM retur_jual a
                        INNER JOIN retur_jual_detail b ON a.id = b.id
                        GROUP BY barcode
                    ) rj ON bd.barcode = rj.barcode
                    
                    -- "In" part: Subquery for approved penyesuaian (adjustment)
                    LEFT JOIN penyesuaian adj ON bd.barcode = adj.barcode AND adj.approved = 1
                    
                    -- "Out" part: Subquery for total retur_beli (return purchases)
                    LEFT JOIN (
                        SELECT DISTINCT barcode, SUM(jumlah) AS total_retur_beli
                        FROM retur_beli a 
                        INNER JOIN retur_beli_detail b ON a.id = b.id
                        WHERE a.status = "tukar"
                        GROUP BY barcode
                    ) rb ON bd.barcode = rb.barcode
                    
                    -- "Out" part: Subquery for total penjualan (sales)
                    LEFT JOIN (
                        SELECT DISTINCT barcode, SUM(jumlah) AS total_penjualan
                        FROM penjualan_detail
                        GROUP BY barcode
                    ) pjd ON bd.barcode = pjd.barcode
                    
                    -- "Out" part: Subquery for total disposal
                    LEFT JOIN (
                        SELECT DISTINCT barcode, SUM(jumlah) AS total_disposal
                        FROM disposal_detail
                        GROUP BY barcode
                    ) dd ON bd.barcode = dd.barcode
                    
                WHERE b.is_delete = "no"
                GROUP BY b.id;';
        $query=$this->db->query($sql);
        return $query->getResult();
    }
    
    public function detailbarcode($kodebarang){
        $sql='SELECT 
                bd.barcode,
                bd.expired AS exp_date,
                (
                    COALESCE(SUM(DISTINCT pbd.total_pembelian), 0) + 
                    COALESCE(SUM(DISTINCT rj.total_retur_jual), 0) + 
                    COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0)
                )
                - (
                    COALESCE(SUM(DISTINCT rb.total_retur_beli), 0) + 
                    COALESCE(SUM(DISTINCT pjd.total_penjualan), 0) + 
                    COALESCE(SUM(DISTINCT dd.total_disposal), 0)
                ) AS jumlah
            FROM barang_detail bd 
                
                -- "In" part: Subquery for total pembelian
                LEFT JOIN (
                    SELECT barcode, SUM(jumlah) AS total_pembelian
                    FROM pembelian_detail
                    GROUP BY barcode
                ) pbd ON bd.barcode = pbd.barcode
                
                -- "In" part: Subquery for total retur_jual
                LEFT JOIN (
                    SELECT barcode, SUM(jumlah) AS total_retur_jual
                    FROM retur_jual a
                    INNER JOIN retur_jual_detail b ON a.id = b.id
                    GROUP BY barcode
                ) rj ON bd.barcode = rj.barcode
                
                -- "In" part: Subquery for approved penyesuaian (adjustment)
                LEFT JOIN penyesuaian adj ON bd.barcode = adj.barcode AND adj.approved = 1
                
                -- "Out" part: Subquery for total retur_beli (return purchases)
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_retur_beli
                    FROM retur_beli a 
                    INNER JOIN retur_beli_detail b ON a.id = b.id
                    WHERE a.status = "tukar"
                    GROUP BY barcode
                ) rb ON bd.barcode = rb.barcode
                
                -- "Out" part: Subquery for total penjualan (sales)
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_penjualan
                    FROM penjualan_detail
                    GROUP BY barcode
                ) pjd ON bd.barcode = pjd.barcode
                
                -- "Out" part: Subquery for total disposal
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_disposal
                    FROM disposal_detail
                    GROUP BY barcode
                ) dd ON bd.barcode = dd.barcode
                WHERE bd.barang_id = 10
                GROUP BY bd.barcode, bd.expired
                HAVING jumlah > 0;';
        $query=$this->db->query($sql,$kodebarang);
        return $query->getResult();
    }
    
    public function stok_bybarcode($barcode){
        $sql='SELECT 
                b.id AS kodebrg,
                b.namabarang AS nama_barang,
                k.namakategori AS kategori,
                b.stokmin AS min,
                (
                    COALESCE(SUM(DISTINCT pbd.total_pembelian), 0) + 
                    COALESCE(SUM(DISTINCT rj.total_retur_jual), 0) + 
                    COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0)
                )
                - (
                    COALESCE(SUM(DISTINCT rb.total_retur_beli), 0) + 
                    COALESCE(SUM(DISTINCT pjd.total_penjualan), 0) + 
                    COALESCE(SUM(DISTINCT dd.total_disposal), 0)
                ) AS stok,
                h.harga1 AS harga1,
                h.harga2 AS harga2,
                h.harga3 AS harga3
            FROM barang b
            INNER JOIN kategori k ON b.id_kategori = k.id
            LEFT JOIN barang_detail bd ON b.id = bd.barang_id
            
            -- "In" part: Subquery for total pembelian
            LEFT JOIN (
                SELECT barcode, SUM(jumlah) AS total_pembelian
                FROM pembelian_detail
                GROUP BY barcode
            ) pbd ON bd.barcode = pbd.barcode
            
            -- "In" part: Subquery for total retur_jual
            LEFT JOIN (
                SELECT barcode, SUM(jumlah) AS total_retur_jual
                FROM retur_jual a
                INNER JOIN retur_jual_detail b ON a.id = b.id
                GROUP BY barcode
            ) rj ON bd.barcode = rj.barcode
            
            -- "In" part: Subquery for approved penyesuaian (adjustment)
            LEFT JOIN penyesuaian adj ON bd.barcode = adj.barcode AND adj.approved = 1
            
            -- "Out" part: Subquery for total retur_beli (return purchases)
            LEFT JOIN (
                SELECT DISTINCT barcode, SUM(jumlah) AS total_retur_beli
                FROM retur_beli a 
                INNER JOIN retur_beli_detail b ON a.id = b.id
                WHERE a.status = "tukar"
                GROUP BY barcode
            ) rb ON bd.barcode = rb.barcode
            
            -- "Out" part: Subquery for total penjualan (sales)
            LEFT JOIN (
                SELECT DISTINCT barcode, SUM(jumlah) AS total_penjualan
                FROM penjualan_detail
                GROUP BY barcode
            ) pjd ON bd.barcode = pjd.barcode
            
            -- "Out" part: Subquery for total disposal
            LEFT JOIN (
                SELECT DISTINCT barcode, SUM(jumlah) AS total_disposal
                FROM disposal_detail
                GROUP BY barcode
            ) dd ON bd.barcode = dd.barcode
            
            -- Join for the latest harga
            LEFT JOIN (
                SELECT h1.id_barang, h1.harga1, h1.harga2, h1.harga3
                FROM harga h1
                INNER JOIN (
                    SELECT id_barang, MAX(tanggal) AS latest_date
                    FROM harga
                    GROUP BY id_barang
                ) h2 ON h1.id_barang = h2.id_barang AND h1.tanggal = h2.latest_date
            ) h ON b.id = h.id_barang
            
            WHERE b.is_delete = "no"
            AND bd.barcode = ?
            GROUP BY b.id;
;
        ';
        $query=$this->db->query($sql,$barcode);
        return $query->getRow();
    }
    
    public function addopname($mdata){
        $sesuai = $this->db->table("penyesuaian");
        if (!$sesuai->insert($mdata)) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menyimpan Opname"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "Opname berhasil disimpan"
        );
    }
    
    public function list_opname(){
        $sql="SELECT 
                    b.id AS kodebrg,
                    b.namabarang AS nama_barang,
                    k.namakategori AS kategori,
                    b.stokmin AS min,
                    (
                        COALESCE(SUM(pbd.jumlah), 0) + 
                        COALESCE(SUM(CASE WHEN rb.status = 'tukar' THEN rbd.jumlah ELSE 0 END), 0) +  
                        COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0) 
                    ) 
                    - (
                        COALESCE(SUM(pjd.jumlah), 0) + 
                        COALESCE(SUM(CASE WHEN rb.status = 'proses' THEN rbd.jumlah ELSE 0 END), 0) +  
                        COALESCE(SUM(dd.jumlah), 0) 
                    ) AS stok,
                    COALESCE(SUM(CASE WHEN adj.approved = 0 THEN adj.jumlah ELSE 0 END), 0) AS riil
                FROM barang b
                INNER JOIN kategori k ON b.id_kategori = k.id
                LEFT JOIN barang_detail bd ON b.id = bd.barang_id
                LEFT JOIN pembelian_detail pbd ON bd.barcode = pbd.barcode
                LEFT JOIN retur_beli_detail rbd ON bd.barcode = rbd.barcode
                LEFT JOIN retur_beli rb ON rb.id = rbd.id
                LEFT JOIN penjualan_detail pjd ON bd.barcode = pjd.barcode
                LEFT JOIN penyesuaian adj ON bd.barcode = adj.barcode  -- Do not filter on adj.approved here
                LEFT JOIN disposal_detail dd ON dd.barcode = bd.barcode
                WHERE b.is_delete = 'no'
                GROUP BY b.id, b.namabarang, k.namakategori, b.stokmin
                HAVING riil != 0;";
        $query=$this->db->query($sql);
        return $query->getResult();
    }
    
    public function barcode_opname($kodebarang){
        $sql="SELECT 
                bd.barcode AS barcode,
                bd.expired AS exp_date,
                k.namakategori AS kategori,
                (
                    COALESCE(SUM(pbd.jumlah), 0) + 
                    COALESCE(SUM(CASE WHEN rb.status = 'tukar' THEN rbd.jumlah ELSE 0 END), 0) +  
                    COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0)
                ) 
                - (
                    COALESCE(SUM(pjd.jumlah), 0) + 
                    COALESCE(SUM(CASE WHEN rb.status = 'proses' THEN rbd.jumlah ELSE 0 END), 0) +  
                    COALESCE(SUM(dd.jumlah), 0) 
                ) AS system_stok,
                (
                    (
                        COALESCE(SUM(pbd.jumlah), 0) + 
                        COALESCE(SUM(CASE WHEN rb.status = 'tukar' THEN rbd.jumlah ELSE 0 END), 0) +  
                        COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0)
                    )
                    - (
                        COALESCE(SUM(pjd.jumlah), 0) + 
                        COALESCE(SUM(CASE WHEN rb.status = 'proses' THEN rbd.jumlah ELSE 0 END), 0) +  
                        COALESCE(SUM(dd.jumlah), 0) 
                    )
                    + COALESCE(SUM(CASE WHEN adj.approved = 0 THEN adj.jumlah ELSE 0 END), 0)
                ) AS riil,
                MAX(CASE WHEN adj.approved = 0 THEN adj.keterangan ELSE NULL END) AS keterangan
            FROM barang b
            INNER JOIN kategori k ON b.id_kategori = k.id
            LEFT JOIN barang_detail bd ON b.id = bd.barang_id
            LEFT JOIN pembelian_detail pbd ON bd.barcode = pbd.barcode
            LEFT JOIN retur_beli_detail rbd ON bd.barcode = rbd.barcode
            LEFT JOIN retur_beli rb ON rb.id = rbd.id
            LEFT JOIN penjualan_detail pjd ON bd.barcode = pjd.barcode
            LEFT JOIN penyesuaian adj ON bd.barcode = adj.barcode  -- Include all adjustments here
            LEFT JOIN disposal_detail dd ON dd.barcode = bd.barcode
            WHERE b.is_delete = 'no' AND bd.barang_id=?
            GROUP BY bd.barcode, bd.expired, k.namakategori
            HAVING riil != 0;";
        $query=$this->db->query($sql,$kodebarang);
        return $query->getResult();
    }
    
    public function add_dispose($barang){
        $dispose = $this->db->table("disposal_detail");
        if (!$dispose->insertBatch($barang)) {
            return (object) array(
                "code"      => 400,
                "message"   => "Gagal menyimpan Hapus Stok"
            );
        }

        return (object) array(
            "code"      => 200,
            "message"   => "Hapus Stok berhasil disimpan"
        );

    }
    
    public function get_barangmin(){
        $sql='SELECT 
                b.id AS kodebrg,
                b.namabarang AS nama_barang,
                k.namakategori AS kategori,
                b.stokmin AS min,
                (
                    COALESCE(SUM(DISTINCT pbd.total_pembelian), 0) + 
                    COALESCE(SUM(DISTINCT rj.total_retur_jual), 0) + 
                    COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0)
                )
                - (
                    COALESCE(SUM(DISTINCT rb.total_retur_beli), 0) + 
                    COALESCE(SUM(DISTINCT pjd.total_penjualan), 0) + 
                    COALESCE(SUM(DISTINCT dd.total_disposal), 0)
                ) AS stok
            FROM barang b
                INNER JOIN kategori k ON b.id_kategori = k.id
                LEFT JOIN barang_detail bd ON b.id = bd.barang_id
                
                -- "In" part: Subquery for total pembelian
                LEFT JOIN (
                    SELECT barcode, SUM(jumlah) AS total_pembelian
                    FROM pembelian_detail
                    GROUP BY barcode
                ) pbd ON bd.barcode = pbd.barcode
                
                -- "In" part: Subquery for total retur_jual
                LEFT JOIN (
                    SELECT barcode, SUM(jumlah) AS total_retur_jual
                    FROM retur_jual a
                    INNER JOIN retur_jual_detail b ON a.id = b.id
                    GROUP BY barcode
                ) rj ON bd.barcode = rj.barcode
                
                -- "In" part: Subquery for approved penyesuaian (adjustment)
                LEFT JOIN penyesuaian adj ON bd.barcode = adj.barcode AND adj.approved = 1
                
                -- "Out" part: Subquery for total retur_beli (return purchases)
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_retur_beli
                    FROM retur_beli a 
                    INNER JOIN retur_beli_detail b ON a.id = b.id
                    WHERE a.status = "tukar"
                    GROUP BY barcode
                ) rb ON bd.barcode = rb.barcode
                
                -- "Out" part: Subquery for total penjualan (sales)
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_penjualan
                    FROM penjualan_detail
                    GROUP BY barcode
                ) pjd ON bd.barcode = pjd.barcode
                
                -- "Out" part: Subquery for total disposal
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_disposal
                    FROM disposal_detail
                    GROUP BY barcode
                ) dd ON bd.barcode = dd.barcode
                
            WHERE b.is_delete = "no"
            GROUP BY b.id
            HAVING stok <= min;';
    $query=$this->db->query($sql);
    return $query->getResult();
}

public function get_laporan_barang() {
    $sql = 'SELECT 
                b.id AS kodebrg,
                b.namabarang AS nama_barang,
                k.namakategori AS kategori,
                hr.harga1,hr.harga2, hr.harga3,
                b.stokmin AS min,
                (
                    COALESCE(SUM(DISTINCT pbd.total_pembelian), 0) +
                    COALESCE(SUM(DISTINCT rj.total_retur_jual), 0) +
                    COALESCE(SUM(CASE WHEN adj.approved = 1 THEN adj.jumlah ELSE 0 END), 0)
                )-(
                    COALESCE(SUM(DISTINCT rb.total_retur_beli), 0) + 
                    COALESCE(SUM(DISTINCT pjd.total_penjualan), 0) + 
                    COALESCE(SUM(DISTINCT dd.total_disposal), 0)
                ) AS stok,
                COALESCE(pjd.avg_penjualan, 0) AS avg_penjualan,
                lp.harga_terbaru
            FROM barang b
                INNER JOIN (SELECT MAX(tanggal), harga1,harga2, harga3, id_barang FROM harga GROUP BY id_barang) hr ON b.id=hr.id_barang
                INNER JOIN kategori k ON b.id_kategori = k.id
                LEFT JOIN barang_detail bd ON b.id = bd.barang_id
                
                -- "In" part: Subquery for total pembelian
                LEFT JOIN (
                    SELECT barcode, SUM(jumlah) AS total_pembelian
                    FROM pembelian_detail
                    GROUP BY barcode
                ) pbd ON bd.barcode = pbd.barcode
                
                -- "In" part: Subquery for total retur_jual
                LEFT JOIN (
                    SELECT barcode, SUM(jumlah) AS total_retur_jual
                    FROM retur_jual a
                    INNER JOIN retur_jual_detail b ON a.id = b.id
                    GROUP BY barcode
                ) rj ON bd.barcode = rj.barcode
                
                -- "In" part: Subquery for approved penyesuaian (adjustment)
                LEFT JOIN penyesuaian adj ON bd.barcode = adj.barcode AND adj.approved = 1
                
                -- "Out" part: Subquery for total retur_beli (return purchases)
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_retur_beli
                    FROM retur_beli a 
                    INNER JOIN retur_beli_detail b ON a.id = b.id
                    WHERE a.status = "tukar"
                    GROUP BY barcode
                ) rb ON bd.barcode = rb.barcode
                
                -- "Out" part: Subquery for total penjualan (sales)
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_penjualan,
                    AVG(jumlah) AS avg_penjualan
                    FROM penjualan_detail
                    GROUP BY barcode
                ) pjd ON bd.barcode = pjd.barcode
                
                -- "Out" part: Subquery for total disposal
                LEFT JOIN (
                    SELECT DISTINCT barcode, SUM(jumlah) AS total_disposal
                    FROM disposal_detail
                    GROUP BY barcode
                ) dd ON bd.barcode = dd.barcode
                -- Subquery for the latest harga from pembelian
                LEFT JOIN (
                    SELECT
                        br.id AS id_barang,
                        subquery.harga_terbaru
                    FROM
                        barang br
                    INNER JOIN (
                        SELECT
                            bd.barang_id,
                            pb.tanggal,
                            pd.harga AS harga_terbaru
                        FROM
                            pembelian pb
                        INNER JOIN pembelian_detail pd ON pb.id = pd.id
                        INNER JOIN barang_detail bd ON pd.barcode = bd.barcode
                        WHERE
                            pd.harga IS NOT NULL
                            AND pb.tanggal = (
                                SELECT MAX(pb2.tanggal)
                                FROM pembelian pb2
                                INNER JOIN pembelian_detail pd2 ON pb2.id = pd2.id
                                INNER JOIN barang_detail bd2 ON pd2.barcode = bd2.barcode
                                WHERE bd2.barang_id = bd.barang_id
                            )
                    ) subquery ON br.id = subquery.barang_id
                ) lp ON b.id = lp.id_barang
            WHERE b.is_delete = "no"
            GROUP BY b.id
            HAVING stok <= min;;
    ';

    $query = $this->db->query($sql)->getResult();
    return $query;
}

public function get_mutasi_stok() {
    $sql = " SELECT
                b.namabarang,
                COALESCE(c.jumlah_awal, 0) AS awal, -- belum fix
                COALESCE(c.jumlah, 0) AS masuk,
                COALESCE(d.jumlah, 0) AS terjual,
                COALESCE(e.jumlah, 0) AS retursup,
                COALESCE(f.jumlah, 0) AS returpel,
                COALESCE(g.jumlah, 0) AS sesuai,
                (
                    COALESCE(c.jumlah_awal, 0) 
                    + COALESCE(c.jumlah, 0) 
                    + COALESCE(f.jumlah, 0) 
                    + COALESCE(g.jumlah, 0) 
                    - COALESCE(d.jumlah, 0) 
                    - COALESCE(e.jumlah, 0) 
                    - COALESCE(h.jumlah, 0)
                ) AS sisa
            FROM
                barang b
            LEFT JOIN (
                SELECT
                    bd.barang_id,
                    SUM(pd.jumlah) AS jumlah_awal, -- awal
                    SUM(pd.jumlah) AS jumlah -- masuk
                FROM
                    barang_detail bd
                INNER JOIN
                    pembelian_detail pd ON pd.barcode = bd.barcode
                GROUP BY
                    bd.barang_id
            ) c ON c.barang_id = b.id
            LEFT JOIN (
                SELECT
                    bd.barang_id, -- terjual
                    SUM(jd.jumlah) AS jumlah
                FROM
                    barang_detail bd
                INNER JOIN
                    penjualan_detail jd ON jd.barcode = bd.barcode
                GROUP BY
                    bd.barang_id
            ) d ON d.barang_id = b.id
            LEFT JOIN (
                SELECT
                    bd.barang_id, -- retur by suplier
                    SUM(rb.jumlah) AS jumlah
                FROM
                    barang_detail bd
                INNER JOIN
                    retur_beli_detail rb ON rb.barcode = bd.barcode
                GROUP BY
                    bd.barang_id
            ) e ON e.barang_id = b.id
            LEFT JOIN (
                SELECT
                    bd.barang_id, -- retur by pelanggan
                    SUM(rj.jumlah) AS jumlah
                FROM
                    barang_detail bd
                INNER JOIN
                    retur_jual_detail rj ON rj.barcode = bd.barcode
                GROUP BY
                    bd.barang_id
            ) f ON f.barang_id = b.id
            LEFT JOIN (
                SELECT
                    bd.barang_id, -- penyesuaian
                    SUM(p.jumlah) AS jumlah
                FROM
                    barang_detail bd
                INNER JOIN
                    penyesuaian p ON p.barcode = bd.barcode
                WHERE
                    p.approved = 1
                GROUP BY
                    bd.barang_id
            ) g ON g.barang_id = b.id
            LEFT JOIN (
                SELECT
                    bd.barang_id, -- disposal
                    SUM(dd.jumlah) AS jumlah
                FROM
                    barang_detail bd
                INNER JOIN
                    disposal_detail dd ON dd.barcode = bd.barcode
                GROUP BY
                    bd.barang_id
            ) h ON h.barang_id = b.id
            WHERE
                b.is_delete = 'no'
            GROUP BY -- grup berdasarkan id dan nama barang
                b.id, b.namabarang";   
    return $this->db->query($sql)->getResult();
}

}

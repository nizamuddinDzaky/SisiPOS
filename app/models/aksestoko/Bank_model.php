<?php defined('BASEPATH') or exit('No direct script access allowed');

class Bank_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return "Home Index";
    }

    public function getAllBank($supplier_id)
    {
        $this->db->where('company_id', $supplier_id);
        $this->db->where("({$this->db->dbprefix('bank')}.is_deleted !=1 OR {$this->db->dbprefix('bank')}.is_deleted IS NULL AND {$this->db->dbprefix('bank')}.is_active = 1)");
        $q = $this->db->get('bank');
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }

        return false;
    }

    public function getBankById($bank_id)
    {
        $q = $this->db->get_where('bank', [
            'id' => $bank_id,
        ], 1);
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }
}

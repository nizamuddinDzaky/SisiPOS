<?php defined('BASEPATH') or exit('No direct script access allowed');


class Target_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getAllProducts()
    {
        $this->db->select("*");
        $this->db->from("products");
        $query=$this->db->get();
        return $query->result();
    }

    public function getProductByID($id)
    {
        $this->db->select("*");
        $this->db->from("products");
        $this->db->where("name", $id);
        $this->db->limit(1);

        $query=$this->db->get();
        return $query->result();
    }
}

/* End of file pts_model.php */
/* Location: ./application/models/pts_types_model.php */

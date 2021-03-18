<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Services_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->database();
    }

    public function getUser($username)
    {
        $this->db->where('username', $username);

        $query = $this->db->get('users');

        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                $result[]=$row;
            }
            return $result;
        } else {
            return false;
        }
    }

    /*    public function getAllUser(){
            $query = $this->db->get('users');
    
            if ($query->num_rows() > 0) {
                foreach (($query->result()) as $row) {
                    $result[]=$row;
                }
                return $result;
            } else{
                return false;
            }
        }*/

    public function getAllUser($start)
    {
        $this->db->from('users');

        if ($start) {
            $this->db->limit(2, $start);
        } else {
            $this->db->limit(2, 0);
        }
        
        $query  = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                $result[]=$row;
            }
            return $result;
        } else {
            return false;
        }
    }

    public function getAllWarehousesProducts($limit, $start)
    {
        $this->db->from('warehouses_products');

        if ($start) {
            $this->db->limit($limit, $start);
        // $this->db->limit($limit, 2990);
        } else {
            $this->db->limit($limit, 0);
        }
        
        $query  = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                $result[]=$row;
            }
            return $result;
        } else {
            return false;
        }
    }

    public function getLastWarehousesProducts()
    {
        $this->db->from('warehouses_products');
        $this->db->order_by('id', 'desc');
        $this->db->limit(1, 0);

        $query=$this->db->get();

        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                $result[]=$row;
            }
            return $result;
        } else {
            return false;
        }
    }

    public function getAllWarehouses()
    {
        $query  = $this->db->get('warehouses');

        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                $result[]=$row;
            }
            return $result;
        } else {
            return false;
        }
    }

    public function addUser($data)
    {
        return $this->db->insert('users', $data);
    }
}

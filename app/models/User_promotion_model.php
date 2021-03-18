<?php defined('BASEPATH') or exit('No direct script access allowed');

class User_promotion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserPromotions($promo_id){
        $this->db->select('user_promotions.company_id as company_id, companies.company as company, user_promotions.supplier_id, companies.cf1 as cf1');
        $this->db->where('user_promotions.promo_id', $promo_id);
        $this->db->where('user_promotions.is_deleted', null);
        $this->db->join("companies", 'companies.id = user_promotions.company_id AND companies.group_name = "customer" AND companies.company_id = user_promotions.supplier_id');

        $query = $this->db->get('user_promotions');
        if(count($query->result_array()) > 0){
            return $query->result_array();
        }
        return false;
    }

    public function addUserPromotion($data){
        if ($this->db->insert('user_promotions', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function updateUserPromotion($promo_id, $company_id, $supplier_id, $data){
        $this->db->where('promo_id', $promo_id);
        $this->db->where('company_id', $company_id);
        $this->db->where('supplier_id', $supplier_id);
        if ($this->db->update('user_promotions', $data)) {
            return true;
        }
        return false;
    }

    public function checkUserPromotion($promo_id, $company_id, $supplier_id){
        $this->db->where('promo_id', $promo_id);
        $this->db->where('company_id', $company_id);
        $this->db->where('supplier_id', $supplier_id);
        $query = $this->db->get('user_promotions');
        if(count($query->result()) > 0){
            return $query->result();
        }

        return false;
    }

    public function setDeleteUserPromotion($promo_id, $data){
        $this->db->where('promo_id', $promo_id);
        if ($this->db->update('user_promotions', $data)) {
            return true;
        }
        return false;
    }
}
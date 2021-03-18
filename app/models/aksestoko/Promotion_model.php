<?php defined('BASEPATH') or exit('No direct script access allowed');

class Promotion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return "Promotion Index";
    }

    public function listPromotion($company_id, $supplier_id)
    {
        $this->db->select('sma_promo.*');
        $this->db->join('user_promotions', 'sma_promo.id = user_promotions.promo_id');
        $this->db->where('sma_promo.status >=', 1);
        $this->db->where('CURDATE()-sma_promo.end_date <= 1');
        $this->db->where('user_promotions.company_id', $company_id);
        $this->db->where('user_promotions.supplier_id', $supplier_id);
        $this->db->where('user_promotions.is_deleted', null);
        $q = $this->db->get('sma_promo');
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
    }

    public function GetPromotion($company_id, $id)
    {
        $this->db->select('sma_promo.*');
        $this->db->join('user_promotions', 'sma_promo.id = user_promotions.promo_id');
        $this->db->where('sma_promo.status >=', 1);
        $this->db->where('user_promotions.company_id', $company_id);
        $this->db->where('user_promotions.is_deleted', null);
        $this->db->where('sma_promo.id', $id);
        $q = $this->db->get('sma_promo');
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function listPromotionPopup($company_id, $supplier_id)
    {
        $this->db->select('sma_promo.*');
        $this->db->join('user_promotions', 'sma_promo.id = user_promotions.promo_id');
        $this->db->where('sma_promo.status >=', 1);
        $this->db->where('sma_promo.is_popup !=', null);
        $this->db->where('CURDATE()-sma_promo.end_date <= 1');
        $this->db->where('user_promotions.company_id', $company_id);
        $this->db->where('user_promotions.supplier_id', $supplier_id);
        $this->db->where('user_promotions.is_deleted', null);
        $this->db->order_by('id', 'asc');
        $q = $this->db->get('sma_promo');
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getTransactionByCompany($promo_id, $company_id)
    {
        $this->db->select('sma_transaction_promo.*');
        $this->db->where('promo_id', $promo_id);
        $this->db->where('company_id', $company_id);

        $q = $this->db->get('sma_transaction_promo');
        // if ($q->num_rows() > 0) {
        return $q->num_rows();
        // }
    }

    public function getTransactionByPromo($promo_id)
    {
        $this->db->select('sma_transaction_promo.*');
        $this->db->where('promo_id', $promo_id);
        // $this->db->where('compani_id', $company_id);

        $q = $this->db->get('sma_transaction_promo');
        // if ($q->num_rows() > 0) {
        return $q->num_rows();
        // }
    }

    public function addPromotion($data)
    {
        if ($this->db->insert("transaction_promo", $data)) {
            return true;
        }
        return false;
    }
}

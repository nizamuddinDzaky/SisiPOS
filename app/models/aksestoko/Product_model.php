<?php defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return "Promotion Index";
    }

    // public function getCompanyProduct($company_id, $per_page, $start )
    // {
    //     $this->db->select("sma_products.*");
    // 	$this->db->join('sma_companies','sma_companies.id=sma_products.company_id','inner');
    //     $this->db->where('sma_products.company_id',$company_id);
    //     $this->db->where('sma_products.is_deleted is null');
    //     $this->db->limit($per_page, $start);
    //     $q = $this->db->get('sma_products');
    //     if ($q->num_rows() > 0) {
    //          return $q->result();
    //     }
    // }

    public function getCompanyProduct($company_id, $per_page, $start, $price_group_id = null, $search)
    {
        $price_group_id = $price_group_id ? $price_group_id : "null";
        $this->db->select("sma_products.*, sma_product_prices.price as group_price, sma_product_prices.price_kredit as group_kredit, sma_product_prices.min_order, sma_product_prices.is_multiple");
        // $this->db->join('sma_companies','sma_companies.id=sma_products.company_id','inner');
        $this->db->join('sma_product_prices', '(sma_product_prices.product_id = sma_products.id && sma_product_prices.price_group_id = ' . $price_group_id . ')', 'left');
        // $this->db->join('sma_v_price_items','(sma_v_price_items.product_id=sma_products.id AND sma_v_price_items.customer_id='.$customer_id.')','left');
        $this->db->where('sma_products.company_id', $company_id);
        $this->db->where('sma_products.is_deleted is null');
        $this->db->where('sma_products.is_retail = 1');
        $this->db->order_by('sma_products.id', 'ASC');
        if ($search) {
            $this->db->where("(sma_products.name like '%$search%' or sma_products.code like '%$search%')");
        }
        $this->db->limit($per_page, $start);
        $q = $this->db->get('sma_products');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
    }

    public function getRowCompanyProduct($company_id, $search)
    {
        $this->db->select("sma_products.*");
        $this->db->join('sma_companies', 'sma_companies.id=sma_products.company_id', 'inner');
        $this->db->where('sma_products.company_id', $company_id);
        $this->db->where('sma_products.is_deleted is null');
        $this->db->where('sma_products.is_retail = 1');
        if ($search) {
            $this->db->where("(sma_products.name like '%$search%' or sma_products.code like '%$search%')");
        }
        return $this->db->get('sma_products')->num_rows();
    }

    public function getProductByID($id, $supplier_id, $price_group_id = null, $customer_id = null)
    {
        $price_group_id = $price_group_id ? $price_group_id : "null";

        $this->db->select("sma_products.*, sma_product_prices.price as group_price, sma_product_prices.price_kredit as group_kredit, sma_product_prices.min_order, sma_product_prices.is_multiple");
        $this->db->join('sma_product_prices', '(sma_product_prices.product_id = sma_products.id && sma_product_prices.price_group_id = ' . $price_group_id . ')', 'left');
        // $this->db->join('sma_v_price_items','(sma_v_price_items.product_id=sma_products.id AND sma_v_price_items.customer_id='.$customer_id.')','left');
        $this->db->where('sma_products.id', $id);
        $this->db->where('sma_products.company_id', $supplier_id);
        $q = $this->db->get('sma_products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCodeAndSupplierId($product_code, $supplier_id)
    {
        $this->db->where('sma_products.code', $product_code);
        $this->db->where('sma_products.company_id', $supplier_id);
        $q = $this->db->get('sma_products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductPhotos($id)
    {
        $q = $this->db->get_where("product_photos", array('product_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getUnit($id)
    {
        $q = $this->db->get_where("units", array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}

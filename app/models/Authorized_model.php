<?php

defined('BASEPATH') or exit('No direct script access allowed');

class authorized_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findPlan($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('sma_plans', 1)->row();
    }

    public function findOrderRef($company_id)
    {
        $this->db->where('company_id', $company_id);
        return $this->db->get('sma_order_ref', 1)->row();
    }

    public function isOrderLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $sales = $this->getSales($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];
        
        if ($plan->sales == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($sales) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isPOSOrderLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $sales = $this->getPOSSales($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];
        
        if ($plan->pos == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($sales) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }

    public function isQuoteLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $quotes = $this->getQuotes($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->quotes == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($quotes) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isPurchaseLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $purchases = $this->getPurchases($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->purchases == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($purchases) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isExpenseLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $expenses = $this->getExpenses($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->expenses == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($expenses) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }

    public function isTransferLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $transfers = $this->getTransfers($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->transfers == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($transfers) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isProductLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $products = $this->getProducts($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->master == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($products) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isSupplierLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $suppliers = $this->getSuppliers($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->master == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($suppliers) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isCustomerLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $customers = $this->getCustomers($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->master == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($customers) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isCategoryLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $categories = $this->getCategories($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->master == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($categories) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isBrandLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $brands = $this->getBrands($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->master == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($brands) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isExpenseCategoryLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $expenseCategories = $this->getExpenseCategories($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->master == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($expenseCategories) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function isUnitLimited($company_id)
    {
        $authorized = $this->view_by($company_id);
        $plan_id = $authorized->plan_id ? $authorized->plan_id : 1;
        $plan = $this->findPlan($plan_id);
        $units = $this->getUnits($company_id);
        
        $data = [
            "max" => 0,
            "status" => false
        ];

        if ($plan->master == 1) {
            if ($plan->limitation == 0) { //Limitation 0 berarti Unlimited
                $data["status"] = false;
            } elseif (count($units) < $plan->limitation) {
                $data["status"] = false;
            } else {
                $data["max"] = $plan->limitation;
                $data["status"] = true;
            }
        } else {
            $data["status"] = true;
        }

        return $data;
    }
    
    public function getSales($company_id, $year = null, $month = null)
    {
        $month = $month ? $month : date('m');
        $year = $year ? $year : date('Y');

        $this->db->select("*");
        $this->db->where("DATE_FORMAT(`date`,'%Y-%m') = '$year-$month' AND `company_id` = $company_id AND pos = 0 AND is_deleted IS NULL");
        return $this->db->get("sma_sales")->result_array();
    }
    
    public function getPOSSales($company_id, $year = null, $month = null)
    {
        $month = $month ? $month : date('m');
        $year = $year ? $year : date('Y');

        $this->db->select("*");
        $this->db->where("DATE_FORMAT(`date`,'%Y-%m') = '$year-$month' AND `company_id` = $company_id AND pos = 1 AND is_deleted IS NULL");
        return $this->db->get("sma_sales")->result_array();
    }

    public function getQuotes($biller_id, $year = null, $month = null)
    {
        $month = $month ? $month : date('m');
        $year = $year ? $year : date('Y');

        $this->db->select("*");
        $this->db->where("DATE_FORMAT(`date`,'%Y-%m') = '$year-$month' AND `biller_id` = $biller_id AND is_deleted IS NULL");
        return $this->db->get("sma_quotes")->result_array();
    }
    
    public function getPurchases($company_id, $year = null, $month = null)
    {
        $month = $month ? $month : date('m');
        $year = $year ? $year : date('Y');

        $this->db->select("*");
        $this->db->where("DATE_FORMAT(`date`,'%Y-%m') = '$year-$month' AND `company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_purchases")->result_array();
    }
    
    public function getExpenses($company_id, $year = null, $month = null)
    {
        $month = $month ? $month : date('m');
        $year = $year ? $year : date('Y');

        $this->db->select("*");
        $this->db->where("DATE_FORMAT(`date`,'%Y-%m') = '$year-$month' AND `company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_expenses")->result_array();
    }
    
    public function getTransfers($company_id, $year = null, $month = null)
    {
        $month = $month ? $month : date('m');
        $year = $year ? $year : date('Y');

        $this->db->select("*");
        $this->db->where("DATE_FORMAT(`date`,'%Y-%m') = '$year-$month' AND `company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_transfers")->result_array();
    }
    
    public function getProducts($company_id)
    {
        $this->db->select("*");
        $this->db->where("`company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_products")->result_array();
    }
    
    public function getSuppliers($company_id)
    {
        $this->db->select("*");
        $this->db->where("`group_name` = 'supplier' AND `company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_companies")->result_array();
    }
    
    public function getCustomers($company_id)
    {
        $this->db->select("*");
        $this->db->where("`group_name` = 'customer' AND `company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_companies")->result_array();
    }
    
    public function getCategories($company_id)
    {
        $this->db->select("*");
        $this->db->where("`company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_categories")->result_array();
    }
    
    public function getBrands($company_id)
    {
        $this->db->select("*");
        $this->db->where("`client_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_brands")->result_array();
    }
    
    public function getExpenseCategories($company_id)
    {
        $this->db->select("*");
        $this->db->where("`company_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_expense_categories")->result_array();
    }
    
    public function getUnits($company_id)
    {
        $this->db->select("*");
        $this->db->where("`client_id` = $company_id AND is_deleted IS NULL");
        return $this->db->get("sma_units")->result_array();
    }

    public function view()
    {
        return $this->db->get('sma_authorized')->result();
    }

    public function view_by($company_id)
    {
        $this->db->where('company_id', $company_id);
        return $this->db->get('sma_authorized', 1)->row();
    }

    public function validation($mode)
    {
        $this->load->library('form_validation');

        if ($mode == "save") {
            $this->form_validation->set_rules('input_company_id', 'company_id', 'required|numeric|max_length[11]');
        }
        $this->form_validation->set_rules('input_users', 'user', 'required|numeric|max_length[11]');
        $this->form_validation->set_rules('input_warehouses', 'warehouses', 'required|numeric|max_length[11]');
        $this->form_validation->set_rules('input_biller', 'biller', 'required|numeric|max_length[11]');
        if ($this->form_validation->run()) {
            return true;
        } else {
            return false;
        }
    }

    public function save()
    {
        $data = array(
            "company_id" => $this->input->post('input_company_id'),
            "users" => $this->input->post('input_users'),
            "warehouses" => $this->input->post('input_warehouses'),
            "biller" => $this->input->post('input_biller')
        );
        $this->db->insert('sma_authorized', $data);
    }

    public function edit()
    {
    }
}

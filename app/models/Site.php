<?php defined('BASEPATH') or exit('No direct script access allowed');

class Site extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_total_qty_alerts()
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->where('quantity < alert_quantity', null, false)->where('track_quantity', 1);
        return $this->db->count_all_results('products');
    }

    public function get_expiring_qty_alerts()
    {
        $date = date('Y-m-d', strtotime('+3 months'));
        $this->db->select('SUM(quantity_balance) as alert_num')
            ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
            ->where('expiry <', $date);
        if (!$this->Owner) {
            $this->db->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (int) $res->alert_num;
        }
        return false;
    }

    public function get_setting()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDateFormat($id)
    {
        $q = $this->db->get_where('date_format', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllCompanies($group_name)
    {
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCompaniesByGroupName($group_name)
    {
        $this->db->select('id, company, name');
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCompanyByPriceGroup($pg_id, $company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        $q = $this->db->get_where('companies', array('price_group_id' => $pg_id, 'group_name' => 'customer', 'is_deleted' => null));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getWarehouseIfNull($company_id, $company)
    {
        $this->db->like('name', $company);
        $q = $this->db->get_where('warehouses', array('company_id' => $company_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCompanyByCode($code)
    {
        $this->db->like('group_name', 'biller');
        $q = $this->db->get_where('companies', array('cf1' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSupplierByid($id)
    {
        $q = $this->db->get_where('deliveries_smig', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCustomerGroupByID($id)
    {
        $q = $this->db->get_where('customer_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUser($id = null)
    {
        if (!$id) {
            $id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findUserByCompanyId($company_id, $group_id = 2)
    {
        $q = $this->db->get_where('users', array(
            'company_id' => $company_id,
            'group_id' => $group_id,
            'biller_id' => $company_id
        ), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }

    public function findUserByIdBk($bk)
    {
        $this->db->select('users.*');   
        $this->db->join('users', 'companies.id = users.company_id', 'left');
        $q = $this->db->get_where('companies', array(
            'cf1'        => 'IDC-' . $bk,
            'group_name' => 'biller'
        ), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }
    
    public function getProductByID($id, $company_id = null)
    {
        if ($company_id == null) {
            $company_id = $this->session->userdata('company_id');
        }

        if (!$this->Owner) {
            $where = "(company_id = '1' OR company_id = '" . $this->session->userdata('company_id') . "') ";
            $this->db->where($where);
        }
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProducts($company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }

        $q = $this->db->get_where('products', array('is_deleted' => null));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAllCurrencies()
    {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCurrencyByCode($code)
    {
        $q = $this->db->get_where('currencies', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllWarehouses($company_id = null, $where = null)
    {
        if (!$this->Owner && $this->session->userdata('identity') && !$this->Principal) {
            if (!$this->Admin) {
                $this->db->where('id', $this->session->userdata('warehouse_id'));
            }
            $this->db->where('company_id', $company_id ?? $this->session->userdata('company_id'));
        }

        if ($where) {
            $this->db->where($where);
        }

        $this->db->where('(is_deleted IS NULL OR is_deleted = "0")');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getAllWarehousesCustomer($company_id = null)
    {

        $this->db->select("warehouses.id `id`, warehouses.code `code`, warehouses.name `name`");
        if (!$this->Owner && $this->session->userdata('identity') && !$this->Principal) {
            if (!$this->Admin) {
                $this->db->where('id', $this->session->userdata('warehouse_id'));
            }
            $this->db->where('company_id', $company_id ?? $this->session->userdata('company_id'));
            $this->db->where('(is_deleted IS NULL OR is_deleted = "0")');
        }
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getWarehouseCustomerByCustomer($customer_id)
    {
        $this->db->select("warehouses.id `id`, warehouses.code `code`, warehouses.name `name`")
            ->join('warehouses', 'warehouses.id = warehouse_customer.warehouse_id')
            ->where("warehouse_customer.customer_id", $customer_id)
            ->where('warehouse_customer.is_deleted = 0');
        $q = $this->db->get('warehouse_customer');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getWarehouseCustomerDefault($biller_id, $customer_id)
    {
        $this->db->select("warehouses.id `id`, warehouses.code `code`, warehouses.name `name`")
            ->join('warehouses', 'warehouse_customer.default = warehouses.id')
            ->where('warehouse_customer.default = warehouses.id')
            ->where('warehouses.company_id =', $biller_id)
            ->where('warehouse_customer.customer_id', $customer_id)
            ->group_by('sma_warehouse_customer.`default`');
        $q = $this->db->get('warehouse_customer');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getListCustomerWarehouse($warehouse_id, $company_id)
    {
        $this->db->select("companies.id, companies.company, companies.name, 
                           companies.phone, companies.cf1, companies.country, 
                           companies.city, companies.state, 
                           IF(sma_warehouse_customer.default = '" . $warehouse_id . "' AND sma_warehouse_customer.is_deleted = 0, 1, 0) AS `default`,
                           warehouse_customer.`default` AS default_id,
                           warehouses.name AS warehouses_name")
            ->join('warehouse_customer', 'companies.id = warehouse_customer.customer_id', 'left')
            ->join('warehouses', 'warehouse_customer.default = warehouses.id', 'left')
            ->where('companies.company_id', $company_id)
            ->where('companies.group_name', 'customer')
            ->where('companies.is_deleted', NULL)
            ->group_by('companies.id');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getCustomerWarehouse($warehouse_id, $company_id)
    {
        $this->db->select("companies.id, companies.company, companies.name, 
                           companies.phone, companies.cf1, companies.country, 
                           companies.city, companies.state, 
                           IF(sma_warehouse_customer.default = '" . $warehouse_id . "', 1, 0) AS `default`,
                           warehouse_customer.`default` AS default_id,
                           warehouses.name AS warehouses_name")
            ->join('companies', 'companies.id = warehouse_customer.customer_id', 'left')
            ->join('warehouses', 'warehouse_customer.default = warehouses.id', 'left')
            ->where('warehouse_customer.warehouse_id', $warehouse_id)
            ->where('companies.company_id', $company_id)
            ->where('warehouse_customer.is_deleted', 0);
        $q = $this->db->get('warehouse_customer');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getWarehousesCustomer($customer_id)
    {
        $this->db->select("warehouses.id `id`, warehouses.code `code`, warehouses.name `name`");
        $this->db->from('warehouses, warehouse_customer');
        $this->db->where("warehouse_customer.warehouse_id = warehouses.id");
        $this->db->where("warehouse_customer.customer_id = ", $customer_id);
        $this->db->where('warehouse_customer.is_deleted = 0');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getWarehousesCustomerExclude($warehouse_id)
    {
        $this->db->select("sma_warehouse_customer.customer_id `id`, sma_warehouses.id `code`, sma_warehouses.name `name`");
        $this->db->from('sma_warehouses, sma_warehouse_customer');
        $this->db->where("sma_warehouse_customer.warehouse_id = sma_warehouses.id");
        $this->db->where("sma_warehouse_customer.warehouse_id !=", $warehouse_id);
        $this->db->where('sma_warehouse_customer.is_deleted = 0');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getWarehousesAndCompany($where = null)
    {
        if ($where) {
            $this->db->where($where);
        }
        $this->db->select("warehouses.id `id`, warehouses.code `code`, warehouses.name `name`, warehouses.address `address`, companies.city `city`, warehouses.is_deleted `is_deleted`, companies.cf1 `cf1`, companies.company `company`");
        $this->db->join('companies', 'warehouses.company_id = companies.id');
        $this->db->join('users', 'companies.id = users.company_id');
        $this->db->where("(companies.client_id is null OR companies.client_id != 'aksestoko')");
        $this->db->where("users.active = 1");
        $this->db->where("users.group_id = 2");

        $q = $this->db->get('warehouses');
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAllSupplierCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'supplier'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseByID($id, $company_id = null)
    {
        if ($company_id == null)
            $company_id = $this->session->userdata('company_id');
        $this->db->where('id', $id);
        if (!$this->Owner) {
            $this->db->where('company_id', $company_id);
        }
        $q = $this->db->get('warehouses');
        // $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getNameAndIdWarehouses()
    {
        $this->db->select('id, name');
        if (!$this->Owner && $this->session->userdata('identity') && !$this->Principal) {
            if (!$this->Admin) {
                $this->db->where('id', $this->session->userdata('warehouse_id'));
            }
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseByCode($Code)
    {
        $this->db->where('code', $Code);
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('warehouses');
        // $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllCategories()
    {
        $this->db->order_by("id", "asc");
        if (!$this->Owner) {
            $this->db->where("(parent_id=0  or parent_id is null) and  (company_id = " . $this->session->userdata('company_id') . " or company_id = 1) ")->order_by('name');
        } else {
            $this->db->where('parent_id', null)->or_where('parent_id', 0)->order_by('name');
        }
        //        $this->db->where('parent_id', NULL)->or_where('parent_id', 0)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPosCategories()
    {
        if (!$this->Owner) {
            $this->db->where('products.company_id', $this->session->userdata('company_id'));
        }
        $this->db->select('categories.*');
        //        $this->db->where('categories.parent_id', NULL)->or_where('categories.parent_id', 0)->order_by('name');
        $this->db->join('products', 'categories.id = products.category_id', 'left');
        $this->db->group_by('products.category_id');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function getSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCategoryByID($id)
    {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGiftCardByID($id)
    {
        $q = $this->db->get_where('gift_cards', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getHistoryLoginByID($start, $end)
    {
        $this->db->select('users.username, user_logins.ip_address, user_logins.login, user_logins.time')
            ->join("users", 'users.id=user_logins.user_id', 'left');
        // ->where("user_logins.id",$id);
        $this->db->where("cast(time as date) >", date('Y-m-d', strtotime($start)));
        $this->db->where("cast(time as date) <", date('Y-m-d', strtotime($end)));
        $q = $this->db->get('user_logins');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getPromotionById($id)
    {
        $q = $this->db->get_where('promo', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGiftCardByNO($no)
    {
        $q = $this->db->get_where('gift_cards', array('card_no' => $no), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateInvoiceStatus()
    {
        $date = date('Y-m-d');
        $q = $this->db->get_where('invoices', array('status' => 'unpaid'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->due_date < $date) {
                    $this->db->update('invoices', array('status' => 'due'), array('id' => $row->id));
                }
            }
            $this->db->update('settings', array('update' => $date), array('setting_id' => '1'));
            return true;
        }
    }

    public function modal_js()
    {
        return '<script type="text/javascript">' . file_get_contents(FCPATH . 'themes/default/assets/js/modal.js') . '</script>';
    }

    public function getOrderRef($company_id = null)
    {
        $company_id = $company_id ? $company_id : $this->session->userdata('company_id');
        $q = $this->db->get_where('order_ref', array('company_id' => $company_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getReference($field, $company_id = null)
    {
        $company_id = $company_id ? $company_id : $this->session->userdata('company_id');
        $month = date('Y-m') . '-01';
        $reset_ref = array('so' => 1, 'qu' => 1, 'po' => 1, 'to' => 1, 'pos' => 1, 'do' => 1, 'pay' => 1, 'ppay' => 1, 're' => 1, 'rep' => 1, 'ex' => 1, 'qa' => 1, 'csg' => 1, 'dr' => 1);
        if ($ref = $this->getOrderRef($company_id)) {
            if ($this->Settings->reference_format == 2 && strtotime($ref->date) < strtotime($month)) {
                $reset_ref['date'] = $month;
                $this->db->update('order_ref', $reset_ref, array('company_id' => $company_id));
            }
        }
        $q = $this->db->get_where('order_ref', array('company_id' => $company_id), 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
                case 'so':
                    $prefix = $this->Settings->sales_prefix;
                    break;
                case 'pos':
                    $prefix = isset($this->Settings->sales_prefix) ? $this->Settings->sales_prefix . '/POS' : '';
                    break;
                case 'qu':
                    $prefix = $this->Settings->quote_prefix;
                    break;
                case 'po':
                    $prefix = $this->Settings->purchase_prefix;
                    break;
                case 'to':
                    $prefix = $this->Settings->transfer_prefix;
                    break;
                case 'do':
                    $prefix = $this->Settings->delivery_prefix;
                    break;
                case 'pay':
                    $prefix = $this->Settings->payment_prefix;
                    break;
                case 'ppay':
                    $prefix = $this->Settings->ppayment_prefix;
                    break;
                case 'ex':
                    $prefix = $this->Settings->expense_prefix;
                    break;
                case 're':
                    $prefix = $this->Settings->return_prefix;
                    break;
                case 'rep':
                    $prefix = $this->Settings->returnp_prefix;
                    break;
                case 'qa':
                    $prefix = $this->Settings->qa_prefix;
                    break;
                case 'sc':
                    $prefix = $this->Settings->stock_prefix;
                    break;
                case 'csg':
                    $prefix = $this->Settings->consignment_prefix;
                    break;
                case 'cpay':
                    $prefix = $this->Settings->cpayment_prefix;
                    break;
                case 'binv':
                    $prefix = $this->Settings->binvoice_prefix;
                    break;
                case 'bpay':
                    $prefix = $this->Settings->bpayment_prefix;
                    break;
                case 'dr':
                    $prefix = $this->Settings->delivery_return_prefix;
                    break;
                default:
                    $prefix = '';
            }

            $ref_no = (!empty($prefix)) ? $prefix . '/' : '';

            if ($this->Settings->reference_format == 1) {
                $ref_no .= date('Y') . "/" . sprintf("%04s", $ref->{$field});
            } elseif ($this->Settings->reference_format == 2) {
                $ref_no .= date('Y') . "/" . date('m') . "/" . sprintf("%04s", $ref->{$field});
            } elseif ($this->Settings->reference_format == 3) {
                $ref_no .= sprintf("%04s", $ref->{$field});
            } else {
                $ref_no .= $this->getRandomReference();
            }
            return $ref_no;
        }
        return false;
    }

    public function getRandomReference($len = 12)
    {
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }

        if ($this->getSaleByReference($result)) {
            $this->getRandomReference();
        }

        return $result;
    }

    public function getSaleByReference($ref)
    {
        $this->db->like('reference_no', $ref, 'before');
        $this->db->where('created_by', $this->session->userdata('user_id'));
        $q = $this->db->get('sales', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateReference($field, $company_id = null)
    {
        $company_id = $company_id ?? $this->session->userdata('company_id');
        $q = $this->db->get_where('order_ref', array('company_id' => $company_id), 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            if ($this->db->update('order_ref', array($field => $ref->{$field} + 1), array('company_id' => $company_id))) {
                return true;
            }
        }
        return false;
    }

    public function checkPermissions()
    {
        $q = $this->db->get_where('permissions', array('group_id' => $this->session->userdata('group_id')), 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function getNotifications()
    {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where("from_date <=", $date);
        $this->db->where("till_date >=", $date);
        $this->db->where("client_id >=", $this->session->userdata('company_id'));
        //        if (!$this->Owner) {
        //            if ($this->Supplier) {
        //                $this->db->where('scope', 4);
        //            } elseif ($this->Customer) {
        //                $this->db->where('scope', 1)->or_where('scope', 3);
        //            } elseif (!$this->Customer && !$this->Supplier) {
        //                $this->db->where('scope', 2)->or_where('scope', 3);
        //            }
        //        }
        $q = $this->db->get("notifications");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getUpcomingEvents()
    {
        $dt = date('Y-m-d');
        $this->db->where('start >=', $dt)->order_by('start')->limit(5);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUserGroup($user_id = false)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $group_id = $this->getUserGroupID($user_id);
        $q = $this->db->get_where('groups', array('id' => $group_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUserGroupID($user_id = false)
    {
        $user = $this->getUser($user_id);
        return $user->group_id;
    }

    public function getWarehouseProductsVariants($option_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasedItem($where_clause)
    {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('purchase_items.*');
        //        if(!$this->Owner){
        //            $this->db->join('warehouses','warehouses.id=purchase_items.warehouse_id','left');
        //            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        //        }
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get_where('purchase_items', $where_clause);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function syncVariantQty($variant_id, $warehouse_id, $product_id = null)
    {
        $balance_qty = $this->getBalanceVariantQuantity($variant_id);
        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
        if ($this->db->update('product_variants', array('quantity' => $balance_qty), array('id' => $variant_id))) {
            if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
                $this->db->update('warehouses_products_variants', array('quantity' => $wh_balance_qty), array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id));
            } else {
                if ($wh_balance_qty) {
                    $this->db->insert('warehouses_products_variants', array('quantity' => $wh_balance_qty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id));
                }
            }
            return true;
        }
        return false;
    }

    public function getWarehouseProducts($product_id, $warehouse_id = null, $company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function syncProductQty($product_id, $warehouse_id, $company_id = null)
    {
        $balance_qty = $this->getBalanceQuantity($product_id, null, $company_id);
        $wh_balance_qty = $this->getBalanceQuantity($product_id, $warehouse_id, $company_id);

        // var_dump($balance_qty, $wh_balance_qty);die;

        //        if($balance_qty!=NULL && $wh_balance_qty!=NULL){
        // $this->db->select('quantity')->where('id',$product_id);
        // $last_qty_p=$this->db->get('products');

        // $this->db->select('quantity')
        //     ->where('product_id',$product_id)->where('warehouse_id',$warehouse_id);
        // $last_qty_wp=$this->db->get('warehouses_products');

        // if( ($last_qty_p->num_rows() > 0) && ($last_qty_wp->num_rows() > 0) ){
        //     $data_p = $last_qty_p->row();
        //     $balance_qty=$data_p->quantity + $balance_qty;

        //     $data_wp = $last_qty_wp->row();
        //     $wh_balance_qty = $data_wp->quantity + $wh_balance_qty;
        // }

        if ($this->db->update('products', array('quantity' => $balance_qty), array('id' => $product_id, 'company_id' => $company_id ?? $this->session->userdata('company_id')))) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id, $company_id)) {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $this->db->update('warehouses_products', array('quantity' => $wh_balance_qty), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'company_id' => $company_id ?? $this->session->userdata('company_id')));
            } else {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $product = $this->site->getProductByID($product_id);
                $this->db->insert('warehouses_products', array('quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost, 'company_id' => $company_id ?? $this->session->userdata('company_id')));
            }
            $this->load->model('Curl_model', 'curl_');
            $this->curl_->get_EProduct($product_id);
            return true;
        }
        //        }
        return false;
    }

    public function syncProductQtyBooking($product_id, $warehouse_id, $company_id = null)
    {
        $balance_qty = $this->getBalanceQuantityBooking($product_id, null, $company_id);
        $wh_balance_qty = $this->getBalanceQuantityBooking($product_id, $warehouse_id, $company_id);

        if ($this->db->update('products', array('quantity_booking' => $balance_qty), array('id' => $product_id, 'company_id' => $company_id ?? $this->session->userdata('company_id')))) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $this->db->update('warehouses_products', array('quantity_booking' => $wh_balance_qty), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id));
            } else {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $product = $this->site->getProductByID($product_id);
                $this->db->insert('warehouses_products', array('quantity_booking' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost, 'company_id' => $company_id ?? $this->session->userdata('company_id')));
            }
            $this->load->model('Curl_model', 'curl_');
            $this->curl_->get_EProduct($product_id);
            return true;
        }
        return false;
    }

    public function getSaleByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalePayments($sale_id)
    {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function syncSalePayments($id)
    {
        $sale = $this->getSaleByID($id);
        $payments = $this->getSalePayments($id);
        $payments = !empty($payments) ? $payments : [];
        $paid = 0;
        $grand_total = $sale->grand_total + $sale->rounding;
        foreach ($payments as $payment) {
            $paid += $payment->amount;
        }

        $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
        if ($this->sma->formatDecimal($paid) >= $this->sma->formatDecimal($grand_total)) {
            $payment_status = 'paid';
        } elseif ($paid != 0) {
            $payment_status = 'partial';
        } elseif ($sale->due_date && $sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
            $payment_status = 'due';
        }

        if ($this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return false;
    }

    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasePayments($purchase_id)
    {
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function syncPurchasePayments($id)
    {
        $purchase = $this->getPurchaseByID($id);
        $payments = $this->getPurchasePayments($id);
        $paid = 0;
        foreach ($payments as $payment) {
            $paid += $payment->amount;
        }

        $payment_status = $paid <= 0 ? 'pending' : $purchase->payment_status;
        if ($this->sma->formatDecimal($purchase->grand_total) > $this->sma->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        } elseif ($this->sma->formatDecimal($purchase->grand_total) <= $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        }

        if ($this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return false;
    }

    private function getBalanceQuantity($product_id, $warehouse_id = null, $company_id = null)
    {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', false);
        $this->db->where('product_id', $product_id)
            ->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->db->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left');
            $this->db->where('warehouses.company_id', $company_id ?? $this->session->userdata('company_id'));
        }
        //        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_start()->where('status', 'returned')->or_where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    private function getBalanceQuantityBooking($product_id, $warehouse_id = null, $company_id = null)
    {
        $this->db->select('SUM(COALESCE(quantity_booking, 0)) as booking', false);
        $this->db->where('product_id', $product_id)
            ->where('quantity_booking !=', 0);
        if ($warehouse_id) {
            $this->db->where('sale_booking_items.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->db->join('warehouses', 'warehouses.id=sale_booking_items.warehouse_id', 'left');
            $this->db->where('warehouses.company_id', $company_id ?? $this->session->userdata('company_id'));
            $this->db->where('warehouses.is_deleted', null);
        }
        $this->db->join('sales', 'sales.id=sale_booking_items.sale_id', 'left');
        $this->db->where('sales.sale_status', 'reserved');
        $this->db->where('sales.is_deleted', null);
        $this->db->group_by('product_id');

        $q = $this->db->get('sale_booking_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->booking;
        }
        return 0;
    }

    private function getBalanceVariantQuantity($variant_id, $warehouse_id = null)
    {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', false);
        $this->db->where('option_id', $variant_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    public function calculateAVCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity)
    {
        $real_item_qty = $quantity;
        $wp_details = $this->getWarehouseProduct($warehouse_id, $product_id);
        if ($pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id)) {
            $cost_row = array();
            $quantity = $item_quantity;
            $balance_qty = $quantity;
            $avg_net_unit_cost = $wp_details->avg_cost;
            $avg_unit_cost = $wp_details->avg_cost;
            foreach ($pis as $pi) {
                if (!empty($pi) && $pi->quantity > 0 && $balance_qty <= $quantity && $quantity > 0) {
                    if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                    }
                }
                if (empty($cost_row)) {
                    break;
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        if ($quantity > 0 && !$this->Settings->overselling) {
            $this->session->set_flashdata('error', sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name)));
            redirect($_SERVER["HTTP_REFERER"]);
        } elseif ($quantity > 0) {
            $cost[] = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $wp_details->avg_cost, 'purchase_unit_cost' => $wp_details->avg_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => null, 'overselling' => 1, 'inventory' => 1);
            $cost[] = array('pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
        }
        return $cost;
    }

    public function calculateCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity)
    {
        $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id);

        $real_item_qty = $quantity;
        $quantity = $item_quantity;
        $balance_qty = $quantity;
        foreach ($pis as $pi) {
            $cost_row = null;
            if (!empty($pi) && $balance_qty <= $quantity && $quantity > 0) {
                $purchase_unit_cost = $pi->unit_cost ? $pi->unit_cost : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity));

                //item returned
                $qty_returned = $this->getPurchasedItem(['purchase_item_id' => $pi->id, 'status' => 'returned']);
                $qty_returned = $qty_returned ? abs($qty_returned->quantity_balance) : 0;

                if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                    $balance_qty = $pi->quantity_balance - $quantity + $qty_returned;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                    $quantity = 0;
                } elseif ($quantity > 0) {
                    $quantity = $quantity - $pi->quantity_balance;
                    $balance_qty = $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $qty_returned, 'inventory' => 1, 'option_id' => $option_id);
                }
            }
            $cost[] = $cost_row;
            if ($quantity == 0) {
                break;
            }
        }
        if ($quantity > 0) {
            $this->session->set_flashdata('error', sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name)));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        return $cost;
    }

    public function getPurchasedItems($product_id, $warehouse_id, $option_id = null)
    {
        // var_dump($product_id, $warehouse_id, $option_id);die;
        $orderby = ($this->Settings->accounting_method == 1) ? 'desc' : 'asc';
        $this->db->select('pi1.id, pi1.quantity, pi1.quantity_balance + coalesce(pi2.quantity_balance, 0) as quantity_balance, pi1.net_unit_cost, pi1.unit_cost, pi1.item_tax');
        $this->db->from('purchase_items pi1');
        $this->db->join("purchase_items pi2", "pi1.id = pi2.purchase_item_id", 'left');

        // $this->db->where('pi1.product_id', $product_id)->where('pi1.warehouse_id', $warehouse_id)->where('pi1.quantity_balance !=', 0);
        $this->db->where('pi1.product_id', $product_id)->where('pi1.warehouse_id', $warehouse_id)->where('pi1.quantity_balance + coalesce(pi2.quantity_balance, 0) !=', 0);

        if ($option_id) {
            $this->db->where('pi1.option_id', $option_id);
        }
        $this->db->group_start()->where('pi1.status', 'received')->or_where('pi1.status', 'partial')->group_end();
        $this->db->group_by('pi1.id');
        $this->db->order_by('pi1.date', $orderby);
        $this->db->order_by('pi1.purchase_id', $orderby);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductComboItems($pid, $warehouse_id = null)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, combo_items.unit_price as unit_price, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function item_costing($item, $pi = null, $company_id = null)
    {

        if ($company_id == null) {
            $company_id = $this->session->userdata('company_id');
        }

        $item_quantity = $pi ? $item['aquantity'] : $item['quantity'];
        if (!isset($item['option_id']) || empty($item['option_id']) || $item['option_id'] == 'null') {
            $item['option_id'] = null;
        }

        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {
            if ($this->getProductByID($item['product_id'], $company_id)) {
                if ($item['product_type'] == 'standard') {
                    $unit = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price'] = $this->convertToBase($unit, $item['unit_price']);
                    $cost = $this->calculateCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price = $combo_item->unit_price;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price = $combo_item->unit_price;
                        }
                        if ($pr->type == 'standard') {
                            $cost[] = $this->calculateCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, null, $item_quantity);
                        } else {
                            $cost[] = array(array('date' => date('Y-m-d'), 'product_id' => $pr->id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => ($combo_item->qty * $item['quantity']), 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $combo_item->unit_price, 'sale_unit_price' => $combo_item->unit_price, 'quantity_balance' => null, 'inventory' => null));
                        }
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 'inventory' => null));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 'inventory' => null));
            }
        } else {
            if ($this->getProductByID($item['product_id'], $company_id)) {
                if ($item['product_type'] == 'standard') {
                    $cost = $this->calculateAVCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $cost = $this->calculateAVCost($combo_item->id, $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], $item_quantity);
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 'inventory' => null));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 'inventory' => null));
            }
        }
        return $cost;
    }

    public function costing($items, $company_id = null)
    {
        if ($company_id == null) {
            $company_id = $this->session->userdata('company_id');
        }
        $citems = array();
        foreach ($items as $item) {
            if (!$item['flag'] || $item['flag'] == "undefined") {
                $pr = $this->getProductByID($item['product_id'], $company_id);
                if ($pr->type == 'standard') {
                    if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id']])) {
                        $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] += $item['quantity'];
                    } else {
                        $citems['p' . $item['product_id'] . 'o' . $item['option_id']] = $item;
                        $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] = $item['quantity'];
                    }
                } elseif ($pr->type == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            if (isset($citems['p' . $combo_item->id . 'o' . $item['option_id']])) {
                                $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] += ($combo_item->qty * $item['quantity']);
                            } else {
                                $cpr = $this->getProductByID($combo_item->id, $company_id);
                                if ($cpr->tax_rate) {
                                    $cpr_tax = $this->getTaxRateByID($cpr->tax_rate);
                                    if ($cpr->tax_method) {
                                        $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / (100 + $cpr_tax->rate));
                                        $net_unit_price = $combo_item->unit_price - $item_tax;
                                        $unit_price = $combo_item->unit_price;
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / 100);
                                        $net_unit_price = $combo_item->unit_price;
                                        $unit_price = $combo_item->unit_price + $item_tax;
                                    }
                                } else {
                                    $net_unit_price = $combo_item->unit_price;
                                    $unit_price = $combo_item->unit_price;
                                }
                                $cproduct = array('product_id' => $combo_item->id, 'product_name' => $cpr->name, 'product_type' => $combo_item->type, 'quantity' => ($combo_item->qty * $item['quantity']), 'net_unit_price' => $net_unit_price, 'unit_price' => $unit_price, 'warehouse_id' => $item['warehouse_id'], 'item_tax' => $item_tax, 'tax_rate_id' => $cpr->tax_rate, 'tax' => ($cpr_tax->type == 1 ? $cpr_tax->rate . '%' : $cpr_tax->rate), 'option_id' => null);
                                $citems['p' . $combo_item->id . 'o' . $item['option_id']] = $cproduct;
                                $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] = ($combo_item->qty * $item['quantity']);
                            }
                        }
                    }
                }
            }
        }
        // $this->sma->print_arrays($combo_items, $citems);
        $cost = array();
        foreach ($citems as $item) {
            $item['aquantity'] = $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'];
            $cost[] = $this->item_costing($item, true, $company_id);
        }
        return $cost;
    }

    public function syncQuantity($sale_id = null, $purchase_id = null, $oitems = null, $product_id = null, $company_id = null)
    {
        if ($sale_id) {
            $sale_items = $this->getAllSaleItems($sale_id);
            foreach ($sale_items as $item) {
                if ($item->product_type == 'standard') {
                    $this->syncProductQty($item->product_id, $item->warehouse_id, $company_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                } elseif ($item->product_type == 'combo') {
                    $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $this->syncProductQty($combo_item->id, $item->warehouse_id, $company_id);
                        }
                    }
                } elseif ($item->product_type == 'consignment') {
                    $this->syncConsignmentQty($item->product_id, $item->warehouse_id);
                }
            }
        } elseif ($purchase_id) {
            $purchase_items = $this->getAllPurchaseItems($purchase_id);
            foreach ($purchase_items as $item) {
                $this->syncProductQty($item->product_id, $item->warehouse_id, $company_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                }
            }
        } elseif ($oitems) {
            foreach ($oitems as $item) {
                if (isset($item->product_type)) {
                    if ($item->product_type == 'standard') {
                        $this->syncProductQty($item->product_id, $item->warehouse_id, $company_id);
                        if (isset($item->option_id) && !empty($item->option_id)) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->syncProductQty($combo_item->id, $item->warehouse_id, $company_id);
                            }
                        }
                    }
                } else {
                    $this->syncProductQty($item->product_id, $item->warehouse_id, $company_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                }
            }
        } elseif ($product_id) {
            $warehouses = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQty($product_id, $warehouse->id, $company_id);
                if ($product_variants = $this->getProductVariants($product_id)) {
                    foreach ($product_variants as $pv) {
                        $this->syncVariantQty($pv->id, $warehouse->id, $product_id);
                    }
                }
            }
        }
    }

    public function syncQuantityBooking($sale_id = null, $purchase_id = null, $oitems = null, $product_id = null)
    {
        if ($sale_id) {
            $sale_items = $this->getAllSaleItems($sale_id);
            foreach ($sale_items as $item) {
                if ($item->product_type == 'standard') {
                    $this->syncProductQtyBooking($item->product_id, $item->warehouse_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                } elseif ($item->product_type == 'combo') {
                    $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $this->syncProductQtyBooking($combo_item->id, $item->warehouse_id);
                        }
                    }
                } elseif ($item->product_type == 'consignment') {
                    $this->syncConsignmentQty($item->product_id, $item->warehouse_id);
                }
            }
        } elseif ($purchase_id) {
            $purchase_items = $this->getAllPurchaseItems($purchase_id);
            foreach ($purchase_items as $item) {
                $this->syncProductQtyBooking($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                }
            }
        } elseif ($oitems) {
            foreach ($oitems as $item) {
                if (isset($item->product_type)) {
                    if ($item->product_type == 'standard') {
                        $this->syncProductQtyBooking($item->product_id, $item->warehouse_id);
                        if (isset($item->option_id) && !empty($item->option_id)) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->syncProductQtyBooking($combo_item->id, $item->warehouse_id);
                            }
                        }
                    }
                } else {
                    $this->syncProductQtyBooking($item->product_id, $item->warehouse_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                }
            }
        } elseif ($product_id) {
            $warehouses = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQtyBooking($product_id, $warehouse->id);
                if ($product_variants = $this->getProductVariants($product_id)) {
                    foreach ($product_variants as $pv) {
                        $this->syncVariantQty($pv->id, $warehouse->id, $product_id);
                    }
                }
            }
        }
    }

    public function getProductVariants($product_id)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSaleItems($sale_id)
    {
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function syncPurchaseItems($data = array())
    {
        if (!empty($data)) {
            foreach ($data as $items) {
                foreach ($items as $item) {
                    if (isset($item['pi_overselling'])) {
                        unset($item['pi_overselling']);
                        $option_id = (isset($item['option_id']) && !empty($item['option_id'])) ? $item['option_id'] : null;
                        $clause = array('purchase_id' => null, 'transfer_id' => null, 'product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'option_id' => $option_id);
                        if ($pi = $this->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + $item['quantity_balance'];
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                        } else {
                            $clause['quantity'] = 0;
                            $clause['item_tax'] = 0;
                            $clause['quantity_balance'] = $item['quantity_balance'];
                            $clause['status'] = 'received';
                            $this->db->insert('purchase_items', $clause);
                        }
                    } else {
                        if ($item['inventory']) {
                            $this->db->update('purchase_items', array('quantity_balance' => $item['quantity_balance']), array('id' => $item['purchase_item_id']));
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function check_customer_deposit($customer_id, $amount)
    {
        $customer = $this->getCompanyByID($customer_id);
        return $customer->deposit_amount >= $amount;
    }

    public function getWarehouseProduct($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllBaseUnits()
    {
        if (!$this->Owner) {
            $this->db->where("( client_id = " . $this->session->userdata('company_id') . " or client_id = 1 )");
        }
        $q = $this->db->get_where("units", array('base_unit' => null));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitsByBUID($base_unit)
    {
        $this->db->select("{$this->db->dbprefix('units')}.id as id, {$this->db->dbprefix('units')}.code, {$this->db->dbprefix('units')}.name, b.name as base_unit, {$this->db->dbprefix('units')}.operator, {$this->db->dbprefix('units')}.operation_value", false);
        $this->db->join("units b", 'b.id=units.base_unit', 'left');
        $this->db->where("(units.client_id=1 OR units.client_id={$this->session->userdata('company_id')})");
        // $this->db->where('id', $base_unit)->or_where('base_unit', $base_unit)->or_where('client_id', $this->session->userdata('company_id'));
        $q = $this->db->get("units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitByID($id)
    {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPriceGroups($company_id = null, $filter = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        if ($filter) {
            $this->db->where($filter);
        }
        $q = $this->db->get_where('price_groups', array('is_deleted' => null));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getPriceGroupByID($id)
    {
        $q = $this->db->get_where('price_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductGroupPrice($product_id, $group_id)
    {
        $q = $this->db->get_where('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductPrices($pg_id)
    {
        $q = $this->db->get_where('product_prices', array('price_group_id' => $pg_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAllBrands()
    {
        $this->db->order_by("id", "asc");
        if (!$this->Owner) {
            $this->db->where("( client_id = " . $this->session->userdata('company_id') . " or client_id = 1 )");
        }
        $q = $this->db->get("brands");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPosBrands()
    {
        if (!$this->Owner) {
            $this->db->where('products.company_id', $this->session->userdata('company_id'));
        }
        $this->db->select('brands.*');
        $this->db->join('products', 'brands.id = products.brand', 'left');
        $this->db->group_by('products.brand');
        $q = $this->db->get("brands");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBrandByID($id)
    {
        $q = $this->db->get_where('brands', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBankByID($id)
    {
        $q = $this->db->get_where('bank', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBankByName($name, $company_id)
    {
        $name = strtolower($name);
        $q = $this->db->get_where('bank', array('bank_name' => $name, 'company_id' => $company_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBanks($company_id = null, $filter = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }

        if ($filter) {
            $this->db->where($filter);
        }

        $q = $this->db->get_where('bank', '(is_deleted is null OR is_deleted = 0)');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function findThirdPartyBankByCompanyId($company_id)
    {
        $q = $this->db->get_where('bank', array('company_id' => $company_id, 'is_third_party' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function convertToBase($unit, $value)
    {
        switch ($unit->operator) {
            case '*':
                return $value / $unit->operation_value;
                break;
            case '/':
                return $value * $unit->operation_value;
                break;
            case '+':
                return $value - $unit->operation_value;
                break;
            case '-':
                return $value + $unit->operation_value;
                break;
            default:
                return $value;
        }
    }

    public function getConsignmentByID($id)
    {
        $this->db->where('id', $id);
        $q = $this->db->get('consignment');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getQuantityItems($product_id, $warehouse_id = null)
    {
        $this->db->select("COALESCE(SUM({$this->db->dbprefix('consignment_items')}.quantity),0) as result")
            ->join('consignment', 'consignment_items.consignment_id=consignment.id', 'left')
            ->where("consignment_items.product_id", $product_id)
            ->where("consignment_items.warehouse_id", $warehouse_id)->where("consignment.company_id", $this->session->userdata('company_id'));

        $q = $this->db->get('consignment_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->result;
        }
        return 0;
    }

    public function getBalanceConsignment($product_id, $warehouse_id)
    {
        $this->db->select("SUM(COALESCE(" . $this->db->dbprefix('sale_items') . ".quantity,0)) as total")
            ->join('sales s', 'sale_items.sale_id=s.id', 'left')
            ->where('s.company_id', $this->session->userdata('company_id'))
            ->where('sale_items.flag', 1)
            ->where('sale_items.product_id', $product_id)
            ->where('sale_items.warehouse_id', $warehouse_id)
            ->where('sale_items.quantity !=', 0);
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->total;
        }
        return 0;
    }

    public function syncConsignmentQty($product_id, $warehouse_id)
    {
        $qty_consignment = $this->getQuantityItems($product_id, $warehouse_id);
        $balance_cons_qty = $this->getBalanceConsignment($product_id, $warehouse_id);

        if ($this->db->update('consignment_products', array('quantity' => $qty_consignment - $balance_cons_qty), array('company_id' => $this->session->userdata('company_id'), 'warehouse_id' => $warehouse_id, 'product_id' => $product_id))) {
            return true;
        }
        return false;
    }

    public function getShippingChargesData($term)
    {
        $this->db->group_start()->where('min_distance <=', $term)->where('max_distance >=', $term)->group_end();
        $this->db->where('company_id', $this->session->userdata('company_id'));
        $this->db->limit(1);
        $q = $this->db->get('shipping_charges');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getConsignmentQuantity($product_id, $warehouse_id)
    {
        $this->db->select('COALESCE(quantity,0) as stock');
        $this->db->where('product_id', $product_id)
            ->where('warehouse_id', $warehouse_id)->where('is_deleted', null);

        $q = $this->db->get('consignment_products');
        if ($q->num_rows() > 0) {
            return $q->row()->stock;
        }
    }

    public function syncConsignmentPayments($id)
    {
        $consignment = $this->getConsignmentByID($id);
        $payments = $this->getConsignmentPayments($id);
        $paid = 0;
        foreach ($payments as $payment) {
            $paid += $payment->amount;
        }

        $payment_status = $paid <= 0 ? 'pending' : $consignment->payment_status;
        if ($this->sma->formatDecimal($consignment->total) > $this->sma->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        } elseif ($this->sma->formatDecimal($consignment->total) <= $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        }

        if ($this->db->update('consignment', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return false;
    }

    public function getConsignmentPayments($id)
    {
        $q = $this->db->get_where('payments', array('consignment_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPoints()
    {
        $q = $this->db->get_where('points', array('company_id' => $this->session->userdata('company_id'), 'is_deleted' => null));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPlanPricingByID($id)
    {
        $q = $this->db->get_where('plans', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllUnits()
    {
        $q = $this->db->get_where('units', array('client_id' => $this->session->userdata('company_id')));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllExpenseCategories()
    {
        $q = $this->db->get_where('expense_categories', array('client_id' => $this->session->userdata('company_id')));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllShippingCharges()
    {
        $q = $this->db->get_where('shipping_charges', array('company_id' => $this->session->userdata('company_id')));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addGuide()
    {
        if ($this->db->insert('guide', array('user_id' => $this->session->userdata('user_id')))) {
            return true;
        }
        return false;
    }

    public function getGuide()
    {
        $q = $this->db->get_where('guide', array('user_id' => $this->session->userdata('user_id')), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function finishGuide($field)
    {
        if ($this->db->update('guide', array($field => 1), array('user_id' => $this->session->userdata('user_id')))) {
            return true;
        }
        return false;
    }

    public function resetGuide()
    {
        if ($this->db->update('guide', array('sales-add' => null, 'purchases-add' => null, 'customers-add' => null, 'products-add' => null), array('user_id' => $this->session->userdata('user_id')))) {
            return true;
        }
        return false;
    }

    public function get_bpartner()
    {
        $this->load->model('Official_model');
        $partner = $this->Official_model->getAllParnerNumber();
        $id_bpartner = $this->Official_model->getParnerNumberbyID(key($partner));
        $check = array('reference_code_1' => $id_bpartner[0], 'supplier_id' => key($partner));
        $data = $this->Official_model->check_to_partner($check);
        if ($data['codestatus'] == 'S') {
            $name = $data['resultdata'][0]['name'];
            $this->db->update('users', array('first_name' => $name), array('company_id' => $this->session->userdata('company_id')));
            $this->db->update('companies', array('name' => $name, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $this->session->userdata('company_id')));
        }
    }

    public function getBillInvByID($id)
    {
        $q = $this->db->get_where('billing_invoices', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBillingInvItem($billing_id)
    {
        $q = $this->db->get_where('billing_invoice_items', array('billing_invoice_id' => $billing_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function send_wa_otp($number, $message)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "wabox_send_wa",
        ], 1);

        if ($q->num_rows() == 0) {
            return false;
        }

        $q = $q->row();

        if ($number[0] == '0') {
            $number = substr($number, 1);
        } elseif ($number[0] == '6' && $number[1] == '2') {
            $number = substr($number, 2);
        }
        if (strlen($number) >= 9) {
            $number = '62' . $number;
            ob_start();

            $dataQuery = http_build_query([
                "token" => $q->token, //"5d785ffca3658d2a1072156a0cc311a95c3eb2c311be9",
                "uid" => $q->username, //'628116065246',
                "to" => $number,
                "custom_uid" => uniqid('aksestoko_'),
                "text" => $message
            ]);

            $server = $q->uri; //"https://www.waboxapp.com/api/send/chat";
            $urlendpoint = "$server?$dataQuery";
            // echo (FCPATH."assets/certificate/cacert.pem");die;

            $curlHandle = curl_init($urlendpoint);

            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

            curl_setopt($curlHandle, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
            curl_setopt($curlHandle, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");

            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            $respon = curl_exec($curlHandle);

            if (curl_error($curlHandle)) {
                throw new \Exception(curl_error($curlHandle));
            }
            // var_dump($respon);

            curl_close($curlHandle);
            // header('Content-Type: application/json');
            // echo $respon;
            // die;

            return true;
        } else {
            return false;
        }
    }

    public function send_wa_otp_wablas($number, $message, $required = false)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "wablas_send_wa",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak dapat menemukan API wablas_send_wa.");
        }

        $q = $q->row();

        if ($number[0] == '0') {
            $number = substr($number, 1);
        } elseif ($number[0] == '6' && $number[1] == '2') {
            $number = substr($number, 2);
        }
        if (strlen($number) >= 9) {
            $number = '62' . $number;

            ob_start();
            // setting
            $apikey      = $q->token; // api key "Fhkhb4t9MRxt26YIHo3L9mYpR0NDflSKYK2dqyhAsHQGVomgIKlxhqS2WP6CwoRQ"
            $urlendpoint = $q->uri; // url endpoint api "https://sambi.wablas.com/api/send-message"

            $senddata = [
                'phone' => trim($number),
                'message' => $message
            ];

            $headers = [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: ' . trim($apikey)
            ];

            $data = http_build_query($senddata);
            $curlHandle = curl_init($urlendpoint);
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $curlHandle,
                CURLOPT_HTTPHEADER,
                $headers
            );
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            $respon = curl_exec($curlHandle);

            //START - Insert Log WA
            $this->load->model('integration_model', 'integration');
            $data_log = [
                'method' => "POST",
                'url' => $urlendpoint,
                'headers' => json_encode(oneToTwoDArray($headers)),
                'body' => json_encode($senddata),
                'parameters' => null,
                'io_type' => 'out',
                'ssl_status' => true,
                'response' => json_encode($respon),
                'note' => curl_error($curlHandle)
            ];
            $this->integration->insertApiLog($data_log);
            //END - Insert Log WA

            if (curl_error($curlHandle)) {
                throw new \Exception(curl_error($curlHandle));
            }

            if ($required) {
                $respon = json_decode($respon);
                if (!$respon) {
                    throw new \Exception("Gagal mengirimkan whatapps kode aktivasi.");
                } else if ($respon->status != true) {
                    throw new \Exception("Gagal mengirimkan whatapps kode aktivasi. Pesan error : " . $respon->message);
                }
            }

            curl_close($curlHandle);

            return true;
        } else {
            throw new \Exception("Nomor tidak valid.");
        }
        return false;
    }

    public function send_sms_otp($number, $message, $required = false, $type = 'otp')
    {
        if (SMS_SERVER == 'rajasms') {
            return $this->send_sms_otp_rajasms($number, $message, $required);
        } elseif (SMS_SERVER == 'medansms') {
            return $this->send_sms_otp_medansms($number, $message, $required);
        } elseif (SMS_SERVER == 'wablas') {
            return $this->send_sms_otp_wablas($number, $message, $required);
        } elseif (SMS_SERVER == 'medansms_masking') {
            return $this->send_sms_otp_medansms_masking($number, $message, $required, $type);
        } else {
            return false;
        }
    }


    public function send_sms_otp_rajasms($number, $message, $required = false)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "rajasms_send_sms",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak dapat menemukan API rajasms_send_sms.");
        }

        $q = $q->row();

        if ($number[0] == '0') {
            $number = substr($number, 1);
        } elseif ($number[0] == '6' && $number[1] == '2') {
            $number = substr($number, 2);
        }
        if (strlen($number) >= 9) {
            $number = '62' . $number;

            ob_start();
            // setting
            $apikey      = $q->token; // api key
            $urlendpoint = $q->uri; // url endpoint api
            $callbackurl = ''; // url callback get status sms

            $senddata = [
                'apikey' => $apikey,
                'callbackurl' => $callbackurl,
                'datapacket' => []
            ];

            $senddata['datapacket'][] = [
                'number' => trim($number),
                'message' => $message
            ];
            
            $data = json_encode($senddata);

            $headers = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ];

            $curlHandle = curl_init($urlendpoint);
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $curlHandle,
                CURLOPT_HTTPHEADER,
                $headers
            );
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            $respon = curl_exec($curlHandle);

            //START - Insert Log SMS
            $this->load->model('integration_model', 'integration');
            $data_log = [
                'method' => "POST",
                'url' => $urlendpoint,
                'headers' => json_encode(oneToTwoDArray($headers)),
                'body' => json_encode($senddata),
                'parameters' => null,
                'io_type' => 'out',
                'ssl_status' => null,
                'response' => json_encode($respon),
                'note' => curl_error($curlHandle)
            ];
            $this->integration->insertApiLog($data_log);
            //END - Insert Log SMS

            if (curl_error($curlHandle)) {
                throw new \Exception(curl_error($curlHandle));
            }

            if ($required) {
                $respon = json_decode($respon);
                if (!$respon) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi.");
                } else if ($respon->sending_respon[0]->globalstatus != 10) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi. Pesan error : " . $respon->sending_respon[0]->globalstatustext);
                } else if ($respon->sending_respon[0]->datapacket[0]->packet->sendingstatus != 10) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi. Pesan error : " . $respon->sending_respon[0]->datapacket[0]->packet->sendingstatustext);
                }
            }

            curl_close($curlHandle);

            return true;
        } else {
            throw new \Exception("Nomor tidak valid.");
        }
        return false;
    }

    public function send_sms_otp_wablas($number, $message, $required = false)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "wablas_send_sms",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak dapat menemukan API wablas_send_sms.");
        }

        $q = $q->row();

        if ($number[0] == '0') {
            $number = substr($number, 1);
        } elseif ($number[0] == '6' && $number[1] == '2') {
            $number = substr($number, 2);
        }
        if (strlen($number) >= 9) {
            $number = '62' . $number;

            ob_start();
            // setting
            $apikey      = $q->token; // api key
            $urlendpoint = $q->uri; // url endpoint api

            $senddata = [
                'phone' => trim($number),
                'message' => $message
            ];

            $data = http_build_query($senddata);

            $headers = [
                'Authorization: ' . $apikey,
                'Content-Length: ' . strlen($data)
            ];

            $curlHandle = curl_init($urlendpoint);
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $curlHandle,
                CURLOPT_HTTPHEADER,
                $headers
            );
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            $respon = curl_exec($curlHandle);

            //START - Insert Log SMS
            $this->load->model('integration_model', 'integration');
            $data_log = [
                'method' => "POST",
                'url' => $urlendpoint,
                'headers' => json_encode(oneToTwoDArray($headers)),
                'body' => json_encode($senddata),
                'parameters' => null,
                'io_type' => 'out',
                'ssl_status' => 1,
                'response' => json_encode($respon),
                'note' => curl_error($curlHandle)
            ];
            $this->integration->insertApiLog($data_log);
            //END - Insert Log SMS

            if (curl_error($curlHandle)) {
                throw new \Exception(curl_error($curlHandle));
            }

            if ($required) {
                $respon = json_decode($respon);
                if (!$respon) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi.");
                } else if ($respon->status != true) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi. Pesan error : " . $respon->message);
                }
            }

            curl_close($curlHandle);

            return true;
        } else {
            throw new \Exception("Nomor tidak valid.");
        }
        return false;
    }

    public function send_sms_otp_medansms($number, $message, $required = false)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "medansms_send_sms",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak dapat menemukan API medansms_send_sms.");
        }

        $q = $q->row();

        if ($number[0] == '0') {
            $number = substr($number, 1);
        } elseif ($number[0] == '6' && $number[1] == '2') {
            $number = substr($number, 2);
        }
        if (strlen($number) >= 9) {
            $number = '62' . $number;

            ob_start();
            // setting
            $query = [
                "action" => "kirim_sms",
                "email" => $q->username,
                "passkey" => $q->token,
                "no_tujuan" => $number,
                "pesan" => $message,
                "json" => "1"
            ];
            $dataQuery = http_build_query($query);

            $server = $q->uri;
            $urlendpoint = "$server?$dataQuery";

            $curlHandle = curl_init($urlendpoint);

            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

            curl_setopt($curlHandle, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
            curl_setopt($curlHandle, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");

            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            $respon = json_decode(curl_exec($curlHandle));

            //START - Insert Log SMS
            $this->load->model('integration_model', 'integration');
            $data_log = [
                'method' => "GET",
                'url' => $server,
                'headers' => null,
                'body' => null,
                'parameters' => json_encode($query),
                'io_type' => 'out',
                'ssl_status' => true,
                'response' => json_encode($respon),
                'note' => curl_error($curlHandle)
            ];
            $this->integration->insertApiLog($data_log);
            //END - Insert Log SMS

            if (curl_error($curlHandle)) {
                throw new \Exception(curl_error($curlHandle));
            }

            if ($required) {
                if (!$respon) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi.");
                } else if ($respon[0]->status != 1) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi. Pesan error : " . $respon[0]->keterangan);
                }
            }

            curl_close($curlHandle);

            return true;
        } else {
            throw new \Exception("Nomor tidak valid.");
        }
        return false;
    }

    public function send_sms_otp_medansms_masking($number, $message, $required = false, $type = 'otp')
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "medansms_send_sms_masking_$type",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak dapat menemukan API medansms_send_sms_masking_$type.");
        }

        $q = $q->row();

        if ($number[0] == '0') {
            $number = substr($number, 1);
        } elseif ($number[0] == '6' && $number[1] == '2') {
            $number = substr($number, 2);
        }
        if (strlen($number) >= 9) {
            $number = '62' . $number;

            ob_start();
            // setting
            $query = [
                "action" => "kirim_sms",
                "email" => $q->username,
                "passkey" => $q->token,
                "no_tujuan" => $number,
                "pesan" => $message,
                "json" => "1"
            ];
            $dataQuery = http_build_query($query);

            $server = $q->uri;
            $urlendpoint = "$server?$dataQuery";

            $curlHandle = curl_init($urlendpoint);

            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

            curl_setopt($curlHandle, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
            curl_setopt($curlHandle, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");

            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            $respon = json_decode(curl_exec($curlHandle));

            //START - Insert Log SMS
            $this->load->model('integration_model', 'integration');
            $data_log = [
                'method' => "GET",
                'url' => $server,
                'headers' => null,
                'body' => null,
                'parameters' => json_encode($query),
                'io_type' => 'out',
                'ssl_status' => true,
                'response' => json_encode($respon),
                'note' => curl_error($curlHandle)
            ];
            $this->integration->insertApiLog($data_log);
            //END - Insert Log SMS

            if (curl_error($curlHandle)) {
                throw new \Exception(curl_error($curlHandle));
            }

            if ($required) {
                if (!$respon) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi.");
                } else if ($respon[0]->status != 1) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi. Pesan error : " . $respon[0]->keterangan);
                }
            }

            curl_close($curlHandle);

            return true;
        } else {
            throw new \Exception("Nomor tidak valid.");
        }
        return false;
    }

    public function send_sms_notif_medansms_masking($number, $message, $required = false)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "medansms_send_sms_masking_notif",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak dapat menemukan API medansms_send_sms_masking_notif.");
        }

        $q = $q->row();

        if ($number[0] == '0') {
            $number = substr($number, 1);
        } elseif ($number[0] == '6' && $number[1] == '2') {
            $number = substr($number, 2);
        }
        if (strlen($number) >= 9) {
            $number = '62' . $number;

            ob_start();
            // setting
            $query = [
                "action" => "kirim_sms",
                "email" => $q->username,
                "passkey" => $q->token,
                "no_tujuan" => $number,
                "pesan" => $message,
                "json" => "1"
            ];
            $dataQuery = http_build_query($query);

            $server = $q->uri;
            $urlendpoint = "$server?$dataQuery";

            $curlHandle = curl_init($urlendpoint);

            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

            curl_setopt($curlHandle, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
            curl_setopt($curlHandle, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");

            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            $respon = json_decode(curl_exec($curlHandle));

            //START - Insert Log SMS
            $this->load->model('integration_model', 'integration');
            $data_log = [
                'method' => "GET",
                'url' => $server,
                'headers' => null,
                'body' => null,
                'parameters' => json_encode($query),
                'io_type' => 'out',
                'ssl_status' => true,
                'response' => json_encode($respon),
                'note' => curl_error($curlHandle)
            ];
            $this->integration->insertApiLog($data_log);
            //END - Insert Log SMS

            if (curl_error($curlHandle)) {
                throw new \Exception(curl_error($curlHandle));
            }

            if ($required) {
                if (!$respon) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi.");
                } else if ($respon[0]->status != 1) {
                    throw new \Exception("Gagal mengirimkan sms kode aktivasi. Pesan error : " . $respon[0]->keterangan);
                }
            }

            curl_close($curlHandle);

            return true;
        } else {
            throw new \Exception("Nomor tidak valid.");
        }
        return false;
    }

    public function uploadImage($image)
    {
        //API URL
        $url = "https://api.imgbb.com/1/upload";

        //create a new cURL resource
        $ch = curl_init($url);

        //setup request to send json via POST
        $data = [
            'key' => "96d7d997fd7063f7948de94db9467e85",
            'image' => $image,
        ];
        // var_dump($data);die;
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $payload = http_build_query($data);

        // var_dump($payload);die;

        //attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute the POST request
        $result = curl_exec($ch);

        if (curl_error($ch)) {
            throw new \Exception(curl_error($ch));
        }

        //close cURL resource
        curl_close($ch);

        return $result;
    }

    public function getCompaniesAddress($company_id)
    {
        $q = $this->db->get_where('companies', ['company_id' => $company_id, 'group_name' => 'address', 'is_deleted' => null]);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function findWarehouseByCode($code, $company_id)
    {
        $q = $this->db->get_where('warehouses', ['company_id' => $company_id, 'code' => $code, 'is_deleted' => null]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return [];
    }

    public function getAllWarehousesProduct($warehouse_id)
    {
        $this->db->select('products.code,warehouses_products.*');
        $this->db->join('products', 'products.id = warehouses_products.product_id', 'left');
        $this->db->where('warehouse_id', $warehouse_id);
        $q = $this->db->get('warehouses_products');
        // var_dump($this->db->error());die;
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function findPromotionByPurchaseId($purchase_id) // dari/untuk aksestoko
    {
        $this->db->select('promo.*');
        $this->db->join('promo', 'promo.id = transaction_promo.promo_id');
        $this->db->where('transaction_promo.purchase_id', $purchase_id);
        $q = $this->db->get('transaction_promo', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function insertLogActivities($data)
    {
        if (!$this->db->insert('sma_log_activities', $data))
            return false;
        return true;
    }

    public function getShipmentProductPriceByShipmentPriceGroupId($ShipmentPriceGroupId)
    {

        $q = $this->db->get_where('shipment_product_price', array('shipment_price_group_id' => $ShipmentPriceGroupId));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getShipmentProductPriceByShipmentPriceGroupIdAndProductId($ShipmentPriceGroupId, $productId)
    {
        $this->db->where('product_id', $productId);
        $q = $this->db->get_where('shipment_product_price', array('shipment_price_group_id' => $ShipmentPriceGroupId), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseInCosting($purchase_id)
    {
        $purchase_items = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        $purchase_items = $purchase_items->num_rows() > 0 ? $purchase_items->result() : [];
        $purchase_items_id = [];
        foreach ($purchase_items as $i => $purchase_item) {
            $purchase_items_id[] = $purchase_item->id;
        }

        $this->db->where_in('purchase_item_id', $purchase_items_id);
        $q = $this->db->get('costing');

        return $q->num_rows() > 0 ? $q->result() : false;
    }

    public function getUnsentQtybySalesId($sale_id)
    {
        $this->db->select('(sum(quantity)-sum(sent_quantity)) as total_unsent')->where('sale_id', $sale_id);
        $q =  $this->db->get('sale_items');

        if ($q->num_rows() > 0) {
            return $q->row()->total_unsent;
        }
        return [];
    }
    public function getPendingApprovalDelivery($sale_id, $AT)
    {
        $query = $this->db->query("SELECT * FROM `sma_deliveries` sd WHERE ((((sd.status = 'packing' OR sd.status = 'delivering') OR (sd.status = 'delivered' AND (SELECT COUNT(*) FROM sma_delivery_items sdi WHERE sdi.bad_quantity > 0 AND sdi.delivery_id = sd.id) > 0) AND (sd.`return_reference_no` IS NULL OR sd.`return_reference_no` = ''))) AND (sd.`is_reject` > 0 AND sd.`is_reject` < 3)) AND sd.sale_id = '$sale_id'");
        $query_qty = $this->db->query("SELECT quantity_ordered, SUM(quantity_sent) as quantity_sent FROM sma_delivery_items WHERE sale_id = '$sale_id' GROUP BY product_id")->result();
        if ($query->num_rows() <= 0) {
            foreach ($query_qty as $q) {
                if ((int) $query_qty->quantity_sent < (int) $query_qty->quantity_ordered) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    public function getPurchasedItemsBySaleId($sales_id)
    {
        if ($sales_id) {
            $query = "SELECT sum(sma_purchase_items.quantity) as quantity, 
                      sum(sma_purchase_items.good_quantity) as good_quantity , 
                      sum(sma_purchase_items.bad_quantity) as bad_quantity
                      FROM sma_purchase_items
                      LEFT JOIN sma_purchases ON sma_purchase_items.purchase_id = sma_purchases.id 
                      LEFT JOIN sma_sales ON sma_sales.reference_no = sma_purchases.cf1 
                      AND sma_sales.company_id = sma_purchases.supplier_id WHERE sma_sales.id = '$sales_id'";
            $q = $this->db->query($query);
            return $q->row();
        }
        return false;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getSalesPersonByRefNo($ref)
    {
        $q = $this->db->get_where('sales_person', array('reference_no' => $ref, 'is_deleted' => NULL, 'is_active' => 1), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSalesPersonByRefId($ref)
    {
        $q = $this->db->get_where('sales_person', array('reference_no' => $ref, 'is_deleted' => NULL, 'is_active' => 1), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllDistributor()
    {
        $this->db->where('client_id IS NULL');
        $q = $this->db->get_where('companies', array('group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDeliveryItemsByDeliveryId($delivery_id)
    {
        $q = $this->db->get_where('delivery_items', array('delivery_id' => $delivery_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function checkAutoClose($sale_id)
    {
        $item       = $this->getPurchasedItemsBySaleId($sale_id);
        $deliveries = $this->getDeliveryBySaleID($sale_id);
        if ($item->quantity == ($item->good_quantity + $item->bad_quantity)) {
            $qtydelivery = 0;
            $kondisireject = 0;

            foreach ($deliveries as $i => $delivery) {
                $kondisi_konfirmasi = true;
                $deliveryItems = $this->getDeliveryItemsByDeliveryId($delivery->id);

                /*
                    untuk pengecekan ketika ada delivery berstatus packing atau delivering
                    tidak dilakukan close. untuk status returned tetap dibiarkan hal ini dilakukan
                    agar qty sent dari do yang return juga ikut dihitung ke $qtydelivery. 
                    qty sent dari do yang return bernilai negatif
                */

                if ($delivery->status == 'packing' || $delivery->status == 'delivering') {
                    continue;
                }

                /*
                    - untuk pengecekan ketika telah dilakukan reject sebanyak 2 kali maka akan diclose 
                    - dan ketika delivery yang direject sebanyak 2 kali tersebut adalah delivery terakhir,
                      hal ini dilakukan untuk mengatasi ketika ada >1 delivery.
                */

                if ($delivery->is_reject == 3 && $i == count($deliveries) - 1) {
                    return true;
                }

                foreach ($deliveryItems as $deliveryItem) {
                    $qtydelivery += $deliveryItem->quantity_sent;

                    /*
                        - untuk pengecekan ketika delivery item memiliki bad > 0 dan kondisinya
                          belum di konfirmasi (belum pilih approve atau reject)
                    */

                    if (
                        $deliveryItem->bad_quantity > 0 && !$delivery->is_reject
                        && !$delivery->is_approval  && !$delivery->is_confirm
                    ) {
                        $kondisi_konfirmasi = false;
                    }


                    /*
                        - untuk pengecekan ketika delivery item memiliki bad > 0 dan kondisinya direject pertama
                          dan belum dikonfirmasi oleh toko
                     
                     */
                    if (
                        $deliveryItem->bad_quantity > 0 &&
                        ($delivery->is_reject >= 1 && $delivery->is_reject <= 2)
                    ) {
                        $kondisi_konfirmasi = false;
                    }
                }

                if ($item->quantity == $qtydelivery && $kondisi_konfirmasi && $i == count($deliveries) - 1) {
                    return true;
                }
            }
        }

        return false;
    }


    public function getAllSalesPerson()
    {
        // $this->db->where('client_id', '!= \'aksestoko\'');
        $q = $this->db->get_where('sales_person');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPaymentMethod()
    {
        $q = $this->db->get('payment_methods');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDocuments($id = null)
    {
        if ($id) $this->db->where('id', $id);
        $this->db->where('is_deleted is null');
        $q = $this->db->get('documents');
        if ($q && $q->num_rows() > 0) {
            return $id ? $q->row() : $q->result();
        }
        return false;
    }

    function insertOrUpdateDocuments(array $data)
    {
        $document = null;
        $this->db->where('is_deleted is null');
        $this->db->where('filename', $data['filename']);
        $q = $this->db->get('documents');
        if ($q && $q->num_rows() > 0) {
            $document = $q->row();
        }
        if ($document) {
            $update = $this->db->update('documents', [
                'name' => $data['name'],
                'filename' => $data['filename'],
                'url' => $data['url'],
                'updated_at' => date("Y-m-d H:i:s"),
                'filesize' => isset($data['size']) ? $data['size'] : filesize(FCPATH . $data['url']),
            ], [
                'id' => $document->id,
            ]);

            if (!$update) {
                throw new Exception("Gagal memperbarui document");
            }
        } else {
            $insert = $this->db->insert('documents', [
                'name' => $data['name'],
                'filename' => $data['filename'],
                'url' => $data['url'],
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
                'filesize' => filesize(FCPATH . $data['url']),
            ]);

            if (!$insert) {
                throw new Exception("Gagal menambahkan document");
            }
        }
        return true;
    }
    public function syncSalePaymentsAT($sale_id, $purchase_id)
    {
        $sale = $this->getSaleByID($sale_id);
        $payments = $this->getSalePayments($sale_id);
        $payments = !empty($payments) ? $payments : [];
        $paid = 0;
        $grand_total = $sale->grand_total + $sale->rounding;
        foreach ($payments as $payment) {
            $paid += $payment->amount;
        }

        $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
        if ($grand_total <= 0) {
            $payment_status = 'paid';
        } else if ($this->sma->formatDecimal($paid) >= $this->sma->formatDecimal($grand_total)) {
            $payment_status = 'paid';
        } elseif ($paid != 0) {
            $payment_status = 'partial';
        } elseif ($sale->due_date && $sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
            $payment_status = 'due';
        }

        if ($this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'paid' => $paid, 'payment_status' => $payment_status), array('id' => $purchase_id))) {
            return true;
        }

        return false;
    }

    public function shorten_link_cuttly($link)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "cuttly_api",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak ditemukan cuttly_api");
        }

        $q = $q->row();

        ob_start();

        $dataQuery = http_build_query([
            "key" => $q->token, //"d68ec3f7a786b7ae1954c73b68cf695a9b96e",
            "short" => ($link),
        ]);

        $server = $q->uri; //"https://cutt.ly/api/api.php";
        $urlendpoint = "$server?$dataQuery";

        // var_dump($urlendpoint);die;

        $curlHandle = curl_init($urlendpoint);

        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

        curl_setopt($curlHandle, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
        curl_setopt($curlHandle, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");

        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
        $respon = json_decode(curl_exec($curlHandle));

        if (curl_error($curlHandle)) {
            throw new \Exception(curl_error($curlHandle));
        }
        curl_close($curlHandle);

        if ($respon->url->status != 7) {
            throw new \Exception("status : " . $respon->url->status);
        }

        return $respon;
    }

    public function shorten_link_bitly($link)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "bitly_shorten_api",
        ], 1);

        if ($q->num_rows() == 0) {
            throw new \Exception("Tidak ditemukan bitly_shorten_api");
        }

        $q = $q->row();

        ob_start();

        $senddata = [
            'domain' => 'bit.ly',
            'long_url' => $link,
        ];

        $data = json_encode($senddata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES);

        $curlHandle = curl_init($q->uri);

        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

        curl_setopt($curlHandle, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
        curl_setopt($curlHandle, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");

        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $curlHandle,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
                'Authorization: Bearer ' . $q->token
            ]
        );
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
        $respon = json_decode(curl_exec($curlHandle));

        if (curl_error($curlHandle)) {
            throw new \Exception(curl_error($curlHandle));
        }

        curl_close($curlHandle);

        if (!property_exists($respon, 'id')) {
            throw new \Exception("Error message : " . $respon->message);
        }

        return $respon;
    }

    public function isUuidExist($uuid, $table_name)
    {
        $table_name = $this->db->dbprefix($table_name);
        $this->db->select('uuid');
        $this->db->where('uuid', $uuid);
        $q = $this->db->get($table_name);
        if ($q && $q->num_rows() > 0) {
            return $q = $q->row();
        }
        return false;
    }

    public function getWarehouseCustomer($warehouse_id, $customer_id)
    {
        $this->db->where('customer_id', $customer_id);
        $this->db->where('warehouse_id', $warehouse_id);
        $q = $this->db->get('warehouse_customer');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseDefault($biller_id, $customer_id = null)
    {
        $this->db->select(" warehouse_customer.customer_id as customer_id,
                            warehouses.id as warehouse_id,
                            warehouses.name as warehouse_name");
        $this->db->from('warehouses, warehouse_customer');
        $this->db->where('warehouse_customer.default = warehouses.id');
        $this->db->where('warehouses.company_id =', $biller_id);
        if ($customer_id) {
            $this->db->where('warehouse_customer.customer_id =', $customer_id);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }

    public function addWarehouseCustomer($data = array())
    {
        if ($this->db->insert('warehouse_customer', $data)) {
            return true;
        }
        return false;
    }

    public function updateWarehouseCustomer($warehouse_id, $customer_id, $data = array())
    {
        if ($this->db->update('warehouse_customer', $data, array('customer_id' => $customer_id, 'warehouse_id' => $warehouse_id))) {
            return true;
        }
        return false;
    }

    public function getDeliveredItemsBySaleId($sales_id)
    {
        if ($sales_id) {
            $query = "SELECT sum(sma_delivery_items.quantity_ordered) as quantity, 
                      sum(sma_delivery_items.good_quantity) as good_quantity, 
                      sum(sma_delivery_items.bad_quantity) as bad_quantity
                      FROM sma_delivery_items
                      LEFT JOIN sma_deliveries ON sma_delivery_items.delivery_id = sma_deliveries.id 
                      LEFT JOIN sma_sales ON sma_sales.reference_no = sma_deliveries.sale_reference_no
                      WHERE sma_sales.id = '$sales_id'";
            $q = $this->db->query($query);
            return $q->row();
        }
        return false;
    }

    public function checkAutoCloseATL($sale_id)
    {
        $item       = $this->getDeliveredItemsBySaleId($sale_id);
        $deliveries = $this->getDeliveryBySaleID($sale_id);
        if ($item->quantity == ($item->good_quantity + $item->bad_quantity)) {
            $qtydelivery = 0;
            $kondisireject = 0;

            foreach ($deliveries as $i => $delivery) {
                $kondisi_konfirmasi = true;
                $deliveryItems = $this->getDeliveryItemsByDeliveryId($delivery->id);

                /*
                    untuk pengecekan ketika ada delivery berstatus packing atau delivering
                    tidak dilakukan close. untuk status returned tetap dibiarkan hal ini dilakukan
                    agar qty sent dari do yang return juga ikut dihitung ke $qtydelivery. 
                    qty sent dari do yang return bernilai negatif
                */

                if ($delivery->status == 'packing' || $delivery->status == 'delivering') {
                    continue;
                }

                /*
                    - untuk pengecekan ketika telah dilakukan reject sebanyak 2 kali maka akan diclose 
                    - dan ketika delivery yang direject sebanyak 2 kali tersebut adalah delivery terakhir,
                      hal ini dilakukan untuk mengatasi ketika ada >1 delivery.
                */

                if ($delivery->is_reject == 3 && $i == count($deliveries) - 1) {
                    return true;
                }

                foreach ($deliveryItems as $deliveryItem) {
                    $qtydelivery += $deliveryItem->quantity_sent;

                    /*
                        - untuk pengecekan ketika delivery item memiliki bad > 0 dan kondisinya
                          belum di konfirmasi (belum pilih approve atau reject)
                    */

                    if (
                        $deliveryItem->bad_quantity > 0 && !$delivery->is_reject
                        && !$delivery->is_approval  && !$delivery->is_confirm
                    ) {
                        $kondisi_konfirmasi = false;
                    }


                    /*
                        - untuk pengecekan ketika delivery item memiliki bad > 0 dan kondisinya direject pertama
                          dan belum dikonfirmasi oleh toko
                     
                     */
                    if (
                        $deliveryItem->bad_quantity > 0 &&
                        ($delivery->is_reject >= 1 && $delivery->is_reject <= 2)
                    ) {
                        $kondisi_konfirmasi = false;
                    }
                }

                if ($item->quantity == $qtydelivery && $kondisi_konfirmasi && $i == count($deliveries) - 1) {
                    return true;
                }
            }
        }

        return false;
    }

    public function findPromoByPurchaseId($purchase_id)
    {
        $this->db->where('purchase_id', $purchase_id);
        $q = $this->db->get('transaction_promo');
        if ($q->num_rows() > 0) {
            $tp = $q->row();
            $this->db->where('id', $tp->promo_id);
            $q = $this->db->get('promo');
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        }
        return null;
    }

    /**
     * otp_code
     * timestamp
     * store
     * activation_link
     * sale_ref
     * old_price
     * new_price
     * do_ref
     * sale_ref
     * total_item
     * grand_total
     * payment_balance
     * status
     * */
    public function makeMessage($type, $option)
    {
        $values = [
            '{otp_code}',
            '{timestamp}',
            '{store}',
            '{activation_link}',
            '{sale_ref}',
            '{old_price}',
            '{new_price}',
            '{do_ref}',
            '{sale_ref}',
            '{total_item}',
            '{grand_total}',
            '{payment_balance}',
            '{payment_amount}',
            '{status}',
            '{value}',
            '{min_pembelian}',
            '{kode_voucher}',
            '{start_date}',
            '{end_date}'
        ];
        $replace = [
            @$option['otp_code'],
            @$option['timestamp'],
            @$option['store'],
            @$option['activation_link'],
            @$option['sale_ref'],
            @$option['old_price'],
            @$option['new_price'],
            @$option['do_ref'],
            @$option['sale_ref'],
            @$option['total_item'],
            @$option['grand_total'],
            @$option['payment_balance'],
            @$option['payment_amount'],
            @$option['status'],
            @$option['value'],
            @$option['min_pembelian'],
            @$option['kode_voucher'],
            @$option['start_date'],
            @$option['end_date'],
        ];
        $this->load->model('integration_model', 'integration');
        $integration = $this->integration->findApiIntegrationByType($type);
        $message = $integration->cf8;
        $message = str_replace($values, $replace, $message);
        return $message;
    }

    public function findPromoByCode($code, $supplier_id = null, $company_id = null)
    {
        $this->db->where('code_promo', $code);
        $q = $this->db->get('promo');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }

    public function getLimit($id)
    {
        $q = $this->db->get_where('limit_credit', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}

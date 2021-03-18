<?php defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function updateLogo($photo)
    {
        $logo = array('logo' => $photo);
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updateLoginLogo($photo)
    {
        $logo = array('logo2' => $photo);
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function getSettings()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDateFormats()
    {
        $q = $this->db->get('date_format');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateSetting($data)
    {
        $this->db->where('setting_id', '1');
        if ($this->db->update('settings', $data)) {
            return true;
        }
        return false;
    }

    public function addTaxRate($data)
    {
        if ($this->db->insert('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function updateTaxRate($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('tax_rates', $data)) {
            return true;
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

    public function addWarehouse($data)
    {
        if ($this->db->insert('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function updateWarehouse($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function getAllWarehouses()
    {
        if (!$this->Owner) {
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

    public function getWarehouseByCode($code)
    {
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('warehouses', array('warehouses.code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseByID($id)
    {
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseAndCompanyByWarehouseID($id)
    {
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->select('warehouses.id, warehouses.code, warehouses.name, warehouses.address, companies.city, warehouses.is_deleted, sma_companies.cf1, sma_companies.company');
        $this->db->join('sma_companies', 'sma_companies.id = warehouses.company_id', 'left');
        $q = $this->db->get_where('warehouses', array('warehouses.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateWareHouseBatch($data = array())
    {
        // $this->db->update_batch('warehouses',$data, 'id');
        // echo $this->db->last_query();die;
        if ($this->db->update_batch('warehouses', $data, 'id')) {
            return true;
        }
        return false;
    }

    public function addWarehouses($data)
    {
        if ($this->db->insert_batch('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function deleteTaxRate($id)
    {
        if ($this->db->delete('tax_rates', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteInvoiceType($id)
    {
        if ($this->db->delete('invoice_types', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteWarehouse($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('warehouses', ['is_deleted' => 1])) {
            return true;
        }
        return false;
    }

    public function checkCustomerGroupByName($company_id, $name)
    {
        $this->db->where('name', $name);
        $this->db->where('(company_id = ' . $company_id . ' OR company_id = 1)');
        $q = $this->db->get('customer_groups');
        return $q->num_rows();
    }

    public function checkPriceGroupByName($company_id, $name)
    {
        $this->db->where('name', $name);
        $this->db->where('(company_id = ' . $company_id . ' OR company_id = 1)');
        $q = $this->db->get('price_groups');
        return $q->num_rows();
    }

    public function addCustomerGroup($data)
    {
        if ($this->db->insert('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateCustomerGroup($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function getAllCustomerGroups()
    {
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function deleteCustomerGroup($id)
    {
        if ($this->db->delete('customer_groups', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getGroups()
    {
        if ($this->Owner) {
            $this->db->where('id !=', 1);
        }
        // $this->db->where('id >', 4);
        $q = $this->db->get('groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getGroupByID($id)
    {
        $q = $this->db->get_where('groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function GroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function updatePermissions($id, $data = array())
    {
        if ($this->db->update('permissions', $data, array('group_id' => $id)) && $this->db->update('users', array('show_price' => $data['products-price'], 'show_cost' => $data['products-cost']), array('group_id' => $id))) {
            return true;
        }
        return false;
    }

    public function addGroup($data)
    {
        if ($this->db->insert("groups", $data)) {
            $gid = $this->db->insert_id();
            $this->db->insert('permissions', array('group_id' => $gid));
            return $gid;
        }
        return false;
    }

    public function updateGroup($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("groups", $data)) {
            return true;
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

    public function getCurrencyByID($id)
    {
        $q = $this->db->get_where('currencies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addCurrency($data)
    {
        if ($this->db->insert("currencies", $data)) {
            return true;
        }
        return false;
    }

    public function updateCurrency($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("currencies", $data)) {
            return true;
        }
        return false;
    }

    public function deleteCurrency($id)
    {
        if ($this->db->delete("currencies", array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getParentCategories()
    {
        if (!$this->Owner) {
            $this->db->where("(parent_id=0  or parent_id is null) and  (company_id = " . $this->session->userdata('company_id') . " or company_id = 1) ")->order_by('name');
        } else {
            $this->db->where('parent_id', null)->or_where('parent_id', 0)->order_by('name');
        }
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
        if (!$this->Owner) {
            $this->db->where("(company_id = " . $this->session->userdata('company_id') . " or company_id = 1) ")->order_by('name');
        }
        $q = $this->db->get_where("categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByCode($code)
    {
        if (!$this->Owner) {
            $this->db->where("company_id ", $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('categories', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addCategory($data)
    {
        if ($this->db->insert("categories", $data)) {
            return true;
        }
        return false;
    }

    public function addCategories($data)
    {
        if ($this->db->insert_batch('categories', $data)) {
            return true;
        }
        return false;
    }

    public function updateCategory($id, $data = array())
    {
        if (!$this->Owner) {
            $this->db->where("company_id ", $this->session->userdata('company_id'));
        }
        if ($this->db->update("categories", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteCategory($id)
    {
        if ($this->db->delete("categories", array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get('paypal');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updatePaypal($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('paypal', $data)) {
            return true;
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get('skrill');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateSkrill($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('skrill', $data)) {
            return true;
        }
        return false;
    }

    public function checkGroupUsers($id)
    {
        $q = $this->db->get_where("users", array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function deleteGroup($id)
    {
        if ($this->db->delete('groups', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addVariant($data)
    {
        if ($this->db->insert('variants', $data)) {
            return true;
        }
        return false;
    }

    public function updateVariant($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('variants', $data)) {
            return true;
        }
        return false;
    }

    public function getAllVariants()
    {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getVariantByID($id)
    {
        $q = $this->db->get_where('variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function deleteVariant($id)
    {
        if ($this->db->delete('variants', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where("expense_categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpenseCategoryByCode($code)
    {
        $q = $this->db->get_where("expense_categories", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addExpenseCategory($data)
    {
        if ($this->db->insert("expense_categories", $data)) {
            return true;
        }
        return false;
    }

    public function addExpenseCategories($data)
    {
        if ($this->db->insert_batch("expense_categories", $data)) {
            return true;
        }
        return false;
    }

    public function updateExpenseCategory($id, $data = array())
    {
        if ($this->db->update("expense_categories", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function hasExpenseCategoryRecord($id)
    {
        $this->db->where('category_id', $id);
        return $this->db->count_all_results('expenses');
    }

    public function deleteExpenseCategory($id)
    {
        if ($this->db->delete("expense_categories", array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addUnit($data)
    {
        if ($this->db->insert("units", $data)) {
            return true;
        }
        return false;
    }

    public function getUnitByCompanyIdAndCode($id, $code)
    {
        $this->db->where("code", $code);
        $this->db->where("(client_id = $id OR client_id = 1)");
        $q = $this->db->get("units");
        if ($q->num_rows() > 0) {
            return false;
        }
        return true;
    }

    public function updateUnit($id, $data = array())
    {
        if (!$this->Owner) {
            $this->db->where("client_id ", $this->session->userdata('company_id'));
        }
        if ($this->db->update("units", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteUnit($id)
    {
        if ($this->db->delete("units", array('id' => $id))) {
            $this->db->delete("units", array('base_unit' => $id));
            return true;
        }
        return false;
    }

    public function getGrossPriceByID($id)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('gross', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addGrossPrice($data)
    {
        if ($this->db->insert('gross', $data)) {
            return true;
        }
        return false;
    }

    public function updateGrossprice($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('gross', $data)) {
            return true;
        }
        return false;
    }

    public function deleteGrossPrice($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('gross', array('is_deleted' => 1))) {
            return true;
        }
        return false;
    }

    public function addPriceGroup($data)
    {
        if ($this->db->insert('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updatePriceGroup($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function getCustomerPriceGroup($id_pg)
    {
        $this->db->select("id, CONCAT(id, CONCAT('~', company)) as custom_id, company, name, phone, cf1, price_group_id, price_group_name, country, city, state");
        $this->db->where('price_group_id', $id_pg);
        $this->db->where('group_name', 'customer');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCustomerByBiller()
    {
        $this->db->select("id, CONCAT(id, CONCAT('~', company)) as custom_id, company,");
        $this->db->where('group_name', 'customer');
        $this->db->where('company_id', $this->session->userdata('company_id'));
        $this->db->where('is_deleted', null);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseCustomer($warehouse_id, $filter = false, $customer_id = null)
    {
        if ($customer_id) {
            $this->db->where('customer_id', $customer_id);
        }
        if ($filter) {
            $this->db->where('is_deleted =', 0);
        }
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

    public function getWarehouseCustomerByCustomer($customer_id)
    {
        $this->db->where('customer_id', $customer_id);
        $q = $this->db->get('warehouse_customer');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addWarehouseCustomer($data = array())
    {
        if ($this->db->insert('warehouse_customer', $data)) {
            return true;
        }
        return false;
    }

    public function updateWarehouseCustomer($id, $warehouse_id, $data = array())
    {
        $this->db->where('customer_id', $id);
        $this->db->where('warehouse_id', $warehouse_id);
        if ($this->db->update('warehouse_customer', $data)) {
            return true;
        }
        return false;
    }

    public function updateAllCustomerByPriceGroup($id_pg)
    {
        $this->db->where('price_group_id', $id_pg);
        if ($this->db->update('companies', ['price_group_id' => null, 'price_group_name' => null])) {
            return true;
        }
        return false;
    }

    public function updateCompanyByPriceGroup($id_pg, $data)
    {
        $this->db->where('price_group_id', $id_pg);
        if ($this->db->update('companies', $data)) {
            return true;
        }
        return false;
    }

    public function getAllPriceGroups()
    {
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPriceGroupsByCompanyId($company_id)
    {
        $this->db->where('company_id', $company_id);
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPriceGroupByID($id)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('price_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function deletePriceGroup($id)
    {
        if ($this->db->delete('price_groups', array('id' => $id)) && $this->db->delete('product_prices', array('price_group_id' => $id))) {
            return true;
        }
        return false;
    }

    public function setProductPriceForPriceGroup($product_id, $group_id, $price, $price_kredit = '', $min_order = '', $is_multiple = '')
    {
        if ($this->getGroupPrice($group_id, $product_id)) {
            if ($this->db->update('product_prices', array('price' => $price, 'price_kredit' => $price_kredit, 'min_order' => $min_order, 'is_multiple' => $is_multiple), array('price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        } else {
            if ($this->db->insert('product_prices', array('price' => $price, 'price_kredit' => $price_kredit, 'min_order' => $min_order, 'is_multiple' => $is_multiple, 'price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        }
        return false;
    }

    public function getGroupPrice($group_id, $product_id)
    {
        $q = $this->db->get_where('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGroupPriceByName($group_price_name, $company_id)
    {
        $q = $this->db->get_where('price_groups', ['name' => $group_price_name, 'company_id' => $company_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addTempo($data)
    {
        if ($this->db->insert('top', $data)) {
            return true;
        }
        return false;
    }

    public function updateTempo($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('top', $data)) {
            return true;
        }
        return false;
    }

    public function getTempoByID($id)
    {
        $q = $this->db->get_where('top', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addAPI($data)
    {
        if ($this->db->insert('api_integration', $data)) {
            return true;
        }
        return false;
    }

    public function updateAPI($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('api_integration', $data)) {
            return true;
        }
        return false;
    }

    public function getAPIByID($id)
    {
        $q = $this->db->get_where('api_integration', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    // public function deleteTempo($id)
    // {
    //     if ($this->db->delete('top', array('id' => $id)) && $this->db->delete('product_prices', array('id' => $id))) {
    //         return true;
    //     }
    //     return false;
    // }

    public function getProductGroupPriceByPID($product_id, $group_id)
    {
        $pg = "(SELECT {$this->db->dbprefix('product_prices')}.price as price,{$this->db->dbprefix('product_prices')}.price_kredit as price_kredit, {$this->db->dbprefix('product_prices')}.product_id as product_id FROM {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.product_id = {$product_id} AND {$this->db->dbprefix('product_prices')}.price_group_id = {$group_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, GP.price, GP.price_kredit", false)
            // ->join('products', 'products.id=product_prices.product_id', 'left')
            ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateGroupPrices($data = array())
    {
        foreach ($data as $row) {
            if ($this->getGroupPrice($row['price_group_id'], $row['product_id'])) {
                $this->db->update('product_prices', array('price' => $row['price']), array('product_id' => $row['product_id'], 'price_group_id' => $row['price_group_id']));
            } else {
                $this->db->insert('product_prices', $row);
            }
        }
        return true;
    }

    public function deleteProductGroupPrice($product_id, $group_id)
    {
        if ($this->db->delete('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id))) {
            return true;
        }
        return false;
    }

    // ---------------------------Bank ----------------------//
    public function addBank($data)
    {
        if ($this->db->insert("bank", $data)) {
            $id = $this->db->insert_id();
            if ($data['is_third_party'] == 1 || $data['is_third_party'] == '1') {
                $this->nonActiveThirdPartyBank($id, $data['company_id']);
            }
            return $id;
        }
        return false;
    }

    public function updateBank($id, $data = array())
    {
        if ($this->db->update("bank", $data, array('id' => $id))) {
            if ($data['is_third_party'] == 1 || $data['is_third_party'] == '1') {
                $this->nonActiveThirdPartyBank($id, $data['company_id']);
            }
            return true;
        }
        return false;
    }

    public function nonActiveThirdPartyBank($idBank, $company_id)
    {
        $this->db->where('id !=', $idBank);
        $this->db->where('company_id =', $company_id);
        if ($this->db->update("bank", ['is_third_party' => 0])) {
            return true;
        }
        return false;
    }

    public function deleteBank($id)
    {
        $data = [
            'is_deleted' => 1
        ];
        if ($this->db->update("bank", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function findBankByCode($code, $company_id)
    {
        $this->db->where('code', $code);
        $this->db->where('company_id', $company_id);
        $q = $this->db->get('bank');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return [];
    }
    // --------------------------END OF BANK ----------------//

    public function getBrandByName($name)
    {
        if (!$this->Owner) {
            $this->db->where("(client_id = " . $this->session->userdata('company_id') . " or client_id = 1) ")->order_by('name');
        }
        $q = $this->db->get_where('brands', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addBrand($data)
    {
        if ($data = $this->db->insert("brands", $data)) {
            return true;
        }
        return false;
    }

    public function addBrands($data)
    {
        if ($this->db->insert_batch('brands', $data)) {
            return true;
        }
        return false;
    }

    public function updateBrand($id, $data = array())
    {
        if (!$this->Owner) {
            $this->db->where("client_id ", $this->session->userdata('company_id'));
        }
        if ($this->db->update("brands", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteBrand($id)
    {
        if ($this->db->delete("brands", array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addPromo($data, $items)
    {
        if ($this->db->insert('promo', $data)) {
            $promo_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['promo_id'] = $promo_id;
                $this->db->insert('promo_i', $item);
            }

            return true;
        }
        return false;
    }

    public function addMultipleDiscount($data)
    {
        if ($this->db->insert('multiple_discount', $data)) {
            return true;
        }
        return false;
    }

    public function getMultipleDiscountByID($id)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('multiple_discount', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getMultipleDiscountByPID($pid)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->group_start()->where('is_deleted', 0)->or_where('is_deleted', null)->group_end();
        $this->db->order_by("discount", "desc");
        $q = $this->db->get_where('multiple_discount', array('product_id' => $pid), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateMultipleDiscount($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('multiple_discount', $data)) {
            return true;
        }
        return false;
    }

    public function deleteMultipleDiscount($id)
    {
        $this->db->where("id", $id);
        if ($this->db->update('multiple_discount', array('is_deleted' => 1))) {
            return true;
        }
        return false;
    }

    public function updateAuthorized($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('authorized', $data)) {
            return true;
        }
        return false;
    }

    public function getAllAuthorized()
    {
        $q = $this->db->get('authorized');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAuthorizedByID($id)
    {
        $this->db->select('companies.company as company, companies.email as email, users, warehouses, authorized.biller as biller, create_on');
        $this->db->join('companies', 'authorized.company_id=companies.company_id', 'left');
        $q = $this->db->get_where('authorized', array('authorized.id' => $id, 'companies.group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            echo "<script type=\'text/javascript\'>console.log('masuk')</script>";
            return $data;
        }
        echo "<script type=\'text/javascript\'>console.log('ga masuk')</script>";
        return false;
    }

    public function deleteAuthorized($id)
    {
        if ($this->db->delete('authorized', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addBonus($data)
    {
        if ($this->db->insert('bonus', $data)) {
            return true;
        }
        return false;
    }

    public function addBonuses($data)
    {
        if ($this->db->insert_batch('bonus', $data)) {
            return true;
        }
        return false;
    }

    public function getBonusByID($id)
    {
        $q = $this->db->get_where('bonus', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBonusByPID($pid)
    {
        $this->db->select('p.name')
            ->join('products as p', 'p.id=bonus.product_bonus', 'left')
            ->group_start()->where('bonus.is_deleted', 0)->or_where('bonus.is_deleted', null)->group_end()
            ->order_by("p.price", "desc");
        $q = $this->db->get_where('bonus', array('product_id' => $pid), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateBonus($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('bonus', $data)) {
            return true;
        }
        return false;
    }

    public function deleteBonus($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('bonus', array('is_deleted' => 1))) {
            return true;
        }
        return false;
    }

    public function getShippingChargesByID($id)
    {
        $q = $this->db->get_where('shipping_charges', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addShippingCharges($data)
    {
        if ($this->db->insert('shipping_charges', $data)) {
            return true;
        }
        return false;
    }

    public function updateShippingCharges($id, $data = array())
    {
        if ($this->db->update('shipping_charges', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteShippingCharges($id)
    {
        if ($this->db->update('shipping_charges', array('is_deleted' => 1), array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function addPoints($data)
    {
        if ($this->db->insert('points', $data)) {
            return true;
        }
        return false;
    }

    public function updatePoints($id, $data)
    {
        $this->db->where('id', $id);
        if ($this->db->update('points', $data)) {
            return true;
        }
        return false;
    }

    public function deletePoints($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('points', array('is_deleted' => 1))) {
            return true;
        }
        return false;
    }

    public function addPlan($data)
    {
        if ($this->db->insert('plans', $data)) {
            return true;
        }
        return false;
    }

    public function updatePlan($id, $data)
    {
        if ($this->db->update('plans', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deletePlan($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('plans', array('is_deleted' => 1))) {
            return true;
        }
        return false;
    }

    public function addAddon($data)
    {
        if ($this->db->insert('addons', $data)) {
            return true;
        }
        return false;
    }

    public function updateAddon($id, $data)
    {
        $this->db->where('id', $id);
        if ($this->db->update('addons', $data)) {
            return true;
        }
        return false;
    }

    public function deleteAddon($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('addons', array('is_deleted' => 1))) {
            return true;
        }
        return false;
    }

    public function getAddonByID($id)
    {
        $q = $this->db->get_where('addons', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }


    // ---------------------CMS------------------//
    public function add_cms($data)
    {
        if ($data = $this->db->insert("cms_retail", $data)) {
            if ($data['is_active'] == '1' || $data['is_active'] == 1) {
                $id = $this->db->insert_id();
                $this->non_active_cms($id);
            }
            return true;
        }
        return false;
    }

    public function non_active_cms($id)
    {
        $this->db->where('id !=', $id);
        if ($this->db->update("cms_retail", ['is_active' => 0])) {
            return true;
        }
        return false;
    }

    public function updateCms($id, $data = array())
    {
        if ($this->db->update("cms_retail", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getCmsById($id)
    {
        $q = $this->db->get_where('cms_retail', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }


    public function deleteCms($id)
    {
        $data = [
            'is_deleted' => 1
        ];
        if ($this->db->update("cms_retail", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }


    // ---------------------CMS FAQ------------------//
    public function add_cms_faq($data)
    {
        if ($data = $this->db->insert("cms_faq", $data)) {
            if ($data['is_active'] == '1' || $data['is_active'] == 1) {
                $id = $this->db->insert_id();
                $this->non_active_cms_faq($id);
            }
            return true;
        }
        return false;
    }

    public function deleteCmsFaq($id)
    {
        $data = [
            'is_deleted' => 1
        ];
        if ($this->db->update("cms_faq", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function updateCmsFaq($id, $data = array())
    {
        if ($this->db->update("cms_faq", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    // public function non_active_cms_faq($id)
    // {
    //     $this->db->where('id !=', $id);
    //     if ($this->db->update("cms_faq", ['is_active'=>0])) {
    //         return true;
    //     }
    //     return false;
    // }

    public function getCmsFaqById($id)
    {
        $q = $this->db->get_where('cms_faq', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function add_cms_faq_pos($data)
    {
        if ($data = $this->db->insert("cms_faq_pos", $data)) {
            if ($data['is_active'] == '1' || $data['is_active'] == 1) {
                $id = $this->db->insert_id();
                $this->non_active_cms_faq($id);
            }
            return true;
        }
        return false;
    }
    public function deleteCmsFaqPos($id)
    {
        $data = [
            'is_deleted' => 1
        ];
        if ($this->db->update("cms_faq_pos", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function updateCmsFaqPos($id, $data = array())
    {
        if ($this->db->update("cms_faq_pos", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function getCmsFaqPosById($id)
    {
        $q = $this->db->get_where('cms_faq_pos', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryFaqPos()
    {
        // $this->db->where("sma_parent_menu_faq_pos.parent_id = sma_cms_faq_pos.parent_id");
        $this->db->where('sma_parent_menu_faq_pos.is_active', 1);
        $q = $this->db->get_where('sma_parent_menu_faq_pos', ['is_active' => '1', 'is_deleted' => '0']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    // Category Faq

    public function getCategoryFaqById($id)
    {
        $q = $this->db->get_where('parent_menu_faq_pos', array('parent_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function add_category_faq($data)
    {
        if ($data = $this->db->insert("parent_menu_faq_pos", $data)) {
            if ($data['is_active'] == '1' || $data['is_active'] == 1) {
                $id = $this->db->insert_id();
                $this->non_active_cms_faq($id);
            }
            return true;
        }
        return false;
    }
    public function deleteCategoryFaq($id)
    {
        $data = [
            'is_deleted' => 1
        ];
        if ($this->db->update("parent_menu_faq_pos", $data, array('parent_id' => $id))) {
            return true;
        }
        return false;
    }

    public function updateCategoryFaq($id, $data = array())
    {
        if ($this->db->update("parent_menu_faq_pos", $data, array('parent_id' => $id))) {
            return true;
        }
        return false;
    }

    // End Category Faq

    public function addShipmentPriceGroup($data)
    {
        if ($this->db->insert('shipment_price_group', $data)) {
            return true;
        }
        return false;
    }

    public function getShipmentPriceGroupByID($id)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('shipment_price_group', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateShipmentPriceGroupByID($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('shipment_price_group', $data)) {
            return true;
        }
        return false;
    }

    public function setProductPriceForShipmentPriceGroup($product_id, $group_id, $price_pickup, $price_delivery)
    {
        if ($this->getShipmentGroupPrice($group_id, $product_id)) {
            if ($this->db->update('shipment_product_price', array('price_pickup' => $price_pickup, 'price_delivery' => $price_delivery), array('shipment_price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        } else {
            if ($this->db->insert('shipment_product_price', array('price_pickup' => $price_pickup, 'price_delivery' => $price_delivery, 'shipment_price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        }
        return false;
    }

    public function getShipmentGroupPrice($group_id, $product_id)
    {
        $q = $this->db->get_where('shipment_product_price', array('shipment_price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getShipmentGroupPriceByCompanyId($company_id)
    {
        $this->db->where('company_id', $company_id);
        $q = $this->db->get('shipment_price_group');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function deleteShipmentProductGroupPrice($product_id, $group_id)
    {
        if ($this->db->delete('shipment_product_price', array('shipment_price_group_id' => $group_id, 'product_id' => $product_id))) {
            return true;
        }
        return false;
    }

    public function getShipmentProductGroupPriceByPID($product_id, $group_id)
    {
        $pg = "(SELECT {$this->db->dbprefix('shipment_product_price')}.price_pickup as price_pickup, {$this->db->dbprefix('shipment_product_price')}.price_delivery as price_delivery,{$this->db->dbprefix('shipment_product_price')}.product_id as product_id FROM {$this->db->dbprefix('shipment_product_price')} WHERE {$this->db->dbprefix('shipment_product_price')}.product_id = {$product_id} AND {$this->db->dbprefix('shipment_product_price')}.shipment_price_group_id = {$group_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, GP.price_pickup, GP.price_delivery", false)
            // ->join('products', 'products.id=product_prices.product_id', 'left')
            ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCompanyPaymentMethodByCompanyId($company_id)
    {
        $this->db->select('sma_payment_methods.id as payment_method_id, sma_payment_methods.NAME, sma_company_payment_methods.is_active ');
        $this->db->join('sma_payment_methods', 'sma_company_payment_methods.payment_method_id = sma_payment_methods.id', 'left');
        $q = $this->db->get_where('sma_company_payment_methods', array('sma_company_payment_methods.company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateCompanyPaymentMethodByCompanyId($company_id, $data)
    {
        if ($this->db->update('sma_company_payment_methods', $data, array('company_id' => $company_id))) {
            return true;
        }
        return false;
    }

    public function updateCompanyPaymentMethodByCompanyIdAndPaymentMethodId($company_id, $payment_method_id, $data)
    {
        if ($this->db->update('sma_company_payment_methods', $data, array('company_id' => $company_id, 'payment_method_id' => $payment_method_id))) {
            return true;
        }
        return false;
    }

    public function getPaymentMethodByCompanyIdAndPaymentMethodId($company_id, $payment_method_id)
    {
        $q = $this->db->get_where('sma_company_payment_methods', array('company_id' => $company_id, 'payment_method_id' => $payment_method_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function insertCompanyPaymentMethod($data)
    {
        if ($this->db->insert('sma_company_payment_methods', $data)) {
            return true;
        }
        return false;
    }

    public function getCustomerOfCustomerGroup($id_cg, $company_id)
    {
        $this->db->select("id, CONCAT(id, CONCAT('~', company)) as custom_id, company, name, phone, cf1, customer_group_id, customer_group_name, country, city, state");
        $this->db->where('customer_group_id', $id_cg);
        $this->db->where('company_id', $company_id);
        $this->db->where('group_name', 'customer');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateAllCustomerByCustomerGroupId($id_cg, $company_id = null)
    {
        $this->db->where('company_id', $company_id ?? $this->session->userdata('company_id'));
        $this->db->where('customer_group_id', $id_cg);
        if ($this->db->update('companies', ['customer_group_id' => null, 'customer_group_name' => null])) {
            return true;
        }
        return false;
    }

    public function getActiveTermKreditProByCompanyId($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $q = $this->db->get('show_kreditpro');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getTermKreditProByCompanyId($company_id)
    {
        $this->db->where('company_id', $company_id);
        $q = $this->db->get('show_kreditpro');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getTermKreditProByCompanyIdAndTerm($company_id, $term)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('term', $term);
        $q = $this->db->get('show_kreditpro', 1);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function addTermKreditpro($data)
    {
        if ($this->db->insert('show_kreditpro', $data)) {
            return true;
        }
        return false;
    }

    public function updateTermKreditproByCompanyIdAndTerm($data)
    {
        $this->db->where('company_id', $data['company_id']);
        $this->db->where('term', $data['term']);
        if ($this->db->update('show_kreditpro', $data)) {
            return true;
        }
        return false;
    }
    public function addUpdatesNotif($data)
    {
        if ($this->db->insert('updates', $data)) {
            return true;
        }
        return false;
    }

    public function updateUpdatesNotif($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('updates', $data)) {
            return true;
        }
        return false;
    }

    public function getUpdatesNotifByID($id)
    {
        $q = $this->db->get_where('updates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addFeedback($data)
    {
        if ($this->db->insert('feedback_question', $data)) {
            return true;
        }
        return false;
    }

    public function addFeedbackCategory($data)
    {
        if ($this->db->insert('feedback_category', $data)) {
            return true;
        }
        return false;
    }

    public function addOptions($data)
    {
        if ($this->db->insert('feedback_option', $data)) {
            return true;
        }
        return false;
    }

    public function updateOptions($option_id, $data)
    {
        $this->db->where('id', $option_id);
        if ($this->db->update('feedback_option', $data)) {
            return true;
        }
        return false;
    }

    public function deleteAllOptionsWithQuestionID($question_id)
    {
        $this->db->where('question_id', $question_id);
        if ($this->db->update('feedback_option', ['is_deleted' => 1, 'is_active' => 0])) {
            return true;
        }
        return false;
    }

    public function getFeedbackLastId()
    {
        $q = $this->db->query("SELECT MAX({$this->db->dbprefix('feedback_question')}.id) as id FROM {$this->db->dbprefix('feedback_question')}");
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateFeedback($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('feedback_question', $data)) {
            return true;
        }
        return false;
    }

    public function updateFeedbackCategory($id, $data)
    {
        $this->db->where('id', $id);
        if ($this->db->update('feedback_category', $data)) {
            return true;
        }
        return false;
    }

    public function setNonActiveFeedbackCategory()
    {
        if ($this->db->update('feedback_category', ['is_active' => 0], ['flag !=' => '1'])) {
            return true;
        }
        return false;
    }

    public function setNonActiveFeedbackCategoryAT()
    {
        if ($this->db->update('feedback_category', ['is_active' => 0], ['flag' => '1'])) {
            return true;
        }
        return false;
    }

    public function getFeedbackCategoryByID($id)
    {
        $q = $this->db->get_where('feedback_category', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getFeedbackByID($id)
    {
        $q = $this->db->get_where('feedback_question', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getOptionsByID($where)
    {
        $q = $this->db->get_where('feedback_option', $where);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getFeedbackCategoryList()
    {
        $q = $this->db->query("SELECT {$this->db->dbprefix('feedback_category')}.id as id, {$this->db->dbprefix('feedback_category')}.category as category FROM {$this->db->dbprefix('feedback_category')}");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getFeedbackByCategory($id)
    {
        $q = $this->db->get_where('feedback_question', array('category_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getResponseByQuestion($id)
    {
        $q = $this->db->get_where('feedback_response', array('question_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getListCustomerToPriceGroups($id_price_group, $company_id)
    {
        $this->db->select("id, company, name, phone, cf1, price_group_id, price_group_name, country, city, state");
        $this->db->where('group_name', 'customer');
        $this->db->where('company_id', $company_id);
        $this->db->where('(price_group_id IS NULL OR price_group_id =' . $id_price_group . ")");
        $this->db->where('is_deleted', NULL);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getListCustomerToCustomerGroups($id_customer_group, $company_id)
    {
        $this->db->select("id, company, name, phone, cf1, customer_group_id, customer_group_name, country, city, state");
        $this->db->where('group_name', 'customer');
        $this->db->where('company_id', $company_id);
        $this->db->where('(customer_group_id IS NULL OR customer_group_id =' . $id_customer_group . ")");
        $this->db->where('is_deleted', NULL);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getProductPricesGroup($id_price_group, $company_id, $product_id = null)
    {
        $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.price_kredit as price_kredit, {$this->db->dbprefix('product_prices')}.min_order as min_order, {$this->db->dbprefix('product_prices')}.is_multiple as is_multiple, {$this->db->dbprefix('product_prices')}.price_group_id as price_group_id FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$id_price_group} ) PP";
        $this->db->select("{$this->db->dbprefix('products')}.id as id, PP.price_group_id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, PP.price as price, PP.price_kredit as price_kredit, PP.min_order as min_order, {$this->db->dbprefix('units')}.name as unit_name, PP.is_multiple");
        $this->db->join($pp, 'PP.product_id = products.id', 'left');
        $this->db->join('sma_units', 'sma_products.sale_unit = sma_units.id', 'inner');
        $this->db->join('sma_units as unit', 'sma_products.unit = unit.id', 'inner');
        $this->db->where('products.company_id', $company_id);
        $this->db->where('products.is_deleted', NULL);
        if ($product_id) {
            $this->db->where('products.id', $product_id);
        }
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function update_markup($data = array(), $where)
    {
        $this->db->where($where);
        if ($this->db->update('warehouses_products', $data)) {
            return true;
        }
        return false;
    }

    public function getLimit($company_id)
    {
        $q = $this->db->get_where('mandiri_loan_request', ['company_id' => $company_id], 1);
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }

    public function add_limit($data)
    {
        if ($this->db->insert("mandiri_loan_request", $data)) {
            $id = $this->db->insert_id();
            return $id;
        }
        return false;
    }

    public function edit_limit($data = array(), $where)
    {
        $this->db->where($where);
        if ($this->db->update('mandiri_loan_request', $data)) {
            return true;
        }
        return false;
    }

    public function getCustomerLimit($company_id)
    {
        $this->db->select("c.id, CONCAT(c.id, CONCAT('~', c.company)) as custom_id, c.company, c.name, c.phone, c.cf1, c.country, c.city, c.state, lr.*");
        $this->db->where('lr.company_id', $company_id);
        $this->db->join('mandiri_loan_request lr', '`c`.`id` = lr.company_id', 'left');
        $q = $this->db->get('companies c');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}

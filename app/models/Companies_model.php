<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Companies_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllBillerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'biller', 'client_id' => null));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllCustomerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'customer'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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

    public function getAllCustomerGroups($company_id)
    {

        if (!$this->Owner) {
            $this->db->where('customer_groups.company_id = ' . $company_id . ' OR customer_groups.company_id = 1 OR customer_groups.company_id IS NULL');
            // $this->db->order_by('companies.company', 'ASC');
        }

        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyUsers($company_id)
    {
        $q = $this->db->get_where('users', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id, $company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }

        $q = $this->db->get_where('companies', array('id' => $id, 'is_deleted' => NULL), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCompanyByEmail($email, $group = null, $company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        $q = $this->db->get_where('companies', array('email' => $email, 'group_name' => $group), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addCompany($data = array())
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('companies', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }

    public function updateCompanyCf1($cf1, $data = array())
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('cf1', $cf1);
        $this->db->where('company_id', $this->session->userdata('company_id'));
        if ($this->db->update('companies', $data)) {
            return true;
        }
        return false;
    }

    public function updateCompany($id, $data = array())
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        if ($this->db->update('companies', $data)) {
            return true;
        }
        return false;
    }

    public function addCompanies($data = array())
    {
        foreach ($data as $row) $row['created_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert_batch('companies', $data)) {
            return true;
        }
        return false;
    }

    public function deleteCustomer($id)
    {
        if ($this->getCustomerSales($id)) {
            return false;
        }
        //        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'customer')) && $this->db->delete('users', array('company_id' => $id))) {
        //            return true;
        //        }

        $data = ['updated_at' => date('Y-m-d H:i:s'), 'is_deleted' => 1];
        if ($this->db->update('companies', $data, array('id' => $id, 'group_name' => 'customer')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteSupplier($id)
    {
        if ($this->getSupplierPurchases($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'supplier')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteBiller($id)
    {
        if ($this->getBillerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'biller'))) {
            return true;
        }
        return FALSE;
    }

    public function getBillerSuggestions($term, $limit = 10)
    {
        $this->db->select("id, company as text");
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'biller'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSuggestions($term, $limit = 10, $warehouse_id = null)
    {
        $this->db->select("sma_companies.id, (CASE WHEN sma_companies.company = '-' THEN sma_companies.name ELSE CONCAT(sma_companies.company, ' (', sma_companies.name, ')') END) as text, CONCAT(IFNULL(sma_companies.cf1, 'null'), ' - ',sma_companies.address) as address");
        $this->db->from("sma_companies");

        if ($warehouse_id) {
            $join = "(  SELECT * FROM sma_warehouse_customer WHERE warehouse_id != " . $warehouse_id . " AND customer_id NOT IN ( SELECT customer_id FROM sma_warehouse_customer WHERE warehouse_id = " . $warehouse_id . " AND is_deleted = 0) AND is_deleted = 0 ) join_a";
            // $this->datatables->join($join1, 'join_a.customer_id = companies.id', 'inner'); // Hanya customer dari warehousse $this->session->userdata('warehouse_id')
            $this->db->join($join, 'join_a.customer_id = companies.id', 'left');
            $this->db->where('join_a.customer_id is NULL');
        }

        $query = $term;
        if (strpos($query, ",") !== false) {
            $query = explode(",", $query, 2);
            $company = trim($query[0]);
            $name = trim($query[1]);
            $this->db->where(" (sma_companies.name LIKE '%" . $name . "%' AND sma_companies.company LIKE '%" . $company . "%') and sma_companies.group_name = 'customer' AND sma_companies.is_deleted is null");
        } elseif (strpos($query, "IDC-") !== false) {
            $query = explode("IDC-", $query, 2);
            $bk = trim($query[1]);
            $this->db->where(" (sma_companies.cf1 LIKE '%" . $bk . "%' OR sma_companies.cf2 LIKE '%" . $bk . "%' OR sma_companies.cf3 LIKE '%" . $bk . "%' OR sma_companies.cf4 LIKE '%" . $bk . "%' OR sma_companies.cf5 LIKE '%" . $bk . "%' OR sma_companies.cf6 LIKE '%" . $bk . "%') and sma_companies.group_name = 'customer' AND sma_companies.is_deleted is null");
        } else {
            $this->db->where(" (sma_companies.id LIKE '%" . $term . "%' OR sma_companies.name LIKE '%" . $term . "%' OR sma_companies.company LIKE '%" . $term . "%' OR sma_companies.email LIKE '%" . $term . "%' OR sma_companies.phone LIKE '%" . $term . "%' OR sma_companies.cf1 LIKE '%" . $term . "%') and sma_companies.group_name = 'customer' AND sma_companies.is_deleted is null");
        }

        if (!$this->Owner && !$this->Principal) {
            $this->db->where('sma_companies.company_id', $this->session->userdata('company_id'));
            // $this->db->or_where('sma_companies.id', 1);
        }

        $this->db->limit($limit);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            if ($q->num_rows() > 1) {
                foreach ($q->result() as $row) {
                    if ($row->id == 1) continue;
                    $data[] = $row;
                }
            } else {
                $data = $q->result();
            }

            return $data;
        }
    }

    public function getSupplierSuggestions($term, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", FALSE);

        if (!$this->Owner) {
            //            $this->db->where("( company_id = ".$this->session->userdata('company_id')." OR company_id = 1 )");
            $this->db->group_start()->where("company_id", 1)->or_where("company_id", $this->session->userdata('company_id'))->group_end();
        }
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $this->db->where('group_name', 'supplier');
        //        $q = $this->db->get_where('companies', array('group_name' => 'supplier'), $limit);
        $q = $this->db->get_where('companies', array(), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSales($id)
    {
        $this->db->where('customer_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getBillerSales($id)
    {
        $this->db->where('biller_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getSupplierPurchases($id)
    {
        $this->db->where('supplier_id', $id)->from('purchases');
        return $this->db->count_all_results();
    }

    public function addDeposit($data, $cdata)
    {
        $cdata['updated_at'] = date('Y-m-d H:i:s');

        if (
            $this->db->insert('deposits', $data) &&
            $this->db->update('companies', $cdata, array('id' => $data['company_id']))
        ) {
            return true;
        }
        return false;
    }

    public function updateDeposit($id, $data, $cdata)
    {
        $cdata['updated_at'] = date('Y-m-d H:i:s');

        if (
            $this->db->update('deposits', $data, array('id' => $id)) &&
            $this->db->update('companies', $cdata, array('id' => $data['company_id']))
        ) {
            return true;
        }
        return false;
    }

    public function getDepositByID($id)
    {
        $q = $this->db->get_where('deposits', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDeposit($id)
    {
        $deposit = $this->getDepositByID($id);
        $company = $this->getCompanyByID($deposit->company_id);
        $cdata = array(
            'deposit_amount' => ($company->deposit_amount - $deposit->amount),
            'updated_at' => date('Y-m-d H:i:s')
        );
        if (
            $this->db->update('companies', $cdata, array('id' => $deposit->company_id)) &&
            $this->db->delete('deposits', array('id' => $id))
        ) {
            return true;
        }
        return false;
    }

    public function getAllPriceGroups($company_id = null)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $company_id ?? $this->session->userdata('company_id'));
        }
        $this->db->where('is_deleted', null);
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            $data = [];
            foreach (($q->result()) as $row) {
                if ($row->id == 1) continue;
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }

    public function getCompanyAddresses($company_id)
    {
        $q = $this->db->get_where('addresses', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addAddress($data)
    {
        if ($this->db->insert('addresses', $data)) {
            return true;
        }
        return false;
    }

    public function updateAddress($id, $data)
    {
        if ($this->db->update('addresses', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteAddress($id)
    {
        if ($this->db->delete('addresses', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getAddressByID($id)
    {
        $q = $this->db->get_where('addresses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function findCompanyByCf1AndCompanyId($supplier_id, $cf1)
    {
        $this->db->where('sma_companies.company_id', $supplier_id);
        $this->db->where('sma_companies.cf1', $cf1);
        $q = $this->db->get('sma_companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }

    public function findCompanyByCf1($cf1)
    {
        $this->db->where('cf1', $cf1);
        $q = $this->db->get('companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return null;
    }

    public function findCompanyByCf1AndCf2($cf1, $cf2)
    {
        $this->db->where('cf1', $cf1);
        $this->db->where('cf2', $cf2);
        $q = $this->db->get('companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }

    public function findCompanyByCf2AndId($cf2, $id)
    {
        $this->db->where('id', $id);
        $this->db->where('cf2', $cf2);
        $q = $this->db->get('companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }
    public function findCf1ById($id)
    {
        $this->db->where('id', $id);
        $q = $this->db->get('companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
    public function getDataTokoAktif($kd_distributor)
    {
        $q = $this->db->get_where('api_integration', ['type' => "data_toko_aktif_kddistributor"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        try {
            $URL    = $integration->uri;                                                  //API URL
            $ch     = curl_init($URL);                                                    //buat sumber daya CURL baru
            $point  = json_encode(['kddistributor' => $kd_distributor]);                  //pengaturan permintaan untuk mengirim json melalui POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $point);                                 //lampirkan string JSON yang disandikan ke bidang POST
            curl_setopt($ch, CURLOPT_FAILONERROR, true);                                  //mengembalikan respons alih-alih mengeluarkan
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);      //setel jenis konten ke aplikasi / json
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                               //mengembalikan respons alih-alih mengeluarkan
            $result = curl_exec($ch);                                                     //jalankan permintaan POST
            $data   = json_decode($result, true);
            if (curl_error($ch)) {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);                                                              //tutup sumber daya CURL
        } catch (Exception $e) {
            return false;
        }
        return $data['data'];
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
    public function cekDataLT($code_bk)
    {
        $this->load->model('curl_model', 'curl');
        $q = $this->db->get_where('api_integration', ['type' => "data_toko_aktif_kdcustomer"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();

        $url = $integration->uri;
        $data = json_encode(['kdcustomer' => $code_bk]);
        $curl = json_decode($this->curl->_post($url, $data, true), true);

        $ret = $curl['data'];
        return $ret;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
    public function _addCompanies($data = [])
    {
        if ($this->db->insert('sma_companies', $data)) {
            return true;
        }
        return false;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
    public function _updateCompanies($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('sma_companies', $data)) {
            return true;
        }
        return false;
    }

    public function updateCompanyBatch($data = array())
    {
        if ($this->db->update_batch('companies', $data, 'id')) {
            return true;
        }
        return false;
    }

    public function getCompanyByParent($id = null, $filter = null)
    {
        if ($id) {
            $this->db->where('company_id', $id);
        }
        if ($filter) {
            $this->db->where($filter);
        }
        $q = $this->db->get_where('companies', array('is_deleted' => NULL));
        // var_dump($this->db->error());die;
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getCompanyNotWhereId($id, $where = array(), $select)
    {
        if ($select) {
            $this->db->select($select);
        }
        if ($where) {
            $this->db->where($where);
        }
        $q = $this->db->get_where('companies', array('company_id !=' => $id, 'is_deleted' => NULL));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getCompanyWhereNotIn($id = array(), $where = array(), $select)
    {
        if ($select) {
            $this->db->select($select);
        }
        if ($where) {
            $this->db->where($where);
        }
        if ($id) {
            $this->db->where_not_in('id', $id);
        }
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getBillerSuggestionsAktif($term, $limit = 20)
    {
        $this->db->select("companies.id, companies.company as text, companies.cf1 as code");
        $this->db->join("users", 'companies.id = users.company_id');
        $this->db->where('users.group_id', '2');
        $this->db->where('users.active', '1');
        $this->db->where(" (companies.id LIKE '%" . $term . "%' OR companies.name LIKE '%" . $term . "%' OR companies.company LIKE '%" . $term . "%' OR companies.cf1 LIKE '%" . $term . "%') ");
        $this->db->where("(companies.client_id != 'aksestoko' OR companies.client_id IS NULL)");
        $this->db->where('companies.is_deleted', NULL);
        $this->db->where('companies.is_active', '1');
        $this->db->order_by('companies.company', 'ASC');
        $q = $this->db->get_where('companies', array('companies.group_name' => 'biller'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getDistributor()
    {
        $this->db->select("companies.*");
        $this->db->join("users", 'companies.id = users.company_id');
        $this->db->where('users.group_id', '2');
        $this->db->where('users.active', '1');
        $this->db->where('companies.group_name', 'biller');
        $this->db->where("(companies.client_id != 'aksestoko' OR companies.client_id IS NULL)");
        $this->db->where('companies.is_deleted', NULL);
        $this->db->order_by('companies.company', 'ASC');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getDistributorPerdaerah($provinsi, $kabupaten, $term, $limit)
    {
        $this->db->select("companies.*");
        $this->db->join("users", 'companies.id = users.company_id');
        $this->db->where('users.group_id', '2');
        $this->db->where('users.active', '1');
        $this->db->where('companies.group_name', 'biller');
        $this->db->where("(companies.client_id != 'aksestoko' OR companies.client_id IS NULL)");
        $this->db->where('companies.is_deleted', NULL);

        if ($provinsi && !empty($provinsi)) {
            $this->db->where('companies.country', $provinsi);
        }

        if ($kabupaten && !empty($kabupaten)) {
            $this->db->where('companies.city', $kabupaten);
        }

        if ($term && !empty($term)) {
            $this->db->group_start();
            $this->db->like('companies.company', $term);
            $this->db->or_like('companies.name', $term);
            $this->db->or_like('companies.cf1', $term);
            $this->db->group_end();
        }

        $this->db->limit($limit);

        $this->db->order_by('companies.company', 'ASC');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function deleteCustomerForPrincipal($id)
    {
        $data = ['updated_at' => date('Y-m-d H:i:s'), 'is_deleted' => 1];
        if ($this->db->update('companies', $data, array('id' => $id, 'group_name' => 'customer'))) {
            return true;
        }
        return FALSE;
    }

    public function recoverCustomer($id)
    {
        $data = ['updated_at' => date('Y-m-d H:i:s'), 'is_deleted' => null];
        if ($this->db->update('companies', $data, array('id' => $id, 'group_name' => 'customer'))) {
            return true;
        }
        return FALSE;
    }

    public function getAllCustomerWithDistributor()
    {
        $this->db->select('a.*, b.company as nama_distributor, b.cf1 as kode_distributor');
        $this->db->from('companies a');
        $this->db->join("companies b", 'a.company_id = b.id');
        $this->db->where('a.group_name', 'customer');
        $this->db->order_by('a.company', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getCompanyWhereInId($where_in, $group_name)
    {
        $this->db->select('a.*, b.company as nama_distributor, b.cf1 as kode_distributor');
        $this->db->from('companies a');
        $this->db->join("companies b", 'a.company_id = b.id');
        $this->db->where('a.group_name', $group_name);
        $this->db->where_in('a.id', $where_in);
        $this->db->order_by('a.company', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAllSupplierByCompanyId($where)
    {
        if ($where) {
            $this->db->where($where);
        }
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCustomerByDistributorId($id_distributor, $filter = null)
    {

        if ($filter) {
            $this->db->where($filter);
        }

        $this->db
            ->where('company_id', $id_distributor)
            ->where('companies.group_name', 'customer')
            ->where('companies.is_deleted IS NULL');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseCustomer($warehouse_id, $customer_id = null)
    {
        if ($customer_id) {
            $this->db->where('customer_id', $customer_id);
        } else {
            $this->db->where('is_deleted', 0);
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
        return null;
    }

    public function getWarehouseByDistributor($id_distributor)
    {
        $this->db->where('company_id', $id_distributor);
        $this->db->where('(is_deleted IS NULL OR is_deleted = "0")');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSalesPerson($company_id = null, $active = true)
    {
        if (!$this->Owner || $company_id) {
            $this->db->where('company_id', $company_id);
        }
        if ($active) {
            $this->db->where('is_active', 1);
        }
        $this->db->where('is_deleted', null);
        $q = $this->db->get('sales_person');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getCustomerAll($company_id, $filter = null)
    {

        if ($filter) {
            $this->db->where($filter);
        }

        $this->db
            ->where('company_id', $company_id)
            ->where('companies.group_name', 'customer')
            ->where('companies.is_deleted IS NULL');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
    }

    public function getAllCustomerPaging($company_id, $filter = null, $limit = null, $offset = null, $sortby = null, $sorttype = null)
    {
        $sql = 'SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = "' . getenv('DB_DATABASE') . '" AND table_name = "sma_companies"';
        $query = $this->db->query($sql);
        $bool = 0;
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                if ($sortby == $row->COLUMN_NAME) {
                    $bool = 1;
                }
            }
        }

        if ($filter) {
            $this->db->where($filter);
        }

        $this->db
            ->where('company_id', $company_id)
            ->where('companies.group_name', 'customer')
            ->where('companies.is_deleted IS NULL');

        if ($bool == 1 && $sorttype) {
            $this->db->order_by('companies.' . $sortby, $sorttype);
        } else {
            $this->db->order_by('companies.name', 'asc');
        }
        if ($limit != null || $offset != null) {
            $this->db->limit($limit, $offset);
        }
        $q = $this->db->get('companies');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSuggestionsCustomers($term, $limit = 10, $warehouse_id = null, $company_id = null)
    {
        $this->db->select("sma_companies.*");
        $this->db->from("sma_companies");

        if ($warehouse_id) {
            $join = "(  SELECT * FROM sma_warehouse_customer WHERE warehouse_id != " . $warehouse_id . " AND customer_id NOT IN ( SELECT customer_id FROM sma_warehouse_customer WHERE warehouse_id = " . $warehouse_id . " AND is_deleted = 0) AND is_deleted = 0 ) join_a";
            // $this->datatables->join($join1, 'join_a.customer_id = companies.id', 'inner'); // Hanya customer dari warehousse $this->session->userdata('warehouse_id')
            $this->db->join($join, 'join_a.customer_id = companies.id', 'left');
            $this->db->where('join_a.customer_id is NULL');
        }

        $query = $term;
        if (strpos($query, ",") !== false) {
            $query = explode(",", $query, 2);
            $company = trim($query[0]);
            $name = trim($query[1]);
            $this->db->where(" (sma_companies.name LIKE '%" . $name . "%' AND sma_companies.company LIKE '%" . $company . "%') and sma_companies.group_name = 'customer' AND sma_companies.is_deleted is null");
        } elseif (strpos($query, "IDC-") !== false) {
            $query = explode("IDC-", $query, 2);
            $bk = trim($query[1]);
            $this->db->where(" (sma_companies.cf1 LIKE '%" . $bk . "%' OR sma_companies.cf2 LIKE '%" . $bk . "%' OR sma_companies.cf3 LIKE '%" . $bk . "%' OR sma_companies.cf4 LIKE '%" . $bk . "%' OR sma_companies.cf5 LIKE '%" . $bk . "%' OR sma_companies.cf6 LIKE '%" . $bk . "%') and sma_companies.group_name = 'customer' AND sma_companies.is_deleted is null");
        } else {
            $this->db->where(" (sma_companies.id LIKE '%" . $term . "%' OR sma_companies.name LIKE '%" . $term . "%' OR sma_companies.company LIKE '%" . $term . "%' OR sma_companies.email LIKE '%" . $term . "%' OR sma_companies.phone LIKE '%" . $term . "%' OR sma_companies.cf1 LIKE '%" . $term . "%') and sma_companies.group_name = 'customer' AND sma_companies.is_deleted is null");
        }

        if (!$this->Owner && !$this->Principal) {
            $this->db->where('sma_companies.company_id', $company_id ?? $this->session->userdata('company_id'));
            $this->db->or_where('sma_companies.id', 1);
        }

        $this->db->limit($limit);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            if ($q->num_rows() > 1) {
                foreach ($q->result() as $row) {
                    if ($row->id == 1) continue;
                    $data[] = $row;
                }
            } else {
                $data = $q->result();
            }

            return $data;
        }
    }

    public function getCustomerLimitSugestions($term, $limit = 10)
    {
        $this->db->select("sma_companies.id, (CASE WHEN sma_companies.company = '-' THEN sma_companies.name ELSE CONCAT(sma_companies.company, ' (', sma_companies.name, ')') END) as text, CONCAT(IFNULL(sma_companies.cf1, 'null'), ' - ',sma_companies.address) as address");
        $this->db->from("sma_companies");
        $this->db->where('sma_companies.group_name', 'biller');
        $this->db->where('sma_companies.client_id', 'aksestoko');

        $query = $term;
        if (strpos($query, ",") !== false) {
            $query = explode(",", $query, 2);
            $company = trim($query[0]);
            $name = trim($query[1]);
            $this->db->where(" (sma_companies.name LIKE '%" . $name . "%' AND sma_companies.company LIKE '%" . $company . "%') and sma_companies.group_name = 'customer' AND sma_companies.is_deleted is null");
        } elseif (strpos($query, "IDC-") !== false) {
            $query = explode("IDC-", $query, 2);
            $bk = trim($query[1]);
            $this->db->where(" (sma_companies.cf1 LIKE '%" . $bk . "%' OR sma_companies.cf2 LIKE '%" . $bk . "%' OR sma_companies.cf3 LIKE '%" . $bk . "%' OR sma_companies.cf4 LIKE '%" . $bk . "%' OR sma_companies.cf5 LIKE '%" . $bk . "%' OR sma_companies.cf6 LIKE '%" . $bk . "%') AND sma_companies.is_deleted is null");
        } else {
            $this->db->where(" (sma_companies.id LIKE '%" . $term . "%' OR sma_companies.name LIKE '%" . $term . "%' OR sma_companies.company LIKE '%" . $term . "%' OR sma_companies.email LIKE '%" . $term . "%' OR sma_companies.phone LIKE '%" . $term . "%' OR sma_companies.cf1 LIKE '%" . $term . "%') AND sma_companies.is_deleted is null");
        }

        if (!$this->Owner && !$this->Principal) {
            $this->db->where('sma_companies.company_id', $this->session->userdata('company_id'));
            // $this->db->or_where('sma_companies.id', 1);
        }

        $this->db->limit($limit);
        $q = $this->db->get();
        // var_dump($this->db->last_query());die;
        if ($q->num_rows() > 0) {
            if ($q->num_rows() > 1) {
                foreach ($q->result() as $row) {
                    if ($row->id == 1) continue;
                    $data[] = $row;
                }
            } else {
                $data = $q->result();
            }

            return $data;
        }
    }
}

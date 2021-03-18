<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_person_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function addSalesPerson($data = array())
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('sales_person', $data)) {
            $spid = $this->db->insert_id();
            return $spid;
        }
        return false;
    }

    public function getSalesPersonById($id)
    {
        $q = $this->db->get_where('sales_person', array('id' => $id, 'is_deleted' => NULL), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateSalesPerson($id, $data = array())
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        if ($this->db->update('sales_person', $data)) {
            return true;
        }
        return false;
    }

    public function addMultipleSalesPerson($data = array())
    {
        foreach ($data as $row) $row['created_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert_batch('sales_person', $data)) {
            return true;
        }
        return false;
    }

    public function getCustomerOfSalesPerson($sales_person_id, $company_id)
    {
        $this->db->select("id, CONCAT(id, CONCAT('~', company)) as custom_id, company,");
        $this->db->where('sales_person_id', $sales_person_id);
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

    public function updateAllCustomerBySalesPersonId($id_sp, $company_id = null)
    {
        $this->db->where('company_id', $company_id ?? $this->session->userdata('company_id'));
        $this->db->where('sales_person_id', $id_sp);
        if ($this->db->update('companies', array('sales_person_id' => null, 'sales_person_ref' => null))) {
            return true;
        }
        return false;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
    public function getDataSalesPerson($kd_distributor)
    {
        $q = $this->db->get_where('api_integration', ['type' => "sycn_sales_person"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        try {
            $URL    = $integration->uri;
            $ch     = curl_init($URL);
            $point  = http_build_query([trim('id_distributor') => trim($kd_distributor)]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $point);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/x-www-form-urlencoded', 'token:' . trim($integration->token)]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data   = json_decode($result, true);
            if (curl_error($ch)) {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
    public function getSalesPersonByreference_no($company_id, $reference_no)
    {
        $q = $this->db->get_where('sales_person', array('reference_no' => $reference_no, 'company_id' => $company_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
}

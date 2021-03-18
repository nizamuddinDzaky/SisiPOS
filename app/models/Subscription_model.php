<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Subscription_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getAddons_row($where = array(), $select=null)
    {
        if($select){
            $this->db->select($select);
        }
        if($where){
            $this->db->where($where);
        }
        $q = $this->db->get_where('addons');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAddons_result($where = array(), $select=null)
    {
        if($select){
            $this->db->select($select);
        }
        if($where){
            $this->db->where($where);
        }
        $q = $this->db->get_where('addons');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getPlan_row($where = array(), $select=null)
    {
        if($select){
            $this->db->select($select);
        }
        if($where){
            $this->db->where($where);
        }
        $q = $this->db->get_where('plans');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPlan_result($where = array(), $select=null)
    {
        if($select){
            $this->db->select($select);
        }
        if($where){
            $this->db->where($where);
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where('plans');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
    
    public function getAuthor_row($where = array(), $select=null)
    {
        if($select){
            $this->db->select($select);
        }
        if($where){
            $this->db->where($where);
        }
        $q = $this->db->get_where('authorized');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBillingPaymentByInv($id)
    {
        $q = $this->db->get_where('billing_payments', ['billing_invoice_id'=>$id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function user_free()
    {
        $this->db->select("COUNT(plan_id) as id");
        $this->db->join("companies", 'companies.id = authorized.company_id');
        $this->db->where('companies.is_deleted', NULL);
        $this->db->where('companies.is_active', '1');
        $this->db->where('plan_id', 1);
        $q = $this->db->get('authorized');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function user_basic()
    {
        $this->db->select("COUNT(plan_id) as id");
        $this->db->join("companies", 'companies.id = authorized.company_id');
        $this->db->where('companies.is_deleted', NULL);
        $this->db->where('companies.is_active', '1');
        $this->db->where('plan_id', 2);
        $q = $this->db->get('authorized');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function payment_basic()
    {
        $this->db->select("SUM(amount) as amount");
        $this->db->where('billing_id !=', null);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function payment_addon()
    {
        $this->db->select("SUM(subtotal) as subtotal");
        $q = $this->db->get('billing_invoice_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function user_basic_result()
    {
        $this->db->select('authorized.*, companies.company');
        $this->db->where('plan_id', 2);
        $this->db->join('companies', 'companies.id=authorized.company_id', 'left');
        $q = $this->db->get('authorized');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function new_payment()
    {
        $this->db->select('billing_invoices.total, billing_invoices.company_name');
        $this->db->join('payments', 'payments.billing_id=billing_invoices.id', 'left');
        $this->db->order_by('payments.date', 'desc');
        $this->db->limit(5);
        $q = $this->db->get('billing_invoices');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getBillerAktif()
    {
        $this->db->select("companies.id, companies.company, companies.cf1 as code");
        $this->db->join("users", 'companies.id = users.company_id');
        $this->db->where('users.group_id', '2');
        $this->db->where('users.active', '1');
        $this->db->where("(companies.client_id != 'aksestoko' OR companies.client_id IS NULL)");
        $this->db->where('companies.is_deleted', NULL);
        $this->db->where('companies.is_active', '1');
        $this->db->order_by('companies.company', 'ASC');
        $q = $this->db->get_where('companies', array('companies.group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function set_distributor($data)
    {
        $insert = $this->db->insert('billing_company_set', $data);
        if($insert){
            return true;
        }
        return false;
    }

    public function delete_set_distributor($id)
    {
        $delete = $this->db->delete('billing_company_set',['id' => $id]);
        if($delete){
            return true;
        }
        return false;
    }

    public function cek_set_distributor($id)
    {
        $cek = $this->db->get_where('billing_company_set', ['company_id' => $id]);
        if($cek->num_rows() > 0){
            return false;
        }
        return true;
    }

    public function get_set_distributor()
    {
        $cek = $this->db->get('billing_company_set');
        if($cek->num_rows() > 0){
            return $cek->result();
        }
        return false;
    }

    public function updateAuthor($data)
    {
        $company_id = $data['company_id'];
        unset($data['company_id']);
        if ($this->db->update('authorized', $data, ['company_id' => $company_id])) {
            return true;
        }
        return false;
    }

    public function getCompany($id=null)
    {
        $join = "(SELECT email, company_id FROM sma_users
                    WHERE group_id = '2'
                ) sma_join ";
        if($id){
            $this->db->where('companies.id', $id);
        }
            $this->db
                ->select('companies.*, sma_join.email as user_email')
                ->join($join, 'sma_join.company_id = companies.id', 'left')
                ->from("companies");
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

    public function getCompanyresult($id=null)
    {
        $join = "(SELECT email, company_id FROM sma_users
                    WHERE group_id = '2' AND `active` = '1'
                ) sma_join ";
        if($id){
            $this->db->where('companies.id', $id);
        }
            $this->db
                ->select('companies.id, 
                    companies.company, 
                    companies.cf1, 
                    sma_join.email as user_email,
                    authorized.create_on, 
                    authorized.plan_id,
                    authorized.plan_name,
                    authorized.start_date,
                    authorized.expired_date,
                    authorized.users,
                    authorized.warehouses,
                    "1" as payment_period,
                    "1" as payment_done,
                    "1" as send_email')
                ->join($join, 'sma_join.company_id = companies.id', 'inner')
                ->join('authorized', 'authorized.company_id = sma_join.company_id', 'left')
                ->from("companies");
        $q = $this->db->get();
        return $q->result();
    }

    public function import_billing($data = array())
    {
        $this->empty_table('billing_company_set');
        if ($this->db->insert_batch('billing_company_set', $data)) {
            return true;
        }
        return false;
    }

    public function empty_table($table_name)
    {
        $run = $this->db->empty_table($table_name);
        if($run){
            return true;
        }
        return false;
    }

    public function setBillingInv($data, $addon=null, $authorize)
    {
        if($data['plan_id'] != 1){
            if ($this->db->insert('billing_invoices', $data)) {
                $billing_invoice_id = $this->db->insert_id();
                $this->site->updateReference('binv', $data['company_id']);
                if ($addon) {
                    foreach ($addon as $k => $item) {
                        if($item['addon_name'] == 'user'){
                            if($item['quantity'] > 0){
                                $item['billing_invoice_id'] = $billing_invoice_id;
                                $this->db->insert('billing_invoice_items', $item);
                            }
                            $addon_item['user'] = $item['quantity'];
                        }
                        if($item['addon_name'] == 'warehouse'){
                            if($item['quantity'] > 0){
                                $item['billing_invoice_id'] = $billing_invoice_id;
                                $this->db->insert('billing_invoice_items', $item);
                            }
                            $addon_item['warehouse'] = $item['quantity'];
                        }
                    }
                }

                $data['additional_user'] = $addon_item['user'];
                $data['additional_warehouse'] = $addon_item['warehouse'];
                //$this->billing_set_history($data);

                if($data['payment_status'] != 'pending'){
                    $plan_detail = $this->site->getPlanPricingByID($data['plan_id']);
                    $plan_user = $plan_detail->users;
                    $plan_wh = $plan_detail->warehouses;

                    $data_author = [
                        'users' => $this->sma->formatDecimal($plan_user) + $addon_item['user'],
                        'warehouses' => $this->sma->formatDecimal($plan_wh) + $addon_item['warehouse'],
                        'plan_id' => $data['plan_id'],
                        'plan_name' => $data['plan_name'],
                        'status' => 'activated',
                        'start_date' => $data['start_date'],
                        'expired_date' => $data['end_date']
                    ];

                    $data_payment = [
                        'date' => date('Y-m-d H:i:s'),
                        'reference_no' => $data['reference_no'],
                        'paid_by' => 'bank',
                        'amount' => $data['total'],
                        'created_by' => $this->session->userdata('user_id'),
                        'type' => 'received',
                        'company_id' => $this->session->userdata('company_id'),
                        'billing_id' => $billing_invoice_id
                    ];
                    $data_bill_payment = array(
                        'date'              => date('Y-m-d H:i:s'),
                        'reference_no'      => $data['reference_no'],
                        'billing_invoice_id' => $billing_invoice_id,
                        'amount'            => $data['total'],
                        'updated_at'        => date('Y-m-d H:i:s'),
                        'updated_by'        => $this->session->userdata('user_id'),
                        'created_by'        => $this->session->userdata('user_id'),
                        'company_id'        => $data['company_id']
                    );

                    if ($this->db->insert('billing_payments', $data_bill_payment)) {
                        if ($this->db->insert('payments', $data_payment)) {
                            $update = $this->db->update('authorized', $data_author, ['id'=>$data['authorized_id']]);
                            if($update){ 
                                return true; 
                            }
                            return false;
                        }
                        return false;
                    }
                    return false;
                }
                else{
                    return true; 
                }
            }
            return false;
        }
        else{
            if($authorize->plan_id != 1){
                $exp_date = strtotime($authorize->expired_date);
                $now = strtotime(date('Y-m-d'));
                if($exp_date < $now){
                    $set_free = true;
                }
                else{
                    $set_free = false;
                }
            }
            else{
                $set_free = true;
            }


            if($set_free == true){
                $date = date('Y-m-d H:i:s');
                $data_history = array(
                    'date' => $date,
                    'plan_id' => $data['plan_id'],
                    'plan_name' => $data['plan_name'],
                    'payment_period' => 0,
                    'price' => 0,
                    'subtotal' => 0,
                    'total' => 0,
                    'company_name' => $data['company_name'],
                    'company_id' => $data['company_id'],
                    'created_by' => $this->session->userdata('user_id'),
                    'additional_user' => 0,
                    'additional_warehouse' => 0,
                    'authorized_id' => $authorize->id,
                    'reference_no' => '',
                    'due_date' => null,
                    'payment_status' => '',
                    'billing_status' => '',
                    'start_date' => null,
                    'end_date' => null
                );

                //$set_history = $this->billing_set_history($data_history);

                unset($data['additional_user']);
                unset($data['additional_warehouse']);
                unset($data['company_id']);
                unset($data['company_name']);

                $update = $this->db->update('authorized', $data, ['id'=>$authorize->id]);
                if($update){ 
                    return true; 
                }
                return false;
            }
            else{
                return true;
            }
        }
    }

    public function pay_from_admin($data_bill_pay, $data_pay, $billing)
    {
        $billing_status = $billing->billing_status;
        $bill_id = $billing->billing_id;
        $comp_id = $billing->company_id;

        if($billing_status == 'pending'){
            $cek = $this->db->get_where('billing_payments', ['billing_invoice_id' => $bill_id, 'company_id' => $comp_id])->num_rows();
            if ($cek > 0) {
                $this->db->delete('billing_payments', ['billing_invoice_id' => $bill_id, 'company_id' => $comp_id]);
            }
            if ($this->db->insert('billing_payments', $data_bill_pay)) {
                if ($this->db->insert('payments', $data_pay)) {
                    if(!$this->updateAuthorized($billing, $data_pay['date'])){
                        return false;
                    }
                    return true;
                }
                return false;
            }
            return false;
        }
        else{
            $cek = $this->db->get_where('billing_payments', ['billing_invoice_id' => $bill_id, 'company_id' => $comp_id])->num_rows();
            if ($cek > 0) {
                $this->db->delete('billing_payments', ['billing_invoice_id' => $bill_id, 'company_id' => $comp_id]);
            }
            if ($this->db->insert('billing_payments', $data_bill_pay)) {
                if ($this->db->insert('payments', $data_pay)) {
                    $author = $this->getAuthor($comp_id);
                    $exp_date = $author->expired_date;

                    $start_new = date('Y-m-d', strtotime('+1 day', strtotime($exp_date)));
                    $end_new = date('Y-m-d', strtotime('+' . $billing->payment_period . ' months', strtotime($start_new)));

                    $data_author = ['expired_date'=>$end_new, 'email_notif'=>null];
                    $where_author = ['id'=>$billing->authorized_id];

                    $data_bill = ['payment_status' => 'paid', 'billing_status' => 'active', 'start_date' => $start_new, 'end_date' => $end_new];
                    $where_bill = ['id' => $bill_id];

                    if ($this->db->update('authorized', $data_author, $where_author) && $this->db->update('billing_invoices', $data_bill, $where_bill)) {
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
    }

    public function updateAuthorized($billing, $date)
    {
        $payment_period = $billing->payment_period;

        $auth = $this->db->get_where('authorized', array('id' => $billing->authorized_id))->row();
        $end = date('Y-m-d', strtotime('+' . $payment_period . ' months', strtotime($date)));
        $binv_item = $this->getBillingInvItem($billing->id);

        $plan_detail = $this->site->getPlanPricingByID($billing->plan_id);
        $plan_user = $plan_detail->users;
        $plan_wh = $plan_detail->warehouses;

        $kelipatan = 5;

        foreach ($binv_item as $item) {
            if ($item->addon_name == 'user') {
                $qty = (int) $item->quantity;
                for ($i = $qty; $i < $kelipatan + $qty; $i++) {
                    if ($i % $kelipatan == 0) {
                        $qty_item = $i;
                        break;
                    }
                }
                $total_user = $this->sma->formatDecimal($plan_user) + $qty_item;
            } elseif ($item->addon_name == 'warehouse') {
                $qty = (int) $item->quantity;
                for ($i = $qty; $i < $kelipatan + $qty; $i++) {
                    if ($i % $kelipatan == 0) {
                        $qty_item = $i;
                        break;
                    }
                }
                $total_wh = $this->sma->formatDecimal($plan_wh) + $qty_item;
            }
        }

        $data_author = ['status'=>'activated',
                        'plan_name'=>$billing->plan_name,
                        'plan_id'=>$billing->plan_id,
                        'start_date'=>$date,
                        'expired_date'=>$end, 
                        'users'=>($total_user?$total_user:$plan_user),
                        'warehouses'=>($total_wh?$total_wh:$plan_wh),
                        'email_notif'=>null];
        $where_author = ['company_id'=>$billing->company_id];

        $data_bill = ['payment_status' => 'paid', 'billing_status' => 'active', 'start_date' => $date, 'end_date' => $end];
        $where_bill = ['id' => $billing->id];

        if ($this->db->update('authorized', $data_author, $where_author) && $this->db->update('billing_invoices', $data_bill, $where_bill)) {
            return true;
        }
        return false;
    }

    public function getAuthor($company_id = null)
    {
        if ($company_id) {
            $company_id = $company_id;
        } else {
            $company_id = $this->session->userdata('company_id');
        }
        $q = $this->db->get_where('authorized', array('company_id' => $company_id));
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

    public function billing_set_history($data)
    {
        $insert = $this->db->insert('billing_set_history', $data);
        if($insert){
            return true;
        }
        return false;
    }
}

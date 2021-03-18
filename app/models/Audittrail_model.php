<?php defined('BASEPATH') or exit('No direct script access allowed');

class Audittrail_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->customer_registration = 'customer_registration';
        $this->customer_activation = 'customer_activation';
        $this->customer_create_order = 'customer_create_order';
        $this->distributor_change_price = 'distributor_change_price';
        $this->customer_approve_price = 'customer_approve_price';
        $this->distributor_create_delivery = 'distributor_create_delivery';
        $this->customer_confirm_delivery = 'customer_confirm_delivery';
        $this->customer_create_payment = 'customer_create_payment';
        $this->distributor_sales_return = 'distributor_sales_return';
        $this->distributor_approve_payment = 'distributor_approve_payment';
        $this->customer_reject_price = 'customer_reject_price';
        $this->distributor_reject_payment = 'distributor_reject_payment';
        $this->customer_set_referal_code = 'customer_set_referal_code';
    }

    public function insertCustomerRegistration($customer_user_id, $customer_company_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_registration,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertCustomerActivation($customer_user_id, $customer_company_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_activation,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertCustomerCreateOrder($customer_user_id, $customer_company_id, $distributor_company_id,$sale_id, $purchase_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_create_order,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'sale_id'               =>$sale_id,
            'purchase_id'           =>$purchase_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertDistributorChangePrice($customer_company_id, $distributor_user_id, $distributor_company_id,$sale_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->distributor_change_price,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'distributor_user_id'   =>$distributor_user_id,
            'sale_id'               =>$sale_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertCustomerApprovePrice($customer_user_id, $customer_company_id, $distributor_company_id,$sale_id, $purchase_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_approve_price,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'sale_id'               =>$sale_id,
            'purchase_id'           =>$purchase_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertCustomerRejectPrice($customer_user_id, $customer_company_id, $distributor_company_id,$sale_id, $purchase_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_reject_price,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'sale_id'               =>$sale_id,
            'purchase_id'           =>$purchase_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertDistributorCreateDelivery($distributor_user_id, $customer_company_id, $distributor_company_id, $sale_id, $delivery_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->distributor_create_delivery,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'distributor_user_id'   =>$distributor_user_id,
            'sale_id'               =>$sale_id,
            'delivery_id'           =>$delivery_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertCustomerConfirmDelivery($customer_user_id, $customer_company_id, $distributor_company_id,$sale_id, $purchase_id, $delivery_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_confirm_delivery,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'sale_id'               =>$sale_id,
            'delivery_id'           =>$delivery_id,
            'purchase_id'           =>$purchase_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertCustomerCreatePayment($customer_user_id, $customer_company_id, $distributor_company_id,$sale_id, $purchase_id, $payment_temp_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_create_payment,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'sale_id'               =>$sale_id,
            'payment_temp_id'       =>$payment_temp_id,
            'purchase_id'           =>$purchase_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertDistributorConfirmPayment($distributor_user_id, $customer_company_id, $distributor_company_id, $sale_id, $purchase_id, $sale_payment_id, $purchase_payment_id, $payment_temp_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->distributor_approve_payment,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'distributor_user_id'   =>$distributor_user_id,
            'sale_id'               =>$sale_id,
            'sale_payment_id'       =>$sale_payment_id,
            'purchase_payment_id'   =>$purchase_payment_id,
            'payment_temp_id'       =>$payment_temp_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertDistributorRejectPayment($distributor_user_id, $customer_company_id, $distributor_company_id, $sale_id, $purchase_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->distributor_reject_payment,
            'customer_company_id'   =>$customer_company_id,
            'distributor_company_id'=>$distributor_company_id,
            'distributor_user_id'   =>$distributor_user_id,
            'sale_id'               =>$sale_id,
            'created_at'            =>date('Y-m-d H:i:s'),
            'updated_at'            =>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }

    public function insertCustomerSetReferralCode($customer_user_id, $customer_company_id, $sales_person_id){
        $dataAuditrail = [
            'ip_address'            =>$this->input->ip_address(),
            'type'                  =>$this->customer_set_referal_code,
            'customer_user_id'      =>$customer_user_id,
            'customer_company_id'   =>$customer_company_id,
            'sales_person_id'       =>$sales_person_id,
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if(!$this->db->insert('sma_log_audit_trail', $dataAuditrail))
            return false;
        return true;
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

class Payment_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return "Home Index";
    }

    public function addPaymentTemp($data, $thirdParty = false)
    {
        if ($this->db->insert('payment_temp', $data)) {
            $id = $this->db->insert_id();
            // if(!$thirdParty){
            $this->updateTransaction($data, $thirdParty);
            // }
            return $id;
        } else {
            throw new \Exception("Gagal Menyimpan Bukti Transfer");
            return false;
        }
        return false;
    }

    public function updateTransaction($data, $thirdParty)
    {

        if (!$this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'payment_status' => 'waiting'), array('id' => $data['sale_id']))) {
            throw new \Exception("Gagal Menyimpan Data");
        }

        if (!$thirdParty) {
            if (!$this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'payment_status' => 'waiting'), array('id' => $data['purchase_id']))) {
                throw new \Exception("Gagal Menyimpan Data");
            }
        }
    }

    public function getPaymentTempByPurchaseId($purchase_id)
    {
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');

        $purchases = $this->at_purchase->getPurchaseByID($purchase_id);

        $this->db->order_by('id', 'asc');
        $this->db->where('purchase_id', $purchase_id);

        if (
            $purchases->payment_method == 'kredit_pro'
        ) {
            $this->db->where('third_party', 'kredit_pro');
        }
        $q = $this->db->get('payment_temp');
        // print_r($purchase_id);die;
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function findPaymentTempByRef($ref)
    {
        $q = $this->db->get_where('payment_temp', array('reference_no' => $ref));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function updatePaymentTemp($data, $where)
    {
        return $this->db->update('payment_temp', $data, $where);
    }

    public function getListPaymentTemp($purchase_id)
    {
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');

        $purchases = $this->at_purchase->getPurchaseByID($purchase_id);

        $this->db->order_by('id', 'asc');
        $this->db->where('purchase_id', $purchase_id);

        if (
            $purchases->payment_method == 'kredit_pro'
        ) {
            $this->db->where('third_party', 'kredit_pro');
        }

        $q = $this->db->get('payment_temp');
        // $this->db->order_by('id', 'asc');
        // $q = $this->db->get_where('payment_temp', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
    }

    public function getTotalPaymentByPoId($purchase_id)
    {
        $this->db->select('SUM(nominal) as total');
        $this->db->where('status !=', 'reject');
        $this->db->where('purchase_id', $purchase_id);
        $q = $this->db->get('payment_temp');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getPaymentPending($purchase_id)
    {
        $this->db->select('payment_temp.*');
        $this->db->where('status', 'pending');
        $this->db->where('purchase_id', $purchase_id);
        $q = $this->db->get('payment_temp');
        if ($q->num_rows() > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getKreditLimit($customer_group_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sma_customer_groups', array('id' => $customer_group_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getTotalDebt($company_id, $purchase_id, $supplier_id)
    {
        $this->db->select('SUM(sma_purchases.grand_total)-SUM(sma_purchases.paid) as total');
        $this->db->where('sma_purchases.company_id', $company_id);
        $this->db->where('sma_purchases.supplier_id', $supplier_id);
        $this->db->where('sma_purchases.bank_id IS NOT NULL');
        $this->db->where('sma_purchases.status != \'canceled\'');
        $this->db->where('sma_purchases.payment_method = \'kredit\'');
        $q = $this->db->get('sma_purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getPaymentMethodByCompanyId($company_id)
    {
        $this->db->select('payment_methods.id, payment_methods.name,payment_methods.value');
        $this->db->join('sma_payment_methods', '(company_payment_methods.payment_method_id=payment_methods.id AND payment_methods.is_active =1)', 'inner');
        $this->db->where('company_payment_methods.company_id', $company_id);
        $this->db->where('company_payment_methods.is_active', 1);
        $q = $this->db->get('company_payment_methods');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
    }

    public function getPaymentMethodByCompanyIdreject($company_id)
    {
        $this->db->select('payment_methods.id, payment_methods.name,payment_methods.value');
        $this->db->join('sma_payment_methods', '(company_payment_methods.payment_method_id=payment_methods.id AND payment_methods.is_active =1)', 'inner');
        $this->db->where('company_payment_methods.company_id', $company_id);
        $this->db->where('company_payment_methods.is_active', 1);
        $this->db->where('payment_methods.value !=', 'kredit_pro');
        $q = $this->db->get('company_payment_methods');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
    }

    public function getTOP($supplier_id = null)
    {
        $this->db->select('duration, description')->order_by('duration', 'ASC')->where('is_active', 1)->where('company_id', $supplier_id ?? $this->session->userdata('supplier_id'));
        $tempo = $this->db->get('top');
        if ($tempo->num_rows() > 0) {
            return $tempo->result();
        }
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

    public function insertLoadInvoice($data)
    {
        if ($this->db->insert('load_invoice', $data)) {
            $id = $this->db->insert_id();
            return $id;
        }
        return false;
    }

    public function insertLoanInquiry($data)
    {
        if ($this->db->insert('loan_inquiry', $data)) {
            $id = $this->db->insert_id();
            return $id;
        }
        return false;
    }

    public function getLimitMandiri()
    {
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('integration_model', 'integration');

        $where = ['company_id' => $this->session->userdata('company_id')];
        $loan = $this->at_site->getLoanRequest($where);

        if ($loan->statusLoan == 'Approve') {
            $data_req = [
                "limitInquiryService" => [
                    "arg0" =>  [
                        "requestID"     =>  getUuid(),
                        "ccy"           =>  "IDR",
                        "sellerCode"    =>  $this->session->userdata('supplier_id'),
                        "buyerCode"     =>  $this->session->userdata('company_id'),
                        "productType"   =>  "RTF",
                        "limitNode"     =>  "Risk Owner"
                    ]
                ]
            ];

            try {
                $limitInquiry = $this->integration->mandiri_limitInquiry($data_req);
                $limit = (float)$limitInquiry->availableLimit;
            } catch (\Throwable $th) {
                $limit = 1000000;
            }

            $balance = 0;
            $sql =  "SELECT sum(grand_total) grand_total_sum, sum(paid) paid_sum,  sum(grand_total)- sum(paid)`balance_sum` FROM `sma_purchases` WHERE `company_id` = '" . $this->session->userdata('company_id') . "' AND `status` != 'canceled' AND `payment_method` = 'kredit_mandiri'";
            $get_purchase = $this->db->query($sql);
            if ($get_purchase->num_rows() > 0) {
                $balance = (float)$get_purchase->row()->balance_sum;
            }
            $sisa_limit = $limit - $balance;

            return $sisa_limit;
        } else {
            return '-';
        }
    }
}

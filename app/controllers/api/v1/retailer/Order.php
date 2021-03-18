<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Retailer_Controller.php';

class Order extends MY_API_Retailer_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->token = $this->getTokenValue();
        $this->Admin = true;

        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/at_company_model', 'at_company');
        $this->load->model('aksestoko/bank_model', 'bank');
        $this->load->model('aksestoko/Payment_model', 'payment');
        $this->load->model('aksestoko/promotion_model', 'promotion');
        $this->load->model('aksestoko/product_model', 'product');
        $this->load->model('integration_model', 'integration');
        $this->load->model('site', 'site');
        $this->load->model('audittrail_model', 'audittrail');
        $this->load->model('Sales_model', 'sales_model');
        $this->load->model('companies_model');
        $this->data['logo'] = true;
        $this->data['array_payment_method'] = [
            'cash on delivery', 'kredit'
        ];
    }

    public function index_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize($this->token);

            if (!$auth) {
                throw new \Exception("unauthorized", 401);
            }

            $status = $this->input->get('status') ?? "on_going"; //complete
            $orders = null;


            if ($status == "on_going") {
                $orders = $this->at_purchase->getOrdersOnGoing($auth->user->id, 10, 0, null);
            }

            $response = $orders;

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil mendapatkan data order", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    //=========================================================//

    
}

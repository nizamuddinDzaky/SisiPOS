<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_REST_Controller.php';

/** 
 * Crm
 **/ 
class Crm extends MY_REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->db->trans_begin();
        $this->token = $this->getTokenValue();
        $this->crm_token = '^&crm_smig&^';

        $this->load->model('db_model');
    }

    public function aksestoko_sales_get(){
        $this->db->trans_begin();
        try {
            if(!$this->token || !$this->token->crm_token || $this->token->crm_token != $this->crm_token){
                throw new Exception("unauthorized", 401);
            }
            $customer_code = $this->input->get('customer_code');
            if(!$customer_code){
                throw new Exception("parameter customer_code required", 400);
            }
            $sales = $this->db_model->getAksestokoSalesByIdbk($customer_code);
            if(!$sales){
                throw new Exception("error get aksestoko sales data", 404);
            }
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "success get aksestoko sales data", $sales);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}

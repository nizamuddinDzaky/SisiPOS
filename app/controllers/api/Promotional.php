<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_REST_Controller.php';

/** 
 * Promotional
 **/ 
class Promotional extends MY_REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->db->trans_begin();
        $this->token = $this->getTokenValue();
        $this->promotional_token = '!0TPC0!';

        $this->load->model('db_model');
    }

    public function customers_get(){
        $this->db->trans_begin();
        try {
            if(!$this->token || !$this->token->promotional_token || $this->token->promotional_token != $this->promotional_token){
                throw new Exception("unauthorized", 401);
            }
            $customers = $this->db_model->getAllUserAksestokoForPromotional();
            if(!$customers){
                throw new Exception("Error get customers data", 404);
            }
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "success get customers data", $customers);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function distributors_get(){
        $this->db->trans_begin();
        try {
            if(!$this->token || !$this->token->promotional_token || $this->token->promotional_token != $this->promotional_token){
                throw new Exception("unauthorized", 401);
            }
            $distributors = $this->db_model->getDistributorAksestokoForPromotional();
            if(!$distributors){
                throw new Exception("Error get distributors data", 404);
            }
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "success get distributors data", $distributors);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}

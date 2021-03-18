<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_REST_Controller.php';

/** 
 * Aksestoko
 **/ 
class Kredit_mandiri extends MY_REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->Admin = true;
        $this->load->model('integration_model', 'integration');
    }

    public function application_status_post()
    {
        $this->db->trans_begin();
        try {
            $integration = $this->integration->findApiIntegrationByType('kredit_mandiri_application_status');
            $token = $this->input->get_request_header("Forca-token");
        
            if ($token != $integration->token) {
                throw new \Exception("unauthorized", 401);
            }

            $IdOutlet       = @$this->post('IdOutlet');
            $LoanId         = @$this->post('LoanId');
            $Status         = @$this->post('Status');
            $Keterangan     = @$this->post('Keterangan');
            $Rate           = @$this->post('Rate');
            $Limit          = @$this->post('Limit');
            $Tenor          = @$this->post('Tenor');
            $Installment    = @$this->post('Installment');
            $UpdateDate     = @$this->post('UpdateDate');

            $this->load->model('aksestoko/at_site_model', 'at_site');
            $where = ['loanID' => $LoanId];
            $getLoan = $this->at_site->getLoanRequest($where);
            if(!$getLoan){
                throw new Exception("loanID not found.", 400);
            }
            
            $data = [ 
                'IdOutlet'      => $IdOutlet,
                'LoanId'        => $LoanId, 
                'Status'        => $Status, 
                'Keterangan'    => $Keterangan,
                'Rate'          => $Rate,
                'Limit'         => $Limit,
                'Tenor'         => $Tenor,
                'Installment'   => $Installment,
                'UpdateDate'    => $UpdateDate,
                'user_id'       => $getLoan->user_id,
                'company_id'    => $getLoan->company_id,
            ];

            $insertLoan = $this->at_site->insertLoanStatus($data);
            if (!$insertLoan) {
                throw new Exception("Insert loan data failed", 400);
            } 

            $ex = explode('=', $Keterangan);
            $dataLoan = [
                'statusLoan'    => $Status,
                'company_code'  => ($Status == 'Approve' ? trim($ex[1]) : ''),
                'dateStatusLoan'=> date('Y-m-d H:i:s'),
            ];

            $where = ['id'=>$getLoan->id];
            $updateLoan = $this->at_site->updateLoanRequest($dataLoan, $where);
            if (!$updateLoan) {
                throw new \Exception("Gagal memperbarui data loan");
            }
            $response = [
                'LoanId' => $LoanId,
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'Success', $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require 'MainController.php';

class Auth extends MainController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('aksestoko/at_auth_model', 'at_auth');
        $this->lang->load('auth', $this->Settings->user_language);
    }

    public function login_post()
    {
        $this->db->trans_begin();
        try {
            $config = [
                [
                    'field' => 'username',
                    'label' => 'username',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'password',
                    'label' => 'password',
                    'rules' => 'required',
                    'errors' => $this->errors
                ]
            ];

            $this->validate_form($config);

            $username    = $this->body('username');
            $password    = $this->body('password');

            $login = $this->at_auth->loginAT($username, $password, false);

            if (!$login) {
                throw new \Exception("Gagal login");
            }
            
            $this->db->trans_commit();

            $response = [
                'user_id' => $this->session->userdata('user_id'),
                'company_id' => $this->session->userdata('company_id'),
            ];

            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil login", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }
}

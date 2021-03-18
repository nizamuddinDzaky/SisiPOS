<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class MY_REST_Controller extends REST_Controller {

    public $request_time;

    public $key;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Site', 'site');
        $this->load->library('form_validation');
        $this->Settings = $this->site->get_setting();
        $this->request_time = date("Y-m-d H:i:s");
        $this->key = APP_TOKEN;
    }

    function getTokenValue()
    {
        $token = $this->input->get_request_header("Authorization");
        if (!$token) {
            return null;
        }
        $data = json_decode($this->decrypt($token, $this->key));
        if (!$data) {
            return null;
        }

        return $data;
    }

    function authorize($data) {
        
        $this->load->model('Companies_model', 'companies');

        if (!$data) {
            return null;
        }

        $company = $this->companies->findCompanyByCf1($data->dist_code);
        
        if(!$company) {
            return null;
        }
        
        $user = $this->site->findUserByCompanyId($company->id);
        if (!$user) {
            return null;
        } 
        
        return (object) [
            "company" => $company,
            "user" => $user
        ];
    }

    function authorizeMaster($data, $warehouse)
    {
        $this->load->model('Companies_model', 'companies');

        if (!$data) {
            return null;
        }

        if (!$warehouse && $warehouse == '') {
            return null;
        }

        $company = $this->companies->findCompanyByCf1AndCf2($warehouse, $data->application_from);
        
        if(!$company) {
            return null;
        }
        $user = $this->site->findUserByCompanyId($company->id);
        if (!$user) {
            return null;
        } 
        return (object) [
            "company" => $company,
            "user" => $user
        ];
    }

    function authorizeTransaction($data, $company_id)
    {
        $this->load->model('Companies_model', 'companies');
        
        if (!$data) {
            return null;
        }
        
        if (!$company_id && $company_id == '') {
            return null;
        }

        $company = $this->companies->findCompanyByCf2AndId($data->application_from, $company_id);
        if(!$company) {
            return null;
        }
        $user = $this->site->findUserByCompanyId($company->id);
        if (!$user) {
            return null;
        } 
        return (object) [
            "company" => $company,
            "user" => $user
        ];
    }

    public function isSuperToken($data)
    {
        if (!property_exists($data, 'application_from')) {
            return false;
        }
        return true;
    }

    public function getAuthMaster($data, $warehouse)
    {
        if ($this->isSuperToken($data)) {
            $auth = $this->authorizeMaster($data, $warehouse);
        }else{
            $auth = $this->authorize($data);
        }
        $this->set_session($auth);
        return $auth;
    }

    public function getAuthTransaction($data, $company_id)
    {
        if ($this->isSuperToken($data)) {
            $auth = $this->authorizeTransaction($data, $company_id);
        }else{
            $auth = $this->authorize($data);
        }
        $this->set_session($auth);
        return $auth;
    }

    function buildResponse($status, $code, $message, $data = null){
        $response = [
            "status" => $status,
            "code" => $code,
            "message" => $message,
            "request_time" => $this->request_time,
            "response_time" => date("Y-m-d H:i:s"),
            "rows" => $data ? count($data) : null,
            "data" => $data
        ];

        $this->load->model('integration_model', 'integration');
        $data_log = [
            'method' => $this->input->method(true),
            'url' => current_url(),
            'headers' => json_encode($this->input->request_headers()),
            'body' => json_encode($this->post()),
            'parameters' => json_encode($this->input->get()),
            'io_type' => 'in',
            'ssl_status' => true,
            'response' => json_encode($response),
            'note' => $message
        ];
        $this->integration->insertApiLog($data_log);

        return $this->set_response($response, $code);
    }

    function encrypt($text, $key)
    {
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($text, $cipher, $key, $options=0, $iv, $tag);
        return base64_encode($ciphertext.'::'.$tag.'::'.$iv);
    }

    function decrypt($text, $key)
    {
        $data = explode('::', base64_decode($text));
        $cipher = "aes-128-gcm";
        $tag = $data[1];
        $original_plaintext = openssl_decrypt($data[0], $cipher, $key, $options=0, $data[2] , $tag);
        return $original_plaintext;
    }

    function set_session($auth){
        $session_data = array(
            'identity'         => $auth->user->username,
            'username'         => $auth->user->username,
            'email'            => $auth->user->email,
            'user_id'          => $auth->user->id, 
            'old_last_login'   => $auth->user->last_login,
            'last_ip'          => $auth->user->last_ip_address,
            'avatar'           => $auth->user->avatar,
            'gender'           => $auth->user->gender,
            'group_id'         => $auth->user->group_id,
            'warehouse_id'     => $auth->user->warehouse_id,
            'view_right'       => $auth->user->view_right,
            'edit_right'       => $auth->user->edit_right,
            'allow_discount'   => $auth->user->allow_discount,
            'biller_id'        => $auth->user->biller_id,
            'company_id'       => $auth->user->company_id,
            'show_cost'        => $auth->user->show_cost,
            'show_price'       => $auth->user->show_price,
            'company_name'     => $auth->user->company,
        );

        // var_dump($session_data);die;

        $this->session->set_userdata($session_data);
    }
}
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class MY_API_Distributor_Controller extends REST_Controller {

    public $request_time;

    public $key;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Site', 'site');
        $this->load->library('sma');

        $this->Settings = $this->site->get_setting();
        
        if ($sma_language = $this->input->cookie('sma_language', TRUE)) {
            $this->config->set_item('language', $sma_language);
            $this->lang->load('sma', $sma_language);
            $this->Settings->user_language = $sma_language;
        } else {
            $this->config->set_item('language', $this->Settings->language);
            $this->lang->load('sma', $this->Settings->language);
            $this->Settings->user_language = $this->Settings->language;
        }

        if ($rtl_support = $this->input->cookie('sma_rtl_support', TRUE)) {
            $this->Settings->user_rtl = $rtl_support;
        } else {
            $this->Settings->user_rtl = $this->Settings->rtl;
        }

        $this->load->library('form_validation');
        $this->request_time = date("Y-m-d H:i:s");
        $this->key = APP_API_TOKEN;
        $this->body = $this->get_body();
        $this->token = null;
    }

    function getTokenValue()
    {
        $token = $this->input->get_request_header("Forca-Token");
        if (!$token) {
            return null;
        }
        $this->token = $token;
        $data = json_decode($this->decrypt($token, $this->key));
        if (!$data) {
            return null;
        }

        return $data;
    }

    function authorize() {

        $data = $this->getTokenValue();

        $this->load->model('Companies_model', 'companies');

        if (!$data) {
            throw new \Exception("unauthorized", 401);
        }

        $company = $this->companies->findCf1ById($data->company_id);
        
        if(!$company) {
            throw new \Exception("unauthorized", 401);
        }
        
        $user = $this->site->getUser($data->user_id);
        if (!$user) {
            throw new \Exception("unauthorized", 401);
        } 
        
        $auth = (object) [
            "company" => $company,
            "user" => $user
        ];
        $this->set_session($auth);

        //if logged in
        $this->default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
        $this->data['default_currency'] = $this->default_currency;
        $this->Owner = $this->sma->in_group('owner', $auth->user->id) ? TRUE : NULL;
        $this->Principal = $this->sma->in_group('principal', $auth->user->id) ? TRUE : NULL;
        $this->Customer = $this->sma->in_group('customer', $auth->user->id) ? TRUE : NULL;
        $this->Supplier = $this->sma->in_group('supplier', $auth->user->id) ? TRUE : NULL;
        $this->Admin = $this->sma->in_group('admin', $auth->user->id) ? TRUE : NULL;
        $this->Manager = $this->sma->in_group('areamanager', $auth->user->id) ? TRUE : NULL;
        $this->Reseller = $this->sma->in_group('reseller', $auth->user->id) ? TRUE : NULL;
        $this->LT = $this->sma->in_group('toko besar', $auth->user->id) ? TRUE : NULL;

        if (!$this->sma->checkMenuPermissions()) {
            throw new Exception(lang('access_denied'), 403);
        }

        return $auth;
    }

    function buildResponse($status, $code, $message, $data = null){
        $response = [
            "status" => $status,
            "code" => $code,
            "message" => strip_tags($message),
            "request_time" => $this->request_time,
            "response_time" => date("Y-m-d H:i:s"),
            "rows" => $data ? (is_object($data) ? count((array)$data) : count($data)) : null,
            "data" => $data
        ];

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
            'cf1'              => $auth->company->cf1,
        );

        $this->session->set_userdata($session_data);
    }

    function get_body($assoc = true){
        return json_decode($this->input->raw_input_stream, $assoc) ?? [];
    }

    function fields_required($config){
        $fields = [];
        foreach ($config as $key => $c) {
            if(strpos($c['rules'], 'required') !== false){
                $fields [] = "`".$c['field']."`";
            }
        }
        return implode(", ", $fields);
    }

    function validate_form($config, $data = null){
        $this->form_validation->set_data($data ?? $this->body);
        $this->form_validation->set_rules($config);
        
        if ($this->form_validation->run() == false) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) break;
            throw new Exception($error ?? "{$this->fields_required($config)} required", 400);
        }
    }

    function body($field){
        return $this->body[$field];
    }

    function format_int($nominal){
        if (is_null($nominal)) return null;
        else return ((int) $nominal) . "";
    }

    function unsetFrom2DArray($keys = [], $array2D = [])
    {
        foreach($array2D as $array){
            foreach ($keys as $i => $key) {
                if (is_object($array)) unset($array->$key); 
                else unset($array[$key]);
            }
        }
        return $array2D;
    }
}
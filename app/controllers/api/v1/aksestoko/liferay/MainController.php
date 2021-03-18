<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use \Firebase\JWT\JWT;

class MainController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Site', 'site');
        $this->load->library('form_validation');
        $this->Settings = $this->site->get_setting();
        $this->request_time = date("Y-m-d H:i:s");
        $this->key = ATL_TOKEN;
        $this->body = $this->get_body();
        $this->token = null;

        $this->errors = [
            'required' => '`%s` dibutuhkan',
            'valid_url' => '`%s` bukan url yang valid'
        ];
    }

    function getTokenValue()
    {
        $token = $this->input->get_request_header("Authorization");
        if (!$token) {
            return null;
        }
        $this->token = $token;
        $data = $this->decrypt($token);
        if (!$data) {
            return null;
        }

        return $data;
    }

    function encrypt($data)
    {
        $payload = [
            "iss" => base_url(),
            "aud" => "https://www.aksestoko.com",
            "sub" => "token_integrasi_aksestoko_liferay",
            "name" => "ForcaPOS",
            "iat" => time(),
            "username" => ATL_USERNAME,
            "password" => ATL_PASSWORD
        ];

        $payload = array_merge($payload, $data);

        return JWT::encode($payload, $this->key, 'HS256');
    }
    
    function decrypt($token)
    {
        return JWT::decode($token, $this->key, ['HS256']);
    }

    function authorize() {

        $data = $this->getTokenValue();
        
        $this->load->model('Companies_model', 'companies');

        if (!$data) {
            throw new \Exception("unauthorized", 401);
        }

        $company = $this->companies->findCompanyByCf1($data->dist_code);
        
        if(!$company) {
            throw new \Exception("unauthorized", 401);
        }
        
        $user = $this->site->findUserByCompanyId($company->id);
        if (!$user) {
            throw new \Exception("unauthorized", 401);
        } 
        
        $auth = (object) [
            "company" => $company,
            "user" => $user
        ];

        $this->set_session($auth);

        $this->Admin = true;

        return $auth;
    }

    function buildResponse($status, $code, $message, $data = []) {
        $code = $code != 0 ? $code : 500;
        $response = [
            "status" => $status,
            "status_code" => $code,
            "message" => $message,
            "request_time" => $this->request_time,
            "response_time" => date("Y-m-d H:i:s"),
        ];

        $response = array_merge($response, $data);

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

    function convertStatus($type, $code)
    {
        switch ($type) {
            case 'order':
                switch ($code) {
                    case '111':
                        return "pending";
                        break;
                    case '112':
                        return "reserved";
                        break;
                    case '113':
                        return "canceled";
                        break;
                    case '116':
                        return "closed";
                        break;
                }
                break;
            case 'payment':
                switch ($code) {
                    case '101':
                        return "pending";
                        break;
                    case '102':
                        return "partial";
                        break;
                    case '103':
                        return "paid";
                        break;
                }
                break;
            case 'delivery':
                switch ($code) {
                    case '117':
                        return "packing";
                        break;
                    case '115':
                        return "delivering";
                        break;
                    case '116':
                        return "delivered";
                        break;
                    case '114':
                        return "delivered";
                        break;
                }
                break;
            case 'payment_method':
                switch ($code) {
                    case '1':
                        return "cash on delivery";
                        break;
                    case '2':
                        return "cash before delivery";
                        break;
                    case '3':
                        return "kredit";
                        break;
                    case '4':
                        return "kredit_pro";
                        break;
                }
                break;
            case 'delivery_method':
                switch ($code) {
                    case '11':
                        return "pickup";
                        break;
                    case '22':
                        return "delivery";
                        break;
                }
                break;
            case 'status_kreditpro':
                switch ($code) {
                    case '111':
                        return "waiting";
                        break;
                    case '401':
                        return "accept";
                        break;
                    case '402':
                        return "reject";
                        break;
                }
                break;
        }
        return "unknown";
    }
}

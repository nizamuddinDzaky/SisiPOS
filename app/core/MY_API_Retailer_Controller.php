<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class MY_API_Retailer_Controller extends REST_Controller
{

    public $request_time;

    public $key;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Site', 'site');
        $this->load->library('form_validation');
        $this->Settings       = $this->site->get_setting();
        $this->request_time   = date("Y-m-d H:i:s");
        $this->key            = APP_TOKEN;
        $this->body           = $this->get_body();
        $this->token          = null;
    }

    function getTokenValue()
    {
        $token = $this->input->get_request_header("Aksestoko-Token");
        if (!$token) {
            return null;
        }
        $data = json_decode($this->decrypt($token, $this->key));
        if (!$data) {
            return null;
        }

        return $data;
    }

    function authorize()
    {
        $data = $this->getTokenValue();

        $this->load->model('Companies_model', 'companies');

        if (!$data) {
            throw new \Exception("Unauthorized", 401);
        }

        $company = $this->companies->findCf1ById($data->company_id);

        if (!$company) {
            throw new \Exception("Unauthorized", 401);
        }

        $user = $this->site->getUser($data->user_id);
        if (!$user) {
            throw new \Exception("Unauthorized", 401);
        }

        $auth = (object) [
            "company" => $company,
            "user"    => $user
        ];
        $this->set_session($auth);

        $this->default_currency           = $this->site->getCurrencyByCode($this->Settings->default_currency);
        $this->data['default_currency']   = $this->default_currency;
        return $auth;
    }

    function buildResponse($status, $code, $message, $data = null)
    {
        $response = [
            "status" => $status,
            "code" => $code,
            "message" => $message,
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
        $ciphertext = openssl_encrypt($text, $cipher, $key, $options = 0, $iv, $tag);
        return base64_encode($ciphertext . '::' . $tag . '::' . $iv);
    }

    function decrypt($text, $key)
    {
        $data = explode('::', base64_decode($text));
        $cipher = "aes-128-gcm";
        $tag = $data[1];
        $original_plaintext = openssl_decrypt($data[0], $cipher, $key, $options = 0, $data[2], $tag);
        return $original_plaintext;
    }

    function set_session($auth)
    {
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
            'aksestoko'        => true
        );

        $this->session->set_userdata($session_data);
    }

    function get_body($assoc = true)
    {
        return json_decode($this->input->raw_input_stream, $assoc) ?? [];
    }

    function fields_required($config)
    {
        $fields = [];
        foreach ($config as $key => $c) {
            if (strpos($c['rules'], 'required') !== false) {
                $fields[] = "`" . $c['field'] . "`";
            }
        }
        return implode(", ", $fields);
    }

    function validate_form($config, $data = null)
    {
        $this->form_validation->set_data($data ?? $this->body);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == false) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) break;
            throw new Exception($error ?? "{$this->fields_required($config)} required", 400);
        }
    }

    function body($field)
    {
        return $this->body[$field];
    }

    function __unit($id_unit)
    {
        $unit = $this->at_site->findUnit($id_unit);
        return $unit->name;
    }

    function __convertDate($date)
    {
        $date = strtotime($date);
        $year = date('Y', $date);
        $month = date('m', $date);
        $number = date('d', $date);
        $time = date('H:i', $date);

        switch ($month) {
            case "01":
                $month = "Januari";
                break;
            case "02":
                $month = "Februari";
                break;
            case "03":
                $month = "Maret";
                break;
            case "04":
                $month = "April";
                break;
            case "05":
                $month = "Mei";
                break;
            case "06":
                $month = "Juni";
                break;
            case "07":
                $month = "Juli";
                break;
            case "08":
                $month = "Agustus";
                break;
            case "09":
                $month = "September";
                break;
            case "10":
                $month = "Oktober";
                break;
            case "11":
                $month = "November";
                break;
            case "12":
                $month = "Desember";
                break;
        }

        return "$number $month $year";
    }

    public function __status($status, $param = 0)
    {
        switch ($status) {
            case "ordered":
                return ["Menunggu Konfirmasi", "warning"];
            case "confirmed":
                return ["Dikonfirmasi", "success"];
            case "packing":
                return ["Sedang Dikemas", "warning"];
            case "delivering":
                return ["Dalam Pengiriman", "info"];
            case "delivered":
                return ["Barang Telah Dikirim", "success"];
            case "partial":
                if ($param == 0) {
                    return ["Diterima Sebagian", "info"];
                } elseif ($param == 1) {
                    return ["Dibayar Sebagian", "info"];
                } elseif ($param == 2) {
                    return ["Menunggu Pelunasan", "info"];
                }
                // no break
            case "received":
                return ["Diterima", "success"];
            case "pending":
                if ($param == 0) {
                    return ["Belum Bayar", "warning"];
                } elseif ($param == 1) {
                    return ["Belum Lunas", "warning"];
                } elseif ($param == 2) {
                    return ["Menunggu Konfirmasi", "warning"];
                }

                // no break
            case "waiting":
                if ($param == 1) {
                    return ["Kredit Ditinjau", "info"];
                } elseif ($param == 0) {
                    return ["Menunggu Konfirmasi", "warning"];
                } elseif ($param == 2) {
                    return ["Menunggu Pelunasan", "info"];
                }

                // no break
            case "paid":
                return ["Telah Dibayar", "success"];
            case "canceled":
                return ["Dibatalkan", "danger"];
            case "accept":
                if ($param == 2) {
                    return ["Kredit Diterima", "success"];
                } elseif ($param == 1) {
                    return ["Kredit Diterima", "info"];
                } elseif ($param == 0) {
                    return ["Diterima", "success"];
                } elseif ($param == 1001) {
                    return ["Diterima", "success"];
                }
                // no break
            case "reject":
                if ($param == 1) {
                    return ["Kredit Ditolak", "danger"];
                } elseif ($param == 0) {
                    return ["Ditolak", "danger"];
                } elseif ($param == 2) {
                    return ["Ditolak", "danger"];
                }
                // no break
            case "cash before delivery":
                return ["Bayar Sebelum Dikirim", ""];
            case "kredit":
                return ["Tempo dengan Distributor", ""];
            case "kredit_pro":
                return ["Kredit Pro", ""];
            case "cash on delivery":
                return ["Bayar Di Tempat", ""];

            case 'pickup':
                return ["Pengambilan Sendiri", ""];
            case 'delivery':
                return ["Pengiriman Distributor", ""];
        }
        return ["Status Tidak Diketahui", "danger"];
    }

    function __operate($a, $b, $char)
    {
        switch ($char) {
            case '-':
                return $a - $b;
            case '*':
                return $a * $b;
            case '+':
                return $a + $b;
            case '/':
                return $a / $b;
        }
        return $a;
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Retailer_Controller.php';

class Auth extends MY_API_Retailer_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/at_auth_model', 'at_auth');
        $this->load->model('aksestoko/home_model', 'home');
        $this->load->model('audittrail_model', 'audittrail');
    }

    public function login_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'username',
                    'label' => 'Username',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ],
                [
                    'field' => 'password',
                    'label' => 'Password',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $username = $this->body('username');
            $password = $this->body('password');

            if (!$this->at_auth->loginAT($username, $password, false)) {
                throw new \Exception("Gagal login", 400);
            }

            $response = [
                'user_id'    => $this->session->userdata('user_id'),
                'company_id' => $this->session->userdata('company_id')
            ];

            $token = $this->encrypt(json_encode($response), $this->key);
            $response['token'] = $token;

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil login", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function register_check_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'kode_bk',
                    'label' => 'Kode BK',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $kode_bk = $this->body('kode_bk');

            $data_customer = [];
            $customer = $this->db->get_where("companies", ["cf1" => "IDC-$kode_bk", 'is_active' => '1', 'is_deleted' => null], 1);
            if ($customer && $customer->num_rows() > 0) {
                $name = explode(" ", $customer->row()->name);
                $data_customer = [
                    'store_name'    => $customer->row()->company ?? '',
                    'email'         => $customer->row()->email ?? '',
                    'handphone'     => $customer->row()->phone ?? '',
                    'firstname'     => $name[0] ?? '',
                    'lastname'      => $name[1] ?? ''
                ];
            } else if (BK_INTEGRATION) {
                $this->load->model('curl_model', 'curl');
                $q = $this->db->get_where('api_integration', ['type' => "data_toko_aktif_kdcustomer"], 1);
                if ($q && $q->num_rows() == 0) {
                    return false;
                }
                $integration    = $q->row();
                $url            = $integration->uri;
                $data           = json_encode(['kdcustomer' => $kode_bk]);
                $curl           = json_decode($this->curl->_post($url, $data, true), true);
                if ($curl['data']['status'] == 'OK') {
                    $name = explode(" ", $curl['data']['data'][0]['NM_CUSTOMER']);
                    $data_customer = [
                        'store_name'    => $curl['data']['data'][0]['NAMA_TOKO'] ?? '',
                        'email'         => '',
                        'handphone'     => $curl['data']['data'][0]['NO_HANDPHONE'] ?? '',
                        'firstname'     => $name[0] ?? '',
                        'lastname'      => $name[1] ?? ''
                    ];
                }
            } else {
                throw new \Exception("Tidak ditemukan data Customer dengan ID Bisnis Kokoh tersebut.", 400);
            }

            $response = [
                'customer'    => $data_customer
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil menemukan data Customer dengan ID Bisnis Kokoh tersebut.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function register_submit_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'kode_bk',
                    'label' => 'Kode BK',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'nama_depan',
                    'label' => 'Nama Depan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'nama_belakang',
                    'label' => 'Nama Belakang',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'nama_toko',
                    'label' => 'Nama Toko',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'password',
                    'label' => 'Password',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'ulangi_password',
                    'label' => 'Ulangi Password',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'no_tlp',
                    'label' => 'No. Telepon',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'registed_by',
                    'label' => 'Registed By',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $firstname          = trim($this->body('nama_depan'));
            $lastname           = trim($this->body('nama_belakang'));
            $email              = trim($this->body('email'));
            $store_name         = trim($this->body('nama_toko'));
            $store_code         = trim($this->body('kode_bk'));
            $password           = trim($this->body('password'));
            $retype_password    = trim($this->body('ulangi_password'));
            $salesPersonRefNo   = trim($this->body('sales_person'));
            $handphone          = $this->body('no_tlp');
            $tipe               = $this->body('registed_by');
            $created_device     = $this->body('created_device') ?? 'Aksestoko Mobile';

            if ($salesPersonRefNo != '') {
                $salesPerson = $this->site->getSalesPersonByRefNo($salesPersonRefNo);
                if (!$salesPerson) {
                    throw new \Exception("Sales person dengan Referal Code tersebut Tidak Ditemukan");
                }
            }

            $idSalesPerson    = $salesPerson ? $salesPerson->id : null;
            $salesPersonRef   = $salesPerson ? $salesPerson->reference_no : null;

            if ($this->at_auth->findUserByUsername($store_code)) {
                throw new \Exception("ID Bisnis Kokoh telah terdaftar");
            }

            if ($this->at_auth->findUserByPhone($handphone)) {
                throw new \Exception("No. Telepon telah terdaftar");
            }

            if (!$this->validatePassword($password)) {
                throw new \Exception("Kata Sandi minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka");
            }

            if ($password !== $retype_password) {
                throw new \Exception("Password dan Ulangi Password tidak sama");
            }

            if (BK_INTEGRATION) {
                $IDC = substr($store_code, 0, 4);
                if ($IDC != '9000') {
                    $api_toko_aktif = $this->at_auth->apiTokoAktif($store_code, 'register');
                    if ($api_toko_aktif['curl']['data']['status'] != 'OK') {
                        throw new \Exception("Customer dengan ID tersebut belum terdaftar di Bisnis Kokoh");
                        return false;
                    }
                    $cek_toko = json_decode($this->at_auth->cekTokoAktif($api_toko_aktif['id_bk'], $api_toko_aktif['curl']));
                    $change = $this->at_auth->changeCustomer($api_toko_aktif['curl']['data']['data'][0], $cek_toko);
                }
            }

            $company = $this->at_site->findCompanyByCF1($store_code);

            if (!$company) {
                throw new \Exception("Customer dengan ID Bisnis Kokoh tersebut tidak ditemukan");
            }

            preg_match_all('!\d+!', $handphone, $phone);
            $phone = implode($phone[0]);

            $requestUser = [
                'first_name'      => trim($firstname),
                'last_name'       => trim($lastname),
                'email'           => trim($email),
                'company'         => trim($store_name),
                'phone'           => trim($phone),
                'username'        => trim($store_code),
                'password'        => trim($password),
                'auth_provider'   => 'email',
                'group_id'        => '10',
                'edit_right'      => 1,
                'allow_discount'  => 1,
                'device_id'       => '1',
                'address'         => $company ? $company->address : '',
                'city'            => $company ? $company->city : '',
                'state'           => $company ? $company->state : '',
                'country'         => $company ? $company->country : '',
                'active'          => 0,
                'registered_by'   => trim($tipe),
            ];

            $requestCompany = [
                'cf1'                   => $company ? $company->cf1 : 'IDC-' . $store_code,
                'customer_group_name'   => $company ? $company->customer_group_name : null,
                'customer_group_id'     => $company ? $company->customer_group_id : null,
                'price_group_id'        => $company ? $company->price_group_id : null,
                'price_group_name'      => $company ? $company->price_group_name : null,
                'address'               => $company ? $company->address : '',
                'city'                  => $company ? $company->city : '',
                'state'                 => $company ? $company->state : '',
                'country'               => $company ? $company->country : '',
                "latitude"              => 0,
                "longitude"             => 0,
                "postal_code"           => $company ? $company->postal_code : '',
                'client_id'             => "aksestoko",
                'created_device'        => $created_device,
                'created_by'            => "Created from API",
                'token'                 => $this->token
            ];

            $register = $this->at_auth->insertUserAT($requestUser, $requestCompany);
            if (!$register) {
                throw new \Exception("Gagal melakukan pendaftaran.");
            }

            $requestProfile = [
                'sales_person_id'  => $idSalesPerson,
                'sales_person_ref' => $salesPersonRef
            ];

            $updateProfile = $this->at_auth->updateUserSalesPerson($register['last_id'], $requestProfile, $register['company_id']);

            if (!$updateProfile) {
                throw new \Exception("Tidak dapat Menyimpan Referal Code");
            }

            if (!$this->audittrail->insertCustomerRegistration($register['last_id'], $register['company_id'])) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_registration");
            }

            $response = [
                'user_id'       => $register['last_id'],
                'data_register' => $requestUser
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pendaftaran.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    function generate_email_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'kode_bk',
                    'label' => 'Kode BK',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $kode_bk = $this->body('kode_bk');

            $karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
            $string = '';
            for ($i = 0; $i < 5; $i++) {
                $pos = rand(0, strlen($karakter) - 1);
                $string .= $karakter{
                    $pos};
            }

            $response = [
                'email' => $kode_bk . '@' . $string . '.com'
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pembuatan random email.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function send_activation_code_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'user_id',
                    'label' => 'User ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'no_tlp',
                    'label' => 'No. Telepon',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $user_id    = $this->body('user_id');
            $new_phone  = $this->body('no_tlp');

            $user       = $this->at_auth->find($user_id);

            if (!$user) {
                throw new \Exception("Pengguna tidak ditemukan.");
            }

            if (!$this->at_auth->updateAT($user->id, ['phone' => $new_phone])) {
                throw new \Exception("Tidak dapat memperbarui No Telepon.");
            }

            if ($user->active == 1) {
                throw new \Exception("Akun Anda sudah aktif. silakan melakukan login.");
            }

            if (!$user->activation_code) {
                throw new \Exception("Kode Aktivasi tidak ditemukan.");
            }

            $now                            = time();
            $last_sent_activation_code_at   = strtotime($user->last_sent_activation_code_at) ?? 0;
            $diff_time                      = $now - $last_sent_activation_code_at;
            $threshold                      = 180;

            if ($diff_time >= 0 && $diff_time <= $threshold) {
                throw new \Exception("Tunggu " . gmdate("i:s", $threshold - $diff_time) . " lagi untuk mengirim kode aktivasi.");
            }

            $link           = base_url("api/v1/retailer/Auth/verify_activation_code") . "?username=" . trim($user->username) . "&activation_code=" . trim($user->activation_code);
            $link           = str_replace("\\", "", $link);

            $shorten_link   = ($this->at_site->shorten_link_cuttly($link))->url->shortLink;
            $shorten_link   = str_replace("https://", "", $shorten_link);

            $message = $this->site->makeMessage('sms_activation_code', [
                'store' => (trim($user->company) . " (" . trim($user->username) . ")"),
                'activation_link' => $shorten_link
            ]);

            $send = $this->at_site->send_sms_otp($new_phone, $message, true);
            if (!$send) {
                throw new \Exception("Tidak dapat mengirim kode aktivasi.");
            }

            $this->db->update('users', ['last_sent_activation_code_at' => date('Y-m-d H:i:s'), 'active' => 0], ['id' => $user->id]);

            $response = [
                'user_id'         => $user->id,
                'username'        => trim($user->username),
                'activation_code' => trim($user->activation_code),
                'link'            => $shorten_link
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil mengirim Kode Aktivasi. Dibutuhkan waktu sekitar 2-5 menit untuk menerima pesan.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function verify_activation_code_get()
    {
        $this->db->trans_begin();
        try {
            $username           = $this->input->get('username');
            $activation_code    = $this->input->get('activation_code');
            $verify             = $this->at_auth->verifyActivationCode($username, $activation_code);

            if (!$verify) {
                throw new \Exception("Tidak dapat verifikasi, Kode Aktivasi salah.");
            }

            if (!$this->audittrail->insertCustomerActivation($verify->id, $verify->company_id)) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_activation");
            }

            /* Start - Mengirim data ke distributor */
            $this->load->model('integration_model', 'integration');
            $distributors = $this->home->getAllCompany("IDC-" . trim($verify->username), $verify->company_id);
            foreach ($distributors as $i => $distributor) {
                if ($this->integration->isIntegrated($distributor->cf2, 'register')) {
                    $response = $this->integration->registered_customer_integration($distributor->cf2, trim($verify->username));
                    if (!$response) {
                        throw new \Exception("Tidak dapat mengirim data ke distributor");
                    }
                }
            }
            $this->session->set_flashdata('success');
            $this->db->trans_commit();
            redirect(aksestoko_route('aksestoko/auth/success_registration'));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(aksestoko_route('aksestoko/auth/success_registration'));
        }
    }

    public function forgot_pasword_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'kode_bk',
                    'label' => 'Kode BK',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'no_tlp',
                    'label' => 'No. Telepon',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $store_code   = $this->body('kode_bk');
            $phone        = $this->body('no_tlp');
            $ip           = $this->input->ip_address();

            $forgetPass   = $this->at_auth->forgetPassword($store_code, $phone, $ip);
            if (!$forgetPass) {
                throw new \Exception("Gagal melakukan aksi lupa password");
            }

            $fp           = $this->at_auth->findResetPassword($forgetPass);
            $user         = $this->at_auth->find($fp->user_id);

            $service      = $user->phone_is_verified ? ['sms', 'wa'] : 'Nomor Telepon belum terverifikasi tidak dapat memilih layanan';

            $response = [
                'id_forget_password' => $forgetPass,
                'store_code'         => $store_code,
                'phone'              => $phone,
                'service'            => $service
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan aksi lupa password.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function send_otp_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'id_forget_password',
                    'label' => 'Forget Password ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'service',
                    'label' => 'Layanan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $id_fp    = $this->body('id_forget_password');
            $service  = $this->body('service');
            $fp       = $this->at_auth->findResetPassword($id_fp);

            if (!$fp) {
                throw new \Exception("Gagal melakukan aksi tidak dapat menemukan data atas ID :" . $id_fp);
            }

            $user = $this->at_auth->find($fp->user_id);

            $message = $this->site->makeMessage('sms_forget_password', [
                'otp_code' => $fp->otp_code,
                'timestamp' => $fp->valid_until
            ]);

            if (strtoupper($service) == "SMS") {
                if (!$user->phone_is_verified) {
                    throw new \Exception("Gagal Layanan SMS tidak dapat digunakan");
                }
                $send = $this->at_site->send_sms_otp($user->phone, $message);
                if (!$send) {
                    throw new \Exception("Gagal Tidak dapat mengirim Kode OTP melalui SMS");
                }
            } else if (strtoupper($service) == "WA") {
                if (!$user->phone_is_verified) {
                    throw new \Exception("Gagal Layanan WhatsApp tidak dapat digunakan");
                }
                $send = $this->at_site->send_wa_otp_wablas($user->phone, $message, true);
                if (!$send) {
                    throw new \Exception("Gagal Tidak dapat mengirim Kode OTP melalui WA");
                }
            } else if (strtoupper($service) == "HELPDESK") {
                $user = [
                    "store_code"  => $fp->store_code,
                    "phone"       => $fp->phone,
                    "ip_address"  => $fp->ip_address,
                    "otp_code"    => $fp->otp_code,
                    "valid_until" => $fp->valid_until
                ];
                $description = '<pre><code class="json">';
                $description .= json_encode($user);
                $description .= '</code></pre>';
                $this->home->insertIssue("Reset Password #$fp->id - " . $fp->store_code, $description, 4);
                $message = "Silakan menghubungi kami melalui https://wa.me/628116065246?text=Saya+tidak+mendapatkan+Kode+OTP.+ID+Bisnis+Kokoh+:+<?=$fp->store_code?>' / 0811-6065-246 (WhatsApp)";
            } else {
                throw new \Exception("Gagal Tidak dapat mengirim Kode OTP melalui layanan tersebut");
            }

            $response = [
                'id_forget_password' => $id_fp,
                'message'            => $message
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengiriman otp.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function check_otp_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'id_forget_password',
                    'label' => 'Forget Password ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'otp',
                    'label' => 'Kode OTP',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $id_fp    = $this->body('id_forget_password');
            $otp      = $this->body('otp');

            if (strlen($otp) != 5) {
                throw new \Exception("Gagal Panjang Kode OTP adalah 5 digit");
            }
            $cek = $this->at_auth->checkOtp($id_fp, $otp);
            if (!$cek) {
                throw new \Exception("Gagal Kode OTP salah");
            }
            $date       = strtotime($cek->valid_until);
            $dateNow    = strtotime('now');
            if ($dateNow > $date) {
                throw new \Exception("Gagal Kode OTP telah kadaluarsa");
            }

            $response = [
                'data' => $cek,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengecekan otp.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function change_password_post()
    {
        $this->db->trans_begin();
        try {

            $config = [
                [
                    'field' => 'id_forget_password',
                    'label' => 'Forget Password ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'password_baru',
                    'label' => 'Password Baru',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'ulangi_password_baru',
                    'label' => 'Ulangi Password Baru',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $id_fp                  = $this->body('id_forget_password');
            $new_password           = $this->body('password_baru');
            $retype_new_password    = $this->body('ulangi_password_baru');

            $fp       = $this->at_auth->findResetPassword($id_fp);
            if (!$fp) {
                throw new \Exception("Gagal melakukan perubahan password tidak dapat menemukan data atas ID :" . $id_fp);
            }
            if ($new_password !== $retype_new_password) {
                throw new \Exception("Gagal melakukan perubahan password Password baru dan Ulangi password baru tidak sama");
            }
            if (!$this->validatePassword($new_password)) {
                throw new \Exception("Gagal melakukan perubahan password Kata Sandi minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka");
            }
            if ($fp->is_success != 1) {
                throw new \Exception("Gagal melakukan perubahan password tidak dapat mereset password");
            }

            $changePassword = $this->at_auth->resetPasswordAT($fp->store_code, $new_password);
            if (!$changePassword) {
                throw new \Exception("Gagal terjadi kesalahan saat melakukan perubahan password");
            }

            $response = [
                'kode_bk'  => $fp->store_code,
                'password' => $new_password
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan perubahan password. Silakan login menggunakan password yang baru.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function user_activation_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            if ($auth->user->active != 1) {
                $message = 'Akun belum diaktifkan';
            } else {
                $message = 'Akun sudah aktif';
            }

            $response = [
                'message'  => $message
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengecekan user aktif", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function user_notif_token_get()
    {
        $this->db->trans_begin();
        try {
            $auth         = $this->authorize();
            $device       = $this->input->get('device');

            if ($device) {
                $Token   = $this->at_site->getTokenNotifikasi(['created_by' => $auth->user->id, 'device_id' => $device]);
            } else {
                $Token   = $this->at_site->getTokenNotifikasi(['created_by' => $auth->user->id]);
            }

            if (!$Token) {
                throw new \Exception("Gagal melakukan pengambilan data token notifikasi / tidak ditemukan token pada user : " . $auth->user->username);
            }

            $data = [
                'user_id'       => $auth->user->id,
                'company_id'    => $auth->company->id,
                'device_name'   => $Token->device_id,
                'token'         => $Token->token
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan data token notifikasi", $data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function addorupdatetoken_notif_post()
    {
        $this->db->trans_begin();
        try {
            $auth   = $this->authorize();
            $config = [
                [
                    'field' => 'token',
                    'label' => 'Token',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'device_name',
                    'label' => 'Nama Device',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ]
                ]
            ];

            $this->validate_form($config);

            $token          = $this->body('token');
            $device_name    = $this->body('device_name');

            if ($device_name) {
                $Token   = $this->at_site->getTokenNotifikasi(['created_by' => $auth->user->id, 'device_id' => $device_name]);
            }

            $data = [
                'scope'         => 0,
                'client_id'     => 'Aksestoko Mobile',
                'device_id'     => $device_name,
                'token'         => $token,
                'created_by'    => $auth->user->id,
                'company_id'    => $auth->company->id
            ];

            if (!$Token) {
                $insertTokenNotif = $this->at_site->insertTokenNotifikasi($data);
                if (!$insertTokenNotif) {
                    throw new Exception("Insert token data failed", 400);
                }
            } else {
                $updateTokenNotif = $this->at_site->updateTokenNotifikasi($data, ['created_by' => $auth->user->id, 'company_id' => $auth->company->id, 'device_id' => $device_name]);
                if (!$updateTokenNotif) {
                    throw new Exception("Update token data failed", 400);
                }
            }

            $response = [
                'data'  => $data
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan penambahan / update token", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------------------------------------//
    private function validatePassword($string)
    {
        $containsUpper    = preg_match('/[A-Z]/', $string);
        $containsLower    = preg_match('/[a-z]/', $string);
        $containsDigit    = preg_match('/\d/', $string);
        $long8            = strlen($string) >= 8;

        if ($containsUpper && $containsLower && $containsDigit && $long8) {
            return true;
        }
        return false;
    }
    //------------------------------------------------------------------------------------------------------------------------------------------------------------//
}

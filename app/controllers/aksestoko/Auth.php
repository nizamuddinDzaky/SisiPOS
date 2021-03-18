<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends MY_Controller
{
    /**
     * @var At_auth_model $at_auth
     */
    public function __construct()
    {
        parent::__construct();


        $this->load->model('aksestoko/at_auth_model', 'at_auth');
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/at_company_model', 'at_company');
        $this->load->model('aksestoko/home_model', 'home');
        $this->load->model('audittrail_model', 'audittrail');

        $this->insertLogActivities();
        $this->lang->load('auth', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
    }

    public function index()
    {
        redirect(aksestoko_route('aksestoko/auth/signin'));
    }

    /**
     * POST
     *
     * Request :
     * - username -> text
     * - password -> text
     *
     */
    public function login()
    {
        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $username = $this->input->post('username');
                $password = $this->input->post('password');
                $remember = (bool) $this->input->post('remember');
                $redirect = $this->session->userdata('redirect') ?? null;
                $login = $this->at_auth->loginAT($username, $password, $remember);

                if (!$login) {
                    throw new \Exception("Gagal login");
                }
                $this->db->trans_commit();

                $this->load->model('db_model');
                $activeSurvey = $this->db_model->getActiveSurveyAT();
                $customerResponse = $this->db_model->checkCustomerResponse();

                if ($activeSurvey) {
                    if (!$customerResponse) {
                        redirect(aksestoko_route($redirect ?? 'aksestoko/survey'));
                    } else {
                        redirect(aksestoko_route($redirect ?? 'aksestoko/home/select_supplier'));
                    }
                } else {
                    redirect(aksestoko_route($redirect ?? 'aksestoko/home/select_supplier'));
                }
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('username', $username);
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/signin'));
    }

    //GET
    public function signin()
    {
        if ($this->logAsAT) {
            // $this->session->unset_userdata('redirect');
            redirect(aksestoko_route('aksestoko/home/select_supplier'));
        }
        $user_id = $this->session->userdata('user_id');
        $this->data['user_temp'] = null;
        if ($user_id) {
            $user = $this->at_auth->find($user_id);
            $this->data['user_temp'] = $user;
            $this->session->unset_userdata('user_id');

            $now = time();
            $last_sent_activation_code_at = strtotime($user->last_sent_activation_code_at) ?? 0;
            $diff_time = $now - $last_sent_activation_code_at;
            $threshold = 180;
            $this->data['timeleft'] = -1;
            if ($diff_time >= 0 && $diff_time <= $threshold) {
                $this->data['timeleft'] = $threshold - $diff_time;
            }
        }
        $this->data['new_register'] = $this->session->userdata('new_register');
        if ($this->data['new_register']) {
            $this->session->unset_userdata('new_register');
        }
        $this->data['title_at'] = "Masuk - AksesToko";
        $this->load->view('aksestoko/login', $this->data);
    }

    private function validatePassword($string)
    {
        $containsUpper = preg_match('/[A-Z]/', $string);
        $containsLower = preg_match('/[a-z]/', $string);
        $containsDigit = preg_match('/\d/', $string);
        $long8 = strlen($string) >= 8;

        if ($containsUpper && $containsLower && $containsDigit && $long8) {
            return true;
        }

        return false;
    }


    /**
     * POST
     *
     * Request :
     * - store_code -> text
     * - store_name -> text
     * - email
     * - handphone
     * - firstname
     * - lastname
     * - password
     * - retype_password
     */
    public function register()
    {
        if ($this->isPost()) {
            $this->db->trans_begin();
            // var_dump($this->input->post('sales_person'));die;
            try {
                $store_code = trim($this->input->post('store_code'));
                $password = trim($this->input->post('password'));
                $retype_password = trim($this->input->post('retype_password'));
                $salesPersonRefNo = trim($this->input->post('sales_person'));
                if ($salesPersonRefNo != '') {
                    $salesPerson = $this->site->getSalesPersonByRefNo($salesPersonRefNo);
                    if (!$salesPerson) {
                        throw new \Exception("Salesperson dengan Referal Code tersebut Tidak Ditemukan");
                    }
                }

                $idSalesPerson = $salesPerson ? $salesPerson->id : null;
                $salesPersonRef = $salesPerson ? $salesPerson->reference_no : null;

                if (!$store_code) {
                    throw new \Exception("ID Bisnis Kokoh harus diisi");
                }

                if ($this->at_auth->findUserByUsername($store_code)) {
                    throw new \Exception("ID Bisnis Kokoh telah terdaftar");
                }

                if ($this->at_auth->findUserByPhone($this->input->post('handphone'))) {
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

                preg_match_all('!\d+!', $this->input->post('handphone'), $phone);
                $phone = implode($phone[0]);
                $tipe = $this->input->post('optradio');
                $requestUser = [
                    'first_name' => trim($this->input->post('firstname')),
                    'last_name' => trim($this->input->post('lastname')),
                    'email' => trim($this->input->post('email')),
                    'company' => trim($this->input->post('store_name')),
                    'phone' => trim($phone),
                    'username' => trim($store_code),
                    'password' => trim($this->input->post('password')),
                    'auth_provider' => 'email',
                    'group_id' => '10',
                    'edit_right' => 1,
                    'allow_discount' => 1,
                    'device_id' => '1',
                    'address' => $company ? $company->address : '',
                    'city' => $company ? $company->city : '',
                    'state' => $company ? $company->state : '',
                    'country' => $company ? $company->country : '',
                    // 'active' => $company ? 1 : 0,
                    'active' => 0,
                    'registered_by' => trim($tipe),
                    'recovery_code' => getUuid(),
                    'recovery_max' => 5,
                    // 'sales_person_id' =>$idSalesPerson,
                    // 'sales_person_ref' =>$salesPersonRef
                ];

                $requestCompany = [
                    'cf1' => $company ? $company->cf1 : 'IDC-' . $store_code,
                    'customer_group_name' => $company ? $company->customer_group_name : null,
                    'customer_group_id' => $company ? $company->customer_group_id : null,
                    'price_group_id' => $company ? $company->price_group_id : null,
                    'price_group_name' => $company ? $company->price_group_name : null,
                    'address' => $company ? $company->address : '',
                    'city' => $company ? $company->city : '',
                    'state' => $company ? $company->state : '',
                    'country' => $company ? $company->country : '',
                    "latitude" => 0,
                    "longitude" => 0,
                    "postal_code" => $company ? $company->postal_code : '',
                    'client_id' => "aksestoko"
                ];

                $this->session->set_userdata('registration', json_decode(json_encode($requestUser), false));
                $this->session->set_userdata('additional_registration', json_decode(json_encode($requestCompany), false));

                $register = $this->at_auth->insertUserAT($requestUser, $requestCompany);
                if (!$register) {
                    throw new \Exception("Gagal mendaftar.");
                }
                // print_r($register['last_id']);die;

                $requestProfile = [
                    'sales_person_id' => $idSalesPerson,
                    'sales_person_ref' => $salesPersonRef
                ];

                $updateProfile = $this->at_auth->updateUserSalesPerson($register['last_id'], $requestProfile, $register['company_id']);

                if (!$updateProfile) {
                    throw new \Exception("Tidak dapat Menyimpan Referal Code");
                }

                if (!$this->audittrail->insertCustomerRegistration($register['last_id'], $register['company_id'])) {
                    throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_registration");
                }

                $this->session->set_userdata('user_id', $register['last_id']);
                $this->session->set_userdata('new_register', true);
                $this->session->set_flashdata('message', "Berhasil mendaftar. Tekan <a href='javascript:void(0)' id='activationBtn'> disini </a> untuk mengirim Kode Aktivasi. Tekan <a href='javascript:void(0)' id='recoveryBtn'> disini </a> untuk melihat Kode Pemulihan.");
                $this->db->trans_commit();
                redirect(aksestoko_route('aksestoko/auth/signin'));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $phone = $this->input->post('handphone');
                if ($phone[0] == '0') {
                    $phone = substr($phone, 1);
                } else if ($phone[0] == '6' && $phone[1] == '2') {
                    $phone = substr($phone, 2);
                }
                $value = [
                    'store_code' => $this->input->post('store_code'),
                    'store_name' => $this->input->post('store_name'),
                    'email' => $this->input->post('email'),
                    'handphone' => $phone,
                    'firstname' => $this->input->post('firstname'),
                    'lastname' => $this->input->post('lastname'),
                    'sales_person' => $this->input->post('sales_person'),
                    'password' => $this->input->post('password'),
                    'retype_password' => $this->input->post('retype_password'),
                    'optradio' => $this->input->post('optradio'),
                ];
                $this->session->set_flashdata('value', $value);
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/signup'));
    }

    //GET - Json Return
    public function customer($bk)
    {
        $json = [
            'status' => false,
            'message' => 'Tidak ditemukan data Customer dengan ID Bisnis Kokoh tersebut.'
        ];
        $customer = $this->db->get_where("companies", ["cf1" => "IDC-$bk", 'is_active' => '1', 'is_deleted' => null], 1);
        if ($customer && $customer->num_rows() > 0) {
            $json = $customer->row();
            if ($this->at_auth->findUserByUsername($bk)) {
                $json = [
                    'status' => false,
                    'message' => 'ID Bisnis Kokoh telah terdaftar.'
                ];
            }
        } else if (BK_INTEGRATION) {
            $this->load->model('curl_model', 'curl');
            $q = $this->db->get_where('api_integration', ['type' => "data_toko_aktif_kdcustomer"], 1);
            if ($q && $q->num_rows() == 0) {
                return false;
            }
            $integration = $q->row();
            $url = $integration->uri;
            $data = json_encode(['kdcustomer' => $bk]);
            $curl = json_decode($this->curl->_post($url, $data, true), true);
            if ($curl['data']['status'] == 'OK') {
                $json = [
                    'name' => $curl['data']['data'][0]['NM_CUSTOMER'] ?? '',
                    'company' => $curl['data']['data'][0]['NAMA_TOKO'] ?? '',
                    'email' => '',
                    'phone' => $curl['data']['data'][0]['NO_HANDPHONE'] ?? ''
                ];
            }
        }
        echo json_encode($json);
        return true;
    }

    //GET
    public function signup()
    {
        if ($this->logAsAT) {
            // $this->session->unset_userdata('redirect');
            redirect(aksestoko_route('aksestoko/home/select_supplier'));
        }
        $this->data['title_at'] = "Daftar - AksesToko";
        $this->load->view('aksestoko/signup', $this->data);
    }



    //GET
    public function logout()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->session->sess_destroy();
        redirect(aksestoko_route('aksestoko/auth/signin'));
    }

    //GET
    public function profile()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->data['title_at'] = "Profil - AksesToko";
        $this->data['profile'] = $this->at_auth->find($this->session->userdata('user_id'));
        $this->data['addresses'] = array_merge([$this->at_site->findCompany($this->session->userdata('company_id'))], $this->at_site->getCompaniesAddress($this->session->userdata('company_id')));
        $this->data['guide'] = $this->at_site->getGuideAT($this->session->userdata('user_id'));
        $this->data['object'] = $this;

        $valid_until = strtotime($this->session->userdata('phone_otp_valid_until'));
        $dateNow = strtotime('now');
        $left_time = ($dateNow - $valid_until);

        $this->data['left_time'] = $valid_until && $left_time < 0 ? abs($left_time) : null;

        if ($this->data['profile']->sales_person_ref != null) {
            $this->data['sales_person'] = $this->site->getSalesPersonByRefNo($this->data['profile']->sales_person_ref);
        }
        // print_r($this->data['sales_person']);die;
        // var_dump($this->data['address']);die;
        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }
        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/profile', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    /**
     * POST
     *
     * Request :
     * - firstname -> text
     * - lastname -> text
     * - email -> text
     * - phone -> text
     * - store_name -> text
     */
    public function update_profile()
    {
        $this->checkATLogged();

        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $requestProfile = [
                    'company' => $this->input->post('store_name'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                ];

                // var_dump($requestProfile);
                // die;

                $updateProfile = $this->at_auth->updateAT($this->session->userdata('user_id'), $requestProfile);
                if (!$updateProfile) {
                    throw new \Exception("Gagal memperbarui profil");
                }

                $this->session->set_flashdata('message', "Berhasil memperbarui profile");
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/profile'));
    }
    /**
     * POST
     *
     * Request :
     * - uploadKTP -> file
     */
    public function update_ktp()
    {
        $this->checkATLogged();

        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $this->load->model('integration_model', 'integration');

                if ($_FILES['uploadKTP']['size'] <= 0) {
                    throw new \Exception("Berkas KTP dibutuhkan");
                }
                $uploadedImg    = $this->integration->upload_files($_FILES['uploadKTP']);
                if (!$uploadedImg) {
                    throw new \Exception("Gagal mengunggah KTP");
                }
                $requestProfile['photo_ktp'] = $uploadedImg->url;

                $updateProfile = $this->at_auth->updateAT($this->session->userdata('user_id'), $requestProfile);
                if (!$updateProfile) {
                    throw new \Exception("Gagal memperbarui KTP");
                }

                $this->session->set_flashdata('message', "Berhasil memperbarui KTP");
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/profile'));
    }

    public function update_sales_person()
    {
        $this->checkATLogged();

        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $salesPersonRefNo = $this->input->post('sales_person');
                if ($salesPersonRefNo != '') {
                    $salesPerson = $this->site->getSalesPersonByRefNo($this->input->post('sales_person'));
                    if (!$salesPerson) {
                        throw new \Exception("Salesperson dengan Kode Referal tersebut Tidak Ditemukan");
                    }
                }

                $idSalesPerson = $salesPerson ? $salesPerson->id : null;
                $salesPersonRef = $salesPerson ? $salesPerson->reference_no : null;

                $requestProfile = [
                    'sales_person_id' => $idSalesPerson,
                    'sales_person_ref' => $salesPersonRef
                ];

                $updateProfile = $this->at_auth->updateUserSalesPerson($this->session->userdata('user_id'), $requestProfile, $this->session->userdata('company_id'));
                // var_dump($updateProfile);die;
                if (!$updateProfile) {
                    throw new \Exception("Gagal memperbarui Salesperson");
                }

                $this->session->set_flashdata('message', "Berhasil memperbarui Salesperson");
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/profile?salesperson'));
    }

    // //GET
    // function change_password()
    // {
    //     $this->checkATLogged(); // seharusnya di paling atas baris
    //     $this->data['title_at'] = "Ganti Password - AksesToko";
    //     // $this->load->view('aksestoko/change_password', $this->data);
    // }

    /**
     * POST
     *
     * Request :
     * - old_password -> text
     * - new_password -> text
     * - retype_new_password -> text
     *
     */
    public function update_password()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        // var_dump($this->session);
        // die;
        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $old_password = $this->input->post('old_password');
                $new_password = $this->input->post('new_password');
                $retype_new_password = $this->input->post('retype_new_password');

                if ($new_password !== $retype_new_password) {
                    throw new \Exception("Password baru dan Ulangi password baru tidak sama");
                }

                if (!$this->validatePassword($new_password)) {
                    throw new \Exception("Kata Sandi minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka");
                }

                $changePassword = $this->at_auth->changePasswordAT($this->session->userdata('username'), $old_password, $new_password);
                if (!$changePassword) {
                    throw new \Exception("Terjadi kesalahan");
                }

                $this->session->set_flashdata('message', "Berhasil memperbarui password");

                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/profile?password'));
    }

    // Load Provinsi
    public function kabupaten()
    {
        $propinsiID = $_GET['id'];
        $kabupaten   = $this->db->get_where('kabupaten', array('id_prov' => $propinsiID));
        echo " <div class='form-group'>
                <label>Kabupaten</label>";
        echo "<select id='kabupaten' onChange='loadKecamatan()' class='form-control'>";
        foreach ($kabupaten->result() as $k) {
            echo "<option value='$k->id'>$k->nama</option>";
        }
        echo "</select></div>";
    }

    public function kecamatan()
    {
        $kabupatenID = $_GET['id'];
        $kecamatan   = $this->db->get_where('kecamatan', array('id_kabupaten' => $kabupatenID));
        echo " <div class='form-group'>
                <label>Kecamatan</label>";
        echo "<select id='kecamatan' onChange='loadDesa()' class='form-control'>";
        foreach ($kecamatan->result() as $k) {
            echo "<option value='$k->id'>$k->nama</option>";
        }
        echo "</select></div>";
    }

    public function desa()
    {
        $kecamatanID  = $_GET['id'];
        $desa         = $this->db->get_where('desa', array('id_kecamatan' => $kecamatanID));
        echo " <div class='form-group'>
                <label>Desa</label>";
        echo "<select class='form-control'>";
        foreach ($desa->result() as $d) {
            echo "<option value='$d->id'>$d->nama</option>";
        }
        echo "</select></div>";
    }

    /**
     * POST
     *
     * Request :
     * - name -> text
     * - company -> text
     * - address -> text
     * - city -> text
     * - state -> text
     * - village -> text
     * - postal_code -> text
     * - country -> text
     * - phone -> text
     * - email -> text
     */
    public function add_address()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $redirect = $this->input->get('redirect');

        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $company = ($this->at_site->getCompanyByID($this->session->userdata('company_id')));

                $requestAddress = [
                    'group_id' => null,
                    'group_name' => 'address',
                    'company_id' => $company->id,
                    'customer_group_id' => $company->customer_group_id,
                    'customer_group_name' => $company->customer_group_name,
                    'name' => $this->input->post('name'),
                    'company' => $this->input->post('company'),
                    'vat_no' => $company->vat_no,
                    'region' => $company->region,
                    'address' => $this->input->post('address'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'village' => $this->input->post('village'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country' => $this->input->post('country'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                    'cf1' => $company->cf1,
                    'cf2' => $company->cf2,
                    'cf3' => $company->cf3,
                    'cf4' => $company->cf4,
                    'cf5' => $company->cf5,
                    'cf6' => $company->cf6,
                    'invoice_footer' => null,
                    'payment_term' => 0,
                    'logo' => 'logo.png',
                    'award_points' => 0,
                    'deposit_amount' => 0,
                    'price_group_id' => $company->price_group_id,
                    'price_group_name' => $company->price_group_name,
                    'client_id' => null,
                    'flag' => null,
                    'is_deleted' => null,
                    'device_id' => null,
                    'uuid' => null,
                    'uuid_app' => null,
                    'manager_area' => null,
                    'mtid' => null,
                    'latitude' => null,
                    'longitude' => null,
                ];

                // var_dump($requestAddress);
                // die;
                $insertCompany = $this->at_company->addCompany($requestAddress);
                if (!$insertCompany) {
                    throw new \Exception("Gagal menambahkan alamat baru");
                }

                $this->session->set_flashdata('message', "Berhasil menambah alamat");

                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        // redirect(aksestoko_route('aksestoko/auth/profile?address'));
        redirect($redirect ? aksestoko_route($redirect) : aksestoko_route('aksestoko/auth/profile?address'));
    }

    /**
     * POST
     *
     * Request :
     * - name -> text
     * - company -> text
     * - address -> text
     * - city -> text
     * - state -> text
     * - village -> text
     * - postal_code -> text
     * - country -> text
     * - phone -> text
     * - email -> text
     */
    public function update_address($id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $redirect = $this->input->get('redirect');

        if ($this->isPost() && $id) {
            $this->db->trans_begin();

            try {
                $requestAddress = [
                    'name' => $this->input->post('name'),
                    'company' => $this->input->post('company'),
                    'address' => $this->input->post('address'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'village' => $this->input->post('village'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country' => $this->input->post('country'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                ];

                // var_dump($requestAddress);
                // die;
                $updateCompany = $this->at_company->updateCompany($id, $requestAddress);
                if (!$updateCompany) {
                    throw new \Exception("Gagal memperbarui alamat");
                }

                $this->session->set_flashdata('message', "Berhasil memperbarui alamat");

                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect($redirect ? aksestoko_route($redirect) : aksestoko_route('aksestoko/auth/profile?address'));
        // redirect(aksestoko_route('aksestoko/auth/profile?address'));
    }

    /**
     * GET
     */
    public function delete_address($id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $redirect = $this->input->get('redirect');

        if ($id) {
            $this->db->trans_begin();

            try {
                $deleteCompany = $this->at_company->softDeleteAddress($id, $this->session->userdata('company_id'));
                if (!$deleteCompany) {
                    throw new \Exception("Gagal menghapus alamat");
                }

                if ($id == $this->session->userdata('company_address_id')) {
                    $this->session->set_userdata(['company_address_id' => $this->session->userdata('company_id')]);
                }

                $this->session->set_flashdata('message', "Berhasil menghapus alamat");

                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect($redirect ? aksestoko_route($redirect) : aksestoko_route('aksestoko/auth/profile?address'));
    }

    public function reset_password()
    {
        $id_fp = $this->session->userdata('id_forget_password');
        if (!$id_fp) {
            redirect(aksestoko_route('aksestoko/auth/signin'));
        }
        $this->data['reset_password'] = $this->at_auth->findResetPassword($id_fp);

        if ($this->session->userdata('recovery_code')) {
            $this->load->view('aksestoko/reset-password-recovery', $this->data);
        } else {
            $this->load->view('aksestoko/reset-password', $this->data);
        }
    }

    /**
     * POST
     *
     * Request :
     * - store_code -> text
     * - phone -> text
     */
    public function forget_password()
    {
        // $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $store_code = $this->input->post('store_code');
                $phone = $this->input->post('phone');
                $ip = $this->input->ip_address();

                $forgetPass = $this->at_auth->forgetPassword($store_code, $phone, $ip);
                if (!$forgetPass) {
                    throw new \Exception("Gagal eksekusi lupa password");
                }

                $this->session->unset_userdata('remembered_otp');

                $this->session->set_userdata([
                    'id_forget_password' => $forgetPass,
                    'store_code' => $store_code,
                    'phone' => $phone
                ]);

                // $this->session->set_flashdata('message', "Berhasil memperbarui alamat");

                $this->db->trans_commit();

                redirect(aksestoko_route('aksestoko/auth/otp_options'));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        // redirect($_SERVER['HTTP_REFERER']);
        redirect(aksestoko_route('aksestoko/auth/signin'));
    }

    /**
     * POST
     *
     * Request :
     * - id_forget_password
     * - otp -> text
     */
    public function check_otp()
    {
        $cek = null;
        if ($this->isPost()) {
            $id_fp = $this->input->post('id_forget_password');
            $otp = $this->input->post('otp');

            $cek = $this->at_auth->checkOtp($id_fp, $otp);
        }
        if ($cek) {
            $date = strtotime($cek->valid_until);
            $dateNow = strtotime('now');

            if ($dateNow > $date) {
                http_response_code(401);
                $cek = "Kode OTP telah kadaluarsa";
            }
        } else {
            http_response_code(400);
            $cek = "Kode OTP salah";
        }



        echo json_encode($cek);
    }
    /**
     * POST
     *
     * Request :
     * - id_forget_password
     * - recovery_code -> text
     */
    public function check_recovery_code()
    {
        $cek = null;
        if ($this->isPost()) {
            $id_fp = $this->input->post('id_forget_password');
            $recovery_code = $this->input->post('recovery_code');

            $cek = $this->at_auth->checkRecoveryCode($id_fp, $recovery_code);
        }
        if (!$cek) {
            http_response_code(400);
            $cek = "Kode Pemulihan salah";
        } else if ($cek && $cek->recovery_code != $recovery_code) {
            $this->db->insert('user_recovery_attempts', [
                'user_id' => $cek->user_id,
                'code' => $recovery_code,
                'is_correct' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            http_response_code(400);
            $cek = "Kode Pemulihan salah";
        } else {
            $this->db->insert('user_recovery_attempts', [
                'user_id' => $cek->user_id,
                'code' => $recovery_code,
                'is_correct' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo json_encode($cek);
    }

    /**
     * POST
     *
     * Request :
     * - id_forget_password
     * - new_password -> text
     * - retype_new_password -> text
     *
     */
    public function change_password()
    {
        if ($this->isPost()) {
            $this->db->trans_begin();

            $fp = $this->at_auth->findResetPassword($this->input->post('id_forget_password'));

            try {
                $new_password = $this->input->post('new_password');
                $retype_new_password = $this->input->post('retype_new_password');

                if ($new_password !== $retype_new_password) {
                    throw new \Exception("Password baru dan Ulangi password baru tidak sama");
                }

                if (!$this->validatePassword($new_password)) {
                    throw new \Exception("Kata Sandi minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka");
                }

                if ($fp->is_success != 1) {
                    throw new \Exception("Tidak dapat mereset password");
                }

                $changePassword = $this->at_auth->resetPasswordAT($fp->store_code, $new_password);
                if (!$changePassword) {
                    throw new \Exception("Terjadi kesalahan");
                }

                $this->session->set_flashdata('message', "Berhasil mereset password. Silakan login menggunakan password yang baru.");

                $this->session->unset_userdata('id_forget_password');
                $this->session->unset_userdata('store_code');
                $this->session->unset_userdata('phone');
                $this->session->unset_userdata('remembered_otp');

                $this->db->trans_commit();

                redirect(aksestoko_route('aksestoko/auth/signin'));
            } catch (\Throwable $th) {
                $this->session->set_userdata([
                    'remembered_otp' => $fp->otp_code
                ]);
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/reset_password'));
    }

    public function otp_options()
    {
        $id_fp = $this->session->userdata('id_forget_password');
        if (!$id_fp) {
            redirect(aksestoko_route('aksestoko/auth/signin'));
        }
        $this->data['fp'] = $this->at_auth->findResetPassword($id_fp);
        $this->data['user'] = $this->at_auth->find($this->data['fp']->user_id);
        // var_dump($this->data['user']);die;
        $this->data['user']->phone_is_verified = ($this->data['user']->phone_is_verified == 1);

        $this->load->view('aksestoko/send_otp', $this->data);
    }

    /**
     * GET
     *
     */
    public function send_otp($service = null)
    {
        $this->session->unset_userdata('recovery_code');

        $id_fp = $this->session->userdata('id_forget_password');
        $fp = $this->at_auth->findResetPassword($id_fp);

        if (!$fp) {
            redirect(aksestoko_route('aksestoko/auth/signin'));
        }

        $user = $this->at_auth->find($fp->user_id);

        try {
            $message = $this->site->makeMessage('sms_forget_password', [
                'otp_code' => $fp->otp_code,
                'timestamp' => date('d M y H:i', strtotime($fp->valid_until))
            ]);
            switch ($service) {
                case "sms":
                    if (!$user->phone_is_verified) {
                        $this->session->set_flashdata('error', "Layanan SMS tidak dapat digunakan");
                        redirect(aksestoko_route('aksestoko/auth/otp_options'));
                    }
                    $send = $this->at_site->send_sms_otp($user->phone, $message, true);
                    if (!$send) {
                        $this->session->set_flashdata('error', "Tidak dapat mengirim Kode OTP melalui SMS");
                        redirect(aksestoko_route('aksestoko/auth/otp_options'));
                    }
                    break;
                case "wa":
                    if (!$user->phone_is_verified) {
                        $this->session->set_flashdata('error', "Layanan WhatsApp tidak dapat digunakan");
                        redirect(aksestoko_route('aksestoko/auth/otp_options'));
                    }
                    $send = $this->at_site->send_wa_otp_wablas($user->phone, $message, true);
                    if (!$send) {
                        $this->session->set_flashdata('error', "Tidak dapat mengirim Kode OTP melalui WA");
                        redirect(aksestoko_route('aksestoko/auth/otp_options'));
                    }
                    break;
                case "helpdesk":
                    $user = [
                        "store_code" => $fp->store_code,
                        "phone" => $fp->phone,
                        "ip_address" => $fp->ip_address,
                        "otp_code" => $fp->otp_code,
                        "valid_until" => $fp->valid_until
                    ];
                    $description = '<pre><code class="json">';
                    $description .= json_encode($user);
                    $description .= '</code></pre>';
                    $this->home->insertIssue("Reset Password #$fp->id - " . $fp->store_code, $description, 4);
                    echo true;
                    return;
                    break;
                case "recovery_code":
                    if (!$user->recovery_code) {
                        $this->session->set_flashdata('error', "Layanan Kode Pemulihan tidak dapat digunakan");
                        redirect(aksestoko_route('aksestoko/auth/otp_options'));
                    }
                    $this->session->set_userdata('recovery_code', true);
                    break;
                default:
                    redirect(aksestoko_route('aksestoko/auth/signin'));
            }
        } catch (\Throwable $th) {
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(aksestoko_route('aksestoko/auth/otp_options'));
        }

        redirect(aksestoko_route('aksestoko/auth/reset_password'));
    }

    public function set_guide($column, $status)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->at_site->setGuide($column, $status, $this->session->userdata('user_id'));
    }

    public function generate_phone_otp()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $user_id = $this->session->userdata('user_id');
        $valid_until = strtotime($this->session->userdata('phone_otp_valid_until'));
        $dateNow = strtotime('now');
        // var_dump();die;

        if ($valid_until) {
            if ($dateNow < $valid_until) {
                $left_time = (int) abs(($dateNow - $valid_until) / 60);
                echo json_encode([
                    "type" => "danger",
                    "message" => "Belum bisa mengirim kode verifikasi, tunggu $left_time menit lagi.",
                    "timeleft" => abs(($dateNow - $valid_until))
                ]);
                return;
            }
        }

        $this->db->trans_begin();
        try {
            $gpo = $this->at_auth->generatePhoneOTP($user_id);
            if (!$gpo) {
                throw new \Exception("Tidak dapat membuat kode verifikasi");
            }
            $user = $this->at_auth->find($user_id);

            $message = $this->site->makeMessage('sms_verify_phone', [
                'otp_code' => $user->phone_otp
            ]);

            $send = $this->at_site->send_sms_otp($user->phone, $message, true);
            if (!$send) {
                throw new \Exception("Tidak dapat mengirim kode verifikasi");
            }

            $this->db->trans_commit();

            $this->session->set_userdata([
                'phone_otp_valid_until' => date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime('now')))
            ]);

            echo json_encode([
                "type" => "success",
                "message" => "Kode verifikasi berhasil dikirim",
                "timeleft" => strtotime('+5 minutes', strtotime('now')) - strtotime('now')
            ]);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo json_encode([
                "type" => "danger",
                "message" => $th->getMessage(),
                "timeleft" => 0
            ]);
            // $this->session->set_flashdata('error', $th->getMessage());
        }
    }


    /**
     * POST
     *
     * Request :
     * - phone_otp -> text
     *
     */
    public function verify_phone_otp()
    {
        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $otp = $this->input->post('phone_otp');
                if (!$otp) {
                    throw new \Exception("Kode verifikasi diperlukan.");
                }
                $verify = $this->at_auth->verifyPhoneOTP($this->session->userdata('user_id'), $otp);
                // var_dump($verify);die;
                if (!$verify) {
                    throw new \Exception("Tidak dapat verifikasi No Telepon, kode verifikasi salah.");
                }

                $this->session->set_flashdata('message', "Berhasil verifikasi No Telepon.");

                $this->db->trans_commit();

                $this->session->unset_userdata('phone_otp_valid_until');
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/profile'));
    }

    /**
     * GET
     *
     * Request :
     * - username -> text
     * - activation_code
     */
    public function verify_activation_code($username = null, $activation_code = null)
    {
        if ($username && $activation_code) {
            $this->db->trans_begin();

            try {
                $verify = $this->at_auth->verifyActivationCode($username, $activation_code);

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
                /* End - Mengirim data ke distributor */
                $this->session->set_flashdata('message', "Berhasil verifikasi. Silakan Login.");

                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        } else {
            $this->session->set_flashdata('error', "Tidak dapat verifikasi, Username atau Kode Aktivasi salah.");
        }
        redirect(aksestoko_route('aksestoko/auth/signin'));
    }


    /**
     * POST
     * user_id - int
     * new_phone - text
     *
     */
    public function send_activation_code()
    {
        if ($this->isPost()) {
            $user_id = $this->input->post('user_id');
            $new_phone = $this->input->post('new_phone');

            $this->db->trans_begin();

            try {
                $user = $this->at_auth->find($user_id);

                if (!$user) {
                    throw new \Exception("Pengguna tidak ditemukan.");
                }

                if (!$this->at_auth->updateAT($user->id, ['phone' => $new_phone])) {
                    throw new \Exception("Tidak dapat memperbarui No Telepon.");
                }

                if ($user->active == 1) {
                    throw new \Exception("Akun Anda sudah aktif. Silakan Login.");
                }

                if (!$user->activation_code) {
                    throw new \Exception("Kode Aktivasi tidak ditemukan.");
                }

                $now = time();
                $last_sent_activation_code_at = strtotime($user->last_sent_activation_code_at) ?? 0;
                $diff_time = $now - $last_sent_activation_code_at;
                $threshold = 180;

                if ($diff_time >= 0 && $diff_time <= $threshold) {
                    throw new \Exception("Tunggu " . gmdate("i:s", $threshold - $diff_time) . " lagi untuk mengirim kode aktivasi.");
                }

                $link = base_url(aksestoko_route("aksestoko/auth/verify_activation_code")) . "/" . trim($user->username) . "/" . trim($user->activation_code);
                $link = str_replace("\\", "", $link);

                // if(SERVER_QA){
                //     $shorten_link = ($this->at_site->shorten_link_cuttly($link))->url->shortLink;
                // }else{
                //     $shorten_link = ($this->at_site->shorten_link_bitly($link))->id;
                // }
                $shorten_link = ($this->at_site->shorten_link_cuttly($link))->url->shortLink;
                $shorten_link = str_replace("https://", "", $shorten_link);

                $message = $this->site->makeMessage('sms_activation_code', [
                    'store' => (trim($user->company) . " (" . trim($user->username) . ")"),
                    'activation_link' => $shorten_link
                ]);

                $send = $this->at_site->send_sms_otp($new_phone, $message, true);
                if (!$send) {
                    throw new \Exception("Tidak dapat mengirim kode aktivasi.");
                }

                $this->session->set_flashdata('message', "Berhasil mengirim Kode Aktivasi. Dibutuhkan waktu sekitar 2-5 menit untuk menerima pesan.");

                $this->db->update('users', ['last_sent_activation_code_at' => date('Y-m-d H:i:s')], ['id' => $user->id]);

                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/auth/signin'));
    }

    public function dump()
    {
        // try {
        //     //code...
        //     var_dump($this->site->send_sms_otp_medansms("082257173520", "Aku suka kamu", true));
        // } catch (\Throwable $th) {
        //     var_dump($th);
        // }
        // die;
    }

    public function send_session()
    {
        $this->db->trans_begin();
        try {
            $this->load->model('encryption_model');
            $code_customer = trim($this->session->userdata('username'));

            // $cekdataLT = $this->at_auth->cekDataLT($code_customer);
            // if (!$cekdataLT || $cekdataLT['status'] == 'empty') {
            //     throw new Exception("Toko dengan ID $code_customer tidak dapat ditemukan di Bisnis Kokoh.");
            // }

            if ($this->session->userdata('group_customer') != 'lt') {
                throw new Exception("Toko dengan ID $code_customer bukan termasuk LT. Tidak bisa menuju ForcaPOS untuk LT.");
            }

            $data = [
                'issued_for' => 'sending_aksestoko_session',
                'user_id'    => $this->session->userdata('user_id')
            ];
            $json = json_encode($data);
            $encrypt = $this->encryption_model->encrypt($json, APP_TOKEN);

            if (!$encrypt) {
                throw new Exception("Tidak bisa melakukan enkripsi.");
            }

            $this->db->trans_commit();
            redirect(prep_url(FORCAPOS_DOMAIN) . "/" . "auth/get_session?session=" . urlencode($encrypt));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(aksestoko_route('aksestoko/home/main'));
        }
    }

    public function get_session()
    {
        $this->db->trans_begin();
        try {
            $this->load->model('encryption_model');
            $session = $this->input->get('session');
            $decrypt = $this->encryption_model->decrypt($session, APP_TOKEN);
            $data = json_decode($decrypt);
            if (!$data) {
                throw new Exception("Data tidak valid");
            }
            if (!in_array($data->issued_for, ['sending_special_session', 'sending_pos_session'])) {
                throw new Exception("Sesi ini tidak dapat digunakan");
            }
            $user = $this->site->getUser($data->user_id);
            if (!$user) {
                throw new Exception("Pengguna tidak ditemukan");
            }
            $company = $this->site->getCompanyByID($user->company_id);
            if (!$company) {
                throw new Exception("Toko tidak ditemukan");
            }
            if ($user->group_id != 10 || $company->client_id != 'aksestoko') {
                throw new \Exception("Bukan akun AksesToko");
            }

            $this->at_auth->set_session($user);

            $session = [
                'cf1'            => $company->cf1,
                'aksestoko'      => true,
                'group_customer' => 'lt'
            ];

            $this->session->set_userdata($session);

            $this->db->trans_commit();
            redirect(aksestoko_route("aksestoko/home/main"));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(aksestoko_route("aksestoko/auth/signin"));
        }
    }

    public function success_registration()
    {
        $this->data['title_at'] = "Daftar - AksesToko";
        $this->load->view('aksestoko/success_registration', $this->data);
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/models/Auth_model.php';

class At_auth_model extends Auth_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserPoint($user_id)
    {
        $find = $this->db->select("*")
            ->where('id', $user_id)
            ->get("users");
        if ($find->num_rows() > 0) {
            // var_dump($find->row());
            // die;
            return $find->row()->award_points;
        }
        return null;
    }

    public function find($id)
    {
        $find = $this->db->select("*")
            ->where('id', $id)
            ->get("users");
        if ($find->num_rows() > 0) {
            return $find->row();
        }
        return null;
    }

    public function loginAT($identity, $password, $remember = false, $provider = null)
    {
        $this->trigger_events('pre_login');

        if (empty($provider)) {
            if (empty($identity) || empty($password)) {
                $this->set_error('login_unsuccessful');

                throw new \Exception("Username atau Password kosong");

                return false;
            }
        }

        $this->trigger_events('extra_where');
        $this->load->helper('email');
        $this->identity_column = valid_email($identity) ? 'email' : 'username';
        $query = $this->db->select($this->identity_column . ', username, email, id, password, active, last_login, last_ip_address, avatar, gender, group_id, warehouse_id, biller_id, company_id, view_right, edit_right, allow_discount, show_cost, show_price,company')
            ->where($this->identity_column, $this->db->escape_str($identity))
            ->limit(1)
            ->get($this->tables['users']);

        //apabila login diatas gagal, maka cek login dengan nomor telepon
        if ($query->num_rows() !== 1) {
            $this->identity_column = 'phone';
            $query = $this->db->select($this->identity_column . ', username, email, id, password, active, last_login, last_ip_address, avatar, gender, group_id, warehouse_id, biller_id, company_id, view_right, edit_right, allow_discount, show_cost, show_price,company')
                ->where($this->identity_column, $this->db->escape_str($identity))
                ->limit(1)
                ->get($this->tables['users']);
        }

        if ($this->is_time_locked_out($identity)) {
            //Hash something anyway, just to take up time
            if (!empty($identity) || !empty($password)) {
                $this->hash_password($password);
            }

            $this->trigger_events('post_login_unsuccessful');
            $this->set_error('login_timeout');

            throw new \Exception("Akun terkunci. Tunggu 10 menit.");

            return false;
        }

        if ($query->num_rows() === 1) {
            $user = $query->row();

            $password = $this->hash_password_db($user->id, $password);

            if ($password === true || (!empty($identity) && !empty($provider))) {
                if (!isset($this->Settings->single_login)) {
                    $this->Settings->single_login = 0;
                }
                if ($this->Settings->single_login) {
                    $userID_Length = strlen($user->id);
                    $now = time() - (10);
                    $statement = 'SELECT session_id FROM app_sessions WHERE last_activity >= ".$now." AND user_data LIKE \'%s:7:"user_id";s:' . $userID_Length . ':"' . $user->id . '";%\'';
                    $sq = $this->db->query($statement);

                    if ($sq->num_rows() > 0) {
                        $ss = $sq->result();
                        foreach ($ss as $s) {
                            if (!$this->db->delete('app_sessions', array('session_id' => $s->session_id))) {
                                $this->set_error('unable_to_logout_from_other_places');
                            }
                        }
                    }
                }

                if ($user->active != 1) {
                    $this->trigger_events('post_login_unsuccessful');
                    $this->set_error('login_unsuccessful_not_active');

                    $this->session->set_userdata(["user_id" => $user->id]);

                    throw new \Exception("Akun belum diaktifkan. Tekan <a href='javascript:void(0)' id='activationBtn'> disini </a> untuk mengirim Kode Aktivasi.");

                    return false;
                }
                
                $this->load->model('aksestoko/at_company_model', 'at_company');
                $company      = $this->at_company->getCompanyByID($user->company_id);

                if ($user->group_id != 10 || $company->client_id != 'aksestoko') {
                    $this->trigger_events('post_login_unsuccessful');
                    throw new \Exception("Bukan akun AksesToko");
                    return false;
                }

                $this->set_session($user);

                $session = [
                    'cf1'            => $company->cf1,
                    'aksestoko'      => true,
                    'group_customer' => null,
                ];
                
                $cekdataLT    = $this->cekDataLT(str_replace('IDC-', '', $company->cf1));

                if ($cekdataLT && $cekdataLT['data'][0]['GROUP_CUSTOMER'] == 'LT') {
                    $session['group_customer'] = 'lt';
                } else if($cekdataLT) {
                    $session['group_customer'] = 'toko';
                }

                if (BK_INTEGRATION) {
                    $ex = explode('-', $company->cf1);
                    $IDC_cf1 = substr($ex[1], 0, 4);
                    if ($IDC_cf1 != '9000') {
                        $api_toko_aktif = $this->apiTokoAktif($user->company_id, 'signin');
                        if ($api_toko_aktif['curl']['data']['status'] != 'OK') {
                            throw new \Exception("Akun belum terdaftar di Bisnis Kokoh");
                            return false;
                        }
                        $cek_toko = json_decode($this->cekTokoAktif($api_toko_aktif['id_bk'], $api_toko_aktif['curl']));
                        $change = $this->changeCustomer($api_toko_aktif['curl']['data']['data'][0], $cek_toko);
                    }
                }

                $this->session->set_userdata($session);

                $this->update_last_login($user->id);
                $this->update_last_login_ip($user->id);
                $ldata = array('user_id' => $user->id, 'ip_address' => $this->input->ip_address(), 'login' => $identity);
                $this->db->insert('user_logins', $ldata);
                $this->clear_login_attempts($identity);

                if ($remember && $this->config->item('remember_users', 'ion_auth')) {
                    $this->remember_user($user->id);
                }

                $this->trigger_events(array('post_login', 'post_login_successful'));
                $this->set_message('login_successful');

                return true;
            }
        }
        if ($query->num_rows() === 0) {
            $this->trigger_events('post_login_unsuccessful');
            $this->set_error('login_unregister');

            throw new \Exception("Username belum terdaftar");

            return false;
        }

        //Hash something anyway, just to take up time
        $this->hash_password($password);

        $this->increase_login_attempts($identity);

        $this->trigger_events('post_login_unsuccessful');
        $this->set_error('login_unsuccessful');

        throw new \Exception("Kombinasi username dan password salah");

        return false;
    }

    public function changePasswordAT($identity, $old, $new)
    {
        // var_dump($this->identity_column, $identity);
        // die;
        $this->identity_column = "username";

        $this->trigger_events('pre_change_password');

        $this->trigger_events('extra_where');

        $query = $this->db->select('id, password, salt', 'biller_id')
            ->where($this->identity_column, $identity)
            ->limit(1)
            ->get($this->tables['users']);

        if ($query->num_rows() !== 1) {
            $this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
            $this->set_error('password_change_unsuccessful');

            throw new \Exception("Akun tidak ditemukan");

            return false;
        }

        $user = $query->row();

        $old_password_matches = $this->hash_password_db($user->id, $old);

        if ($old_password_matches === true) {
            //store the new password and reset the remember code so all remembered instances have to re-login
            $hashed_new_password = $this->hash_password($new, $user->salt);
            $data = array(
                'password' => $hashed_new_password,
                'remember_code' => null,
            );

            $this->trigger_events('extra_where');

            $successfully_changed_password_in_db = $this->db->update($this->tables['users'], $data, array($this->identity_column => $identity));
            if ($successfully_changed_password_in_db) {
                $this->trigger_events(array('post_change_password', 'post_change_password_successful'));
                $this->set_message('password_change_successful');
                // $this->load->model('Curl_model', 'curl_');
                // $this->curl_->updateEcomerce($user->biller_id,$hashed_new_password);
            } else {
                $this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
                $this->set_error('password_change_unsuccessful');

                throw new \Exception("Gagal menyimpan password");
            }

            return $successfully_changed_password_in_db;
        } else {
            $this->set_error('old_password_wrong');

            throw new \Exception("Password lama salah");
        }

        $this->set_error('password_change_unsuccessful');

        throw new \Exception("Gagal mengganti password");

        return false;
    }

    public function resetPasswordAT($identity, $new)
    {
        // var_dump($this->identity_column, $identity);
        // die;

        $this->identity_column = "username";

        $this->trigger_events('pre_change_password');

        $this->trigger_events('extra_where');

        $query = $this->db->select('id, password, salt', 'biller_id')
            ->where($this->identity_column, $identity)
            ->limit(1)
            ->get($this->tables['users']);

        if ($query->num_rows() !== 1) {
            $this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
            $this->set_error('password_change_unsuccessful');

            throw new \Exception("Akun tidak ditemukan");

            return false;
        }

        $user = $query->row();

        //store the new password and reset the remember code so all remembered instances have to re-login
        $hashed_new_password = $this->hash_password($new, $user->salt);
        $data = array(
            'password' => $hashed_new_password,
            'remember_code' => null,
        );

        $this->trigger_events('extra_where');

        $successfully_changed_password_in_db = $this->db->update($this->tables['users'], $data, array($this->identity_column => $identity));
        if ($successfully_changed_password_in_db) {
            $this->trigger_events(array('post_change_password', 'post_change_password_successful'));
            $this->set_message('password_change_successful');
        } else {
            $this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
            $this->set_error('password_change_unsuccessful');

            throw new \Exception("Gagal menyimpan password");
            return false;
        }

        return $successfully_changed_password_in_db;
    }

    public function updateUserSalesPerson($user_id, $data, $company_id)
    {
        $this->db->where('id', $user_id);
        if ($this->db->update('users', $data)) {
            $this->load->model('audittrail_model', 'audittrail');
            if (!$this->audittrail->insertCustomerSetReferralCode($user_id, $company_id, $data['sales_person_id'])) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_set_referal_code");
            }
            return true;
        }
        return false;
    }

    public function updateAT($id, array $data, $upgs = array())
    {
        $this->trigger_events('pre_update_user');

        $user = $this->user($id)->row();

        // var_dump($this->identity_column);die;

        $this->db->trans_begin();

        if (array_key_exists($this->identity_column, $data) && $this->identity_check($data[$this->identity_column]) && $user->{$this->identity_column} !== $data[$this->identity_column]) {
            $this->db->trans_rollback();
            $this->set_error('account_creation_duplicate_' . $this->identity_column);

            $this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
            $this->set_error('update_unsuccessful');

            throw new \Exception("Gagal memperbarui profil. Duplikat Email.");

            return false;
        }

        $this->identity_column = 'phone';

        if (array_key_exists($this->identity_column, $data) && $this->identity_check($data[$this->identity_column]) && $user->{$this->identity_column} !== $data[$this->identity_column]) {
            $this->db->trans_rollback();
            $this->set_error('account_creation_duplicate_' . $this->identity_column);

            $this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
            $this->set_error('update_unsuccessful');

            throw new \Exception("Gagal memperbarui profil. Duplikat No Telepon.");

            return false;
        }

        $this->identity_column = 'email';

        // Filter the data passed
        $data = $this->_filter_data($this->tables['users'], $data);

        if (array_key_exists('username', $data) || array_key_exists('password', $data) || array_key_exists('email', $data)) {
            if (array_key_exists('password', $data)) {
                if (!empty($data['password'])) {
                    $data['password'] = $this->hash_password($data['password'], $user->salt);
                } else {
                    // unset password so it doesn't effect database entry if no password passed
                    unset($data['password']);
                }
            }
        }

        if ($user->phone != $data['phone']) {
            $data['phone_is_verified'] = 0;
        }

        $this->trigger_events('extra_where');
        $this->db->update($this->tables['users'], $data, array('id' => $user->id));
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            $this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
            $this->set_error('update_unsuccessful');

            throw new \Exception("Gagal memperbarui profil");
            return false;
        }



        $this->db->trans_commit();

        $this->trigger_events(array('post_update_user', 'post_update_user_successful'));
        $this->set_message('update_successful');
        $user = $this->user($id)->row();
        $this->set_session($user);
        return true;
    }

    public function insertUserAT($data, $companies)
    {
        $this->trigger_events('pre_register');

        $manual_activation = $this->config->item('manual_activation', 'ion_auth');
        $email_activation = $this->config->item('email_activation', 'ion_auth');

        $ip_address = $this->_prepare_ip($this->input->ip_address());
        $salt = $this->store_salt ? $this->salt() : false;
        $data['password'] = $this->hash_password($data['password'], $salt);

        $activate_code = sha1(time());
        $data['ip_address'] = $ip_address;
        $data['created_on'] = time();
        $data['last_login'] = time();
        $data['activation_code'] = $activate_code;

        if ($this->store_salt) {
            $data['salt'] = $salt;
        }
        $this->trigger_events('extra_set');

        if ($this->db->insert('users', $data)) {
            $LastID = $this->db->insert_id();
            $getUsers =  $this->db->get_where('users', array('id' => $LastID))->row();
            $this->db->where('id', $getUsers->biller_id);
            $companies['updated_at'] = date('Y-m-d H:i:s');
            $px = $this->db->update('companies', $companies);
            $this->trigger_events('post_register');
            $abc = array(
                'last_id' => $LastID,
                'code' => $data['activation_code'],
                'company_id' => $getUsers->company_id
            );
            return $abc;
        } else {
            throw new \Exception($this->db->error()['message']);
            return false;
        }
    }

    public function findUserByUsername($username)
    {
        $q = $this->db->get_where("users", ["username" => $username], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findUserByPhone($phone)
    {
        $q = $this->db->get_where("users", ["phone" => $phone], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findUserByComapanyId($company_id)
    {
        $q = $this->db->get_where("companies", ["id" => $company_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function forgetPassword($store_code, $phone, $ip)
    {
        $this->db->where("RIGHT(phone, 4) = '$phone'");
        $q = $this->db->get_where("users", ["username" => $store_code], 1);
        // var_dump($this->db->error(), "RIGHT(phone, 4) = '$phone'");die;
        if ($q->num_rows() > 0) {
            $q = $q->row();

            //create kode otp
            $otp = date('d-m-Y H:i:s') . $store_code . $phone . $ip;
            preg_match_all('!\d+!', md5($otp), $otp);
            $otp =  implode('', $otp[0]);
            $otp = substr($otp, 0, 5);
            // var_dump($otp);die;

            $date = strtotime('now');
            $date = date('Y-m-d H:i:s', strtotime('+30 minutes', $date));

            $this->db->insert('users_reset_password', [
                "user_id" => $q->id,
                "store_code" => $store_code,
                "phone" => $phone,
                "ip_address" => $ip,
                "otp_code" => $otp,
                "valid_until" => $date
            ]);

            return $this->db->insert_id();
        }
        throw new \Exception("Akun dengan kombinasi Kode Toko dan No Telp tidak ditemukan");
        return false;
    }

    public function findResetPassword($id_fp)
    {
        $q = $this->db->get_where("users_reset_password", ["id" => $id_fp], 1);
        if ($q->num_rows() > 0) {
            $q = $q->row();
            return $q;
        }
        return null;
    }

    public function checkOtp($id_fp, $otp)
    {
        $q = $this->db->get_where("users_reset_password", ["id" => $id_fp, "otp_code" => $otp], 1);
        if ($q->num_rows() > 0) {
            $q = $q->row();
            $this->db->update("users_reset_password", ["is_success" => 1], ["id" => $id_fp, "otp_code" => $otp]);

            // $date = strtotime($q->created_at);
            // $date = date('Y-m-d H:i:s', strtotime('+30 minutes', $date));
            // return $date;

            return $q;
        }
        return null;
    }

    public function checkRecoveryCode($id_fp)
    {
        $this->db->from('users_reset_password');
        $this->db->join('users', 'users_reset_password.user_id = users.id', 'left');
        $this->db->where(["users_reset_password.id" => $id_fp]);
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }

    public function generatePhoneOTP($user_id)
    {
        $user = $this->find($user_id);
        if (!$user) {
            return false;
        }

        //create kode otp
        $otp = date('d-m-Y H:i:s') . $user->username . $user->phone . uniqid();
        preg_match_all('!\d+!', md5($otp), $otp);
        $otp =  implode('', $otp[0]);
        $otp = substr($otp, 0, 5);

        return $this->db->update('users', [
            "phone_otp" => $otp,
        ], [
            "id" => $user_id
        ]);
    }

    public function verifyPhoneOTP($user_id, $phone_otp)
    {
        $q = $this->db->get_where("users", ["id" => $user_id, "phone_otp" => $phone_otp], 1);
        if ($q->num_rows() > 0) {
            // $q = $q->row();

            // $date = strtotime($q->created_at);
            // $date = date('Y-m-d H:i:s', strtotime('+30 minutes', $date));
            // return $date;

            return $this->db->update("users", ["phone_is_verified" => 1, "phone_otp" => null], ["id" => $user_id, "phone_otp" => $phone_otp]);
        }
        return null;
    }

    public function verifyActivationCode($username, $activation_code)
    {
        $q = $this->db->get_where("users", ["username" => $username, "activation_code" => $activation_code], 1);
        if ($q->num_rows() > 0) {
            if (!$this->db->update("users", ["active" => 1, "phone_is_verified" => 1, "activation_code" => null, 'activated_at' => date('Y-m-d H:i:s')], ["username" => $username, "activation_code" => $activation_code])) {
                return null;
            }
            return $q->row();
        }
        return null;
    }

    public function apiTokoAktif($param, $type)
    {
        $this->load->model('curl_model', 'curl');
        if ($type == 'signin') {
            $q_c = $this->db->get_where('companies', ['id' => $param/*, 'group_name' => "customer"*/]);
            if ($q_c->num_rows() > 0) {
                $c = $q_c->row();
            }
            if (is_null($c->cf1)) {
                return false;
            }
            $ex = explode('-', $c->cf1);
            $id_bk = $ex[1];
        } else {
            $id_bk = $param;
        }

        $q = $this->db->get_where('api_integration', ['type' => "data_toko_aktif_kdcustomer"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();

        $url = $integration->uri;
        $data = json_encode(['kdcustomer' => $id_bk]);
        $curl = json_decode($this->curl->_post($url, $data, true), true);

        $ret['curl'] = $curl;
        $ret['id_bk'] = $id_bk;
        return $ret;
    }

    public function cekDataLT($code_bk)
    {
        $this->load->model('curl_model', 'curl');
        $q = $this->db->get_where('api_integration', ['type' => "data_toko_aktif_kdcustomer"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();

        $url = $integration->uri;
        $data = json_encode(['kdcustomer' => $code_bk]);
        $curl = json_decode($this->curl->_post($url, $data, true), true);

        $ret = $curl['data'];
        return $ret;
    }

    public function cekTokoAktif($id_bk, $toko_aktif)
    {
        //if($toko_aktif['data']['status'] == 'OK'){
        $distributor = $toko_aktif['data']['data'][0]['DISTRIBUTOR'];
        $distributor2 = $toko_aktif['data']['data'][0]['DISTRIBUTOR2'];
        $distributor3 = $toko_aktif['data']['data'][0]['DISTRIBUTOR3'];
        $distributor4 = $toko_aktif['data']['data'][0]['DISTRIBUTOR4'];

        $no_dis = $toko_aktif['data']['data'][0]['NOMOR_DISTRIBUTOR'];
        $no_dis2 = $toko_aktif['data']['data'][0]['NOMOR_DISTRIBUTOR2'];
        $no_dis3 = $toko_aktif['data']['data'][0]['NOMOR_DISTRIBUTOR3'];
        $no_dis4 = $toko_aktif['data']['data'][0]['NOMOR_DISTRIBUTOR4'];

        if ($distributor == null && $distributor2 == null && $distributor3 == null && $distributor4 == null) {
            throw new \Exception("Customer belum memiliki distributor");
        }

        $kwsg = "KOPERASI WARGA SEMEN GRESIK";
        $sid = "SEMEN INDONESIA DISTRIBUTOR";

        if ($distributor != null) {
            if (strpos($distributor, $kwsg) !== false || strpos($distributor, $sid) !== false) {
                $cek_sidigi1 = json_decode($this->apiSidigi($id_bk, $no_dis), true);
                if ($cek_sidigi1['success'] == true) {
                    $ret1 = [
                        'id_bk' => $id_bk,
                        'id_distributor' => $cek_sidigi1['data']['ID_GUDANG']
                        //'id_gudang_sidigi' => $cek_sidigi1['data']['ID_GUDANG']
                    ];
                }
            } else {
                $ret1 = [
                    'id_bk' => $id_bk,
                    'id_distributor' => $no_dis
                    //'id_gudang_sidigi' => null
                ];
            }
        } else {
            $ret1 = null;
        }

        if ($distributor2 != null) {
            if (strpos($distributor2, $kwsg) !== false || strpos($distributor2, $sid) !== false) {
                $cek_sidigi2 = json_decode($this->apiSidigi($id_bk, $no_dis2), true);
                if ($cek_sidigi2['success'] == true) {
                    $ret2 = [
                        'id_bk' => $id_bk,
                        'id_distributor' => $cek_sidigi2['data']['ID_GUDANG']
                        //'id_gudang_sidigi' => $cek_sidigi2['data']['ID_GUDANG']
                    ];
                }
            } else {
                $ret2 = [
                    'id_bk' => $id_bk,
                    'id_distributor' => $no_dis2,
                    //'id_gudang_sidigi' => null
                ];
            }
        } else {
            $ret2 = null;
        }

        if ($distributor3 != null) {
            if (strpos($distributor3, $kwsg) !== false || strpos($distributor3, $sid) !== false) {
                $cek_sidigi3 = json_decode($this->apiSidigi($id_bk, $no_dis3), true);
                if ($cek_sidigi3['success'] == true) {
                    $ret3 = [
                        'id_bk' => $id_bk,
                        'id_distributor' => $cek_sidigi3['data']['ID_GUDANG']
                        //'id_gudang_sidigi' => $cek_sidigi3['data']['ID_GUDANG']
                    ];
                }
            } else {
                $ret3 = [
                    'id_bk' => $id_bk,
                    'id_distributor' => $no_dis3,
                    //'id_gudang_sidigi' => null
                ];
            }
        } else {
            $ret3 = null;
        }

        if ($distributor4 != null) {
            if (strpos($distributor4, $kwsg) !== false || strpos($distributor4, $sid) !== false) {
                $cek_sidigi4 = json_decode($this->apiSidigi($id_bk, $no_dis4), true);
                if ($cek_sidigi4['success'] == true) {
                    $ret4 = [
                        'id_bk' => $id_bk,
                        'id_distributor' => $cek_sidigi4['data']['ID_GUDANG']
                        //'id_gudang_sidigi' => $cek_sidigi4['data']['ID_GUDANG']
                    ];
                }
            } else {
                $ret4 = [
                    'id_bk' => $id_bk,
                    'id_distributor' => $no_dis4,
                    //'id_gudang_sidigi' => null
                ];
            }
        } else {
            $ret4 = null;
        }

        $ret = ['ret1' => $ret1, 'ret2' => $ret2, 'ret3' => $ret3, 'ret4' => $ret4];
        //}
        return json_encode($ret);
    }

    public function apiSidigi($id_bk, $id_distributor)
    {
        $q = $this->db->get_where('api_integration', ['type' => "sidigi_get_data"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $data = 'id_bisnis_kokoh=' . $id_bk . '&id_distributor=' . $id_distributor;
        $integration = $q->row();
        $url = $integration->uri . '?' . $data;

        $curl = $this->curl->_get($url);
        return $curl;
    }

    public function changeCustomer($data, $cekToko)
    {
        $this->db->like('cf1', $data['KD_CUSTOMER'])
            ->where('group_name', 'customer');
        $cek = $this->db->get('companies');
        $id_api_tambah = [];
        $id_db_compare = [];
        $id_api_compare = [];
        if ($cek->num_rows() > 0) {
            foreach ($cek->result() as $k => $v) {
                $id_db_compare[] = $v->company_id;
                $compare = $this->compareCompanyId($data, $cekToko, $v);
                if (!empty($compare['id_api_tambah'])) {
                    $id_api_tambah[] =  $compare['id_api_tambah'];
                }
                if (!empty($compare['id_api_compare'])) {
                    $id_api_compare[] =  $compare['id_api_compare'];
                }
            }
            $id_api_tambah = array_unique(array_merge($id_api_tambah));
            $id_api_compare = array_unique(array_merge($id_api_compare));

            if (!empty($id_api_tambah)) {
                foreach ($id_api_tambah as $k => $i) {
                    foreach ($i as $item) {
                        if (!in_array($item, $id_db_compare)) {
                            $ins = [
                                "group_id" => 3,
                                "group_name" => "customer",
                                "company_id" => $item,
                                "customer_group_id" => 1,
                                "customer_group_name" => "General",
                                "name" => $data['NM_CUSTOMER'] ?? '',
                                "company" => $data['NAMA_TOKO'] ?? '',
                                "address" => $data['ADDRESS'] ?? '',
                                "city" => $data['NM_DISTRIK'] ?? '',
                                "state" => $data['KECAMATAN'] ?? '',
                                "country" => $data['PROVINSI'] ?? '',
                                "phone" => $data['NO_HANDPHONE'] ?? '',
                                "cf1" => "IDC-" . $data['KD_CUSTOMER'],
                                "payment_term" => 0,
                                "logo" =>  "logo.png",
                                "award_points" => 0,
                                "is_active" => 1
                            ];
                            //$insert = $this->db->insert('companies', $ins);
                        }
                    }
                }
            }

            if (!empty($id_api_compare)) {
                foreach ($id_api_compare as $i) {
                    $id_change = array_diff($id_db_compare, $i);
                }
            }

            if ($id_change && !empty($id_change)) {
                foreach ($id_change as $v) {
                    $this->db->like('cf1', $data['KD_CUSTOMER'])
                        ->where('company_id', $v)
                        ->where('group_name', 'customer');
                    $get = $this->db->get('companies')->row();
                    $ganti = ['cf1' => $data['KD_CUSTOMER'],  'is_active' => 0];
                    //$this->db->update('companies', $ganti, ['id' => $get->id]);
                }
            }
        }
        return true;
    }

    public function compareCompanyId($dataApi, $cekToko, $dataDB)
    {
        $id_api_tambah = [];
        $id_api_compare = [];
        for ($i = 1; $i < 5; $i++) {
            $val = "ret" . $i;
            if ($cekToko->$val != null) {
                $id_distributor = ltrim($cekToko->$val->id_distributor, '0');
                $this->db->where('cf1', $id_distributor)
                    ->where('group_name', 'biller');
                $q = $this->db->get('companies');
                if ($q->num_rows() > 0) {
                    $company_id = $q->row()->company_id;
                    $id_api_compare[] = $company_id;
                    if (strpos($dataDB->company_id, $company_id) == false) {
                        $id_api_tambah[] = $company_id;
                    }

                    if ($dataDB->is_active == 0 && strpos($dataDB->cf1, $dataApi['KD_CUSTOMER'])  !== false && $dataDB->company_id == $company_id) {
                        $ganti = ['cf1' => "IDC-" . $dataApi['KD_CUSTOMER'],  'is_active' => 1];
                        //$this->db->update('companies', $ganti, ['id' => $dataDB->id]);
                    }
                }
            }
        }
        $return = [
            'id_api_tambah' => $id_api_tambah,
            'id_api_compare' => $id_api_compare
        ];
        return $return;
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->insertLogActivities();
        $this->lang->load('auth', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->load->model('auth_model');
        $this->load->model('daerah_model');
        $this->load->model('companies_model');
        $this->load->model('sales_person_model');
        $this->load->model('products_model');
        $this->load->model('subscription_model');
        $this->load->model('integration_model');
        $this->load->library('ion_auth');
    }

    public function index()
    {
        if (!$this->loggedIn) {
            redirect('login');
        } else {
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function users()
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        //        if (!$this->Owner) {
        //            $this->session->set_flashdata('warning', lang('access_denied'));
        //            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        //        }

        $link_type = ['mb_users', 'mb_edit_user'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('users')));
        $meta = array('page_title' => lang('users'), 'bc' => $bc);
        $this->page_construct('auth/index', $meta, $this->data);
    }

    public function getUsers()
    {
        //        if ( ! $this->Owner) {
        //            $this->session->set_flashdata('warning', lang('access_denied'));
        //            $this->sma->md();
        //        }

        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users') . ".id as id, " . $this->db->dbprefix('users') . ".first_name, " . $this->db->dbprefix('users') . ".last_name, " . $this->db->dbprefix('users') . ".email, " . $this->db->dbprefix('users') . ".company, " . $this->db->dbprefix('warehouses') . ".name as warehouse, " . $this->db->dbprefix('users') . ".award_points, " . $this->db->dbprefix('groups') . ".name, " . $this->db->dbprefix('users') . ".active")
            ->from("users")
            ->join('warehouses', 'warehouses.id=users.warehouse_id')
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where($this->db->dbprefix('users') . '.company_id', $this->session->userdata('company_id'))
            ->edit_column($this->db->dbprefix('users') . '.active', '$1__$2', $this->db->dbprefix('users') . '.active, id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('auth/profile/$1') . "' class='tip' title='" . lang("edit_user") . "'><i class=\"fa fa-edit\"></i></a></div>", "id");
        //
        //        if (!$this->Owner) {
        //            $this->datatables->unset_column('id');
        //        }
        echo $this->datatables->generate();
    }

    public function getUserLogins($id = null)
    {
        if (!$this->ion_auth->in_group(array('super-admin', 'admin'))) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("login, ip_address, time")
            ->from("user_logins")
            ->where('user_id', $id);

        echo $this->datatables->generate();
    }

    public function delete_avatar($id = null, $avatar = null)
    {
        $this->db->trans_begin();
        try {
            if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('owner') && $id != $this->session->userdata('user_id')) {
                // $this->session->set_flashdata('warning', lang("access_denied"));
                throw new Exception(lang("access_denied"));

                // die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER["HTTP_REFERER"] . "'; }, 0);</script>");
                // redirect($_SERVER["HTTP_REFERER"]);
            }
            unlink('assets/uploads/avatars/' . $avatar);
            unlink('assets/uploads/avatars/thumbs/' . $avatar);
            if ($id == $this->session->userdata('user_id')) {
                $this->session->unset_userdata('avatar');
            }
            $this->db->update('users', array('avatar' => null), array('id' => $id));
            $this->session->set_flashdata('message', lang("avatar_deleted"));
            // die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER["HTTP_REFERER"] . "'; }, 0);</script>");
            $this->db->trans_commit();
            //            var_dump('berhasil');die;
            redirect($_SERVER["HTTP_REFERER"]);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            //            var_dump('gagal');die;
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function profile($id = null)
    {
        if (!$this->ion_auth->logged_in() || (!$this->ion_auth->in_group('owner') && !$this->ion_auth->in_group('admin')
            && !$this->ion_auth->in_group('admin gudang') && !$this->ion_auth->in_group('kasir') && !$this->ion_auth->in_group('toko besar'))) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$id || empty($id)) {
            redirect('auth');
        }

        $this->data['title'] = lang('profile');
        $this->data['authorized'] = $this->sma->getAuthorized();

        $user = $this->ion_auth->user($id)->row();
        if ($user->company_id != $this->session->userdata('company_id')) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        //        var_dump([$this->session->userdata('company_id'),$user->company_id]);die;
        $groups = $this->ion_auth->groups()->result_array();
        $this->data['company'] = $this->companies_model->getCompanyByID($this->session->userdata('biller_id'));
        $this->data['csrf'] = $this->_get_csrf_nonce();
        $this->data['user'] = $user;
        $this->data['groups'] = $groups;
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['password'] = array(
            'name' => 'password',
            'id' => 'password',
            'class' => 'form-control',
            'type' => 'password',
            'value' => ''
        );
        $this->data['password_confirm'] = array(
            'name' => 'password_confirm',
            'id' => 'password_confirm',
            'class' => 'form-control',
            'type' => 'password',
            'value' => ''
        );
        $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
        $this->data['old_password'] = array(
            'name' => 'old',
            'id' => 'old',
            'class' => 'form-control',
            'type' => 'password',
        );
        $this->data['new_password'] = array(
            'name' => 'new',
            'id' => 'new',
            'type' => 'password',
            'class' => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        );
        $this->data['new_password_confirm'] = array(
            'name' => 'new_confirm',
            'id' => 'new_confirm',
            'type' => 'password',
            'class' => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        );
        $this->data['user_id'] = array(
            'name' => 'user_id',
            'id' => 'user_id',
            'type' => 'hidden',
            'value' => $user->id,
        );

        $this->data['id'] = $id;

        $link_type = ['mb_edit_profile', 'mb_change_password', 'mb_change_avatar'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('auth/users'), 'page' => lang('users')), array('link' => '#', 'page' => lang('profile')));
        $meta = array('page_title' => lang('profile'), 'bc' => $bc);
        $this->page_construct('auth/profile', $meta, $this->data);
    }

    public function captcha_check($cap)
    {
        $expiration = time() - 300; // 5 minutes limit
        $this->db->delete('captcha', array('captcha_time <' => $expiration));

        $this->db->select('COUNT(*) AS count')
            ->where('word', $cap)
            ->where('ip_address', $this->input->ip_address())
            ->where('captcha_time >', $expiration);

        if ($this->db->count_all_results('captcha')) {
            return true;
        } else {
            $this->form_validation->set_message('captcha_check', lang('captcha_wrong'));
            return false;
        }
    }


    public function login($m = null)
    {
        if ($this->loggedIn) {
            $this->session->set_flashdata('error', $this->session->flashdata('error'));
            redirect('welcome');
        }
        $this->data['title'] = lang('login');

        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }
        $GetUsername = $this->input->post('identity') ? $this->input->post('identity') : $this->input->post('email');
        $GetProvider = $this->input->post('provider');
        $ip_address = $this->input->post('ip_address');
        if ($this->form_validation->run() == true or !empty($GetProvider)) {
            $remember = (bool) $this->input->post('remember');
            if ($this->ion_auth->login($GetUsername, $this->input->post('password'), $remember, $this->input->post('provider'), $ip_address)) {
                if ($this->Settings->mmode) {
                    if (!$this->ion_auth->in_group('owner')) {
                        $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                        redirect('auth/logout');
                    }
                }
                if ($this->ion_auth->in_group('customer')) {
                    redirect('auth/logout/1');
                }
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $referrer = $this->session->userdata('requested_page') ? $this->session->userdata('requested_page') : 'welcome';
                if (!$this->ion_auth->in_group('owner')) {
                    $this->sma->checkExperience();
                }
                redirect($referrer);
            } elseif ($GetProvider == "google" || $GetProvider == "facebook") {
                $dataGoogle = array(
                    'provider' => $this->input->post('provider'),
                    'first_name' => $this->input->post('fname'),
                    'last_name' => $this->input->post('lname'),
                    'email' => $this->input->post('email'),
                    'login_hint' => $this->input->post('login_hint'),
                    'uuid' => $this->input->post('uuid'),
                    'picture' => $this->input->post('picture')
                );
                $this->session->set_userdata('dataAuth', $dataGoogle);
                $this->session->set_flashdata('message', lang('register_email'));
                $this->sign_up($dataGoogle);
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                $this->session->set_userdata('email', $this->input->post('identity'));
                redirect('login');
            }
        }
        // elseif ($this->input->post('provider')) {
        //     $cek=$this->auth_model->getData($this->input->post('email'));

        //     if ($this->ion_auth->login($this->input->post('email'), NULL, $remember, $this->input->post('provider'))) {
        //         if ($this->Settings->mmode) {
        //             if (!$this->ion_auth->in_group('owner')) {
        //                 $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
        //                 redirect('auth/logout');
        //             }
        //         }
        //         if ($this->ion_auth->in_group('customer') || $this->ion_auth->in_group('supplier')) {
        //             redirect('auth/logout/1');
        //         }
        //         $this->session->set_flashdata('message', $this->ion_auth->messages());
        //         $referrer = $this->session->userdata('requested_page') ? $this->session->userdata('requested_page') : 'welcome';
        //         redirect($referrer);
        //     }
        //     else{
        //         $dataGoogle=array(
        //             'provider'=>$this->input->post('provider'),
        //             'first_name'=>$this->input->post('fname'),
        //             'last_name'=>$this->input->post('lname'),
        //             'email'=>$this->input->post('email'),
        //             'login_hint'=>$this->input->post('login_hint'),
        //             'uuid'=>$this->input->post('uuid'),
        //             'picture'=>$this->input->post('picture'));
        //         $this->session->set_userdata('dataAuth',$dataGoogle);
        //         $this->sign_up($dataGoogle);
        //         // redirect('auth/sign_up/'.);
        //     }
        // }
        else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message'] = $this->session->flashdata('message');
            if ($this->Settings->captcha) {
                $this->load->helper('captcha');
                $vals = array(
                    'img_path' => './assets/captcha/',
                    'img_url' => site_url() . 'assets/captcha/',
                    'img_width' => 150,
                    'img_height' => 34,
                    'word_length' => 5,
                    'colors' => array('background' => array(255, 255, 255), 'border' => array(204, 204, 204), 'text' => array(102, 102, 102), 'grid' => array(204, 204, 204))
                );
                $cap = create_captcha($vals);
                $capdata = array(
                    'captcha_time' => $cap['time'],
                    'ip_address' => $this->input->ip_address(),
                    'word' => $cap['word']
                );

                $query = $this->db->insert_string('captcha', $capdata);
                $this->db->query($query);
                $this->data['image'] = $cap['image'];
                $this->data['captcha'] = array(
                    'name' => 'captcha',
                    'id' => 'captcha',
                    'type' => 'text',
                    'class' => 'form-control',
                    'required' => 'required',
                    'placeholder' => lang('type_captcha')
                );
            }

            $this->data['identity'] = array(
                'name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => lang('email'),
                'value' => $this->form_validation->set_value('identity'),
            );
            $this->data['password'] = array(
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => lang('password'),
            );
            $this->data['allow_reg'] = $this->Settings->allow_reg;
            if ($m == 'db') {
                $this->data['message'] = lang('db_restored');
            } elseif ($m) {
                $this->data['error'] = lang('we_are_sorry_as_this_sction_is_still_under_development.');
            }
            $this->data['version'] = $this->Settings->version;
            $this->load->view($this->theme . 'auth/login', $this->data);
        }
    }

    public function reload_captcha()
    {
        $this->load->helper('captcha');
        $vals = array(
            'img_path' => './assets/captcha/',
            'img_url' => site_url() . 'assets/captcha/',
            'img_width' => 150,
            'img_height' => 34,
            'word_length' => 5,
            'colors' => array('background' => array(255, 255, 255), 'border' => array(204, 204, 204), 'text' => array(102, 102, 102), 'grid' => array(204, 204, 204))
        );
        $cap = create_captcha($vals);
        $capdata = array(
            'captcha_time' => $cap['time'],
            'ip_address' => $this->input->ip_address(),
            'word' => $cap['word']
        );
        $query = $this->db->insert_string('captcha', $capdata);
        $this->db->query($query);
        //$this->data['image'] = $cap['image'];

        echo $cap['image'];
    }

    public function logout($m = null)
    {
        $this->ion_auth->logout();
        $this->session->set_flashdata('message', $this->ion_auth->messages());

        redirect('login/' . $m);
    }

    public function change_password()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('login');
        }
        $this->form_validation->set_rules('old_password', lang('old_password'), 'required');
        $this->form_validation->set_rules('new_password', lang('new_password'), 'required|min_length[8]|max_length[25]');
        $this->form_validation->set_rules('new_password_confirm', lang('confirm_password'), 'required|matches[new_password]');

        $user = $this->ion_auth->user()->row();

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('auth/profile/' . $user->id . '/#cpassword');
        } else {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));

            $change = $this->ion_auth->change_password($identity, $this->input->post('old_password'), $this->input->post('new_password'));
            if ($change) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->logout();
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('auth/profile/' . $user->id . '/#cpassword');
            }
        }
    }

    public function forgot_password()
    {
        $this->form_validation->set_rules('forgot_email', lang('email_address'), 'required|valid_email');

        if ($this->form_validation->run() == false) {
            $error = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->session->set_flashdata('error', $error);
            redirect("login#forgot_password");
        } else {
            $identity = $this->ion_auth->where('email', strtolower($this->input->post('forgot_email')))->users()->row();

            if (empty($identity)) {
                $this->ion_auth->set_message('forgot_password_email_not_found');
                $this->session->set_flashdata('error', $this->ion_auth->messages());
                redirect("login#forgot_password");
            }

            $forgotten = $this->ion_auth->forgotten_password($identity->email);
            if ($forgotten) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("login#forgot_password");
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect("login#forgot_password");
            }
        }
    }

    public function reset_password($code = null)
    {
        if (!$code) {
            show_404();
        }

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {
            $this->form_validation->set_rules('new', lang('password'), 'required|min_length[8]|max_length[25]|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', lang('confirm_password'), 'required');

            if ($this->form_validation->run() == false) {
                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['message'] = $this->session->flashdata('message');
                $this->data['title'] = lang('reset_password');
                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = array(
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'class' => 'form-control',
                    'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                    'data-bv-regexp-message' => lang('pasword_hint'),
                    'placeholder' => lang('new_password')
                );
                $this->data['new_password_confirm'] = array(
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'class' => 'form-control',
                    'data-bv-identical' => 'true',
                    'data-bv-identical-field' => 'new',
                    'data-bv-identical-message' => lang('pw_not_same'),
                    'placeholder' => lang('confirm_password')
                );
                $this->data['user_id'] = array(
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                );
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['code'] = $code;
                $this->data['identity_label'] = $user->email;
                //render
                $this->load->view($this->theme . 'auth/reset_password', $this->data);
            } else {
                // do we have a valid request?
                //$this->_valid_csrf_nonce() === false || $user->id != $this->input->post('user_id')
                if ($user->id != $this->input->post('user_id')) {

                    //something fishy might be up
                    $this->ion_auth->clear_forgotten_password_code($code);
                    show_error(lang('error_csrf'));
                } else {
                    // finally change the password
                    $identity = $user->email;

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

                    if ($change) {
                        //if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        //$this->logout();
                        redirect('login');
                    } else {
                        $this->session->set_flashdata('error', $this->ion_auth->errors());
                        redirect('auth/reset_password/' . $code);
                    }
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect("login#forgot_password");
        }
    }

    public function activate($id, $code = false)
    {
        if ($this->loggedIn && (!$this->sma->UpdateAutorizedPermissions('users') || (!$this->Admin && !$this->Owner && !$this->LT))) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('auth/users');
        }
        $this->db->trans_begin();
        try {
            if ($code !== false) {
                $activation = $this->ion_auth->activate($id, $code);
            } elseif ($this->Owner || $this->Admin) {
                $activation = $this->ion_auth->activate($id);
            }

            if (!$activation) {
                throw new Exception($this->ion_auth->errors());
            }

            $this->db->trans_commit();

            $this->session->set_flashdata('message', $this->ion_auth->messages());

            if ($this->Owner || $this->Admin) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                redirect("auth/login");
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect("forgot_password");
        }
    }

    public function deactivate($id = null)
    {
        $this->db->trans_begin();
        try {
            // $this->sma->checkPermissions('users', true);
            $id = $this->config->item('use_mongodb', 'ion_auth') ? (string) $id : (int) $id;
            $this->form_validation->set_rules('confirm', lang("confirm"), 'required');

            if ($this->form_validation->run() == false) {
                if ($this->input->post('deactivate')) {
                    throw new Exception(validation_errors());
                } else {
                    $this->data['csrf'] = $this->_get_csrf_nonce();
                    $this->data['user'] = $this->ion_auth->user($id)->row();
                    $this->data['modal_js'] = $this->site->modal_js();
                    $this->load->view($this->theme . 'auth/deactivate_user', $this->data);
                }
            } else {
                if ($this->input->post('confirm') == 'yes') {
                    if ($id != $this->input->post('id')) {
                        show_error(lang('error_csrf'));
                    }

                    if ($this->ion_auth->logged_in() && ($this->Owner || $this->Admin)) {
                        $this->ion_auth->deactivate($id);
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                    }
                }
                $this->db->trans_commit();
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function activate_users($id, $code = false)
    {
        $this->db->trans_begin();
        try {
            if ($code !== false) {
                $activation = $this->ion_auth->activate($id, $code);
            } elseif ($this->Owner || $this->Admin) {
                $activation = $this->ion_auth->activate($id);
            }

            if (!$activation) {
                throw new Exception($this->ion_auth->errors());
            }

            $this->db->trans_commit();

            $this->session->set_flashdata('message', $this->ion_auth->messages());

            if ($this->Owner || $this->Admin) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                redirect("auth/login");
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function deactivate_users($id = null)
    {
        $this->db->trans_begin();
        try {
            $id = $this->config->item('use_mongodb', 'ion_auth') ? (string) $id : (int) $id;
            $this->form_validation->set_rules('confirm', lang("confirm"), 'required');

            if ($this->form_validation->run() == false) {
                if ($this->input->post('deactivate')) {
                    throw new Exception(validation_errors());
                } else {
                    $this->data['csrf'] = $this->_get_csrf_nonce();
                    $this->data['user'] = $this->ion_auth->user($id)->row();
                    $this->data['modal_js'] = $this->site->modal_js();
                    $this->load->view($this->theme . 'auth/deactivate_user', $this->data);
                }
            } else {
                if ($this->input->post('confirm') == 'yes') {
                    if ($id != $this->input->post('id')) {
                        show_error(lang('error_csrf'));
                    }

                    if ($this->ion_auth->logged_in() && ($this->Owner || $this->Admin)) {
                        $this->ion_auth->deactivate($id);
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                    }
                }
                $this->db->trans_commit();
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    
    public function create_user()
    {
        $this->db->trans_begin();
        try {

            if (!$this->sma->CreatedPermissions('users') || (!$this->Admin && !$this->Owner && !$this->LT)) {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('auth/users');
            }
            $this->data['title'] = "Create User";
            $this->form_validation->set_rules('username', lang("username"), 'trim|is_unique[users.username]');
            $this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[users.email]');
            $this->form_validation->set_rules('status', lang("status"), 'trim|required');
            $this->form_validation->set_rules('group', lang("group"), 'trim|required');

            if ($this->form_validation->run() == true) {
                //            $username = strtolower($this->input->post('username'));
                $username =  strtolower($this->input->post('email'));
                $email = strtolower($this->input->post('email'));
                $password = $this->input->post('password');
                $notify = $this->input->post('notify');
                $location = $this->companies_model->getCompanyByID($this->session->userdata('biller_id'));

                $additional_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'company_id' => $this->session->userdata('company_id'),
                    'phone' => $this->input->post('phone'),
                    'gender' => $this->input->post('gender'),
                    'group_id' => $this->input->post('group') ? $this->input->post('group') : '3',
                    'biller_id' => $this->input->post('biller'),
                    'warehouse_id' => $this->input->post('warehouse'),
                    'view_right' => $this->input->post('view_right'),
                    'edit_right' => $this->input->post('edit_right'),
                    'allow_discount' => $this->input->post('allow_discount'),
                    'device_id' => '0',
                    'city' => $location->city,
                    'state' => $location->state,
                    'address' => $location->address,
                );
                $active = $this->input->post('status');
            }
            if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
                $this->db->trans_commit();

                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("auth/users");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
                $this->data['groups'] = $this->ion_auth->groups()->result_array();
                $this->data['billers'] = $this->site->getAllCompanies('biller');
                $this->data['warehouses'] = $this->site->getAllWarehouses();

                $link_type = ['mb_create_user'];
                $this->load->model('db_model');
                $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
                foreach ($get_link as $val) {
                    $this->data[$val->type] = $val->uri;
                }

                $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('auth/users'), 'page' => lang('users')), array('link' => '#', 'page' => lang('create_user')));
                $meta = array('page_title' => lang('users'), 'bc' => $bc);
                $this->page_construct('auth/create_user', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function edit_user($id = null)
    {
        $this->db->trans_begin();
        try {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
            }
            $this->data['title'] = lang("edit_user");

            if (!$this->loggedIn || !$this->Admin && $id != $this->session->userdata('user_id')) {
                throw new Exception(lang("access_denied"));
            }

            $user = $this->ion_auth->user($id)->row();

            if ($user->username != $this->input->post('username')) {
                $this->form_validation->set_rules('username', lang("username"), 'trim|is_unique[users.username]');
            }
            if ($user->email != $this->input->post('email')) {
                $this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[users.email]');
            }

            if ($this->form_validation->run() === true) {
                if ($this->Owner) {
                    if ($id == $this->session->userdata('user_id')) {
                        $data = array(
                            'first_name' => $this->input->post('first_name'),
                            'last_name' => $this->input->post('last_name'),
                            'company' => $this->input->post('company'),
                            'phone' => $this->input->post('phone'),
                            'gender' => $this->input->post('gender'),
                            'address' => $this->sma->clear_tags($this->input->post('address')),
                            'country' => $this->input->post('provinsi'),
                            'city' => $this->input->post('kabupaten'),
                            'state' => $this->input->post('kecamatan'),
                        );
                    } elseif ($this->ion_auth->in_group('customer', $id) || $this->ion_auth->in_group('supplier', $id)) {
                        $data = array(
                            'first_name' => $this->input->post('first_name'),
                            'last_name' => $this->input->post('last_name'),
                            'company' => $this->input->post('company'),
                            'phone' => $this->input->post('phone'),
                            'gender' => $this->input->post('gender'),
                        );
                    } else {
                        $data = array(
                            'first_name' => $this->input->post('first_name'),
                            'last_name' => $this->input->post('last_name'),
                            'company' => $this->input->post('company'),
                            'username' => $this->input->post('username'),
                            'email' => $this->input->post('email'),
                            'phone' => $this->input->post('phone'),
                            'gender' => $this->input->post('gender'),
                            'active' => $this->input->post('status'),
                            'group_id' => $this->input->post('group'),
                            'biller_id' => $this->input->post('biller') ? $this->input->post('biller') : null,
                            'warehouse_id' => $this->input->post('warehouse') ? $this->input->post('warehouse') : null,
                            'award_points' => $this->input->post('award_points'),
                            'view_right' => $this->input->post('view_right'),
                            'edit_right' => $this->input->post('edit_right'),
                            'allow_discount' => $this->input->post('allow_discount'),
                            'country' => $this->input->post('provinsi'),
                            'city' => $this->input->post('kabupaten'),
                            'state' => $this->input->post('kecamatan'),
                            'address' => $this->input->post('address'),
                        );
                    }
                } elseif ($this->Admin) {
                    if ($id == $this->session->userdata('user_id')) {
                        $data = array(
                            'first_name' => $this->input->post('first_name'),
                            'last_name' => $this->input->post('last_name'),
                            'company' => $this->input->post('company'),
                            'phone' => $this->input->post('phone'),
                            'email' => $this->input->post('email'),
                            'gender' => $this->input->post('gender'),
                            'award_points' => $this->input->post('award_points'),
                            'country' => $this->input->post('provinsi'),
                            'username' => $this->input->post('email'),
                            'city' => $this->input->post('kabupaten'),
                            'state' => $this->input->post('kecamatan'),
                            'address' => $this->input->post('address'),
                            'notify' => $this->input->post('notify'),
                        );
                    } else {
                        $data =  array(
                            'first_name' => $this->input->post('first_name'),
                            'last_name' => $this->input->post('last_name'),
                            'company' => $this->input->post('company'),
                            'phone' => $this->input->post('phone'),
                            'email' => $this->input->post('email'),
                            'group_id' => $this->input->post('group'),
                            'gender' => $this->input->post('gender'),
                            'warehouse_id' => $this->input->post('warehouse') ? $this->input->post('warehouse') : null,
                            'view_right' => $this->input->post('view_right'),
                            'edit_right' => $this->input->post('edit_right'),
                            'allow_discount' => $this->input->post('allow_discount'),
                            'award_points' => $this->input->post('award_points'),
                            'country' => $this->input->post('provinsi'),
                            'username' => $this->input->post('email'),
                            'city' => $this->input->post('kabupaten'),
                            'state' => $this->input->post('kecamatan'),
                            'address' => $this->input->post('address'),
                            'notify' => $this->input->post('notify'),
                        );

                        if ($this->sma->UpdateAutorizedPermissions('users'))
                            $data['active'] = $this->input->post('status');
                    }
                } else {
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'phone' => $this->input->post('phone'),
                        'gender' => $this->input->post('gender'),
                    );
                }
                if ($this->Owner) {
                    if ($this->input->post('password')) {
                        if (DEMO) {
                            throw new Exception(lang('disabled_in_demo'));
                            // $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                            // redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $this->form_validation->set_rules('password', lang('edit_user_validation_password_label'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
                        $this->form_validation->set_rules('password_confirm', lang('edit_user_validation_password_confirm_label'), 'required');

                        $data['password'] = $this->input->post('password');
                    }
                }
                //$this->sma->print_arrays($data);
            }

            if ($this->form_validation->run() === true && $this->ion_auth->update($user->id, $data)) {
                if ($this->input->post('cf1') != null) {
                    $companies = array(
                        "latitude" => $this->input->post('latitude'),
                        "longitude" => $this->input->post('longitude'),
                        "postal_code" => $this->input->post('postalcode'),
                        // "cf1" =>$this->input->post('cf1')?$this->input->post('cf1'):null,
                        "cf2" => $this->input->post('cf2') ? $this->input->post('cf2') : null,
                        "cf3" => $this->input->post('cf3') ? $this->input->post('cf3') : null,
                        "cf4" => $this->input->post('cf4') ? $this->input->post('cf4') : null,
                        "cf5" => $this->input->post('cf5') ? $this->input->post('cf5') : null,
                    );

                    $cmp = $this->auth_model->checkCF1Distributor($this->input->post('cf1'), $this->session->userdata('company_id'));
                    if ($cmp) {
                        throw new Exception(lang('duplicate_cf1'));
                    }
                    $companies['cf1'] = $this->input->post('cf1');
                } else {
                    $companies = array(
                        "latitude" => $this->input->post('latitude'),
                        "longitude" => $this->input->post('longitude'),
                        //                        "postal_code" => $this->input->post('postalcode'),
                    );
                }
                $this->db->where('id', $user->biller_id);
                $px = $this->db->update('companies', $companies);
                $this->load->model('Curl_model', 'curl_');
                $this->curl_->updateEcomerce($user->biller_id);

                $this->session->set_flashdata('message', lang('user_updated'));

                $this->db->trans_commit();

                redirect("auth/profile/" . $id);
            } else {
                throw new Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect($_SERVER["HTTP_REFERER"]);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    public function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    public function _valid_csrf_nonce()
    {
        if (
            $this->input->post($this->session->flashdata('csrfkey')) !== false &&
            $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function _render_page($view, $data = null, $render = false)
    {
        $this->viewdata = (empty($data)) ? $this->data : $data;
        $view_html = $this->load->view('header', $this->viewdata, $render);
        $view_html .= $this->load->view($view, $this->viewdata, $render);
        $view_html = $this->load->view('footer', $this->viewdata, $render);

        if (!$render) {
            return $view_html;
        }
    }

    /**
     * @param null $id
     */
    public function update_avatar($id = null)
    {
        $this->db->trans_begin();
        try {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
            }

            if (!$this->ion_auth->logged_in() || !$this->Owner && $id != $this->session->userdata('user_id')) {
                throw new Exception(lang("access_denied"));

                // $this->session->set_flashdata('warning', lang("access_denied"));
                // redirect($_SERVER["HTTP_REFERER"]);
            }

            //validate form input
            $this->form_validation->set_rules('avatar', lang("avatar"), 'trim');

            if ($this->form_validation->run() == true) {
                if ($_FILES['avatar']['size'] > 0) {
                    $file = $this->integration_model->upload_files($_FILES['avatar']);
                    $photo = $file->url;
                    
                    /* $this->load->library('upload');

                    $config['upload_path'] = 'assets/uploads/avatars';
                    $config['allowed_types'] = 'gif|jpg|png';
                    //$config['max_size'] = '500';
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload('avatar')) {
                        $error = $this->upload->display_errors();
                        throw new Exception($error);

                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }

                    $photo = $this->upload->file_name;

                    $this->load->helper('file');
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = 'assets/uploads/avatars/' . $photo;
                    $config['new_image'] = 'assets/uploads/avatars/thumbs/' . $photo;
                    $config['maintain_ratio'] = true;
                    $config['width'] = 150;
                    $config['height'] = 150;

                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);

                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }*/
                    $user = $this->ion_auth->user($id)->row(); 
                } else {
                    $this->form_validation->set_rules('avatar', lang("avatar"), 'required');
                }
            }

            if ($this->form_validation->run() == true && $this->auth_model->updateAvatar($id, $photo)) {
                /*unlink('assets/uploads/avatars/' . $user->avatar);
                unlink('assets/uploads/avatars/thumbs/' . $user->avatar);*/
                if (file_exists('assets/uploads/avatars/thumbs/' . $user->avatar)) {
                    unlink('assets/uploads/avatars/thumbs/' . $user->avatar);
                    unlink('assets/uploads/avatars/' . $user->avatar);
                } else {
                    unlink($user->avatar);
                }
                $this->session->set_userdata('avatar', $photo);
                $this->session->set_flashdata('message', lang("avatar_updated"));
                $this->load->model('Curl_model', 'curl_');
                $this->curl_->updateEcomerce($user->biller_id);
                $this->db->trans_commit();
                redirect("auth/profile/" . $id);
            } else {
                throw new Exception(validation_errors());

                // $this->session->set_flashdata('error', validation_errors());
                // redirect("auth/profile/" . $id);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function register()
    {
        $this->db->trans_begin();
        try {
            $this->data['title'] = "Register";
            // if (!$this->allow_reg) {
            if ($this->allow_reg) {
                throw new Exception(lang('registration_is_disabled'));
                // $this->session->set_flashdata('error', lang('registration_is_disabled'));
                // redirect("login");
            }

            $this->form_validation->set_message('is_unique', lang('account_exists'));
            $this->form_validation->set_rules('first_name', lang('first_name'), 'required');
            $this->form_validation->set_rules('last_name', lang('last_name'), 'required');
            $this->form_validation->set_rules('email', lang('email_address'), 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('username', lang('username'), 'required|is_unique[users.username]');
            $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
            $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');
            if ($this->Settings->captcha) {
                $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
            }

            if ($this->form_validation->run() == true) {
                $username = strtolower($this->input->post('username'));
                $email = strtolower($this->input->post('email'));
                $password = $this->input->post('password');

                $additional_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                    'gender' => $this->input->post('gender'),
                    // 'group_id' => $this->input->post('group') ? $this->input->post('group') : '3',
                    'group_id' => $this->input->post('group') ? $this->input->post('group') : 2,
                    'biller_id' => $this->input->post('biller') ? $this->input->post('biller') : 0,
                    'warehouse_id' => $this->input->post('warehouse') ? $this->input->post('warehouse') : 0,
                    'view_right' => $this->input->post('view_right') ? $this->input->post('view_right') : 1,
                    'edit_right' => $this->input->post('edit_right'),
                    'allow_discount' => $this->input->post('allow_discount')
                );
            }
            if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data)) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->db->trans_commit();
                redirect("login");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
                $this->data['groups'] = $this->ion_auth->groups()->result_array();

                $this->load->helper('captcha');
                $vals = array(
                    'img_path' => './assets/captcha/',
                    'img_url' => site_url() . 'assets/captcha/',
                    'img_width' => 150,
                    'img_height' => 34,
                );
                $cap = create_captcha($vals);
                $capdata = array(
                    'captcha_time' => $cap['time'],
                    'ip_address' => $this->input->ip_address(),
                    'word' => $cap['word']
                );

                $query = $this->db->insert_string('captcha', $capdata);
                $this->db->query($query);
                $this->data['image'] = $cap['image'];
                $this->data['captcha'] = array(
                    'name' => 'captcha',
                    'id' => 'captcha',
                    'type' => 'text',
                    'class' => 'form-control',
                    'placeholder' => lang('type_captcha')
                );

                $this->data['first_name'] = array(
                    'name' => 'first_name',
                    'id' => 'first_name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'required' => 'required',
                    'value' => $this->form_validation->set_value('first_name'),
                );
                $this->data['last_name'] = array(
                    'name' => 'last_name',
                    'id' => 'last_name',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('last_name'),
                );
                $this->data['email'] = array(
                    'name' => 'email',
                    'id' => 'email',
                    'type' => 'email',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('email'),
                );
                $this->data['company'] = array(
                    'name' => 'company',
                    'id' => 'company',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('company'),
                );
                $this->data['phone'] = array(
                    'name' => 'phone',
                    'id' => 'phone',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('phone'),
                );
                $this->data['username'] = array(
                    'name' => 'username',
                    'id' => 'username',
                    'type' => 'text',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('username'),
                );
                $this->data['password'] = array(
                    'name' => 'password',
                    'id' => 'password',
                    'type' => 'password',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('password'),
                );
                $this->data['password_confirm'] = array(
                    'name' => 'password_confirm',
                    'id' => 'password_confirm',
                    'type' => 'password',
                    'required' => 'required',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('password_confirm'),
                );
                $this->db->trans_commit();
                $this->load->view($this->theme . 'auth/register', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect('login');
        }
    }

    public function user_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        if ($id != $this->session->userdata('user_id')) {
                            $this->auth_model->delete_user($id);
                        }
                    }
                    $this->session->set_flashdata('message', lang("users_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } else if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('sales'));
                    $sheet->SetCellValue('A1', lang('first_name'))
                        ->SetCellValue('B1', lang('last_name'))
                        ->SetCellValue('C1', lang('email'))
                        ->SetCellValue('D1', lang('company'))
                        ->SetCellValue('E1', lang('group'))
                        ->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $user = $this->site->getUser($id);
                        $sheet->SetCellValue('A' . $row, $user->first_name)
                            ->SetCellValue('B' . $row, $user->last_name)
                            ->SetCellValue('C' . $row, $user->email)
                            ->SetCellValue('D' . $row, $user->company)
                            ->SetCellValue('E' . $row, $user->group)
                            ->SetCellValue('F' . $row, $user->status);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'users_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                        $sheet->getDefaultStyle()->applyFromArray($styleArray);
                        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = IOFactory::createWriter($sheet, 'Pdf');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                        header('Cache-Control: max-age=0');

                        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                        ob_end_clean();
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_user_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete($id = null)
    {
        $this->db->trans_begin();
        try {
            if (DEMO) {
                throw new Exception(lang('disabled_in_demo'));
                // $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                // redirect($_SERVER["HTTP_REFERER"]);
            }
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }

            if (!$this->Owner || $id == $this->session->userdata('user_id')) {
                throw new Exception(lang('access_denied'));
                // $this->session->set_flashdata('warning', lang('access_denied'));
                // redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
            }

            if ($this->auth_model->delete_user($id)) {
                //echo lang("user_deleted");
                $this->session->set_flashdata('message', 'user_deleted');
                $this->db->trans_commit();
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function sign_up($auth = null)
    {
        $this->db->trans_begin();
        try {
            if ($this->loggedIn) {
                redirect('login');
            }
            $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message']  = $this->session->flashdata('message');
            if ($auth) {
                $this->data['groups'] = $this->ion_auth->groups()->result_array();
                $this->data['billers'] = $this->site->getAllCompanies('biller');
                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['title'] = 'Registrasi';
                $auth = array_merge($auth, $this->data);
                $this->load->view($this->theme . "auth/sign_up", $auth);
            } else {
                if (!$auth) {
                    $this->form_validation->set_rules('username', lang("username"), 'trim|is_unique[users.username]');
                    $this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[users.email]');
                } else {
                    $this->form_validation->set_rules('username', lang("username"), 'trim|required');
                    $this->form_validation->set_rules('email', lang("email"), 'trim|required');
                }
                // $this->form_validation->set_rules('group', lang("group"), 'trim|required');
                $this->form_validation->set_rules('password', lang('password'), 'required|matches[confirm_password]');
                $this->form_validation->set_rules('confirm_password', lang('confirm_password'), 'required');
                $data = array(
                    'first_name' => $this->input->post('fname'),
                    'last_name' => $this->input->post('lname'),
                    'email' => $this->input->post('email'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                    'gender' => $this->input->post('gender'),
                    'country' => $this->input->post('provinsi'),
                    'username' => $this->input->post('username') ? $this->input->post('username') : $this->input->post('email'),
                    'city' => $this->input->post('kabupaten'),
                    'password' => $this->input->post('password'),
                    'state' => $this->input->post('kecamatan'),
                    'address' => $this->input->post('address'),
                    'auth_provider' => $this->input->post('provider'),
                    'group_id' => '2',
                    'edit_right' => 1,
                    'allow_discount' => 1,
                    'device_id' => '1',
                    'ip_address' => $this->input->post('ip_address'),
                );
                $companies = array(
                    "latitude" => $this->input->post('latitude'),
                    "longitude" => $this->input->post('longitude'),
                    "postal_code" => $this->input->post('postalcode')
                );
                $this->session->set_userdata('registration', json_decode(json_encode($data), false));
                $this->session->set_userdata('additional_registration', json_decode(json_encode($companies), false));

                if ($this->form_validation->run() == false) {
                    $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                    $this->data['message']  = $this->session->flashdata('message');
                    $this->data['groups'] = $this->ion_auth->groups()->result_array();
                    $this->data['billers'] = $this->site->getAllCompanies('biller');
                    $this->data['warehouses'] = $this->site->getAllWarehouses();
                    $this->data['title'] = 'Registrasi';
                    $this->load->view($this->theme . "auth/sign_up", $this->data);
                } else {

                    $sendData = $this->auth_model->insertUser($data, $companies);
                    if (!$sendData) {
                        throw new Exception('<div class="alert alert-danger text-center">Failed registered. Try Again </div>');
                    }

                    if ($this->input->post('provider') == 'email') {
                        $this->load->library('parser');
                        $parse_data = array(
                            'client_link' => base_url() . 'auth/activate/' . $sendData['last_id'] . '/' . $sendData['code'],
                            'client_name' => $data['first_name'] . ' ' . $data['last_name'],
                            'site_link' => site_url(),
                            'site_name' => $this->Settings->site_name,
                            'email' => $data['email'],
                            'password' => $data['password'],
                            'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                        );

                        $msg = file_get_contents('./themes/' . $this->theme . 'email_templates/credentials.html');
                        $message = $this->parser->parse_string($msg, $parse_data);
                        $subject = $this->lang->line('new_user_created') . ' - ' . $this->Settings->site_name;
                        if ($this->sma->send_email($data['email'], $subject, $message)) {
                            $this->session->unset_userdata('registration');
                            $this->session->unset_userdata('additional_registration');
                            $this->session->set_flashdata('message', '<div class="alert alert-success text-center">Successfully registered. Please confirm the mail that has been sent to your email. </div>');
                        } else {
                            $this->session->set_flashdata('error', '<div class="text-center">Successfully registerred. Failed send email. <a data-toggle="modal" data-target="#resend_email">Send it again?</a></div>');
                        }
                    } else {
                        $this->session->set_flashdata('message', '<div class="alert alert-success text-center">Successfully registered</div>');
                    }

                    $this->db->trans_commit();
                    redirect('login');
                }
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect('login');
        }
    }

    public function confirmEmail($hashcode)
    {
        if ($this->auth_model->verifyEmail($hashcode)) {
            $this->session->set_flashdata('messsage', '<div class="alert alert-success text-center">Email address is confirmed. Please login to the system</div>');
            redirect('login');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger text-center">Email address is not confirmed. Please try to re-register.</div>');
            redirect('login');
        }
    }

    public function availableEmail()
    {
        if (!empty($_POST["email"])) {
            $this->db->from('users');
            $this->db->where('email', $_POST["email"]);

            $result = $this->db->get();

            if ($result->num_rows() > 0) {
                echo "0";
            } else {
                echo "1";
            }
        }
    }

    public function availableCompany()
    {
        if (!empty($_POST["company"])) {
            $this->db->from('users');
            $this->db->where('company', $_POST["company"]);

            $result = $this->db->get();

            if ($result->num_rows() > 0) {
                echo "<span class='status-not-available' style='color:red' > Company Not Available. Please Input Another Name Company</span>";
            } else {
                echo "<span class='status-available' style='color:green'> Company Available.</span>";
            }
        }
    }

    public function availableUsername()
    {
        if (!empty($_POST["username"])) {
            $this->db->from('users');
            $this->db->where('username', $_POST["username"]);

            $result = $this->db->get();

            if ($result->num_rows() > 0) {
                echo "<span class='status-not-available' style='color:red' > Username Not Available. Please Input Another Username</span>";
            } else {
                echo "<span class='status-available' style='color:green'> Username Available.</span>";
            }
        }
    }

    public function add_billing_invoice($id = null)
    {
        $this->db->trans_begin();
        try {
            $id_plan = $id ? $id : $this->input->get('id', true);
            $add_ons = $this->auth_model->getAddons();

            $reference = $this->site->getReference('binv');
            $date = date('Y-m-d H:i:s');
            $plan_detail = $this->site->getPlanPricingByID($id_plan);
            $authorize = $this->sma->getAuthorized();
            $item_billing = [];

            if ($plan_detail && $this->input->post('add_plan')) {
                foreach ($add_ons as $item) {
                    if ($this->input->post('p_' . $item->id) || $this->input->post('p_qty_' . $item->id)) {
                        $qty = $this->input->post('p_qty_' . $item->id) ? $this->input->post('p_qty_' . $item->id) : 1;
                        $subtotal = $item->price * $qty;

                        $item_billing[] = array(
                            'addon_id'      => $item->id,
                            'addon_name'    => $item->name,
                            'price'         => $item->price,
                            'quantity'      => $qty,
                            'subtotal'      => $subtotal,
                        );

                        $total = $total + $subtotal;
                    }
                }

                $total = $total + $plan_detail->price;
                $data = array(
                    'date' => $date,
                    'reference_no' => $reference,
                    'authorized_id' => $authorize->id,
                    'plan_id' => $plan_detail->id,
                    'plan_name' => $plan_detail->name,
                    'price' => $plan_detail->price,
                    'total' => $total,
                    'due_date' => date('Y-m-d H:i:s', strtotime($date) + (60 * _PAYMENT_TERM)),
                    'payment_status' => 'pending',
                    'company_name' => $this->session->userdata('company_name'),
                    'company_id' => $this->session->userdata('company_id'),
                    'created_by' => $this->session->userdata('user_id'),
                );
            } elseif ($this->input->post('add_plan')) {
                echo json_encode(validation_errors());
                return true;
            }

            if (!$this->auth_model->addBillingInv($data, $item_billing)) {
                throw new Exception("Add Billing failed");
            }
            $this->db->trans_commit();
            $this->get_billing_invoice();
            return true;
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
        }
        // return false;
    }

    public function add_proof_payment($id)
    {
        $this->db->trans_begin();
        try {
            $billing_detail = $this->auth_model->getBillingByID($id);
            $date = date('Y-m-d H:i:s');


            $this->form_validation->set_rules('add_proof', lang("proof_of_payments"), 'required');
            //        if($this->form_validation->run()==true){
            if ($_FILES['add_proof']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = 'assets/uploads/proof_payments/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = '500';
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('add_proof')) {
                    $error = $this->upload->display_errors();
                    throw new Exception($error);
                    // $this->session->set_flashdata('error', $error);
                    // redirect("welcome");
                }
                $photo = $this->upload->file_name;*/
                // $this->load->library('image_lib');
                // $config['image_library'] = 'gd2';
                // $config['source_image'] = 'assets/uploads/proof_payments/' . $photo;
                // $config['new_image'] = 'assets/uploads/proof_payments/thumbs/' . $photo;
                // $config['maintain_ratio'] = true;
                // $config['width'] = $this->Settings->twidth;
                // $config['height'] = $this->Settings->theight;
                // $this->image_lib->clear();
                // $this->image_lib->initialize($config);
                // if (!$this->image_lib->resize()) {
                //     // echo $this->image_lib->display_errors();
                //     throw new Exception($this->image_lib->display_errors());
                // }
                // $this->image_lib->clear();
                // $config = null;

                $file   = $this->integration_model->upload_files($_FILES['add_proof']);
                $photo  = $file->url;

                $data = array(
                    'date'              => $date,
                    'billing_invoice_id' => $id,
                    'image'             => $photo,
                    'created_by'        => $this->session->userdata('user_id'),
                    'company_id'        => $this->session->userdata('company_id'),
                );

                if (!$this->auth_model->addProofPayment($data)) {
                    // redirect($_SERVER["HTTP_REFERER"]);
                    throw new Exception("Add proof failed");
                }

                $this->db->trans_commit();
                redirect($_SERVER["HTTP_REFERER"]);
            }
            //        }
            else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['id'] = $id;
                $this->data['payment'] = $billing_detail;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'auth/add_proof_payment', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect('welcome');
        }
    }

    public function get_subscription_record()
    {
        $this->load->library('datatables');

        //        $this->datatables
        //            ->select("{$this->db->dbprefix('billing_invoices')}.id, {$this->db->dbprefix('billing_invoices')}.date, {$this->db->dbprefix('billing_invoices')}.reference_no, payment_status as status, company_name, plan_name, date(bp.updated_at) as start_date, (date(bp.updated_at) + INTERVAL 30 DAY) as expired_date, total")
        //            ->from("billing_invoices")
        //            ->join('billing_payments bp','billing_invoices.id=bp.billing_invoice_id','left');

        $this->datatables
            ->select("{$this->db->dbprefix('billing_invoices')}.id, {$this->db->dbprefix('billing_invoices')}.date, {$this->db->dbprefix('billing_invoices')}.reference_no, payment_status as status, company_name, plan_name, date(p.date) as start_date, (date(p.date) + INTERVAL 30 DAY) as expired_date, total")
            ->from("billing_invoices")
            ->join('payments p', 'billing_invoices.id=p.billing_id', 'left');

        if ($this->Admin) {
            $this->datatables->where('billing_invoices.company_id', $this->session->userdata('company_id'));
        }

        $this->datatables->add_column("Actions", "<div class=\"text-center\"> <a href='" . site_url('auth/add_proof_payment/$1') . "' class='tip' title='" . lang("add_payment") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-money\"></i></a></div>", "{$this->db->dbprefix('billing_invoices')}.id")
            //$this->datatables->add_column("Actions", "<div class=\"text-center\"> <a href='javascript:void(0);' onClick='pay($1)' class='tip' title='" . lang("add_payment") . "' ><i class=\"fa fa-money\"></i></a></div>", "{$this->db->dbprefix('billing_invoices')}.id")
            ->unset_column("{$this->db->dbprefix('billing_invoices')}.id");

        echo $this->datatables->generate();
    }

    public function notification_payment()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('notification')));
        $meta = array('page_title' => lang('payment'), 'bc' => $bc);
        $this->page_construct('auth/notif_payment', $meta, $this->data);
    }

    public function get_notification_payment()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('billing_invoices')}.id, {$this->db->dbprefix('billing_invoices')}.date, {$this->db->dbprefix('billing_invoices')}.reference_no, company_name, total")
            ->from("billing_invoices")
            ->join('billing_payments bp', 'billing_invoices.id=bp.billing_invoice_id', 'left');
        $this->datatables->where('updated_by', null)->where("image != 'no_image.png'");

        $this->datatables->add_column("Actions", "<div class=\"text-center\"> <a href='" . site_url('auth/add_billing_payment/$1') . "' class='tip' title='" . lang("add_billing_payment") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-money\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_payment_account") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('auth/payment_account/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "{$this->db->dbprefix('billing_invoices')}.id");
        echo $this->datatables->generate();
    }

    public function add_billing_payment($id)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('amount', lang("amount"), 'required');
            if ($this->form_validation->run() == true) {
                $data = array(
                    'amount' => $this->input->post('amount'),
                    'reference_no' => $this->site->getReference('bpay'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->session->userdata('user_id'),
                );
            }

            if ($this->form_validation->run() == true && $this->auth_model->addBillingPayment($data, $id)) {
                $this->session->set_flashdata('message', lang("payment_updated"));
                $this->db->trans_commit();
                redirect("auth/notification_payment");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['billing'] = $this->auth_model->getBillingByID($id);
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'auth/add_billing_payment', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view_plans_pricing()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['plans'] = $this->auth_model->getPlanPricing();
        $this->data['addons'] = $this->auth_model->getAddons();
        $this->data['current'] = $this->sma->getAuthorized();
        $this->load->view($this->theme . 'auth/view_plans_pricing', $this->data);
    }

    public function get_billing_invoice()
    {
        $billing = $this->auth_model->getBillingInv();
        $company = $this->site->getAllCompanies('biller');
        $item = $this->auth_model->getBillingInvItem($billing->id);
        $result = array('billing' => $billing, 'company' => $company, 'item' => $item);
        echo json_encode($result);
    }

    public function get_data_billing_by_id($id)
    {
        $billing = $this->auth_model->getBillingByID($id);
        $items = $this->site->getBillingInvItem($id);
        $billing->items = $items;
        $this->sma->send_json($billing);
    }

    public function check_limitation_free()
    {
        if ($this->Owner) {
            $this->sma->send_json(null);
            return;
        }

        $this->cek_expired_billing();
        $this->email_to_expired();
        $authorized = $this->sma->getAuthorized();
        $order_ref = $this->site->getOrderRef();
        $plan = $this->site->getPlanPricingByID($authorized->plan_id);
        $products = $this->products_model->getAllProducts();
        $suppliers = $this->site->getAllCompanies('supplier');
        $suppliers = !empty($suppliers) ? $suppliers : [];
        $customers = $this->site->getAllCompanies('customer');
        $customers = !empty($customers) ? $customers : [];
        $categories = $this->site->getAllCategories();
        $categories = !empty($categories) ? $categories : [];
        $brands = $this->site->getAllBrands();
        $brands = !empty($brands) ? $brands : [];
        $units = $this->site->getAllUnits();
        $units = !empty($units) ? $units : [];
        $expenses = $this->site->getAllExpenseCategories();
        $expenses = !empty($expenses) ? $expenses : [];
        $shipping = $this->site->getAllShippingCharges();
        $shipping = !empty($shipping) ? $shipping : [];

        $count = 0;
        $count += $this->getCount($products);
        $count += $this->getCount($categories);
        $count += $this->getCount($brands);
        
        $total_master = $count + count($suppliers) + count($customers) + count($units) + count($expenses) + count($shipping);
        $arr_order_ref = json_decode(json_encode($order_ref), true);
        unset($arr_order_ref["ref_id"]);
        unset($arr_order_ref["date"]);
        unset($arr_order_ref["client_id"]);
        unset($arr_order_ref["flag"]);
        unset($arr_order_ref["is_deleted"]);
        unset($arr_order_ref["device_id"]);
        unset($arr_order_ref["uuid"]);
        unset($arr_order_ref["uuid_app"]);
        unset($arr_order_ref["company_id"]);
        $total_trx = array_sum($arr_order_ref);

        $result = array('order_ref' => $order_ref, 'authorized' => $authorized, 'plan' => $plan, 'total_trx' => $total_trx, 'total' => $total_master,);

        $this->sma->send_json($result);
    }

    private function getCount($param)
    {
        $bulk = 0;
        if ($param) {
            foreach ($param as $item) {
                if (@$item->client_id == $this->session->userdata('company_id') || @$item->company_id == $this->session->userdata('company_id')) {
                    $bulk++;
                }
            }
        }
        return $bulk;
    }

    public function resend_email()
    {
        $this->form_validation->set_rules('resend_email', lang('email_address'), 'required|valid_email');

        if ($this->form_validation->run() == false) {
            $error = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->session->set_flashdata('error', $error);
            redirect("login#resend_email");
        } else {
            $identity = $this->ion_auth
                ->where('email', strtolower($this->input->post('resend_email')))
                ->where('active', 0)->users()->row();

            if (empty($identity)) {
                $this->ion_auth->set_message('account_active');
                $this->session->set_flashdata('error', $this->ion_auth->messages());
                redirect("login#resend_email");
            }

            $this->load->library('parser');
            $parse_data = array(
                'client_link' => base_url() . 'auth/activate/' . $identity->id . '/' . $identity->activation_code,
                'client_name' => $identity->first_name . ' ' . $identity->last_name,
                'site_link' => site_url(),
                'site_name' => $this->Settings->site_name,
                'email' => $identity->email,
                'password' => '',
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
            );

            $msg = file_get_contents('./themes/' . $this->theme . 'email_templates/credentials.html');
            $message = $this->parser->parse_string($msg, $parse_data);
            $subject = $this->lang->line('new_user_created') . ' - ' . $this->Settings->site_name;

            if ($this->sma->send_email($identity->email, $subject, $message)) {
                $this->session->set_flashdata('message', lang('success_send_email'));
                redirect("login");
            } else {
                $this->session->set_flashdata('error', lang('failed_send_email'));
                redirect("login#resend_email");
            }
        }
    }

    public function return_to_free()
    {
        $this->db->trans_begin();
        try {
            $data = array(
                'users' => 2,
                'warehouses' => 1,
                'plan_id' => 1,
                'plan_name' => 'Free',
                'status' => null,
                'start_date' => null,
                'expired_date' => null,
            );
            if (!$this->db->update('authorized', $data, array('company_id' => $this->session->userdata('company_id')))) {
                throw new Exception("Failed, return to free");
            }
            $return = true;
            $this->db->trans_commit();
        } catch (\Throwable $th) {
            $return = false;
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            // redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->sma->send_json(array('return' => $return));
    }

    public function check_cf1_distributor($cf1)
    {
        $cmp = $this->auth_model->checkCF1Distributor($cf1, $this->session->userdata('company_id'));
        if ($cmp) {
            echo 0;
        } else {
            echo 1;
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
                throw new Exception("Data is not valid");
            }
            if (!in_array($data->issued_for, ['sending_aksestoko_session', 'sending_special_session'])) {
                throw new Exception("This session is not issued for this.");
            }
            $user = $this->site->getUser($data->user_id);
            if (!$user) {
                throw new Exception("User not found");
            }
            if ($user->view_right != 1) {
                $user->view_right = '1';
                $this->db->update('users', array('view_right' => '1'), array('id' => $user->id));
            }
            $this->auth_model->set_session($user);
            $this->db->trans_commit();
            redirect('welcome');
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect('login');
        }
    }

    public function cek_expired_billing()
    {
        $active = $this->auth_model->cekactiveBilling();
        $active = !empty($active) ? $active : [];
        foreach ($active as $k => $v) {
            $start_new = strtotime(date('Y-m-d', strtotime('+1 day', strtotime($v->end_date))));
            $str_now = strtotime(date('Y-m-d H:i:s'));

            if ($str_now > $start_new) {
                $data_bill = ['billing_status' => 'expired'];
                $where_bill = ['id' => $v->id];
                $update = $this->auth_model->updateBilling($data_bill, $where_bill);
            }
        }
    }

    public function email_to_expired()
    {
        $active = $this->auth_model->getAllAuthor();
        $active = !empty($active) ? $active : [];
        $send_email = [] ;
        $parse_data = [];

        foreach ($active as $k => $v) {
            $email = $v->email;
            $company_id = $v->company_id;
            $company_name = $v->company;
            $min_15 = date('Y-m-d', strtotime ('-15 day', strtotime($v->expired_date)));
            $min_7 = date('Y-m-d', strtotime ('-7 day', strtotime($v->expired_date)));
            $min_3 = date('Y-m-d', strtotime ('-3 day', strtotime($v->expired_date)));
            $min_2 = date('Y-m-d', strtotime ('-2 day', strtotime($v->expired_date)));
            $min_1 = date('Y-m-d', strtotime ('-1 day', strtotime($v->expired_date)));
            $notif = $v->email_notif;
            $now = date('Y-m-d');

            if($now == $min_15 && $notif != $now){
                $data = ['company_id' => $company_id, 'email_notif' => date('Y-m-d'), 'plan_id' => 2];
                $update = $this->subscription_model->updateAuthor($data);
                $parse_data[] = [
                    'email' => $email,
                    'client_link' => base_url() . 'billing_portal/subscription',
                    'site_name' => $this->Settings->site_name,
                    'company_name' => $company_name,
                    'plan_name' => $v->plan_name,
                    'start_date' => $this->sma->hrsd($v->start_date),
                    'expired_date' => $this->sma->hrsd($v->expired_date),
                    'expire_days' => '15 days',
                    'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                ];
            }
            elseif($now == $min_7 && $notif != $now){
                $data = ['company_id' => $company_id, 'email_notif' => date('Y-m-d'), 'plan_id' => 2];
                $update = $this->subscription_model->updateAuthor($data);
                $parse_data[] = [
                    'email' => $email,
                    'client_link' => base_url() . 'billing_portal/subscription',
                    'site_name' => $this->Settings->site_name,
                    'company_name' => $company_name,
                    'plan_name' => $v->plan_name,
                    'start_date' => $this->sma->hrsd($v->start_date),
                    'expired_date' => $this->sma->hrsd($v->expired_date),
                    'expire_days' => '7 days',
                    'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                ];
            }
            elseif($now == $min_3 && $notif != $now){
                $data = ['company_id' => $company_id, 'email_notif' => date('Y-m-d'), 'plan_id' => 2];
                $update = $this->subscription_model->updateAuthor($data);
                $parse_data[] = [
                    'email' => $email,
                    'client_link' => base_url() . 'billing_portal/subscription',
                    'site_name' => $this->Settings->site_name,
                    'company_name' => $company_name,
                    'plan_name' => $v->plan_name,
                    'start_date' => $this->sma->hrsd($v->start_date),
                    'expired_date' => $this->sma->hrsd($v->expired_date),
                    'expire_days' => '3 days',
                    'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                ];
            }
            elseif($now == $min_2 && $notif != $now){
                $data = ['company_id' => $company_id, 'email_notif' => date('Y-m-d'), 'plan_id' => 2];
                $update = $this->subscription_model->updateAuthor($data);
                $parse_data[] = [
                    'email' => $email,
                    'client_link' => base_url() . 'billing_portal/subscription',
                    'site_name' => $this->Settings->site_name,
                    'company_name' => $company_name,
                    'plan_name' => $v->plan_name,
                    'start_date' => $this->sma->hrsd($v->start_date),
                    'expired_date' => $this->sma->hrsd($v->expired_date),
                    'expire_days' => '2 days',
                    'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                ];
            }
            elseif($now == $min_1 && $notif != $now){
                $data = ['company_id' => $company_id, 'email_notif' => date('Y-m-d'), 'plan_id' => 2];
                $update = $this->subscription_model->updateAuthor($data);
                $parse_data[] = [
                    'email' => $email,
                    'client_link' => base_url() . 'billing_portal/subscription',
                    'site_name' => $this->Settings->site_name,
                    'company_name' => $company_name,
                    'plan_name' => $v->plan_name,
                    'start_date' => $this->sma->hrsd($v->start_date),
                    'expired_date' => $this->sma->hrsd($v->expired_date),
                    'expire_days' => '1 days',
                    'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                ];
            }
        }

        if(count($parse_data) > 0){
            $this->send_email_subscription_expire($parse_data);
        }
    }

    public function send_email_subscription_expire($parse_data)
    {
        $this->load->library('parser');
        $msg = file_get_contents('./themes/' . $this->theme . 'email_templates/subscription_expired.html');
        foreach ($parse_data as $k => $v) {
            $message = $this->parser->parse_string($msg, $parse_data[$k]);
            $subject = 'Subscription - ' . $this->Settings->site_name;
            $send = $this->sma->send_email($v['email'], $subject, $message);
        }
        return true;
    }
    
    public function list_all_users()
    {
        if (!$this->loggedIn) {
            redirect('login');
        }

        // $link_type = ['mb_list_all_users'];
        // $this->load->model('db_model');
        // $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        // foreach ($get_link as $val) {
        //     $this->data[$val->type] = $val->uri;
        // }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('list_all_users')));
        $meta = array('page_title' => lang('list_all_users'), 'bc' => $bc);
        $this->page_construct('auth/list_all_users', $meta, $this->data);
    }

    public function getListAllUsers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users') . ".id as id, " .
                $this->db->dbprefix('users') . ".username," .
                $this->db->dbprefix('users') . ".email, " .
                $this->db->dbprefix('users') . ".first_name, " .
                $this->db->dbprefix('users') . ".last_name, " .
                $this->db->dbprefix('users') . ".company, " .
                $this->db->dbprefix('groups') . ".name, 
                (CASE WHEN " . $this->db->dbprefix('companies') . ".client_id = 'aksestoko' 
                THEN 1 
		        ELSE 0 END ) AS type," .
                $this->db->dbprefix('users') . ".active")
            ->from("users")
            ->join('companies', 'users.company_id = companies.id', 'left')
            ->join('groups', 'users.group_id = groups.id', 'left')
            ->group_by('users.id')
            ->where($this->db->dbprefix('users') . '.id !=', $this->session->userdata('user_id'))
            ->edit_column($this->db->dbprefix('users') . '.active', '$1__$2', $this->db->dbprefix('users') . '.active, id')
            ->edit_column('type', '$1__$2', 'type, ' . $this->db->dbprefix('users') . 'id')
            ->add_column("Actions", "
                <div class=\"text-center\">
                <a href='" . site_url('auth/view_list_all_users/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("detail_users") . "'><i class=\"fa fa-eye\"></i></a>
                <a href='" . site_url('auth/edit_list_all_users/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_user") . "'><i class=\"fa fa-edit\"></i></a>
                <a href='" . site_url('auth/reset_password_list_all_users/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("change_password") . "'><i class=\"fa fa-key\"></i></a>
                <a href='" . site_url('auth/login_as_user/$1') . "' class='tip' title='" . lang("login_as_user") . "'><i class=\"fa fa-sign-in\"></i></a>
                </div>", "id");
        echo $this->datatables->generate();
    }

    public function login_as_user($user_id)
    {
        if(!($this->loggedIn && $this->Owner)) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"] ?? 'login');
        }
        $this->load->model('encryption_model');
        $data = [
            'issued_for' => 'sending_special_session',
            'user_id'    => $user_id
        ];
        $json = json_encode($data);
        $encrypt = $this->encryption_model->encrypt($json, APP_TOKEN);

        if (!$encrypt) {
            $this->session->set_flashdata('error', "Tidak bisa melakukan enkripsi.");
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $user = $this->auth_model->getUsersById($user_id);
        $company    = $this->companies_model->getCompanyByID($user->company_id);
        
        if($company->client_id == 'aksestoko') {
            redirect(prep_url(AKSESTOKO_DOMAIN) . "/" . aksestoko_route("aksestoko/auth/get_session"). "?session=" . urlencode($encrypt));
        } else {
            $this->ion_auth->logout();
            redirect("auth/get_session?session=" . urlencode($encrypt));
        }
    }

    public function view_list_all_users($id = null)
    {
        $this->data['error']          = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users']          = $this->auth_model->getUsersById($id);
        $this->data['sales_person']   = $this->sales_person_model->getSalesPersonById($this->data['users']->sales_person_id);
        $this->load->view($this->theme . 'auth/view_list_all_users', $this->data);
    }

    public function edit_list_all_users($id)
    {
        $user       = $this->ion_auth->user($id)->row();
        $groups     = $this->ion_auth->groups()->result_array();
        $company    = $this->companies_model->getCompanyByID($user->company_id);

        $this->db->trans_begin();
        try {

            $this->form_validation->set_rules('username', lang("username"), 'trim|required');
            $this->form_validation->set_rules('email', lang("email"), 'trim|required');
            $this->form_validation->set_rules('first_name', lang("first_name"), 'trim|required');
            $this->form_validation->set_rules('last_name', lang("last_name"), 'trim|required');
            $this->form_validation->set_rules('company', lang("company"), 'trim|required');
            $this->form_validation->set_rules('phone', lang("phone"), 'trim|required');
            $this->form_validation->set_rules('gender', lang("gender"), 'trim|required');
            $this->form_validation->set_rules('address', lang("address"), 'trim|required');
            $this->form_validation->set_rules('award_points', lang("award_points"), 'trim|required');
            $this->form_validation->set_rules('country', lang("country"), 'trim|required');
            $this->form_validation->set_rules('city', lang("city"), 'trim|required');
            $this->form_validation->set_rules('state', lang("state"), 'trim|required');
            $this->form_validation->set_rules('group', lang("group"), 'trim|required');
            $this->form_validation->set_rules('avatar', lang("avatar"), 'trim');

            if ($this->form_validation->run() == true) {
                if ($user->username != $this->input->post('username')) {
                    $findusername = $this->ion_auth->findUsernameUsers($this->input->post('username'));
                    if ($findusername) {
                        throw new Exception(lang('duplicate_username'));
                    }
                }
                if ($user->email != $this->input->post('email')) {
                    $findemail = $this->ion_auth->findEmailUsers($this->input->post('email'));
                    if ($findemail) {
                        throw new Exception(lang('duplicate_email'));
                    }
                }
                $sp   = $this->sales_person_model->getSalesPersonById($this->input->post('sales_person'));
                $data = [
                    'first_name'       => $this->input->post('first_name') ? $this->input->post('first_name') : $user->first_name,
                    'last_name'        => $this->input->post('last_name') ? $this->input->post('last_name') : $user->last_name,
                    'company'          => $this->input->post('company') ? $this->input->post('company') : $user->company,
                    'username'         => $this->input->post('username') ? $this->input->post('username') : $user->username,
                    'email'            => $this->input->post('email') ? $this->input->post('email') : $user->email,
                    'phone'            => $this->input->post('phone') ? $this->input->post('phone') : $user->phone,
                    'gender'           => $this->input->post('gender') ? $this->input->post('gender') : $user->gender,
                    'active'           => $this->input->post('is_active') != 'on' ? 0 : 1,
                    'group_id'         => $this->input->post('group') ? $this->input->post('group') : $user->group,
                    'biller_id'        => $this->input->post('biller') ? $this->input->post('biller') : $user->biller,
                    'warehouse_id'     => $this->input->post('warehouse') ? $this->input->post('warehouse') : $user->warehouse,
                    'award_points'     => $this->input->post('award_points') ? $this->input->post('award_points') : $user->award_points,
                    'view_right'       => $this->input->post('view_right') ? $this->input->post('view_right') : $user->view_right,
                    'edit_right'       => $this->input->post('edit_right') ? $this->input->post('edit_right') : $user->edit_right,
                    'allow_discount'   => $this->input->post('allow_discount') ? $this->input->post('allow_discount') : $user->allow_discount,
                    'country'          => $this->input->post('country') ? $this->input->post('country') : $user->country,
                    'city'             => $this->input->post('city') ? $this->input->post('city') : $user->city,
                    'state'            => $this->input->post('state') ? $this->input->post('state') : $user->state,
                    'address'          => $this->input->post('address') ? $this->input->post('address') : $user->address,
                    'sales_person_id'  => $sp->id ? $sp->id : $user->sales_person_id,
                    'sales_person_ref' => $sp->reference_no ? $sp->reference_no : $user->reference_no,
                ];
                if (!$this->ion_auth->update($user->id, $data)) {
                    throw new Exception('Update error in data users');
                }
                $companies = [
                    "postal_code"   => $this->input->post('postal_code') ? $this->input->post('postal_code') : $company->postal_code,
                    "vat_no"        => $this->input->post('vat_no') ? $this->input->post('vat_no') : $company->vat_no,
                    "cf2"           => $this->input->post('cf2') ? $this->input->post('cf2') : $company->cf2,
                    "cf3"           => $this->input->post('cf3') ? $this->input->post('cf3') : $company->cf3,
                    "cf4"           => $this->input->post('cf4') ? $this->input->post('cf4') : $company->cf4,
                    "cf5"           => $this->input->post('cf5') ? $this->input->post('cf5') : $company->cf5
                ];

                if ($this->input->post('cf1') != $company->cf1) {
                    if ($this->input->post('cf1') != '') {
                        $cmp = $this->auth_model->checkCF1Distributor($this->input->post('cf1'), $$user->company_id);
                        if ($cmp) {
                            throw new Exception(lang('duplicate_cf1'));
                        }
                    }
                }
                $companies['cf1']   = $this->input->post('cf1') ? $this->input->post('cf1') : $company->cf1;

                if (!$this->companies_model->updateCompany($user->company_id, $companies)) {
                    throw new Exception('Update error in data company');
                }

                if ($_FILES['avatar']['size'] > 0) {
                    $file = $this->integration_model->upload_files($_FILES['avatar']);
                    $photo = $file->url;
                    $user = $this->ion_auth->user($id)->row();
                }

                if ($this->auth_model->updateAvatar($id, $photo)) {
                    unlink('assets/uploads/avatars/' . $user->avatar);
                    unlink('assets/uploads/avatars/thumbs/' . $user->avatar);
                    $this->session->set_userdata('avatar', $photo);
                    $this->session->set_flashdata('message', lang("avatar_updated"));
                    $this->load->model('Curl_model', 'curl_');
                    $this->curl_->updateEcomerce($user->biller_id);
                }

                $this->session->set_flashdata('message', lang('user_updated'));
                $this->db->trans_commit();
                redirect($_SERVER["HTTP_REFERER"]);
            } elseif ($this->input->post('edit_users')) {
                throw new Exception(validation_errors());
            } else {
                $salespersons = [];
                if($company->client_id == 'aksestoko'){
                    $this->load->model('aksestoko/home_model', 'at_home');
                    $distributors = $this->at_home->getAllCompany($company->cf1, $company->company_id);
                    foreach ($distributors as $d) {
                        $salespersons = array_merge($salespersons, $this->companies_model->getAllSalesPerson($d->company_id));
                    }
                }
                $this->data['csrf']             = $this->_get_csrf_nonce();
                $this->data['user']             = $user;
                $this->data['company']          = $company;
                $this->data['groups']           = $groups;
                $this->data['billers']          = [$this->site->getCompanyByID($company->id)];
                $this->data['warehouses']       = $this->site->getAllWarehouses(null, ['company_id' => $company->id]);
                $this->data['sales_persons']    = $salespersons;
                $this->data['salesperson']      = $this->sales_person_model->getSalesPersonById($user->sales_person_id);
                $this->data['modal_js']         = $this->site->modal_js();
                $this->data['error']            = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->load->view($this->theme . 'auth/edit_list_all_users', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function reset_password_list_all_users($id)
    {
        $user       = $this->ion_auth->user($id)->row();

        $this->db->trans_begin();
        try {

            $this->form_validation->set_rules('new_password', lang('edit_user_validation_password_label'), 'required|min_length[8]|max_length[25]|matches[confirm_password]');
            $this->form_validation->set_rules('confirm_password', lang('edit_user_validation_password_confirm_label'), 'required');

            if ($this->form_validation->run() == true) {

                $data['password'] = $this->input->post('new_password');

                if (!$this->ion_auth->update($user->id, $data)) {
                    throw new Exception(lang('error_reset'));
                }

                $this->session->set_flashdata('message', lang('succses_reset'));
                $this->db->trans_commit();
                redirect($_SERVER["HTTP_REFERER"]);
            } elseif ($this->input->post('confirm_password')) {
                throw new Exception(validation_errors());
            } else {
                $this->data['user']           = $user;
                $this->data['error']          = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->load->view($this->theme . 'auth/reset_password_users', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function getUsername($usernamereplace)
    {
        $username = str_replace('_', '@', $usernamereplace);
        $data = $this->ion_auth->findUsernameUsers($username);
        echo json_encode($data);
    }

    public function getEmail($emailreplace)
    {
        $email = str_replace('_', '@', $emailreplace);
        $data = $this->ion_auth->findEmailUsers($email);
        echo json_encode($data);
    }
}

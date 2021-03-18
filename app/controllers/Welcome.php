<?php defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('db_model');
        $this->load->model('subscription_model');
        $this->load->model('integration_model');
        $this->lang->load('feedback', $this->Settings->user_language);
        $this->lang->load('auth', $this->Settings->user_language);
        $this->insertLogActivities();
    }

    public function index()
    {
        if ($this->Settings->version == '2.3') {
            $this->session->set_flashdata('warning', 'Please complete your update by synchronizing your database.');
            redirect('sync');
        }

        $this->data['error']              = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['sales']              = $this->db_model->getLatestSales();
        $this->data['quotes']             = $this->db_model->getLastestQuotes();
        $this->data['purchases']          = $this->db_model->getLatestPurchases();
        $this->data['deliveries_smig']    = $this->db_model->getLastestDeliveriesSmig();
        $this->data['transfers']          = $this->db_model->getLatestTransfers();
        $this->data['customers']          = $this->db_model->getLatestCustomers();
        $this->data['suppliers']          = $this->db_model->getLatestSuppliers();
        $this->data['stock']              = $this->db_model->getStockValue();
        $this->data['product']            = $this->db_model->getDataProduct();
        $this->data['brand']              = $this->db_model->getDataBrand();

        $this->data['bs']   = $this->db_model->getBestSeller(null, null, $this->session->userdata('company_id'));
        $this->load->model('promo_model');
        $lmsdate            = date('Y-m-d', strtotime('first day of last month')) . ' 00:00:00';
        $lmedate            = date('Y-m-d', strtotime('last day of last month')) . ' 23:59:59';
        $this->data['lmbs'] = $this->db_model->getBestSeller($lmsdate, $lmedate, $this->session->userdata('company_id'));
        $bc                 = array(array('link' => '#', 'page' => lang('dashboard')));
        $meta               = array('page_title' => lang('dashboard'), 'bc' => $bc);
        if ($this->AdminBilling) {
            $user_free        = $this->subscription_model->user_free();
            $user_basic       = $this->subscription_model->user_basic();
            $payment_basic    = $this->subscription_model->payment_basic();
            $payment_addon    = $this->subscription_model->payment_addon();
            $user_basic_all   = $this->subscription_model->user_basic_result();
            $new_payment      = $this->subscription_model->new_payment();

            $percent_free = floor(100 / ($user_free->id + $user_basic->id) * $user_free->id);
            $percent_basic = ceil(100 / ($user_free->id + $user_basic->id) * $user_basic->id);

            $this->data['user_free']        = ($user_free) ? $user_free : false;
            $this->data['user_basic']       = ($user_basic) ? $user_basic : false;
            $this->data['payment_basic']    = ($payment_basic) ? $payment_basic : false;
            $this->data['payment_addon']    = ($payment_addon) ? $payment_addon : false;
            $this->data['percent_free']     = ($percent_free) ? $percent_free : false;
            $this->data['percent_basic']    = ($percent_basic) ? $percent_basic : false;
            $this->data['user_basic_all']   = $user_basic_all;
            $this->data['new_payment']      = $new_payment;

            $this->page_construct_ab('dashboard', $meta, $this->data);
        } else {
            $this->page_construct('dashboard', $meta, $this->data);
        }
    }

    public function getDashboardByDate()
    {
        $start = $this->input->get('start');
        $end = $this->input->get('end');

        $data_by_date = $this->db_model->getDataByDate($start, $end);

        $this->sma->send_json($data_by_date);
    }

    public function getDashboardByDist()
    {
        $dist = $this->input->get('distrib') ?? null;

        if ($dist) {
            $dist = explode(',', $dist);
        }

        $data_by_dist = $this->data['data_by_dist'] = $this->db_model->getDataDistributor($dist);

        $this->sma->send_json($data_by_dist);
    }

    public function getDashboardByProvince()
    {
        $prov = $this->input->get('provinsi') ?? null;

        if ($prov) {
            $prov = explode(',', $prov);
        }

        $data_by_prov = $this->data['data_by_prov'] = $this->db_model->getDataProvinsi($prov);

        $this->sma->send_json($data_by_prov);
    }

    public function getDashboardByMap()
    {
        $prod = $this->input->get('prod') ?? null;


        $data_by_prov = $this->data['data_map'] = $this->db_model->getDataMap($prod);

        $this->sma->send_json($data_by_prov);
    }

    public function getDashboardChartData()
    {
        $prod           = $this->input->get('prod') ?? null;
        $brand          = $this->input->get('brand') ?? null;
        $Notprincipal   = $this->input->get('tipe') ?? null;

        if (($prod != null or $brand != null) or $Notprincipal == null) {
            $data_chart = $this->data['chatData'] = $this->db_model->getChartData($prod, $brand);
        } else {
            $data_chart = $this->data['chatData'] = $this->db_model->getChartData2($prod);
        }

        $this->sma->send_json($data_chart);
    }

    public function promotions()
    {
        $this->load->view($this->theme . 'promotions', $this->data);
    }
    public function ecomerce()
    {
        $this->data['ecomerce'] = $this->site->getCompanyByID($this->session->userdata('biller_id'));
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'ecomerce', $this->data);
    }

    public function addEcomerce()
    {
        $this->load->library('ion_auth');
        if (!$this->ion_auth->logged_in()) {
            redirect('login');
        }
        $this->form_validation->set_rules('password', lang('password'), 'required');

        $user = $this->ion_auth->user()->row();
        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('welcome/ecomerce');
        } else {
            $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));
            $change = $this->ion_auth->validation_password($identity, $this->input->post('password'));
            if ($change) {
                $json = array("message" => "Anda Sudah Terdaftar di Ecomerce");
            } else {
                $json = array("message" => $this->ion_auth->errors());
            }
            echo json_encode($json);
        }
    }
    public function image_upload()
    {
        if (DEMO) {
            $error = array('error' => $this->lang->line('disabled_in_demo'));
            $this->sma->send_json($error);
            exit;
        }
        $this->security->csrf_verify();
        if (isset($_FILES['file'])) {
            /*$this->load->library('upload');
            $config['upload_path'] = 'assets/uploads/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = '500';
            $config['max_width'] = $this->Settings->iwidth;
            $config['max_height'] = $this->Settings->iheight;
            $config['encrypt_name'] = true;
            $config['overwrite'] = false;
            $config['max_filename'] = 25;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $error = $this->upload->display_errors();
                $error = array('error' => $error);
                $this->sma->send_json($error);
                exit;
            }
            $photo = $this->upload->file_name;
            $array = array(
                'filelink' => base_url() . 'assets/uploads/images/' . $photo
            );*/
            $uploadedImg = $this->integration_model->upload_files($_FILES['file']);
            $array = array(
                'filelink' => $uploadedImg->url
            );
            echo stripslashes(json_encode($array));
            exit;
        } else {
            $error = array('error' => 'No file selected to upload!');
            $this->sma->send_json($error);
            exit;
        }
    }

    public function set_data($ud, $value)
    {
        $this->session->set_userdata($ud, $value);
        echo true;
    }

    public function hideNotification($id = null)
    {
        $this->session->set_userdata('hidden' . $id, 1);
        echo true;
    }

    public function language($lang = false)
    {
        if ($this->input->get('lang')) {
            $lang = $this->input->get('lang');
        }
        //$this->load->helper('cookie');
        $folder = 'app/language/';
        $languagefiles = scandir($folder);
        if (in_array($lang, $languagefiles)) {
            $cookie = array(
                'name' => 'language',
                'value' => $lang,
                'expire' => '31536000',
                'prefix' => 'sma_',
                'secure' => false
            );
            $this->input->set_cookie($cookie);
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function toggle_rtl()
    {
        $cookie = array(
            'name' => 'rtl_support',
            'value' => $this->Settings->user_rtl == 1 ? 0 : 1,
            'expire' => '31536000',
            'prefix' => 'sma_',
            'secure' => false
        );
        $this->input->set_cookie($cookie);
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function download($file)
    {
        if (file_exists('./files/' . $file)) {
            $this->load->helper('download');
            force_download('./files/' . $file, null);
            exit();
        }
        $this->session->set_flashdata('error', lang('file_x_exist'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function exp_account()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'errors/alert_billing', $this->data);
    }

    public function experience_guide()
    {
        $guide = $this->site->getGuide();
        $this->sma->send_json($guide);
    }

    public function update_guide()
    {
        $field = $this->input->post('column', true);
        if ($this->site->finishGuide($field)) {
            $this->sma->send_json(true);
        }
        $this->sma->send_json(false);
    }

    public function reset_guide()
    {
        if ($this->site->resetguide()) {
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->session->set_flashdata('message', lang("reset guide not working"));
            redirect("welcome");
        }
    }


    public function update_notif()
    {
        try {
            $data['show_updates'] = $this->db_model->getUpdateNotif();
            $this->load->view($this->theme . 'modal_update_notif', $data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();

            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function log_update()
    {
        try {
            $data['show_updates'] = $this->db_model->getChangeLog();
            $this->load->view($this->theme . 'modal_update_notif', $data);
        } catch (\Throwable $th) {
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function user_last_update($version)
    {
        $this->db->trans_begin();
        try {
            $data = ['last_update' => $version];
            $this->db->where('id', $this->session->userdata('user_id'));
            if (!$this->db->update('users', $data)) {
                throw new \Exception('Failed');
            }
            $session_data = array('last_update' => $version);
            $this->session->set_userdata($session_data);
            $this->db->trans_commit();
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function info_feedback()
    {
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'feedback/modal_feedback', $this->data);
    }
    
    public function feedback(){
        if(!$this->db_model->checkCustomerResponse()){
            if ($this->isPost()) {
                $this->db->trans_begin();
                try {
                    $active_survey = $this->db_model->getActiveSurvey();
                    $company_data = $this->site->getCompanyByID($this->session->userdata('company_id'));
                    $data=[
                        'category_id'   => $active_survey->id,
                        'repeat'        => $active_survey->repeat,
                        'user_id'       => $this->session->userdata('user_id'),
                        'f_company_id'  => $this->session->userdata('company_id'),
                        'company'       => $company_data->company,
                        'user_code'     => $company_data->cf1,
                        'created_at'    => date('Y-m-d H:i:s')
                    ];
                    $id = $this->db_model->addFeedback($data);
                    if (!$id) {
                        throw new \Exception('Failed');
                    }
                    $this->db->trans_commit();
    
                    $data = []; $num = 0;
                    $response_id = $this->db_model->getLastResponseID();
                    for($i=1;$i<=$this->input->post('num');$i++) {
                        $type = $this->input->post('question_type_'.$i);
                        if($type == 'checkbox'){
                            $list_answer = $this->input->post('answer_'.$i.'[]');
                            for($j=0; $j< count($list_answer); $j++){
                                $data[$num]= [
                                    'survey_id'     => $response_id->id,
                                    'question_id'   => $this->input->post('question_'.$i),
                                    'answer'        => $list_answer[$j],
                                    'created_at'    => date('Y-m-d H:i:s')
                                ];
                                $num++;
                            }
                        }else{
                            $data[$num]= [
                                'survey_id'     => $response_id->id,
                                'question_id'   => $this->input->post('question_'.$i),
                                'answer'        => $this->input->post('answer_'.$i),
                                'created_at'    => date('Y-m-d H:i:s')
                            ];
                        }
                        $num++;
                    }
                    $id = $this->db_model->addFeedbackResponse($data);
                    if (!$id) {
                        throw new \Exception('Failed');
                    }
                    $this->db->trans_commit();
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Feedback')));
                    $meta = array('page_title' => lang('Feedback'), 'bc' => $bc);
                    $this->page_construct_feedback('feedback_thanks', $meta, $this->data);
                } catch (\Throwable $th) {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('error', $th->getMessage());
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $survey_active = $this->db_model->getActiveSurvey();
                $this->data['question'] = $this->db_model->getQuestion($survey_active->id);
                foreach($this->data['question'] as $row) {
                    $row->option_list = $this->db_model->getFeedbackOption($row->id);
                }
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Feedback')));
                $meta = array('page_title' => lang('Feedback'), 'bc' => $bc);
                $this->page_construct_feedback('index', $meta, $this->data);
            }
        } else {
            $this->session->set_flashdata('error', lang('already_taken'));
            redirect('welcome');
        }
    }
}

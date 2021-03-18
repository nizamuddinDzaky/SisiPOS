<?php defined('BASEPATH') or exit('No direct script access allowed');

class Plugin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->insertLogActivities();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->lang->load('customers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->lang->load('sma', $this->Settings->user_language);
        $this->lang->load('pos', $this->Settings->user_language);
        $this->lang->load('auth', $this->Settings->user_language);
        $this->load->model('subscription_model');
        $this->load->model('companies_model');
        $this->load->model('settings_model');
    }

    public function index()
    {
        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['title'] = 'List Add-on';
        $meta = array('page_title' => 'Add-on');

        if ($this->AdminBilling || $this->Owner) {
            $this->page_construct_ab('list_plugin', $meta, $this->data);
        }else{
            $this->page_construct_ab('list_plugin_user', $meta, $this->data);
        }
    }

    public function user()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['title'] = 'List User Plugin';
        $meta = array('page_title' => 'User Plugin');

        $this->page_construct_ab('list_user', $meta, $this->data);
    }

    public function getPlugin()
    {
        $edit_link = "<a class=\"tip\" title='".lang("edit_addon")."' href='".site_url('billing_portal/plugin/edit/$1')."'><i class=\"fa fa-edit\"></i> ". lang("edit_plugin")."</a>";
        $delete_link = "<a onclick='delete_button(".'$1'.")' id='delete_id_".'$1'."'  data-action='".site_url('billing_portal/plugin/delete/$1')."'  title='".lang("delete_plugin")."'><i class='fa fa-trash-o'></i> ".lang("delete_plugin")."</a></div>";

        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-info notika-btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $edit_link . '</li>
            </ul>
            </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("id, name, price, is_active")
            ->from('addons');

        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }

    public function add()
    {
        // $this->sma->checkPermissions(false, true);
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('price', lang("price"), 'required');
            if ($this->form_validation->run() == false) {
                if(validation_errors()){
                    $res['message'] = validation_errors();
                    $res['notif'] = 'danger';
                    $res['to_link'] = '';
                    echo json_encode($res); 
                    die;
                }
                $meta = array('page_title' => 'Add-on');
                $this->page_construct_ab('add', $meta, $this->data);
            }
            else{
                $data=array(
                    'name'  => $this->input->post('name'),
                    'price' => $this->input->post('price'),
                    'is_active' => $this->input->post('is_active'),
                );

                if ($this->settings_model->addAddon($data)) {
                    $this->db->trans_commit();
                    $res['message'] = 'Data Berhasil ditambah';
                    $res['notif'] = 'success';
                    $res['to_link'] = site_url('billing_portal/plugin');
                }else{
                    $this->db->trans_rollback();
                    $res['message'] = 'Data Gagal ditambah';
                    $res['notif'] = 'danger';
                    $res['to_link'] = '';
                }
                echo json_encode($res);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $res['message'] = $th->getMessage();
            $res['notif'] = 'danger';
            $res['to_link'] = '';
            echo json_encode($res);
        }
    }

    public function edit($id)
    {
        // $this->sma->checkPermissions(false, true);
            $this->form_validation->set_rules('image_thumb', lang("image_thumb"), 'xss_clean');
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('price', lang("price"), 'required');

        if ($this->form_validation->run() == false) {
            $where = ['id'=>$id];
            $this->data['getAddons_row'] = $this->subscription_model->getAddons_row($where);
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $meta = array('page_title' => 'Add-on');

            $this->page_construct_ab('edit', $meta, $this->data);
        }
        else{
            $data = array(
                'name'  => $this->input->post('name'),
                'price' => $this->input->post('price'),
                'is_active' => $this->input->post('is_active'),
            );

            if ($this->settings_model->updateAddon($id, $data)) {
                $res['message'] = 'Data Berhasil diedit';
                $res['notif'] = 'success';
                $res['to_link'] = site_url('billing_portal/plugin');
            }else{
                $res['message'] = 'Data Gagal diedit';
                $res['notif'] = 'danger';
                $res['to_link'] = '';
            }
            echo json_encode($res);
        }
    }

}

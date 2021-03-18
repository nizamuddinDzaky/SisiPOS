<?php defined('BASEPATH') or exit('No direct script access allowed');

class Plan extends MY_Controller
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
        $this->load->model('settings_model');
    }

    public function index()
    {
        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['title'] = 'List Plan';
        $meta = array('page_title' => 'Plan');

        $this->page_construct_ab('list_plan', $meta, $this->data);
    }

    public function add()
    {
        // $this->sma->checkPermissions(false, true);
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name_plan', lang("name"), 'required');
            $this->form_validation->set_rules('warehouses_plan', lang("warehouse"), 'required');
            $this->form_validation->set_rules('users_plan', lang("user"), 'required');
            $this->form_validation->set_rules('price_plan', lang("price"), 'required');
            if ($this->form_validation->run() == false) {
                if(validation_errors()){
                    $res['message'] = validation_errors();
                    $res['notif'] = 'danger';
                    $res['to_link'] = '';
                    echo json_encode($res); 
                    die;
                }
                $meta = array('page_title' => 'Plan');
                $this->page_construct_ab('add_plan', $meta, $this->data);
            }
            else{
                $data = array(
                    'name'          => $this->input->post('name_plan'),
                    'description'   => $this->input->post('description_plan'),
                    'price'         => $this->input->post('price_plan'),
                    'warehouses'    => $this->input->post('warehouses_plan'),
                    'users'         => $this->input->post('users_plan'),
                    'limitation'    => $this->input->post('limitation'),
                );
                
                if ($this->settings_model->addPlan($data)) {
                    $this->db->trans_commit();
                    $res['message'] = 'Data Berhasil ditambah';
                    $res['notif'] = 'success';
                    $res['to_link'] = site_url('billing_portal/plan');
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
            $this->form_validation->set_rules('price', lang("price"), 'required');

        if ($this->form_validation->run() == false) {
            if(validation_errors()){
                $res['message'] = validation_errors();
                $res['notif'] = 'danger';
                $res['to_link'] = '';
                echo json_encode($res); 
                die;
            }
            $this->data['id'] = $id;
            $this->data['plan'] = $this->site->getPlanPricingByID($id);
            $meta = array('page_title' => 'Plan');

            $this->page_construct_ab('edit_plan', $meta, $this->data);
        }
        else{
            $data = array(
                'master' => $this->input->post('master_data'),
                'pos' => $this->input->post('pos'),
                'purchases' => $this->input->post('purchases'),
                'sales' => $this->input->post('sales'),
                'quotes' => $this->input->post('quotes'),
                'expenses' => $this->input->post('expenses'),
                'reports' => $this->input->post('reports'),
                'transfers' => $this->input->post('transfers'),
                'limitation' => $this->input->post('limitation'),
                'users' => $this->input->post('users'),
                'warehouses' => $this->input->post('warehouses'),
                'price' => $this->input->post('price'),
                'limitation' => $this->input->post('limitation'),
                'description' => $this->input->post('description_plan'),
            );
            
            if ($this->settings_model->updatePlan($id, $data)) {
                $res['message'] = 'Data Berhasil diedit';
                $res['notif'] = 'success';
                $res['to_link'] = site_url('billing_portal/plan');
            }else{
                $res['message'] = 'Data Gagal diedit';
                $res['notif'] = 'danger';
                $res['to_link'] = '';
            }
            echo json_encode($res);
        }
    }

    public function getPlan()
    {
        $edit_link = "<a class=\"tip\" title='".lang("edit_plan")."' href='".site_url('billing_portal/plan/edit/$1')."'><i class=\"fa fa-edit\"></i> ". lang("edit_plan")."</a>";
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
            ->select("id, name, description, price, limitation")
            ->from('plans');

        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }

}

<?php defined('BASEPATH') or exit('No direct script access allowed');

class Authorized extends MY_Controller
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
        $this->load->model('companies_model');
        $this->load->model('settings_model');
    }

    public function index()
    {
        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['title'] = 'List User';
        $meta = array('page_title' => 'User');

        $this->page_construct_ab('list_authorized', $meta, $this->data);
    }

    public function getAuthorized()
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

        $join = "(SELECT email, company_id
                FROM
                sma_users
                WHERE group_id = '2'
                ) sma_join ";

        $this->load->library('datatables');
        $this->datatables
            ->select('authorized.id, companies.company, sma_join.email, users, warehouses, biller')
            ->join('companies', 'authorized.company_id = companies.id', 'left')
            ->join($join, 'sma_join.company_id = authorized.company_id', 'left')
            ->from("authorized");

        if ($this->Owner || $this->AdminBilling ){}else{
            $this->datatables->where('authorized.company_id', $this->session->userdata('company_id'));
        }

        // $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }

}

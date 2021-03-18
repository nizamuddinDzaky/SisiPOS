<?php defined('BASEPATH') or exit('No direct script access allowed');

class Target extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
 
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('target_model');
        // $this->insertLogActivities();
    }
    
    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }
        
        $this->data['allproduk'] = $this->target_model->getAllProducts();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => 'Target'));
        $meta = array('page_title' => 'Target', 'bc' => $bc);
        
        $this->page_construct('target/index', $meta, $this->data);
    }
    
    public function getTarget($year, $month)
    {
        $this->load->library('datatables');

        $this->datatables
            ->select("id,week1,week2,week3,week4,week5")
            ->from("target")
            ->where("month", $month)
            ->where("year", $year);

        echo $this->datatables->generate();
    }

    public function show()
    {
        $produk=$this->input->post('product');
        $month=$this->input->post('month');
        $year=$this->input->post('year');

        $this->load->library('datatables');

        $this->datatables
            ->select("week1")
            ->from("target")
            ->where("month", $month)
            ->where("year", $year);

        echo $this->datatables->generate();
        redirect('target/');
    }
}

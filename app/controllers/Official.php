<?php defined('BASEPATH') or exit('No direct script access allowed');

class Official extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('Official_model');
        $this->load->model('Curl_model');
    }
    
    public function index()
    {
        $this->data['suppliers'] = $this->Official_model->getPartner('supplier');
        $this->data['reference'] = $this->Official_model->getAllParnerNumber();
        $bc = array(array('link' => '#', 'page' => lang('Official Partner')));
        $meta = array('page_title' => lang('Official Partner'), 'bc' => $bc);
        $this->page_construct('Official/ListPartner', $meta, $this->data);
    }
    public function registered($id=null)
    {
        $this->data['vsupplier'] = $id;
        $this->data['reference'] =  $this->Official_model->getParnerNumberbyID($id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme.'Official/Customer_validation', $this->data);
    }
    
    public function add_registered()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('reference_kode1', lang('reference_kode'), 'required|min_length[2]');
        $this->form_validation->set_rules('read_supplier', lang('reference_kode'), 'required');
        
        $return = array(
            "reference_code_1" => strtolower($this->input->post('reference_kode1')),
            "supplier_id" => $this->input->post('read_supplier')
        );
        
        if ($this->form_validation->run() == true && $record = $this->Official_model->add_partner($return)) {
            $json = array('message' => $record['message'],'code'=> '1','return' => $record);
        } else {
            $json =array('message' => 'False','code' => '500' );
        }
        
        header('Content-Type: application/json');
        echo json_encode($json);
        return true;
    }
    
    public function register_product()
    {
        $name=$this->input->get('name', true);
        $supplier=$this->input->get('supplier', true);
        $id = $this->input->get('id', true);
        
        $pid=$this->Official_model->update_product_erp($name, $supplier, $id);
        if ($pid) {
            $response=array('code'=>1, 'message'=> lang('success_sync'));
        } else {
            $response= array('code'=>0, 'message'=> lang('failed_sync'));
        }
        
        $this->sma->send_json($response);
    }
    
    public function get_product($stockist=null, $pid=null)
    {
        $term=$this->input->get('term', true);
        $supplier=$this->input->get('supplier', true);
        $id=$this->input->get('product_id', true);
        if (!$supplier) {
            $supplier=$stockist;
        }
        
        $result=$this->Official_model->get_product_erp($term, $supplier, $pid);
        $this->sma->send_json($result);
    }
}

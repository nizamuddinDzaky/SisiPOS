<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Common\Entity\Row;

class Subscription extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->insertLogActivities();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->lang->load('customers', $this->Settings->user_language);
        $this->lang->load('sma', $this->Settings->user_language);
        $this->lang->load('pos', $this->Settings->user_language);
        $this->lang->load('auth', $this->Settings->user_language);
        $this->load->model('auth_model');
        $this->load->model('subscription_model');
        $this->load->model('integration_model', 'integration');
    }

    public function index()
    {
        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['title'] = 'Subscription';
        $this->data['authorized'] = $this->sma->getAuthorized();
        $this->data['expiredBill'] = $this->auth_model->getExpiredBill();

        $meta = array('page_title' => 'Subscription');

        $this->page_construct_ab('subscription', $meta, $this->data);
    }

    public function set_billing()
    {
        if ($this->AdminBilling) {
            $meta = array('page_title' => lang("set_billing"));
            $this->data['message'] = $this->session->flashdata('message');
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['plan'] = $plan = $this->subscription_model->getPlan_result();
            $this->page_construct_ab('set_billing', $meta, $this->data);
        }
        else{
            redirect(site_url().'billing_portal/subscription');
        }
    }

    public function set_billing_history()
    {
        if ($this->AdminBilling) {
            $meta = array('page_title' => lang("set_billing_history"));
            $this->data['message'] = $this->session->flashdata('message');
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->page_construct_ab('set_billing_history', $meta, $this->data);
        }
        else{
            redirect(site_url().'billing_portal/subscription');
        }
    }

    public function set_billing_add()
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', '4096M');

        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('plan_id', lang("plan_id"), 'required');
            if ($this->form_validation->run() == true) {
                $plan_id = $this->input->post('plan_id');
                $get = $this->subscription_model->get_set_distributor();

                if($plan_id == 1){
                    if($get){
                        $set_false = [];    $user_set = false;    $wh_set = false;
                        $where_plan = ['id' => $plan_id];
                        $get_plan = $this->subscription_model->getPlan_row($where_plan);

                        foreach ($get as $k => $v) {
                            $data_author = [
                                'company_id'=>$v->company_id,
                                'company_name'=>$v->company_name,
                                'users' => 2,
                                'warehouses' => 1,
                                'plan_name'=> $get_plan->name,
                                'plan_id'=> $plan_id,
                                'status' => null,
                                'start_date' => null,
                                'expired_date' => null
                            ];
                            $where_author = ['company_id' => $v->company_id];
                            $authorize = $this->subscription_model->getAuthor_row($where_author);

                            $set_billing = $this->subscription_model->setBillingInv($data_author, null, $authorize);

                            if($set_billing){
                                if($v->email != ''){
                                    $data_email[] = [
                                        'company_name'=>$v->company_name,
                                        'plan_name'=> $get_plan->name,
                                        'plan_id'=> $plan_id,
                                        'additional_user'=> 0,
                                        'additional_warehouse'=> 0,
                                        'payment_period'=> 0,
                                        'payment_status'=> '-',
                                        'start_date'=> '-',
                                        'expired_date'=> '-',
                                        'email' => $v->email
                                    ];
                                }
                            }
                            else{
                                $set_false[] = $v->company_id;
                            }
                        }

                        if(count($set_false) > 0){
                            $this->db->trans_rollback();
                            $res['message'] = lang('set_billing_failed');
                            $res['notif'] = 'danger';
                            echo json_encode($res); die;
                        }

                        $this->db->trans_commit();
                        if($this->input->post('send_email')){
                            $send = $this->send_email($data_email);
                            if($send){
                                $this->subscription_model->empty_table('billing_company_set');
                                $res['message'] = lang('set_billing_success');
                                $res['notif'] = 'success';
                                $res['to_link'] = base_url().'billing_portal/subscription';
                                echo json_encode($res); die;
                            }
                            else{
                                $res['message'] = lang('set_billing_success');
                                $res['notif'] = 'success';
                                $res['to_link'] = base_url().'billing_portal/subscription';
                                echo json_encode($res);die;
                            }
                        }
                        else{
                            $this->subscription_model->empty_table('billing_company_set');
                            $res['message'] = lang('set_billing_success');
                            $res['notif'] = 'success';
                            $res['to_link'] = base_url().'billing_portal/subscription';
                            echo json_encode($res);
                        }
                    }
                    else{
                        $res['message'] = 'No Company Selected';
                        $res['notif'] = 'danger';
                        echo json_encode($res);
                    }
                }
                else{
                    $this->form_validation->set_rules('start_date', lang("start_date"), 'required');
                    $this->form_validation->set_rules('end_date', lang("end_date"), 'required');
                    $this->form_validation->set_rules('payment', lang("payment"), 'required');
                    if ($this->form_validation->run() == true) {
                        if($get){
                            $set = true;    $user_set = false;    $wh_set = false;
                            $start_date = $this->input->post('start_date');
                            $end_date = $this->input->post('end_date');
                            $user = $this->input->post('user');
                            $warehouse = $this->input->post('warehouse');
                            $payment = $this->input->post('payment');
                            $payment_done = $this->input->post('payment_done');
                            $send_email = $this->input->post('send_email');

                            $start = strtotime($start_date);
                            $end = strtotime('+'.$payment.' months', $start);
                            $str_end = strtotime($end_date);

                            if($str_end != $end){
                                $res['message'] = 'End date harus menyesuaikan payment period';
                                $res['notif'] = 'danger';
                                echo json_encode($res);
                            }
                            else{
                                $where_addon_user = ['name' => 'user'];
                                $get_addon_user = $this->subscription_model->getAddons_row($where_addon_user);
                                $where_addon_wh = ['name' => 'warehouse'];
                                $get_addon_wh = $this->subscription_model->getAddons_row($where_addon_wh);

                                if($user != ''){
                                    $jml_user_set = $user;
                                    $price_user_master = $get_addon_user->price;
                                    $id_addon_user = $get_addon_user->id;
                                    $name_addon_user = $get_addon_user->name;
                                    $user_set = true;
                                }
                                else{
                                    $price_user_master = $get_addon_user->price;
                                    $id_addon_user = $get_addon_user->id;
                                    $name_addon_user = $get_addon_user->name;
                                }

                                if($warehouse != ''){
                                    $jml_warehouse_set = $warehouse;
                                    $price_warehouse_master = $get_addon_wh->price;
                                    $id_addon_warehouse = $get_addon_wh->id;
                                    $name_addon_warehouse = $get_addon_wh->name;
                                    $wh_set = true;
                                }
                                else{
                                    $price_warehouse_master = $get_addon_wh->price;
                                    $id_addon_warehouse = $get_addon_wh->id;
                                    $name_addon_warehouse = $get_addon_wh->name;
                                }

                                $where_plan = ['id' => $this->input->post('plan_id')];
                                $get_plan = $this->subscription_model->getPlan_row($where_plan);

                                foreach ($get as $k => $v) {
                                    $data_author = ['status'=>'activated',
                                                    'plan_name'=> $get_plan->name,
                                                    'plan_id'=> $this->input->post('plan_id'),
                                                    'start_date'=> $this->input->post('start_date'),
                                                    'expired_date'=> $this->input->post('end_date'),
                                                    'company_id' => $v->company_id
                                                ];

                                    $where_author = ['company_id' => $v->company_id];
                                    $authorize = $this->subscription_model->getAuthor_row($where_author);
                                    $author_user = $authorize->users;
                                    $author_wh = $authorize->warehouses;
                                    $plan_user = $get_plan->users;
                                    $plan_wh = $get_plan->warehouses;

                                    $kelipatan = 5;
                                    $date = date('Y-m-d H:i:s');
                                    $reference = $this->site->getReference('binv', $v->company_id);

                                    //======= menghitung jumlah user & penetapan harganya
                                    if($user_set == true){
                                        $jml_user = $jml_user_set;
                                    } else{
                                        if($author_user > $plan_user){
                                            $jml_user = $author_user - $plan_user;
                                        } else{
                                            $jml_user = 0;
                                        }
                                    }

                                    if($jml_user > 0){
                                        for ($i = $jml_user; $i < $kelipatan+$jml_user ; $i++) { 
                                            if($i % $kelipatan == 0){
                                                $qty_user = $i;
                                                break;
                                            }
                                        }
                                        $price_user = $price_user_master * ($qty_user / $kelipatan);
                                    } else{
                                        $price_user = 0;
                                    }
                                    

                                    //======= menghitung jumlah warehouse & penetapan harganya
                                    if($wh_set == true){
                                        $jml_wh = $jml_warehouse_set;
                                    }else{
                                        if($author_user > $plan_user){
                                            $jml_wh = $author_wh - $plan_wh;
                                        } else{
                                            $jml_wh = 0;
                                        }
                                    }

                                    if($jml_wh > 0){
                                        for ($i = $jml_wh; $i < $kelipatan+$jml_wh ; $i++) { 
                                            if($i % $kelipatan == 0){
                                                $qty_wh = $i;
                                                break;
                                            }
                                        }
                                        $price_warehouse = $price_warehouse_master * ($qty_wh / $kelipatan);
                                    } else{
                                        $price_warehouse = 0;
                                    }

                                    $subtotal_invoice = $get_plan->price + $price_warehouse + $price_user;
                                    $total_invoice = $subtotal_invoice * $payment;

                                    if($payment_done){
                                        $data_invoice = array(
                                            'date' => $date,
                                            'plan_id' => $get_plan->id,
                                            'plan_name' => $get_plan->name,
                                            'payment_period' => $payment,
                                            'price' => $get_plan->price,
                                            'subtotal' => $subtotal_invoice,
                                            'total' => $total_invoice,
                                            'company_name' => $v->company_name,
                                            'company_id' => $v->company_id,
                                            'created_by' => $this->session->userdata('user_id'),
                                            'authorized_id' => $authorize->id,
                                            'reference_no' => $reference,
                                            'due_date' => date('Y-m-d H:i:s', strtotime($date) + (60 * _PAYMENT_TERM)),
                                            'payment_status' => 'paid',
                                            'billing_status' => 'active',
                                            'start_date' => $start_date,
                                            'end_date' => $end_date
                                        );
                                    }
                                    else{
                                        $data_invoice = array(
                                            'date' => $date,
                                            'plan_id' => $get_plan->id,
                                            'plan_name' => $get_plan->name,
                                            'payment_period' => $payment,
                                            'price' => $get_plan->price,
                                            'subtotal' => $subtotal_invoice,
                                            'total' => $total_invoice,
                                            'company_name' => $v->company_name,
                                            'company_id' => $v->company_id,
                                            'created_by' => $this->session->userdata('user_id'),
                                            'authorized_id' => $authorize->id,
                                            'reference_no' => $reference,
                                            'due_date' => date('Y-m-d H:i:s', strtotime($date) + (60 * _PAYMENT_TERM)),
                                            'payment_status' => 'pending',
                                            'billing_status' => 'pending',
                                            'start_date' => $start_date,
                                            'end_date' => $end_date
                                        );
                                    }
                                    
                                    $data_item = [
                                        [
                                            'addon_id'      => $id_addon_user,
                                            'addon_name'    => $name_addon_user,
                                            'price'         => $price_user_master,
                                            'quantity'      => $jml_user,
                                            'subtotal'      => $price_user
                                        ],
                                        [
                                            'addon_id'      => $id_addon_warehouse,
                                            'addon_name'    => $name_addon_warehouse,
                                            'price'         => $price_warehouse_master,
                                            'quantity'      => $jml_wh,
                                            'subtotal'      => $price_warehouse
                                        ]
                                    ];

                                    $set_billing = $this->subscription_model->setBillingInv($data_invoice, $data_item, $authorize);
                                    if($set_billing){
                                        if($v->email != ''){
                                            $data_email[] = [
                                                'company_name'=>$v->company_name,
                                                'plan_name'=> $get_plan->name,
                                                'plan_id'=> $this->input->post('plan_id'),
                                                'additional_user'=> $jml_user,
                                                'additional_warehouse'=> $jml_wh,
                                                'payment_period'=> $payment,
                                                'payment_status'=> $data_invoice['payment_status'],
                                                'start_date'=> $this->input->post('start_date'),
                                                'expired_date'=> $this->input->post('end_date'),
                                                'email' => $v->email
                                            ];
                                        }
                                    }
                                    else{
                                        $set = false;
                                    }
                                }

                                if($set == false){
                                    $this->db->trans_rollback();
                                    $res['message'] = lang('set_billing_failed');
                                    $res['notif'] = 'danger';
                                    echo json_encode($res);
                                }

                                $this->db->trans_commit();
                                if($this->input->post('send_email')){
                                    $send = $this->send_email($data_email);
                                    if($send){
                                        $this->subscription_model->empty_table('billing_company_set');
                                        $res['message'] = lang('set_billing_success');
                                        $res['notif'] = 'success';
                                        $res['to_link'] = base_url().'billing_portal/subscription';
                                        echo json_encode($res); die;
                                    }
                                    else{
                                        $res['message'] = lang('set_billing_success');
                                        $res['notif'] = 'success';
                                        $res['to_link'] = base_url().'billing_portal/subscription';
                                        echo json_encode($res);die;
                                    }
                                }
                                else{
                                    $this->subscription_model->empty_table('billing_company_set');
                                    $res['message'] = lang('set_billing_success');
                                    $res['notif'] = 'success';
                                    $res['to_link'] = base_url().'billing_portal/subscription';
                                    echo json_encode($res);
                                }
                            }
                        }
                        else{
                            $res['message'] = 'No Company Selected';
                            $res['notif'] = 'danger';
                            echo json_encode($res);
                        }
                    }
                    else{
                        $res['message'] = validation_errors();
                        $res['notif'] = 'danger';
                        echo json_encode($res);
                        // throw new \Exception(validation_errors());
                    }
                }
            }
            else{
                throw new \Exception(validation_errors());
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(site_url().'billing_portal/subscription/set_billing');
        }
    }

    public function send_email($data=null)
    {
        $this->load->library('parser');
        foreach ($data as $k => $v) {
            $parse_data = [
                'client_link' => base_url() . 'billing_portal/subscription',
                'site_name' => $this->Settings->site_name,
                'company_name' => $v['company_name'],
                'plan_name' => $v['plan_name'],
                'additional_user' => $v['additional_user'].' User',
                'additional_warehouse' => $v['additional_warehouse'].' Warehouse',
                'start_date' => ($v['start_date'] == '-') ? $v['start_date'] : $this->sma->hrsd($v['start_date']),
                'expired_date' => ($v['expired_date'] == '-') ? $v['expired_date'] : $this->sma->hrsd($v['expired_date']),
                'payment_period' => $v['payment_period'].' Month',
                'payment_status' => $v['payment_status'],
                /*'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'*/
            ];

            $msg = file_get_contents('./themes/' . $this->theme . 'email_templates/subscription.html');
            $message = $this->parser->parse_string($msg, $parse_data);
            $subject = 'Subscription - ' . $this->Settings->site_name;
            $send = $this->sma->send_email($v['email'], $subject, $message);
        }
        
        if (!$send) {
            $this->session->set_flashdata('error', lang('failed_send_email'));
            redirect(site_url().'billing_portal/subscription/set_billing');
        }
        return true;
    }

    public function view_plans_pricing($renew=null)
    {
        $meta = array('page_title' => 'Subscription');
        if($renew){
            if($renew == 'renew'){
                $this->data['url_renew'] = 'billing_portal/subscription/get_billing_invoice_renewal'; 
            }else{
                $this->data['url_pay_reject'] = 'billing_portal/subscription/get_billing_invoice/reject'; 
            }
        }
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['author'] = $this->auth_model->getAuthor(); 
        $this->data['plans'] = $this->auth_model->getPlanPricing();
        $this->data['addons'] = $this->auth_model->getAddons();
        $this->data['current'] = $this->sma->getAuthorized();
        $this->page_construct_ab('view_plans_pricing', $meta, $this->data);
    }

    public function get_subscription_record()
    {
        $this->load->library('datatables');
        $join = "(SELECT group_id, id 
                    FROM sma_users) sma_join ";

        $this->datatables
            ->select("{$this->db->dbprefix('billing_invoices')}.id, 
                {$this->db->dbprefix('billing_invoices')}.date, 
                {$this->db->dbprefix('billing_invoices')}.reference_no, 
                billing_status, 
                payment_status as status, 
                company_name, 
                {$this->db->dbprefix('billing_invoices')}.plan_name, 
                date({$this->db->dbprefix('billing_invoices')}.start_date) as start_date, 
                date({$this->db->dbprefix('billing_invoices')}.end_date) as expired_date, 
                total, 
                a.status as approval,
                sma_join.group_id")
            ->from("billing_invoices")
            ->where("billing_invoices.plan_id !=", 1)
            ->join($join, 'sma_join.id = billing_invoices.created_by', 'left')
            ->join('payments p', 'billing_invoices.id=p.billing_id', 'left')
            ->join('authorized a', 'billing_invoices.company_id=a.company_id', 'left');

        if ($this->Admin) {
            $this->datatables->where('billing_invoices.company_id', $this->session->userdata('company_id'));
        }

        $this->datatables->add_column("Actions", "", "{$this->db->dbprefix('billing_invoices')}.id");

        echo $this->datatables->generate();
    }

    public function get_billing_history()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, date, company_name, plan_id, plan_name, start_date, end_date, reference_no, billing_status, payment_status, total")
            ->from("billing_set_history");

        $this->datatables->add_column("Actions", "", "id");

        echo $this->datatables->generate();
    }

    public function get_billing_history_detail()
    {
        $id = $this->input->get('id');
        $this->db
            ->select("*")
            ->where('id', $id)
            ->from("billing_set_history");
        $q = $this->db->get();
        foreach (($q->result()) as $row) {
            $data[] = $row;
        }
        echo json_encode($data);
    }

    public function getBillingPayment()
    {
        $id = $this->input->get('id');

        $getBilling = $this->auth_model->getBillInvByID($id);
        $getBillingPayment = $this->subscription_model->getBillingPaymentByInv($getBilling->id); 
        if($getBillingPayment->image == 'no_image.png' || is_null($getBillingPayment->image)){
            $getBilling->image = site_url().'assets/uploads/no_image.png';
        } 
        else{
            $url = site_url().'assets/uploads/proof_payments/'.$getBillingPayment->image;
            $getBilling->image = validate_url($getBillingPayment->image, $url);
        }
        echo json_encode($getBilling);
    }

    public function get_billing_invoice($reject=null)
    {
        if($reject){
            $billing = $this->auth_model->getBillingInvReject();
        }
        else{
            $billing = $this->auth_model->getBillingInv();
        }
        $author = $this->auth_model->getAuthor(); 
        $company = '';  $item = '';  $subtotal = '';
        if($billing){
            $company = $this->site->getAllCompanies('biller');
            $item = $this->auth_model->getBillingInvItem($billing->id);
            $subtotal = $this->auth_model->getBillInvByID($billing->id);
        }
        $result = array('billing' => $billing, 'company' => $company, 'item' => $item, 'subtotal' => $subtotal, 'author' => $author);
        echo json_encode($result);
    }

    public function get_billing_temp()
    {
        $billing_temp = $this->auth_model->getBillingTemp();

        $item = '';
        if($billing_temp){
            $item = $this->auth_model->getBillingItemTemp($billing_temp->id);
        }

        $result['billing'] = $billing_temp;
        $result['item'] = $item;
        echo json_encode($result);
    }

    public function get_billing_invoice_renewal()
    {
        $this->db->trans_begin();
        try {
            $active = $this->auth_model->getActiveBill();
            $billing_detail = $this->auth_model->getBillingByID($active->id);
            $item = $this->auth_model->getBillingInvItem($billing_detail->billing_id);
            $date = date('Y-m-d H:i:s');
            $data_billing = [
                            'date' => $date,
                            'plan_id' => $billing_detail->plan_id,
                            'plan_name' => $billing_detail->plan_name,
                            'price' => $billing_detail->price,
                            'company_name' => $billing_detail->company_name,
                            'company_id' => $this->session->userdata('company_id'),
                            'created_by' => $this->session->userdata('user_id'),
                            'payment_period' => $billing_detail->payment_period,
                            'subtotal' => $billing_detail->subtotal,
                            'total' => $billing_detail->total_inv,
                        ];
            $data_item = '';    $subtotal = '';    
            if($item){
                $data_item = $item;
            }

            $billing_id = $this->auth_model->addInvRenewal($data_billing, $data_item);
            if($billing_id){
                $this->db->trans_commit();
                $billing_temp = $this->auth_model->getBillingTempByID($billing_id);
                $subtotal = $billing_temp->subtotal;
            }
            
            $author = $this->auth_model->getAuthor(); 
            $company = $this->site->getAllCompanies('biller');
            $result = array('billing' => $billing_temp, 'company' => $company, 'item' => $data_item, 'subtotal' => $subtotal, 'author' => $author);
            echo json_encode($result);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        return false;
    }

    public function add_billing_temp($id = null)
    {
        $this->db->trans_begin();
        try {
            $id_plan = $id ? $id : $this->input->get('id', true);
            $add_ons = $this->auth_model->getAddons();

            $date = date('Y-m-d H:i:s');
            $plan_detail = $this->site->getPlanPricingByID($id_plan);
            $payment_period = $this->input->post('payment_period');
            $add_plan = $this->input->post('add_plan');

            if ($plan_detail && $add_plan && $payment_period) {
                foreach ($add_ons as $item) {
                    $kelipatan = 5;
                    if ($this->input->post('p_' . $item->id) || $this->input->post('p_qty_' . $item->id)) {
                        $qty = $this->input->post('p_qty_' . $item->id) ? $this->input->post('p_qty_' . $item->id) : 1;
                        for ($i = $qty; $i < $kelipatan+$qty ; $i++) { 
                            if($i % $kelipatan == 0){
                                $qty_item = $i;
                                break;
                            }
                        }
                        $subtotal = $item->price * ($qty_item / $kelipatan);
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
                $subtotal_billing = $total + $plan_detail->price;
                $total = ($total + $plan_detail->price) * $payment_period ;
                $data = array(
                    'date' => $date,
                    'plan_id' => $plan_detail->id,
                    'plan_name' => $plan_detail->name,
                    'payment_period' => $payment_period,
                    'price' => $plan_detail->price,
                    'subtotal' => $subtotal_billing,
                    'total' => $total,
                    'company_name' => $this->session->userdata('company_name'),
                    'company_id' => $this->session->userdata('company_id'),
                    'created_by' => $this->session->userdata('user_id'),
                );
            } elseif ($this->input->post('add_plan')) {
                echo json_encode(validation_errors());
                return true;
            }

            if (!$this->auth_model->addBillingTemp($data, $item_billing)) {
                throw new Exception("Add Temporary Billing failed");
            }
            
            $this->db->trans_commit();
            $this->get_billing_temp();
            return true;
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        return false;
    }

    public function add_billing_invoice($renew = null)
    {
        $this->db->trans_begin();
        try {
            $billing_temp = $this->auth_model->getBillingTemp();
            $data_item = [];
            if($renew){
                $billing_status = 'pending renewal';
            } else{
                $billing_status = 'pending';
            }

            if($billing_temp){
                $date = date('Y-m-d H:i:s');
                $authorize = $this->sma->getAuthorized();
                $reference = $this->site->getReference('binv');
                $data = array(
                    'id_temp' => $billing_temp->id,
                    'date' => $billing_temp->date,
                    'plan_id' => $billing_temp->plan_id,
                    'plan_name' => $billing_temp->plan_name,
                    'payment_period' => $billing_temp->payment_period,
                    'price' => $billing_temp->price,
                    'subtotal' => $billing_temp->subtotal,
                    'total' => $billing_temp->total,
                    'company_name' => $billing_temp->company_name,
                    'company_id' => $billing_temp->company_id,
                    'created_by' => $billing_temp->created_by,
                    'authorized_id' => $authorize->id,
                    'reference_no' => $reference,
                    'due_date' => date('Y-m-d H:i:s', strtotime($date) + (60 * _PAYMENT_TERM)),
                    'payment_status' => 'pending',
                    'billing_status' => $billing_status,
                );

                $item = $this->auth_model->getBillingItemTemp($billing_temp->id);
                if($item){
                    foreach ($item as $key => $value) {
                        unset($item[$key]->id);
                        unset($item[$key]->billing_invoices_temp_id);
                        $data_item[] = $item[$key];
                    }
                }

                if (!$this->auth_model->addBillingInv($data, $data_item)) {
                    throw new Exception("Add Billing failed");
                }
            }
            // if ($this->input->post('add_checkout')) {
            /*} elseif ($this->input->post('add_checkout')) {
                echo json_encode(validation_errors());
                return true;
            }*/
            $this->db->trans_commit();
            $this->get_billing_invoice();
            return true;
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        return false;
    }

    public function add_proof_payment($id)
    {
        $this->db->trans_begin();
        try {
            $billing_detail = $this->auth_model->getBillingByID($id);
            $date = date('Y-m-d H:i:s');

            $this->form_validation->set_rules('add_proof', lang("upload_file"), 'required');
            //if($this->form_validation->run()==true){
            if ($_FILES['add_proof']['size'] > 0) {
                if((int)$billing_detail->total_inv > 0){
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
                        $this->db->trans_rollback();
                        $error = $this->upload->display_errors();
                        throw new Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg = $this->integration->upload_files($_FILES['add_proof']);
                    $photo = $uploadedImg->url;
                    $data = array(
                        'date'              => $date,
                        'billing_invoice_id' => $id,
                        'image'             => $photo,
                        'created_by'        => $this->session->userdata('user_id'),
                        'company_id'        => $this->session->userdata('company_id'),
                        'amount'            => $billing_detail->total_inv,
                        'reference_no'      => $billing_detail->inv_ref,
                        'updated_at'        => $date,
                        'updated_by'        => $this->session->userdata('user_id')
                    );

                    if (!$this->auth_model->addProofPayment($data)) {
                        throw new Exception("Add proof failed");
                    }
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang('add_proof_payment_success'));
                    redirect(site_url().'billing_portal/subscription');
                }
                else if((int)$billing_detail->total_inv == 0){
                    $data = array(
                        'date'              => $date,
                        'billing_invoice_id' => $id,
                        'image'             => 'no_image.jpg',
                        'created_by'        => $this->session->userdata('user_id'),
                        'company_id'        => $this->session->userdata('company_id'),
                        'amount'            => $billing_detail->total_inv,
                        'reference_no'      => $billing_detail->inv_ref,
                        'updated_at'        => $date,
                        'updated_by'        => $this->session->userdata('user_id')
                    );

                    if (!$this->auth_model->addProofPayment($data)) {
                        throw new Exception("Add proof failed");
                    }
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang('add_proof_payment_success'));
                    redirect(site_url().'billing_portal/subscription');
                }
            }
            else {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Upload File is Required');
                redirect(site_url().'billing_portal/subscription/view_plans_pricing');
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(site_url().'billing_portal/subscription/view_plans_pricing');
        }
    }

    public function add_proof_payment_renewal($id)
    {
        $this->db->trans_begin();
        try {
            $date = date('Y-m-d H:i:s');
            $billing_detail = $this->auth_model->getBillingByID($id);

            $this->form_validation->set_rules('add_proof', lang("proof_of_payments"), 'required');
            //if($this->form_validation->run()==true){
            if ($_FILES['add_proof']['size'] > 0) {
                if((int)$billing_detail->total_inv > 0){
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
                        $this->db->trans_rollback();
                        $error = $this->upload->display_errors();
                        throw new Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg = $this->integration->upload_files($_FILES['add_proof']);
                    $photo = $uploadedImg->url;
                    $data = array(
                        'date'              => $date,
                        'image'             => $photo,
                        'created_by'        => $this->session->userdata('user_id'),
                        'company_id'        => $this->session->userdata('company_id'),
                        'billing_invoice_id'=> $id,
                        'amount'            => $billing_detail->total_inv,
                        'reference_no'      => $billing_detail->inv_ref,
                        'updated_at'        => $date,
                        'updated_by'        => $this->session->userdata('user_id')
                    );

                    if (!$this->auth_model->addProofPaymentRenewal($data)) {
                        $this->db->trans_rollback();
                        throw new Exception("Add proof failed");
                    }
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang('add_proof_payment_success'));
                    redirect(site_url().'billing_portal/subscription');
                }
                else if((int)$billing_detail->total_inv == 0){
                    $data = array(
                        'date'              => $date,
                        'image'             => 'no_image.jpg',
                        'created_by'        => $this->session->userdata('user_id'),
                        'company_id'        => $this->session->userdata('company_id'),
                        'billing_invoice_id'=> $id,
                        'amount'            => $billing_detail->total_inv,
                        'reference_no'      => $billing_detail->inv_ref,
                        'updated_at'        => $date,
                        'updated_by'        => $this->session->userdata('user_id')
                    );

                    if (!$this->auth_model->addProofPaymentRenewal($data)) {
                        $this->db->trans_rollback();
                        throw new Exception("Add proof failed");
                    }
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang('add_proof_payment_success'));
                    redirect(site_url().'billing_portal/subscription');
                }
            }
            else {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Upload File is Required');
                redirect(site_url().'billing_portal/subscription/view_plans_pricing');
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function finish()
    {
        $this->db->trans_begin();
        $id = $this->input->post('id');
        $getBilling = $this->auth_model->getBillInvByID($id);

        $data = array(
            'date' => date('Y-m-d H:i:s'),
            'reference_no' => $getBilling->reference_no,
            'paid_by' => 'bank',
            'amount' => $getBilling->total,
            'created_by' => $this->session->userdata('user_id'),
            'type' => 'received',
            'company_id' => $this->session->userdata('company_id'),
            'billing_id' => $id
        );

        if ((sizeof($data)>0) && $this->auth_model->addPaymentBillingInv($data)) {
            $this->db->trans_commit();
            $res['message'] = lang('payment_subscription_success');
            $res['notif'] = 'success';
            $res['to_link'] = '';
            echo json_encode($res); die;
        } else {
            $this->db->trans_rollback();
            $res['message'] = lang('payment_subscription_failed');
            $res['notif'] = 'danger';
            $res['to_link'] = '';
            echo json_encode($res); die;
        }
    }

    public function finishRenewal()
    {
        $this->db->trans_begin();
        $id = $this->input->post('id');
        $getBilling = $this->auth_model->getBillInvByID($id);

        $data = array(
            'date' => date('Y-m-d H:i:s'),
            'reference_no' => $getBilling->reference_no,
            'paid_by' => 'bank',
            'amount' => $getBilling->total,
            'created_by' => $this->session->userdata('user_id'),
            'type' => 'received',
            'company_id' => $this->session->userdata('company_id'),
            'billing_id' => $id
        );

        if ((sizeof($data)>0) && $this->auth_model->addPaymentBillingInvRenewal($data, $getBilling)) {
            $this->db->trans_commit();
            $res['message'] = lang('payment_renewal_subscription_success');
            $res['notif'] = 'success';
            $res['to_link'] = '';
            echo json_encode($res); die;
        } else {
            $this->db->trans_rollback();
            $res['message'] = lang('payment_renewal_subscription_failed');
            $res['notif'] = 'danger';
            $res['to_link'] = '';
            echo json_encode($res); die;
        }
    }

    public function pay_from_admin()
    {
        $this->db->trans_begin();
        $id = $this->input->post('id');
        $getBilling = $this->auth_model->getBillingByID($id);
        $date = date('Y-m-d H:i:s');

        $data_billing_payment = array(
            'date'              => $date,
            'reference_no'      => $getBilling->reference_no,
            'billing_invoice_id' => $id,
            'amount'            => $getBilling->total,
            'updated_at'        => $date,
            'updated_by'        => $this->session->userdata('user_id'),
            'created_by'        => $this->session->userdata('user_id'),
            'company_id'        => $getBilling->company_id
        );

        $data_payment = array(
            'date'          => $date,
            'reference_no'  => $getBilling->reference_no,
            'paid_by'       => 'bank',
            'amount'        => $getBilling->total,
            'created_by'    => $this->session->userdata('user_id'),
            'type'          => 'received',
            'company_id'    => $this->session->userdata('company_id'),
            'billing_id'    => $id
        );

        if ($this->subscription_model->pay_from_admin($data_billing_payment, $data_payment, $getBilling)) {
            $this->db->trans_commit();
            $res['message'] = lang('payment_subscription_success');
            $res['notif'] = 'success';
            $res['to_link'] = '';
            echo json_encode($res); die;
        } else {
            $this->db->trans_rollback();
            $res['message'] = lang('payment_subscription_failed');
            $res['notif'] = 'danger';
            $res['to_link'] = '';
            echo json_encode($res); die;
        }
    }

    public function cancel()
    {
        $this->db->trans_begin();
        $id = $this->input->post('id');
        $data = ['billing_status' => 'canceled'];

        if ($this->auth_model->cancelBillingInv($id, $data)) {
            $this->db->trans_commit();
            $res['message'] = lang('cancel_subscription_success');
            $res['notif'] = 'success';
            $res['to_link'] = '';
            echo json_encode($res); die;
        } else {
            $this->db->trans_rollback();
            $res['message'] = lang('cancel_subscription_failed');
            $res['notif'] = 'danger';
            $res['to_link'] = '';
            echo json_encode($res); die;
        }
    }

    public function reject()
    {
        $this->db->trans_begin();
        $id = $this->input->post('id');
        $data = ['billing_status' => 'pending', 'payment_status' => 'rejected'];
        $where = ['id' => $id];

        if ($this->auth_model->updateBilling($data, $where)) {
            $this->db->trans_commit();
            $res['message'] = lang('reject_payment_success');
            $res['notif'] = 'success';
            $res['to_link'] = '';
            echo json_encode($res); die;
        } else {
            $this->db->trans_rollback();
            $res['message'] = lang('reject_payment_failed');
            $res['notif'] = 'danger';
            $res['to_link'] = '';
            echo json_encode($res); die;
        }
    }

    public function cek_pending_billing()
    {
        $cekPending = $this->auth_model->getPendingBill();
        if($cekPending){
            $res['message'] = lang('there_are_pending_or_waiting_or_reject_bill_in_your_account');
            $res['notif'] = 'danger';
            echo json_encode($res); die;
        }else{
            $res['message'] = '';
            $res['notif'] = '';
            echo json_encode($res);
        }
    }

    public function pdf($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['rows'] = $billing_detail = $this->auth_model->getBillingByID($id);
        $this->data['item'] = $this->auth_model->getBillingInvItem($billing_detail->id);
        $this->data['biller'] = $this->site->getCompanyByID($billing_detail->company_id);

        if($this->AdminBilling){
            $name = lang("invoice").' '.$billing_detail->reference_no.".pdf";
            $html = $this->load->view($this->theme . 'billing_portal/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
        else{
            if($this->session->userdata('company_id') == $billing_detail->company_id){
                $name = lang("invoice").' '.$billing_detail->reference_no.".pdf";
                $html = $this->load->view($this->theme . 'billing_portal/pdf', $this->data, true);
                if (!$this->Settings->barcode_img) {
                    $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
                }
                $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
            }
            else{
                $this->session->set_flashdata('error', 'Not Your Invoice');
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
    }

    public function getBillerAktif()
    {
        $this->load->library('datatables');
        $join = "(SELECT email, company_id FROM sma_users
                    WHERE group_id = '2' AND `active` = '1'
                ) sma_join ";
        $join2 = "(SELECT DATE_ADD(DATE_ADD(create_on, INTERVAL 6 MONTH),INTERVAL 1 DAY) as start, create_on, company_id 
                    FROM sma_authorized) sma_join2 ";
        $this->datatables
                ->select('companies.id, companies.cf1, companies.company, sma_join.email as mail, sma_join2.create_on, sma_join2.start, sma_join.email as user_email')
                ->join($join, 'sma_join.company_id = companies.id', 'inner')
                ->join($join2, 'sma_join2.company_id = companies.id', 'inner')
                ->from("companies");
        $this->datatables->add_column("Actions", "", "companies.id");
        echo $this->datatables->generate();
    }

    public function getSetCompany()
    {
        $this->load->library('datatables');
        $this->datatables->select("id, company_name, email")
                ->from("billing_company_set");
        $this->datatables->add_column("Actions", "", "id");
        echo $this->datatables->generate();
    }

    public function set_distributor()
    {   
        $this->load->model('Companies_model', 'companies');
        $id = $this->input->post('id', TRUE);
        $cek = $this->subscription_model->cek_set_distributor($id);
        if($cek){
            $company = $this->subscription_model->getCompany($id);
            $data = [ 'date' => date('Y-m-d H:i:s'),
                    'company_id' => $company->id,
                    'company_name' => $company->company,
                    'company_code' => $company->cf1,
                    'email' => $company->user_email 
                ];
            $set = $this->subscription_model->set_distributor($data);
            if($set){
                $res['message'] = lang('set_company_success');
                $res['notif'] = 'success';
            }else{
                $res['message'] = lang('set_company_failed');
                $res['notif'] = 'danger';
            }
        }
        else{
            $res['message'] = lang('selected_company_is_exist');
            $res['notif'] = 'danger';
        }
        echo json_encode($res);
    }

    public function delete_distributor()
    {   
        $id = $this->input->post('id', TRUE);
        if($id){
            $set = $this->subscription_model->delete_set_distributor($id);
        }
        else{
            $set = $this->subscription_model->empty_table('billing_company_set');
        }

        if($set){
            $res['message'] = lang('delete_company_success');
            $res['notif'] = 'success';
        }else{
            $res['message'] = lang('delete_company_failed');
            $res['notif'] = 'danger';
        }
        echo json_encode($res);
    }

    public function import_billing()
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', '4096M');
        
        $this->load->helper('security');
        $this->form_validation->set_rules('import_file', lang("import_file"), 'xss_clean');
        $file_name = '';

        if ($this->form_validation->run() == true) {
            if (isset($_FILES["import_file"])) {
                /*$this->load->library('upload');
                $path                     = 'assets/uploads/csv/';
                $config['upload_path']    = $path;
                $config['allowed_types']  = 'xlsx|xls';
                $config['max_size']       = '2000';
                $config['overwrite']      = false;
                $config['encrypt_name']   = true;

                $this->upload->initialize($config);
                if (!$this->upload->do_upload('import_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect(site_url(). 'billing_portal/subscription/set_billing');
                }
                $csv = $this->upload->file_name;*/

                $uploadedImg = $this->integration->upload_files($_FILES['import_file']);
                $file_name = $uploadedImg->url;
                $insert = [];
                /*$reader = ReaderEntityFactory::createReaderFromFile($path . $file_name);
                $reader->open($path . $file_name); //open file xlsx*/
                $reader = ReaderEntityFactory::createReaderFromFile($file_name);
                $reader->open($file_name); //open file xlsx
                foreach ($reader->getSheetIterator() as $sheet)
                {
                    $numRow = 1;
                    foreach ($sheet->getRowIterator() as $row)
                    {
                        $data = [];     
                        if ($numRow > 1){
                            foreach ($row->getCells() as $cell) {
                                $data[] = $cell->getValue();
                            }
                        }
                        $numRow++;
                        $insert[] = $data;
                    }
                }
                $reader->close();

                $field = [];    $adjust_period = [];    $start_kosong = [];
                foreach ($insert as $k => $v) {
                    if(count($v) > 0){
                        $plan_id = $v[5];
                        if($plan_id == 2){
                            if($v[7] != ''){
                                if(is_object($v[7])){
                                    $start = $v[7]->format('Y-m-d');
                                }
                                else{
                                    $start = $v[7];
                                }
                            }

                            if($v[8] != ''){
                                if(is_object($v[8])){
                                    $exp = $v[8]->format('Y-m-d');
                                }
                                else{
                                    $exp = $v[8];
                                }
                            }

                            $payment = $v[11];
                            $end = strtotime('+'.$payment.' months', strtotime($start));
                            $str_end = strtotime($exp);

                            if($str_end != $end){
                                $adjust_period[] = $v[0];
                            }
                        }
                        $field[]= [
                            'date'              => date('Y-m-d H:i:s'),
                            'company_id'        => $v[0],
                            'company_name'      => $v[1],
                            'company_code'      => $v[2],
                            'email'             => $v[3],
                            'created_at'        => $v[4],
                            'plan_id'           => $v[5],
                            'plan_name'         => $v[6],
                            'start_date'        => $start,
                            'expired_date'      => $exp,
                            'user'              => $v[9],
                            'warehouse'         => $v[10],
                            'payment_period'    => $v[11],
                            'payment_done'      => $v[12],
                            'send_email'        => $v[13]
                        ];
                    }
                }

                if(count($adjust_period) > 0){
                    $id = '';
                    foreach ($adjust_period as $v) {
                        $id .=  $v.', &nbsp; ';
                    }
                    $print = 'Company id : <br>'.$id.'  <br>Start Date/Expired date harus sesuai dengan masa Payment period';
                    $this->session->set_flashdata('error', $print);
                    // unlink($path . $file_name);
                    unlink($file_name);
                    redirect(site_url(). 'billing_portal/subscription/set_billing');
                }
                else{
                    $import = $this->import_billing_add($field);
                    if($import){
                        $this->session->set_flashdata('message', lang('set_billing_success'));
                        redirect(site_url().'billing_portal/subscription/set_billing');
                    }
                    else{
                        $this->db->trans_rollback();
                        $this->session->set_flashdata('error', lang('set_billing_failed'));
                        redirect(site_url().'billing_portal/subscription/set_billing');
                    }
                }
            }
        }
    }

    public function import_billing_add($from_import)
    {
        $this->db->trans_begin();
        try {
            $set_false = [];    $user_set = false;    $wh_set = false;  $data_email = [];
            foreach ($from_import as $k => $val) {
                $plan_id = $val['plan_id'];
                $start_date = $val['start_date'];
                $end_date = $val['expired_date'];
                $user = $val['user'];
                $warehouse = $val['warehouse'];
                $payment = $val['payment_period'];
                $payment_done = $val['payment_done'];
                $send_email = $val['send_email'];
                $company_id = $val['company_id'];
                $company_name = $val['company_name'];
                $email = $val['email'];

                $start = strtotime($start_date);
                $end = strtotime('+'.$payment.' months', $start);
                $str_end = strtotime($end_date);

                $where_addon_user = ['name' => 'user'];
                $get_addon_user = $this->subscription_model->getAddons_row($where_addon_user);
                $where_addon_wh = ['name' => 'warehouse'];
                $get_addon_wh = $this->subscription_model->getAddons_row($where_addon_wh);
                $where_author = ['company_id' => $company_id];
                $authorize = $this->subscription_model->getAuthor_row($where_author);
                $where_plan = ['id' => $plan_id];
                $get_plan = $this->subscription_model->getPlan_row($where_plan);

                if($plan_id != 1){
                    if($user != ''){
                        $jml_user_set = $user;
                        $price_user_master = $get_addon_user->price;
                        $id_addon_user = $get_addon_user->id;
                        $name_addon_user = $get_addon_user->name;
                        $user_set = true;
                    }
                    else{
                        $price_user_master = $get_addon_user->price;
                        $id_addon_user = $get_addon_user->id;
                        $name_addon_user = $get_addon_user->name;
                    }

                    if($warehouse != ''){
                        $jml_warehouse_set = $warehouse;
                        $price_warehouse_master = $get_addon_wh->price;
                        $id_addon_warehouse = $get_addon_wh->id;
                        $name_addon_warehouse = $get_addon_wh->name;
                        $wh_set = true;
                    }
                    else{
                        $price_warehouse_master = $get_addon_wh->price;
                        $id_addon_warehouse = $get_addon_wh->id;
                        $name_addon_warehouse = $get_addon_wh->name;
                    }

                    if(strtotime($authorize->expired_date) > $str_end){
                        $end_date = $authorize->expired_date;
                    }

                    $kelipatan = 5;
                    $date = date('Y-m-d H:i:s');
                    $reference = $this->site->getReference('binv', $company_id);

                    //======= menghitung jumlah user & penetapan harganya
                    if($user_set == true){
                        $jml_user = $jml_user_set;
                    } else{
                        if($author_user > $plan_user){
                            $jml_user = $author_user - $plan_user;
                        } else{
                            $jml_user = 0;
                        }
                    }

                    if($jml_user > 0){
                        for ($i = $jml_user; $i < $kelipatan+$jml_user ; $i++) { 
                            if($i % $kelipatan == 0){
                                $qty_user = $i;
                                break;
                            }
                        }
                        $price_user = $price_user_master * ($qty_user / $kelipatan);
                    } else{
                        $price_user = 0;
                    }
                    
                    //======= menghitung jumlah warehouse & penetapan harganya
                    if($wh_set == true){
                        $jml_wh = $jml_warehouse_set;
                    }else{
                        if($author_user > $plan_user){
                            $jml_wh = $author_wh - $plan_wh;
                        } else{
                            $jml_wh = 0;
                        }
                    }

                    if($jml_wh > 0){
                        for ($i = $jml_wh; $i < $kelipatan+$jml_wh ; $i++) { 
                            if($i % $kelipatan == 0){
                                $qty_wh = $i;
                                break;
                            }
                        }
                        $price_warehouse = $price_warehouse_master * ($qty_wh / $kelipatan);
                    } else{
                        $price_warehouse = 0;
                    }

                    $subtotal_invoice = $get_plan->price + $price_warehouse + $price_user;
                    $total_invoice = $subtotal_invoice * $payment;

                    if($payment_done == 1){
                        $data_invoice = array(
                            'date' => $date,
                            'plan_id' => $get_plan->id,
                            'plan_name' => $get_plan->name,
                            'payment_period' => $payment,
                            'price' => $get_plan->price,
                            'subtotal' => $subtotal_invoice,
                            'total' => $total_invoice,
                            'company_name' => $company_name,
                            'company_id' => $company_id,
                            'created_by' => $this->session->userdata('user_id'),
                            'authorized_id' => $authorize->id,
                            'reference_no' => $reference,
                            'due_date' => date('Y-m-d H:i:s', strtotime($date) + (60 * _PAYMENT_TERM)),
                            'payment_status' => 'paid',
                            'billing_status' => 'active',
                            'start_date' => $start_date,
                            'end_date' => $end_date
                        );
                    }
                    else{
                        $data_invoice = array(
                            'date' => $date,
                            'plan_id' => $get_plan->id,
                            'plan_name' => $get_plan->name,
                            'payment_period' => $payment,
                            'price' => $get_plan->price,
                            'subtotal' => $subtotal_invoice,
                            'total' => $total_invoice,
                            'company_name' => $company_name,
                            'company_id' => $company_id,
                            'created_by' => $this->session->userdata('user_id'),
                            'authorized_id' => $authorize->id,
                            'reference_no' => $reference,
                            'due_date' => date('Y-m-d H:i:s', strtotime($date) + (60 * _PAYMENT_TERM)),
                            'payment_status' => 'pending',
                            'billing_status' => 'pending',
                            'start_date' => $start_date,
                            'end_date' => $end_date
                        );
                    }
                        
                    $data_item = [
                        [
                            'addon_id'      => $id_addon_user,
                            'addon_name'    => $name_addon_user,
                            'price'         => $price_user_master,
                            'quantity'      => $jml_user,
                            'subtotal'      => $price_user
                        ],
                        [
                            'addon_id'      => $id_addon_warehouse,
                            'addon_name'    => $name_addon_warehouse,
                            'price'         => $price_warehouse_master,
                            'quantity'      => $jml_wh,
                            'subtotal'      => $price_warehouse
                        ]
                    ];

                    $set_billing = $this->subscription_model->setBillingInv($data_invoice, $data_item, $authorize);
                    if($set_billing){
                        if($send_email == 1){
                            $data_email[] = [
                                'company_name'=>$company_name,
                                'plan_name'=> $get_plan->name,
                                'plan_id'=> $plan_id,
                                'additional_user'=> $jml_user,
                                'additional_warehouse'=> $jml_wh,
                                'payment_period'=> $payment,
                                'payment_status'=> $data_invoice['payment_status'],
                                'start_date'=> $start_date,
                                'expired_date'=> $end_date,
                                'email' => $email
                            ];
                        }
                    }
                    else{
                        $set_false[] = $company_id;
                    }
                }
                else{
                    $where_plan = ['id' => $plan_id];
                    $get_plan = $this->subscription_model->getPlan_row($where_plan);

                    $data_author = [
                        'company_id'=>$company_id,
                        'company_name'=>$company_name,
                        'users' => 2,
                        'warehouses' => 1,
                        'plan_name'=> $get_plan->name,
                        'plan_id'=> $plan_id,
                        'status' => null,
                        'start_date' => null,
                        'expired_date' => null
                    ];
                    $where_author = ['company_id' => $company_id];
                    $authorize = $this->subscription_model->getAuthor_row($where_author);

                    $set_billing = $this->subscription_model->setBillingInv($data_author, null, $authorize);
                    if($set_billing){
                        if($send_email == 1){
                            $data_email[] = [
                                'company_name'=>$company_name,
                                'plan_name'=> $get_plan->name,
                                'plan_id'=> $plan_id,
                                'additional_user'=> 0,
                                'additional_warehouse'=> 0,
                                'payment_period'=> 0,
                                'payment_status'=> '-',
                                'start_date'=> '-',
                                'expired_date'=> '-',
                                'email' => $email
                            ];
                        }
                    }
                    else{
                        $set_false[] = $company_id;
                    }
                }
            }

            if(count($set_false) > 0){
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', lang('set_billing_failed'));
                redirect(site_url().'billing_portal/subscription/set_billing');
            }

            $this->db->trans_commit();
            if(count($data_email) > 0){
                $send = $this->send_email($data_email);
                if($send){
                    $this->subscription_model->empty_table('billing_company_set');
                    $this->session->set_flashdata('message', lang('set_billing_success'));
                    redirect(site_url().'billing_portal/subscription/set_billing');
                }
                else{
                    $this->session->set_flashdata('message', lang('set_billing_success'));
                    redirect(site_url().'billing_portal/subscription/set_billing');
                }
            }
            else{
                $this->subscription_model->empty_table('billing_company_set');
                $this->session->set_flashdata('message', lang('set_billing_success'));
                redirect(site_url().'billing_portal/subscription/set_billing');
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(site_url().'billing_portal/subscription/set_billing');
        }
    }

    public function export_company_author()
    {
        if ($this->AdminBilling) {
            $writer = \Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
            $writer->setShouldCreateNewSheetsAutomatically(true);

            $filename = 'company_' . date('Y_m_d');
            $writer->openToBrowser($filename . '.xlsx');
            $header = [
                lang('company_id'),
                lang('company'),
                lang('company_code'),
                lang('email'),
                lang('created_at'),
                lang('plan_id'),
                lang('plan_name'),
                lang('start_date'),
                lang('expired_date'),
                lang('additional_user'),
                lang('additional_warehouse'),
                lang('payment_period'),
                lang('payment_(done)'),
                lang('send_email')
            ];
            $write_header = WriterEntityFactory::createRowFromArray($header);
            $writer->addRow($write_header);

            $load_data = $this->subscription_model->getCompanyresult();

            foreach ($load_data as $val) {
                $start = ($val->start_date == '') ? '' : date("Y-m-d", strtotime($val->start_date));
                $exp = ($val->expired_date == '') ? '' : date("Y-m-d", strtotime($val->expired_date));
                $created = ($val->create_on == '') ? '' : $val->create_on;

                $my_data = [
                    $val->id,
                    $val->company,
                    $val->cf1,
                    $val->user_email,
                    $created,
                    $val->plan_id,
                    $val->plan_name,
                    $start,
                    $exp,
                    $val->users,
                    $val->warehouses,
                    $val->payment_period,
                    $val->payment_done,
                    $val->send_email
                ];
                $write_data = WriterEntityFactory::createRowFromArray($my_data);
                $writer->addRow($write_data);
            }
            $writer->close();
        }
        else{
            redirect(site_url().'billing_portal/subscription');
        }
    }

    public function get_endDate_byPeriod()
    {
        $payment = $this->input->post('payment');
        $start_date = $this->input->post('start_date');
        $end = date('Y-m-d', strtotime('+'.$payment.' months', strtotime($start_date)));

        echo $end;
    }
}

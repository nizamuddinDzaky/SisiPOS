<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Snap extends MY_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */


    public function __construct()
    {
        parent::__construct();
        
        $params = array('server_key' => SB_SERVER, 'production' => false);
        $this->load->library('midtrans');
        $this->lang->load('auth', $this->Settings->user_language);
        $this->midtrans->config($params);
        $this->load->helper('url');
        $this->load->model('auth_model');
        $this->load->model('curl_model', '_curl');
        $this->load->library('form_validation');
        // $this->insertLogActivities();
    }

    public function index()
    {
        $this->load->view('checkout_snap');
    }

    public function token()
    {
        $id = $this->input->get('id', true);
        $billing = $this->site->getBillInvByID($id);
        $items = $this->site->getBillingInvItem($id);
        $user = $this->site->getUser();
        $company = $this->site->getCompanyByID($this->session->userdata('company_id'));
        $payment = $this->auth_model->getPaymentByBID($id);

        // Required
        $transaction_details = array(
          'order_id' => $id,
          'gross_amount' => (int)$billing->total, // no decimal allowed for creditcard
//            'gross_amount' => 93000, // no decimal allowed for creditcard
        );

        // Optional
        $item_details[]=array(
            'id'=>strtotime($billing->date).$billing->id.$billing->plan_id,
            'price' => (int)$billing->price,
            'quantity' => 1,
            'name' => 'Plan : '.$billing->plan_name
        );
        
        if (!empty($items)) {
            foreach ($items as $item) {
                $item_details[]=array(
                    'id' => strtotime($billing->date).$billing->id.$item->id,
                    'price' => (int)$item->price,
                    'quantity' => (int)$item->quantity,
                    'name' => $item->addon_name
                );
            }
        }
        
        // Optional
        //		$item1_details = array(
        //		  'id' => 'a1',
        //		  'price' => 18000,
        //		  'quantity' => 3,
        //		  'name' => "Apple"
        //		);
//
        //		// Optional
        //		$item2_details = array(
        //		  'id' => 'a2',
        //		  'price' => 20000,
        //		  'quantity' => 2,
        //		  'name' => "Orange"
        //		);
//
        //		// Optional
        //		$item_details = array ($item1_details, $item2_details);

        // Optional
        $billing_address = array(
          'first_name'    => $user->first_name,
          'last_name'     => $user->last_name,
          'address'       => $user->address,
          'city'          => $user->city,
          'postal_code'   => $company->postal_code,
          'phone'         => $user->phone,
          'country_code'  => 'IDN'
        );

        // Optional
//        $shipping_address = array(
//          'first_name'    => "Obet",
//          'last_name'     => "Supriadi",
//          'address'       => "Manggis 90",
//          'city'          => "Jakarta",
//          'postal_code'   => "16601",
//          'phone'         => "08113366345",
//          'country_code'  => 'IDN'
//        );

        // Optional
        $customer_details = array(
          'first_name'    => $user->first_name,
          'last_name'     => $user->last_name,
          'email'         => $user->email ? $user->email : null,
          'phone'         => $user->phone ? $user->phone : null,
          'billing_address'  => $billing_address,
          'shipping_address' => $billing_address
        );

        // Data yang akan dikirim untuk request redirect_url.
        $credit_card['secure'] = true;
        //ser save_card true to enable oneclick or 2click
        //$credit_card['save_card'] = true;

        $time = time();
        $custom_expiry = array(
            'start_time' => date("Y-m-d H:i:s O", $time),
            'unit' => 'minute',
            'duration'  => _PAYMENT_TERM,
        );
        
        $callbacks=array(
            'finish' => base_url().'snap/finish/',//'https://demo.midtrans.com/',
        );
        
        

        $transaction_data = array(
            'transaction_details'=> $transaction_details,
            'item_details'       => $item_details,
            'customer_details'   => $customer_details,
            'credit_card'        => $credit_card,
            'callbacks'          => $callbacks,
            'expiry'             => $custom_expiry,
            'enabled_payments'   => array('credit_card','mandiri_clickpay','bca_klikbca','echannel','permata_va','bca_va','bni_va','other_va','gopay','indomaret','telkomsel_cash','xl_tunai'),
        );

        error_log(json_encode($transaction_data));
        $snapToken = $this->midtrans->getSnapToken($transaction_data);
        error_log($snapToken);

        $return = array('token'=>$snapToken, 'billing_invoice'=>$payment);
        $this->sma->send_json($return);
    }

    public function finish()
    {
        $result = json_decode($this->input->post('result_data'));
        $url = 'https://api.sandbox.midtrans.com/v2/'.$result->order_id.'/status';
        $header=array(
            'Authorization: Basic '.base64_encode(SB_SERVER.':'),
            'Accept: application/json',
            'Content-Type: application/json');
        $transaction =$this->_curl->_get($url, $header);
        $json=json_decode($transaction, false);
        
//        var_dump($url, $header, $transaction, $json);die();
        
        $billing_id = $this->input->post('billing_id');
        $status_flashdata='warning';
        if ($json->status_code == '200') {
            $data=array(
                'date' => date('Y-m-d H:i:s'),
                'reference_no' => $this->site->getReference('bpay'),
                'paid_by' => $json->payment_type,
                'amount' => $json->gross_amount,
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
                'company_id' => $this->session->userdata('company_id'),
                'billing_id' => $billing_id
            );
            $status_flashdata='message';
        } elseif ($json->status_code == '201') {
            $data=array(
                'date' => date('Y-m-d H:i:s'),
                'reference_no' => $this->site->getReference('bpay'),
                'paid_by' => $json->payment_type,
                'amount' => $json->gross_amount,
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'pending',
                'company_id' => $this->session->userdata('company_id'),
                'billing_id' => $billing_id
            );
            $status_flashdata='warning';
        } else {
            $this->session->set_flashdata('error', lang('failed_payment_subscription'));
        }

        if ((sizeof($data)>0) && $this->auth_model->addPaymentBillingInv($data)) {
            $this->session->set_flashdata(
                $status_flashdata,
                lang('success_payment_subscription').'. '.$json->status_message.'. '.($status_flashdata=='warning'?lang('plz_pay_immediately'):'')
            );
        } else {
            $this->session->set_flashdata('warning', lang('failed_insert'));
        }
        
        redirect($_SERVER["HTTP_REFERER"]);
        
//        $result = json_decode($this->input->post('result_data'));
//    	echo 'RESULT <br><pre>';
//    	var_dump($result);
//    	echo '</pre>' ;
    }
    
    public function confirm_payment($id=null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('id', 'error coba lagi', 'required');

            if ($this->form_validation->run() == true) {
                $url = 'https://api.sandbox.midtrans.com/v2/'.$this->input->post('id').'/status';
                $header=array(
                    'Authorization: Basic '.base64_encode(SB_SERVER.':'),
                    'Accept: application/json',
                    'Content-Type: application/json');
                $transaction =$this->_curl->_get($url, $header);
                $json=json_decode($transaction, false);
            }
            
            if ($this->form_validation->run() == true && $this->auth_model->confirmPaymentBilling($json)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang('confirm_successful'));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                if ($this->input->post('payments_confirmation')) {
                    $this->session->set_flashdata('error', lang('billing_not_paid'));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'auth/confirm_payment', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
}

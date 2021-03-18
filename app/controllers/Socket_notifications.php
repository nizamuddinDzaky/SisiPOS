<?php defined('BASEPATH') or exit('No direct script access allowed');

class Socket_notifications extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        $this->lang->load('notifications', $this->Settings->user_language);
    }

    public function index(){
        
    }

    public function getNotifications(){
        $company_id = $this->input->get('company_id');

        $count = $this->db->get_where('socket_notifications', ['company_id' => $company_id, 'is_deleted' => null]);
		$this->load->library('pagination');
		$config['base_url'] = base_url().'socket_notifications/getNotifications?company_id='.$company_id.'&row='.($this->input->get('row')+5);
		$config['total_rows'] = $count->num_rows();
		$config['per_page'] = 5;
		$from = $this->input->get('row') && $this->input->get('row') >= 5 ? $this->input->get('row') : 0;
        $this->pagination->initialize($config);
        $this->db->order_by('id DESC');
        $result = $this->db->get_where('socket_notifications', ['company_id' => $company_id, 'is_deleted' => null], $config['per_page'], $from);
        $notification_message = [];
        foreach ($result->result_array() as $notification) {
            $notification_message[] = $this->createMessageNotification($notification);
        }

        $json = [
            'status' => 'success',
            'next_url' => $config['base_url'],
            'previous_url' => $config['base_url'],
            'total_rows' => $result->num_rows(),
            'total_unread' => $this->db->get_where('socket_notifications', ['company_id' => $company_id, 'is_read' => '0', 'is_deleted' => null])->num_rows(),
            'data' => $notification_message
        ];
        
        echo json_encode($json);
    }

    public function setReadNotification(){
        $id = $this->input->get('id');
        $result = $this->db->update('socket_notifications', ['is_read' => '1', 'updated_at'=> date('Y-m-d H:i:s')], array('id' => $id));
        
        if($result){
            echo true;
        }else{
            echo false;
        }
    }

    public function setReadAllNotification(){
        $company_id = $this->input->get('company_id');
        $result = $this->db->update('socket_notifications', ['is_read' => '1', 'updated_at'=> date('Y-m-d H:i:s')], array('company_id' => $company_id));
        
        if($result){
            echo true;
        }else{
            echo false;
        }
    }
    
    
    public function createMessageNotification($data){
        $notification_message['id']                        = $data['id'];
        $notification_message['company_id']                = $data['company_id'];
        $notification_message['transaction_id']            = explode('-', $data['transaction_id'])[1];
        $notification_message['transaction_type']          = explode('-', $data['transaction_id'])[0];
        $notification_message['transaction_delivery_id']   = explode('-', $data['transaction_id'])[2] ?? '';
        $notification_message['title']                     = lang($data['type'].'_title');
        $notification_message['message']                   = lang($data['type'].'_'.$data['to']);
        $notification_message['is_read']                   = $data['is_read'];
        $notification_message['date']                      = $this->sma->hrld($data['created_at']);
        
        $notification_message['message'] = str_replace('[[customer_name]]', $data['customer_name'], $notification_message['message']);
        $notification_message['message'] = str_replace('[[reference_no]]', $data['reference_no'], $notification_message['message']);
        $notification_message['message'] = str_replace('[[price]]', number_format($data['price'], '0','.','.'), $notification_message['message']);
        $notification_message['message'] = str_replace('[[note]]', $data['note'], $notification_message['message']);

        return $notification_message;
    }
}

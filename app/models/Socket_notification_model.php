<?php defined('BASEPATH') or exit('No direct script access allowed');

class Socket_notification_model extends CI_Model
{

    public $new_notif, $send_to_company_id, $socket_notification_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function addNotification($data){
        $result = $this->db->insert('socket_notifications', $data);
        
        $this->session->set_flashdata('new_notif', 'New Notification');
        $this->session->set_flashdata('send_to_company_id', $data['company_id']);
        $this->session->set_flashdata('socket_notification_id', $this->db->insert_id());

        $this->new_notif = 'New Notification';
        $this->send_to_company_id = $data['company_id'];
        $this->socket_notification_id = $this->db->insert_id();
        
        return $result ? true : false;
    }
}
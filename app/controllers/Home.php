<?php defined('BASEPATH') or exit('No direct script access allowed');

class Home extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        if (get_domain() == AKSESTOKO_DOMAIN) {
            redirect(aksestoko_route('aksestoko/home'));
        }
        if ($this->loggedIn) {
            redirect('welcome');
        }
        $this->load->model('integration_model', 'integration');
        $this->data['mobile_android_ps'] = $this->integration->findApiIntegrationByType('mobile_android_ps', false);
        $this->data['mobile_android_as'] = $this->integration->findApiIntegrationByType('mobile_android_as', false);
        $this->load->view($this->theme . 'home_page', $this->data);
    }
}

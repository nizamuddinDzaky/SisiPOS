<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class helps extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('help_model');
    }

    public function index()
    {
        $this->data['menu_image']   = $this->help_model->getHomeMenu();
        $bc                         = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Helps')));
        $meta                       = array('page_title' => lang('Helps'), 'bc' => $bc);
        $this->page_construct_helps_land('index', $meta, $this->data);
    }

    public function article($id = null)
    {
        $this->data['cms_faq_pos'] = $this->help_model->getActivationCmsFaq($id);
        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $this->data['cms_faq_pos']->caption, $image);
        $default_image = $this->data['assets'] . "images/Logo.png";
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Helps')));
        $meta = [
            'page_title' => $this->data['cms_faq_pos']->title . " | " . lang('Helps'), 
            'bc' => $bc,
            'caption' => $this->data['cms_faq_pos']->caption,
            'first_image' => $image['src'] == null || $image['src'] == '' ? $default_image : $image['src'] 
        ];

        $this->page_construct_helps('article', $meta, $this->data);
    }

    public function search_menu()
    {
        $menu = $this->help_model->getMenuActiveByGroupId();
        foreach ($menu as $key) {
            $menu[$key]->id;
            $menu[$key]->title;
        }
        echo json_encode($menu);
    }
}

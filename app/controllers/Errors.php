<?php defined('BASEPATH') or exit('No direct script access allowed');

class Errors extends MY_Controller
{
    public function error_404()
    {
        $data['title'] = lang('error_404');
        $data['site_url'] = base_url();
        $data['site_name'] = $this->Settings->site_name;
        $data['assets'] = $this->data['assets'];
        //$this->load->view('default/views/errors/error_404', $data); // For errors style
        $this->load->view('default/views/errors/animated', $data); // For circle 404 page
        //$this->load->view('default/views/errors/rectangle', $data); // For rectangle 404 page
    }
    public function error_404_at()
    {
        $data['title'] = lang('error_404');
        $data['site_url'] = base_url();
        $data['site_name'] = $this->Settings->site_name;
        $data['assets'] = $this->data['assets'];
        $data['assets_at'] = $this->data['assets_at'];
        //$this->load->view('default/views/errors/error_404', $data); // For errors style
        $this->load->view('default/views/errors/animated_at', $data); // For circle 404 page
        //$this->load->view('default/views/errors/rectangle', $data); // For rectangle 404 page
    }

    public function show_error()
    {
        show_error('This is general error message.');
    }
}

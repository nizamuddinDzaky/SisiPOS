<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Image extends REST_Controller
{
    public function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        
        $this->load->library('ion_auth');
        $this->load->model('auth_model');
        $this->load->model('integration_model');

        $this->Settings = $this->site->get_setting();
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
    }
    
    public function index_get()
    {
        $this->set_response([
            'status' => false,
            'message' => 'User could not be found'
        ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response codes
    }
    
    public function index_post()
    {
        foreach (getallheaders() as $header => $value) {
            if ($header=="Authorization") {
                $auth= explode(' ', $value);
            }
        }
        $result=explode(':', base64_decode($auth[1]));
        $username=$result[0];
        $password=$result[1];
//        $username=$this->post('username');
//        $password=$this->post('password');
        
        $this->load->library('upload');
        if ($this->ion_auth->login($username, $password)) {
            if ($_FILES['product_image']['size'] > 0) {
                /*$config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $this->upload->file_name=$_FILES['product_image']['name'];
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->set_response(array(
                        'status' => $this->http_status_codes[REST_Controller::HTTP_INTERNAL_SERVER_ERROR],
                        'code' => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => 'image.NOT_FOUND_IMAGE',
                        'data' => $error,
                    ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    $photo = $this->upload->file_name;
                    $data['image'] = $photo;
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->upload_path . $photo;
                    $config['new_image'] = $this->thumbs_path . $photo;
                    $config['maintain_ratio'] = true;
                    $config['width'] = $this->Settings->twidth;
                    $config['height'] = $this->Settings->theight;
                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);
                    if (!$this->image_lib->resize()) {
                        $error_imagelib=$this->image_lib->display_errors();
                        $this->set_response(array(
                            'status' => $this->http_status_codes[REST_Controller::HTTP_INTERNAL_SERVER_ERROR],
                            'code' => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                            'message' => 'image.NOT_FOUND_IMAGE',
                            'data' => $error_imagelib,
                        ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    if ($this->Settings->watermark) {
                        $this->image_lib->clear();
                        $wm['source_image'] = $this->upload_path . $photo;
                        $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                        $wm['wm_type'] = 'text';
                        $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                        $wm['quality'] = '100';
                        $wm['wm_font_size'] = '16';
                        $wm['wm_font_color'] = '999999';
                        $wm['wm_shadow_color'] = 'CCCCCC';
                        $wm['wm_vrt_alignment'] = 'top';
                        $wm['wm_hor_alignment'] = 'right';
                        $wm['wm_padding'] = '10';
                        $this->image_lib->initialize($wm);
                        $this->image_lib->watermark();
                    }
                    $this->set_response(array(
                        'status' => $this->http_status_codes[REST_Controller::HTTP_OK],
                        'code' => REST_Controller::HTTP_OK,
                        'message' => 'image.SUCCESS',
                        'data' => new stdClass(),
                    ));
                }
                $this->image_lib->clear();*/

                $uploadedImg = $this->integration_model->upload_files($_FILES['product_image']);
                if(!$uploadedImg){
                    $this->set_response(array(
                        'status' => $this->http_status_codes[REST_Controller::HTTP_INTERNAL_SERVER_ERROR],
                        'code' => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => 'image.NOT_FOUND_IMAGE',
                        'data' => new stdClass(),
                    ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    $photo          = $uploadedImg->url;
                    $data['image']  = $photo;
                }
                $config = null;
            } else {
                $this->set_response(array(
                    'status' => $this->http_status_codes[REST_Controller::HTTP_NOT_FOUND],
                    'code' => REST_Controller::HTTP_NOT_FOUND,
                    'message' => 'image.NOT_FOUND_IMAGE',
                    'data' => new stdClass(),
                ));
            }

            if ($_FILES['userfile']['name'][0] != "") {
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $uploadedImg = $this->integration_model->upload_files($_FILES['userfile']);
                    if(!$uploadedImg){
                        $this->set_response(array(
                            'status' => $this->http_status_codes[REST_Controller::HTTP_INTERNAL_SERVER_ERROR],
                            'code' => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                            'message' => 'image.ERROR',
                            'data' => new stdClass(),
                        ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    } else {
                        $photo      = $uploadedImg->url;
                        $photos[]   = $photo;
                    }

                    /*$this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->set_response(array(
                            'status' => $this->http_status_codes[REST_Controller::HTTP_INTERNAL_SERVER_ERROR],
                            'code' => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                            'message' => 'image.ERROR',
                            'data' => $error,
                        ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            $error_imagelib=$this->image_lib->display_errors();
                            $this->set_response(array(
                                'status' => $this->http_status_codes[REST_Controller::HTTP_INTERNAL_SERVER_ERROR],
                                'code' => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                                'message' => 'image.ERROR',
                                'data' => $error_imagelib,
                            ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '10';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'None';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'right';
                            $wm['wm_padding'] = '0';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }*/
                }
                $config = null;
            }
        } else {
            $this->set_response(array(
                'status' => $this->http_status_codes[REST_Controller::HTTP_NOT_FOUND],
                'code' => REST_Controller::HTTP_NOT_FOUND,
                'message' => 'user.NOT_FOUND_USER',
                'data' => new stdClass(),
            ));
        }
    }
}

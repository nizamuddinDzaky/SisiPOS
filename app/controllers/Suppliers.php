<?php defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Suppliers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '50';
        $this->lang->load('suppliers', $this->Settings->user_language);
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->model('authorized_model');
        $this->load->library('form_validation');
        $this->load->model('companies_model');
        $this->load->model('integration_model');
        // $this->insertLogActivities();
    }

    public function index($action = null)
    {
        $this->sma->checkPermissions();

        $link_type = ['mb_suppliers','mb_add_supplier','mb_edit_supplier'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('suppliers')));
        $meta = array('page_title' => lang('suppliers'), 'bc' => $bc);
        $this->page_construct('suppliers/index', $meta, $this->data);
    }

    public function getSuppliers()
    {
        $this->sma->checkPermissions('index');
        $add_user = "<a class=\"tip\" title='" . $this->lang->line("add_user") . "' href='" . site_url('suppliers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-plus-circle\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("id, company, name, email, phone, city, country, vat_no")
            ->from("companies");
        if (!$this->Owner) {
            $this->datatables->where("( company_id = ".$this->session->userdata('company_id')." or company_id = 1 )");
        }
        /*        if(!$this->Owner){
                    $this->datatables
                    ->where('group_id', $this->session->userdata('group_id'))
                    ->where('company_id', $this->session->userdata('company_id'));
                }*/
        // if(!$this->Owner){
        //     $this->datatables->where('company_id', $this->session->userdata('company_id'));
            
        //     if($this->Manager){ // for Manager
        //         $this->datatables->where('manager_area', $this->session->userdata('manager_area'));
        //     }
        //     elseif($this->Supplier){ // for Distributor
        //         $this->datatables
        //             ->where('manager_area', $this->session->userdata('manager_area'))
        //             // ->where('supplier_id', $this->session->userdata('supplier_id'));
        //             ->where('id', $this->session->userdata('supplier_id'));
        //     }
        //     elseif ($this->Reseller) { // for LT
        //         $this->datatables
        //             ->where('manager_area', $this->session->userdata('manager_area'))
        //             // ->where('warehouse_supplier_id', $this->session->userdata('warehouse_supplier_id'))
        //             ->where('id', $this->session->userdata('supplier_id'));
        //     }
        //     elseif($this->Customer){ // for Toko
        //         $this->datatables
        //             ->where('group_id', $this->session->userdata('group_id'))
        //             ->where('warehouse_supplier_id', $this->session->userdata('warehouse_supplier_id'))
        //             ->where('large_id', $this->session->userdata('large_id'))
        //             ->where('supplier_id', $this->session->userdata('supplier_id'));
        //     }
        // }
        $this->datatables
            ->where('group_name', 'supplier')->where('is_deleted', null)
            ->add_column("Actions", "<div class=\"text-center\">".($this->Owner?$add_user:'')." <a class=\"tip\" title='" . $this->lang->line("edit_supplier") . "' id='suppliersEdit' href='" . site_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a class=\"tip\" title='" . $this->lang->line("list_users") . "' href='" . site_url('suppliers/users/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-users\"></i></a>  <!-- Sementara disembunyikan <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_supplier") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme.'suppliers/view', $this->data);
    }

    public function add()
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions(false, true);
        try {
            $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[companies.email]');

            if ($this->form_validation->run('companies/add') == true) {
                
                //nge cek apakah jumlah Suppliers telah limit
                $isLimited = $this->authorized_model->isSupplierLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_master"));
                    $message = str_replace("yyy", lang("suppliers"), $message);

                    $this->session->set_flashdata('error', $message);
                    redirect("suppliers");
                }
                // akhir cek
                
                $this->load->library('upload');
                $data = array('name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'group_id' => '4',
                    'group_name' => 'supplier',
                    'company' => $this->input->post('company'),
                    'company_id' => $this->session->userdata('company_id'),
                    'address' => $this->input->post('address'),
                    'vat_no' => $this->input->post('vat_no'),
                    'country' => $this->input->post('provinsi'),
                    'city' => $this->input->post('kabupaten'),
                    'state' => $this->input->post('kecamatan'),
                    'postal_code' => $this->input->post('postal_code'),
                    'phone' => $this->input->post('phone'),
                    'cf1' => $this->input->post('cf1'),
                    'cf2' => $this->input->post('cf2'),
                    'cf3' => $this->input->post('cf3'),
                    'cf4' => $this->input->post('cf4'),
                    'cf5' => $this->input->post('cf5'),
                    'cf6' => $this->input->post('cf6'),
                );
                
                if ($_FILES['logo']['size'] > 0) {
                    /*$config['upload_path'] = 'assets/uploads/avatars/';
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['max_filename'] = 25;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('logo')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("suppliers");
                    } else {
                        $photo = $this->upload->file_name;
                        $data['logo'] = $photo;
                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = 'assets/uploads/avatars/' . $photo;
                        $config['new_image'] = 'assets/uploads/avatars/thumbs/' . $photo;
                        $config['maintain_ratio'] = true;
                        $config['width'] = 150;
                        $config['height'] = 150;
                        $this->image_lib->clear();
                        $this->image_lib->initialize($config);
                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }
                        $this->image_lib->clear();
                    }*/
                    $uploadedImg    = $this->integration_model->upload_files($_FILES['logo']);
                    $photo          = $uploadedImg->url;
                    $data['logo']   = $photo;
                    $config = null;
                }
            } elseif ($this->input->post('add_supplier')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('suppliers');
            }

            if ($this->form_validation->run() == true && $sid = $this->companies_model->addCompany($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', $this->lang->line("supplier_added"));
                $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : null;
                // redirect($ref[0] . '?supplier=' . $sid);
                redirect($ref[0]);
            } else {
                $this->load->model('daerah_model');
                $this->data['provinsi']=$this->daerah_model->getProv();
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'suppliers/add', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function edit($id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions(false, true);
        try {
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }

            $company_details = $this->companies_model->getCompanyByID($id);
            
            if (!$this->Owner && ($company_details->company_id==1)) {
                $this->session->set_flashdata('warning', $this->lang->line("access_denied"));
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '". $_SERVER["HTTP_REFERER"] ."'; }, 10);</script>");
                //            redirect('login');
            }
            
            if ($this->input->post('email') != $company_details->email) {
                $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');
            }

            if ($this->form_validation->run('companies/add') == true) {
                $data = array('name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'group_id' => '4',
                    'group_name' => 'supplier',
                    'company' => $this->input->post('company'),
                    'address' => $this->input->post('address'),
                    'vat_no' => $this->input->post('vat_no'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country' => $this->input->post('country'),
                    'phone' => $this->input->post('phone'),
                    'cf1' => $this->input->post('cf1'),
                    'cf2' => $this->input->post('cf2'),
                    'cf3' => $this->input->post('cf3'),
                    'cf4' => $this->input->post('cf4'),
                    'cf5' => $this->input->post('cf5'),
                    'cf6' => $this->input->post('cf6'),
                );
                
                $this->load->library('upload');
                if ($_FILES['logo']['size'] > 0) {
                    /*$config['upload_path'] = 'assets/uploads/avatars/';
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['max_filename'] = 25;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('logo')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('message', $error);
                        redirect("suppliers/index");
                    } else {
                        $photo = $this->upload->file_name;
                        $data['logo'] = $photo;
                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = 'assets/uploads/avatars/' . $photo;
                        $config['new_image'] = 'assets/uploads/avatars/thumbs/' . $photo;
                        $config['maintain_ratio'] = true;
                        $config['width'] = 150;
                        $config['height'] = 150;
                        $this->image_lib->clear();
                        $this->image_lib->initialize($config);
                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }
                        $this->image_lib->clear();
                    }*/
                    $uploadedImg    = $this->integration_model->upload_files($_FILES['logo']);
                    $photo          = $uploadedImg->url;
                    $data['logo']   = $photo;
                    $config = null;
                }
            } elseif ($this->input->post('edit_supplier')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            }
            
            if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', $this->lang->line("supplier_updated"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['supplier'] = $company_details;
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'suppliers/edit', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function users($company_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'suppliers/users', $this->data);
    }

    public function add_user($company_id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions(false, true);
        try {
            if ($this->input->get('id')) {
                $company_id = $this->input->get('id');
            }
            $company = $this->companies_model->getCompanyByID($company_id);

            $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[users.email]');
            $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
            $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

            if ($this->form_validation->run('companies/add_user') == true) {
                $active = $this->input->post('status');
                $notify = $this->input->post('notify');
                //            list($username, $domain) = explode("@", $this->input->post('email'));
                $username = strtolower($this->input->post('email'));
                $email = strtolower($this->input->post('email'));
                $password = $this->input->post('password');
                $additional_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
    //                'phone' => $this->input->post('phone'),
                    'phone' => $company->phone,
                    'gender' => $this->input->post('gender'),
                    'company_id' => $company->id,
                    'company' => $company->company,
    //                'group_id' => 3,
                    'group_id' => 4,
                    'city' => $company->city,
                    'state' => $company->state,
                    'country' => $company->country,
                    'address' => $company->address
                );
                $this->load->library('ion_auth');
            } elseif ($this->input->post('add_user')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('suppliers');
            }

            if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', $this->lang->line("user_added"));
                redirect("suppliers");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['company'] = $company;
                $this->load->view($this->theme . 'suppliers/add_user', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function import_csv()
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        try {
            $this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');

            if ($this->form_validation->run() == true) {
                if (DEMO) {
                    $this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if (isset($_FILES["csv_file"])) /* if($_FILES['userfile']['size'] > 0) */ {
                    // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                    $this->load->library('upload');

                    $config['upload_path'] = 'assets/uploads/csv/';
                    $config['allowed_types'] = 'csv';
                    $config['max_size'] = '2000';
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload('csv_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("suppliers");
                    }

                    $csv = $this->upload->file_name;

                    $arrResult = array();
                    $handle = fopen("assets/uploads/csv/" . $csv, "r");
                    if ($handle) {
                        while (($row = fgetcsv($handle, 5001, ",")) !== false) {
                            $arrResult[] = $row;
                        }
                        fclose($handle);
                    }
                    $titles = array_shift($arrResult);

                    $keys = array('company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6');

                    $final = array();

                    foreach ($arrResult as $key => $value) {
                        $final[] = array_combine($keys, $value);
                    }
                    $rw = 2;
                    foreach ($final as $csv) {
                        if ($this->companies_model->getCompanyByEmail($csv['email'])) {
                            $this->session->set_flashdata('error', $this->lang->line("check_supplier_email") . " (" . $csv['email'] . "). " . $this->lang->line("supplier_already_exist") . " (" . $this->lang->line("line_no") . " " . $rw . ")");
                            redirect("suppliers");
                        }
                        $rw++;
                    }
                    foreach ($final as $record) {
                        $record['group_id'] = 4;
                        $record['group_name'] = 'supplier';
                        $data[] = $record;
                    }
                    //$this->sma->print_arrays($data);
                }
            } elseif ($this->input->post('import')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('customers');
            }

            if ($this->form_validation->run() == true && !empty($data)) {
                if (!$this->companies_model->addCompanies($data)) {
                    throw new \Exception("Failed Save");
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', $this->lang->line("suppliers_added"));
                redirect('suppliers');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'suppliers/import', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('companies', $id);
        if ($this->companies_model->deleteSupplier($id)) {
            echo $this->lang->line("supplier_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('supplier_x_deleted_have_purchases'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    public function suggestions($term = null, $limit = null)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $limit = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
        $object =new stdClass();
        $object->id = '1';
        $object->text = "Tunai (Undefined)";
        array_push($rows['results'], $object);
        $this->sma->send_json($rows);
    }

    public function getSupplier($id = null)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => $row->company)));
    }
    
    public function getSupplierById($id = null)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json($row);
    }

    public function supplier_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteSupplier($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("suppliers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('customer'))
                          ->SetCellValue('A1', lang('company'))
                          ->SetCellValue('B1', lang('name'))
                          ->SetCellValue('C1', lang('email'))
                          ->SetCellValue('D1', lang('phone'))
                          ->SetCellValue('E1', lang('address'))
                          ->SetCellValue('F1', lang('city'))
                          ->SetCellValue('G1', lang('state'))
                          ->SetCellValue('H1', lang('postal_code'))
                          ->SetCellValue('I1', lang('country'))
                          ->SetCellValue('J1', lang('vat_no'))
                          ->SetCellValue('K1', lang('scf1'))
                          ->SetCellValue('L1', lang('scf2'))
                          ->SetCellValue('M1', lang('scf3'))
                          ->SetCellValue('N1', lang('scf4'))
                          ->SetCellValue('O1', lang('scf5'))
                          ->SetCellValue('P1', lang('scf6'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $sheet->SetCellValue('A' . $row, $customer->company)
                              ->SetCellValue('B' . $row, $customer->name)
                              ->SetCellValue('C' . $row, $customer->email)
                              ->SetCellValue('D' . $row, $customer->phone)
                              ->SetCellValue('E' . $row, $customer->address)
                              ->SetCellValue('F' . $row, $customer->city)
                              ->SetCellValue('G' . $row, $customer->state)
                              ->SetCellValue('H' . $row, $customer->postal_code)
                              ->SetCellValue('I' . $row, $customer->country)
                              ->SetCellValue('J' . $row, $customer->vat_no)
                              ->SetCellValue('K' . $row, $customer->cf1)
                              ->SetCellValue('L' . $row, $customer->cf2)
                              ->SetCellValue('M' . $row, $customer->cf3)
                              ->SetCellValue('N' . $row, $customer->cf4)
                              ->SetCellValue('O' . $row, $customer->cf5)
                              ->SetCellValue('P' . $row, $customer->cf6);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('C')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray( ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER] );
                    $filename = 'suppliers_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                        $sheet->getDefaultStyle()->applyFromArray($styleArray);
                        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = IOFactory::createWriter($sheet, 'Pdf');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                        header('Cache-Control: max-age=0');

                        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                        ob_end_clean();
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
}

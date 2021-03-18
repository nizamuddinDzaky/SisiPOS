<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Sales_person extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->model('cron_model');
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        $this->lang->load('sales_person_lang', $this->Settings->user_language);
        $this->load->model('sales_person_model');
        $this->load->model('companies_model');
        $this->load->model('integration_model');
        $this->load->library('form_validation');
        // $this->Settings = $this->cron_model->getSettings();
    }

    public function index($action = null)
    {
        $this->sma->checkPermissions();

        $link_type = ['mb_sales_person', 'mb_add_sales_person', 'mb_edit_sales_person'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales_person')));
        $meta = array('page_title' => lang('sales_person'), 'bc' => $bc);
        $this->page_construct('sales_person/index', $meta, $this->data);
    }

    public function getSalesPerson()
    {
        $this->sma->checkPermissions('index');
        $add_user = "<a class=\"tip\" title='" . lang("add_user") . "' id='customersAdd' href='" . site_url('customers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-user-plus\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("id, company, name, email, phone , vat_no, reference_no, is_active")
            ->from("sales_person");
        if (!$this->Owner) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->where('is_deleted', null);
        $this->datatables->edit_column('is_active', '$1__$2', 'is_active, id');
        $this->datatables->add_column("Actions", "$1||" . site_url('sales_person/edit/$2') . '||' . site_url('sales_person/add_customer_to_sales_person/$2'), "is_active, id");

        echo $this->datatables->generate();
    }

    public function add_customer_to_sales_person($sales_person_id)
    {
        $sales_person_detail = $this->sales_person_model->getSalesPersonById($sales_person_id);
        if ($sales_person_detail->is_active == 0) {
            $this->session->set_flashdata('error', lang('inactive_sales_person'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->sma->checkMenuPermissions();
        $this->data['customers'] = $this->companies_model->getCompanyByParent($this->session->userdata('company_id'));
        $this->data['customer_of_sales_person'] = $this->sales_person_model->getCustomerOfSalesPerson($sales_person_id, $this->session->userdata('company_id'));
        $this->data['sales_person'] = $sales_person_detail;
        $this->data['id'] = $sales_person_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales_person'), 'page' => lang('sales_person')),  array('link' => '#', 'page' => lang('add_customer_to_sales_person')));
        $meta = array('page_title' => lang('add_customer_to_sales_person'), 'bc' => $bc);
        $this->page_construct('sales_person/add_customer_to_sales_person', $meta, $this->data);
    }

    public function save_customer_to_sales_person($id)
    {
        $this->db->trans_begin();
        try {
            $this->sales_person_model->updateAllCustomerBySalesPersonId($id);
            foreach ($this->input->post("list_toko") as $key => $value) {
                $data = array();
                $data['sales_person_id'] = $id;
                $data['sales_person_ref'] = $this->input->post('sp_ref');
                if (!$this->companies_model->updateCompany($value, $data)) {
                    throw new \Exception('update failed');
                }
            }
            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang("customer_added"));
            redirect(base_url('sales_person/'));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function getCustomers_sp()
    {
        $add_user = "<a class=\"tip\" title='" . lang("add_user") . "' id='customersAdd' href='" . site_url('customers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-user-plus\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            //            ->select($this->db->dbprefix('companies').".id, {$this->db->dbprefix('companies')}.company, {$this->db->dbprefix('companies')}.name, {$this->db->dbprefix('companies')}.email, {$this->db->dbprefix('companies')}.phone, {$this->db->dbprefix('companies')}.price_group_name, {$this->db->dbprefix('companies')}.customer_group_name, {$this->db->dbprefix('companies')}.vat_no, {$this->db->dbprefix('companies')}.deposit_amount, {$this->db->dbprefix('companies')}.award_points")
            ->select("CONCAT(id, CONCAT('~', company)), company, name, phone, cf1")
            ->from("companies")
            ->where('group_name', 'customer')
            ->where('(sales_person_id IS NULL OR sales_person_id = ' . $this->input->get('id_sp') . ')');
        if (!$this->Owner) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }

        if ($this->input->get('provinsi') != '') {
            $this->datatables->where('country', $this->input->get('provinsi'));
        }

        if ($this->input->get('provinsi') != '') {
            $this->datatables->where('country', $this->input->get('provinsi'));
        }
        // $this->db->get();
        // echo $this->db->last_query();die;
        $this->datatables->where('is_deleted', null);
        echo $this->datatables->generate();
    }

    public function add()
    {
        $this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[sales_person.email]|required');

        $this->form_validation->set_rules('reference_no', lang('referral_code'), 'is_unique[sales_person.reference_no]|required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'company' => $this->session->userdata('company_name'),
                'company_id' => $this->session->userdata('company_id'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('kabupaten'),
                'state' => $this->input->post('kecamatan'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('provinsi'),
                'phone' => $this->input->post('phone'),
                'reference_no' => $this->input->post('reference_no'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            );

            $this->load->library('upload');
            if ($_FILES['photo']['size'] > 0) {
                /*$config['upload_path'] = 'assets/uploads/avatars/';
                $config['allowed_types'] = 'gif|jpg|jpeg|png|tif';
                $config['max_size'] = '1000';
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['photo'] = $photo;
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
                $this->image_lib->clear();*/
                $uploadedImg    = $this->integration_model->upload_files($_FILES['photo']);
                $data['photo']  = $uploadedImg->url;
                $config = null;
            }
        } elseif ($this->input->post('add_sales_person')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true) {
            try {
                $this->db->trans_begin();
                if (!$this->sales_person_model->addSalesPerson($data)) {
                    throw new \Exception(lang('failed_save_sales_person'));
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("sales_person_added"));
                // $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : null;
                // redirect($ref[0] . '?customer=' . $cid);
                redirect($_SERVER["HTTP_REFERER"]);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['reference_no'] = 'SP-' . substr(code_generator(), 0, 6);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales_person/add', $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $sales_person_detail = $this->sales_person_model->getSalesPersonById($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');
            $this->data['a'] = true;
        }

        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'company' => $this->session->userdata('company_name'),
                'company_id' => $this->session->userdata('company_id'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('kabupaten'),
                'state' => $this->input->post('kecamatan'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('provinsi'),
                'phone' => $this->input->post('phone'),
                'reference_no' => $this->input->post('reference_no'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            );
            $this->load->library('upload');
            if ($_FILES['photo']['size'] > 0) {
                /*$config['upload_path'] = 'assets/uploads/avatars/';
                $config['allowed_types'] = 'gif|jpg|jpeg|png|tif';
                $config['max_size'] = '1000';
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['photo'] = $photo;
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
                $this->image_lib->clear();*/
                $uploadedImg    = $this->integration_model->upload_files($_FILES['photo']);
                $data['photo']  = $uploadedImg->url;
                $config = null;
            }
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true) {
            try {
                $this->db->trans_begin();
                // var_dump($this->sales_person_model->updateSalesPerson($id, $data));die;
                if (!$this->sales_person_model->updateSalesPerson($id, $data)) {
                    throw new \Exception("failed");
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("sales_person_updated"));
                redirect($_SERVER["HTTP_REFERER"]);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER["HTTP_REFERER"]);
            }
            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['sales_person'] = $sales_person_detail;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            // $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups($this->session->userdata('company_id'));
            // $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'sales_person/edit', $this->data);
        }
    }

    public function sales_person_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        if ($id != $this->session->userdata('user_id')) {
                            $this->auth_model->delete_user($id);
                        }
                    }
                    $this->session->set_flashdata('message', lang("users_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('sales'))
                        ->SetCellValue('A1', lang('name'))
                        ->SetCellValue('B1', lang('company'))
                        ->SetCellValue('C1', lang('reference_no'))
                        ->SetCellValue('D1', lang('address'))
                        ->SetCellValue('E1', lang('phone'))
                        ->SetCellValue('F1', lang('email'))
                        ->SetCellValue('G1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $salePerson = $this->sales_person_model->getSalesPersonById($id);
                        $status = $salePerson == 1 ? 'Active' : 'Inactive';
                        $sheet->SetCellValue('A' . $row, $salePerson->name)
                            ->SetCellValue('B' . $row, $salePerson->company)
                            ->SetCellValue('C' . $row, $salePerson->reference_no)
                            ->SetCellValue('D' . $row, $salePerson->address . ',' . $salePerson->state . ',' . $salePerson->city . ',' . $salePerson->country)
                            ->SetCellValue('E' . $row, $salePerson->phone)
                            ->SetCellValue('F' . $row, $salePerson->email)
                            ->SetCellValue('G' . $row, $status);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'users_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_user_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function import_csv()
    {
        $this->db->trans_begin();
        try {
            $this->sma->checkPermissions('add', true);
            $this->load->helper('security');
            $this->form_validation->set_rules('csv_file', lang("upload_file"), 'xss_clean');

            if ($this->form_validation->run() == true) {

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
                        throw new Exception($error);
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
                    // print_r($arrResult);die;
                    $titles = array_shift($arrResult);

                    $keys = array('name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'reference_no');

                    $final = array();
                    foreach ($arrResult as $key => $value) {
                        $final[] = array_combine($keys, $value);
                    }
                    $rw = 2;
                    foreach ($final as $record) {
                        $record['company_id'] = $this->session->userdata('company_id');
                        $record['company'] = $this->session->userdata('company_name');
                        $record['reference_no'] = !empty($record['reference_no']) ? $record['reference_no'] : 'SP-' . substr(code_generator(), 0, 6);
                        $data[] = $record;
                    }
                }
            } elseif ($this->input->post('import')) {
                throw new Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && !empty($data)) {
                if ($this->sales_person_model->addMultipleSalesPerson($data)) {
                    $this->session->set_flashdata('message', lang("sales_person_added"));
                    $this->db->trans_commit();
                    redirect('sales_person');
                } else {
                    throw new Exception($this->db->error()['message']);
                }
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'sales_person/import', $this->data);
            }    
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['sales_person'] = $this->sales_person_model->getSalesPersonById($id);
        $this->load->view($this->theme . 'sales_person/view', $this->data);
    }

    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function search_sales_person()
    {
        try {
            $this->form_validation->set_rules('kode', lang('kode'), 'required');
            $this->form_validation->set_rules('sync_strategy', lang('sync_strategy'), 'required');
            if ($this->form_validation->run() == true) {
                $kode_distributor   = $this->input->post('kode') ?? '';
                $sync_strategy      = $this->input->post('sync_strategy') ?? 'strategy_1';
                if (!$kode_distributor || $kode_distributor == '-') {
                    throw new Exception(lang("code_not_found"));
                }
                $this->salespersonsycn($kode_distributor, $sync_strategy);
                redirect("sales_person");
            } else {
                $this->data['cf1']        = $this->companies_model->findCf1ById($this->session->userdata('company_id'));
                $this->data['modal_js']   = $this->site->modal_js();
                $this->load->view($this->theme . 'sales_person/search_sales_person', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    function randomemail()
    {
        $karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
        $string = '';
        for ($i = 0; $i < 5; $i++) {
            $pos = rand(0, strlen($karakter) - 1);
            $string .= $karakter{
                $pos};
        }
        return $string;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    private function salespersonsycn($kode_distributor, $sync_strategy)
    {
        $response_1         = $this->sales_person_model->getDataSalesPerson($kode_distributor);
        $response           = $response_1;
        if ($response['total'] <= 0) {
            $response_2   = $this->sales_person_model->getDataSalesPerson(str_pad($kode_distributor, 10, '0', STR_PAD_LEFT));
            if ($response_2['total'] <= 0) {
                throw new Exception(lang("not_found"));
            }
            $response = $response_2;
        }
        $jumlah   = 0;
        $count    = 0;
        $this->db->update('sales_person', ['flag' => 0], ['company_id' => $this->session->userdata('company_id')]);
        foreach ($response['data'] as $row['data']) {
            $count          += 1;
            $reference_no   = $row['data']['USER_SALES'];
            $data_sales_person       = $this->sales_person_model->getSalesPersonByreference_no($this->session->userdata('company_id'), $reference_no);
            if ($data_sales_person != NULL) {
                $data = array(
                    'flag'       => '1',
                    'updated_at' => date('Y-m-d H:i:s')
                );
                if (in_array($sync_strategy, ['strategy_2', 'strategy_3'])) {
                    $data['name']           = $row['data']['NAMA_SALES'];
                    $data['reference_no']   = $row['data']['USER_SALES'];
                    $data['is_active']      = '1';
                    $data['is_deleted']     = null;
                }
                $this->db->trans_begin();
                if ($this->sales_person_model->updateSalesPerson($data_sales_person->id, $data)) {
                    $this->db->trans_commit();
                    $jumlah += 1;
                } else {
                    $this->db->trans_rollback();
                    continue;
                }
            } else {
                $email    = trim($row['data']['NAMA_SALES']) . '@' . $this->randomemail() . '.com';
                $data     = $this->companies_model->getCompanyByID($this->session->userdata('biller_id'), $this->session->userdata('company_id'));
                $data = [
                    'name'          => $row['data']['NAMA_SALES'],
                    'email'         => $email,
                    'company'       => $this->session->userdata('company_name'),
                    'company_id'    => $this->session->userdata('company_id'),
                    'address'       => $data->address,
                    'vat_no'        => $data->vat_no,
                    'city'          => $data->city,
                    'state'         => $data->state,
                    'postal_code'   => $data->postal_code,
                    'country'       => $data->country,
                    'phone'         => $data->phone,
                    'reference_no'  => $row['data']['USER_SALES'],
                    'is_active'     => 1,
                    'flag'          => 1
                ];
                $this->db->trans_begin();
                if ($this->sales_person_model->addSalesPerson($data)) {
                    $this->db->trans_commit();
                    $jumlah += 1;
                } else {
                    $this->db->trans_rollback();
                    continue;
                }
            }
        }
        if (in_array($sync_strategy, ['strategy_3'])) {
            $this->db->update('sales_person', ['is_deleted' => 1, 'is_active' => 0], ['company_id' => $this->session->userdata('company_id'), 'flag' => 0]);
        }
        if ($jumlah != $count) {
            $this->session->set_flashdata('message', $jumlah . ' ' . lang("synchron_success") . lang("from") . $count . ' ' . lang("data"));
        } else {
            $this->session->set_flashdata('message', lang("success"));
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
}

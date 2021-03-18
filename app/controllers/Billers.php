<?php defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Billers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        // $this->insertLogActivities();
        $this->lang->load('billers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');
    }

    public function index($action = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('billers')));
        $meta = array('page_title' => lang('billers'), 'bc' => $bc);
        $this->page_construct('billers/index', $meta, $this->data);
    }

    public function getBillers()
    {
        $this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select("id, company, name, vat_no, phone, email, city, country")
            ->from("companies");
        if (!$this->Owner) {
            $this->datatables
            ->where('group_id', $this->session->userdata('group_id'))
            ->where('company_id', $this->session->userdata('company_id'));
        }
        $this->datatables
            ->where('group_name', 'biller')
            ->where('client_id IS NULL')
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_biller") . "' href='" . site_url('billers/edit/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_biller") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('billers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    public function add()
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {
            $data = array('name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => null,
                'group_name' => 'biller',
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'logo' => $this->input->post('logo'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'invoice_footer' => $this->input->post('invoice_footer'),
                'company_id' => $this->input->post('company_id'),
            );
        } elseif ($this->input->post('add_biller')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('billers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', $this->lang->line("biller_added"));
            redirect("billers");
        } else {
            $this->data['logos'] = $this->getLogoList();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'billers/add', $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run('companies/add') == true) {
            $data = array('name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => null,
                'group_name' => 'biller',
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'logo' => $this->input->post('logo'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'invoice_footer' => $this->input->post('invoice_footer'),
            );
        } elseif ($this->input->post('edit_biller')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('billers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line("biller_updated"));
            redirect("billers");
        } else {
            $this->data['biller'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['logos'] = $this->getLogoList();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'billers/edit', $this->data);
        }
    }


    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->companies_model->deleteBiller($id)) {
            echo $this->lang->line("biller_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('biller_x_deleted_have_sales'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    public function suggestions($term = null, $limit = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $limit = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getBillerSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }

    public function getBiller($id = null)
    {
        $this->sma->checkPermissions('index');

        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => $row->company)));
    }

    public function getLogoList()
    {
        $this->load->helper('directory');
        $dirname = "assets/uploads/logos";
        $ext = array("jpg", "png", "jpeg", "gif");
        $files = array();
        if ($handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                for ($i = 0; $i < sizeof($ext); $i++) {
                    if (stristr($file, "." . $ext[$i])) { //NOT case sensitive: OK with JpeG, JPG, ecc.
                        $files[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        sort($files);
        return $files;
    }

    public function biller_actions()
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
                        if (!$this->companies_model->deleteBiller($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('billers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("billers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('billers'))
                          ->SetCellValue('A1', lang('company'))
                          ->SetCellValue('B1', lang('name'))
                          ->SetCellValue('C1', lang('phone'))
                          ->SetCellValue('D1', lang('email'))
                          ->SetCellValue('E1', lang('city'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $sheet->SetCellValue('A' . $row, $customer->company)
                              ->SetCellValue('B' . $row, $customer->name)
                              ->SetCellValue('C' . $row, $customer->phone)
                              ->SetCellValue('D' . $row, $customer->email)
                              ->SetCellValue('E' . $row, $customer->city);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray( ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER] );
                    $filename = 'billers_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', $this->lang->line("no_biller_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
}

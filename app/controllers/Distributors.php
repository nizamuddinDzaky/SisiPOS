<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Distributors extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->insertLogActivities();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('customers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->model('authorized_model');
        $this->load->model('companies_model');
    }

    public function index($action = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $this->data['billers'] = $this->companies_model->getAllBillerCompanies();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('distributors')));
        $meta = array('page_title' => lang('distributors'), 'bc' => $bc);

        $this->page_construct('distributors/index', $meta, $this->data);
    }

    public function getDistributors()
    {
        $this->sma->checkPermissions('index');
        $this->load->library('datatables');
        $this->datatables
            ->select("companies.id as id, companies.company, companies.name, companies.email, companies.phone, companies.city, companies.vat_no, companies.cf1")
            ->from("companies")
            ->join("users",'companies.id = users.company_id')
            ->where('users.group_id', '2')
            ->where('users.active', '1')
            ->where('companies.group_name', 'biller')
            ->where('(client_id is null OR client_id != "aksestoko")')
            ->where('companies.is_active', '1')
            ->where('companies.is_deleted', null);

        echo $this->datatables->generate();
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme . 'customers/view', $this->data);
    }

    public function distributor_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            if(!$this->Principal){
                $this->session->set_flashdata('warning', lang('access_denied'));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
        	if($this->input->post('form_action') == 'export_excel_all'){
        		$spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('customer'))
                    ->SetCellValue('A1', lang('id'))
                    ->SetCellValue('B1', lang('company'))
                    ->SetCellValue('C1', lang('name'))
                    ->SetCellValue('D1', lang('email'))
                    ->SetCellValue('E1', lang('phone'))
                    ->SetCellValue('F1', lang('address'))
                    ->SetCellValue('G1', lang('city'))
                    ->SetCellValue('H1', lang('state'))
                    ->SetCellValue('I1', lang('postal_code'))
                    ->SetCellValue('J1', lang('country'))
                    ->SetCellValue('K1', lang('vat_no'))
                    ->SetCellValue('L1', lang('deposit_amount'))
                    ->SetCellValue('M1', lang('ccf1'))
                    ->SetCellValue('N1', lang('ccf2'))
                    ->SetCellValue('O1', lang('ccf3'))
                    ->SetCellValue('P1', lang('ccf4'))
                    ->SetCellValue('Q1', lang('ccf5'))
                    ->SetCellValue('R1', lang('ccf6'))
                    ->SetCellValue('S1', lang('kode'));

            	$row = 2;
            	$customer = $this->companies_model->getDistributor();

            	foreach ($customer as $key => $value) {
                    $sheet->SetCellValue('A' . $row, $value->id)
                        ->SetCellValue('B' . $row, $value->company)
                        ->SetCellValue('C' . $row, $value->name)
                        ->SetCellValue('D' . $row, $value->email)
                        ->SetCellValue('E' . $row, $value->phone)
                        ->SetCellValue('F' . $row, $value->address)
                        ->SetCellValue('G' . $row, $value->city)
                        ->SetCellValue('H' . $row, $value->state)
                        ->SetCellValue('I' . $row, $value->postal_code)
                        ->SetCellValue('J' . $row, $value->country)
                        ->SetCellValue('K' . $row, $value->vat_no)
                        ->SetCellValue('L' . $row, $value->deposit_amount)
                        ->SetCellValue('M' . $row, $value->cf1)
                        ->SetCellValue('N' . $row, $value->cf2)
                        ->SetCellValue('O' . $row, $value->cf3)
                        ->SetCellValue('P' . $row, $value->cf4)
                        ->SetCellValue('Q' . $row, $value->cf5)
                        ->SetCellValue('R' . $row, $value->cf6)
                        ->SetCellValue('S' . $row, $value->cf1);
                    $row++;
            	}

            	$sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                $filename = 'distributors_' . date('Y_m_d_H_i_s');

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');

                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                return $objWriter->save('php://output');

                redirect($_SERVER["HTTP_REFERER"]);

            }

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('customer'))
                        ->SetCellValue('A1', lang('id'))
                        ->SetCellValue('B1', lang('company'))
                        ->SetCellValue('C1', lang('name'))
                        ->SetCellValue('D1', lang('email'))
                        ->SetCellValue('E1', lang('phone'))
                        ->SetCellValue('F1', lang('address'))
                        ->SetCellValue('G1', lang('city'))
                        ->SetCellValue('H1', lang('state'))
                        ->SetCellValue('I1', lang('postal_code'))
                        ->SetCellValue('J1', lang('country'))
                        ->SetCellValue('K1', lang('vat_no'))
                        ->SetCellValue('L1', lang('deposit_amount'))
                        ->SetCellValue('M1', lang('ccf1'))
                        ->SetCellValue('N1', lang('ccf2'))
                        ->SetCellValue('O1', lang('ccf3'))
                        ->SetCellValue('P1', lang('ccf4'))
                        ->SetCellValue('Q1', lang('ccf5'))
                        ->SetCellValue('R1', lang('ccf6'))
                        ->SetCellValue('S1', lang('kode'));

                    $row = 2;
                    $get_distributor = $this->companies_model->getCompanyWhereInId($_POST['val'], 'biller');
                	foreach ($get_distributor as $distributor) {
                        $sheet->SetCellValue('A' . $row, $distributor->id)
                            ->SetCellValue('B' . $row, $distributor->company)
                            ->SetCellValue('C' . $row, $distributor->name)
                            ->SetCellValue('D' . $row, $distributor->email)
                            ->SetCellValue('E' . $row, $distributor->phone)
                            ->SetCellValue('F' . $row, $distributor->address)
                            ->SetCellValue('G' . $row, $distributor->city)
                            ->SetCellValue('H' . $row, $distributor->state)
                            ->SetCellValue('I' . $row, $distributor->postal_code)
                            ->SetCellValue('J' . $row, $distributor->country)
                            ->SetCellValue('K' . $row, $distributor->vat_no)
                            ->SetCellValue('L' . $row, $distributor->deposit_amount)
                            ->SetCellValue('M' . $row, $distributor->cf1)
                            ->SetCellValue('N' . $row, $distributor->cf2)
                            ->SetCellValue('O' . $row, $distributor->cf3)
                            ->SetCellValue('P' . $row, $distributor->cf4)
                            ->SetCellValue('Q' . $row, $distributor->cf5)
                            ->SetCellValue('R' . $row, $distributor->cf6)
                            ->SetCellValue('S' . $row, $distributor->cf1);
                        $row++;
                    }

                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('C')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'distributors_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_customer_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
}

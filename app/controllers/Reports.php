<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->lang->load('customers', $this->Settings->user_language);
        $this->lang->load('reports', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('reports_model');
        $this->data['pb'] = array(
            'cash' => lang('cash'),
            'CC' => lang('CC'),
            'Cheque' => lang('Cheque'),
            'paypal_pro' => lang('paypal_pro'),
            'stripe' => lang('stripe'),
            'gift_card' => lang('gift_card'),
            'deposit' => lang('deposit'),
            'authorize' => lang('authorize'),
        );
        // $this->insertLogActivities();
    }

    public function index()
    {
        $this->sma->checkPermissions();
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['monthly_sales'] = $this->reports_model->getChartData();
        $this->data['stock'] = $this->reports_model->getStockValue();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/index', $meta, $this->data);
    }

    public function warehouse_stock($warehouse = null)
    {
        $this->sma->checkPermissions('index', true);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        }

        $this->data['stock'] = $warehouse ? $this->reports_model->getWarehouseStockValue($warehouse) : $this->reports_model->getStockValue();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse;
        $this->data['warehouse'] = $warehouse ? $this->site->getWarehouseByID($warehouse) : null;
        $this->data['totals'] = $this->reports_model->getWarehouseTotals($warehouse);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('warehouse_stock')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/warehouse_stock', $meta, $this->data);
    }

    public function getWarehouseStockCard()
    {
        $this->sma->checkPermissions('index');

        $product = $this->input->get('product') ?? null;
        $warehouse = $this->input->get('warehouse') ?? null;
        $monthly = $this->input->get('monthly') ? $this->input->get('monthly') : date('m');
        $year = $this->input->get('annually') ? $this->sma->fld($this->input->get('annually')) : date('Y');

        $this->load->library('datatables');
        $this->datatables->select("sma_report_stock_card.id, sma_report_stock_card.created_at, COUNT(sma_report_stock_card.reference_no), sma_warehouses.name, sma_report_stock_card.product_name, ROUND(sma_report_stock_card.stock_awal), ROUND(SUM(sma_report_stock_card.masuk)), ROUND(SUM(sma_report_stock_card.keluar)), ROUND(((sma_report_stock_card.stock_awal+SUM(sma_report_stock_card.masuk))-SUM(sma_report_stock_card.keluar)))")
            ->from('report_stock_card')
            ->join('sma_warehouses', 'sma_warehouses.id  = report_stock_card.warehouse_id')
            ->where('sma_report_stock_card.is_deleted IS NULL');
        //->where('(sma_report_stock_card.status = "returned" OR sma_report_stock_card.status = "completed" OR sma_report_stock_card.status = "received")');

        if ($this->Admin) {
            $this->datatables->where("sma_report_stock_card.company_id", $this->session->userdata('company_id'));
        }
        if ($warehouse) {
            $this->datatables->where('sma_report_stock_card.warehouse_id', $warehouse);
        }
        if ($product) {
            $this->datatables->where('sma_report_stock_card.product_id', $product);
        }
        if ($monthly && $year) {
            $this->datatables->where("YEAR(sma_report_stock_card.created_at) = '" . $year . "' AND MONTH(sma_report_stock_card.created_at) = '" . $monthly . "' GROUP BY DAY(sma_report_stock_card.created_at), sma_report_stock_card.product_id");
        }

        $this->datatables->unset_column("sma_report_stock_card.id");
        echo $this->datatables->generate();
    }

    public function getExportWarehouseStockCard($export_to = null)
    {
        $this->sma->checkPermissions('index');

        $product = $this->input->get('product') ?? null;
        $warehouse = $this->input->get('warehouse') ?? null;
        $monthly = $this->input->get('monthly') ? $this->input->get('monthly') : date('m');
        $year = $this->input->get('annually') ? $this->sma->fld($this->input->get('annually')) : date('Y');

        $this->db->select("sma_report_stock_card.id, DATE_FORMAT(sma_report_stock_card.created_at, \"%Y-%M-%d\") AS date, COUNT(sma_report_stock_card.reference_no) AS total_transaksi, sma_warehouses.name, sma_report_stock_card.product_name, ROUND(sma_report_stock_card.stock_awal) as stock_awal, ROUND(SUM(sma_report_stock_card.masuk)) as masuk, ROUND(SUM(sma_report_stock_card.keluar)) as keluar, ROUND(((sma_report_stock_card.stock_awal+SUM(sma_report_stock_card.masuk))-SUM(sma_report_stock_card.keluar))) as stok_akhir")
            ->from('report_stock_card')
            ->join('sma_warehouses', 'sma_warehouses.id  = report_stock_card.warehouse_id')
            ->where('sma_report_stock_card.is_deleted IS NULL');
        //->where('(sma_report_stock_card.status = "returned" OR sma_report_stock_card.status = "completed" OR sma_report_stock_card.status = "received")');

        if ($this->Admin) {
            $this->db->where("sma_report_stock_card.company_id", $this->session->userdata('company_id'));
        }
        if ($warehouse) {
            $this->db->where('sma_report_stock_card.warehouse_id', $warehouse);
        }
        if ($product) {
            $this->db->where('sma_report_stock_card.product_id', $product);
        }
        if ($monthly && $year) {
            $this->db->where("YEAR(sma_report_stock_card.created_at) = '" . $year . "' AND MONTH(sma_report_stock_card.created_at) = '" . $monthly . "' GROUP BY DAY(sma_report_stock_card.created_at), sma_report_stock_card.product_id");
        }

        $list_stock_card = $this->db->get()->result();

        if ($export_to) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('report'))
                ->SetCellValue('A1', lang('report_stock_card_date'))
                ->SetCellValue('B1', lang('report_stock_card_total_transaksi'))
                ->SetCellValue('C1', lang('report_stock_card_warehouse_name'))
                ->SetCellValue('D1', lang('report_stock_card_product_name'))
                ->SetCellValue('E1', lang('report_stock_card_stock_first'))
                ->SetCellValue('F1', lang('report_stock_card_stock_in'))
                ->SetCellValue('G1', lang('report_stock_card_stock_out'))
                ->SetCellValue('H1', lang('report_stock_card_stock_last'));
            $row = 2;
            foreach ($list_stock_card as $stock_card) {
                $sheet->SetCellValue('A' . $row, $stock_card->date)
                    ->SetCellValue('B' . $row, $stock_card->total_transaksi)
                    ->SetCellValue('C' . $row, $stock_card->name)
                    ->SetCellValue('D' . $row, $stock_card->product_name)
                    ->SetCellValue('E' . $row, $stock_card->stock_awal)
                    ->SetCellValue('F' . $row, $stock_card->masuk)
                    ->SetCellValue('G' . $row, $stock_card->keluar)
                    ->SetCellValue('H' . $row, $stock_card->stok_akhir);
                $row++;
            }
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);

            $filename = 'warehuse_stock_card_' . date('Y_m_d_H_i_s');
            if ($export_to == 'pdf') {
                $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                $sheet->getDefaultStyle()->applyFromArray($styleArray);
                $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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
            if ($export_to == 'xls') {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');

                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                return $objWriter->save('php://output');
            }

            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    public function expiry_alerts($warehouse_id = null)
    {
        $this->sma->checkPermissions('expiry_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_expiry_alerts')));
        $meta = array('page_title' => lang('product_expiry_alerts'), 'bc' => $bc);
        $this->page_construct('reports/expiry_alerts', $meta, $this->data);
    }

    public function getExpiryAlerts($warehouse_id = null)
    {
        $this->sma->checkPermissions('expiry_alerts', true);
        $date = date('Y-m-d', strtotime('+3 months'));

        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("thumb_image, product_code, product_name, quantity_balance, warehouses.name, expiry, image")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('warehouse_id', $warehouse_id)
                ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
                ->where('expiry <', $date);
        } else {
            $this->datatables
                ->select("thumb_image, product_code, product_name, quantity_balance, warehouses.name, expiry, image")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
                ->where('expiry <', $date);
        }

        if (!$this->Owner) {
            $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->edit_column("thumb_image", "$1___$2", 'url_image_thumb(thumb_image), url_image_thumb(image, 0)');
        echo $this->datatables->generate();
    }

    public function quantity_alerts($warehouse_id = null)
    {
        $this->sma->checkPermissions('quantity_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_quantity_alerts')));
        $meta = array('page_title' => lang('product_quantity_alerts'), 'bc' => $bc);
        $this->page_construct('reports/quantity_alerts', $meta, $this->data);
    }

    public function getQuantityAlerts($warehouse_id = null, $pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('quantity_alerts', true);
        if (!$this->Owner && !$this->Admin  && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        if ($pdf || $xls) {
            if ($warehouse_id) {
                $this->db
                    ->select('products.image as image, products.code, products.name, warehouses_products.quantity, alert_quantity')
                    ->from('products')->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
                    ->where('alert_quantity > warehouses_products.quantity', null)
                    ->where('warehouse_id', $warehouse_id)
                    ->where('track_quantity', 1);
                if (!$this->Owner) {
                    $this->db->where('company_id', $this->session->userdata('company_id'));
                }
                $this->db->order_by('products.code desc');
            } else {
                $this->db
                    ->select('image, code, name, quantity, alert_quantity')
                    ->from('products')
                    ->where('alert_quantity > quantity', null)
                    ->where('track_quantity', 1);
                if (!$this->Owner) {
                    $this->db->where('company_id', $this->session->userdata('company_id'));
                }
                $this->db->order_by('code desc');
            }


            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('product_quantity_alerts'))
                    ->SetCellValue('A1', lang('product_code'))
                    ->SetCellValue('B1', lang('product_name'))
                    ->SetCellValue('C1', lang('quantity'))
                    ->SetCellValue('D1', lang('alert_quantity'));

                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->code)
                        ->SetCellValue('B' . $row, $data_row->name)
                        ->SetCellValue('C' . $row, $data_row->quantity)
                        ->SetCellValue('D' . $row, $data_row->alert_quantity);
                    $row++;
                }
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);

                $filename = 'Prouct Ware House Reports';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            if ($warehouse_id) {
                $this->datatables
                    ->select('thumb_image, code, name, wp.quantity, alert_quantity, image')
                    ->from('products')
                    ->join("( SELECT * from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left')
                    ->where('alert_quantity > wp.quantity', null)
                    //                    ->or_where('wp.quantity', NULL)
                    //                    ->where('alert_quantity>wp.quantity OR wp.quantity IS NULL',null)
                    ->where('track_quantity', 1);
                if (!$this->Owner) {
                    $this->datatables->where('wp.company_id', $this->session->userdata('company_id'));
                }
                $this->datatables->group_by('products.id');
            } else {
                $this->datatables
                    ->select("thumb_image, code, name, {$this->db->dbprefix('products')}.quantity, alert_quantity, image")
                    ->from('products')
                    ->where('alert_quantity > quantity', null)
                    ->where('track_quantity', 1);
                if (!$this->Owner) {
                    $this->datatables->where('company_id', $this->session->userdata('company_id'));
                }
            }
            $this->datatables->edit_column("thumb_image", "$1___$2", 'url_image_thumb(thumb_image), url_image_thumb(image, 0)');

            echo $this->datatables->generate();
        }
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1) {
            die();
        }

        $rows = $this->reports_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")");
            }
            $this->sma->send_json($pr);
        } else {
            echo false;
        }
    }

    public function best_sellers($warehouse_id = null)
    {
        $this->sma->checkPermissions('products');

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $y1 = date('Y', strtotime('-1 month'));
        $m1 = date('m', strtotime('-1 month'));
        $m1sdate = $y1 . '-' . $m1 . '-01 00:00:00';
        $m1edate = $y1 . '-' . $m1 . '-' . days_in_month($m1, $y1) . ' 23:59:59';
        $this->data['m1'] = date('M Y', strtotime($y1 . '-' . $m1));
        $this->data['m1bs'] = $this->reports_model->getBestSeller($m1sdate, $m1edate, $warehouse_id);
        $y2 = date('Y', strtotime('-2 months'));
        $m2 = date('m', strtotime('-2 months'));
        $m2sdate = $y2 . '-' . $m2 . '-01 00:00:00';
        $m2edate = $y2 . '-' . $m2 . '-' . days_in_month($m2, $y2) . ' 23:59:59';
        $this->data['m2'] = date('M Y', strtotime($y2 . '-' . $m2));
        $this->data['m2bs'] = $this->reports_model->getBestSeller($m2sdate, $m2edate, $warehouse_id);
        $y3 = date('Y', strtotime('-3 months'));
        $m3 = date('m', strtotime('-3 months'));
        $m3sdate = $y3 . '-' . $m3 . '-01 23:59:59';
        $this->data['m3'] = date('M Y', strtotime($y3 . '-' . $m3)) . ' - ' . $this->data['m1'];
        $this->data['m3bs'] = $this->reports_model->getBestSeller($m3sdate, $m1edate, $warehouse_id);
        $y4 = date('Y', strtotime('-12 months'));
        $m4 = date('m', strtotime('-12 months'));
        $m4sdate = $y4 . '-' . $m4 . '-01 23:59:59';
        $this->data['m4'] = date('M Y', strtotime($y4 . '-' . $m4)) . ' - ' . $this->data['m1'];
        $this->data['m4bs'] = $this->reports_model->getBestSeller($m4sdate, $m1edate, $warehouse_id);
        // $this->sma->print_arrays($this->data['m1bs'], $this->data['m2bs'], $this->data['m3bs'], $this->data['m4bs']);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('best_sellers')));
        $meta = array('page_title' => lang('best_sellers'), 'bc' => $bc);
        $this->page_construct('reports/best_sellers', $meta, $this->data);
    }

    public function products()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_report')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
        $this->page_construct('reports/products', $meta, $this->data);
    }

    public function getProductsReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);

        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : null;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $cf1 = $this->input->get('cf1') ? $this->input->get('cf1') : null;
        $cf2 = $this->input->get('cf2') ? $this->input->get('cf2') : null;
        $cf3 = $this->input->get('cf3') ? $this->input->get('cf3') : null;
        $cf4 = $this->input->get('cf4') ? $this->input->get('cf4') : null;
        $cf5 = $this->input->get('cf5') ? $this->input->get('cf5') : null;
        $cf6 = $this->input->get('cf6') ? $this->input->get('cf6') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $consignment = $this->input->get('consignment') ? $this->input->get('consignment') : null;

        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
        $sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id ";

        if ($consignment) {
            $pp = "( SELECT pi.product_id, SUM(pi.quantity) as purchasedQty, SUM(pi.quantity) as balacneQty, pi.net_unit_price as balacneValue, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('consignment_items')} pi LEFT JOIN {$this->db->dbprefix('consignment')} p on p.id = pi.consignment_id ";
            //            $pp .= "WHERE pi.flag=1 ";
            $sp .= "WHERE si.flag=1 ";
        } else {
            $pp .= "WHERE pi.flag is NULL ";
            $sp .= "WHERE si.flag is NULL ";
        }

        if ($start_date || $warehouse) {
            //            $pp .= " WHERE ";
            //            $sp .= " WHERE ";
            $pp = $pp . (strpos($pp, "WHERE") ? "AND " : "WHERE ");
            $sp = $sp . (strpos($sp, "WHERE") ? "AND " : "WHERE ");
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }

        $pp .= " GROUP BY pi.product_id ) PCosts";
        $sp .= " GROUP BY si.product_id ) PSales";
        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 ) as SoldQty,
				COALESCE( PCosts.balacneQty, 0 ) as BalacneQty,
				COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
				COALESCE( PCosts.balacneValue, 0 ) as TotalBalance,
				COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", false)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->order_by('products.name');

            if (!$this->Owner) {
                $this->db->where('products.company_id', $this->session->userdata('company_id'));
            }

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->db->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->db->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->db->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->db->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->db->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->db->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->db->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->db->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('products_report'))
                    ->SetCellValue('A1', lang('product_code'))
                    ->SetCellValue('B1', lang('product_name'))
                    ->SetCellValue('C1', lang('purchased'))
                    ->SetCellValue('D1', lang('sold'))
                    ->SetCellValue('E1', lang('balance'))
                    ->SetCellValue('F1', lang('purchased_amount'))
                    ->SetCellValue('G1', lang('sold_amount'))
                    ->SetCellValue('H1', lang('profit_loss'))
                    ->SetCellValue('I1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->code)
                        ->SetCellValue('B' . $row, $data_row->name)
                        ->SetCellValue('C' . $row, $data_row->PurchasedQty)
                        ->SetCellValue('D' . $row, $data_row->SoldQty)
                        ->SetCellValue('E' . $row, $data_row->BalacneQty)
                        ->SetCellValue('F' . $row, $data_row->TotalPurchase)
                        ->SetCellValue('G' . $row, $data_row->TotalSales)
                        ->SetCellValue('H' . $row, $data_row->Profit)
                        ->SetCellValue('I' . $row, $data_row->TotalBalance);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $bAmt += $data_row->TotalBalance;
                    $pl += $data_row->Profit;
                    $row++;
                }
                $sheet->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('C' . $row, $pQty);
                $sheet->SetCellValue('D' . $row, $sQty);
                $sheet->SetCellValue('E' . $row, $bQty);
                $sheet->SetCellValue('F' . $row, $pAmt);
                $sheet->SetCellValue('G' . $row, $sAmt);
                $sheet->SetCellValue('H' . $row, $pl);
                $sheet->SetCellValue('I' . $row, $bAmt);

                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(25);
                $sheet->getColumnDimension('I')->setWidth(25);

                $filename = 'products_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            if ($consignment) {
                $this->datatables->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                    CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
                    CONCAT(COALESCE( PSales.soldQty, 0 ), '__', COALESCE( PSales.totalSale, 0 )) as sold,
                    (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
                    CONCAT(COALESCE( PCosts.balacneQty, 0 )-COALESCE( PSales.soldQty, 0 ), '__', COALESCE((COALESCE(PCosts.balacneQty,0)-COALESCE(PSales.soldQty,0))*PCosts.balacneValue,0)) as balance, {$this->db->dbprefix('products')}.id as id", false);
            } else {
                $this->datatables->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                    CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
                    CONCAT(COALESCE( PSales.soldQty, 0 ), '__', COALESCE( PSales.totalSale, 0 )) as sold,
                    (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
                    CONCAT(COALESCE( PCosts.balacneQty, 0 ), '__', COALESCE( PCosts.balacneValue, 0 )) as balance, {$this->db->dbprefix('products')}.id as id", false);
            }
            $this->datatables->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->group_by('products.code, PSales.soldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase, PCosts.balacneQty, PCosts.balacneValue');

            if (!$this->Owner) {
                $this->datatables->where('products.company_id', $this->session->userdata('company_id'));
            }
            if ($consignment) {
                $this->datatables->where('products.type', 'consignment');
            }

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($subcategory) {
                $this->datatables->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->datatables->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            echo $this->datatables->generate();
        }
    }

    public function categories()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('categories_report')));
        $meta = array('page_title' => lang('categories_report'), 'bc' => $bc);
        $this->page_construct('reports/categories', $meta, $this->data);
    }

    public function getCategoriesReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $pp = "( SELECT pp.category_id as category, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id WHERE p.company_id = " . $this->session->userdata('company_id');
        $sp = "( SELECT sp.category_id as category, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id WHERE s.company_id = " . $this->session->userdata('company_id');

        if ($start_date || $warehouse) {
            $pp .= " AND ";
            $sp .= " AND ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.category_id ) PCosts";
        $sp .= " GROUP BY sp.category_id ) PSales";

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", false)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->where('PSales.soldQty != 0 or PCosts.purchasedQty !=0')
                ->group_by('categories.id, categories.code, categories.name')
                ->order_by('categories.code', 'asc');

            if ($category) {
                $this->db->where($this->db->dbprefix('categories') . ".id", $category);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('categories_report'))
                    ->SetCellValue('A1', lang('category_code'))
                    ->SetCellValue('B1', lang('category_name'))
                    ->SetCellValue('C1', lang('purchased'))
                    ->SetCellValue('D1', lang('sold'))
                    ->SetCellValue('E1', lang('purchased_amount'))
                    ->SetCellValue('F1', lang('sold_amount'))
                    ->SetCellValue('G1', lang('profit_loss'));
                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $sheet->SetCellValue('A' . $row, $data_row->code)
                        ->SetCellValue('B' . $row, $data_row->name)
                        ->SetCellValue('C' . $row, $data_row->PurchasedQty)
                        ->SetCellValue('D' . $row, $data_row->SoldQty)
                        ->SetCellValue('E' . $row, $data_row->TotalPurchase)
                        ->SetCellValue('F' . $row, $data_row->TotalSales)
                        ->SetCellValue('G' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $sheet->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('C' . $row, $pQty);
                $sheet->SetCellValue('D' . $row, $sQty);
                $sheet->SetCellValue('E' . $row, $pAmt);
                $sheet->SetCellValue('F' . $row, $sAmt);
                $sheet->SetCellValue('G' . $row, $pl);

                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);

                $filename = 'categories_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('categories') . ".id as cid, " . $this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", false)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category ', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->where('PSales.soldQty != 0 or PCosts.purchasedQty !=0');

            if ($category) {
                $this->datatables->where('categories.id', $category);
            }
            $this->datatables->group_by('categories.id, categories.code, categories.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('cid');
            echo $this->datatables->generate();
        }
    }

    public function brands()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('brands_report')));
        $meta = array('page_title' => lang('brands_report'), 'bc' => $bc);
        $this->page_construct('reports/brands', $meta, $this->data);
    }

    public function getBrandsReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $pp = "( SELECT pp.brand as brand, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id WHERE p.company_id = " . $this->session->userdata('company_id');
        $sp = "( SELECT sp.brand as brand, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id WHERE s.company_id = " . $this->session->userdata('company_id');
        if ($start_date || $warehouse) {
            $pp .= " AND ";
            $sp .= " AND ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.brand ) PCosts";
        $sp .= " GROUP BY sp.brand ) PSales";

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('brands') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", false)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->join($pp, 'brands.id = PCosts.brand', 'left')
                ->where('PSales.soldQty != 0 or PCosts.purchasedQty !=0')
                ->group_by('brands.id, brands.name')
                ->order_by('brands.code', 'asc');

            if ($brand) {
                $this->db->where($this->db->dbprefix('brands') . ".id", $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('brands_report'))
                    ->SetCellValue('A1', lang('brands'))
                    ->SetCellValue('B1', lang('purchased'))
                    ->SetCellValue('C1', lang('sold'))
                    ->SetCellValue('D1', lang('purchased_amount'))
                    ->SetCellValue('E1', lang('sold_amount'))
                    ->SetCellValue('F1', lang('profit_loss'));
                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $sheet->SetCellValue('A' . $row, $data_row->name)
                        ->SetCellValue('B' . $row, $data_row->PurchasedQty)
                        ->SetCellValue('C' . $row, $data_row->SoldQty)
                        ->SetCellValue('D' . $row, $data_row->TotalPurchase)
                        ->SetCellValue('E' . $row, $data_row->TotalSales)
                        ->SetCellValue('F' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $sheet->getStyle("B" . $row . ":F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('B' . $row, $pQty);
                $sheet->SetCellValue('C' . $row, $sQty);
                $sheet->SetCellValue('D' . $row, $pAmt);
                $sheet->SetCellValue('E' . $row, $sAmt);
                $sheet->SetCellValue('F' . $row, $pl);

                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);

                $filename = 'brands_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('brands') . ".id as id, " . $this->db->dbprefix('brands') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", false)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->where('PSales.soldQty != 0 or PCosts.purchasedQty !=0')
                ->join($pp, 'brands.id = PCosts.brand', 'left');

            if ($brand) {
                $this->datatables->where('brands.id', $brand);
            }
            $this->datatables->group_by('brands.id, brands.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('id');
            echo $this->datatables->generate();
        }
    }

    public function profit($date = null, $warehouse_id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->data['costing'] = $this->reports_model->getCosting($date, $warehouse_id);
        $this->data['discount'] = $this->reports_model->getOrderDiscount($date, $warehouse_id);
        $this->data['expenses'] = $this->reports_model->getExpenses($date, $warehouse_id);
        $this->data['returns'] = $this->reports_model->getReturns($date, $warehouse_id);
        $this->data['date'] = $date;
        $this->load->view($this->theme . 'reports/profit', $this->data);
    }
    public function monthly_profit($year, $month, $warehouse_id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->data['costing'] = $this->reports_model->getCosting(null, $warehouse_id, $year, $month);
        $this->data['discount'] = $this->reports_model->getOrderDiscount(null, $warehouse_id, $year, $month);
        $this->data['expenses'] = $this->reports_model->getExpenses(null, $warehouse_id, $year, $month);
        $this->data['returns'] = $this->reports_model->getReturns(null, $warehouse_id, $year, $month);
        $this->data['date'] = date('F Y', strtotime($year . '-' . $month . '-' . '01'));
        $this->load->view($this->theme . 'reports/monthly_profit', $this->data);
    }

    public function daily_sales($warehouse_id = null, $year = null, $month = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => true,
            'next_prev_url' => site_url('reports/daily_sales/' . ($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $user_id ? $this->reports_model->getStaffDailySales($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailySales($year, $month, $warehouse_id);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("shipping") . "</td><td>" . $this->sma->formatMoney($sale->shipping) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($sale->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_sales") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_sales_report')));
        $meta = array('page_title' => lang('daily_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/daily', $meta, $this->data);
    }


    public function monthly_sales($warehouse_id = null, $year = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['sales'] = $user_id ? $this->reports_model->getStaffMonthlySales($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlySales($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang("monthly_sales") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_sales_report')));
        $meta = array('page_title' => lang('monthly_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly', $meta, $this->data);
    }

    public function sales()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaffName();
        $this->data['warehouses'] = $this->site->getNameAndIdWarehouses();
        $this->data['billers'] = $this->site->getCompaniesByGroupName('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_report')));
        $meta = array('page_title' => lang('sales_report'), 'bc' => $bc);
        $this->page_construct('reports/sales', $meta, $this->data);
    }

    public function getSalesReport($year = null, $month = null, $pdf = null, $xls = null)
    {
        ini_set('memory_limit', '2048M');

        $this->sma->checkPermissions('sales', true);

        $product        = $this->input->get('product') ? $this->input->get('product') : null;
        $user           = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse      = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $reference_no   = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date     = ($this->input->get('start_date') && $this->input->get('start_date') != '-') ? $this->input->get('start_date') : null;
        $end_date       = ($this->input->get('end_date') && $this->input->get('end_date') != '-') ? $this->input->get('end_date') : null;
        $serial         = $this->input->get('serial') ? $this->input->get('serial') : null;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {
            $this->db
                ->select(
                    "
                          sales.id as `id`,
                          date, 
                          reference_no, 
                          biller, 
                          customer,
                          " . $this->db->dbprefix('sale_items') . ".product_code,
                          " . $this->db->dbprefix('sale_items') . ".product_name,
                          brands.name AS brand_name,
                          " . $this->db->dbprefix('sale_items') . ".quantity, 
                          (" . $this->db->dbprefix('sale_items') . ".quantity - SUM(" . $this->db->dbprefix('sale_items') . ".sent_quantity) ) AS quantity_unsent,
                          SUM( " . $this->db->dbprefix('sale_items') . ".sent_quantity ) AS quantity_sent,
                          grand_total, 
                          paid, 
                          payment_status, 
                          sale_status,
                          total,
                          total_discount,
                          charge,
                          CASE
                            WHEN SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) > SUM( " . $this->db->dbprefix('sale_items') . ".sent_quantity ) AND SUM( " . $this->db->dbprefix('sale_items') . ".sent_quantity ) = 0 THEN 'pending' 
                            WHEN SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) > SUM( " . $this->db->dbprefix('sale_items') . ".sent_quantity ) AND SUM( " . $this->db->dbprefix('sale_items') . ".sent_quantity ) > 0 THEN 'partial' 
                            WHEN SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) <= SUM( " . $this->db->dbprefix('sale_items') . ".sent_quantity ) AND SUM( " . $this->db->dbprefix('sale_items') . ".sent_quantity ) > 0 THEN 'done' 
                            ELSE '-'
                          END AS delivery_status, 
                          warehouses.code, 
                          warehouses.name,
                          sale_items.unit_price,
                          sale_items.subtotal,
                          sale_items.product_unit_code,
                          companies.cf1,
                          companies.country AS customer_province,
                          companies.city AS customer_city,
                          companies.state AS customer_state,
                          IF(" . $this->db->dbprefix('sales') . ".client_id = 'aksestoko', 'AksesToko', 'POS') AS flag, 
                          CONCAT( " . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) AS created_by,
                          " . $this->db->dbprefix('users') . ".username",
                    false
                )
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id = sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->join('companies', 'companies.id = sales.customer_id', 'left')
                ->join('users', 'sales.created_by = users.id', 'left')
                ->join('products', 'sale_items.product_id = products.id', 'left')
                ->join('brands', 'products.brand = brands.id', 'left');
            // echo("date, reference_no, biller,".$this->db->dbprefix('companies').".cf1, customer," . $this->db->dbprefix('sale_items') . ".product_name," . $this->db->dbprefix('sale_items') . ".quantity, grand_total, paid, payment_status");
            if ($month != '-') {
                $this->db->where('month(date)', $month);
            }
            if ($year != '-') {
                $this->db->where('year(date)', $year);
            }
            if (!$this->Owner && !$this->Principal) {
                $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
            }
            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            $this->db->where("sales.is_deleted", null);
            $this->db->group_by("sale_items.id");
            $this->db->order_by('sma_sales.id asc');
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('sales_report'))
                    ->setCellValue('A1', lang('id_sales'))
                    ->setCellValue('B1', lang('date'))
                    ->setCellValue('C1', lang('reference_no'))
                    ->setCellValue('D1', lang('biller'))
                    ->setCellValue('E1', lang('warehouse_code'))
                    ->setCellValue('F1', lang('warehouse'))
                    ->setCellValue('G1', lang('customers_code'))
                    ->setCellValue('H1', lang('customer'))
                    ->setCellValue('I1', lang('province'))
                    ->setCellValue('J1', lang('city'))
                    ->setCellValue('K1', lang('state'))
                    ->setCellValue('L1', lang('total'))
                    ->setCellValue('M1', lang('discount'))
                    ->setCellValue('N1', lang('charge'))
                    ->setCellValue('O1', lang('grand_total'))
                    ->setCellValue('P1', lang('paid'))
                    ->setCellValue('Q1', lang('balance'))
                    ->setCellValue('R1', lang('payment_status'))
                    ->setCellValue('S1', lang('sale_status'))
                    ->setCellValue('T1', lang('delivery_status'))
                    ->setCellValue('U1', lang('flag'))
                    ->setCellValue('V1', lang('created_by') . " " . lang('name'))
                    ->setCellValue('W1', lang('created_by') . " " . lang('identity'));

                $row        = 2;
                $t          = 0;
                $t_discount = 0;
                $total      = 0;
                $paid       = 0;
                $balance    = 0;
                $discount    = 0;
                $inserted_id   = [];

                foreach ($data as $data_row) {
                    if (in_array($data_row->id, $inserted_id)) {
                        continue;
                    }
                    $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode("dd/mm/yyyy hh:mm:ss");
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->date);

                    $sheet->SetCellValue('A' . $row, $data_row->id)
                        ->SetCellValue('B' . $row, $date)
                        ->SetCellValue('C' . $row, $data_row->reference_no)
                        ->SetCellValue('D' . $row, $data_row->biller)
                        ->SetCellValue('E' . $row, $data_row->code)
                        ->SetCellValue('F' . $row, $data_row->name)
                        ->SetCellValue('G' . $row, str_replace("IDC-", "", $data_row->cf1))
                        ->SetCellValue('H' . $row, $data_row->customer)
                        ->SetCellValue('I' . $row, $data_row->customer_province)
                        ->SetCellValue('J' . $row, $data_row->customer_city)
                        ->SetCellValue('K' . $row, $data_row->customer_state)
                        ->SetCellValue('L' . $row, $data_row->total)
                        ->SetCellValue('M' . $row, $data_row->total_discount * -1)
                        ->SetCellValue('N' . $row, $data_row->charge ?? 0)
                        ->SetCellValue('O' . $row, $data_row->grand_total)
                        ->SetCellValue('P' . $row, $data_row->paid)
                        ->SetCellValue('Q' . $row, ($data_row->grand_total - $data_row->paid))
                        ->SetCellValue('R' . $row, lang($data_row->payment_status))
                        ->SetCellValue('S' . $row, $data_row->sale_status)
                        ->SetCellValue('T' . $row, $data_row->delivery_status)
                        ->SetCellValue('U' . $row, $data_row->flag)
                        ->SetCellValue('V' . $row, $data_row->created_by)
                        ->SetCellValue('W' . $row, $data_row->username);
                    $row++;
                    $t          += $data_row->total;
                    $t_discount += ($data_row->total_discount * -1);
                    $total = $total + $data_row->grand_total;
                    $paid = $paid + $data_row->paid;
                    $balance = $balance + ($data_row->grand_total - $data_row->paid);
                    $inserted_id[] = $data_row->id;
                    $charge += $data_row->charge;
                }
                $sheet->getStyle("L" . $row . ":Q" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('L' . $row, $t);
                $sheet->SetCellValue('M' . $row, $t_discount);
                $sheet->SetCellValue('N' . $row, $charge);
                $sheet->SetCellValue('O' . $row, $total);
                $sheet->SetCellValue('P' . $row, $paid);
                $sheet->SetCellValue('Q' . $row, $balance);

                $spreadsheet->createSheet();
                $sheet = $spreadsheet->setActiveSheetIndex(1);
                $sheet->setTitle(lang('sale_items_report'))
                    ->setCellValue('A1', lang('id_sales'))
                    ->setCellValue('B1', lang('product_code'))
                    ->setCellValue('C1', lang('product'))
                    ->setCellValue('D1', lang('brand'))
                    ->setCellValue('E1', lang('quantity'))
                    ->setCellValue('F1', lang('unit'))
                    ->setCellValue('G1', lang('unit_price'))
                    ->setCellValue('H1', lang('subtotal'))
                    ->setCellValue('I1', lang('unsend_quantity'))
                    ->setCellValue('J1', lang('sent_quantity'));

                $row        = 2;

                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->id)
                        ->SetCellValue('B' . $row, $data_row->product_code)
                        ->SetCellValue('C' . $row, $data_row->product_name)
                        ->SetCellValue('D' . $row, $data_row->brand_name)
                        ->SetCellValue('E' . $row, $data_row->quantity)
                        ->SetCellValue('F' . $row, $data_row->product_unit_code)
                        ->SetCellValue('G' . $row, $data_row->unit_price)
                        ->SetCellValue('H' . $row, $data_row->subtotal)
                        ->SetCellValue('I' . $row, $data_row->quantity_unsent)
                        ->SetCellValue('J' . $row, $data_row->quantity_sent);
                    $row++;
                }

                $filename = 'sales_report';

                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
                    $sheet->getParent()->getDefaultStyle()->applyFromArray($styleArray);
                    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "Mpdf" . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = "Mpdf";
                    $rendererLibrary = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererName;
                    if (!IOFactory::registerWriter($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = IOFactory::createWriter($spreadsheet, 'Pdf');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $tmp = "(SELECT {$this->db->dbprefix('sales')}.id AS id, 
                    (CASE WHEN {$this->db->dbprefix('sales')}.client_id = 'aksestoko' 
                    THEN CONCAT( 'AksesToko' ) ELSE CONCAT( 'Forca POS') END ) AS created_by 
                    FROM {$this->db->dbprefix('sales')} 
                    WHERE {$this->db->dbprefix('sales')}.date BETWEEN 'year(date)-month(date)-01' AND NOW() ) tmp";

            $this->load->library('datatables');
            $this->datatables
                ->select("date, 
                        reference_no, 
                        biller,
                        warehouses.code, warehouses.name, companies.cf1,
                        customer, 
                        tmp.created_by, 
                        GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as iname, 
                        sale_status, 
                        grand_total, 
                        paid, 
                        (grand_total-paid) as balance, 
                        payment_status, 
                        {$this->db->dbprefix('sales')}.id as id")
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id = sales.id', 'left')
                ->join('products', 'products.id = sale_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
                ->join('companies', 'companies.id = sales.customer_id', 'left')
                ->join($tmp, 'tmp.id = sales.id', 'left');
            if (!$this->Owner && !$this->Principal) {
                $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
            }
            if ($year != null) {
                $this->datatables->where('year(date)', $year);
            }
            if ($month != null) {
                $this->datatables->where('month(date)', $month);
            }
            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sma_sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            } else {
                $this->datatables->where("date BETWEEN 'year(date)-month(date)-01' AND NOW() + INTERVAL 1 DAY");
            }
            $this->datatables->where("sales.is_deleted", null);
            $this->datatables->group_by('sale_items.sale_id');
            echo $this->datatables->generate();
        }
    }

    public function getQuotesReport($pdf = null, $xls = null)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = null;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = null;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = null;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = null;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = null;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = null;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = null;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = null;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if ($pdf || $xls) {
            $this->db
                ->select("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('quote_items') . ".product_name, ' (', " . $this->db->dbprefix('quote_items') . ".quantity, ')') SEPARATOR '<br>') as iname, grand_total, status", false)
                ->from('quotes')
                ->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->db->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->db->where('quote_items.product_id', $product);
            }
            if ($biller) {
                $this->db->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('quotes') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('quotes_report'))
                    ->SetCellValue('A1', lang('date'))
                    ->SetCellValue('B1', lang('reference_no'))
                    ->SetCellValue('C1', lang('biller'))
                    ->SetCellValue('D1', lang('customer'))
                    ->SetCellValue('E1', lang('product_qty'))
                    ->SetCellValue('F1', lang('grand_total'))
                    ->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                        ->SetCellValue('B' . $row, $data_row->reference_no)
                        ->SetCellValue('C' . $row, $data_row->biller)
                        ->SetCellValue('D' . $row, $data_row->customer)
                        ->SetCellValue('E' . $row, $data_row->iname)
                        ->SetCellValue('F' . $row, $data_row->grand_total)
                        ->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $filename = 'quotes_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $qi = "( SELECT quote_id, product_id, GROUP_CONCAT(CONCAT({$this->db->dbprefix('quote_items')}.product_name, '__', {$this->db->dbprefix('quote_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('quote_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('quote_items')}.product_id = {$product} ";
            }
            $qi .= " GROUP BY {$this->db->dbprefix('quote_items')}.quote_id ) FQI";
            $this->load->library('datatables');
            $this->datatables
                ->select("date, reference_no, biller, customer, FQI.item_nane as iname, grand_total, status, {$this->db->dbprefix('quotes')}.id as id", false)
                ->from('quotes')
                ->join($qi, 'FQI.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->datatables->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FQI.product_id', $product, false);
            }
            if ($biller) {
                $this->datatables->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('quotes') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getTransfersReport($pdf = null, $xls = null)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = null;
        }

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('transfers') . ".date, transfer_no, (CASE WHEN " . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '<br>') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, ' (', " . $this->db->dbprefix('transfer_items') . ".quantity, ')') SEPARATOR '<br>') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . ".status")
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id')->order_by('transfers.date desc');
            if ($product) {
                $this->db->where($this->db->dbprefix('purchase_items') . ".product_id", $product);
                $this->db->or_where($this->db->dbprefix('transfer_items') . ".product_id", $product);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('transfers_report'))
                    ->SetCellValue('A1', lang('date'))
                    ->SetCellValue('B1', lang('transfer_no'))
                    ->SetCellValue('C1', lang('product_qty'))
                    ->SetCellValue('D1', lang('warehouse') . ' (' . lang('from') . ')')
                    ->SetCellValue('E1', lang('warehouse') . ' (' . lang('to') . ')')
                    ->SetCellValue('F1', lang('grand_total'))
                    ->SetCellValue('G1', lang('status'));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                        ->SetCellValue('B' . $row, $data_row->transfer_no)
                        ->SetCellValue('C' . $row, $data_row->iname)
                        ->SetCellValue('D' . $row, $data_row->fname . ' (' . $data_row->fcode . ')')
                        ->SetCellValue('E' . $row, $data_row->tname . ' (' . $data_row->tcode . ')')
                        ->SetCellValue('F' . $row, $data_row->grand_total)
                        ->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $filename = 'transfers_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('transfers')}.date, transfer_no, (CASE WHEN {$this->db->dbprefix('transfers')}.status = 'completed' THEN  GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___') ELSE GROUP_CONCAT(CONCAT({$this->db->dbprefix('transfer_items')}.product_name, '__', {$this->db->dbprefix('transfer_items')}.quantity) SEPARATOR '___') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, {$this->db->dbprefix('transfers')}.status, {$this->db->dbprefix('transfers')}.id as id", false)
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id');
            if ($product) {
                $this->datatables->where(" (({$this->db->dbprefix('purchase_items')}.product_id = {$product}) OR ({$this->db->dbprefix('transfer_items')}.product_id = {$product})) ", null, false);
            }
            $this->datatables->edit_column("fname", "$1 ($2)", "fname, fcode")
                ->edit_column("tname", "$1 ($2)", "tname, tcode")
                ->unset_column('fcode')
                ->unset_column('tcode');
            echo $this->datatables->generate();
        }
    }

    public function deliveries()
    {
        $this->sma->checkPermissions();

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('deliveries_report')));
        $meta = array('page_title' => lang('deliveries_report'), 'bc' => $bc);
        $this->page_construct('reports/deliveries', $meta, $this->data);
    }

    public function getDeliveriesReport($year = null, $month = null, $pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('deliveries');

        if ($pdf || $xls) {
            $this->db
                ->select("deliveries.id,
                        deliveries.date, 
                        deliveries.do_reference_no,
                        deliveries.return_reference_no,
                        deliveries.sale_reference_no, 
                        deliveries.address,
                        deliveries.status,
                        sales.biller, 
                        warehouses.code as warehouse_code, 
                        warehouses.name as warehouse,
                        companies.cf1 as customer_code,
                        companies.company as customer,
                        delivery_items.product_code,
                        delivery_items.product_name,
                        delivery_items.quantity_sent,
                        delivery_items.product_unit_code,
                        users.first_name,
                        users.last_name,
                        users.username")
                ->from('deliveries')
                ->join('sales', 'sales.id = deliveries.sale_id', 'inner')
                ->join('warehouses', 'warehouses.id = sales.warehouse_id', 'inner')
                ->join('companies', 'companies.id = sales.customer_id', 'inner')
                ->join('users', 'users.id = deliveries.created_by', 'inner')
                ->join('delivery_items', 'deliveries.id = delivery_items.delivery_id', 'left')
                ->where('deliveries.is_deleted', null)
                ->where('sales.company_id', $this->session->userdata('company_id'))
                ->order_by('deliveries.id desc');

            if ($month != '-') {
                $this->db->where('month(sma_deliveries.date)', $month);
            }
            if ($year != '-') {
                $this->db->where('year(sma_deliveries.date)', $year);
            }
            // if (!$this->Owner && !$this->Principal) {
            //     $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
            // }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('deliveries_report'));
                $sheet->SetCellValue('A1', lang('date'))
                    ->SetCellValue('B1', lang('reference_no'))
                    ->SetCellValue('C1', lang('sale_reference_no'))
                    ->SetCellValue('D1', lang('delivery_status'))
                    ->SetCellValue('E1', lang('return_reference_no'))
                    ->SetCellValue('F1', lang('address'))
                    ->SetCellValue('G1', lang('biller'))
                    ->SetCellValue('H1', lang('warehouse_code'))
                    ->SetCellValue('I1', lang('warehouse'))
                    ->SetCellValue('J1', lang('customer_code'))
                    ->SetCellValue('K1', lang('customer'))
                    ->SetCellValue('L1', lang('product_code'))
                    ->SetCellValue('M1', lang('product'))
                    ->SetCellValue('N1', lang('quantity'))
                    ->SetCellValue('O1', lang('product_unit'))
                    ->SetCellValue('P1', lang('created_by'));

                $row = 2;
                $index = 0;
                $RowCounter = 2;
                $merge = false;
                $tags = array("<p>", "</p>", "<br>");
                foreach ($data as $data_row) {
                    $address = str_replace($tags, " ", $data_row->address);
                    $created_by = $data_row->first_name . ' ' . $data_row->last_name . ' [' . $data_row->username . ']';
                    if ($index > 0) {
                        if ($sheet->getCell('B' . $RowCounter)->getValue() == $data_row->do_reference_no) {
                            $sheet->SetCellValue('L' . $row, $data_row->product_code)
                                ->SetCellValue('M' . $row, $data_row->product_name)
                                ->SetCellValue('N' . $row, $data_row->quantity_sent)
                                ->SetCellValue('O' . $row, $data_row->product_unit_code);
                            $merge = true;
                        } else {
                            if ($merge) {
                                $sheet->mergeCells('A' . ($RowCounter) . ':A' . ($row - 1))
                                    ->mergeCells('B' . ($RowCounter) . ':B' . ($row - 1))
                                    ->mergeCells('C' . ($RowCounter) . ':C' . ($row - 1))
                                    ->mergeCells('D' . ($RowCounter) . ':D' . ($row - 1))
                                    ->mergeCells('E' . ($RowCounter) . ':E' . ($row - 1))
                                    ->mergeCells('F' . ($RowCounter) . ':F' . ($row - 1))
                                    ->mergeCells('G' . ($RowCounter) . ':G' . ($row - 1))
                                    ->mergeCells('H' . ($RowCounter) . ':H' . ($row - 1))
                                    ->mergeCells('I' . ($RowCounter) . ':I' . ($row - 1))
                                    ->mergeCells('J' . ($RowCounter) . ':J' . ($row - 1))
                                    ->mergeCells('K' . ($RowCounter) . ':K' . ($row - 1))
                                    ->mergeCells('P' . ($RowCounter) . ':P' . ($row - 1));

                                $sheet->SetCellValue('A' . $row, $data_row->date)
                                    ->SetCellValue('B' . $row, $data_row->do_reference_no)
                                    ->SetCellValue('C' . $row, $data_row->sale_reference_no)
                                    ->SetCellValue('D' . $row, $data_row->status)
                                    ->SetCellValue('E' . $row, $data_row->return_reference_no)
                                    ->SetCellValue('F' . $row, $address)
                                    ->SetCellValue('G' . $row, $data_row->biller)
                                    ->SetCellValue('H' . $row, $data_row->warehouse_code)
                                    ->SetCellValue('I' . $row, $data_row->warehouse)
                                    ->SetCellValue('J' . $row, str_replace("IDC-", "", $data_row->customer_code))
                                    ->SetCellValue('K' . $row, $data_row->customer)
                                    ->SetCellValue('L' . $row, $data_row->product_code)
                                    ->SetCellValue('M' . $row, $data_row->product_name)
                                    ->SetCellValue('N' . $row, $data_row->quantity_sent)
                                    ->SetCellValue('O' . $row, $data_row->product_unit_code)
                                    ->SetCellValue('P' . $row, $created_by);
                                $merge = false;
                            } else {
                                $sheet->SetCellValue('A' . $row, $data_row->date)
                                    ->SetCellValue('B' . $row, $data_row->do_reference_no)
                                    ->SetCellValue('C' . $row, $data_row->sale_reference_no)
                                    ->SetCellValue('D' . $row, $data_row->status)
                                    ->SetCellValue('E' . $row, $data_row->return_reference_no)
                                    ->SetCellValue('F' . $row, $address)
                                    ->SetCellValue('G' . $row, $data_row->biller)
                                    ->SetCellValue('H' . $row, $data_row->warehouse_code)
                                    ->SetCellValue('I' . $row, $data_row->warehouse)
                                    ->SetCellValue('J' . $row, str_replace("IDC-", "", $data_row->customer_code))
                                    ->SetCellValue('K' . $row, $data_row->customer)
                                    ->SetCellValue('L' . $row, $data_row->product_code)
                                    ->SetCellValue('M' . $row, $data_row->product_name)
                                    ->SetCellValue('N' . $row, $data_row->quantity_sent)
                                    ->SetCellValue('O' . $row, $data_row->product_unit_code)
                                    ->SetCellValue('P' . $row, $created_by);
                            }
                            $total += $data_row->grand_total;
                            $paid += $data_row->paid;
                            $balance += ($data_row->grand_total - $data_row->paid);
                            $RowCounter = $row;
                        }
                        $row++;
                    } else {
                        $sheet->SetCellValue('A' . $row, $data_row->date)
                            ->SetCellValue('B' . $row, $data_row->do_reference_no)
                            ->SetCellValue('C' . $row, $data_row->sale_reference_no)
                            ->SetCellValue('D' . $row, $data_row->status)
                            ->SetCellValue('E' . $row, $data_row->return_reference_no)
                            ->SetCellValue('F' . $row, $address)
                            ->SetCellValue('G' . $row, $data_row->biller)
                            ->SetCellValue('H' . $row, $data_row->warehouse_code)
                            ->SetCellValue('I' . $row, $data_row->warehouse)
                            ->SetCellValue('J' . $row, str_replace("IDC-", "", $data_row->customer_code))
                            ->SetCellValue('K' . $row, $data_row->customer)
                            ->SetCellValue('L' . $row, $data_row->product_code)
                            ->SetCellValue('M' . $row, $data_row->product_name)
                            ->SetCellValue('N' . $row, $data_row->quantity_sent)
                            ->SetCellValue('O' . $row, $data_row->product_unit_code)
                            ->SetCellValue('P' . $row, $created_by);


                        $total += $data_row->grand_total;
                        $paid += $data_row->paid;
                        $balance += ($data_row->grand_total - $data_row->paid);
                        $row++;
                    }
                    $index++;
                }
                if ($merge) {
                    $sheet->mergeCells('A' . ($RowCounter) . ':A' . ($row - 1))
                        ->mergeCells('B' . ($RowCounter) . ':B' . ($row - 1))
                        ->mergeCells('C' . ($RowCounter) . ':C' . ($row - 1))
                        ->mergeCells('D' . ($RowCounter) . ':D' . ($row - 1))
                        ->mergeCells('E' . ($RowCounter) . ':E' . ($row - 1))
                        ->mergeCells('F' . ($RowCounter) . ':F' . ($row - 1))
                        ->mergeCells('G' . ($RowCounter) . ':G' . ($row - 1))
                        ->mergeCells('H' . ($RowCounter) . ':H' . ($row - 1))
                        ->mergeCells('I' . ($RowCounter) . ':I' . ($row - 1))
                        ->mergeCells('J' . ($RowCounter) . ':J' . ($row - 1))
                        ->mergeCells('K' . ($RowCounter) . ':K' . ($row - 1))
                        ->mergeCells('P' . ($RowCounter) . ':P' . ($row - 1));
                }

                $filename = 'deliveries_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $di = "( 
                SELECT 
                di.delivery_id, 
                di.product_id, 
                (GROUP_CONCAT(CONCAT(di.product_name, '__', di.quantity_sent) SEPARATOR '___')) as item_name 
                from 
                {$this->db->dbprefix('delivery_items')} di
                left join sma_deliveries deliv on deliv.id = di.delivery_id
                left join sma_sales sale on sale.id = deliv.sale_id
                WHERE 
                year(deliv.date) = '$year'
                and month(deliv.date) = '$month'
                and sale.is_deleted is null
                and deliv.is_deleted is null
                and sale.company_id = {$this->session->userdata('company_id')}
                GROUP BY di.delivery_id
                ) DPI";
            $this->datatables
                ->select("deliveries.id as id, deliveries.date, deliveries.do_reference_no, deliveries.sale_reference_no, deliveries.customer, DPI.item_name as iname, deliveries.address, deliveries.status")
                ->from('deliveries')
                ->join($di, 'DPI.delivery_id=deliveries.id', 'left')
                ->join('sales', 'sales.id = deliveries.sale_id', 'left')
                ->where('deliveries.is_deleted', null)
                ->where('sales.company_id', $this->session->userdata('company_id'));
            // ->group_by('deliveries.id');
            if (!$this->Admin && !$this->Owner) {
                $this->datatables->where('deliveries.created_by', $this->session->userdata('user_id'));
            }
            if ($this->Admin) {
                $this->datatables->where('sales.company_id', $this->session->userdata('company_id'));
            }

            if ($year && $month) {
                $this->datatables->where('year(sma_deliveries.date)', $year);
                $this->datatables->where('month(sma_deliveries.date)', $month);
            }

            echo $this->datatables->generate();
        }
    }

    public function purchases()
    {
        $this->sma->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['suppliers'] = $this->site->getAllSupplierCompanies();
        // var_dump( $this->data['suppliers']);die;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('purchases_report')));
        $meta = array('page_title' => lang('purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/purchases', $meta, $this->data);
    }

    public function getPurchasesReport($year = '-', $month = '-', $pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('purchases', true);

        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('01/m/Y');
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('d/m/Y', strtotime(date("Y-m-d") . "+ 1 days"));

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {
            $this->db
                ->select("" . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".code as wcode," . $this->db->dbprefix('warehouses') . ".name as wname, supplier," . $this->db->dbprefix('purchase_items') . ".product_code," . $this->db->dbprefix('purchase_items') . ".product_name," . $this->db->dbprefix('purchase_items') . ".quantity, grand_total, paid, " . $this->db->dbprefix('purchases') . ".status, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' '," . $this->db->dbprefix('users') . ".last_name) AS created_by", false)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->join('users', 'purchases.created_by = users.id', 'left')
                //                ->group_by('purchases.id')
                ->order_by('purchases.date desc');
            if (!$this->Owner) {
                $this->db->where("purchases.company_id ", $this->session->userdata('company_id'));
            }
            if ($month != '-') {
                $this->db->where('month(date)', $month);
            }
            if ($year != '-') {
                $this->db->where('year(date)', $year);
            }
            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if (!$this->Owner) {
                $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('purchase_report'))
                    ->SetCellValue('A1', lang('date'))
                    ->SetCellValue('B1', lang('reference_no'))
                    ->SetCellValue('C1', lang('warehouse_code'))
                    ->SetCellValue('D1', lang('warehouse'))
                    ->SetCellValue('E1', lang('supplier'))
                    ->SetCellValue('F1', lang('product_code'))
                    ->SetCellValue('G1', lang('product'))
                    ->SetCellValue('H1', lang('qty'))
                    ->SetCellValue('I1', lang('grand_total'))
                    ->SetCellValue('J1', lang('paid'))
                    ->SetCellValue('K1', lang('balance'))
                    ->SetCellValue('L1', lang('status'))
                    ->SetCellValue('M1', lang('created_by'));
                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                $index = 0;
                $RowCounter = 2;
                $merge = false;
                foreach ($data as $data_row) {
                    if ($index > 0) {
                        if (
                            $sheet->getCell('B' . $RowCounter)->getValue() == $data_row->reference_no &&
                            $sheet->getCell('A' . $RowCounter)->getValue() == $this->sma->hrld($data_row->date)
                        ) {
                            $sheet->SetCellValue('F' . $row, $data_row->product_code);
                            $sheet->SetCellValue('G' . $row, $data_row->product_name);
                            $sheet->SetCellValue('H' . $row, $data_row->quantity);
                            $merge = true;
                            /*
                                Untuk menggabungkan row yang memiliki date dan refrence number sama. sehingga menjadi 1 row saja
                            */
                            // $product_name = $sheet->getCell('E'.$RowCounter)->getValue();
                            // $qty = $sheet->getCell('F'.$RowCounter)->getValue();
                            // $product_name .= ' , '.$data_row->product_name;
                            // $qty .= ' , '.floatVal($data_row->quantity);
                            // $sheet->getCell('E'.$RowCounter)->setValue($product_name);
                            // $sheet->getCell('F'.$RowCounter)->setValue($qty);

                        } else {
                            if ($merge) {
                                $sheet->mergeCells('A' . ($RowCounter) . ':A' . ($row - 1))
                                    ->mergeCells('B' . ($RowCounter) . ':B' . ($row - 1))
                                    ->mergeCells('C' . ($RowCounter) . ':C' . ($row - 1))
                                    ->mergeCells('D' . ($RowCounter) . ':D' . ($row - 1))
                                    ->mergeCells('E' . ($RowCounter) . ':E' . ($row - 1))
                                    ->mergeCells('I' . ($RowCounter) . ':I' . ($row - 1))
                                    ->mergeCells('J' . ($RowCounter) . ':J' . ($row - 1))
                                    ->mergeCells('K' . ($RowCounter) . ':K' . ($row - 1))
                                    ->mergeCells('L' . ($RowCounter) . ':L' . ($row - 1))
                                    ->mergeCells('M' . ($RowCounter) . ':M' . ($row - 1));
                                $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                                    ->SetCellValue('B' . $row, $data_row->reference_no)
                                    ->SetCellValue('C' . $row, $data_row->wcode)
                                    ->SetCellValue('D' . $row, $data_row->wname)
                                    ->SetCellValue('E' . $row, $data_row->supplier)
                                    ->SetCellValue('F' . $row, $data_row->product_code)
                                    ->SetCellValue('G' . $row, $data_row->product_name)
                                    ->SetCellValue('H' . $row, $data_row->quantity)
                                    ->SetCellValue('I' . $row, $data_row->grand_total)
                                    ->SetCellValue('J' . $row, $data_row->paid)
                                    ->SetCellValue('K' . $row, ($data_row->grand_total - $data_row->paid))
                                    ->SetCellValue('L' . $row, $data_row->status)
                                    ->SetCellValue('M' . $row, $data_row->created_by);
                                $merge = false;
                            } else {
                                $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                                    ->SetCellValue('B' . $row, $data_row->reference_no)
                                    ->SetCellValue('C' . $row, $data_row->wcode)
                                    ->SetCellValue('D' . $row, $data_row->wname)
                                    ->SetCellValue('E' . $row, $data_row->supplier)
                                    ->SetCellValue('F' . $row, $data_row->product_code)
                                    ->SetCellValue('G' . $row, $data_row->product_name)
                                    ->SetCellValue('H' . $row, $data_row->quantity)
                                    ->SetCellValue('I' . $row, $data_row->grand_total)
                                    ->SetCellValue('J' . $row, $data_row->paid)
                                    ->SetCellValue('K' . $row, ($data_row->grand_total - $data_row->paid))
                                    ->SetCellValue('L' . $row, $data_row->status)
                                    ->SetCellValue('M' . $row, $data_row->created_by);
                            }
                            $total += $data_row->grand_total;
                            $balance += ($data_row->grand_total - $data_row->paid);
                            $paid += $data_row->paid;
                            $RowCounter = $row;
                        }
                        $row++;
                    } else {
                        $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                            ->SetCellValue('B' . $row, $data_row->reference_no)
                            ->SetCellValue('C' . $row, $data_row->wcode)
                            ->SetCellValue('D' . $row, $data_row->wname)
                            ->SetCellValue('E' . $row, $data_row->supplier)
                            ->SetCellValue('F' . $row, $data_row->product_code)
                            ->SetCellValue('G' . $row, $data_row->product_name)
                            ->SetCellValue('H' . $row, $data_row->quantity)
                            ->SetCellValue('I' . $row, $data_row->grand_total)
                            ->SetCellValue('J' . $row, $data_row->paid)
                            ->SetCellValue('K' . $row, ($data_row->grand_total - $data_row->paid))
                            ->SetCellValue('L' . $row, $data_row->status)
                            ->SetCellValue('M' . $row, $data_row->created_by);
                        $row++;
                        $total += $data_row->grand_total;
                        $paid += $data_row->paid;
                        $balance += ($data_row->grand_total - $data_row->paid);
                    }
                    $index++;
                }
                if ($merge) {
                    $sheet->mergeCells('A' . ($RowCounter) . ':A' . ($row - 1))
                        ->mergeCells('B' . ($RowCounter) . ':B' . ($row - 1))
                        ->mergeCells('C' . ($RowCounter) . ':C' . ($row - 1))
                        ->mergeCells('D' . ($RowCounter) . ':D' . ($row - 1))
                        ->mergeCells('E' . ($RowCounter) . ':E' . ($row - 1))
                        ->mergeCells('I' . ($RowCounter) . ':I' . ($row - 1))
                        ->mergeCells('J' . ($RowCounter) . ':J' . ($row - 1))
                        ->mergeCells('K' . ($RowCounter) . ':K' . ($row - 1))
                        ->mergeCells('L' . ($RowCounter) . ':L' . ($row - 1))
                        ->mergeCells('M' . ($RowCounter) . ':M' . ($row - 1));
                }

                $sheet->getStyle("I" . $row . ":K" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('I' . $row, $total);
                $sheet->SetCellValue('J' . $row, $paid);
                $sheet->SetCellValue('K' . $row, $balance);

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);
                $sheet->getColumnDimension('K')->setWidth(20);
                $sheet->getColumnDimension('L')->setWidth(20);
                $sheet->getColumnDimension('M')->setWidth(20);
                $filename = 'purchase_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
            //$pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ,sma_purchase_items.product_id) FPI";
            $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id) FPI";

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, supplier, 
                (FPI.item_nane) as iname, 
                grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", false)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
            //                 ->group_by('purchases.id');

            if (!$this->Owner) {
                $this->datatables->where("purchases.company_id ", $this->session->userdata('company_id'));
            }
            if ($month != '-') {
                $this->datatables->where('month(date)', $month);
            }
            if ($year != '-') {
                $this->datatables->where('year(date)', $year);
            }
            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, false);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if (!$this->Owner) {
                $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
            }

            echo $this->datatables->generate();
        }
    }

    public function payments()
    {
        $this->sma->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('payments_report')));
        $meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
        $this->page_construct('reports/payments', $meta, $this->data);
    }

    public function getPaymentsReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('payments', true);

        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : null;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : null;
        $sale_ref = $this->input->get('sale_ref') ? $this->input->get('sale_ref') : null;
        $purchase_ref = $this->input->get('purchase_ref') ? $this->input->get('purchase_ref') : null;
        $card = $this->input->get('card') ? $this->input->get('card') : null;
        $cheque = $this->input->get('cheque') ? $this->input->get('cheque') : null;
        $transaction_id = $this->input->get('tid') ? $this->input->get('tid') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if ($start_date) {
            $start_date = $this->sma->fsd($start_date);
            $end_date = $this->sma->fsd($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {
            $this->db
                ->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref," . $this->db->dbprefix('consignment') . ".reference_no as consignment_ref, paid_by, amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->join('consignment', 'payments.consignment_id=consignment.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date desc');
            if (!$this->Owner) {
                $this->db->where("( sales.company_id = " . $this->session->userdata('company_id') . " or purchases.company_id = " . $this->session->userdata('company_id') . " or consignment.company_id=" . $this->session->userdata('company_id') . ")");
                //                $this->db->where("( sales.company_id = ".$this->session->userdata('company_id')." or purchases.company_id = ".$this->session->userdata('company_id')." )" );
            }
            if ($user) {
                $this->db->where('payments.created_by', $user);
            }
            if ($card) {
                $this->db->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->db->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->db->where('payments.transaction_id', $transaction_id);
            }
            // if ($customer) {
            //     $this->db->where('sales.customer_id', $customer);
            // }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $join = "(SELECT sma_companies.id FROM sma_companies JOIN (
                            SELECT cf1, id FROM sma_companies WHERE id = " . $customer . "
                        )cmp ON cmp.cf1 = sma_companies.cf1
                        WHERE group_name = 'biller' OR group_name = 'customer' OR group_name = 'address') comp";

                $this->db->join($join, 'sma_sales.customer_id = comp.id', 'inner');
                // $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('payments_report'))
                    ->SetCellValue('A1', lang('date'))
                    ->SetCellValue('B1', lang('payment_reference'))
                    ->SetCellValue('C1', lang('sale_reference'))
                    ->SetCellValue('D1', lang('purchase_reference'))
                    ->SetCellValue('E1', lang('paid_by'))
                    ->SetCellValue('F1', lang('amount'))
                    ->SetCellValue('G1', lang('type'));
                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                        ->SetCellValue('B' . $row, $data_row->payment_ref)
                        ->SetCellValue('C' . $row, $data_row->sale_ref)
                        ->SetCellValue('D' . $row, $data_row->purchase_ref)
                        ->SetCellValue('E' . $row, lang($data_row->paid_by))
                        ->SetCellValue('F' . $row, $data_row->amount)
                        ->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $sheet->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('F' . $row, $total);

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $filename = 'payments_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, " . $this->db->dbprefix('consignment') . ".reference_no as consignment_ref, paid_by, amount, type,  {$this->db->dbprefix('payments')}.id as id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->join('consignment', 'payments.consignment_id=consignment.id', 'left')
                ->group_by('payments.id');
            if (!$this->Owner) {
                $this->datatables->where("( sales.company_id = " . $this->session->userdata('company_id') . " or purchases.company_id = " . $this->session->userdata('company_id') . " or consignment.company_id=" . $this->session->userdata('company_id') . ")");
                $this->datatables->where("( sales.company_id = " . $this->session->userdata('company_id') . " or purchases.company_id = " . $this->session->userdata('company_id') . ")");
            }
            if ($user) {
                $this->datatables->where('payments.created_by', $user);
            }
            if ($card) {
                $this->datatables->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->datatables->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->datatables->where('payments.transaction_id', $transaction_id);
            }
            // if ($customer) {
            //     $this->datatables->where('sales.customer_id', $customer);
            // }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $join = "(SELECT sma_companies.id FROM sma_companies JOIN (
                            SELECT cf1, id FROM sma_companies WHERE id = " . $customer . "
                        )cmp ON cmp.cf1 = sma_companies.cf1
                        WHERE group_name = 'biller' OR group_name = 'customer' OR group_name = 'address') comp";
                // echo $join;die;
                $this->datatables->join($join, 'sma_sales.customer_id = comp.id', 'inner');
                // $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function customers()
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customers', $meta, $this->data);
    }

    public function getCustomers($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('customers', true);

        if ($pdf || $xls) {
            $join = "(SELECT
                        customer_id,
                        sum( total ) AS total,
                        sum( total_amount ) AS total_amount,
                        sum( paid ) AS paid,
                        sum( balance ) AS balance,
                        sma_companies.* 
                    FROM
                        (
                        SELECT
                            customer_id,
                            count( sma_sales.id ) AS total,
                            COALESCE ( sum( grand_total ), 0 ) AS total_amount,
                            COALESCE ( sum( paid ), 0 ) AS paid,
                            ( COALESCE ( sum( grand_total ), 0 ) - COALESCE ( sum( paid ), 0 ) ) AS balance 
                        FROM
                            sma_sales 
                        WHERE
                            client_id != 'aksestoko' 
                            OR client_id IS NULL 
                            AND sma_sales.biller_id = '" . $this->session->userdata('company_id') . "'
                        GROUP BY
                            sma_sales.customer_id UNION
                        SELECT
                            cmp.id AS customer_id,
                            comp.total,
                            comp.total_amount,
                            comp.paid,
                            comp.balance 
                        FROM
                            sma_companies
                            JOIN (
                            SELECT
                                sma_companies.id AS customer_id,
                                count( sma_sales.id ) AS total,
                                COALESCE ( sum( grand_total ), 0 ) AS total_amount,
                                COALESCE ( sum( paid ), 0 ) AS paid,
                                ( COALESCE ( sum( grand_total ), 0 ) - COALESCE ( sum( paid ), 0 ) ) AS balance 
                            FROM
                                sma_sales
                                JOIN sma_users ON sma_users.id = sma_sales.created_by
                                JOIN sma_companies ON sma_users.company_id = sma_companies.id 
                            WHERE
                                sma_sales.client_id = 'aksestoko' 
                                AND sma_sales.biller_id = '" . $this->session->userdata('company_id') . "'
                            GROUP BY
                                created_by 
                            ) comp ON comp.customer_id = sma_companies.id
                            JOIN sma_companies AS cmp ON cmp.cf1 = sma_companies.cf1 
                            AND cmp.group_name = 'customer' 
                        ) AS X
                        JOIN sma_companies ON X.customer_id = sma_companies.id 
                    GROUP BY
                        X.customer_id ) tmp";

            $this->db
                ->select("tmp.id as ids, tmp.company, tmp.name, tmp.phone, tmp.email, tmp.total,tmp.total_amount, tmp.paid, tmp.balance", false)
                ->from("companies")
                ->join($join, 'tmp.customer_id=companies.id')
                ->where('tmp.company_id', $this->session->userdata('company_id'));

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('customers_report'))
                    ->SetCellValue('A1', lang('company'))
                    ->SetCellValue('B1', lang('name'))
                    ->SetCellValue('C1', lang('phone'))
                    ->SetCellValue('D1', lang('email'))
                    ->SetCellValue('E1', lang('total_sales'))
                    ->SetCellValue('F1', lang('total_amount'))
                    ->SetCellValue('G1', lang('paid'))
                    ->SetCellValue('H1', lang('balance'));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->company)
                        ->SetCellValue('B' . $row, $data_row->name)
                        ->SetCellValue('C' . $row, $data_row->phone)
                        ->SetCellValue('D' . $row, $data_row->email)
                        ->SetCellValue('E' . $row, $data_row->total)
                        ->SetCellValue('F' . $row, $this->sma->formatMoney($data_row->total_amount))
                        ->SetCellValue('G' . $row, $this->sma->formatMoney($data_row->paid))
                        ->SetCellValue('H' . $row, $this->sma->formatMoney($data_row->balance));
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $filename = 'customers_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $join = "(SELECT
                        customer_id,
                        sum( total ) AS total,
                        sum( total_amount ) AS total_amount,
                        sum( paid ) AS paid,
                        sum( balance ) AS balance,
                        sma_companies.* 
                    FROM
                        (
                        SELECT
                            customer_id,
                            count( sma_sales.id ) AS total,
                            COALESCE ( sum( grand_total ), 0 ) AS total_amount,
                            COALESCE ( sum( paid ), 0 ) AS paid,
                            ( COALESCE ( sum( grand_total ), 0 ) - COALESCE ( sum( paid ), 0 ) ) AS balance 
                        FROM
                            sma_sales 
                        WHERE
                            client_id != 'aksestoko' 
                            OR client_id IS NULL 
                            AND sma_sales.biller_id = '" . $this->session->userdata('company_id') . "'
                        GROUP BY
                            sma_sales.customer_id UNION
                        SELECT
                            cmp.id AS customer_id,
                            comp.total,
                            comp.total_amount,
                            comp.paid,
                            comp.balance 
                        FROM
                            sma_companies
                            JOIN (
                            SELECT
                                sma_companies.id AS customer_id,
                                count( sma_sales.id ) AS total,
                                COALESCE ( sum( grand_total ), 0 ) AS total_amount,
                                COALESCE ( sum( paid ), 0 ) AS paid,
                                ( COALESCE ( sum( grand_total ), 0 ) - COALESCE ( sum( paid ), 0 ) ) AS balance 
                            FROM
                                sma_sales
                                JOIN sma_users ON sma_users.id = sma_sales.created_by
                                JOIN sma_companies ON sma_users.company_id = sma_companies.id 
                            WHERE
                                sma_sales.client_id = 'aksestoko' 
                                AND sma_sales.biller_id = '" . $this->session->userdata('company_id') . "'
                            GROUP BY
                                sma_sales.created_by 
                            ) comp ON comp.customer_id = sma_companies.id
                            JOIN sma_companies AS cmp ON cmp.cf1 = sma_companies.cf1 
                            AND cmp.group_name = 'customer' 
                        ) AS X
                        JOIN sma_companies ON X.customer_id = sma_companies.id 
                    GROUP BY
                        X.customer_id ) tmp";
            $this->datatables
                ->select("tmp.id as ids, tmp.company, tmp.name, tmp.phone, tmp.email, tmp.total,tmp.total_amount, tmp.paid, tmp.balance", false)
                ->from("companies")
                ->join($join, 'tmp.customer_id=companies.id')
                ->where('tmp.company_id', $this->session->userdata('company_id'))
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "ids")
                ->unset_column('ids');
                // $this->db->get();
                // var_dump($this->db->error());die;
            echo $this->datatables->generate();
        }
    }

    public function customer_report($user_id = null)
    {
        $this->sma->checkPermissions('customers', true);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_customer_selected"));
            redirect('reports/customers');
        }


        $this->data['sales'] = $this->reports_model->getSalesTotalsRev($user_id);
        $this->data['total_sales'] = $this->reports_model->getCustomerSalesRev($user_id);
        $this->data['total_quotes'] = $this->reports_model->getCustomerQuotes($user_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturnsRev($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customer_report', $meta, $this->data);
    }

    public function suppliers()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/suppliers', $meta, $this->data);
    }

    public function getSuppliers($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('suppliers', true);

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count({$this->db->dbprefix('purchases')}.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", false)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('suppliers_report'))
                    ->SetCellValue('A1', lang('company'))
                    ->SetCellValue('B1', lang('name'))
                    ->SetCellValue('C1', lang('phone'))
                    ->SetCellValue('D1', lang('email'))
                    ->SetCellValue('E1', lang('total_purchases'))
                    ->SetCellValue('F1', lang('total_amount'))
                    ->SetCellValue('G1', lang('paid'))
                    ->SetCellValue('H1', lang('balance'));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->company)
                        ->SetCellValue('B' . $row, $data_row->name)
                        ->SetCellValue('C' . $row, $data_row->phone)
                        ->SetCellValue('D' . $row, $data_row->email)
                        ->SetCellValue('E' . $row, $data_row->total)
                        ->SetCellValue('F' . $row, $data_row->total_amount)
                        ->SetCellValue('G' . $row, $data_row->paid)
                        ->SetCellValue('H' . $row, $data_row->balance);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $filename = 'suppliers_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $p = "( SELECT supplier_id, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance from {$this->db->dbprefix('purchases')} WHERE company_id = '" . $this->session->userdata('company_id') . "' GROUP BY {$this->db->dbprefix('purchases')}.supplier_id ) FP";

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, FP.total, FP.total_amount, FP.paid, FP.balance", false)
                ->from("companies")
                ->join($p, 'FP.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                //                ->where('companies.company_id',$this->session->userdata('company_id'))
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/supplier_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');
            echo $this->datatables->generate();
        }
    }

    public function supplier_report($user_id = null)
    {
        $this->sma->checkPermissions('suppliers', true);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_supplier_selected"));
            redirect('reports/suppliers');
        }

        $this->data['purchases'] = $this->reports_model->getPurchasesTotals($user_id);
        $this->data['total_purchases'] = $this->reports_model->getSupplierPurchases($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/supplier_report', $meta, $this->data);
    }

    public function users()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);
        $this->page_construct('reports/users', $meta, $this->data);
    }

    public function getUsers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users') . ".id as id, first_name, last_name, email, company, " . $this->db->dbprefix('groups') . ".name, active")
            ->from("users")
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id');
        // ->where('company_id', NULL);
        if (!$this->Owner) {
            $this->datatables
                ->where('company_id', $this->session->userdata('company_id'))
                ->where('group_id !=', 1);
        }
        $this->datatables
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/staff_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
            ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function staff_report($user_id = null, $year = null, $month = null, $pdf = null, $cal = 0)
    {
        //
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_user_selected"));
            redirect('reports/users');
        }
        //
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['purchases'] = $this->reports_model->getStaffPurchases($user_id);
        $this->data['sales'] = $this->reports_model->getStaffSales($user_id);
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        //
        if (!$year) {
            $year = date('Y');
        }
        if (!$month || $month == '#monthly-con') {
            $month = date('m');
        }
        if ($pdf) {
            if ($cal) {
                $this->monthly_sales($year, $pdf, $user_id);
            } else {
                $this->daily_sales($year, $month, $pdf, $user_id);
            }
        }
        $config = array(
            'show_next_prev' => true,
            'next_prev_url' => site_url('reports/staff_report/' . $user_id),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable reports-table">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th class="text-center"><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th class="text-center" colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th class="text-center"><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);

        $sales = $this->reports_model->getStaffDailySales($user_id, $year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($sale->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        //        if ($this->input->get('pdf')) {
        //
        //        }
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        //        $this->data['msales'] = $this->reports_model->getStaffMonthlySales($user_id, $year);
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);

        $this->page_construct('reports/staff_report', $meta, $this->data);
    }

    public function getUserLogins($id = null, $pdf = null, $xls = null)
    {
        if ($this->input->get('start_date')) {
            $login_start_date = $this->input->get('start_date');
        } else {
            $login_start_date = null;
        }
        if ($this->input->get('end_date')) {
            $login_end_date = $this->input->get('end_date');
        } else {
            $login_end_date = null;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        if ($pdf || $xls) {
            $this->db
                ->select("login, ip_address, time")
                ->from("user_logins")
                ->where('user_id', $id)
                ->order_by('time desc');
            if ($login_start_date) {
                $this->db->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", null, false);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('staff_login_report'))
                    ->SetCellValue('A1', lang('email'))
                    ->SetCellValue('B1', lang('ip_address'))
                    ->SetCellValue('C1', lang('time'));

                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->login)
                        ->SetCellValue('B' . $row, $data_row->ip_address)
                        ->SetCellValue('C' . $row, $this->sma->hrld($data_row->time));
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(35);

                $filename = 'staff_login_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("login, ip_address, DATE_FORMAT(time, '%Y-%m-%d %T') as time")
                ->from("user_logins")
                ->where('user_id', $id);
            if ($login_start_date) {
                $this->datatables->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", null, false);
            }
            echo $this->datatables->generate();
        }
    }

    public function getCustomerLogins($id = null)
    {
        if ($this->input->get('login_start_date')) {
            $login_start_date = $this->input->get('login_start_date');
        } else {
            $login_start_date = null;
        }
        if ($this->input->get('login_end_date')) {
            $login_end_date = $this->input->get('login_end_date');
        } else {
            $login_end_date = null;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("login, ip_address, time")
            ->from("user_logins")
            ->where('customer_id', $id);
        if ($login_start_date) {
            $this->datatables->where('time BETWEEN "' . $login_start_date . '" and "' . $login_end_date . '"');
        }
        echo $this->datatables->generate();
    }

    public function profit_loss($cons = false, $start_date = null, $end_date = null)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['total_purchases'] = ($cons == 1 ? $this->reports_model->getTotalConsignments($start, $end) : $this->reports_model->getTotalPurchases($start, $end));
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end, null, $cons);
        $this->data['total_expenses'] = ($cons == 1 ? 0 : $this->reports_model->getTotalExpenses($start, $end));
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end, $cons);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end, $cons);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);
        $this->data['consignment'] = $cons ? $cons : 0;

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = ($cons == 1 ? $this->reports_model->getTotalConsignments($start, $end, $warehouse->id) : $this->reports_model->getTotalPurchases($start, $end, $warehouse->id));
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id, $cons);
            $total_expenses = $cons == 1 ? 0 : $this->reports_model->getTotalExpenses($start, $end, $warehouse->id);
            $warehouses_report[] = array(
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
                'total_expenses' => $total_expenses,
            );
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('profit_loss')));
        $meta = array('page_title' => lang('profit_loss'), 'bc' => $bc);
        $this->page_construct('reports/profit_loss', $meta, $this->data);
    }

    public function profit_loss_pdf($start_date = null, $end_date = null)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
            $warehouses_report[] = array(
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
            );
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $html = $this->load->view($this->theme . 'reports/profit_loss_pdf', $this->data, true);
        $name = lang("profit_loss") . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['start']) . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['end']) . ".pdf";
        $this->sma->generate_pdf($html, $name, false, false, false, false, false, 'L');
    }

    public function register()
    {
        $this->sma->checkPermissions('register');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('register_report')));
        $meta = array('page_title' => lang('register_report'), 'bc' => $bc);
        $this->page_construct('reports/register', $meta, $this->data);
    }

    public function getRrgisterlogs($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('register', true);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = null;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = null;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = null;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {
            $this->db
                ->select("date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' (', users.email, ')') as user, cash_in_hand, total_cc_slips, total_cheques, total_cash, total_cc_slips_submitted, total_cheques_submitted,total_cash_submitted, note", false)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left')
                ->order_by('date desc');
            //->where('status', 'close');

            if ($user) {
                $this->db->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('register_report'))
                    ->SetCellValue('A1', lang('open_time'))
                    ->SetCellValue('B1', lang('close_time'))
                    ->SetCellValue('C1', lang('user'))
                    ->SetCellValue('D1', lang('cash_in_hand'))
                    ->SetCellValue('E1', lang('cc_slips'))
                    ->SetCellValue('F1', lang('cheques'))
                    ->SetCellValue('G1', lang('total_cash'))
                    ->SetCellValue('H1', lang('cc_slips_submitted'))
                    ->SetCellValue('I1', lang('cheques_submitted'))
                    ->SetCellValue('J1', lang('total_cash_submitted'))
                    ->SetCellValue('K1', lang('note'));

                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                        ->SetCellValue('B' . $row, $data_row->closed_at)
                        ->SetCellValue('C' . $row, $data_row->user)
                        ->SetCellValue('D' . $row, $data_row->cash_in_hand)
                        ->SetCellValue('E' . $row, $data_row->total_cc_slips)
                        ->SetCellValue('F' . $row, $data_row->total_cheques)
                        ->SetCellValue('G' . $row, $data_row->total_cash)
                        ->SetCellValue('H' . $row, $data_row->total_cc_slips_submitted)
                        ->SetCellValue('I' . $row, $data_row->total_cheques_submitted)
                        ->SetCellValue('J' . $row, $data_row->total_cash_submitted)
                        ->SetCellValue('K' . $row, $data_row->note);
                    if ($data_row->total_cash_submitted < $data_row->total_cash || $data_row->total_cheques_submitted < $data_row->total_cheques || $data_row->total_cc_slips_submitted < $data_row->total_cc_slips) {
                        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray(
                            array('fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'F2DEDE')))
                        );
                    }
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(15);
                $sheet->getColumnDimension('K')->setWidth(35);
                $filename = 'register_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    //$sheet->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, '<br>', " . $this->db->dbprefix('users') . ".email) as user, cash_in_hand, CONCAT(total_cc_slips, ' (', total_cc_slips_submitted, ')'), CONCAT(total_cheques, ' (', total_cheques_submitted, ')'), CONCAT(total_cash, ' (', total_cash_submitted, ')'), note", false)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left');

            if (!$this->Owner) {
                $this->datatables->where('users.company_id', $this->session->userdata('company_id'));
            }

            if ($user) {
                $this->datatables->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function expenses($id = null)
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['categories'] = $this->reports_model->getExpenseCategories($this->session->userdata('company_id'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('expenses')));
        $meta = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->page_construct('reports/expenses', $meta, $this->data);
    }

    public function getExpensesReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('expenses');

        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;
        $note = $this->input->get('note') ? $this->input->get('note') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {
            $this->db
                ->select("date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
                ->from('expenses')
                ->join('users', 'users.id=expenses.created_by', 'left')
                ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
                ->group_by('expenses.id');
            if (!$this->Owner) {
                $this->db->where('users.company_id', $this->session->userdata('company_id'));
            }
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }

            if ($note) {
                $this->db->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->db->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->db->where('category_id', $category);
            }
            if ($user) {
                $this->db->where('created_by', $user);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('expenses_report'))
                    ->SetCellValue('A1', lang('date'))
                    ->SetCellValue('B1', lang('reference_no'))
                    ->SetCellValue('C1', lang('category'))
                    ->SetCellValue('D1', lang('amount'))
                    ->SetCellValue('E1', lang('note'))
                    ->SetCellValue('F1', lang('created_by'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                        ->SetCellValue('B' . $row, $data_row->reference)
                        ->SetCellValue('C' . $row, $data_row->category)
                        ->SetCellValue('D' . $row, $data_row->amount)
                        ->SetCellValue('E' . $row, $data_row->note)
                        ->SetCellValue('F' . $row, $data_row->created_by);
                    $total += $data_row->amount;
                    $row++;
                }
                $sheet->getStyle("D" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('D' . $row, $total);

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(25);

                $filename = 'expenses_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    //$sheet->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
                ->from('expenses')
                ->join('users', 'users.id=expenses.created_by', 'left')
                ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
                ->group_by('expenses.id');
            if (!$this->Owner) {
                $this->datatables->where('users.company_id', $this->session->userdata('company_id'));
            }
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('created_by', $this->session->userdata('user_id'));
            }

            if ($note) {
                $this->datatables->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->datatables->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->datatables->where('category_id', $category);
            }
            if ($user) {
                $this->datatables->where('created_by', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function daily_purchases($warehouse_id = null, $year = null, $month = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => true,
            'next_prev_url' => site_url('reports/daily_purchases/' . ($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $purchases = $user_id ? $this->reports_model->getStaffDailyPurchases($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailyPurchases($year, $month, $warehouse_id);

        if (!empty($purchases)) {
            foreach ($purchases as $purchase) {
                $daily_purchase[$purchase->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($purchase->discount) . "</td></tr><tr><td>" . lang("shipping") . "</td><td>" . $this->sma->formatMoney($purchase->shipping) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($purchase->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($purchase->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($purchase->total) . "</td></tr></table>";
            }
        } else {
            $daily_purchase = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_purchase);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_purchases") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_purchases_report')));
        $meta = array('page_title' => lang('daily_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/daily_purchases', $meta, $this->data);
    }


    public function monthly_purchases($warehouse_id = null, $year = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['purchases'] = $user_id ? $this->reports_model->getStaffMonthlyPurchases($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlyPurchases($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang("monthly_purchases") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_purchases_report')));
        $meta = array('page_title' => lang('monthly_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly_purchases', $meta, $this->data);
    }

    public function adjustments($warehouse_id = null)
    {
        $this->sma->checkPermissions('products');

        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('adjustments_report')));
        $meta = array('page_title' => lang('adjustments_report'), 'bc' => $bc);
        $this->page_construct('reports/adjustments', $meta, $this->data);
    }

    public function getAdjustmentReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);

        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {
            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, ' (', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END), ')') SEPARATOR '\n') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";

            $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname, {$this->db->dbprefix('adjustments')}.id as id", false)
                ->from('adjustments')
                ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
                ->join('users', 'users.id=adjustments.created_by', 'left')
                ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if (!$this->Owner) {
                $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
            }

            if ($user) {
                $this->db->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->db->where('FAI.product_id', $product, false);
            }
            if ($serial) {
                $this->db->like('FAI.serial_no', $serial, false);
            }
            if ($warehouse) {
                $this->db->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('adjustments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('adjustments_report'))
                    ->SetCellValue('A1', lang('date'))
                    ->SetCellValue('B1', lang('reference_no'))
                    ->SetCellValue('C1', lang('warehouse'))
                    ->SetCellValue('D1', lang('created_by'))
                    ->SetCellValue('E1', lang('note'))
                    ->SetCellValue('F1', lang('products'));

                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date))
                        ->SetCellValue('B' . $row, $data_row->reference_no)
                        ->SetCellValue('C' . $row, $data_row->wh_name)
                        ->SetCellValue('D' . $row, $data_row->created_by)
                        ->SetCellValue('E' . $row, $this->sma->decode_html($data_row->note))
                        ->SetCellValue('F' . $row, $data_row->iname);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(30);
                $filename = 'adjustments_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    $sheet->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, '__', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END)) SEPARATOR '___') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id ";
            if ($product) {
                $ai .= " WHERE {$this->db->dbprefix('adjustment_items')}.product_id = {$product} ";
            }
            $ai .= " GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname, {$this->db->dbprefix('adjustments')}.id as id", false)
                ->from('adjustments')
                ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
                ->join('users', 'users.id=adjustments.created_by', 'left')
                ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if (!$this->Owner) {
                $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
            }

            if ($user) {
                $this->datatables->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FAI.product_id', $product, false);
            }
            if ($serial) {
                $this->datatables->like('FAI.serial_no', $serial, false);
            }
            if ($warehouse) {
                $this->datatables->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('adjustments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function get_deposits($company_id = null)
    {
        $this->sma->checkPermissions('customers', true);
        $this->load->library('datatables');
        $this->datatables
            ->select("date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note", false)
            ->from("deposits")
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits') . '.company_id', $company_id);
        echo $this->datatables->generate();
    }

    public function monitoring()
    {
        $this->load->model('products_model');
        $start_date = ($this->input->post('start_date') ? explode(" ", $this->sma->fld($this->input->post('start_date'))) : $this->input->post('start_date'));
        $end_date = ($this->input->post('end_date') ? explode(" ", $this->sma->fld($this->input->post('end_date'))) : $this->input->post('end_date'));
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['products'] = $this->products_model->getAllProducts();
        $i = 1;
        foreach ($this->data['products'] as $p) {
            if ($p->company_id == 1) {
                //                $this->data['sales'.$i.'']=$this->reports_model->getSales($this->input->post('biller'),$p->code,$start_date[0],$end_date[0]);
                //                $this->data['purchases'.$i.'']=$this->reports_model->getPurchases($this->input->post('biller'),$p->code,$start_date[0],$end_date[0]);
                $purchases[] = $this->reports_model->getPurchases($this->input->post('biller'), $p->code, $start_date[0], $end_date[0]);
                $sales[] = $this->reports_model->getSales($this->input->post('biller'), $p->code, $start_date[0], $end_date[0]);
            }
            $i++;
        }
        $this->data['sales'] = $sales;
        $this->data['purchases'] = $purchases;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monitoring_report')));
        $meta = array('page_title' => lang('monitoring_report'), 'bc' => $bc);

        $this->page_construct('reports/monitoring', $meta, $this->data);
    }

    public function stock_card($warehouse_id = null)
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        //        $this->data['categories'] = $this->site->getAllCategories();
        //        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('stock_card_report')));
        $meta = array('page_title' => lang('stock_card_report'), 'bc' => $bc);
        $this->page_construct('reports/stock_card', $meta, $this->data);
    }

    public function getStockCard($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        $product = $this->input->get('product') ?? null;
        $warehouse = $this->input->get('warehouse') ?? null;
        $start_date = $this->input->get('start_date') ? $this->sma->fld($this->input->get('start_date')) : date('Y-m-d');
        $end_date = $this->input->get('end_date') ? $this->sma->fld($this->input->get('end_date')) : date('Y-m-d');

        $end_date = new DateTime($end_date);
        $end_date->modify('+1 day');
        $end_date = $end_date->format('Y-m-d');

        // var_dump($start_date, $end_date);
        // die;
        //        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
        //            $user = $this->site->getUser();
        //            $warehouse_id = $user->warehouse_id;
        //        }

        // $union="(SELECT s.company_id  , si.product_id  , si.warehouse_id, s.id, s.date, s.reference_no, si.product_name, NULL as masuk, si.quantity as keluar 
        //     FROM ".$this->db->dbprefix('sales')." as s
        //     LEFT JOIN ".$this->db->dbprefix('sale_items')." as si ON s.id=si.sale_id
        //     WHERE si.flag is null
        //     UNION ALL(
        //         SELECT p.company_id  , pi.product_id  , pi.warehouse_id, p.id, p.date, p.reference_no, pi.product_name, pi.quantity as masuk, NULL as keluar 
        //         FROM ".$this->db->dbprefix('purchases')." as p
        //         LEFT JOIN ".$this->db->dbprefix('purchase_items')." as pi ON p.id=pi.purchase_id
        //     )
        //     UNION(
        //         SELECT * 
        //         FROM(SELECT swp.company_id as company_id, swp.product_id, swp.warehouse_id, swp.id, swp.date, ' ' as reference_no, CONCAT(p.name,' added by product') as pname, swp.quantity as masuk, NULL as keluar
        //             FROM story_warehouses_products as swp
        //             LEFT JOIN sma_purchase_items as pi ON swp.product_id=pi.product_id
        //             LEFT JOIN sma_products as p ON swp.product_id=p.id
        //             WHERE pi.purchase_id is null
        //             GROUP BY swp.id ORDER BY swp.date ASC) as temp GROUP BY temp.company_id, temp.product_id, temp.warehouse_id
        //     )) as union_sp";

        // $get_total_in="get_total_in(union_sp.date,union_sp.company_id,union_sp.warehouse_id,union_sp.product_id)";
        // $get_total_out="get_total_out(union_sp.date,union_sp.company_id,union_sp.warehouse_id,union_sp.product_id)";
        //        $select_function="sum_stok_previous({$this->db->dbprefix('sale_items')}.product_id,{$this->db->dbprefix('sale_items')}.warehouse_id,{$this->db->dbprefix('sales')}.company_id,{$this->db->dbprefix('sales')}.id)";

        // echo "union_sp.id, union_sp.date, union_sp.reference_no, {$this->db->dbprefix('products')}.name as name, COALESCE(".$get_total_in.",0)-COALESCE(".$get_total_out.",0)+COALESCE(keluar,0)-COALESCE(masuk,0) as stock_awal, COALESCE(masuk,0), COALESCE(keluar,0), ".$get_total_in."-COALESCE(".$get_total_out.",0) as stok_akhir FROM sma_products LEFT JOIN ".$union." ON union_sp.product_id=products.id";
        // exit;
        $this->load->library('datatables');
        // $this->datatables->select("union_sp.id, union_sp.date, union_sp.reference_no, {$this->db->dbprefix('products')}.name as name, COALESCE(".$get_total_in.",0)-COALESCE(".$get_total_out.",0)+COALESCE(keluar,0)-COALESCE(masuk,0) as stock_awal, COALESCE(masuk,0), COALESCE(keluar,0), ".$get_total_in."-COALESCE(".$get_total_out.",0) as stok_akhir")
        //         ->from('products')
        //         ->join($union, 'union_sp.product_id=products.id', 'left');

        $this->datatables->select("sma_report_stock_card.id, sma_report_stock_card.created_at, sma_report_stock_card.reference_no, sma_warehouses.name, sma_report_stock_card.product_name, ROUND(sma_report_stock_card.stock_awal), ROUND(sma_report_stock_card.masuk), ROUND(sma_report_stock_card.keluar), ROUND(sma_report_stock_card.stok_akhir)")
            ->from('report_stock_card')
            ->join('sma_warehouses', 'sma_warehouses.id  = report_stock_card.warehouse_id')
            ->where('sma_report_stock_card.is_deleted IS NULL');
        //->where('(sma_report_stock_card.status = "returned" OR sma_report_stock_card.status = "completed" OR sma_report_stock_card.status = "received")');

        if ($this->Admin) {
            $this->datatables->where("sma_report_stock_card.company_id", $this->session->userdata('company_id'));
        }
        if ($warehouse) {
            $this->datatables->where('sma_report_stock_card.warehouse_id', $warehouse);
        }
        if ($product) {
            $this->datatables->where('sma_report_stock_card.product_id', $product);
        }
        if ($start_date && $end_date) {
            // $day=$this->sma->fsd($date);
            // $this->datatables->where("sma_report_stock_card.date >= ", $start_date);
            // $this->datatables->where("sma_report_stock_card.date <= ", $end_date);

            $this->datatables->where("sma_report_stock_card.created_at BETWEEN '" . $start_date . "' AND '" . $end_date . "'");
        }

        $this->datatables->unset_column("sma_report_stock_card.id");
        echo $this->datatables->generate();
    }

    public function getExportStockCard($export_to = null)
    {
        $this->sma->checkPermissions('index');

        $product = $this->input->get('product') ?? null;
        $warehouse = $this->input->get('warehouse') ?? null;
        $start_date = $this->input->get('start_date') ? $this->sma->fld($this->input->get('start_date')) : date('Y-m-d');
        $end_date = $this->input->get('end_date') ? $this->sma->fld($this->input->get('end_date')) : date('Y-m-d');

        $end_date = new DateTime($end_date);
        $end_date->modify('+1 day');
        $end_date = $end_date->format('Y-m-d');

        // $product = $product != '-' ? $product : null;
        // $warehouse = $warehouse != '-' ? $warehouse : null;
        // $start_date = $start_date != '-' ? $this->sma->fld($start_date) : date('Y-m-d');
        // $end_date = $end_date != '-' ? $this->sma->fld($end_date) : date('Y-m-d');

        $this->db->select("sma_report_stock_card.id, sma_report_stock_card.created_at AS date, sma_report_stock_card.reference_no, sma_warehouses.name, sma_report_stock_card.product_name, ROUND(sma_report_stock_card.stock_awal) as 'stock_awal', ROUND(sma_report_stock_card.masuk) as 'masuk', ROUND(sma_report_stock_card.keluar) as 'keluar', ROUND(sma_report_stock_card.stok_akhir) as 'stok_akhir'")
            ->from('report_stock_card')
            ->join('sma_warehouses', 'sma_warehouses.id  = report_stock_card.warehouse_id')
            ->where('sma_report_stock_card.is_deleted IS NULL');
        //->where('(sma_report_stock_card.status = "returned" OR sma_report_stock_card.status = "completed" OR sma_report_stock_card.status = "received")');

        if ($this->Admin) {
            $this->db->where("sma_report_stock_card.company_id", $this->session->userdata('company_id'));
        }
        if ($warehouse) {
            $this->db->where('sma_report_stock_card.warehouse_id', $warehouse);
        }
        if ($product) {
            $this->db->where('sma_report_stock_card.product_id', $product);
        }
        if ($start_date && $end_date) {
            $this->db->where("sma_report_stock_card.created_at BETWEEN '" . $start_date . "' AND '" . $end_date . "'");
        }

        $list_stock_card = $this->db->get()->result();

        if ($export_to) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('report'))
                ->SetCellValue('A1', lang('report_stock_card_date'))
                ->SetCellValue('B1', lang('report_stock_card_ref_no'))
                ->SetCellValue('C1', lang('report_stock_card_warehouse_name'))
                ->SetCellValue('D1', lang('report_stock_card_product_name'))
                ->SetCellValue('E1', lang('report_stock_card_stock_first'))
                ->SetCellValue('F1', lang('report_stock_card_stock_in'))
                ->SetCellValue('G1', lang('report_stock_card_stock_out'))
                ->SetCellValue('H1', lang('report_stock_card_stock_last'));
            $row = 2;
            foreach ($list_stock_card as $stock_card) {
                $sheet->SetCellValue('A' . $row, $stock_card->date)
                    ->SetCellValue('B' . $row, $stock_card->reference_no)
                    ->SetCellValue('C' . $row, $stock_card->name)
                    ->SetCellValue('D' . $row, $stock_card->product_name)
                    ->SetCellValue('E' . $row, $stock_card->stock_awal)
                    ->SetCellValue('F' . $row, $stock_card->masuk)
                    ->SetCellValue('G' . $row, $stock_card->keluar)
                    ->SetCellValue('H' . $row, $stock_card->stok_akhir);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);

            $filename = 'stock_card_' . date('Y_m_d_H_i_s');
            if ($export_to == 'pdf') {
                $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                $sheet->getDefaultStyle()->applyFromArray($styleArray);
                $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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

            if ($export_to == 'xls') {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');

                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                return $objWriter->save('php://output');
            }

            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    // public function promotion($value='')
    // {
    //     # code...
    // }

    public function promotion()
    {
        if (!$this->Owner && !$this->Principal) {
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('promotion_report')));
        $meta = array('page_title' => lang('promotion_report'), 'bc' => $bc);
        $this->page_construct('reports/promotion', $meta, $this->data);
    }

    public function getPromotionReport($year, $month)
    {
        $this->load->library('datatables');
        $this->datatables->select("{$this->db->dbprefix('promo')}.name,{$this->db->dbprefix('companies')}.company,{$this->db->dbprefix('purchases')}.supplier, {$this->db->dbprefix('transaction_promo')}.date")
            ->from("transaction_promo")
            ->join('companies', 'companies.id=transaction_promo.company_id', 'left')
            ->join('promo', 'promo.id=transaction_promo.promo_id', 'left')
            ->join('purchases', 'purchases.id=transaction_promo.purchase_id', 'left');
        // if ($mont != null || $year=null) {
        $this->datatables->where('month(sma_transaction_promo.date)', $month);
        $this->datatables->where('year(sma_transaction_promo.date)', $year);
        // }
        echo $this->datatables->generate();
    }

    public function promotionReportAction()
    {
        $this->load->model('promo_model');

        if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
            $data = $this->promo_model->getTransactionPromo($this->input->post('monthly'), $this->input->post('annually'));
        } else {
            $data = $this->promo_model->getTransactionPromo();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle(lang('adjustments_report'))
            ->SetCellValue('A1', 'Promo Name')
            ->SetCellValue('B1', 'Toko')
            ->SetCellValue('C1', 'Distibutor')
            ->SetCellValue('D1', 'Date');
        $row = 2;
        foreach ($data as $data_row) {
            $sheet->SetCellValue('A' . $row, $data_row->name)
                ->SetCellValue('B' . $row, $data_row->company)
                ->SetCellValue('C' . $row, $data_row->supplier)
                ->SetCellValue('D' . $row, $data_row->date);
            //  ->SetCellValue('E' . $row, $this->sma->decode_html($data_row->note));
            //  ->SetCellValue('F' . $row, $data_row->iname);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $filename = 'Promotion';
        $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
        if ($this->input->post('form_action') == 'export_all_excel' || $this->input->post('form_action') == 'export_excel') {
            $sheet->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
            ob_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            ob_clean();
            $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
            ob_end_clean();
            $objWriter->save('php://output');
            exit();
        }
        // print_r($this->input->post('form_action'));die;
        if ($this->input->post('form_action') == 'export_pdf' || $this->input->post('form_action') == 'export_all_pdf') {
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    )
                )
            );
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
            $objWriter->save('php://output');
            exit();
        }
        // print_r($data);
    }
    /*
    function getPromotionReport() {
        $type = $this->input->get('type');
        $validity = $this->input->get('validity');

        $uni="(";
        if($type){
            $kind=($type=='bonus'?lang('bonus'):( $type=='multiple_discount'?lang('discount'):lang('gross') ));
            $uni.="SELECT id, warehouse_id, product_id, quantity, start_date, end_date, company_id
                FROM {$this->db->dbprefix($type)}
                WHERE (is_deleted is null OR is_deleted=0)";
        }else{
            $uni.="SELECT md.id, md.warehouse_id, md.product_id, md.quantity, md.start_date, md.end_date, md.company_id, '".lang('discounts')."' as type FROM {$this->db->dbprefix('multiple_discount')} as md
                WHERE (md.is_deleted is null OR md.is_deleted=0)
                UNION ALL(
                    SELECT b.id, b.warehouse_id,b.product_id,b.quantity,b.start_date,b.end_date, b.company_id, '".lang('bonus')."' as type FROM {$this->db->dbprefix('bonus')} as b
                    WHERE (b.is_deleted is null OR b.is_deleted=0)
                )
                UNION ALL(
                    SELECT g.id, g.warehouse_id, g.product_id,g.quantity,g.start_date,g.end_date, g.company_id, '".lang('gross')."' as type FROM {$this->db->dbprefix('gross')} as g
                    WHERE (g.is_deleted is null OR g.is_deleted=0)
                )";
        }
        $uni.=") as union_promo";
        $this->load->library('datatables');

        $this->datatables->select("union_promo.id, ".($type?"'".$kind."'":"union_promo.type")." as type, {$this->db->dbprefix('products')}.name as pname, {$this->db->dbprefix('warehouses')}.name as wname, union_promo.start_date, union_promo.end_date")
                ->from('products');

        $this->datatables->join($uni,'products.id=union_promo.product_id','left')
                ->join('warehouses','warehouses.id=union_promo.warehouse_id','left');
        if($this->Admin){
            $this->datatables->where('union_promo.company_id',$this->session->userdata('company_id'));
        }
        if($validity){
            $this->datatables->where('union_promo.end_date<NOW()');
        }else{
            $this->datatables->where('union_promo.end_date>NOW()');
        }

        $this->datatables->unset_column('union_promo.id');
        echo $this->datatables->generate();
    }*/


    public function products_warehouse()
    {
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }

        // $this->sma->checkPermissions();
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->site->getNameAndIdWarehouses();
        $this->data['companies']  = $this->site->getCompaniesByGroupName('biller');
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_report')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
        $this->page_construct('reports/products_warehouse', $meta, $this->data);
    }

    public function get_warehouse_by_company($company_id = null)
    {
        $this->db->select("id, name")->from("sma_warehouses");
        if ($company_id != 0) {
            $this->db->where("company_id", $company_id);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }
        $output = "<option value=''>Select Warehouse</option>";
        foreach ($data as $k) {
            $output .= "<option value='{$k->id}'>{$k->name}</option>";
        }
        echo $output;
    }

    public function get_products_warehouse($company_id = null, $warehouse_id = null, $pdf = null, $xls = null)
    {
        if ($pdf || $xls) {
            $this->db
                ->select("sma_companies.country AS 'Propinsi',
                            sma_companies.company AS 'Distributor',
                            sma_warehouses.NAME AS 'Nama_Gudang',
                            sma_warehouses.address,
                            sma_products.CODE AS 'Kode_Produk',
                            sma_products.NAME AS 'Nama_Produk',
                            CAST( sma_warehouses_products.quantity AS UNSIGNED INTEGER ) AS 'Stok_Gudang'", false)
                ->from('sma_warehouses_products')
                ->join('sma_products', 'sma_products.id = sma_warehouses_products.product_id', 'left')
                ->join('sma_warehouses', 'sma_warehouses_products.warehouse_id=sma_warehouses.id', 'left')
                ->join('sma_companies', 'sma_warehouses_products.company_id = sma_companies.id', 'left')
                ->where('sma_warehouses_products.quantity > 0')
                ->like('sma_products.NAME', 'semen', false);
            if ($company_id != 0) {
                $this->db->where('sma_companies.id = ' . $company_id);
            }
            if ($warehouse_id != 0) {
                $this->db->where('sma_warehouses.id = ' . $warehouse_id);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('product_quantity_alerts'))
                    ->SetCellValue('A1', lang("Province"))
                    ->SetCellValue('B1', lang("Distributor"))
                    ->SetCellValue('C1', lang("Warehouse"))
                    ->SetCellValue('D1', lang("Address"))
                    ->SetCellValue('E1', lang("product_code"))
                    ->SetCellValue('F1', lang("product_name"))
                    ->SetCellValue('G1', lang("Stock"));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->Propinsi)
                        ->SetCellValue('B' . $row, $data_row->Distributor)
                        ->SetCellValue('C' . $row, $data_row->Nama_Gudang)
                        ->SetCellValue('D' . $row, $data_row->address)
                        ->SetCellValue('E' . $row, $data_row->Kode_Produk)
                        ->SetCellValue('F' . $row, $data_row->Nama_Produk)
                        ->SetCellValue('G' . $row, $data_row->Stok_Gudang);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);

                $filename = 'product_quantity_alerts';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    // var_dump('pdf');die;
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    // var_dump('xls');die;
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".country AS Provinsi, " . $this->db->dbprefix('companies') . ".company AS nama_peusahaan," . $this->db->dbprefix('warehouses') . ".NAME as nama_gudang, " . $this->db->dbprefix('warehouses') . ".address AS ALAMAT, " . $this->db->dbprefix('products') . ".CODE AS Kode, " . $this->db->dbprefix('products') . ".NAME AS product_name, CAST( " . $this->db->dbprefix('warehouses_products') . ".quantity AS UNSIGNED INTEGER ) AS STock", false)
                ->from('sma_warehouses_products')
                ->join('sma_products', 'sma_products.id = sma_warehouses_products.product_id', 'left')
                ->join('sma_warehouses', 'sma_warehouses_products.warehouse_id=sma_warehouses.id', 'left')
                ->join('sma_companies', 'sma_warehouses_products.company_id = sma_companies.id', 'left')
                ->where('sma_warehouses_products.quantity > 0')
                ->like('sma_products.NAME', 'semen', false);
            if ($company_id != 0) {
                $this->datatables->where('sma_companies.id = ' . $company_id);
            }
            if ($warehouse_id != 0) {
                $this->datatables->where('sma_warehouses.id = ' . $warehouse_id);
            }
            echo $this->datatables->generate();
        }
    }

    public function quantum_sale_by_date()
    {
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }

        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

        $this->data['companies']  = $this->site->getCompaniesByGroupName('biller');
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('quantum_sale_by_date')));
        $meta = array('page_title' => lang('quantum_sale_by_date'), 'bc' => $bc);
        $this->page_construct('reports/quantum_sale_by_date', $meta, $this->data);
    }

    public function get_quantum_sale_by_date($start_date = '-', $end_date = '-', $company_id = null)
    {
        if ($this->input->post('form_action')) {
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $warehouse = $this->input->post('warehouse');

            if ($start_date != null) {
                $start_date = str_replace("/", "-", $start_date);
                $start_date = date("Y-m-d", strtotime($start_date));
            }

            if ($end_date != null) {
                $end_date = str_replace("/", "-", $end_date);
                $end_date = date("Y-m-d", strtotime($end_date . "+ 1 day"));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }


            $this->db
                ->select(
                    "DATE_FORMAT( " . $this->db->dbprefix('sales') . ".date, '%d/%m/%Y' ) AS tanggal, "
                        . $this->db->dbprefix('warehouses') . ".code AS warehouse_code, "
                        . $this->db->dbprefix('companies') . ".company AS nama_comp, "
                        . $this->db->dbprefix('warehouses') . ".name AS nama_gudang, "
                        . $this->db->dbprefix('warehouses') . ".address AS ALAMAT, "
                        . $this->db->dbprefix('sale_items') . ".product_code AS product_code, "
                        . $this->db->dbprefix('sale_items') . ".product_name AS Nama_Produk,
                    CAST( sum( " . $this->db->dbprefix('sale_items') . ".quantity ) AS UNSIGNED INTEGER ) AS sale
                    ",
                    false
                )
                ->from('sma_sale_items')
                ->join('sma_sales', 'sma_sales.id = sma_sale_items.sale_id', 'left')
                ->join('sma_products', 'sma_products.id = sma_sale_items.product_id', 'left')
                ->join('sma_warehouses', 'sma_sales.warehouse_id = sma_warehouses.id', 'left')
                ->join('sma_companies', 'sma_sales.company_id = sma_companies.id ', 'left');
            $this->db->like('sma_products.NAME', 'semen', false);
            if ($start_date == null && $end_date == null) {
                $cur = date('Y-m-d');
                $this->db->where("sma_sales.date = CAST( \"" . $cur . "\" AS DATE )");
            } elseif ($start_date != null && $end_date == null) {
                $this->db->where("sma_sales.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == null && $end_date != null) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $f_end_date . '" and "' . $end_date . '"');
            } elseif ($start_date != null && $end_date != null) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            if ($warehouse != null) {
                $this->db->where("sma_sales.company_id", $warehouse);
            }
            $this->db->where("sma_companies.id !=6");
            $this->db->group_by("DATE_FORMAT( sma_sales.date, '%d-%m-%Y' ),
                            sma_sale_items.product_code,
                            sma_sales.warehouse_id");

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('product_quantity_alerts'))
                    ->SetCellValue('A1', lang("Date"))
                    ->SetCellValue('B1', lang("Ware House Code"))
                    ->SetCellValue('C1', lang("Distributor"))
                    ->SetCellValue('D1', lang("Ware House"))
                    ->SetCellValue('E1', lang("Address"))
                    ->SetCellValue('F1', lang("Product_code"))
                    ->SetCellValue('G1', lang("Product_name"))
                    ->SetCellValue('H1', lang("Stock"));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->tanggal)
                        ->SetCellValue('B' . $row, $data_row->warehouse_code)
                        ->SetCellValue('C' . $row, $data_row->nama_comp)
                        ->SetCellValue('D' . $row, $data_row->nama_gudang)
                        ->SetCellValue('E' . $row, $data_row->ALAMAT)
                        ->SetCellValue('F' . $row, $data_row->product_code)
                        ->SetCellValue('G' . $row, $data_row->Nama_Produk)
                        ->SetCellValue('H' . $row, $data_row->sale);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);

                $filename = 'product_quantity_alerts';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($this->input->post('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    // echo "string";
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            if ($start_date != '-') {
                $start_date = date("Y-m-d", strtotime($start_date));
            }

            if ($end_date != '-') {
                $end_date = date("Y-m-d", strtotime($end_date . "+ 1 day"));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }

            $join = "(
                SELECT CAST( sum( sma_sale_items.quantity ) AS UNSIGNED INTEGER ) AS qty, sma_sale_items.sale_id FROM sma_sale_items 
                LEFT JOIN sma_sales ON sma_sales.id = sma_sale_items.sale_id
                LEFT JOIN sma_warehouses ON sma_sales.warehouse_id = sma_warehouses.id
                GROUP BY
                DATE_FORMAT( sma_sales.date, '%d-%m-%Y' ),
                sma_sale_items.product_code,
                sma_sales.warehouse_id 
                ) item_sale";

            $this->load->library('datatables');
            $this->datatables
                ->select(
                    $this->db->dbprefix('sales') . ".date AS tanggal, "
                        . $this->db->dbprefix('warehouses') . ".code AS warehouse_code, "
                        . $this->db->dbprefix('companies') . ".company AS nama_comp, "
                        . $this->db->dbprefix('warehouses') . ".name AS nama_gudang, "
                        . $this->db->dbprefix('warehouses') . ".address AS ALAMAT, "
                        . $this->db->dbprefix('sale_items') . ".product_code AS product_code, "
                        . $this->db->dbprefix('sale_items') . ".product_name AS Nama_Produk,
                    item_sale.qty
                    ",
                    false
                )
                ->from('sma_sale_items')
                ->join('sma_products', 'sma_products.id = sma_sale_items.product_id', 'left')
                ->join('sma_sales', 'sma_sales.id = sma_sale_items.sale_id', 'left')
                ->join('sma_warehouses', 'sma_sales.warehouse_id = sma_warehouses.id', 'left')
                ->join('sma_companies', 'sma_sales.company_id = sma_companies.id ', 'left');

            $this->datatables->join($join, 'item_sale.sale_id = sma_sales.id', 'inner');

            $this->datatables->like('sma_products.NAME', 'semen', false);
            if ($start_date == '-' && $end_date == '-') {
                $cur = date('Y-m-d');
                $this->datatables->where("sma_sales.date = CAST( \"" . $cur . "\" AS DATE )");
            } elseif ($start_date != '-' && $end_date == '-') {
                $this->datatables->where("sma_sales.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == '-' && $end_date != '-') {
                $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $f_end_date . '" and "' . $end_date . '"');
            } elseif ($start_date != '-' && $end_date != '-') {
                $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            if ($company_id != null) {
                $this->datatables->where("sma_sales.company_id", $company_id);
            }

            $this->datatables->where("sma_companies.id !=6");

            $this->datatables->group_by("DATE_FORMAT( sma_sales.date, '%d-%m-%Y' ),
                            sma_sale_items.product_code,
                            sma_sales.warehouse_id");
            echo $this->datatables->generate();
        }
    }

    public function quantum_purchase_by_date()
    {
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }

        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

        $this->data['companies']  = $this->site->getCompaniesByGroupName('biller');
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('quantum_purchase_by_date')));
        $meta = array('page_title' => lang('quantum_purchase_by_date'), 'bc' => $bc);
        $this->page_construct('reports/quantum_purchase_by_date', $meta, $this->data);
    }

    public function get_quantum_purchase_by_date($start_date = '-', $end_date = '-', $company_id = null)
    {
        if ($this->input->post('form_action')) {
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $warehouse = $this->input->post('warehouse');

            if ($start_date != null) {
                $start_date = str_replace("/", "-", $start_date);
                $start_date = date("Y-m-d", strtotime($start_date));
            }

            if ($end_date != null) {
                $end_date = str_replace("/", "-", $end_date);
                $end_date = date("Y-m-d", strtotime($end_date . "+ 1 day"));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }
            $this->db
                ->select(
                    "DATE_FORMAT( " . $this->db->dbprefix('purchases') . ".date, '%d/%m/%Y' ) AS tanggal, "
                        . $this->db->dbprefix('warehouses') . ".code AS warehouse_code, "
                        . $this->db->dbprefix('companies') . ".company AS nama_comp, "
                        . $this->db->dbprefix('warehouses') . ".name AS nama_gudang, "
                        . $this->db->dbprefix('warehouses') . ".address AS ALAMAT, "
                        . $this->db->dbprefix('purchase_items') . ".product_code AS product_code, "
                        . $this->db->dbprefix('purchase_items') . ".product_name AS Nama_Produk,
                    CAST( sum( " . $this->db->dbprefix('purchase_items') . ".quantity ) AS UNSIGNED INTEGER ) AS sale
                    ",
                    false
                )
                ->from('sma_purchase_items')
                ->join('sma_purchases', 'sma_purchases.id = sma_purchase_items.purchase_id', 'left')
                ->join('sma_products', 'sma_products.id = sma_purchase_items.product_id', 'left')
                ->join('sma_warehouses', 'sma_purchases.warehouse_id = sma_warehouses.id', 'left')
                ->join('sma_companies', 'sma_purchases.company_id = sma_companies.id', 'left');
            $this->db->like('sma_products.NAME', 'semen', false);
            if ($start_date == null && $end_date == null) {
                $cur = date('Y-m-d');
                $this->db->where("sma_purchases.date >= CAST( \"" . $cur . "\" AS DATE ) ");
            } elseif ($start_date != null && $end_date == null) {
                $this->db->where("sma_purchases.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == null && $end_date != null) {
                $this->db->where("sma_purchases.date BETWEEN CAST( \"" . $f_end_date . "\" AS DATE ) AND CAST( \"" . $end_date . "\" AS DATE )");
            } elseif ($start_date != null && $end_date != null) {
                $this->db->where("sma_purchases.date BETWEEN CAST( \"" . $start_date . "\" AS DATE ) AND CAST( \"" . $end_date . "\" AS DATE )");
            }

            if ($warehouse != null) {
                $this->db->where("sma_purchases.company_id", $warehouse);
            }

            $this->db->where("sma_companies.id !=6");

            $this->db->group_by("DATE_FORMAT( sma_purchases.date, '%d-%m-%Y' ),
                            sma_purchase_items.product_code,
                            sma_purchases.warehouse_id");

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            // var_dump($data);die;
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('product_quantity_alerts'))
                    ->SetCellValue('A1', lang("Date"))
                    ->SetCellValue('B1', lang("Ware House Code"))
                    ->SetCellValue('C1', lang("Distributor"))
                    ->SetCellValue('D1', lang("Ware House"))
                    ->SetCellValue('E1', lang("Address"))
                    ->SetCellValue('F1', lang("Product_code"))
                    ->SetCellValue('G1', lang("Product_name"))
                    ->SetCellValue('H1', lang("Stock"));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->tanggal)
                        ->SetCellValue('B' . $row, $data_row->warehouse_code)
                        ->SetCellValue('C' . $row, $data_row->nama_comp)
                        ->SetCellValue('D' . $row, $data_row->nama_gudang)
                        ->SetCellValue('E' . $row, $data_row->ALAMAT)
                        ->SetCellValue('F' . $row, $data_row->product_code)
                        ->SetCellValue('G' . $row, $data_row->Nama_Produk)
                        ->SetCellValue('H' . $row, $data_row->sale);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);

                $filename = 'product_quantity_alerts';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($this->input->post('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    // echo "string";
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            if ($start_date != '-') {
                $start_date = date("Y-m-d", strtotime($start_date));
            }
            if ($end_date != '-') {
                $end_date = date("Y-m-d", strtotime($end_date . "+ 1 day"));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }

            $join = "(
                    SELECT CAST( sum( sma_purchase_items.quantity ) AS UNSIGNED INTEGER ) AS qty, sma_purchase_items.purchase_id FROM sma_purchase_items 
                    LEFT JOIN sma_purchases ON sma_purchases.id = sma_purchase_items.purchase_id
                    LEFT JOIN sma_warehouses ON sma_purchases.warehouse_id = sma_warehouses.id
                    GROUP BY
                    sma_purchase_items.product_code,
                    sma_purchases.warehouse_id,
                    DATE_FORMAT( sma_purchases.date, '%d-%m-%Y' ) 
                ) sma_item_purchase ";

            $this->load->library('datatables');
            $this->datatables
                ->select(
                    $this->db->dbprefix('purchases') . ".date AS tanggal, "
                        . $this->db->dbprefix('warehouses') . ".code AS warehouse_code, "
                        . $this->db->dbprefix('companies') . ".company AS nama_comp, "
                        . $this->db->dbprefix('warehouses') . ".name AS nama_gudang, "
                        . $this->db->dbprefix('warehouses') . ".address AS ALAMAT, "
                        . $this->db->dbprefix('purchase_items') . ".product_code AS product_code, "
                        . $this->db->dbprefix('purchase_items') . ".product_name AS Nama_Produk,
                    sma_item_purchase.qty
                    ",
                    false
                )
                ->from('sma_purchase_items')
                ->join('sma_purchases', 'sma_purchases.id = sma_purchase_items.purchase_id', 'left')
                ->join('sma_warehouses', 'sma_purchases.warehouse_id = sma_warehouses.id', 'left')
                ->join('sma_products', 'sma_products.id = sma_purchase_items.product_id', 'left')
                ->join('sma_companies', 'sma_purchases.company_id = sma_companies.id', 'left');

            $this->datatables->join($join, 'item_purchase.purchase_id = sma_purchases.id', 'inner');
            $this->datatables->like('sma_products.NAME', 'semen', false);
            if ($start_date == '-' && $end_date == '-') {
                $cur = date('Y-m-d');
                $this->datatables->where("sma_purchases.date >= CAST( \"" . $cur . "\" AS DATE ) ");
            } elseif ($start_date != '-' && $end_date == '-') {
                $this->datatables->where("sma_purchases.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == '-' && $end_date != '-') {
                $this->datatables->where("sma_purchases.date BETWEEN CAST( \"" . $f_end_date . "\" AS DATE ) AND CAST( \"" . $end_date . "\" AS DATE )");
            } elseif ($start_date != '-' && $end_date != '-') {
                $this->datatables->where("sma_purchases.date BETWEEN CAST( \"" . $start_date . "\" AS DATE ) AND CAST( \"" . $end_date . "\" AS DATE )");
            }

            if ($company_id != null) {
                $this->datatables->where("sma_purchases.company_id", $company_id);
            }

            $this->datatables->where("sma_companies.id !=6");

            $this->datatables->group_by("DATE_FORMAT( sma_purchases.date, '%d-%m-%Y' ),
                            sma_purchase_items.product_code,
                            sma_purchases.warehouse_id");
            echo $this->datatables->generate();
        }
    }

    public function products_warehouse_by_date()
    {
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }

        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

        $this->data['companies']  = $this->site->getCompaniesByGroupName('biller');
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_warehouse_by_date')));
        $meta = array('page_title' => lang('products_warehouse_by_date'), 'bc' => $bc);
        $this->page_construct('reports/products_warehouse_by_date', $meta, $this->data);
    }

    public function get_products_warehouse_by_date($start_date = '-', $end_date = '-', $company_id = null)
    {
        if ($this->input->post('form_action')) {
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $warehouse = $this->input->post('warehouse');

            if ($start_date != null) {
                $start_date = str_replace("/", "-", $start_date);
                $start_date = date("Y-m-d", strtotime($start_date));
            }

            if ($end_date != null) {
                $end_date = str_replace("/", "-", $end_date);
                $end_date = date("Y-m-d", strtotime($end_date . "+ 1 day"));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }
            $this->db->set_dbprefix('');
            $this->db
                ->select(
                    "DATE_FORMAT( story_warehouses_products.date, '%d/%m/%Y' ) AS tanggal,
                    sma_warehouses.code as warehouse_code,
                    sma_companies.country AS Propinsi,
                    sma_companies.company AS Distributor,
                    sma_warehouses.NAME AS Nama_Gudang,
                    sma_warehouses.address,
                    sma_products.CODE AS Kode_Produk,
                    sma_products.NAME AS Nama_Produk,
                    CAST( story_warehouses_products.quantity AS UNSIGNED INTEGER ) AS Stok_Gudang
                    ",
                    false
                )
                ->from('story_warehouses_products')
                ->join('sma_products', 'sma_products.id = story_warehouses_products.product_id', 'left')
                ->join('sma_warehouses', 'story_warehouses_products.warehouse_id = sma_warehouses.id', 'left')
                ->join('sma_companies', 'story_warehouses_products.company_id = sma_companies.id', 'left')
                ->where("story_warehouses_products.quantity > 0 ")
                ->where("story_warehouses_products.company_id != 6")
                ->like('sma_products.NAME', 'semen', false);

            if ($start_date == null && $end_date == null) {
                $cur = date('Y-m-d');
                $this->db->where("story_warehouses_products.date = CAST( \"" . $cur . "\" AS DATE )");
            } elseif ($start_date != null && $end_date == null) {
                $this->db->where("story_warehouses_products.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == null && $end_date != null) {
                $this->db->where('story_warehouses_products.date BETWEEN "' . $f_end_date . '" and "' . $end_date . '"');
            } elseif ($start_date != null && $end_date != null) {
                $this->db->where('story_warehouses_products.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if ($warehouse != null) {
                $this->db->where("story_warehouses_products.company_id", $warehouse);
            }
            $this->db->group_by("DATE_FORMAT( story_warehouses_products.date, '%d-%m-%Y' ),
                            story_warehouses_products.product_id,
                            story_warehouses_products.warehouse_id ");

            // print_r($this->db->error());die;
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('product_quantity_alerts'))
                    ->SetCellValue('A1', lang("Date"))
                    ->SetCellValue('B1', lang("Ware House Code"))
                    ->SetCellValue('C1', lang("Provinsi"))
                    ->SetCellValue('D1', lang("Distributor"))
                    ->SetCellValue('E1', lang("Ware House"))
                    ->SetCellValue('F1', lang("Address"))
                    ->SetCellValue('G1', lang("Product_code"))
                    ->SetCellValue('H1', lang("Product_name"))
                    ->SetCellValue('I1', lang("Stock"));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->tanggal)
                        ->SetCellValue('B' . $row, $data_row->warehouse_code)
                        ->SetCellValue('C' . $row, $data_row->Propinsi)
                        ->SetCellValue('D' . $row, $data_row->Distributor)
                        ->SetCellValue('E' . $row, $data_row->Nama_Gudang)
                        ->SetCellValue('F' . $row, $data_row->address)
                        ->SetCellValue('G' . $row, $data_row->Kode_Produk)
                        ->SetCellValue('H' . $row, $data_row->Nama_Produk)
                        ->SetCellValue('I' . $row, $data_row->Stok_Gudang);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);

                $filename = 'products warehouse by date';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($this->input->post('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            if ($start_date != '-') {
                $start_date = date("Y-m-d", strtotime($start_date));
            }
            if ($end_date != '-') {
                $end_date = date("Y-m-d", strtotime($end_date . "+ 1 day"));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }
            $this->db->set_dbprefix('');
            $this->load->library('datatables');
            $this->datatables
                ->select(
                    "story_warehouses_products.date AS tanggal,
                    sma_warehouses.code,
                    sma_companies.country AS Propinsi,
                    sma_companies.company AS Distributor,
                    sma_warehouses.NAME AS Nama_Gudang,
                    sma_warehouses.address,
                    sma_products.CODE AS Kode_Produk,
                    sma_products.NAME AS Nama_Produk,
                    CAST( story_warehouses_products.quantity AS UNSIGNED INTEGER ) AS Stok_Gudang
                    ",
                    false
                )
                ->from('story_warehouses_products')
                ->join('sma_products', 'sma_products.id = story_warehouses_products.product_id', 'left')
                ->join('sma_warehouses', 'story_warehouses_products.warehouse_id = sma_warehouses.id', 'left')
                ->join('sma_companies', 'story_warehouses_products.company_id = sma_companies.id', 'left')
                ->where("story_warehouses_products.quantity > 0 ")
                ->where("story_warehouses_products.company_id != 6")
                ->like('sma_products.NAME', 'semen', false);

            if ($start_date == '-' && $end_date == '-') {
                $cur = date('Y-m-d');
                $this->datatables->where("story_warehouses_products.date = CAST( \"" . $cur . "\" AS DATE )");
            } elseif ($start_date != '-' && $end_date == '-') {
                $this->datatables->where("story_warehouses_products.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == '-' && $end_date != '-') {
                $this->datatables->where('story_warehouses_products.date BETWEEN "' . $f_end_date . '" and "' . $end_date . '"');
            } elseif ($start_date != '-' && $end_date != '-') {
                $this->datatables->where('story_warehouses_products.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if ($company_id != null) {
                $this->datatables->where("story_warehouses_products.company_id", $company_id);
            }
            $this->datatables->group_by("DATE_FORMAT( story_warehouses_products.date, '%d-%m-%Y' ),
                            story_warehouses_products.product_id,
                            story_warehouses_products.warehouse_id ");
            echo $this->datatables->generate();
        }
    }

    public function getListUsers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users') . ".id as id, first_name, last_name, email, company, award_points, " . $this->db->dbprefix('groups') . ".name, active")
            ->from("users")
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where('company_id', $this->session->userdata('company_id'))
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('auth/profile/$1') . "' class='tip' title='" . lang("edit_user") . "'><i class=\"fa fa-edit\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }

    public function report_list_user_aksestoko()
    {
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('reports_list_user_aksestoko')));
        $meta = array('page_title' => lang('list_user_aksestoko'), 'bc' => $bc);
        $this->page_construct('reports/list_user_aksestoko', $meta, $this->data);
    }

    public function getListUserAksesToko()
    {
        if ($this->input->post('form_action')) {
            // print_r($this->input->post());die;

            $sqlJoin = '(SELECT id, cf1 FROM sma_companies WHERE group_name = \'customer\' AND company_id = ' . $this->session->userdata('company_id') . ' AND is_deleted IS NULL AND sma_companies.cf1 LIKE \'IDC-%\') sma_x ';
            $this->db
                ->select("sma_companies.id, REPLACE(sma_companies.cf1, 'IDC-', '') AS ibk, sma_companies.company as nama_toko, sma_companies.email as email,  sma_users.phone as phone,sma_users.active as status, sma_users.phone_is_verified as phone_status")
                ->join($sqlJoin, 'x.cf1 = sma_companies.cf1', 'inner')
                ->join('sma_users', 'sma_companies.id = sma_users.company_id')
                ->where('group_name', 'biller')
                ->where('client_id', 'aksestoko');

            // $this->db->select('sma_v_sales_aksestoko.*')
            //         ->from('sma_v_sales_aksestoko');
            // if ($month != '' && $month != '-') {
            //     $this->db->where('month(sma_v_sales_aksestoko.tanggal_transaksi) = \''.$month.'\'');
            // }

            // if ($year != '' && $year != '-') {
            //     $this->db->where('year(sma_v_sales_aksestoko.tanggal_transaksi) = \''.$year.'\'');
            // }
            $this->db->group_by('sma_companies.id');
            $this->db->from("companies");

            $q = $this->db->get();
            // var_dump($this->db->error());die;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            $this->load->model('companies_model', 'company');
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('user_aksestoko'))
                    ->SetCellValue('A1', lang("ID Bisnis Kokoh"))
                    ->SetCellValue('B1', lang("Nama Toko"))
                    ->SetCellValue('C1', lang("Email"))
                    ->SetCellValue('D1', lang("No Hp"))
                    ->SetCellValue('E1', lang("Status"))
                    ->SetCellValue('F1', lang("Phone Status"));

                $spreadsheet->createSheet();
                $sheet = $spreadsheet->setActiveSheetIndex(1);
                $sheet->setTitle(lang('addres'))
                    ->SetCellValue('A1', lang("ID Bisnis Kokoh"))
                    ->SetCellValue('B1', lang("Nama Toko"))
                    ->SetCellValue('C1', lang("Nama Penerima"))
                    ->SetCellValue('D1', lang("No Hp"))
                    ->SetCellValue('E1', lang("Alamat"));

                $row = 2;
                $row_address = 2;
                foreach ($data as $data_row) {
                    if ($data_row->status == 1) {
                        $status = lang('active');
                    } else {
                        $status = lang('inactive');
                    }

                    if ($data_row->phone_status == 1) {
                        $phone_status = lang('verified');
                    } else {
                        $phone_status = lang('unverified');
                    }

                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->SetCellValue('A' . $row, $data_row->ibk)
                        ->SetCellValue('B' . $row, $data_row->nama_toko)
                        ->SetCellValue('C' . $row, $data_row->email)
                        ->SetCellValue('D' . $row, $data_row->phone)
                        ->SetCellValue('E' . $row, $status)
                        ->SetCellValue('F' . $row, $phone_status);

                    $sheet = $spreadsheet->setActiveSheetIndex(1);
                    $addresses = array_merge([$this->company->getCompanyByID($data_row->id)], $this->site->getCompaniesAddress($data_row->id));
                    foreach ($addresses as $key => $address) {
                        $alamat = trim($address->address) . ' , ' . ucwords(strtolower($address->village)) . ' , ' . ucwords(strtolower($address->state)) . ' , ' . ucwords(strtolower($address->city)) . ' , ' . ucwords(strtolower($address->country)) . ', ' . $address->postal_code;
                        $sheet->SetCellValue('A' . $row_address, $data_row->ibk)
                            ->SetCellValue('B' . $row_address, $data_row->nama_toko)
                            ->SetCellValue('C' . $row_address, $address->nama_toko)
                            ->SetCellValue('D' . $row_address, $data_row->phone)
                            ->SetCellValue('E' . $row_address, $alamat);
                        $row_address++;
                    }
                    $row++;
                }
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);

                $filename = 'user_akses_toko_' . date("Y-m-d H:i:s");
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($this->input->post('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $sqlJoin = '(SELECT id, cf1 FROM sma_companies WHERE group_name = \'customer\' AND company_id = ' . $this->session->userdata('company_id') . ' AND is_deleted IS NULL AND sma_companies.cf1 LIKE \'IDC-%\') sma_x ';
            $this->load->library('datatables');
            $this->datatables
                ->select("sma_companies.id, REPLACE(sma_companies.cf1, 'IDC-', ''), sma_companies.company, sma_companies.email,  sma_users.phone,sma_users.active, sma_users.phone_is_verified")
                ->join($sqlJoin, 'x.cf1 = sma_companies.cf1', 'inner')
                ->join('sma_users', 'sma_companies.id = sma_users.company_id')
                ->where('group_name', 'biller')
                ->where('client_id', 'aksestoko')
                ->edit_column('active', '$1__$2', 'active, id')
                ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("list_addresses") . "' href='" . site_url('reports/list_addres_user_aksestoko/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                        <i class=\"fa fa-location-arrow\"></i></a></div>", "sma_companies.id");
            $this->datatables->group_by('sma_companies.id');
            $this->datatables->from("companies");
            echo $this->datatables->generate();
        }
    }

    public function list_addres_user_aksestoko($id)
    {
        $this->load->model('companies_model', 'company');
        $this->data['addresses'] = array_merge([$this->company->getCompanyByID($id)], $this->site->getCompaniesAddress($id));
        $this->load->view($this->theme . 'reports/modal_list_address', $this->data);
    }


    public function user_aktivasi_aksestoko()
    {
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }

        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

        $this->data['start_date'] = '01/' . date('m/Y');
        $this->data['end_date'] = date("d/m/Y");
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('user_aktivasi_aksestoko')));
        $meta = array('page_title' => lang('user_aktivasi_aksestoko'), 'bc' => $bc);
        $this->page_construct('reports/user_aktivasi_aksestoko', $meta, $this->data);
    }

    public function get_user_aktivasi_aksestoko()
    {
        $start_date =  $this->input->get('start_date') ?? date('Y/m') . '/01';
        $start_date =  strtr($start_date, '/', '-');
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date =  $this->input->get('end_date') ?? date("Y/m/d");
        $end_date =  strtr($end_date, '/', '-');
        $end_date = date("Y-m-d", strtotime($end_date));
        if ($this->input->get('form_action')) {

            // print_r($this->input->post());die;
            $this->db->select('sma_v_aktivasi_aksestoko.*')
                ->from('sma_v_aktivasi_aksestoko');


            //            if ($month != '' && $month != '-') {
            //                $this->db->where('month(sma_v_aktivasi_aksestoko.tanggal_aktivasi) = \''.$month.'\'');
            //            }
            //
            //            if ($year != '' && $year != '-') {
            //                $this->db->where('year(sma_v_aktivasi_aksestoko.tanggal_aktivasi) = \''.$year.'\'');
            //            }

            $this->db->where('sma_v_aktivasi_aksestoko.tanggal_aktivasi BETWEEN "' . $start_date . '" and "' . $end_date . '"');

            $this->db->order_by('sma_v_aktivasi_aksestoko.tanggal_aktivasi DESC');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                // print_r($data);die;
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('user_aksestoko'))
                    ->SetCellValue('A1', lang("date"))
                    ->SetCellValue('B1', lang("ibk"))
                    ->SetCellValue('C1', lang("nama_toko"))
                    ->SetCellValue('D1', lang("alamat"))
                    ->SetCellValue('E1', lang("phone"))
                    ->SetCellValue('F1', lang("distributor"))
                    ->SetCellValue('G1', lang("provinsi"))
                    ->SetCellValue('H1', lang("distributor"))
                    ->SetCellValue('I1', lang("sales_person"))
                    ->SetCellValue('J1', lang("registered_by"));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->getStyle('A' . $row)
                        ->getNumberFormat()
                        ->setFormatCode("MM/DD/YYYY");
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->tanggal_aktivasi);

                    $sheet->SetCellValue('A' . $row, $date)
                        ->SetCellValue('B' . $row, $data_row->idbk)
                        ->SetCellValue('C' . $row, $data_row->nama_toko)
                        ->SetCellValue('D' . $row, $data_row->alamat)
                        ->SetCellValue('E' . $row, $data_row->phone)
                        ->SetCellValue('F' . $row, $data_row->distributor)
                        ->SetCellValue('G' . $row, $data_row->provinsi)
                        ->SetCellValue('H' . $row, $data_row->dist)
                        ->SetCellValue('I' . $row, $data_row->sales_person)
                        ->SetCellValue('J' . $row, $data_row->registered_by);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(25);

                $filename = 'user aktivasi aksestoko_' . date("Y-m-d H:i:s");
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($this->input->get('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->get('form_action') == 'export_excel') {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select(
                    "sma_v_aktivasi_aksestoko.tanggal_aktivasi, 
                    sma_v_aktivasi_aksestoko.idbk,
                    sma_v_aktivasi_aksestoko.nama_toko,
                    sma_v_aktivasi_aksestoko.alamat,
                    sma_v_aktivasi_aksestoko.phone,
                    sma_v_aktivasi_aksestoko.distributor,
                    sma_v_aktivasi_aksestoko.provinsi,
                    sma_v_aktivasi_aksestoko.dist,
                    sma_v_aktivasi_aksestoko.registered_by"

                )
                ->from('sma_v_aktivasi_aksestoko');
            // echo $start_date.'=>'.$end_date
            $this->datatables->where('sma_v_aktivasi_aksestoko.tanggal_aktivasi BETWEEN "' . $start_date . '" and "' . $end_date . '"');

            echo $this->datatables->generate();
        }
    }

    public function sale_transaction()
    {
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }
        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['start_date'] = '01/' . date('m/Y');
        $this->data['end_date'] = date("d/m/Y");
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sale_transaction')));
        $meta = array('page_title' => lang('sales_transaction'), 'bc' => $bc);
        $this->page_construct('reports/sales_transaction', $meta, $this->data);
    }

    public function get_sale_transaction()
    {
        $start_date =  $this->input->get('start_date') ?? date('Y/m') . '/01';
        $start_date =  strtr($start_date, '/', '-');
        $start_date = date("Y-m-d", strtotime($start_date));

        $end_date =  $this->input->get('end_date') ?? date("Y/m/d");
        $end_date =  strtr($end_date, '/', '-');
        $end_date = date("Y-m-d", strtotime($end_date));


        if ($this->input->get('form_action')) {

            // print_r($this->input->post());die;
            $this->db->select('sma_v_sales_aksestoko.*')
                ->from('sma_v_sales_aksestoko');

            $this->db->where('tanggal_transaksi BETWEEN "' . $start_date . '" and "' . $end_date . '"');

            $q = $this->db->get();

            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('sale_transaction'))
                    ->SetCellValue('A1', lang("date"))
                    ->SetCellValue('B1', lang("ibk"))
                    ->SetCellValue('C1', lang("customer"))
                    ->SetCellValue('D1', lang("alamat"))
                    ->SetCellValue('E1', lang("phone"))
                    ->SetCellValue('F1', lang("distributor"))
                    ->SetCellValue('G1', lang("warehouse"))
                    ->SetCellValue('H1', lang("reference_no"))
                    ->SetCellValue('I1', lang("created_by"))
                    ->SetCellValue('J1', lang("sale_status"))
                    ->SetCellValue('K1', lang("product"))
                    ->SetCellValue('L1', lang("quantity"))
                    ->SetCellValue('M1', lang("grand_total"))
                    ->SetCellValue('N1', lang("payment_method"));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->getStyle('A' . $row)
                        ->getNumberFormat()
                        ->setFormatCode("MM/DD/YYYY");
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->tanggal_transaksi);

                    $sheet->SetCellValue('A' . $row, $date)
                        ->SetCellValue('B' . $row, $data_row->ibk)
                        ->SetCellValue('C' . $row, $data_row->nama_toko)
                        ->SetCellValue('D' . $row, $data_row->alamat)
                        ->SetCellValue('E' . $row, $data_row->phone)
                        ->SetCellValue('F' . $row, $data_row->distributor)
                        ->SetCellValue('G' . $row, $data_row->gudang)
                        ->SetCellValue('H' . $row, $data_row->no_penjualan)
                        ->SetCellValue('I' . $row, $data_row->created_by)
                        ->SetCellValue('J' . $row, $data_row->sale_status)
                        ->SetCellValue('K' . $row, $data_row->nama_produk)
                        ->SetCellValue('L' . $row, $data_row->quantity)
                        ->SetCellValue('M' . $row, $data_row->grand_total)
                        ->SetCellValue('N' . $row, lang($data_row->payment_method));
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(25);
                $sheet->getColumnDimension('I')->setWidth(25);
                $sheet->getColumnDimension('J')->setWidth(25);
                $sheet->getColumnDimension('K')->setWidth(25);
                $sheet->getColumnDimension('L')->setWidth(25);
                $sheet->getColumnDimension('M')->setWidth(25);

                $filename = 'sale_transaction_' . date("Y-m-d H:i:s");
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($this->input->get('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->get('form_action') == 'export_excel') {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select(
                    'sma_v_sales_aksestoko.tanggal_transaksi, 
                    sma_v_sales_aksestoko.ibk, 
                    sma_v_sales_aksestoko.nama_toko,
                    sma_v_sales_aksestoko.alamat,
                    sma_v_sales_aksestoko.phone,
                    sma_v_sales_aksestoko.distributor,
                    sma_v_sales_aksestoko.gudang, 
                    sma_v_sales_aksestoko.no_penjualan,
                    sma_v_sales_aksestoko.sale_status, 
                    sma_v_sales_aksestoko.nama_produk, 
                    sma_v_sales_aksestoko.quantity,
                    sma_v_sales_aksestoko.grand_total,
                    sma_v_sales_aksestoko.payment_method'
                )
                ->from('sma_v_sales_aksestoko');
            $this->datatables->where('sma_v_sales_aksestoko.tanggal_transaksi BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            echo $this->datatables->generate();
        }
    }

    public function sale_delivered()
    {
        // echo date('d-m-y')-date('d');

        // die;
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }

        // $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['start_date'] = '01/' . date('m/Y');
        $this->data['end_date'] = date("d/m/Y");
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sale_delivered')));
        $meta = array('page_title' => lang('sale_delivered'), 'bc' => $bc);
        $this->page_construct('reports/sales_delivered', $meta, $this->data);
    }

    public function get_sale_delivered()
    {
        $start_date =  $this->input->get('start_date') ?? date('Y/m') . '/01';
        $start_date =  strtr($start_date, '/', '-');
        $start_date = date("Y-m-d", strtotime($start_date));

        $end_date =  $this->input->get('end_date') ?? date("Y/m/d");
        $end_date =  strtr($end_date, '/', '-');
        $end_date = date("Y-m-d", strtotime($end_date));

        $join = "(
	SELECT
	CASE
		WHEN
			SUM( unit_quantity ) > SUM( sent_quantity ) 
			AND SUM( sent_quantity ) = 0 THEN
				'pending' 
				WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
				AND SUM( sent_quantity ) > 0 THEN
					'partial' 
					WHEN SUM( unit_quantity ) = SUM( sent_quantity ) 
					AND SUM( sent_quantity ) > 0 THEN
						'done' 
						END AS delivery_status,
					sale_id 
				FROM
					sma_sale_items 
				GROUP BY
					sma_sale_items.sale_id 
				) sma_item";
        // $select = ;
        if ($this->input->get('form_action')) {
            $this->db->select("
                                sma_sales.id,
                                DATE_FORMAT( sma_sales.date, '%Y-%m-%d' ) AS 'date',
                                sma_sales.reference_no AS 'reference_no',
                                biller AS 'distributor',
                                sma_sales.customer AS 'customer',
                                sma_users.phone AS 'phone',
                                IF
                                (
                                    sma_sales.client_id = 'aksestoko',
                                    CONCAT( sma_users.first_name, ' ', sma_users.last_name, ' (AksesToko)' ),
                                    CONCAT( sma_users.first_name, ' ', sma_users.last_name ) 
                                ) AS 'created_by',
                                sma_sales.sale_status AS 'sale_status',
                                CAST( sma_sales.grand_total AS INT ) AS 'grand_total',
                                CAST( sma_sales.paid AS INT ) AS 'paid',
                                CAST( ( sma_sales.grand_total - sma_sales.paid ) AS INT ) AS 'balance',
                                sma_sales.payment_status AS 'payment_Status',
                                delivery_status AS 'delivery_status',
                                sma_products.NAME AS 'product_name',
                                CAST( sma_sale_items.quantity AS INT ) AS 'quantity',
                                CAST( sma_sale_items.subtotal AS INT ) AS 'sub_total',
                                CAST( sma_sales.grand_total AS INT ) AS 'grand_total',
                                sma_companies.address AS 'alamat',
                                REPLACE ( sma_companies.cf1, 'IDC-', '' ) AS 'ibk' 
                            ")
                ->from('sales')
                ->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left')
                ->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left')
                ->join($this->db->dbprefix('products'), $this->db->dbprefix('sale_items') . '.product_id=sma_products.id', 'left')
                ->join($this->db->dbprefix('companies'), $this->db->dbprefix('users') . '.company_id=sma_companies.id', 'left');
            $this->db->join($join, 'sma_item.sale_id = sma_sales.id', 'left');
            $this->db->where('sma_sales.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            $this->db->where('sma_sales.is_deleted IS NULL');
            $this->db->where("sma_item.delivery_status = 'done' ");
            $this->db->where("sma_sales.biller_id != 6 ");
            $this->db->where("sma_sales.client_id = 'aksestoko'");
            $q = $this->db->get();
            // var_dump($this->db->error());die;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('sale_delivered'))
                    //->SetCellValue('A1', lang("transaction_id"))
                    ->SetCellValue('A1', lang("date"))
                    ->SetCellValue('B1', lang("reference_no"))
                    ->SetCellValue('C1', lang("distributor"))
                    ->SetCellValue('D1', lang("customer"))
                    ->SetCellValue('E1', lang("phone"))
                    ->SetCellValue('F1', lang("created_by"))
                    ->SetCellValue('G1', lang("sale_status"))
                    ->SetCellValue('H1', lang("grand_total"))
                    ->SetCellValue('I1', lang("total_paid"))
                    ->SetCellValue('J1', lang("balance"))
                    ->SetCellValue('K1', lang("payment_status"))
                    ->SetCellValue('L1', lang("delivery_status"))
                    ->SetCellValue('M1', lang("product"))
                    ->SetCellValue('N1', lang("qty"))
                    ->SetCellValue('O1', lang("sub_total"))
                    ->SetCellValue('P1', lang("grand_total"))
                    ->SetCellValue('Q1', lang("alamat"))
                    ->SetCellValue('R1', lang("ibk"));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->getStyle('A' . $row)
                        ->getNumberFormat()
                        ->setFormatCode("MM/DD/YYYY");

                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->date);

                    $sheet //->SetCellValue('A' . $row, $data_row->id)
                        ->SetCellValue('A' . $row, $date)
                        ->SetCellValue('B' . $row, $data_row->reference_no)
                        ->SetCellValue('C' . $row, $data_row->distributor)
                        ->SetCellValue('D' . $row, $data_row->customer)
                        ->SetCellValue('E' . $row, $data_row->phone)
                        ->SetCellValue('F' . $row, $data_row->created_by)
                        ->SetCellValue('G' . $row, $data_row->sale_status)
                        ->SetCellValue('H' . $row, $data_row->grand_total)
                        ->SetCellValue('I' . $row, $data_row->paid)
                        ->SetCellValue('J' . $row, $data_row->balance)
                        ->SetCellValue('K' . $row, $data_row->payment_Status)
                        ->SetCellValue('L' . $row, $data_row->delivery_status)
                        ->SetCellValue('M' . $row, $data_row->product_name)
                        ->SetCellValue('N' . $row, $data_row->quantity)
                        ->SetCellValue('O' . $row, $data_row->sub_total)
                        ->SetCellValue('P' . $row, $data_row->grand_total)
                        ->SetCellValue('Q' . $row, $data_row->alamat)
                        ->SetCellValue('R' . $row, $data_row->ibk);
                    $row++;
                }



                //$sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(25);
                $sheet->getColumnDimension('I')->setWidth(25);
                $sheet->getColumnDimension('J')->setWidth(25);
                $sheet->getColumnDimension('K')->setWidth(25);
                $sheet->getColumnDimension('L')->setWidth(25);
                $sheet->getColumnDimension('M')->setWidth(25);
                $sheet->getColumnDimension('N')->setWidth(25);
                $sheet->getColumnDimension('O')->setWidth(25);
                $sheet->getColumnDimension('P')->setWidth(25);
                $sheet->getColumnDimension('Q')->setWidth(25);
                $sheet->getColumnDimension('R')->setWidth(25);

                $filename = 'sale_delivered_' . date("Y-m-d H:i:s");
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($this->input->get('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->get('form_action') == 'export_excel') {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
            // print_r($data);die;
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, 
                {$this->db->dbprefix('sales')}.reference_no as reference_no, 
                REPLACE ( sma_companies.cf1, 'IDC-', '' ) AS 'ibk',
                sma_sales.biller AS 'distributor',
                sma_sales.customer as customer, 
                sma_companies.address AS 'alamat',
                IF({$this->db->dbprefix('sales')}.client_id = 'aksestoko', CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, ' (AksesToko)'), CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name)) AS created_by, 
                {$this->db->dbprefix('sales')}.sale_status,
                delivery_status,
                CAST({$this->db->dbprefix('sales')}.grand_total AS INT ) as grand_total, 
                CAST({$this->db->dbprefix('sales')}.paid AS INT ) as paid, 
                {$this->db->dbprefix('sales')}.payment_status
                ")
                ->from('sales')
                ->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left')
                ->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left')
                ->join($this->db->dbprefix('products'), $this->db->dbprefix('sale_items') . '.product_id=sma_products.id', 'left')
                ->join($this->db->dbprefix('companies'), $this->db->dbprefix('users') . '.company_id=sma_companies.id', 'left');
            $this->datatables->join($join, 'sma_item.sale_id = sma_sales.id', 'left');
            $this->datatables->where('sma_sales.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            $this->datatables->where('sma_sales.is_deleted IS NULL');
            $this->datatables->where("sma_item.delivery_status = 'done' ");
            $this->datatables->where("sma_sales.biller_id != 6 ");
            $this->datatables->where("sma_sales.client_id = 'aksestoko'");
            echo $this->datatables->generate();
        }
    }

    public function piutang($id = null)
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('piutang_report')));
        $meta = array('page_title' => lang('piutang_report'), 'bc' => $bc);

        $this->data['user_id'] = $id;

        //        if ()

        if ($id == null) {
            $this->page_construct('reports/piutang', $meta, $this->data);
        } elseif ($id == 'pos') {
            $this->page_construct('reports/piutang_pos', $meta, $this->data);
        } else {
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => site_url('reports/piutang/pos'), 'page' => lang('piutang_report')), array('link' => '#', 'page' => lang('piutang_report_detail')));
            $meta = array('page_title' => lang('piutang_report_detail'), 'bc' => $bc);

            $q =  $this->db
                ->select("name,company,phone")
                ->where('id', $id)
                ->get('companies')
                ->row();
            //            $query = $this->db->get();
            //            $ret = $query->result();
            $this->data['name_company'] = $q->name;
            $this->data['compann_company'] = $q->company;
            $this->data['phone_company'] = $q->phone;
            //            var_dump($this->data);
            //
            //            return $ret;




            $this->page_construct('reports/piutang_details', $meta, $this->data);
        }
    }

    public function getpiutang($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('customers', true);
        if ($pdf || $xls) {
            $this->db
                ->select('sma_sales.customer_id as ids,sma_companies.company,sma_companies.name,sma_companies.phone,sma_companies.email,
                        count(sma_sales.customer_id) as pembelian,
                        COALESCE (sum(grand_total), 0) AS total_amount,
                        COALESCE (sum(paid), 0) AS paid,
                        (
                            COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
                        ) AS balance,
                        CASE
								WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 0 and 14  
								THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS tagihan_kurang30,
						CASE
								WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 15 and 30  
								THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS tagihan_1530,
						CASE
								WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 30 DAY 
								THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS tagihan_lebih30,
                        CASE
                                WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 0 DAY 
                                THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS semua')
                ->from('sales')
                ->join('sma_companies', 'sma_companies.id = sma_sales.customer_id')
                ->where('sma_sales.biller_id', $this->session->userdata('company_id'))
                ->where('sma_sales.client_id', 'aksestoko')
                ->group_by('sma_sales.customer_id');


            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('customers_report'))
                    ->SetCellValue('A1', lang('company'))
                    ->SetCellValue('B1', lang('name'))
                    ->SetCellValue('C1', lang('phone'))
                    ->SetCellValue('D1', lang('email'))
                    ->SetCellValue('E1', lang('total_sales'))
                    ->SetCellValue('F1', lang('total_amount'))
                    ->SetCellValue('G1', lang('paid'))
                    ->SetCellValue('H1', lang('balance'))
                    ->SetCellValue('I1', lang('semua'))
                    ->SetCellValue('J1', lang('h15'))
                    ->SetCellValue('K1', lang('h1530'))
                    ->SetCellValue('L1', lang('h30'));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->company)
                        ->SetCellValue('B' . $row, $data_row->name)
                        ->SetCellValue('C' . $row, $data_row->phone)
                        ->SetCellValue('D' . $row, $data_row->email)
                        ->SetCellValue('E' . $row, $data_row->pembelian)
                        ->SetCellValue('F' . $row, $this->sma->formatMoney($data_row->total_amount))
                        ->SetCellValue('G' . $row, $this->sma->formatMoney($data_row->paid))
                        ->SetCellValue('H' . $row, $this->sma->formatMoney($data_row->balance))
                        ->SetCellValue('I' . $row, $this->sma->formatMoney($data_row->semua))
                        ->SetCellValue('J' . $row, $this->sma->formatMoney($data_row->tagihan_kurang30))
                        ->SetCellValue('K' . $row, $this->sma->formatMoney($data_row->tagihan_1530))
                        ->SetCellValue('L' . $row, $this->sma->formatMoney($data_row->tagihan_lebih30));
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $filename = 'piutang_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select('sma_sales.customer_id as ids,sma_companies.company,sma_companies.name,sma_companies.phone,sma_companies.email,
                        count(sma_sales.customer_id) as pembelian,
                        COALESCE (sum(grand_total), 0) AS total_amount,
                        COALESCE (sum(paid), 0) AS paid,
                        (COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)) AS balance,
                        CASE WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 0 DAY THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS semua,
                        CASE WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 0 and 14 THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS tagihan_kurang30,
						CASE WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 15 and 30 THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS tagihan_1530,
						CASE WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 30 DAY THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS tagihan_lebih30')
                ->from('sales')
                ->join('sma_companies', 'sma_companies.id = sma_sales.customer_id')
                ->where('sma_sales.biller_id', $this->session->userdata('company_id'))
                ->where('sma_sales.client_id', 'aksestoko')
                ->group_by('sma_sales.customer_id')

                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/piutang/$1') . '/?aksestoko=1' . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "ids")

                ->unset_column('ids');

            echo $this->datatables->generate();
        }
        //        tagihan_lebih30,tagihan_1530,tagihan_kurang30

    }

    public function getpiutangdetails($pdf = null, $xls = null)
    {
        $aksestoko = $this->input->get('aksestoko');

        $this->sma->checkPermissions('sales', true);

        $now  = date("d/m/Y");

        // $user = $this->input->get('user') ? $this->input->get('user') : null;

        $customer = $this->input->get('customer') ? $this->input->get('customer') : null;

        // if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
        //     $user = $this->session->userdata('user_id');
        // }

        if ($pdf || $xls) {
            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";

            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";

            $this->db
                ->select("date, reference_no, comp.nama_toko AS nama_toko, comp.phone_companies as phone_companies, grand_total,paid,(grand_total-paid) as balance,COALESCE(payment_term,0) as payment_term, COALESCE(due_date,date) as date2,DATEDIFF(COALESCE(due_date,date),NOW()) as sejak,customer,IF (
    sma_sales.client_id = 'aksestoko',
    CONCAT( ' (AksesToko)' ),
CONCAT( '(Forca POS)') 
) AS created_by, FSI.item_nane as iname, payment_status,, {$this->db->dbprefix('sales')}.id as id,due_date,nama_companies", false)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');


            //echo("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, ' (', " . $this->db->dbprefix('sale_items') . ".quantity, ')') SEPARATOR '\n') as iname, grand_total, paid, payment_status");

            $join = "(SELECT sma_companies.id,sma_companies.name AS nama_companies,sma_companies.company AS nama_toko,sma_companies.phone AS phone_companies FROM sma_companies WHERE id = " . $customer . ") comp";

            if ($aksestoko != null) {
                $this->db->where('sma_sales.client_id', 'aksestoko');

                // $join = "(SELECT sma_companies.id,sma_companies.name AS nama_companies,sma_companies.company AS nama_toko,sma_companies.phone AS phone_companies FROM sma_companies JOIN (
                //             SELECT cf1, id FROM sma_companies WHERE id = ".$customer."
                //         )cmp ON cmp.cf1 = sma_companies.cf1
                //         WHERE group_name = 'biller' OR group_name = 'customer' OR group_name = 'address') comp";
                // $this->db->join($join, 'sma_sales.customer_id = comp.id', 'inner');
            } else {
                $this->db->where('(sma_sales.client_id != \'aksestoko\' OR sma_sales.client_id is null)');

                // $join = "(SELECT sma_companies.id,sma_companies.name AS nama_companies,sma_companies.company AS nama_toko,sma_companies.phone AS phone_companies FROM sma_companies WHERE id = ".$customer.") comp";

            }
            $this->db->join($join, 'sma_sales.customer_id = comp.id', 'inner');
            //            if (!$this->Owner && !$this->Principal) {
            //                $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
            //            }
            $this->db->where('sma_sales.biller_id', $this->session->userdata('company_id'));
            // if ($user) {
            //     $this->db->where('sales.created_by', $user);
            // }
            $this->db->order_by('date');
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            //            var_dump($data);

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('piutang_report'));
                $sheet->SetCellValue('A1', lang('date_now') . " " . $now);
                $sheet->SetCellValue('A2', lang('date'));
                $sheet->SetCellValue('B2', lang('reference_no'));
                $sheet->SetCellValue('C2', lang('company'));
                $sheet->SetCellValue('D2', lang('name'));
                $sheet->SetCellValue('E2', lang('phone'));
                $sheet->SetCellValue('F2', lang('total_amount'));
                $sheet->SetCellValue('G2', lang('paid'));
                $sheet->SetCellValue('H2', lang('balance'));
                $sheet->SetCellValue('I2', lang('top'));
                $sheet->SetCellValue('J2', lang('tanggal_jt'));
                $sheet->SetCellValue('K2', lang('sejak'));

                $row = 3;
                $total = 0;
                $paid = 0;
                $balance = 0;

                $index = 0;
                $RowCounter = 2;
                foreach ($data as $data_row) {
                    if ($index > 0) {
                        if (
                            $sheet->getCell('B' . $RowCounter)->getValue() == $data_row->reference_no &&
                            $sheet->getCell('A' . $RowCounter)->getValue() == $this->sma->hrld($data_row->date)
                        ) {

                            /*
                                Untuk menggabungkan row yang memiliki date dan refrence number sama. sehingga menjadi 1 row saja
                            */
                            $product_name = $sheet->getCell('E' . $RowCounter)->getValue();
                            $qty = $sheet->getCell('F' . $RowCounter)->getValue();

                            $product_name .= ' , ' . $data_row->product_name;
                            $qty .= ' , ' . floatVal($data_row->quantity);

                            //                            $sheet->getCell('E'.$RowCounter)->setValue($product_name);
                            //                            $sheet->getCell('F'.$RowCounter)->setValue($qty);
                        } else {
                            $sheet->SetCellValue('A' . $row, date('d/m/Y', strtotime($data_row->date)));
                            $sheet->SetCellValue('B' . $row, $data_row->reference_no);
                            $sheet->SetCellValue('C' . $row, $data_row->nama_toko);
                            $sheet->SetCellValue('D' . $row, $data_row->nama_companies);
                            $sheet->SetCellValue('E' . $row, $data_row->phone_companies);
                            $sheet->SetCellValue('F' . $row, $data_row->grand_total);
                            $sheet->SetCellValue('G' . $row, $data_row->paid);
                            $sheet->SetCellValue('H' . $row, $data_row->balance);
                            $sheet->SetCellValue('I' . $row, $data_row->payment_term);
                            $sheet->SetCellValue('J' . $row, date('d/m/Y', strtotime($data_row->date2)));
                            $sheet->SetCellValue('K' . $row, $data_row->sejak);

                            $total += $data_row->grand_total;
                            $paid += $data_row->paid;
                            $balance += ($data_row->grand_total - $data_row->paid);
                            $row++;
                            $RowCounter++;
                        }
                    } else {
                        $sheet->SetCellValue('A' . $row, date('d/m/Y', strtotime($data_row->date)));
                        $sheet->SetCellValue('B' . $row, $data_row->reference_no);
                        $sheet->SetCellValue('C' . $row, $data_row->nama_toko);
                        $sheet->SetCellValue('D' . $row, $data_row->nama_companies);
                        $sheet->SetCellValue('E' . $row, $data_row->phone_companies);
                        $sheet->SetCellValue('F' . $row, $data_row->grand_total);
                        $sheet->SetCellValue('G' . $row, $data_row->paid);
                        $sheet->SetCellValue('H' . $row, $data_row->balance);
                        $sheet->SetCellValue('I' . $row, $data_row->payment_term);
                        $sheet->SetCellValue('J' . $row, date('d/m/Y', strtotime($data_row->date2)));
                        $sheet->SetCellValue('K' . $row, $data_row->sejak);

                        $total += $data_row->grand_total;
                        $paid += $data_row->paid;
                        $balance += ($data_row->grand_total - $data_row->paid);
                        $row++;
                    }
                    $index++;
                }
                $sheet->getStyle("F" . $row . ":H" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('F' . $row, $total);
                $sheet->SetCellValue('G' . $row, $paid);
                $sheet->SetCellValue('H' . $row, $balance);

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(20);
                $sheet->getColumnDimension('K')->setWidth(15);

                $filename = 'Piutang Report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);;
                if ($pdf) {
                    // echo "string";die;
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
                    echo "string";
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";

            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("date, reference_no,grand_total,paid,(grand_total-paid) as balance,COALESCE(payment_term,0), COALESCE(due_date,date),DATEDIFF(COALESCE(due_date,date),NOW()),customer,IF (
                            sma_sales.client_id = 'aksestoko',
                            CONCAT( ' (AksesToko)' ),
                        CONCAT( '(Forca POS)') 
                        ) AS created_by, FSI.item_nane as iname, payment_status, {$this->db->dbprefix('sales')}.id as id,due_date,nama_companies", false)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');

            $join = "(SELECT sma_companies.id,sma_companies.name AS nama_companies,sma_companies.company AS nama_toko,sma_companies.phone AS phone_companies FROM sma_companies WHERE id = " . $customer . ") comp";
            if ($aksestoko == 1) {
                $this->datatables->where('sma_sales.client_id', 'aksestoko');
                // $join = "(SELECT sma_companies.id,sma_companies.name AS nama_companies,sma_companies.company AS nama_toko,sma_companies.phone AS phone_companies FROM sma_companies JOIN (
                //             SELECT cf1, id FROM sma_companies WHERE id = ".$customer."
                //         )cmp ON cmp.cf1 = sma_companies.cf1
                //         WHERE group_name = 'biller' OR group_name = 'customer' OR group_name = 'address') comp";
                // $this->datatables->join($join, 'sma_sales.customer_id = comp.id', 'inner');
            } else {
                $this->datatables->where('(sma_sales.client_id != \'aksestoko\' OR sma_sales.client_id is null)');
                // $join = "(SELECT sma_companies.id,sma_companies.name AS nama_companies,sma_companies.company AS nama_toko,sma_companies.phone AS phone_companies FROM sma_companies WHERE id = ".$customer.") comp";
            }
            $this->datatables->join($join, 'sma_sales.customer_id = comp.id', 'inner');
            $this->datatables->where('sma_sales.biller_id', $this->session->userdata('company_id'));
            //            if (!$this->Owner && !$this->Principal) {
            //                $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
            //            }
            // if ($user) {
            //     $this->datatables->where('sales.created_by', $user);
            // }

            echo $this->datatables->generate();
        }
    }

    public function getpiutangall($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('customers', true);
        if ($pdf || $xls) {

            $this->db
                ->select('sma_sales.customer_id as ids,sma_companies.company as company,sma_companies.name as name,sma_companies.phone as phone,sma_companies.email as email,
                        count(sma_sales.customer_id) as pembelian,
                        COALESCE (sum(grand_total), 0) AS total_amount,
                        COALESCE (sum(paid), 0) AS paid,
                        (
                            COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
                        ) AS balance,
                        CASE
								WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 0 and 14  
								THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS tagihan_kurang30,
						CASE
								WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 15 and 30  
								THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS tagihan_1530,
						CASE
								WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 30 DAY 
								THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS tagihan_lebih30,
                        CASE
                                WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 0 DAY 
                                THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)
						END AS semua')
                ->from('sales')
                ->join('sma_companies', 'sma_companies.id = sma_sales.customer_id')
                ->where('sma_sales.biller_id', $this->session->userdata('company_id'))
                ->where('(sma_sales.client_id != \'aksestoko\' OR sma_sales.client_id is null)')
                ->group_by('sma_sales.customer_id');


            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('customers_report'))
                    ->SetCellValue('A1', lang('company'))
                    ->SetCellValue('B1', lang('name'))
                    ->SetCellValue('C1', lang('phone'))
                    ->SetCellValue('D1', lang('email'))
                    ->SetCellValue('E1', lang('total_sales'))
                    ->SetCellValue('F1', lang('total_amount'))
                    ->SetCellValue('G1', lang('paid'))
                    ->SetCellValue('H1', lang('balance'))
                    ->SetCellValue('I1', lang('semua'))
                    ->SetCellValue('J1', lang('h15'))
                    ->SetCellValue('K1', lang('h1530'))
                    ->SetCellValue('L1', lang('h30'));
                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->company)
                        ->SetCellValue('B' . $row, $data_row->name)
                        ->SetCellValue('C' . $row, $data_row->phone)
                        ->SetCellValue('D' . $row, $data_row->email)
                        ->SetCellValue('E' . $row, $data_row->pembelian)
                        ->SetCellValue('F' . $row, $this->sma->formatMoney($data_row->total_amount))
                        ->SetCellValue('G' . $row, $this->sma->formatMoney($data_row->paid))
                        ->SetCellValue('H' . $row, $this->sma->formatMoney($data_row->balance))
                        ->SetCellValue('I' . $row, $this->sma->formatMoney($data_row->semua))
                        ->SetCellValue('J' . $row, $this->sma->formatMoney($data_row->tagihan_kurang30))
                        ->SetCellValue('K' . $row, $this->sma->formatMoney($data_row->tagihan_1530))
                        ->SetCellValue('L' . $row, $this->sma->formatMoney($data_row->tagihan_lebih30));
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $filename = 'piutang_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');

            $this->datatables
                ->select('sma_sales.customer_id as ids,sma_companies.company,sma_companies.name,sma_companies.phone,sma_companies.email,
                        count(sma_sales.customer_id) as pembelian,
                        COALESCE (sum(grand_total), 0) AS total_amount,
                        COALESCE (sum(paid), 0) AS paid,
                        (COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0)) AS balance,
                        CASE WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 0 DAY THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS semua,
                        CASE WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 0 and 14 THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS tagihan_kurang30,
						CASE WHEN DATEDIFF(NOW(), COALESCE(due_date,date)) between 15 and 30 THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS tagihan_1530,
						CASE WHEN DATE(COALESCE(due_date,date)) < CURDATE() - INTERVAL 30 DAY THEN COALESCE (sum(grand_total), 0) - COALESCE (sum(paid), 0) END AS tagihan_lebih30')
                ->from('sales')
                ->join('sma_companies', 'sma_companies.id = sma_sales.customer_id')
                ->where('sma_sales.biller_id', $this->session->userdata('company_id'))
                ->where('(sma_sales.client_id != \'aksestoko\' OR sma_sales.client_id is null)')
                ->group_by('sma_sales.customer_id')

                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/piutang/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "ids")

                ->unset_column('ids');
            echo $this->datatables->generate();
        }
    }

    public function principal()
    {
        $this->load->model('db_model');
        if ($this->Settings->version == '2.3') {
            $this->session->set_flashdata('warning', 'Please complete your update by synchronizing your database.');
            redirect('sync');
        }

        $this->data['province'] = $this->db_model->getDataProvince();
        $this->data['distrib'] = $this->db_model->getDataDistrib();
        $this->data['total_aktivasi'] = $this->db_model->getTotal('total_aktivasi');
        $this->data['total_transaksi'] = $this->db_model->getTotal('total_transaksi');
        $this->data['toko_transaksi'] = $this->db_model->getTotal('toko_transaksi');
        $this->data['toko_repeat'] = $this->db_model->getTotal('toko_repeat');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('dashboard')));
        $meta = array('page_title' => lang('dashboard'), 'bc' => $bc);
        $this->page_construct('dashboard2', $meta, $this->data);
    }

    public function audittrail()
    {
        // if (!$this->Principal) {
        //     $this->session->set_flashdata('error', lang("access_denied"));
        //     redirect($_SERVER["HTTP_REFERER"]);
        // }

        $this->data['dataTypeAudittrail'] = [
            'customer_registration' => lang('customer_registration'),
            'customer_activation' => lang('customer_activation'),
            'customer_create_order' => lang('customer_create_order'),
            'distributor_change_price' => lang('distributor_change_price'),
            'customer_approve_reject_price' => lang('customer_approve_reject_price'),
            'distributor_create_delivery' => lang('distributor_create_delivery'),
            'customer_confirm_delivery' => lang('customer_confirm_delivery'),
            'customer_create_payment' => lang('customer_create_payment'),
            // 'distributor_sales_return'=>lang('distributor_sales_return'),
            'distributor_approve_reject_payment' => lang('distributor_approve_reject_payment'),
        ];

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('audittrail')));
        $meta = array('page_title' => lang('audittrail'), 'bc' => $bc);
        $this->page_construct('reports/audittrail', $meta, $this->data);
    }

    public function load_view_auditrail()
    {
        $this->data['page_title'] = lang($this->input->post('page'));
        $data['output'] = $this->load->view($this->theme . 'reports/audittrail_' . $this->input->post('page'), $this->data, true);
        echo json_encode($data);
    }

    public function get_audittrail_customer_registration($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(sma_companies.cf1, 'IDC-', '') as ibk, companies.company as nama_toko, log_audit_trail.type")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies', 'sma_companies.id  = log_audit_trail.customer_company_id')
            ->where('log_audit_trail.type = \'customer_registration\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrail_customer_registration_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));

        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(sma_companies.cf1, 'IDC-', '') as ibk, companies.company as nama_toko, log_audit_trail.type")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies', 'sma_companies.id  = log_audit_trail.customer_company_id')
            ->where('log_audit_trail.type = \'customer_registration\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');

        $q = $this->db->get();
        // var_dump($this->db->error());die;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('Customer Registration'))
                ->SetCellValue('A1', lang('date'))
                ->SetCellValue('B1', lang('Ip'))
                ->SetCellValue('C1', lang('ibk'))
                ->SetCellValue('D1', lang('customer'))
                ->SetCellValue('E1', lang('audittrail_activity'));

            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $filename = 'audittrail customer registration';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {

                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_customer_activation($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(sma_companies.cf1, 'IDC-', '') as ibk, companies.company as nama_toko, log_audit_trail.type")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies', 'sma_companies.id  = log_audit_trail.customer_company_id')
            ->where('log_audit_trail.type = \'customer_activation\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrailcustomer_activation_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(sma_companies.cf1, 'IDC-', '') as ibk, companies.company as nama_toko, log_audit_trail.type")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies', 'sma_companies.id  = log_audit_trail.customer_company_id')
            ->where('log_audit_trail.type = \'customer_activation\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('customer_activation'))
                ->SetCellValue('A1', lang('date'))
                ->SetCellValue('B1', lang('Ip'))
                ->SetCellValue('C1', lang('ibk'))
                ->SetCellValue('D1', lang('customer'))
                ->SetCellValue('E1', lang('audittrail_activity'));

            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $filename = 'audittrail customer registration';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_customer_create_order($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sale_no, sma_purchases.reference_no as purchase_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->where('log_audit_trail.type = \'customer_create_order\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        // echo 'DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \''.$start_date.'\' AND \''.$end_date.'\''
        echo $this->datatables->generate();
    }

    public function audittrail_customer_create_order_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company as distributor, distributor.cf1 as distributor_code, sma_sales.reference_no as sale_no, sma_purchases.reference_no as purchase_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->where('log_audit_trail.type = \'customer_create_order\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('customer_create_order'))
                ->SetCellValue('A1', lang('date'))
                ->SetCellValue('B1', lang('Ip'))
                ->SetCellValue('C1', lang('ibk'))
                ->SetCellValue('D1', lang('customer'))
                ->SetCellValue('E1', lang('audittrail_activity'))
                ->SetCellValue('F1', lang('distributor'))
                ->SetCellValue('G1', lang('distributor_code'))
                ->SetCellValue('H1', lang('sale_reference_no'))
                ->SetCellValue('I1', lang('purchase_ref'));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->sale_no)
                    ->SetCellValue('I' . $row, $data_row->purchase_no);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(25);
            $filename = 'audittrail customer create order';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_distributor_change_price($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', ''), customer.company as nama_toko, sma_sales.reference_no as sale_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->where('log_audit_trail.type = \'distributor_change_price\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrail_distributor_change_price_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, sma_sales.reference_no as sale_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->where('log_audit_trail.type = \'distributor_change_price\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('distributor_change_price'))
                ->SetCellValue('A1', lang("date"))
                ->SetCellValue('B1', lang("ip"))
                ->SetCellValue('C1', lang("distributor"))
                ->SetCellValue('D1', lang("distributor_code"))
                ->SetCellValue('E1', lang("audittrail_activity"))
                ->SetCellValue('F1', lang("ibk"))
                ->SetCellValue('G1', lang("customer"))
                ->SetCellValue('H1', lang("sale_reference_no"));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->company)
                    ->SetCellValue('D' . $row, $data_row->cf1)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->ibk)
                    ->SetCellValue('G' . $row, $data_row->nama_toko)
                    ->SetCellValue('H' . $row, $data_row->sale_no);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(25);
            $filename = 'audittrail distributor change price';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_customer_approve_reject_price($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_purchases.reference_no as purchase_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->where('(log_audit_trail.type = \'customer_approve_price\' OR log_audit_trail.type = \'customer_reject_price\')')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrail_customer_approve_reject_price_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_purchases.reference_no as purchase_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->where('(log_audit_trail.type = \'customer_approve_price\' OR log_audit_trail.type = \'customer_reject_price\')')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        // var_dump($this->db->error());die;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('approve_reject_price'))
                ->SetCellValue('A1', lang('date'))
                ->SetCellValue('B1', lang('Ip'))
                ->SetCellValue('C1', lang('ibk'))
                ->SetCellValue('D1', lang('customer'))
                ->SetCellValue('E1', lang('audittrail_activity'))
                ->SetCellValue('F1', lang('distributor'))
                ->SetCellValue('G1', lang('distributor_code'))
                ->SetCellValue('H1', lang('purchase_ref'));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->purchase_no);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(25);
            $filename = 'audittrail customer approve or reject price';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_distributor_create_delivery($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', ''), customer.company as nama_toko, sma_sales.reference_no as sale_no, sma_deliveries.do_reference_no as delivery_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_deliveries', 'sma_deliveries.id  = log_audit_trail.delivery_id')
            ->where('log_audit_trail.type = \'distributor_create_delivery\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrail_distributor_create_delivery_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', ''), customer.company as nama_toko, sma_sales.reference_no as sale_no, sma_deliveries.do_reference_no as delivery_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_deliveries', 'sma_deliveries.id  = log_audit_trail.delivery_id')
            ->where('log_audit_trail.type = \'distributor_create_delivery\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('distributor_create_delivery'))
                ->SetCellValue('A1', lang("date"))
                ->SetCellValue('B1', lang("ip"))
                ->SetCellValue('C1', lang("distributor"))
                ->SetCellValue('D1', lang("distributor_code"))
                ->SetCellValue('E1', lang("audittrail_activity"))
                ->SetCellValue('F1', lang("ibk"))
                ->SetCellValue('G1', lang("customer"))
                ->SetCellValue('H1', lang("sale_reference_no"))
                ->SetCellValue('I1', lang("do_reference_no"));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->company)
                    ->SetCellValue('D' . $row, $data_row->cf1)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->ibk)
                    ->SetCellValue('G' . $row, $data_row->nama_toko)
                    ->SetCellValue('H' . $row, $data_row->sale_no)
                    ->SetCellValue('I' . $row, $data_row->delivery_no);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(25);
            $filename = 'audittrail distributor create delivery';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_customer_confirm_delivery($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sales_no, sma_purchases.reference_no as purchase_no, sma_deliveries.do_reference_no as delivery_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_deliveries', 'sma_deliveries.id  = log_audit_trail.delivery_id')
            ->where('log_audit_trail.type = \'customer_confirm_delivery\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrail_customer_confirm_delivery_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sales_no, sma_purchases.reference_no as purchase_no, sma_deliveries.do_reference_no as delivery_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_deliveries', 'sma_deliveries.id  = log_audit_trail.delivery_id')
            ->where('log_audit_trail.type = \'customer_confirm_delivery\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('customer_confirm_delivery'))
                ->SetCellValue('A1', lang('date'))
                ->SetCellValue('B1', lang('Ip'))
                ->SetCellValue('C1', lang('ibk'))
                ->SetCellValue('D1', lang('customer'))
                ->SetCellValue('E1', lang('audittrail_activity'))
                ->SetCellValue('F1', lang('distributor'))
                ->SetCellValue('G1', lang('distributor_code'))
                ->SetCellValue('H1', lang('purchase_ref'))
                ->SetCellValue('I1', lang('sale_reference_no'))
                ->SetCellValue('J1', lang('do_reference_no'));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->purchase_no)
                    ->SetCellValue('I' . $row, $data_row->sales_no)
                    ->SetCellValue('J' . $row, $data_row->delivery_no);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(25);
            $sheet->getColumnDimension('J')->setWidth(25);
            $sheet->getColumnDimension('K')->setWidth(25);
            $filename = 'audittrail customer confirm delivery';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_customer_create_payment($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sales_no, sma_purchases.reference_no as purchase_no, sma_payment_temp.reference_no as customer_payment_ref")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_payment_temp', 'sma_payment_temp.id  = log_audit_trail.payment_temp_id')
            ->where('log_audit_trail.type = \'customer_create_payment\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrail_customer_create_payment_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sales_no, sma_purchases.reference_no as purchase_no, sma_payment_temp.reference_no as customer_payment_ref")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_payment_temp', 'sma_payment_temp.id  = log_audit_trail.payment_temp_id')
            ->where('log_audit_trail.type = \'customer_create_payment\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('customer_create_payment'))
                ->SetCellValue('A1', lang('date'))
                ->SetCellValue('B1', lang('Ip'))
                ->SetCellValue('C1', lang('ibk'))
                ->SetCellValue('D1', lang('customer'))
                ->SetCellValue('E1', lang('audittrail_activity'))
                ->SetCellValue('F1', lang('distributor'))
                ->SetCellValue('G1', lang('distributor_code'))
                ->SetCellValue('H1', lang('purchase_ref'))
                ->SetCellValue('I1', lang('sale_reference_no'))
                ->SetCellValue('J1', lang('customer_payment_ref'));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->purchase_no)
                    ->SetCellValue('I' . $row, $data_row->sales_no)
                    ->SetCellValue('J' . $row, $data_row->customer_payment_ref);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(25);
            $sheet->getColumnDimension('J')->setWidth(25);
            $sheet->getColumnDimension('K')->setWidth(25);
            $filename = 'audittrail customer confirm delivery';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function get_audittrail_distributor_approve_reject_payment($start_date, $end_date)
    {
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->load->library('datatables');
        $this->datatables->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', ''), customer.company as nama_toko, sma_sales.reference_no as sale_no, sma_payment_temp.reference_no as customer_payment_ref, purchase_payment.reference_no as purchase_payment_ref, sale_payment.reference_no as sale_payment_ref")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_payments sale_payment', 'sale_payment.id  = log_audit_trail.sale_payment_id')
            ->join('sma_payments purchase_payment', 'purchase_payment.id  = log_audit_trail.purchase_payment_id')
            ->join('sma_payment_temp', 'sma_payment_temp.id  = log_audit_trail.payment_temp_id')
            ->where('(log_audit_trail.type = \'distributor_approve_payment\' OR log_audit_trail.type = \'distributor_reject_payment\')')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        echo $this->datatables->generate();
    }

    public function audittrail_distributor_approve_reject_payment_action()
    {
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if ($end_date == '-' || $end_date == '') {
            $end_date = date('Y-m-d');
        } else {
            $end_date =  strtr($end_date, '/', '-');
        }
        $end_date = date('Y-m-d', strtotime($end_date));
        if ($start_date == '-' || $start_date == '') {
            $start_date = date('Y-m', strtotime($end_date)) . '-01';
        } else {
            $start_date =  strtr($start_date, '/', '-');
        }
        $start_date = date('Y-m-d', strtotime($start_date));
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, sma_sales.reference_no as sale_no, sma_payment_temp.reference_no as customer_payment_ref, purchase_payment.reference_no as purchase_payment_ref, sale_payment.reference_no as sale_payment_ref")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_payments sale_payment', 'sale_payment.id  = log_audit_trail.sale_payment_id')
            ->join('sma_payments purchase_payment', 'purchase_payment.id  = log_audit_trail.purchase_payment_id')
            ->join('sma_payment_temp', 'sma_payment_temp.id  = log_audit_trail.payment_temp_id')
            ->where('(log_audit_trail.type = \'distributor_approve_payment\' OR log_audit_trail.type = \'distributor_reject_payment\')')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        // var_dump($this->db->error());die;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        if (!empty($data)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('approve_reject_payment'))
                ->SetCellValue('A1', lang("date"))
                ->SetCellValue('B1', lang("ip"))
                ->SetCellValue('C1', lang("distributor"))
                ->SetCellValue('D1', lang("distributor_code"))
                ->SetCellValue('E1', lang("audittrail_activity"))
                ->SetCellValue('F1', lang("ibk"))
                ->SetCellValue('G1', lang("customer"))
                ->SetCellValue('H1', lang("sale_reference_no"))
                ->SetCellValue('I1', lang("customer_payment_ref"))
                ->SetCellValue('J1', lang("purchase_payment_ref"))
                ->SetCellValue('K1', lang("sale_payment_ref"));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->company)
                    ->SetCellValue('D' . $row, $data_row->cf1)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->ibk)
                    ->SetCellValue('G' . $row, $data_row->nama_toko)
                    ->SetCellValue('H' . $row, $data_row->sale_no)
                    ->SetCellValue('I' . $row, $data_row->customer_payment_ref)
                    ->SetCellValue('J' . $row, $data_row->purchase_payment_ref)
                    ->SetCellValue('K' . $row, $data_row->sale_payment_ref);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(25);
            $sheet->getColumnDimension('I')->setWidth(25);
            $sheet->getColumnDimension('J')->setWidth(25);
            $sheet->getColumnDimension('K')->setWidth(25);
            $filename = 'audittrail distributor create delivery';
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            if ($this->input->post('form_action') == 'pdf') {
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                );
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
                $objWriter->save('php://output');
                exit();
            }
            if ($this->input->post('form_action') == 'export_excel') {
                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        }
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }
    public function filter_tanggal_audittrail()
    {
        $this->load->view($this->theme . 'reports/audittrail_filter_export_all', $this->data);
    }

    public function audittrail_export_all()
    {
        $start_date =  strtr($this->input->post('start_date'), '/', '-');
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date =  strtr($this->input->post('end_date'), '/', '-');
        $end_date = date('Y-m-d', strtotime($end_date));
        $spreadsheet = new Spreadsheet();
        // sheet customer regist
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(sma_companies.cf1, 'IDC-', '') as ibk, companies.company as nama_toko, log_audit_trail.type")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies', 'sma_companies.id  = log_audit_trail.customer_company_id')
            ->where('log_audit_trail.type = \'customer_registration\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');

        $q = $this->db->get();
        $data = [];
        // var_dump($this->db->error());die;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle(lang('Customer Registration'))
            ->SetCellValue('A1', lang('date'))
            ->SetCellValue('B1', lang('Ip'))
            ->SetCellValue('C1', lang('ibk'))
            ->SetCellValue('D1', lang('customer'))
            ->SetCellValue('E1', lang('audittrail_activity'));

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);

        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type);
                $row++;
            }
        }

        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(sma_companies.cf1, 'IDC-', '') as ibk, companies.company as nama_toko, log_audit_trail.type")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies', 'sma_companies.id  = log_audit_trail.customer_company_id')
            ->where('log_audit_trail.type = \'customer_activation\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');

        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }
        // end of customer_regist
        // customer activation
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle(lang('customer_activation'))
            ->SetCellValue('A1', lang('date'))
            ->SetCellValue('B1', lang('Ip'))
            ->SetCellValue('C1', lang('ibk'))
            ->SetCellValue('D1', lang('customer'))
            ->SetCellValue('E1', lang('audittrail_activity'));

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $row = 2;
        if (!empty($data)) {
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type);
                $row++;
            }
        }
        // end of customer activation
        // customer_order
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company as distributor, distributor.cf1 as distributor_code, sma_sales.reference_no as sale_no, sma_purchases.reference_no as purchase_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->where('log_audit_trail.type = \'customer_create_order\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle(lang('customer_create_order'))
            ->SetCellValue('A1', lang('date'))
            ->SetCellValue('B1', lang('Ip'))
            ->SetCellValue('C1', lang('ibk'))
            ->SetCellValue('D1', lang('customer'))
            ->SetCellValue('E1', lang('audittrail_activity'))
            ->SetCellValue('F1', lang('distributor'))
            ->SetCellValue('G1', lang('distributor_code'))
            ->SetCellValue('H1', lang('sale_reference_no'))
            ->SetCellValue('I1', lang('purchase_ref'));

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);

        if (!empty($data)) {

            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->sale_no)
                    ->SetCellValue('I' . $row, $data_row->purchase_no);
                $row++;
            }
        }
        // end of customer create order
        // distributor change Price
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, sma_sales.reference_no as sale_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->where('log_audit_trail.type = \'distributor_change_price\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(3);
        $sheet->setTitle(lang('distributor_change_price'))
            ->SetCellValue('A1', lang("date"))
            ->SetCellValue('B1', lang("ip"))
            ->SetCellValue('C1', lang("distributor"))
            ->SetCellValue('D1', lang("distributor_code"))
            ->SetCellValue('E1', lang("audittrail_activity"))
            ->SetCellValue('F1', lang("ibk"))
            ->SetCellValue('G1', lang("customer"))
            ->SetCellValue('H1', lang("sale_reference_no"));
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->company)
                    ->SetCellValue('D' . $row, $data_row->cf1)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->ibk)
                    ->SetCellValue('G' . $row, $data_row->nama_toko)
                    ->SetCellValue('H' . $row, $data_row->sale_no);
                $row++;
            }
        }
        // end of distributor change Price

        // customer confirm Price
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_purchases.reference_no as purchase_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->where('(log_audit_trail.type = \'customer_approve_price\' OR log_audit_trail.type = \'customer_reject_price\')')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(4);
        $sheet->setTitle(lang('approve_reject_price'))
            ->SetCellValue('A1', lang('date'))
            ->SetCellValue('B1', lang('Ip'))
            ->SetCellValue('C1', lang('ibk'))
            ->SetCellValue('D1', lang('customer'))
            ->SetCellValue('E1', lang('audittrail_activity'))
            ->SetCellValue('F1', lang('distributor'))
            ->SetCellValue('G1', lang('distributor_code'))
            ->SetCellValue('H1', lang('purchase_ref'));
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->purchase_no);
                $row++;
            }
        }
        // End Of customer confirm Price

        // Distributor Create Delivery

        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', ''), customer.company as nama_toko, sma_sales.reference_no as sale_no, sma_deliveries.do_reference_no as delivery_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_deliveries', 'sma_deliveries.id  = log_audit_trail.delivery_id')
            ->where('log_audit_trail.type = \'distributor_create_delivery\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(5);
        $sheet->setTitle(lang('distributor_create_delivery'))
            ->SetCellValue('A1', lang("date"))
            ->SetCellValue('B1', lang("ip"))
            ->SetCellValue('C1', lang("distributor"))
            ->SetCellValue('D1', lang("distributor_code"))
            ->SetCellValue('E1', lang("audittrail_activity"))
            ->SetCellValue('F1', lang("ibk"))
            ->SetCellValue('G1', lang("customer"))
            ->SetCellValue('H1', lang("sale_reference_no"))
            ->SetCellValue('I1', lang("do_reference_no"));
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->company)
                    ->SetCellValue('D' . $row, $data_row->cf1)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->ibk)
                    ->SetCellValue('G' . $row, $data_row->nama_toko)
                    ->SetCellValue('H' . $row, $data_row->sale_no)
                    ->SetCellValue('I' . $row, $data_row->delivery_no);
                $row++;
            }
        }
        // End Of Distributor Create Delivery        

        // Customer Confirm Delivery
        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sales_no, sma_purchases.reference_no as purchase_no, sma_deliveries.do_reference_no as delivery_no")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_deliveries', 'sma_deliveries.id  = log_audit_trail.delivery_id')
            ->where('log_audit_trail.type = \'customer_confirm_delivery\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(6);
        $sheet->setTitle(lang('customer_confirm_delivery'))
            ->SetCellValue('A1', lang('date'))
            ->SetCellValue('B1', lang('Ip'))
            ->SetCellValue('C1', lang('ibk'))
            ->SetCellValue('D1', lang('customer'))
            ->SetCellValue('E1', lang('audittrail_activity'))
            ->SetCellValue('F1', lang('distributor'))
            ->SetCellValue('G1', lang('distributor_code'))
            ->SetCellValue('H1', lang('purchase_ref'))
            ->SetCellValue('I1', lang('sale_reference_no'))
            ->SetCellValue('J1', lang('do_reference_no'));
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);
        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->purchase_no)
                    ->SetCellValue('I' . $row, $data_row->sales_no)
                    ->SetCellValue('J' . $row, $data_row->delivery_no);
                $row++;
            }
        }
        // END OF Customer Confirm Delivery

        // Customer Create Payment

        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sales_no, sma_purchases.reference_no as purchase_no, sma_payment_temp.reference_no as customer_payment_ref")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_payment_temp', 'sma_payment_temp.id  = log_audit_trail.payment_temp_id')
            ->where('log_audit_trail.type = \'customer_create_payment\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(7);
        $sheet->setTitle(lang('customer_create_payment'))
            ->SetCellValue('A1', lang('date'))
            ->SetCellValue('B1', lang('Ip'))
            ->SetCellValue('C1', lang('ibk'))
            ->SetCellValue('D1', lang('customer'))
            ->SetCellValue('E1', lang('audittrail_activity'))
            ->SetCellValue('F1', lang('distributor'))
            ->SetCellValue('G1', lang('distributor_code'))
            ->SetCellValue('H1', lang('purchase_ref'))
            ->SetCellValue('I1', lang('sale_reference_no'))
            ->SetCellValue('J1', lang('customer_payment_ref'));
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);

        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->purchase_no)
                    ->SetCellValue('I' . $row, $data_row->sales_no)
                    ->SetCellValue('J' . $row, $data_row->customer_payment_ref);
                $row++;
            }
        }

        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, log_audit_trail.type, distributor.company, distributor.cf1, sma_sales.reference_no as sales_no, sma_purchases.reference_no as purchase_no, sma_payment_temp.reference_no as customer_payment_ref")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.customer_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_purchases', 'sma_purchases.id  = log_audit_trail.purchase_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_payment_temp', 'sma_payment_temp.id  = log_audit_trail.payment_temp_id')
            ->where('log_audit_trail.type = \'customer_create_payment\'')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(7);
        $sheet->setTitle(lang('customer_create_payment'))
            ->SetCellValue('A1', lang('date'))
            ->SetCellValue('B1', lang('Ip'))
            ->SetCellValue('C1', lang('ibk'))
            ->SetCellValue('D1', lang('customer'))
            ->SetCellValue('E1', lang('audittrail_activity'))
            ->SetCellValue('F1', lang('distributor'))
            ->SetCellValue('G1', lang('distributor_code'))
            ->SetCellValue('H1', lang('purchase_ref'))
            ->SetCellValue('I1', lang('sale_reference_no'))
            ->SetCellValue('J1', lang('customer_payment_ref'));
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);
        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->ibk)
                    ->SetCellValue('D' . $row, $data_row->nama_toko)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->distributor_code)
                    ->SetCellValue('H' . $row, $data_row->purchase_no)
                    ->SetCellValue('I' . $row, $data_row->sales_no)
                    ->SetCellValue('J' . $row, $data_row->customer_payment_ref);
                $row++;
            }
        }

        $this->db->select("log_audit_trail.created_at, log_audit_trail.ip_address, distributor.company, distributor.cf1, log_audit_trail.type, REPLACE(customer.cf1, 'IDC-', '') as ibk, customer.company as nama_toko, sma_sales.reference_no as sale_no, sma_payment_temp.reference_no as customer_payment_ref, purchase_payment.reference_no as purchase_payment_ref, sale_payment.reference_no as sale_payment_ref")
            ->from('log_audit_trail')
            ->join('sma_users', 'sma_users.id  = log_audit_trail.distributor_user_id')
            ->join('sma_companies customer', 'customer.id  = log_audit_trail.customer_company_id')
            ->join('sma_companies distributor', 'distributor.id  = log_audit_trail.distributor_company_id')
            ->join('sma_sales', 'sma_sales.id  = log_audit_trail.sale_id')
            ->join('sma_payments sale_payment', 'sale_payment.id  = log_audit_trail.sale_payment_id')
            ->join('sma_payments purchase_payment', 'purchase_payment.id  = log_audit_trail.purchase_payment_id')
            ->join('sma_payment_temp', 'sma_payment_temp.id  = log_audit_trail.payment_temp_id')
            ->where('(log_audit_trail.type = \'distributor_approve_payment\' OR log_audit_trail.type = \'distributor_reject_payment\')')
            ->where('DATE_FORMAT(sma_log_audit_trail.created_at, \'%Y-%m-%d\') BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'');
        $q = $this->db->get();
        $data = [];
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = null;
        }

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(8);
        $sheet->setTitle(lang('approve_reject_payment'))
            ->SetCellValue('A1', lang("date"))
            ->SetCellValue('B1', lang("ip"))
            ->SetCellValue('C1', lang("distributor"))
            ->SetCellValue('D1', lang("distributor_code"))
            ->SetCellValue('E1', lang("audittrail_activity"))
            ->SetCellValue('F1', lang("ibk"))
            ->SetCellValue('G1', lang("customer"))
            ->SetCellValue('H1', lang("sale_reference_no"))
            ->SetCellValue('I1', lang("customer_payment_ref"))
            ->SetCellValue('J1', lang("purchase_payment_ref"))
            ->SetCellValue('K1', lang("sale_payment_ref"));
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);

        if (!empty($data)) {
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->SetCellValue('A' . $row, $data_row->created_at)
                    ->SetCellValue('B' . $row, $data_row->ip_address)
                    ->SetCellValue('C' . $row, $data_row->company)
                    ->SetCellValue('D' . $row, $data_row->cf1)
                    ->SetCellValue('E' . $row, $data_row->type)
                    ->SetCellValue('F' . $row, $data_row->ibk)
                    ->SetCellValue('G' . $row, $data_row->nama_toko)
                    ->SetCellValue('H' . $row, $data_row->sale_no)
                    ->SetCellValue('I' . $row, $data_row->customer_payment_ref)
                    ->SetCellValue('J' . $row, $data_row->purchase_payment_ref)
                    ->SetCellValue('K' . $row, $data_row->sale_payment_ref);
                $row++;
            }
        }

        $filename = 'audittrail customer registration';
        $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
        ob_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        ob_clean();
        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $objWriter->save('php://output');
        exit();
        $this->session->set_flashdata('error', lang('nothing_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function sales_person()
    {
        $this->data['distributor'] = $this->site->getAllDistributor();
        $this->data['sales_person'] = $this->site->getAllSalesPerson();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_person')));
        $meta = array('page_title' => lang('sales_person'), 'bc' => $bc);
        $this->page_construct('reports/sales_person', $meta, $this->data);
    }

    public function get_sales_person()
    {
        if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
            $this->db->select("sma_log_audit_trail.created_at , distributor.company as ditributor_name, customer.company as customer_name ,sma_sales_person.NAME, sma_sales_person.reference_no")
                ->from('sma_users')
                ->join('sma_sales_person', 'sma_users.sales_person_id = sma_sales_person.id')
                ->join('sma_companies distributor', 'distributor.id = sma_sales_person.company_id')
                ->join('sma_companies customer', 'sma_users.company_id = customer.id')
                ->join('sma_log_audit_trail', 'sma_users.id = sma_log_audit_trail.customer_user_id AND sma_sales_person.id = sma_log_audit_trail.sales_person_id ');
            if ($this->input->post('distributor_id') != '' && $this->input->post('distributor_id') != null) {
                $this->db->where('sma_sales_person.company_id = ' . $this->input->post('distributor_id'));
            }
            if ($this->input->post('sales_person_id') != '' && $this->input->post('sales_person_id') != null) {
                $this->db->where('sma_sales_person.id = ' . $this->input->post('sales_person_id'));
            }
            $q = $this->db->get();
            $data = [];
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('sales_person'))
                    ->SetCellValue('A1', lang("time"))
                    ->SetCellValue('B1', lang("distributor"))
                    ->SetCellValue('C1', lang("customer"))
                    ->SetCellValue('D1', lang("sales_person"))
                    ->SetCellValue('E1', lang("referal_code"));

                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->created_at)
                        ->SetCellValue('B' . $row, $data_row->ditributor_name)
                        ->SetCellValue('C' . $row, $data_row->customer_name)
                        ->SetCellValue('D' . $row, $data_row->NAME)
                        ->SetCellValue('E' . $row, $data_row->reference_no);
                    $row++;
                }

                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $filename = lang('sales_person');
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
        } else {
            $distributor_id = $this->input->get('distributor_id') ?? false;
            $sales_person_id = $this->input->get('sales_person_id') ?? false;
            $this->load->library('datatables');
            $this->datatables->select("sma_sales_person.reference_no , distributor.company as ditributor_name, customer.company as customer_name ,sma_sales_person.NAME,sma_log_audit_trail.created_at")
                ->from('sma_users')
                ->join('sma_sales_person', 'sma_users.sales_person_id = sma_sales_person.id')
                ->join('sma_companies distributor', 'distributor.id = sma_sales_person.company_id')
                ->join('sma_companies customer', 'sma_users.company_id = customer.id')
                ->join('sma_log_audit_trail', 'sma_users.id = sma_log_audit_trail.customer_user_id AND sma_sales_person.id = sma_log_audit_trail.sales_person_id ');
            if ($distributor_id) {
                $this->datatables->where('sma_sales_person.company_id = ' . $distributor_id);
            }
            if ($sales_person_id) {
                $this->datatables->where('sma_sales_person.id = ' . $sales_person_id);
            }
            echo $this->datatables->generate();
        }
    }

    public function get_exported_excel_reports()
    {
        $this->load->library('datatables');
        $this->datatables->select("name, filename, filesize, created_at, url")
            ->from('sma_documents')
            ->where('is_deleted is null')
            ->add_column("Actions", "$1", "validate_url('url')");
        $this->datatables->edit_column("filesize", '$1', "human_filesize('filesize')");
        echo $this->datatables->generate();
    }

    public function exported_excel_reports()
    {
        $bc = [
            [
                'link' => base_url(),
                'page' => lang('home')
            ], [
                'link' => site_url('reports'),
                'page' => lang('reports')
            ], [
                'link' => '#',
                'page' => lang('exported_excel_reports')
            ]
        ];
        $meta = array('page_title' => lang('exported_excel_reports'), 'bc' => $bc);
        $this->page_construct('reports/exported_excel_reports', $meta, $this->data);
    }

    public function billing()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('billing_report')));
        $meta = array('page_title' => lang('billing_report'), 'bc' => $bc);
        $this->page_construct('reports/billing', $meta, $this->data);
    }

    public function getBillingReport($xls = null)
    {
        $this->sma->checkPermissions();
        if ($xls) {
            $join = "( SELECT
                    sma_companies.id AS id,
                    CAST((IFNULL(sales.total,0) + IFNULL(purchase.total,0))AS INT) AS total_transaksi 
                    FROM sma_companies
                    LEFT JOIN 
                            ( SELECT company_id, date AS TGL, COUNT(*) AS total 
                            FROM sma_sales 
                            GROUP BY sma_sales.company_id ) sales 
                    ON sma_companies.id = sales.company_id
                    LEFT JOIN 
                            ( SELECT company_id, date AS TGL, COUNT(*) AS total 
                            FROM sma_purchases 
                            GROUP BY sma_purchases.company_id) purchase 
                    ON sma_companies.id = purchase.company_id
                    WHERE sales.TGL IS NOT NULL 
                    OR purchase.TGL IS NOT NULL 
                    OR sales.total IS NOT NULL 
                    OR purchase.total IS NOT NULL ) jml";

            $this->db
                ->select("sma_companies.company,
                          sma_companies.cf1,
                          sma_authorized.plan_name,
                          sma_companies.cf1 AS `kode_distributor`, 
                          (CASE WHEN sma_authorized.start_date IS NULL THEN DATE('2020-01-01')
                            ELSE DATE_FORMAT(sma_authorized.start_date, '%Y-%m-%d') 
                           END) AS start_date,
                        (CASE WHEN sma_authorized.expired_date IS NULL THEN ADDDATE('2020-01-31', INTERVAL 11 MONTH) ELSE DATE_FORMAT(sma_authorized.expired_date, '%Y-%m-%d') END) AS expired_date,
                        (CASE WHEN (COUNT(sma_warehouses.id) > sma_authorized.warehouses) THEN COUNT(sma_warehouses.id) ELSE sma_authorized.warehouses END) AS Jumlah_gudang,
                        (CASE WHEN (COUNT(sma_users.id) > sma_authorized.users) THEN COUNT(sma_users.id) ELSE sma_authorized.users END) AS Jumlah_pengguna,
                        IFNULL(jml.total_transaksi,0) AS Jumlah_transaksi,
                        ((CAST((CASE WHEN (COUNT(sma_warehouses.id) > sma_authorized.warehouses) THEN COUNT(sma_warehouses.id) ELSE sma_authorized.warehouses END) / 5 AS INT) * (SELECT price FROM sma_addons WHERE name = 'warehouse') ) + (CAST((CASE WHEN (COUNT(sma_users.id) > sma_authorized.users ) THEN COUNT(sma_users.id) ELSE sma_authorized.users END) / 5 AS INT) * (SELECT price FROM sma_addons WHERE name = 'user') ) + (SELECT price FROM sma_plans WHERE id = 2)) AS price_per_bulan,
                        TIMESTAMPDIFF(MONTH, (CASE WHEN sma_authorized.start_date IS NULL THEN DATE('2020-01-01') ELSE DATE_FORMAT(sma_authorized.start_date, '%Y-%m-%d') END), (CASE WHEN sma_authorized.start_date IS NULL THEN ADDDATE('2020-01-31', INTERVAL 1 YEAR) ELSE DATE_FORMAT(sma_authorized.start_date, '%Y-%m-%d') END)) AS bulan_tagihan")
                ->from('sma_authorized')
                ->join('sma_companies', 'sma_authorized.company_id = sma_companies.id', 'left')
                ->join('sma_warehouses', 'sma_authorized.company_id = sma_warehouses.company_id', 'left')
                ->join('sma_users', 'sma_authorized.company_id = sma_users.id', 'left')
                ->join('sma_plans', 'sma_authorized.plan_id = sma_plans.id', 'left')
                ->join($join, 'jml.id = sma_authorized.company_id', 'left')
                ->where("sma_companies.company !='' AND sma_companies.cf1 NOT LIKE '%IDC-%' AND sma_companies.cf1 !='' AND sma_companies.cf2 IS NULL")
                ->group_by('sma_authorized.company_id')
                ->group_by('sma_authorized.id,sma_authorized.create_on', 'DESC');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }


            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('sales_report'))
                    ->setCellValue('A1', lang('company'))
                    ->setCellValue('B1', lang('kode_distributor'))
                    ->setCellValue('C1', lang('plan_name'))
                    ->setCellValue('D1', lang('start_date'))
                    ->setCellValue('E1', lang('expired_date'))
                    ->setCellValue('F1', lang('jumlah_gudang'))
                    ->setCellValue('G1', lang('jumlah_pengguna'))
                    ->setCellValue('H1', lang('jumlah_transaksi'))
                    ->setCellValue('I1', lang('price'))
                    ->setCellValue('J1', lang('bulan_tagihan'));


                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $sheet->SetCellValue('A' . $row, $data_row->company)
                        ->SetCellValue('B' . $row, $data_row->kode_distributor)
                        ->SetCellValue('C' . $row, $data_row->plan_name)
                        ->SetCellValue('D' . $row, $data_row->start_date)
                        ->SetCellValue('E' . $row, $data_row->expired_date)
                        ->SetCellValue('F' . $row, $data_row->Jumlah_gudang)
                        ->SetCellValue('G' . $row, $data_row->Jumlah_transaksi)
                        ->SetCellValue('H' . $row, $data_row->Jumlah_pengguna)
                        ->SetCellValue('I' . $row, $data_row->price_per_bulan)
                        ->SetCellValue('J' . $row, $data_row->bulan_tagihan);
                    $row++;
                    $total += $data_row->price_per_bulan;
                }
                $sheet->getStyle("I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $sheet->SetCellValue('I' . $row, $total);
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(20);


                $filename = lang('billing_report');
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $q = "( SELECT 
                        sma_authorized.id,
                        sma_companies.company,
                        sma_companies.cf1,
                        sma_authorized.plan_name,
                        (CASE WHEN sma_authorized.start_date IS NULL THEN DATE('2020-01-01') ELSE DATE_FORMAT(sma_authorized.start_date, '%Y-%m-%d') END) AS start_date,
                        (CASE WHEN sma_authorized.expired_date IS NULL THEN ADDDATE('2020-01-31', INTERVAL 11 MONTH) ELSE DATE_FORMAT(sma_authorized.expired_date, '%Y-%m-%d') END) AS `expired_date`,
                        (CASE WHEN (COUNT(sma_warehouses.id) > sma_authorized.warehouses) THEN COUNT(sma_warehouses.id) ELSE sma_authorized.warehouses END) AS jumlah_gudang,
                        (CASE WHEN (COUNT(sma_users.id) > sma_authorized.users) THEN COUNT(sma_users.id) ELSE sma_authorized.users END) AS jumlah_pengguna,
                        IFNULL(jml.total_transaksi,0) AS jumlah_transaksi,
                        ((CAST((CASE WHEN (COUNT(sma_warehouses.id) > sma_authorized.warehouses) THEN COUNT(sma_warehouses.id) ELSE sma_authorized.warehouses END) / 5 AS INT) * (SELECT price FROM sma_addons WHERE name = 'warehouse') ) + (CAST((CASE WHEN (COUNT(sma_users.id) > sma_authorized.users ) THEN COUNT(sma_users.id) ELSE sma_authorized.users END) / 5 AS INT) * (SELECT price FROM sma_addons WHERE name = 'user') ) + (SELECT price FROM sma_plans WHERE id = 2)) AS price_per_bulan,
                        TIMESTAMPDIFF(MONTH, (CASE WHEN sma_authorized.start_date IS NULL THEN DATE('2020-01-01') ELSE DATE_FORMAT(sma_authorized.start_date, '%Y-%m-%d') END), (CASE WHEN sma_authorized.start_date IS NULL THEN ADDDATE('2020-01-31', INTERVAL 1 YEAR) ELSE DATE_FORMAT(sma_authorized.start_date, '%Y-%m-%d') END)) AS bulan_tagihan
                        FROM sma_authorized 
                        LEFT JOIN sma_companies ON sma_authorized.company_id = sma_companies.id
                        LEFT JOIN sma_warehouses ON sma_authorized.company_id = sma_warehouses.company_id
                        LEFT JOIN sma_users ON sma_authorized.company_id = sma_users.id
                        LEFT JOIN sma_plans ON sma_authorized.plan_id = sma_plans.id
                        LEFT JOIN 
                                ( SELECT
                                    sma_companies.id AS id,
                                    CAST((IFNULL(sales.total,0) + IFNULL(purchase.total,0))AS INT) AS total_transaksi 
                                    FROM sma_companies
                                    LEFT JOIN 
                                        ( SELECT company_id, date AS TGL, COUNT(*) AS total 
                                        FROM sma_sales 
                                        WHERE DATE_FORMAT(date, '%Y-%m-%d') BETWEEN '2019-12-01' AND NOW() 
                                        GROUP BY sma_sales.company_id ) sales 
                                ON sma_companies.id = sales.company_id
                                LEFT JOIN 
                                        ( SELECT company_id, date AS TGL, COUNT(*) AS total 
                                        FROM sma_purchases 
                                        WHERE DATE_FORMAT(date, '%Y-%m-%d') BETWEEN '2019-12-01' AND NOW() 
                                        GROUP BY sma_purchases.company_id) purchase 
                                ON sma_companies.id = purchase.company_id
                                WHERE sales.TGL IS NOT NULL 
                                OR purchase.TGL IS NOT NULL 
                                OR sales.total IS NOT NULL 
                                OR purchase.total IS NOT NULL ) jml
                        ON jml.id = sma_authorized.company_id
                        WHERE (sma_companies.company !='' AND sma_companies.cf1 NOT LIKE '%IDC-%' AND sma_companies.cf1 !='' AND sma_companies.cf2 IS NULL) 
                        GROUP BY sma_authorized.company_id
                        ORDER BY sma_authorized.id,sma_authorized.create_on DESC) tmp";
            $this->datatables
                ->select("tmp.cf1, 
                        tmp.company, 
                        tmp.plan_name, 
                        tmp.start_date, 
                        tmp.expired_date, 
                        tmp.jumlah_gudang, 
                        tmp.jumlah_pengguna, 
                        tmp.jumlah_transaksi, 
                        tmp.price_per_bulan, 
                        tmp.bulan_tagihan")
                ->from('sma_authorized')
                ->join($q, 'tmp.id = sma_authorized.id', 'left')
                ->join('sma_companies', 'sma_authorized.company_id = sma_companies.id', 'left')
                ->where("sma_companies.company !='' AND sma_companies.cf1 NOT LIKE '%IDC-%' AND sma_companies.cf1 !='' AND sma_companies.cf2 IS NULL")
                ->group_by('sma_authorized.company_id')
                ->group_by('sma_authorized.id,sma_authorized.create_on', 'DESC');
            echo $this->datatables->generate();
        }
    }

    public function item_delivered()
    {
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('item_delivered')));
        $meta = array('page_title' => lang('item_delivered'), 'bc' => $bc);
        $this->page_construct('reports/item_delivered', $meta, $this->data);
    }

    public function get_item_delivered($start_date = '-', $end_date = '-')
    {
        if ($this->input->post('form_action')) {

            $this->db->save_queries = true;

            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            if ($start_date != null) {
                $start_date = strtr($start_date, '/', '-');
                $start_date = date("Y-m-d", strtotime($start_date));
            }

            if ($end_date != null) {
                $end_date = strtr($end_date, '/', '-');
                $end_date = date("Y-m-d", strtotime($end_date));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }
            $this->db->select("sma_sales.date AS sale_date,
                                CONCAT(sma_sales.reference_no, '-', sma_sales.biller_id) AS sale_no,
                                (
                                IF
                                    (
                                        sma_products.supplier1_part_no != '',
                                        sma_products.supplier1_part_no,
                                    IF
                                        (
                                            distributor.cf1 IS NOT NULL,
                                            distributor.cf1,
                                        IF
                                            ( distributor.cf2 IS NOT NULL, distributor.cf2, distributor.cf3 ) 
                                        ) 
                                    ) 
                                ) AS distributor_code,
                                distributor.company AS distributor_name,
                                sma_warehouses.code AS warehouse_code,
                                sma_warehouses.name as warehouse_name,
                                REPLACE(customer.cf1 , 'IDC-', '') AS customer_code,
                                customer.company AS customer_name,
                                sma_sales.sale_status AS sale_status,
                                sma_sales.grand_total AS grand_total,
                                sma_sales.paid AS total_paid,
                                sma_sales.payment_status AS payment_status,
                                sma_products.code AS product_code,
                                sma_products.name AS product_name,
                                sma_delivery_items.quantity_ordered AS quantity_ordered,
                                CONCAT(sma_users.first_name, ' ', sma_users.last_name) AS created_by,
                                sma_deliveries.date AS delivery_date,
                                CONCAT(sma_deliveries.do_reference_no, '-', sma_sales.biller_id) AS delivery_no,
                                ROUND ( sma_delivery_items.quantity_sent  ) AS quantity_sent,
                                sma_deliveries.status AS delivery_status,
                                CONCAT(deliv_users.first_name, ' ', deliv_users.last_name)  AS delivery_created_by")
                ->from('sma_delivery_items')
                ->join('sma_deliveries', 'sma_delivery_items.delivery_id = sma_deliveries.id')
                ->join('sma_sales', 'sma_deliveries.sale_id = sma_sales.id')
                ->join('sma_companies distributor', 'distributor.id = sma_sales.biller_id')
                ->join('sma_warehouses', 'sma_sales.warehouse_id = sma_warehouses.id')
                ->join('sma_users', 'sma_sales.created_by = sma_users.id')
                ->join('sma_companies customer', 'customer.id = sma_users.company_id')
                ->join('sma_products', 'sma_delivery_items.product_id = sma_products.id')
                // ->join('sma_delivery_items returnItem', 'returnItem.delivery_items_id = sma_delivery_items.id', 'left')
                ->join('sma_users deliv_users', 'sma_deliveries.created_by = deliv_users.id')
                ->where('sma_sales.client_id = \'aksestoko\'')
                ->where('sma_sales.biller_id != 6');
            if ($start_date == null && $end_date == null) {
                $cur = date('Y-m') . '-01';
                $this->db->where("sma_sales.date >= CAST( \"" . $cur . "\" AS DATE )");
            } elseif ($start_date != null && $end_date == null) {
                $this->db->where("sma_sales.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == null && $end_date != null) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $f_end_date . '" and "' . $end_date . '"');
            } elseif ($start_date != null && $end_date != null) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }



            //     $start_date =  $this->input->get('start_date') ?? date('Y/m').'/01';

            // $start_date = date("Y-m-d", strtotime($start_date));
            // $end_date =  $this->input->get('end_date') ?? date("Y/m/d");
            // $end_date =  strtr($end_date, '/', '-');
            // $end_date = date("Y-m-d", strtotime($end_date));
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('item_delivered'))
                    ->setCellValue('A1', lang("sale_date"))
                    ->setCellValue('B1', lang("sale_reference_no"))
                    ->setCellValue('C1', lang("distributor_code"))
                    ->setCellValue('D1', lang("distributor"))
                    ->setCellValue('E1', lang("warehouse_code"))
                    ->setCellValue('F1', lang("warehouse"))
                    ->setCellValue('G1', lang("customer_code"))
                    ->setCellValue('H1', lang("customer"))
                    ->setCellValue('I1', lang("sale_status"))
                    ->setCellValue('J1', lang("grand_total"))
                    ->setCellValue('K1', lang("total_paid"))
                    ->setCellValue('L1', lang("payment_status"))
                    ->setCellValue('M1', lang("product_code"))
                    ->setCellValue('N1', lang("product"))
                    ->setCellValue('O1', lang("total_quantity"))
                    ->setCellValue('P1', lang("created_by"))
                    ->setCellValue('Q1', lang("delivery_date"))
                    ->setCellValue('R1', lang("do_reference_no"))
                    ->setCellValue('S1', lang("quantity_sent"))
                    ->setCellValue('T1', lang("delivery_status"))
                    ->setCellValue('U1', lang("created_by"));


                $row = 2;
                foreach ($data as $data_row) {
                    $sheet->getStyle('A' . $row)
                        ->getNumberFormat()
                        ->setFormatCode("MM/DD/YYYY");
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->sale_date);

                    $sheet->setCellValue('A' . $row, $date)
                        ->setCellValue('B' . $row, $data_row->sale_no)
                        ->setCellValue('C' . $row, $data_row->distributor_code)
                        ->setCellValue('D' . $row, $data_row->distributor_name)
                        ->setCellValue('E' . $row, $data_row->warehouse_code)
                        ->setCellValue('F' . $row, $data_row->warehouse_name)
                        ->setCellValue('G' . $row, $data_row->customer_code)
                        ->setCellValue('H' . $row, $data_row->customer_name)
                        ->setCellValue('I' . $row, $data_row->sale_status)
                        ->setCellValue('J' . $row, $data_row->grand_total)
                        ->setCellValue('K' . $row, $data_row->total_paid)
                        ->setCellValue('L' . $row, $data_row->payment_status)
                        ->setCellValue('M' . $row, $data_row->product_code)
                        ->setCellValue('N' . $row, $data_row->product_name)
                        ->setCellValue('O' . $row, $data_row->quantity_ordered)
                        ->setCellValue('P' . $row, $data_row->created_by)
                        ->setCellValue('Q' . $row, $data_row->delivery_date)
                        ->setCellValue('R' . $row, $data_row->delivery_no)
                        ->setCellValue('S' . $row, $data_row->quantity_sent)
                        ->setCellValue('T' . $row, $data_row->delivery_status)
                        ->setCellValue('U' . $row, $data_row->delivery_created_by);

                    $row++;
                }

                $filename = lang('item_delivered');
                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

                ob_clean();
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                ob_clean();
                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                $objWriter->save('php://output');
                exit();
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            if ($start_date != '-') {
                $start_date = date("Y-m-d", strtotime($start_date));
            }

            if ($end_date != '-') {
                $end_date = date("Y-m-d", strtotime($end_date . "+ 1 day"));
                $f_end_date = date("Y-m-01", strtotime($end_date));
            }
            $this->load->library('datatables');
            $this->datatables->select("sma_sales.date AS sale_date,
                                CONCAT(sma_sales.reference_no, '-', sma_sales.biller_id) AS sale_no,
                                (
                                IF
                                    (
                                        sma_products.supplier1_part_no != '',
                                        sma_products.supplier1_part_no,
                                    IF
                                        (
                                            distributor.cf1 IS NOT NULL,
                                            distributor.cf1,
                                        IF
                                            ( distributor.cf2 IS NOT NULL, distributor.cf2, distributor.cf3 ) 
                                        ) 
                                    ) 
                                ) AS distributor_code,
                                distributor.company AS distributor_name,
                                sma_warehouses.CODE AS warehouse_code,
                                sma_warehouses.NAME as warehouse_name,
                                REPLACE(customer.cf1 , 'IDC-', '') AS customer_code,
                                customer.company AS customer_name,
                                sma_sales.sale_status AS sale_status,
                                sma_sales.grand_total AS grand_total,
                                sma_sales.paid AS total_paid,
                                sma_sales.payment_status AS payment_status,
                                sma_products.CODE AS product_code,
                                sma_products.NAME AS product_name,
                                sma_delivery_items.quantity_ordered AS quantity_ordered,
                                CONCAT(sma_users.first_name, ' ', sma_users.last_name) AS created_by,
                                sma_deliveries.date AS delivery_date,
                                CONCAT(sma_deliveries.do_reference_no, '-', sma_sales.biller_id) AS delivery_no,
                                ROUND ( sma_delivery_items.quantity_sent  ) AS quantity_sent,
                                sma_deliveries.status AS delivery_status,
                                CONCAT(deliv_users.first_name, ' ', deliv_users.last_name)  AS delivery_created_by")
                ->from('sma_delivery_items')
                ->join('sma_deliveries', 'sma_delivery_items.delivery_id = sma_deliveries.id')
                ->join('sma_sales', 'sma_deliveries.sale_id = sma_sales.id')
                ->join('sma_companies distributor', 'distributor.id = sma_sales.biller_id')
                ->join('sma_warehouses', 'sma_sales.warehouse_id = sma_warehouses.id')
                ->join('sma_users', 'sma_sales.created_by = sma_users.id')
                ->join('sma_companies customer', 'customer.id = sma_users.company_id')
                ->join('sma_products', 'sma_delivery_items.product_id = sma_products.id')
                // ->join('sma_delivery_items returnItem', 'returnItem.delivery_items_id = sma_delivery_items.id', 'left')
                ->join('sma_users deliv_users', 'sma_deliveries.created_by = deliv_users.id')
                ->where('sma_sales.client_id = \'aksestoko\'')
                ->where('sma_sales.biller_id != 6');
            if ($start_date == '-' && $end_date == '-') {
                $cur = date('Y-m') . '-01';
                $this->datatables->where("sma_sales.date >= CAST( \"" . $cur . "\" AS DATE )");
            } elseif ($start_date != '-' && $end_date == '-') {
                $this->datatables->where("sma_sales.date >= CAST( \"" . $start_date . "\" AS DATE )");
            } elseif ($start_date == '-' && $end_date != '-') {
                $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $f_end_date . '" and "' . $end_date . '"');
            } elseif ($start_date != '-' && $end_date != '-') {
                $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            // $this->db->get();
            // var_dump($this->db->error());
            // if ($distributor_id) {
            //     $this->datatables->where('sma_sales_person.company_id = '.$distributor_id);
            // }
            // if ($sales_person_id) {
            //     $this->datatables->where('sma_sales_person.id = '.$sales_person_id);
            // }
            echo $this->datatables->generate();
        }
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------//

    public function history_login()
    {
        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('history_login')));
        $meta = array('page_title' => lang('history_login'), 'bc' => $bc);
        $this->page_construct('reports/history-login', $meta, $this->data);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------//

    public function getHistoryLogin($start = null, $end = null)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('user_logins')}.time, {$this->db->dbprefix('user_logins')}.ip_address, {$this->db->dbprefix('users')}.username, {$this->db->dbprefix('users')}.email, CONCAT({$this->db->dbprefix('users')}.first_name, ' ' ,{$this->db->dbprefix('users')}.last_name) AS name")
            ->from("user_logins")
            ->join("users", 'users.id=user_logins.user_id', 'left');
        if ($start || $end) {
            if ($start != null) {
                $this->datatables->where("cast(time as date) >=", date('Y-m-d', strtotime($start)));
            }
            if ($end != null) {
                $this->datatables->where("cast(time as date) <=", date('Y-m-d', strtotime($end)));
            }
        } else {
            $start = date('Y-m-01');
            $end = date('Y-m-d');
            $this->datatables->where("cast(time as date) between '$start' and '$end' ");
        }
        echo $this->datatables->generate();
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------//

    public function history_login_actions()
    {
        if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
            $start = str_replace('/', '-', $_POST['start_date']);
            $end = str_replace('/', '-', $_POST['end_date']);
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('User Login'))
                ->SetCellValue('A1', lang('user'))
                ->SetCellValue('B1', lang('Ip Address'))
                ->SetCellValue('C1', lang('Email'))
                ->SetCellValue('D1', lang('Time'));

            $row = 2;
            $data = $this->site->getHistoryLoginByID($start, $end);
            foreach ($data as $sc) {
                $sheet->SetCellValue('A' . $row, $sc->username)
                    ->SetCellValue('B' . $row, $sc->ip_address)
                    ->SetCellValue('C' . $row, $sc->login)
                    ->SetCellValue('D' . $row, $sc->time);
                $row++;
            }
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            $filename = 'User Login' . date('Y_m_d_H_i_s');
            if ($this->input->post('form_action') == 'export_pdf') {
                $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                $sheet->getDefaultStyle()->applyFromArray($styleArray);
                $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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
        }
    }
    //-------------------------------------------------------------------------------------------------------------------------------//
    public function getSalesReportRev($year = null, $month = null, $pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('sales', true);
        $product        = $this->input->get('product') ? $this->input->get('product') : null;
        $user           = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse      = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $reference_no   = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date     = ($this->input->get('start_date') && $this->input->get('start_date') != '-') ? $this->input->get('start_date') : null;
        $end_date       = ($this->input->get('end_date') && $this->input->get('end_date') != '-') ? $this->input->get('end_date') : null;
        $serial         = $this->input->get('serial') ? $this->input->get('serial') : null;
        if ($start_date) {
            $start_date   = $this->sma->fld($start_date);
            $end_date     = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {
            $join = "(SELECT customer_id AS customer_id,
                            company_id AS company_id,   
                            date AS date, 
                            reference_no AS reference_no, 
                            biller AS biller, 
                            customer AS customer, 
                            client AS cli, 
                            produk AS produk,
                            total_amount AS total_amount,
                            paid AS paid,
                            balance AS balance,
                            payment_status AS payment_status,
                            idsales AS id_sales,
                            product_id AS product_id,
                            serial_no AS serial_no,
                            biller_id AS biller_id,
                            warehouseid AS warehouse_id
                    FROM ( SELECT customer_id, sma_sales.company_id, date, reference_no, biller, customer, 
                                (CASE WHEN sma_sales.client_id IS NULL THEN 'Forca POS' ELSE sma_sales.client_id END) AS client,
                                        GROUP_CONCAT(CONCAT(sma_products.name, '__', sma_sale_items.quantity) SEPARATOR '___') AS produk, 
                                COALESCE (grand_total, 0) AS total_amount,
                                COALESCE (paid, 0) AS paid,
                                COALESCE (grand_total, 0) - COALESCE (paid, 0)  AS balance,
                                payment_status,
                                sma_sales.id AS idsales,
                                sma_sale_items.product_id AS product_id,
                                sma_sale_items.serial_no AS serial_no,
                                        sma_sales.biller_id AS biller_id,
                                sma_sales.warehouse_id AS warehouseid 
                            FROM sma_sales
                            LEFT JOIN sma_sale_items ON sma_sale_items.sale_id = sma_sales.id
                            LEFT JOIN sma_products ON sma_products.id = sma_sale_items.product_id
                            WHERE sma_sales.client_id != 'aksestoko' OR sma_sales.client_id IS NULL
                            GROUP BY sma_sales.id
                            UNION
                            SELECT cmp.id AS customer_id,
                                cmp.company_id AS company_id,
                                comp.date AS date,
                                comp.reference_no AS reference_no,
                                comp.biller AS biller,
                                comp.customer AS customer,
                                comp.clientID AS client,
                                comp.produk1 AS produk,
                                        comp.total_amount AS total_amount,
                                comp.paid AS paid,
                                comp.balance AS balance,
                                comp.payment_status AS payment_status, 
                                comp.idsales AS idsales,
                                comp.product_id AS product_id,
                                        comp.serial_no AS serial_no,
                                        comp.biller_id AS biller_id,
                                comp.warehouse_id AS warehouseid 
                            FROM sma_companies
                            JOIN ( SELECT sma_companies.id AS customer_id,
                                        sma_sales.date AS date, 
                                        sma_sales.reference_no AS reference_no, 
                                        sma_sales.biller AS biller, 
                                        sma_sales.customer AS customer, 
                                        (CASE WHEN sma_sales.client_id = 'aksestoko' THEN 'AksesToko' ELSE sma_sales.client_id END) AS clientID,
                                        GROUP_CONCAT(CONCAT(sma_products.name, '__', sma_sale_items.quantity) SEPARATOR '___') AS produk1,
                                        COALESCE (grand_total, 0) AS total_amount,
                                        COALESCE (paid, 0) AS paid,
                                        COALESCE (grand_total, 0) - COALESCE (paid, 0) AS balance,
                                        sma_sales.payment_status AS payment_status,
                                        sma_sales.id AS idsales,
                                        sma_sale_items.product_id AS product_id,
                                        sma_sale_items.serial_no AS serial_no,
                                                sma_sales.biller_id AS biller_id,
                                        sma_sales.warehouse_id AS warehouse_id 
                                FROM sma_sales
                                LEFT JOIN sma_sale_items ON sma_sale_items.sale_id = sma_sales.id
                                    LEFT JOIN sma_products ON sma_products.id = sma_sale_items.product_id
                                JOIN sma_users ON sma_users.id = sma_sales.created_by
                                JOIN sma_companies ON sma_users.company_id = sma_companies.id
                                WHERE sma_sales.client_id = 'aksestoko' GROUP BY sma_sales.id) comp ON comp.customer_id = sma_companies.id
                            JOIN sma_companies AS cmp ON cmp.cf1 = sma_companies.cf1 AND cmp.group_name = 'customer') AS x ) company";
            $this->db
                ->select("company.date, 
                          company.reference_no, 
                          company.biller, 
                          company.cf1,
                          company.customer,
                          company.product_name,
                          company.quantity,
                          company.total_amount,
                          company.paid,
                          company.payment_status,
                          company.code,
                          company.name,
                          company.created, 
                          company.username")
                ->from('sma_companies')
                ->join($join, 'company.customer_id = sma_companies.id');
            if ($month != '-') {
                $this->db->where('month(company.date)', $month);
            }
            if ($year != '-') {
                $this->db->where('year(company.date)', $year);
            }
            if (!$this->Owner && !$this->Principal) {
                $this->db->where('company.company_id', $this->session->userdata('company_id'));
            }
            if ($user) {
                $this->db->where('company.cli', $user);
            }
            if ($product) {
                $this->db->where('company.product_id', $product);
            }
            if ($serial) {
                $this->db->like('company.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('company.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('company.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('company.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('company.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where('company.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            if (!empty($data)) {
                $spreadsheet    = new Spreadsheet();
                $sheet          = $spreadsheet->setActiveSheetIndex(0);
                $sheet->setTitle(lang('sales_report'))
                    ->setCellValue('A1', lang('date'))
                    ->setCellValue('B1', lang('reference_no'))
                    ->setCellValue('C1', lang('biller'))
                    ->setCellValue('D1', lang('warehouse_code'))
                    ->setCellValue('E1', lang('warehouse'))
                    ->setCellValue('F1', lang('customers_code'))
                    ->setCellValue('G1', lang('customer'))
                    ->setCellValue('H1', lang('product'))
                    ->setCellValue('I1', lang('quantity'))
                    ->setCellValue('J1', lang('grand_total'))
                    ->setCellValue('K1', lang('paid'))
                    ->setCellValue('L1', lang('balance'))
                    ->setCellValue('M1', lang('payment_status'))
                    ->setCellValue('N1', lang('created_by'));
                $row            = 2;
                $total          = 0;
                $paid           = 0;
                $balance        = 0;
                $index          = 0;
                $RowCounter     = 2;
                $merge          = false;
                foreach ($data as $data_row) {
                    $data_row->created = $data_row->created . ' [' . $data_row->username . ']';
                    if ($index > 0) {
                        if (
                            $sheet->getCell('B' . $RowCounter)->getValue() == $data_row->reference_no &&
                            $sheet->getCell('A' . $RowCounter)->getValue() == $this->sma->hrld($data_row->date)
                        ) {
                            $sheet->setCellValue('H' . $row, $data_row->product_name)
                                ->setCellValue('I' . $row, $data_row->quantity);
                            $merge = true;
                        } else {
                            if ($merge) {
                                $sheet->mergeCells('A' . ($RowCounter) . ':A' . ($row - 1))
                                    ->mergeCells('B' . ($RowCounter) . ':B' . ($row - 1))
                                    ->mergeCells('C' . ($RowCounter) . ':C' . ($row - 1))
                                    ->mergeCells('D' . ($RowCounter) . ':D' . ($row - 1))
                                    ->mergeCells('E' . ($RowCounter) . ':E' . ($row - 1))
                                    ->mergeCells('F' . ($RowCounter) . ':F' . ($row - 1))
                                    ->mergeCells('G' . ($RowCounter) . ':G' . ($row - 1))
                                    ->mergeCells('J' . ($RowCounter) . ':J' . ($row - 1))
                                    ->mergeCells('K' . ($RowCounter) . ':K' . ($row - 1))
                                    ->mergeCells('L' . ($RowCounter) . ':L' . ($row - 1))
                                    ->mergeCells('M' . ($RowCounter) . ':M' . ($row - 1))
                                    ->mergeCells('N' . ($RowCounter) . ':N' . ($row - 1));

                                $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date) ? $this->sma->hrld($data_row->date) : '-')
                                    ->SetCellValue('B' . $row, $data_row->reference_no ? $data_row->reference_no : '-')
                                    ->SetCellValue('C' . $row, $data_row->biller ? $data_row->biller : '-')
                                    ->SetCellValue('D' . $row, $data_row->code ? $data_row->code : '-')
                                    ->SetCellValue('E' . $row, $data_row->name ? $data_row->name : '-')
                                    ->SetCellValue('F' . $row, str_replace("IDC-", "", $data_row->cf1) ? str_replace("IDC-", "", $data_row->cf1) : '-')
                                    ->SetCellValue('G' . $row, $data_row->customer ? $data_row->customer : '-')
                                    ->SetCellValue('H' . $row, $data_row->product_name ? $data_row->product_name : '-')
                                    ->SetCellValue('I' . $row, $data_row->quantity ? $data_row->quantity : 0)
                                    ->SetCellValue('J' . $row, $data_row->total_amount ? $data_row->total_amount : 0)
                                    ->SetCellValue('K' . $row, $data_row->paid ? $data_row->paid : 0)
                                    ->SetCellValue('L' . $row, ($data_row->total_amount - $data_row->paid) ? ($data_row->total_amount - $data_row->paid) : 0)
                                    ->SetCellValue('M' . $row, lang($data_row->payment_status) ? lang($data_row->payment_status) : '-')
                                    ->SetCellValue('N' . $row, $data_row->created ? $data_row->created : '-');
                                $merge = false;
                            } else {
                                $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date) ? $this->sma->hrld($data_row->date) : '-')
                                    ->SetCellValue('B' . $row, $data_row->reference_no ? $data_row->reference_no : '-')
                                    ->SetCellValue('C' . $row, $data_row->biller ? $data_row->biller : '-')
                                    ->SetCellValue('D' . $row, $data_row->code ? $data_row->code : '-')
                                    ->SetCellValue('E' . $row, $data_row->name ? $data_row->name : '-')
                                    ->SetCellValue('F' . $row, str_replace("IDC-", "", $data_row->cf1) ? str_replace("IDC-", "", $data_row->cf1) : '-')
                                    ->SetCellValue('G' . $row, $data_row->customer ? $data_row->customer : '-')
                                    ->SetCellValue('H' . $row, $data_row->product_name ? $data_row->product_name : '-')
                                    ->SetCellValue('I' . $row, $data_row->quantity ? $data_row->quantity : 0)
                                    ->SetCellValue('J' . $row, $data_row->total_amount ? $data_row->total_amount : 0)
                                    ->SetCellValue('K' . $row, $data_row->paid ? $data_row->paid : 0)
                                    ->SetCellValue('L' . $row, ($data_row->total_amount - $data_row->paid) ? ($data_row->total_amount - $data_row->paid) : 0)
                                    ->SetCellValue('M' . $row, lang($data_row->payment_status) ? lang($data_row->payment_status) : '-')
                                    ->SetCellValue('N' . $row, $data_row->created ? $data_row->created : '-');
                            }
                            $total      += $data_row->total_amount;
                            $paid       += $data_row->paid;
                            $balance    += ($data_row->total_amount - $data_row->paid);
                            $RowCounter = $row;
                        }
                        $row++;
                    } else {
                        $sheet->SetCellValue('A' . $row, $this->sma->hrld($data_row->date) ? $this->sma->hrld($data_row->date) : '-')
                            ->SetCellValue('B' . $row, $data_row->reference_no ? $data_row->reference_no : '-')
                            ->SetCellValue('C' . $row, $data_row->biller ? $data_row->biller : '-')
                            ->SetCellValue('D' . $row, str_replace("IDC-", "", $data_row->cf1) ? str_replace("IDC-", "", $data_row->cf1) : '-')
                            ->SetCellValue('E' . $row, $data_row->customer ? $data_row->customer : '-')
                            ->SetCellValue('F' . $row, $data_row->product_name ? $data_row->product_name : '-')
                            ->SetCellValue('G' . $row, $data_row->quantity ? $data_row->quantity : 0)
                            ->SetCellValue('H' . $row, $data_row->total_amount ? $data_row->total_amount : 0)
                            ->SetCellValue('I' . $row, $data_row->paid ? $data_row->paid : 0)
                            ->SetCellValue('J' . $row, ($data_row->total_amount - $data_row->paid) ? ($data_row->total_amount - $data_row->paid) : 0)
                            ->SetCellValue('K' . $row, lang($data_row->payment_status) ? lang($data_row->payment_status) : '-');

                        $total   += $data_row->total_amount;
                        $paid    += $data_row->paid;
                        $balance += ($data_row->total_amount - $data_row->paid);
                        $row++;
                    }
                    $index++;
                }
                if ($merge) {
                    $sheet->mergeCells('A' . ($RowCounter) . ':A' . ($row - 1))
                        ->mergeCells('B' . ($RowCounter) . ':B' . ($row - 1))
                        ->mergeCells('C' . ($RowCounter) . ':C' . ($row - 1))
                        ->mergeCells('D' . ($RowCounter) . ':D' . ($row - 1))
                        ->mergeCells('E' . ($RowCounter) . ':E' . ($row - 1))
                        ->mergeCells('F' . ($RowCounter) . ':F' . ($row - 1))
                        ->mergeCells('G' . ($RowCounter) . ':G' . ($row - 1))
                        ->mergeCells('J' . ($RowCounter) . ':J' . ($row - 1))
                        ->mergeCells('K' . ($RowCounter) . ':K' . ($row - 1))
                        ->mergeCells('L' . ($RowCounter) . ':L' . ($row - 1))
                        ->mergeCells('M' . ($RowCounter) . ':M' . ($row - 1))
                        ->mergeCells('N' . ($RowCounter) . ':N' . ($row - 1));
                }
                $sheet->getStyle("J" . $row . ":L" . $row)->getBorders()
                    ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->SetCellValue('J' . $row, $total);
                $sheet->SetCellValue('K' . $row, $paid);
                $sheet->SetCellValue('L' . $row, $balance);

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(30);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(15);
                $sheet->getColumnDimension('K')->setWidth(15);
                $sheet->getColumnDimension('L')->setWidth(15);
                $sheet->getColumnDimension('M')->setWidth(20);
                $sheet->getColumnDimension('N')->setWidth(25);
                $filename = 'sales_report';

                $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
                    $testNum = 0;
                    echo "string" . $testNum;
                    $testNum++;
                    $sheet->getParent()->getDefaultStyle()->applyFromArray($styleArray);
                    echo "string" . $testNum;
                    $testNum++;
                    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                    echo "string" . $testNum;
                    $testNum++;
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "Mpdf" . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "mpdf.php");
                    echo "string" . $testNum;
                    $testNum++;
                    $rendererName = "Mpdf";
                    echo "string" . $testNum;
                    $testNum++;
                    $rendererLibrary = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
                    echo "string" . $testNum;
                    $testNum++;
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererName;
                    echo "string" . $testNum;
                    $testNum++;
                    if (!IOFactory::registerWriter($rendererName, $rendererLibraryPath)) {
                        echo "string" . $testNum . " fail";
                        $testNum++;
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }
                    echo "string" . $testNum . " last";
                    $testNum++;
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Pdf');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $sheet->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->db->save_queries = true;
            $join = "(SELECT customer_id AS customer_id,
                            company_id AS company_id,   
                            date AS date, 
                            reference_no AS reference_no, 
                            biller AS biller, 
                            customer AS customer, 
                            client AS cli, 
                            produk AS produk,
                            total_amount AS total_amount,
                            paid AS paid,
                            balance AS balance,
                            payment_status AS payment_status,
                            idsales AS id_sales,
                            product_id AS product_id,
                            serial_no AS serial_no,
                            biller_id AS biller_id,
                            warehouseid AS warehouse_id
                    FROM ( SELECT customer_id, sma_sales.company_id, date, reference_no, biller, customer, 
                                (CASE WHEN sma_sales.client_id IS NULL THEN 'Forca POS' ELSE sma_sales.client_id END) AS client,
                                        GROUP_CONCAT(CONCAT(sma_products.name, '__', sma_sale_items.quantity) SEPARATOR '___') AS produk, 
                                COALESCE (grand_total, 0) AS total_amount,
                                COALESCE (paid, 0) AS paid,
                                COALESCE (grand_total, 0) - COALESCE (paid, 0)  AS balance,
                                payment_status,
                                sma_sales.id AS idsales,
                                sma_sale_items.product_id AS product_id,
                                sma_sale_items.serial_no AS serial_no,
                                        sma_sales.biller_id AS biller_id,
                                sma_sales.warehouse_id AS warehouseid 
                            FROM sma_sales
                            LEFT JOIN sma_sale_items ON sma_sale_items.sale_id = sma_sales.id
                            LEFT JOIN sma_products ON sma_products.id = sma_sale_items.product_id
                            WHERE sma_sales.client_id != 'aksestoko' OR sma_sales.client_id IS NULL
                            GROUP BY sma_sales.id
                            UNION
                            SELECT cmp.id AS customer_id,
                                cmp.company_id AS company_id,
                                comp.date AS date,
                                comp.reference_no AS reference_no,
                                comp.biller AS biller,
                                comp.customer AS customer,
                                comp.clientID AS client,
                                comp.produk1 AS produk,
                                        comp.total_amount AS total_amount,
                                comp.paid AS paid,
                                comp.balance AS balance,
                                comp.payment_status AS payment_status, 
                                comp.idsales AS idsales,
                                comp.product_id AS product_id,
                                        comp.serial_no AS serial_no,
                                        comp.biller_id AS biller_id,
                                comp.warehouse_id AS warehouseid 
                            FROM sma_companies
                            JOIN ( SELECT sma_companies.id AS customer_id,
                                        sma_sales.date AS date, 
                                        sma_sales.reference_no AS reference_no, 
                                        sma_sales.biller AS biller, 
                                        sma_sales.customer AS customer, 
                                        (CASE WHEN sma_sales.client_id = 'aksestoko' THEN 'AksesToko' ELSE sma_sales.client_id END) AS clientID,
                                        GROUP_CONCAT(CONCAT(sma_products.name, '__', sma_sale_items.quantity) SEPARATOR '___') AS produk1,
                                        COALESCE (grand_total, 0) AS total_amount,
                                        COALESCE (paid, 0) AS paid,
                                        COALESCE (grand_total, 0) - COALESCE (paid, 0) AS balance,
                                        sma_sales.payment_status AS payment_status,
                                        sma_sales.id AS idsales,
                                        sma_sale_items.product_id AS product_id,
                                        sma_sale_items.serial_no AS serial_no,
                                                sma_sales.biller_id AS biller_id,
                                        sma_sales.warehouse_id AS warehouse_id 
                                FROM sma_sales
                                LEFT JOIN sma_sale_items ON sma_sale_items.sale_id = sma_sales.id
                                    LEFT JOIN sma_products ON sma_products.id = sma_sale_items.product_id
                                JOIN sma_users ON sma_users.id = sma_sales.created_by
                                JOIN sma_companies ON sma_users.company_id = sma_companies.id
                                WHERE sma_sales.client_id = 'aksestoko' GROUP BY sma_sales.id) comp ON comp.customer_id = sma_companies.id
                            JOIN sma_companies AS cmp ON cmp.cf1 = sma_companies.cf1 AND cmp.group_name = 'customer') AS x ) company";
            $this->load->library('datatables');
            $this->datatables
                ->select("company.date, 
                          company.reference_no, 
                          company.biller, 
                          company.customer, 
                          company.cli, 
                          company.produk,
                          company.total_amount,
                          company.paid,
                          company.balance,
                          company.payment_status,
                          company.id_sales")
                ->from('sma_companies')
                ->join($join, 'company.customer_id = sma_companies.id');
            if (!$this->Owner && !$this->Principal) {
                $this->datatables->where('company.company_id', $this->session->userdata('company_id'));
            }
            if ($year != null) {
                $this->datatables->where('year(company.date)', $year);
            }
            if ($month != null) {
                $this->datatables->where('month(company.date)', $month);
            }
            if ($user) {
                $this->datatables->where('company.client', $user);
            }
            if ($product) {
                $this->datatables->where('company.product_id', $product, false);
            }
            if ($serial) {
                $this->datatables->like('company.serial_no', $serial, false);
            }
            if ($biller) {
                $this->datatables->where('company.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('company.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('company.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('company.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where('company.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            } else {
                $this->datatables->where("company.date BETWEEN 'year(company.date)-month(company.date)-01' AND NOW() + INTERVAL 1 DAY");
            }
            echo $this->datatables->generate();
        }
    }

    public function warehouses_list()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('warehouse_list')));
        $meta = array('page_title' => lang('warehouse_list'), 'bc' => $bc);
        $this->page_construct('reports/warehouses_list', $meta, $this->data);
    }

    public function getWarehousesList()
    {
        $this->load->library('datatables');
        $this->datatables->select("sma_warehouses.code, sma_warehouses.name, sma_warehouses.address, sma_companies.cf1, sma_companies.company, sma_companies.country, sma_warehouses.id")
            ->from('sma_warehouses')
            ->join('sma_companies', 'sma_companies.id = sma_warehouses.`company_id`')
            ->where('sma_companies.id !=', '1')
            ->where('(sma_companies.client_id IS NULL OR sma_companies.client_id != "aksestoko")')
            ->where('sma_companies.group_name', 'biller');

        $this->datatables->unset_column("sma_warehouses.id");
        echo $this->datatables->generate();
    }

    public function getExportWarehousesList($export_to = null)
    {
        $this->sma->checkPermissions('index');

        $this->db->select("sma_warehouses.code as 'warehouse_code', sma_warehouses.name as 'warehouse_name', sma_warehouses.address as 'warehouse_address', sma_companies.cf1 as 'company_code', sma_companies.company as 'company_name', sma_companies.country as 'company_country', sma_warehouses.id")
            ->from('sma_warehouses')
            ->join('sma_companies', 'sma_companies.id = sma_warehouses.`company_id`')
            ->where('sma_companies.id !=', '1')
            ->where('(sma_companies.client_id IS NULL OR sma_companies.client_id != "aksestoko")')
            ->where('sma_companies.group_name', 'biller');

        $warehouse_list = $this->db->get()->result();

        if ($export_to) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('warehouse_list'))
                ->SetCellValue('A1', lang('warehouse_code'))
                ->SetCellValue('B1', lang('warehouse_name'))
                ->SetCellValue('C1', lang('warehouse_address'))
                ->SetCellValue('D1', lang('distributor_code'))
                ->SetCellValue('E1', lang('distributor_name'))
                ->SetCellValue('F1', lang('distributor_province'));
            $row = 2;
            foreach ($warehouse_list as $warehouse) {
                $sheet->SetCellValue('A' . $row, $warehouse->warehouse_code)
                    ->SetCellValue('B' . $row, $warehouse->warehouse_name)
                    ->SetCellValue('C' . $row, $warehouse->warehouse_address)
                    ->SetCellValue('D' . $row, $warehouse->company_code)
                    ->SetCellValue('E' . $row, $warehouse->company_name)
                    ->SetCellValue('F' . $row, $warehouse->company_country);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);

            $filename = 'warehouse_list_' . date('Y_m_d_H_i_s');
            if ($export_to == 'pdf') {
                $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                $sheet->getDefaultStyle()->applyFromArray($styleArray);
                $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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

            if ($export_to == 'xls') {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');

                $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                ob_end_clean();
                return $objWriter->save('php://output');
            }

            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function customer_response()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->data['categories'] = $this->reports_model->getSurveyCategories();
        $this->data['companies']    = $this->reports_model->getCompaniesName();
        $this->data['respondens']  = $this->reports_model->getRespondenName();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('customer_response')));
        $meta = array('page_title' => lang('customer_response'), 'bc' => $bc);
        $this->page_construct('reports/survey_response', $meta, $this->data);
    }

    public function getCustomerResponse($pdf = null, $xls = null)
    {
        ini_set('memory_limit', '2048M');
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $category       = $this->input->get('category') ? $this->input->get('category') : null;
        $start_date     = ($this->input->get('start_date') && $this->input->get('start_date') != '-') ? $this->input->get('start_date') : null;
        $end_date       = ($this->input->get('end_date') && $this->input->get('end_date') != '-') ? $this->input->get('end_date') : null;
        $company        = $this->input->get('company') ? $this->input->get('company') : null;
        $company_data   = $this->site->getCompanyByID($company);
        $responden      = $this->input->get('responden') ? $this->input->get('responden') : null;
        $responden_data = $this->site->getUser($responden);
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {
            $this->db->select("	sma_feedback_category.id as category_id,
                                sma_feedback_category.category as category,
                                sma_feedback_response.created_at as create_at,
                                sma_users.id as user_id,
                                sma_users.username as username,
                                sma_feedback.company as company,
                                sma_feedback.user_code as user_code,
                                sma_feedback_question.id as question_id,
                                sma_feedback_question.question as question,
                                sma_feedback_response.answer as answer")
                ->from('sma_feedback, sma_feedback_question, sma_feedback_response, sma_users, sma_feedback_category')
                ->where('sma_feedback_category.id = sma_feedback.category_id')
                ->where('sma_feedback_response.survey_id = sma_feedback.id')
                ->where('sma_feedback.user_id = sma_users.id')
                ->where('sma_feedback_response.question_id = sma_feedback_question.id');
            if ($category) {
                $this->db->where("sma_feedback.category_id", $category);
            }
            if ($start_date) {
                $this->db->where('sma_feedback.created_at BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if ($company) {
                $this->db->where('sma_feedback.f_company_id', $company);
                $company = "\n[Filter = " . $company_data->company . "]";
            }
            if ($responden) {
                $this->db->where('sma_feedback.user_id', $responden);
                $responden = "\n[Filter = " . $responden_data->username . "]";
            }
            $this->db->order_by('sma_feedback_category.id asc, sma_feedback_response.id asc');

            $q = $this->db->get();
            // echo($this->db->last_query());die;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $alphas  = range('A', 'Z');
                foreach ($alphas as $a) {
                    foreach ($alphas as $b) {
                        $extra[] = $a . $b;
                    }
                }
                $columnCode = array_merge($alphas, $extra);

                $activeSheet = 0;
                $category = 0;
                $col = 4;
                $create_at = "";
                $row = 2;
                $user_id = 0;
                $question_id = 0;
                $category_id = 0;
                $spreadsheet = new Spreadsheet();
                foreach ($data as $data_row) {
                    if ($data_row->category_id != $category) {
                        if ($activeSheet > 0) {
                            $spreadsheet->createSheet();
                        }
                        $col = 4;
                        $create_at = "";
                        $row = 2;
                        $user_id = 0;
                        $sheet = $spreadsheet->setActiveSheetIndex($activeSheet);
                        $pattern = "/[\/\\\?\*\[\]]/";
                        $string = preg_replace($pattern, '_', $data_row->category);
                        $sheet->setTitle(substr($string, 0, 31))
                            ->setCellValue('A1', lang('date'))
                            ->setCellValue('B1', lang('username') . $responden)
                            ->setCellValue('C1', lang('company') . $company)
                            ->setCellValue('D1', lang('user_code') . $company);
                        $sheet->getStyle('A1:ZZ1')->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('A')->setWidth(20);
                        $sheet->getColumnDimension('B')->setWidth(25);
                        $sheet->getColumnDimension('C')->setWidth(25);
                        $sheet->getColumnDimension('D')->setWidth(25);
                        $category = $data_row->category_id;
                        $activeSheet++;
                    }

                    if ($create_at != $data_row->create_at && $user_id != $data_row->user_id) {
                        $create_at = $data_row->create_at;
                        $user_id = $data_row->user_id;
                        $sheet->setCellValue('A' . ($row + 1), $create_at);
                        $sheet->setCellValue('B' . ($row + 1), $data_row->username);
                        $sheet->setCellValue('C' . ($row + 1), $data_row->company);
                        $sheet->setCellValue('D' . ($row + 1), $data_row->user_code);
                        $col = 4;
                        $row++;
                    }

                    $writing = true;
                    $triger = true;
                    while ($writing) {
                        if ((int)$sheet->getCell($columnCode[$col] . '2')->getValue()) {
                            if (((int)$sheet->getCell($columnCode[$col] . '2')->getValue()) == $data_row->question_id) {
                                $value = $data_row->answer;
                                if($category_id == $data_row->category_id && $data_row->user_id == $user_id){
                                    $value_0 = $sheet->getCell($columnCode[$col] . $row)->getValue();
                                    $value = $value . ", " . $value_0; 
                                }
                                if($category_id != $data_row->category_id && $data_row->user_id == $user_id){
                                    $value_0 = $sheet->getCell($columnCode[$col] . $row)->getValue();
                                    $value = $value . ", " . $value_0; 
                                }
                                $question_id = $data_row->question_id;
                                $category_id = $data_row->category_id;
                                if(substr($value, -2) == ', ') {
                                    $value = substr($value, 0, -2);
                                }
                                $sheet->SetCellValue($columnCode[$col] . $row, $value);
                                $writing = false;
                            } else {
                                if ($triger) {
                                    $col = 3;
                                    $triger = false;
                                }
                                $col++;
                            }
                        } else {
                            $sheet->SetCellValue($columnCode[$col] . '1', $data_row->question);
                            $sheet->SetCellValue($columnCode[$col] . '2', $data_row->question_id);
                            $sheet->SetCellValue($columnCode[$col] . $row, $data_row->answer);
                            $sheet->getColumnDimension($columnCode[$col])->setWidth(50);
                            $writing = false;
                        }
                    }
                }

                $filename = 'customer_response';
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        )
                    );
                    $sheet->getParent()->getDefaultStyle()->applyFromArray($styleArray);
                    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "Mpdf" . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = "Mpdf";
                    $rendererLibrary = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererName;

                    if (!IOFactory::registerWriter($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Pdf');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("sma_feedback.id as id, sma_feedback.created_at, sma_feedback_category.category, sma_feedback.company, sma_users.username")
                ->from("sma_feedback_category, sma_users, sma_feedback")
                ->where("sma_feedback.user_id = sma_users.id")
                ->where("sma_feedback.category_id = sma_feedback_category.id");
            if ($category) {
                $this->datatables->where('sma_feedback_category.id', $category);
            }
            if ($company) {
                $this->datatables->where('sma_feedback.f_company_id', $company);
            }
            if ($responden) {
                $this->datatables->where('sma_feedback.user_id', $responden);
            }
            if ($start_date) {
                $this->datatables->where('sma_feedback.created_at BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('reports/view_response/$1') . "' class='tip' title='" . lang('view_response') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a></div>", "id");
            echo $this->datatables->generate();
        }
    }

    public function view_response($id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['response'] = $this->reports_model->getResponseBy($id);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'reports/view_response', $this->data);
    }

    public function sales_associate()
    {
        // $link_type = ['mb_sales_person','mb_add_sales_person','mb_edit_sales_person'];
        // $this->load->model('db_model');
        // $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        // foreach ($get_link as $val) {
        //     $this->data[$val->type] = $val->uri;
        // }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales_person_report')));
        $meta = array('page_title' => lang('sales_person_report'), 'bc' => $bc);
        $this->page_construct('reports/sales_associate', $meta, $this->data);
    }

    public function getSalesAssociate()
    {
        $this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select("sma_sales_person.id, sma_sales_person.name, sma_sales_person.reference_no, sma_sales_person.phone, sma_sales_person.email, COUNT(sma_users.sales_person_id) AS total_customer")
            ->from("sma_sales_person")
            ->join('sma_users', 'sma_users.sales_person_id = sma_sales_person.id', 'left');
        if (!$this->Owner) {
            $this->datatables->where('sma_sales_person.company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->where('sma_sales_person.is_deleted', null);
        $this->datatables->group_by('sma_sales_person.id');
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("list_customers") . "' href='" . site_url('reports/list_users_sales_associate/$1') . "'><i class=\"fa fa-users\"></i></a></div>", "sma_sales_person.id");

        echo $this->datatables->generate();
    }

    public function list_users_sales_associate($sales_person_id)
    {
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc                     = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('list_customers')));
        $meta                   = array('page_title' => lang('list_customers'), 'bc' => $bc);
        $this->data['id']       = $sales_person_id;
        $this->page_construct('reports/list_users_sales_associate', $meta, $this->data);
    }

    public function getListUsersSalesAssociate()
    {
        $this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select("sma_users.id, sma_users.company, sma_companies.name, sma_users.phone, sma_companies.cf1, sma_users.created_on, sma_users.active")
            ->from('sma_users')
            ->join('sma_companies', 'sma_users.company_id = sma_companies.id', 'left')
            ->where('sma_users.sales_person_id', $this->input->get('sp'));
        $this->datatables->edit_column('sma_users.active', '$1__$2', 'sma_users.active, id');
        echo $this->datatables->generate();
    }

    public function sales_person_report_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {

                if ($this->input->post('form_action') == 'export_excel') {
                    $spreadsheet    = new Spreadsheet();
                    $sheet          = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('Salesperson'))
                        ->SetCellValue('A1', lang('Sales_Person_ID'))
                        ->SetCellValue('B1', lang('name'))
                        ->SetCellValue('C1', lang('reference_no'))
                        ->SetCellValue('D1', lang('phone'))
                        ->SetCellValue('E1', lang('email'))
                        ->SetCellValue('F1', lang('total_customer'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $salePerson = $this->reports_model->getSalesPersonById($id);
                        $sheet->SetCellValue('A' . $row, $salePerson->id)
                            ->SetCellValue('B' . $row, $salePerson->name)
                            ->SetCellValue('C' . $row, $salePerson->reference_no)
                            ->SetCellValue('D' . $row, $salePerson->phone)
                            ->SetCellValue('E' . $row, $salePerson->email)
                            ->SetCellValue('F' . $row, $salePerson->total_customer);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('c')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getColumnDimension('E')->setWidth(20);
                    $sheet->getColumnDimension('F')->setWidth(20);

                    $spreadsheet->createSheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(1);
                    $sheet->setTitle(lang('Users'))
                        ->SetCellValue('A1', lang('company'))
                        ->SetCellValue('B1', lang('name'))
                        ->SetCellValue('C1', lang('phone'))
                        ->SetCellValue('D1', lang('customer_code'))
                        ->SetCellValue('E1', lang('registered'))
                        ->SetCellValue('F1', lang('status'))
                        ->SetCellValue('G1', lang('Sales_Person_ID'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $ListUsersalePerson = $this->reports_model->getListUsersSalesPersonById($id);
                        foreach ($ListUsersalePerson as $row_users) {
                            $status = $row_users->active == 1 ? 'Active' : 'Inactive';
                            $sheet->SetCellValue('A' . $row, $row_users->company)
                                ->SetCellValue('B' . $row, $row_users->name)
                                ->SetCellValue('C' . $row, $row_users->phone)
                                ->SetCellValue('D' . $row, $row_users->cf1)
                                ->SetCellValue('E' . $row, date("Y/m/d H:i:s", $row_users->created_on))
                                ->SetCellValue('F' . $row, $status)
                                ->SetCellValue('G' . $row, $row_users->sales_person_id);
                            $row++;
                        }
                    }
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('c')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getColumnDimension('E')->setWidth(20);
                    $sheet->getColumnDimension('F')->setWidth(20);
                    $sheet->getColumnDimension('G')->setWidth(20);


                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'Salesperson Report ' . date('Y m d-H:i:s');

                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');

                    $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    ob_end_clean();
                    return $objWriter->save('php://output');

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_sp_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
}

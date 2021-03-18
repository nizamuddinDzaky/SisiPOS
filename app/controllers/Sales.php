<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Sales extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        // $this->insertLogActivities();
        //        if ($this->Supplier) {
        //            $this->session->set_flashdata('warning', lang('access_denied'));
        //            redirect($_SERVER["HTTP_REFERER"]);
        //        }
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $this->load->model('audittrail_model', 'audittrail');
        $this->lang->load('customers', $this->Settings->user_language);
        $this->lang->load('sales', $this->Settings->user_language);
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('sales_model');
        $this->load->model('purchases_model');
        $this->load->model('authorized_model');
        $this->load->model('integration_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null)
    {
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $link_type = ['mb_sales', 'mb_edit_sale', 'mb_import_csv_sales', 'mb_export_excel_sales'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales')));
        $meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->page_construct('sales/index', $meta, $this->data);
    }

    public function getSales($year, $month, $warehouse_id = null)
    {
        //$this->sma->checkPermissions('index');

        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        // $detail_link = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <!-- <li>' . $duplicate_link . '</li> -->
        <li>' . $payments_link . '</li>
        <li>' . $add_payment_link . '</li>
        <li>' . $add_delivery_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $pdf_link . '</li>
        <li>' . $email_link . '</li>
        <li>' . $return_link . '</li>

        </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($this->Supplier) {
            $this->datatables
                ->select($this->db->dbprefix('purchases') . ".id, DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, {$this->db->dbprefix('purchases')}.reference_no, {$this->db->dbprefix('purchases')}.supplier, CONCAT(first_name,' ',last_name) as customer, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
                ->from("purchases")
                ->join('users', 'users.id=purchases.created_by', 'left')
                ->where('purchases.supplier_id', $this->session->userdata('company_id'));
        } else {
            $items = "( SELECT CASE
            WHEN
            SUM( unit_quantity ) > SUM( sent_quantity ) 
            AND SUM( sent_quantity ) = 0 THEN
            'pending' 
            WHEN 
            SUM( unit_quantity ) > SUM( sent_quantity ) 
            AND SUM( sent_quantity ) > 0 THEN
            'partial' 
            WHEN 
            SUM( unit_quantity ) = SUM( sent_quantity ) 
            AND SUM( sent_quantity ) > 0 THEN
            'done' 
            END AS delivery_status,
            sale_id 
            FROM
            sma_sale_items 
            GROUP BY
            sma_sale_items.sale_id ) sma_item ";
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, {$this->db->dbprefix('sales')}.reference_no, sma_sales.customer as customer, IF({$this->db->dbprefix('sales')}.client_id = 'aksestoko', CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, ' (AksesToko)'), CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name)) AS created_by, {$this->db->dbprefix('sales')}.sale_status, {$this->db->dbprefix('sales')}.grand_total, {$this->db->dbprefix('sales')}.paid, ({$this->db->dbprefix('sales')}.grand_total-{$this->db->dbprefix('sales')}.paid) as balance, {$this->db->dbprefix('sales')}.payment_status, delivery_status, {$this->db->dbprefix('sales')}.attachment, {$this->db->dbprefix('sales')}.return_id")
                ->from('sales');
            $this->datatables->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left');
            $this->datatables->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left');

            // $this->db->set_dbprefix('');

            $this->datatables->join($items, 'item.sale_id = sma_sales.id', 'left');
            if ($warehouse_id) {
                $this->datatables->where('sma_sales.warehouse_id', $warehouse_id);
            }
            $this->datatables->where('sma_sales.biller_id', $this->session->userdata('company_id'));
            $this->datatables->where('sma_sales.pos !=', 1);
        }
        if ($this->Admin) {
            $this->datatables->where('sma_sales.company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->where('month(sma_sales.date)', $month);
        $this->datatables->where('year(sma_sales.date)', $year);
        $this->datatables->where('sma_sales.is_deleted is null');
        $this->datatables->where('sma_sales.sale_type is null');
        $this->datatables->group_by('sma_sale_items.sale_id');
        //        $this->datatables
        //            ->join('warehouses','warehouses.id=sales.warehouse_id','left')
        //            ->where('sales.pos !=', 1)
        //            ->where('month(date)',$month)
        //            ->where('warehouses.company_id',$this->session->userdata('company_id')); // ->where('sale_status !=', 'returned');
        //        $this->datatables->where('sale_status', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('sma_sales.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('sma_sales.customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function list_booking_sales($warehouse_id = null)
    {
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('list_booking_sales')));
        $meta = array('page_title' => lang('list_booking_sales'), 'bc' => $bc);
        $this->page_construct('sales/list_booking_sales', $meta, $this->data);
    }



    public function getCountPendingSales()
    {
        echo $this->sales_model->getCountPendingSales();
    }

    public function modal_view($id = null)
    {
        // //$this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->sma->transactionPermissions('sales', $id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inv = $this->sales_model->getInvoiceByID($id);
        $gettop = $this->sales_model->getTOP();
        $top = '';
        if ($gettop) {
            foreach ($gettop as $k => $v) {
                if ($inv->payment_term == $v->duration) {
                    $top = $v->description;
                }
            }
            if ($top == '') {
                $top = $inv->payment_term . ' days';
            }
        }
        $inv->top = $top;

        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['po'] = $this->sales_model->getPurchasesByRefNo($inv->reference_no, $inv->biller_id);
        $this->data['atl_order'] = $this->sales_model->getOrderAtlBySaleId($inv->id);
        $this->data['atl_kreditpro_status'] = $this->sales_model->getAtlKreditproStatus($this->data['atl_order']->orderid);
        $this->load->view($this->theme . 'sales/modal_view', $this->data);
    }
    public function modal_view_print($id = null)
    {
        // //$this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->sma->transactionPermissions('sales', $id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['po'] = $this->sales_model->getPurchasesByRefNo($inv->reference_no, $inv->biller_id);
        $this->data['promo'] = $this->site->findPromoByPurchaseId($this->data['po']->id);
        $this->load->view($this->theme . 'print/sales', $this->data);
    }

    public function view($id = null)
    {
        // //$this->sma->checkPermissions('index');
        $this->sales_model->cek_sales($id, 'sales_booking/view/');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('sales', $id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['sale_type'] = $inv->sale_type;
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal'] = $this->sales_model->getPaypalSettings();
        $this->data['skrill'] = $this->sales_model->getSkrillSettings();
        $this->data['po'] = $this->sales_model->getPurchasesByRefNo($inv->reference_no, $inv->biller_id);


        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_sales_details'), 'bc' => $bc);
        $this->page_construct('sales/view', $meta, $this->data);
    }

    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        // //$this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('sales', $id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);



        if ($inv->sale_type != 'booking') {
            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
            $this->data['po'] = $this->sales_model->getPurchasesByRefNo($inv->reference_no, $inv->biller_id);
            //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
            //$this->data['skrill'] = $this->sales_model->getSkrillSettings();
        } else {

            if ($inv->sale_status != 'closed') {
                $this->session->set_flashdata('error', lang("close_can_not_pdf"));
                $this->sma->md();
            }
            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
            $this->data['po'] = $this->sales_model->getPurchasesByRefNo($inv->reference_no, $inv->biller_id);
            $this->data['promo'] = $this->site->findPromoByPurchaseId($this->data['po']->id);
            //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
            //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        }
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $name = lang("sale") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'sales/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
    }

    public function combine_pdf($sales_id)
    {
        // //$this->sma->checkPermissions('pdf');

        foreach ($sales_id as $id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
            $html_data = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = array(
                'content' => $html_data,
                'footer' => $this->data['biller']->invoice_footer,
            );
        }

        $name = lang("sales") . ".pdf";
        $this->sma->generate_pdf($html, $name);
    }

    public function email($id = null)
    {
        // //$this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('sales', $id);
        $inv = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('to', lang("to") . " " . lang("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', lang("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            //            $paypal = $this->sales_model->getPaypalSettings();
            //            $skrill = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            //            if ($paypal->active == "1" && $inv->grand_total != "0.00") {
            //                if (trim(strtolower($customer->country)) == $biller->country) {
            //                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
            //                } else {
            //                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
            //                }
            //                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . site_url('sales/view/' . $inv->id) . '&cancel_return=' . site_url('sales/view/' . $inv->id) . '&notify_url=' . site_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
            //
            //            }
            //            if ($skrill->active == "1" && $inv->grand_total != "0.00") {
            //                if (trim(strtolower($customer->country)) == $biller->country) {
            //                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
            //                } else {
            //                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
            //                }
            //                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . site_url('sales/view/' . $inv->id) . '&cancel_url=' . site_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . site_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            //            }

            $btn_code .= '<div class="clearfix"></div>
            </div>';
            $message = $message . $btn_code;

            $attachment = base_url() . $this->pdf($id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        //        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
        if ($this->form_validation->run() == true && $this->sma->email_trap($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->session->set_flashdata('message', lang("email_sent"));
            redirect("sales");
        } else {
            if (file_exists('./themes/' . $this->theme . '/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/views/email_templates/sale.html');
            }

            $this->data['subject'] = array(
                'name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('invoice') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array(
                'name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $sale_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/email', $this->data);
        }
    }

    /* ------------------------------------------------------------------ */

    public function add($quote_id = null)
    {
        $this->session->set_flashdata('error', lang('add sale can not be used anymore'));
        redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');

        $this->db->trans_begin();
        try {
            // //$this->sma->checkPermissions();
            $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;

            // check returned sale cannot be duplicate
            $inv = $this->sales_model->getInvoiceByID($sale_id);
            if ($inv->sale_status == 'returned') {
                throw new Exception(lang('sale_x_action'));
            }
            if ($inv->sale_status == 'closed') {
                throw new Exception(lang('close_cant_duplicate'));
            }
            // end

            $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('biller', lang("biller"), 'required');
            $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
            $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

            if ($this->form_validation->run() == true) {
                $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $warehouse_id = $this->input->post('warehouse');
                $customer_id = $this->input->post('customer');
                $biller_id = $this->input->post('biller');
                $total_items = $this->input->post('total_items');
                $sale_status = $this->input->post('sale_status');
                $payment_status = $this->input->post('payment_status');
                $payment_term = $this->input->post('payment_term');
                $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
                $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                $customer_details = $this->site->getCompanyByID($customer_id);
                $customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
                $biller_details = $this->site->getCompanyByID($biller_id);
                $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                $note = $this->sma->clear_tags($this->input->post('note'));
                $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
                $quote_id = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
                $sale_type = !empty($this->input->post('sale_type')) ? 'booking' : null;
                $uuid_sales = $this->input->post('uuid');

                if ($uuid = $this->site->isUuidExist($uuid_sales, 'sales')) {
                    throw new Exception("UUID $uuid is exist.");
                }

                $total = 0;
                $product_tax = 0;
                $order_tax = 0;
                $product_discount = 0;
                $order_discount = 0;
                $percentage = '%';
                $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
                for ($r = 0; $r < $i; $r++) {
                    $item_id = $_POST['product_id'][$r];
                    $item_type = $_POST['product_type'][$r];
                    $item_code = $_POST['product_code'][$r];
                    $item_name = $_POST['product_name'][$r];
                    $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                    $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                    $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                    $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_unit = $_POST['product_unit'][$r];
                    $item_quantity = $_POST['product_base_quantity'][$r];
                    $flag_consignment = $_POST['consignment'][$r] ? $_POST['consignment'][$r] : null;

                    if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                        $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                        // $unit_price = $real_unit_price;
                        $pr_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount);
                            }
                        }

                        $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                        $item_net_price = $unit_price;
                        $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount += $pr_item_discount;
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $item_tax = 0;
                        $tax = "";

                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $pr_tax = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_price = $unit_price - $item_tax;
                                }
                            } elseif ($tax_details->type == 2) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_price = $unit_price - $item_tax;
                                }

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        }

                        $product_tax += $pr_item_tax;
                        $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                        $unit = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_id' => $item_id,
                            'product_code' => $item_code,
                            'product_name' => $item_name,
                            'product_type' => $item_type,
                            'option_id' => $item_option,
                            'net_unit_price' => $item_net_price,
                            'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                            'quantity' => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $unit ? $unit->code : null,
                            'unit_quantity' => $item_unit_quantity,
                            'warehouse_id' => $warehouse_id,
                            'item_tax' => $pr_item_tax,
                            'tax_rate_id' => $pr_tax,
                            'tax' => $tax,
                            'discount' => $item_discount,
                            'item_discount' => $pr_item_discount,
                            'subtotal' => $this->sma->formatDecimal($subtotal),
                            'serial_no' => $item_serial,
                            'real_unit_price' => $real_unit_price,
                            'flag' => $flag_consignment,
                        );

                        $booking[] = array(
                            'product_id' => $item_id,
                            'warehouse_id' => $warehouse_id,
                            'product_code' => $item_code,
                            'product_name' => $item_name,
                            'product_type' => $item_type,
                            'quantity_order' => $item_quantity,
                            'quantity_booking' => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $unit ? $unit->code : null,
                            'client_id' => null,
                            'created_at' => date('Y-m-d H:i:s'),
                        );

                        $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    }
                }

                if (empty($products)) {
                    $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } else {
                    krsort($products);
                }

                if ($this->input->post('order_discount')) {
                    $order_discount_id = $this->input->post('order_discount');
                    $opos = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                    } else {
                        $order_discount = $this->sma->formatDecimal($order_discount_id);
                    }
                } else {
                    $order_discount_id = null;
                }
                $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

                if ($this->Settings->tax2) {
                    $order_tax_id = $this->input->post('order_tax');
                    if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                        if ($order_tax_details->type == 2) {
                            $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                        } elseif ($order_tax_details->type == 1) {
                            $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                        }
                    }
                } else {
                    $order_tax_id = null;
                }

                $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
                $data = array(
                    'date' => $date,
                    'reference_no' => $reference,
                    'customer_id' => $customer_id,
                    'customer' => $customer,
                    'biller_id' => $biller_id,
                    'biller' => $biller,
                    'warehouse_id' => $warehouse_id,
                    'note' => $note,
                    'staff_note' => $staff_note,
                    'total' => $total,
                    'product_discount' => $product_discount,
                    'order_discount_id' => $order_discount_id,
                    'order_discount' => $order_discount,
                    'total_discount' => $total_discount,
                    'product_tax' => $product_tax,
                    'order_tax_id' => $order_tax_id,
                    'order_tax' => $order_tax,
                    'total_tax' => $total_tax,
                    'shipping' => $this->sma->formatDecimal($shipping),
                    'grand_total' => $grand_total,
                    'total_items' => $total_items,
                    'sale_status' => $sale_status,
                    'payment_status' => $payment_status,
                    'payment_term' => $payment_term,
                    'due_date' => $due_date,
                    'paid' => 0,
                    'created_by' => $this->session->userdata('user_id'),
                    'company_id' => $this->session->userdata('company_id'),
                    'sale_type' => $sale_type,
                    'uuid' => $uuid_sales,
                );

                if ($payment_status == 'partial' || $payment_status == 'paid') {
                    if ($this->input->post('paid_by') == 'deposit') {
                        if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                            throw new Exception(lang("amount_greater_than_deposit"));
                        }
                    }
                    if ($this->input->post('paid_by') == 'gift_card') {
                        $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                        $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                        $gc_balance = $gc->balance - $amount_paying;
                        $payment = array(
                            'date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount' => $this->sma->formatDecimal($amount_paying),
                            'paid_by' => $this->input->post('paid_by'),
                            'cheque_no' => $this->input->post('cheque_no'),
                            'cc_no' => $this->input->post('gift_card_no'),
                            'cc_holder' => $this->input->post('pcc_holder'),
                            'cc_month' => $this->input->post('pcc_month'),
                            'cc_year' => $this->input->post('pcc_year'),
                            'cc_type' => $this->input->post('pcc_type'),
                            'created_by' => $this->session->userdata('user_id'),
                            'note' => $this->input->post('payment_note'),
                            'type' => 'received',
                            'gc_balance' => $gc_balance,
                        );
                    } else {
                        $payment = array(
                            'date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount' => $this->sma->formatDecimal($this->input->post('amount-paid')),
                            'paid_by' => $this->input->post('paid_by'),
                            'cheque_no' => $this->input->post('cheque_no'),
                            'cc_no' => $this->input->post('pcc_no'),
                            'cc_holder' => $this->input->post('pcc_holder'),
                            'cc_month' => $this->input->post('pcc_month'),
                            'cc_year' => $this->input->post('pcc_year'),
                            'cc_type' => $this->input->post('pcc_type'),
                            'created_by' => $this->session->userdata('user_id'),
                            'note' => $this->input->post('payment_note'),
                            'type' => 'received',
                        );
                    }
                } else {
                    $payment = array();
                }

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }

                // $this->sma->print_arrays($data, $products, $payment);
            }

            //        echo json_encode($data);echo json_encode($products);echo json_encode($payment);die();
            $addsale = $this->sales_model->addSale($data, $products, $payment, null, $booking);

            if ($this->form_validation->run() == true && $addsale) {
                $this->db->trans_commit();
                $this->session->set_userdata('remove_slls', 1);
                if ($quote_id) {
                    $this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
                }
                $this->session->set_flashdata('message', lang("sale_added"));
                redirect("sales");
            } else {

                //nge cek apakah jumlah Sales Order telah limit
                $isLimited = $this->authorized_model->isOrderLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_trx"));
                    $message = str_replace("yyy", lang("sales"), $message);
                    throw new Exception($message);
                }
                // akhir cek

                if ($quote_id || $sale_id) {
                    if ($quote_id) {
                        $this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
                        $items = $this->sales_model->getAllQuoteItems($quote_id);
                    } elseif ($sale_id) {
                        $this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
                        $items = $this->sales_model->getAllInvoiceItems($sale_id);
                    }
                    krsort($items);
                    $c = rand(100000, 9999999);
                    $x = 0;
                    foreach ($items as $item) {
                        $row = $this->site->getProductByID($item->product_id);
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->tax_method = 0;
                        } else {
                            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        }
                        $row->quantity = 0;
                        $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $row->quantity += $pi->quantity_balance;
                            }
                        }
                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->type = $item->product_type;
                        $row->qty = $item->quantity;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                        $row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                        $row->real_unit_price = $item->real_unit_price;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->serial = '';
                        $row->option = $item->option_id;
                        $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                        if ($options) {
                            $option_quantity = 0;
                            foreach ($options as $option) {
                                $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                                if ($pis) {
                                    foreach ($pis as $pi) {
                                        $option_quantity += $pi->quantity_balance;
                                    }
                                }
                                if ($option->quantity > $option_quantity) {
                                    $option->quantity = $option_quantity;
                                }
                            }
                        }
                        $combo_items = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                        }
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array(
                            'id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options
                        );
                        $id_rand_temp[$x] = array('trx_id' => $c, 'product_id' => $row->id);
                        $x++;
                        $c++;
                    }
                    $this->data['quote_items'] = json_encode($pr);
                    $this->data['rand_id'] = json_encode($id_rand_temp);
                }

                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['quote_id'] = $quote_id ? $quote_id : $sale_id;
                $this->data['billers'] = $this->site->getAllCompanies('biller');
                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
                $this->data['slnumber'] = ''; //$this->site->getReference('so');
                $this->data['payment_ref'] = ''; //$this->site->getReference('pay');

                $link_type = ['mb_add_sale'];
                $this->load->model('db_model');
                $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
                foreach ($get_link as $val) {
                    $this->data[$val->type] = $val->uri;
                }

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale')));
                $meta = array('page_title' => lang('add_sale'), 'bc' => $bc);
                $this->page_construct('sales/add', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }



    /* ------------------------------------------------------------------------ */

    public function edit($id = null)
    {
        $this->sales_model->cek_sales($id, 'sales_booking/edit_booking_sale/');

        $this->db->trans_begin();
        try {
            // //$this->sma->checkPermissions();
            $this->load->helper('security');
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('sales', $id);
            $inv = $this->sales_model->getInvoiceByID($id);
            $deliv = $this->sales_model->getDeliveryBySaleID($id);
            if ($inv->sale_status == 'closed') {
                throw new Exception(lang('close cant edit'));
            }
            if ($deliv != false) {
                throw new Exception(lang('delivery_available'));
            }
            if ($inv->sale_status == 'returned' || $inv->sale_status == 'canceled' || $inv->return_id || $inv->return_sale_ref) {
                throw new Exception(lang('sale_x_action'));
            }
            if (!$this->session->userdata('edit_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            // $inv = 0;
            if (empty($inv)) {
                throw new Exception(lang('sale_not_found'));
            }
            $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
            $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('biller', lang("biller"), 'required');
            $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
            $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');
            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            $this->data['po'] = $this->sales_model->getPurchasesByRefNo($this->data['inv']->reference_no, $this->data['inv']->biller_id);
            $this->data['user'] = $this->site->getUser($this->data['inv']->created_by);
            if ($this->form_validation->run() == true) {
                $reference = $this->input->post('reference_no');
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = $inv->date;
                }
                // if ($this->data['po']->payment_method == 'cash before delivery' && $this->data['inv']->paid < $this->data['inv']->grand_total && $this->input->post('sale_status') == 'completed') {
                //     $this->session->set_flashdata('error', 'payment has not been paid yet');
                //     redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
                // }

                if (($this->data['inv']->payment_status == 'waiting' || $this->data['inv']->paid > 0) && $this->input->post('sale_status') == 'canceled') {
                    throw new Exception('waiting payment or partial/full paid');
                }
                $warehouse_id = $this->input->post('warehouse');
                $customer_id = $this->input->post('customer');
                $biller_id = $this->input->post('biller');
                $total_items = $this->input->post('total_items');
                $sale_status = $this->input->post('sale_status');
                $payment_status = $this->input->post('payment_status');
                $payment_term = $this->input->post('payment_term');
                $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
                $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                $charge = $this->input->post('charge') ? $this->input->post('charge') : 0;
                $reason = $this->input->post('reason') ? $this->sma->clear_tags($this->input->post('reason')) : "";
                $customer_details = $this->site->getCompanyByID($customer_id);
                $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
                $biller_details = $this->site->getCompanyByID($biller_id);
                $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                $note = $this->sma->clear_tags($this->input->post('note'));
                $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
                $sale_type = !empty($this->input->post('sale_type')) ? 'booking' : null;

                $total = 0;
                $product_tax = 0;
                $order_tax = 0;
                $product_discount = 0;
                $order_discount = 0;
                $percentage = '%';
                $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
                for ($r = 0; $r < $i; $r++) {
                    $item_id = $_POST['product_id'][$r];
                    $item_type = $_POST['product_type'][$r];
                    $item_code = $_POST['product_code'][$r];
                    $item_name = $_POST['product_name'][$r];
                    $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                    $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                    $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                    $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_unit = $_POST['product_unit'][$r];
                    $item_quantity = $_POST['product_base_quantity'][$r];

                    if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                        $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                        // $unit_price = $real_unit_price;
                        $pr_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount);
                            }
                        }

                        $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                        $item_net_price = $unit_price;
                        $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount += $pr_item_discount;
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $item_tax = 0;
                        $tax = "";

                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $pr_tax = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_price = $unit_price - $item_tax;
                                }
                            } elseif ($tax_details->type == 2) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_price = $unit_price - $item_tax;
                                }

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        }

                        $product_tax += $pr_item_tax;
                        $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                        $unit = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_id' => $item_id,
                            'product_code' => $item_code,
                            'product_name' => $item_name,
                            'product_type' => $item_type,
                            'option_id' => $item_option,
                            'net_unit_price' => $item_net_price,
                            'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                            'quantity' => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity' => $item_unit_quantity,
                            'warehouse_id' => $warehouse_id,
                            'item_tax' => $pr_item_tax,
                            'tax_rate_id' => $pr_tax,
                            'tax' => $tax,
                            'discount' => $item_discount,
                            'item_discount' => $pr_item_discount,
                            'subtotal' => $this->sma->formatDecimal($subtotal),
                            'serial_no' => $item_serial,
                            'real_unit_price' => $real_unit_price,
                        );

                        $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    }
                }
                if (empty($products)) {
                    $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } else {
                    krsort($products);
                }
                if ($this->input->post('order_discount')) {
                    $order_discount_id = $this->input->post('order_discount');
                    $opos = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                    } else {
                        $order_discount = $this->sma->formatDecimal($order_discount_id);
                    }
                } else {
                    $order_discount_id = null;
                }
                $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

                if ($this->Settings->tax2) {
                    $order_tax_id = $this->input->post('order_tax');
                    if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                        if ($order_tax_details->type == 2) {
                            $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                        }
                        if ($order_tax_details->type == 1) {
                            $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                        }
                    }
                } else {
                    $order_tax_id = null;
                }

                $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount + $charge), 4);
                $data = array(
                    'date' => $date,
                    'reference_no' => $reference,
                    'customer_id' => $customer_id,
                    'customer' => $customer,
                    'biller_id' => $biller_id,
                    'biller' => $biller,
                    'warehouse_id' => $warehouse_id,
                    'note' => $note,
                    'staff_note' => $staff_note,
                    'total' => $total,
                    'product_discount' => $product_discount,
                    'order_discount_id' => $order_discount_id,
                    'order_discount' => $order_discount,
                    'total_discount' => $total_discount,
                    'product_tax' => $product_tax,
                    'order_tax_id' => $order_tax_id,
                    'order_tax' => $order_tax,
                    'total_tax' => $total_tax,
                    'shipping' => $this->sma->formatDecimal($shipping),
                    'grand_total' => $grand_total,
                    'total_items' => $total_items,
                    'payment_status' => $payment_status,
                    'payment_term' => $payment_term,
                    'due_date' => $due_date,
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'charge' => $charge,
                    'reason' => $reason,
                    'sale_type' => $sale_type
                );

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }
                // $this->sma->print_arrays($data, $products);
            }
            // print_r($this->input->post());die;

            if ($this->form_validation->run() == true) {
                $updatesale = $this->sales_model->updateSale($sale_status, $id, $data, $products, $booking);
                if (!$this->audittrail->insertDistributorChangePrice($data['customer_id'], $this->session->userdata('user_id'), $this->session->userdata('company_id'), $id)) {
                    throw new \Exception("Tidak dapat menyimpan rekam jejak audit distributor_change_price");
                }
                if (!$updatesale) {
                    throw new Exception("update sale failed");
                }

                //START - Mengirim SMS notifikasi kepada retail toko AksesToko
                $message = $this->site->makeMessage('sms_notif_change_price', [
                    'sale_ref' => $this->data['inv']->reference_no,
                    'old_price' => number_format($this->sma->formatDecimal($this->data['inv']->grand_total), 0, ',', '.'),
                    'new_price' => number_format($this->sma->formatDecimal($grand_total), 0, ',', '.'),
                ]);
                if (SMS_NOTIF) {
                    $message_sms = '';
                    if ($this->data['user']->phone_is_verified == 1 && $charge != $this->sma->formatDecimal($inv->charge)) {
                        $status_sms = false;
                        $status_sms = $this->site->send_sms_otp((string) $this->data['user']->phone, $message, false, 'notif');
                        $message_sms = '|| sending sms notification failed';
                        if ($status_sms) {
                            $message_sms = '|| sending sms notification success';
                        }
                    }
                }
                //END - Mengirim SMS notifikasi kepada retail toko AksesToko

                //START - Mengirim WA notifikasi kepada retail toko AksesToko
                if (WA_NOTIF) {
                    $message_sms = '';
                    if ($this->data['user']->phone_is_verified == 1 && $charge != $this->sma->formatDecimal($inv->charge)) {
                        $status_sms = false;
                        $status_sms = $this->site->send_wa_otp_wablas((string) $this->data['user']->phone, $message);
                        $message_sms = '|| sending wa notification failed';
                        if ($status_sms) {
                            $message_sms = '|| sending wa notification success';
                        }
                    }
                }
                //END - Mengirim WA notifikasi kepada retail toko AksesToko

                //START - Mengirim notifikasi kepada AksesToko Mobile
                if ($inv->client_id == 'aksestoko' && $charge != $this->sma->formatDecimal($inv->charge)) {
                    $notification   = [
                        'title' => 'AksesToko - Perubahan Harga',
                        'body'  => $message
                    ];
                    $data = [
                        'click_action'   => 'FLUTTER_NOTIFICATION_CLICK',
                        'title'          => 'AksesToko - Perubahan Harga',
                        'body'           => $message,
                        'type'           => 'sms_notif_change_price',
                        'id_pemesanan'   => $this->data['po']->id,
                        'id_sales'       => $id,
                        'reference_no'   => $reference,
                        'tanggal'        => date('d/m/Y'),
                    ];
                    $notifikasi_atmobiel = $this->integration_model->notification_atmobile($notification, $data, $this->data['user']->id);

                    if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                        $tipe          = 'warning';
                        $message_notif = "sending notification to aksestoko mobile failed " . $notifikasi_atmobiel->results[0]->error;
                    } else {
                        $tipe          = 'message';
                        $message_notif = "sending notification to aksestoko mobile success.";
                    }
                }
                //END - Mengirim notifikasi kepada AksesToko Mobile

                $this->db->trans_commit();
                $this->session->set_userdata('remove_slls', 1);
                $this->session->set_flashdata('message', lang('sale_updated') . ' ' . @$message_sms);
                $this->session->set_flashdata($tipe, lang('sale_updated') . ' ' . @$message_notif);
                redirect($inv->pos ? 'pos/sales' : 'sales');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                if ($this->Settings->disable_editing) {
                    if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                        throw new Exception(sprintf(lang("sale_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    }
                }
                $inv_items = $this->sales_model->getAllInvoiceItems($id);
                krsort($inv_items);
                $c = rand(100000, 9999999);

                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row = json_decode('{}');
                        $row->tax_method = 0;
                        $row->quantity = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->id = $item->product_id;
                    $row->code = $item->product_code;
                    $row->name = $item->product_name;
                    $row->type = $item->product_type;
                    $row->base_quantity = $item->quantity;
                    $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                    $row->unit = $item->product_unit_id;
                    $row->qty = $item->unit_quantity;
                    $row->quantity += $item->quantity;
                    $row->discount = $item->discount ? $item->discount : '0';
                    $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                    $row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate = $item->tax_rate_id;
                    $row->serial = $item->serial_no;
                    $row->option = $item->option_id;
                    $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);

                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            $option_quantity += $item->quantity;
                            if ($option->quantity > $option_quantity) {
                                $option->quantity = $option_quantity;
                            }
                        }
                    }
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                        $te = $combo_items;
                        foreach ($combo_items as $combo_item) {
                            $combo_item->quantity = $combo_item->qty * $item->quantity;
                        }
                    }
                    $units = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri = $this->Settings->item_addition ? $row->id : $c;
                    $pr[$ri] = array(
                        'id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                        'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'client_id' => $inv->client_id
                    );
                    $c++;
                }

                $this->data['inv_items'] = json_encode($pr);
                $this->data['id'] = $id;
                //$this->data['currencies'] = $this->site->getAllCurrencies();
                $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                $this->data['warehouses'] = $this->site->getAllWarehouses();

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('edit_sale')));
                $meta = array('page_title' => lang('edit_sale'), 'bc' => $bc);
                $this->page_construct('sales/edit', $meta, $this->data);
            }
        } catch (Exception $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* ------------------------------- */

    public function return_sale($id = null)
    {
        // //$this->sma->checkPermissions('return_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('sales', $id);
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->return_id) {
            $this->session->set_flashdata('error', lang("sale_already_returned"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($sale->client_id == 'aksestoko') {
            $this->session->set_flashdata('error', lang("sale_cannot_be_returned"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $sale_item_id = $_POST['sale_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0 - $_POST['product_base_quantity'][$r]);


                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount, 4);
                        }
                    }

                    $unit_price = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity, 4);
                    $product_discount += $pr_item_discount;
                    $pr_tax = 0;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_price * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $sale->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'serial_no' => $item_serial,
                        'real_unit_price' => $real_unit_price,
                        'sale_item_id' => $sale_item_id,
                    );

                    $si_return[] = array(
                        'id' => $sale_item_id,
                        'sale_id' => $id,
                        'product_id' => $item_id,
                        'option_id' => $item_option,
                        'quantity' => (0 - $item_quantity),
                        'warehouse_id' => $sale->warehouse_id,
                    );

                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id, 4);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal($product_tax + $order_tax, 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) - $order_discount), 4);
            $data = array(
                'date' => $date,
                'sale_id' => $id,
                'reference_no' => $sale->reference_no,
                'customer_id' => $sale->customer_id,
                'customer' => $sale->customer,
                'biller_id' => $sale->biller_id,
                'biller' => $sale->biller,
                'warehouse_id' => $sale->warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->sma->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_sale_ref' => $reference,
                'sale_status' => 'returned',
                'payment_status' => $sale->payment_status == 'paid' ? 'due' : 'pending',
                'company_id' => $this->session->userdata('company_id'),
            );
            if ($this->input->post('amount-paid') && $this->input->post('amount-paid') > 0) {
                $pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
                $payment = array(
                    'date' => $date,
                    'reference_no' => $pay_ref,
                    'amount' => (0 - $this->input->post('amount-paid')),
                    'paid_by' => $this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'returned',
                    'company_id' => $this->session->userdata('company_id'),
                );
                $data['payment_status'] = $grand_total == $this->input->post('amount-paid') ? 'paid' : 'partial';
            } else {
                $payment = array();
            }

            if ($_FILES['document']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                $photo              = $uploadedImg->url;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products, $si_return, $payment);
        }
        //        echo json_encode($_POST);die();
        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, $si_return)) {
            $this->session->set_flashdata('message', lang("return_sale_added"));
            redirect("sales");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $sale;
            if ($this->data['inv']->sale_status != 'completed') {
                $this->session->set_flashdata('error', lang("sale_status_x_competed"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
                $this->session->set_flashdata('error', lang("sale_x_edited_older_than_3_months"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->id = $item->product_id;
                $row->sale_item_id = $item->id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->quantity;
                $row->oqty = $item->quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->serial = $item->serial_no;
                $row->option = $item->option_id;
                $options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options);
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['payment_ref'] = '';
            $this->data['reference'] = ''; // $this->site->getReference('re');
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('return_sale')));
            $meta = array('page_title' => lang('return_sale'), 'bc' => $bc);
            $this->page_construct('sales/return_sale', $meta, $this->data);
        }
    }

    public function close_update_sale($sale, $sale_items)
    {

        $shipping           = $sale->shipping;
        $total              = 0;
        $product_tax        = 0;
        $order_tax          = 0;
        $product_discount   = 0;
        $order_discount     = 0;
        $percentage         = '%';

        foreach ($sale_items as $item) {
            $item_id              = $item->product_id;
            $item_type            = $item->product_type;
            $item_code            = $item->product_code;
            $item_name            = $item->product_name;
            $item_option          = $item->option_id;
            $item_unit_quantity   = $item->unit_quantity;

            $item_quantity        = $item->sent_quantity;
            $item_send_quantity   = $item->sent_quantity;
            $warehouse_id         = $item->warehouse_id;
            $item_serial          = $item->serial_no;
            $item_tax_rate        = $item->tax_rate_id;
            $item_discount        = $item->discount ? $item->discount : '0';
            $real_unit_price      = $item->discount != 0 ? $item->real_unit_price : $item->net_unit_price;
            $unit_price           = $item->discount != 0 ? $item->real_unit_price : $item->unit_price;
            $flag                 = $item->flag;
            $tax_rate             = $this->site->getTaxRateByID($item_tax_rate);

            if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                // $unit_price = $real_unit_price;
                $pr_discount = 0;

                if (isset($item_discount)) {
                    $discount   = $item_discount;
                    $dpos       = strpos($discount, $percentage);
                    if ($dpos !== false) {
                        $pds = explode("%", $discount);
                        $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (float) ($pds[0])) / 100), 4);
                    } else {
                        $pr_discount = $this->sma->formatDecimal($discount);
                    }
                }

                $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                $item_net_price   = $unit_price;
                $pr_item_discount = $this->sma->formatDecimal($pr_discount * ($item_quantity != $item_unit_quantity ? $item_quantity : $item_unit_quantity));
                $product_discount += $pr_item_discount;
                $pr_tax           = 0;
                $pr_item_tax      = 0;
                $item_tax         = 0;
                $tax              = "";

                if (isset($item_tax_rate) && $item_tax_rate != 0) {
                    $pr_tax         = $item_tax_rate;
                    $tax_details    = $this->site->getTaxRateByID($pr_tax);
                    if ($tax_details->type == 1 && $tax_details->rate != 0) {
                        if ($product_details && $product_details->tax_method == 1) {
                            $item_tax   = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                            $tax        = $tax_details->rate . "%";
                        } else {
                            $item_tax         = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                            $tax              = $tax_details->rate . "%";
                            $item_net_price   = $unit_price - $item_tax;
                        }
                    } elseif ($tax_details->type == 2) {
                        if ($product_details && $product_details->tax_method == 1) {
                            $item_tax   = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                            $tax        = $tax_details->rate . "%";
                        } else {
                            $item_tax         = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                            $tax              = $tax_details->rate . "%";
                            $item_net_price   = $unit_price - $item_tax;
                        }

                        $item_tax   = $this->sma->formatDecimal($tax_details->rate);
                        $tax        = $tax_details->rate;
                    }
                    $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                }

                $product_tax += $pr_item_tax;
                $subtotal = (($item_net_price * $item_quantity) + $pr_item_tax);
                $items[] = array(
                    'sale_item_id'  => $item->id,
                    'product_code'  => $item_code,
                    'quantity'      => $item_quantity,
                    'unit_quantity' => $item_unit_quantity,
                    'sent_quantity' => $item_send_quantity,
                    'item_discount' => $pr_item_discount,
                    'subtotal'      => $this->sma->formatDecimal($subtotal),
                );
                $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
            }
        }


        if ($sale->order_discount_id) {
            $order_discount_id    = $sale->order_discount_id;
            $opos                 = strpos($order_discount_id, $percentage);
            if ($opos !== false) {
                $ods = explode("%", $order_discount_id);
                $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
            } else {
                $order_discount = $this->sma->formatDecimal($order_discount_id);
            }
        } else {
            $order_discount_id = null;
        }
        $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);
        if ($this->Settings->tax2) {
            $order_tax_id = $this->input->post('order_tax');
            if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                if ($order_tax_details->type == 2) {
                    $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                } elseif ($order_tax_details->type == 1) {
                    $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                }
            }
        } else {
            $order_tax_id = null;
        }

        if ((int) $total == 0) {
            $total_discount       = 0;
            $order_discount       = 0;
            $order_discount_id    = 0;
        }

        $total_tax    = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
        $grand_total  = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
        $data = array(
            'total'             => $total,
            'grand_total'       => $grand_total,
            'order_discount_id' => $order_discount_id,
            'total_discount'    => $total_discount,
            'order_discount'    => $order_discount,
            'sale_status'       => 'closed',
        );
        return $this->sales_model->updateSaleandPurchase($sale, $data, $items);
    }

    public function close_sale($id = null)
    {
        $this->db->trans_begin();
        try {
            // //$this->sma->checkPermissions('close_sale');
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('sales', $id);
            $sale                 = $this->sales_model->getInvoiceByID($id);
            $sale_items           = $this->sales_model->getSaleItemsBySaleId($sale->id);
            $getDeliveryToClose   = $this->sales_model->getDeliveryToClose($id);
            $purchase             = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->biller_id);
            if (count($getDeliveryToClose) > 0) {
                $str_delivering   = '';
                $str_approve      = '';
                $str_confirm      = '';
                $str_close        = [];
                $str_received     = [];
                foreach ($getDeliveryToClose as $v) {
                    if ($v->receive_status == 'received') {
                        $str_received[] = $v->receive_status;
                    }
                    if ($v->is_approval == null && $v->is_reject == null && $v->is_confirm == null && (int) $v->bad > 0) {
                        $str_approve .= $v->do_reference_no . ', ';
                    } elseif ($v->is_reject == 1 && is_null($v->is_confirm)) {
                        $str_confirm .= $v->do_reference_no . ', ';
                    } elseif ($v->is_reject == 2 && $v->is_confirm == 1 && $v->is_approval != 1) {
                        $str_approve .= $v->do_reference_no . ', ';
                    } elseif ($v->status == 'packing' || $v->status == 'delivering') {
                        $str_delivering .= $v->do_reference_no . ', ';
                    } else {
                        $str_close[] = 1;
                    }
                }

                $str = '';

                if ($str_confirm != '')
                    $str .= lang("sale_is_delivering_packing") . ' = ' . substr($str_confirm, 0, strlen($str_confirm) - 2) . '<br>';
                if ($str_approve != '')
                    $str .= lang("sale_need_approval") . ' = ' . substr($str_approve, 0, strlen($str_approve) - 2) . '<br>';
                if ($str_delivering != '')
                    $str .= lang("sale_is_delivering_packing") . ' = ' . substr($str_delivering, 0, strlen($str_delivering) - 2) . '<br>';

                if (array_sum($str_close) != count($getDeliveryToClose) || count($getDeliveryToClose) != count($str_received)) {
                    throw new Exception($str);
                }
            }


            if (@$purchase->payment_method == 'kredit_pro' && !(@$purchase->payment_status == 'reject' || @$purchase->payment_status == 'pending')) {
                throw new \Exception(lang("close_error_kredit_pro"));
            } else if ($sale->sale_status == 'closed') {
                throw new \Exception(lang("cannot_close_again"));
            } else if ($sale->sale_status != 'reserved') {
                throw new \Exception(lang("close_must_reserved"));
            } else if ($sale->payment_status == 'waiting') {
                throw new \Exception(lang("close_not_waiting"));
            }
            // redirect("sales/list_booking_sales");
            $payment_status   = $sale->payment_status;
            $shipping         = $sale->shipping;
            $total            = 0;
            $product_tax      = 0;
            $order_tax        = 0;
            $product_discount = 0;
            $order_discount   = 0;
            $percentage       = '%';

            foreach ($sale_items as $item) {
                $item_id            = $item->product_id;
                $item_type          = $item->product_type;
                $item_code          = $item->product_code;
                $item_name          = $item->product_name;
                $item_option        = $item->option_id;
                $item_unit_quantity = $item->unit_quantity;
                $item_quantity      = $item->quantity - $item->sent_quantity;
                $item_send_quantity = $item->sent_quantity;
                $warehouse_id       = $item->warehouse_id;
                $item_serial        = $item->serial_no;
                $item_tax_rate      = $item->tax_rate_id;
                $item_discount      = $item->discount ? $item->discount : '0';
                $real_unit_price    = $item->discount != 0 ? $item->real_unit_price : $item->net_unit_price;
                $unit_price         = $item->discount != 0 ? $item->real_unit_price : $item->unit_price;
                $flag               = $item->flag;
                $tax_rate           = $this->site->getTaxRateByID($item_tax_rate);
                // var_dump($real_unit_price, $unit_price, $item->discount);die;
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details    = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount        = 0;

                    if (isset($item_discount)) {
                        $discount   = $item_discount;
                        $dpos       = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds            = explode("%", $discount);
                            $pr_discount    = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax           = 0;
                    $pr_item_tax      = 0;
                    $item_tax         = 0;
                    $tax              = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax   = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax        = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax   = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax        = $tax_details->rate . "%";
                            } else {
                                $item_tax         = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax              = $tax_details->rate . "%";
                                $item_net_price   = $unit_price - $item_tax;
                            }
                            $item_tax   = $this->sma->formatDecimal($tax_details->rate);
                            $tax        = $tax_details->rate;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }

                    $product_tax    += $pr_item_tax;
                    $subtotal       = (($item_net_price * $item_quantity) + $pr_item_tax);

                    $products[] = array(
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_unit_quantity,
                        'sent_quantity'     => $item_send_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $pr_tax,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                        'flag'              => $flag,
                    );
                    $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
                }
            }


            if ($sale->order_discount_id) {
                $order_discount_id = $sale->order_discount_id;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    } elseif ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array(
                'sale_id'           => $sale->id,
                'date'              => date('Y-m-d H:i:s'),
                'company_id'        => $sale->company_id,
                'customer_id'       => $sale->customer_id,
                'customer'          => $sale->customer,
                'biller_id'         => $sale->biller_id,
                'biller'            => $sale->biller,
                'warehouse_id'      => $sale->warehouse_id,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $order_discount_id,
                'total_discount'    => $total_discount,
                'order_discount'    => $order_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $order_tax_id,
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $sale->shipping,
                'grand_total'       => $grand_total,
                'paid'              => $sale->paid,
            );
            if ($this->sales_model->closeSale($id, $data, $products)) {
                if (!$this->close_update_sale($sale, $sale_items)) {
                    throw new Exception(lang("error"));
                }

                if ($sale->client_id == 'atl') {
                    $this->load->model('Integration_atl_model', 'integration_atl');
                    $call_update_atl = $this->integration_atl->update_order_atl($sale->id);
                    if (!$call_update_atl) {
                        throw new \Exception(lang('failed') . " -> Call API Update Order ATL");
                    }
                }

                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("close_successful"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                throw new \Exception(lang("sale_cannot_close"));
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }



    /* ------------------------------- */

    public function delete($id = null)
    {
        $this->db->trans_begin();
        try {
            // //$this->sma->checkPermissions(null, true);

            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('sales', $id);
            $inv = $this->sales_model->getInvoiceByID($id);
            if ($inv->sale_status == 'returned') {
                throw new Exception(lang('sale_x_action'));
            }

            if ($this->sales_model->deleteSale($id)) {
                $this->db->trans_begin();
                if ($this->input->is_ajax_request()) {
                    echo lang("sale_deleted");
                    die();
                }
                $this->session->set_flashdata('message', lang('sale_deleted'));
                redirect('welcome');
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_return($id = null)
    {
        $this->db->trans_begin();
        try {
            // //$this->sma->checkPermissions(null, true);

            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('sales', $id);
            if ($this->sales_model->deleteReturn($id)) {
                $this->db->trans_begin();
                if ($this->input->is_ajax_request()) {
                    echo lang("return_sale_deleted");
                    die();
                }
                $this->session->set_flashdata('message', lang('return_sale_deleted'));
                redirect('welcome');
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function sale_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    // //$this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteSale($id);
                    }
                    $this->session->set_flashdata('message', lang("sales_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('sales'))
                        ->SetCellValue('A1', lang('date'))
                        ->SetCellValue('B1', lang('reference_no'))
                        ->SetCellValue('C1', lang('biller'))
                        ->SetCellValue('D1', lang('customer_code'))
                        ->SetCellValue('E1', lang('customer'))
                        ->SetCellValue('F1', lang('grand_total'))
                        ->SetCellValue('G1', lang('paid'))
                        ->SetCellValue('H1', lang('payment_status'))
                        ->SetCellValue('I1', lang('return_sale_ref'))
                        ->SetCellValue('J1', lang('created_by'));

                    $spreadsheet->createSheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(1);
                    $sheet->setTitle(lang('sale_items'))
                        ->SetCellValue('A1', lang('date'))
                        ->SetCellValue('B1', lang('reference_no'))
                        ->SetCellValue('C1', lang('product_code'))
                        ->SetCellValue('D1', lang('product_name'))
                        ->SetCellValue('E1', lang('quantity'))
                        ->SetCellValue('F1', lang('unit_price'))
                        ->SetCellValue('G1', lang('tax'))
                        ->SetCellValue('H1', lang('warehouse'))
                        ->SetCellValue('I1', lang('subtotal'));

                    $row = 2;
                    $row_item = 2;
                    foreach ($_POST['val'] as $id) {
                        $sheet = $spreadsheet->setActiveSheetIndex(0);
                        $sale = $this->sales_model->getInvoiceByID($id);
                        $company = $this->site->getCompanyByID($sale->customer_id);
                        $user = $this->site->getUser($sale->created_by);
                        $created_by = $user->first_name . ' ' . $user->last_name;
                        if ($sale->client_id == 'aksestoko') {
                            $created_by .= ' (AksesToko)';
                        }
                        $created_by .= ' [' . $user->username . ']';
                        $sheet->SetCellValue('A' . $row, $this->sma->hrld($sale->date))
                            ->SetCellValue('B' . $row, $sale->reference_no)
                            ->SetCellValue('C' . $row, $sale->biller)
                            ->SetCellValue('D' . $row, str_replace("IDC-", "", $company->cf1))
                            ->SetCellValue('E' . $row, $sale->customer)
                            ->SetCellValue('F' . $row, $sale->grand_total)
                            ->SetCellValue('G' . $row, lang($sale->paid))
                            ->SetCellValue('H' . $row, lang($sale->payment_status))
                            ->SetCellValue('I' . $row, $sale->return_sale_ref)
                            ->SetCellValue('J' . $row, $created_by);

                        $sheet = $spreadsheet->setActiveSheetIndex(1);
                        $items = $this->site->getAllSaleItems($id);
                        foreach ($items as $item) {
                            $warehouse = $this->site->getWarehouseByID($item->warehouse_id);
                            $sheet->SetCellValue('A' . $row_item, $this->sma->hrld($sale->date))
                                ->SetCellValue('B' . $row_item, $sale->reference_no)
                                ->SetCellValue('C' . $row_item, '"' . $item->product_code . '"')
                                ->SetCellValue('D' . $row_item, $item->product_name)
                                ->SetCellValue('E' . $row_item, floatVal($item->quantity))
                                ->SetCellValue('F' . $row_item, floatVal($item->unit_price))
                                ->SetCellValue('G' . $row_item, floatVal($item->item_tax))
                                ->SetCellValue('H' . $row_item, $warehouse->name)
                                ->SetCellValue('I' . $row_item, $item->subtotal);
                            $row_item++;
                        }
                        $row++;
                    }
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'sales_' . date('Y_m_d_H_i_s');
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
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* ------------------------------- */

    public function deliveries()
    {
        // //$this->sma->checkPermissions();

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('deliveries')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->page_construct('sales/deliveries', $meta, $this->data);
    }

    public function getDeliveries()
    {
        // //$this->sma->checkPermissions('deliveries');

        $detail_link = anchor('sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $email_link = anchor('sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $edit_link = anchor('sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $pdf_link = anchor('sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_delivery/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_delivery') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
    <li>' . $detail_link . '</li>
    <li>' . $edit_link . '</li>
    <li>' . $pdf_link . '</li>
    </ul>
    </div></div>';
        // <li>' . $delete_link . '</li>

        $this->load->library('datatables');
        //GROUP_CONCAT(CONCAT('Name: ', sale_items.product_name, ' Qty: ', sale_items.quantity ) SEPARATOR '<br>')
        $this->datatables
            ->select("deliveries.id as id, deliveries.date, deliveries.do_reference_no, deliveries.sale_reference_no, deliveries.customer, deliveries.address, deliveries.status, deliveries.attachment")
            ->from('deliveries')
            ->join('sales',  "sales.id = deliveries.sale_id", 'left')
            ->where('sales.sale_type', null)
            ->where('deliveries.is_deleted', null);

        if ($this->input->get('sale_id')) {
            $this->datatables->where('deliveries.sale_id=' . $this->input->get('sale_id'));
        }
        //  ->group_by('deliveries.id');
        if (!$this->Admin && !$this->Owner) {
            $this->datatables->where('deliveries.created_by', $this->session->userdata('user_id'));
        }
        if ($this->Admin) {
            $this->datatables->where('sales.company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }

    public function pdf_delivery($id = null, $view = null, $save_bufffer = null)
    {
        // //$this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        // $this->sma->transactionPermissions('deliveries', $id);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);

        $this->data['delivery'] = $deli;
        $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows'] = $this->sales_model->getDeliveryItemsByDeliveryId($deli->id);
        $this->data['user'] = $this->site->getUser($deli->created_by);

        $name = lang("delivery") . "_" . str_replace('/', '_', $deli->do_reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_delivery', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    public function view_delivery($id = null)
    {
        // //$this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $qGet = $this->db->get_where('deliveries', array('id' => $id), 1);
        $this->sma->transactionPermissions('sales', $qGet->row()->sale_id);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);

        $new_view = ['aksestoko', 'new_delivery'];
        $this->data['new_view'] = $new_view;

        if ($deli->status == "returned") {
            $this->data['delivery_items'] = $this->sales_model->getReturnItemsByDeliveryId($deli->id);
            $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            if (!$sale) {
                $this->session->set_flashdata('error', lang('sale_not_found'));
                $this->sma->md();
            }
            $this->data['shipping'] = $sale->shipping;
            $this->data['delivery'] = $deli;
            $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
            $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
            $this->data['user'] = $this->site->getUser($deli->created_by);
            $this->data['page_title'] = lang("return_delivery"); //need lang
        } else {
            $returnDelivery = $this->sales_model->getReturnDeliveryByRef($deli->do_reference_no, $deli->sale_id);

            if ($returnDelivery) {
                $this->data['returned_items'] = $this->sales_model->getReturnItemsByDeliveryId($returnDelivery->id);
                $deli->return_reference_no = $returnDelivery->do_reference_no;
            }
            if (in_array($deli->client_id, $new_view)) {
                $this->data['delivery_items'] = $this->sales_model->getDeliveryItemsByDeliveryId($id);
            }
            $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            if (!$sale) {
                $this->session->set_flashdata('error', lang('sale_not_found'));
                $this->sma->md();
            }
            $this->data['shipping'] = $sale->shipping;
            $this->data['delivery'] = $deli;
            $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
            $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
            $this->data['user'] = $this->site->getUser($deli->created_by);
            $this->data['page_title'] = lang("delivery_order");
            $this->data['sale'] = $sale;
        }

        $this->load->view($this->theme . 'sales/view_delivery', $this->data);
    }

    public function view_delivery_print($id = null)
    {
        ////$this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $qGet = $this->db->get_where('deliveries', array('id' => $id), 1);
        $this->sma->transactionPermissions('sales', $qGet->row()->sale_id);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);
        $new_view = ['aksestoko', 'new_delivery'];
        $this->data['new_view'] = $new_view;
        if ($deli->status == "returned") {
            $this->data['delivery_items'] = $this->sales_model->getReturnItemsByDeliveryId($deli->id);
            $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            if (!$sale) {
                $this->session->set_flashdata('error', lang('sale_not_found'));
                $this->sma->md();
            }
            $this->data['shipping'] = $sale->shipping;
            $this->data['delivery'] = $deli;
            $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
            $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
            $this->data['user'] = $this->site->getUser($deli->created_by);
            $this->data['page_title'] = lang("return_delivery"); //need lang
        } else {
            $returnDelivery = $this->sales_model->getReturnDeliveryByRef($deli->do_reference_no, $deli->sale_id);

            if ($returnDelivery) {
                $this->data['returned_items'] = $this->sales_model->getReturnItemsByDeliveryId($returnDelivery->id);
                $deli->return_reference_no = $returnDelivery->do_reference_no;
            }
            if (in_array($deli->client_id, $new_view)) {
                $this->data['delivery_items'] = $this->sales_model->getDeliveryItemsByDeliveryId($id);
            }
            $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            if (!$sale) {
                $this->session->set_flashdata('error', lang('sale_not_found'));
                $this->sma->md();
            }
            $this->data['shipping'] = $sale->shipping;
            $this->data['delivery'] = $deli;
            $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
            $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
            $this->data['user'] = $this->site->getUser($deli->created_by);
            $this->data['page_title'] = lang("delivery_order");
            $this->data['sale'] = $sale;
        }

        $this->load->view($this->theme . 'print/deliver', $this->data);
    }

    public function add_delivery($id = null)
    {
        $this->db->trans_begin();
        try {
            //$this->sma->checkPermissions();
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('sales', $id);
            $sale         = $this->sales_model->getInvoiceByID($id);
            $data_sales   = $this->sales_model->getSaleItemsBySaleId($id);

            $this->data['user']   = $this->site->getUser($sale->created_by);
            $purchase             = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->biller_id);
            if ($sale->sale_type == 'booking') {
                if ($sale->sale_status == 'closed') {
                    throw new Exception(lang('close_cant_delivery'));
                }
                if ($sale->sale_status != 'reserved') {
                    throw new Exception(lang('status_is_x_reserved'));
                }
            } else {
                if ($sale->sale_status != 'completed') {
                    throw new Exception(lang('status_is_x_completed'));
                }
            }
            if ($sale->payment_status != 'paid' && $purchase->payment_method == 'cash before delivery') {
                throw new Exception(lang('paid_yet'));
            }
            if ($purchase->payment_method == 'kredit_pro' && $purchase->payment_status != 'accept') {
                throw new Exception(lang('payment_not_been_approved'));
            }

            $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('address', lang("address"), 'required');

            if ($this->form_validation->run() == true) {
                $sale_items_id = $this->input->post('sale_items_id');
                $sent_quantity = $this->input->post('sent_quantity');
                if (array_sum($sent_quantity) == 0) {
                    throw new Exception(lang('At_least'));
                }
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $dlDetails = array(
                    'date'              => $date,
                    'sale_id'           => $this->input->post('sale_id'),
                    'do_reference_no'   => $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do'),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'customer'          => $this->input->post('customer'),
                    'address'           => $this->input->post('address'),
                    'status'            => $this->input->post('status'),
                    'delivered_by'      => $this->input->post('delivered_by'),
                    'received_by'       => $this->input->post('received_by'),
                    'note'              => $this->sma->clear_tags($this->input->post('note')),
                    'created_by'        => $this->session->userdata('user_id'),
                    'client_id'         => $sale->client_id == "aksestoko" ? $sale->client_id : "new_delivery",
                    'uuid'              => $this->input->post('uuid'),
                );

                if ($uuid = $this->site->isUuidExist($dlDetails['uuid'], 'deliveries')) {
                    throw new Exception("UUID $uuid is exist.");
                }

                if ($this->input->post('status') == 'delivering') {
                    $dlDetails['delivering_date'] = date('Y-m-d H:i:s');
                } elseif ($this->input->post('status') == 'delivered') {
                    $dlDetails['delivering_date']   = date('Y-m-d H:i:s');
                    $dlDetails['delivered_date']    = date('Y-m-d H:i:s');
                }

                $shipping_cost = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']    = $this->digital_upload_path;
                    $config['allowed_types']  = $this->digital_file_types;
                    $config['max_size']       = $this->allowed_file_size;
                    $config['overwrite']      = false;
                    $config['encrypt_name']   = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new Exception($error);
                    }
                    $photo                      = $this->upload->file_name;*/
                    $uploadedImg                = $this->integration_model->upload_files($_FILES['document']);
                    $photo                      = $uploadedImg->url;
                    $dlDetails['attachment']    = $photo;
                }
                if ($sale->sale_type == 'booking') {
                    foreach ($data_sales as $index => $value) {
                        $unsend_quantity = $value->unit_quantity - $value->sent_quantity;
                        if ($sent_quantity[$index] > $unsend_quantity) {
                            throw new Exception($value->product_name . " (" . $value->product_code . ") " . lang('no_remaining_unsend_quantity'));
                        }

                        $real_stock = $this->sales_model->getWarehouseProduct($value->warehouse_id, $value->product_id);
                        if (!$real_stock) {
                            throw new Exception(lang('kuantitas_not_found'));
                        }
                        if ($real_stock->quantity < $sent_quantity[$index]) {
                            throw new Exception($value->product_name . " (" . $value->product_code . ") " . lang('out of stock'));
                        }
                    }
                }
                $delivery_id = $this->sales_model->addDelivery($dlDetails, $shipping_cost, $sale_items_id, $sent_quantity);
                if ($delivery_id) {
                    if (!$this->audittrail->insertDistributorCreateDelivery($this->session->userdata('user_id'), $sale->customer_id, $this->session->userdata('company_id'), $dlDetails['sale_id'], $delivery_id)) {
                        throw new Exception(lang('not_save_audit'));
                    }
                    if (in_array($dlDetails['status'], ['delivered', 'delivering'])) {
                        $_type        = "Pengiriman";
                        $notify_type  = "delivering_delivery";
                        $message_type = "sms_notif_delivery";
                        $message      = $this->site->makeMessage('sms_notif_delivery', [
                            'sale_ref'   => $sale->reference_no,
                            'total_item' => array_sum($sent_quantity),
                        ]);
                    } else if (in_array($dlDetails['status'], ['packing'])) {
                        $_type        = "Dikemas";
                        $notify_type  = "packing_delivery";
                        $message_type = "sms_notif_delivery_packing";
                        $message      = $this->site->makeMessage('sms_notif_delivery_packing', [
                            'sale_ref'   => $sale->reference_no,
                            'total_item' => array_sum($sent_quantity)
                        ]);
                    }
                    $this->load->model('socket_notification_model');
                    if ($notify_type) {
                        $data_socket_notification = [
                            'company_id'        => $purchase->company_id,
                            'transaction_id'    => 'SALE-' . $purchase->id . '-' . $delivery_id,
                            'customer_name'     => '',
                            'reference_no'      => $purchase->cf1,
                            'price'             => '',
                            'type'              => $notify_type,
                            'to'                => 'aksestoko',
                            'note'              => '',
                            'created_at'        => date('Y-m-d H:i:s')
                        ];
                        $this->socket_notification_model->addNotification($data_socket_notification);
                    }
                    //START - Mengirim SMS notifikasi kepada retail toko AksesToko
                    if (SMS_NOTIF) {
                        $message_sms = '';
                        if ($this->data['user']->phone_is_verified == 1 && ($this->input->post('status') == 'delivering' || $this->input->post('status') == 'delivered')) {
                            $status_sms = false;
                            $status_sms = $this->site->send_sms_otp((string) $this->data['user']->phone, $message, false, 'notif');
                            $message_sms = '|| sending sms notification failed';
                            if ($status_sms) {
                                $message_sms = '|| sending sms notification success';
                            }
                        }
                    }
                    //END - Mengirim SMS notifikasi kepada retail toko AksesToko
                    //START - Mengirim WA notifikasi kepada retail toko AksesToko
                    if (WA_NOTIF) {
                        $message_sms = '';
                        if ($this->data['user']->phone_is_verified == 1 && ($this->input->post('status') == 'delivering' || $this->input->post('status') == 'delivered')) {
                            $status_sms = false;
                            $status_sms = $this->site->send_wa_otp_wablas((string) $this->data['user']->phone, $message);
                            $message_sms = '|| sending wa notification failed';
                            if ($status_sms) {
                                $message_sms = '|| sending wa notification success';
                            }
                        }
                    }
                    //END - Mengirim WA notifikasi kepada retail toko AksesToko
                    //START - Mengirim notifikasi kepada AksesToko Mobile
                    $notification   = [
                        'title' => 'AksesToko - ' . $_type,
                        'body'  => $message
                    ];
                    $data = [
                        'click_action'   => 'FLUTTER_NOTIFICATION_CLICK',
                        'title'          => 'AksesToko - ' . $_type,
                        'body'           => $message,
                        'type'           => $message_type,
                        'id_pemesanan'   => $$purchase->id,
                        'id_sales'       => $id,
                        'reference_no'   => $sale->reference_no,
                        'do_ref'         => $this->input->post('do_reference_no'),
                        'id_pengiriman'  => $delivery_id,
                        'tanggal'        => date('d/m/Y'),
                    ];
                    $notifikasi_atmobiel = $this->integration_model->notification_atmobile($notification, $data, $this->data['user']->id);

                    if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                        $tipe          = 'warning';
                        $message_notif = "sending notification to aksestoko mobile failed " . $notifikasi_atmobiel->results[0]->error;
                    } else {
                        $tipe          = 'message';
                        $message_notif = "sending notification to aksestoko mobile success.";
                    }
                    //END - Mengirim notifikasi kepada AksesToko Mobile

                    if ($sale->client_id == 'atl') {
                        if (in_array($dlDetails['status'], ['delivered', 'delivering'])) {
                            $this->load->model('Integration_atl_model', 'integration_atl');
                            $call_insert_delivery_atl = $this->integration_atl->insert_delivery_atl($delivery_id);
                            if (!$call_insert_delivery_atl) {
                                throw new \Exception(lang('failed') . " -> Call API Insert Delivery ATL");
                            }

                            $data_deliveries = ['atl_doid' => $call_insert_delivery_atl->doid];
                            $this->sales_model->update_deliveries($delivery_id, $data_deliveries);
                        }
                    }
                    $this->db->trans_commit();

                    $this->session->set_userdata('remove_slls', 1);
                    $this->session->set_flashdata('message', lang('delivery_added') . ' ' . @$message_sms);
                    $this->session->set_flashdata($tipe, lang('delivery_added') . ' ' . @$message_notif);


                    /* Start-CekDuplicateNoRef - Fungsi ini sengaja diluar transaction, karena ada case tersendiri.*/
                    $new_delivery = $this->sales_model->getDeliveryByID($delivery_id);
                    if (!$this->sales_model->checkDupplicateNoDeliveryRef($new_delivery, $sale)) {
                        $this->session->set_flashdata('message', lang('delivery_added') . ' ' . $message_sms . ' | check dupplicate DO failed');
                    };
                    /* End-CekDuplicateNoRef */
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } elseif ($this->input->post('add_delivery')) {
                throw new Exception(validation_errors());
            } else {
                $data_customer = $this->site->getCompanyByID($sale->customer_id);
                $data_user = $this->site->getUser();
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['customer'] = $data_customer;
                $this->data['user'] = $data_user;
                $this->data['inv'] = $sale;
                $this->data['order_atl'] = $this->sales_model->getOrderAtlBySaleId($sale->id);
                $this->data['do_reference_no'] = ''; //$this->site->getReference('do');
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['sale_items'] = $this->sales_model->getSaleItemsBySaleId($sale->id);
                $this->data['sale_type'] = $sale->sale_type;
                $this->load->view($this->theme . 'sales/add_delivery', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            $this->sma->md();
        }
    }

    public function edit_delivery($id = null)
    {
        //$this->sma->checkPermissions();

        $this->db->trans_begin();
        try {
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $qGet = $this->sales_model->getDeliveryByID($id);
            if ($qGet->status == "returned" || $qGet->status == "delivered") {
                throw new Exception(lang('cant_edit'));
            }
            $sales = $this->sales_model->getSalesById($qGet->sale_id);
            $data_sales = $this->sales_model->getSaleItemsBySaleId($qGet->sale_id);
            $this->sma->transactionPermissions('sales', $qGet->sale_id);
            //        $this->sma->transactionPermissions('deliveries',$id);
            $this->form_validation->set_rules('do_reference_no', lang("do_reference_no"), 'required');
            $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('address', lang("address"), 'required');

            if ($this->form_validation->run() == true) {

                $dlDetails = array(
                    'sale_id' => $this->input->post('sale_id'),
                    'do_reference_no' => $this->input->post('do_reference_no'),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'customer' => $this->input->post('customer'),
                    'address' => $this->input->post('address'),
                    'status' => $this->input->post('status'),
                    'delivered_by' => $this->input->post('delivered_by'),
                    'received_by' => $this->input->post('received_by'),
                    'note' => $this->sma->clear_tags($this->input->post('note')),
                    'updated_by' => $this->session->userdata('user_id'),
                );

                if ($this->input->post('status') == 'delivering') {
                    $dlDetails['delivering_date'] = date('Y-m-d H:i:s');
                } elseif ($this->input->post('status') == 'delivered') {
                    if (!$qGet->delivering_date) {
                        $dlDetails['delivering_date'] = date('Y-m-d H:i:s');
                    }
                    $dlDetails['delivered_date'] = date('Y-m-d H:i:s');
                }

                $sent_quantity = $this->input->post('sent_quantity');
                $deliv_item_id = $this->input->post('delivery_items_id');
                if ($sales->sale_type == 'booking') {
                    foreach ($sent_quantity as $i => $sq) {
                        $delivery_item = $this->sales_model->getDeliveryItemByDeliveryItemId($deliv_item_id[$i]);
                        $real_stock = $this->sales_model->getWarehouseProduct($sales->warehouse_id, $delivery_item->product_id);
                        $current_stock = $real_stock->quantity; // + $delivery_item->quantity_sent;
                        if ((float) $current_stock < (float) $sq) {
                            throw new Exception($delivery_item->product_name . " (" . $delivery_item->product_code . ") " . lang('out of stock'));
                        }
                    }
                }
                $deliveryItems = [
                    'delivery_items_id' => $this->input->post('delivery_items_id'),
                    'sent_quantity'     => $sent_quantity
                ];

                $shipping_cost = $this->input->post('shipping') ? $this->input->post('shipping') : 0;

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg            = $this->integration_model->upload_files($_FILES['document']);
                    $photo                  = $uploadedImg->url;
                    $dlDetails['attachment'] = $photo;
                }

                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                    $dlDetails['date'] = $date;
                }
            } elseif ($this->input->post('edit_delivery')) {
                throw new Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $this->sales_model->updateDelivery($id, $dlDetails, $shipping_cost, $deliveryItems)) {
                // start -- trigger penerimaan barang pada aksestoko ketika delivery diubah statusnya menjadi delivered
                $purchase_data = $this->sales_model->getPurchasesByRefNo($this->input->post('sale_reference_no'), $this->session->userdata('company_id'));
                if ($this->input->post('status') == 'delivered' && $purchase_data) {
                    $deliveryItem = $this->sales_model->getDeliveryItemsByDeliveryId($id);
                    $arrDeliveryItemId = [];
                    $product_code = [];
                    $quantity_received = [];
                    $good = [];
                    $bad = [];
                    foreach ($deliveryItem as $key => $value) {
                        $arrDeliveryItemId[] = $value->id;
                        $product_code[] = $value->product_code;
                        $quantity_received[] = $value->quantity_sent;
                        $good[] = $value->good_quantity;
                        $bad[] = 0;
                    }

                    $data_confirm_received = [
                        'purchase_id' => $purchase_data->id,
                        'product_code' => $product_code,
                        'quantity_received' => $quantity_received,
                        'do_ref' => $this->input->post('do_reference_no'),
                        'do_id' => $id,
                        'delivery_item_id' => $arrDeliveryItemId,
                        'good' => $good,
                        'bad' => $bad,
                        'note' => 'Received by Distributor',
                        'file' => null
                    ];
                    if ($sales->sale_type == 'booking') {
                        $confirm = $this->at_purchase->confirmReceivedBooking($data_confirm_received, $this->session->userdata('user_id'), $this->input->post('sale_id'));
                    } else {
                        $confirm = $this->at_purchase->confirmReceived($data_confirm_received, $this->session->userdata('user_id'), $this->input->post('sale_id'));
                    }

                    if (!$confirm) {
                        throw new \Exception("cannot receive product for aksestoko");
                    }

                    if ($sales->sale_type == 'booking') {
                        if ($this->site->checkAutoClose($sale->id)) {
                            $this->sales_model->closeSale($sale->id);
                        }
                    }

                    if ($purchase_data->payment_method == 'kredit_pro' && $purchase_data->status == 'received') {
                        $attachment = [];
                        $attachment = $this->generatePDFDeliv($sales);
                        $pathPDFInv = $this->generatePDFInv($sales, $purchase_data);
                        array_push($attachment, $pathPDFInv);
                        $this->sales_model->send_email_delivery($purchase_data->id, $sales, $attachment);
                    }
                }
                // end -- 

                if (in_array($dlDetails['status'], ['delivered', 'delivering'])) {
                    $notify_type = "delivering_delivery";
                } else if (in_array($dlDetails['status'], ['packing'])) {
                    $notify_type = "packing_delivery";
                }

                $purchase = $this->sales_model->getPurchasesByRefNo($sales->reference_no, $sales->company_id);
                $this->load->model('socket_notification_model');
                if ($notify_type) {
                    $data_socket_notification = [
                        'company_id'        => $sales->customer_id,
                        'transaction_id'    => 'SALE-' . $purchase->id . '-' . $id,
                        'customer_name'     => '',
                        'reference_no'      => $sales->reference_no,
                        'price'             => '',
                        'type'              => $notify_type,
                        'to'                => 'aksestoko',
                        'note'              => '',
                        'created_at'        => date('Y-m-d H:i:s')
                    ];
                    $this->socket_notification_model->addNotification($data_socket_notification);
                }

                if ($sales->client_id == 'atl') {
                    if (in_array($dlDetails['status'], ['delivered', 'delivering'])) {
                        $this->load->model('Integration_atl_model', 'integration_atl');
                        $call_insert_delivery_atl = $this->integration_atl->insert_delivery_atl($id);
                        if (!$call_insert_delivery_atl) {
                            throw new \Exception(lang('failed') . " -> Call API Update Delivery ATL");
                        }

                        $data_deliveries = ['atl_doid' => $call_insert_delivery_atl->doid];
                        $this->sales_model->update_deliveries($id, $data_deliveries);
                    }
                }

                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("delivery_updated"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['sale'] = $sales;
                $this->data['delivery'] = $this->sales_model->getDeliveryByID($id);
                $this->data['delivery_items'] = $this->sales_model->getDeliveryItemsByDeliveryId($this->data['delivery']->id);
                $ref = $this->data['delivery']->sale_reference_no;
                $get_sale = $this->db->get_where('sma_sales', ['reference_no' => $this->data['delivery']->sale_reference_no])->row();
                $this->data['sale_date'] = $get_sale->date;
                $this->data['sale_type'] = $sales->sale_type;

                $now = new DateTime();
                $date_db = new DateTime($this->data['delivery']->date);
                $date_db->modify('+2 day');
                $date_now = strtotime($now->format('Y-m-d'));
                $date_db = strtotime($date_db->format('Y-m-d'));
                $this->data['val_status'] = ($date_db > $date_now) ? '1' : '0';
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'sales/edit_delivery', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function generatePDFDeliv($sales)
    {
        $path = [];
        $this->load->model('sales_model');
        $deliveries = $this->sales_model->getAllDeliveryBySaleID($sales->id);
        // print_r($deliveries);die;
        foreach ($deliveries as $key => $deli) {
            $this->data['delivery'] = $deli;
            // $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            $this->data['biller'] = $this->site->getCompanyByID($sales->biller_id);
            $this->data['rows'] = $this->sales_model->getDeliveryItemsByDeliveryId($deli->id);
            $this->data['user'] = $this->site->getUser($deli->created_by);
            $name = lang("delivery") . "_" . str_replace('/', '_', $deli->do_reference_no) . "-" . $sales->biller_id . ".pdf";
            $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $path[] = $this->sma->generate_pdf($html, $name, 'S');
        }
        return $path;
    }

    public function generatePDFInv($inv, $purchase)
    {
        $this->load->model('aksestoko/at_site_model', 'at_site');
        // $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($inv->id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id, $inv->biller_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($inv->id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['po'] = $purchase;
        $name = "INVOICE_-_" . str_replace('/', '_', $inv->reference_no) . "-" . $inv->biller_id . ".pdf";
        $html = $this->load->view($this->theme . 'sales/sale_pdf_kredit_pro', $this->data, true);
        // var_dump($html);die;
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        return $this->sma->generate_pdf($html, $name, 'S', $this->data['biller']->invoice_footer);
    }

    public function return_delivery($id = null, $approval = null)
    {

        //$this->sma->checkPermissions('return_delivery');

        $this->db->trans_begin();
        try {
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $delivery = $this->sales_model->getDeliveryByID($id);
            $getClientStatus = $this->sales_model->getClientStatusByDeliveryId($id);
            $sale = $this->sales_model->getSalesById($delivery->sale_id);

            if ($delivery->status == "returned") {
                throw new Exception(lang('return_do_return'));
            } elseif ($delivery->status != "delivered") {
                throw new Exception(lang('not_delivered_do_return'));
            } elseif ($delivery->status == "delivered" && $this->sales_model->getReturnDeliveryByRef($delivery->do_reference_no, $delivery->sale_id)) {
                throw new Exception(lang('has_been_do_return'));
            } elseif ($delivery->status == "delivered" && $sale->client_id == "aksestoko" && $getClientStatus->bad <= 0) {
                throw new Exception(lang('return_AT'));
            } elseif ($sale->sale_status == "closed") {
                throw new Exception("You Can't return delivery. Sale is closed");
            }

            $this->sma->transactionPermissions('sales', $delivery->sale_id);
            //        $this->sma->transactionPermissions('deliveries',$id);
            $this->form_validation->set_rules('do_reference_no', lang("do_reference_no"), 'required');
            $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('address', lang("address"), 'required');


            if ($this->form_validation->run() == true) {
                // var_dump($this->input->post());die;
                if ($this->sales_model->getReturnDeliveryByRef($delivery->do_reference_no, $delivery->sale_id)) {
                    throw new Exception("you have returned delivery before");
                }
                $dlDetails = array(
                    'sale_id' => $this->input->post('sale_id'),
                    'do_reference_no' => $this->site->getReference('dr'),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'return_reference_no' => $this->input->post('do_reference_no'),
                    'customer' => $this->input->post('customer'),
                    'address' => $this->input->post('address'),
                    'status' => "returned",
                    'delivered_by' => $this->input->post('delivered_by'),
                    'received_by' => $this->input->post('received_by'),
                    'note' => $this->sma->clear_tags($this->input->post('note')),
                    'created_by' => $this->session->userdata('user_id'),
                    'client_id' => "aksestoko",
                );

                $delivery_items_id = $this->input->post('delivery_items_id');
                $delivered_quantity = $this->input->post('sent_quantity');
                $return_quantity = $this->input->post('return_quantity');

                foreach ($return_quantity as $i => $value) {
                    if ($return_quantity[$i] > $delivered_quantity[$i]) {
                        throw new Exception("Return quantity higher than delivered quantity");
                    }
                }
                if (array_sum($return_quantity) == 0) {
                    throw new Exception("At least return 1 quantity");
                }
                $shipping_cost = $this->input->post('shipping') ? $this->input->post('shipping') : 0;

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }

                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                    $dlDetails['date'] = $date;
                }
            } elseif ($this->input->post('return_delivery')) {
                throw new Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $this->sales_model->return_delivery($id, $dlDetails, $shipping_cost, $delivery_items_id, $delivered_quantity, $return_quantity)) {
                $this->db->update('deliveries', ['is_approval' => 1], ['id' => $id]);

                // $this->load->model('aksestoko/at_purchase_model', 'at_purchase_model');
                $sale = $this->sales_model->getSalesById($delivery->sale_id);
                $purchase = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->company_id);
                $this->data['user'] = $this->site->getUser($sale->created_by);
                if ($purchase) {
                    $purchase_items = $this->sales_model->getAllPurchaseItems($purchase->id);
                    $status = $purchase->status;

                    foreach ($purchase_items as $item) {
                        $newData = $this->db->query("SELECT 
                            sdi.`quantity_ordered` AS 'total_quantity', 
                            SUM(IF(sd.status = 'delivered', sdi.`good_quantity`, 0)) AS 'good_quantity', 
                            SUM(IF(sd.status = 'delivered', sdi.`bad_quantity`, IF(sd.status = 'returned', (sdi.`bad_quantity`*-1), 0))) AS 'bad_quantity'
                            FROM `sma_delivery_items` sdi 
                            INNER JOIN `sma_deliveries` sd 
                            ON sd.`id` = sdi.`delivery_id` 
                            WHERE sdi.`sale_id` = '" . $delivery->sale_id . "' AND sdi.`product_code` = '" . $item->product_code . "'
                            GROUP BY sdi.`product_code`
                            ")->result()[0];
                        // AND (sd.receive_status = 'received' OR ((sd.receive_status IS NULL OR sd.receive_status = '')) AND sd.status = 'returned')
                        $itemPurchase['good'] = $newData->good_quantity;
                        $itemPurchase['bad'] = $newData->bad_quantity;
                        $itemPurchase['sent'] = ($newData->bad_quantity + $newData->good_quantity);

                        $status = $itemPurchase['sent'] >= $item->quantity ? "received" : "partial";
                        $this->sales_model->updatePurchaseItemsById($item->id, [
                            'status' => $status,
                            'quantity_balance' => $itemPurchase['sent'],
                            'quantity_received' => $itemPurchase['sent'],
                            'good_quantity' => $itemPurchase['good'],
                            'bad_quantity' => $itemPurchase['bad'],
                        ]);
                    }
                    $this->site->syncQuantity(null, null, $purchase_items);

                    $items = $this->site->getAllPurchaseItems($purchase->id);
                    $status = 'received';
                    foreach ($items as $i => $item) {
                        if ($item->status != "received") {
                            $status = "partial";
                            break;
                        }
                    }
                    if ($status == "partial") {
                        $this->db->update('purchases', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $purchase->id]);
                    }
                }

                $this->load->model('socket_notification_model');
                $data_socket_notification = [
                    'company_id'        => $sale->customer_id,
                    'transaction_id'    => 'SALE-' . $purchase->id . '-' . $delivery->id,
                    'customer_name'     => '',
                    'reference_no'      => $sale->reference_no . ' (' . $delivery->do_reference_no . ')',
                    'price'             => '',
                    'type'              => 'confirm_return_delivery',
                    'to'                => 'aksestoko',
                    'note'              => '',
                    'created_at'        => date('Y-m-d H:i:s')
                ];
                $this->socket_notification_model->addNotification($data_socket_notification);

                //START - Mengirim SMS notifikasi kepada retail toko AksesToko
                $message = $this->site->makeMessage('sms_notif_return_approve', [
                    'do_ref' => $delivery->do_reference_no,
                ]);
                if (SMS_NOTIF) {
                    $message_sms = '';
                    if ($this->data['user']->phone_is_verified == 1) {
                        $status_sms = false;
                        $status_sms = $this->site->send_sms_otp((string) $this->data['user']->phone, $message, false, 'notif');
                        $message_sms = '|| sending sms notification failed';
                        if ($status_sms) {
                            $message_sms = '|| sending sms notification success';
                        }
                    }
                }
                //END - Mengirim SMS notifikasi kepada retail toko AksesToko
                //START - Mengirim WA notifikasi kepada retail toko AksesToko
                if (WA_NOTIF) {
                    $message_sms = '';
                    if ($this->data['user']->phone_is_verified == 1) {
                        $status_sms = false;
                        $status_sms = $this->site->send_wa_otp_wablas((string) $this->data['user']->phone, $message);
                        $message_sms = '|| sending wa notification failed';
                        if ($status_sms) {
                            $message_sms = '|| sending wa notification success';
                        }
                    }
                }
                //END - Mengirim WA notifikasi kepada retail toko AksesToko
                //START - Mengirim notifikasi kepada AksesToko Mobile
                $notification   = [
                    'title' => 'AksesToko - Pengembalian',
                    'body'  => $message
                ];
                $data = [
                    'click_action'   => 'FLUTTER_NOTIFICATION_CLICK',
                    'title'          => 'AksesToko - Pengembalian',
                    'body'           => $message,
                    'type'           => 'sms_notif_return_approve',
                    'id_pemesanan'   => $purchase->id,
                    'id_sales'       => $delivery->sale_id,
                    'do_ref'         => $delivery->do_reference_no,
                    'id_pengiriman'  => $delivery->id,
                    'tanggal'        => date('d/m/Y'),
                ];
                $notifikasi_atmobiel = $this->integration_model->notification_atmobile($notification, $data, $this->data['user']->id);

                if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                    $tipe          = 'warning';
                    $message_notif = "sending notification to aksestoko mobile failed " . $notifikasi_atmobiel->results[0]->error;
                } else {
                    $tipe          = 'message';
                    $message_notif = "sending notification to aksestoko mobile success.";
                }
                //END - Mengirim notifikasi kepada AksesToko Mobile

                $this->db->trans_commit();
                $this->session->set_userdata('remove_slls', 1);
                $this->session->set_flashdata('message', lang("delivery_updated") . ' ' . @$message_sms);
                $this->session->set_flashdata($tipe, lang("delivery_updated") . ' ' . @$message_notif);
                redirect("sales_booking/deliveries_booking");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['delivery'] = $this->sales_model->getDeliveryByID($id);
                $this->data['delivery_items'] = $this->sales_model->getDeliveryItemsByDeliveryId($this->data['delivery']->id);
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['sale_date'] = $sale->date;

                if (!is_null($approval)) {
                    $this->load->view($this->theme . 'sales/return_delivery_approval', $this->data);
                } else {
                    $this->load->view($this->theme . 'sales/return_delivery', $this->data);
                }
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();

            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_delivery($id = null)
    {
        //$this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deleteDelivery($id)) {
            echo lang("delivery_deleted");
        }
    }

    public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions'] && !$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    //$this->sma->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang("deliveries_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('deliveries'));
                    $sheet->SetCellValue('A1', lang('deliveries_created_date_do'))
                        ->SetCellValue('B1', lang('deliveries_reference_do'))
                        ->SetCellValue('C1', lang('sale_reference_no'))
                        ->SetCellValue('D1', lang('deliveries_customer_name'))
                        ->SetCellValue('E1', lang('deliveries_customer_address'))
                        ->SetCellValue('F1', lang('deliveries_product_code'))
                        ->SetCellValue('G1', lang('deliveries_product_name'))
                        ->SetCellValue('H1', lang('deliveries_quantity_do'))
                        ->SetCellValue('I1', lang('deliveries_quantity_delivery'))
                        //->SetCellValue('J1', lang('deliveries_quantity_received'))
                        ->SetCellValue('J1', lang('deliveries_quantity_good'))
                        ->SetCellValue('K1', lang('deliveries_quantity_bad'))
                        ->SetCellValue('L1', lang('deliveries_delivery_date'))
                        ->SetCellValue('M1', lang('deliveries_received_date'))
                        ->SetCellValue('N1', lang('deliveries_duration'))
                        ->SetCellValue('O1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->sales_model->getDeliveryByID($id);
                        $delivery_items = $this->sales_model->getDeliveryItemsByDeliveryId($delivery->id);
                        $is_first = true;
                        $merge_row = $row;
                        foreach ($delivery_items as $item) {
                            $sheet->SetCellValue('A' . $row, $is_first ? $this->sma->hrld($delivery->date) : '')
                                ->SetCellValue('B' . $row, $is_first ? $delivery->do_reference_no : '')
                                ->SetCellValue('C' . $row, $is_first ? $delivery->sale_reference_no : '')
                                ->SetCellValue('D' . $row, $is_first ? $delivery->customer : '')
                                ->SetCellValue('E' . $row, $is_first ? str_replace(['<br>', '<p>', '</p>'], [' ', ' ', ' '], $delivery->address) : '')
                                ->SetCellValue('F' . $row, $item->product_code ?? '-')
                                ->SetCellValue('G' . $row, $item->product_name ?? '-')
                                ->SetCellValue('H' . $row, intval($item->quantity_ordered) ?? '-')
                                ->SetCellValue('I' . $row, intval($item->quantity_sent) ?? '-')
                                //->SetCellValue('J' . $row, intval($item->quantity_received))
                                ->SetCellValue('J' . $row, intval($item->good_quantity) ?? '-')
                                ->SetCellValue('K' . $row, intval($item->bad_quantity) ?? '-')
                                ->SetCellValue('L' . $row, $delivery->delivering_date ?? '-')
                                ->SetCellValue('M' . $row, $delivery->delivered_date ?? '-');
                            $duration = null;
                            if ($delivery->delivering_date && $delivery->delivered_date) {
                                $delivering_date = date_create($delivery->delivering_date);
                                $delivered_date  = date_create($delivery->delivered_date);
                                $duration        = date_diff($delivered_date, $delivering_date);
                            }
                            $sheet->SetCellValue('N' . $row, isset($duration) ? ($duration->days != 0 ? $duration->days . ' Days ' : '') . ($duration->h != 0 ? $duration->h . ' Hour ' : '') : '0');
                            $sheet->SetCellValue('O' . $row, lang($delivery->status));
                            $is_first = false;
                            $row++;
                        }

                        if (count($delivery_items) > 1) {
                            $sheet = $spreadsheet->setActiveSheetIndex(0)->mergeCells("A" . $merge_row . ":A" . ($row - 1));
                            $sheet = $spreadsheet->setActiveSheetIndex(0)->mergeCells("B" . $merge_row . ":B" . ($row - 1));
                            $sheet = $spreadsheet->setActiveSheetIndex(0)->mergeCells("C" . $merge_row . ":C" . ($row - 1));
                            $sheet = $spreadsheet->setActiveSheetIndex(0)->mergeCells("D" . $merge_row . ":D" . ($row - 1));
                            $sheet = $spreadsheet->setActiveSheetIndex(0)->mergeCells("E" . $merge_row . ":E" . ($row - 1));
                        }
                    }

                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->getColumnDimension('A')->setWidth(19);
                    $sheet->getColumnDimension('B')->setWidth(19);
                    $sheet->getColumnDimension('C')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(18);
                    $sheet->getColumnDimension('E')->setWidth(35);
                    $sheet->getColumnDimension('F')->setWidth(13);
                    $sheet->getColumnDimension('G')->setWidth(20);
                    $sheet->getColumnDimension('H')->setWidth(16);
                    $sheet->getColumnDimension('I')->setWidth(16);
                    //$sheet->getColumnDimension('J')->setWidth(18);
                    $sheet->getColumnDimension('J')->setWidth(16);
                    $sheet->getColumnDimension('K')->setWidth(16);
                    $sheet->getColumnDimension('L')->setWidth(21);
                    $sheet->getColumnDimension('M')->setWidth(21);
                    $sheet->getColumnDimension('N')->setWidth(10);
                    $sheet->getColumnDimension('O')->setWidth(10);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
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

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_delivery_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function confirm_payment($id)
    {
        // //$this->sma->checkPermissions(false, true);
        $this->db->trans_begin();
        try {
            $paymentTmp = $this->sales_model->getPaymentTmpById($id);
            $this->load->model('purchases_model');
            $purchase = $this->site->getPurchaseByID($paymentTmp->purchase_id);
            $sale = $this->sales_model->getInvoiceByID($paymentTmp->sale_id);

            if ($purchase->payment_method != 'kredit_pro') {
                $responeSavePayment = $this->sales_model->confirm_payment($id);
                if (!$responeSavePayment) {
                    throw new \Exception("Failed");
                }
                if (!$this->audittrail->insertDistributorConfirmPayment($this->session->userdata('user_id'), $responeSavePayment['customer_id'], $this->session->userdata('company_id'), $responeSavePayment['sale_id'], $responeSavePayment['purchase_id'], $responeSavePayment['sale_payment_id'], $responeSavePayment['purchase_payment_id'], $responeSavePayment['paymet_tmp_id'])) {
                    throw new \Exception("Tidak dapat menyimpan rekam jejak audit distributor_approve_payment");
                }
            } else {
                if (!$this->sales_model->addPaymentFromThirdParty($id, $this->session->userdata('company_id'))) {
                    throw new \Exception("Failed");
                }
            }

            $this->load->model('socket_notification_model');
            $data_socket_notification = [
                'company_id'        => $sale->customer_id,
                'transaction_id'    => 'SALE-' . $purchase->id,
                'customer_name'     => '',
                'reference_no'      => $purchase->cf1,
                'price'             => '',
                'type'              => 'confirm_payment',
                'to'                => 'aksestoko',
                'note'              => '',
                'created_at'        => date('Y-m-d H:i:s')
            ];
            $this->socket_notification_model->addNotification($data_socket_notification);
            $sale = $this->sales_model->getInvoiceByID($paymentTmp->sale_id);
            $this->data['user'] = $this->site->getUser($sale->created_by);
            $now = time();
            $end_date = strtotime(date('Y-m-d', strtotime($purchase->payment_deadline)));
            $datediff = $now - $end_date;
            $duration = round($datediff / (60 * 60 * 24));

            //START - Mengirim SMS notifikasi kepada retail toko AksesToko
            if ($sale->grand_total - $sale->paid == 0) {
                $message_type = 'sms_notif_payment_paid';
                $message = $this->site->makeMessage('sms_notif_payment_paid', [
                    'sale_ref' => $sale->reference_no,
                    'grand_total' => $this->sma->formatMoney($sale->paid)
                ]);
            } else {
                $message_type = 'sms_notif_payment_partial';
                $message = $this->site->makeMessage('sms_notif_payment_partial', [
                    'sale_ref' => $sale->reference_no,
                    'payment_amount' => $this->sma->formatMoney($paymentTmp->nominal),
                    'payment_balance' => $this->sma->formatMoney($sale->grand_total - $sale->paid),
                    'grand_total' => $this->sma->formatMoney($sale->grand_total)
                ]);
            }

            if (SMS_NOTIF) {
                $message_sms = '';
                if ($this->data['user']->phone_is_verified == 1) {
                    $status_sms = false;
                    $status_sms = $this->site->send_sms_otp((string) $this->data['user']->phone, $message, false, 'notif');
                    $message_sms = 'sending sms notification failed';
                    if ($status_sms) {
                        $message_sms = 'sending sms notification success';
                    }
                }
            }
            //END - Mengirim SMS notifikasi kepada retail toko AksesToko
            //START - Mengirim WA notifikasi kepada retail toko AksesToko
            if (WA_NOTIF) {
                $message_sms = '';
                if ($this->data['user']->phone_is_verified == 1) {
                    $status_sms = false;
                    $status_sms = $this->site->send_wa_otp_wablas((string) $this->data['user']->phone, $message);
                    $message_sms = 'sending wa notification failed';
                    if ($status_sms) {
                        $message_sms = 'sending wa notification success';
                    }
                }
            }
            //END - Mengirim WA notifikasi kepada retail toko AksesToko
            //START - Mengirim notifikasi kepada AksesToko Mobile
            $notification   = [
                'title' => 'AksesToko - Pembayaran',
                'body'  => $message
            ];
            $data = [
                'click_action'     => 'FLUTTER_NOTIFICATION_CLICK',
                'title'            => 'AksesToko - Pembayaran',
                'body'             => $message,
                'type'             => $message_type,
                'id_pemesanan'     => $purchase->id,
                'id_sales'         => $paymentTmp->sale_id,
                'id_pembayaran'    => $id,
                'sale_ref'         => $sale->reference_no,
                'jenis_pembayaran' => $purchase->payment_method,
                'tanggal'          => date('d/m/Y'),
            ];
            $notifikasi_atmobiel = $this->integration_model->notification_atmobile($notification, $data, $this->data['user']->id);

            if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                $tipe          = 'warning';
                $message_notif = "sending notification to aksestoko mobile failed " . $notifikasi_atmobiel->results[0]->error;
            } else {
                $tipe          = 'message';
                $message_notif = "sending notification to aksestoko mobile success.";
            }
            //END - Mengirim notifikasi kepada AksesToko Mobile

            $this->db->trans_commit();
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', "payment confirmed | " . @$message_sms);
            $this->session->set_flashdata($tipe, "payment confirmed" . @$message_notif);
        } catch (Exception $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function reject_payment($id)
    {
        $this->db->trans_begin();
        try {
            $responeRejectPayment = $this->sales_model->reject_payment($id);
            $paymentTmp = $this->sales_model->getPaymentTmpById($id);
            $purchase = $this->site->getPurchaseByID($paymentTmp->purchase_id);
            if (!$responeRejectPayment) {
                throw new \Exception("Failed");
            }
            if (!$this->audittrail->insertDistributorRejectPayment($this->session->userdata('user_id'), $responeRejectPayment['customer_id'], $this->session->userdata('user_id'), $responeRejectPayment['sale_id'], $responeRejectPayment['purchase_id'])) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit distributor_reject_payment");
            }

            $sale = $this->sales_model->getSalesByRefNo($purchase->cf1, $purchase->supplier_id);
            $this->load->model('socket_notification_model');
            $data_socket_notification = [
                'company_id'        => $sale->customer_id,
                'transaction_id'    => 'SALE-' . $purchase->id,
                'customer_name'     => '',
                'reference_no'      => $purchase->cf1,
                'price'             => '',
                'type'              => 'reject_payment',
                'to'                => 'aksestoko',
                'note'              => '',
                'created_at'        => date('Y-m-d H:i:s')
            ];
            $this->socket_notification_model->addNotification($data_socket_notification);

            $sale = $this->sales_model->getInvoiceByID($paymentTmp->sale_id);
            $this->data['user'] = $this->site->getUser($sale->created_by);
            $now = time();
            $end_date = strtotime(date('Y-m-d', strtotime($purchase->payment_deadline)));
            $datediff = $now - $end_date;
            $duration = round($datediff / (60 * 60 * 24));
            //START - Mengirim SMS notifikasi kepada retail toko AksesToko
            $message = $this->site->makeMessage('sms_notif_payment_reject', [
                'sale_ref' => $sale->reference_no,
                'payment_amount' => $this->sma->formatMoney($paymentTmp->nominal),
                'payment_balance' => $this->sma->formatMoney($sale->grand_total - $sale->paid)
            ]);
            if (SMS_NOTIF) {
                $message_sms = '';
                if ($this->data['user']->phone_is_verified == 1) {
                    $status_sms = false;
                    $status_sms = $this->site->send_sms_otp((string) $this->data['user']->phone, $message, false, 'notif');
                    $message_sms = 'sending sms notification failed';
                    if ($status_sms) {
                        $message_sms = 'sending sms notification success';
                    }
                }
            }
            //END - Mengirim SMS notifikasi kepada retail toko AksesToko
            //START - Mengirim WA notifikasi kepada retail toko AksesToko
            if (WA_NOTIF) {
                $message_sms = '';
                if ($this->data['user']->phone_is_verified == 1) {
                    $status_sms = false;
                    $status_sms = $this->site->send_wa_otp_wablas((string) $this->data['user']->phone, $message);
                    $message_sms = 'sending wa notification failed';
                    if ($status_sms) {
                        $message_sms = 'sending wa notification success';
                    }
                }
            }
            //END - Mengirim WA notifikasi kepada retail toko AksesToko
            //START - Mengirim notifikasi kepada AksesToko Mobile
            $notification   = [
                'title' => 'AksesToko - Pembayaran',
                'body'  => $message
            ];
            $data = [
                'click_action'     => 'FLUTTER_NOTIFICATION_CLICK',
                'title'            => 'AksesToko - Pembayaran',
                'body'             => $message,
                'type'             => 'sms_notif_payment_reject',
                'id_pemesanan'     => $purchase->id,
                'id_sales'         => $paymentTmp->sale_id,
                'id_pembayaran'    => $id,
                'sale_ref'         => $sale->reference_no,
                'jenis_pembayaran' => $purchase->payment_method,
                'tanggal'          => date('d/m/Y'),
            ];
            $notifikasi_atmobiel = $this->integration_model->notification_atmobile($notification, $data, $this->data['user']->id);

            if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                $tipe          = 'warning';
                $message_notif = "sending notification to aksestoko mobile failed " . $notifikasi_atmobiel->results[0]->error;
            } else {
                $tipe          = 'message';
                $message_notif = "sending notification to aksestoko mobile success.";
            }
            //END - Mengirim notifikasi kepada AksesToko Mobile

            $this->db->trans_commit();
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', "reject payment success | " . @$message_sms);
            $this->session->set_flashdata($tipe, "reject payment success" . @$message_notif);
        } catch (Exception $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function payments($id = null)
    {
        $this->data['payments']           = $this->sales_model->getInvoicePaymentsBySalesId($id);
        $this->data['inv']                = $this->sales_model->getInvoiceByID($id);
        $this->data['payments_tmp']       = $this->sales_model->getPendingPaymentTmp($id);
        $this->data['payments_atl_tmp']   = $this->sales_model->getPendingPaymentAtlTmp($id);
        $this->data['purchase']           = $this->sales_model->getPurchasesByRefNo($this->data['inv']->reference_no, $this->data['inv']->company_id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    public function payments_tmp_image($id)
    {
        $this->data['payments_tmp'] = $this->sales_model->getPaymentTmpById($id);
        $this->load->view($this->theme . 'sales/payments_tmp_image', $this->data);
    }

    public function payments_tmp_atl_image($id)
    {
        $this->data['payments_tmp_atl'] = $this->sales_model->getPaymentTmpAtlById($id);
        $this->load->view($this->theme . 'sales/payment_tmp_atl_image', $this->data);
    }

    public function payment_note($id = null)
    {
        $payment                    = $this->sales_model->getPaymentByID($id);
        $inv                        = $this->sales_model->getSalesBySalesId($payment->sale_id);
        $this->data['biller']       = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer']     = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv']          = $inv;
        $this->data['payment']      = $payment;
        $this->data['page_title']   = $this->lang->line("payment_note");

        $this->load->view($this->theme . 'sales/payment_note', $this->data);
    }

    public function add_payment($id = null)
    {
        $this->db->trans_begin();
        try {
            //$this->sma->checkPermissions('payments', true);
            $this->load->helper('security');
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }

            $sale = $this->sales_model->getInvoiceByID($id);
            $purchase = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->company_id);

            if ($purchase) {

                if ($sale->sale_status == 'canceled') {
                    throw new \Exception(lang('cant_payment_for_canceled'));
                }

                if ($purchase->payment_method == 'kredit_pro') {
                    throw new \Exception(lang('cant_payment_for_kredit_pro'));
                }

                if ((int) $purchase->grand_total == (int) $purchase->paid && $sale->sale_type == 'booking') {
                    throw new \Exception(lang('close_cant_payment'));
                }
            }


            if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
                throw new \Exception(lang("sale_already_paid"));
            }

            if ($sale->sale_status == 'pending' && $sale->sale_type == 'booking') {
                throw new \Exception(lang("close_must_reserved"));
            }

            //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
            $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
            $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
            $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
            if ($this->form_validation->run() == true) {
                if ($this->input->post('paid_by') == 'deposit') {
                    $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                    $customer_id = $sale->customer_id;
                    if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        throw new \Exception(lang("amount_greater_than_deposit"));
                    }
                } else {
                    $customer_id = null;
                }
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $payment = array(
                    'date' => $date,
                    'sale_id' => $this->input->post('sale_id'),
                    'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
                    'amount' => $this->input->post('amount-paid'),
                    'paid_by' => $this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'note' => $this->input->post('note'),
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'received',
                    'company_id' => $this->session->userdata('company_id'),
                    'reference_dist' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay')
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg            = $this->integration_model->upload_files($_FILES['userfile']);
                    $photo                  = $uploadedImg->url;
                    $payment['attachment'] = $photo;
                }

                if ($purchase) {
                    $dataPaymentTemp = [
                        'purchase_id' => $purchase->id,
                        'sale_id' => $this->input->post('sale_id'),
                        'nominal' => $this->input->post('amount-paid'),
                        // 'url_image' => $uploadedImg->data->image->url,
                        'status' => 'accept',
                        'reference_no' => $payment['reference_no']
                    ];
                }

                //$this->sma->print_arrays($payment);
            } elseif ($this->input->post('add_payment')) {
                throw new \Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $purchase) {
                $this->load->model('aksestoko/payment_model');
                $id_payment_tmp = $this->payment_model->addPaymentTemp($dataPaymentTemp);
                if (!$this->sales_model->confirm_payment($id_payment_tmp)) {
                    throw new Exception("Confirm Payment Failed", 1);
                }


                $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $this->load->model('socket_notification_model');
                $data_socket_notification = [
                    'company_id'        => $sale->customer_id,
                    'transaction_id'    => 'SALE-' . $purchase->id,
                    'customer_name'     => '',
                    'reference_no'      => $sale->reference_no,
                    'price'             => $this->input->post('amount-paid'),
                    'type'              => 'new_payment',
                    'to'                => 'aksestoko',
                    'note'              => '',
                    'created_at'        => date('Y-m-d H:i:s')
                ];
                $this->socket_notification_model->addNotification($data_socket_notification);
                $this->db->trans_commit();

                $this->session->set_flashdata('message', lang("payment_added"));
                redirect($_SERVER["HTTP_REFERER"]);
            } elseif ($this->form_validation->run() == true && $payment_add = $this->sales_model->addPayment($payment, $customer_id)) {

                if ($sale->client_id == 'atl') {
                    $this->load->model('Integration_atl_model', 'integration_atl');
                    $call_insert_payment_atl = $this->integration_atl->insert_payment_atl($payment_add);
                    if (!$call_insert_payment_atl) {
                        throw new \Exception(lang('failed') . " -> Call API Insert Payment ATL");
                    }
                }

                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("payment_added"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                    throw new \Exception(lang('payment_was_returned'));
                }
                $this->data['inv'] = $sale;
                $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
                $this->data['modal_js'] = $this->site->modal_js();

                $this->load->view($this->theme . 'sales/add_payment', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_payment($id = null)
    {
        $this->db->trans_begin();
        try {
            //$this->sma->checkPermissions('edit', true);
            $this->load->helper('security');
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $payment = $this->sales_model->getPaymentByID($id);
            $sale = $this->sales_model->getInvoiceByID($payment->sale_id);
            $purchase =  $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->company_id);

            if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
                throw new \Exception(lang('x_edit_payment'));
            }
            $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
            $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
            $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
            $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
            if ($this->form_validation->run() == true) {
                if ($this->input->post('paid_by') == 'deposit') {
                    $customer_id = $sale->customer_id;
                    $amount = $this->input->post('amount-paid') - $payment->amount;
                    if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                        throw new \Exception(lang("amount_greater_than_deposit"));
                    }
                } else {
                    $customer_id = null;
                }
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = $payment->date;
                }
                $paymentUpdate = array(
                    'date' => $date,
                    'sale_id' => $this->input->post('sale_id'),
                    'reference_no' => $this->input->post('reference_no'),
                    'amount' => $this->input->post('amount-paid'),
                    'pos_paid' => $this->input->post('amount-paid'),
                    'paid_by' => $this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'note' => $this->input->post('note'),
                    'created_by' => $this->session->userdata('user_id'),
                );

                if ($purchase) {
                    $updatePaymentTemp = [
                        'nominal' => $this->input->post('amount-paid'),
                        'update_at' => date('Y-m-d H:i:s')
                    ];
                }

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg                = $this->integration_model->upload_files($_FILES['userfile']);
                    $photo                      = $uploadedImg->url;
                    $paymentUpdate['attachment'] = $photo;
                }

                //$this->sma->print_arrays($payment);
            } elseif ($this->input->post('edit_payment')) {
                throw new \Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $paymentUpdate, $customer_id)) {
                if ($purchase) {

                    //untuk mendapatkan sales yang terbaru
                    $sales = $this->sales_model->getSalesById($payment->sale_id);

                    $updatePurchase = [
                        'paid' => $sales->paid,
                        'payment_status' => $sales->payment_status,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $updatePaymentSent = [
                        'amount' => $sales->paid,
                        'pos_paid' => $sales->paid,
                    ];

                    if (!($this->db->update('payment_temp', $updatePaymentTemp, ['reference_no' => $payment->reference_dist]) &&
                        $this->db->update('purchases', $updatePurchase, ['id' => $purchase->id]))) {
                        throw new \Exception(lang("payment_failed"));
                    }
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("payment_updated"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['payment'] = $payment;

                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'sales/edit_payment', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_payment($id = null)
    {
        //$this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('sales', $id);
        if ($this->sales_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* --------------------------------------------------------------------------------------------- */

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sales_model->getProductNames($sr, $warehouse_id);

        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $get_wh = $this->sales_model->getWarehouseProduct($warehouse_id, $row->id);

                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->qty_wh = ($get_wh->quantity == null) ? 0 : $get_wh->quantity;
                $row->qty_book_wh = ($get_wh->quantity_booking == null) ? 0 : $get_wh->quantity_booking;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->cons = 0;
                $row->discount = '0';
                $row->serial = '';
                $options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                }
                $row->option = $option_id;
                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if ($row->type != 'consignment') {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                } else {
                    if ($cons = $this->site->getConsignmentQuantity($row->id, $warehouse_id)) {
                        $row->quantity = $cons;
                        $row->cons = 1;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }

                $row->markup = $get_wh->markup;

                if ($row->promotion) {
                    $row->price = $row->promo_price;
                    $row->price_credit = $row->promo_price;
                } elseif ($row->markup != null) {
                    if (strpos($row->markup, '%')) {
                        $row->price = (($row->markup / 100) * $get_wh->avg_cost) + $get_wh->avg_cost;
                        $row->price_credit = $row->price;
                    } else {
                        $row->price = $row->markup + $get_wh->avg_cost;
                        $row->price_credit = $row->price;
                    }
                } elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price != 0 ? $pr_group_price->price : $row->price;
                        $row->price_credit = $pr_group_price->price_kredit != 0 ? $pr_group_price->price_kredit : $row->credit_price;
                    } else {
                        $row->price = $row->price;
                        $row->price_credit = $row->credit_price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price != 0 ? $pr_group_price->price : $row->price;
                        $row->price_credit = $pr_group_price->price_kredit != 0 ? $pr_group_price->price_kredit : $row->credit_price;
                    } else {
                        $row->price = $row->price;
                        $row->price_credit = $row->credit_price;
                    }
                } else {
                    $row->price = $row->price;
                    $row->price_credit = $row->credit_price;
                }

                $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                $row->price_credit = $row->price_credit + (($row->price * $customer_group->percent) / 100);
                $row->real_unit_price = $row->price;
                $row->real_unit_price_credit = $row->price_credit;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = array(
                    'id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id,
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options
                );
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* ------------------------------------ Gift Cards ---------------------------------- */

    public function gift_cards()
    {
        //$this->sma->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('gift_cards')));
        $meta = array('page_title' => lang('gift_cards'), 'bc' => $bc);
        $this->page_construct('sales/gift_cards', $meta, $this->data);
    }

    public function getGiftCards()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('gift_cards') . ".id as id, card_no, value, balance, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by, customer, expiry", false)
            ->join('users', 'users.id=gift_cards.created_by', 'left')
            ->from("gift_cards")
            ->where("created_by", $this->session->userdata('user_id'))
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('sales/view_gift_card/$1') . "' class='tip' title='" . lang("view_gift_card") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('sales/topup_gift_card/$1') . "' class='tip' title='" . lang("topup_gift_card") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-dollar\"></i></a> <a href='" . site_url('sales/edit_gift_card/$1') . "' class='tip' title='" . lang("edit_gift_card") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_gift_card") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_gift_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function view_gift_card($id = null)
    {
        $this->data['page_title']   = lang('gift_card');
        $gift_card                  = $this->site->getGiftCardByID($id);
        $this->data['gift_card']    = $this->site->getGiftCardByID($id);
        $this->data['customer']     = $this->site->getCompanyByID($gift_card->customer_id);
        $this->data['topups']       = $this->sales_model->getAllGCTopups($id);
        $this->load->view($this->theme . 'sales/view_gift_card', $this->data);
    }

    public function topup_gift_card($card_id)
    {
        //$this->sma->checkPermissions('add_gift_card', true);
        $card = $this->site->getGiftCardByID($card_id);
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'card_id'    => $card_id,
                'amount'     => $this->input->post('amount'),
                'date'       => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id'),
            );
            $card_data['balance'] = ($this->input->post('amount') + $card->balance);
            // $card_data['value'] = ($this->input->post('amount')+$card->value);
            if ($this->input->post('expiry')) {
                $card_data['expiry'] = $this->sma->fld(trim($this->input->post('expiry')));
            }
        } elseif ($this->input->post('topup')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->topupGiftCard($data, $card_data)) {
            $this->session->set_flashdata('message', lang("topup_added"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['card'] = $card;
            $this->data['page_title'] = lang("topup_gift_card");
            $this->load->view($this->theme . 'sales/topup_gift_card', $this->data);
        }
    }

    public function validate_gift_card($no)
    {
        ////$this->sma->checkPermissions();
        if ($gc = $this->site->getGiftCardByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    $this->sma->send_json($gc);
                } else {
                    $this->sma->send_json(false);
                }
            } else {
                $this->sma->send_json($gc);
            }
        } else {
            $this->sma->send_json(false);
        }
    }

    public function add_gift_card()
    {
        $this->db->trans_begin();
        try {
            //$this->sma->checkPermissions(false, true);

            $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|is_unique[gift_cards.card_no]|required');
            $this->form_validation->set_rules('value', lang("value"), 'required');

            if ($this->form_validation->run() == true) {
                $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
                $customer = $customer_details ? $customer_details->company : null;
                $data = array(
                    'card_no' => $this->input->post('card_no'),
                    'value' => $this->input->post('value'),
                    'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                    'customer' => $customer,
                    'balance' => $this->input->post('value'),
                    'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                    'created_by' => $this->session->userdata('user_id'),
                );
                $sa_data = array();
                $ca_data = array();
                if ($this->input->post('staff_points')) {
                    $sa_points = $this->input->post('sa_points');
                    $user = $this->site->getUser($this->input->post('user'));
                    if ($user->award_points < $sa_points) {
                        throw new \Exception(lang("award_points_wrong"));
                    }
                    $sa_data = array('user' => $user->id, 'points' => ($user->award_points - $sa_points));
                } elseif ($customer_details && $this->input->post('use_points')) {
                    $ca_points = $this->input->post('ca_points');
                    if ($customer_details->award_points < $ca_points) {
                        throw new \Exception(lang("award_points_wrong"));
                    }
                    $ca_data = array('customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points));
                }
                // $this->sma->print_arrays($data, $ca_data, $sa_data);
            } elseif ($this->input->post('add_gift_card')) {
                throw new \Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("gift_card_added"));
                redirect("sales/gift_cards");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['users'] = $this->sales_model->getStaff();
                $this->data['page_title'] = lang("new_gift_card");
                $this->load->view($this->theme . 'sales/add_gift_card', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function edit_gift_card($id = null)
    {
        $this->db->trans_begin();
        try {
            //$this->sma->checkPermissions(false, true);
            $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
            $gc_details = $this->site->getGiftCardByID($id);
            if ($this->input->post('card_no') != $gc_details->card_no) {
                $this->form_validation->set_rules('card_no', lang("card_no"), 'is_unique[gift_cards.card_no]');
            }
            $this->form_validation->set_rules('value', lang("value"), 'required');
            //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

            if ($this->form_validation->run() == true) {
                $gift_card = $this->site->getGiftCardByID($id);
                $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
                $customer = $customer_details ? $customer_details->company : null;
                $data = array(
                    'card_no' => $this->input->post('card_no'),
                    'value' => $this->input->post('value'),
                    'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                    'customer' => $customer,
                    'balance' => ($this->input->post('value') - $gift_card->value) + $gift_card->balance,
                    'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                );
            } elseif ($this->input->post('edit_gift_card')) {
                throw new \Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $this->sales_model->updateGiftCard($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("gift_card_updated"));
                redirect("sales/gift_cards");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['gift_card'] = $this->site->getGiftCardByID($id);
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'sales/edit_gift_card', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function sell_gift_card()
    {
        //$this->sma->checkPermissions('gift_cards', true);
        $error = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang("value") . " " . lang("is_required");
        }
        if (empty($gcData[1])) {
            $error = lang("card_no") . " " . lang("is_required");
        }

        $customer_details = (!empty($gcData[2])) ? $this->site->getCompanyByID($gcData[2]) : null;
        $customer = $customer_details ? $customer_details->company : null;
        $data = array(
            'card_no' => $gcData[0],
            'value' => $gcData[1],
            'customer_id' => (!empty($gcData[2])) ? $gcData[2] : null,
            'customer' => $customer,
            'balance' => $gcData[1],
            'expiry' => (!empty($gcData[3])) ? $this->sma->fsd($gcData[3]) : null,
            'created_by' => $this->session->userdata('user_id'),
        );

        if (!$error) {
            if ($this->sales_model->addGiftCard($data)) {
                $this->sma->send_json(array('result' => 'success', 'message' => lang("gift_card_added")));
            }
        } else {
            $this->sma->send_json(array('result' => 'failed', 'message' => $error));
        }
    }

    public function delete_gift_card($id = null)
    {
        //$this->sma->checkPermissions();

        if ($this->sales_model->deleteGiftCard($id)) {
            echo lang("gift_card_deleted");
        }
    }

    public function gift_card_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    //$this->sma->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteGiftCard($id);
                    }
                    $this->session->set_flashdata('message', lang("gift_cards_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('gift_cards'))
                        ->SetCellValue('A1', lang('card_no'))
                        ->SetCellValue('B1', lang('value'))
                        ->SetCellValue('C1', lang('customer'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getGiftCardByID($id);
                        $sheet->SetCellValue('A' . $row, $sc->card_no)
                            ->SetCellValue('B' . $row, $sc->value)
                            ->SetCellValue('C' . $row, $sc->customer);
                        $row++;
                    }

                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'gift_cards_' . date('Y_m_d_H_i_s');
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

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_gift_card_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function get_award_points($id = null)
    {
        //$this->sma->checkPermissions('index');

        $row = $this->site->getUser($id);
        $this->sma->send_json(array('sa_points' => $row->award_points));
    }

    /* -------------------------------------------------------------------------------------- */

    public function sale_by_csv()
    {
        $this->session->set_flashdata('error', lang('add sale by csv can not be used anymore'));
        redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');

        //$this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : null;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';

            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("sales/sale_by_csv");
                }

                $csv = $this->upload->file_name;
                $data['attachment'] = $csv;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");

                $data['attachment'] = $csv;
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity'])) {
                        if ($product_details = $this->sales_model->getProductByCode($csv_pr['code'])) {
                            if ($csv_pr['variant']) {
                                $item_option = $this->sales_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_id = $product_details->id;
                            $item_type = $product_details->type;
                            $item_code = $product_details->code;
                            $item_name = $product_details->name;
                            $item_net_price = $this->sma->formatDecimal($csv_pr['net_unit_price']);
                            $item_quantity = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_serial = $csv_pr['serial'];

                            if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
                                $product_details = $this->sales_model->getProductByCode($item_code);

                                if (isset($item_discount)) {
                                    $discount = $item_discount;
                                    $dpos = strpos($discount, $percentage);
                                    if ($dpos !== false) {
                                        $pds = explode("%", $discount);
                                        $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($item_net_price)) * (float) ($pds[0])) / 100), 4);
                                    } else {
                                        $pr_discount = $this->sma->formatDecimal($discount);
                                    }
                                } else {
                                    $pr_discount = 0;
                                }
                                $item_net_price = $this->sma->formatDecimal(($item_net_price - $pr_discount), 4);
                                $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                                $product_discount += $pr_item_discount;

                                if (isset($item_tax_rate) && $item_tax_rate != 0) {
                                    if ($tax_details = $this->sales_model->getTaxRateByName($item_tax_rate)) {
                                        $pr_tax = $tax_details->id;
                                        if ($tax_details->type == 1) {
                                            $item_tax = $this->sma->formatDecimal((($item_net_price) * $tax_details->rate) / 100, 4);
                                            $tax = $tax_details->rate . "%";
                                        } elseif ($tax_details->type == 2) {
                                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                            $tax = $tax_details->rate;
                                        }
                                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);
                                    } else {
                                        $this->session->set_flashdata('error', lang("tax_not_found") . " ( " . $item_tax_rate . " ). " . lang("line_no") . " " . $rw);
                                        redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                } elseif ($product_details->tax_rate) {
                                    $pr_tax = $product_details->tax_rate;
                                    $tax_details = $this->site->getTaxRateByID($pr_tax);
                                    if ($tax_details->type == 1) {
                                        $item_tax = $this->sma->formatDecimal((($item_net_price) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } elseif ($tax_details->type == 2) {
                                        $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                        $tax = $tax_details->rate;
                                    }
                                    $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);
                                } else {
                                    $item_tax = 0;
                                    $pr_tax = 0;
                                    $pr_item_tax = 0;
                                    $tax = "";
                                }
                                $product_tax += $pr_item_tax;
                                $subtotal = $this->sma->formatDecimal((($item_net_price * $item_quantity) + $pr_item_tax), 4);
                                $unit = $this->site->getUnitByID($product_details->unit);

                                $products[] = array(
                                    'product_id' => $product_details->id,
                                    'product_code' => $item_code,
                                    'product_name' => $item_name,
                                    'product_type' => $item_type,
                                    'option_id' => $item_option->id,
                                    'net_unit_price' => $item_net_price,
                                    'quantity' => $item_quantity,
                                    'product_unit_id' => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity' => $item_quantity,
                                    'warehouse_id' => $warehouse_id,
                                    'item_tax' => $pr_item_tax,
                                    'tax_rate_id' => $pr_tax,
                                    'tax' => $tax,
                                    'discount' => $item_discount,
                                    'item_discount' => $pr_item_discount,
                                    'subtotal' => $subtotal,
                                    'serial_no' => $item_serial,
                                    'unit_price' => $this->sma->formatDecimal(($item_net_price + $item_tax), 4),
                                    'real_unit_price' => $this->sma->formatDecimal(($item_net_price + $item_tax + $pr_discount), 4),
                                );

                                $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
                            }
                        } else {
                            $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $rw++;
                    }
                }
            }

            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array(
                'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'sale_status' => $sale_status,
                'payment_status' => $payment_status,
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
                'company_id' => $this->session->userdata('company_id'),
            );

            if ($payment_status == 'paid') {
                $payment = array(
                    'date' => $date,
                    'reference_no' => $this->site->getReference('pay'),
                    'amount' => $grand_total,
                    'paid_by' => 'cash',
                    'cheque_no' => '',
                    'cc_no' => '',
                    'cc_holder' => '',
                    'cc_month' => '',
                    'cc_year' => '',
                    'cc_type' => '',
                    'created_by' => $this->session->userdata('user_id'),
                    'note' => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
                    'type' => 'received',
                );
            } else {
                $payment = array();
            }

            if ($_FILES['document']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                $photo              = $uploadedImg->url;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', $this->lang->line("sale_added"));
            redirect("sales");
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['slnumber'] = $this->site->getReference('so');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale_by_csv')));
            $meta = array('page_title' => lang('add_sale_by_csv'), 'bc' => $bc);
            $this->page_construct('sales/sale_by_csv', $meta, $this->data);
        }
    }

    public function update_status($id)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('status', lang("sale_status"), 'required');
            $this->data['inv']         = $this->sales_model->getInvoiceByID($id);
            $sales_items               = $this->sales_model->getSaleItemsBySaleId($id);
            foreach ($sales_items as $value) {
                $real_stock = $this->sales_model->getWarehouseProduct($value->warehouse_id, $value->product_id);
                if ($real_stock->quantity < $value->quantity) {
                    $this->data['sales_item'] = $value;
                    $this->data['real_stock'] = $this->sales_model->getWarehouseProduct($value->warehouse_id, $value->product_id);
                }
            }
            $this->data['user']        = $this->site->getUser($this->data['inv']->created_by);
            $this->data['po']          = $this->sales_model->getPurchasesByRefNo($this->data['inv']->reference_no, $this->data['inv']->biller_id);

            $deliv = $this->sales_model->getDeliveryBySaleID($id);
            $this->load->model('Companies_model');
            if ($this->data['po']) {
                $company = $this->Companies_model->getCompanyByID($this->data['po']->company_id);
                $comp = $this->Companies_model->findCompanyByCf1AndCompanyId($this->session->userdata('company_id'), $company->cf1);
                $kredit_limit = $this->sales_model->getKreditLimit($comp->customer_group_id)->kredit_limit;

                $debt_AT = $this->sales_model->getTotalDebt_AT($this->data['po']->company_id, $this->session->userdata('company_id'), $this->data['po']->id);
                $debt_AT = $debt_AT ? $debt_AT->total : 0;

                $debt_POS = $this->sales_model->getTotalDebt_POS($comp->id, $this->data['inv']->biller_id, null, $id) ?? 0;
                $debt_POS = $debt_POS ? $debt_POS->total : 0;

                $this->data['kredit_limit'] =  $kredit_limit - ($debt_AT + $debt_POS);
                // $this->data['kredit_limit'] =  $debt_POS ;

            }

            if (!$this->data['po']) {
                $company = $this->Companies_model->getCompanyByID($this->data['inv']->customer_id);
                $biller_id =  $this->sales_model->getBillerid($company->cf1);

                if ($biller_id) {
                    $debt_AT = $this->sales_model->getTotalDebt_AT($biller_id->company_id, $this->session->userdata('company_id'), null, $id);
                    $debt_AT = $debt_AT ? $debt_AT->total : 0;
                } else {
                    $debt_AT = 0;
                }

                $debt_POS = $this->sales_model->getTotalDebt_POS($this->data['inv']->customer_id, $this->data['inv']->biller_id, $this->data['inv']->id) ?? 0;
                $debt_POS = $debt_POS ? $debt_POS->total : 0;

                $kredit_limit = $this->sales_model->getKreditLimit($company->customer_group_id)->kredit_limit;
                $this->data['kredit_limit'] =  $kredit_limit - ($debt_AT + $debt_POS);
            }


            if ($this->form_validation->run() == true) {
                $status = $this->input->post('status');
                if (in_array($status, ['confirmed', 'reserved', 'completed'])) {
                    $stts = 'sedang diproses';
                } else {
                    $stts = 'telah dibatalkan';
                }
                $message = $this->site->makeMessage('sms_notif_update_status', [
                    'sale_ref' => $this->data['inv']->reference_no,
                    'status' =>  $stts,
                ]);
                $note = $this->sma->clear_tags($this->input->post('note'));
                $reason = $this->sma->clear_tags($this->input->post('reason'));
            } elseif ($this->input->post('update')) {
                throw new \Exception(validation_errors());
            }


            if ($this->form_validation->run() == true) {
                if ($deliv != false) {
                    throw new \Exception(lang('delivery_available'));
                }
                if ($this->data['po']) {
                    // if ($this->data['po']->payment_method == 'cash before delivery' && $this->data['inv']->paid < $this->data['inv']->grand_total && $status == 'completed') {
                    //     $this->session->set_flashdata('error', 'payment has not been paid yet');
                    //     redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
                    // }
                    if (($this->data['inv']->payment_status == 'waiting' || $this->data['inv']->paid > 0) && $status == 'canceled') {
                        throw new \Exception('waiting payment or partial/full paid');
                    }
                }
                if ($this->sales_model->updateStatus($id, $status, $note, $reason)) {

                    if ($this->data['po']) {
                        if (in_array($status, ['confirmed', 'reserved', 'completed'])) {
                            $notify_type = "confirm_order";
                        } else if (in_array($status, ['canceled'])) {
                            $notify_type = "canceled_order";
                        }

                        // else if(in_array($status, ['reserved', 'completed'])){
                        //     $notify_type = "accept_order";
                        // }

                        if ($notify_type) {
                            $this->load->model('socket_notification_model');
                            $data_socket_notification = [
                                'company_id'        => $this->data['po']->company_id,
                                'transaction_id'    => 'SALE-' . $this->data['po']->id,
                                'customer_name'     => '',
                                'reference_no'      => $this->data['po']->cf1,
                                'price'             => '',
                                'type'              => $notify_type,
                                'to'                => 'aksestoko',
                                'note'              => $note,
                                'created_at'        => date('Y-m-d H:i:s')
                            ];
                            $this->socket_notification_model->addNotification($data_socket_notification);
                        }
                    }

                    //START - Mengirim SMS notifikasi kepada retail toko AksesToko
                    if (SMS_NOTIF) {
                        $message_sms = '';
                        if ($this->input->post('sms')) {
                            $status_sms = false;
                            $status_sms = $this->site->send_sms_otp((string) $this->data['user']->phone, $message, false, 'notif');
                            $message_sms = '|| sending sms notification failed';
                            if ($status_sms) {
                                $message_sms = '|| sending sms notification success';
                            }
                        }

                        $message_wa = '';
                        if ($this->input->post('whatssapp')) {
                            $status_sms = false;
                            $status_sms = $this->site->send_wa_otp_wablas((string) $this->data['user']->phone, $message);
                            $message_wa = '|| sending whatsapp notification failed';
                            if ($status_sms) {
                                $message_wa = '|| sending whatsapp notification success';
                            }
                        }
                    }
                    //END - Mengirim SMS notifikasi kepada retail toko AksesToko
                    //START - Mengirim notifikasi kepada AksesToko Mobile
                    $notification   = [
                        'title' => 'AksesToko - Perubahan Status',
                        'body'  => $message
                    ];
                    $data = [
                        'click_action'     => 'FLUTTER_NOTIFICATION_CLICK',
                        'title'            => 'AksesToko - Perubahan Status',
                        'body'             => $message,
                        'type'             => 'sms_notif_update_status',        
                        'id_pemesanan'     => $this->data['po']->id,
                        'id_sales'         => $id,
                        'reference'        => $this->data['inv']->reference_no,
                        'tanggal'          => date('d/m/Y'),
                    ];

                    $notifikasi_atmobiel = $this->integration_model->notification_atmobile($notification, $data, $this->data['user']->id);

                    if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                        $tipe          = 'warning';
                        $message_notif = "sending notification to aksestoko mobile failed " . $notifikasi_atmobiel->results[0]->error;
                    } else {
                        $tipe          = 'message';
                        $message_notif = "sending notification to aksestoko mobile success.";
                    }
                    //END - Mengirim notifikasi kepada AksesToko Mobile

                    if ($this->data['inv']->client_id == 'atl') {
                        $this->load->model('Integration_atl_model', 'integration_atl');
                        $call_update_atl = $this->integration_atl->update_order_atl($id);
                        if (!$call_update_atl) {
                            throw new \Exception(lang('failed') . " -> Call API Update Order ATL");
                        }
                    }

                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang('status_updated') . ' ' . @$message_sms . ' ' . @$message_wa);
                    $this->session->set_flashdata($tipe, lang('status_updated') . ' ' . @$message_notif);
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
                } else {
                    throw new \Exception('Update failed');
                }
            } else {
                $this->data['returned'] = false;
                if ($this->data['inv']->sale_status == 'returned' || $this->data['inv']->return_id) {
                    $this->data['returned'] = true;
                }
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'sales/update_status', $this->data);
            }
        } catch (\Throwable $e) {
            $this->db->trans_rollback();

            $this->session->set_flashdata('error', $e->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    //    public function getCharges() {
    //        $city=$this->input->get('city',true);
    //        $address=$this->input->get('address',true);
    //        $state=$this->input->get('state',true);
    //
    //        $user=$this->site->getUser();
    //        $coor_customer=$this->sma->get_coordinates($city,$address,$state);
    //        $coor_user=$this->sma->get_coordinates($user->city,$user->address,$user->state);
    //
    //        $distance=$this->sma->GetDrivingDistance($coor_user['lat'],$coor_customer['lat'],$coor_user['long'],$coor_customer['long']);
    //        $rounding_distance=substr_replace((string)$distance['distance'],'',strpos((string)$distance['distance'],"."));
    //        $cost=$this->site->getShippingChargesData(intval($rounding_distance));
    //
    //        $this->sma->send_json($cost);
    //    }

    public function getCharges()
    {
        $lat_start = $this->input->get('sourceLat', true);
        $lng_start = $this->input->get('sourceLng', true);
        $lat_finish = $this->input->get('destinationLat', true);
        $lng_finish = $this->input->get('destinationLng', true);

        $distance = $this->sma->GetDrivingDistance($lat_start, $lat_finish, $lng_start, $lng_finish);
        $rounding_distance = substr_replace((string) $distance['distance'], '', strpos((string) $distance['distance'], "."));
        $cost = (($lat_finish && $lng_finish) ? $this->site->getShippingChargesData(intval($rounding_distance)) : array('cost' => 'Undefined Coordinates', 'cost_member' => 'Undefined Coordinates'));

        $this->sma->send_json($cost);
    }

    public function getShippingCostMap()
    {
        $distance = $this->input->get('distance', true);

        $cost = $this->site->getShippingChargesData($distance);
        $this->sma->send_json($cost);
    }

    // ---------------------------- Promotion -------------------//


    public function promotion_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    // //$this->sma->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->delete_promotion($id);
                    }
                    $this->session->set_flashdata('message', lang("gift_cards_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_gift_card_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function creditlimit($companies_id)
    {
        echo json_encode($this->sales_model->getRemainingCreditLimit($companies_id, $this->session->userdata('company_id')));
    }

    public function getQtyProduct($product_id, $warehouse_id)
    {
        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $get = $this->sales_model->getWarehouseProduct($warehouse_id, $product_id);
        if ($get) {
            $return['qty'] = (int) $get->quantity;
        } else {
            $return['qty'] = false;
        }

        echo json_encode($return);
    }

    // ---------------------------- END OF PROMOTION---------------//
    /* -------------------------------------------------------------------------------- */

    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function confirm_payment_atl($id)
    {
        $this->db->trans_begin();
        try {
            $paymentTmp   = $this->sales_model->getPaymentTmpAtlById($id);
            $sale         = $this->sales_model->getInvoiceByID($paymentTmp->sale_id);

            if (!$this->sales_model->addPaymentAtl($id, $this->session->userdata('company_id'))) {
                throw new \Exception(lang('failed'));
            }

            $this->load->model('Integration_atl_model', 'integration_atl');
            $call_api_confirm_payment_atl = $this->integration_atl->confirm_payment_atl($id);
            if (!$call_api_confirm_payment_atl) {
                throw new \Exception(lang('failed') . " -> Call API Confirm Payment ATL");
            }

            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang('payment_confirmed'));
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function reject_payment_atl($id)
    {
        $this->db->trans_begin();
        try {
            $responeRejectPayment   = $this->sales_model->response_reject_payment_atl($id);

            if (!$responeRejectPayment) {
                throw new \Exception(lang('failed'));
            }

            $this->load->model('Integration_atl_model', 'integration_atl');
            $call_api_confirm_payment_atl = $this->integration_atl->confirm_payment_atl($id);
            if (!$call_api_confirm_payment_atl) {
                throw new \Exception(lang('failed') . " -> Call API Confirm Payment ATL");
            }

            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang('reject_payment'));
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
}

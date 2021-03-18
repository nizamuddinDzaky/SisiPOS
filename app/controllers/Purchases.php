<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Purchases extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        // $this->insertLogActivities();
        $this->lang->load('purchases', $this->Settings->user_language);
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('authorized_model');
        $this->load->model('purchases_model');
        $this->load->model('companies_model');
        $this->load->model('integration_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    /* ------------------------------------------------------------------------- */

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $link_type = ['mb_purchases', 'mb_edit_purchases', 'mb_export_excel_purchases', 'mb_export_pdf_purchases'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchases')));
        $meta = array('page_title' => lang('purchases'), 'bc' => $bc);
        $this->page_construct('purchases/index', $meta, $this->data);
    }

    public function getPurchases($year, $month = null, $warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link      = anchor('purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link    = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $received_link    = anchor('purchases/received/$1', '<i class="fa fa-archive"></i> ' . 'View Received', 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $add_payment_link = anchor('purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $email_link       = anchor('purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $edit_link        = anchor('purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link         = anchor('purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode    = anchor('products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link      = anchor('purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        // $delete_link      = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase") . "</b>' data-content=\"<p>"
        //         . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete/$1') . "'>"
        //         . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        //         . lang('delete_purchase') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class = "dropdown-menu pull-right" role = "menu">
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $received_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $return_link . '</li>

        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        //        $this->datatables
        //            ->select($this->db->dbprefix('purchases').".id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment")
        //            ->from('purchases')
        //            ->join('warehouses','warehouses.id=purchases.warehouse_id','left')
        //            ->where('warehouses.company_id', $this->session->userdata('company_id'));
        //        if($month){
        //
        //        }
        $this->datatables
            ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, supplier, if(is_watched = 0, concat(status, '-unwatched'), status) as status, grand_total, paid, (grand_total-paid) as balance, sino_so, payment_status, attachment")
            ->from('purchases');
        if ($warehouse_id) {
            $this->datatables->where('warehouse_id', $warehouse_id);
        }

        if ($this->Admin) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->where("month(date)", $month);
        $this->datatables->where("year(date)", $year);

        //         $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }

        $this->datatables->where("is_deleted", null);

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    /* ----------------------------------------------------------------------------- */

    public function modal_view($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->sma->transactionPermissions('purchases', $purchase_id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->load->model('Official_model');
        $this->data['Official'] = $this->Official_model->status_order_partner($purchase_id, $inv->supplier_id);
        $this->Official_model->check_payment_partner($purchase_id);
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['customer']        = $this->site->getCompanyByID($inv->company_id);
        $this->data['inv']             = $inv;
        $this->data['payments']        = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;

        $this->db->update('purchases', ["is_watched" => 1, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $purchase_id]);

        $this->load->view($this->theme . 'purchases/modal_view', $this->data);
    }

    public function modal_view_print($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->sma->transactionPermissions('purchases', $purchase_id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->load->model('Official_model');
        $this->data['Official'] = $this->Official_model->status_order_partner($purchase_id, $inv->supplier_id);
        $this->Official_model->check_payment_partner($purchase_id);
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']             = $inv;
        $this->data['payments']        = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;

        $this->db->update('purchases', ["is_watched" => 1, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $purchase_id]);

        $this->load->view($this->theme . 'print/purchase', $this->data);
    }

    public function view($purchase_id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('purchases', $purchase_id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->load->model('Official_model');
        $this->data['Official'] = $this->Official_model->status_order_partner($purchase_id, $inv->supplier_id);
        $this->Official_model->check_payment_partner($purchase_id);
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['customer']        = $this->site->getCompanyByID($inv->company_id);
        $this->data['inv']             = $inv;
        $this->data['payments']        = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;

        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_details'), 'bc' => $bc);
        $this->page_construct('purchases/view', $meta, $this->data);
    }

    /* ----------------------------------------------------------------------------- */

    //generate pdf and force to download

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('purchases', $purchase_id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['inv']             = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $name              = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html              = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchases/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    public function combine_pdf($purchases_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($purchases_id as $purchase_id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by']      = $this->site->getUser($inv->created_by);
            $this->data['inv']             = $inv;
            $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
            $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
            $inv_html          = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $inv_html = preg_replace("'\<\?xml(.*)\?\>'", '', $inv_html);
            }
            $html[] = array(
                'content' => $inv_html,
                'footer'  => '',
            );
        }

        $name = lang("purchases") . ".pdf";
        $this->sma->generate_pdf($html, $name);
    }

    public function email($purchase_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('purchases', $purchase_id);
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        $this->form_validation->set_rules('to', $this->lang->line("to") . " " . $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', $this->lang->line("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', $this->lang->line("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to      = $this->input->post('to');
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
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person'   => $supplier->name,
                'company'          => $supplier->company,
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            );
            $msg        = $this->input->post('note');
            $message    = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('purchases', array('status' => 'ordered'), array('id' => $purchase_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("purchases");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/views/email_templates/purchase.html');
            }
            $this->data['subject'] = array(
                'name' => 'subject',
                'id'    => 'subject',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array(
                'name' => 'note',
                'id'    => 'note',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_temp),
            );
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id']       = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/email', $this->data);
        }
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- */

    public function add($quote_id = null, $deliveries_smig = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions();
        try {
            $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
            //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
            $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
            $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

            $this->session->unset_userdata('csrf_token');
            if ($this->form_validation->run() == true) {
                $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $warehouse_id     = $this->input->post('warehouse');
                $supplier_id      = $this->input->post('supplier');
                $status           = $this->input->post('status');
                $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier         = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                $note             = $this->sma->clear_tags($this->input->post('note'));
                $payment_term     = $this->input->post('payment_term');
                $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
                $shipping_date    = $this->sma->fld(trim($this->input->post('delivery_date'))) ? $this->sma->fld(trim($this->input->post('delivery_date'))) : null;
                $receiver         = $this->input->post('acceptor') ? $this->input->post('acceptor') : null;

                $total            = 0;
                $product_tax      = 0;
                $order_tax        = 0;
                $product_discount = 0;
                $order_discount   = 0;
                $percentage       = '%';
                $i                = sizeof($_POST['product']);
                for ($r = 0; $r < $i; $r++) {
                    $item_code          = $_POST['product'][$r];
                    $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                    $unit_cost          = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                    $real_unit_cost     = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                    $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                    $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                    $item_unit          = $_POST['product_unit'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];

                    // var_dump($item_code,$real_unit_cost,$unit_cost,$item_quantity);
                    // break;

                    if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                        $product_details = $this->purchases_model->getProductByCode($item_code);
                        if ($item_expiry) {
                            $today = date('Y-m-d');
                            if ($item_expiry <= $today) {
                                throw new \Exception(lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');

                                // $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                                // redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        // $unit_cost = $real_unit_cost;
                        $pr_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos     = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds         = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount);
                            }
                        }

                        $unit_cost         = $this->sma->formatDecimal($unit_cost - $pr_discount);
                        $item_net_cost     = $unit_cost;
                        $pr_item_discount  = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount += $pr_item_discount;
                        $pr_tax            = 0;
                        $pr_item_tax       = 0;
                        $item_tax          = 0;
                        $tax               = "";

                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $pr_tax      = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax      = $tax_details->rate . "%";
                                } else {
                                    $item_tax      = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax           = $tax_details->rate . "%";
                                    $item_net_cost = $unit_cost - $item_tax;
                                }
                            } elseif ($tax_details->type == 2) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax      = $tax_details->rate . "%";
                                } else {
                                    $item_tax      = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax           = $tax_details->rate . "%";
                                    $item_net_cost = $unit_cost - $item_tax;
                                }

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax      = $tax_details->rate;
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        }

                        $product_tax += $pr_item_tax;
                        $subtotal     = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                        $unit         = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_id'        => $product_details->id,
                            'product_code'      => $item_code,
                            'product_name'      => $product_details->name,
                            'option_id'         => $item_option,
                            'net_unit_cost'     => $item_net_cost,
                            'unit_cost'         => $this->sma->formatDecimal($item_net_cost + $item_tax),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity'     => $item_unit_quantity,
                            'quantity_balance'  => $status == "received" ? $item_quantity : 0,
                            'quantity_received' => $status == "received" ? $item_quantity : 0,
                            'warehouse_id'      => $warehouse_id,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $pr_tax,
                            'tax'               => $tax,
                            'discount'          => $item_discount,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->sma->formatDecimal($subtotal),
                            'expiry'            => $item_expiry,
                            'real_unit_cost'    => $real_unit_cost,
                            'date'              => date('Y-m-d', strtotime($date)),
                            'status'            => $status,
                            'supplier_part_no'  => $supplier_part_no,
                        );
                        $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                    }
                }
                if (empty($products)) {
                    $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } else {
                    krsort($products);
                }

                if ($this->input->post('discount')) {
                    $order_discount_id = $this->input->post('discount');
                    $opos              = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods            = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                    } else {
                        $order_discount = $this->sma->formatDecimal($order_discount_id);
                    }
                } else {
                    $order_discount_id = null;
                }
                $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

                if ($this->Settings->tax2 != 0) {
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

                $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
                
                /* if (!$this->input->post('no_si_so')) {
                    $cf1 = $this->input->post('cf1');
                } else {
                    $cf1 = 'PO/' . date('Y/m/') . $this->input->post('no_si_so');
                } */

                $data = array(
                    'reference_no'      => $reference,
                    'date'              => $date,
                    'supplier_id'       => $supplier_id,
                    'supplier'          => $supplier,
                    'warehouse_id'      => $warehouse_id,
                    'note'              => $note,
                    'total'             => $total,
                    'product_discount'  => $product_discount,
                    'order_discount_id' => $order_discount_id,
                    'order_discount'    => $order_discount,
                    'total_discount'    => $total_discount,
                    'product_tax'       => $product_tax,
                    'order_tax_id'      => $order_tax_id,
                    'order_tax'         => $order_tax,
                    'total_tax'         => $total_tax,
                    'shipping'          => $this->sma->formatDecimal($shipping),
                    'grand_total'       => $grand_total,
                    'status'            => $status,
                    'created_by'        => $this->session->userdata('user_id'),
                    'payment_term'      => $payment_term,
                    'due_date'          => $due_date,
                    'company_id'        => $this->session->userdata('company_id'),
                    'sino_spj'          => $this->input->post('no_si_spj') ? $this->input->post('no_si_spj') : NULL,
                    'sino_do'           => $this->input->post('no_si_do') ? $this->input->post('no_si_do') : NULL,
                    'sino_so'           => $this->input->post('no_si_so') ? $this->input->post('no_si_so') : NULL,
                    'sino_billing'      => $this->input->post('no_si_billing') ? $this->input->post('no_si_billing') : NULL,
                    'shipping_date'     => $shipping_date != '0000-00-00 00:00' ? date('Y-m-d H:i:s', strtotime($shipping_date)) : NULL,
                    'receiver'          => $receiver,
                    'cf1'               => $this->input->post('cf1'),
                    'cf2'               => $this->input->post('cf2'),
                    'license_plate'     => $this->input->post('license_plate')
                );

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                    }
                    $photo              = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }

                // $this->sma->print_arrays($data, $products);
            }
            if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {
                $this->db->trans_commit();
                $this->session->set_userdata('remove_pols', 1);
                $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
                redirect('purchases');
            } else {
                //nge cek apakah jumlah Purchases telah limit
                $isLimited = $this->authorized_model->isPurchaseLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_trx"));
                    $message = str_replace("yyy", lang("purchases"), $message);

                    $this->session->set_flashdata('error', $message);
                    redirect('purchases');
                }
                // akhir cek

                if ($quote_id) {
                    $this->data['quote'] = $deliveries_smig == 'smig' ? $this->purchases_model->getDeliveriesSmigByID($quote_id) : $this->purchases_model->getQuoteByID($quote_id);
                    if (property_exists($this->data['quote'], 'status_penerimaan') && $this->data['quote']->status_penerimaan == 'received') {
                        $this->session->set_flashdata('error', lang("already_received"));
                        $this->data['quote'] = $deliveries_smig == 'smig' ? redirect("deliveries_smig") : redirect("quotes");
                    } else {
                        $supplier_id = $this->data['quote']->supplier_id;
                        $items       = $deliveries_smig == 'smig' ? $this->purchases_model->getAllDeliveriesSmigtems($quote_id) : $this->purchases_model->getAllQuoteItems($quote_id);
                        krsort($items);
                        $c = rand(100000, 9999999);
                        $x = 0;
                        foreach ($items as $item) {
                            $row = $this->site->getProductByID($item->product_id);
                            if ($row->type == 'combo') {
                                $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                                foreach ($combo_items as $citem) {
                                    $crow = $this->site->getProductByID($citem->id);
                                    if (!$crow) {
                                        $crow      = json_decode('{}');
                                        $crow->qty = $item->quantity;
                                    } else {
                                        unset($crow->details, $crow->product_details, $crow->price);
                                        $crow->qty = $citem->qty * $item->quantity;
                                    }
                                    $crow->base_quantity  = $item->quantity;
                                    $crow->base_unit      = $crow->unit ? $crow->unit : $item->product_unit_id;
                                    $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                                    $crow->unit           = $item->product_unit_id;
                                    $crow->discount       = $item->discount ? $item->discount : '0';
                                    $supplier_cost        = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                                    $crow->cost           = $supplier_cost ? $supplier_cost : 0;
                                    $crow->tax_rate       = $item->tax_rate_id;
                                    $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                                    $crow->expiry         = '';
                                    $options              = $this->purchases_model->getProductOptions($crow->id);
                                    $units                = $this->site->getUnitsByBUID($row->base_unit);
                                    $tax_rate             = $this->site->getTaxRateByID($crow->tax_rate);
                                    $ri                   = $this->Settings->item_addition ? $crow->id : $c;

                                    $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                                    $c++;
                                }
                            } elseif ($row->type == 'standard') {
                                if (!$row) {
                                    $row           = json_decode('{}');
                                    $row->quantity = 0;
                                } else {
                                    unset($row->details, $row->product_details);
                                }

                                $row->id             = $item->product_id;
                                $row->code           = $item->product_code;
                                $row->name           = $item->product_name;
                                $row->base_quantity  = $item->quantity;
                                $row->base_unit      = $row->unit ? $row->unit : $item->product_unit_id;
                                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                                $row->unit           = $item->product_unit_id;
                                $row->qty            = $item->unit_quantity;
                                $row->option         = $item->option_id;
                                $row->discount       = $item->discount ? $item->discount : '0';
                                $supplier_cost       = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                                $row->cost           = $supplier_cost ? $supplier_cost : 0;
                                $row->tax_rate       = $item->tax_rate_id;
                                $row->expiry         = '';
                                $row->real_unit_cost = $row->cost ? $row->cost : 0;
                                $options             = $this->purchases_model->getProductOptions($row->id);
                                $units               = $this->site->getUnitsByBUID($row->base_unit);
                                $tax_rate            = $this->site->getTaxRateByID($row->tax_rate);
                                $ri                  = $this->Settings->item_addition ? $row->id : $c;

                                $pr[$ri] = array(
                                    'id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options
                                );
                                $id_rand_temp[$x] = array('trx_id' => $c, 'product_id' => $row->id);
                                $x++;
                                $c++;
                            }
                        }
                        $this->data['quote_items'] = json_encode($pr);
                        $this->data['rand_id']     = json_encode($id_rand_temp);
                    }
                }

                $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['quote_id']   = $quote_id;
                $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
                $this->data['categories'] = $this->site->getAllCategories();
                $this->data['tax_rates']  = $this->site->getAllTaxRates();
                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['ponumber']   = '';                                                                                //$this->site->getReference('po');
                $this->data['company']    = $this->companies_model->getCompanyByID($this->session->userdata('company_id'));

                $link_type = ['mb_add_purchase'];
                $this->load->model('db_model');
                $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
                foreach ($get_link as $val) {
                    $this->data[$val->type] = $val->uri;
                }

                $this->load->helper('string');
                $value = random_string('alnum', 20);
                $this->session->set_userdata('user_csrf', $value);
                $this->data['csrf'] = $this->session->userdata('user_csrf');
                $bc     = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase')));
                $meta   = array('page_title' => lang('add_purchase'), 'bc' => $bc);
                $this->page_construct('purchases/add', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* ------------------------------------------------------------------------------------- */

    public function edit($id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions();

        try {
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('purchases', $id);
            $inv                       = $this->purchases_model->getPurchaseByID($id);
            $purchase_items_in_costing = $this->site->getPurchaseInCosting($id);

            if (empty($inv)) {
                throw new \Exception(lang('purchases_not_found'));
            }

            if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
                throw new \Exception(lang('purchase_x_action'));
            }

            if ($purchase_items_in_costing) {
                throw new \Exception(lang('purchase_has_transaction'));
            }

            if (!$this->session->userdata('edit_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
            $this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
            $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
            $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

            $this->session->unset_userdata('csrf_token');
            if ($this->form_validation->run() == true) {
                $reference = $this->input->post('reference_no');
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = $inv->date;
                }
                $warehouse_id     = $this->input->post('warehouse');
                $supplier_id      = $this->input->post('supplier');
                $status           = $this->input->post('status');
                $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier         = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                $note             = $this->sma->clear_tags($this->input->post('note'));
                $payment_term     = $this->input->post('payment_term');
                $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
                $shipping_date    = $this->sma->fld(trim($this->input->post('delivery_date'))) ? $this->sma->fld(trim($this->input->post('delivery_date'))) : null;
                $receiver         = $this->input->post('acceptor') ? $this->input->post('acceptor') : null;

                $total            = 0;
                $product_tax      = 0;
                $order_tax        = 0;
                $product_discount = 0;
                $order_discount   = 0;
                $percentage       = '%';
                $partial          = false;
                $i                = sizeof($_POST['product']);
                for ($r = 0; $r < $i; $r++) {
                    $item_code          = $_POST['product'][$r];
                    $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                    $unit_cost          = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                    $real_unit_cost     = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $quantity_received  = $_POST['received_base_quantity'][$r];
                    $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                    $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                    $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                    $quantity_balance   = $_POST['quantity_balance'][$r];
                    $ordered_quantity   = $_POST['ordered_quantity'][$r];
                    $item_unit          = $_POST['product_unit'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];

                    if ($status == 'received' || $status == 'partial') {
                        if ($quantity_received < $item_quantity) {
                            $partial = 'partial';
                        } elseif ($quantity_received > $item_quantity) {
                            throw new \Exception(lang("received_more_than_ordered"));
                            // $this->session->set_flashdata('error', lang("received_more_than_ordered"));
                            // redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $balance_qty = $quantity_received - ($ordered_quantity - $quantity_balance);
                    } else {
                        $balance_qty       = $item_quantity;
                        $quantity_received = $item_quantity;
                    }
                    if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                        $product_details = $this->purchases_model->getProductByCode($item_code);
                        // $unit_cost = $real_unit_cost;
                        $pr_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos     = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds         = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount);
                            }
                        }

                        $unit_cost         = $this->sma->formatDecimal($unit_cost - $pr_discount);
                        $item_net_cost     = $unit_cost;
                        $pr_item_discount  = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount += $pr_item_discount;
                        $pr_tax            = 0;
                        $pr_item_tax       = 0;
                        $item_tax          = 0;
                        $tax               = "";

                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $pr_tax      = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax      = $tax_details->rate . "%";
                                } else {
                                    $item_tax      = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax           = $tax_details->rate . "%";
                                    $item_net_cost = $unit_cost - $item_tax;
                                }
                            } elseif ($tax_details->type == 2) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax      = $tax_details->rate . "%";
                                } else {
                                    $item_tax      = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax           = $tax_details->rate . "%";
                                    $item_net_cost = $unit_cost - $item_tax;
                                }

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax      = $tax_details->rate;
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        }

                        $product_tax += $pr_item_tax;
                        $subtotal     = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                        $unit         = $this->site->getUnitByID($item_unit);

                        $items[] = array(
                            'product_id'        => $product_details->id,
                            'product_code'      => $item_code,
                            'product_name'      => $product_details->name,
                            'option_id'         => $item_option,
                            'net_unit_cost'     => $item_net_cost,
                            'unit_cost'         => $this->sma->formatDecimal($item_net_cost + $item_tax),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity'     => $item_unit_quantity,
                            'quantity_balance'  => $status == "pending" ? 0 : $quantity_received,
                            'quantity_received' => $status == "pending" ? 0 : $quantity_received,
                            'warehouse_id'      => $warehouse_id,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $pr_tax,
                            'tax'               => $tax,
                            'discount'          => $item_discount,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->sma->formatDecimal($subtotal),
                            'expiry'            => $item_expiry,
                            'real_unit_cost'    => $real_unit_cost,
                            'supplier_part_no'  => $supplier_part_no,
                            'date'              => date('Y-m-d', strtotime($date)),
                        );

                        $total += $item_net_cost * $item_unit_quantity;
                    }
                }
                if ($status == 'received' || $status == 'partial') {
                    $status = $partial ? $partial : 'received';
                }
                if (empty($items)) {
                    $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } else {
                    foreach ($items as $item) {
                        $item["status"] = $status;
                        $products[]          = $item;
                    }
                    krsort($products);
                }

                if ($this->input->post('discount')) {
                    $order_discount_id = $this->input->post('discount');
                    $opos              = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods            = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                    } else {
                        $order_discount = $this->sma->formatDecimal($order_discount_id);
                    }
                } else {
                    $order_discount_id = null;
                }
                $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

                if ($this->Settings->tax2 != 0) {
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

                $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
                $data        = array(
                    'reference_no' => $reference,
                    'supplier_id'       => $supplier_id,
                    'supplier'          => $supplier,
                    'warehouse_id'      => $warehouse_id,
                    'note'              => $note,
                    'total'             => $total,
                    'product_discount'  => $product_discount,
                    'order_discount_id' => $order_discount_id,
                    'order_discount'    => $order_discount,
                    'total_discount'    => $total_discount,
                    'product_tax'       => $product_tax,
                    'order_tax_id'      => $order_tax_id,
                    'order_tax'         => $order_tax,
                    'total_tax'         => $total_tax,
                    'shipping'          => $this->sma->formatDecimal($shipping),
                    'grand_total'       => $grand_total,
                    'status'            => $status,
                    'updated_by'        => $this->session->userdata('user_id'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                    'payment_term'      => $payment_term,
                    'due_date'          => $due_date,
                    'sino_spj'          => $this->input->post('no_si_spj') ? $this->input->post('no_si_spj') : NULL,
                    'sino_do'           => $this->input->post('no_si_do') ? $this->input->post('no_si_do') : NULL,
                    'sino_so'           => $this->input->post('no_si_so') ? $this->input->post('no_si_so') : NULL,
                    'sino_billing'      => $this->input->post('no_si_billing') ? $this->input->post('no_si_billing') : NULL,
                    'shipping_date'     => $shipping_date != '0000-00-00 00:00' ? date('Y-m-d H:i:s', strtotime($shipping_date)) : NULL,
                    'receiver'          => $receiver,
                    'license_plate'     => $this->input->post('license_plate')
                );
                if ($date) {
                    $data['date'] = $date;
                }

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                    }
                    $photo                = $this->upload->file_name;*/
                    $uploadedImg            = $this->integration_model->upload_files($_FILES['document']);
                    $photo                  = $uploadedImg->url;
                    $data['attachment']     = $photo;
                }
            }
            if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products)) {
                $this->db->trans_commit();
                $this->session->set_userdata('remove_pols', 1);
                $this->session->set_flashdata('message', $this->lang->line("purchase_updated"));
                redirect('purchases');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['inv']   = $inv;
                if ($this->Settings->disable_editing) {
                    if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                        throw new \Exception(sprintf(lang("purchase_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                        // $this->session->set_flashdata('error', );
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                }
                $inv_items = $this->purchases_model->getAllPurchaseItems($id);
                krsort($inv_items);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row                   = $this->site->getProductByID($item->product_id);
                    $row->expiry           = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                    $row->base_quantity    = $item->quantity;
                    $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                    $row->unit             = $item->product_unit_id;
                    $row->qty              = $item->unit_quantity;
                    $row->oqty             = $item->quantity;
                    $row->supplier_part_no = $item->supplier_part_no;
                    $row->received         = $item->quantity_received ? $item->quantity_received : $item->quantity;
                    $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                    $row->discount         = $item->discount ? $item->discount : '0';
                    $options               = $this->purchases_model->getProductOptions($row->id);
                    $row->option           = $item->option_id;
                    $row->real_unit_cost   = $item->real_unit_cost;
                    $row->cost             = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                    $row->tax_rate         = $item->tax_rate_id;
                    unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                    $units                 = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate              = $this->site->getTaxRateByID($row->tax_rate);
                    $ri                    = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = array(
                        'id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                        'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options
                    );
                    $c++;
                }

                $this->data['inv_items']    = json_encode($pr);
                $this->data['id']           = $id;
                $this->data['suppliers']    = $this->site->getAllCompanies('supplier');
                $this->data['purchase']     = $this->purchases_model->getPurchaseByID($id);
                $date                       = $this->purchases_model->getPurchaseByID($id);
                $this->data['categories']   = $this->site->getAllCategories();
                $this->data['tax_rates']    = $this->site->getAllTaxRates();
                $this->data['warehouses']   = $this->site->getAllWarehouses();
                $this->load->helper('string');
                $value = random_string('alnum', 20);
                $this->session->set_userdata('user_csrf', $value);
                $this->session->set_userdata('remove_pols', 1);
                $this->data['csrf'] = $this->session->userdata('user_csrf');
                $bc                 = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('edit_purchase')));
                $meta               = array('page_title' => lang('edit_purchase'), 'bc' => $bc);
                $this->page_construct('purchases/edit', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* ----------------------------------------------------------------------------------------------------------- */

    public function purchase_by_csv()
    {
        $this->db->trans_begin();
        try {
            $this->sma->checkPermissions('csv');
            $this->load->helper('security');
            $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
            $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
            $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required|is_natural_no_zero');
            $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');

            if ($this->form_validation->run() == true) {
                $quantity  = "quantity";
                $product   = "product";
                $unit_cost = "unit_cost";
                $tax_rate  = "tax_rate";
                $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = null;
                }
                $warehouse_id     = $this->input->post('warehouse');
                $supplier_id      = $this->input->post('supplier');
                $status           = $this->input->post('status');
                $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier         = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                $note             = $this->sma->clear_tags($this->input->post('note'));

                $total            = 0;
                $product_tax      = 0;
                $order_tax        = 0;
                $product_discount = 0;
                $order_discount   = 0;
                $percentage       = '%';

                if (isset($_FILES["userfile"])) {
                    // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                    $this->load->library('upload');

                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = 'csv';
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = true;

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("purchases/purchase_by_csv");
                    }

                    $csv  = $this->upload->file_name;
                    $keys = array('code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'discount', 'expiry');

                    $arrResult = array();
                    $handle    = fopen($this->digital_upload_path . $csv, "r");
                    $keys = array('code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'discount', 'expiry');
                    if ($handle) {
                        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                            $arrResult[] = $row;
                        }
                        fclose($handle);
                    }
                    $titles = array_shift($arrResult);

                    $final = array();
                    foreach ($arrResult as $key => $value) {
                        $final[] = array_combine($keys, $value);
                    }

                    $rw = 2;
                    foreach ($final as $csv_pr) {
                        if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {
                            if ($product_details = $this->purchases_model->getProductByCode($csv_pr['code'])) {
                                if ($csv_pr['variant']) {
                                    $item_option = $this->purchases_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                    if (!$item_option) {
                                        throw new \Exception(lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                        // redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                } else {
                                    $item_option     = json_decode('{}');
                                    $item_option->id = null;
                                }

                                $item_code        = $csv_pr['code'];
                                $item_net_cost    = $this->sma->formatDecimal($csv_pr['net_unit_cost']);
                                $item_quantity    = $csv_pr['quantity'];
                                $quantity_balance = $csv_pr['quantity'];
                                $item_tax_rate    = $csv_pr['item_tax_rate'];
                                $item_discount    = $csv_pr['discount'];
                                $item_expiry      = isset($csv_pr['expiry']) ? $this->sma->fsd($csv_pr['expiry']) : null;

                                if (isset($item_discount) && $this->Settings->product_discount) {
                                    $discount = $item_discount;
                                    $dpos     = strpos($discount, $percentage);
                                    if ($dpos !== false) {
                                        $pds         = explode("%", $discount);
                                        $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($item_net_cost)) * (float) ($pds[0])) / 100), 4);
                                    } else {
                                        $pr_discount = $this->sma->formatDecimal($discount);
                                    }
                                } else {
                                    $pr_discount = 0;
                                }
                                $pr_item_discount  = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                                $product_discount += $pr_item_discount;

                                if (isset($item_tax_rate) && $item_tax_rate != 0) {
                                    if ($tax_details = $this->purchases_model->getTaxRateByName($item_tax_rate)) {
                                        $pr_tax = $tax_details->id;
                                        if ($tax_details->type == 1) {
                                            if (!$product_details->tax_method) {
                                                $item_tax       = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                                $tax            = $tax_details->rate . "%";
                                                $item_net_cost -= $item_tax;
                                            } else {
                                                $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / 100, 4);
                                                $tax      = $tax_details->rate . "%";
                                            }
                                        } elseif ($tax_details->type == 2) {
                                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                            $tax      = $tax_details->rate;
                                        }
                                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);
                                    } else {
                                        throw new \Exception(lang("tax_not_found") . " ( " . $item_tax_rate . " ). " . lang("line_no") . " " . $rw);
                                        // $this->session->set_flashdata('error', );
                                        // redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                } elseif ($product_details->tax_rate) {
                                    $pr_tax      = $product_details->tax_rate;
                                    $tax_details = $this->site->getTaxRateByID($pr_tax);
                                    if ($tax_details->type == 1) {
                                        if (!$product_details->tax_method) {
                                            $item_tax       = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                            $tax            = $tax_details->rate . "%";
                                            $item_net_cost -= $item_tax;
                                        } else {
                                            $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / 100, 4);
                                            $tax      = $tax_details->rate . "%";
                                        }
                                    } elseif ($tax_details->type == 2) {
                                        $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                        $tax      = $tax_details->rate;
                                    }
                                    $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);
                                } else {
                                    $pr_tax      = 0;
                                    $pr_item_tax = 0;
                                    $tax         = "";
                                }
                                $product_tax += $pr_item_tax;
                                $subtotal     = $this->sma->formatDecimal(((($item_net_cost * $item_quantity) + $pr_item_tax) - $pr_item_discount), 4);
                                $unit         = $this->site->getUnitByID($product_details->unit);
                                $products[]   = array(
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $item_code,
                                    'product_name'      => $product_details->name,
                                    'option_id'         => $item_option->id,
                                    'net_unit_cost'     => $item_net_cost,
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity'     => $item_quantity,
                                    'quantity_balance'  => $quantity_balance,
                                    'warehouse_id'      => $warehouse_id,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $pr_tax,
                                    'tax'               => $tax,
                                    'discount'          => $item_discount,
                                    'item_discount'     => $pr_item_discount,
                                    'expiry'            => $item_expiry,
                                    'subtotal'          => $subtotal,
                                    'date'              => date('Y-m-d', strtotime($date)),
                                    'status'            => $status,
                                    'unit_cost'         => $this->sma->formatDecimal(($item_net_cost + $item_tax), 4),
                                    'real_unit_cost'    => $this->sma->formatDecimal(($item_net_cost + $item_tax + $pr_discount), 4),
                                );

                                $total += $this->sma->formatDecimal(($item_net_cost * $item_quantity), 4);
                            } else {
                                throw new \Exception($this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                                // $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                                // redirect($_SERVER["HTTP_REFERER"]);
                            }
                            $rw++;
                        }
                    }
                }

                if ($this->input->post('discount')) {
                    $order_discount_id = $this->input->post('discount');
                    $opos              = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods            = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                    } else {
                        $order_discount = $this->sma->formatDecimal($order_discount_id);
                    }
                } else {
                    $order_discount_id = null;
                }
                $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);

                if ($this->Settings->tax2 != 0) {
                    $order_tax_id = $this->input->post('order_tax');
                    if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                        if ($order_tax_details->type == 2) {
                            $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                        }
                        if ($order_tax_details->type == 1) {
                            $order_tax = $this->sma->formatDecimal((($total + $product_tax - $total_discount) * $order_tax_details->rate) / 100);
                        }
                    }
                } else {
                    $order_tax_id = null;
                }

                $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $total_discount), 4);
                $data        = array(
                    'reference_no' => $reference,
                    'date'              => $date,
                    'supplier_id'       => $supplier_id,
                    'supplier'          => $supplier,
                    'warehouse_id'      => $warehouse_id,
                    'note'              => $note,
                    'total'             => $total,
                    'product_discount'  => $product_discount,
                    'order_discount_id' => $order_discount_id,
                    'order_discount'    => $order_discount,
                    'total_discount'    => $total_discount,
                    'product_tax'       => $product_tax,
                    'order_tax_id'      => $order_tax_id,
                    'order_tax'         => $order_tax,
                    'total_tax'         => $total_tax,
                    'shipping'          => $this->sma->formatDecimal($shipping),
                    'grand_total'       => $grand_total,
                    'status'            => $status,
                    'created_by'        => $this->session->userdata('user_id'),
                    'company_id'        => $this->session->userdata('company_id'),
                    'company_head_id'   => $this->session->userdata('company_id'),
                );

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo              = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }

                //$this->sma->print_arrays($data, $products);
            }

            if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
                redirect("purchases");
            } else {
                $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['tax_rates']  = $this->site->getAllTaxRates();
                $this->data['ponumber']   = '';                               // $this->site->getReference('po');

                $link_type = ['mb_add_purchase_by_csv'];
                $this->load->model('db_model');
                $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
                foreach ($get_link as $val) {
                    $this->data[$val->type] = $val->uri;
                }

                $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase_by_csv')));
                $meta = array('page_title' => lang('add_purchase_by_csv'), 'bc' => $bc);
                $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* --------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('purchases', $id);
        if ($this->purchases_model->deletePurchase($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("purchase_deleted");
                die();
            }
            $this->session->set_flashdata('message', lang('purchase_deleted'));
            redirect('welcome');
        }
    }

    /* --------------------------------------------------------------------------- */

    public function suggestions($id_supplier = null)
    {
        $term        = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->purchases_model->getProductNames($sr, $id_supplier);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option               = false;
                $row->item_tax_method = $row->tax_method;
                $options              = $this->purchases_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt       = json_decode('{}');
                    $opt->cost = 0;
                }
                $row->option           = $option_id;
                $row->supplier_part_no = '';
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                //                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->cost             = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : ($row->supplier1price ? $row->supplier1price : $row->cost);
                $row->real_unit_cost   = $row->cost;
                $row->base_quantity    = 1;
                $row->base_unit        = $row->unit;
                $row->base_unit_cost   = $row->cost;
                $row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->new_entry        = 1;
                $row->expiry           = '';
                $row->qty              = 1;
                $row->quantity_balance = '';
                $row->discount         = '0';
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);

                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = array(
                    'id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options
                );
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function purchase_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchases_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('purchases'))
                        ->SetCellValue('A1', lang('date'))
                        ->SetCellValue('B1', lang('reference_no'))
                        ->SetCellValue('C1', lang('supplier'))
                        ->SetCellValue('D1', lang('status'))
                        ->SetCellValue('E1', lang('grand_total'));

                    $spreadsheet->createSheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(1);
                    $sheet->setTitle(lang('purchase_items'))
                        ->SetCellValue('A1', lang('date'))
                        ->SetCellValue('B1', lang('reference_no'))
                        ->SetCellValue('C1', lang('product_code'))
                        ->SetCellValue('D1', lang('product_name'))
                        ->SetCellValue('E1', lang('unit_cost'))
                        ->SetCellValue('F1', lang('quantity'))
                        ->SetCellValue('G1', lang('received_quantity'))
                        ->SetCellValue('H1', lang('warehouse'))
                        ->SetCellValue('I1', lang('tax'))
                        ->SetCellValue('J1', lang('subtotal'))
                        ->SetCellValue('K1', lang('subtotal_received'))
                        ->SetCellValue('L1', lang('sisa_reseived'));

                    $row      = 2;
                    $row_item = 2;
                    foreach ($_POST['val'] as $id) {
                        $sheet = $spreadsheet->setActiveSheetIndex(0);
                        $purchase = $this->purchases_model->getPurchaseByID($id);
                        $sheet->SetCellValue('A' . $row, $this->sma->hrld($purchase->date))
                            ->SetCellValue('B' . $row, $purchase->reference_no)
                            ->SetCellValue('C' . $row, $purchase->supplier)
                            ->SetCellValue('D' . $row, $purchase->status)
                            ->SetCellValue('E' . $row, $this->sma->formatMoney($purchase->grand_total));

                        $sheet = $spreadsheet->setActiveSheetIndex(1);
                        $items = $this->site->getAllPurchaseItems($id);
                        foreach ($items as $item) {
                            $warehouse = $this->site->getWarehouseByID($item->warehouse_id);
                            if ($purchase->status == 'returned') {
                                $receive_quantity = $item->quantity;
                            } else {
                                $receive_quantity = $item->quantity_received;
                            }
                            $sheet->SetCellValue('A' . $row_item, $this->sma->hrld($purchase->date))
                                ->SetCellValue('B' . $row_item, $purchase->reference_no)
                                ->SetCellValue('C' . $row_item, '"' . $item->product_code . '"')
                                ->SetCellValue('D' . $row_item, $item->product_name)
                                ->SetCellValue('E' . $row_item, $item->unit_cost)
                                ->SetCellValue('F' . $row_item, $this->sma->formatQuantity($item->quantity))
                                ->SetCellValue('G' . $row_item, $this->sma->formatQuantity($receive_quantity))
                                ->SetCellValue('H' . $row_item, $warehouse->name)
                                ->SetCellValue('I' . $row_item, $this->sma->formatDecimal($item->item_tax))
                                ->SetCellValue('J' . $row_item, $item->subtotal)
                                ->SetCellValue('K' . $row_item, $receive_quantity * $item->unit_cost)
                                ->SetCellValue('L' . $row_item, $this->sma->formatQuantity($item->quantity-$receive_quantity));
                            $row_item++;
                        }
                        $row++;
                    }

                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'purchases_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                        $sheet->getDefaultStyle()->applyFromArray($styleArray);
                        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
                        $rendererName        = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary     = 'MPDF';
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
                        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        //header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                        //header('Cache-Control: max-age=0');
                        //
                        //$objExcel = IOFactory::createWriter($spreadsheet, 'Xlsx');
                        //$objWriter = new PHPExcel_Writer_PDF($objExcel);
                        //return $objWriter->save("05featuredemo.pdf");
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
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function received($id = null)
    {
        $this->sma->checkPermissions(false, true);
        $received               = $this->purchases_model->getStoryReceived($id);
        $this->data['received'] = $received;
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'purchases/received', $this->data);
    }

    public function payments($id = null)
    {
        $this->sma->checkPermissions(false, true);
        $inv = $this->purchases_model->getPurchaseByID($id);

        $this->load->model('Official_model');
        $this->Official_model->check_payment_partner($id);
        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->data['inv']      = $inv;
        $this->data['Official'] = $this->Official_model->status_order_partner($id, $inv->supplier_id);
        $this->load->view($this->theme . 'purchases/payments', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment                  = $this->purchases_model->getPaymentByID($id);
        $inv                      = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $this->data['supplier']   = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");

        $this->load->view($this->theme . 'purchases/payment_note', $this->data);
    }

    public function add_payment($id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        try {
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('purchases', $id);
            $purchase = $this->purchases_model->getPurchaseByID($id);
            if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
                $this->session->set_flashdata('error', lang("purchase_already_paid"));
                $this->sma->md();
            }

            //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
            $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
            $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
            $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $payment = array(
                    'date'         => $date,
                    'purchase_id'  => $this->input->post('purchase_id'),
                    'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                    'amount'       => $this->input->post('amount-paid'),
                    'paid_by'      => $this->input->post('paid_by'),
                    'cheque_no'    => $this->input->post('cheque_no'),
                    'cc_no'        => $this->input->post('pcc_no'),
                    'cc_holder'    => $this->input->post('pcc_holder'),
                    'cc_month'     => $this->input->post('pcc_month'),
                    'cc_year'      => $this->input->post('pcc_year'),
                    'cc_type'      => $this->input->post('pcc_type'),
                    'note'         => $this->sma->clear_tags($this->input->post('note')),
                    'created_by'   => $this->session->userdata('user_id'),
                    'type'         => 'sent',
                    'company_id'   => $this->session->userdata('company_id'),
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo                 = $this->upload->file_name;*/
                    $uploadedImg            = $this->integration_model->upload_files($_FILES['userfile']);
                    $photo                  = $uploadedImg->url;
                    $payment['attachment']  = $photo;
                }

                //$this->sma->print_arrays($payment);
            } elseif ($this->input->post('add_payment')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("payment_added"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['inv']         = $purchase;
                $this->data['payment_ref'] = '';                                                                                //$this->site->getReference('ppay');
                $this->data['modal_js']    = $this->site->modal_js();

                $this->load->view($this->theme . 'purchases/add_payment', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function edit_payment($id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions('edit', true);
        try {
            $this->load->helper('security');
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $get_payment = $this->purchases_model->getPaymentByID($id);
            $this->sma->transactionPermissions('purchases', $get_payment->purchase_id);
            $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
            $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
            $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
            $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $payment = array(
                    'date'         => $date,
                    'purchase_id'  => $this->input->post('purchase_id'),
                    'reference_no' => $this->input->post('reference_no'),
                    'amount'       => $this->input->post('amount-paid'),
                    'paid_by'      => $this->input->post('paid_by'),
                    'cheque_no'    => $this->input->post('cheque_no'),
                    'cc_no'        => $this->input->post('pcc_no'),
                    'cc_holder'    => $this->input->post('pcc_holder'),
                    'cc_month'     => $this->input->post('pcc_month'),
                    'cc_year'      => $this->input->post('pcc_year'),
                    'cc_type'      => $this->input->post('pcc_type'),
                    'note'         => $this->sma->clear_tags($this->input->post('note')),
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo                 = $this->upload->file_name;*/
                    $uploadedImg            = $this->integration_model->upload_files($_FILES['userfile']);
                    $photo                  = $uploadedImg->url;
                    $payment['attachment']  = $photo;
                }

                //$this->sma->print_arrays($payment);
            } elseif ($this->input->post('edit_payment')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("payment_updated"));
                redirect("purchases");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['payment']  = $this->purchases_model->getPaymentByID($id);
                $this->data['modal_js'] = $this->site->modal_js();

                $this->load->view($this->theme . 'purchases/edit_payment', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_payment($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('purchases', $id);
        if ($this->purchases_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function expenses($id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc      = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('expenses')));
        $meta    = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->page_construct('purchases/expenses', $meta, $this->data);
    }

    public function getExpenses()
    {
        $this->sma->checkPermissions('expenses');

        $detail_link = anchor('purchases/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('purchases/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        //$attachment_link = '<a href="'.base_url('assets/uploads/$1').'" target="_blank"><i class="fa fa-chain"></i></a>';
        // $delete_link = "<!-- Sementara tombol delete disembunyikan  <a href='#' class='po' title='<b>" . $this->lang->line("delete_expense") . "</b>' data-content=\"<p>"
        //         . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete_expense/$1') . "'>"
        //         . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        //         . lang('delete_expense') . "</a> -->";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class = "dropdown-menu pull-right" role = "menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
        </ul>
    </div></div>';
        // <li>' . $delete_link . '</li>

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment", false)
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
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function expense_note($id = null)
    {
        $expense                  = $this->purchases_model->getExpenseByID($id);
        $this->data['user']       = $this->site->getUser($expense->created_by);
        $this->data['category']   = $expense->category_id ? $this->purchases_model->getExpenseCategoryByID($expense->category_id) : null;
        $this->data['warehouse']  = $expense->warehouse_id ? $this->site->getWarehouseByID($expense->warehouse_id) : null;
        $this->data['expense']    = $expense;
        $this->data['page_title'] = $this->lang->line("expense_note");
        $this->load->view($this->theme . 'purchases/expense_note', $this->data);
    }

    public function add_expense()
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions('expenses', true);
        $this->load->helper('security');
        try {
            //$this->form_validation->set_rules('reference', lang("reference"), 'required');
            $this->form_validation->set_rules('amount', lang("amount"), 'required');
            $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
            if ($this->form_validation->run() == true) {
                //nge cek apakah jumlah Expenses telah limit
                $isLimited = $this->authorized_model->isExpenseLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_trx"));
                    $message = str_replace("yyy", lang("expenses"), $message);

                    $this->session->set_flashdata('error', $message);
                    redirect("purchases/expenses");
                }
                // akhir cek

                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $data = array(
                    'date'         => $date,
                    'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                    'amount'       => $this->input->post('amount'),
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                    'company_id'   => $this->session->userdata('company_id'),
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo        = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['userfile']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }

                //$this->sma->print_arrays($data);
            } elseif ($this->input->post('add_expense')) {
                throw new \Exception($error);
                // $this->session->set_flashdata('error', validation_errors());
                // redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->form_validation->run() == true && $this->purchases_model->addExpense($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("expense_added"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['exnumber']   = '';                                                                                    //$this->site->getReference('ex');
                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['categories'] = $this->purchases_model->getExpenseCategories($this->session->userdata('company_id'));
                $this->data['modal_js']   = $this->site->modal_js();
                $this->load->view($this->theme . 'purchases/add_expense', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());

            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function edit_expense($id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        try {
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('expenses', $id);
            $this->form_validation->set_rules('reference', lang("reference"), 'required');
            $this->form_validation->set_rules('amount', lang("amount"), 'required');
            $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $data = array(
                    'date'         => $date,
                    'reference'    => $this->input->post('reference'),
                    'amount'       => $this->input->post('amount'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                );
                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo        = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['userfile']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }

                //$this->sma->print_arrays($data);
            } elseif ($this->input->post('edit_expense')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->form_validation->run() == true && $this->purchases_model->updateExpense($id, $data)) {
                $this->session->set_flashdata('message', lang("expense_updated"));
                redirect("purchases/expenses");
            } else {
                $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['expense']    = $this->purchases_model->getExpenseByID($id);
                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['modal_js']   = $this->site->modal_js();
                $this->data['categories'] = $this->purchases_model->getExpenseCategories($this->session->userdata('company_id'));
                $this->load->view($this->theme . 'purchases/edit_expense', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_expense($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('purchases', $id);
        $expense = $this->purchases_model->getExpenseByID($id);
        if ($this->purchases_model->deleteExpense($id)) {
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            echo lang("expense_deleted");
        }
    }

    public function expense_actions()
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
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deleteExpense($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("expenses_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } else if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('expenses'))
                        ->SetCellValue('A1', lang('date'))
                        ->SetCellValue('B1', lang('reference'))
                        ->SetCellValue('C1', lang('amount'))
                        ->SetCellValue('D1', lang('note'))
                        ->SetCellValue('E1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $expense = $this->purchases_model->getExpenseByID($id);
                        $user    = $this->site->getUser($expense->created_by);
                        $sheet->SetCellValue('A' . $row, $this->sma->hrld($expense->date))
                            ->SetCellValue('B' . $row, $expense->reference)
                            ->SetCellValue('C' . $row, $this->sma->formatMoney($expense->amount))
                            ->SetCellValue('D' . $row, $expense->note)
                            ->SetCellValue('E' . $row, $user->first_name . ' ' . $user->last_name);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(35);
                    $sheet->getColumnDimension('E')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'expenses_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                        $sheet->getDefaultStyle()->applyFromArray($styleArray);
                        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
                        $rendererName        = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary     = 'MPDF';
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
                $this->session->set_flashdata('error', $this->lang->line("no_expense_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view_return($id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('purchases', $id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv     = $this->purchases_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']   = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['supplier']  = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments']  = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user']      = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']       = $inv;
        $this->data['rows']      = $this->purchases_model->getAllReturnItems($id);
        $this->data['purchase']  = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme . 'purchases/view_return', $this->data);
    }

    public function return_purchase($id = null)
    {
        $this->sma->checkPermissions('return_purchases');
        $this->db->trans_begin();
        try {
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            $this->sma->transactionPermissions('purchases', $id);
            $purchase = $this->purchases_model->getPurchaseByID($id);
            if ($purchase->return_id) {
                throw new \Exception(lang("purchase_already_returned"));
            }
            $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

            if ($this->form_validation->run() == true) {
                $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }

                $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
                $note             = $this->sma->clear_tags($this->input->post('note'));

                $total            = 0;
                $product_tax      = 0;
                $order_tax        = 0;
                $product_discount = 0;
                $order_discount   = 0;
                $percentage       = '%';
                $i                = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
                for ($r = 0; $r < $i; $r++) {
                    $item_id            = $_POST['product_id'][$r];
                    $item_code          = $_POST['product'][$r];
                    $purchase_item_id   = $_POST['purchase_item_id'][$r];
                    $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                    $real_unit_cost     = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                    $unit_cost          = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                    $item_unit_quantity = (0 - $_POST['quantity'][$r]);                                                                                                                                  //<<<suspect
                    $item_expiry        = isset($_POST['expiry'][$r]) ? $_POST['expiry'][$r] : '';
                    $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_unit          = $_POST['product_unit'][$r];
                    $item_quantity      = (0 - $_POST['product_base_quantity'][$r]);

                    $_cekstok   = $this->purchases_model->getWarehouseProductQuantity($purchase->warehouse_id, $item_id);
                    $hasil      = $_cekstok->quantity + ($item_unit_quantity);
                    if ($hasil < 0) {
                        throw new \Exception(lang("do_not_return"));
                    }

                    if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                        $product_details = $this->purchases_model->getProductByCode($item_code);

                        $item_type = $product_details->type;
                        $item_name = $product_details->name;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos     = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds         = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount);
                            }
                        } else {
                            $pr_discount = 0;
                        }
                        $pr_item_discount  = $this->sma->formatDecimal(($pr_discount * $item_unit_quantity), 4);
                        $product_discount += $pr_item_discount;

                        $item_tax = 0;
                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $pr_tax      = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                if (!$product_details->tax_method) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax      = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax      = $tax_details->rate . "%";
                                }
                            } elseif ($tax_details->type == 2) {
                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax      = $tax_details->rate;
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        } else {
                            $pr_tax      = 0;
                            $pr_item_tax = 0;
                            $tax         = "";
                        }

                        $item_net_cost  = $product_details->tax_method ? $this->sma->formatDecimal(($unit_cost - $pr_discount), 4) : $this->sma->formatDecimal(($unit_cost - $item_tax - $pr_discount), 4);
                        $product_tax   += $pr_item_tax;
                        $subtotal       = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                        $unit           = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_id'        => $item_id,
                            'product_code'      => $item_code,
                            'product_name'      => $item_name,
                            'option_id'         => $item_option,
                            'net_unit_cost'     => $item_net_cost,
                            'unit_cost'         => $this->sma->formatDecimal($item_net_cost + $item_tax),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity'     => $item_unit_quantity,
                            'quantity_balance'  => $item_quantity,
                            'warehouse_id'      => $purchase->warehouse_id,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $pr_tax,
                            'tax'               => $tax,
                            'discount'          => $item_discount,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->sma->formatDecimal($subtotal),
                            'real_unit_cost'    => $real_unit_cost,
                            'purchase_item_id'  => $purchase_item_id,
                            'status'            => 'returned',
                        );

                        $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                    }
                }
                if (empty($products)) {
                    $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } else {
                    krsort($products);
                }

                if ($this->input->post('discount')) {
                    $order_discount_id = $this->input->post('order_discount');
                    $opos              = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods            = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                    } else {
                        $order_discount = $this->sma->formatDecimal($order_discount_id);
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

                $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) - $order_discount), 4);
                $data        = array(
                    'date' => $date,
                    'purchase_id'         => $id,
                    'reference_no'        => $purchase->reference_no,
                    'supplier_id'         => $purchase->supplier_id,
                    'supplier'            => $purchase->supplier,
                    'warehouse_id'        => $purchase->warehouse_id,
                    'note'                => $note,
                    'total'               => $total,
                    'product_discount'    => $product_discount,
                    'order_discount_id'   => $order_discount_id,
                    'order_discount'      => $order_discount,
                    'total_discount'      => $total_discount,
                    'product_tax'         => $product_tax,
                    'order_tax_id'        => $order_tax_id,
                    'order_tax'           => $order_tax,
                    'total_tax'           => $total_tax,
                    'surcharge'           => $this->sma->formatDecimal($return_surcharge),
                    'grand_total'         => $grand_total,
                    'created_by'          => $this->session->userdata('user_id'),
                    'return_purchase_ref' => $reference,
                    'status'              => 'returned',
                    'payment_status'      => $purchase->payment_status == 'paid' ? 'due' : 'pending',
                    'company_id'          => $this->session->userdata('company_id'),
                );

                if ($_FILES['document']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                    }
                    $photo        = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }

                if($this->purchases_model->addPurchase($data, $products)){
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("return_purchase_added"));
                    redirect("purchases");
                }else{
                    throw new \Exception(lang("error"));
                }

            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['inv'] = $purchase;
                if ($this->data['inv']->status != 'received') {
                    throw new \Exception(lang("purchase_status_x_received"));
                }

                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
                    throw new \Exception(lang("purchase_x_edited_older_than_3_months"));
                }
                $inv_items = $this->purchases_model->getAllPurchaseItems($id);
                krsort($inv_items);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row                   = $this->site->getProductByID($item->product_id);
                    $row->expiry           = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                    $row->base_quantity    = ($item->quantity != $item->quantity_received) ? $item->quantity_received : $item->quantity;
                    $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                    $row->unit             = $item->product_unit_id;
                    $row->qty              = 0;// ($item->unit_quantity != $item->quantity_received) ? $item->quantity_received : $item->unit_quantity;
                    $row->oqty             = $item->unit_quantity;
                    $row->purchase_item_id = $item->id;
                    $row->supplier_part_no = $item->supplier_part_no;
                    $row->received         = $item->quantity_received ? $item->quantity_received : $item->quantity;
                    $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                    $row->discount         = $item->discount ? $item->discount : '0';
                    $options               = $this->purchases_model->getProductOptions($row->id);
                    $row->option           = !empty($item->option_id) ? $item->option_id : '';
                    $row->real_unit_cost   = $item->real_unit_cost;
                    $row->cost             = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                    $row->tax_rate         = $item->tax_rate_id;
                    unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options);

                    $c++;
                }

                $this->data['inv_items'] = json_encode($pr);
                $this->data['id']        = $id;
                $this->data['reference'] = '';
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                $bc                      = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('return_purchase')));
                $meta                    = array('page_title' => lang('return_purchase'), 'bc' => $bc);
                $this->page_construct('purchases/return_purchase', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function getSupplierCost($supplier_id, $product)
    {
        switch ($supplier_id) {
            case  $product->supplier1:
                $cost = $product->supplier1price > 0 ? $product->supplier1price : $product->cost;
                break;
            case  $product->supplier2:
                $cost = $product->supplier2price > 0 ? $product->supplier2price : $product->cost;
                break;
            case  $product->supplier3:
                $cost = $product->supplier3price > 0 ? $product->supplier3price : $product->cost;
                break;
            case  $product->supplier4:
                $cost = $product->supplier4price > 0 ? $product->supplier4price : $product->cost;
                break;
            case  $product->supplier5:
                $cost = $product->supplier5price > 0 ? $product->supplier5price : $product->cost;
                break;
            default:
                $cost = $product->cost;
        }
        return $cost;
    }

    public function update_status($id)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('status', lang("status"), 'required');

            $this->db->update('purchases', ["is_watched" => 1, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $id]);

            if ($this->form_validation->run() == true) {
                $status            = $this->input->post('status');
                $purchases_item_id = $this->input->post('id');
                $received_amount   = $this->input->post('received_amount');
                $total_amount      = 0;
                foreach ($received_amount as $value) {
                    $total_amount += $value;
                }
                $note = $this->sma->clear_tags($this->input->post('note'));
                $data = [
                    'status'            => $status,
                    'purchases_item_id' => $purchases_item_id,
                    'received_amount'   => $received_amount,
                    'note'              => $note,
                    'do_reference'      => $this->input->post('do_reference')
                ];

                if ($total_amount == 0 && $status == 'received') {
                    throw new \Exception("Total amount received cannot zero");
                    // $this->session->set_flashdata('error', "Total amount received cannot zero");
                    // redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
                }
            } elseif ($this->input->post('update')) {
                throw new \Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $this->purchases_model->updateStatus($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang('status_updated'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
            } else {
                $items = $this->site->getAllPurchaseItems($id);

                $this->data['inv']      = $this->purchases_model->getPurchaseByID($id);
                $this->data['returned'] = false;
                if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                    $this->data['returned'] = true;
                }
                $this->data['items']    = $items;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'purchases/update_status', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function order_from_aksestoko()
    {
        $this->db->trans_begin();
        try {
            $this->load->model('encryption_model');
            $company_id = $this->session->userdata('company_id');
            $company = $this->site->getCompanyByID($company_id);

            if($company->client_id != 'aksestoko'){
                throw new Exception("Can't do purchasing from AksesToko");
            }

            $data = [
                'issued_for' => 'sending_pos_session',
                'user_id'    => $this->session->userdata('user_id')
            ];

            $json = json_encode($data);
            $encrypt = $this->encryption_model->encrypt($json, APP_TOKEN);

            if (!$encrypt) {
                throw new Exception("Can't encrypt data.");
            }

            $this->db->trans_commit();
            redirect(prep_url(AKSESTOKO_DOMAIN) . "/" . aksestoko_route("aksestoko/auth/get_session"). "?session=" . urlencode($encrypt));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect('welcome');
        }
    }
}

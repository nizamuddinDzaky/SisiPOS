<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Sales_booking extends MY_Controller
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
        $this->lang->load('customers', $this->Settings->user_language);
        $this->lang->load('sales', $this->Settings->user_language);
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('sales_model');
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

    public function list_booking_sales($warehouse_id = null)
    {
        // $this->sma->checkMenuPermissions();
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

        $link_type = ['mb_sales_booking', 'mb_edit_booking_sale', 'mb_export_excel_sales_booking', 'mb_export_pdf_sales_booking'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('list_booking_sales')));
        $meta = array('page_title' => lang('list_booking_sales'), 'bc' => $bc);
        $this->page_construct('sales_booking/list_booking_sales', $meta, $this->data);
    }

    public function set_session_filter()
    {
        $filter_date_range = $this->input->post('filter_date_range');
        if ($filter_date_range)
            $this->session->set_userdata('filter_date_range', $filter_date_range);
    }

    public function getSalesBooking($warehouse_id = null)
    {
        // $this->sma->checkPermissions('list_booking_sales');
        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales_booking/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        // $detail_link = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $duplicate_link = anchor('sales_booking/add_booking_sale?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $edit_link = anchor('sales_booking/edit_booking_sale/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $close_link = "<a href='#' class='po' title='<b>" . lang("close_sale") . "</b>' data-content=\"<p>"
            . lang('close_confirm') . "</p><a class='btn btn-danger' href='" . site_url('sales/close_sale/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-close\"></i>"
            . lang('close_sale') . "</a>";
        // $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
        // . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        // . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        // . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li>' . $duplicate_link . '</li>
        <li>' . $payments_link . '</li>
        <li>' . $add_payment_link . '</li>
        <li>' . $add_delivery_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $pdf_link . '</li>
        <li>' . $email_link . '</li>
        <li>' . $close_link . '</li>

        </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');

        $filter_date_range = $this->session->userdata('filter_date_range');
        if ($filter_date_range) {
            $start_date = explode(" - ", $filter_date_range)[0];
            $end_date   = explode(" - ", $filter_date_range)[1];
        } else {
            $start_date = date('d/m/Y', strtotime('-7 days'));
            $end_date   = date('d/m/Y');
        }

        if ($this->Supplier) {
            $this->datatables
                ->select($this->db->dbprefix('purchases') . ".id, DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, {$this->db->dbprefix('purchases')}.reference_no, {$this->db->dbprefix('purchases')}.supplier, CONCAT(first_name,' ',last_name) as customer, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
                ->from("purchases")
                ->join('users', 'users.id=purchases.created_by', 'left')
                ->where('purchases.supplier_id', $this->session->userdata('company_id'));
        } else {
            $items = "( (SELECT 
            CASE
            WHEN SUM( si.quantity ) > SUM( si.sent_quantity ) AND SUM( si.sent_quantity ) = 0 THEN 'pending' 
            WHEN SUM( si.quantity ) > SUM( si.sent_quantity ) AND SUM( si.sent_quantity ) > 0 THEN 'partial' 
            WHEN SUM( si.quantity ) <= SUM( si.sent_quantity ) AND SUM( si.sent_quantity ) > 0 THEN 'done' 
            END AS delivery_status,
            si.sale_id 
            FROM
            sma_sale_items si
            join sma_sales s on s.id = si.sale_id and s.biller_id = " . $this->session->userdata('company_id') . "
            WHERE
            DATE_FORMAT(s.date, '%Y-%m-%d') >= '" . $this->sma->fld($start_date) . "'
            AND DATE_FORMAT(s.date, '%Y-%m-%d') <= '" . $this->sma->fld($end_date) . "'
            GROUP BY
            si.sale_id )) sma_item ";
            $this->datatables
                ->select(
                    $this->db->dbprefix('sales') . ".id as id, 
                    DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, 
                    if({$this->db->dbprefix('sales.client_id')} = 'atl', concat({$this->db->dbprefix('sales')}.reference_no, ' (', {$this->db->dbprefix('atl_orders.ordercode')} ,')'), {$this->db->dbprefix('sales')}.reference_no) as reference_no, 
                    sma_sales.customer as customer, 
                    IF({$this->db->dbprefix('sales')}.client_id = 'aksestoko', CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, ' (AksesToko)'), CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, if({$this->db->dbprefix('sales')}.client_id = 'atl', ' (AksesToko)', '') )) AS created_by, 
                    {$this->db->dbprefix('sales')}.sale_status, 
                    if({$this->db->dbprefix('sales.client_id')} = 'atl', {$this->db->dbprefix('atl_orders.payment_method')}, {$this->db->dbprefix('purchases')}.payment_method), 
                    if({$this->db->dbprefix('atl_orders.payment_method')} = 'kredit_pro', {$this->db->dbprefix('atl_kreditpro_status.status')}, IF({$this->db->dbprefix('purchases')}.payment_method = 'kredit_pro', CONCAT({$this->db->dbprefix('purchases')}.status, CONCAT('|', {$this->db->dbprefix('purchases')}.payment_status)), '-|-')) AS 'status_kredit_pro', 
                    {$this->db->dbprefix('sales')}.grand_total, 
                    {$this->db->dbprefix('sales')}.paid, 
                    ({$this->db->dbprefix('sales')}.grand_total-{$this->db->dbprefix('sales')}.paid) as balance, 
                    {$this->db->dbprefix('sales')}.payment_status, 
                    delivery_status, 
                    {$this->db->dbprefix('sales')}.attachment, 
                    {$this->db->dbprefix('sales')}.return_id"
                )
                ->from('sales');
            $this->datatables->join($this->db->dbprefix('atl_orders'), $this->db->dbprefix('atl_orders.sale_id') . ' = sales.id', 'left');
            $this->datatables->join($this->db->dbprefix('atl_kreditpro_status'), $this->db->dbprefix('atl_kreditpro_status.sale_id') . ' = sales.id', 'left');
            $this->datatables->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left');
            $this->datatables->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left');
            $this->datatables->join($this->db->dbprefix('purchases'), $this->db->dbprefix('purchases') . '.cf1=sales.reference_no AND ' . $this->db->dbprefix('purchases') . '.supplier_id=sales.biller_id', 'left');

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

        $this->datatables->where('DATE_FORMAT(sma_sales.date, "%Y-%m-%d") >= "' . $this->sma->fld($start_date) . '"');
        $this->datatables->where('DATE_FORMAT(sma_sales.date, "%Y-%m-%d") <= "' . $this->sma->fld($end_date) . '"');

        $this->datatables->where('sma_sales.is_deleted is null');
        $this->datatables->where('sma_sales.sale_type', 'booking');
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



    /* ------------------------------------------------------------------ */

    public function add_booking_sale($quote_id = null)
    {
        $this->db->trans_begin();
        try {
            // $this->sma->checkPermissions();
            $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;

            // check returned sale cannot be duplicate
            $inv = $this->sales_model->getInvoiceByID($sale_id);
            if ($inv && $inv->sale_status == 'returned') {
                throw new Exception(lang('sale_x_action'));
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
                $price_type = $this->input->post('price_type');

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
                    'price_type' => $price_type,
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

                krsort($data);
                krsort($booking);
                $addsale = $this->sales_model->addSaleBooking($data, $products, $payment, null, $booking);
            }


            if ($this->form_validation->run() == true && $addsale) {
                $this->db->trans_commit();
                $this->session->set_userdata('remove_slls', 1);
                if ($quote_id) {
                    $this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
                }
                $this->session->set_flashdata('message', lang("sale_added"));
                if ($this->input->post('create_delivery') && $sale_status == 'reserved')
                    $this->session->set_flashdata('create_delivery', $addsale);
                redirect("sales_booking/list_booking_sales");
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

                $gettop = $this->sales_model->getTOP();
                if ($gettop) {
                    foreach ($gettop as $k => $v) {
                        $top[$v->duration] = $v->duration;
                    }
                } else {
                    $top[] = "none";
                }

                $this->data['top'] = $top;
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['quote_id'] = $quote_id ? $quote_id : $sale_id;
                $this->data['billers'] = $this->site->getAllCompanies('biller');
                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
                $this->data['slnumber'] = ''; //$this->site->getReference('so');
                $this->data['payment_ref'] = ''; //$this->site->getReference('pay');

                $link_type = ['mb_add_booking_sale'];
                $this->load->model('db_model');
                $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
                foreach ($get_link as $val) {
                    $this->data[$val->type] = $val->uri;
                }

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales_booking/list_booking_sales'), 'page' => lang('list_booking_sale')), array('link' => '#', 'page' => lang('add_booking_sale')));
                $meta = array('page_title' => lang('add_booking_sale'), 'bc' => $bc);
                $this->page_construct('sales_booking/add_booking_sale', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* ------------------------------------------------------------------------ */

    public function edit_booking_sale($id = null)
    {
        $this->sales_model->cek_sales($id, 'sales/edit/', 'booking');

        $this->db->trans_begin();
        try {
            // $this->sma->checkPermissions();
            $this->load->helper('security');
            if ($this->input->get('id')) {
                $id   = $this->input->get('id');
            }
            $this->sma->transactionPermissions('sales', $id);
            $inv      = $this->sales_model->getInvoiceByID($id);
            $deliv    = $this->sales_model->getDeliveryBySaleID($id);

            if ($inv->sale_status == 'closed') {
                throw new \Exception(lang('close_cant_edit'));
            }
            if ($inv->sale_status == 'canceled') {
                throw new \Exception(lang('status_is_x_canceled'));
            }
            if ($deliv != false) {
                throw new \Exception(lang('delivery_available'));
            }
            if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
                throw new \Exception(lang('sale_x_action'));
            }

            if ($inv->client_id == 'aksestoko' || $inv->client_id == 'atl') {
                if ($inv->sale_status == 'reserved') {
                    throw new \Exception(lang('at_reserved_cant_edit'));
                }
            }

            if (!$this->session->userdata('edit_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            // $inv = 0;
            if (empty($inv)) {
                throw new \Exception(lang('sale_not_found'));
            }
            $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
            $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('biller', lang("biller"), 'required');
            $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
            $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');
            $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

            $this->data['inv']    = $this->sales_model->getInvoiceByID($id);
            $customer             = $this->site->getCompanyByID($inv->customer_id);
            $customer_id          = $this->sales_model->findCompanyByCf1AndCompanyId($inv->biller_id, $customer->cf1);
            $warehouse_cus        = $this->sales_model->findWarehouseCustomerByCustomerId($customer_id->id);
            if ($inv->warehouse_id != $warehouse_cus->default && $warehouse_cus->default != NULL) {
                $warehouse_customer             = $warehouse_cus->default;
            } else {
                $warehouse_customer             = $inv->warehouse_id;
            }
            $this->data['customer_warehouse']   = $warehouse_customer;
            $this->data['po']                   = $this->sales_model->getPurchasesByRefNo($this->data['inv']->reference_no, $this->data['inv']->biller_id);
            $this->data['user']                 = $this->site->getUser($this->data['inv']->created_by);
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
                    $this->session->set_flashdata('error', 'waiting payment or partial/full paid');
                    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
                }

                $warehouse_id       = $this->input->post('warehouse');
                $customer_id        = $this->input->post('customer');
                $biller_id          = $this->input->post('biller');
                $total_items        = $this->input->post('total_items');
                $sale_status        = $this->input->post('sale_status');
                $payment_status     = $this->input->post('payment_status');
                $payment_term       = $this->input->post('payment_term');
                $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
                $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                $charge             = $this->input->post('charge') ? $this->input->post('charge') : 0;
                $reason             = $this->input->post('reason') ? $this->sma->clear_tags($this->input->post('reason')) : "";
                $customer_details   = $this->site->getCompanyByID($customer_id);
                $customer           = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
                $biller_details     = $this->site->getCompanyByID($biller_id);
                $biller             = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                $note               = $this->sma->clear_tags($this->input->post('note'));
                $staff_note         = $this->sma->clear_tags($this->input->post('staff_note'));
                $sale_type          = !empty($this->input->post('sale_type')) ? 'booking' : null;
                $price_type         = $this->input->post('price_type');

                $total              = 0;
                $product_tax        = 0;
                $order_tax          = 0;
                $product_discount   = 0;
                $order_discount     = 0;
                $percentage         = '%';
                $i                  = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
                for ($r = 0; $r < $i; $r++) {
                    $item_id            = $_POST['product_id'][$r];
                    $item_type          = $_POST['product_type'][$r];
                    $item_code          = $_POST['product_code'][$r];
                    $item_name          = $_POST['product_name'][$r];
                    $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                    $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                    $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                    $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $item_unit          = $_POST['product_unit'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];

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

                        $product_tax    += $pr_item_tax;
                        $subtotal       = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                        $unit           = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_id'          => $item_id,
                            'product_code'        => $item_code,
                            'product_name'        => $item_name,
                            'product_type'        => $item_type,
                            'option_id'           => $item_option,
                            'net_unit_price'      => $item_net_price,
                            'unit_price'          => $this->sma->formatDecimal($item_net_price + $item_tax),
                            'quantity'            => $item_quantity,
                            'product_unit_id'     => $item_unit,
                            'product_unit_code'   => $unit->code,
                            'unit_quantity'       => $item_unit_quantity,
                            'warehouse_id'        => $warehouse_id,
                            'item_tax'            => $pr_item_tax,
                            'tax_rate_id'         => $pr_tax,
                            'tax'                 => $tax,
                            'discount'            => $item_discount,
                            'item_discount'       => $pr_item_discount,
                            'subtotal'            => $this->sma->formatDecimal($subtotal),
                            'serial_no'           => $item_serial,
                            'real_unit_price'     => $real_unit_price,
                        );

                        $booking[] = array(
                            'product_id'          => $item_id,
                            'warehouse_id'        => $warehouse_id,
                            'product_code'        => $item_code,
                            'product_name'        => $item_name,
                            'product_type'        => $item_type,
                            'quantity_order'      => $item_quantity,
                            'quantity_booking'    => $item_quantity,
                            'product_unit_id'     => $item_unit,
                            'product_unit_code'   => $unit ? $unit->code : null,
                            'client_id'           => null,
                            'created_at'          => date('Y-m-d H:i:s'),
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
                        $ods              = explode("%", $order_discount_id);
                        $order_discount   = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
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
                    'date'                => $date,
                    'reference_no'        => $reference,
                    'customer_id'         => $customer_id,
                    'customer'            => $customer,
                    'biller_id'           => $biller_id,
                    'biller'              => $biller,
                    'warehouse_id'        => $warehouse_id,
                    'note'                => $note,
                    'staff_note'          => $staff_note,
                    'total'               => $total,
                    'product_discount'    => $product_discount,
                    'order_discount_id'   => $order_discount_id,
                    'order_discount'      => $order_discount,
                    'total_discount'      => $total_discount,
                    'product_tax'         => $product_tax,
                    'order_tax_id'        => $order_tax_id,
                    'order_tax'           => $order_tax,
                    'total_tax'           => $total_tax,
                    'shipping'            => $this->sma->formatDecimal($shipping),
                    'grand_total'         => $grand_total,
                    'total_items'         => $total_items,
                    'payment_status'      => $payment_status,
                    'payment_term'        => $payment_term,
                    'due_date'            => $due_date,
                    'updated_by'          => $this->session->userdata('user_id'),
                    'updated_at'          => date('Y-m-d H:i:s'),
                    'charge'              => $charge,
                    'reason'              => $reason,
                    'sale_type'           => $sale_type,
                    'price_type'          => $price_type
                );

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
                    $photo = $this->upload->file_name;*/
                    $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                    $photo              = $uploadedImg->url;
                    $data['attachment'] = $photo;
                }
                // $this->sma->print_arrays($data, $products);
            }
            // print_r($this->input->post());die;
            krsort($data);
            krsort($booking);

            if ($this->form_validation->run() == true) {
                $updatesale = $this->sales_model->updateSaleBooking($sale_status, $id, $data, $products, $booking);

                if (!$updatesale) {
                    throw new Exception("update sale failed");
                }

                if (in_array($sale_status, ['confirmed', 'reserved', 'completed'])) {
                    $notify_type = "confirm_order";
                } else if (in_array($sale_status, ['canceled'])) {
                    $notify_type = "canceled_order";
                } else if ($charge != $this->sma->formatDecimal($inv->charge)) {
                    $notify_type = "new_update_price";
                }

                // else if(in_array($sale_status, ['reserved', 'completed'])){
                //     $notify_type = "accept_order";
                // }

                $this->load->model('socket_notification_model');
                if ($notify_type) {
                    $data_socket_notification = [
                        'company_id'        => $customer_id,
                        'transaction_id'    => 'SALE-' . $this->data['po']->id,
                        'customer_name'     => $customer,
                        'reference_no'      => $this->data['po']->cf1,
                        'price'             => '',
                        'type'              => $notify_type,
                        'to'                => 'aksestoko',
                        'note'              => $note,
                        'created_at'        => date('Y-m-d H:i:s')
                    ];
                    $this->socket_notification_model->addNotification($data_socket_notification);
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
                        $status_sms   = false;
                        $status_sms   = $this->site->send_sms_otp((string) $this->data['user']->phone, $message, false, 'notif');
                        $message_sms  = '|| sending sms notification failed';
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
                        $status_sms   = false;
                        $status_sms   = $this->site->send_wa_otp_wablas((string) $this->data['user']->phone, $message);
                        $message_sms  = '|| sending wa notification failed';
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

                if ($inv->client_id == 'atl') {
                    $this->load->model('Integration_atl_model', 'integration_atl');
                    $call_update_atl = $this->integration_atl->update_order_atl($id);
                    if (!$call_update_atl) {
                        throw new \Exception(lang('failed') . " -> Call API Update Order ATL");
                    }
                }

                $this->db->trans_commit();
                $this->session->set_userdata('remove_slls', 1);
                $this->session->set_flashdata('message', lang('sale_updated') . ' ' . @$message_sms);
                $this->session->set_flashdata($tipe, lang('delivery_added') . ' ' . @$message_notif);
                if ($this->input->post('create_delivery') && $sale_status == 'reserved')
                    $this->session->set_flashdata('create_delivery', $id);
                redirect($inv->pos ? 'pos/sales/list_booking_sales' : 'sales_booking/list_booking_sales');
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
                    $get_wh = $this->sales_model->getWarehouseProduct($item->warehouse_id, $item->product_id);

                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row = json_decode('{}');
                        $row->tax_method = 0;
                        $row->quantity = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    $row->qty_wh          = $row->quantity;
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->qty_wh          = ($get_wh->quantity == null) ? 0 : $get_wh->quantity;
                    $row->qty_book_wh     = ($get_wh->quantity_booking == null) ? 0 : $get_wh->quantity_booking;
                    $row->id              = $item->product_id;
                    $row->code            = $item->product_code;
                    $row->name            = $item->product_name;
                    $row->type            = $item->product_type;
                    $row->base_quantity   = $item->quantity;
                    $row->base_unit       = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                    $row->unit            = $item->product_unit_id;
                    $row->qty             = $item->unit_quantity;
                    $row->quantity        += $item->quantity;
                    $row->discount        = $item->discount ? $item->discount : '0';

                    if (is_null($inv->price_type) || $inv->price_type == 'cash') {
                        $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                        $row->real_unit_price = $item->real_unit_price;
                        if ($customer->price_group_id) {
                            if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                                $row->price_credit = $pr_group_price->price_kredit != 0 ? $pr_group_price->price_kredit : $row->credit_price;
                                $row->real_unit_price_credit = $row->price_credit;
                            } else {
                                $row->price_credit = $this->sma->formatDecimal($row->credit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                                $row->real_unit_price_credit = $row->price_credit;
                            }
                        } else {
                            $row->price_credit = $this->sma->formatDecimal($row->credit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                            $row->real_unit_price_credit = $row->price_credit;
                        }
                    } else {
                        $row->price           = $this->sma->formatDecimal($item->real_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                        $row->price_credit    = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                        $row->real_unit_price = $item->real_unit_price;
                        $row->real_unit_price_credit = $item->net_unit_price;
                    }
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                    $row->tax_rate        = $item->tax_rate_id;
                    $row->serial          = $item->serial_no;
                    $row->option          = $item->option_id;
                    $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);

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
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;
                    $pr[$ri] = array(
                        'id'            => $c,
                        'item_id'       => $row->id,
                        'label'         => $row->name . " (" . $row->code . ")",
                        'row'           => $row,
                        'combo_items'   => $combo_items,
                        'tax_rate'      => $tax_rate,
                        'units'         => $units,
                        'options'       => $options,
                        'client_id'     => $inv->client_id
                    );
                    $c++;
                }

                $gettop = $this->sales_model->getTOP();
                $pay_term = [];
                if ($gettop) {
                    foreach ($gettop as $k => $v) {
                        $pay_term[] = (int)$v->duration;
                        $top[$v->duration] = $v->duration;
                    }
                } else {
                    $top[0] = "none";
                }

                $this->data['pay_term']     = $pay_term;
                $this->data['top']          = $top;
                $this->data['inv_items']    = json_encode($pr);
                $this->data['id']           = $id;
                //$this->data['currencies'] = $this->site->getAllCurrencies();
                $this->data['billers']      = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
                $this->data['tax_rates']    = $this->site->getAllTaxRates();
                $this->data['warehouses']   = $this->site->getAllWarehouses();

                $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales_booking/list_booking_sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('edit_booking_sale')));
                $meta = array('page_title' => lang('edit_booking_sale'), 'bc' => $bc);
                $this->page_construct('sales_booking/edit_booking_sale', $meta, $this->data);
            }
        } catch (Exception $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function deliveries_booking()
    {
        // $this->sma->checkPermissions();

        $link_type = ['mb_delivery_order', 'mb_add_delivery_order', 'mb_edit_delivery_order', 'mb_export_excel_delivery_order', 'mb_export_pdf_delivery_order'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('deliveries')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->page_construct('sales_booking/deliveries_booking', $meta, $this->data);
    }

    public function set_session_filter_deliveries()
    {
        $filter_date_range = $this->input->post('filter_date_range_deliveries');
        if ($filter_date_range)
            $this->session->set_userdata('filter_date_range_deliveries', $filter_date_range);
    }

    public function getDeliveries_booking()
    {
        $this->sma->checkPermissions('deliveries_booking');

        $detail_link = anchor('sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        // $email_link = anchor('sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $edit_link = anchor('sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $return_link = anchor('sales/return_delivery/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_delivery'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $pdf_link = anchor('sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        // $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
        // . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_delivery/$1') . "'>"
        // . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        // . lang('delete_delivery') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $pdf_link . '</li>
        <li>' . $return_link . '</li>
        </ul>
        </div></div>';
        // <li>' . $delete_link . '</li>

        $this->load->library('datatables');

        $this->datatables
            ->select("
                    sma_deliveries.id AS id, 
                    sma_deliveries.date AS date, 
                    sma_deliveries.do_reference_no AS do_reference_no, 
                    sma_deliveries.sale_reference_no AS sale_reference_no, 
                    sma_deliveries.customer AS customer, 
                    sma_deliveries.address AS address, 
                    sma_deliveries.status AS status, 
                    sma_deliveries.attachment AS attachment, 
                    SUM(sma_delivery_items.bad_quantity) AS bad, 
                    sma_deliveries.is_reject AS is_reject, 
                    sma_deliveries.is_approval AS is_approval
                ")
            ->from('deliveries')
            ->join('sales', "sales.id = deliveries.sale_id", 'left')
            ->join('delivery_items', 'delivery_items.delivery_id = deliveries.id', 'left')
            ->where('sales.sale_type', 'booking')
            ->where('sales.is_deleted', null)
            ->where('deliveries.is_deleted', null);
        $this->datatables->group_by('deliveries.id');

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('deliveries.created_by', $this->session->userdata('user_id'));
        } else if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && $this->session->userdata('view_right')) {
            $this->datatables->where('sales.warehouse_id', $this->session->userdata('warehouse_id'));
        }

        $filter_date_range_deliveries = $this->session->userdata('filter_date_range_deliveries');
        if ($filter_date_range_deliveries) {
            $start_date = explode(" - ", $filter_date_range_deliveries)[0];
            $end_date   = explode(" - ", $filter_date_range_deliveries)[1];
        } else {
            $start_date = date('d/m/Y', strtotime('-7 days'));
            $end_date   = date('d/m/Y');
        }

        if ($this->input->get('sale_id')) {
            $this->datatables->where('sma_deliveries.sale_id', $this->input->get('sale_id'));
        } else {
            $this->datatables->where('DATE_FORMAT(sma_deliveries.date, "%Y-%m-%d") >= "' . $this->sma->fld($start_date) . '"');
            $this->datatables->where('DATE_FORMAT(sma_deliveries.date, "%Y-%m-%d") <= "' . $this->sma->fld($end_date) . '"');
        }
        if (!$this->Owner) {
            $this->datatables->where('sales.company_id', $this->session->userdata('company_id'));
        }

        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
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
                    // $this->sma->checkPermissions('delete');
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
                        ->SetCellValue('I1', lang('return_sale_ref'));

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
                            ->SetCellValue('I' . $row, $sale->return_sale_ref);

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

    public function combine_pdf($sales_id)
    {
        // $this->sma->checkPermissions('combine_pdf');

        $sale_id_closed = [];
        foreach ($sales_id as $v) {
            $sale_status = $this->db->select('sale_status')->get_where('sales', ['id' => $v])->row()->sale_status;
            if ($sale_status == 'closed') {
                $sale_id_closed[] = $v;
            }
        }

        foreach ($sale_id_closed as $id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['barcode']        = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer']       = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments']       = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller']         = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user']           = $this->site->getUser($inv->created_by);
            $this->data['warehouse']      = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv']            = $inv;
            $this->data['rows']           = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale']    = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows']    = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
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

    public function reject_bad_quantity($delivery_id)
    {
        $get = $this->db->get_where('deliveries', ['id' => $delivery_id])->row();
        if (is_null($get->is_reject)) {
            $is_reject = 1;
        } elseif ($get->is_reject == 2) {
            $is_reject = 3;
        }
        $param = ['is_reject' => $is_reject];
        $this->db->update('deliveries', $param, ['id' => $delivery_id]);
        $update = $this->db->affected_rows();
        if ($update > 0) {
            $delivery = $this->sales_model->getDeliveryByID($delivery_id);
            $sale = $this->sales_model->getSalesBySalesId($delivery->sale_id);
            $this->data['user'] = $this->site->getUser($sale->created_by);
            if ($this->site->checkAutoClose($delivery->sale_id)) {
                $this->sales_model->closeSale($delivery->sale_id);
            }

            $purchase = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->company_id);
            $this->load->model('socket_notification_model');
            $data_socket_notification = [
                'company_id'        => $sale->customer_id,
                'transaction_id'    => 'SALE-' . $purchase->id,
                'customer_name'     => '',
                'reference_no'      => $sale->reference_no . ' (' . $delivery->do_reference_no . ')',
                'price'             => '',
                'type'              => 'confirm_reject_delivery',
                'to'                => 'aksestoko',
                'note'              => '',
                'created_at'        => date('Y-m-d H:i:s')
            ];
            $this->socket_notification_model->addNotification($data_socket_notification);

            //START - Mengirim SMS notifikasi kepada retail toko AksesToko
            $message = $this->site->makeMessage('sms_notif_return_reject', [
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
            $inv    = $this->sales_model->getInvoiceByID($delivery->sale_id);
            $po     = $this->sales_model->getPurchasesByRefNo($inv->reference_no, $inv->biller_id);
            $notification   = [
                'title' => 'AksesToko - Pengembalian',
                'body'  => $message
            ];
            $data = [
                'click_action'   => 'FLUTTER_NOTIFICATION_CLICK',
                'title'          => 'AksesToko - Pengembalian',
                'body'           => $message,
                'type'           => 'sms_notif_return_reject',
                'id_pemesanan'   => $po->id,
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

            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', 'Bad quantity is rejected ' . @$message_sms);
            $this->session->set_flashdata($tipe, 'Bad quantity is rejected ' . @$message_notif);
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->session->set_flashdata('error', 'Failed to update');
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view($id = null)
    {
        // $this->sma->checkPermissions('index');
        $this->sales_model->cek_sales($id, 'sales/view/', 'booking');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissions('sales', $id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['sale_type']    = $inv->sale_type;
        $this->data['barcode']      = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']     = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']     = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']       = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']   = $this->site->getUser($inv->created_by);
        $this->data['updated_by']   = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']    = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']          = $inv;

        $this->data['return_sale']  = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']  = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']       = $this->sales_model->getPaypalSettings();
        $this->data['skrill']       = $this->sales_model->getSkrillSettings();
        $this->data['po']           = $this->sales_model->getPurchasesByRefNo($inv->reference_no, $inv->biller_id);

        $this->data['atl_order'] = $this->sales_model->getOrderAtlBySaleId($inv->id);
        $this->data['atl_kreditpro_status'] = $this->sales_model->getAtlKreditproStatus($this->data['atl_order']->orderid);


        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales_booking/list_booking_sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_sales_details'), 'bc' => $bc);
        $this->page_construct('sales_booking/view_booking', $meta, $this->data);
    }

    // ---------------------------- END OF PROMOTION---------------//

}

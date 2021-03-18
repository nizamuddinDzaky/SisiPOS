<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '\libraries\vendor\firebase\php-jwt\src\JWT.php';
use \Firebase\JWT\JWT;

class Services extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        date_default_timezone_set('Asia/Jakarta');
        parent::__construct($config);

        $this->lang->load('rest_controller', $this->Settings->user_language);
        $this->load->model('services_model', 'services_model');
        $this->load->model('pos_model', 'pos_model');
        $this->load->model('purchases_model', 'purchases_model');
        $this->load->model('integration_model');
        $this->key="pos_key";
        $this->expired= time() + (24*60*60);
        $this->now= time();

        // $this->load->library('form_validation');

        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : null;

        foreach (getallheaders() as $name => $value) {
            if ($name=='pos_api_key' || $name=='device_id' || $name=='username') {
                $this->headers_token[$name] = $value;
            }
            $this->headers[$name] = $value;
        }
        $this->headers_token['expired']=$this->expired;

        $method=$this->headers['method'];
        if ($method) {
            if ($this->headers['code']) {
                try {
                    $decode=JWT::decode($this->headers['code'], $this->key, array('HS256'));
                } catch (Exception $e) {
                    $this->response(array('status' => 'fail', 'response'=> REST_Controller::HTTP_BAD_REQUEST,'content' => 'Wrong token'));
                }
                if ($this->now>=$decode->expired) {
                    $this->_getToken($this->headers_token, $this->key);
                }
            } else {
                $this->_getToken($this->headers_token, $this->key);
            }
        } else {
            $this->response(array('status' => 'fail', 'response' => REST_Controller::HTTP_NOT_FOUND, 'messages' => 'No method'));
        }
    }

    // FUNCTION OF GET
    public function api_get()
    {
        $limit=10;
        if ($method=='get.user') {
            if (isset($this->headers['username']) && !empty($this->headers['username'])) {
                $data=$this->services_model->getUser($this->headers['username']);
                $this->response(array(
                    'status' => ($data ? 'success' : 'fail'),
                    'total'=>($data ? count($data) : 0),
                    'response' => ($data ? REST_Controller::HTTP_OK : REST_Controller::HTTP_BAD_REQUEST),
                    'content' => ($data ? $data : null) ));
            } else {
                // if($this->headers['page']){
                //     $data=$this->services_model->getAllUser($this->headers['page']);
                // }else{
                $data=$this->services_model->getAllUser();
                // }
                $this->response(array(
                    'status' => 'success',
                    'total'=>count($data),
                    'response' => REST_Controller::HTTP_OK,
                    'content' => $data));
            }
        } elseif ($method=='get.product') {
            $products = $this->db->get('products')->result();
            $this->response(array('status' => 'success', 'response' => REST_Controller::HTTP_OK, 'content' => ($products ? $products : null) ));
        } elseif ($method=='get.warehouses') {
            $data=$this->services_model->getAllWarehouses();
            $this->response(array('status' => 'success', 'total'=>count($data), 'response' => REST_Controller::HTTP_OK, 'content' => $data));
        } elseif ($this->headers['method']=='get.warehouse.product') {
            $flag=0;
            if ($this->headers['page']) {
                $containToken=JWT::decode($this->headers['page'], $this->key, array('HS256'));
                $data = $this->services_model->getAllWarehousesProducts($limit, $containToken->offset);
                $lastData=$this->services_model->getLastWarehousesProducts();

                $next['id'] = $data[$limit-1]->id;
                $next['offset'] = ($containToken->offset)+10;
                $nextPage = JWT::encode($next, $this->key);

                for ($i=0; $i < count($data); $i++) {
                    if ($data[$i]->id==$lastData[0]->id) {
                        $flag=1;
                        break 1;
                    } else {
                        $flag=0;
                    }
                }

                if ($flag==1) {
                    $this->response(array('status' => 'success','total'=>count($data), 'response' => REST_Controller::HTTP_OK, 'content' => $data));
                } else {
                    $this->response(array('status' => 'success','total'=>count($data), 'next-page' => $nextPage ,'response' => REST_Controller::HTTP_OK, 'content' => $data));
                }
            } else {
                $data = $this->services_model->getAllWarehousesProducts($limit);
                $next['id'] = $data[$limit-1]->id;
                $next['offset'] = $limit;
                $nextPage = JWT::encode($next, $this->key);
                $this->response(array('status' => 'success','total'=>count($data), 'next-page' => $nextPage ,'response' => REST_Controller::HTTP_OK, 'content' => $data));
            }
        } else {
            $this->response(array('status' => 'fail', 'response' => REST_Controller::HTTP_METHOD_NOT_ALLOWED, 'messages' => lang('text_rest_unknown_method')));
        }
    }

    // FUNCTION OF POST
    public function api_post()
    {
        if ($method=='post.add.user') {
            if ($this->headers['username'] && $this->headers['password'] && $this->headers['email']) {
                if (!filter_var($this->headers['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->response(array('status'=>'fail','response'=> REST_Controller::HTTP_FORBIDDEN,'messages'=>'invalid format email'));
                } else {
                    $data= array(
                        'ip_address' => $this->_getIPAddress(),
                        'username' => $this->headers['username'],
                        'password' => md5($this->headers['password']),
                        'email' => $this->headers['email'],
                        'created_on'=> time(),
                        'group_id'=> 5);

                    $cek=$this->services_model->addUser($data);

                    $this->response(array(
                        'status'=> ($cek? 'success': 'fail'),
                        'response'=> ($cek? REST_Controller::HTTP_ACCEPTED: REST_Controller::HTTP_BAD_REQUEST),
                        'messages'=> ($cek? 'input data berhasil': 'terdapat kesalahan. data belum masuk')
                        ));
                }
            } else {
                $this->response(array('status'=>'fail','response'=> REST_Controller::HTTP_BAD_REQUEST,'messages'=>'terdapat data yang masih kosong'));
            }
        }

        /* ---------------------------------------- S T A R T   N E W   M E T H O D ---------------------------------------- */
        elseif ($this->headers['method']=='post.purchase') {
        }

        /* ---------------------------------------- S T A R T   N E W   M E T H O D ---------------------------------------- */
        elseif ($this->headers['method']=='post.sale') {
            $this->session->set_userdata('identity', $this->headers['username']);
            // if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            //     $this->session->set_flashdata('warning', lang('please_update_settings'));
            //     redirect('pos/settings');
            // }
            // if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            //     $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            //     $this->session->set_userdata($register_data);
            // } else {
            //     $this->session->set_flashdata('error', lang('register_not_open'));
            //     redirect('pos/open_register');
            // }

            // $this->data['sid'] = $this->post('suspend_id') ? $this->post('suspend_id') : $sid;
            // $did = $this->post('delete_id') ? $this->post('delete_id') : NULL;
            // $suspend = $this->post('suspend') ? TRUE : FALSE;
            // $count = $this->post('count') ? $this->post('count') : NULL;

            // $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
            // $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
            // $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');

            if ($this->post('warehouse') && $this->post('customer') && $this->post('biller')) {
                $date = date('Y-m-d H:i:s');
                $warehouse_id = $this->post('warehouse');
                $customer_id = $this->post('customer');
                $biller_id = $this->post('biller');
                $total_items = $this->post('total_items');
                $sale_status = 'completed';
                $payment_status = 'due';
                $payment_term = 0;
                $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
                $shipping = $this->post('shipping') ? $this->post('shipping') : 0;
                $customer_details = $this->site->getCompanyByID($customer_id);
                $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
                $biller_details = $this->site->getCompanyByID($biller_id);
                $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                $note = $this->sma->clear_tags($this->post('pos_note'));
                $staff_note = $this->sma->clear_tags($this->post('staff_note'));
                $reference = $this->site->getReference('pos');

                $total = 0;
                $product_tax = 0;
                $order_tax = 0;
                $product_discount = 0;
                $order_discount = 0;
                $percentage = '%';
                $i = $this->post('product_code') ? sizeof($this->post('product_code')) : 0;
                for ($r = 0; $r < $i; $r++) {
                    $item_id = $this->post('product_id')[$r];
                    $item_type = $this->post('product_type')[$r];
                    $item_code = $this->post('product_code')[$r];
                    $item_name = $this->post('product_name')[$r];
                    $item_option = isset($this->post('product_option')[$r]) && $this->post('product_option')[$r] != 'false' ? $this->post('product_option')[$r] : null;
                    $real_unit_price = $this->sma->formatDecimal($this->post('real_unit_price')[$r]);
                    $unit_price = $this->sma->formatDecimal($this->post('unit_price')[$r]);
                    $item_unit_quantity = $this->post('quantity')[$r];
                    $item_serial = isset($this->post('serial')[$r]) ? $this->post('serial')[$r] : '';
                    $item_tax_rate = isset($this->post('product_tax')[$r]) ? $this->post('product_tax')[$r] : null;
                    $item_discount = isset($this->post('product_discount')[$r]) ? $this->post('product_discount')[$r] : null;
                    $item_unit = $this->post('product_unit')[$r];
                    $item_quantity = $this->post('product_base_quantity')[$r];

                    if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                        $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : null;
                        // $unit_price = $real_unit_price;
                        $pr_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float)($pds[0])) / 100), 4);
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
                            $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        }

                        $product_tax += $pr_item_tax;
                        $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                        $unit = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_id'      => $item_id,
                            'product_code'    => $item_code,
                            'product_name'    => $item_name,
                            'product_type'    => $item_type,
                            'option_id'       => $item_option,
                            'net_unit_price'  => $item_net_price,
                            'unit_price'      => $this->sma->formatDecimal($item_net_price + $item_tax),
                            'quantity'        => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $unit ? $unit->code : null,
                            'unit_quantity' => $item_unit_quantity,
                            'warehouse_id'    => $warehouse_id,
                            'item_tax'        => $pr_item_tax,
                            'tax_rate_id'     => $pr_tax,
                            'tax'             => $tax,
                            'discount'        => $item_discount,
                            'item_discount'   => $pr_item_discount,
                            'subtotal'        => $this->sma->formatDecimal($subtotal),
                            'serial_no'       => $item_serial,
                            'real_unit_price' => $real_unit_price,
                            );

                        $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    }
                }
                if (empty($products)) {
                    $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } elseif ($this->pos_settings->item_order == 1) {
                    krsort($products);
                }

                if ($this->post('discount')) {
                    $order_discount_id = $this->post('discount');
                    $opos = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float)($ods[0])) / 100), 4);
                    } else {
                        $order_discount = $this->sma->formatDecimal($order_discount_id);
                    }
                } else {
                    $order_discount_id = null;
                }
                $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

                if ($this->Settings->tax2) {
                    $order_tax_id = $this->post('order_tax');
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
                $rounding = 0;
                if ($this->pos_settings->rounding) {
                    $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                    $rounding = $this->sma->formatMoney($round_total - $grand_total);
                }

                $data = array('date'              => $date,
                  'reference_no'      => $reference,
                  'customer_id'       => $customer_id,
                  'customer'          => $customer,
                  'biller_id'         => $biller_id,
                  'biller'            => $biller,
                  'warehouse_id'      => $warehouse_id,
                  'note'              => $note,
                  'staff_note'        => $staff_note,
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
                  'total_items'       => $total_items,
                  'sale_status'       => $sale_status,
                  'payment_status'    => $payment_status,
                  'payment_term'      => $payment_term,
                  'rounding'          => $rounding,
                  'pos'               => 1,
                  'paid'              => $this->post('amount-paid') ? $this->post('amount-paid') : 0,
                  'created_by'        => 3,//$this->session->userdata('user_id'),
                  );

                if (!$suspend) {
                    $p = $this->post('amount') ? sizeof($this->post('amount')) : 0;
                    $paid = 0;
                    for ($r = 0; $r < $p; $r++) {
                        if (isset($this->post('amount')[$r]) && !empty($this->post('amount')[$r]) && isset($this->post('paid_by')[$r]) && !empty($this->post('paid_by')[$r])) {
                            $amount = $this->sma->formatDecimal($this->post('balance_amount')[$r] > 0 ? $this->post('amount')[$r] - $this->post('balance_amount')[$r] : $this->post('amount')[$r]);
                            if ($this->post('paid_by')[$r] == 'deposit') {
                                if (! $this->site->check_customer_deposit($customer_id, $amount)) {
                                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            }
                            if ($this->post('paid_by')[$r] == 'gift_card') {
                                $gc = $this->site->getGiftCardByNO($this->post('paying_gift_card_no')[$r]);
                                $amount_paying = $this->post('amount')[$r] >= $gc->balance ? $gc->balance : $this->post('amount')[$r];
                                $gc_balance = $gc->balance - $amount_paying;
                                $payment[] = array(
                                    'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                    'amount'       => $amount,
                                    'paid_by'      => $this->post('paid_by')[$r],
                                    'cheque_no'    => $this->post('cheque_no')[$r],
                                    'cc_no'        => $this->post('paying_gift_card_no')[$r],
                                    'cc_holder'    => $this->post('cc_holder')[$r],
                                    'cc_month'     => $this->post('cc_month')[$r],
                                    'cc_year'      => $this->post('cc_year')[$r],
                                    'cc_type'      => $this->post('cc_type')[$r],
                                    'cc_cvv2'      => $this->post('cc_cvv2')[$r],
                                    'created_by'   => 3,//$this->session->userdata('user_id'),
                                    'type'         => 'received',
                                    'note'         => $this->post('payment_note')[$r],
                                    'pos_paid'     => $this->post('amount')[$r],
                                    'pos_balance'  => $this->post('balance_amount')[$r],
                                    'gc_balance'  => $gc_balance,
                                    );
                            } else {
                                $payment[] = array(
                                    'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                    'amount'       => $amount,
                                    'paid_by'      => $this->post('paid_by')[$r],
                                    'cheque_no'    => $this->post('cheque_no')[$r],
                                    'cc_no'        => $this->post('cc_no')[$r],
                                    'cc_holder'    => $this->post('cc_holder')[$r],
                                    'cc_month'     => $this->post('cc_month')[$r],
                                    'cc_year'      => $this->post('cc_year')[$r],
                                    'cc_type'      => $this->post('cc_type')[$r],
                                    'cc_cvv2'      => $this->post('cc_cvv2')[$r],
                                    'created_by'   => 3,//$this->session->userdata('user_id'),
                                    'type'         => 'received',
                                    'note'         => $this->post('payment_note')[$r],
                                    'pos_paid'     => $this->post('amount')[$r],
                                    'pos_balance'  => $this->post('balance_amount')[$r],
                                    );
                            }
                        }
                    }
                }

                if (!isset($payment) || empty($payment)) {
                    $payment = array();
                }

                // $this->sma->print_arrays($data, $products, $payment);
            }

            if ($this->post('warehouse') && $this->post('customer') && $this->post('biller') && !empty($products) && !empty($data)) {
                if ($suspend) {
                    $data['suspend_note'] = $this->post('suspend_note');
                    if ($this->pos_model->suspendSale($data, $products, $did)) {
                        $this->session->set_userdata('remove_posls', 1);
                        $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                        // redirect("pos");
                        $this->response(array('status' => 'success', 'response'=> REST_Controller::HTTP_OK,'content' => 'Masuk ke Suspend' ));
                    }
                } else {
                    if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                        /*$this->session->set_userdata('remove_posls', 1);*/
                        /*$msg = $this->lang->line("sale_added");
                        if (!empty($sale['message'])) {
                            foreach ($sale['message'] as $m) {
                                $msg .= '<br>' . $m;
                            }
                        }
                        //echo $msg; die();
                        $this->session->set_flashdata('message', $msg);*/
                        $this->response(array('status' => 'success', 'response'=> REST_Controller::HTTP_OK,'content' => 'Success Input Data Sale Transaction' ));
                        // redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id']);
                    }
                }
            }
        }

        /* ---------------------------------------- S T A R T   N E W   M E T H O D ---------------------------------------- */
        elseif ($method=='post.purchases.transaction') {
            $reference = $this->headers['reference_no'] ? $this->headers['reference_no'] : $this->site->getReference('po');
            $date = date('Y-m-d H:i:s');
            $warehouse_id = $this->headers['warehouse'];
            $supplier_id = $this->headers['supplier'];
            $status = $this->headers['status'];
            $shipping = $this->headers['shipping'] ? $this->headers['shipping'] : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->headers['note']);
            $payment_term = $this->headers['payment_term'];
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';

            $arr_product=$this->_convertToArray($this->headers['product']);
            $arr_net_cost=$this->_convertToArray($this->headers['net_cost']);
            $arr_unit_cost=$this->_convertToArray($this->headers['unit_cost']);
            $arr_real_unit_cost=$this->_convertToArray($this->headers['real_unit_cost']);
            $arr_quantity=$this->_convertToArray($this->headers['quantity']);
            $arr_product_option=$this->_convertToArray($this->headers['product_option']);
            $arr_product_tax=$this->_convertToArray($this->headers['product_tax']);
            $arr_product_discount=$this->_convertToArray($this->headers['product_discount']);
            $arr_expiry=$this->_convertToArray($this->headers['expiry']);
            $arr_part_no=$this->_convertToArray($this->headers['part_no']);
            $arr_product_unit=$this->_convertToArray($this->headers['product_unit']);
            $arr_product_base_quantity=$this->_convertToArray($this->headers['product_base_quantity']);

            $i = sizeof($arr_product);
            for ($r = 0; $r < $i; $r++) {
                $item_code = $arr_product[$r];
                $item_net_cost = $this->sma->formatDecimal($arr_net_cost[$r]);
                $unit_cost = $this->sma->formatDecimal($arr_unit_cost[$r]);
                $real_unit_cost = $this->sma->formatDecimal($arr_real_unit_cost[$r]);
                $item_unit_quantity = $arr_quantity[$r];
                $item_option = isset($arr_product_option[$r]) && $arr_product_option[$r] != 'false' ? $arr_product_option[$r] : null;
                $item_tax_rate = isset($arr_product_tax[$r]) ? $arr_product_tax[$r] : null;
                $item_discount = isset($arr_product_discount[$r]) ? $arr_product_discount[$r] : null;
                $item_expiry = (isset($arr_expiry[$r]) && !empty($arr_expiry[$r])) ? $this->sma->fsd($arr_expiry[$r]) : null;
                $supplier_part_no = (isset($arr_part_no[$r]) && !empty($arr_part_no[$r])) ? $arr_part_no[$r] : null;
                $item_unit = $arr_product_unit[$r];
                $item_quantity = $arr_product_base_quantity[$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
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
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'quantity_received' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'supplier_part_no' => $supplier_part_no,
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
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);
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

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
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
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
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
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration_model->upload_files($_FILES['document']);
                $photo              = $uploadedImg->url;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);

            if ($this->purchases_model->addPurchase($data, $products)) {
                $this->session->set_userdata('remove_pols', 1);
                $this->session->set_flashdata('message', $this->lang->line("purchase_added"));

                $this->response(array(
                    'status'=> 'success',
                    'response'=> REST_Controller::HTTP_ACCEPTED,
                    'messages'=> getallheaders()
                    ));
            } else {
                $this->response(array(
                    'status'=> 'fail',
                    'response'=> REST_Controller::HTTP_ACCEPTED,
                    'messages'=> getallheaders()
                    ));
            }
        }

        /* ---------------------------------------- S T A R T   N E W   M E T H O D ---------------------------------------- */
        elseif ($method=='post.sale.transaction') {
            $this->session->set_userdata('identity', $this->headers['username']);
            // $this->data['sid'] = $this->headers['suspend_id'];
            // $did = $this->headers['delete_id'] ? $this->headers['delete_id'] : NULL;
            // $suspend = $this->headers['suspend'] ? TRUE : FALSE;
            // $count = $this->headers['count'] ? $this->headers['count'] : NULL;

            //validate form input
            // $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
            // $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
            // $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');

            if ($this->headers['customer'] && $this->headers['biller'] && $this->headers['warehouse']) {
                $date = date('Y-m-d H:i:s');
                $warehouse_id = $this->headers['warehouse'];
                $customer_id = $this->headers['customer'];
                $biller_id = $this->headers['biller'];
                $total_items = $this->headers['total_items'];
                $sale_status = 'completed';
                $payment_status = 'due';
                $payment_term = 0;
                $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
                $shipping = $this->headers['shipping'] ? $this->headers['shipping'] : 0;
                $customer_details = $this->site->getCompanyByID($customer_id);
                $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
                $biller_details = $this->site->getCompanyByID($biller_id);
                $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                $note = $this->sma->clear_tags($this->headers['pos_note']);
                $staff_note = $this->sma->clear_tags($this->headers['staff_note']);
                $reference = $this->site->getReference('pos');

                $total = 0;
                $product_tax = 0;
                $order_tax = 0;
                $product_discount = 0;
                $order_discount = 0;
                $percentage = '%';

                $i = isset($this->headers['product_code']) ? sizeof($this->headers['product_code']) : 0;
                for ($r = 0; $r < $i; $r++) {
                    $item_id = $this->headers['product_id'][$r];
                    $item_type = $this->headers['product_type'][$r];
                    $item_code = $this->headers['product_code'][$r];
                    $item_name = $this->headers['product_name'][$r];
                    $item_option = isset($this->headers['product_option'][$r]) && $this->headers['product_option'][$r] != 'false' ? $this->headers['product_option'][$r] : null;
                    $real_unit_price = $this->sma->formatDecimal($this->headers['real_unit_price'][$r]);
                    $unit_price = $this->sma->formatDecimal($this->headers['unit_price'][$r]);
                    $item_unit_quantity = $this->headers['quantity'][$r];
                    $item_serial = isset($this->headers['serial'][$r]) ? $this->headers['serial'][$r] : '';
                    $item_tax_rate = isset($this->headers['product_tax'][$r]) ? $this->headers['product_tax'][$r] : null;
                    $item_discount = isset($this->headers['product_discount'][$r]) ? $this->headers['product_discount'][$r] : null;
                    $item_unit = $this->headers['product_unit'][$r];
                    $item_quantity = $this->headers['product_base_quantity'][$r];

                    // $item_id = $this->headers['product_id'];
                    // $item_type = $this->headers['product_type'];
                    // $item_code = $this->headers['product_code'];
                    // $item_name = $this->headers['product_name'];
                    // $item_option = isset($this->headers['product_option']) && $this->headers['product_option'] != 'false' ? $this->headers['product_option'] : NULL;
                    // $real_unit_price = $this->sma->formatDecimal($this->headers['real_unit_price']);
                    // $unit_price = $this->sma->formatDecimal($this->headers['unit_price']);
                    // $item_unit_quantity = $this->headers['quantity'];
                    // $item_serial = isset($this->headers['serial']) ? $this->headers['serial'] : '';
                    // $item_tax_rate = isset($this->headers['product_tax']) ? $this->headers['product_tax'] : NULL;
                    // $item_discount = isset($this->headers['product_discount']) ? $this->headers['product_discount'] : NULL;
                    // $item_unit = $this->headers['product_unit'];
                    // $item_quantity = $this->headers['product_base_quantity'];

                    if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                        $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : null;
                        // $unit_price = $real_unit_price;
                        $pr_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float)($pds[0])) / 100), 4);
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
                            $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        }

                        $product_tax += $pr_item_tax;
                        $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                        $unit = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_id'      => $item_id,
                            'product_code'    => $item_code,
                            'product_name'    => $item_name,
                            'product_type'    => $item_type,
                            'option_id'       => $item_option,
                            'net_unit_price'  => $item_net_price,
                            'unit_price'      => $this->sma->formatDecimal($item_net_price + $item_tax),
                            'quantity'        => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $unit ? $unit->code : null,
                            'unit_quantity' => $item_unit_quantity,
                            'warehouse_id'    => $warehouse_id,
                            'item_tax'        => $pr_item_tax,
                            'tax_rate_id'     => $pr_tax,
                            'tax'             => $tax,
                            'discount'        => $item_discount,
                            'item_discount'   => $pr_item_discount,
                            'subtotal'        => $this->sma->formatDecimal($subtotal),
                            'serial_no'       => $item_serial,
                            'real_unit_price' => $real_unit_price,
                            );

                        $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    }
                }
                if (empty($products)) {
                    // $this->form_validation->set_rules('product', lang("order_items"), 'required');
                } elseif ($this->pos_settings->item_order == 1) {
                    krsort($products);
                }

                if ($this->input->post('discount')) {
                    $order_discount_id = $this->input->post('discount');
                    $opos = strpos($order_discount_id, $percentage);
                    if ($opos !== false) {
                        $ods = explode("%", $order_discount_id);
                        $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float)($ods[0])) / 100), 4);
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
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
                $rounding = 0;
                if ($this->pos_settings->rounding) {
                    $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                    $rounding = $this->sma->formatMoney($round_total - $grand_total);
                }
                $data = array('date'              => $date,
                  'reference_no'      => $reference,
                  'customer_id'       => $customer_id,
                  'customer'          => $customer,
                  'biller_id'         => $biller_id,
                  'biller'            => $biller,
                  'warehouse_id'      => $warehouse_id,
                  'note'              => $note,
                  'staff_note'        => $staff_note,
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
                  'total_items'       => $total_items,
                  'sale_status'       => $sale_status,
                  'payment_status'    => $payment_status,
                  'payment_term'      => $payment_term,
                  'rounding'          => $rounding,
                  'pos'               => 1,
                  'paid'              => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                  'created_by'        => 3//$this->session->userdata('user_id'),
                  );

                if (!$suspend) {
                    $p = isset($this->headers['amount']) ? sizeof($this->headers['amount']) : 0;
                    $paid = 0;
                    for ($r = 0; $r < $p; $r++) {
                        if (isset($this->headers['amount'][$r]) && !empty($this->headers['amount'][$r]) && isset($this->headers['paid_by'][$r]) && !empty($this->headers['paid_by'][$r])) {
                            // if (isset($this->headers['amount']) && !empty($this->headers['amount']) && isset($this->headers['paid_by']) && !empty($this->headers['paid_by'])) {
                            $amount = $this->sma->formatDecimal($this->headers['balance_amount'][$r] > 0 ? $this->headers['amount'][$r] - $this->headers['balance_amount'][$r] : $this->headers['amount'][$r]);
                            // $amount = $this->sma->formatDecimal($this->headers['balance_amount'] > 0 ? $this->headers['amount'] - $this->headers['balance_amount'] : $this->headers['amount']);
                            if ($this->headers['paid_by'][$r] == 'deposit') {
                                // if ($this->headers['paid_by'] == 'deposit') {
                                if (! $this->site->check_customer_deposit($customer_id, $amount)) {
                                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            }
                            if ($this->headers['paid_by'][$r] == 'gift_card') {
                                // if ($this->headers['paid_by'] == 'gift_card') {
                                $gc = $this->site->getGiftCardByNO($this->headers['paying_gift_card_no'][$r]);
                                // $gc = $this->site->getGiftCardByNO($this->headers['paying_gift_card_no']);
                                $amount_paying = $this->headers['amount'][$r] >= $gc->balance ? $gc->balance : $this->headers['amount'][$r];
                                // $amount_paying = $this->headers['amount'] >= $gc->balance ? $gc->balance : $this->headers['amount'];
                                $gc_balance = $gc->balance - $amount_paying;
                                $payment[] = array(
                                    'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                    'amount'       => $amount,
                                    'paid_by'      => $this->headers['paid_by'][$r],
                                    'cheque_no'    => $this->headers['cheque_no'][$r],
                                    'cc_no'        => $this->headers['paying_gift_card_no'][$r],
                                    'cc_holder'    => $this->headers['cc_holder'][$r],
                                    'cc_month'     => $this->headers['cc_month'][$r],
                                    'cc_year'      => $this->headers['cc_year'][$r],
                                    'cc_type'      => $this->headers['cc_type'][$r],
                                    'cc_cvv2'      => $this->headers['cc_cvv2'][$r],
                                    'created_by'   => 3,//$this->session->userdata('user_id'),
                                    'type'         => 'received',
                                    'note'         => $this->headers['payment_note'][$r],
                                    'pos_paid'     => $this->headers['amount'][$r],
                                    'pos_balance'  => $this->headers['balance_amount'][$r],
                                    'gc_balance'  => $gc_balance,
                                    );
                            } else {
                                $payment[] = array(
                                    'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                    'amount'       => $amount,
                                    'paid_by'      => $this->headers['paid_by'][$r],
                                    'cheque_no'    => $this->headers['cheque_no'][$r],
                                    'cc_no'        => $this->headers['cc_no'][$r],
                                    'cc_holder'    => $this->headers['cc_holder'][$r],
                                    'cc_month'     => $this->headers['cc_month'][$r],
                                    'cc_year'      => $this->headers['cc_year'][$r],
                                    'cc_type'      => $this->headers['cc_type'][$r],
                                    'cc_cvv2'      => $this->headers['cc_cvv2'][$r],
                                    'created_by'   => 3,//$this->session->userdata('user_id'),
                                    'type'         => 'received',
                                    'note'         => $this->headers['payment_note'][$r],
                                    'pos_paid'     => $this->headers['amount'][$r],
                                    'pos_balance'  => $this->headers['balance_amount'][$r],
                                    );
                            }
                        }
                    }
                }
                if (!isset($payment) || empty($payment)) {
                    $payment = array();
                }

                // $this->sma->print_arrays($data, $products, $payment);
            }

            if ($this->headers['customer'] && $this->headers['biller'] && $this->headers['warehouse'] && !empty($products) && !empty($data)) {
                if ($suspend) {
                    $data['suspend_note'] = $this->input->post('suspend_note');
                    if ($this->pos_model->suspendSale($data, $products, $did)) {
                        $this->session->set_userdata('remove_posls', 1);
                        $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                        redirect("pos");
                    }
                } else {
                    if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                        $this->session->set_userdata('remove_posls', 1);
                        $msg = $this->lang->line("sale_added");
                        if (!empty($sale['message'])) {
                            foreach ($sale['message'] as $m) {
                                $msg .= '<br>' . $m;
                            }
                        }
                        //echo $msg; die();
                        $this->session->set_flashdata('message', $msg);
                        // redirect($this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id']);
                        $this->response(array(
                            'status'=> 'success',
                            'response'=> REST_Controller::HTTP_ACCEPTED,
                            'messages'=> getallheaders()
                            ));
                    }
                }
            }



            // $this->session->set_userdata('identity',$this->headers['username']);
/*            $this->load->library('../controllers/pos');
            $this->pos->getSales();*/
/*            $id_delete=$this->headers['delete_id'];
            $suspend=$this->headers['suspend'];
            $this->input->get('suspend_id');
            $count=$this->headers['count'];
            $warehouse_id = $this->headers['warehouse'];
            $customer_id = $this->headers['customer'];
            $biller_id = $this->headers['biller'];
            $total_items = $this->headers['total_items'];
            $shipping = $this->headers['shipping'];
            $pos_note=$this->headers['pos_note'];
            $staff_note=$this->headers['staff_note'];

            $product_code=$this->headers['product_code'];
            $amount_paid=$this->headers['amount-paid'];*/

            // $warehouse_id = $this->headers['warehouse'];
            // $customer_id = $this->headers['customer'];
            // $biller_id = $this->headers['biller'];

            // $amount=$this->headers['amount'];
            // $paid_by=$this->headers['paid_by'];
            // $balance_amount=$this->headers['balance_amount'];

            // $cheque_no=$this->headers['cheque_no'];
            // $paying_gift_card_no=$this->headers['paying_gift_card_no'];
            // $cc_holder=$this->headers['cc_holder'];
            // $cc_month=$this->headers['cc_month'];
            // $cc_year=$this->headers['cc_year'];
            // $cc_type=$this->headers['cc_type'];
            // $cc_cvv2=$this->headers['cc_cvv2'];
            // $cc_no=$this->headers['cc_no'];
            // $payment_note=$this->headers['payment_note'];

/*            $attr=array('id'=>'formIndex' , 'name'=>'formIndex' );
            echo form_open('pos',$attr);
            echo "<input type='hidden' value='".$warehouse_id."' name='warehouse'>";
            echo "<input type='hidden' value='".$customer_id."' name='customer'>";
            echo "<input type='hidden' value='".$biller_id."' name='biller'>";

            echo "<input type='hidden' value='".$amount."' name='amount[]'>";
            echo "<input type='hidden' value='".$paid_by."' name='paid_by[]'>";
            echo "<input type='hidden' value='".$balance_amount."' name='balance_amount[]'>";
            echo "<input type='hidden' value='".$cheque_no."' name='cheque_no[]'>";
            echo "<input type='hidden' value='".$paying_gift_card_no."' name='paying_gift_card_no[]'>";
            echo "<input type='hidden' value='".$cc_holder."' name='cc_holder[]'>";
            echo "<input type='hidden' value='".$cc_month."' name='cc_month[]'>";
            echo "<input type='hidden' value='".$cc_year."' name='cc_year[]'>";
            echo "<input type='hidden' value='".$cc_type."' name='cc_type[]'>";
            echo "<input type='hidden' value='".$cc_cvv2."' name='cc_cvv2[]'>";
            echo "<input type='hidden' value='".$cc_no."' name='cc_no[]'>";
            echo "<input type='hidden' value='".$payment_note."' name='payment_note[]'>";
            echo "<input type='submit'>";
            echo form_close();*/
        }
    }

    // FUNCTION OF UPDATE
    public function api_put()
    {
        if ($method=='put.user') {
            if ($this->headers['username']) {
                $data = array(
                    'username'  => $this->headers['username'],
                    'password'  => $this->headers['password'],
                    'email'     => $this->headers['email']);
                $this->db->where('username', $this->headers['username']);

                $update = $this->db->update('users', $data);
                
                $this->response(array(
                    'status'=> ($update? 'success': 'fail'),
                    'response'=> ($update? REST_Controller::HTTP_ACCEPTED: REST_Controller::HTTP_BAD_REQUEST),
                    'messages'=> ($update? 'success update': 'update data failed')
                    ));
            } else {
                $this->response(array(
                    'status'=> 'fail',
                    'response'=> REST_Controller::HTTP_BAD_REQUEST,
                    'messages'=> 'username kosong'
                    ));
            }
        }

        /*        $nim = $this->put('nim');
                $data = array(
                            'nim'       => $this->put('nim'),
                            'nama'      => $this->put('nama'),
                            'id_jurusan'=> $this->put('id_jurusan'),
                            'alamat'    => $this->put('alamat'));
                $this->db->where('nim', $nim);
                $update = $this->db->update('mahasiswa', $data);
                if ($update) {
                    $this->response($data, 200);
                } else {
                    $this->response(array('status' => 'fail', 502));
                }*/
    }

    // FUNCTION OF DELETE
    public function api_delete()
    {
        if ($method=='delete.user') {
            if ($this->headers['username']) {
                $this->db->where('username', $this->headers['username']);

                $delete = $this->db->delete('users');

                $this->response(array(
                    'status'=> ($delete? 'success': 'fail'),
                    'response'=> ($delete? REST_Controller::HTTP_ACCEPTED: REST_Controller::HTTP_BAD_REQUEST),
                    'messages'=> ($delete? 'success delete': 'delete data failed')
                    ));
            } else {
                $this->response(array('status' => 'fail', 'response'=>REST_Controller::HTTP_BAD_REQUEST, 'messages' => 'tidak bisa delete. username kosong'));
            }
        }
        /*        $nim = $this->delete('nim');
                $this->db->where('nim', $nim);
                $delete = $this->db->delete('mahasiswa');
                if ($delete) {
                    $this->response(array('status' => 'success'), 201);
                } else {
                    $this->response(array('status' => 'fail', 502));
                }*/
    }

    public function _getToken($objectToken, $keyToken)
    {
        $this->session->unset_userdata('key_token');
        $token=JWT::encode($objectToken, $keyToken);
        $this->session->set_userdata('key_token', $keyToken);
        $this->response(array('status' => 'success', 'response'=> REST_Controller::HTTP_OK,'content' => $token ));
    }

    public function _getIPAddress()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public function _convertToArray($field)
    {
        $arr=explode(",", $field);
        for ($i=0; $i < count($arr); $i++) {
            $arr[$i]=trim($arr[$i]);
        }
        return $arr;
    }
}

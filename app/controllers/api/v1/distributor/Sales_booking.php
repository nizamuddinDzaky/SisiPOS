<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Distributor_Controller.php';

class Sales_booking extends MY_API_Distributor_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $this->lang->load('products', $this->Settings->user_language);
        $this->load->model('sales_model');
        $this->load->model('integration_model');
        $this->lang->load('sales', $this->Settings->user_language);

        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '545625';
    }

    public function list_sales_booking_get()
    {
        $this->db->trans_begin();

        try {
            $auth             = $this->authorize();

            $search           = $this->input->get('search');
            $start_date       = $this->input->get('start_date');
            $end_date         = $this->input->get('end_date');

            $where            = "sma_sales.is_deleted IS NULL";

            $warehouse_id     = $this->input->get('warehouse_id');
            $sale_status      = $this->input->get('sale_status');
            $payment_status   = $this->input->get('payment_status');
            $delivery_status  = $this->input->get('delivery_status');
            $payment_method   = $this->input->get('payment_method');

            $aksestoko        = $this->input->get('aksestoko');
            $limit            = $this->input->get('limit');
            $offset           = $this->input->get('offset');
            $sortby           = $this->input->get('sortby');
            $sorttype         = $this->input->get('sorttype');

            if ($warehouse_id) {
                $where .= " AND {$this->db->dbprefix('sales')}.warehouse_id = {$warehouse_id}";
            }

            if ($sale_status) {
                $where .= " AND {$this->db->dbprefix('sales')}.sale_status = '{$sale_status}'";
            }

            if ($aksestoko) {
                $where .= " AND {$this->db->dbprefix('sales')}.client_id = '{$aksestoko}'";
            } else {
                $where .= " AND ({$this->db->dbprefix('sales')}.client_id != 'aksestoko' OR {$this->db->dbprefix('sales')}.client_id IS NULL)";
            }

            if ($payment_status) {
                $where .= " AND {$this->db->dbprefix('sales')}.payment_status = '{$payment_status}'";
            }

            if ($delivery_status) {
                $where .= " AND delivery_status = '{$delivery_status}'";
            }

            if ($payment_method) {
                $where .= " AND {$this->db->dbprefix('purchases')}.payment_method = '{$payment_method}'";
            }

            if ($search) {
                $where .= " AND ({$this->db->dbprefix('sales')}.reference_no LIKE '%{$search}%' OR sma_sales.customer LIKE '%{$search}%' OR {$this->db->dbprefix('purchases')}.`payment_method` LIKE '%{$search}%' OR {$this->db->dbprefix('sales')}.`grand_total` LIKE '%{$search}%' OR {$this->db->dbprefix('sales')}.`paid` LIKE '%{$search}%' OR {$this->db->dbprefix('sales')}.`payment_status` LIKE '%{$search}%' OR `delivery_status` LIKE '%{$search}%')";
            }

            if ($start_date && $end_date) {
                $date_range = "({$this->db->dbprefix('sales')}.date BETWEEN '{$start_date}' AND '{$end_date}')";
            }

            if ($limit || $offset || $sortby || $sorttype) {
                $sales        = $this->sales_model->getAllSalesBookingPaging($auth->company->id, $where, $date_range, $limit, $offset, $sortby, $sorttype);
                if (!$limit) {
                    if ($offset || $sortby || $sorttype) {
                        if (count($sales) > 500) {
                            throw new Exception("Data More Than 500");
                        }
                    }
                }
                $all_sales    = $this->sales_model->getSalesBookingAll($auth->company->id, $where, $date_range);
                if (!$all_sales) {
                    throw new Exception(lang('not_found'), 404);
                }
            } else {
                $sales        = $this->sales_model->getAllSalesBooking($auth->company->id, $where, $date_range);
                if (count($sales) > 500) {
                    throw new Exception("Get List Sales Booking failed, because data more than 500");
                }
            }

            if (!$sales) {
                throw new Exception('Not Found', 404);
            }

            if ($limit != null) {
                $response = [
                    "limit"                => $limit,
                    "offset"               => $offset,
                    "rows"                 => $all_sales,
                    "count"                => count($sales),
                    "list_sales_booking"   => $sales
                ];
            } else {
                $response = [
                    "rows"               => count($sales),
                    "list_sales_booking" => $sales
                ];
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Sales Booking success", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_sales_booking_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $id_sales_booking = $this->input->get('id_sales_booking');

            $sale = $this->sales_model->getInvoiceByIdDetail($id_sales_booking, $auth->company->id);

            if (!$sale) {
                throw new Exception('Not Found', 404);
            }

            $sale_items = $this->sales_model->getSaleItemsBySaleId($sale->id);

            if ($sale->payment_term == 0) {
                $sale->payment_term = NULL;
            }

            if (!$sale_items) {
                throw new Exception('Not Found', 404);
            }

            $response = [
                "sale" => $sale,
                "sale_items" => $sale_items
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Sales Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_sales_booking_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'warehouse',
                    'label' => 'warehouse',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'customer',
                    'label' => 'customer',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'sale_status',
                    'label' => 'sale_status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $config_item_sale = [
                [
                    'field' => 'product_id',
                    'label' => 'product_id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'price',
                    'label' => 'price',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'quantity',
                    'label' => 'quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $sale_items = $this->body('products');
            if (!$sale_items) {
                throw new Exception('Post Add Sales Booking failed, Because field `products` is required', 400);
            }

            $total_quantity = 0;
            foreach ($sale_items as $key => $sale_item) {
                $this->validate_form($config_item_sale, $sale_item);
                $total_quantity = $total_quantity + (int) $sale_item['quantity'];
            }

            $reference = $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->body('date');
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id       = $this->body('warehouse');
            $customer_id        = $this->body('customer');
            $biller_id          = $auth->company->id;
            $total_items        = $total_quantity;
            $sale_status        = $this->body('sale_status');
            $payment_status     = 'pending';
            $payment_term       = $this->body('payment_term') ?? 0;
            $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping           = $this->body('shipping') ?? 0;

            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = $customer_details->company;

            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller             = $biller_details->company;
            $note               = $this->sma->clear_tags($this->body('note'));
            $staff_note         = $this->sma->clear_tags($this->body('staff_note'));
            $created_device         = $this->body('created_device') ?? '';

            $quote_id           = null;
            $sale_type          = 'booking';

            $total              = 0;
            $product_tax        = 0;
            $order_tax          = 0;
            $product_discount   = 0;
            $order_discount     = 0;
            $percentage         = '%';

            foreach ($sale_items as $key => $sale_item) {
                $product = $this->site->getProductByID($sale_item['product_id'], $auth->company->id);

                if (!$product) {
                    throw new Exception("Post Add Sales Booking failed, Because product with id " . $sale_item['product_id'] . " not found", 404);
                }

                $item_id            = $product->id;
                $item_type          = $product->type;
                $item_code          = $product->code;
                $item_name          = $product->name;
                $item_option        = null;
                $real_unit_price    = $this->sma->formatDecimal($sale_item['price']);
                $unit_price         = $this->sma->formatDecimal($sale_item['price']);
                $item_unit_quantity = $sale_item['quantity'];
                $item_serial        = '';
                $item_tax_rate      = null;
                $item_discount      = isset($sale_item['discount']) ? $sale_item['discount'] : null;
                $item_unit          = $product->unit;
                $item_quantity      = $sale_item['quantity'];
                $flag_consignment   = null;

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

                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_tax           = 0;
                    $pr_item_tax      = 0;
                    $item_tax         = 0;
                    $tax              = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax                   = $item_tax_rate;
                        $tax_details              = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax         = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax              = $tax_details->rate . "%";
                            } else {
                                $item_tax         = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax              = $tax_details->rate . "%";
                                $item_net_price   = $unit_price - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax         = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                $tax              = $tax_details->rate . "%";
                            } else {
                                $item_tax         = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax              = $tax_details->rate . "%";
                                $item_net_price   = $unit_price - $item_tax;
                            }

                            $item_tax             = $this->sma->formatDecimal($tax_details->rate);
                            $tax                  = $tax_details->rate;
                        }
                        $pr_item_tax              = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }

                    $product_tax    += $pr_item_tax;
                    $subtotal       = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit           = $this->site->getUnitByID($item_unit);

                    $products[] = [
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
                    ];

                    $booking[] = [
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
                    ];

                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }

            if ($this->body('order_discount')) {
                $order_discount_id = $this->body('order_discount');
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
                $order_tax_id = $this->body('order_tax');
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
            $data = [
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
                'created_by' => $auth->user->id,
                'company_id' => $auth->company->id,
                'sale_type' => $sale_type,
                'created_device' => $created_device,
                'cf1' => "Created from API",
                'cf2' => $this->token
            ];

            krsort($products);
            krsort($data);
            krsort($booking);

            $addsale = $this->sales_model->addSaleBooking($data, $products, null, null, $booking);

            if (!$addsale) {
                throw new Exception("Post Add Sales Booking failed");
            }

            $response = [
                "sale" => [
                    "id" => $addsale,
                    "reference_no" => $reference
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Sales Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function edit_sales_booking_put()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'warehouse',
                    'label' => 'warehouse',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'customer',
                    'label' => 'customer',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'sale_status',
                    'label' => 'sale_status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $config_item_sale = [
                [
                    'field' => 'product_id',
                    'label' => 'product_id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'price',
                    'label' => 'price',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'quantity',
                    'label' => 'quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $sale_items = $this->body('products');
            if (!$sale_items) {
                throw new Exception('Put Edit Sales Booking failed, Because `products` is required', 400);
            }

            $total_quantity = 0;
            foreach ($sale_items as $key => $sale_item) {
                $this->validate_form($config_item_sale, $sale_item);
                $total_quantity = $total_quantity + (int) $sale_item['quantity'];
            }

            $id_sales_booking = $this->input->get('id_sales_booking');

            $sale = $this->sales_model->getSalesById($id_sales_booking);

            if (!$sale) {
                throw new Exception('Not Found', 404);
            }

            if ($sale->sale_status == 'closed') {
                throw new Exception('Put Edit Sales Booking failed, Because Data already closed cannot edit');
            }

            if ($sale->sale_status == 'canceled') {
                throw new Exception('Put Edit Sales Booking failed, Because Status data is Canceled');
            }

            if ($sale->sale_status == 'returned' || $sale->return_id || $sale->return_sale_ref) {
                throw new Exception('Put Edit Sales Booking failed, Because this action cannot be performed for sale with a return record');
            }

            $delivery  = $this->sales_model->getDeliveryBySaleID($sale->id);

            if ($delivery) {
                throw new Exception('Put Edit Sales Booking failed, Because delivery is available');
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->body('date');
            } else {
                $date = $sale->date;
            }

            $warehouse_id       = $this->body('warehouse');
            $customer_id        = $this->body('customer');
            $biller_id          = $auth->company->id;
            $total_items        = $total_quantity;
            $sale_status        = $this->body('sale_status');
            //  
            $payment_term       = $this->body('payment_term') ?? 0;
            $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping           = $this->body('shipping') ?? 0;

            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = $customer_details->company;

            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller             = $biller_details->company;
            $note               = $this->sma->clear_tags($this->body('note'));
            $staff_note         = $this->sma->clear_tags($this->body('staff_note'));
            $updated_device     = $this->body('updated_device') ?? '';
            $quote_id           = null;
            $sale_type          = 'booking';

            $total              = 0;
            $product_tax        = 0;
            $order_tax          = 0;
            $product_discount   = 0;
            $order_discount     = 0;
            $percentage         = '%';
            foreach ($sale_items as $key => $sale_item) {
                $product = $this->site->getProductByID($sale_item['product_id'], $auth->company->id);

                if (!$product) {
                    throw new Exception("Put Edit Sales Booking failed, Because product with id " . $sale_item['product_id'] . " not found", 404);
                }

                $item_id = $product->id;
                $item_type = $product->type;
                $item_code = $product->code;
                $item_name = $product->name;
                $item_option = null;
                $real_unit_price = $this->sma->formatDecimal($sale_item['price']);
                $unit_price = $this->sma->formatDecimal($sale_item['price']);
                $item_unit_quantity = $sale_item['quantity'];
                $item_serial = '';
                $item_tax_rate = null;
                $item_discount = isset($sale_item['discount']) ? $sale_item['discount'] : null;
                $item_unit = $product->unit;
                $item_quantity = $sale_item['quantity'];
                $flag_consignment = null;

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

                    $products[] = [
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
                    ];

                    $booking[] = [
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
                    ];

                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if ($this->body('order_discount')) {
                $order_discount_id = $this->body('order_discount');
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
                $order_tax_id = $this->body('order_tax');
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
            $data = [
                'date' => $date,
                'reference_no' => $sale->reference_no,
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
                'payment_status' => $sale->payment_status,
                'payment_term' => $payment_term,
                'reason' => $this->body('reason'),
                'due_date' => $due_date,
                'updated_by' => $auth->user->id,
                'updated_at' => date('Y-m-d H:i:s'),
                'sale_type' => $sale_type,
                'updated_device' => $updated_device,
                'cf1' => "Updated from API",
                'cf2' => $this->token
            ];

            krsort($products);
            krsort($data);
            krsort($booking);

            $updatesale = $this->sales_model->updateSaleBooking($sale_status, $id_sales_booking, $data, $products, $booking);

            if (!$updatesale) {
                throw new Exception("Put Edit Sales Booking failed");
            }

            $response = [
                "sale" => [
                    "id" => $id_sales_booking,
                    "reference_no" => $sale->reference_no
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Edit Sales Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_status_sales_booking_put()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'sale_status',
                    'label' => 'Sale Status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $id_sales_booking = $this->input->get('id_sales_booking');

            $sale = $this->sales_model->getSalesById($id_sales_booking);

            if (!$sale) {
                throw new Exception('Not Found', 404);
            }

            $deliv  = $this->sales_model->getDeliveryBySaleID($sale->id);

            if ($deliv) {
                throw new Exception('Put Update Status Sales Booking failed, Because delivery is available', 400);
            }

            $sale_status = $this->body('sale_status');
            $note = $this->body('note');

            $updatesale = $this->sales_model->updateStatus($id_sales_booking, $sale_status, $note, null);

            if (!$updatesale) {
                throw new Exception("Put Update Status Sales Booking failed");
            }

            $response = [
                "sale" => [
                    "id" => $id_sales_booking,
                    "reference_no" => $sale->reference_no
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Update Status Sales Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_deliveries_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $id_delivery = $this->input->get('id_delivery');

            $delivery = $this->sales_model->getDeliveryAndSaleByDeliveryId($id_delivery);

            if (!$delivery) {
                throw new Exception('Not Found', 404);
            }

            $delivery_items = $this->sales_model->getDeliveryItemsByDeliveryId($id_delivery);

            if (!$delivery_items) {
                throw new Exception('Not Found', 404);
            }

            $response = [
                "delivery" => $delivery,
                "delivery_items" => $delivery_items
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Deliveiries success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_deliveries_booking_get()
    {
        $this->db->trans_begin();
        try {
            $auth               = $this->authorize();
            $id_sales_booking   = $this->input->get('id_sales_booking');
            $where              = "{$this->db->dbprefix('sales')}.company_id = {$auth->company->id}";
            $search             = $this->input->get('search');
            $status             = $this->input->get('status');
            $start_date         = $this->input->get('start_date');
            $end_date           = $this->input->get('end_date');

            if ($search) {
                $where = " AND ({$this->db->dbprefix('deliveries')}.sale_reference_no LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries')}.customer LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries')}.address LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries')}.status LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries')}.do_reference_no LIKE '%{$search}%')";
            }
            if ($status) {
                $where .= " AND `{$this->db->dbprefix('deliveries')}.status` = '{$status}'";
            }
            if ($id_sales_booking) {
                $where .= " AND {$this->db->dbprefix('deliveries')}.sale_id = {$id_sales_booking}";
            }

            if ($start_date && $end_date) {
                $date_range = "({$this->db->dbprefix('deliveries')}.date BETWEEN '{$start_date}' AND '{$end_date}')";
            }

            $deliveries = $this->sales_model->getAllDeliveriesBooking($auth->company->id, $where, $date_range);
            if (!$deliveries) {
                throw new Exception('Not Found', 404);
            }

            if (count($deliveries) > 500) {
                throw new Exception("Get List Deliveries Booking failed, Because data more than 500");
            }

            $response = [
                "total_deliveries_booking" => count($deliveries),
                "list_deliveries_booking" => $this->unsetFrom2DArray(['client_id'], $deliveries)
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Deliveries Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_return_deliveries_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'sale_id',
                    'label' => 'sale_id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'sale_reference_no',
                    'label' => 'sale_reference_no',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'do_reference_no',
                    'label' => 'do_reference_no',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'customer',
                    'label' => 'customer',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'address',
                    'label' => 'address',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'status',
                    'label' => 'status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $config_item_delivery = [
                [
                    'field' => 'delivery_items_id',
                    'label' => 'delivery_items_id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'delivered_quantity',
                    'label' => 'delivered_quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'return_quantity',
                    'label' => 'return_quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $delivery_items = $this->body('products');
            if (!$delivery_items) {
                throw new Exception('`products` required', 400);
            }

            $id_deliveries_booking    = $this->input->get('id_deliveries_booking');
            $delivery                 = $this->sales_model->getDeliveryByID($id_deliveries_booking);
            $getClientStatus          = $this->sales_model->getClientStatusByDeliveryId($id_deliveries_booking);
            $sale                     = $this->sales_model->getSalesByID($delivery->sale_id);

            if (!$delivery) {
                throw new Exception('Put Edit Deliveries Booking failed, Because delivery not found', 400);
            }

            if ($delivery->status == "returned") {
                throw new Exception("Post Return Deliveries Booking failed, Because You Can't Return", 400);
            } elseif ($delivery->status != "delivered") {
                throw new Exception("Post Return Deliveries Booking failed, Because You Can't Return Before Delivery status is Delivered", 400);
            } elseif ($delivery->status == "delivered" && $this->sales_model->getReturnDeliveryByRef($delivery->do_reference_no, $delivery->sale_id)) {
                throw new Exception("Post Return Deliveries Booking failed, Because You Have Return Before", 400);
            } elseif ($delivery->status == "delivered" && $sale->client_id == "aksestoko" && $getClientStatus->bad <= 0) {
                throw new Exception("Post Return Deliveries Booking failed, Because Return for AksesToko made by filling the number of bad quantity items at AksesToko", 400);
            } elseif ($sale->sale_status == "closed") {
                throw new Exception("Post Return Deliveries Booking failed, Because The Sales Status Is Closed", 400);
            }

            $total_sent_quantity    = 0;
            $delivery_items_id      = [];
            $delivered_quantity     = [];
            $return_quantity        = [];

            foreach ($delivery_items as $delivery_item) {
                $this->validate_form($config_item_delivery, $delivery_item);
                $total_sent_quantity    = $total_sent_quantity + (int) $delivery_item['return_quantity'];
                $delivery_items_id[]    = $delivery_item['delivery_items_id'];
                $delivered_quantity[]   = $delivery_item['delivered_quantity'];
                $return_quantity[]      = $delivery_item['return_quantity'];
            }

            for ($i = 0; $i < count($return_quantity); $i++) {
                if ($return_quantity[$i] > $delivered_quantity[$i]) {
                    throw new Exception("Post Return Deliveries Booking failed, Because Return Quantity Higher Than Delivered Quantity", 400);
                }
            }

            if (array_sum($return_quantity) == 0) {
                throw new Exception("Post Return Deliveries Booking failed, Because At least return 1 quantity", 400);
            }

            $dlDetails = array(
                'sale_id'               => $delivery->sale_id,
                'do_reference_no'       => $this->site->getReference('dr'),
                'sale_reference_no'     => $this->body('sale_reference_no'),
                'return_reference_no'   => $this->body('do_reference_no'),
                'customer'              => $this->body('customer'),
                'address'               => $this->body('address'),
                'status'                => "returned",
                'delivered_by'          => $this->body('delivered_by'),
                'received_by'           => $this->body('received_by'),
                'note'                  => $this->sma->clear_tags($this->body('note')),
                'created_by'            => $auth->user->id,
                'client_id'             => $sale->client_id,
                'date'                  => $this->body('date_return') ?? date('Y-m-d h:i:s'),
                'created_device'        => $this->body('created_device') ?? '',
            );

            $shipping_cost        = $this->body('shipping') ? $this->body('shipping') : 0;
            $delivery_return_id   = $this->sales_model->return_delivery($id_deliveries_booking, $dlDetails, $shipping_cost, $delivery_items_id, $delivered_quantity, $return_quantity);

            if (!$delivery_return_id) {
                throw new Exception("Post Return Deliveries Booking failed");
            }

            $this->db->update('deliveries', ['is_approval' => 1], ['id' => $id_deliveries_booking]);

            $purchase             = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->company_id);

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

                    $itemPurchase['good']   = $newData->good_quantity;
                    $itemPurchase['bad']    = $newData->bad_quantity;
                    $itemPurchase['sent']   = ($newData->bad_quantity + $newData->good_quantity);
                    $status                 = $itemPurchase['sent'] >= $item->quantity ? "received" : "partial";

                    $this->sales_model->updatePurchaseItemsById($item->id, [
                        'status'              => $status,
                        'quantity_balance'    => $itemPurchase['sent'],
                        'quantity_received'   => $itemPurchase['sent'],
                        'good_quantity'       => $itemPurchase['good'],
                        'bad_quantity'        => $itemPurchase['bad']
                    ]);
                }
                $this->site->syncQuantity(null, null, $purchase_items);

                $items    = $this->site->getAllPurchaseItems($purchase->id);
                $status   = 'received';
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

            $response = [
                "delivery" => [
                    "id"                  => $delivery_return_id,
                    "do_reference_no"     => $dlDetails['do_reference_no'],
                    "so_reference_no"     => $dlDetails['sale_reference_no'],
                    "return_reference_no" => $dlDetails['return_reference_no']
                ]
            ];

            $this->session->set_userdata('remove_slls', 1);
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Return Deliveries Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }


    public function add_deliveries_booking_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'sale_reference_no',
                    'label' => 'sale_reference_no',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'customer',
                    'label' => 'customer',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'address',
                    'label' => 'address',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'status',
                    'label' => 'status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $config_item_delivery = [
                [
                    'field' => 'sale_items_id',
                    'label' => 'sale_items_id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'sent_quantity',
                    'label' => 'sent_quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $delivery_items = $this->body('products');

            if (!$delivery_items) {
                throw new Exception('`products` required', 400);
            }

            $total_sent_quantity = 0;
            $sale_items_id = [];
            $sent_quantity = [];
            foreach ($delivery_items as $key => $delivery_item) {
                $this->validate_form($config_item_delivery, $delivery_item);
                $total_sent_quantity = $total_sent_quantity + (int) $delivery_item['sent_quantity'];
                $sale_items_id[] =  $delivery_item['sale_items_id'];
                $sent_quantity[] =  $delivery_item['sent_quantity'];
            }

            if ($total_sent_quantity <= 0) {
                throw new Exception('Post Add Deliveries Booking failed, Because at Least Send 1 Quantity', 400);
            }

            $sale_reference_no    = $this->body('sale_reference_no');
            $customer             = $this->body('customer');
            $address              = $this->body('address');
            $status               = $this->body('status');
            $delivered_by         = $this->body('delivered_by');
            $received_by          = $this->body('received_by');
            $note                 = $this->body('note');
            $id_sales_booking     = $this->input->get('id_sales_booking');
            $shipping_cost        = $this->body('shipping_cost') ? $this->body('shipping_cost') : 0;

            $sale = $this->sales_model->getSalesById($id_sales_booking);
            if (!$sale) {
                throw new Exception('Post Add Deliveries Booking failed, Because sale not found', 404);
            }

            if ($sale->sale_status == 'closed') {
                throw new Exception('Post Add Deliveries Booking failed, Because status data is close can not delivery');
            }
            if ($sale->sale_status != 'reserved') {
                throw new Exception('Post Add Deliveries Booking failed, Because sale status is not reserved, you can add delivery order for reserved sales only');
            }

            $sale_items = $this->sales_model->getSaleItemsBySaleId($id_sales_booking);

            if (!$sale_items) {
                throw new Exception('Post Add Deliveries Booking failed, Because sale items not found', 404);
            }
            
            foreach($delivery_items as $index => $di){
                $key = array_search($di['sale_items_id'], array_column($sale_items, 'id'));
                $real_stock = $this->sales_model->getWarehouseProduct($sale_items[$key]->warehouse_id, $sale_items[$key]->product_id);
                if (!$real_stock) {
                    throw new Exception("Post Add Deliveries Booking failed, Because can't find quantity", 404);
                }

                if ($real_stock->quantity < $sent_quantity[$index]) {
                    throw new Exception('Post Add Deliveries Booking failed, Because you are out of stock', 400);
                }

                $sentQty    = $di['sent_quantity'] + $sale_items[$key]->sent_quantity;
                if ($sale_items[$key]->quantity < $sentQty) {
                    throw new Exception('Post Add Deliveries Booking failed, because the quantity of product ' . $sale_items[$key]->name . ' exceeds that ordered. Please check again', 400);
                }
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->body('date');
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $dlDetails = [
                'date'              => $date,
                'sale_id'           => $id_sales_booking,
                'do_reference_no'   => $this->site->getReference('do'),
                'sale_reference_no' => $sale_reference_no,
                'customer'          => $customer,
                'address'           => $address,
                'status'            => $status,
                'delivered_by'      => $delivered_by,
                'received_by'       => $received_by,
                'note'              => $note,
                'created_by'        => $auth->user->id,
                'client_id'         => "new_delivery",
                'created_device'    => $this->body('created_device') ?? '',
            ];

            if ($status == 'delivering') {
                $dlDetails['delivering_date'] = date('Y-m-d H:i:s');
            } elseif ($status == 'delivered') {
                $dlDetails['delivering_date']   = date('Y-m-d H:i:s');
                $dlDetails['delivered_date']    = date('Y-m-d H:i:s');
            }

            $delivery_id = $this->sales_model->addDelivery($dlDetails, $shipping_cost, $sale_items_id, $sent_quantity);
            if (!$delivery_id) {
                throw new Exception("Post Add Deliveries Booking failed");
            }
            $response = [
                "delivery" => [
                    "id" => $delivery_id,
                    "reference_no" => $dlDetails['do_reference_no']
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Deliveries Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function edit_deliveries_booking_put()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'customer',
                    'label' => 'customer',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'address',
                    'label' => 'address',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'status',
                    'label' => 'status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $config_item_delivery = [
                [
                    'field' => 'delivery_items_id',
                    'label' => 'delivery_items_id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'sent_quantity',
                    'label' => 'sent_quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $delivery_items = $this->body('products');
            if (!$delivery_items) {
                throw new Exception('Put Edit Deliveries Booking failed, Because `products` is required', 400);
            }

            $total_sent_quantity    = 0;
            $delivery_items_id      = [];
            $sent_quantity          = [];
            foreach ($delivery_items as $delivery_item) {
                $this->validate_form($config_item_delivery, $delivery_item);
                $total_sent_quantity = $total_sent_quantity + (int) $delivery_item['sent_quantity'];
                $delivery_items_id[] =  $delivery_item['delivery_items_id'];
                $sent_quantity[]     =  $delivery_item['sent_quantity'];
            }

            if ($total_sent_quantity <= 0) {
                throw new Exception('Put Edit Deliveries Booking failed, Because at Least Send 1 Quantity', 400);
            }

            $id_deliveries_booking    = $this->input->get('id_deliveries_booking');

            $delivery                 = $this->sales_model->getDeliveryByID($id_deliveries_booking);

            if (!$delivery) {
                throw new Exception('Put Edit Deliveries Booking failed, Because delivery not found', 404);
            }

            if ($delivery->status == "returned" || $delivery->status == "delivered") {
                throw new Exception("Put Edit Deliveries Booking failed, Because you can't edit delivery", 400);
            }

            $sale = $this->sales_model->getSalesById($delivery->sale_id);

            if (!$sale) {
                throw new Exception('Put Edit Deliveries Booking failed, Because sale not found', 404);
            }

            $sale_items   = $this->sales_model->getSaleItemsBySaleId($sale->id);

            if (!$sale_items) {
                throw new Exception('Put Edit Deliveries Booking failed, Because sale items not found', 404);
            }

            if ($sale->sale_type == 'booking') {
                foreach ($sale_items as $index => $value) {
                    $real_stock       = $this->sales_model->getWarehouseProduct($value->warehouse_id, $value->product_id);
                    $delivery_item    = $this->sales_model->getDeliveryItemByDeliveryItemId($delivery_items_id[$index]);
                    $current_stock    = $real_stock->quantity + $delivery_item->quantity_sent;
                    if ($current_stock < $sent_quantity[$index]) {
                        throw new Exception('Put Edit Deliveries Booking failed, Because you are out of stock', 400);
                    }
                }
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->body('date');
            } else {
                $date = $delivery->date;
            }

            $sale_reference_no    = $delivery->sale_reference_no;
            $customer             = $this->body('customer');
            $attachment           = $this->body('attachment');
            $address              = $this->body('address');
            $status               = $this->body('status');
            $delivered_by         = $this->body('delivered_by');
            $received_by          = $this->body('received_by');
            $note                 = $this->body('note');
            $shipping_cost        = $this->body('shipping_cost') ? $this->body('shipping_cost') : 0;

            $dlDetails = [
                'date'              => $date,
                'sale_id'           => $delivery->sale_id,
                'do_reference_no'   => $delivery->do_reference_no,
                'sale_reference_no' => $sale_reference_no,
                'customer'          => $customer,
                'address'           => $address,
                'status'            => $status,
                'delivered_by'      => $delivered_by,
                'received_by'       => $received_by,
                'note'              => $note,
                'updated_by'        => $auth->user->id,
                'attachment'        => $attachment,
                'updated_device'    => $this->body('updated_device') ?? '',
            ];

            if ($status == 'delivering') {
                if (!$delivery->delivering_date) {
                    $dlDetails['delivering_date'] = date('Y-m-d H:i:s');
                }
            } elseif ($status == 'delivered') {
                if (!$delivery->delivering_date) {
                    $dlDetails['delivering_date'] = date('Y-m-d H:i:s');
                }
                $dlDetails['delivered_date'] = date('Y-m-d H:i:s');
            }
            $deliveryItems = [
                'delivery_items_id' => $delivery_items_id,
                'sent_quantity'     => $sent_quantity
            ];

            if (!$this->sales_model->updateDelivery($id_deliveries_booking, $dlDetails, $shipping_cost, $deliveryItems)) {
                throw new Exception('Put Edit Deliveries Booking failed');
            }

            $purchase_data = $this->sales_model->getPurchasesByRefNo($sale_reference_no, $auth->company->id);

            if ($status == 'delivered' && $purchase_data) {
                $deliveryItem         = $this->sales_model->getDeliveryItemsByDeliveryId($id_deliveries_booking);
                $arrDeliveryItemId    = [];
                $product_code         = [];
                $quantity_received    = [];
                $good                 = [];
                $bad                  = [];

                foreach ($deliveryItem as $value) {
                    $arrDeliveryItemId[]    = $value->id;
                    $product_code[]         = $value->product_code;
                    $quantity_received[]    = $value->quantity_sent;
                    $good[]                 = $value->good_quantity;
                    $bad[]                  = 0;
                }

                $data_confirm_received = [
                    'purchase_id'         => $purchase_data->id,
                    'product_code'        => $product_code,
                    'quantity_received'   => $quantity_received,
                    'do_ref'              => $delivery->do_reference_no,
                    'do_id'               => $id_deliveries_booking,
                    'delivery_item_id'    => $arrDeliveryItemId,
                    'good'                => $good,
                    'bad'                 => $bad,
                    'note'                => 'Received by Distributor',
                    'file'                => null
                ];

                if ($sale->sale_type == 'booking') {
                    $confirm = $this->at_purchase->confirmReceivedBooking($data_confirm_received, $auth->user->id, $delivery->sale_id);
                } else {
                    $confirm = $this->at_purchase->confirmReceived($data_confirm_received, $auth->user->id, $delivery->sale_id);
                }

                if (!$confirm) {
                    throw new \Exception("Put Edit Deliveries Booking failed, Because cannot receive product for aksestoko");
                }

                if ($sale->sale_type == 'booking') {
                    if ($this->site->checkAutoClose($sale->id)) {
                        $this->sales_model->closeSale($sale->id);
                    }
                }

                if ($purchase_data->payment_method == 'kredit_pro' && $purchase_data->status == 'received') {
                    $attachment = [];
                    $attachment = $this->generatePDFDeliv($sale);
                    $pathPDFInv = $this->generatePDFInv($sale, $purchase_data);
                    array_push($attachment, $pathPDFInv);
                    $this->sales_model->send_email_delivery($purchase_data->id, $sale, $attachment);
                }
            }

            if (in_array($dlDetails['status'], ['delivered', 'delivering'])) {
                $notify_type = "delivering_delivery";
            } else if (in_array($dlDetails['status'], ['packing'])) {
                $notify_type = "packing_delivery";
            }

            $purchase = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->company_id);
            $this->load->model('socket_notification_model');

            if ($notify_type) {
                $data_socket_notification = [
                    'company_id'        => $sale->customer_id,
                    'transaction_id'    => 'SALE-' . $purchase->id . '-' . $id_deliveries_booking,
                    'customer_name'     => '',
                    'reference_no'      => $sale->reference_no,
                    'price'             => '',
                    'type'              => $notify_type,
                    'to'                => 'aksestoko',
                    'note'              => '',
                    'created_at'        => date('Y-m-d H:i:s')
                ];
                $this->socket_notification_model->addNotification($data_socket_notification);
            }

            $response = [
                "delivery" => [
                    "id"           => $id_deliveries_booking,
                    "reference_no" => $dlDetails['do_reference_no']
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Edit Deliveries Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function upload_file_delivery_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $id_delivery = $this->input->get('id_deliver');

            $delivery = $this->sales_model->getDeliveryByID($id_delivery);

            if (!$delivery) {
                throw new Exception('Post upload file payment, Because payment not found', 404);
            }

            if ($_FILES['file']['size'] < 0) {
                throw new Exception('Post upload file payment, Size less then zero', 404);
            }

            /*$this->load->library('upload');
            $config['upload_path']    = $this->digital_upload_path;
            $config['allowed_types']  = $this->digital_file_types;
            $config['max_size']       = $this->allowed_file_size;
            $config['overwrite']      = false;
            $config['encrypt_name']   = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $error = $this->upload->display_errors();
                throw new Exception('Post upload file failed ' . $error, 404);
            }
            $photo                      = $this->upload->file_name;*/
            $uploadedImg                = $this->integration_model->upload_files($_FILES['file']);
            $photo                      = $uploadedImg->url;
            $dlDetails['attachment']    = $photo;

            if (!$this->sales_model->updateDelivery($id_delivery, $dlDetails)) {
                throw new Exception('Post upload file failed, Because edit payment booking failed');
            }

            $response = [
                "delivery" => [
                    "id"           => $id_delivery,
                    "file_name"    => $_FILES['file']['name']
                ]
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post upload file payment Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_payments_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $id_sales_booking = $this->input->get('id_sales_booking');

            $payments = $this->sales_model->getInvoicePaymentsBySalesId($id_sales_booking);
            if (!$payments) {
                throw new Exception('Not Found', 404);
            }

            $response = [
                "total_payments" => count($payments),
                "list_payments" => $payments
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Payments success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_payments_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'amount_paid',
                    'label' => 'amount_paid',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'payment_method',
                    'label' => 'payment_method',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $id_sales_booking = $this->input->get('id_sales_booking');

            $sale = $this->sales_model->getSalesById($id_sales_booking);

            if (!$sale) {
                throw new Exception('Not Found', 404);
            }

            if ($sale->payment_status == 'paid' && $sale->grand_total >= $sale->paid) {
                throw new Exception("Post Add Payment failed, Because payment status is already paid for the sale");
            }

            if ($sale->sale_status == 'pending' && $sale->sale_type == 'booking') {
                throw new Exception("Post Add Payment failed, Because sale status must be reserved");
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->body('date');
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $payment = [
                'date' => $date,
                'sale_id' => $sale->id,
                'reference_no' => $this->site->getReference('pay'),
                'amount' => $this->body('amount_paid'),
                "note" => $this->body('note'),
                'paid_by' => $this->body('payment_method'),
                'created_by' => $auth->user->id,
                'type' => 'received',
                'company_id' => $auth->company->id,
                'reference_dist' => $this->site->getReference('pay'),
                'created_device'    => $this->body('created_device') ?? '',
            ];

            $id_payment = $this->sales_model->addPayment($payment);

            if (!$id_payment) {
                throw new Exception("Post Add Payment failed");
            }

            $response = [
                "payment" => [
                    "id" => $id_payment,
                    "reference_no" => $payment['reference_no']
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Payments success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function edit_payments_put()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'amount_paid',
                    'label' => 'amount_paid',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'payment_method',
                    'label' => 'payment_method',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $id_payments = $this->input->get('id_payments');

            $payment = $this->sales_model->getPaymentByID($id_payments);

            if (!$payment) {
                throw new Exception('Not Found', 404);
            }

            $sale = $this->sales_model->getSalesById($payment->sale_id);

            if (!$sale) {
                throw new Exception('Put Edit Payments failed, Because sale not found', 404);
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->body('date');
            } else {
                $date = $payment->date;
            }

            $payment = [
                'date' => $date,
                'sale_id' => $sale->id,
                'reference_no' => $payment->reference_no,
                'amount' => $this->body('amount_paid'),
                "note" => $this->body('note'),
                'paid_by' => $this->body('payment_method'),
                'attachment' => $this->body('attachment'),
                'updated_device'    => $this->body('updated_device') ?? '',
            ];

            $updatePayment = $this->sales_model->updatePayment($id_payments, $payment);

            if (!$updatePayment) {
                throw new Exception("Put Edit Payments failed", 404);
            }

            $response = [
                "payment" => [
                    "id" => $id_payments,
                    "reference_no" => $payment['reference_no']
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Edit Payments success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function upload_file_payment_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $id_payments = $this->input->get('id_payments');

            $payment = $this->sales_model->getPaymentByID($id_payments);

            if (!$payment) {
                throw new Exception('Post upload file payment, because payment not found', 404);
            }

            if ($_FILES['file']['size'] < 0) {
                throw new Exception('Post upload file payment, because size less then zero', 404);
            }

            /*$this->load->library('upload');
            $config['upload_path']    = $this->digital_upload_path;
            $config['allowed_types']  = $this->digital_file_types;
            $config['max_size']       = $this->allowed_file_size;
            $config['overwrite']      = false;
            $config['encrypt_name']   = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $error = $this->upload->display_errors();
                throw new Exception('Post upload file payment failed ' . $error, 404);
            }
            $photo                      = $this->upload->file_name;*/
            $uploadedImg            = $this->integration_model->upload_files($_FILES['file']);
            $photo                  = $uploadedImg->url;
            $data['attachment']     = $photo;

            if (!$this->sales_model->updatePayment($id_payments, $data)) {
                throw new Exception('Post upload file payment failed, Because edit payment failed');
            }

            $response = [
                "delivery" => [
                    "id"           => $id_payments,
                    "file_name"    => $_FILES['file']['name']
                ]
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post upload file payment success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_transaction_sales_booking_get()
    {
        $this->db->trans_begin();

        try {
            $auth         = $this->authorize();
            $year_month   = $this->input->get('year_month');
            $warehouse_id = $this->input->get('warehouse_id');

            if (!$year_month) {
                throw new Exception("Get List Transaction Sales Booking failed because cant get the year and month value", 404);
            }
            $split_year_month   = explode('-', $year_month, 2);
            $year               = $split_year_month[0];
            $month              = $split_year_month[1];
            $status_sale        = ["pending", "confirmed", "canceled", "completed", "returned", "reserved", "closed"];

            foreach ($status_sale as $value) {
                $total              = $this->sales_model->gettransactionSalesBooking($auth->company->id, $value, $year, $month, $warehouse_id);
                $response[$value]   = $total;
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Transaction Sales Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function close_sales_booking_post()
    {
        $this->db->trans_begin();

        try {
            $auth   = $this->authorize();
            $id     = $this->input->get('id_sales');

            if (!$id) {
                throw new Exception("Post Close Sales Booking failed, because cant get the id sale", 404);
            }

            $sale                 = $this->sales_model->getInvoiceByID($id, $auth->company->id);
            $sale_items           = $this->sales_model->getSaleItemsBySaleId($sale->id);
            $getDeliveryToClose   = $this->sales_model->getDeliveryToClose($id);
            $purchase             = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->biller_id);

            if (!$sale) {
                throw new Exception("Post Close Sales Booking failed, because cant get the data sale", 404);
            }
            if (!$sale_items) {
                throw new Exception("Post Close Sales Booking failed, because cant get the data sale item", 404);
            }

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
                    $str .= "There is Delivering or packing status in this Delivery" . ' = ' . substr($str_confirm, 0, strlen($str_confirm) - 2) . '<br>';
                if ($str_approve != '')
                    $str .= "The Delivery need approval" . ' = ' . substr($str_approve, 0, strlen($str_approve) - 2) . '<br>';
                if ($str_delivering != '')
                    $str .= "There is Delivering or packing status in this Delivery" . ' = ' . substr($str_delivering, 0, strlen($str_delivering) - 2) . '<br>';

                if (array_sum($str_close) != count($getDeliveryToClose) || count($getDeliveryToClose) != count($str_received)) {
                    throw new Exception("Post Close Sales Booking failed, because" . $str, 404);
                }
            }

            if ($purchase->payment_method == 'kredit_pro' && !($purchase->payment_status == 'reject' || $purchase->payment_status == 'pending')) {
                throw new Exception("Post Close Sales Booking failed, because could not be closed because the CreditPro submission is / has been processed.", 404);
            } else if ($sale->sale_status == 'closed') {
                throw new Exception("Post Close Sales Booking failed, because sale status is closed", 404);
            } else if ($sale->sale_status != 'reserved') {
                throw new Exception("Post Close Sales Booking failed, because sale status must be reserved", 404);
            }
            if ($sale->payment_status == 'waiting') {
                throw new Exception("Post Close Sales Booking failed, because can't close when payment status is waiting", 404);
            }

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

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details    = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
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
                $order_tax_id = $this->post('order_tax');
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
                'updated_device'    => $this->body('updated_device') ?? '',
            );

            if ($this->sales_model->closeSale($id, $data, $products)) {
                if (!$this->close_update_sale($sale, $sale_items)) {
                    throw new Exception("Post Close Sales Booking failed, because error close update sale", 404);
                }

                $response = [
                    "id_sale"       => $id,
                    "reference_no"  => $sale->reference_no
                ];

                $this->db->trans_commit();
            } else {
                throw new Exception("Post Close Sales Booking failed, because close sale error", 404);
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Close Sales Booking success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function term_of_payment_get()
    {
        $this->db->trans_begin();
        try {
            $auth   = $this->authorize();
            $gettop = $this->sales_model->getTOP($auth->company->id);
            if(!$gettop){
                $gettop[0]=[
                    "id" => "0",
                    "description" => "Tidak Ada",
                    "duration" => "0"
                ];
            }

            $response = [
                "term_of_payment"       => $gettop,
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Term Of Payment success", $response);

        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function generatePDFDeliv($sales)
    {
        $path = [];
        $this->load->model('sales_model');
        $deliveries = $this->sales_model->getAllDeliveryBySaleID($sales->id);
        foreach ($deliveries as $deli) {
            $this->data['delivery']   = $deli;
            $this->data['biller']     = $this->site->getCompanyByID($sales->biller_id);
            $this->data['rows']       = $this->sales_model->getDeliveryItemsByDeliveryId($deli->id);
            $this->data['user']       = $this->site->getUser($deli->created_by);
            $name                     = lang("delivery") . "_" . str_replace('/', '_', $deli->do_reference_no) . "-" . $sales->biller_id . ".pdf";
            $html                     = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
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
        $this->data['barcode']        = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']       = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']       = $this->sales_model->getPaymentsForSale($inv->id);
        $this->data['biller']         = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user']           = $this->site->getUser($inv->created_by);
        $this->data['warehouse']      = $this->site->getWarehouseByID($inv->warehouse_id, $inv->biller_id);
        $this->data['inv']            = $inv;
        $this->data['rows']           = $this->sales_model->getAllInvoiceItems($inv->id);
        $this->data['return_sale']    = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']    = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['po']             = $purchase;
        $name                         = "INVOICE_-_" . str_replace('/', '_', $inv->reference_no) . "-" . $inv->biller_id . ".pdf";
        $html                         = $this->load->view($this->theme . 'sales/sale_pdf_kredit_pro', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        return $this->sma->generate_pdf($html, $name, 'S', $this->data['biller']->invoice_footer);
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
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------//
}

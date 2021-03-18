<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Distributor_Controller.php';

class Purchases extends MY_API_Distributor_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->Settings = $this->site->get_setting();
        $this->lang->load('purchases', $this->Settings->user_language);
        $this->load->model('purchases_model');
    }

    public function list_purchases_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $where = "{$this->db->dbprefix('purchases')}.company_id = {$auth->company->id}";
            $search = $this->input->get('search');
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $payment_status = $this->input->get('payment_status');
            $purchase_status = $this->input->get('purchase_status');
            $offset = $this->input->get('offset');
            $limit = $this->input->get('limit');

            if ($payment_status) {
                $where .= " AND {$this->db->dbprefix('purchases')}.payment_status = '{$payment_status}'";
            }

            if ($purchase_status) {
                $where .= " AND {$this->db->dbprefix('purchases')}.status = '{$purchase_status}'";
            }

            if ($search) {
                $where .= " AND ({$this->db->dbprefix('purchases')}.reference_no LIKE '%{$search}%' OR {$this->db->dbprefix('purchases')}.supplier LIKE '%{$search}%' OR {$this->db->dbprefix('purchases')}.grand_total LIKE '%{$search}%' OR {$this->db->dbprefix('purchases')}.paid LIKE '%{$search}%' OR {$this->db->dbprefix('purchases')}.payment_status LIKE '%{$search}%' OR {$this->db->dbprefix('purchases')}.cf1 LIKE '%{$search}%' OR {$this->db->dbprefix('purchases')}.status LIKE '%{$search}%' )";
            }

            if ($start_date && $end_date) {
                $date_range = "({$this->db->dbprefix('purchases')}.date BETWEEN '{$start_date}' AND '{$end_date}')";
            }

            $purchases = $this->purchases_model->getAllPurchases($where, $date_range, $limit, $offset);

            if (!$purchases) {
                throw new Exception(lang('not_found'), 404);
            }

            if (count($purchases) > 500) {
                throw new Exception("Data More Than 500");
            }

            $response = [
                "total_purchases" => count($purchases),
                "list_purchases" => $purchases
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Purchases success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_purchases_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $id_purchases = $this->input->get('id_purchases');

            $purchase = $this->purchases_model->getPurchaseByID($id_purchases, $auth->company->id);

            if (!$purchase) {
                throw new Exception(lang('not_found'), 404);
            }

            $purchase_items = $this->purchases_model->getAllPurchaseItems($purchase->id);

            if (!$purchase_items) {
                throw new Exception(lang('not_found'), 404);
            }

            $response = [
                "purchase" => $purchase,
                "purchase_items" => $purchase_items
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Purchases success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_goods_received_get()
    {
        $this->db->trans_begin();
        try {
            $auth         = $this->authorize();
            $start_date   = $this->input->get('start_date');
            $end_date     = $this->input->get('end_date');
            $search       = $this->input->get('search');
            $warehouse_id = $this->input->get('warehouse_id');
            $gr_status    = $this->input->get('goods_received_status');
            $limit        = $this->input->get('limit');
            $offset       = $this->input->get('offset');
            $sortby       = $this->input->get('sortby');
            $sorttype     = $this->input->get('sorttype');

            $where = "{$this->db->dbprefix('deliveries_smig')}.biller_id = {$auth->company->id}";
            if ($search) {
                $where .= " AND ({$this->db->dbprefix('deliveries_smig')}.no_so LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries_smig')}.no_do LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries_smig')}.no_spj LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries_smig')}.no_polisi LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries_smig')}.nama_sopir LIKE '%{$search}%' OR {$this->db->dbprefix('deliveries_smig')}.status_penerimaan LIKE '%{$search}%' )";
            }

            if ($warehouse_id) {
                $where .= " AND {$this->db->dbprefix('deliveries_smig')}.warehouse_id = $warehouse_id";
            }

            if ($gr_status) {
                $where .= " AND {$this->db->dbprefix('deliveries_smig')}.status_penerimaan = '$gr_status'";
            }

            if ($start_date && $end_date) {
                $date_range = "({$this->db->dbprefix('deliveries_smig')}.tanggal_do BETWEEN '{$start_date}' AND '{$end_date}')";
            }

            if ($limit || $offset || $sortby || $sorttype) {
                $deliveries_smig        = $this->purchases_model->getAllDeliverySmigPagination($where, $date_range, $limit, $offset, $sortby, $sorttype);
                if (!$limit) {
                    if ($offset || $sortby || $sorttype) {
                        if (count($deliveries_smig) > 500) {
                            throw new Exception("Data More Than 500");
                        }
                    }
                }
                $all_deliveries_smig    = $this->purchases_model->getDeliverySmigAll($where, $date_range);
                if (!$all_deliveries_smig) {
                    throw new Exception(lang('not_found'), 404);
                }
            } else {
                $deliveries_smig = $this->purchases_model->getAllDeliverySmig($where, $date_range);

                if (count($deliveries_smig) > 500) {
                    throw new Exception("Data More Than 500");
                }
            }

            if (!$deliveries_smig) {
                throw new Exception(lang('not_found'), 404);
            }

            if ($limit != null) {
                $response = [
                    "limit"                => $limit,
                    "offset"               => $offset,
                    "rows"                 => $all_deliveries_smig,
                    "count"                => count($deliveries_smig),
                    "list_goods_received"  => $deliveries_smig
                ];
            } else {
                $response = [
                    "rows"                 => count($deliveries_smig),
                    "list_goods_received"  => $deliveries_smig
                ];
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Goods Received success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
    public function add_purchases_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $config = [
                [
                    'field' => 'date',
                    'label' => 'Date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'warehouse_id',
                    'label' => 'Warehouse',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'supplier_id',
                    'label' => 'Supplier',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'status',
                    'label' => 'Status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ]
            ];

            $config_item_purchases = [
                [
                    'field' => 'product_id',
                    'label' => 'Product Id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'price',
                    'label' => 'Price',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'quantity',
                    'label' => 'Quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $purchases_items = $this->body('products');
            if (!$purchases_items) {
                throw new Exception('Purchases Items Not Found', 404);
            }

            foreach ($purchases_items as $key => $purchases_items) {
                $this->form_validation->set_data($purchases_items);
                $this->form_validation->set_rules($config_item_purchases);

                if ($this->form_validation->run() == false) {
                    $errors = $this->form_validation->error_array();
                    foreach ($errors as $error) {
                        break;
                    }
                    throw new Exception($error, 400);
                }
            }

            $reference                  = $this->body('reference_no') ? $this->body('reference_no') : $this->site->getReference('po');
            $date                       = date("Y-m-d H:i:s", strtotime($this->body('date')));
            $warehouse_id               = $this->body('warehouse_id');
            $supplier_id                = $this->body('supplier_id');
            $company_id                 = $auth->company->id;
            $user_id                    = $auth->user->id;
            $status                     = $this->body('status');
            $shipping                   = $this->body('shipping') ? $this->post('shipping') : 0;
            $supplier_details           = $this->site->getCompanyByID($supplier_id);
            $supplier                   = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note                       = $this->sma->clear_tags($this->body('note'));
            $payment_term               = $this->body('payment_term');
            $due_date                   = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping_date              = $this->body('delivery_date') ? $this->sma->fsd(trim($this->body('delivery_date'))) : null;
            $discount_purchases         = $this->body('discount') ? $this->body('discount') : null;
            $receiver                   = $this->body('acceptor') ? $this->body('acceptor') : null;
            $total                      = 0;
            $product_tax                = 0;
            $order_tax                  = 0;
            $product_discount           = 0;
            $order_discount             = 0;
            $percentage                 = '%';
            $purchases_items_products   = $this->body('products');
            $no_si_spj                  = $this->body('no_si_spj') ? $this->body('no_si_spj') : null;
            $no_si_do                   = $this->body('no_si_do') ? $this->body('no_si_do') : null;
            $no_si_so                   = $this->body('no_si_so') ? $this->body('no_si_so') : null;
            $no_si_billing              = $this->body('no_si_billing') ? $this->body('no_si_billing') : null;
            $cf2                        = $this->body('cf2');

            foreach ($purchases_items_products as $row) {
                $product_details    = $this->purchases_model->getProductByID($row['product_id']);
                $item_code          = $product_details->code;
                $item_net_cost      = $this->sma->formatDecimal($row['price'] ? $row['price'] : $row['price']);
                $unit_cost          = $this->sma->formatDecimal($row['price'] ? $row['price'] : $row['price']);
                $real_unit_cost     = $this->sma->formatDecimal($row['price'] ? $row['price'] : $row['price']);
                $item_unit_quantity = $row['quantity'];
                $item_option        = isset($row['product_option']) && $row['product_option'] != 'false' ? $row['product_option'] : null;
                $item_tax_rate      = isset($row['product_tax']) ? $row['product_tax'] : null;
                $item_discount      = isset($row['product_discount']) ? $row['product_discount'] : null;
                $item_expiry        = (isset($row['expiry']) && !empty($row['expiry'])) ? $this->sma->fsd($row['expiry']) : null;
                $supplier_part_no   = (isset($row['part_no']) && !empty($row['part_no'])) ? $row['part_no'] : null;
                $item_unit          = $product_details->unit;
                $item_quantity      = $row['quantity'];
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            throw new \Exception('Masalah Tanggal Kedaluwarsa Produk' . ' (' . $product_details->name . ')');
                        }
                    }
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

                    $products[] = [
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
                        'date'              => date('Y-m-d H:i:s', strtotime($date)),
                        'status'            => $status,
                        'supplier_part_no'  => $supplier_part_no,
                    ];
                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }

            if (empty($products)) {
                throw new \Exception('Barang Pesanan Tidak Boleh Kosing');
            } else {
                krsort($products);
            }
            if ($discount_purchases) {
                $order_discount_id = $discount_purchases;
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
            if (!$no_si_so) {
                $cf1 = $this->input->post('cf1');
            } else {
                $cf1 = 'PO/' . date('Y/m/') . $this->input->post('no_si_so');
            }

            $data = [
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
                'created_by'        => $user_id,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'company_id'        => $company_id,
                'sino_spj'          => $no_si_spj,
                'sino_do'           => $no_si_do,
                'sino_so'           => $no_si_so,
                'sino_billing'      => $no_si_billing,
                'shipping_date'     => $shipping_date != '0000-00-00' ? date('Y-m-d', strtotime($shipping_date)) : NULL,
                'receiver'          => $receiver,
                'cf1'               => $cf1,
                'cf2'               => $cf2,
            ];
            $addPurchase = $this->purchases_model->addPurchase($data, $products);

            if (!$addPurchase) {
                throw new Exception("Post Add Purchase failed");
            }

            $response = [
                "purchase" => [
                    "id" => $addPurchase,
                    "reference_no" => $reference
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Purchase success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function edit_purchase_put()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'Date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'warehouse_id',
                    'label' => 'Warehouse',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'supplier_id',
                    'label' => 'Supplier',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'status',
                    'label' => 'Status',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ]
            ];

            $config_item_purchases = [
                [
                    'field' => 'product_id',
                    'label' => 'Product Id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'price',
                    'label' => 'Price',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'quantity',
                    'label' => 'Quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $purchases_items = $this->body('products');
            if (!$purchases_items) {
                throw new Exception(lang('purchases_items_not_found'), 404);
            }

            foreach ($purchases_items as $key => $purchases_item) {
                $this->validate_form($config_item_purchases, $purchases_item);
            }

            $id_purchase = $this->input->get('id_purchase');

            $inv                       = $this->purchases_model->getPurchaseByID($id_purchase);
            $purchase_items_in_costing = $this->site->getPurchaseInCosting($id_purchase);

            $base_purchase_items = $this->purchases_model->getAllPurchaseItems($id_purchase);

            
            // var_dump($base_purchase_items);die;

            if (empty($inv)) {
                throw new \Exception(lang('purchases_not_found'));
            }

            if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
                throw new \Exception(lang('purchase_x_action'));
            }

            if ($purchase_items_in_costing) {
                throw new \Exception(lang('purchase_has_transaction'));
            }

            $reference                  = $this->body('reference_no');
            $date                       = date("Y-m-d H:i:s", strtotime($this->body('date')));
            $warehouse_id               = $this->body('warehouse_id');
            $supplier_id                = $this->body('supplier_id');
            $company_id                 = $auth->company->id;
            $user_id                    = $auth->user->id;
            $status                     = $this->body('status');
            $shipping                   = $this->body('shipping') ? $this->post('shipping') : 0;
            $supplier_details           = $this->site->getCompanyByID($supplier_id);
            $supplier                   = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note                       = $this->sma->clear_tags($this->body('note'));
            $payment_term               = $this->body('payment_term');
            $due_date                   = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping_date              = $this->body('delivery_date') ? $this->sma->fsd(trim($this->body('delivery_date'))) : null;
            $discount_purchases         = $this->body('discount') ? $this->body('discount') : null;
            $receiver                   = $this->body('acceptor') ? $this->body('acceptor') : null;
            $total                      = 0;
            $product_tax                = 0;
            $order_tax                  = 0;
            $product_discount           = 0;
            $order_discount             = 0;
            $percentage                 = '%';
            // $purchases_items_products   = $this->body('products');
            /*$no_si_spj                  = $this->body('no_si_spj') ? $this->body('no_si_spj') : null;
            $no_si_do                   = $this->body('no_si_do') ? $this->body('no_si_do') : null;
            $no_si_so                   = $this->body('no_si_so') ? $this->body('no_si_so') : null;
            $no_si_billing              = $this->body('no_si_billing') ? $this->body('no_si_billing') : null;*/
            $cf2                        = $this->body('cf2');
            foreach ($purchases_items as $row) {
                $product_details    = $this->purchases_model->getProductByID($row['product_id']);
                $item_code          = $product_details->code;
                $item_net_cost      = $this->sma->formatDecimal($row['price'] ? $row['price'] : $row['price']);
                $unit_cost          = $this->sma->formatDecimal($row['price'] ? $row['price'] : $row['price']);
                $real_unit_cost     = $this->sma->formatDecimal($row['price'] ? $row['price'] : $row['price']);
                $item_unit_quantity = $row['quantity'];
                $quantity_received  = $row['received_quantity'];
                $item_option        = isset($row['product_option']) && $row['product_option'] != 'false' ? $row['product_option'] : null;
                $item_tax_rate      = isset($row['product_tax']) ? $row['product_tax'] : null;
                $item_discount      = isset($row['product_discount']) ? $row['product_discount'] : null;
                $item_expiry        = (isset($row['expiry']) && !empty($row['expiry'])) ? $this->sma->fsd($row['expiry']) : null;
                $supplier_part_no   = (isset($row['part_no']) && !empty($row['part_no'])) ? $row['part_no'] : null;
                $item_unit          = $product_details->unit;
                $item_quantity      = $row['quantity'];

                $key_item_base      = array_search($row['product_id'],array_column($base_purchase_items, 'product_id') );
                $quantity_balance   = is_int($key_item_base) ? $base_purchase_items[$key_item_base]->quantity : 1 ;
                $ordered_quantity   = is_int($key_item_base) ? $base_purchase_items[$key_item_base]->quantity : 1 ;

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

            
            // var_dump();die;
            
            $updatePurchase = $this->purchases_model->updatePurchase($id_purchase, $data, $products);

            if(!$updatePurchase){
                throw new Exception("Post Update Purchase failed");
            }

            $response = [
                "purchase" => [
                    "id" => $id_purchase,
                    "reference_no" => $reference
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Update Purchase success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_goods_received_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $id_gr = $this->input->get('id_goods_received');

            $delivery_smig = $this->purchases_model->getDeliveriesSmigByID($id_gr, $auth->company->id);

            if (!$delivery_smig) {
                throw new Exception(lang('not_found'), 404);
            }

            $delivery_smig_items = $this->purchases_model->getAllDeliveriesSmigtems($id_gr, $auth->company->id);

            if (!$delivery_smig_items) {
                throw new Exception(lang('not_found'), 404);
            }

            $response = [
                "good_received" => $delivery_smig,
                "good_received_items" => $delivery_smig_items
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Goods Received success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_gr_to_po_post()
    {
        $this->db->trans_begin();
        try {
            $auth             = $this->authorize();
            $id_gr            = $this->input->get('id_goods_received');

            $delivery_smig    = $this->purchases_model->getDeliveriesSmigByID($id_gr, $auth->company->id);

            if (!$delivery_smig) {
                throw new Exception(lang('Not Found'), 404);
            }

            $delivery_smig_items    = $this->purchases_model->getAllDeliveriesSmigtems($id_gr, $auth->company->id);
            if (!$delivery_smig_items) {
                throw new Exception(lang('Not Found'), 404);
            }

            $cek_purchases        = $this->purchases_model->getPurchasesByDOSpjSo($delivery_smig->no_do, $delivery_smig->no_spj, $delivery_smig->no_so);
            if ($cek_purchases) {
                throw new Exception('Post Goods Received failed, Because good received data has already been made purchases', 404);
            }
            $head                 = $delivery_smig;
            $items                = $delivery_smig_items;
            $company              = $this->site->getCompanyByID($head->biller_id);
            $warehouse            = $this->site->getWarehouseIfNull($company->id, $company->company);
            $reference            = $this->post('reference_no') ? $this->post('reference_no') : $this->site->getReference('po');
            $date                 = date("Y-m-d H:i:s");
            $warehouse_id         = $this->post('warehouse_id') ? $this->post('warehouse_id') : ($head->warehouse_id ? $head->warehouse_id : $warehouse->id);
            $supplier_id          = $head->supplier_id;
            $company_id           = $head->biller_id;
            $user_id              = $this->post('user_id') ? $this->post('user_id') : null;
            $status               = $this->post('status') ? $this->post('status') : 'received';
            $shipping             = $this->post('shipping') ? $this->post('shipping') : 0;
            $supplier_details     = $this->site->getCompanyByID($supplier_id);
            $supplier             = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note                 = $this->sma->clear_tags($this->post('note'));
            $payment_term         = $this->post('payment_term');
            $due_date             = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping_date        = $head->tanggal_do ? $head->tanggal_do : null;
            $discount_purchases   = $this->post('discount') ? $this->post('discount') : null;
            $receiver             = $this->post('acceptor') ? $this->post('acceptor') : $this->session->userdata('user_id');
            $total                = 0;
            $product_tax          = 0;
            $order_tax            = 0;
            $product_discount     = 0;
            $order_discount       = 0;
            $percentage           = '%';
            $no_si_spj            = $head->no_spj ? $head->no_spj : ($this->post('no_si_spj') ? $this->post('no_si_spj') : null);
            $no_si_do             = $head->no_do ? $head->no_do : ($this->post('no_si_do') ? $this->post('no_si_do') : null);
            $no_si_so             = $head->no_so ? $head->no_so : ($this->post('no_so') ? $this->post('no_so') : null);
            $no_si_billing        = $this->post('no_si_billing') ? $this->post('no_si_billing') : null;
            $cf2                  = $this->post('cf2');
            $price                = $this->post('price');

            foreach ($items as $row) {
                $item_code          = $row->product_code;
                $product_details    = $this->purchases_model->getProductByCode($item_code);
                $item_net_cost      = $this->sma->formatDecimal($price ? $price : ($product_details->net_cost ? $product_details->net_cost : $row->real_unit_price));
                $unit_cost          = $this->sma->formatDecimal($price ? $price : ($product_details->unit_cost ? $product_details->unit_cost : $row->unit_price));
                $real_unit_cost     = $this->sma->formatDecimal($price ? $price : ($product_details->real_unit_cost ? $product_details->real_unit_cost : $row->net_unit_price));
                $item_unit_quantity = (int) $row->quantity;
                $item_option        = isset($row->product_option) && $row->product_option != 'false' ? $row->option_id : null;
                $item_tax_rate      = isset($row->product_tax) ? $row->product_tax : null;
                $item_discount      = isset($row->product_discount) ? $row->discount : 0;
                $item_expiry        = (isset($row->expiry) && !empty($row->expiry)) ? $this->sma->fsd($row->expiry) : null;
                $supplier_part_no   = (isset($row->part_no) && !empty($row->part_no)) ? $row->part_no : null;
                $item_unit          = $product_details->unit;
                $item_quantity      = $row->quantity;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            throw new \Exception('Post Goods Received failed, Because problem of product expiration date' . ' (' . $product_details->name . ')');
                        }
                    }
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

                    $products[] = [
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
                        'date'              => date('Y-m-d H:i:s', strtotime($date)),
                        'status'            => $status,
                        'supplier_part_no'  => $supplier_part_no,
                    ];
                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
                if (empty($products)) {
                    throw new \Exception('Post Goods Received failed, Because ordered items may not be empty');
                } else {
                    krsort($products);
                }
                if ($discount_purchases) {
                    $order_discount_id = $discount_purchases;
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
                if (!$no_si_so) {
                    $cf1 = $this->input->post('cf1');
                } else {
                    $cf1 = 'PO/' . date('Y/m/') . $this->input->post('no_si_so');
                }
                $data = [
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
                    'created_by'        => $user_id,
                    'payment_term'      => $payment_term,
                    'due_date'          => $due_date,
                    'company_id'        => $company_id,
                    'sino_spj'          => $no_si_spj,
                    'sino_do'           => $no_si_do,
                    'sino_so'           => $no_si_so,
                    'sino_billing'      => $no_si_billing,
                    'shipping_date'     => $shipping_date != '0000-00-00' ? date('Y-m-d', strtotime($shipping_date)) : NULL,
                    'receiver'          => $receiver,
                    'cf1'               => $cf1,
                    'cf2'               => $cf2,
                ];
            }
            $response = [
                "good_received_data"  => 'Post Head Goods Received success',
                "good_received_items" => 'Post Item Goods Received success'
            ];
            if ($this->purchases_model->addPurchase($data, $products)) {
                $this->db->trans_commit();
                $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Goods Received success", $response);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}

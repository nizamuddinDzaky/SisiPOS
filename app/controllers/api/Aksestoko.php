<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_REST_Controller.php';

/** 
 * Aksestoko
 **/ 
class Aksestoko extends MY_REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->data = $this->getTokenValue();

        $this->Admin = true;
    }

    

    public function token_post()
    {
        try {
            $token = json_encode($this->post());
            $token = $this->encrypt($token, $this->key);
            $this->buildResponse("success", REST_Controller::HTTP_OK, "token generated", ["token" => $token]);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode(), $th->getMessage(), null);
        }
    }

    public function whoami_post()
    {
        try {
            $token = $this->post('token');
            $token = $this->decrypt($token, $this->key);
            $this->buildResponse("success", REST_Controller::HTTP_OK, "token decrypted", json_decode($token));
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode(), $th->getMessage(), null);
        }
    }

    public function customers_post()
    {
        $this->db->trans_begin();
        try {
            $warehouse = $this->input->get('warehouse');
            $this->auth = $this->getAuthMaster($this->data, $warehouse);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }
            
            //validation
            $config = [
                [
                    'field' => 'customer_name',
                    'label' => 'Customer Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_email',
                    'label' => 'Customer Email',
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => '%s required',
                        'valid_email' => '%s is not a valid email address'
                    ],
                ],
                [
                    'field' => 'customer_store',
                    'label' => 'Customer Store',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_address',
                    'label' => 'Customer Address',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_city',
                    'label' => 'Customer City',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_postal_code',
                    'label' => 'Customer Postal Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_province',
                    'label' => 'Customer Province',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_phone',
                    'label' => 'Customer Phone',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_district',
                    'label' => 'Customer District',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_village',
                    'label' => 'Customer Village',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_region',
                    'label' => 'Customer Region',
                    'rules' => 'required|in_list[1,2,3]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s must be 1, 2 or 3'
                    ],
                ],
                [
                    'field' => 'customer_code',
                    'label' => 'Customer Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'customer_active',
                    'label' => 'Customer Active',
                    'rules' => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s must be 0 or 1'
                    ],
                ],
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) break;
                throw new Exception($error, 400);
            }
            
            $this->load->model('companies_model');
            $this->load->model('aksestoko/at_site_model', 'at_site');
            $check_update = $this->at_site->findCompanyByCf1AndCompanyId($this->auth->company->id, 'IDC-'.$this->post("customer_code"), false);

            $data = array(
                'name' => $this->post("customer_name"),
                'email' => $this->post("customer_email"),
                'group_id' => 3,
                'group_name' => 'customer',
                'customer_group_id' => 1,
                'customer_group_name' => 'General',
                'company' => $this->post("customer_store"), 'company_id'=> $this->auth->company->id,
                'address' => $this->post("customer_address"),
                'city' => $this->post("customer_city"),
                'postal_code' => $this->post("customer_postal_code"),
                'country' => $this->post("customer_province"),
                'phone' => $this->post("customer_phone"),
                'state' => $this->post("customer_district"),
                'village' => $this->post("customer_village"),
                'region' => $this->post("customer_region"),
                'cf1' => 'IDC-'.$this->post("customer_code"),
                'is_active' =>$this->post("customer_active"),
            );
            if ($check_update) {
                $data['customer_group_id'] = $check_update->customer_group_id;
                $data['customer_group_name'] = $check_update->customer_group_name;
                if (!$this->companies_model->updateCompany($check_update->id, $data)) {
                    throw new Exception("update customer failed");
                }
                $msg = 'update customer success';
                $id = $check_update->id;
            } else {
                $msg = 'add customer success';
                $id = $this->companies_model->addCompany($data);
                if (!$id) {
                    throw new Exception("add customer failed");
                }
            }
            $response = [
                'customer_id' => $id,
                'customer_code' => $this->post("customer_code")
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, $msg, $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function products_post()
    {
        $this->db->trans_begin();
        try {
            
            $warehouse = $this->input->get('warehouse');
            $this->auth = $this->getAuthMaster($this->data, $warehouse);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            //validation baru
            $config = [
                [
                    'field' => 'product_code',
                    'label' => 'Product Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'product_name',
                    'label' => 'Product Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'product_brand',
                    'label' => 'Product Brand',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'product_cost',
                    'label' => 'Product Cost',
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                        'greater_than' => '%s must be greater than 0'
                    ],
                ],
                [
                    'field' => 'product_price',
                    'label' => 'Product Price',
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                        'greater_than' => '%s must be greater than 0'
                    ],
                ],
                [
                    'field' => 'product_credit_price',
                    'label' => 'Product Credit Price',
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                        'greater_than' => '%s must be greater than 0'
                    ],
                ],
                [
                    'field' => 'product_active',
                    'label' => 'Product Active',
                    'rules' => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s must be 0 or 1'
                    ],
                ],
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            }

            $this->load->model('products_model');

            $str_brand = strtolower($this->post("product_brand"));
            $brand = 1;
            $unit = 1;
            if ($str_brand == 'sg') {
                $brand = 2;
                $unit = 29;
            } elseif ($str_brand == 'sp') {
                $brand = 3;
                $unit = 29;
            } elseif ($str_brand == 'st') {
                $brand = 4;
                $unit = 29;
            } elseif($str_brand == 'sbi'){
                $br = $this->products_model->getBrandByCode("SBI");
                $brand = $br ? $br->id : null;
                $unit = 29;
            }

            $product = $this->products_model->getProductByCode($this->post("product_code"), $this->auth->company->id);
            $data = [
                "code" => $this->post("product_code"),
                "name" => $this->post("product_name"),
                "brand" => $brand,
                "cost" => $this->post("product_cost"),
                "price" => $this->post("product_price"),
                "is_retail" => $this->post("product_active"),
                "credit_price" => $this->post("product_credit_price"),
                'unit' => $unit,
                'company_id' => $this->auth->company->id,
                'barcode_symbology' => 'code128',
                'type' => 'standard',
                'category_id' => '2',
                'sale_unit' => $unit,
                'purchase_unit' => $unit,
                'alert_quantity' => 0,
                'track_quantity' => 0,
                'product_details' => '',
                'quantity' => '1000000000'
            ];
            $warehouse_qty = [];

            $warehouse_qty[] = [
                'warehouse_id' => $this->auth->user->warehouse_id,
                'quantity' => $data['quantity'],
                'rack' => null,
                'company_id' => $this->auth->company->id
            ];

            if ($product) {
                $data['quantity'] = $product->quantity;
                if (!$this->products_model->updateProduct($product->id, $data, null, null, null, null, null)) {
                    throw new Exception("update product failed");
                }
                $msg = 'update product success';
                $id = $product->id;
            } else {
                $msg = 'add product success';
                $id = $this->products_model->addProduct($data, null, $warehouse_qty, null, null);
                if (!$id) {
                    throw new Exception("add product failed");
                }
            }
            $response = [
                'product_id' => $id,
                'product_code' => $this->post("product_code")
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, $msg, $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function orders_post()
    {

        $this->db->trans_begin();
        try {

            //validation
            $config = [
                [
                    'field' => 'order_ref',
                    'label' => 'Order Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'order_grand_total',
                    'label' => 'Order Grand Total',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'order_status',
                    'label' => 'Order Status',
                    'rules' => 'required|in_list[pending,canceled,confirmed,completed]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s value must be pending, canceled, confirmed or completed'
                    ],
                ]
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            }

            $arr = explode('-',$this->post("order_ref"));
            $order_ref = trim($arr[0]);
            $company_id = trim($arr[1]);
            $this->auth = $this->getAuthTransaction($this->data, $company_id);

            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            $this->load->model('sales_model');
            $this->load->model('site');

            $sales = $this->sales_model->getSalesByRefNo($order_ref, $this->auth->company->id);
            if(!$sales){
                throw new \Exception("order not found", 400);
            }
            $saleItem = $this->sales_model->getSaleItemsBySaleId($sales->id);
            if($sales->client_id != 'atl'){
                $purchase = $this->sales_model->getPurchasesByRefNo($sales->reference_no, $sales->company_id);
                $purchaseItem =  $this->site->getAllPurchaseItems($purchase->id);
            }
            $newStatus = $this->post("order_status");

            if($newStatus == 'completed' && $sales->sale_type == 'booking'){
                $newStatus = 'reserved';
            }
            
            if ($sales->is_updated_price == 1) {
                throw new Exception("already update order price. wait until store confirmed.");
            }

            if ($sales->grand_total != $this->post('order_grand_total') && ($newStatus != 'pending' && $newStatus != 'canceled')) {
                throw new Exception("grand total has been changed. status cannot be changed to $newStatus.");
            }

            if($newStatus != 'canceled'){
                $charge = $this->post('order_grand_total') - $sales->grand_total;

                if ($charge != 0 || $charge != '0') {
                    $sales->charge = $charge;
                    if($sales->client_id != 'atl'){
                        $purchase->charge = $charge;
                        $purchase->grand_total = $this->post("order_grand_total");
                    }
                    $sales->grand_total = $this->post("order_grand_total");
                    $sales->is_updated_price = 1;
                }
            }

            $itemPurchase = [];
            $itemSale = [];

            foreach ($saleItem as $keySaleItem => $valueSaleItem) { 
                $itemSale[]=(array)$valueSaleItem;
            }

            if($sales->client_id != 'atl'){
                foreach ($purchaseItem as $keyPurchaseItem => $valuePurchaseItem) { 
                    $itemPurchase[]=(array)$valuePurchaseItem;
                }
            }

            $sales->staff_note = "updated from API with token : ".$this->input->get_request_header("Authorization");
            if($sales->client_id != 'atl'){
                if (!$this->sales_model->updateOrder($this->auth->company->id, $newStatus, $purchase, $sales, $itemPurchase, $itemSale)) {
                    throw new Exception("update order failed. unknown error.");
                }
            } else {
                if (!$this->sales_model->updateOrderATL($this->auth->company->id, $newStatus, $sales, $itemSale)) {
                    throw new Exception("update order failed. unknown error.");
                }
                $this->load->model('integration_atl_model', 'integration_atl');
                $call_api = $this->integration_atl->update_order_atl($sales->id);
                if(!$call_api){
                    throw new \Exception(lang('failed') . " -> Call API Update Order ATL");
                }
            }

            $this->db->trans_commit();
            $response = [
                'order_id' => $sales->id,
                'order_ref' => $this->post("order_ref")
            ];
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'update order success', $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function payments_post()
    {
        $this->db->trans_begin();
        try {

            // $this->auth = $this->getAuthTransaction($this->data, $this->post("payment_order_ref"));
            
            // if (!$this->auth) {
            //     throw new \Exception("unauthorized", 401);
            // }
        
            //validation
            $config = [
                [
                    'field' => 'payment_ref',
                    'label' => 'Payment Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'payment_order_ref',
                    'label' => 'Payment Order Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'payment_status',
                    'label' => 'Payment Status',
                    'rules' => 'required|in_list[accept,reject]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s value must be accept or reject'
                    ],
                ],
                [
                    'field' => 'payment_receipt_image',
                    'label' => 'Payment Receipt Image',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'payment_nominal',
                    'label' => 'Payment Nominal',
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s only numeric value',
                        'greater_than' => '%s must be greater than 0'
                    ],
                ],
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            }

            $arr = explode('-',$this->post("payment_order_ref"));
            $order_ref = trim($arr[0]);
            $company_id = trim($arr[1]);
            $this->auth = $this->getAuthTransaction($this->data, $company_id);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }
            
            $this->load->model('sales_model');
            $this->load->model('aksestoko/payment_model');
    
            $sales = $this->sales_model->getSalesByRefNo($order_ref, $this->auth->company->id);
            if(!$sales){
                throw new \Exception("order not found", 400);
            }
            $payment_tmp = $this->sales_model->findPaymentTmpByRef($this->post("payment_ref"), $sales->id);
            $purchase = $this->sales_model->getPurchasesByRefNo($sales->reference_no, $sales->company_id);
            
            // if ($sales->paid >= $sales->grand_total) {
            //     throw new Exception("payment already paid", 400);
            // }
            // if ($this->post("payment_nominal") > ($sales->grand_total - $sales->paid)) {
            //     throw new Exception("payment nominal too much", 400);
            // }
 
            if ($payment_tmp) {
                if ($payment_tmp->status != 'pending') {
                    throw new \Exception("payment already executed (accepted/rejected)", 400);
                }

                if ($this->post("payment_status") == 'accept') {
                    if (!$this->sales_model->confirm_payment($payment_tmp->id, $this->auth->user->id, $this->auth->company->id)) {
                        throw new \Exception("confirm payment failed");
                    }
                } elseif ($this->post("payment_status") == 'reject') {
                    if (!$this->sales_model->reject_payment($payment_tmp->id)) {
                        throw new \Exception("payment reject failed");
                    }
                }
                $id = $payment_tmp->id;
                $msg = 'update payment success';
                if($sales->client_id == 'atl'){
                    $this->load->model('integration_atl_model', 'integration_atl');
                    $call_api = $this->integration_atl->confirm_payment_atl($id);
                    if(!$call_api){
                        throw new \Exception(lang('failed') . " -> Call API Confirm Payment ATL");
                    }
                }
            } else {
                $dataPaymentTemp = [
                    'purchase_id' => $purchase->id,
                    'sale_id' => $sales->id,
                    'nominal' => $this->post("payment_nominal"),
                    'url_image' => $this->post("payment_receipt_image"),
                    'status' => 'pending',
                    'reference_no' => $this->post("payment_ref")
                ];
                $id = $this->payment_model->addPaymentTemp($dataPaymentTemp);
                $msg = 'add payment success';
                if (!$id) {
                    throw new \Exception("add payment failed");
                }
                if (!$this->sales_model->confirm_payment($id, $this->auth->user->id, $this->auth->company->id)) {
                    throw new \Exception("confirm payment failed");
                }
                if($sales->client_id == 'atl'){
                    $this->load->model('integration_atl_model', 'integration_atl');
                    $call_api = $this->integration_atl->insert_payment_atl($id);
                    if(!$call_api){
                        throw new \Exception(lang('failed') . " -> Call API Insert Payment ATL");
                    }
                }
            }
            $response = [
                'payment_id' => $id,
                'payment_ref' => $this->post("payment_ref")
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, $msg, $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
        // $this->set_response($data, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function deliveries_post()
    {
        $this->db->trans_begin();
        try {
            //validation
            $config = [
                [
                    'field' => 'delivery_ref',
                    'label' => 'Delivery Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'delivery_order_ref',
                    'label' => 'Delivery Order Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'delivery_driver',
                    'label' => 'Delivery Driver',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'delivery_truck_no',
                    'label' => 'Delivery Truck No',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'delivery_status',
                    'label' => 'Delivery Status',
                    'rules' => 'required|in_list[packing,delivering,delivered]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s must be packing, delivering or delivered'
                    ],
                ],
            ];

            //validation
            $config2 = [
                [
                    'field' => 'product_code',
                    'label' => 'Product Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'product_quantity',
                    'label' => 'Product Quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run()==false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            } else {
                foreach ($this->post('delivery_details') as $item) {
                    $this->form_validation->set_data($item);
                    $this->form_validation->set_rules($config2);
                    
                    if ($this->form_validation->run()==false) {
                        $errors = $this->form_validation->error_array();
                        foreach ($errors as $error) {
                            break;
                        }
                        throw new Exception($error, 400);
                    }
                }
            }

            $arr = explode('-',$this->post("delivery_order_ref"));
            $order_ref = trim($arr[0]);
            $company_id = trim($arr[1]);
            $this->auth = $this->getAuthTransaction($this->data, $company_id);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            $this->load->model('sales_model');
            $this->load->model('site');

            $sales = $this->sales_model->getSalesByRefNo($order_ref, $this->auth->company->id);
            if(!$sales){
                throw new \Exception("order not found", 400);
            }
            $delivery = $this->sales_model->getDeliveryBySaleIdAndDeliveryRef($sales->id, $this->post("delivery_ref"));
            $items = $this->post("delivery_details");

            if($sales->sale_type != 'booking' && $sales->sale_status != 'completed' || 
                $sales->sale_type == 'booking' && $sales->sale_status != 'reserved'){
                throw new Exception("order is not completed yet.", 400);
            }

            $company = $this->site->getCompanyByID($sales->customer_id);
            $dlDetails = array(
                'date' => date('Y-m-d H:i:s'),
                'sale_id' => $sales->id,
                'do_reference_no' => $this->post("delivery_ref"),
                'sale_reference_no' => $order_ref,
                'customer' => $sales->customer,
                'address' => $company->address.', '.$company->state.', '.$company->country,
                'status' => $this->post("delivery_status"),
                'delivered_by' => $this->post("delivery_driver") . " (".$this->post("delivery_truck_no").")",
                'created_by' => $this->auth->user->id,
                'client_id' => "aksestoko",
            );

            if ($delivery) {
                if ($delivery->receive_status == "received") {
                    throw new Exception("delivery received. cannot update.", 400);
                }

                $delivery_items = $this->sales_model->getDeliveryItemsByDeliveryId($delivery->id);
                $delivery_items_id = [];
                $sent_quantity = [];
                foreach ($items as $key => $item) {
                    foreach ($delivery_items as $i => $delivery_item) {
                        if ($item['product_code'] == $delivery_item->product_code) {
                            $delivery_items_id[] = $delivery_item->id;
                            $sent_quantity[] = $item['product_quantity'];
                        }
                    }
                }
                
                $deliveryItems = [
                    'delivery_items_id' => $delivery_items_id,
                    'sent_quantity' => $sent_quantity
                ];


                if (!$this->sales_model->updateDelivery($delivery->id, $dlDetails, null, $deliveryItems)) {
                    throw new Exception("update delivery failed. unknown error.", 400);
                }

                if ($sales->sale_type == 'booking') {
                    if($sales->client_id == 'atl'){
                        if ($this->site->checkAutoCloseATL($sale_id)) {
                            $this->sales_model->closeSale($sale_id);
                        }
                    } else {
                        if ($this->site->checkAutoClose($sales->id)) {
                            $this->sales_model->closeSale($sales->id);
                        }
                    }
                }

                if($sales->client_id == 'atl'){
                    $this->load->model('integration_atl_model', 'integration_atl');
                    $call_api = $this->integration_atl->insert_delivery_atl($id);
                    if(!$call_api){
                        throw new \Exception(lang('failed') . " -> Call API Update Delivery ATL");
                    }
                }

                $id = $delivery->id;
                $msg = 'update delivery success';
                $response = [
                    'delivery_id' => $id,
                    'delivery_ref' => $this->post("delivery_ref")
                ];
            } else {
                $saleItem = $this->sales_model->getSaleItemsBySaleId($sales->id);
                $sale_items_id = [];
                $sent_quantity = [];
                foreach ($items as $key => $item) {
                    foreach ($saleItem as $keySaleItem => $valueSaleItem) {
                        if ($item['product_code'] == $valueSaleItem->product_code) {
                            $sale_items_id[] = $valueSaleItem->id;
                            $sent_quantity[] = $item['product_quantity'];
                        }
                    }
                }
                $id = $this->sales_model->addDelivery($dlDetails, null, $sale_items_id, $sent_quantity);
                if (!$id) {
                    throw new Exception("add delivery failed. unknown error.", 400);
                }
                if ($sales->client_id == 'atl') {
                    $data_delivery['atl_doid'] = $id;
                    if(!$this->sales_model->update_deliveries($id, $data_delivery)){
                        throw new Exception("add delivery failed. can not update atl_doid.", 400);
                    }
                    $this->load->model('integration_atl_model', 'integration_atl');
                    $call_api = $this->integration_atl->insert_delivery_atl($id);
                    if(!$call_api){
                        throw new \Exception(lang('failed') . " -> Call API Insert Delivery ATL");
                    }
                }

                $response = [
                    'delivery_id' => $id,
                    'delivery_ref' => $this->post("delivery_ref")
                ];
                $msg = 'add delivery success';
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, $msg, $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function banks_post()
    {
        $this->db->trans_begin();
        try {
            $warehouse = $this->input->get('warehouse');
            $this->auth = $this->getAuthMaster($this->data, $warehouse);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            //validation
            $config = [
                [
                    'field' => 'bank_code',
                    'label' => 'Bank Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'bank_name',
                    'label' => 'Bank Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'bank_account',
                    'label' => 'Bank Account',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'bank_client_name',
                    'label' => 'Bank Client Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'bank_active',
                    'label' => 'Bank Active',
                    'rules' => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s must be 0 or 1'
                    ],
                ],
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            }

            $this->load->model('settings_model');

            $bank = $this->settings_model->findBankByCode($this->post("bank_code"), $this->auth->company->id);

            $dataBank = [ 
                'bank_name' => strtolower($this->post("bank_name")), 
                'no_rekening' => $this->post("bank_account"), 
                'code' => $this->post("bank_code"), 
                'name' => $this->post("bank_client_name"),
                'company_id' => $this->auth->company->id,
                'is_active' => $this->post("bank_active"), 
                'logo'=>'/bank_logo/'.strtolower($this->post("bank_name")).'.png',
            ];

            if ($bank) {
                if (!$this->settings_model->updateBank($bank->id, $dataBank)) {
                    throw new Exception("update bank failed. unknown error.", 400);
                }
                $msg = 'update bank success';
                $id = $bank->id;
            } else {
                $msg = 'add bank success';
                $id = $this->settings_model->addBank($dataBank);
                if (!$id) {
                    throw new Exception("add bank failed");
                }
            }
            $response = [
                'bank_id' => $id,
                'bank_code' => $this->post("bank_code"),
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, $msg, $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function adjustments_post()
    {
        $this->db->trans_begin();
        try {
            $warehouse = $this->input->get('warehouse');
            $this->auth = $this->getAuthMaster($this->data, $warehouse);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            $this->load->model('site');
            $this->load->model('aksestoko/at_site_model');
            $this->load->model('products_model');

            $warehouse = $this->at_site_model->getFirstWarehouseOfCompany($this->auth->company->id); //ngambil warehouse dari company
            $warehouse_product = $this->site->getAllWarehousesProduct($warehouse->id);
            $adjustments_details  = $this->post('adjustment_details');
            $products = [];
            foreach ($adjustments_details as $adjustments_detail) {
                foreach ($warehouse_product as $key => $prod) {
                    if ($adjustments_detail['product_code'] == $prod->code) {
                        if ($adjustments_detail['product_quantity'] < $prod->quantity) {
                            $type = 'subtraction';
                        } else {
                            $type = 'addition';
                        }
                        $products[] = array(
                            'product_id' => $prod->product_id,
                            'type' => $type,
                            'quantity' => abs($adjustments_detail['product_quantity'] - $prod->quantity),
                            'warehouse_id' => $warehouse->id,
                            'option_id' => '',
                            );
                    }
                }
            }
            
            $dataAdjustment = [
                'date' => date('Y-m-d H:i:s'),
                'reference_no'  => $this->site->getReference('qa', $this->auth->company->id),
                'warehouse_id'  => $warehouse->id,
                'note'          => $this->post('adjustment_note'),
                'created_by'    => $this->auth->user->id,
            ];
            
            $adjustment_id = $this->products_model->addAdjustment($dataAdjustment, $products);
            if (!$adjustment_id) {
                throw new Exception("adjustment failed");
            }
            $response = [
                'adjustment_id' => $adjustment_id,
                'adjustments_ref' => $dataAdjustment['reference_no'],
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'adjustment success', $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function correction_post()
    {
        $this->db->trans_begin();
        try {

            //validation
            $config = [
                [
                    'field' => 'order_ref',
                    'label' => 'Order Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'order_grand_total',
                    'label' => 'Order Grand Total',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ]
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            }

            $arr        = explode('-',$this->post("order_ref"));
            $order_ref  = trim($arr[0]);
            $company_id = trim($arr[1]);
            $this->auth = $this->getAuthTransaction($this->data, $company_id);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            $this->load->model('sales_model');
            $this->load->model('site');

            $sales          = $this->sales_model->getSalesByRefNo($order_ref, $this->auth->company->id);
            if(!$sales){
                throw new \Exception("order not found", 400);
            }
            $purchase       = $this->sales_model->getPurchasesByRefNo($sales->reference_no, $sales->company_id);
            
            if($sales->correction_price != NULL && $purchase->correction_price != NULL)
            {
                throw new Exception("Update price can only be executed once");
            }
            if($sales->sale_status != 'completed' && $purchase->status != 'received'){
                throw new Exception("data cannot be changed because the status is not completed or received");
            }
            $correction = $this->post('order_grand_total') - $sales->grand_total;
            if ($correction != 0 || $correction != '0') {
                $sales->correction_price    = $correction;
                $purchase->correction_price = $correction;
                $purchase->grand_total      = $this->post("order_grand_total");
                $sales->grand_total         = $this->post("order_grand_total");
            }
            $sales->is_updated_price    = 2;

            $sales->staff_note    = "updated from API with token : ".$this->input->get_request_header("Authorization");
            if (!$this->sales_model->updateOrderReceived($purchase, $sales)) {
                throw new Exception("update order failed. unknown error.");
            }

            $this->db->trans_commit();
            $response = [
                'order_id'  => $sales->id,
                'order_ref' => $this->post("order_ref")
            ];
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'update order success', $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function receives_post()
    {
        $this->db->trans_begin();
        try {
            //validation
            $config = [
                [
                    'field' => 'receive_order_ref',
                    'label' => 'Receive Order Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'receive_delivery_ref',
                    'label' => 'Receive Delivery Ref',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'receive_date_top',
                    'label' => 'Receive Date TOP',
                    // 'rules' => 'required',
                    // 'errors' => [
                    //     'required' => '%s required',
                    // ],
                ],
                [
                    'field' => 'receive_top',
                    'label' => 'Receive TOP',
                    // 'rules' => 'required',
                    // 'errors' => [
                    //     'required' => '%s required',
                    // ],
                ],
            ];

            //validation
            $config2 = [
                [
                    'field' => 'product_code',
                    'label' => 'Product Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'product_bad',
                    'label' => 'Product Bad',
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                    ],
                ],
                [
                    'field' => 'product_good',
                    'label' => 'Product Good',
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                    ],
                ],
            ];

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            } else {
                foreach ($this->post('receive_details') as $item) {
                    $this->form_validation->set_data($item);
                    $this->form_validation->set_rules($config2);
                    
                    if ($this->form_validation->run() == false) {
                        $errors = $this->form_validation->error_array();
                        foreach ($errors as $error) {
                            break;
                        }
                        throw new Exception($error, 400);
                    }
                }
            }

            $arr = explode('-', $this->post("receive_order_ref"));
            $order_ref = trim($arr[0]);
            $company_id = trim($arr[1]);
            $this->auth = $this->getAuthTransaction($this->data, $company_id);
            // print_r($this->auth);die;
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            $this->load->model('site');
            $this->load->model('aksestoko/at_site_model');
            $this->load->model('aksestoko/at_sale_model');
            $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
            $this->load->model('products_model');
            $this->load->model('sales_model');

            $sales = $this->sales_model->getSalesByRefNo($order_ref, $this->auth->company->id);
            if(!$sales){
                throw new \Exception("order not found", 400);
            }
            $delivery = $this->sales_model->getDeliveryBySaleIdAndDeliveryRef($sales->id, $this->post("receive_delivery_ref"));
            $purchase = $this->sales_model->getPurchasesByRefNo($sales->reference_no, $sales->company_id);
            $delivery_items = $this->at_sale_model->findDeliveryItems($delivery->id);

            $receive_details = $this->post("receive_details");
            $product_code = [];
            $product_good = [];
            $product_bad = [];
            $product_quantity = [];
            $id_delivery_item = [];
            // print_r($delivery_items);die;
            foreach ($delivery_items->items as $key => $delivery_item) {
                $id_delivery_item[] = $delivery_item->id;
                $product_code[$key]=$delivery_item->product_code;
                $product_good[$key]=$delivery_item->good_quantity;
                $product_bad[$key]=$delivery_item->bad_quantity;
                $product_quantity[$key]=$delivery_item->quantity_sent;
                foreach ($receive_details as $receive_detail) {
                    if ($receive_detail['product_code'] == $delivery_item->product_code) {
                        $product_good[$key]=$receive_detail['product_good'];
                        $product_bad[$key]=$receive_detail['product_bad'];
                    }
                }
            }

            $data = [
                'purchase_id' => $purchase->id,
                'product_code' => $product_code,
                'quantity_received' => $product_quantity,
                'do_ref' => $this->post("receive_delivery_ref"),
                'do_id' => $delivery->id,
                'delivery_item_id' => $id_delivery_item,
                'good' => $product_good,
                'bad' => $product_bad,
                'note' => $this->post("recive_delivery_note"),
                'file' => null,
            ];
            if (!$this->at_purchase->confirmReceived($data, $this->auth->user->id, $sales->id, $this->post('receive_top'), $this->post('receive_date_top'))) {
                throw new Exception("receive failed");
            }
            $response = [
                'receive_delivery_id' => $delivery->id,
                'receive_delivery_ref' => $this->post("receive_delivery_ref"),
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'receive success', $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }


    public function price_groups_post(){
        
        $this->db->trans_begin();
        try{
            $warehouse = $this->input->get('warehouse');
            $this->auth = $this->getAuthMaster($this->data, $warehouse);
            
            if (!$this->auth) {
                throw new \Exception("unauthorized", 401);
            }

            //validation
            $config = [
                [
                    'field' => 'customer_code',
                    'label' => 'Customer Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ]
            ];

            //validation
            $config2 = [
                [
                    'field' => 'product_code',
                    'label' => 'Product Code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'product_price',
                    'label' => 'Product Price',
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                        'greater_than' => '%s must be greater than 0'
                    ],
                ],
                [
                    'field' => 'product_credit_price',
                    'label' => 'Product Credit Price',
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                        'greater_than' => '%s must be greater than 0'
                    ],
                ],
                [
                    'field' => 'product_min_order',
                    'label' => 'Product Minimum Order',
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => '%s required',
                        'numeric' => '%s must be numeric value',
                        'greater_than' => '%s must be greater than 0'
                    ],
                ],
                [
                    'field' => 'product_multiple',
                    'label' => 'Product Multiple',
                    'rules' => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => '%s required',
                        'in_list' => '%s must be 0 or 1'
                    ],
                ]
            ];


            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules($config);
            
            if ($this->form_validation->run()==false) {
                $errors = $this->form_validation->error_array();
                foreach ($errors as $error) {
                    break;
                }
                throw new Exception($error, 400);
            } else {
                foreach ($this->post('product_details') as $item) {
                    $this->form_validation->set_data($item);
                    $this->form_validation->set_rules($config2);
                    
                    if ($this->form_validation->run()==false) {
                        $errors = $this->form_validation->error_array();
                        foreach ($errors as $error) {
                            break;
                        }
                        throw new Exception($error, 400);
                    }
                }
            }

            
            $this->load->model('settings_model');
            $this->load->model('products_model');
            $this->load->model('companies_model');
            $this->load->model('aksestoko/at_site_model', 'at_site');

            $check_update = $this->at_site->findCompanyByCf1AndCompanyId($this->auth->company->id, 'IDC-'.$this->post("customer_code"), false);
            if(!$check_update){
                throw new Exception("customer code not found.", 400);
            }

            $list_response_product = [];
            $price_group_name = $this->post('customer_code').'-'.$this->auth->company->id;
            $price_group_data = [
                'name' => $price_group_name,
                'company_id' => $this->auth->company->id,
                'warehouse_id' => $this->auth->user->warehouse_id,
            ];

            $price_group = $this->settings_model->getGroupPriceByName($price_group_name, $this->auth->company->id);
            if($price_group){
                $msg = 'update price group success.';
                if(!$this->settings_model->updatePriceGroup($price_group->id, $price_group_data)){
                    $msg = "update price group failed";
                    throw new Exception($msg, 400);
                }
            }else{
                $msg = 'add price group success.';
                if(!$this->settings_model->addPriceGroup($price_group_data)){
                    $msg = "add price group failed";
                    throw new Exception($msg, 400);
                }
            }

            $price_group = $this->settings_model->getGroupPriceByName($price_group_name, $this->auth->company->id);
            foreach ($this->post('product_details') as $item) {
                $product = $this->products_model->getProductByCode($item['product_code'], $this->auth->company->id);
                
                if ($product && $this->settings_model->setProductPriceForPriceGroup($product->id, $price_group->id, $item['product_price'], $item['product_credit_price'], $item['product_min_order'], $item['product_multiple'])) {
                    $list_response_product[] = [
                        'product_code' => $item['product_code'],
                        "status" => "success"
                    ];
                }else{
                    $list_response_product[] = [
                        'product_code' => $item['product_code'],
                        "status" => "failed"
                    ];
                }
            }

            $customer_data = [
                'price_group_id' => $price_group->id,
                'price_group_name' => $price_group->name
            ];

            if(!$this->companies_model->updateCompany($check_update->id, $customer_data)){
                throw new Exception("failed to update the price group for this customer.", 400);
            }

            $response = [
                "price_group_name" => $price_group_name,
                'company_id' => $check_update->id,
                "product_detail" => $list_response_product
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, $msg, $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
   
}

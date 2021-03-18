<?php defined('BASEPATH') or exit('No direct script access allowed');

require 'MainController.php';

class Master extends MainController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Companies_model', 'companies_model');
    }

    // price_group_id = 
    public function price_groups_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $price_group_id = $this->input->get('price_group_id');
            if ($price_group_id) {
                $price_group_id = $price_group_id == 'null' ? null : $price_group_id;
                $where = ['id' => $price_group_id];
            }

            $response = [];

            $price_groups = $this->site->getPriceGroups($auth->company->id, $where);
            $products = $this->site->getProducts($auth->company->id);
            foreach ($products as $i => $p) {
                $unit = $this->site->getUnitByID($p->sale_unit);
                $p->sale_unit_code = $unit->code;
            }
            foreach ($price_groups as $i => $pg) {

                $customers = $this->site->getCompanyByPriceGroup($pg->id);
                $customers_response = [];
                foreach ($customers as $i => $c) {
                    $isIdc = substr($c->cf1, 0, 4);
                    if ($isIdc == 'IDC-') {
                        $customer_code = substr($c->cf1, 4);
                        $customers_response[] = [
                            'customer_id' => $c->id,
                            'customer_name' => $c->name,
                            'customer_company' => $c->company,
                            'customer_code' => $customer_code
                        ];
                    }
                }

                $products_response = [];
                foreach ($products as $i => $p) {
                    $product_price = $this->site->getProductGroupPrice($p->id, $pg->id);
                    $products_response[] = [
                        'product_id' => $p->id,
                        'product_code' => $p->code,
                        'product_name' => $p->name,
                        'product_uom' => $p->sale_unit_code,
                        'product_price' => (int) ($product_price->price && $product_price->price != 0 ? $product_price->price : $p->price),
                        'product_credit_price' => (int) ($product_price->price_kredit && $product_price->price_kredit != 0 ? $product_price->price_kredit : $p->credit_price),
                        'product_multiple' => $product_price->is_multiple ? true : false,
                        'product_min_order' => (int) ($product_price->min_order && $product_price->min_order != 0 ? $product_price->min_order : 1),
                    ];
                }

                $response[] = [
                    'dist_code'         => $auth->company->cf1,
                    'price_group_id'    => $pg->id,
                    'price_group_name'  => $pg->name,
                    'customers_rows'    => count($customers_response),
                    'customers'         => $customers_response,
                    'products_rows'     => count($products_response),
                    'products'          => $products_response
                ];
            }

            $customers_pg_null = $this->site->getCompanyByPriceGroup(null, $auth->company->id);
            if (count($customers_pg_null) > 0 && !$price_group_id) {

                $customers_response = [];
                foreach ($customers_pg_null as $i => $c) {
                    $isIdc = substr($c->cf1, 0, 4);
                    if ($isIdc == 'IDC-') {
                        $customer_code = substr($c->cf1, 4);
                        $customers_response[] = [
                            'customer_id' => $c->id,
                            'customer_name' => $c->name,
                            'customer_company' => $c->company,
                            'customer_code' => $customer_code
                        ];
                    }
                }

                $products_response = [];
                foreach ($products as $i => $p) {
                    $products_response[] = [
                        'product_id' => $p->id,
                        'product_code' => $p->code,
                        'product_name' => $p->name,
                        'product_uom' => $p->sale_unit_code,
                        'product_price' => (int) $p->price,
                        'product_credit_price' => (int) $p->credit_price,
                        'product_multiple' => false,
                        'product_min_order' => 1,
                    ];
                }

                $response[] = [
                    'dist_code'         => $auth->company->cf1,
                    'price_group_id'    => null,
                    'price_group_name'  => null,
                    'customers_rows'    => count($customers_response),
                    'customers'         => $customers_response,
                    'products_rows'     => count($products_response),
                    'products'          => $products_response
                ];
            }

            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil mendapatkan data price group", ['rows' => count($response), 'data' => $response]);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }

    // customer_code = 
    public function customers_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $customer_code_get = $this->input->get('customer_code');
            if ($customer_code_get) {
                $where = ['cf1' => 'IDC-' . $customer_code_get];
            }

            $customers = $this->companies_model->getCompanyByParent($auth->company->id, $where);
            $customers_response = [];
            foreach ($customers as $i => $c) {
                $isIdc = substr($c->cf1, 0, 4);
                if ($isIdc == 'IDC-') {
                    $customer_code = substr($c->cf1, 4);
                    $customers_response[] = [
                        'customer_id' => $c->id,
                        'customer_name' => $c->name,
                        'customer_company' => $c->company,
                        'customer_code' => $customer_code,
                        'customer_price_group_id' => $c->price_group_id,
                    ];
                }
            }

            $response = [
                'rows' => count($customers_response),
                'data' => $customers_response
            ];

            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil mendapatkan data pelanggan", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }

    //bank_account_number = 
    public function banks_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $bank_account_number = $this->input->get('bank_account_number');
            if ($bank_account_number) {
                $where = ['no_rekening' => $bank_account_number];
            }

            $banks = $this->site->getBanks($auth->company->id, $where);
            $banks_response = [];
            foreach ($banks as $i => $b) {
                $banks_response[] = [
                    'bank_id' => $b->id,
                    'bank_code' => $b->code,
                    'bank_name' => $b->bank_name,
                    'bank_account_number' => $b->no_rekening,
                    'bank_account_name' => $b->name,
                ];
            }

            $response = [
                'rows' => count($banks_response),
                'data' => $banks_response
            ];

            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil mendapatkan data bank", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }

    public function true_post()
    {
        $response = [
            'message' => 'OK',
            'status' => 200,
            'datas' => null
        ];
        return $this->set_response($response, 200);
    }
    
    public function true_get()
    {
        $response = [
            'message' => 'OK',
            'status' => 200,
            'datas' => null
        ];
        return $this->set_response($response, 200);
    }

}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Distributor_Controller.php';

class Products extends MY_API_Distributor_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->load('products', $this->Settings->user_language);
        $this->load->model('products_model');
        $this->load->model('sales_model');
    }

    public function list_products_get()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();
            $search         = $this->input->get('search');
            $limit          = $this->input->get('limit');
            $offset         = $this->input->get('offset');
            $sortby         = $this->input->get('sortby');
            $sorttype       = $this->input->get('sorttype');
            $warehouse_id   = $this->input->get('warehouse_id');
            $consignment    = $this->input->get('consignment') ?? 'no';

            $where = $this->db->dbprefix('products') . ".`company_id` = {$auth->company->id}";

            if ($search) {
                $where .= " AND ({$this->db->dbprefix('products')}.`code` LIKE '%{$search}%' OR {$this->db->dbprefix('products')}.`name` LIKE '%{$search}%')";
            }

            if (!$this->Owner && !$this->Admin && !$warehouse_id) {
                $warehouse_id   = $auth->user->warehouse_id;
            }

            if ($limit || $offset || $sortby || $sorttype) {
                $products       = $this->products_model->getAllProductsPaging($where, $consignment, $warehouse_id, $limit, $offset, $sortby, $sorttype);
                $all_products   = $this->products_model->getProductsAll($where, $consignment, $warehouse_id);
            } else {
                $products = $this->products_model->getAllProduk($where, $consignment, $warehouse_id);
            }

            if (!$products) {
                throw new Exception(lang('not_found'), 404);
            }

            if (!$this->Owner && !$this->Admin) {
                if ($auth->user->show_cost != '1') {
                    foreach ($products as $i => $data) {
                        unset($products[$i]->cost);

                    }
                }
                if ($auth->user->show_price != '1') {
                    foreach ($products as $i => $data) {
                        unset($products[$i]->price);
                    }
                }
            }
            foreach ($products as $i => $data) {
                $cek      = strpos($data->image, 'https://');
                if ($cek !== false) {
                    $products[$i]->image = $data->image;
                } else {
                    $products[$i]->image = base_url() . 'assets/uploads/thumbs/' . $data->image;
                }
            }
            if ($limit != null) {
                $response = [
                    "limit"               => $limit,
                    "offset"              => $offset,
                    "rows"                => $all_products,
                    "count"               => count($products),
                    "list_products"       => $products
                ];
            } else {
                $response = [
                    "rows"          => count($products),
                    "list_products" => $products
                ];
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Products success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_product_purchase_get()
    {
        $this->db->trans_begin();
        try {
            $this->load->model('purchases_model');
            $auth           = $this->authorize();
            $supplier_id    = $this->input->get('supplier_id');

            if(!$supplier_id){
                throw new Exception(lang('not_found'), 404);
            }

            $where = $this->db->dbprefix('products') . ".`company_id` = {$auth->company->id} AND (";
            for( $i = 1 ; $i <= 5 ; $i++){
                $where .= $this->db->dbprefix('products') . ".`supplier" .$i. "` = {$supplier_id} ";
                if($i != 5){
                    $where .= ' OR ';
                }
            }
            $where .= ')';
            $rows = $this->products_model->getAllProduk($where, 'no');
            
            if (!$rows) {
                throw new Exception(lang('not_found'), 404);
            }
            $products = [];
            foreach($rows as $row){
                $row->item_tax_method = $row->tax_method;
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
                $products[]            = $row;
            }

            $response = [
                "rows"          => count($products),
                "list_products" => $products
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Products success", $response);

        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_product_sales_get()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();
            $customer_id    = $this->input->get('customer_id');

            $where = $this->db->dbprefix('products') . ".`company_id` = {$auth->company->id}";

            $products = $this->products_model->getAllProduk($where, 'no');
            $warehouse      = $this->site->getWarehouseByID($warehouse_id);
            $customer       = $this->site->getCompanyByID($customer_id);
            $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
            $rows           = $this->products_model->getAllProduk($where, 'no');

            if ($rows) {
                $c = str_replace(".", "", microtime(true));
                $r = 0;
                foreach ($rows as $row) {
                    $get_wh                 = $this->sales_model->getWarehouseProduct($warehouse_id, $row->id);
                    $option                 = false;
                    $row->qty_wh            = ($get_wh->quantity == null) ? 0 : $get_wh->quantity;
                    $row->qty_book_wh       = ($get_wh->quantity_booking == null) ? 0 : $get_wh->quantity_booking;
                    $row->quantity          = 0;
                    $row->item_tax_method   = $row->tax_method;
                    $row->qty               = 1;
                    $row->cons              = 0;
                    $row->discount          = '0';
                    $row->serial            = '';

                    if ($row->promotion) {
                        $row->price           = $row->promo_price;
                        $row->price_credit    = $row->promo_price;
                    } elseif ($customer->price_group_id) {
                        if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                            $row->price           = $pr_group_price->price != 0 ? $pr_group_price->price : $row->price;
                            $row->price_credit    = $pr_group_price->price_kredit != 0 ? $pr_group_price->price_kredit : $row->credit_price;
                        } else {
                            $row->price = $row->price;
                            $row->price_credit = $row->credit_price;
                        }
                    } elseif ($warehouse->price_group_id) {
                        if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                            $row->price           = $pr_group_price->price != 0 ? $pr_group_price->price : $row->price;
                            $row->price_credit    = $pr_group_price->price_kredit != 0 ? $pr_group_price->price_kredit : $row->credit_price;
                        } else {
                            $row->price           = $row->price;
                            $row->price_credit    = $row->credit_price;
                        }
                    } else {
                        $row->price           = $row->price;
                        $row->price_credit    = $row->credit_price;
                    }

                    $row->price                     = $row->price + (($row->price * $customer_group->percent) / 100);
                    $row->price_credit              = $row->price_credit + (($row->price * $customer_group->percent) / 100);
                    $row->real_unit_price           = $row->price;
                    $row->real_unit_price_credit    = $row->price_credit;
                    $row->base_quantity             = 1;
                    $row->base_unit                 = $row->unit;
                    $row->base_unit_price           = $row->price;
                    $row->unit                      = $row->sale_unit ? $row->sale_unit : $row->unit;
                    $pr[]                           = $row;
                    $r++;
                }
                $response = [
                    "rows"          => count($pr),
                    "list_products" => $pr
                ];
            } else {
                throw new Exception(lang('not_found'), 404);
            }
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Products success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_products_get()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();
            $id_products    = $this->input->get('id_products');

            $products       = $this->products_model->getDetailProductByID($id_products, $auth->company->id);
            
            if (!$products) {
                throw new Exception(lang('not_found'), 404);
            }
            $suppliers = $this->products_model->getSupplierById($products->supplier1, $products->supplier2, $products->supplier3, $products->supplier4, $products->supplier5);
            $strSupplier = "";
            foreach ($suppliers as $key => $supplier) {
                $strSupplier .= "-\t$supplier->name\n";
            }
            $products->supplier = $strSupplier;

            if (!$this->Owner && !$this->Admin) {
                $warehouse_id   = $auth->user->warehouse_id;
            }
            $warehouses = $this->products_model->getAllWarehousesWithPQ($id_products, $warehouse_id);

            if (!$this->Owner && !$this->Admin) {
                if ($auth->user->show_cost != '1') {
                    unset($products->cost);
                }

                if ($auth->user->show_price != '1') {
                    unset($products->price);
                }
            }

            $response = [
                'product'    => $products,
                'warehouses' => $warehouses
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Products success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
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
}

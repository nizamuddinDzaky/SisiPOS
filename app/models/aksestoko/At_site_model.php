<?php defined('BASEPATH') or exit('No direct script access allowed');

// require APPPATH.'/models/Site.php';

class At_site_model extends Site
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getFirstWarehouseOfCompany($company_id)
    {
        $this->db->where('company_id', $company_id);
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findProduct($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findRelationProduct($product, $company)
    {
        $q = $this->db->get_where('products', ['code' => $product->code, 'company_id' => $company->id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        } else {
            if (!$this->insertProduct($product, $company)) {
                throw new Exception("Gagal menambahkan relasi produk.");
            }
            return $this->findRelationProduct($product, $company);
        }
        return false;
    }

    public function findUnit($id)
    {
        $q = $this->db->get_where('units', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function insertProduct($product, $company)
    {
        $requestProduct = [
            'code'              => $product->code,
            'name'              => $product->name,
            'unit'              => $product->unit,
            'cost'              => $product->cost,
            'price'             => $product->price,
            'alert_quantity'    => $product->alert_quantity,
            'image'             => $product->image,
            'thumb_image'       => $product->thumb_image,
            'category_id'       => $product->category_id,
            'company_id'        => $company->id,
            'subcategory_id'    => $product->subcategory_id,
            'cf1'               => $product->cf1,
            'cf2'               => $product->cf2,
            'cf3'               => $product->cf3,
            'cf4'               => $product->cf4,
            'cf5'               => $product->cf5,
            'cf6'               => $product->cf6,
            'quantity'          => 0,
            'tax_rate'          => $product->tax_rate,
            'track_quantity'    => $product->track_quantity,
            'details'           => $product->details,
            'warehouse'         => $product->warehouse,
            'barcode_symbology' => $product->barcode_symbology,
            'file'              => $product->file,
            'product_details'   => $product->product_details,
            'tax_method'        => $product->tax_method,
            'type'              => $product->type,
            'supplier1'         => $product->company_id,
            'supplier1price'    => $product->price,
            'supplier2'         => null,
            'supplier2price'    => null,
            'supplier3'         => null,
            'supplier3price'    => null,
            'supplier4'         => null,
            'supplier4price'    => null,
            'supplier5'         => null,
            'supplier5price'    => null,
            'promotion'         => $product->promotion,
            'promo_price'       => $product->promo_price,
            'start_date'        => $product->start_date,
            'end_date'          => $product->end_date,
            'supplier1_part_no' => null,
            'supplier2_part_no' => null,
            'supplier3_part_no' => null,
            'supplier4_part_no' => null,
            'supplier5_part_no' => null,
            'sale_unit'         => $product->sale_unit,
            'purchase_unit'     => $product->purchase_unit,
            'brand'             => $product->brand,
            'uuid'              => null,
            'is_deleted'        => null,
            'uuid_app'          => null,
            'mtid'              => null,
            'item_id'           => $product->item_id,
            'public'            => $product->public,
            'price_public'      => $product->price_public,
            'weight'            => $product->weight,
            'credit_price'      => $product->credit_price,
            'e_minqty'          => $product->e_minqty,
        ];

        $warehouse_qty = [];

        $warehouse_qty[] = [
            'warehouse_id' => ($this->findCompanyWarehouse($company->id))->id,
            'quantity' => $requestProduct['quantity'],
            'rack' => null,
            'company_id' => $company->id
        ];

        $this->load->model('products_model');

        return $this->products_model->addProduct($requestProduct, null, $warehouse_qty, null, null);

        // return $this->db->insert('products', $requestProduct);
    }

    public function findCompanyWarehouseByPriceGroup($price_group_id, $company_id)
    {
        if ($price_group_id == null) {
            $c = $this->findCompanyWarehouse($company_id);
            return $c->id;
        } else {
            $q = $this->db->get_where('price_groups', ['id' => $price_group_id]);
            if ($q->num_rows() > 0) {
                if ($q->row()->warehouse_id == null || $q->row()->warehouse_id == '' || $q->row()->warehouse_id == 0) {
                    $c = $this->findCompanyWarehouse($company_id);
                    return $c->id;;
                } else {
                    return $q->row()->warehouse_id;
                }
            }
        }
        return false;
    }

    public function findCompanyWarehouse($company_id)
    {
        $q = $this->db->get_where('warehouses', ['company_id' => $company_id, 'is_deleted' => null], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function emptyCart($supplier_id, $user_id)
    {
        return $this->db->delete('carts', [
            'supplier_id' => $supplier_id,
            'user_id' => $user_id
        ]);
    }

    public function insertCart($cart)
    {
        if (!$this->updateQtyCartIfExist($cart)) {
            return $this->db->insert('carts', $cart);
        }
        return true;
    }

    public function updateQtyCartIfExist($cart)
    {
        $where = [
            'product_id' => $cart['product_id'],
            'supplier_id' => $cart['supplier_id'],
            'user_id' => $cart['user_id']
        ];
        $q = $this->db->get_where('carts', $where, 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $this->db->update('carts', [
                'quantity' => ($row->quantity + $cart['quantity'])
            ], ['id' => $row->id]);
        }

        return false;
    }

    public function insertCartMobile($cart)
    {
        if (!$this->updateQtyCartIfExist($cart)) {
            $this->db->insert('carts', $cart);
            $id    = $this->db->insert_id();
            return $id;
        }

        $where = [
            'product_id'  => $cart['product_id'],
            'supplier_id' => $cart['supplier_id'],
            'user_id'     => $cart['user_id']
        ];

        $q = $this->db->get_where('carts', $where, 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row->id;
        }
        return false;
    }
    
    public function getProductInCart($supplier_id, $user_id, $price_group_id = null)
    {
        $price_group_id = $price_group_id ? $price_group_id : "null";
        $this->db->select('sma_products.*, sma_carts.id as id_cart,sma_carts.quantity as cart_qty, sma_product_prices.price as group_price, sma_product_prices.price_kredit as group_kredit, sma_product_prices.min_order, sma_product_prices.is_multiple');
        $this->db->join('sma_products', 'sma_products.id=sma_carts.product_id', 'inner');
        $this->db->join('sma_product_prices', '(sma_product_prices.product_id = sma_products.id && sma_product_prices.price_group_id = ' . $price_group_id . ')', 'left');
        // $this->db->join('sma_v_price_items','(sma_v_price_items.product_id=sma_products.id AND sma_v_price_items.customer_id='.$customer_id.')','left');
        $this->db->where([
            'carts.supplier_id' => $supplier_id,
            'carts.user_id' => $user_id
        ]);
        $q = $this->db->get('carts');
        if ($q->num_rows() > 0) {
            $products = $q->result();
            foreach ($products as $i => $product) {
                $product->price = $product->group_price && $product->group_price > 0 ? $product->group_price : $product->price;
                $unit = $this->findUnit($product->sale_unit);
                $product->price = $this->__operate($product->price, $unit->operation_value, $unit->operator);
            }

            return $products;
        }

        return null;
    }

    public function removeProductInCart($id, $supplier_id, $user_id)
    {
        return $this->db->delete('carts', [
            'id' => $id,
            'supplier_id' => $supplier_id,
            'user_id' => $user_id
        ]);
    }

    public function updateProductInCart($id, $supplier_id, $user_id, $qty)
    {
        return $this->db->update('carts', [
            'quantity' => $qty
        ], [
            'id' => $id,
            'supplier_id' => $supplier_id,
            'user_id' => $user_id
        ]);
    }

    public function findCompany($company_id)
    {
        $this->db->select('companies.*, users.avatar');
        $this->db->join('users', 'users.company_id = companies.company_id');
        $q = $this->db->get_where('companies', ['companies.id' => $company_id], 1);
        // var_dump($this->db->error());die;
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findPromoByCode($code, $supplier_id = null, $company_id = null)
    {
        $this->db->select('sma_promo.*');
        $this->db->join('sma_promo', 'sma_promo.id = user_promotions.promo_id');
        $this->db->where("(sma_promo.supplier_id = $supplier_id OR sma_promo.supplier_id = 0)");
        $this->db->where('sma_promo.is_deleted !=', 1);
        $this->db->where('sma_promo.status', 1);
        $this->db->where('sma_promo.code_promo', $code);
        $this->db->where('user_promotions.supplier_id', $supplier_id);
        $this->db->where('user_promotions.company_id', $company_id);
        $this->db->where('user_promotions.is_deleted', null);


        $q = $this->db->get('user_promotions');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getCompaniesAddress($company_id)
    {
        $q = $this->db->get_where('companies', ['company_id' => $company_id, 'group_name' => 'address', 'is_deleted' => null]);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function findCompanyAddress($id, $company_id)
    {
        $q = $this->db->get_where('companies', ['id' => $id, 'company_id' => $company_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findCompanyByCF1($kode)
    {
        $q = $this->db->get_where('companies', ['cf1' => "IDC-$kode", 'is_deleted' => null, 'group_name' => 'customer'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findGuide($user_id)
    {
        $q = $this->db->get_where('guide_retail', ['user_id' => $user_id], 1);
        if ($q->num_rows() > 0) {
            $q = $q->row();
            foreach ($q as $i => $qq) {
                // echo $i . " : ";
                if ($i != "id" && $i != "user_id") {
                    $q->$i = $qq == 1 ? true : false;
                }
            }
            return $q;
        } else {
            $this->db->insert('guide_retail', ['user_id' => $user_id]);
            return $this->findGuide($user_id);
        }
    }

    public function setGuide($column, $status, $user_id)
    {
        $this->db->update('guide_retail', [$column => $status], ['user_id' => $user_id]);
    }

    public function getGuideAT($user_id)
    {
        $q = $this->db->get_where('guide_retail', ['user_id' => $user_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function __operate($a, $b, $char)
    {
        switch ($char) {
            case '-':
                return $a - $b;
            case '*':
                return $a * $b;
            case '+':
                return $a + $b;
            case '/':
                return $a / $b;
        }
        return $a;
    }

    public function getActiveCMS()
    {
        $q = $this->db->get_where('cms_retail', ['is_active' => "1"], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findCompanyByCf1AndCompanyId($supplier_id, $cf1, $active = true)
    {
        $this->db->where('sma_companies.company_id', $supplier_id);
        $this->db->where('sma_companies.cf1', $cf1);
        $this->db->where('sma_companies.is_deleted IS NULL');
        if ($active) {
            $this->db->where('sma_companies.is_active', 1);
        }
        $q = $this->db->get('sma_companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function findWarehouseCustomerByCustomerId($customer_id)
    {
        $this->db->where("sma_warehouse_customer.customer_id = ", $customer_id);
        $this->db->where('sma_warehouse_customer.is_deleted = 0');
        $this->db->where('sma_warehouse_customer.default != 0');

        $q = $this->db->get('sma_warehouse_customer');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function cek_booking_item($product_id, $warehouse_id, $quantity)
    {
        $this->load->model('pos_model');
        $max = '';
        $get_wh = $this->pos_model->getProductQuantity($product_id, $warehouse_id);
        $qty_book = $get_wh['quantity_booking'];
        $qty_real = $get_wh['quantity'];
        $qty_compare = $qty_real - $qty_book;
        if ($qty_compare < $quantity) {
            if ($qty_compare <= 0) {
                $max .= 'This product is out of Stock';
            } else {
                $max .= 'Maximum Quantity is ' . $qty_compare;
            }
        }

        if (!empty($max)) {
            return $max;
        }
    }

    public function getCountPendingSalesBooking($warehouse_id = null)
    {
        // if (!$this->Owner && !$this->Admin) {
        //     $user = $this->site->getUser();
        //     $warehouse_id = $user->warehouse_id;
        // } 

        $this->db->select('COUNT(*) as count');
        $this->db->where('sale_type', 'booking');
        $this->db->where('sale_status', 'pending');
        $this->db->where('company_id', $this->session->userdata('company_id'));

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        // if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
        //     $this->db->where('created_by', $this->session->userdata('user_id'));
        // } elseif ($this->Customer) {
        //     $this->db->where('customer_id', $this->session->userdata('user_id'));
        // }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                return $row->count;
            }
        }

        return 0;
    }

    public function get_bad_qty_confirm_pending()
    {
        $this->db
            ->select("deliveries.status, SUM(sma_delivery_items.bad_quantity) as bad, deliveries.is_reject, deliveries.is_approval")
            ->from('deliveries')
            ->join('sales', "sales.id = deliveries.sale_id", 'left')
            ->join('delivery_items', "delivery_items.delivery_id = deliveries.id", 'left')
            ->where('sale_type', 'booking')
            ->where('company_id', $this->session->userdata('company_id'))
            ->group_by('deliveries.id');
        if (!$this->Admin && !$this->Owner) {
            $this->db->where('deliveries.created_by', $this->session->userdata('user_id'));
        }
        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            $total = [];
            foreach (($q->result()) as $row) {
                $status = $row->status;
                $bad = (int) $row->bad;
                $is_reject = $row->is_reject;
                $is_approval = $row->is_approval;

                if ($bad > 0 && $is_reject == null && $is_approval == null && $status != 'returned') {
                    $total[] = 1;
                }
            }
            return array_sum($total);
        }

        return 0;
    }

    public function insertLoanRequest($data)
    {
        if ($this->db->insert('mandiri_loan_request', $data)) {
            $id = $this->db->insert_id();
            return $id;
        } 
        return false;
    }

    public function updateLoanRequest($data, $where)
    {
        if (!$this->db->update('mandiri_loan_request', $data, $where)) {
            throw new \Exception("Gagal Menyimpan Data");
        }
        return true;
    }

    public function getLoanRequest($where)
    {
        $q = $this->db->get_where('mandiri_loan_request', $where, 1);
        if($q && $q->num_rows() > 0){
            return $q->row();
        }

        return null;
    }

    public function getKurBtnRequest($company_id, $onlyExist = true)
    {
        $this->db->select("c.id, CONCAT(c.id, CONCAT('~', c.company)) as custom_id, c.company, c.name, c.phone, c.cf1, c.country, c.city, c.state, btn.*");
        $this->db->where('btn.company_id', $company_id);
        $this->db->join('btn_pengajuan_kur btn', '`c`.`id` = btn.company_id', $onlyExist ? 'inner' : 'left');
        $q = $this->db->get('companies c');
        if($q){
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        } else {
            return null;
        }
    }

    public function checkPersyaratanKurBtn($company_id)
    {
        $syarat = [
            'jumlah' => 0,
            'tonase' => 0,
        ];
        // syarat tonase transaksi
        $this->db->select('SUM(sma_purchase_items.quantity_received * sma_products.weight) as tonase')
            ->from('purchases')
            ->join('purchase_items', "purchases.id = purchase_items.purchase_id", 'inner')
            ->join('products', "purchase_items.product_id = products.id", 'inner')
            ->where("(purchases.status = 'received' or purchases.status = 'partial')")
            ->where('purchases.company_head_id', $company_id);

        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $syarat['tonase'] = $row->tonase / 1000;
                break;
            }
        }else{
            throw new Exception($this->db->error()['message']. "\n".$this->db->last_query());
        }

        // syarat jumlah transaksi
        $this->db->select('COUNT(*) as jumlah');
        $this->db->where("(purchases.status = 'received' or purchases.status = 'partial')");
        $this->db->where('company_id', $company_id);

        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $syarat['jumlah'] = $row->jumlah;
                break;
            }
        }
        return $syarat;
    }

    public function insertKurBtnRequest($data)
    {
        // cek old data
        $q = $this->db->get_where('btn_pengajuan_kur', ['company_id'=>$data['company_id']], 1);
        if($q && $q->num_rows() > 0){ // update
            $data['updated_at'] = date('Y-m-d H:i:s');
            if (!$this->db->update('btn_pengajuan_kur', $data, ['company_id'=>$data['company_id']])) {
                $error = $this->db->error();
                throw new \Exception("Gagal Menyimpan Data: {$error['message']}");
            }
            return true;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            if ($this->db->insert('btn_pengajuan_kur', $data)) {
                $id = $this->db->insert_id();
                return $id;
            }
            $error = $this->db->error();
            throw new \Exception("Gagal Menyimpan Data: {$error['message']}");
        }
        return false;
    }

    public function insertLoanStatus($data)
    {
        if ($this->db->insert('mandiri_loan_status', $data)) {
            $id = $this->db->insert_id();
            return $id;
        } 
        return false;
    }

    public function getLoanStatus($where)
    {
        $q = $this->db->get_where('mandiri_loan_status', $where, 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function getTokenNotifikasi($where)
    {
        $q = $this->db->get_where('notifications', $where, 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function insertTokenNotifikasi($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('notifications', $data)) {
            return true;
        }
        return false;
    }

    public function updateTokenNotifikasi($data, $where)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (!$this->db->update('notifications', $data, $where)) {
            return false;
        }
        return true;
    }

    public function getBtnBranchs()
    {
        $this->db->order_by('name', 'ASC');
        $q = $this->db->get('btn_branchs');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function findBtnBranchs($code)
    {
        $q = $this->db->get_where('btn_branchs', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return null;
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

class Sales_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $warehouse_id, $limit = 5)
    {
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name, gross.quantity as gqty, gross.warehouse_id as gwid, gross.price as gprice, gross.operation', false)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('gross', 'gross.product_id=products.id', 'left')
            ->join('consignment_products', 'products.id=consignment_products.product_id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("(products.track_quantity = 0 OR FWP.quantity > 0 OR consignment_products.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        }

        if (!$this->Owner) {
            $this->db->where('products.company_id', $this->session->userdata('company_id'));
        }

        $this->db->where("products.is_deleted", null);
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductComboItems($pid, $warehouse_id = null)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name,products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }

        if (!$this->Owner) {
            $this->db->where('warehouses_products.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductByCode($code, $company_id = null)
    {
        $this->db->where('company_id', $company_id ?? $this->session->userdata('company_id'));
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function syncQuantity($sale_id)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                }
            }
        }
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = null)
    {
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (!$this->Settings->overselling && !$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductVariants($product_id)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getCountPendingSales()
    {
        $this->db
            ->select('COUNT(*) as count')
            ->where('sale_type is null')
            ->where('sale_status', 'pending')
            ->where('company_id', $this->session->userdata('company_id'));
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                return $row->count;
            }
        }

        return 0;
    }

    public function getCountPendingSalesBooking($warehouse_id = null)
    {
        if (!$this->Owner && !$this->Admin) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->db->select('COUNT(*) as count');
        $this->db->where('sale_type', 'booking');
        $this->db->where('sale_status', 'pending');
        $this->db->where('company_id', $this->session->userdata('company_id'));

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->db->where('customer_id', $this->session->userdata('user_id'));
        }

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
        ini_set('memory_limit', '4096M');
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

    public function getAllInvoiceItems($sale_id, $return_id = null)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItemsFromPurchase($purchase_id, $return_id = null)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id = purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id = purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id = purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        if ($purchase_id && !$return_id) {
            $this->db->where('purchase_id', $purchase_id);
        } elseif ($return_id) {
            $this->db->where('purchase_id', $return_id);
        }
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItemsDelivery($dalivery_id, $return_id = null)
    {
        $this->db->select('delivery_items.*,  
                           sale_items.*, 
                           sale_items.id  as items_sale_id,
                           tax_rates.code as tax_code, 
                           tax_rates.name as tax_name, 
                           tax_rates.rate as tax_rate, 
                           products.image, 
                           products.details as details, 
                           product_variants.name as variant')
            ->join('sale_items', 'sale_items.sale_id  = delivery_items.sale_id 
                                        AND sale_items.product_id = delivery_items.product_id', 'left')
            ->join('products', 'products.id         = delivery_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id = sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id        = sale_items.tax_rate_id', 'left')
            ->group_by('delivery_items.id')
            ->order_by('delivery_items.id', 'asc');

        if ($dalivery_id && !$return_id) {
            $this->db->where('delivery_id', $dalivery_id);
        } elseif ($return_id) {
            $this->db->where('delivery_id', $return_id);
        }
        $q = $this->db->get('delivery_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function getAllInvoiceItemsWithDetails($sale_id)
    {
        $this->db->select('sale_items.*, products.details, product_variants.name as variant');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->group_by('sale_items.id');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getInvoiceByID($id, $company_id = null)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }

        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }

        if ($this->Principal) {
            $q = $this->db->get_where('sales', array('id' => $id), 1);
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        }
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getInvoiceByIdDetail($id, $company_id = null)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }

        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }

        if ($this->Principal) {
            $q = $this->db->get_where('sales', array('id' => $id), 1);
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        }

        $deliveryStatus = "(SELECT
        CASE
            WHEN
                SUM( si.quantity ) > SUM( si.sent_quantity ) 
                AND SUM( si.sent_quantity ) = 0 THEN
                    'pending' 
                    WHEN SUM( si.quantity ) > SUM( si.sent_quantity ) 
                    AND SUM( si.sent_quantity ) > 0 THEN
                        'partial' 
                        WHEN SUM( si.quantity ) <= SUM( si.sent_quantity ) AND SUM( si.sent_quantity ) > 0 THEN
                        'done' 
                    END AS delivery_status,
                    si.sale_id 
                FROM
                    sma_sale_items si 
            WHERE
            si.sale_id = $id ) delivery_status";
        $this->db->join($deliveryStatus, 'delivery_status.sale_id = sma_sales.id', 'left');

        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getInvoiceAtByID($id)
    {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesBySalesId($id)
    {
        if ($id) {
            $sql = "SELECT * FROM sma_sales WHERE id = ? LIMIT 1";
            $query = $this->db->query($sql, array($id));
            if ($query->num_rows() > 0) {
                return $query->row();
            }
            return false;
        }
    }
    public function getReturnByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnBySID($sale_id)
    {
        $q = $this->db->get_where('sales', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return true;
            }
        }
        return false;
    }

    public function addOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return true;
            }
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function addSale($data = array(), $items = array(), $payment = array(), $si_return = array(), $booking = array())
    {
        $cost = $this->site->costing($items);
        // $this->sma->print_arrays($cost);

        if ($data['sale_status']  == 'completed') {
            $book = $this->cek_item_for_complete_sale($items, null, null);
            if (!empty($book)) {
                $this->session->set_flashdata('error', $book);
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            }
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if ($data['sale_status'] == 'completed') {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $sale_id;
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $sale_id;
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }

            if ($data['sale_status'] == 'completed') {
                $this->site->syncPurchaseItems($cost);
            }

            if (!empty($si_return)) {
                $this->site->updateReference('re');
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                            $this->updatePurchaseItem(null, ($return_item['quantity'] * $combo_item->qty), null, $combo_item->id, $return_item['warehouse_id']);
                        }
                    } else {
                        $this->updateCostingLine($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                        $this->updatePurchaseItem(null, $return_item['quantity'], $return_item['id']);
                    }
                }
                $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'], 'return_sale_total' => $data['grand_total'], 'return_id' => $sale_id), array('id' => $data['sale_id']));
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update('companies', array('updated_at' => date('Y-m-d H:i:s'), 'deposit_amount' => ($customer->deposit_amount - $payment['amount'])), array('id' => $customer->id));
                    }
                    $this->db->insert('payments', $payment);
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($sale_id);
            }

            $this->site->syncQuantity($sale_id);
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            return true;
        }

        return false;
    }

    public function addSaleBooking($data = array(), $items = array(), $payment = array(), $si_return = array(), $booking = array())
    {
        /* 
            karena booking hanya pencatatan, maka fungsi costing dipindah ke add delivery 
        */
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            }
            if ($data['sale_status'] == 'reserved') {
                foreach ($booking as $k => $v) {
                    $_cekstok_wh    = $this->getWarehouseProductCompany($v['warehouse_id'], $v['product_id']);
                    $_whstok_bk     = ($_cekstok_wh->quantity_booking + $v['quantity_booking']);
                    if ($this->db->update('warehouses_products', ['quantity_booking' => $_whstok_bk], ['warehouse_id' => $v['warehouse_id'], 'product_id' => $v['product_id'], 'company_id' => $this->session->userdata('company_id')])) {
                        $_cekstok_prod  = $this->site->getProductByID($v['product_id']);
                        $_prodstok_bk   = ($_cekstok_prod->quantity_booking + $v['quantity_booking']);
                        if (!$this->db->update('products', ['quantity_booking' => $_prodstok_bk], ['id' => $v['product_id']])) {
                            throw new \Exception(lang("failed_update_stock_prod"));
                        }
                    } else {
                        throw new \Exception(lang("failed_update_stock"));
                    }
                    // $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products',$where_wh)->row();
                    // $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $v['quantity_booking']];
                    // $this->db->update('warehouses_products', $up_wh, $where_wh);
                    // $where_prod = ['id' => $v['product_id']];
                    // $get_prod = $this->db->select('quantity_booking')->get_where('products',$where_prod)->row();
                    // $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $v['quantity_booking']];
                    // $this->db->update('products', $up_prod, $where_prod);

                    $booking[$k]['sale_id'] = $sale_id;
                }
                if (!$this->db->insert_batch('sale_booking_items', $booking)) {
                    throw new \Exception(lang("failed_insert_sale_booking_items_reserved"));
                }
            } else {
                foreach ($booking as $k => $v) {
                    $booking[$k]['sale_id'] = $sale_id;
                    $booking[$k]['quantity_booking'] = 0;
                }
                if (!$this->db->insert_batch('sale_booking_items', $booking)) {
                    throw new \Exception(lang("failed_insert_sale_booking_items"));
                }
            }

            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                if (!$this->db->insert('sale_items', $item)) {
                    throw new \Exception(lang("failed_insert_sale_items"));
                }
                $sale_item_id = $this->db->insert_id();
                /* 
                    karena booking hanya pencatatan, maka fungsi costing dipindah ke add delivery 
                */
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    if (!$this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']])) {
                        throw new \Exception(lang("failed_update_gift_cards"));
                    }
                    unset($payment['gc_balance']);
                    if (!$this->db->insert('payments', $payment)) {
                        throw new \Exception(lang("failed_insert_payment"));
                    }
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        if (!$this->db->update('companies', array('updated_at' => date('Y-m-d H:i:s'), 'deposit_amount' => ($customer->deposit_amount - $payment['amount'])), array('id' => $customer->id))) {
                            throw new \Exception(lang("failed_update_deposit_amount"));
                        }
                    }
                    $this->db->insert('payments', $payment);
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($sale_id);
            }
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            return $sale_id;
        }

        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function updateSale($sale_status, $id, $data, $items = array())
    {
        $this->resetSaleActions($id, false, true);

        if ($sale_status == 'completed') {
            $cost = $this->site->costing($items);
        }

        $sale_data = $this->getSalesById($id);
        $purchase_data = $this->getPurchasesByRefNo($sale_data->reference_no, $sale_data->company_id);
        if (!$purchase_data) {
            $data['sale_status'] = $sale_status;
            $data['updated_at'] = date('Y-m-d H:i:s');
            if ($this->db->update('sales', $data, array('id' => $id))) {
                if (
                    $this->db->delete('sale_items', array('sale_id' => $id)) &&
                    $this->db->delete('costing', array('sale_id' => $id))
                ) {
                    foreach ($items as $item) {
                        $item['sale_id'] = $id;
                        $this->db->insert('sale_items', $item);
                        $sale_item_id = $this->db->insert_id();
                        if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item['product_id'])) {
                            $item_costs = $this->site->item_costing($item);
                            foreach ($item_costs as $item_cost) {
                                if (isset($item_cost['date'])) {
                                    $item_cost['sale_item_id'] = $sale_item_id;
                                    $item_cost['sale_id'] = $id;
                                    if (!isset($item_cost['pi_overselling'])) {
                                        $this->db->insert('costing', $item_cost);
                                    }
                                } else {
                                    foreach ($item_cost as $ic) {
                                        $ic['sale_item_id'] = $sale_item_id;
                                        $ic['sale_id'] = $id;
                                        if (!isset($ic['pi_overselling'])) {
                                            $this->db->insert('costing', $ic);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($data['sale_status'] == 'completed') {
                        $this->site->syncPurchaseItems($cost);
                    }

                    $this->site->syncSalePayments($id);
                    $this->site->syncQuantity($id);
                    $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
                    return true;
                }
                return true;
            }
        } else {
            if ($sale_data->sale_status != 'pending' && $sale_status == 'pending') {
                return false;
            }

            if ($sale_data->sale_status == 'completed' && $sale_status == 'confirmed') {
                return false;
            }

            if ($sale_data->sale_status == 'canceled') {
                return false;
            }

            if (($sale_data->sale_status == 'confirmed' || $sale_data->sale_status == 'completed') && $sale_status == 'canceled') {
                return false;
            }

            $flagSaleItem = false;
            if ($sale_data->sale_status == 'pending' && $data['charge'] != 0) {
                // var_dump($data['sale_status']);
                $flagSaleItem = true;
            }
            // var_dump($flagSaleItem);die;
            if ($sale_data->is_updated_price == 1) {
                $flagSaleItem = true;
            }

            if (!$flagSaleItem) {
                $data['sale_status'] = $sale_status;
                $purchasStatus = $sale_status == 'completed' || $sale_status == 'confirmed' ? 'confirmed' : ($sale_status == 'canceled' ? 'canceled' : 'pending');
                // if (!$this->db->update('purchases', array('status' => $sale_status == 'completed' || $sale_status == 'confirmed' ? 'confirmed' : ($sale_status == 'canceled' ? 'canceled' : 'pending') ), array('id' => $purchase_data->id))) {
                //     return false;
                // }
            }

            if ($flagSaleItem) {
                $data['is_updated_price'] = 1;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            if (!$this->db->update('sales', $data, array('id' => $id))) {
                return false;
            }

            if ($sale_data->sale_status == 'pending' || $sale_data->sale_status == 'confirmed') {

                if (!$this->db->delete('sale_items', array('sale_id' => $id))) {
                    return false;
                }
                if (!$this->db->delete('costing', array('sale_id' => $id))) {
                    return false;
                }

                foreach ($items as $item) {
                    $item['sale_id'] = $id;
                    $this->db->insert('sale_items', $item);
                    $sale_item_id = $this->db->insert_id();
                    if ($sale_status == 'completed' && $this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            if (isset($item_cost['date'])) {
                                $item_cost['sale_item_id'] = $sale_item_id;
                                $item_cost['sale_id'] = $id;
                                if (!isset($item_cost['pi_overselling'])) {
                                    $this->db->insert('costing', $item_cost);
                                }
                            } else {
                                foreach ($item_cost as $ic) {
                                    $ic['sale_item_id'] = $sale_item_id;
                                    $ic['sale_id'] = $id;
                                    if (!isset($ic['pi_overselling'])) {
                                        $this->db->insert('costing', $ic);
                                    }
                                }
                            }
                        }
                    }
                }
                if ($sale_status == 'completed') {
                    $this->site->syncPurchaseItems($cost);
                }
                $this->site->syncSalePayments($id);
                $this->site->syncQuantity($id);

                $purchase_data->grand_total = $data['grand_total'];
                $purchase_data->charge = $data['charge'];
                $purchase_data->total = $data['total'];

                $purchase_data->status = $purchasStatus ? $purchasStatus : $purchase_data->status;

                $data = (array) $purchase_data;
                $itemsPurchase = $this->site->getAllPurchaseItems($data['id']);
                $purchaseItem = [];

                foreach ($itemsPurchase as $keys => $itemPurchase) {
                    foreach ($items as $key => $item) {
                        if ($item['product_code'] == $itemPurchase->product_code) {
                            $itemPurchase->quantity = $item['quantity'];
                            $itemPurchase->net_unit_cost = $item['net_unit_price'];
                            $itemPurchase->unit_cost = $item['unit_price'];
                            $itemPurchase->subtotal = $item['subtotal'];
                            $itemPurchase->real_unit_cost = $item['real_unit_price'];
                            $purchaseItem[] = (array) $itemPurchase;
                        }
                    }
                }

                $items = $purchaseItem;
                $this->load->model('purchases_model');

                $opurchase = $this->purchases_model->getPurchaseByID($data['id']);
                $oitems = $this->purchases_model->getAllPurchaseItems($data['id']);

                $updatedPurchase = false;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $this->session->userdata('user_id');
                if ($this->db->update('purchases', $data, array('id' => $data['id'])) && $this->db->delete('purchase_items', array('purchase_id' => $data['id']))) {
                    $purchase_id = $data['id'];
                    foreach ($items as $item) {
                        $item['purchase_id'] = $data['id'];
                        $this->db->insert('purchase_items', $item);
                        if ($data['status'] == 'received' || $data['status'] == 'partial') {
                            $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                        }
                    }
                    $this->site->syncQuantity(null, null, $oitems);
                    if ($data['status'] == 'received' || $data['status'] == 'partial') {
                        $this->site->syncQuantity(null, $id);
                        foreach ($oitems as $oitem) {
                            $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost));
                        }
                    }
                    if ($data['status'] == 'received') {
                        $this->load->model('Official_model');
                        $this->Official_model->check_order($data['id']);
                    }
                    $this->site->syncPurchasePayments($data['id']);
                    $updatedPurchase = true;
                }
            }

            // if ($sale_data->sale_status != $sale_status) {
            //     if ($sale_data->sale_status == 'pending' ) {
            //         if ($sale_status == 'canceled') {
            //             $this->db->update('sales', array('sale_status' => $sale_status), array('id' => $id));

            //         }else if ($sale_status == 'confirmed') {
            //             $this->db->update('sales', array('sale_status' => $sale_status), array('id' => $id));
            //             $this->db->update('purchases', array('status' => 'confirmed'), array('id' => $purchase_data->id));
            //         }else if ($sale_status == 'completed') {
            //             $this->db->update('sales', array('sale_status' => $sale_status), array('id' => $id));

            //             foreach ($items as $item) {
            //                 $item = (array) $item;
            //                 if ($this->site->getProductByID($item['product_id'])) {
            //                     $item_costs = $this->site->item_costing($item);
            //                     foreach ($item_costs as $item_cost) {
            //                         $item_cost['sale_item_id'] = $item['id'];
            //                         $item_cost['sale_id'] = $id;
            //                         if(! isset($item_cost['pi_overselling'])) {
            //                             $this->db->insert('costing', $item_cost);
            //                         }
            //                     }
            //                 }
            //             }
            //             $this->db->update('purchases', array('status' => 'confirmed'), array('id' => $purchase_data->id));
            //             if (!empty($cost)) { $this->site->syncPurchaseItems($cost); }
            //             $this->site->syncQuantity($id);

            //         }else{
            //             return false;
            //         }
            //     }else if ($sale_data->sale_status == 'canceled' || $sale_data->sale_status == 'completed') {
            //         return false;
            //     }else if ($sale_data->sale_status == 'confirmed') {
            //         if ($sale_status == 'completed') {
            //             $this->db->update('sales', array('sale_status' => $sale_status), array('id' => $id));
            //             foreach ($items as $item) {
            //                 $item = (array) $item;
            //                 if ($this->site->getProductByID($item['product_id'])) {
            //                     $item_costs = $this->site->item_costing($item);
            //                     foreach ($item_costs as $item_cost) {
            //                         $item_cost['sale_item_id'] = $item['id'];
            //                         $item_cost['sale_id'] = $id;
            //                         if(! isset($item_cost['pi_overselling'])) {
            //                             $this->db->insert('costing', $item_cost);
            //                         }
            //                     }
            //                 }
            //             }

            //             $this->db->update('purchases', array('status' => 'confirmed'), array('id' => $purchase_data->id));
            //             if (!empty($cost)) { $this->site->syncPurchaseItems($cost); }
            //             $this->site->syncQuantity($id);

            //         }else{
            //             return false;
            //         }
            //     }
            //     return true;
            // }
            return true;
        }

        return false;
    }

    public function updateSaleBooking($sale_status, $id, $data, $items = array(), $booking = array())
    {
        $this->resetSaleActions($id, false, true);
        $sale_data = $this->getSalesById($id);
        $purchase_data = $this->getPurchasesByRefNo($sale_data->reference_no, $sale_data->company_id);

        if (($sale_data->sale_status == 'pending' && $sale_status == 'reserved') ||
            ($sale_data->sale_status == 'confirmed' && $sale_status == 'reserved') ||
            ($sale_data->sale_status == 'reserved' && $sale_status == 'reserved')
        ) {
        }

        $get_sale_book = $this->db->get_where('sale_booking_items', ['sale_id' => $id])->result();

        foreach ($get_sale_book as $k => $v) {
            $where_wh = [
                'product_id' => $v->product_id,
                'warehouse_id' => $v->warehouse_id
            ];
            $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();
            $up_wh = ['quantity_booking' => $get_wh->quantity_booking - $v->quantity_booking];
            $this->db->update('warehouses_products', $up_wh, $where_wh);

            $where_prod = ['id' => $v->product_id];
            $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();
            $up_prod = ['quantity_booking' => $get_prod->quantity_booking - $v->quantity_booking];
            $this->db->update('products', $up_prod, $where_prod);
        }
        $del_booking = $this->db->delete('sale_booking_items', array('sale_id' => $id));

        foreach ($booking as $k => $v) {
            if ($sale_status == 'reserved') {
                $where_wh = [
                    'product_id' => $v['product_id'],
                    'warehouse_id' => $v['warehouse_id']
                ];
                $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();
                $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $v['quantity_booking']];
                $this->db->update('warehouses_products', $up_wh, $where_wh);

                $where_prod = ['id' => $v['product_id']];
                $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();
                $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $v['quantity_booking']];
                $this->db->update('products', $up_prod, $where_prod);
            }

            if ($sale_status == 'pending' || $sale_status == 'confirmed') {
                $booking[$k]['quantity_booking'] = 0;
            }
            $booking[$k]['sale_id'] = $id;
        }
        $this->db->insert_batch('sale_booking_items', $booking);


        if (!$purchase_data) {
            $data['sale_status'] = $sale_status;
            $data['updated_at'] = date('Y-m-d H:i:s');

            if ($this->db->update('sales', $data, array('id' => $id))) {
                if ($this->db->delete('sale_items', array('sale_id' => $id))) {

                    foreach ($items as $item) {
                        $item['sale_id'] = $id;
                        $this->db->insert('sale_items', $item);
                    }

                    if ($data['sale_status'] == 'reserved') {
                        $this->site->syncPurchaseItems($cost);
                    }

                    $this->site->syncSalePayments($id);
                    //$this->site->syncQuantity($id);
                    $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
                    return true;
                }
                return true;
            }
        } else {
            if ($sale_data->sale_status != 'pending' && $sale_status == 'pending') {
                return false;
            }

            if ($sale_data->sale_status == 'reserved' && ($sale_status == 'pending' || $sale_status == 'confirmed')) {
                return false;
            }

            if ($sale_data->sale_status == 'canceled') {
                return false;
            }

            if (($sale_data->sale_status == 'confirmed' || $sale_data->sale_status == 'reserved') && $sale_status == 'canceled') {
                return false;
            }

            $flagSaleItem = false;
            if ($sale_data->sale_status == 'pending' && $data['charge'] != 0) {
                $flagSaleItem = true;
            }

            if ($sale_data->is_updated_price == 1) {
                $flagSaleItem = true;
            }

            if (!$flagSaleItem) {
                $data['sale_status'] = $sale_status;
                $purchasStatus = $sale_status == 'confirmed' || $sale_status == 'reserved' ? 'confirmed' : ($sale_status == 'canceled' ? 'canceled' : 'pending');
                // if (!$this->db->update('purchases', array('status' => $sale_status == 'completed' || $sale_status == 'confirmed' ? 'confirmed' : ($sale_status == 'canceled' ? 'canceled' : 'pending') ), array('id' => $purchase_data->id))) {
                //     return false;
                // }
            }

            if ($flagSaleItem) {
                $data['is_updated_price'] = 1;
                $data['sale_status'] = 'pending';
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            if (!$this->db->update('sales', $data, array('id' => $id))) {
                return false;
            }

            if ($sale_data->sale_status == 'pending' || $sale_data->sale_status == 'confirmed') {

                if (!$this->db->delete('sale_items', array('sale_id' => $id))) {
                    return false;
                }
                if (!$this->db->delete('costing', array('sale_id' => $id))) {
                    return false;
                }

                foreach ($items as $item) {
                    $item['sale_id'] = $id;
                    $this->db->insert('sale_items', $item);
                    $sale_item_id = $this->db->insert_id();
                    if ($sale_status == 'reserved' && $this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            if (isset($item_cost['date'])) {
                                $item_cost['sale_item_id'] = $sale_item_id;
                                $item_cost['sale_id'] = $id;
                                if (!isset($item_cost['pi_overselling'])) {
                                    $this->db->insert('costing', $item_cost);
                                }
                            } else {
                                foreach ($item_cost as $ic) {
                                    $ic['sale_item_id'] = $sale_item_id;
                                    $ic['sale_id'] = $id;
                                    if (!isset($ic['pi_overselling'])) {
                                        $this->db->insert('costing', $ic);
                                    }
                                }
                            }
                        }
                    }
                }
                if ($sale_status == 'reserved') {
                    //$this->site->syncPurchaseItems($cost);
                }
                $this->site->syncSalePayments($id);
                //$this->site->syncQuantity($id);

                $purchase_data->grand_total = $data['grand_total']  + (int) $purchase_data->charge_third_party;
                $purchase_data->charge = $data['charge'];
                $purchase_data->total = $data['total'];

                $purchase_data->status = $purchasStatus ? $purchasStatus : $purchase_data->status;

                $data = (array) $purchase_data;
                $itemsPurchase = $this->site->getAllPurchaseItems($data['id']);
                $purchaseItem = [];

                foreach ($itemsPurchase as $keys => $itemPurchase) {
                    foreach ($items as $key => $item) {
                        if ($item['product_code'] == $itemPurchase->product_code) {
                            $itemPurchase->quantity = $item['quantity'];
                            $itemPurchase->net_unit_cost = $item['net_unit_price'];
                            $itemPurchase->unit_cost = $item['unit_price'];
                            $itemPurchase->subtotal = $item['subtotal'];
                            $itemPurchase->real_unit_cost = $item['real_unit_price'];
                            $purchaseItem[] = (array) $itemPurchase;
                        }
                    }
                }

                $items = $purchaseItem;
                $this->load->model('purchases_model');

                $opurchase = $this->purchases_model->getPurchaseByID($data['id']);
                $oitems = $this->purchases_model->getAllPurchaseItems($data['id']);

                $updatedPurchase = false;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $this->session->userdata('user_id');
                if ($this->db->update('purchases', $data, array('id' => $data['id'])) && $this->db->delete('purchase_items', array('purchase_id' => $data['id']))) {
                    $purchase_id = $data['id'];
                    foreach ($items as $item) {
                        $item['purchase_id'] = $data['id'];
                        $this->db->insert('purchase_items', $item);
                        if ($data['status'] == 'received' || $data['status'] == 'partial') {
                            $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                        }
                    }
                    $this->site->syncQuantity(null, null, $oitems);
                    if ($data['status'] == 'received' || $data['status'] == 'partial') {
                        $this->site->syncQuantity(null, $id);
                        foreach ($oitems as $oitem) {
                            $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost));
                        }
                    }
                    if ($data['status'] == 'received') {
                        $this->load->model('Official_model');
                        $this->Official_model->check_order($data['id']);
                    }
                    if ($data['payment_method'] != "kredit_pro") {
                        $this->site->syncPurchasePayments($data['id']);
                    }
                    $updatedPurchase = true;
                }
            }
            return true;
        }

        return false;
    }
    public function updateOrderReceived($dataPurchase, $dataSales)
    {
        $this->load->model('purchases_model');

        $data                           = (array) $dataSales;
        $data['updated_at']             = date('Y-m-d H:i:s');
        $data['updated_by']             = $this->session->userdata('user_id');

        $data_purchase                  = (array) $dataPurchase;
        $data_purchase['updated_at']    = date('Y-m-d H:i:s');
        $data_purchase['updated_by']    = $this->session->userdata('user_id');
        if (!$this->db->update('sales', $data, array('id' => $data['id']))) {
            return false;
        }
        if (!$this->db->update('purchases', $data_purchase, array('id' => $data_purchase['id']))) {
            return false;
        }
        return true;
    }
    public function updateOrder($company_id, $status, $dataPurchase, $dataSales, $itemsPurchase = array(), $itemSale = array())
    {
        $data = (array) $dataSales;
        $updatedSale = false;
        $items = (array) $itemSale;
        $this->resetSaleActions($data['id'], false, true);

        if ($status == 'completed') {
            $cost = $this->site->costing($items, $company_id);
        }

        if ($data['sale_status'] != 'pending' && $status == 'pending') {
            return false;
        }

        if ($dataSales->sale_type == 'booking' && $data['sale_status'] == 'reserved' && ($status == 'pending' || $status == 'confirmed')) {
            return false;
        }

        if (($data['sale_status'] == 'completed' || $data['sale_status'] == 'reserved') && $this->getDeliveryBySaleID($data['id'])) {
            return false;
        }

        $stausPurchase = $status == 'reserved' || $status == 'completed' || $status == 'confirmed' ? 'confirmed' : ($status == 'canceled' ? 'canceled' : 'ordered');

        $data['sale_status'] = $status;
        $dataPurchase->status = $stausPurchase;

        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->update('sales', $data, array('id' => $data['id']))) {
            if ($dataSales->sale_status == 'pending' || $dataSales->sale_status == 'confirmed') {
                if($dataSales->sale_type == 'booking' && $status == 'reserved'){
                    $sale_items = $this->getSaleItemsBySaleId($data['id']);
                    $get_sale_book = $this->db->get_where('sale_booking_items', ['sale_id' => $data['id']])->result();
                    foreach ($get_sale_book as $k => $v) {
                        $where_wh = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id];
                        $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();

                        $where_prod = ['id' => $v->product_id];
                        $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();

                        $where_book = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id, 'sale_id' => $data['id']];
                        $get_book = $this->db->select('quantity_booking')->get_where('sale_booking_items', $where_book)->row();

                        $qty_sale = $sale_items[$k]->unit_quantity;
                        $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $qty_sale];
                        $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $qty_sale];
                        $up_book = ['quantity_booking' => $get_book->quantity_booking + $qty_sale];

                        $this->db->update('warehouses_products', $up_wh, $where_wh);
                        $this->db->update('products', $up_prod, $where_prod);
                        $this->db->update('sale_booking_items', $up_book, $where_book);
                    }
                }
                if (
                    $this->db->delete('sale_items', array('sale_id' => $data['id'])) &&
                    $this->db->delete('costing', array('sale_id' => $data['id']))
                ) {
                    foreach ($items as $item) {
                        $item = (array) $item;
                        $item['sale_id'] = $data['id'];
                        $this->db->insert('sale_items', $item);
                        $sale_item_id = $this->db->insert_id();
                        if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'reserved' && $this->site->getProductByID($item['product_id'])) {
                            $item_costs = $this->site->item_costing($item);
                            foreach ($item_costs as $item_cost) {
                                if (isset($item_cost['date'])) {
                                    $item_cost['sale_item_id'] = $sale_item_id;
                                    $item_cost['sale_id'] = $data['id'];
                                    if (!isset($item_cost['pi_overselling'])) {
                                        $this->db->insert('costing', $item_cost);
                                    }
                                } else {
                                    foreach ($item_cost as $ic) {
                                        $ic['sale_item_id'] = $sale_item_id;
                                        $ic['sale_id'] = $data['id'];
                                        if (!isset($ic['pi_overselling'])) {
                                            $this->db->insert('costing', $ic);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($data['sale_status'] == 'completed') {
                        $this->site->syncPurchaseItems($cost);
                    }

                    $this->site->syncSalePayments($data['id']);

                    if ($data['sale_status'] == 'completed') {
                        $this->site->syncQuantity($data['id']);
                    }

                    $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
                    // return true;
                    $updatedSale = true;
                }
                $updatedSale = true;
            }
        }

        $data = (array) $dataPurchase;
        $items = (array) $itemsPurchase;
        $this->load->model('purchases_model');

        $opurchase = $this->purchases_model->getPurchaseByID($data['id']);
        $oitems = $this->purchases_model->getAllPurchaseItems($data['id']);

        $updatedPurchase = false;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $this->session->userdata('user_id');
        if ($this->db->update('purchases', $data, array('id' => $data['id'])) && $this->db->delete('purchase_items', array('purchase_id' => $data['id']))) {
            $purchase_id = $data['id'];
            foreach ($items as $item) {
                $item = (array) $item;
                $item['purchase_id'] = $data['id'];
                $this->db->insert('purchase_items', $item);
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }
            $this->site->syncQuantity(null, null, $oitems);
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                $this->site->syncQuantity(null, $data['id']);
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost));
                }
            }
            if ($data['status'] == 'received') {
                $this->load->model('Official_model');
                $this->Official_model->check_order($data['id']);
            }
            $this->site->syncPurchasePayments($data['id']);
            $updatedPurchase = true;
        }

        if ($updatedSale && $updatedPurchase) {
            return true;
        } else {
            return false;
        }
    }

    public function updateOrderATL($company_id, $status, $dataSales, $itemSale = array())
    {
        $data = (array) $dataSales;
        $updatedSale = false;
        $items = (array) $itemSale;
        $this->resetSaleActions($data['id'], false, true);

        if ($status == 'completed') {
            $cost = $this->site->costing($items, $company_id);
        }

        if ($data['sale_status'] != 'pending' && $status == 'pending') {
            return false;
        }

        if ($dataSales->sale_type == 'booking' && $data['sale_status'] == 'reserved' && ($status == 'pending' || $status == 'confirmed')) {
            return false;
        }

        if (($data['sale_status'] == 'completed' || $data['sale_status'] == 'reserved') && $this->getDeliveryBySaleID($data['id'])) {
            return false;
        }

        $stausPurchase = $status == 'reserved' || $status == 'completed' || $status == 'confirmed' ? 'confirmed' : ($status == 'canceled' ? 'canceled' : 'ordered');

        $data['sale_status'] = $status;

        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->update('sales', $data, array('id' => $data['id']))) {
            if ($dataSales->sale_status == 'pending' || $dataSales->sale_status == 'confirmed') {
                if($dataSales->sale_type == 'booking' && $status == 'reserved'){
                    $sale_items = $this->getSaleItemsBySaleId($data['id']);
                    $get_sale_book = $this->db->get_where('sale_booking_items', ['sale_id' => $data['id']])->result();
                    foreach ($get_sale_book as $k => $v) {
                        $where_wh = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id];
                        $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();

                        $where_prod = ['id' => $v->product_id];
                        $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();

                        $where_book = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id, 'sale_id' => $data['id']];
                        $get_book = $this->db->select('quantity_booking')->get_where('sale_booking_items', $where_book)->row();

                        $qty_sale = $sale_items[$k]->unit_quantity;
                        $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $qty_sale];
                        $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $qty_sale];
                        $up_book = ['quantity_booking' => $get_book->quantity_booking + $qty_sale];

                        $this->db->update('warehouses_products', $up_wh, $where_wh);
                        $this->db->update('products', $up_prod, $where_prod);
                        $this->db->update('sale_booking_items', $up_book, $where_book);
                    }
                }
                if (
                    $this->db->delete('sale_items', array('sale_id' => $data['id'])) &&
                    $this->db->delete('costing', array('sale_id' => $data['id']))
                ) {
                    foreach ($items as $item) {
                        $item = (array) $item;
                        $item['sale_id'] = $data['id'];
                        $this->db->insert('sale_items', $item);
                        $sale_item_id = $this->db->insert_id();
                        if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'reserved' && $this->site->getProductByID($item['product_id'])) {
                            $item_costs = $this->site->item_costing($item);
                            foreach ($item_costs as $item_cost) {
                                if (isset($item_cost['date'])) {
                                    $item_cost['sale_item_id'] = $sale_item_id;
                                    $item_cost['sale_id'] = $data['id'];
                                    if (!isset($item_cost['pi_overselling'])) {
                                        $this->db->insert('costing', $item_cost);
                                    }
                                } else {
                                    foreach ($item_cost as $ic) {
                                        $ic['sale_item_id'] = $sale_item_id;
                                        $ic['sale_id'] = $data['id'];
                                        if (!isset($ic['pi_overselling'])) {
                                            $this->db->insert('costing', $ic);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($data['sale_status'] == 'completed') {
                        $this->site->syncPurchaseItems($cost);
                    }

                    $this->site->syncSalePayments($data['id']);

                    if ($data['sale_status'] == 'completed') {
                        $this->site->syncQuantity($data['id']);
                    }

                    $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
                    // return true;
                    $updatedSale = true;
                }
                $updatedSale = true;
            }
        }

        if ($updatedSale) {
            return true;
        } else {
            return false;
        }
    }

    public function updateStatus($id, $status, $note, $reason)
    {
        $sale_data = $this->getSalesById($id);
        $purchase_data = $this->getPurchasesByRefNo($sale_data->reference_no, $sale_data->company_id);
        $sale = $this->getInvoiceByID($id);

        $items = $this->getAllInvoiceItems($id);

        if ($sale_data->sale_type != 'booking') {
            if (($sale->sale_status == 'pending' || $sale->sale_status == 'completed') && $status == 'completed') {
                $book = $this->cek_item_for_complete_sale($items, null, null);
                if (!empty($book)) {
                    $this->session->set_flashdata('error', $book);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
        }

        $cost = array();
        if ($status == 'completed' && $status != $sale->sale_status) {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }

        if (strtolower($sale->client_id) != 'aksestoko') { // dari pos
            if ($status != $sale->sale_status && $sale_data->sale_type != 'booking') {
                $this->resetSaleActions($id, false, true);
            }

            if ($sale_data->sale_type == 'booking') {
                if ($sale->sale_status == 'pending' && $status == 'pending') {
                } else {
                    $sale_items = $this->getSaleItemsBySaleId($id);
                    $get_sale_book = $this->db->get_where('sale_booking_items', ['sale_id' => $id])->result();
                    foreach ($get_sale_book as $k => $v) {
                        $where_wh = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id];
                        $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();

                        $where_prod = ['id' => $v->product_id];
                        $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();

                        $where_book = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id, 'sale_id' => $id];
                        $get_book = $this->db->select('quantity_booking')->get_where('sale_booking_items', $where_book)->row();

                        $qty_sale = $sale_items[$k]->unit_quantity;

                        if ($sale->sale_status == 'pending' && $status == 'reserved') {
                            $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $qty_sale];
                            $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $qty_sale];
                            $up_book = ['quantity_booking' => $get_book->quantity_booking + $qty_sale];
                        }

                        if ($sale->sale_status == 'reserved' && $status == 'pending') {
                            $up_wh = ['quantity_booking' => $get_wh->quantity_booking - $v->quantity_booking];
                            $up_prod = ['quantity_booking' => $get_prod->quantity_booking - $v->quantity_booking];
                            $up_book = ['quantity_booking' => $get_book->quantity_booking - $v->quantity_booking];
                        }

                        $this->db->update('warehouses_products', $up_wh, $where_wh);
                        $this->db->update('products', $up_prod, $where_prod);
                        $this->db->update('sale_booking_items', $up_book, $where_book);
                    }
                }
            }

            if ($this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'sale_status' => $status, 'note' => $note), array('id' => $id))) {
                if ($status == 'completed' && $status != $sale->sale_status) {
                    foreach ($items as $item) {
                        $item = (array) $item;
                        if ($this->site->getProductByID($item['product_id'])) {
                            $item_costs = $this->site->item_costing($item);
                            foreach ($item_costs as $item_cost) {
                                $item_cost['sale_item_id'] = $item['id'];
                                $item_cost['sale_id'] = $id;
                                if (!isset($item_cost['pi_overselling'])) {
                                    $this->db->insert('costing', $item_cost);
                                }
                            }
                        }
                    }
                }
                // elseif ($status != 'completed' && $sale->sale_status == 'completed') {

                // }

                if (!empty($cost)) {
                    if ($status != 'reserved') {
                        if ($status != $sale->sale_status) {
                            $this->site->syncPurchaseItems($cost);
                        }
                    }
                }
                if ($status != 'reserved') {
                    if ($status != $sale->sale_status) {
                        $this->site->syncQuantity($id);
                    }
                }
                return true;
            }
        } else {
            if ($sale_data->sale_status == 'pending') {
                if ($status == 'canceled' || $status == 'confirmed' || $status == 'pending') {
                    $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'sale_status' => $status, 'note' => $note, 'reason' => $reason), array('id' => $id));
                    $this->db->update('purchases', array('updated_by' => $this->session->userdata('user_id'), 'updated_at' => date('Y-m-d H:i:s'), 'status' => $status), array('id' => $purchase_data->id));
                } elseif ($status == 'reserved') {
                    $sale_items = $this->getSaleItemsBySaleId($id);
                    $get_sale_book = $this->db->get_where('sale_booking_items', ['sale_id' => $id])->result();
                    foreach ($get_sale_book as $k => $v) {
                        $where_wh = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id];
                        $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();

                        $where_prod = ['id' => $v->product_id];
                        $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();

                        $where_book = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id, 'sale_id' => $id];
                        $get_book = $this->db->select('quantity_booking')->get_where('sale_booking_items', $where_book)->row();

                        $qty_sale = $sale_items[$k]->unit_quantity;
                        $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $qty_sale];
                        $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $qty_sale];
                        $up_book = ['quantity_booking' => $get_book->quantity_booking + $qty_sale];

                        $this->db->update('warehouses_products', $up_wh, $where_wh);
                        $this->db->update('products', $up_prod, $where_prod);
                        $this->db->update('sale_booking_items', $up_book, $where_book);
                    }

                    $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'sale_status' => $status, 'note' => $note), array('id' => $id));
                    $this->db->update('purchases', array('updated_by' => $this->session->userdata('user_id'), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 'confirmed'), array('id' => $purchase_data->id));
                } elseif ($status == 'completed') {
                    $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'sale_status' => $status, 'note' => $note), array('id' => $id));

                    foreach ($items as $item) {
                        $item = (array) $item;
                        if ($this->site->getProductByID($item['product_id'])) {
                            $item_costs = $this->site->item_costing($item);
                            foreach ($item_costs as $item_cost) {
                                $item_cost['sale_item_id'] = $item['id'];
                                $item_cost['sale_id'] = $id;
                                if (!isset($item_cost['pi_overselling'])) {
                                    $this->db->insert('costing', $item_cost);
                                }
                            }
                        }
                    }
                    $this->db->update('purchases', array('updated_by' => $this->session->userdata('user_id'), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 'confirmed'), array('id' => $purchase_data->id));
                    if (!empty($cost)) {
                        $this->site->syncPurchaseItems($cost);
                    }
                    $this->site->syncQuantity($id);
                } else {
                    return false;
                }
            } elseif ($sale_data->sale_status == 'canceled' || $sale_data->sale_status == 'completed') {
                $this->session->set_flashdata('error', 'You are not allowed to change status in this sale');
                redirect($_SERVER["HTTP_REFERER"]);
            } elseif ($sale_data->sale_status == 'reserved') {
                return false;
            } elseif ($sale_data->sale_status == 'confirmed') {
                if ($status == 'completed' || $status == 'reserved') {
                    if ($status == 'reserved') {
                        $sale_items = $this->getSaleItemsBySaleId($id);
                        $get_sale_book = $this->db->get_where('sale_booking_items', ['sale_id' => $id])->result();
                        foreach ($get_sale_book as $k => $v) {
                            $where_wh = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id];
                            $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();

                            $where_prod = ['id' => $v->product_id];
                            $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();

                            $where_book = ['product_id' => $v->product_id, 'warehouse_id' => $v->warehouse_id, 'sale_id' => $id];
                            $get_book = $this->db->select('quantity_booking')->get_where('sale_booking_items', $where_book)->row();

                            $qty_sale = $sale_items[$k]->unit_quantity;
                            $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $qty_sale];
                            $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $qty_sale];
                            $up_book = ['quantity_booking' => $get_book->quantity_booking + $qty_sale];

                            $this->db->update('warehouses_products', $up_wh, $where_wh);
                            $this->db->update('products', $up_prod, $where_prod);
                            $this->db->update('sale_booking_items', $up_book, $where_book);
                        }
                    }

                    $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'sale_status' => $status, 'note' => $note), array('id' => $id));
                    if ($sale_data->sale_type != 'booking') {
                        foreach ($items as $item) {
                            $item = (array) $item;
                            if ($this->site->getProductByID($item['product_id'])) {
                                $item_costs = $this->site->item_costing($item);
                                foreach ($item_costs as $item_cost) {
                                    $item_cost['sale_item_id'] = $item['id'];
                                    $item_cost['sale_id'] = $id;
                                    if (!isset($item_cost['pi_overselling'])) {
                                        $this->db->insert('costing', $item_cost);
                                    }
                                }
                            }
                        }
                    }


                    $this->db->update('purchases', array('updated_by' => $this->session->userdata('user_id'), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 'confirmed'), array('id' => $purchase_data->id));

                    if ($status == 'completed') {
                        if (!empty($cost)) {
                            $this->site->syncPurchaseItems($cost);
                        }
                        $this->site->syncQuantity($id);
                    }
                } else {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function deleteSale($id)
    {
        $sale_items = $this->resetSaleActions($id);
        if (
            $this->db->update('sale_items', array('is_deleted' => 1), array('sale_id' => $id)) &&
            $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'is_deleted' => 1), array('id' => $id)) &&
            $this->db->update('costing', array('is_deleted' => 1), array('sale_id' => $id))
        ) {
            $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'is_deleted' => 1), array('sale_id' => $id));
            $this->db->update('payments', array('is_deleted' => 1), array('sale_id' => $id));
            $this->site->syncQuantity(null, null, $sale_items);

            return true;
        }
        return false;
    }

    public function resetSaleActions($id, $return_id = null, $check_return = null)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if ($check_return && $sale->sale_status == 'returned') {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }

            if ($sale->sale_status == 'completed') {
                $items = $this->getAllInvoiceItems($id);
                foreach ($items as $item) {
                    if ($item->product_type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $qty = ($item->quantity * $combo_item->qty);
                                $this->updatePurchaseItem(null, $qty, null, $combo_item->id, $item->warehouse_id);
                            }
                        }
                    } else {
                        $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : null;
                        $this->updatePurchaseItem(null, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                    }
                }
                if ($sale->return_id || $return_id) {
                    $rid = $return_id ? $return_id : $sale->return_id;
                    $returned_items = $this->getAllInvoiceItems(false, $rid);
                    foreach ($returned_items as $item) {
                        if ($item->product_type == 'combo') {
                            $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                            foreach ($combo_items as $combo_item) {
                                if ($combo_item->type == 'standard') {
                                    $qty = ($item->quantity * $combo_item->qty);
                                    $this->updatePurchaseItem(null, $qty, null, $combo_item->id, $item->warehouse_id);
                                }
                            }
                        } else {
                            $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : null;
                            $this->updatePurchaseItem(null, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                        }
                    }
                }
                $this->site->syncQuantity(null, null, $items);
                $this->sma->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, true);
                return $items;
            }
        }
    }

    public function updatePurchaseItem($id, $qty, $sale_item_id, $product_id = null, $warehouse_id = null, $option_id = null)
    {
        if ($id) {
            if ($pi = $this->getPurchaseItemByID($id)) {
                $pr = $this->site->getProductByID($pi->product_id);
                if ($pr->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($pr->id, $pi->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $cpi = $this->site->getPurchasedItem(array('product_id' => $combo_item->id, 'warehouse_id' => $pi->warehouse_id, 'option_id' => null));
                            $bln = $pi->quantity_balance + ($qty * $combo_item->qty);
                            if (!$this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $combo_item->id))) {
                                throw new Exception("Error !! tidak dapat melakukan update purchase_item pada fungsi updatePurchaseItem 😟 ");
                            }
                        }
                    }
                } else {
                    $bln = $pi->quantity_balance + $qty;
                    if (!$this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $id))) {
                        throw new Exception("Error !! tidak dapat melakukan update purchase_item pada fungsi updatePurchaseItem 😟 ");
                    }
                }
            }
        } else {
            if ($sale_item_id) {
                if ($sale_item = $this->getSaleItemByID($sale_item_id)) {
                    $option_id = isset($sale_item->option_id) && !empty($sale_item->option_id) ? $sale_item->option_id : null;
                    $clause = array('product_id' => $sale_item->product_id, 'warehouse_id' => $sale_item->warehouse_id, 'option_id' => $option_id);
                    if ($pi = $this->site->getPurchasedItem($clause)) {
                        $quantity_balance = $pi->quantity_balance + $qty;
                        if (!$this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id))) {
                            throw new Exception("Error !! tidak dapat melakukan update purchase_item pada fungsi updatePurchaseItem 😟 ");
                        }
                    } else {
                        $clause['purchase_id'] = null;
                        $clause['transfer_id'] = null;
                        $clause['quantity'] = 0;
                        $clause['quantity_balance'] = $qty;
                        if (!$this->db->insert('purchase_items', $clause)) {
                            throw new Exception("Error !! tidak dapat melakukan insert purchase_item pada fungsi updatePurchaseItem 😟 ");
                        }
                    }
                }
            } else {
                if ($product_id && $warehouse_id) {
                    $pr = $this->site->getProductByID($product_id);
                    $clause = array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
                    if ($pr->type == 'standard') {
                        if ($pi = $this->site->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + $qty;
                            if (!$this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id))) {
                                throw new Exception("Error !! tidak dapat melakukan update purchase_item pada fungsi updatePurchaseItem 😟 ");
                            }
                        } else {
                            $clause['purchase_id'] = null;
                            $clause['transfer_id'] = null;
                            $clause['quantity'] = 0;
                            $clause['quantity_balance'] = $qty;
                            if (!$this->db->insert('purchase_items', $clause)) {
                                throw new Exception("Error !! tidak dapat melakukan insert purchase_item pada fungsi updatePurchaseItem 😟 ");
                            }
                        }
                    } elseif ($pr->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($pr->id, $warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            $clause = array('product_id' => $combo_item->id, 'warehouse_id' => $warehouse_id, 'option_id' => null);
                            if ($combo_item->type == 'standard') {
                                if ($pi = $this->site->getPurchasedItem($clause)) {
                                    $quantity_balance = $pi->quantity_balance + ($qty * $combo_item->qty);
                                    if (!$this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause)) {
                                        throw new Exception("Error !! tidak dapat melakukan update purchase_item pada fungsi updatePurchaseItem 😟 ");
                                    }
                                } else {
                                    $clause['transfer_id'] = null;
                                    $clause['purchase_id'] = null;
                                    $clause['quantity'] = 0;
                                    $clause['quantity_balance'] = $qty;
                                    if (!$this->db->insert('purchase_items', $clause)) {
                                        throw new Exception("Error !! tidak dapat melakukan insert purchase_item pada fungsi updatePurchaseItem 😟 ");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function confirm_payment($id, $user_id = null, $company_id = null)
    {
        if ($this->db->update('payment_temp', array('status' => 'accept'), array('id' => $id))) {
            $payment_tmp = $this->getPaymentTmpById($id);
            $total_payment = $this->getTotalPaymentByPoId($payment_tmp->purchase_id);
            $sales = $this->getSalesById($payment_tmp->sale_id);

            $paid = $payment_tmp->nominal + $sales->paid;
            if ($sales->grand_total > $paid) {
                $status = 'partial';
            } else {
                $status = 'paid';
            }

            $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'paid' => (float) $paid, 'payment_status' => $status), array('id' => $payment_tmp->sale_id));

            $this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'paid' => (float) $paid, 'payment_status' => $status), array('id' => $payment_tmp->purchase_id));

            if ($company_id == null) {
                $company_id = $this->session->userdata('company_id');
            }
            if ($user_id == null) {
                $user_id = $this->session->userdata('user_id');
            }

            $refNoPurchase = $this->site->getReference('ppay', $company_id);
            // var_dump($refNoPurchase, $payment_tmp, $company_id);die;

            $dataPaymentPurchase = [
                'purchase_id' => $payment_tmp->purchase_id,
                'reference_no' => $refNoPurchase,
                'paid_by' => 'bank',
                'amount' => (int) $payment_tmp->nominal,
                'created_by' => $user_id,
                'type' => 'sent',
                'pos_paid' => (int) $payment_tmp->nominal,
                'company_id' => $company_id,
                'reference_dist' => $payment_tmp->reference_no
            ];

            if (!$this->db->insert('payments', $dataPaymentPurchase)) {
                throw new \Exception("Gagal Menyimpan Data");
            }
            $purchasePaymentId = $this->db->insert_id();
            // echo $purchasePaymentId;die;
            $this->site->updateReference('ppay', $company_id);
            $refNoSales = $this->site->getReference('pay', $company_id);

            $dataPaymentSales = [
                'sale_id' => $payment_tmp->sale_id,
                'reference_no' => $refNoSales,
                'paid_by' => 'bank',
                'amount' => (int) $payment_tmp->nominal,
                'created_by' => $user_id,
                'type' => 'received',
                'pos_paid' => (int) $payment_tmp->nominal,
                'company_id' => $company_id,
                'reference_dist' => $payment_tmp->reference_no
            ];
            if (!$this->db->insert('payments', $dataPaymentSales)) {
                throw new \Exception("Gagal Menyimpan Data");
            }
            $salePaymentId = $this->db->insert_id();
            // echo $salePaymentId;die;
            $this->site->updateReference('pay', $company_id);

            return [
                'sale_id' => $payment_tmp->sale_id,
                'customer_id' => $sales->customer_id,
                'purchase_id' => $payment_tmp->purchase_id,
                'sale_payment_id' => $salePaymentId,
                'purchase_payment_id' => $purchasePaymentId,
                'paymet_tmp_id' => $payment_tmp->id
            ];
        }
        return false;
    }

    public function addPaymentFromThirdParty($paymentTmpId, $company_id, $toDistributor = true)
    {
        if (!$this->db->update('payment_temp', array('status' => 'accept'), array('id' => $paymentTmpId))) {
            return false;
        }

        $payment_tmp = $this->getPaymentTmpById($paymentTmpId);

        if (!$toDistributor) {
            $this->load->model('purchases_model');
            $purchase = $this->site->getPurchaseByID($payment_tmp->purchase_id);

            $paid = $payment_tmp->nominal + $purchase->paid;
            if ($purchase->grand_total > $paid) {
                $status = 'partial';
            } else {
                $status = 'paid';
            }
            // echo $status;die;
            if (!$this->db->update('purchases', array('paid' => (float) $paid, 'payment_status' => $status), array('id' => $payment_tmp->purchase_id))) {
                return false;
            }

            if (!$this->db->update('sales', array('payment_status' => 'paid'), array('id' => $payment_tmp->sale_id))) {
                return false;
            }

            $refNoPurchase = $this->site->getReference('ppay', $company_id);

            $dataPaymentPurchase = [
                'purchase_id' => $payment_tmp->purchase_id,
                'reference_no' => $refNoPurchase,
                'paid_by' => 'bank',
                'amount' => (int) $payment_tmp->nominal,
                'created_by' => $company_id,
                'type' => 'sent',
                'pos_paid' => (int) $payment_tmp->nominal,
                'company_id' => $company_id,
                'reference_dist' => $payment_tmp->reference_no
            ];

            if (!$this->db->insert('payments', $dataPaymentPurchase)) {
                return false;
            }

            if (!$this->site->updateReference('ppay', $company_id))
                return false;
        } else {
            $sales = $this->getSalesById($payment_tmp->sale_id);
            if (!$this->db->update('sales', array('paid' => $payment_tmp->nominal, 'payment_status' => 'paid'), array('id' => $payment_tmp->sale_id))) {
                return false;
            }
            $refNoSales = $this->site->getReference('pay', $company_id);

            $dataPaymentSales = [
                'sale_id' => $payment_tmp->sale_id,
                'reference_no' => $refNoSales,
                'paid_by' => 'bank',
                'amount' => (int) $payment_tmp->nominal,
                'created_by' => $company_id,
                'type' => 'received',
                'pos_paid' => (int) $payment_tmp->nominal,
                'company_id' => $company_id,
                'reference_dist' => $payment_tmp->reference_no
            ];

            if (!$this->db->insert('payments', $dataPaymentSales)) {
                return false;
            }

            if (!$this->site->updateReference('pay', $company_id)) {
                return false;
            }
        }
        return true;
    }

    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function addPaymentAtl($paymentTmpId, $company_id)
    {
        $payment_tmp    = $this->getPaymentTmpAtlById($paymentTmpId);
        $refNoSales     = $this->site->getReference('pay', $company_id);

        $dataPaymentSales = [
            'sale_id'         => $payment_tmp->sale_id,
            'reference_no'    => $refNoSales,
            'paid_by'         => 'bank',
            'amount'          => (int) $payment_tmp->paymentamount,
            'created_by'      => $company_id,
            'type'            => 'received',
            'pos_paid'        => (int) $payment_tmp->paymentamount,
            'company_id'      => $company_id
        ];

        $payment_id = $this->addPayment($dataPaymentSales);
        if (!$payment_id) {
            return false;
        }
        if (!$this->db->update('atl_payments', array('status' => 'accept', 'payment_id' => $payment_id), array('id' => $paymentTmpId))) {
            return false;
        }
        return true;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function response_reject_payment_atl($id)
    {
        $payment_atl_tmp = $this->getPaymentTmpAtlById($id);
        if ($this->db->update('atl_payments', array('status' => 'reject'), array('id' => $id))) {
            $this->site->syncSalePayments($payment_atl_tmp->sale_id);
            return true;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    // public function addPaymentAksestokoFromThirdParty($paymentTmpId, $company_id){

    // }

    public function reject_payment($id)
    {
        // $p = $this->getPaymentTmpById($id);
        $payment_tmp = $this->getPaymentTmpById($id);
        // $x =$this->getRejectPaymentByPoId($payment_tmp->purchase_id)->total + $payment_tmp->nominal;
        // print_r($p);die;
        // die;
        if ($this->db->update('payment_temp', array('status' => 'reject'), array('id' => $id))) {
            $total_payment = $this->getTotalPaymentByPoId($payment_tmp->purchase_id);
            if ($total_payment->total <= 0) {
                $this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'payment_status' => 'reject'), array('id' => $payment_tmp->purchase_id));
                $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'payment_status' => 'pending'), array('id' => $payment_tmp->sale_id));
            } else {
                $this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'payment_status' => 'partial'), array('id' => $payment_tmp->sale_id));

                $this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'payment_status' => 'partial'), array('id' => $payment_tmp->purchase_id));
            }
            return [
                'sale_id' => $payment_tmp->sale_id,
                'purchase_id' => $payment_tmp->purchase_id,
                'payment_tmp_id' => $payment_tmp->id,
                'customer_id' => $sales->customer_id
            ];
        }
        return false;
    }

    public function getPurchasesByRefNo($no_reference, $supplier_id)
    {
        $q = $this->db->get_where('purchases', array('cf1' => $no_reference, 'supplier_id' => $supplier_id, 'is_deleted' => null), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesByRefNo($no_reference, $company_id, $except_id = null)
    {
        if ($except_id) {
            $this->db->where_not_in('id', $except_id);
        }

        $q = $this->db->get_where('sales', ['reference_no' => $no_reference, 'biller_id' => $company_id, 'is_deleted' => null], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveryByRefNo($no_reference, $company_id, $except_id = null)
    {
        $this->db->select('deliveries.*');
        $this->db->join('sales', 'sales.id = deliveries.sale_id', 'left');
        $this->db->where([
            'deliveries.do_reference_no' => $no_reference,
            'sales.biller_id' => $company_id,
            'deliveries.is_deleted' => null
        ]);

        if ($except_id) {
            $this->db->where_not_in('deliveries.id', $except_id);
        }

        $q = $this->db->get('deliveries', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesById($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCostingLines($sale_item_id, $product_id, $sale_id = null)
    {
        if ($sale_id) {
            $this->db->where('sale_id', $sale_id);
        }
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('costing', array('sale_item_id' => $sale_item_id, 'product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSaleItemsBySaleId($sale_id, $array = false)
    {
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            return $array ? $q->result_array() : $q->result();
        }
        return false;
    }

    public function getSaleItemByID($id)
    {
        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addDelivery($data = array(), $shipping = null, $sale_items_id = [], $sent_quantity = [])
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $sale_data = $this->getSalesById($data['sale_id']);
        if ($sale_data->sale_type == 'booking' && $data['status'] != 'packing') {
            foreach ($sale_items_id as $i => $sale_item_id) {
                if ($sent_quantity[$i] != 0) {
                    $sale_item    = $this->getSaleItemByID($sale_item_id);
                    $cost_items[] = $this->createItemForCosting($sale_item, $sent_quantity[$i], null, null);
                }
            }
        }
        $cost = $this->site->costing($cost_items);
        if ($this->db->insert('deliveries', $data)) {
            $id_delivery = $this->db->insert_id();
            foreach ($sale_items_id as $i => $sale_item_id) {
                if ($sent_quantity[$i] != 0) {
                    if (!$this->insertDeliveryItem($id_delivery, $sale_item_id, $sent_quantity[$i])) {
                        throw new \Exception(lang("error_insert"));
                    }
                    $deliveryItemId   = $this->db->insert_id();
                    $sale_item        = $this->getSaleItemByID($sale_item_id);
                    if (!$this->updateAfterInsertDeliveryItem($sale_data->sale_type, $data['status'], $sale_item, $sent_quantity[$i])) {
                        throw new \Exception(lang("error_update"));
                    }
                    $cost_items[$i]['delivery_id']        = $id_delivery;
                    $cost_items[$i]['delivery_item_id']   = $deliveryItemId;

                    for ($j = 0; $j < count($cost[$i]); $j++) {
                        if ($cost[$i][$j]['product_id'] == $sale_item->product_id) {
                            $cost[$i][$j]['delivery_id']        = $id_delivery;
                            $cost[$i][$j]['delivery_item_id']   = $deliveryItemId;
                        }
                    }
                }
            }
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            if ($shipping) {
                $this->syncShipping($data['sale_id'], $shipping);
            }
            if ($sale_data->sale_type == 'booking') {
                foreach ($cost_items as $item) {
                    $item['sale_id']    = $data['sale_id'];
                    $sale_item_id       = $item['sale_item_id'];
                    $item_costs         = $this->site->item_costing($item);
                    $id_delivery_item   = $item['delivery_item_id'];
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) {
                            $item_cost['sale_item_id']      = $sale_item_id;
                            $item_cost['sale_id']           = $data['sale_id'];
                            $item_cost['delivery_id']       = $id_delivery;
                            $item_cost['delivery_item_id']  = $id_delivery_item;
                            if (!isset($item_cost['pi_overselling'])) {
                                if (!$this->db->insert('sma_costing', $item_cost)) {
                                    throw new \Exception(lang("error_costing"));
                                }
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id']             = $sale_item_id;
                                $ic['sale_id']                  = $data['sale_id'];
                                $ic['delivery_id']              = $id_delivery;
                                $ic['delivery_item_id']         = $id_delivery_item;
                                if (!isset($ic['pi_overselling'])) {
                                    if (!$this->db->insert('sma_costing', $ic)) {
                                        throw new \Exception(lang("error_costing"));
                                    }
                                }
                            }
                        }
                    }
                }
                if ($data['status'] != 'packing') {
                    $this->site->syncPurchaseItems($cost);
                }
                $this->site->syncQuantity($data['sale_id']);
            }
            return $id_delivery;
        }
        return false;
    }

    public function getSaleItem_ByDeliveryItemId($id)
    {
        $this->db->select('sale_items.*');
        $this->db->join('delivery_items', 'delivery_items.product_id = sale_items.product_id  AND delivery_items.sale_id = sale_items.sale_id');
        $this->db->where('delivery_items.id', $id);
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleBookingItem($product_id, $sale_id)
    {
        $q = $this->db->get_where('sale_booking_items', array('product_id' => $product_id, 'sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function return_delivery($deliv_id, $data = array(), $shipping = null, $delivery_items_id = [], $delivered_quantity = [], $return_quantity = [])
    {
        $this->db->trans_begin();

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $sales = $this->getSalesById($data['sale_id']);

        foreach ($delivery_items_id as $i => $id) {
            if ($return_quantity[$i] != 0) {
                $sales_item = $this->getSaleItem_ByDeliveryItemId($id);
                $sales_booking_item = $this->getSaleBookingItem($sales_item->product_id, $sales_item->sale_id);
                $product = $this->site->getProductByID($sales_item->product_id);
                $warehouse_product = $this->getWarehouseProduct($sales_booking_item->warehouse_id, $sales_item->product_id);

                $qty_booking_sl_booking = $sales_booking_item->quantity_booking + $return_quantity[$i];
                $qty_return_sl_booking = $sales_booking_item->quantity_return + $return_quantity[$i];
                $qty_product = $product->quantity_booking + $return_quantity[$i];
                $qty_wh_product = $warehouse_product->quantity_booking + $return_quantity[$i];


                $return_item = array(
                    'id' => $sales_item->id,
                    'sale_id' => $sales_item->sale_id,
                    'product_id' => $sales_item->product_id,
                    'option_id' => $sales_item->option_id,
                    'quantity' => (0 - (-1 * $return_quantity[$i])),
                    'warehouse_id' => $sales_item->warehouse_id,
                );


                $update_sent_Qty = $sales_item->sent_quantity - $return_quantity[$i];
                $update_sale_booking_items = [
                    'quantity_return' => $qty_return_sl_booking,
                    'quantity_booking' => $qty_booking_sl_booking,
                ];
                $this->db->update('sale_items', array('sent_quantity' => $update_sent_Qty), array('product_id' => $return_item['product_id'], 'sale_id' => $return_item['sale_id']));
                $this->db->update('sale_booking_items', $update_sale_booking_items, array('product_id' => $return_item['product_id'], 'sale_id' => $return_item['sale_id']));
                $this->db->update('products', array('quantity_booking' => $qty_product), array('id' => $sales_item->product_id));
                $this->db->update('warehouses_products', array('quantity_booking' => $qty_wh_product), array('product_id' => $sales_item->product_id, 'warehouse_id' => $sales_item->warehouse_id));

                //penambahan real stock ketika return    
                $product = $this->site->getProductByID($return_item['product_id']);
                if ($product->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $this->updateCostingLineForReturnDeliv($return_item['id'], $combo_item->id, $return_item['quantity'], $deliv_id, $id);
                        $this->updatePurchaseItem(null, ($return_item['quantity'] * $combo_item->qty), null, $combo_item->id, $return_item['warehouse_id']);
                    }
                } else {
                    $this->updateCostingLineForReturnDeliv($return_item['id'], $return_item['product_id'], $return_item['quantity'], $deliv_id, $id);
                    $this->updatePurchaseItem(null, $return_item['quantity'], $return_item['id']);
                }
            }
        }
        $this->site->syncQuantity($data['sale_id']);

        if ($this->db->insert('deliveries', $data)) {
            $id_delivery = $this->db->insert_id();
            if ($this->site->getReference('dr') == $data['do_reference_no']) {
                $this->site->updateReference('dr');
            }
            if ($shipping) {
                $this->syncShipping($data['sale_id'], $shipping);
            }

            foreach ($delivery_items_id as $i => $delivery_items_id) {
                if ($return_quantity[$i] != 0) {
                    if (!$this->insertReturnItem($id_delivery, $delivery_items_id, $return_quantity[$i])) {
                        $this->db->trans_rollback();
                        return false;
                    }
                    $good_qty = $delivered_quantity[$i] - $return_quantity[$i];
                    $this->db->update('delivery_items', array('good_quantity' => $good_qty, 'bad_quantity' => $return_quantity[$i]), array('id' => $delivery_items_id));
                }
            }

            $this->db->trans_commit();
            return $id_delivery;
        }
        $this->db->trans_rollback();
        return false;
    }

    public function insertDeliveryItem($id_delivery, $sale_item_id, $sent_quantity)
    {
        $sale_item = $this->getSaleItemByID($sale_item_id);
        if (!$sale_item) {
            throw new \Exception(lang("not_data_item_sale"));
        }
        // $q = $this->db->get_where("sale_items", ["id" => $sale_item_id], 1);
        // if ($q->num_rows() > 0) {
        //     $sale_item = $q->row();
        $requestDeliveryItem = [
            "delivery_id"       => $id_delivery,
            "sale_id"           => $sale_item->sale_id,
            "product_id"        => $sale_item->product_id,
            "product_code"      => $sale_item->product_code,
            "product_name"      => $sale_item->product_name,
            "product_type"      => $sale_item->product_type,
            "quantity_ordered"  => $sale_item->quantity,
            "quantity_sent"     => $sent_quantity,
            "good_quantity"     => $sent_quantity,
            "product_unit_id"   => $sale_item->product_unit_id,
            "product_unit_code" => $sale_item->product_unit_code,
            "client_id"         => "aksestoko",
            "flag"              => null,
            "is_deleted"        => null,
            "device_id"         => null,
            "uuid"              => null,
            "uuid_app"          => null,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        if ($this->db->insert('delivery_items', $requestDeliveryItem)) {
            return true;
        }
        // }
        return false;
    }

    public function insertReturnItem($id_delivery, $return_from_delivery_id, $sent_quantity)
    {
        $q = $this->db->get_where("delivery_items", ["id" => $return_from_delivery_id], 1);

        if ($q->num_rows() > 0) {
            $return_from_delivery = $q->row();
            $requestDeliveryItem = [
                "delivery_id" => $id_delivery,
                "sale_id" => $return_from_delivery->sale_id,
                "product_id" => $return_from_delivery->product_id,
                "product_code" => $return_from_delivery->product_code,
                "product_name" => $return_from_delivery->product_name,
                "product_type" => $return_from_delivery->product_type,
                "quantity_ordered" => $return_from_delivery->quantity,
                "quantity_sent" => (0 - $sent_quantity),
                "bad_quantity" => $sent_quantity,
                "product_unit_id" => $return_from_delivery->product_unit_id,
                "product_unit_code" => $return_from_delivery->product_unit_code,
                "client_id" => "aksestoko",
                "flag" => null,
                "is_deleted" => null,
                "device_id" => null,
                "uuid" => null,
                "uuid_app" => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'delivery_items_id' => $return_from_delivery->id,
            ];

            if ($this->db->insert('delivery_items', $requestDeliveryItem)) {
                /*
                nantinya akan berkaitan dengan stock
                $this->db->update("sale_items", ["sent_quantity" => $sale_item->sent_quantity + $sent_quantity], ['id' => $sale_item_id]);
            */
                return true;
            }
        }
        return false;
    }

    public function send_email_delivery($purchas_id, $sale, $attachment)
    {
        $purchase = $this->at_purchase->getPurchaseByID($purchas_id);
        $bank = $this->site->findThirdPartyBankByCompanyId($sale->biller_id);
        if ($purchase->payment_method == 'kredit_pro' && $purchase->status == 'received') {
            // $attachment = [];
            // $attachment = $this->generatePDFDeliv($sale);
            // $pathPDFInv = $this->generatePDFInv($sale, $purchase);
            // array_push($attachment, $pathPDFInv);
            $receiver = $this->at_purchase->getEmailReceiverThirdParty($purchase->payment_method, 'receiver');
            $sender = $this->at_purchase->getEmailSenderThirdParty($purchase->payment_method, 'sender');

            $toko =  $this->site->getUser($sale->created_by);
            $subject = "AksesToko.id Order Details : " . $sale->reference_no . "-" . $sale->biller_id;
            $body = 'Dear KreditPro Team,<br>
                        Following is the details of the Order from <b>AksesToko.id</b>:<br>
                        - OrderID       : ' . $sale->reference_no . "-" . $sale->biller_id . '<br>
                        - Owner Name    : ' . $toko->first_name . ' ' . $toko->last_name . '<br>
                        - Store         : ' . $toko->company . '<br>
                        - Phone Number  : ' . $toko->phone . '<br>
                        - Amount        :  Rp ' . number_format(abs($sale->grand_total), 0, ',', '.') . '<br><br>

                        Following is the detail of distributor accounts<br>
                        - Name              : ' . strtoupper($bank->name) . ' <br>
                        - Bank              : ' . strtoupper($bank->bank_name) . ' <br>
                        - Account Number    : ' . $bank->no_rekening . '<br><br>

                        Best Regards,<br><br>

                        AksesToko';
            if ($this->sma->send_email_php_mailer($sender, $receiver, $attachment, $subject, $body)) {
                $this->deleteFileAttachment($attachment);
                $this->at_purchase->updatePurchaseById($purchase->id, ['third_party_sent_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function deleteFileAttachment($attachments)
    {
        if (count($attachments) > 0) {
            foreach ($attachments as $key => $attachment) {
                unlink($attachment);
            }
        }
    }

    public function updateDelivery($id, $data = array(), $shipping = null, $delivery_items = null)
    {
        // $this->db->trans_begin();
        // try {
        $delivery = $this->getDeliveryByID($id);
        $total_sent_before =  $this->getTotalQtyDeliveryItemByDeliveryId($id)->total_sent;
        $total_sent_now = array_sum($delivery_items['sent_quantity']);

        $sale_data = $this->getSalesById($delivery->sale_id);
        $sale_items = $this->getSaleItemsBySaleId($delivery->sale_id);
        $items = $this->getAllInvoiceItemsDelivery($id);

        //Menghitung Costing 
        if ($sale_data->sale_type == 'booking' && $data->status != 'packing') {
            foreach ($delivery_items['delivery_items_id'] as $i => $delivery_item_id) {
                if ($delivery_items['sent_quantity'][$i] != 0) {
                    $get_item_deliv = $this->getDeliveryItemByDeliveryItemId($delivery_item_id);
                    $sale_item = $this->getSaleItemByID($get_item_deliv->sale_item_id);
                    $cost_items[] = $this->createItemForCosting($sale_item, $delivery_items['sent_quantity'][$i], null, null);
                }
            }

            if ($total_sent_before != $total_sent_now && $delivery->status != 'packing') {
                foreach ($delivery_items['delivery_items_id'] as $i => $delivery_item_id) {
                    $qty_now = $delivery_items['sent_quantity'][$i];
                    $get_item_deliv = $this->getDeliveryItemByDeliveryItemId($delivery_item_id);

                    //Reset stock
                    if ((int) $get_item_deliv->quantity_sent != $qty_now) {
                        if (!$this->updatePurchaseItem(null, $get_item_deliv->quantity_sent, $get_item_deliv->sale_item_id)) {
                            throw new Exception("Error !! tidak dapat melakukan reset stock 😟");
                        }
                    }
                }
            }

            if (($delivery->status == 'packing' && $data->status != 'packing') || $total_sent_before != $total_sent_now) {
                $cost = $this->site->costing($cost_items);
            }
        }

        $deliver_before = $this->getDeliveryByID($id);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (!$this->db->update('deliveries', $data, array('id' => $id))) {
            throw new Exception("Error !! tidak dapat update delivery 😟");
        }

        if ($shipping) {
            $this->syncShipping($id, $shipping);
        }

        if ($delivery_items && $delivery_items['delivery_items_id']) {

            if ($sale_data->sale_type == 'booking') {
                $sale_id = $delivery->sale_id;
                $sent_qty = [];
                foreach ($delivery_items['sent_quantity'] as $key => $value) {
                    $sent_qty[] = $delivery_items['sent_quantity'][$key];
                }

                if ($cost_items && $cost) {
                    foreach ($cost_items as $i => $item) {
                        $deliv_id = $delivery_items['delivery_items_id'][$i];
                        $cost_items[$i]['delivery_id'] = $id;
                        $cost_items[$i]['delivery_item_id'] = $deliv_id;
                        $qty_now = $delivery_items['sent_quantity'][$i];
                        $prod_id = $item->product_id;
                        $get_item_deliv = $this->db->get_where('delivery_items', ['id' => $deliv_id])->row();


                        for ($j = 0; $j < count($cost[$i]); $j++) {
                            if ($cost[$i][$j]['product_id'] == $prod_id) {
                                $cost[$i][$j]['delivery_id'] = $id;
                                $cost[$i][$j]['delivery_item_id'] = $delivery_items['delivery_items_id'][$i];
                            }
                        }
                        /*
                            
                            saat update status dan qty berbeda dari sebelumnya maka akan dihapus dan diinsertkan 
                            kembali nantinya     
                        */
                        if ($data['status'] != 'packing' && (int) $get_item_deliv->quantity_sent != $qty_now) {
                            if (!$this->db->delete('costing', array('delivery_id' => $id, 'delivery_item_id' => $deliv_id))) {
                                throw new Exception("Error !! tidak dapat delete costing 😟");
                            }
                            $insertagain = true;
                        }

                        if (($deliver_before->status == 'packing' && $data['status'] != 'packing') || $insertagain) {
                            $sale_item_id = $item['sale_item_id'];
                            $item_costs = $this->site->item_costing($item);
                            $id_delivery_item = $deliv_id;
                            foreach ($item_costs as $item_cost) {
                                if (isset($item_cost['date'])) {
                                    $item_cost['sale_item_id']      = $sale_item_id;
                                    $item_cost['sale_id']           = $data['sale_id'];
                                    $item_cost['delivery_id']       = $id;
                                    $item_cost['delivery_item_id']  = $id_delivery_item;

                                    if (!isset($item_cost['pi_overselling'])) {
                                        if (!$this->db->insert('costing', $item_cost)) {
                                            throw new Exception("Error !! tidak dapat menambahkan costing 😟");
                                        }
                                    }
                                } else {
                                    foreach ($item_cost as $ic) {
                                        $ic['sale_item_id']         = $sale_item_id;
                                        $ic['sale_id']              = $data['sale_id'];
                                        $ic['delivery_id']          = $id;
                                        $ic['delivery_item_id']     = $id_delivery_item;
                                        if (!isset($ic['pi_overselling'])) {
                                            if (!$this->db->insert('costing', $ic)) {
                                                throw new Exception("Error !! tidak dapat menambahkan costing 😟");
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($items as $i => $item) {
                    $deliv_id = $delivery_items['delivery_items_id'][$i];
                    $wh_id = $item->warehouse_id;
                    $prod_id = $item->product_id;
                    $qty_before = $item->quantity_sent;
                    $qty_now = $delivery_items['sent_quantity'][$i];
                    $sale_item_id = $item->items_sale_id;
                    $get_item = $this->db->get_where('sale_booking_items', ['product_id' => $prod_id, 'sale_id' => $sale_id])->row();
                    $qty_selisih = $get_item->quantity_booking;
                    $get_item_deliv = $this->db->get_where('delivery_items', ['id' => $deliv_id])->row();

                    if ($deliver_before->status == 'packing' && $data['status'] != 'packing') {
                        if ($data['status'] == 'delivered') {
                            // $this->updateCostingLine($sale_item_id, $prod_id, $qty_now);
                            if (!$this->updatePurchaseItem(null, (-1 * $qty_now), $sale_item_id)) {
                                throw new Exception("Error !! tidak dapat melakukan update purchase item 😟");
                            }
                            if (!$this->update_booking($sale_id, $wh_id, $prod_id, $qty_now, 'delivered')) {
                                throw new Exception("Error !! tidak dapat melakukan update booking 😟");
                            }
                        } else {
                            // $this->updateCostingLine($sale_item_id, $prod_id, $qty_now);
                            if (!$this->updatePurchaseItem(null, (-1 * $qty_now), $sale_item_id)) {
                                throw new Exception("Error !! tidak dapat melakukan update purchase item 😟");
                            }
                            if (!$this->update_booking($sale_id, $wh_id, $prod_id, $qty_now)) {
                                throw new Exception("Error !! tidak dapat melakukan update booking 😟");
                            }
                        }
                    }



                    if ($deliver_before->status == 'delivering' && $data['status'] == 'packing') {
                        if (!$this->reset_booking($sale_id, $wh_id, $prod_id, $deliv_id)) {
                            throw new Exception("Error !! tidak dapat melakukan reset booking 😟");
                        }
                    }

                    if ($deliver_before->status == 'delivering' && $data['status'] == 'delivering') {
                        if ($get_item_deliv->quantity_sent != $qty_now) {
                            if (!$this->updatePurchaseItem(null, (-1 * $qty_now), $sale_item_id)) {
                                throw new Exception("Error !! tidak dapat melakukan update purchase item 😟");
                            }
                        }

                        if (!$this->reset_booking($sale_id, $wh_id, $prod_id, $deliv_id)) {
                            throw new Exception("Error !! tidak dapat melakukan reset booking 😟");
                        }


                        if (!$this->update_booking($sale_id, $wh_id, $prod_id, $qty_now)) {
                            throw new Exception("Error !! tidak dapat melakukan update booking 😟");
                        }
                    }

                    if ($deliver_before->status == 'delivering' && $data['status'] == 'delivered') {
                        if ($get_item_deliv->quantity_sent != $qty_now) {
                            if (!$this->updatePurchaseItem(null, (-1 * $qty_now), $sale_item_id)) {
                                throw new Exception("Error !! tidak dapat melakukan update purchase item 😟");
                            }
                        }

                        if (!$this->reset_booking($sale_id, $wh_id, $prod_id, $deliv_id)) {
                            throw new Exception("Error !! tidak dapat melakukan reset booking 😟");
                        }

                        if (!$this->update_booking($sale_id, $wh_id, $prod_id, $qty_now, 'delivered')) {
                            throw new Exception("Error !! tidak dapat melakukan update booking 😟");
                        }
                    }
                }
            }


            foreach ($delivery_items['delivery_items_id'] as $i => $id_item) {
                if (!$this->db->update("delivery_items", [
                    "quantity_sent" => $delivery_items['sent_quantity'][$i],
                    "good_quantity" => $delivery_items['sent_quantity'][$i],
                    'updated_at' => date('Y-m-d H:i:s'),
                ], [
                    "id" => $id_item
                ])) {
                    throw new \Exception($this->db->error()['message']);
                }
            }

            foreach ($sale_items as $i => $sale_item) {
                $this->db->select("sale_id, product_id, sum(quantity_sent) as quantity_sent");
                $this->db->where([
                    "sale_id" => $sale_item->sale_id,
                    "product_id" => $sale_item->product_id
                ]);
                $this->db->group_by("sale_id, product_id");
                $delivery_item = $this->db->get("delivery_items");
                if ($delivery_item->num_rows() > 0) {
                    $delivery_item = $delivery_item->row();
                    if (!$this->db->update("sale_items", [
                        "sent_quantity" => $delivery_item->quantity_sent
                    ], [
                        "id" => $sale_item->id
                    ])) {
                        throw new \Exception($this->db->error()['message']);
                    }
                }
            }
        }

        if ($sale_data->sale_type == 'booking') {
            //$this->site->syncPurchaseItems($cost);
            $this->site->syncQuantity($sale_id);
        }


        return true;
    }

    public function getReturnDeliveryByRef($id, $sale_id)
    {
        $q = $this->db->get_where('deliveries', array('return_reference_no' => $id, 'sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnItemsByDeliveryId($delivery_id)
    {
        $q = $this->db->get_where('delivery_items', array('delivery_id' => $delivery_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveryAndSaleByDeliveryId($id)
    {
        $this->db->select("sma_sales.biller,
                biller.address AS alamat_biller,
                biller.email AS email_biller,
                biller.country AS provinsi_biller,
                biller.state AS state_biller,
                sma_sales.customer,
                customer.address AS alamat_customer,
                customer.email AS email_customer,
                customer.country AS provinsi_customer,
                customer.state AS state_customer,
                deliveries.* ");

        $this->db->join("sma_sales", "deliveries.sale_id = sma_sales.id");
        $this->db->join("sma_companies biller", "biller.id = sma_sales.biller_id");
        $this->db->join("sma_companies customer", "customer.id = sma_sales.customer_id ");
        $q = $this->db->get_where('deliveries', array('deliveries.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveryBySaleIdAndDeliveryRef($sale_ref, $delivery_ref)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_ref, 'do_reference_no' => $delivery_ref), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveryItemsByDeliveryId($delivery_id)
    {
        $this->db->where(array('delivery_items.delivery_id' => $delivery_id));
        $this->db->join("sale_items", "sale_items.sale_id = delivery_items.sale_id AND sale_items.product_id = delivery_items.product_id");
        $this->db->select("delivery_items.*, sale_items.sent_quantity as all_sent_qty, sale_items.warehouse_id");
        $q = $this->db->get('delivery_items');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function deleteDelivery($id)
    {
        if ($this->db->delete('deliveries', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getInvoicePayments($sale_id)
    {
        $this->db->order_by('payments.id', 'asc');
        $q = $this->db->get_where('payments', array('payments.sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getInvoicePaymentsBySalesId($sale_id)
    {
        if ($sale_id) {
            $sql = "SELECT sma_payments.*, sma_payment_temp.url_image, sma_payment_temp.id AS id_temp, sma_atl_payments.image, sma_atl_payments.id AS id_tmp_atl  
                    FROM sma_payments 
                    LEFT JOIN sma_payment_temp ON sma_payment_temp.sale_id = sma_payments.sale_id AND sma_payment_temp.reference_no = sma_payments.reference_dist
                    AND sma_payment_temp.status = 'accept' 
                    LEFT JOIN sma_atl_payments ON sma_atl_payments.sale_id = sma_payments.sale_id
                    AND sma_atl_payments.status = 'accept'
                    WHERE sma_payments.sale_id = ?
                    GROUP BY sma_payments.id
                    ";
            $query = $this->db->query($sql, [$sale_id]);
            if ($query->num_rows() > 0) {
                foreach (($query->result()) as $row) {
                    $data[] = $row;
                }
                return $data;
            }
        }
    }

    public function getPendingPaymentTmp($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $this->db->where('status !=', 'accept');
        $this->db->where('sale_id', $sale_id);
        $q = $this->db->get('payment_temp');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPendingPaymentAtlTmp($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $this->db->where('status !=', 'accept');
        $this->db->where('sale_id', $sale_id);
        $q = $this->db->get('atl_payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getTotalPaymentByPoId($purchase_id)
    {
        $this->db->select('SUM(nominal) as total');
        $this->db->where('status !=', 'reject');
        $this->db->where('purchase_id', $purchase_id);
        $q = $this->db->get('payment_temp');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getRejectPaymentByPoId($purchase_id)
    {
        $this->db->select('SUM(nominal) as total');
        $this->db->where('status ', 'reject');
        $this->db->where('purchase_id', $purchase_id);
        $q = $this->db->get('payment_temp');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getTotalPaymentBySoId($sale_id)
    {
        $this->db->select('SUM(paymentamount) as total');
        $this->db->where('status !=', 'reject');
        $this->db->where('sale_id', $sale_id);
        $q = $this->db->get('atl_payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getPaymentTmpAtlById($id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('atl_payments', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getPaymentTmpById($id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payment_temp', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id = payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addPayment($data = array(), $customer_id = null)
    {
        if ($this->db->insert('payments', $data)) {
            $id_payment = $this->db->insert_id();
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('updated_at' => date('Y-m-d H:i:s'), 'deposit_amount' => ($customer->deposit_amount - $data['amount'])), array('id' => $customer_id));
            }
            return $id_payment;
        }
        return false;
    }

    public function updatePayment($id, $data = array(), $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncSalePayments($data['sale_id']);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance + $opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('updated_at' => date('Y-m-d H:i:s'), 'deposit_amount' => ($customer->deposit_amount + $opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('updated_at' => date('Y-m-d H:i:s'), 'deposit_amount' => ($customer->deposit_amount - $data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }

    public function findPaymentTmpByRef($pay_ref, $sale_id)
    {
        $this->db->where('reference_no', $pay_ref);
        $this->db->where('sale_id', $sale_id);
        $q = $this->db->get('payment_temp');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function findPaymentByRefDistAndSale_id($pay_ref, $sale_id)
    {
        $this->db->where('reference_dist', $pay_ref);
        $this->db->where('sale_id', $sale_id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->syncSalePayments($opay->sale_id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance + $opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', array('updated_at' => date('Y-m-d H:i:s'), 'deposit_amount' => ($customer->deposit_amount + $opay->amount)), array('id' => $customer->id));
            }
            return true;
        }
        return false;
    }

    public function getWarehouseProduct($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProductCompany($warehouse_id, $product_id)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = array(), $ca_data = array(), $sa_data = array())
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', array('updated_at' => date('Y-m-d H:i:s'), 'award_points' => $ca_data['points']), array('id' => $ca_data['customer']));
            } elseif (!empty($sa_data)) {
                $this->db->update('users', array('award_points' => $sa_data['points']), array('id' => $sa_data['user']));
            }
            return true;
        }
        return false;
    }

    public function updateGiftCard($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        if ($this->db->delete('gift_cards', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateCostingLine($sale_item_id, $product_id, $quantity)
    {
        if ($costings = $this->getCostingLines($sale_item_id, $product_id)) {
            foreach ($costings as $cost) {
                if ($cost->quantity >= $quantity) {
                    $qty = $cost->quantity - $quantity;
                    $bln = $cost->quantity_balance && $cost->quantity_balance >= $quantity ? $cost->quantity_balance - $quantity : 0;
                    $this->db->update('costing', array('quantity' => $qty, 'quantity_balance' => $bln), array('id' => $cost->id));
                    $quantity = 0;
                } elseif ($cost->quantity < $quantity) {
                    $qty = $quantity - $cost->quantity;
                    $this->db->delete('costing', array('id' => $cost->id));
                    $quantity = $qty;
                }
            }
            return true;
        }
        return false;
    }

    public function topupGiftCard($data = array(), $card_data = null)
    {
        if ($this->db->insert('gift_card_topups', $data)) {
            $this->db->update('gift_cards', $card_data, array('id' => $data['card_id']));
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
            ->join('users', 'users.id=gift_card_topups.created_by', 'left')
            ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', array('card_id' => $card_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function syncShipping($id, $shipping_cost)
    {
        $inv = $this->getInvoiceByID($id);
        if ($this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'shipping' => $shipping_cost, 'grand_total' => $inv->grand_total + $shipping_cost), array('id' => $id))) {
            $this->site->syncSalePayments($id);
            return true;
        }
        return false;
    }

    // --------------------------- Promotion -----------------//
    public function add_promotion($data)
    {
        if ($this->db->insert('promo', $data)) {
            return true;
        }
        return false;
    }

    public function edit_promotion($id, $data)
    {
        if ($this->db->update("promo", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function delete_promotion($id)
    {
        $data = [
            // 'is_deleted'=>1,
            'status' => 0,
        ];

        if ($this->db->update("promo", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function active_promotion($id)
    {
        $data = [
            'status' => 1,
        ];

        if ($this->db->update("promo", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    // --------------------------- END OF PROMOTION------------//

    public function getKreditLimit($customer_group_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sma_customer_groups', array('id' => $customer_group_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getTotalDebt_AT($company_id_AT, $supplier_id, $purchase_id = null, $sale_id = null)
    {
        if ($purchase_id) {
            $this->db->select('SUM(sma_purchases.grand_total)-SUM(sma_purchases.paid) as total');
            $this->db->where('sma_purchases.company_id', $company_id_AT);
            $this->db->where('sma_purchases.supplier_id', $supplier_id);
            $this->db->where('sma_purchases.bank_id IS NOT NULL');
            $this->db->where('sma_purchases.id <', $purchase_id);
            $this->db->where('sma_purchases.payment_method = \'kredit\'');
            $this->db->where('sma_purchases.status != \'canceled\'');
            $q = $this->db->get('sma_purchases');
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        } else {

            $query = "(SELECT sma_purchases.company_id, sma_purchases.cf1 , sma_purchases.payment_method FROM sma_purchases WHERE sma_purchases.company_id = {$company_id_AT}) purchases";

            $this->db->select('SUM(sma_sales.total)-SUM(sma_sales.paid) as total')
                ->join($query, 'sma_sales.reference_no = purchases.cf1')
                ->where('purchases.payment_method', 'kredit')
                ->where('sma_sales.customer_id', $company_id_AT)
                ->where('biller_id', $supplier_id)
                ->where('id <', $sale_id)
                ->where('payment_status != \'paid\'');
            $q = $this->db->get('sales');

            return $q->row();
        }
    }

    public function getTotalDebt_POS($company_id_POS, $biller_id, $purchase_id = null, $sale_id = null)
    {
        // echo $purchase_id;die;
        if ($purchase_id) {
            $this->db->select('SUM(total)-SUM(paid) as total')
                ->where('customer_id', $company_id_POS)
                ->where('biller_id', $biller_id)
                ->where('id <', $purchase_id)
                ->where('payment_status != \'paid\'');
            $q = $this->db->get('sales');

            return $q->row();
        } else {
            $this->db->select('SUM(total)-SUM(paid) as total')
                ->where('customer_id', $company_id_POS)
                ->where('biller_id', $biller_id)
                ->where('id <', $sale_id)
                ->where('payment_status != \'paid\'');
            $q = $this->db->get('sales');

            return $q->row();
        }
    }

    public function getBillerid($cf_id)
    {
        $this->db
            ->select('company_id')
            ->where('cf1', $cf_id)
            ->where('group_name', 'biller');

        $q = $this->db->get('companies');
        return $q->row();
    }

    public function getAllDeliveriesExpired() //get delivery more than 3 days not received by toko
    {
        //         select sma_deliveries.* from sma_deliveries
        // join sma_sales on sma_deliveries.sale_id = sma_sales.id and  sma_sales.client_id = "aksestoko"
        // where DATE(sma_deliveries.date + INTERVAL 3 DAY) < current_date
        $this->db->select('sma_deliveries.id as do_id, sma_deliveries.do_reference_no as do_ref, sma_purchases.id as purchase_id');
        $this->db->join('sma_sales', 'sma_deliveries.sale_id = sma_sales.id and sma_sales.client_id = "aksestoko"');
        $this->db->join('sma_purchases', 'sma_purchases.cf1 = sma_sales.reference_no and sma_purchases.supplier_id = sma_sales.biller_id');
        $this->db->where('DATE(sma_deliveries.date + INTERVAL 3 DAY) < NOW() AND sma_deliveries.date > "2019-08-01" AND sma_deliveries.receive_status is null');
        $q = $this->db->get('sma_deliveries');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }


    public function getRemainingCreditLimit($company_id, $biller_id)
    {
        $totalDebt_POS = 0;
        $totalDebt_AT = 0;

        $this->db->where('id', $company_id);
        $customer = $this->db->get('companies')->row();

        $company_id_POS = $customer->id;
        $kredit = $this->getKreditLimit($customer->customer_group_id)->kredit_limit;

        // cek debt dari AksesToko 
        $this->db
            ->select('company_id')
            ->where('cf1', $customer->cf1)
            ->where('client_id', 'aksestoko')
            ->where('group_name', 'biller');

        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            $q = $q->row();
            $company_id_AT = $q->company_id;

            $this->db->select('SUM(sma_purchases.grand_total)-SUM(sma_purchases.paid) as total');
            $this->db->where('sma_purchases.company_id', $company_id_AT);
            $this->db->where('sma_purchases.bank_id IS NOT NULL');
            $this->db->where('sma_purchases.supplier_id', $biller_id);
            $this->db->where('sma_purchases.payment_method = \'kredit\'');
            $this->db->where('sma_purchases.status != \'canceled\'');
            $totalDebt = $this->db->get('sma_purchases');
            $totalDebt_AT = $totalDebt->row()->total;
        }

        // cek debt dari POS
        $this->db->select('SUM(total)-SUM(paid) as total')
            ->where('customer_id', $company_id_POS)
            ->where('biller_id', $biller_id)
            ->where("sale_status != 'canceled'")
            ->where('payment_status != \'paid\'');
        $totalDebt = $this->db->get('sales');
        $totalDebt_POS = $totalDebt->row()->total;

        $remaining_credit = $kredit - ($totalDebt_POS + $totalDebt_AT);
        return $this->sma->formatMoney($remaining_credit);
    }

    public function get_booking_item($id)
    {
        $q = $this->db->get_where('sale_booking_items', array('sale_id' => $id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function cek_booking_item($booking = array(), $id = null, $oldbooking = null, $sent_quantity = null)
    {
        $this->load->model('pos_model');
        $max = '';
        $count = count($booking);

        if (!$oldbooking) {
            foreach ($booking as $k => $v) {
                if ($sent_quantity) {
                    $qty_now = $sent_quantity[$k];
                } else {
                    $qty_now = $v->unit_quantity ? (float) $v->unit_quantity : ($v['quantity_booking']) ?: (float) $v['quantity_booking'];
                }
                $product_id = $v->product_id ? $v->product_id : ($v['product_id']) ?: $v['product_id'];
                $warehouse_id = $v->warehouse_id ? $v->warehouse_id : ($v['warehouse_id']) ?: $v['warehouse_id'];
                $product_name = $v->product_name ? $v->product_name : ($v['product_name']) ?: $v['product_name'];

                $get_wh = $this->pos_model->getProductQuantity($product_id, $warehouse_id);
                $qty_book = $get_wh['quantity_booking'];
                $qty_real = $get_wh['quantity'];
                $qty_compare = $qty_real - $qty_book;

                if ($qty_compare < $qty_now) {
                    $br = $count - 1 == $k ? '' : '<br>';
                    if ($qty_compare <= 0) {
                        $max .= $product_name . ' is out of Stock Booking in this sale' . $br;
                    } else {
                        $max .= 'Maximum Quantity for ' . $product_name . ' is ' . $qty_compare . ' in this sale ' . $br;
                    }
                }
            }
        } else {
            foreach ($booking as $k => $v) {
                $product_name = $v->product_name ? $v->product_name : ($v['product_name']) ?: $v['product_name'];

                $get_wh = $this->pos_model->getProductQuantity($v['product_id'], $v['warehouse_id']);
                $qty_book = ($get_wh['quantity_booking'] - $oldbooking[$k]->quantity_booking);
                $qty_real = $get_wh['quantity'];
                $qty_compare = $qty_real - $qty_book;
                if ($qty_compare < $v['quantity_booking']) {
                    $br = $count - 1 == $k ? '' : '<br>';

                    if ($qty_compare <= 0) {
                        $max .= $product_name . ' is out of Stock Booking in this sale' . $br;
                    } else {
                        $max .= 'Maximum Quantity for ' . $product_name . ' is ' . $qty_compare . ' in this sale ' . $br;
                    }
                }
            }
        }

        if (!empty($max)) {
            return $max;
        }
    }

    public function cek_item_for_complete_sale($booking = array(), $id = null, $oldbooking = null, $sent_quantity = null)
    {
        $this->load->model('pos_model');
        $max = '';
        $count = count($booking);

        if (!$oldbooking) {
            foreach ($booking as $k => $v) {
                if ($sent_quantity) {
                    $qty_now = $sent_quantity[$k];
                } else {
                    $qty_now = $v->unit_quantity ? (float) $v->unit_quantity : ($v['quantity_booking']) ?: (float) $v['quantity_booking'];
                }
                $product_id = $v->product_id ? $v->product_id : ($v['product_id']) ?: $v['product_id'];
                $warehouse_id = $v->warehouse_id ? $v->warehouse_id : ($v['warehouse_id']) ?: $v['warehouse_id'];
                $product_name = $v->product_name ? $v->product_name : ($v['product_name']) ?: $v['product_name'];

                $get_wh = $this->pos_model->getProductQuantity($product_id, $warehouse_id);
                $qty_book = $get_wh['quantity_booking'];
                $qty_real = $get_wh['quantity'];
                $qty_compare = $qty_real - $qty_book;

                if ($qty_compare < $qty_now) {
                    $br = $count - 1 == $k ? '' : '<br>';
                    if ($qty_compare <= 0) {
                        $max .= $product_name . ' is out of Stock in this sale' . $br;
                    } else {
                        $max .= 'Maximum Quantity for ' . $product_name . ' is ' . $qty_compare . ' in this sale ' . $br;
                    }
                }
            }
        } else {
            foreach ($booking as $k => $v) {
                $product_name = $v->product_name ? $v->product_name : ($v['product_name']) ?: $v['product_name'];

                $get_wh = $this->pos_model->getProductQuantity($v['product_id'], $v['warehouse_id']);
                $qty_book = ($get_wh['quantity_booking'] - $oldbooking[$k]->quantity_booking);
                $qty_real = $get_wh['quantity'];
                $qty_compare = $qty_real - $qty_book;
                if ($qty_compare < $v['quantity_booking']) {
                    $br = $count - 1 == $k ? '' : '<br>';

                    if ($qty_compare <= 0) {
                        $max .= $product_name . ' is out of Stock in this sale' . $br;
                    } else {
                        $max .= 'Maximum Quantity for ' . $product_name . ' is ' . $qty_compare . ' in this sale ' . $br;
                    }
                }
            }
        }

        if (!empty($max)) {
            return $max;
        }
    }

    public function update_booking($sale_id, $wh_id, $prod_id, $qty_now, $is_delivered = null)
    {
        $get_item = $this->db->get_where('sale_booking_items', ['product_id' => $prod_id, 'sale_id' => $sale_id])->row();
        $qty_deliver = $get_item->quantity_delivering + $qty_now;
        $qty_booking = $get_item->quantity_booking - $qty_now;
        $qty_delivered = $get_item->quantity_delivered + $qty_now;

        $where_wh = ['product_id' => $prod_id, 'warehouse_id' => $wh_id];
        $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();
        $where_prod = ['id' => $prod_id];
        $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();

        $wh_book = (int) $get_wh->quantity_booking - (int) $qty_now;
        $prod_book = (int) $get_prod->quantity_booking - (int) $qty_now;

        if ($wh_book < 0 || $prod_book < 0) {
            $this->session->set_flashdata('error', lang('Error Update Delivery Item'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if (!empty($is_delivered)) {
            $update_booking = [
                "quantity_booking" => $qty_booking,
                "quantity_delivering" => $qty_deliver,
                "quantity_delivered" => $qty_delivered
            ];
        } else {
            $update_booking = [
                "quantity_booking" => $qty_booking,
                "quantity_delivering" => $qty_deliver
            ];
        }

        $up_sale_booking_items = ['product_id' => $prod_id, 'sale_id' => $sale_id];
        $up_wh = ['quantity_booking' => $get_wh->quantity_booking - $qty_now];
        $up_prod = ['quantity_booking' => $get_prod->quantity_booking - $qty_now];

        if (
            $this->db->update("sale_booking_items", $update_booking, $up_sale_booking_items) &&
            $this->db->update('warehouses_products', $up_wh, $where_wh) &&
            $this->db->update('products', $up_prod, $where_prod)
        ) {
            return true;
        }
        return false;
    }

    public function reset_booking($sale_id, $wh_id, $prod_id, $deliv_id)
    {
        $get_item = $this->db->get_where('sale_booking_items', ['product_id' => $prod_id, 'sale_id' => $sale_id])->row();
        $get_deliver = $this->db->get_where('delivery_items', ['id' => $deliv_id])->row();

        $qty_wh_prod_reset = $get_deliver->quantity_sent;
        $qty_booking = $get_item->quantity_booking + $get_deliver->quantity_sent;
        $qty_deliver = $get_item->quantity_delivering - $get_deliver->quantity_sent;

        $update_booking = [
            "quantity_booking" => $qty_booking,
            "quantity_delivering" => $qty_deliver
        ];
        $where_update_booking = ['product_id' => $prod_id, 'sale_id' => $sale_id];



        $where_wh = [
            'product_id' => $prod_id,
            'warehouse_id' => $wh_id
        ];
        $get_wh = $this->db->select('quantity_booking')->get_where('warehouses_products', $where_wh)->row();
        $up_wh = ['quantity_booking' => $get_wh->quantity_booking + $qty_wh_prod_reset];


        $where_prod = ['id' => $prod_id];
        $get_prod = $this->db->select('quantity_booking')->get_where('products', $where_prod)->row();
        $up_prod = ['quantity_booking' => $get_prod->quantity_booking + $qty_wh_prod_reset];

        if (
            $this->db->update("sale_booking_items", $update_booking, $where_update_booking) &&
            $this->db->update('warehouses_products', $up_wh, $where_wh) &&
            $this->db->update('products', $up_prod, $where_prod)
        ) {
            return true;
        }
        return false;
    }

    public function closeSale($id, $dataclosed = null, $close_item = null)
    {
        $sale_items = $this->getSaleItemsBySaleId($id);
        $getDeliveredItem = $this->getDeliveredItem($id);
        $str_delivering = '';
        $str_approve = '';
        $str_confirm = '';
        if (count($getDeliveredItem) > 0) {
            $str_close = [];
            $str_received = [];
            foreach ($getDeliveredItem as $v) {
                if ($v->is_approval == null && $v->is_reject == null && $v->is_confirm == null && (int) $v->bad > 0) {
                    $str_approve .= $v->do_reference_no . ', ';
                } elseif ($v->is_reject == 1 && is_null($v->is_confirm)) {
                    $str_confirm .= $v->do_reference_no . ', ';
                } elseif ($v->is_reject == 2 && $v->is_confirm == 1 && $v->is_approval != 1 && (int) $v->bad > 0) {
                    $str_approve .= $v->do_reference_no . ', ';
                } else {
                    $str_close[] = 1;
                }
            }
        }
        $str = '';
        if ($str_confirm != '') {
            $str .= substr($str_confirm, 0, strlen($str_confirm) - 2) . '<br>';
        }
        if ($str_approve != '') {
            $str .= substr($str_approve, 0, strlen($str_approve) - 2) . '<br>';
        }
        if ($str_delivering != '') {
            $str .= substr($str_delivering, 0, strlen($str_delivering) - 2) . '<br>';
        }
        if ($str != '') {
            return false;
        }

        if (!$dataclosed) {
            $sale = $this->getSalesById($id);
            $dataclosed = array(
                'sale_id' => $sale->id,
                'date' => date('Y-m-d H:i:s'),
                'company_id' => $sale->company_id,
                'customer_id' => $sale->customer_id,
                'customer' => $sale->customer,
                'biller_id' => $sale->biller_id,
                'biller' => $sale->biller,
                'warehouse_id' => $sale->warehouse_id,
            );
            if (!$this->db->update('sales', ['sale_status' => 'closed'], ['id' => $sale->id])) {
                return false;
            }
        }
        $this->db->insert('close_sale', $dataclosed);
        $close_sale_id = $this->db->insert_id();
        $row = 0;

        if (!$close_item) {
            foreach ($sale_items as $item) {
                $close_item[] = array(
                    'product_id' => $item->product_id,
                    'product_code' => $item->product_code,
                    'product_name' => $item->product_name,
                    'product_type' => $item->product_type,
                    'option_id' => $item->option_id,
                    'net_unit_price' => $item->net_unit_price,
                    'unit_price' => $item->unit_price,
                    'quantity' => 0,
                    'unit_quantity' => 0,
                    'sent_quantity' => 0,
                    'warehouse_id' => $item->warehouse_id,
                    'item_tax' => $item->item_tax,
                    'tax_rate_id' => $item->tax_rate_id,
                    'tax' => $item->tax,
                    'discount' => $item->discount,
                    'item_discount' => $item->item_discount,
                    'subtotal' => 0,
                    'serial_no' => $item->serial_no,
                    'real_unit_price' => $item->real_unit_price,
                    'flag' => $item->flag,
                );
            }
        }

        foreach ($sale_items as $item) {
            $close_item[$row]['close_sale_id'] = $close_sale_id;

            if ($item->quantity != $item->sent_quantity) {
                $bookingitem = $this->getSaleBookingItem($item->product_id, $item->sale_id);

                $product = $this->site->getProductByID($item->product_id);
                $warehouse_product = $this->getWarehouseProduct($item->warehouse_id, $item->product_id);


                $qty_product = $product->quantity_booking - $bookingitem->quantity_booking;
                $qty_wh_product = $warehouse_product->quantity_booking - $bookingitem->quantity_booking;

                if ($qty_product < 0) {
                    return false;
                }

                if ($qty_wh_product < 0) {
                    return false;
                }

                $this->db->update('sale_booking_items', ['quantity_booking' => 0], array('product_id' => $item->product_id, 'sale_id' => $item->sale_id));
                $this->db->update('products', array('quantity_booking' => $qty_product), array('id' => $item->product_id));
                $this->db->update('warehouses_products', array('quantity_booking' => $qty_wh_product), array('product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id));
            }
            $this->db->insert('close_sale_items', $close_item[$row]);
            $row++;
        }
        return true;
    }

    public function getCloseSale($sale_id)
    {
        $q = $this->db->get_where('close_sale', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCloseInvoiceItems($close_sale_id)
    {


        $this->db->select('close_sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=close_sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=close_sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=close_sale_items.tax_rate_id', 'left')
            ->group_by('close_sale_items.id')
            ->order_by('id', 'asc')
            ->where('close_sale_id', $close_sale_id);

        $q = $this->db->get('close_sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    //  update sale and purchase when close sale
    public function updateSaleandPurchase($sale, $data, $items)
    {
        $purchase_AT = $this->sales_model->getPurchasesByRefNo($sale->reference_no, $sale->company_id);
        if ($sale->charge && @$purchase_AT->charge) {
            $data['grand_total'] += ($sale->charge ? $sale->charge : $purchase_AT->charge);
        }
        if (!$this->db->update('sales', $data, ['id' => $sale->id])) {
            return false;
        }
        if (!$this->site->syncSalePayments($sale->id)) {
            return false;
        }
        if ($purchase_AT) {
            $update_purchase = [
                'total'       => $data['total'],
                'status'      => 'received',
                'grand_total' =>  $data['grand_total']
            ];
            if (!$this->db->update('purchases', $update_purchase, ['id' => $purchase_AT->id])) {
                return false;
            }
            if (!$this->site->syncSalePaymentsAT($sale->id, $purchase_AT->id)) {
                return false;
            }
        }

        foreach ($items as $item) {
            $update_sale_item = [
                'quantity'      => $item['quantity'],
                'unit_quantity' => $item['unit_quantity'],
                'sent_quantity' => $item['sent_quantity'],
                'subtotal'      => $item['subtotal'],
                'item_discount' => $item['item_discount']
            ];
            if (!$this->db->update('sale_items', $update_sale_item, ['id' => $item['sale_item_id']])) {
                return false;
            }

            if ($purchase_AT) {
                $update_purchase_item = [
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'status'   => 'received'
                ];

                if (!$this->db->update('purchase_items', $update_purchase_item, ['purchase_id' => $purchase_AT->id, 'product_code' => $item['product_code']])) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getClientStatusByDeliveryId($delivery_id)
    {
        $this->db->select('ss.client_id, SUM(sdi.bad_quantity) as bad');
        $this->db->from('deliveries sd');
        $this->db->join('sales ss', 'ss.id = sd.sale_id', 'left');
        $this->db->join('delivery_items sdi', 'sdi.delivery_id = sd.id', 'left');
        $this->db->where('sd.id', $delivery_id);
        $this->db->group_by('sdi.delivery_id');
        $get = $this->db->get();
        if ($get->num_rows() > 0) {
            return $get->row();
        }
        return false;
    }

    public function getDeliveryToClose($sale_id)
    {
        $get = $this->db->query("SELECT sd.*, SUM(sdi.bad_quantity) as bad FROM sma_deliveries sd
            LEFT JOIN sma_delivery_items sdi ON sdi.delivery_id = sd.id
            WHERE sd.status != 'returned'  AND sd.status != 'delivered' AND sd.sale_id = '$sale_id'
            AND (sd.`is_approval` != 3 OR sd.`is_approval` IS NULL )
            GROUP BY sd.id")->result();
        return $get;
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function updatePurchaseItemsById($id, $data)
    {
        $this->db->update('purchase_items', $data, [
            'id' => $id,
        ]);
    }

    public function createItemForCosting($item, $sent_quantity, $delivery_id, $delivery_item_id)
    {


        $product_tax        = 0;
        $order_tax          = 0;
        $product_discount   = 0;
        $order_discount     = 0;
        $percentage         = '%';


        $sale_item              = $item->id;
        $item_id                = $item->product_id;
        $item_type              = $item->product_type;
        $item_code              = $item->product_code;
        $item_name              = $item->product_name;
        $item_option            = $item->option_id;
        $real_unit_price        = $item->real_unit_price;
        $unit_price             = $item->unit_price;
        $item_unit_quantity     = $sent_quantity;
        $item_product_unit_id   = $item->product_unit_id;
        $item_product_unit_code = $item->product_unit_code;

        $item_quantity          = $sent_quantity; //===============

        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        } else {
            $warehouse_id       = $item->warehouse_id;
        }

        $item_serial            = $item->serial_no;
        $item_tax_rate          = $item->tax_rate_id;
        $item_discount          = $item->discount ? $item->discount : '0';
        $flag                   = $item->flag;
        $tax_rate               = $this->site->getTaxRateByID($item_tax_rate);

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
            $subtotal = (($item_net_price * $item_quantity) + $pr_item_tax);

            $products = array(
                'id' => $sale_item,
                'sale_id' => $item->sale_id,
                'delivery_id' => $delivery_id,
                'delivery_item_id' => $delivery_item_id,
                'product_id' => $item_id,
                'product_code' => $item_code,
                'product_name' => $item_name,
                'product_type' => $item_type,
                'option_id' => $item_option,
                'net_unit_price' => $item_net_price,
                'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                'quantity' => $item_quantity,
                'warehouse_id' => $warehouse_id,
                'item_tax' => $pr_item_tax,
                'tax_rate_id' => $pr_tax,
                'tax' => $tax,
                'discount' => $item_discount,
                'item_discount' => $pr_item_discount,
                'subtotal' => $this->sma->formatDecimal($subtotal),
                'serial_no' => $item_serial,
                'real_unit_price' => $real_unit_price,
                'sale_item_id' => $sale_item,
                'product_unit_id' => $item_product_unit_id,
                'product_unit_code' => $item_product_unit_code,
                'unit_quantity' => $item_unit_quantity,
                'sent_quantity' => $item_unit_quantity,
                'flag' => $flag
            );
        }
        return $products;
    }

    public function getDeliveredItem($sale_id)
    {
        $get = $this->db->query("SELECT sd.*, SUM(sdi.bad_quantity) as bad FROM sma_deliveries sd
            LEFT JOIN sma_delivery_items sdi ON sdi.delivery_id = sd.id
            WHERE sd.status = 'delivered' AND sd.sale_id = '$sale_id'
            AND (sd.`is_approval` != 3 OR sd.`is_approval` IS NULL )
            GROUP BY sd.id")->result();
        return $get;
    }

    /*
        for update warehouses_products,  products , sale_booking_items , sale_items 
        after insert delivery
    */
    public function updateAfterInsertDeliveryItem($sale_type, $status, $sale_item, $sent_quantity)
    {
        if ($sale_type == 'booking') {
            if ($status != 'packing') {
                $get_item       = $this->getSaleBookingItem($sale_item->product_id, $sale_item->sale_id);
                if (!$get_item) {
                    throw new \Exception(lang("not_get_data"));
                }
                // $get_item       = $this->db->get_where('sale_booking_items', ['product_id' => $sale_item->product_id, 'sale_id' => $sale_item->sale_id])->row();
                $qty_deliver    = $get_item->quantity_delivering + $sent_quantity;
                $qty_booking    = $get_item->quantity_booking - $sent_quantity;
                $qty_delivered  = $get_item->quantity_delivered + $sent_quantity;

                $get_wh         = $this->getWarehouseProductCompany($get_item->warehouse_id, $sale_item->product_id);
                $get_prod       = $this->site->getProductByID($sale_item->product_id);

                $wh_book        = (int) $get_wh->quantity_booking - (int) $sent_quantity;
                $prod_book      = (int) $get_prod->quantity_booking - (int) $sent_quantity;

                if ($wh_book < 0 || $prod_book < 0) {
                    throw new \Exception(lang("error_insert_delivery_item"));
                }

                if ($status == 'delivered') {
                    $update_booking = [
                        "quantity_booking"    => $qty_booking,
                        "quantity_delivering" => $qty_deliver,
                        "quantity_delivered"  => $qty_delivered
                    ];
                } else {
                    $update_booking = [
                        "quantity_booking"    => $qty_booking,
                        "quantity_delivering" => $qty_deliver
                    ];
                }
                if (!$this->db->update('sale_booking_items', $update_booking, ['product_id' => $sale_item->product_id, 'sale_id' => $sale_item->sale_id])) {
                    throw new \Exception(lang("failed_update_sale_booking_item"));
                } else {
                    if (!$this->db->update('warehouses_products', ['quantity_booking' => $wh_book], ['warehouse_id' => $get_item->warehouse_id, 'product_id' => $sale_item->product_id, 'company_id' => $this->session->userdata('company_id')])) {
                        throw new \Exception(lang("failed_update_stock"));
                    } else {
                        if (!$this->db->update('products', ['quantity_booking' => $prod_book], ['id' => $sale_item->product_id])) {
                            throw new \Exception(lang("failed_update_stock_prod"));
                        }
                    }
                }
            }
            if ($this->db->update('sale_items', ["sent_quantity" => $sale_item->sent_quantity + $sent_quantity], ['id' => $sale_item->id])) {
                return true;
            }
        } elseif ($sale_type == NULL) {
            if ($this->db->update('sale_items', ["sent_quantity" => $sale_item->sent_quantity + $sent_quantity], ['id' => $sale_item->id])) {
                return true;
            }
        }
        return false;
    }

    public function updateCostingLineForReturnDeliv($sale_item_id, $product_id, $quantity, $delivery_id, $delivery_item_id)
    {
        if ($costings = $this->getCostingLinesForReturnDeliv($sale_item_id, $product_id, $delivery_id, $delivery_item_id)) {
            foreach ($costings as $cost) {
                if ($cost->quantity >= $quantity) {
                    $qty = $cost->quantity - $quantity;
                    $bln = $cost->quantity_balance && $cost->quantity_balance >= $quantity ? $cost->quantity_balance - $quantity : 0;
                    $this->db->update('costing', array('quantity' => $qty, 'quantity_balance' => $bln), array('id' => $cost->id));
                    $quantity = 0;
                } elseif ($cost->quantity < $quantity) {
                    $qty = $quantity - $cost->quantity;
                    $this->db->delete('costing', array('id' => $cost->id));
                    $quantity = $qty;
                }
            }
            return true;
        }
        return false;
    }

    public function getCostingLinesForReturnDeliv($sale_item_id, $product_id, $delivery_id, $delivery_item_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('costing', array('sale_item_id' => $sale_item_id, 'product_id' => $product_id, 'delivery_id' => $delivery_id, 'delivery_item_id' => $delivery_item_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDeliveryItemByDeliveryItemId($delivery_item_id)
    {
        $this->db->where(array('delivery_items.id' => $delivery_item_id));
        $this->db->join("sale_items", "sale_items.sale_id = delivery_items.sale_id AND sale_items.product_id = delivery_items.product_id");
        $this->db->select("delivery_items.*, sale_items.id as sale_item_id");
        $q = $this->db->get('delivery_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return [];
    }

    public function getTotalQtyDeliveryItemByDeliveryId($delivery_id)
    {
        $this->db->select('SUM(quantity_sent) as total_sent');
        $this->db->where('delivery_id', $delivery_id);
        $q = $this->db->get('delivery_items');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return [];
    }

    public function cek_sales($sale_id, $link, $sale_type = null)
    {
        if ($sale_id) {
            $this->db->where('id', $sale_id);
            $q = $this->db->get('sales');
            if ($q->num_rows() > 0) {
                if ($q->row()->sale_type != $sale_type) {
                    redirect(base_url($link) . $sale_id);
                }
            } else {
                $this->session->set_flashdata('error', 'Sale is not found');
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
    }

    public function getAllSalesBooking($company_id, $filter = null, $date_range = null)
    {
        $deliveryStatus = "(
            SELECT
                sma_sales.id AS sale_id,
            CASE
                    
                    WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                    AND SUM( sent_quantity ) = 0 THEN
                        'pending' 
                        WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                        AND SUM( sent_quantity ) > 0 THEN
                            'partial' 
                            WHEN SUM( unit_quantity ) <= SUM( sent_quantity ) 
                            AND SUM( sent_quantity )> 0 THEN
                                'done' 
                                END AS delivery_status 
                        FROM
                            `sma_sales`
                            LEFT JOIN `sma_sale_items` ON `sma_sale_items`.`sale_id` = `sma_sales`.`id`
                        GROUP BY
                            `sma_sale_items`.`sale_id` 
                        ) delivery_status";
        $this->db
            ->select("{$this->db->dbprefix('sales')}.*, IF({$this->db->dbprefix('sales')}.client_id = 'aksestoko', CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, ' (AksesToko)'), CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name)) AS created_by, {$this->db->dbprefix('purchases')}.payment_method, IF({$this->db->dbprefix('purchases')}.payment_method = 'kredit_pro', CONCAT({$this->db->dbprefix('purchases')}.status, CONCAT('|', {$this->db->dbprefix('purchases')}.payment_status)), '-|-') AS 'status_kredit_pro', delivery_status.delivery_status")
            ->from('sales');
        $this->db->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left');
        $this->db->join($deliveryStatus, 'delivery_status.sale_id = sales.id');
        $this->db->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left');
        $this->db->join($this->db->dbprefix('purchases'), $this->db->dbprefix('purchases') . '.cf1=sales.reference_no AND ' . $this->db->dbprefix('purchases') . '.supplier_id=sales.biller_id', 'left');
        $this->db->where('sma_sales.biller_id', $company_id);
        $this->db->where('sma_sales.pos !=', 1);
        $this->db->where('sma_sales.company_id', $company_id);
        $this->db->where('sma_sales.sale_type', 'booking');
        $this->db->group_by('sma_sale_items.sale_id');
        $this->db->order_by('sma_sales.date', 'asc');

        if ($filter && $filter != '') {
            $this->db->where($filter);
        }

        if (!$date_range) {
            $this->db->limit(100);
        } else {
            $this->db->where($date_range);
        }

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAllDeliveriesBooking($where = null, $date_range = null)
    {
        $this->db
            ->select("{$this->db->dbprefix('deliveries')}.*")
            ->from("{$this->db->dbprefix('deliveries')}")
            ->join("{$this->db->dbprefix('sales')}", "{$this->db->dbprefix('sales')}.id = {$this->db->dbprefix('deliveries')}.sale_id", 'left')
            ->where("{$this->db->dbprefix('sales')}.sale_type", 'booking')
            ->where("{$this->db->dbprefix('sales')}.is_deleted IS NULL");

        if ($filter && $filter != '') {
            $this->db->where($where);
        }

        if (!$date_range) {
            $this->db->limit(100);
        } else {
            $this->db->where($date_range);
        }

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getDeliveryScheduler($date)
    {
        $this->db
            ->select("{$this->db->dbprefix('deliveries')}.*")
            ->from("{$this->db->dbprefix('deliveries')}")
            ->where("{$this->db->dbprefix('deliveries')}.delivering_date < '{$date}'")
            ->where("{$this->db->dbprefix('deliveries')}.receive_status is null")
            ->where("{$this->db->dbprefix('deliveries')}.status = 'delivering'");
        $this->db
            ->join($this->db->dbprefix('sales'), "sales.id = deliveries.sale_id")
            ->where("{$this->db->dbprefix('sales')}.client_id = 'aksestoko'");

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAllSalesBookingPaging($company_id, $filter = null, $date_range = null, $limit = null, $offset = null, $sortby = null, $sorttype = null)
    {
        $sql = 'SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = "' . getenv('DB_DATABASE') . '" AND table_name = "sma_sales"';
        $query = $this->db->query($sql);
        $bool = 0;
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                if ($sortby == $row->COLUMN_NAME) {
                    $bool = 1;
                }
            }
        }
        $deliveryStatus = "(
            SELECT
                sma_sales.id AS sale_id,
            CASE
                    
                    WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                    AND SUM( sent_quantity ) = 0 THEN
                        'pending' 
                        WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                        AND SUM( sent_quantity ) > 0 THEN
                            'partial' 
                            WHEN SUM( unit_quantity ) <= SUM( sent_quantity ) 
                            AND SUM( sent_quantity )> 0 THEN
                                'done' 
                                END AS delivery_status 
                        FROM
                            `sma_sales`
                            LEFT JOIN `sma_sale_items` ON `sma_sale_items`.`sale_id` = `sma_sales`.`id`
                        GROUP BY
                            `sma_sale_items`.`sale_id` 
                        ) delivery_status";
        $this->db
            ->select("{$this->db->dbprefix('sales')}.*, IF({$this->db->dbprefix('sales')}.client_id = 'aksestoko', CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, ' (AksesToko)'), CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name)) AS created_by, {$this->db->dbprefix('purchases')}.payment_method, IF({$this->db->dbprefix('purchases')}.payment_method = 'kredit_pro', CONCAT({$this->db->dbprefix('purchases')}.status, CONCAT('|', {$this->db->dbprefix('purchases')}.payment_status)), '-|-') AS 'status_kredit_pro', delivery_status.delivery_status")
            ->from('sales');
        $this->db->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left');
        $this->db->join($deliveryStatus, 'delivery_status.sale_id = sales.id');
        $this->db->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left');
        $this->db->join($this->db->dbprefix('purchases'), $this->db->dbprefix('purchases') . '.cf1=sales.reference_no AND ' . $this->db->dbprefix('purchases') . '.supplier_id=sales.biller_id', 'left');
        $this->db->where('sma_sales.biller_id', $company_id);
        $this->db->where('sma_sales.pos !=', 1);
        $this->db->where('sma_sales.company_id', $company_id);
        $this->db->where('sma_sales.sale_type', 'booking');
        $this->db->group_by('sma_sale_items.sale_id');

        if ($bool == 1 && $sorttype) {
            $this->db->order_by('sma_sales.' . $sortby, $sorttype);
        } else {
            $this->db->order_by('sma_sales.date', 'desc');
        }

        if ($filter && $filter != '') {
            $this->db->where($filter);
        }

        if ($limit != null || $offset != null) {
            $this->db->limit($limit, $offset);
        }

        if (!$date_range) {
            if ($limit != null || $offset != null) {
                $this->db->limit($limit, $offset);
            } else {
                $this->db->limit(100);
            }
        } else {
            $this->db->where($date_range);
        }
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getSalesBookingAll($company_id, $filter = null, $date_range = null)
    {
        $deliveryStatus = "(
            SELECT
                sma_sales.id AS sale_id,
            CASE
                    
                    WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                    AND SUM( sent_quantity ) = 0 THEN
                        'pending' 
                        WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                        AND SUM( sent_quantity ) > 0 THEN
                            'partial' 
                            WHEN SUM( unit_quantity ) <= SUM( sent_quantity ) 
                            AND SUM( sent_quantity )> 0 THEN
                                'done' 
                                END AS delivery_status 
                        FROM
                            `sma_sales`
                            LEFT JOIN `sma_sale_items` ON `sma_sale_items`.`sale_id` = `sma_sales`.`id`
                        GROUP BY
                            `sma_sale_items`.`sale_id` 
                        ) delivery_status";
        $this->db
            ->select("{$this->db->dbprefix('sales')}.*, IF({$this->db->dbprefix('sales')}.client_id = 'aksestoko', CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, ' (AksesToko)'), CONCAT( {$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name)) AS created_by, {$this->db->dbprefix('purchases')}.payment_method, IF({$this->db->dbprefix('purchases')}.payment_method = 'kredit_pro', CONCAT({$this->db->dbprefix('purchases')}.status, CONCAT('|', {$this->db->dbprefix('purchases')}.payment_status)), '-|-') AS 'status_kredit_pro', delivery_status.delivery_status")
            ->from('sales');
        $this->db->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left');
        $this->db->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left');
        $this->db->join($deliveryStatus, 'delivery_status.sale_id = sales.id');
        $this->db->join($this->db->dbprefix('purchases'), $this->db->dbprefix('purchases') . '.cf1=sales.reference_no AND ' . $this->db->dbprefix('purchases') . '.supplier_id=sales.biller_id', 'left');
        $this->db->where('sma_sales.biller_id', $company_id);
        $this->db->where('sma_sales.pos !=', 1);
        $this->db->where('sma_sales.company_id', $company_id);
        $this->db->where('sma_sales.sale_type', 'booking');
        $this->db->group_by('sma_sale_items.sale_id');
        $this->db->order_by('sma_sales.date', 'asc');

        if ($filter && $filter != '') {
            $this->db->where($filter);
        }

        if ($date_range) {
            $this->db->where($date_range);
        }

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
    }
    /**
     * Fungsi digunakan setelah trans commit untuk pemesanan pada aksestoko (saat ini)
     * namun pengecekan ini bisa juga digunakan untuk sale manual distributor.
     */
    public function checkDupplicateNoSaleRef($new_sale, $new_purchase = null)
    {
        $this->db->trans_begin();
        try {
            $sale_by_ref = $this->getSalesByRefNo($new_sale->reference_no, $new_sale->biller_id, [$new_sale->id]);
            if ($sale_by_ref && $sale_by_ref->id < $new_sale->id) {
                $new_so_ref = $new_purchase ? substr_replace($this->at_site->getReference('so', $new_sale->biller_id), "/AT", 4, 0) : $this->at_site->getReference('so', $new_sale->biller_id);
                $data_update_so = [
                    'reference_no' => $new_so_ref
                ];
                $this->db->update('sales', $data_update_so, ['id' => $new_sale->id]);
                if ($new_purchase) {
                    $data_update_po = [
                        'cf1' => $new_so_ref
                    ];
                    $this->db->update('purchases', $data_update_po, ['id' => $new_purchase->id]);
                }
                $this->site->updateReference('so', $new_sale->biller_id);
            }

            $this->db->trans_commit();
            return true;
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            return false;
        }
        return true;
    }

    /**
     * Fungsi digunakan setelah trans commit untuk pengiriman (DO)
     */
    public function checkDupplicateNoDeliveryRef($new_delivery, $sale)
    {
        $this->db->trans_begin();
        try {
            $delivery_by_ref = $this->getDeliveryByRefNo($new_delivery->do_reference_no, $sale->biller_id, [$new_delivery->id]);
            if ($delivery_by_ref && $delivery_by_ref->id < $new_delivery->id) {
                $new_do_ref = $this->at_site->getReference('do', $sale->biller_id);
                $data_update_do = [
                    'do_reference_no' => $new_do_ref
                ];
                $this->db->update('deliveries', $data_update_do, ['id' => $new_delivery->id]);

                $this->site->updateReference('do', $sale->biller_id);
            }

            $this->db->trans_commit();
            return true;
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            return false;
        }
        return true;
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
    //-----------------------------------------------------------------------------------------------------------------------------//

    public function gettransactionSalesBooking($company_id, $status, $year, $month, $warehouse_id = null)
    {
        $this->db->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id = sma_sales.id', 'left');
        $this->db->where('sma_sales.biller_id', $company_id);
        $this->db->where('sma_sales.pos !=', 1);
        $this->db->where('sma_sales.company_id', $company_id);
        $this->db->where('sma_sales.sale_type', 'booking');
        $this->db->where('year(sma_sales.date)', $year);
        $this->db->where('month(sma_sales.date)', $month);
        if ($warehouse_id) {
            $this->db->where('sma_sales.warehouse_id', $warehouse_id);
        }
        $this->db->where('sma_sales.sale_status', $status);
        $this->db->where("(sma_sales.client_id != 'aksestoko' OR sma_sales.client_id IS NULL)");
        $this->db->group_by('sma_sales.id');
        $this->db->order_by('sma_sales.date', 'asc');
        $q = $this->db->get('sma_sales');

        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return 0;
    }

    public function getTOP($company_id = null)
    {
        if (!$company_id) {
            $company_id = $this->session->userdata('company_id');
        }

        $this->db->order_by('duration', 'ASC')
            ->where('is_active', 1)
            ->where('company_id', $company_id);
        $tempo = $this->db->get('top');
        if ($tempo->num_rows() > 0) {
            return $tempo->result();
        }
        return false;
    }

    public function findCompanyWarehouse($company_id)
    {
        $q = $this->db->get_where('warehouses', ['company_id' => $company_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function insert_order($data_order, $data_detail)
    {
        if (!$this->db->insert('atl_orders', $data_order)) {
            throw new \Exception($this->db->error()['message']);
        }

        //Kondisi apabila payment method menggunakan kreditpro
        if ($data_order['paymentmethodid'] == "4") {
            $data_kreditpro_status = [
                'sale_id' => $data_order['sale_id'],
                'company_id' => $data_order['company_id'],
                'orderid' => $data_order['orderid'],
                'statuskredit' => '111',
                'status' => 'waiting',
                'datetime' => $data_order['orderdate'],
            ];
            if (!$this->db->insert('atl_kreditpro_status', $data_kreditpro_status)) {
                throw new \Exception($this->db->error()['message']);
            }
        }

        $get_sale_item = $this->getSaleItemsBySaleId($data_order['sale_id']);

        foreach ($data_detail as $item) {
            foreach ($get_sale_item as $k => $v) {
                if ($item['productcode'] == $v->product_code) {
                    $item['sale_item_id'] = $v->id;
                }
            }
            if (!$this->db->insert('atl_order_items', $item)) {
                throw new \Exception($this->db->error()['message']);
            }
        }
        return true;
    }
    //-----------------------------------------------------------------------------------------------------------------------------//
    public function insert_payment_atl($data_payment)
    {
        if ($this->db->insert('atl_payments', $data_payment)) {
            $id = $this->db->insert_id();
            $this->updateTransaction($data_payment);
            return $id;
        }
        return false;
    }

    public function findPaymentAtl($where)
    {
        $this->db->where($where);
        $q = $this->db->get('atl_payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //-----------------------------------------------------------------------------------------------------------------------------//
    public function updateTransaction($data)
    {
        if (!$this->db->update('sales', array('updated_at' => date('Y-m-d H:i:s'), 'payment_status' => 'waiting'), array('id' => $data['sale_id']))) {
            throw new \Exception("Gagal Menyimpan Data");
        }
    }
    //-----------------------------------------------------------------------------------------------------------------------------//
    public function getSaleidAtl($order_id)
    {
        $this->db->where("orderid", $order_id);
        $q = $this->db->get('atl_orders');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function getOrderAtlBySaleId($sale_id)
    {
        $this->db->where("sale_id", $sale_id);
        $q = $this->db->get('atl_orders');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
    //-----------------------------------------------------------------------------------------------------------------------------//
    public function update_deliveries($id, $data_deliveries)
    {
        if ($this->db->update("deliveries", $data_deliveries, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function update_delivery_items($id, $data_item)
    {
        if ($this->db->update("delivery_items", $data_item, array('delivery_id' => $id, 'product_id' => $data_item['product_id']))) {
            return true;
        }
        return false;
    }

    public function get_atl_order($order_id)
    {
        $q = $this->db->get_where("atl_orders", array('orderid' => $order_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function get_atl_order_item($order_id)
    {
        $q = $this->db->get_where('atl_order_items', array('orderid' => $orderid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAtlKreditproStatus($order_id)
    {
        $q = $this->db->get_where("atl_kreditpro_status", array('orderid' => $order_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function insert_atl_confirm($data_confirm)
    {
        if ($this->db->insert("atl_confirmation_deliveries", $data_confirm)) {
            return true;
        }
        return false;
    }

    public function insert_atl_confirm_item($data_item)
    {
        if ($this->db->insert_batch("atl_confirmation_delivery_items", $data_item)) {
            return true;
        }
        return false;
    }

    public function get_atl_confirmation($doid)
    {
        $q = $this->db->get_where("atl_confirmation_deliveries", array('doid' => $doid));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function get_delivery_by_atldoid($doid)
    {
        $q = $this->db->get_where("deliveries", array('atl_doid' => $doid));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findDeliveryItem($where)
    {
        $q = $this->db->get_where("delivery_items", $where, 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //--------------------------------------------------------------------------------------------------------------------//
    public function getProductNameSugges($term, $warehouse_id, $limit = null)
    {
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name, gross.quantity as gqty, gross.warehouse_id as gwid, gross.price as gprice, gross.operation, units.name as unit_name, brands.name as brand, units.code as unit_code', false)
            ->join($wp, 'FWP.product_id = products.id', 'left')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->join('gross', 'gross.product_id = products.id', 'left')
            ->join('consignment_products', 'products.id = consignment_products.product_id', 'left')
            ->join('units', 'products.unit = units.id', 'left')
            ->join('brands', 'products.brand = brands.id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("(products.track_quantity = 0 OR FWP.quantity > 0 OR consignment_products.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        }

        if (!$this->Owner) {
            $this->db->where('products.company_id', $this->session->userdata('company_id'));
        }

        $this->db->where("products.is_deleted", null);
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
}

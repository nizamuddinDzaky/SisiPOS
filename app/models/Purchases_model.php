<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchases_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $id_supplier = null, $limit = 5)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
            if ($id_supplier) {
                $this->db->group_start();
                for ($i = 1; $i <= 5; $i++) {
                    if ($i == 1) {
                        $this->db->where('supplier' . $i, $id_supplier);
                    }
                    $this->db->or_where('supplier' . $i, $id_supplier);
                }
                $this->db->group_end();
            }
        }
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->where("is_deleted", null);
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductsByCode($code)
    {
        $this->db->select('*')->from('products')->like('code', $code, 'both');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code, 'company_id' => $this->session->userdata('company_id')), 1);
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

    public function getAllPurchases($where = null, $date_range = null, $limit = null, $offset = null)
    {
        if ($where) {
            $this->db->where($where);
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

        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getCountPendingPurchases()
    {
        $q = $this->db->get_where('purchases', [
            'status'     => 'pending',
            'company_id' => $this->session->userdata('company_id')
        ]);
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }

        return 0;
    }

    public function getCountUnwatchedPurchases()
    {
        $this->db
            ->select('COUNT(*) as count')
            ->where('is_watched', 0)
            ->where('company_id', $this->session->userdata('company_id'));
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                return $row->count;
            }
        }

        return 0;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
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

    public function getPurchaseByID($id, $company_id = null)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $company_id ?? $this->session->userdata('company_id'));
        }

        $q = $this->db->get_where('purchases', array('id' => $id), 1);
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

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return true;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                return true;
            }
        }
        return false;
    }

    public function resetProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                return true;
            }
        }
        return false;
    }

    public function getOverSoldCosting($product_id)
    {
        $q = $this->db->get_where('costing', array('overselling' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addPurchase($data, $items)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $this->db->insert('purchase_items', $item);
                $purchases_item_id = $this->db->insert_id();
                if ($this->Settings->update_cost) {
                    $this->db->update('products', array('cost' => $item['real_unit_cost']), array('id' => $item['product_id'], 'company_id' => $this->session->userdata('company_id')));
                }
                if ($item['option_id']) {
                    $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                }
                if ($data['status'] == 'received' || $data['status'] == 'returned') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }

            $this->load->model('Official_model');
            $json = $this->Official_model->order_to_partner($data, $items, $purchase_id);

            if ($data['status'] == 'returned') {
                $this->db->update('deliveries_smig', array('status_penerimaan' => $data['status']), array('no_do' => @$data['sino_do']));
                $this->site->updateReference('rep');
                $this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'], 'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id), array('id' => $data['purchase_id']));
            }

            if ($data['status'] == 'received' || $data['status'] == 'returned') {
                $this->db->update('deliveries_smig', array('status_penerimaan' => $data['status']), array('no_do' => @$data['sino_do']));
                $this->site->syncQuantity(null, $purchase_id);
            }

            if ($data['status'] == 'received') {
                foreach ($items as $item) {
                    $data = [
                        "purchase_item_id"  => $purchases_item_id,
                        "purchase_id"       => $purchase_id,
                        "reference_no"      => $data['reference_no'],
                        "product_id"        => $item['product_id'],
                        "product_code"      => $item['product_code'],
                        "product_name"      => $item['product_name'],
                        "quantity"          => $item['quantity'],
                        "quantity_received" => $item['quantity'],
                        "sino_do"           => $data['sino_do'],
                        "created_by"        => $this->session->userdata('user_id'),
                    ];
                    $this->insertStoryReceived($data);
                }
            }

            return $purchase_id;
        }
        return false;
    }

    public function updatePurchase($id, $data, $items = array())
    {
        $purchase              = $this->getPurchaseByID($id);
        $oitems                = $this->getAllPurchaseItems($id);
        $data['updated_at']    = date('Y-m-d H:i:s');
        $data['updated_by']    = $this->session->userdata('user_id');
        if ($this->db->update('purchases', $data, array('id' => $id)) && $this->db->delete('purchase_items', array('purchase_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $this->db->insert('purchase_items', $item);
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }
            $this->site->syncQuantity(null, null, $oitems);
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                $this->site->syncQuantity(null, $id);

                $oitems = $this->getAllPurchaseItems($id);
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost));
                    if ($data['status'] == 'received' || $data['status'] == 'partial') {
                        $data = [
                            "purchase_item_id"  => $oitem->id,
                            "purchase_id"       => $id,
                            "reference_no"      => $purchase->reference_no,
                            "product_id"        => $oitem->product_id,
                            "product_code"      => $oitem->product_code,
                            "product_name"      => $oitem->product_name,
                            "quantity"          => $oitem->quantity,
                            "quantity_received" => $oitem->quantity_received,
                            "sino_do"           => $oitem->sino_do,
                            "created_by"        => $this->session->userdata('user_id'),
                        ];
                        $this->insertStoryReceived($data);
                    }
                }
            }
            if ($data['status'] == 'received') {
                $this->load->model('Official_model');
                $this->Official_model->check_order($id);
            }
            $this->site->syncPurchasePayments($id);
            return true;
        }

        return false;
    }

    public function updateStatus($id, $data)
    {
        $purchase          = $this->getPurchaseByID($id);
        $items             = $this->site->getAllPurchaseItems($id);
        $status            = $data['status'];
        $note              = $data['note'];
        $received_amount   = $data['received_amount'];
        $purchases_item_id = $data['purchases_item_id'];
        $do_reference      = $data['do_reference'];

        $is_parsial = false;
        if ($this->db->update('purchases', array('updated_by' => $this->session->userdata('user_id'), 'updated_at' => date('Y-m-d H:i:s'), 'status' => $status, 'note' => $note), array('id' => $id))) {
            foreach ($items as $i => $item) {
                if ($status == 'received' || $status == 'partial') {
                    $qr = $qb = $item->quantity_received + $received_amount[$i];
                    //                    $qb = $item->quantity - $qr;
                    if (!$is_parsial) {
                        $status     = $qr == $item->quantity ? $status : "partial";
                        $is_parsial = true;
                    }
                    $this->db->update('purchase_items', array('status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr), array('id' => $purchases_item_id[$i]));
                } else {
                    $qb = $status == 'completed' ? ($item->quantity_balance + ($item->quantity - $item->quantity_received)) : $item->quantity_balance;
                    $qr = $status == 'completed' ? $item->quantity : $item->quantity_received;
                    $this->db->update('purchase_items', array('status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr), array('id' => $item->id));
                }
                $this->updateAVCO(array('product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost));
            }
            $this->site->syncQuantity(null, null, $items);

            foreach ($items as $i => $item) {
                if ($status == 'received' || $status == 'partial') {
                    $data = [
                        "purchase_item_id"  => $purchases_item_id[$i],
                        "purchase_id"       => $id,
                        "reference_no"      => $purchase->reference_no,
                        "product_id"        => $item->product_id,
                        "product_code"      => $item->product_code,
                        "product_name"      => $item->product_name,
                        "quantity"          => $item->quantity,
                        "quantity_received" => $received_amount[$i],
                        "sino_do"           => $do_reference,
                        "created_by"        => $this->session->userdata('user_id'),
                    ];
                    $this->insertStoryReceived($data);
                }
            }


            if ($status == 'received') {
                $this->load->model('Official_model');
                $this->Official_model->check_order($id);
            }
            if ($status == "partial") {
                $this->db->update('purchases', array('updated_at' => date('Y-m-d H:i:s'), 'status' => $status), array('id' => $id));
            }
            return true;
        }
        return false;
    }

    public function deletePurchase($id)
    {
        $purchase       = $this->getPurchaseByID($id);
        $purchase_items = $this->site->getAllPurchaseItems($id);
        if ($this->db->delete('purchase_items', array('purchase_id' => $id)) && $this->db->delete('purchases', array('id' => $id))) {
            $this->db->delete('payments', array('purchase_id' => $id));
            if ($purchase->status == 'received' || $purchase->status == 'partial') {
                foreach ($purchase_items as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost));
                    $received = $oitem->quantity_received ? $oitem->quantity_received : $oitem->quantity;
                    if ($oitem->quantity_balance < $received) {
                        $clause = array('purchase_id' => null, 'transfer_id' => null, 'product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'option_id' => $oitem->option_id);
                        if ($pi = $this->site->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + ($oitem->quantity_balance - $received);
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
                        } else {
                            $clause['quantity']         = 0;
                            $clause['item_tax']         = 0;
                            $clause['quantity_balance'] = ($oitem->quantity_balance - $received);
                            $this->db->insert('purchase_items', $clause);
                        }
                    }
                }
            }
            $this->site->syncQuantity(null, null, $purchase_items);
            return true;
        }
        return false;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id, $company_id = null)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id, 'company_id' => $company_id ?? $this->session->userdata('company_id')), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type, payments.date_dist, payments.reference_dist')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addPayment($data = array())
    {
        if ($this->db->insert('payments', $data)) {
            $data['id'] = $this->db->insert_id();
            if ($this->site->getReference('ppay') == $data['reference_no']) {
                $this->site->updateReference('ppay');
            }
            $this->site->syncPurchasePayments($data['purchase_id']);
            $this->load->model('Official_model');
            $this->Official_model->payment_to_partner($data);
            return true;
        }
        return false;
    }

    public function updatePayment($id, $data = array())
    {
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncPurchasePayments($data['purchase_id']);
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->syncPurchasePayments($opay->purchase_id);
            return true;
        }
        return false;
    }

    public function getProductOptions($product_id)
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

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addExpense($data = array())
    {
        if ($this->db->insert('expenses', $data)) {
            if ($this->site->getReference('ex') == $data['reference']) {
                $this->site->updateReference('ex');
            }
            return true;
        }
        return false;
    }

    public function updateExpense($id, $data = array())
    {
        if ($this->db->update('expenses', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteExpense($id)
    {
        if ($this->db->delete('expenses', array('id' => $id))) {
            return true;
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

    public function getDeliveriesSmigByID($id, $company_id = null)
    {
        if ($company_id) {
            $this->db->where('biller_id', $company_id);
        }
        $q = $this->db->get_where('deliveries_smig', array('id' => $id), 1);
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

    public function getAllDeliveriesSmigtems($quote_id)
    {
        $q = $this->db->get_where('deliveries_smig_items', array('deliveries_smig_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('return_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllReturnItems($return_id)
    {
        $this->db->select('return_purchase_items.*, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=return_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_purchase_items.option_id', 'left')
            ->group_by('return_purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('return_purchase_items', array('return_id' => $return_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPurcahseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function returnPurchase($data = array(), $items = array())
    {
        $purchase_items = $this->site->getAllPurchaseItems($data['purchase_id']);

        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('rep') == $data['reference_no']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $this->db->insert('return_purchase_items', $item);

                if ($purchase_item = $this->getPurcahseItemByID($item['purchase_item_id'])) {
                    if ($purchase_item->quantity == $item['quantity']) {
                        $this->db->delete('purchase_items', array('id' => $item['purchase_item_id']));
                    } else {
                        $nqty          = $purchase_item->quantity - $item['quantity'];
                        $bqty          = $purchase_item->quantity_balance - $item['quantity'];
                        $rqty          = $purchase_item->quantity_received - $item['quantity'];
                        $tax           = $purchase_item->unit_cost - $purchase_item->net_unit_cost;
                        $discount      = $purchase_item->item_discount / $purchase_item->quantity;
                        $item_tax      = $tax * $nqty;
                        $item_discount = $discount * $nqty;
                        $subtotal      = $purchase_item->unit_cost * $nqty;
                        $this->db->update('purchase_items', array('quantity' => $nqty, 'quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal), array('id' => $item['purchase_item_id']));
                    }
                }
            }
            $this->calculatePurchaseTotals($data['purchase_id'], $return_id, $data['surcharge']);
            $this->site->syncQuantity(null, null, $purchase_items);
            $this->site->syncQuantity(null, $data['purchase_id']);
            return true;
        }
        return false;
    }

    public function calculatePurchaseTotals($id, $return_id, $surcharge)
    {
        $purchase = $this->getPurchaseByID($id);
        $items    = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total            = 0;
            $product_tax      = 0;
            $order_tax        = 0;
            $product_discount = 0;
            $order_discount   = 0;
            foreach ($items as $item) {
                $product_tax      += $item->item_tax;
                $product_discount += $item->item_discount;
                $total            += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage        = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos              = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods            = explode("%", $order_discount_id);
                    $order_discount = (($total + $product_tax) * (float) ($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax      = $product_tax + $order_tax;
            $grand_total    = $total + $total_tax + $purchase->shipping - $order_discount + $surcharge;
            $data           = array(
                'total'            => $total,
                'product_discount' => $product_discount,
                'order_discount'   => $order_discount,
                'total_discount'   => $total_discount,
                'product_tax'      => $product_tax,
                'order_tax'        => $order_tax,
                'total_tax'        => $total_tax,
                'grand_total'      => $grand_total,
                'return_id'        => $return_id,
                'surcharge'        => $surcharge,
                'updated_at'       => date('Y-m-d H:i:s')
            );

            if ($this->db->update('purchases', $data, array('id' => $id))) {
                return true;
            }
        } else {
            $this->db->delete('purchases', array('id' => $id));
        }
        return false;
    }

    public function getExpenseCategories($company_id)
    {
        $this->db->where("`company_id` = '$company_id' AND is_deleted IS NULL");
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where("expense_categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateAVCO($data, $company_id = null)
    {
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'], $company_id)) {
            $total_cost     = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];
            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('warehouses_products', array('avg_cost' => $avg_cost), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'company_id' => $company_id ?? $this->session->userdata('company_id')));
            }
        } else {
            $this->db->insert('warehouses_products', array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0, 'company_id' => $company_id ?? $this->session->userdata('company_id')));
        }
    }

    public function insertStoryReceived($data)
    {
        $this->db->dbprefix = '';
        if ($this->db->insert('story_received_purchases', $data)) {
            $this->db->dbprefix = 'sma_';
            return true;
        }
        $this->db->dbprefix = 'sma_';
        return false;
    }

    public function getStoryReceived($purchase_id)
    {
        $this->db->dbprefix = '';
        $q = $this->db->order_by('id', 'desc')->get_where('story_received_purchases', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllDeliverySmig($filter = null, $date_range = null)
    {
        $this->db
            ->select("deliveries_smig.*")
            ->from('deliveries_smig');

        if ($filter && $filter != '') {
            $this->db->where($filter);
        }

        if (!$date_range) {
            $this->db->limit(100);
        } else {
            $this->db->where($date_range);
        }

        $q = $this->db->get();

        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getPurchasesByDOSpjSo($no_do, $no_spj, $no_so)
    {
        $q = $this->db->get_where('purchases', array('sino_do' => $no_do, 'sino_spj' => $no_spj, 'sino_so' => $no_so), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllDeliverySmigPagination($filter = null, $date_range = null, $limit = null, $offset = null, $sortby = null, $sorttype = null)
    {
        $sql = 'SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = "' . getenv('DB_DATABASE') . '" AND table_name = "sma_deliveries_smig"';
        $query = $this->db->query($sql);
        $bool = 0;
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                if ($sortby == $row->COLUMN_NAME) {
                    $bool = 1;
                }
            }
        }

        $this->db
            ->select("deliveries_smig.*")
            ->from('deliveries_smig');

        if ($bool == 1 && $sorttype) {
            $this->db->order_by('deliveries_smig.' . $sortby, $sorttype);
        } else {
            $this->db->order_by('deliveries_smig.tanggal_do', 'desc');
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

        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getDeliverySmigAll($filter = null, $date_range = null)
    {
        $this->db
            ->select("deliveries_smig.*")
            ->from('deliveries_smig');

        if ($filter && $filter != '') {
            $this->db->where($filter);
        }

        if ($date_range) {
            $this->db->where($date_range);
        }

        $q = $this->db->get();

        if ($q && $q->num_rows() > 0) {
            return $q->num_rows();
        }
        return false;
    }

    public function getPurchasesBySaleRefNo($sale_no_reference, $supplier_id)
    {
        $q = $this->db->get_where('purchases', array('cf1' => $sale_no_reference, 'supplier_id' => $supplier_id, 'is_deleted' => null), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}

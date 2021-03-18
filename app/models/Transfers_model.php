<?php defined('BASEPATH') or exit('No direct script access allowed');

class Transfers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $warehouse_id, $limit = 5)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate, type, unit, purchase_unit, tax_method')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('sma_products.id,
                            CODE,
                            NAME,
                            sma_warehouses_products.quantity,
                            cost,
                            tax_rate,
                            type,
                            unit,
                            purchase_unit,
                            tax_method');
        if ($this->Settings->overselling) {
            $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("type = 'standard' AND warehouses_products.warehouse_id = '" . $warehouse_id . "' AND warehouses_products.quantity > 0 AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
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

    public function getWHProduct($id)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', array('warehouses_products.product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function addTransfer($data = array(), $items = array())
    {
        $status = $data['status'];
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            if ($this->site->getReference('to') == $data['transfer_no']) {
                $this->site->updateReference('to');
            }
            foreach ($items as $item) {
                $item['transfer_id'] = $transfer_id;
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                if ($status == 'sent' || $status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['quantity'], $item['option_id']);
                }
            }

            return true;
        }
        return false;
    }

    public function updateTransfer($id, $data = array(), $items = array())
    {
        $ostatus = $this->resetTransferActions($id);
        $status = $data['status'];

        if ($this->db->update('transfers', $data, array('id' => $id))) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, array('transfer_id' => $id));

            foreach ($items as $item) {
                $item['transfer_id'] = $id;
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                if ($data['status'] == 'sent' || $data['status'] == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['quantity'], $item['option_id']);
                }
            }

            return true;
        }

        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        $ostatus = $this->resetTransferActions($id);
        $transfer = $this->getTransferByID($id);
        $items = $this->getAllTransferItems($id, $transfer->status);

        if ($this->db->update('transfers', array('status' => $status, 'note' => $note), array('id' => $id))) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, array('transfer_id' => $id));

            foreach ($items as $item) {
                $item = (array) $item;
                $item['transfer_id'] = $id;
                unset($item['id'], $item['variant'], $item['unit'], $item['updated_at']);
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $transfer->to_warehouse_id;
                    $item['status'] = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                if ($status == 'sent' || $status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $transfer->from_warehouse_id, $item['quantity'], $item['option_id']);
                } else {
                    $this->site->syncQuantity(null, null, null, $item['product_id']);
                }
            }
            return true;
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

    public function getProductByCategoryID($id)
    {
        $q = $this->db->get_where('products', array('category_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }

        return false;
    }

    public function getProductQuantity($product_id, $warehouse = DEFAULT_WAREHOUSE)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return false;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->update('warehouses_products', array('quantity' => $quantity), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code,'company_id' =>$this->session->userdata('company_id')), 1);
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
 
    public function getTransferByID($id)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        {
        $q = $this->db->get_where('transfers', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }
    }

    public function getAllTransferItems($transfer_id, $status)
    {
        if ($status == 'completed') {
            $this->db->select('purchase_items.*, product_variants.name as variant, products.unit')
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
                ->group_by('purchase_items.id')
                ->where('transfer_id', $transfer_id);
        } else {
            $this->db->select('transfer_items.*, product_variants.name as variant, products.unit')
                ->from('transfer_items')
                ->join('products', 'products.id=transfer_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
                ->group_by('transfer_items.id')
                ->where('transfer_id', $transfer_id);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getWarehouseProduct($warehouse_id, $product_id, $variant_id)
    {
        if ($variant_id) {
            return $this->getProductWarehouseOptionQty($variant_id, $warehouse_id);
        } else {
            return $this->getWarehouseProductQuantity($warehouse_id, $product_id);
        }
        return false;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function resetTransferActions($id)
    {
        $otransfer = $this->getTransferByID($id);
        $oitems = $this->getAllTransferItems($id, $otransfer->status);
        $ostatus = $otransfer->status;
        if ($ostatus == 'sent' || $ostatus == 'completed') {
            // $this->db->update('purchase_items', array('warehouse_id' => $otransfer->from_warehouse_id, 'transfer_id' => NULL), array('transfer_id' => $otransfer->id));
            foreach ($oitems as $item) {
                $option_id = (isset($item->option_id) && ! empty($item->option_id)) ? $item->option_id : null;
                $clause = array('purchase_id' => null, 'transfer_id' => null, 'product_id' => $item->product_id, 'warehouse_id' => $otransfer->from_warehouse_id, 'option_id' => $option_id);
                $pi = $this->site->getPurchasedItem(array('id' => $item->id));
                if ($ppi = $this->site->getPurchasedItem($clause)) {
                    $quantity_balance = $ppi->quantity_balance + $item->quantity;
                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $ppi->id));
                } else {
                    $clause['quantity'] = $item->quantity;
                    $clause['item_tax'] = 0;
                    $clause['quantity_balance'] = $item->quantity;
                    $clause['status'] = 'received';
                    $this->db->insert('purchase_items', $clause);
                }
            }
        }
        return $ostatus;
    }

    public function deleteTransfer($id)
    {
        $ostatus = $this->resetTransferActions($id);
        $oitems = $this->getAllTransferItems($id, $ostatus);
        $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
        if ($this->db->delete('transfers', array('id' => $id)) && $this->db->delete($tbl, array('transfer_id' => $id))) {
            foreach ($oitems as $item) {
                $this->site->syncQuantity(null, null, null, $item->product_id);
            }
            return true;
        }
        return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $zero_check = true)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->group_by('sma_product_variants.id,sma_product_variants.NAME,sma_product_variants.cost,sma_product_variants.quantity,sma_warehouses_products_variants.quantity');
        if ($zero_check) {
            $this->db->where('warehouses_products_variants.quantity >', 0);
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

    public function getProductComboItems($pid, $warehouse_id)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
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

    public function syncTransderdItem($product_id, $warehouse_id, $quantity, $option_id = null)
    {
        if ($pis = $this->site->getPurchasedItems($product_id, $warehouse_id, $option_id)) {
            $balance_qty = $quantity;
            foreach ($pis as $pi) {
                if ($balance_qty <= $quantity && $quantity > 0) {
                    if ($pi->quantity_balance >= $quantity) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $this->db->update('purchase_items', array('quantity_balance' => $balance_qty), array('id' => $pi->id));
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $this->db->update('purchase_items', array('quantity_balance' => 0), array('id' => $pi->id));
                    }
                }
                if ($quantity == 0) {
                    break;
                }
            }
        } else {
            $clause = array('purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
            if ($pi = $this->site->getPurchasedItem($clause)) {
                $quantity_balance = $pi->quantity_balance - $quantity;
                $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
            } else {
                $clause['quantity'] = 0;
                $clause['item_tax'] = 0;
                $clause['status'] = 'received';
                $clause['quantity_balance'] = (0 - $quantity);
                $this->db->insert('purchase_items', $clause);
            }
        }
        $this->site->syncQuantity(null, null, null, $product_id);
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllProducts($where = null)
    {
        if ($where) {
            $this->db->where($where);
        }
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSubCategoryProducts($subcategory_id)
    {
        $q = $this->db->get_where('products', array('subcategory_id' => $subcategory_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptions($pid)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptionsWithWH($pid)
    {
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(array('' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'))
            ->order_by('product_variants.id');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $pid, 'warehouses_products_variants.quantity !=' => null));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductComboItems($pid)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price')->join('products', 'products.code=combo_items.item_code', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductByID($id, $company_id = null)
    {
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDetailProductByID($id, $company_id = null)
    {
        $this->db
            ->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category_name, ' . $this->db->dbprefix('brands') . '.name as brand_name, ' . $this->db->dbprefix('units') . '.name as unit_name')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('brands', 'brands.id = products.brand', 'left')
            ->join('units', 'units.id = products.unit', 'left');

        if ($company_id) {
            $this->db->where('sma_products.company_id', $company_id);
        }

        $q = $this->db->get_where('products', array('sma_products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductWithCategory($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function has_purchase($product_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function getProductDetails($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('categories') . '.code as category_code, cost, price, quantity, alert_quantity')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductDetail($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('tax_rates') . '.name as tax_rate_name, ' . $this->db->dbprefix('tax_rates') . '.code as tax_rate_code, c.code as category_code, sc.code as subcategory_code', false)
            ->join('tax_rates', 'tax_rates.id=products.tax_rate', 'left')
            ->join('categories c', 'c.id=products.category_id', 'left')
            ->join('categories sc', 'sc.id=products.subcategory_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSubCategories($parent_id)
    {
        if (!$this->Owner) {
            $this->db->where(" (company_id = " . $this->session->userdata('company_id') . " or company_id = 1) ")->order_by('name');
        }
        $this->db->select('id as id, name as text')
            ->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getAllWarehousesWithPQ($product_id, $warehouse_id = null)
    {
        if (!$this->Owner) {
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }

        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity, '
            . $this->db->dbprefix('warehouses_products') . '.rack, ' . $this->db->dbprefix('warehouses_products') . '.quantity_booking')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->where('warehouses.is_deleted', null)
            ->group_by('warehouses.id');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductPhotos($id)
    {
        $q = $this->db->get_where("product_photos", array('product_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code, $comp_id = null)
    {
        if ($comp_id == null) {
            $comp_id = $this->session->userdata('company_id');
        }
        $q = $this->db->get_where('products', array('code' => $code, 'company_id' => $comp_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();


            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $warehouses = $this->site->getAllWarehouses();
            if ($data['type'] == 'combo' || $data['type'] == 'service') {
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0, 'company_id' => $this->session->userdata('company_id')));
                }
            }

            if ($data['type'] == 'consignment') {
                foreach ($warehouses as $wh) {
                    $this->db->insert('consignment_products', array(
                        'product_name' => $data['name'],
                        'product_id' => $product_id,
                        'warehouse_id' => $wh->id,
                        'created_by' => $this->session->userdata('user_id'),
                        'company_id' => $this->session->userdata('company_id')
                    ));
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && !empty($wh_qty['quantity'])) {
                        $this->db->insert(
                            'warehouses_products',
                            array(
                                'product_id' => $product_id,
                                'warehouse_id' => $wh_qty['warehouse_id'],
                                'quantity' => $wh_qty['quantity'],
                                'rack' => $wh_qty['rack'],
                                'avg_cost' => $data['cost'],
                                'company_id' => isset($wh_qty['company_id']) ? $wh_qty['company_id']  : $this->session->userdata('company_id'),
                            )
                        );
                        $wh0qty = $wh_qty['quantity'];
                        if (!$product_attributes) {
                            $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                            $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : null;
                            $unit_cost = $data['cost'];
                            if ($tax_rate) {
                                if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                    if ($data['tax_method'] == '0') {
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                        $net_item_cost = $data['cost'] - $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    } else {
                                        $net_item_cost = $data['cost'];
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                        $unit_cost = $data['cost'] + $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    }
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $item_tax = $tax_rate->rate;
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = 0;
                            }

                            $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

                            // $item = array(
                            //     'product_id' => $product_id,
                            //     'product_code' => $data['code'],
                            //     'product_name' => $data['name'],
                            //     'net_unit_cost' => $net_item_cost,
                            //     'unit_cost' => $unit_cost,
                            //     'real_unit_cost' => $unit_cost,
                            //     'quantity' => $wh_qty['quantity'],
                            //     'quantity_balance' => $wh_qty['quantity'],
                            //     'item_tax' => $item_tax,
                            //     'tax_rate_id' => $tax_rate_id,
                            //     'tax' => $tax,
                            //     'subtotal' => $subtotal,
                            //     'warehouse_id' => $wh_qty['warehouse_id'],
                            //     'date' => date('Y-m-d'),
                            //     'status' => 'received',
                            // );
                            $item = array(
                                'product_id' => $product_id,
                                'product_code' => $data['code'],
                                'product_name' => $data['name'],
                                'net_unit_cost' => $net_item_cost,
                                'quantity' => $wh_qty['quantity'],
                                'warehouse_id' => $wh_qty['warehouse_id'],
                                'item_tax' => $item_tax,
                                'tax_rate_id' => $tax_rate_id,
                                'tax' => $tax,
                                'subtotal' => $subtotal,
                                'quantity_balance' => $wh_qty['quantity'],
                                'date' => date('Y-m-d'),
                                'status' => 'received',
                                'unit_cost' => $unit_cost,
                                'real_unit_cost' => $unit_cost,
                                'unit_quantity' => 0
                            );
                            $this->db->insert('purchase_items', $item);
                            $this->site->syncProductQty($product_id, $wh_qty['warehouse_id'], isset($wh_qty['company_id']) ? $wh_qty['company_id'] : null);
                        }
                    } else {
                        if (!$this->Owner) {
                            $this->load->model('Curl_model', 'curl_');
                            $this->curl_->get_EProduct($product_id);
                            $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => 0, 'company_id' => $this->session->userdata('company_id')));
                        }
                    }
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : null;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $product_id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        );
                        $this->db->insert('purchase_items', $item);
                    }

                    foreach ($warehouses as $warehouse) {
                        if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                            $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                        }
                    }

                    $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $product_id, 'photo' => $photo));
                    $gambar = $photo;
                }
            }
            return $product_id;
        }
        return false;
    }


    public function getPrductVariantByPIDandName($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addAjaxProduct($data)
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }
        return false;
    }

    public function add_products($products = array())
    {
        if (!empty($products)) {
            $warehouses = $this->site->getAllWarehouses();
            foreach ($products as $product) {
                $variants = explode('|', $product['variants']);
                unset($product['variants']);
                if ($this->db->insert('products', $product)) {
                    $product_id = $this->db->insert_id();
                    foreach ($variants as $variant) {
                        if ($variant && trim($variant) != '') {
                            $vat = array('product_id' => $product_id, 'name' => trim($variant));
                            $this->db->insert('product_variants', $vat);
                        }
                    }

                    foreach ($warehouses as $wh) { // Added by Gigih on 30-08-2018
                        $this->db->insert(
                            'warehouses_products',
                            array(
                                'product_id' => $product_id,
                                'warehouse_id' => $wh->id,
                                'quantity' => 0,
                                'company_id' => $this->session->userdata('company_id')
                            )
                        );
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname')
            ->where("type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
            ->where('' . $this->db->dbprefix('product_variants') . '.name', null)
            ->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getQASuggestions($term, $limit = 5)
    {
        if (!$this->Owner) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name')
            ->where("type != 'combo' AND type != 'consignment' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);

        $this->db->where("is_deleted", null);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductsForPrinting($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price')
            ->where("(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)
    {
        if ($this->db->update('products', $data, array('id' => $id))) {
            //            if($consignment){
            //                foreach($consignment as $csg){
            //                    if($csg['c_id']){
            //                        $this->db->update('consignment', array('quantity'=>$csg['quantity']) , array('id'=>$csg['c_id']));
            //                    }else{
            //                        $csg['product_id']=$id;
            //                        $csg['product_name']=$this->site->getProductByID($id)->name;
            //                        $this->db->insert('consignment', $csg);
            //                    }
            //                }
            //            }

            $product_consignments = $this->getCSGProductByPID($id);
            if ($product_consignments) {
                foreach ($product_consignments as $pr_csg) {
                    if ($pr_csg->is_deleted == null && $data['type'] != 'consignment') {
                        $this->db->update('consignment_products', array('is_deleted' => 1), array('product_id' => $id));
                    } elseif ($pr_csg->is_deleted && $data['type'] == 'consignment') {
                        $this->db->update('consignment_products', array('is_deleted' => null), array('product_id' => $id));
                    }
                }
            } else {
                $warehouses = $this->site->getAllWarehouses();
                foreach ($warehouses as $wh) {
                    $this->db->insert('consignment_products', array(
                        'product_name' => $data['name'],
                        'product_id' => $id,
                        'warehouse_id' => $wh->id,
                        'created_by' => $this->session->userdata('user_id'),
                        'company_id' => $this->session->userdata('company_id')
                    ));
                }
            }

            if ($items) {
                $this->db->delete('combo_items', array('product_id' => $id));
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    $this->db->update('warehouses_products', array('rack' => $wh_qty['rack']), array('product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']));
                }
            }

            if ($update_variants) {
                $this->db->update_batch('product_variants', $update_variants, 'id');
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $id, 'photo' => $photo));
                    $gambar = $photo;
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr['product_id'] = $id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    $this->db->insert('product_variants', $pr_attr);
                    $option_id = $this->db->insert_id();

                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : null;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        );
                        $this->db->insert('purchase_items', $item);
                    }
                }
            }
            $this->site->syncQuantity(null, null, null, $id);
            return true;
        } else {
            return false;
        }
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            if ($this->db->update('warehouses_products_variants', array('quantity' => $quantity), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function updatePrice($data = array())
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id)
    {
        //        if ($this->db->delete('products', array('id' => $id)) && $this->db->delete('warehouses_products', array('product_id' => $id))) {
        //            $this->db->delete('warehouses_products_variants', array('product_id' => $id));
        //            $this->db->delete('product_variants', array('product_id' => $id));
        //            $this->db->delete('product_photos', array('product_id' => $id));
        //            $this->db->delete('product_prices', array('product_id' => $id));
        //            return true;
        //        }
        if ($this->db->update('products', array('is_deleted' => 1), array('id' => $id)) && $this->db->delete('warehouses_products', array('is_deleted' => 1), array('product_id' => $id))) {
            $this->db->update('warehouses_products_variants', array('is_deleted' => 1), array('product_id' => $id));
            $this->db->update('product_variants', array('is_deleted' => 1), array('product_id' => $id));
            $this->db->update('product_photos', array('is_deleted' => 1), array('product_id' => $id));
            $this->db->update('product_prices', array('is_deleted' => 1), array('product_id' => $id));
            return true;
        }
        return false;
    }


    public function totalCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        return $q->num_rows();
    }

    public function getCategoryByCode($code)
    {
        $q = $this->db->get_where('categories', array('code' => $code), 1);
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

    public function getAdjustmentByID($id)
    {
        $q = $this->db->get_where('adjustments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAdjustmentItems($adjustment_id)
    {
        $this->db->select('adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=adjustment_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=adjustment_items.option_id', 'left')
            ->group_by('adjustment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('adjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function syncAdjustment($data = array())
    {
        if (!empty($data)) {
            $where_clause = array('product_id' => $data['product_id'], 'option_id' => $data['option_id'], 'warehouse_id' => $data['warehouse_id'], 'status' => 'received');
            if ($purchase_item = $this->site->getPurchasedItem($where_clause)) {
                $quantity_balance = $data['type'] == 'subtraction' ? $purchase_item->quantity_balance - $data['quantity'] : $purchase_item->quantity_balance + $data['quantity'];

                $this->db->where('id', $purchase_item->id);
                $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance));
                // $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $purchase_item->id));
            } else {
                $pr = $this->site->getProductByID($data['product_id']);
                $item = array(
                    'product_id' => $data['product_id'],
                    'product_code' => $pr->code,
                    'product_name' => $pr->name,
                    'option_id' => $data['option_id'],
                    'net_unit_cost' => 0,
                    'quantity' => 0,
                    'warehouse_id' => $data['warehouse_id'],
                    'item_tax' => 0,
                    'tax_rate_id' => 0,
                    'tax' => '',
                    'subtotal' => 0,
                    'quantity_balance' => $data['type'] == 'subtraction' ? (0 - $data['quantity']) : $data['quantity'],
                    'date' => date('Y-m-d'),
                    'status' => 'received',
                    'unit_cost' => 0,
                    'unit_quantity' => 0
                );
                $this->db->insert('purchase_items', $item);
            }

            $this->site->syncProductQty($data['product_id'], $data['warehouse_id']);
            if ($data['option_id']) {
                $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
            }
        }
    }

    public function reverseAdjustment($id)
    {
        if ($products = $this->getAdjustmentItems($id)) {
            foreach ($products as $adjustment) {
                $where_clause = array('product_id' => $adjustment->product_id, 'warehouse_id' => $adjustment->warehouse_id, 'option_id' => $adjustment->option_id, 'status' => 'received');
                if ($purchase_item = $this->site->getPurchasedItem($where_clause)) {
                    $quantity_balance = $adjustment->type == 'subtraction' ? $purchase_item->quantity_balance + $adjustment->quantity : $purchase_item->quantity_balance - $adjustment->quantity;
                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $purchase_item->id));
                }

                $this->site->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
                if ($adjustment->option_id) {
                    $this->site->syncVariantQty($adjustment->option_id, $adjustment->warehouse_id, $adjustment->product_id);
                }
            }
        }
    }

    public function addAdjustment($data, $products)
    {
        if ($this->db->insert('adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                $this->db->insert('adjustment_items', $product);
                $this->syncAdjustment($product);
            }
            if ($this->site->getReference('qa') == $data['reference_no']) {
                $this->site->updateReference('qa');
            }
            return $adjustment_id;
        }
        return false;
    }

    public function updateAdjustment($id, $data, $products)
    {
        $this->reverseAdjustment($id);
        if (
            $this->db->update('adjustments', $data, array('id' => $id)) &&
            $this->db->delete('adjustment_items', array('adjustment_id' => $id))
        ) {
            foreach ($products as $product) {
                $product['adjustment_id'] = $id;
                $this->db->insert('adjustment_items', $product);
                $this->syncAdjustment($product);
            }
            return true;
        }
        return false;
    }

    public function deleteAdjustment($id)
    {
        $this->reverseAdjustment($id);
        if (
            $this->db->update('adjustments', array('is_deleted' => 1), array('id' => $id)) &&
            $this->db->update('adjustment_items', array('is_deleted' => 1), array('adjustment_id' => $id))
        ) {
            return true;
        }
        return false;
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return false;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            if ($this->updateQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return true;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return true;
            }
        }

        return false;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        $product = $this->site->getProductByID($product_id);
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity, 'rack' => $rack, 'avg_cost' => $product->cost, 'company_id' => $this->session->userdata('company_id')))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        $data = $rack ? array('quantity' => $quantity, 'rack' => $rack) : $data = array('quantity' => $quantity);
        if ($this->db->update('warehouses_products', $data, array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = null)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = null)
    {
        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->order_by("id", "asc");
        $query = $this->db->get("products");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function syncVariantQty($option_id)
    {
        $wh_pr_vars = $this->getProductWarehouseOptions($option_id);
        $qty = 0;
        foreach ($wh_pr_vars as $row) {
            $qty += $row->quantity;
        }
        if ($this->db->update('product_variants', array('quantity' => $qty), array('id' => $option_id))) {
            return true;
        }
        return false;
    }

    public function getProductWarehouseOptions($option_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function setRack($data)
    {
        if ($this->db->update('warehouses_products', array('rack' => $data['rack']), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']))) {
            return true;
        }
        return false;
    }

    public function getSoldQty($id)
    {
        $this->db->select("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) as sold, SUM( " . $this->db->dbprefix('sale_items') . ".subtotal ) as amount")
            ->from('sales')
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('sale_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasedQty($id)
    {
        $this->db->select("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . ".quantity ) as purchased, SUM( " . $this->db->dbprefix('purchase_items') . ".subtotal ) as amount")
            ->from('purchases')
            ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllVariants()
    {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseProductVariant($warehouse_id, $product_id, $option_id = null)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('product_id' => $product_id, 'option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseItems($purchase_id)
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

    public function getTransferItems($transfer_id)
    {
        $q = $this->db->get_where('purchase_items', array('transfer_id' => $transfer_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitByCode($code)
    {
        $q = $this->db->get_where("units", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBrandByName($name)
    {
        $q = $this->db->get_where('brands', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBrandByCode($code)
    {
        $q = $this->db->get_where('brands', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStockCountProducts($warehouse_id, $type, $categories = null, $brands = null)
    {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('warehouses_products')}.quantity as quantity")
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->where('products.type', 'standard')
            ->order_by('products.code', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if ($brands) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('products.brand', $brand);
                } else {
                    $this->db->or_where('products.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockCountProductVariants($warehouse_id, $product_id)
    {
        $this->db->select("{$this->db->dbprefix('product_variants')}.name, {$this->db->dbprefix('warehouses_products_variants')}.quantity as quantity")
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $product_id, 'warehouses_products_variants.warehouse_id' => $warehouse_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function addStockCount($data)
    {
        if ($this->db->insert('stock_counts', $data)) {
            if ($this->site->getReference('sc') == $data['reference_no']) {
                $this->site->updateReference('sc');
            }
            return true;
        }
        return false;
    }

    public function finalizeStockCount($id, $data, $products)
    {
        if ($this->db->update('stock_counts', $data, array('id' => $id))) {
            foreach ($products as $product) {
                $this->db->insert('stock_count_items', $product);
            }
            return true;
        }
        return false;
    }

    public function getStouckCountByID($id)
    {
        $q = $this->db->get_where("stock_counts", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStockCountItems($stock_count_id)
    {
        $q = $this->db->get_where("stock_count_items", array('stock_count_id' => $stock_count_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }

    public function getAdjustmentByCountID($count_id)
    {
        $q = $this->db->get_where('adjustments', array('count_id' => $count_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariantID($product_id, $name)
    {
        $q = $this->db->get_where("product_variants", array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            $variant = $q->row();
            return $variant->id;
        }
        return null;
    }

    public function getDataProducts($term, $limit = 5)
    {
        // $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname')
        //     ->where("type != 'combo' AND "
        //         . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
        //         concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");

        // $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
        //     ->where('' . $this->db->dbprefix('product_variants') . '.name', NULL)
        //     ->group_by('products.id')->limit($limit);

        //        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name, cost, price, {$this->db->dbprefix('categories')}.name as category, type, barcode_symbology as barcode, subcategory_id, {$this->db->dbprefix('brands')}.name as brand,{$this->db->dbprefix('products')}.brand as brand_code, {$this->db->dbprefix('units')}.name as unit, {$this->db->dbprefix('products')}.unit as unit_id ,{$this->db->dbprefix('products')}.category_id as category_id, sale_unit, purchase_unit", FALSE);
        //
        //        $this->db->join('brands','products.brand=brands.id','left');
        //        $this->db->join('categories','products.category_id=categories.id','left');
        //        $this->db->join('units','products.unit=units.id','left');
        $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        // $this->db->or_group_start();
        $this->db->where("company_id", 1);
        // $this->db->group_end();
        $this->db->group_by('code');
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

    public function getSupplierById($supplier1, $supplier2, $supplier3, $supplier4, $supplier5)
    {
        $suppliers = array($supplier1, $supplier2, $supplier3, $supplier4, $supplier5);
        $q = $this->db
            ->select('*')
            ->from('companies')
            ->where_in('id', $suppliers)
            ->where('group_name', 'supplier')
            ->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getConsignmentByID($id)
    {
        $this->db->where('id', $id);
        $q = $this->db->get('consignment');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getConsignmentItems($id)
    {
        $this->db->select('consignment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=consignment_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=consignment_items.option_id', 'left')
            ->group_by('consignment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('consignment_id', $id);
        $q = $this->db->get('consignment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCSGProductByPID($id)
    {
        $this->db->where('product_id', $id);
        $q = $this->db->get('consignment_products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addConsignment($data, $items)
    {
        if ($this->db->insert('consignment', $data)) {
            $consignment_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['consignment_id'] = $consignment_id;
                $this->db->insert('consignment_items', $item);
                $this->site->syncConsignmentQty($item['product_id'], $item['warehouse_id']);
            }
            if ($this->site->getReference('csg') == $data['reference_no']) {
                $this->site->updateReference('csg');
            }
            return true;
        }
        return false;
    }

    public function updateConsignment($id, $data, $items)
    {
        //        $this->reverseAdjustment($id);
        if (
            $this->db->update('consignment', $data, array('id' => $id)) &&
            $this->db->delete('consignment_items', array('consignment_id' => $id))
        ) {
            foreach ($items as $product) {
                $product['consignment_id'] = $id;
                $this->db->insert('consignment_items', $product);
                //                $this->syncAdjustment($product);
                $this->site->syncConsignmentQty($product['product_id'], $product['warehouse_id']);
            }
            return true;
        }
        return false;
    }

    //    public function syncConsignment($data=array()){
    //        if($data){
    //            $pCSG= $this->getCSGProcure($data['product_id'],$data['warehouse_id']);
    //            $sCSG= $this->getCSGSale($data['product_id'],$data['warehouse_id']);
    //            if($this->db->update('consignment_products',array('quantity'=>$pCSG-$sCSG),array('product_id'=>$data['product_id'], 'warehouse_id'=> $data['warehouse_id'], 'company_id'=>$this->session->userdata('company_id')))){
    //                return true;
    //            }
    //            return false;
    //        }
    //        return false;
    //
    //    }

    //    public function getCSGSale($pid, $wid){
    //        $this->db->select("COALESCE(SUM({$this->db->dbprefix('sale_items')}.quantity),0) as result")
    //                ->join('sales','sale_items.sale_id=sales.id','left')
    //                ->where("sale_items.product_id",$pid)
    //                ->where("sale_items.warehouse_id",$wid)->where("sales.company_id",$this->session->userdata('company_id'));
    //
    //        $q=$this->db->get('sale_items');
    //        if ($q->num_rows() > 0) {
    //            $data = $q->row();
    //            return $data->result;
    //        }
    //        return 0;
    //    }

    public function getCSGSuggestions($term, $supplier = null, $limit = 5)
    {
        if (!$this->Owner) {
            $this->db->where('products.company_id', $this->session->userdata('company_id'));
        }
        $this->db->select('' . $this->db->dbprefix('products') . '.*')
            ->join('consignment_products', 'products.id=consignment_products.product_id', 'left')
            ->where("type = 'consignment' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);

        if ($supplier) {
            $this->db->where('products.supplier1', $supplier);
        }
        $this->db->where("products.is_deleted", null)->where("consignment_products.is_deleted", null);
        $this->db->group_by("id");
        $q = $this->db->get('products');
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
            if ($this->site->getReference('cpay') == $data['reference_no']) {
                $this->site->updateReference('cpay');
            }
            $this->site->syncConsignmentPayments($data['consignment_id']);
            return true;
        }
        return false;
    }

    public function getAllProductsPaging($where = null, $cons = null, $warehouse_id = null, $limit = null, $offset = null, $sortby = null, $sorttype = null)
    {
        $sql = 'SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = "' . getenv('DB_DATABASE') . '" AND table_name = "sma_products"';
        $query = $this->db->query($sql);
        $bool = 0;
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {
                if ($sortby == $row->COLUMN_NAME) {
                    $bool = 1;
                }
            }
        }

        if ($cons == 'all') {
            $this->db->select($this->db->dbprefix('products') . ".id as id,
            {$this->db->dbprefix('products')}.code as code,
            {$this->db->dbprefix('products')}.name as name,
            {$this->db->dbprefix('products')}.unit as unit,
            {$this->db->dbprefix('products')}.cost as cost,
            {$this->db->dbprefix('products')}.price as price,
            {$this->db->dbprefix('products')}.alert_quantity as alert_quantity,
            {$this->db->dbprefix('products')}.image as image,
            {$this->db->dbprefix('products')}.category_id as category_id,
            {$this->db->dbprefix('products')}.company_id as company_id,
            {$this->db->dbprefix('products')}.subcategory_id as subcategory_id,
            {$this->db->dbprefix('products')}.cf1 as cf1,
            {$this->db->dbprefix('products')}.cf2 as cf2,
            {$this->db->dbprefix('products')}.cf3 as cf3,
            {$this->db->dbprefix('products')}.cf4 as cf4,
            {$this->db->dbprefix('products')}.cf5 as cf5,
            {$this->db->dbprefix('products')}.cf6 as cf6, 
            COALESCE(cons.qty, 0) + COALESCE(" . ($warehouse_id ? "wp" : "{{$this->db->dbprefix('products')}") . ".quantity,0) as quantity,
            " . ($warehouse_id ? "wp.rack" : "''") . " as rack,
            COALESCE(" . ($warehouse_id ? "wp.quantity_booking" : 0) . ", 0) as quantity_booking,
            {$this->db->dbprefix('products')}.tax_rate as tax_rate,
            {$this->db->dbprefix('products')}.track_quantity as track_quantity,
            {$this->db->dbprefix('products')}.details as details,
            {$this->db->dbprefix('products')}.warehouse as warehouse,
            {$this->db->dbprefix('products')}.barcode_symbology as barcode_symbology,
            {$this->db->dbprefix('products')}.file as file,
            {$this->db->dbprefix('products')}.product_details as product_details,
            {$this->db->dbprefix('products')}.tax_method as tax_method,
            {$this->db->dbprefix('products')}.type as type,
            {$this->db->dbprefix('products')}.supplier1 as supplier1,
            {$this->db->dbprefix('products')}.supplier1price as supplier1price,
            {$this->db->dbprefix('products')}.supplier2 as supplier2,
            {$this->db->dbprefix('products')}.supplier2price as supplier2price,
            {$this->db->dbprefix('products')}.supplier3 as supplier3,
            {$this->db->dbprefix('products')}.supplier3price as supplier3price,
            {$this->db->dbprefix('products')}.supplier4 as supplier4,
            {$this->db->dbprefix('products')}.supplier4price as supplier4price,
            {$this->db->dbprefix('products')}.supplier5 as supplier5,
            {$this->db->dbprefix('products')}.supplier5price as supplier5price,
            {$this->db->dbprefix('products')}.promotion as promotion,
            {$this->db->dbprefix('products')}.promo_price as promo_price,
            {$this->db->dbprefix('products')}.start_date as start_date,
            {$this->db->dbprefix('products')}.end_date as end_date,
            {$this->db->dbprefix('products')}.supplier1_part_no as supplier1_part_no,
            {$this->db->dbprefix('products')}.supplier2_part_no as supplier2_part_no,
            {$this->db->dbprefix('products')}.supplier3_part_no as supplier3_part_no,
            {$this->db->dbprefix('products')}.supplier4_part_no as supplier4_part_no,
            {$this->db->dbprefix('products')}.supplier5_part_no as supplier5_part_no,
            {$this->db->dbprefix('products')}.sale_unit as sale_unit,
            {$this->db->dbprefix('products')}.purchase_unit as purchase_unit,
            {$this->db->dbprefix('products')}.brand as brand,
            {$this->db->dbprefix('products')}.uuid as uuid,
            {$this->db->dbprefix('products')}.is_deleted as is_deleted,
            {$this->db->dbprefix('products')}.uuid_app as uuid_app,
            {$this->db->dbprefix('products')}.mtid as mtid,
            {$this->db->dbprefix('products')}.item_id as item_id,
            {$this->db->dbprefix('products')}.public as public,
            {$this->db->dbprefix('products')}.price_public as price_public,
            {$this->db->dbprefix('products')}.weight as weight,
            {$this->db->dbprefix('products')}.e_minqty as e_minqty,
            {$this->db->dbprefix('products')}.credit_price as credit_price,
            {$this->db->dbprefix('products')}.is_retail as is_retail,
            {$this->db->dbprefix('units')}.name as unit_name,
            {$this->db->dbprefix('brands')}.name as brand,
            {$this->db->dbprefix('categories')}.name as categori_name,
            {$this->db->dbprefix('units')}.code as unit_code");
        } elseif ($cons == 'yes') {
            $this->db->select($this->db->dbprefix('products') . ".id as id,
              {$this->db->dbprefix('products')}.code as code,
              {$this->db->dbprefix('products')}.name as name,
              {$this->db->dbprefix('products')}.unit as unit,
              {$this->db->dbprefix('products')}.cost as cost,
              {$this->db->dbprefix('products')}.price as price,
              {$this->db->dbprefix('products')}.alert_quantity as alert_quantity,
              {$this->db->dbprefix('products')}.image as image,
              {$this->db->dbprefix('products')}.category_id as category_id,
              {$this->db->dbprefix('products')}.company_id as company_id,
              {$this->db->dbprefix('products')}.subcategory_id as subcategory_id,
              {$this->db->dbprefix('products')}.cf1 as cf1,
              {$this->db->dbprefix('products')}.cf2 as cf2,
              {$this->db->dbprefix('products')}.cf3 as cf3,
              {$this->db->dbprefix('products')}.cf4 as cf4,
              {$this->db->dbprefix('products')}.cf5 as cf5,
              {$this->db->dbprefix('products')}.cf6 as cf6, 
              COALESCE(cons.qty, 0) as quantity,
              " . ($warehouse_id ? "wp.rack" : "''") . " as rack,
              COALESCE(" . ($warehouse_id ? "wp.quantity_booking" : 0) . ", 0) as quantity_booking,
              {$this->db->dbprefix('products')}.tax_rate as tax_rate,
              {$this->db->dbprefix('products')}.track_quantity as track_quantity,
              {$this->db->dbprefix('products')}.details as details,
              {$this->db->dbprefix('products')}.warehouse as warehouse,
              {$this->db->dbprefix('products')}.barcode_symbology as barcode_symbology,
              {$this->db->dbprefix('products')}.file as file,
              {$this->db->dbprefix('products')}.product_details as product_details,
              {$this->db->dbprefix('products')}.tax_method as tax_method,
              {$this->db->dbprefix('products')}.type as type,
              {$this->db->dbprefix('products')}.supplier1 as supplier1,
              {$this->db->dbprefix('products')}.supplier1price as supplier1price,
              {$this->db->dbprefix('products')}.supplier2 as supplier2,
              {$this->db->dbprefix('products')}.supplier2price as supplier2price,
              {$this->db->dbprefix('products')}.supplier3 as supplier3,
              {$this->db->dbprefix('products')}.supplier3price as supplier3price,
              {$this->db->dbprefix('products')}.supplier4 as supplier4,
              {$this->db->dbprefix('products')}.supplier4price as supplier4price,
              {$this->db->dbprefix('products')}.supplier5 as supplier5,
              {$this->db->dbprefix('products')}.supplier5price as supplier5price,
              {$this->db->dbprefix('products')}.promotion as promotion,
              {$this->db->dbprefix('products')}.promo_price as promo_price,
              {$this->db->dbprefix('products')}.start_date as start_date,
              {$this->db->dbprefix('products')}.end_date as end_date,
              {$this->db->dbprefix('products')}.supplier1_part_no as supplier1_part_no,
              {$this->db->dbprefix('products')}.supplier2_part_no as supplier2_part_no,
              {$this->db->dbprefix('products')}.supplier3_part_no as supplier3_part_no,
              {$this->db->dbprefix('products')}.supplier4_part_no as supplier4_part_no,
              {$this->db->dbprefix('products')}.supplier5_part_no as supplier5_part_no,
              {$this->db->dbprefix('products')}.sale_unit as sale_unit,
              {$this->db->dbprefix('products')}.purchase_unit as purchase_unit,
              {$this->db->dbprefix('products')}.brand as brand,
              {$this->db->dbprefix('products')}.uuid as uuid,
              {$this->db->dbprefix('products')}.is_deleted as is_deleted,
              {$this->db->dbprefix('products')}.uuid_app as uuid_app,
              {$this->db->dbprefix('products')}.mtid as mtid,
              {$this->db->dbprefix('products')}.item_id as item_id,
              {$this->db->dbprefix('products')}.public as public,
              {$this->db->dbprefix('products')}.price_public as price_public,
              {$this->db->dbprefix('products')}.weight as weight,
              {$this->db->dbprefix('products')}.e_minqty as e_minqty,
              {$this->db->dbprefix('products')}.credit_price as credit_price,
              {$this->db->dbprefix('products')}.is_retail as is_retail,
              {$this->db->dbprefix('units')}.name as unit_name,
              {$this->db->dbprefix('brands')}.name as brand,
              {$this->db->dbprefix('categories')}.name as categori_name,
              {$this->db->dbprefix('units')}.code as unit_code");
        } else {
            $this->db->select($this->db->dbprefix('products') . ".id as id,
            {$this->db->dbprefix('products')}.code as code,
            {$this->db->dbprefix('products')}.name as name,
            {$this->db->dbprefix('products')}.unit as unit,
            {$this->db->dbprefix('products')}.cost as cost,
            {$this->db->dbprefix('products')}.price as price,
            {$this->db->dbprefix('products')}.alert_quantity as alert_quantity,
            {$this->db->dbprefix('products')}.thumb_image as image,
            {$this->db->dbprefix('products')}.category_id as category_id,
            {$this->db->dbprefix('products')}.company_id as company_id,
            {$this->db->dbprefix('products')}.subcategory_id as subcategory_id,
            {$this->db->dbprefix('products')}.cf1 as cf1,
            {$this->db->dbprefix('products')}.cf2 as cf2,
            {$this->db->dbprefix('products')}.cf3 as cf3,
            {$this->db->dbprefix('products')}.cf4 as cf4,
            {$this->db->dbprefix('products')}.cf5 as cf5,
            {$this->db->dbprefix('products')}.cf6 as cf6, 
            COALESCE(" . ($warehouse_id ? "wp" : "{$this->db->dbprefix('products')}") . ".quantity, 0) as quantity,
            " . ($warehouse_id ? "wp.rack" : "''") . " as rack,
            COALESCE(" . ($warehouse_id ? "wp" : "{$this->db->dbprefix('products')}") . ".quantity_booking, 0) as quantity_booking,
            {$this->db->dbprefix('products')}.tax_rate as tax_rate,
            {$this->db->dbprefix('products')}.track_quantity as track_quantity,
            {$this->db->dbprefix('products')}.details as details,
            {$this->db->dbprefix('products')}.warehouse as warehouse,
            {$this->db->dbprefix('products')}.barcode_symbology as barcode_symbology,
            {$this->db->dbprefix('products')}.file as file,
            {$this->db->dbprefix('products')}.product_details as product_details,
            {$this->db->dbprefix('products')}.tax_method as tax_method,
            {$this->db->dbprefix('products')}.type as type,
            {$this->db->dbprefix('products')}.supplier1 as supplier1,
            {$this->db->dbprefix('products')}.supplier1price as supplier1price,
            {$this->db->dbprefix('products')}.supplier2 as supplier2,
            {$this->db->dbprefix('products')}.supplier2price as supplier2price,
            {$this->db->dbprefix('products')}.supplier3 as supplier3,
            {$this->db->dbprefix('products')}.supplier3price as supplier3price,
            {$this->db->dbprefix('products')}.supplier4 as supplier4,
            {$this->db->dbprefix('products')}.supplier4price as supplier4price,
            {$this->db->dbprefix('products')}.supplier5 as supplier5,
            {$this->db->dbprefix('products')}.supplier5price as supplier5price,
            {$this->db->dbprefix('products')}.promotion as promotion,
            {$this->db->dbprefix('products')}.promo_price as promo_price,
            {$this->db->dbprefix('products')}.start_date as start_date,
            {$this->db->dbprefix('products')}.end_date as end_date,
            {$this->db->dbprefix('products')}.supplier1_part_no as supplier1_part_no,
            {$this->db->dbprefix('products')}.supplier2_part_no as supplier2_part_no,
            {$this->db->dbprefix('products')}.supplier3_part_no as supplier3_part_no,
            {$this->db->dbprefix('products')}.supplier4_part_no as supplier4_part_no,
            {$this->db->dbprefix('products')}.supplier5_part_no as supplier5_part_no,
            {$this->db->dbprefix('products')}.sale_unit as sale_unit,
            {$this->db->dbprefix('products')}.purchase_unit as purchase_unit,
            {$this->db->dbprefix('products')}.brand as brand,
            {$this->db->dbprefix('products')}.uuid as uuid,
            {$this->db->dbprefix('products')}.is_deleted as is_deleted,
            {$this->db->dbprefix('products')}.uuid_app as uuid_app,
            {$this->db->dbprefix('products')}.mtid as mtid,
            {$this->db->dbprefix('products')}.item_id as item_id,
            {$this->db->dbprefix('products')}.public as public,
            {$this->db->dbprefix('products')}.price_public as price_public,
            {$this->db->dbprefix('products')}.weight as weight,
            {$this->db->dbprefix('products')}.e_minqty as e_minqty,
            {$this->db->dbprefix('products')}.credit_price as credit_price,
            {$this->db->dbprefix('products')}.is_retail as is_retail,
            {$this->db->dbprefix('units')}.name as unit_name,
            {$this->db->dbprefix('brands')}.name as brand,
            {$this->db->dbprefix('categories')}.name as categori_name,
            {$this->db->dbprefix('units')}.code as unit_code");
        }

        if ($warehouse_id) {
            $table = "(SELECT product_id as pid, product_name as pname, quantity as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} WHERE warehouse_id=" . $warehouse_id . ") as cons";
            if ($this->Settings->display_all_products) {
                $this->db->join("( SELECT product_id, quantity, quantity_booking, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) AS  wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->db->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id);
            }
            $this->db->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.is_deleted', null);
        } else {
            $table = "(SELECT product_id as pid, product_name as pname, sum(quantity) as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} GROUP BY product_id, company_id) as cons";
            $this->db->join('categories', 'products.category_id = categories.id', 'left')
                ->join('units', 'products.unit = units.id', 'left')
                ->join('brands', 'products.brand = brands.id', 'left')
                ->where('products.is_deleted', null);
            $this->db->group_by("products.id");
        }
        if ($where) {
            $this->db->where($where);
        }
        if ($cons == 'yes') {
            $this->db->join($table, 'products.id=cons.pid', 'right')
                ->where('cons.is_deleted', null);
        } elseif ($cons == 'no') {
            $this->db->where("products.type != 'consignment'");
        } else {
            $this->db->join($table, 'products.id=cons.pid', 'left')
                ->where('cons.is_deleted', null);
        }

        if ($bool == 1 && $sorttype) {
            $this->db->order_by('products.' . $sortby, $sorttype);
        } else {
            $this->db->order_by('products.name', 'asc');
        }
        if ($limit != null || $offset != null) {
            $this->db->limit($limit, $offset);
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductsAll($where = null, $cons = null, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $table = "(SELECT product_id as pid, product_name as pname, quantity as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} WHERE warehouse_id=" . $warehouse_id . ") as cons";
            if ($this->Settings->display_all_products) {
                $this->db->join("( SELECT product_id, quantity, quantity_booking, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) AS  wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->db->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id);
            }
            $this->db->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.is_deleted', null);
        } else {
            $table = "(SELECT product_id as pid, product_name as pname, sum(quantity) as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} GROUP BY product_id, company_id) as cons";
            $this->db->join('categories', 'products.category_id = categories.id', 'left')
                ->join('units', 'products.unit = units.id', 'left')
                ->join('brands', 'products.brand = brands.id', 'left')
                ->where('products.is_deleted', null);
            $this->db->group_by("products.id");
        }
        if ($where) {
            $this->db->where($where);
        }
        if ($cons == 'yes') {
            $this->db->join($table, 'products.id=cons.pid', 'right')
                ->where('cons.is_deleted', null);
        } elseif ($cons == 'no') {
            $this->db->where("products.type != 'consignment'");
        } else {
            $this->db->join($table, 'products.id=cons.pid', 'left')
                ->where('cons.is_deleted', null);
        }
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function getAllProduk($where = null, $cons = null, $warehouse_id = null)
    {
        if ($cons == 'all') {
            $this->db->select($this->db->dbprefix('products') . ".id as id,
            {$this->db->dbprefix('products')}.code as code,
            {$this->db->dbprefix('products')}.name as name,
            {$this->db->dbprefix('products')}.unit as unit,
            {$this->db->dbprefix('products')}.cost as cost,
            {$this->db->dbprefix('products')}.price as price,
            {$this->db->dbprefix('products')}.alert_quantity as alert_quantity,
            {$this->db->dbprefix('products')}.image as image,
            {$this->db->dbprefix('products')}.category_id as category_id,
            {$this->db->dbprefix('products')}.company_id as company_id,
            {$this->db->dbprefix('products')}.subcategory_id as subcategory_id,
            {$this->db->dbprefix('products')}.cf1 as cf1,
            {$this->db->dbprefix('products')}.cf2 as cf2,
            {$this->db->dbprefix('products')}.cf3 as cf3,
            {$this->db->dbprefix('products')}.cf4 as cf4,
            {$this->db->dbprefix('products')}.cf5 as cf5,
            {$this->db->dbprefix('products')}.cf6 as cf6, 
            COALESCE(cons.qty, 0) + COALESCE(" . ($warehouse_id ? "wp" : "{{$this->db->dbprefix('products')}") . ".quantity,0) as quantity,
            " . ($warehouse_id ? "wp.rack" : "''") . " as rack,
            COALESCE(" . ($warehouse_id ? "wp.quantity_booking" : 0) . ", 0) as quantity_booking,
            {$this->db->dbprefix('products')}.tax_rate as tax_rate,
            {$this->db->dbprefix('products')}.track_quantity as track_quantity,
            {$this->db->dbprefix('products')}.details as details,
            {$this->db->dbprefix('products')}.warehouse as warehouse,
            {$this->db->dbprefix('products')}.barcode_symbology as barcode_symbology,
            {$this->db->dbprefix('products')}.file as file,
            {$this->db->dbprefix('products')}.product_details as product_details,
            {$this->db->dbprefix('products')}.tax_method as tax_method,
            {$this->db->dbprefix('products')}.type as type,
            {$this->db->dbprefix('products')}.supplier1 as supplier1,
            {$this->db->dbprefix('products')}.supplier1price as supplier1price,
            {$this->db->dbprefix('products')}.supplier2 as supplier2,
            {$this->db->dbprefix('products')}.supplier2price as supplier2price,
            {$this->db->dbprefix('products')}.supplier3 as supplier3,
            {$this->db->dbprefix('products')}.supplier3price as supplier3price,
            {$this->db->dbprefix('products')}.supplier4 as supplier4,
            {$this->db->dbprefix('products')}.supplier4price as supplier4price,
            {$this->db->dbprefix('products')}.supplier5 as supplier5,
            {$this->db->dbprefix('products')}.supplier5price as supplier5price,
            {$this->db->dbprefix('products')}.promotion as promotion,
            {$this->db->dbprefix('products')}.promo_price as promo_price,
            {$this->db->dbprefix('products')}.start_date as start_date,
            {$this->db->dbprefix('products')}.end_date as end_date,
            {$this->db->dbprefix('products')}.supplier1_part_no as supplier1_part_no,
            {$this->db->dbprefix('products')}.supplier2_part_no as supplier2_part_no,
            {$this->db->dbprefix('products')}.supplier3_part_no as supplier3_part_no,
            {$this->db->dbprefix('products')}.supplier4_part_no as supplier4_part_no,
            {$this->db->dbprefix('products')}.supplier5_part_no as supplier5_part_no,
            {$this->db->dbprefix('products')}.sale_unit as sale_unit,
            {$this->db->dbprefix('products')}.purchase_unit as purchase_unit,
            {$this->db->dbprefix('products')}.brand as brand,
            {$this->db->dbprefix('products')}.uuid as uuid,
            {$this->db->dbprefix('products')}.is_deleted as is_deleted,
            {$this->db->dbprefix('products')}.uuid_app as uuid_app,
            {$this->db->dbprefix('products')}.mtid as mtid,
            {$this->db->dbprefix('products')}.item_id as item_id,
            {$this->db->dbprefix('products')}.public as public,
            {$this->db->dbprefix('products')}.price_public as price_public,
            {$this->db->dbprefix('products')}.weight as weight,
            {$this->db->dbprefix('products')}.e_minqty as e_minqty,
            {$this->db->dbprefix('products')}.credit_price as credit_price,
            {$this->db->dbprefix('products')}.is_retail as is_retail,
            {$this->db->dbprefix('units')}.name as unit_name,
            {$this->db->dbprefix('brands')}.name as brand,
            {$this->db->dbprefix('categories')}.name as categori_name,
            {$this->db->dbprefix('units')}.code as unit_code");
        } elseif ($cons == 'yes') {
            $this->db->select($this->db->dbprefix('products') . ".id as id,
              {$this->db->dbprefix('products')}.code as code,
              {$this->db->dbprefix('products')}.name as name,
              {$this->db->dbprefix('products')}.unit as unit,
              {$this->db->dbprefix('products')}.cost as cost,
              {$this->db->dbprefix('products')}.price as price,
              {$this->db->dbprefix('products')}.alert_quantity as alert_quantity,
              {$this->db->dbprefix('products')}.image as image,
              {$this->db->dbprefix('products')}.category_id as category_id,
              {$this->db->dbprefix('products')}.company_id as company_id,
              {$this->db->dbprefix('products')}.subcategory_id as subcategory_id,
              {$this->db->dbprefix('products')}.cf1 as cf1,
              {$this->db->dbprefix('products')}.cf2 as cf2,
              {$this->db->dbprefix('products')}.cf3 as cf3,
              {$this->db->dbprefix('products')}.cf4 as cf4,
              {$this->db->dbprefix('products')}.cf5 as cf5,
              {$this->db->dbprefix('products')}.cf6 as cf6, 
              COALESCE(cons.qty, 0) as quantity,
              " . ($warehouse_id ? "wp.rack" : "''") . " as rack,
              COALESCE(" . ($warehouse_id ? "wp.quantity_booking" : 0) . ", 0) as quantity_booking,
              {$this->db->dbprefix('products')}.tax_rate as tax_rate,
              {$this->db->dbprefix('products')}.track_quantity as track_quantity,
              {$this->db->dbprefix('products')}.details as details,
              {$this->db->dbprefix('products')}.warehouse as warehouse,
              {$this->db->dbprefix('products')}.barcode_symbology as barcode_symbology,
              {$this->db->dbprefix('products')}.file as file,
              {$this->db->dbprefix('products')}.product_details as product_details,
              {$this->db->dbprefix('products')}.tax_method as tax_method,
              {$this->db->dbprefix('products')}.type as type,
              {$this->db->dbprefix('products')}.supplier1 as supplier1,
              {$this->db->dbprefix('products')}.supplier1price as supplier1price,
              {$this->db->dbprefix('products')}.supplier2 as supplier2,
              {$this->db->dbprefix('products')}.supplier2price as supplier2price,
              {$this->db->dbprefix('products')}.supplier3 as supplier3,
              {$this->db->dbprefix('products')}.supplier3price as supplier3price,
              {$this->db->dbprefix('products')}.supplier4 as supplier4,
              {$this->db->dbprefix('products')}.supplier4price as supplier4price,
              {$this->db->dbprefix('products')}.supplier5 as supplier5,
              {$this->db->dbprefix('products')}.supplier5price as supplier5price,
              {$this->db->dbprefix('products')}.promotion as promotion,
              {$this->db->dbprefix('products')}.promo_price as promo_price,
              {$this->db->dbprefix('products')}.start_date as start_date,
              {$this->db->dbprefix('products')}.end_date as end_date,
              {$this->db->dbprefix('products')}.supplier1_part_no as supplier1_part_no,
              {$this->db->dbprefix('products')}.supplier2_part_no as supplier2_part_no,
              {$this->db->dbprefix('products')}.supplier3_part_no as supplier3_part_no,
              {$this->db->dbprefix('products')}.supplier4_part_no as supplier4_part_no,
              {$this->db->dbprefix('products')}.supplier5_part_no as supplier5_part_no,
              {$this->db->dbprefix('products')}.sale_unit as sale_unit,
              {$this->db->dbprefix('products')}.purchase_unit as purchase_unit,
              {$this->db->dbprefix('products')}.brand as brand,
              {$this->db->dbprefix('products')}.uuid as uuid,
              {$this->db->dbprefix('products')}.is_deleted as is_deleted,
              {$this->db->dbprefix('products')}.uuid_app as uuid_app,
              {$this->db->dbprefix('products')}.mtid as mtid,
              {$this->db->dbprefix('products')}.item_id as item_id,
              {$this->db->dbprefix('products')}.public as public,
              {$this->db->dbprefix('products')}.price_public as price_public,
              {$this->db->dbprefix('products')}.weight as weight,
              {$this->db->dbprefix('products')}.e_minqty as e_minqty,
              {$this->db->dbprefix('products')}.credit_price as credit_price,
              {$this->db->dbprefix('products')}.is_retail as is_retail,
              {$this->db->dbprefix('units')}.name as unit_name,
              {$this->db->dbprefix('brands')}.name as brand,
              {$this->db->dbprefix('categories')}.name as categori_name,
              {$this->db->dbprefix('units')}.code as unit_code");
        } else {
            $this->db->select($this->db->dbprefix('products') . ".id as id,
            {$this->db->dbprefix('products')}.code as code,
            {$this->db->dbprefix('products')}.name as name,
            {$this->db->dbprefix('products')}.unit as unit,
            {$this->db->dbprefix('products')}.cost as cost,
            {$this->db->dbprefix('products')}.price as price,
            {$this->db->dbprefix('products')}.alert_quantity as alert_quantity,
            {$this->db->dbprefix('products')}.thumb_image as image,
            {$this->db->dbprefix('products')}.category_id as category_id,
            {$this->db->dbprefix('products')}.company_id as company_id,
            {$this->db->dbprefix('products')}.subcategory_id as subcategory_id,
            {$this->db->dbprefix('products')}.cf1 as cf1,
            {$this->db->dbprefix('products')}.cf2 as cf2,
            {$this->db->dbprefix('products')}.cf3 as cf3,
            {$this->db->dbprefix('products')}.cf4 as cf4,
            {$this->db->dbprefix('products')}.cf5 as cf5,
            {$this->db->dbprefix('products')}.cf6 as cf6, 
            COALESCE(" . ($warehouse_id ? "wp" : "{$this->db->dbprefix('products')}") . ".quantity, 0) as quantity,
            " . ($warehouse_id ? "wp.rack" : "''") . " as rack,
            COALESCE(" . ($warehouse_id ? "wp" : "{$this->db->dbprefix('products')}") . ".quantity_booking, 0) as quantity_booking,
            {$this->db->dbprefix('products')}.tax_rate as tax_rate,
            {$this->db->dbprefix('products')}.track_quantity as track_quantity,
            {$this->db->dbprefix('products')}.details as details,
            {$this->db->dbprefix('products')}.warehouse as warehouse,
            {$this->db->dbprefix('products')}.barcode_symbology as barcode_symbology,
            {$this->db->dbprefix('products')}.file as file,
            {$this->db->dbprefix('products')}.product_details as product_details,
            {$this->db->dbprefix('products')}.tax_method as tax_method,
            {$this->db->dbprefix('products')}.type as type,
            {$this->db->dbprefix('products')}.supplier1 as supplier1,
            {$this->db->dbprefix('products')}.supplier1price as supplier1price,
            {$this->db->dbprefix('products')}.supplier2 as supplier2,
            {$this->db->dbprefix('products')}.supplier2price as supplier2price,
            {$this->db->dbprefix('products')}.supplier3 as supplier3,
            {$this->db->dbprefix('products')}.supplier3price as supplier3price,
            {$this->db->dbprefix('products')}.supplier4 as supplier4,
            {$this->db->dbprefix('products')}.supplier4price as supplier4price,
            {$this->db->dbprefix('products')}.supplier5 as supplier5,
            {$this->db->dbprefix('products')}.supplier5price as supplier5price,
            {$this->db->dbprefix('products')}.promotion as promotion,
            {$this->db->dbprefix('products')}.promo_price as promo_price,
            {$this->db->dbprefix('products')}.start_date as start_date,
            {$this->db->dbprefix('products')}.end_date as end_date,
            {$this->db->dbprefix('products')}.supplier1_part_no as supplier1_part_no,
            {$this->db->dbprefix('products')}.supplier2_part_no as supplier2_part_no,
            {$this->db->dbprefix('products')}.supplier3_part_no as supplier3_part_no,
            {$this->db->dbprefix('products')}.supplier4_part_no as supplier4_part_no,
            {$this->db->dbprefix('products')}.supplier5_part_no as supplier5_part_no,
            {$this->db->dbprefix('products')}.sale_unit as sale_unit,
            {$this->db->dbprefix('products')}.purchase_unit as purchase_unit,
            {$this->db->dbprefix('products')}.brand as brand,
            {$this->db->dbprefix('products')}.uuid as uuid,
            {$this->db->dbprefix('products')}.is_deleted as is_deleted,
            {$this->db->dbprefix('products')}.uuid_app as uuid_app,
            {$this->db->dbprefix('products')}.mtid as mtid,
            {$this->db->dbprefix('products')}.item_id as item_id,
            {$this->db->dbprefix('products')}.public as public,
            {$this->db->dbprefix('products')}.price_public as price_public,
            {$this->db->dbprefix('products')}.weight as weight,
            {$this->db->dbprefix('products')}.e_minqty as e_minqty,
            {$this->db->dbprefix('products')}.credit_price as credit_price,
            {$this->db->dbprefix('products')}.is_retail as is_retail,
            {$this->db->dbprefix('units')}.name as unit_name,
            {$this->db->dbprefix('brands')}.name as brand,
            {$this->db->dbprefix('categories')}.name as categori_name,
            {$this->db->dbprefix('units')}.code as unit_code");
        }

        if ($warehouse_id) {
            $table = "(SELECT product_id as pid, product_name as pname, quantity as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} WHERE warehouse_id=" . $warehouse_id . ") as cons";
            if ($this->Settings->display_all_products) {
                $this->db->join("( SELECT product_id, quantity, quantity_booking, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) AS  wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->db->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id);
            }
            $this->db->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.is_deleted', null);
        } else {
            $table = "(SELECT product_id as pid, product_name as pname, sum(quantity) as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} GROUP BY product_id, company_id) as cons";
            $this->db->join('categories', 'products.category_id = categories.id', 'left')
                ->join('units', 'products.unit = units.id', 'left')
                ->join('brands', 'products.brand = brands.id', 'left')
                ->where('products.is_deleted', null);
            $this->db->group_by("products.id");
        }
        if ($where) {
            $this->db->where($where);
        }
        if ($cons == 'yes') {
            $this->db->join($table, 'products.id=cons.pid', 'right')
                ->where('cons.is_deleted', null);
        } elseif ($cons == 'no') {
            $this->db->where("products.type != 'consignment'");
        } else {
            $this->db->join($table, 'products.id=cons.pid', 'left')
                ->where('cons.is_deleted', null);
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}

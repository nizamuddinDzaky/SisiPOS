<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/models/Purchases_model.php';

class At_purchase_model extends Purchases_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPurchaseAT($data, $items)
    {
        //melakukan pengecekan apakah sudah ada purchase dengan noref sale yang sama atau belum
        if ($this->getPurchasesBySaleRefNo($data['cf1'], $data['supplier_id'])) {
            throw new \Exception("Tidak dapat membuat PO. No Ref SO telah digunakan.");
        };

        $data['created_at'] = date('Y-m-d H:i:s');
        if (!$this->db->insert('purchases', $data)) {
            throw new \Exception($this->db->error()['message']);
        }

        $purchase_id = $this->db->insert_id();

        if ($this->site->getReference('po', $data['company_head_id']) == $data['reference_no']) {
            $this->site->updateReference('po', $data['company_head_id']);
        }

        foreach ($items as $item) {
            $item['purchase_id'] = $purchase_id;
            if (!$this->db->insert('purchase_items', $item)) {
                throw new \Exception($this->db->error()['message']);
            }

            if ($this->Settings->update_cost) {
                $this->db->update('products', array('cost' => $item['real_unit_cost']), array('id' => $item['product_id'], 'company_id' => $data['company_head_id']));
            }
            if ($item['option_id']) {
                $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
            }
        }

        return $purchase_id;
    }

    public function findPurchase($id, $user_id)
    {
        $q = $this->db->get_where('purchases', [
            'id' => $id,
            'created_by' => $user_id
        ], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function findPurchaseByPurchaseId($purchase_id)
    {
        $q = $this->db->get_where('purchases', [
            'id' => $purchase_id
        ]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    // public function getOrders($user_id) {
    //     $this->db->join('sma_sales','sma_sales.reference_no=sma_purchases.cf1 and sma_sales.biller_id=sma_purchases.supplier_id','inner');
    //     $this->db->select('sma_purchases.*');
    //     $this->db->where([
    //         'purchases.created_by' => $user_id
    //     ]);
    //     $this->db->order_by('id', 'desc');
    //     $q = $this->db->get('purchases');
    //     if ($q->num_rows() > 0) {
    //         return $q->result();
    //     }
    //     return [];
    // }

    public function getOrdersOnGoing($user_id, $limit, $start, $search)
    {
        $this->db->join('sma_sales', 'sma_sales.reference_no=sma_purchases.cf1 and sma_sales.biller_id=sma_purchases.supplier_id', 'inner');
        $this->db->select('sma_purchases.*, sma_sales.is_updated_price  ');
        $this->db->where('sma_purchases.created_by=' . $user_id . ' AND !((sma_purchases.status = \'delivered\' OR sma_purchases.status =  \'received\') AND sma_purchases.payment_status = \'paid\' OR sma_purchases.status = \'canceled\')');
        $this->db->where('sma_purchases.is_deleted', null);
        $this->db->order_by('id', 'desc');
        $this->db->limit($limit, $start);
        if ($search) {
            $this->db->like('sma_sales.reference_no', $search);
            // $this->db->or_like('sma_products.code', $search);
        }
        $q = $this->db->get('sma_purchases');
        if ($q->num_rows() > 0) {
            $purchases = $q->result();
            foreach ($purchases as $i => $purchase) {
                $q = $this->db->get_where('purchase_items', [
                    'purchase_id' => $purchase->id
                ]);
                $purchase->items = $q->result();
            }

            return $purchases;
        }
        return [];
    }

    public function getRowsOrdersOnGoing($user_id, $search)
    {
        $this->db->select('count(sma_purchases.id) as count');
        if ($search) {
            $this->db->like('sma_sales.reference_no', $search);
        }
        $this->db->where('sma_purchases.created_by=' . $user_id . ' AND !((sma_purchases.status = \'delivered\' OR sma_purchases.status =  \'received\' ) AND sma_purchases.payment_status = \'paid\' OR sma_purchases.status =  \'canceled\') ');
        $this->db->where('sma_purchases.is_deleted', null);
        $this->db->order_by('sma_purchases.id', 'desc');
        $this->db->join('sma_sales', 'sma_sales.reference_no=sma_purchases.cf1 and sma_sales.biller_id=sma_purchases.supplier_id', 'inner');
        $q = $this->db->get('purchases');
        return $q->row()->count;
    }

    public function getOrdersComplete($user_id, $limit, $start, $search)
    {
        $this->db->select('sma_purchases.*');
        $this->db->where('sma_purchases.created_by=' . $user_id . ' AND ((sma_purchases.status = \'delivered\' OR sma_purchases.status =  \'received\') AND sma_purchases.payment_status = \'paid\' OR sma_purchases.status =  \'canceled\')');
        $this->db->where('sma_purchases.is_deleted', null);
        $this->db->limit($limit, $start);
        $this->db->order_by('id', 'desc');
        $this->db->join('sma_sales', 'sma_sales.reference_no=sma_purchases.cf1 and sma_sales.biller_id=sma_purchases.supplier_id', 'inner');
        if ($search) {
            $this->db->like('sma_sales.reference_no', $search);
        }
        $q = $this->db->get('sma_purchases');
        // var_dump($search);die;


        // var_dump($this->db->error());die;

        if ($q->num_rows() > 0) {
            $purchases = $q->result();
            foreach ($purchases as $i => $purchase) {
                $q = $this->db->get_where('purchase_items', [
                    'purchase_id' => $purchase->id
                ]);
                $purchase->items = $q->result();
            }

            return $purchases;
        }
        return [];
    }

    public function getRowsOrdersComplete($user_id, $search)
    {
        $this->db->join('sma_sales', 'sma_sales.reference_no=sma_purchases.cf1 and sma_sales.biller_id=sma_purchases.supplier_id', 'inner');
        $this->db->select('count(sma_purchases.id) as count');
        $this->db->where('sma_purchases.created_by=' . $user_id . ' AND ((sma_purchases.status = \'delivered\' OR sma_purchases.status =  \'received\') AND sma_purchases.payment_status = \'paid\' OR sma_purchases.status =  \'canceled\')');
        $this->db->where('sma_purchases.is_deleted', null);
        $this->db->order_by('sma_purchases.id', 'desc');
        if ($search) {
            $this->db->like('sma_sales.reference_no', $search);
        }
        $q = $this->db->get('purchases');
        return $q->row()->count;
    }

    public function getOrderItems($purchase_id, $user_id)
    {
        $q = $this->db->get_where('purchases', [
            'id' => $purchase_id,
            'created_by' => $user_id,
            'is_deleted' => null
        ], 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            $item = $this->db->get_where('purchase_items', [
                'purchase_id' => $purchase_id,
            ]);
            $row->items = $item->result();
            return $row;
        }
        return false;
    }

    // public function getSumReceiveItemByPurchaseId($purchase_id){
    //     $this->db->select('SUM(quantity_received) as total_received');
    //     $this->db->where('purchase_id', $purchase_id);
    //     $q = $this->db->get('purchase_items');
    //     if ($q->num_rows() > 0) {
    //         return $q->row();
    //     }
    // }

    // public function getSumOrderItemByPurchaseId($purchase_id){
    //     $this->db->select('SUM(quantity) as total_received');
    //     $this->db->where('purchase_id', $purchase_id);
    //     $q = $this->db->get('purchase_items');
    //     if ($q->num_rows() > 0) {
    //         return $q->row();
    //     }
    // }

    public function findPurchaseItemsByPurchaseIdAndProductCode($purchase_id, $product_code)
    {
        $q = $this->db->get_where('purchase_items', [
            'purchase_id' => $purchase_id,
            'product_code' => $product_code
        ]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updatePurchaseById($purchase_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $this->session->userdata('user_id');
        return $this->db->update('purchases', $data, [
            'id' => $purchase_id,
        ]);
    }

    public function updatePurchaseItemsById($id, $data)
    {
        $this->db->update('purchase_items', $data, [
            'id' => $id,
        ]);
    }
    public function getPurchaseByID($id, $company_id = null)
    {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function confirmReceivedBooking($data, $user_id, $sale_id = null)
    {
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('integration_model', 'integration');

        $purchase = $this->getPurchaseByID($data['purchase_id']);
        $items = $this->site->getAllPurchaseItems($data['purchase_id']);
        $status = 'received';
        $is_parsial = false;
        $uploadedImg = null;
        // var_dump(is_string($data['file']));die;
        if ($data['file']) {
            if (!is_string($data['file']) && $data['file']['error'] == 0) {
                $check = getimagesize($data['file']["tmp_name"]);

                if (!$check) {
                    throw new \Exception("File tidak valid");
                }
                if ($data['file']["size"] > 16000000) { //15mb
                    throw new \Exception("Ukuran File terlalu besar");
                }
                $uploadedImg = $this->integration->upload_files($data['file']);
            } else if (is_string($data['file'])) {
                $uploadedImg = $this->integration->upload_files($data['file'], 'base64');
            }
        }
        // var_dump($uploadedImg);die;

        $data_purchase = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($purchase->payment_deadline == null && ($purchase->payment_method == 'kredit' || $purchase->payment_method == 'cash on delivery' || $purchase->payment_method == 'kredit_pro' || $purchase->payment_method == 'kredit_mandiri')) {

            if ($purchase->payment_method == 'kredit' || $purchase->payment_method == 'kredit_pro' || $purchase->payment_method == 'kredit_mandiri') {
                $deadline = $purchase->payment_duration;
                $cur = date('Y-m-d');
                $deadline = date('Y-m-d', strtotime('+' . $deadline . ' days', strtotime($cur)));
            } else {
                $deadline = 0;
                $cur = date('Y-m-d');
                $deadline = date('Y-m-d', strtotime('+' . $deadline . ' days', strtotime($cur)));
                $data_purchase['payment_duration'] = 0;
            }

            $data_purchase['payment_deadline'] = $deadline;

            $this->db->update('sales', ['payment_term' => $data_purchase['payment_duration'], 'due_date' => $data_purchase['payment_deadline']], ['id' => $sale_id]);
        }

        if ($this->db->update('purchases', $data_purchase, ['id' => $data['purchase_id']])) {

            $this->db->update('deliveries', [
                'status' => 'delivered',
                'receive_status' => $status,
                'note' => $data['note'],
                'spj_file' => $uploadedImg != null ? $uploadedImg->url : '',
                'updated_at' => date('Y-m-d H:i:s'),
                'delivered_date' => $data['date'] ?? date('Y-m-d H:i:s'),
            ], ['id' => $data['do_id']]);

            $arrayItemPurchase = [];

            foreach ($data['product_code'] as $i => $pc) {
                $item = $this->findPurchaseItemsByPurchaseIdAndProductCode($data['purchase_id'], $pc);

                $this->db->update('delivery_items', [
                    'good_quantity' => $data['good'][$i],
                    'bad_quantity' => $data['bad'][$i],
                    'updated_at' => date('Y-m-d H:i:s'),
                ], ['id' => $data['delivery_item_id'][$i]]);

                $dataStory = [
                    "purchase_item_id" => $item->id,
                    "purchase_id" => $purchase->id,
                    "reference_no" => $purchase->reference_no,
                    "product_id" => $item->product_id,
                    "product_code" => $item->product_code,
                    "product_name" => $item->product_name,
                    "quantity" => $item->quantity,
                    "quantity_received" => $data['quantity_received'][$i],
                    "delivery_item_id" => $data['delivery_item_id'][$i],
                    "delivery_id" => $data['do_id'],
                    "sino_do" => $data['do_ref'],
                    "created_by" => $user_id,
                ];
                $this->insertStoryReceived($dataStory);
                $this->updateAVCO([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity' => $item->quantity,
                    'cost' => $item->real_unit_cost
                ], $purchase->company_head_id);


                $arrayItemPurchase[$pc]['good'] = 0;
                $arrayItemPurchase[$pc]['bad'] = 0;
                $arrayItemPurchase[$pc]['sent'] = 0;

                $newData = $this->db->query("SELECT 
                                                sdi.`quantity_ordered` AS 'total_quantity', 
                                                SUM(IF(sd.status = 'delivered', sdi.`good_quantity`, 0)) AS 'good_quantity', 
                                                SUM(IF(sd.status = 'delivered', sdi.`bad_quantity`, IF(sd.status = 'returned', (sdi.`bad_quantity`*-1), 0))) AS 'bad_quantity'
                                            FROM `sma_delivery_items` sdi 
                                            INNER JOIN `sma_deliveries` sd 
                                                ON sd.`id` = sdi.`delivery_id` 
                                                WHERE sdi.`sale_id` = '" . $sale_id . "' AND sdi.`product_code` = '" . $pc . "'
                                                GROUP BY sdi.`product_code`
                                            ")->row();
                // AND (sd.receive_status = 'received' OR ((sd.receive_status IS NULL OR sd.receive_status = '')) AND sd.status = 'returned')
                $arrayItemPurchase[$pc]['good'] = $newData->good_quantity;
                $arrayItemPurchase[$pc]['bad'] = $newData->bad_quantity;
                $arrayItemPurchase[$pc]['sent'] = ($newData->bad_quantity + $newData->good_quantity);
            }

            

            foreach ($arrayItemPurchase as $productCode => $itemPurchase) {
                $item = $this->findPurchaseItemsByPurchaseIdAndProductCode($data['purchase_id'], $productCode);
                /*if (!$is_parsial) {
                    $is_parsial = true;
                }*/
                $status = $itemPurchase['sent'] >= (int)$item->quantity ? 'received' : "partial";

                $this->updatePurchaseItemsById($item->id, [
                    'status' => $status,
                    'quantity_balance' => $itemPurchase['sent'],
                    'quantity_received' => $itemPurchase['sent'],
                    'good_quantity' => $itemPurchase['good'],
                    'bad_quantity' => $itemPurchase['bad'],
                ]);
            }

            $this->site->syncQuantity(null, null, $items, null, $purchase->company_head_id);

            $items = $this->site->getAllPurchaseItems($data['purchase_id']);

            $status = 'received';
            foreach ($items as $i => $item) {
                if ($item->status != "received") {
                    $status = "partial";
                    break;
                }
            }
            if ($status == "partial") {
                $this->db->update('purchases', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $data['purchase_id']]);
            }

            return true;
        }
        return false;
    }

    public function confirmReceived($data, $user_id, $sale_id = null, $term_of_payment = null, $deadline = null)
    {

        $this->load->model('aksestoko/at_site_model', 'at_site');

        $purchase = $this->getPurchaseByID($data['purchase_id']);
        $items = $this->site->getAllPurchaseItems($data['purchase_id']);
        $status = 'received';
        $is_parsial = false;
        $uploadedImg = null;
        if ($data['file'] && $data['file']['error'] == 0) {
            $check = getimagesize($data['file']["tmp_name"]);

            if (!$check) {
                throw new \Exception("File tidak valid");
            }
            if ($data['file']["size"] > 16000000) { //15mb
                throw new \Exception("Ukuran File terlalu besar");
            }

            // $image = base64_encode(file_get_contents($data['file']["tmp_name"]));
            // $uploadedImg = json_decode($this->at_site->uploadImage($image));
            $uploadedImg = $this->integration->upload_files($data['file']);
        }

        $data_purchase = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($purchase->payment_deadline == null && ($purchase->payment_method == 'kredit' || $purchase->payment_method == 'cash on delivery')) {
            if ($purchase->payment_method == 'kredit') {
                $deadline = $purchase->payment_duration;
                $cur = date('Y-m-d');
                $deadline = date('Y-m-d', strtotime('+' . $deadline . ' days', strtotime($cur)));
            } else {
                $deadline = 0;
                $cur = date('Y-m-d');
                $deadline = date('Y-m-d', strtotime('+' . $deadline . ' days', strtotime($cur)));
                $data_purchase['payment_duration'] = 0;
            }

            $data_purchase['payment_deadline'] = $deadline;
        }

        if ($term_of_payment != null && $deadline != null) {
            $data_purchase['payment_duration'] = $term_of_payment;
            $data_purchase['payment_deadline'] = $deadline;
        }

        if ($this->db->update('purchases', $data_purchase, ['id' => $data['purchase_id']])) {

            $this->db->update('deliveries', [
                'status' => 'delivered',
                'receive_status' => $status,
                'note' => $data['note'],
                'spj_file' => $uploadedImg ? $uploadedImg->url : '',
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $data['do_id']]);

            $arrayItemPurchase = [];
            foreach ($data['product_code'] as $i => $pc) {
                $item = $this->findPurchaseItemsByPurchaseIdAndProductCode($data['purchase_id'], $pc);

                $this->db->update('delivery_items', [
                    'good_quantity' => $data['good'][$i],
                    'bad_quantity' => $data['bad'][$i],
                    'updated_at' => date('Y-m-d H:i:s'),
                ], ['id' => $data['delivery_item_id'][$i]]);

                $dataStory = [
                    "purchase_item_id" => $item->id,
                    "purchase_id" => $purchase->id,
                    "reference_no" => $purchase->reference_no,
                    "product_id" => $item->product_id,
                    "product_code" => $item->product_code,
                    "product_name" => $item->product_name,
                    "quantity" => $item->quantity,
                    "quantity_received" => $data['quantity_received'][$i],
                    "delivery_item_id" => $data['delivery_item_id'][$i],
                    "delivery_id" => $data['do_id'],
                    "sino_do" => $data['do_ref'],
                    "created_by" => $user_id,
                ];
                $this->insertStoryReceived($dataStory);
                $this->updateAVCO([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity' => $item->quantity,
                    'cost' => $item->real_unit_cost
                ], $purchase->company_head_id);


                $arrayItemPurchase[$pc]['good'] = 0;
                $arrayItemPurchase[$pc]['bad'] = 0;
                $arrayItemPurchase[$pc]['sent'] = 0;

                $newData = $this->db->query("SELECT 
                                                sdi.`quantity_ordered` AS 'total_quantity', 
                                                SUM(IF(sd.status = 'delivered', sdi.`good_quantity`, 0)) AS 'good_quantity', 
                                                SUM(IF(sd.status = 'delivered', sdi.`bad_quantity`, IF(sd.status = 'returned', (sdi.`bad_quantity`*-1), 0))) AS 'bad_quantity'
                                            FROM `sma_delivery_items` sdi 
                                            INNER JOIN `sma_deliveries` sd 
                                                ON sd.`id` = sdi.`delivery_id` 
                                                WHERE sdi.`sale_id` = '" . $sale_id . "' AND sdi.`product_code` = '" . $pc . "'
                                                GROUP BY sdi.`product_code`
                                            ")->result()[0];
                $arrayItemPurchase[$pc]['good'] = $newData->good_quantity;
                $arrayItemPurchase[$pc]['bad'] = $newData->bad_quantity;
                $arrayItemPurchase[$pc]['sent'] = ($newData->bad_quantity + $newData->good_quantity);
            }

            foreach ($arrayItemPurchase as $productCode => $itemPurchase) {
                $item = $this->findPurchaseItemsByPurchaseIdAndProductCode($data['purchase_id'], $productCode);
                if (!$is_parsial) {
                    $status = $itemPurchase['sent'] >= $item->quantity ? $status : "partial";
                    $is_parsial = true;
                }
                $this->updatePurchaseItemsById($item->id, [
                    'status' => $status,
                    'quantity_balance' => $itemPurchase['sent'],
                    'quantity_received' => $itemPurchase['sent'],
                    'good_quantity' => $itemPurchase['good'],
                    'bad_quantity' => $itemPurchase['bad'],
                ]);
            }

            $this->site->syncQuantity(null, null, $items, null, $purchase->company_head_id);

            $items = $this->site->getAllPurchaseItems($data['purchase_id']);

            $status = 'received';
            foreach ($items as $i => $item) {
                if ($item->status != "received") {
                    $status = "partial";
                    break;
                }
            }
            if ($status == "partial") {
                $this->db->update('purchases', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $data['purchase_id']]);
            }

            return true;
        }
        return false;
    }

    public function getDeliveryItemBySaleIdAndDoId($sale_id, $do_id = null)
    {

        $this->db->where(['sale_id' => $sale_id]);
        if ($do_id != null)
            $this->db->where(['delivery_id' => $do_id]);
        // $q = $this->db->get_where('delivery_items', ['sale_id' => $sale_id,
        // ]);
        $q = $this->db->get('delivery_items');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function updateReview($purchase_id, $data)
    {
        $this->db->update('purchases', ['note' => $data['note'], 'updated_at' => date('Y-m-d H:i:s')], ['id' => $purchase_id]);
        foreach ($data['items'] as $i => $item) {
            $this->db->update('purchase_items', [
                'good_quantity' => $item['good'],
                'bad_quantity' => $item['bad'],
            ], ['id' => $item['purchase_item_id']]);
        }
        return true;
    }

    public function cancelOrder($purchase_id, $user_id)
    {
        $purchase = $this->findPurchaseByPurchaseId($purchase_id);
        if ($purchase->status == "ordered") {
            $updatePO = $this->db->update('purchases', [
                "status" => "canceled",
                "updated_at" => date('Y-m-d H:i:s'),
                "updated_by" => $this->session->userdata('user_id')
            ], [
                'id' => $purchase_id,
                'created_by' => $user_id
            ]);
            if ($updatePO) {
                return $this->db->update('sales', [
                    "sale_status" => "canceled",
                    'updated_at' => date('Y-m-d H:i:s'),
                    'is_updated_price' => null
                ], [
                    'reference_no' => $purchase->cf1,
                    'biller_id' => $purchase->supplier_id
                ]);
            }
        }
        return false;
    }

    public function getEmailReceiverThirdParty($thirdPartyName, $type)
    {
        $this->db->where('third_party_name', $thirdPartyName);
        $this->db->where('type', $type);
        $q = $this->db->get('email_third_party');
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function getEmailSenderThirdParty($thirdPartyName, $type)
    {
        $this->db->where('third_party_name', $thirdPartyName);
        $this->db->where('type', $type);
        $q = $this->db->get('email_third_party', 1);
        if ($q->num_rows() > 0) {
            return $q->row_array();
        }
        return false;
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/models/Sales_model.php';

class At_sale_model extends Sales_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addSaleAT($data = array(), $items = array(), $payment = array(), $si_return = array())
    {

        $data['created_at'] = date('Y-m-d H:i:s');

        if (!$this->db->insert('sales', $data)) {
            throw new \Exception($this->db->error()['message']);
        }
        $sale_id = $this->db->insert_id();
        // if ($this->site->getReference('so', $data['biller_id']) == $data['reference_no']) {

        if (!$this->site->updateReference('so', $data['biller_id'])) {
            throw new \Exception("Gagal memperbarui SO Reference");
        }
        // }
        foreach ($items as $item) {
            $item['sale_id'] = $sale_id;
            if (!$this->db->insert('sale_items', $item)) {
                throw new \Exception($this->db->error()['message']);
            }
        }
        return $sale_id;
    }

    public function addSaleATBooking($data = array(), $items = array(), $payment = array(), $si_return = array())
    {
        //mengecek apakah sudah ada sale sama atau belum
        if ($this->getSalesByRefNo($data['reference_no'], $data['biller_id'])) {
            throw new \Exception("Tidak dapat membuat SO. No Ref telah digunakan.");
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        if (!$this->db->insert('sales', $data)) {
            throw new \Exception($this->db->error()['message']);
        }
        $sale_id = $this->db->insert_id();

        // if ($this->site->getReference('so', $data['biller_id']) == $data['reference_no']) {

        if (!$this->site->updateReference('so', $data['biller_id'])) {
            throw new \Exception("Gagal memperbarui SO Reference");
        }
        // }
        foreach ($items as $item) {
            $booking = array(
                'sale_id' => $sale_id,
                'product_id' => $item['product_id'],
                'warehouse_id' => $item['warehouse_id'],
                'product_code' => $item['product_code'],
                'product_name' => $item['product_name'],
                'product_type' => $item['product_type'],
                'quantity_order' => $item['quantity'],
                'product_unit_id' => $item['product_unit_id'],
                'product_unit_code' => $item['product_unit_code'],
                'client_id' => null,
                'created_at' => date('Y-m-d H:i:s')
            );

            if (!$this->db->insert('sale_booking_items', $booking)) {
                throw new \Exception($this->db->error()['message']);
            }

            $item['sale_id'] = $sale_id;
            if (!$this->db->insert('sale_items', $item)) {
                throw new \Exception($this->db->error()['message']);
            }
        }
        return $sale_id;
    }

    public function updateOrders($sale, $purchase)
    {
        $sale['updated_at'] = date('Y-m-d H:i:s');
        $purchase['updated_at'] = date('Y-m-d H:i:s');
        $purchase['updated_by'] = $this->session->userdata('user_id');
        if ($this->db->update('purchases', $purchase, array('id' => $purchase['id'])) && $this->db->update('sales', $sale, array('id' => $sale['id']))) {
            return true;
        }
        return false;
    }

    public function findSalesByReferenceNo($cf1, $supplier_id)
    {
        $q = $this->db->get_where('sales', [
            'reference_no' => $cf1,
            'company_id' => $supplier_id
        ]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveriesItems($company_id, $sale_ref)
    {
        $q = $this->db->get_where('sales', [
            'company_id' => $company_id,
            'reference_no' => $sale_ref
        ], 1);

        if ($q->num_rows() > 0) {
            $sales = $q->row();
            $q = $this->db->get_where('deliveries', [
                'sale_id' => $sales->id,
                'status !=' => 'returned',
            ]);
            if ($q->num_rows() > 0) {
                $deliveries = $q->result();
                foreach ($deliveries as $i => $delivery) {
                    $item = $this->db->get_where('delivery_items', [
                        'delivery_id' => $delivery->id,
                        'sale_id' => $sales->id,
                    ]);
                    $delivery->items = $item->result();
                }

                return $deliveries;
            }
        }
        return false;
    }

    public function findDeliveryItems($do_id)
    {
        $q = $this->db->get_where('deliveries', [
            'id' => $do_id,
        ], 1);

        if ($q->num_rows() > 0) {
            $delivery = $q->row();
            $item = $this->db->get_where('delivery_items', [
                'delivery_id' => $delivery->id,
                'sale_id' => $delivery->sale_id
            ]);
            $delivery->items = $item->result();
            // var_dump($delivery); die;
            return $delivery;
        }
        return false;
    }
}

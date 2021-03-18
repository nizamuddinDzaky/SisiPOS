<?php defined('BASEPATH') or exit('No direct script access allowed');

class Scheduler extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sales_model');
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
    }

    public function confirm_received()
    {
        $deliveries = $this->sales_model->getAllDeliveriesExpired();
        foreach ($deliveries as $i => $delivery) {
            $this->db->trans_begin();
            try {
                echo "------------------------- \n";
                echo "Proses untuk id : " . $delivery->do_id ."\n";
                $delivery_items = $this->sales_model->getDeliveryItemsByDeliveryId($delivery->do_id);

                $product_code = [];
                $quantity_received = [];
                $delivery_item_id = [];
                $good = [];
                $bad = [];

                foreach ($delivery_items as $i => $item) {
                    $product_code [] = $item->product_code;
                    $quantity_received [] = (int) $item->quantity_sent;
                    $delivery_item_id [] = $item->id;
                    $good [] = (int) $item->quantity_sent;
                    $bad [] = 0;
                }

                $data = [
                    'purchase_id' => $delivery->purchase_id,
                    'product_code' => $product_code,
                    'quantity_received' => $quantity_received,
                    'do_ref' => $delivery->do_ref,
                    'do_id' => $delivery->do_id,
                    'delivery_item_id' => $delivery_item_id,
                    'good' => $good,
                    'bad' => $bad,
                    'note' => "Diterima secara otomatis pada ".date('Y-m-d H:i:s')
                ];
                
                $confirm = $this->at_purchase->confirmReceived($data, 1);
                
                if (!$confirm) {
                    throw new \Exception("Gagal konfirmasi penerimaan -> " . $delivery->do_id);
                }
                
                $this->db->trans_commit();
                echo "Berhasil konfirmasi penerimaan -> " . $delivery->do_id . "\n";
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                echo $th->getMessage(). "\n";
            }
            echo "-------------------------". "\n". "\n";
        }
        return;
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require 'MainController.php';

class Delivery extends MainController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('site', 'site');
        $this->load->model('audittrail_model', 'audittrail');
        $this->load->model('Sales_model', 'sales_model');
        $this->load->model('companies_model');
    }

    public function confirmation_delivery_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            
            $config = [
                [
                    'field' => 'penerimaan',
                    'label' => 'penerimaan',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'doid',
                    'label' => 'doid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'orderid',
                    'label' => 'orderid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'tanggalterima',
                    'label' => 'tanggalterima',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'spj',
                    'label' => 'spj',
                    'rules' => 'required',
                    'errors' => $this->errors
                ]
            ];

            $config_detail = [
                [
                    'field' => 'penerimaandetailid',
                    'label' => 'penerimaandetailid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'penerimaanid',
                    'label' => 'penerimaanid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'productid',
                    'label' => 'productid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'productcode',
                    'label' => 'productcode',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'qty_terima',
                    'label' => 'qty_terima',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'qty_baik',
                    'label' => 'qty_baik',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'qty_buruk',
                    'label' => 'qty_buruk',
                    'rules' => 'required',
                    'errors' => $this->errors
                ]
            ];
            $this->validate_form($config);

            $this->load->model('integration_model', 'integration');

            $penerimaandetail = $this->body('penerimaandetail');
            if (!$penerimaandetail) {
                throw new Exception('`penerimaandetail` dibutuhkan', 400);
            }

            foreach ($penerimaandetail as $k => $detail_item) {
                $this->validate_form($config_detail, $detail_item);
                $jumlah = ((int) $detail_item['qty_baik'] + (int) $detail_item['qty_buruk']);
                if ($jumlah != (int) $detail_item['qty_terima'] ) {
                    throw new Exception("Jumlah `qty_baik` dan `qty_buruk` tidak sama dengan `qty_terima` pada `penerimaandetailid={$detail_item['penerimaandetailid']}`", 400);
                }
            }
            
            $penerimaan     = $this->body('penerimaan');
            $doid           = $this->body('doid');
            $orderid        = $this->body('orderid');
            $tanggalterima  = $this->body('tanggalterima');
            $spj            = $this->body('spj');

            $get_order      = $this->sales_model->get_atl_order($orderid);
            if(!$get_order){
                throw new \Exception("Pesanan dengan `orderid=$orderid` tidak ditemukan", 404);
            }
            $get_delivery   = $this->sales_model->get_delivery_by_atldoid($doid);
            if(!$get_delivery){
                throw new \Exception("Pengiriman dengan `doid=$doid` tidak ditemukan", 404);
            }

            if($get_order->sale_id != $get_delivery->sale_id) {
                throw new \Exception("Pengiriman dengan kombinasi `doid=$doid` dan `orderid=$orderid` tidak ditemukan", 404);
            }

            $cek_doid       = $this->sales_model->get_atl_confirmation($doid);
            if($cek_doid){
                throw new \Exception("Telah ada penerimaan dengan `doid=$doid`", 400);
            }
            $sale_id        = $get_order->sale_id;
            $delivery_id    = $get_delivery->id;
            $biller_id      = $auth->company->id;
            $biller         = $this->site->getCompanyByID($biller_id);
            $idbk_toko      = $get_order->bisniskokohidtoko;

            foreach ($penerimaandetail as $k => $item) {
                $product = $this->sales_model->findDeliveryItem([
                    'product_code' => $item['productcode'],
                    'delivery_id' => $delivery_id 
                ]);

                if(!$product) {
                    throw new \Exception("Item dengan `productcode={$item['productcode']}` tidak ditemukan", 404);
                }

                if($product->quantity_sent != $item['qty_terima']) {
                    throw new \Exception("Jumlah `qty_terima` pada `productcode={$item['productcode']}` tidak sesuai, seharusnya " . (int) $product->quantity_sent . ". ", 400);
                }

                $confirm_item[] = [
                    'product_id'    => $product->product_id,
                    'good_quantity' => $item['qty_baik'],
                    'bad_quantity'  => $item['qty_buruk']
                ];

                $confirm_item_temp[] = [
                    'penerimaandetailid'    => $item['penerimaandetailid'],
                    'penerimaanid'          => $item['penerimaanid'],
                    'productid'             => $item['productid'],
                    'productcode'           => $item['productcode'],
                    'qty_terima'            => $item['qty_terima'],
                    'qty_baik'              => $item['qty_baik'],
                    'qty_buruk'             => $item['qty_buruk']
                ];

                $products[] = [
                    'product_code'  => $item['productcode'],
                    'good_quantity' => $item['qty_baik'],
                    'bad_quantity'  => $item['qty_buruk']
                ];
            }

            $data_confirm = [
                "status"            => 'delivered',
                "spj_file"          => $spj,
                "receive_status"    => 'received',
                "delivered_date"    => $tanggalterima
            ];

            $data_confirm_temp = [
                "sale_id"           => $sale_id,
                "delivery_id"       => $delivery_id,
                "company_id"        => $auth->company->id,
                "penerimaan"        => $penerimaan,
                "doid"              => $doid,
                "orderid"           => $orderid,
                "tanggalterima"     => $tanggalterima,
                "spj"               => $spj
            ];

            $delivery = $this->sales_model->update_deliveries($delivery_id, $data_confirm);
            if(!$delivery){
                throw new \Exception("Gagal memperbarui data `delivery`");
            }

            foreach ($confirm_item as $k => $val) {
                $item = $this->sales_model->update_delivery_items($delivery_id, $val);
                if(!$item){
                    throw new \Exception("Gagal memperbarui data `delivery_item`");
                }
            }

            if(!$this->sales_model->insert_atl_confirm($data_confirm_temp)){
                throw new \Exception("Gagal menambahkan data konfirmasi penerimaan ke tabel `atl_confirmation_deliveries`");
            }

            if(!$this->sales_model->insert_atl_confirm_item($confirm_item_temp)){
                throw new \Exception("Gagal menambahkan data item konfirmasi penerimaan ke tabel `atl_confirmation_delivery_items`");
            }

            if ($this->site->checkAutoCloseATL($sale_id)) {
                $this->sales_model->closeSale($sale_id);
            }

            if ($this->integration->isIntegrated($biller->cf2)) {
                $sale_data = $this->sales_model->getSalesById($sale_id);
                $delivery = $this->sales_model->getDeliveryByID($delivery_id);
                $deliveryItems = $this->sales_model->getDeliveryItemsByDeliveryId($delivery_id);
                $call_api = $this->integration->confirm_received_integration($biller->cf2, $idbk_toko, $sale_data, $delivery, $deliveryItems);
                if (!$call_api) {
                    throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                }
            }
            $response = [
                'doid' => $doid,
                'pos_do_id' => $delivery_id,
                'pos_do_code' => $get_delivery->do_reference_no
            ];

            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil mengonfirmasi penerimaan barang", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }

}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require 'MainController.php';

class Order extends MainController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('site', 'site');
        $this->load->model('audittrail_model', 'audittrail');
        $this->load->model('Sales_model', 'sales_model');
        $this->load->model('companies_model');
    }

    public function insert_order_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'orderid',
                    'label' => 'orderid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'bisniskokohiddist',
                    'label' => 'bisniskokohiddist',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'bisniskokohidtoko',
                    'label' => 'bisniskokohidtoko',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'no_rek',
                    'label' => 'no_rek',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'alamat_detail',
                    'label' => 'alamat_detail',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'tipepengirimanid',
                    'label' => 'tipepengirimanid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'statusorderid',
                    'label' => 'statusorderid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'ordercode',
                    'label' => 'ordercode',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'orderdate',
                    'label' => 'orderdate',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'totalharga',
                    'label' => 'totalharga',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'tanggalexpetasipengiriman',
                    'label' => 'tanggalexpetasipengiriman',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'catatan',
                    'label' => 'catatan',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'jumlahbarang',
                    'label' => 'jumlahbarang',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'biayatempo',
                    'label' => 'biayatempo',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'active_',
                    'label' => 'active_',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'paymentmethodid',
                    'label' => 'paymentmethodid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'tempo',
                    'label' => 'tempo',
                    'rules' => 'required',
                    'errors' => $this->errors
                ]
            ];

            $config_order_detail = [
                [
                    'field' => 'orderdetailid',
                    'label' => 'orderdetailid',
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
                    'field' => 'productcode',
                    'label' => 'productcode',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'productname',
                    'label' => 'productname',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'itemprice',
                    'label' => 'itemprice',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'jumlahproduct',
                    'label' => 'jumlahproduct',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'totalharga',
                    'label' => 'totalharga',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'active_',
                    'label' => 'active_',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'uomid',
                    'label' => 'uomid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
            ];
            $this->validate_form($config);

            $this->load->model('integration_model', 'integration');

            $order_detail = $this->body('order_detail');
            if (!$order_detail) {
                throw new Exception('`order_detail` dibutuhkan', 400);
            }

            $total = 0;
            $total_product = 0;
            foreach ($order_detail as $k => $detail_item) {
                $this->validate_form($config_order_detail, $detail_item);

                $subtotal = (float) $detail_item['itemprice'] * (float) $detail_item['jumlahproduct'];
                if ($subtotal != (float) $detail_item['totalharga']) {
                    throw new Exception("`totalharga` pada `productcode={$detail_item['productcode']}` tidak sesuai perhitungan `itemprice` dan `jumlahproduct`", 400);
                }
                $total += $subtotal;
                $total_product += (float) $detail_item['jumlahproduct'];
            }

            $totalharga         = (float) $this->body('totalharga');
            $jumlahbarang       = $this->body('jumlahbarang');

            if ($total != $totalharga) {
                throw new Exception("`totalharga` tidak sesuai dengan perhitungan harga seluruh item", 400);
            }

            if ($total_product != $jumlahbarang) {
                throw new Exception("`jumlahbarang` tidak sesuai dengan perhitungan kuantitas seluruh item", 400);
            }


            $biller_id          = $auth->company->id;
            $customer_group_id  = $auth->company->customer_group_id;
            $price_group_id     = $auth->company->price_group_id;
            $cf1                = $auth->company->cf1;
            $user_id            = $auth->user->id;
            $company_id         = $auth->user->company_id;
            $username           = $auth->user->username;
            $orderid            = $this->body('orderid');
            $id_distributor     = $this->body('bisniskokohiddist');
            $idbk_toko          = $this->body('bisniskokohidtoko');
            $bank               = $this->body('bank');
            $no_rek             = $this->body('no_rek');
            $alamat_detail      = $this->body('alamat_detail');
            $delivery_method    = $this->body('tipepengirimanid');

            $statusorderid      = $this->body('statusorderid');
            $convert_status     = $this->convertStatus('order', $statusorderid);
            if ($convert_status == 'unknown') {
                throw new \Exception("`statusorderid=$statusorderid` tidak dikenali", 400);
            }

            $ordercode          = $this->body('ordercode');
            $orderdate          = $this->body('orderdate');
            $delivery_date      = $this->body('tanggalexpetasipengiriman');
            $note               = $this->body('catatan');
            $biayatempo         = $this->body('biayatempo');
            $active_            = $this->body('active_');
            $payment_method     = $this->body('paymentmethodid');
            $tempo_payment      = $this->body('tempo');

            $cek_orderid        = $this->sales_model->get_atl_order($orderid);
            if ($cek_orderid) {
                throw new \Exception("Telah ada pemesanan dengan `orderid=$orderid`", 400);
            }
            $customer           = $this->sales_model->findCompanyByCf1AndCompanyId($biller_id, 'IDC-' . $idbk_toko);
            if (!$customer) {
                throw new \Exception("Toko dengan `bisniskokohidtoko=$idbk_toko` tidak ditemukan", 404);
            }
            $customer_name      = $customer->company != '-' ? $customer->company : $customer->name;
            $biller             = $this->site->getCompanyByID($biller_id);
            $biller_name        = $biller->company != '-' ? $biller->company : $biller->name;
            $staff_note         = null;
            $sale_type          = 'booking';
            $sale_status        = 'pending';
            $payment_status     = 'pending';
            $payment_term       = $tempo_payment;
            $date               = $orderdate;
            $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $uuid_sales         = getUuid();

            // $reff               = substr_replace($this->site->getReference('so'), "/ATL", 4, 0);
            // $sale_by_ref        = $this->sales_model->getSalesByRefNo($reff, $biller_id);
            // if ($sale_by_ref) {
            //     $this->site->updateReference('so', $biller_id);
            // }
            // $reference          = substr_replace($this->site->getReference('so'), "/ATL", 4, 0);
            $reference          = $this->site->getReference('so');

            $get_customer_warehouse     = $this->sales_model->findWarehouseCustomerByCustomerId($customer->id);
            if ($get_customer_warehouse) {
                $warehouse_id         = $get_customer_warehouse->default;
            } else {
                $warehouse_id         = $this->sales_model->findCompanyWarehouse($biller_id)->id;
            }

            if ($uuid = $this->site->isUuidExist($uuid_sales, 'sales')) {
                throw new Exception("UUID $uuid is exist.", 400);
            }

            $total          = 0;
            $total_items    = 0;

            foreach ($order_detail as $r => $item) {
                $product    = $this->sales_model->getProductByCode($item['productcode'], $company_id);

                if (!$product) {
                    throw new Exception("Item dengan `productcode={$item['productcode']}` tidak ditemukan", 404);
                }

                $item_id    = $product->id;
                $item_type  = $product->type;
                $item_code  = $product->code;
                $item_name  = $product->name;
                $real_unit_price    = $this->sma->formatDecimal($item['itemprice']);
                $unit_price         = $this->sma->formatDecimal($item['itemprice']);
                $item_unit_quantity = $item['jumlahproduct'];
                $item_unit          = $product->unit;
                $item_quantity      = $item['jumlahproduct'];
                $total_items        += $item_quantity;

                if ($item_code && $real_unit_price && $unit_price && $item_quantity) {

                    $item_net_price = $unit_price;

                    $subtotal       = $item_net_price * $item_unit_quantity;
                    $unit           = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id'            => $item_id,
                        'product_code'          => $item_code,
                        'product_name'          => $item_name,
                        'product_type'          => $item_type,
                        'option_id'             => null,
                        'net_unit_price'        => $item_net_price,
                        'unit_price'            => $item_net_price,
                        'quantity'              => $item_quantity,
                        'product_unit_id'       => $item_unit,
                        'product_unit_code'     => $unit ? $unit->code : null,
                        'unit_quantity'         => $item_unit_quantity,
                        'warehouse_id'          => $warehouse_id,
                        'item_tax'              => null,
                        'tax_rate_id'           => null,
                        'tax'                   => null,
                        'discount'              => null,
                        'item_discount'         => null,
                        'subtotal'              => $this->sma->formatDecimal($subtotal),
                        'serial_no'             => null,
                        'real_unit_price'       => $real_unit_price,
                        'flag'                  => null,
                    );

                    $booking[] = array(
                        'product_id'        => $item_id,
                        'warehouse_id'      => $warehouse_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'quantity_order'    => $item_quantity,
                        'quantity_booking'  => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'client_id'         => 'atl',
                        'created_at'        => date('Y-m-d H:i:s'),
                    );

                    $order_item[] = [
                        'orderdetailid' => $item['orderdetailid'],
                        'orderid'       => $item['orderid'],
                        'productcode'   => $item['productcode'],
                        'productname'   => $item['productname'],
                        'itemprice'     => $item['itemprice'],
                        'jumlahproduct' => $item['jumlahproduct'],
                        'totalharga'    => $item['totalharga'],
                        'active_'       => $item['active_'],
                        'uomid'         => $item['uomid']
                    ];
                    $total += $subtotal;
                }
            }

            krsort($products);

            if($payment_method == '1' || $payment_method == '2'){
                $price_type = 'cash';
            } else {
                $price_type = 'credit';
            }

            $grand_total = $total;
            $data = array(
                'date' => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer->id,
                'customer'          => $customer_name,
                'biller_id'         => $biller_id,
                'biller'            => $biller_name,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => null,
                'order_discount_id' => null,
                'order_discount'    => null,
                'total_discount'    => null,
                'product_tax'       => null,
                'order_tax_id'      => null,
                'order_tax'         => null,
                'total_tax'         => null,
                'shipping'          => null,
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'paid'              => 0,
                'created_by'        => $user_id,
                'company_id'        => $company_id,
                'sale_type'         => $sale_type,
                'client_id'         => 'atl',
                'price_type'        => $price_type,
                'uuid'              => $uuid_sales,
            );

            $data_order = [
                "company_id"        => $auth->company->id,
                "orderid"           => $orderid,
                "bisniskokohiddist" => $id_distributor,
                "bisniskokohidtoko" => $idbk_toko,
                "bank"              => $bank,
                "no_rek"            => $no_rek,
                "alamat_detail"     => $alamat_detail,
                "tipepengirimanid"  => $delivery_method,
                "delivery_method"   => $this->convertStatus('delivery_method', $delivery_method),
                "statusorderid"     => $statusorderid,
                "ordercode"         => $ordercode,
                "orderdate"         => $orderdate,
                "totalharga"        => $totalharga,
                "tanggalexpetasipengiriman" => $delivery_date,
                "catatan"           => $note,
                "jumlahbarang"      => $jumlahbarang,
                "biayatempo"        => $biayatempo,
                "active_"           => $active_,
                "paymentmethodid"   => $payment_method,
                "payment_method"    => $this->convertStatus('payment_method', $payment_method),
                "tempo"             => $tempo_payment
            ];

            krsort($data);
            krsort($booking);

            $sale_id = $this->sales_model->addSaleBooking($data, $products, [], null, $booking);

            if (!$sale_id) {
                throw new \Exception("Gagal menambahkan penjualan", 500);
            }

            $data_order['sale_id'] = $sale_id;
            foreach ($order_item as $k => $v) {
                $order_item[$k]['sale_id'] = $sale_id;
            }
            krsort($order_item);
            $insert_order   = $this->sales_model->insert_order($data_order, $order_item);
            if (!$insert_order) {
                throw new \Exception("Gagal menambahkan data pesanan ke tabel `atl_orders` atau `atl_order_items`", 400);
            }

            if ($this->integration->isIntegrated($biller->cf2)) {
                $sale_data = $this->sales_model->getSalesBySalesId($sale_id);
                $bank = $this->site->getBankByName($bank, $company_id);
                $order_data = [
                    'id'                => $orderid,
                    'shipping_date'     => $delivery_date,
                    'payment_duration'  => $tempo_payment,
                    'payment_method'    => $this->convertStatus('payment_method', $payment_method),
                    'bank_id'           => $bank->id
                ];
                $call_api = $this->integration->create_order_integration($biller->cf2, $idbk_toko, $sale_data, $products, $order_data);
                if (!$call_api) {
                    throw new \Exception("Tidak dapat mengirim order ke distributor");
                }
            }
            // $this->site->updateReference('so', $biller_id);

            $response = [
                'orderid' => $orderid,
                'pos_sale_id' => $sale_id,
                'pos_sale_code' => $reference,
            ];
            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil menambahkan pesanan", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }

    public function update_kreditpro_status_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'orderid',
                    'label' => 'orderid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'statuskredit',
                    'label' => 'statuskredit',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'datetime',
                    'label' => 'datetime',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
            ];

            $this->validate_form($config);

            $orderid = $this->body('orderid');
            $statuskredit = $this->body('statuskredit');
            $datetime = $this->body('datetime');

            $order = $this->sales_model->get_atl_order($orderid);
            if (!$order) {
                throw new \Exception("Pemesanan dengan `orderid=$orderid` tidak ditemukan", 404);
            }

            $kreditpro_status = $this->sales_model->getAtlKreditproStatus($orderid);
            if (!$kreditpro_status) {
                throw new \Exception("KreditPro Status dengan `orderid=$orderid` tidak ditemukan", 404);
            }

            $sale = $this->sales_model->getInvoiceByID($order->sale_id);
            if (!$order) {
                throw new \Exception("Penjualan tidak ditemukan", 404);
            }

            $this->db->update('atl_kreditpro_status', ['statuskredit' => $statuskredit, 'status' => $this->convertStatus('status_kreditpro', $statuskredit), 'datetime' => $datetime], ['orderid' => $orderid]);

            $response = [
                'orderid' => $orderid,
            ];

            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil memperbarui kreditpro status", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }
    
    public function update_order_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'orderid',
                    'label' => 'orderid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'statusadjustmentid',
                    'label' => 'statusadjustmentid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'perubahanharga',
                    'label' => 'perubahanharga',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
                [
                    'field' => 'datetime',
                    'label' => 'datetime',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
            ];

            $this->validate_form($config);

            $this->load->model('integration_model', 'integration');

            $orderid            = $this->body('orderid');
            $statusadjustmenid  = $this->body('statusadjustmentid');
            $perubahanharga     = $this->body('perubahanharga');
            $datetime           = $this->body('datetime');

            $order = $this->sales_model->get_atl_order($orderid);
            if (!$order) {
                throw new \Exception("Pemesanan dengan `orderid=$orderid` tidak ditemukan", 404);
            }

            $sale = $this->sales_model->getInvoiceByID($order->sale_id);
            if (!$order) {
                throw new \Exception("Penjualan tidak ditemukan", 404);
            }

            $biller_id          = $auth->company->id;
            $biller             = $this->site->getCompanyByID($biller_id);
            $idbk_toko          = $order->bisniskokohidtoko;
            $delivery_date      = $order->tanggalexpetasipengiriman;
            $tempo              = $order->tempo;
            $payment_method     = $order->paymentmethodid;
            $status             = $this->convertStatus('order', $statusadjustmenid);

            if ($sale->sale_status == 'canceled' || $sale->sale_status == 'closed') {
                throw new \Exception("Penjualan telah ditutup atau dibatalkan", 400);
            }

            if ($status == 'canceled') {
                if ($sale->sale_status == 'reserved') {
                    //close sale
                    if (!$this->sales_model->closeSale($sale->id)) {
                        throw new \Exception("Gagal menutup penjualan", 500);
                    }
                } else if ($sale->sale_status == 'pending') {
                    // update to canceled
                    if (!$this->sales_model->updateStatus($sale->id, $status, 'note_canceled', 'reason_canceled')) {
                        throw new \Exception("Gagal membatalkan penjualan", 500);
                    }
                }
            } else if ($status == 'reserved') {
                //update to reserved
                if (!$this->sales_model->updateStatus($sale->id, $status, 'note_reserved', 'reason_reserved')) {
                    throw new \Exception("Gagal mengonfirmasi penjualan", 500);
                }
            } else {
                throw new \Exception("`statusadjustmenid=$statusadjustmenid` tidak dikenali", 400);
            }

            $this->db->update('atl_orders', ['statusorderid' => $statusadjustmenid], ['orderid' => $orderid]);

            if ($this->integration->isIntegrated($biller->cf2)) {
                $sale_data = $this->sales_model->getSalesBySalesId($sale->id);
                $bank = $this->site->getBankByName($bank, $company_id);
                $order_item = $this->sales_model->get_atl_order_item($orderid);
                foreach ($order_item as $k => $v) {
                    $products['product_code']   = $v['productcode'];
                    $products['quantity']       = $v['jumlahproduct'];
                    $products['unit_price']     = $v['itemprice'];
                }
                $order_data = [
                    'id'                => $orderid,
                    'shipping_date'     => $delivery_date,
                    'payment_duration'  => $tempo,
                    'payment_method'    => $this->convertStatus('payment_method', $payment_method),
                    'bank_id'           => $bank->id
                ];
                $call_api = $this->integration->update_confirmation_integration($biller->cf2, $idbk_toko, $sale_data, $products, $order_data);
                if (!$call_api) {
                    throw new \Exception("Tidak dapat membatalkan pesanan ke distributor");
                }
            }

            $response = [
                'orderid' => $orderid,
                'pos_sale_id' => $sale->id,
                'pos_sale_code' => $sale->reference_no,
            ];
            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil memperbarui pesanan", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }
}

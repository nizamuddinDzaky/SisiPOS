<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Scheduler extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->load('reports', $this->Settings->user_language);
        $this->load->model('integration_model', 'integration');
    }

    public function sync_report_stock_card_cid()
    {
        $query = $this->db->query("SELECT * FROM `sma_companies` WHERE `group_name` = 'biller' AND (`client_id` IS NULL OR `client_id` NOT LIKE '%aksestoko%')");
        foreach ($query->result() as $row) {
            $success = $this->db->query('call sync_report_stock_card_cid(' . $row->id . ')');
            if (!$success)
                echo "Syncrone sync_report_stock_card_cid error : id = " . $row->id;
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function get_do_csms()
    {
        $this->load->model('deliveries_smig_model');
        try {
            $kode_plant   = $this->deliveries_smig_model->getMasterPlant();
            if (!$kode_plant) {
                throw new Exception("plant tidak boleh kosong");
            }
            $response = [];
            foreach ($kode_plant as $row_plant) {
                echo 'Sedang mengambil data pada no plant : ' . $row_plant['plant'] . "...\n";
                $response_makasar = $this->deliveries_smig_model->send_data_deliveries_smig_makasar($row_plant['plant']);
                if ($response_makasar) {
                    $response = $response_makasar;
                }
                $response_smig = $this->deliveries_smig_model->send_data_deliveries_smig($row_plant['plant']);
                if ($response_smig) {
                    $response = $response_smig;
                }
                $response_padang = $this->deliveries_smig_model->send_data_deliveries_smig_padang($row_plant['plant']);
                if ($response_padang) {
                    $response = $response_padang;
                }
                echo $response . "\n";
                if (!$response) {
                    echo 'tidak terdapat data pada no plant : ' . $row_plant['plant'] . "\n";
                } else {
                    $count = count($response);
                    echo 'terdapat ' . $count . ' data pada no plant : ' . $row_plant['plant'] . "\n";
                    if ($count > 0) {
                        $total_distributor = 0;
                        $total_product     = 0;
                        foreach ($response as $index => $row) {
                            $wh  = $this->deliveries_smig_model->setDeliveriesSmigByWarhouse($row->kodeShipto);
                            $sp  = $this->deliveries_smig_model->setDeliveriesSmigBySupplier($row->com);
                            $bl  = $this->deliveries_smig_model->setDeliveriesSmigBySupplier((int)$row->kodeDistributor);
                            if (!$bl) {
                                $total_distributor += 1;
                                echo 'data distributor : ' . (int)$row->kodeDistributor . ' tidak terdapat pada FORCA POS : ' . ($index + 1) . '/' . $count . "\n";
                                continue;
                            }
                            $pd  = $this->deliveries_smig_model->getProductByCode($row->kodeproduk);
                            if (!$pd) {
                                $total_product += 1;
                                echo 'code product : ' . $row->kodeproduk . ' dengan nama ' . $row->produk . ' tidak terdapat pada FORCA POS : ' . ($index + 1) . '/' . $count . "\n";
                                continue;
                            }
                            $tx  = $this->deliveries_smig_model->getTaxRateByID($pd->tax_rate);

                            $pr_discount      = 0;
                            $item_tax_rate    = $tx->rate;
                            $unit_price       = $this->sma->formatDecimal($pd->cost - $pr_discount);
                            $item_net_price   = $pd->cost;
                            $pr_item_discount = $this->sma->formatDecimal($pr_discount * $row->qtyDO);
                            $product_discount += $pr_item_discount;
                            $pr_tax           = 0.0000;
                            $pr_item_tax      = 0.0000;
                            $item_tax         = 0.0000;
                            $shipping         = 0.0000;
                            $tax              = "";

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

                            $product_tax     += $pr_item_tax;
                            $subtotal         = (($item_net_price * $row->qtyDO) + $pr_item_tax);

                            $products = [
                                'product_id'        => $pd->id,
                                'product_code'      => $row->kodeproduk,
                                'product_name'      => $row->produk,
                                'product_type'      => $pd->type,
                                'net_unit_price'    => $item_net_price,
                                'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                                'quantity'          => $row->qtyDO,
                                'product_unit_id'   => $pd->unit,
                                'product_unit_code' => $row->uom,
                                'unit_quantity'     => $row->qtyDO,
                                'warehouse_id'      => $wh->id,
                                'item_tax'          => $pr_item_tax,
                                'tax_rate_id'       => $pr_tax,
                                'tax'               => $tax,
                                'item_discount'     => $pr_item_discount,
                                'subtotal'          => $this->sma->formatDecimal($subtotal),
                                'real_unit_price'   => $pd->cost,
                            ];
                            $total += $this->sma->formatDecimal(($item_net_price * $row->qtyDO), 4);
                            if (empty($products)) {
                                $this->form_validation->set_rules('product', lang("order_items"), 'required');
                            } else {
                                krsort($products);
                            }

                            if ($this->input->post('discount')) {
                                $order_discount_id = $this->input->post('discount');
                                $opos = strpos($order_discount_id, $percentage);
                                if ($opos !== false) {
                                    $ods = explode("%", $order_discount_id);
                                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                                } else {
                                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                                }
                            } else {
                                $order_discount_id = null;
                            }
                            $total_discount = $order_discount + $product_discount;

                            if ($this->Settings->tax2 != 0) {
                                $order_tax_id = $this->input->post('order_tax');
                                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                                    if ($order_tax_details->type == 2) {
                                        $order_tax = $order_tax_details->rate;
                                    }
                                    if ($order_tax_details->type == 1) {
                                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                                    }
                                }
                            } else {
                                $order_tax_id = 0.0000;
                            }

                            $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);

                            $data = [
                                'company_code'      => $row->com,
                                'company_name'      => $row->com_name,
                                'no_so'             => $row->noSO,
                                'line_so'           => $row->lineSO,
                                'tipe_order'        => $row->tipeOrder,
                                'tanggal_so'        => $row->tglSO,
                                'incotrem'          => $row->incoterm,
                                'no_do'             => $row->noDO,
                                'tanggal_do'        => $row->tglDO,
                                'kode_produk'       => $row->kodeproduk,
                                'nama_produk'       => $row->produk,
                                'qty_do'            => $row->qtyDO,
                                'uom'               => $row->uom,
                                'no_spj'            => $row->noSPJ,
                                'tanggal_spj'       => $row->tglSPJ,
                                'jam_spj'           => $row->jamSPJ,
                                'no_spss'           => $row->noSPPS,
                                'no_polisi'         => $row->noPolisi,
                                'nama_sopir'        => $row->namaSupir,
                                'kode_distributor'  => $row->kodeDistributor,
                                'distributor'       => $row->distributor,
                                'kode_shipto'       => $row->kodeShipto,
                                'nama_shipto'       => $row->namaShipto,
                                'alamat_shipto'     => $row->alamatShipto,
                                'kode_distrik'      => $row->kodeDistrik,
                                'distrik'           => $row->distrik,
                                'kode_kecamatan'    => $row->kodeKecamatan,
                                'nama_kecamatan'    => $row->namaKecamatan,
                                'kode_ekspeditur'   => $row->kodeEkspeditur,
                                'ekspeditur'        => $row->ekspeditur,
                                'kode_plant'        => $row->kodePlant,
                                'nama_plant'        => $row->plant,
                                'nama_kapal'        => $row->namaKapal,
                                'status'            => $row->status,
                                'nomer_po'          => $row->nomerPO,
                                'no_transaksi'      => $row->noTransaksi,
                                'no_pp'             => $row->noPP,
                                'tanggal_pp'        => $row->tglPP,
                                'tanggal_antri'     => $row->tglAntri,
                                'jam_antri'         => $row->jamAntri,
                                'tanggal_masuk'     => $row->tglMasuk,
                                'jam_masuk'         => $row->jamMasuk,
                                'supplier_id'       => $sp->id,
                                'warehouse_id'      => $wh->id,
                                'biller_id'         => $bl->id,
                                'total'             => $total,
                                'product_discount'  => $product_discount,
                                'order_discount_id' => $order_discount_id,
                                'order_discount'    => $order_discount,
                                'total_discount'    => $total_discount,
                                'product_tax'       => $product_tax,
                                'order_tax_id'      => $order_tax_id,
                                'order_tax'         => $order_tax,
                                'total_tax'         => $total_tax,
                                'shipping'          => $this->sma->formatDecimal($shipping),
                                'grand_total'       => $grand_total

                            ];
                            $no   = $this->deliveries_smig_model->getDeliveriesSmigByDO($row->noDO);
                            if ($no != NULL) {
                                $data['updated_at']  = date('Y-m-d h:i:s'); //date('Ymd')
                                $this->db->trans_begin();
                                if ($this->deliveries_smig_model->updateDeliveriesSmig($no->id, $data, $products)) {
                                    $this->db->trans_commit();
                                    echo 'success update no do : ' . $row->noDO . ' data index : ' . ($index + 1) . '/' . $count . "\n";
                                } else {
                                    $this->db->trans_rollback();
                                    echo 'gagal update data no ' . ($index + 1) . '/' . $count . "\n";
                                }
                            } else {
                                $data['created_at']        = date('Y-m-d h:i:s'); //date('Ymd')
                                $data['status_penerimaan'] = 'delivering';
                                $this->db->trans_begin();
                                if ($this->deliveries_smig_model->addDeliveriesSmig($data, $products)) {
                                    $this->db->trans_commit();
                                    echo 'success insert no do : ' . $row->noDO . ' data index : ' . ($index + 1) . '/' . $count . "\n";
                                } else {
                                    $this->db->trans_rollback();
                                    echo 'gagal insert data no ' . ($index + 1) . '/' . $count . "\n";
                                }
                            }
                            $total = 0;
                        }
                        echo 'terdapat ' . $total_distributor . ' data distributor dan ' . $total_product . ' data product yang tidak terdapat pada FORCA POS dengan no plant : ' . $row_plant['plant'] . "\n";
                    }
                }
            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

    public function writeExcelSalesTransaction($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();
        try {
            $start_date = $start_date ?? "2020-01-01";
            $end_date = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $join = "(
                SELECT
                    CASE
                        WHEN
                            SUM( unit_quantity ) > SUM( sent_quantity ) 
                            AND SUM( sent_quantity ) = 0 THEN
                                'pending' 
                        WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                            AND SUM( sent_quantity ) > 0 THEN
                                'partial' 
                        WHEN SUM( unit_quantity ) <= SUM( sent_quantity ) 
                            AND SUM( sent_quantity ) > 0 THEN
                                'done' 
                        END AS delivery_status,
                    sma_sale_items.sale_id 
                FROM
                    sma_sale_items
                LEFT JOIN sma_sales s on s.id = sma_sale_items.sale_id
                WHERE
                s.date BETWEEN '" . $start_date . "' and '" . $end_date . "'
                GROUP BY
                    sma_sale_items.sale_id 
            ) sma_item";
            $this->db->select("
                sma_sales.id,
                DATE_FORMAT( sma_sales.date, '%Y-%m-%d' ) AS 'date',
                sma_sales.reference_no AS 'reference_no',
                biller AS 'distributor',
                sma_sales.customer AS 'customer',
                sma_users.phone AS 'phone',
                IF
                (
                    sma_sales.client_id = 'aksestoko',
                    CONCAT( sma_users.first_name, ' ', sma_users.last_name, ' (AksesToko)' ),
                    CONCAT( sma_users.first_name, ' ', sma_users.last_name ) 
                ) AS 'created_by',
                sma_sales.sale_status AS 'sale_status',
                CAST( sma_sales.grand_total AS UNSIGNED ) AS 'grand_total',
                CAST( sma_sales.paid AS UNSIGNED ) AS 'paid',
                CAST( ( sma_sales.grand_total - sma_sales.paid ) AS UNSIGNED ) AS 'balance',
                sma_sales.payment_status AS 'payment_Status',
                delivery_status AS 'delivery_status',
                sma_sales.device_id `delivery_address`,
                sma_products.NAME AS 'product_name',
                CAST( sma_sale_items.quantity AS UNSIGNED ) AS 'quantity',
                CAST( sma_sale_items.subtotal AS UNSIGNED ) AS 'sub_total',
                CAST( sma_sales.grand_total AS UNSIGNED ) AS 'grand_total',
                sma_companies.address AS 'alamat',
                REPLACE ( sma_companies.cf1, 'IDC-', '' ) AS 'ibk' ,
                sma_warehouses.name as `gudang`,
                sma_sales.biller_id as `biller_id`,
                sma_purchases.payment_method,
                sma_brands.name as `brand_name`,
                sma_companies.city as `city_cust`
            ")
                ->from('sales')
                ->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left')
                ->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left')
                ->join($this->db->dbprefix('products'), $this->db->dbprefix('sale_items') . '.product_id=sma_products.id', 'left')
                ->join($this->db->dbprefix('brands'), $this->db->dbprefix('products') . '.brand=sma_brands.id', 'left')
                ->join($this->db->dbprefix('companies'), $this->db->dbprefix('users') . '.company_id=sma_companies.id', 'left')
                ->join($this->db->dbprefix('warehouses'), $this->db->dbprefix('warehouses') . '.id=sma_sales.warehouse_id', 'left')
                ->join('sma_purchases', '( `sma_sales`.`reference_no` = `sma_purchases`.`cf1` AND `sma_sales`.`biller_id` = `sma_purchases`.`supplier_id` )');
            $this->db->join($join, 'sma_item.sale_id = sma_sales.id', 'left');
            $this->db->where('sma_sales.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            $this->db->where('sma_sales.is_deleted IS NULL');
            $this->db->where("sma_sales.biller_id != 6 ");
            $this->db->where("sma_sales.client_id = 'aksestoko'");

            $q = $this->db->get();
            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $data = $q->result();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('sale_transaction'))
                ->SetCellValue('A1', lang("date"))
                ->SetCellValue('B1', lang("ibk"))
                ->SetCellValue('C1', lang("customer"))
                ->SetCellValue('D1', lang("alamat"))
                ->SetCellValue('E1', lang("phone"))
                ->SetCellValue('F1', lang("distributor"))
                ->SetCellValue('G1', lang("warehouse"))
                ->SetCellValue('H1', lang("reference_no"))
                ->SetCellValue('I1', lang("created_by"))
                ->SetCellValue('J1', lang("do_status"))
                ->SetCellValue('K1', lang("product"))
                ->SetCellValue('L1', lang("brand"))
                ->SetCellValue('M1', lang("quantity"))
                ->SetCellValue('N1', lang("grand_total"))
                ->SetCellValue('O1', lang("sale_status"))
                ->SetCellValue('P1', lang("payment_method"))
                ->SetCellValue('Q1', lang("delivery_address"))
                ->SetCellValue('R1', lang("city"));
            $row = 2;

            foreach ($data as $data_row) {
                $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode("MM/DD/YYYY");
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->date);

                $sheet->SetCellValue('A' . $row, $date)
                    ->SetCellValue('B' . $row, $data_row->ibk)
                    ->SetCellValue('C' . $row, $data_row->customer)
                    ->SetCellValue('D' . $row, $data_row->alamat)
                    ->SetCellValue('E' . $row, $data_row->phone)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->gudang)
                    ->SetCellValue('H' . $row, $data_row->reference_no . '-' . $data_row->biller_id)
                    ->SetCellValue('I' . $row, $data_row->created_by)
                    ->SetCellValue('J' . $row, $data_row->delivery_status)
                    ->SetCellValue('K' . $row, $data_row->product_name)
                    ->SetCellValue('L' . $row, $data_row->brand_name)
                    ->SetCellValue('M' . $row, $data_row->quantity)
                    ->SetCellValue('N' . $row, $data_row->grand_total)
                    ->SetCellValue('O' . $row, $data_row->sale_status)
                    ->SetCellValue('P' . $row, lang($data_row->payment_method))
                    ->SetCellValue('Q' . $row, $data_row->delivery_address)
                    ->SetCellValue('R' . $row, $data_row->city_cust);
                $row++;
            }
            $filename = 'sales_transaction_' . date("Y-m-d_H") . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);

            $this->site->insertOrUpdateDocuments([
                'name' => "Sales Transaction",
                'filename' => $filename,
                'url' => $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }
    public function updateDeliveries()
    {
        ini_set('memory_limit', '2048M');
        // $this->load->model('sales_model');
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->load->model('integration_model', 'integration');
        $param_date = new DateTime(date('Y-m-d'));;
        $deliveries = $this->at_sale->getDeliveryScheduler($param_date->modify('-3 day')->format('Y-m-d'));
        foreach ($deliveries as $key => $delivery) {
            $this->db->trans_begin();
            try {
                $sale = $this->at_sale->getSalesById($delivery->sale_id);
                $purchase = $this->at_sale->getPurchasesByRefNo($sale->reference_no, $sale->biller_id);
                if ($purchase) {
                    $deliveryItems = $this->at_sale->getDeliveryItemsByDeliveryId($delivery->id);

                    $product_code = [];
                    $quantity_received = [];
                    $delivery_item_id = [];
                    $good = [];
                    $bad = [];
                    foreach ($deliveryItems as $keyDi => $deliveryitem) {
                        $product_code[] = $deliveryitem->product_code;
                        $quantity_received[] = $deliveryitem->quantity_sent;
                        $delivery_item_id[] = $deliveryitem->product_id;
                        $good[] = $deliveryitem->good_quantity;
                        $bad[] = $deliveryitem->bad_quantity;
                    }
                    $data = [
                        'purchase_id' => $purchase->id,
                        'product_code' => $product_code,
                        'quantity_received' => $quantity_received,
                        'do_ref' => $delivery->do_reference_no,
                        'do_id' => $delivery->id,
                        'delivery_item_id' => $delivery_item_id,
                        'good' => $good,
                        'bad' => $bad,
                        'note' => "Diterima secara otomatis pada " . date('Y-m-d H:i:s'),
                        'file' => '',
                    ];
                    if ($sale->sale_type == 'booking') {
                        $confirm = $this->at_purchase->confirmReceivedBooking($data, 1, $delivery->sale_id);
                    } else {
                        $confirm = $this->at_purchase->confirmReceived($data, 1, $delivery->sale_id);
                    }
                    if (!$confirm) {
                        throw new \Exception("Gagal konfirmasi penerimaan");
                    }

                    //get newest delivery after update (confirmReceived)
                    $delivery = $this->at_sale->getDeliveryByID($delivery->id);
                    $deliveryItems = $this->at_sale->getDeliveryItemsByDeliveryId($delivery->id);
                    $supplier = $this->at_site->getCompanyByID($sale->biller_id);
                    $user = $this->site->findUserByCompanyId($sale->customer_id, 10);
                    $purchase = $this->site->getPurchaseByID($purchase->id);

                    if ($sale->sale_type == 'booking') {
                        if ($this->site->checkAutoClose($sale->id)) {
                            $this->at_sale->closeSale($sale->id);
                        }
                    }

                    if ($purchase->payment_method == 'kredit_pro' && $purchase->status == 'received') {
                        $attachment = $this->generatePDFDeliv($sale);
                        $pathPDFInv = $this->generatePDFInv($sale, $purchase);
                        array_push($attachment, $pathPDFInv);
                        $this->at_sale->send_email_delivery($purchase->id, $sale, $attachment);
                    }

                    if ($this->integration->isIntegrated($supplier->cf2)) {
                        $response = $this->integration->confirm_received_integration($supplier->cf2, trim($user->username), $sale, $delivery, $deliveryItems);
                        if (!$response) {
                            throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                        }
                    }

                    echo "\n\n Delivery ID -> " . $delivery->id . " : Success";
                } else {
                    echo "\n\n Delivery ID -> " . $delivery->id . " : Not AksesToko Order";
                }
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                echo "\n\n Delivery ID -> " . $delivery->id . " : " . $th->getMessage();
            }
        }
        echo "\n\nDone";
    }

    public function generatePDFDeliv($sales)
    {
        $path = [];
        $this->load->model('sales_model');
        $deliveries = $this->sales_model->getAllDeliveryBySaleID($sales->id);
        // print_r($deliveries);die;
        foreach ($deliveries as $key => $deli) {
            $this->data['delivery'] = $deli;
            // $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            $this->data['biller'] = $this->site->getCompanyByID($sales->biller_id);
            $this->data['rows'] = $this->sales_model->getDeliveryItemsByDeliveryId($deli->id);
            $this->data['user'] = $this->site->getUser($deli->created_by);
            $name = lang("delivery") . "_" . str_replace('/', '_', $deli->do_reference_no) . "-" . $sales->biller_id . ".pdf";
            $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $path[] = $this->sma->generate_pdf($html, $name, 'S');
        }
        return $path;
    }

    public function generatePDFInv($inv, $purchase)
    {
        $this->load->model('aksestoko/at_site_model', 'at_site');
        // $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($inv->id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id, $inv->biller_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($inv->id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['po'] = $purchase;
        $name = "INVOICE_-_" . str_replace('/', '_', $inv->reference_no) . "-" . $inv->biller_id . ".pdf";
        $html = $this->load->view($this->theme . 'sales/sale_pdf_kredit_pro', $this->data, true);
        // var_dump($html);die;
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        return $this->sma->generate_pdf($html, $name, 'S', $this->data['biller']->invoice_footer);
    }

    public function sendInvoiceToKreditPro($sale_id, $force_send = 0)
    {
        $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $sale = $this->at_sale->getSalesById($sale_id);
        $purchase = $this->at_sale->getPurchasesByRefNo($sale->reference_no, $sale->biller_id);
        if ($force_send == 1 || ($purchase->payment_method == 'kredit_pro' && $purchase->third_party_sent_at == null && $purchase->status == 'received')) {
            $attachment = $this->generatePDFDeliv($sale);
            $pathPDFInv = $this->generatePDFInv($sale, $purchase);
            array_push($attachment, $pathPDFInv);
            $this->at_sale->send_email_delivery($purchase->id, $sale, $attachment);
            echo "Success";
        } else {
            echo "Failed";
        }
    }

    /* public function writeExcelSalesTransaction($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();
        try {
            $start_date = $start_date ?? "2020-01-01";
            $end_date = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $this->db->select('*')->from('sma_v_sales_aksestoko');

            $this->db->where('tanggal_transaksi BETWEEN "'.$start_date. '" and "'. $end_date.'"');

            $q = $this->db->get();

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");                
            }
            
            $data = $q->result();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('sale_transaction'))
                    ->SetCellValue('A1', lang("date"))
                    ->SetCellValue('B1', lang("ibk"))
                    ->SetCellValue('C1', lang("customer"))
                    ->SetCellValue('D1', lang("alamat"))
                    ->SetCellValue('E1', lang("phone"))
                    ->SetCellValue('F1', lang("distributor"))
                    ->SetCellValue('G1', lang("warehouse"))
                    ->SetCellValue('H1', lang("reference_no"))
                    ->SetCellValue('I1', lang("created_by"))
                    ->SetCellValue('J1', lang("sale_status"))
                    ->SetCellValue('K1', lang("product"))
                    ->SetCellValue('L1', lang("quantity"))
                    ->SetCellValue('M1', lang("grand_total"));
            $row = 2;
            
            foreach ($data as $data_row) {
                $sheet->getStyle('A'.$row)->getNumberFormat()->setFormatCode("MM/DD/YYYY");
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->tanggal_transaksi);

                $sheet->SetCellValue('A' . $row, $date)
                        ->SetCellValue('B' . $row, $data_row->ibk)
                        ->SetCellValue('C' . $row, $data_row->nama_toko)
                        ->SetCellValue('D' . $row, $data_row->alamat)
                        ->SetCellValue('E' . $row, $data_row->phone)
                        ->SetCellValue('F' . $row, $data_row->distributor)
                        ->SetCellValue('G' . $row, $data_row->gudang)
                        ->SetCellValue('H' . $row, $data_row->no_penjualan)
                        ->SetCellValue('I' . $row, $data_row->created_by)
                        ->SetCellValue('J' . $row, $data_row->sale_status)
                        ->SetCellValue('K' . $row, $data_row->nama_produk)
                        ->SetCellValue('L' . $row, $data_row->quantity)
                        ->SetCellValue('M' . $row, $data_row->grand_total);
                $row++;
            }
            $filename = 'sales_transaction_'.date("Y-m-d_H");
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray( ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER] );
            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer->save(FCPATH."/assets/documents/".$filename.".xlsx");
            $this->site->insertOrUpdateDocuments([
                'name' => "Sales Transaction",
                'filename' => $filename.".xlsx",
                'url' => "/assets/documents/".$filename.".xlsx",
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);

            $this->db->trans_commit();

        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    } */

    public function writeExcelSalesDelivered($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();

        try {
            $start_date = $start_date ?? "2020-01-01";
            $end_date = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $join = "(
                SELECT
                CASE
                    WHEN
                        SUM( unit_quantity ) > SUM( sent_quantity ) 
                        AND SUM( sent_quantity ) = 0 THEN
                            'pending' 
                            WHEN SUM( unit_quantity ) > SUM( sent_quantity ) 
                            AND SUM( sent_quantity ) > 0 THEN
                                'partial' 
                                WHEN SUM( unit_quantity ) <= SUM( sent_quantity ) 
                                AND SUM( sent_quantity ) > 0 THEN
                                    'done' 
                                    END AS delivery_status,
                                sale_id 
                            FROM
                                sma_sale_items 
                            GROUP BY
                                sma_sale_items.sale_id 
                            ) sma_item";
            $this->db->select("
                sma_sales.id,
                DATE_FORMAT( sma_sales.date, '%Y-%m-%d' ) AS 'date',
                sma_sales.reference_no AS 'reference_no',
                biller AS 'distributor',
                sma_sales.customer AS 'customer',
                sma_users.phone AS 'phone',
                IF
                (
                    sma_sales.client_id = 'aksestoko',
                    CONCAT( sma_users.first_name, ' ', sma_users.last_name, ' (AksesToko)' ),
                    CONCAT( sma_users.first_name, ' ', sma_users.last_name ) 
                ) AS 'created_by',
                sma_sales.sale_status AS 'sale_status',
                CAST( sma_sales.grand_total AS UNSIGNED ) AS 'grand_total',
                CAST( sma_sales.paid AS UNSIGNED ) AS 'paid',
                CAST( ( sma_sales.grand_total - sma_sales.paid ) AS UNSIGNED ) AS 'balance',
                sma_sales.payment_status AS 'payment_Status',
                delivery_status AS 'delivery_status',
                sma_products.NAME AS 'product_name',
                CAST( sma_sale_items.quantity AS UNSIGNED ) AS 'quantity',
                CAST( sma_sale_items.subtotal AS UNSIGNED ) AS 'sub_total',
                CAST( sma_sales.grand_total AS UNSIGNED ) AS 'grand_total',
                sma_companies.address AS 'alamat',
                REPLACE ( sma_companies.cf1, 'IDC-', '' ) AS 'ibk' 
            ")
                ->from('sales')
                ->join($this->db->dbprefix('users'), $this->db->dbprefix('users') . '.id=sales.created_by', 'left')
                ->join($this->db->dbprefix('sale_items'), $this->db->dbprefix('sale_items') . '.sale_id=sales.id', 'left')
                ->join($this->db->dbprefix('products'), $this->db->dbprefix('sale_items') . '.product_id=sma_products.id', 'left')
                ->join($this->db->dbprefix('companies'), $this->db->dbprefix('users') . '.company_id=sma_companies.id', 'left');
            $this->db->join($join, 'sma_item.sale_id = sma_sales.id', 'left');
            $this->db->where('sma_sales.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            $this->db->where('sma_sales.is_deleted IS NULL');
            $this->db->where("sma_item.delivery_status = 'done' ");
            $this->db->where("sma_sales.biller_id != 6 ");
            $this->db->where("sma_sales.client_id = 'aksestoko'");

            $q = $this->db->get();

            // var_dump($this->db->last_query());die;

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $data = $q->result();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('sale_delivered'))
                //->SetCellValue('A1', lang("transaction_id"))
                ->SetCellValue('A1', lang("date"))
                ->SetCellValue('B1', lang("reference_no"))
                ->SetCellValue('C1', lang("distributor"))
                ->SetCellValue('D1', lang("customer"))
                ->SetCellValue('E1', lang("phone"))
                ->SetCellValue('F1', lang("created_by"))
                ->SetCellValue('G1', lang("sale_status"))
                ->SetCellValue('H1', lang("grand_total"))
                ->SetCellValue('I1', lang("total_paid"))
                ->SetCellValue('J1', lang("balance"))
                ->SetCellValue('K1', lang("payment_status"))
                ->SetCellValue('L1', lang("delivery_status"))
                ->SetCellValue('M1', lang("product"))
                ->SetCellValue('N1', lang("qty"))
                ->SetCellValue('O1', lang("sub_total"))
                ->SetCellValue('P1', lang("grand_total"))
                ->SetCellValue('Q1', lang("alamat"))
                ->SetCellValue('R1', lang("ibk"));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->getStyle('A' . $row)
                    ->getNumberFormat()
                    ->setFormatCode("MM/DD/YYYY");

                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->date);

                $sheet
                    ->SetCellValue('A' . $row, $date)
                    ->SetCellValue('B' . $row, $data_row->reference_no)
                    ->SetCellValue('C' . $row, $data_row->distributor)
                    ->SetCellValue('D' . $row, $data_row->customer)
                    ->SetCellValue('E' . $row, $data_row->phone)
                    ->SetCellValue('F' . $row, $data_row->created_by)
                    ->SetCellValue('G' . $row, $data_row->sale_status)
                    ->SetCellValue('H' . $row, $data_row->grand_total)
                    ->SetCellValue('I' . $row, $data_row->paid)
                    ->SetCellValue('J' . $row, $data_row->balance)
                    ->SetCellValue('K' . $row, $data_row->payment_Status)
                    ->SetCellValue('L' . $row, $data_row->delivery_status)
                    ->SetCellValue('M' . $row, $data_row->product_name)
                    ->SetCellValue('N' . $row, $data_row->quantity)
                    ->SetCellValue('O' . $row, $data_row->sub_total)
                    ->SetCellValue('P' . $row, $data_row->grand_total)
                    ->SetCellValue('Q' . $row, $data_row->alamat)
                    ->SetCellValue('R' . $row, $data_row->ibk);
                $row++;
            }
            $filename = 'sales_delivered_' . date("Y-m-d_H") . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);

            $this->site->insertOrUpdateDocuments([
                'name' => "Sales Delivered",
                'filename' => $filename,
                'url' =>  $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }

    public function writeExcelUserActivation($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();

        try {
            $start_date = $start_date ?? "2020-01-01";
            $end_date = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $this->db->select('sma_v_aktivasi_aksestoko.*')->from('sma_v_aktivasi_aksestoko');

            $this->db->where('sma_v_aktivasi_aksestoko.tanggal_aktivasi BETWEEN "' . $start_date . '" and "' . $end_date . '"');

            $this->db->order_by('sma_v_aktivasi_aksestoko.tanggal_aktivasi ASC');

            $q = $this->db->get();

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $data = $q->result();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('user_activation'))
                ->SetCellValue('A1', lang("date"))
                ->SetCellValue('B1', lang("ibk"))
                ->SetCellValue('C1', lang("nama_toko"))
                ->SetCellValue('D1', lang("alamat"))
                ->SetCellValue('E1', lang("phone"))
                ->SetCellValue('F1', lang("distributor"))
                ->SetCellValue('G1', lang("provinsi"))
                ->SetCellValue('H1', lang("distributor"));
            $row = 2;
            foreach ($data as $data_row) {
                $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode("MM/DD/YYYY");
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->tanggal_aktivasi);
                $sheet->SetCellValue('A' . $row, $date)
                    ->SetCellValue('B' . $row, $data_row->idbk)
                    ->SetCellValue('C' . $row, $data_row->nama_toko)
                    ->SetCellValue('D' . $row, $data_row->alamat)
                    ->SetCellValue('E' . $row, $data_row->phone)
                    ->SetCellValue('F' . $row, $data_row->distributor)
                    ->SetCellValue('G' . $row, $data_row->provinsi)
                    ->SetCellValue('H' . $row, $data_row->dist);
                $row++;
            }
            $filename = 'user_activation_' . date("Y-m-d_H") . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);
            
            $this->site->insertOrUpdateDocuments([
                'name' => "User Activation",
                'filename' => $filename,
                'url' => $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }

    public function writeExcelAllSales($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '4096M');
        ob_clean();
        $this->db->trans_begin();

        try {
            $start_date = $start_date ?? date('Y-m-d');
            $end_date = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $this->db->select('*')->from('sma_v_all_sales');

            $this->db->where('tanggal BETWEEN "' . $start_date . '" and "' . $end_date . '"');

            // $this->db->limit('19');

            $q = $this->db->get();

            // var_dump($this->db->error());die;

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $data = [];
            foreach ($q->result_array() as $d) {
                $d['tanggal'] = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($d['tanggal']);
                $data[] = $d;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('all_sales'))
                ->SetCellValue('A1', lang("tanggal"))
                ->SetCellValue('B1', lang("reference_no"))
                ->SetCellValue('C1', lang("Nama Toko"))
                ->SetCellValue('D1', lang("Alamat Toko"))
                ->SetCellValue('E1', lang("Telepon Toko"))
                ->SetCellValue('F1', lang("ID Toko"))
                ->SetCellValue('G1', lang("Nama Distributor"))
                ->SetCellValue('H1', lang("Alamat Distributor"))
                ->SetCellValue('I1', lang("Provinsi Distributor"))
                ->SetCellValue('J1', lang("Telepon Distributor"))
                ->SetCellValue('K1', lang("ID Distributor"))
                ->SetCellValue('L1', lang("Kode Produk"))
                ->SetCellValue('M1', lang("Nama Produk"))
                ->SetCellValue('N1', lang("quantity"))
                ->SetCellValue('O1', lang("UOM"))
                ->SetCellValue('P1', lang("Unit Price"))
                ->SetCellValue('Q1', lang("Price Total"))
                ->SetCellValue('R1', lang("Status Sales"))
                ->SetCellValue('S1', lang("Created By"));

            $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode("MM/DD/YYYY");
            $sheet->fromArray($data, null, 'A2');

            $filename = 'all_sales_' . date("Y-m-d_H") . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);
            $this->site->insertOrUpdateDocuments([
                'name' => "All Sales",
                'filename' => $filename,
                'url' => $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }

    public function writeExcelItemDelivered($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();

        try {
            $start_date = $start_date ?? "2020-01-01";
            $end_date = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $this->db->select("sma_sales.date AS sale_date,
                                CONCAT(sma_sales.reference_no, '-', sma_sales.biller_id) AS sale_no,
                                (
                                IF
                                    (
                                        sma_products.supplier1_part_no != '',
                                        sma_products.supplier1_part_no,
                                    IF
                                        (
                                            distributor.cf1 IS NOT NULL,
                                            distributor.cf1,
                                        IF
                                            ( distributor.cf2 IS NOT NULL, distributor.cf2, distributor.cf3 ) 
                                        ) 
                                    ) 
                                ) AS distributor_code,
                                distributor.company AS distributor_name,
                                sma_warehouses.code AS warehouse_code,
                                sma_warehouses.name as warehouse_name,
                                REPLACE(customer.cf1 , 'IDC-', '') AS customer_code,
                                customer.company AS customer_name,
                                sma_sales.sale_status AS sale_status,
                                sma_sales.grand_total AS grand_total,
                                sma_sales.paid AS total_paid,
                                sma_sales.payment_status AS payment_status,
                                sma_products.code AS product_code,
                                sma_products.name AS product_name,
                                sma_delivery_items.quantity_ordered AS quantity_ordered,
                                CONCAT(sma_users.first_name, ' ', sma_users.last_name) AS created_by,
                                sma_deliveries.date AS delivery_date,
                                CONCAT(sma_deliveries.do_reference_no, '-', sma_sales.biller_id) AS delivery_no,
                                ROUND ( sma_delivery_items.quantity_sent  ) AS quantity_sent,
                                sma_deliveries.status AS delivery_status,
                                CONCAT(deliv_users.first_name, ' ', deliv_users.last_name)  AS delivery_created_by")
                ->from('sma_delivery_items')
                ->join('sma_deliveries', 'sma_delivery_items.delivery_id = sma_deliveries.id')
                ->join('sma_sales', 'sma_deliveries.sale_id = sma_sales.id')
                ->join('sma_companies distributor', 'distributor.id = sma_sales.biller_id')
                ->join('sma_warehouses', 'sma_sales.warehouse_id = sma_warehouses.id')
                ->join('sma_users', 'sma_sales.created_by = sma_users.id')
                ->join('sma_companies customer', 'customer.id = sma_users.company_id')
                ->join('sma_products', 'sma_delivery_items.product_id = sma_products.id')
                // ->join('sma_delivery_items returnItem', 'returnItem.delivery_items_id = sma_delivery_items.id', 'left')
                ->join('sma_users deliv_users', 'sma_deliveries.created_by = deliv_users.id')
                ->where('sma_sales.client_id = \'aksestoko\'');

            $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');

            // $this->db->limit('19');

            $q = $this->db->get();

            // var_dump($this->db->error());die;

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $data = $q->result();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('item_delivered'))
                ->setCellValue('A1', lang("sale_date"))
                ->setCellValue('B1', lang("sale_reference_no"))
                ->setCellValue('C1', lang("distributor_code"))
                ->setCellValue('D1', lang("distributor"))
                ->setCellValue('E1', lang("warehouse_code"))
                ->setCellValue('F1', lang("warehouse"))
                ->setCellValue('G1', lang("customer_code"))
                ->setCellValue('H1', lang("customer"))
                ->setCellValue('I1', lang("sale_status"))
                ->setCellValue('J1', lang("grand_total"))
                ->setCellValue('K1', lang("total_paid"))
                ->setCellValue('L1', lang("payment_status"))
                ->setCellValue('M1', lang("product_code"))
                ->setCellValue('N1', lang("product"))
                ->setCellValue('O1', lang("total_quantity"))
                ->setCellValue('P1', lang("created_by"))
                ->setCellValue('Q1', lang("delivery_date"))
                ->setCellValue('R1', lang("do_reference_no"))
                ->setCellValue('S1', lang("quantity_sent"))
                ->setCellValue('T1', lang("delivery_status"))
                ->setCellValue('U1', lang("created_by"));

            $row = 2;
            foreach ($data as $data_row) {
                $sheet->getStyle('A' . $row)
                    ->getNumberFormat()
                    ->setFormatCode("MM/DD/YYYY");
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->sale_date);

                $sheet->setCellValue('A' . $row, $date)
                    ->setCellValue('B' . $row, $data_row->sale_no)
                    ->setCellValue('C' . $row, $data_row->distributor_code)
                    ->setCellValue('D' . $row, $data_row->distributor_name)
                    ->setCellValue('E' . $row, $data_row->warehouse_code)
                    ->setCellValue('F' . $row, $data_row->warehouse_name)
                    ->setCellValue('G' . $row, $data_row->customer_code)
                    ->setCellValue('H' . $row, $data_row->customer_name)
                    ->setCellValue('I' . $row, $data_row->sale_status)
                    ->setCellValue('J' . $row, $data_row->grand_total)
                    ->setCellValue('K' . $row, $data_row->total_paid)
                    ->setCellValue('L' . $row, $data_row->payment_status)
                    ->setCellValue('M' . $row, $data_row->product_code)
                    ->setCellValue('N' . $row, $data_row->product_name)
                    ->setCellValue('O' . $row, $data_row->quantity_ordered)
                    ->setCellValue('P' . $row, $data_row->created_by)
                    ->setCellValue('Q' . $row, $data_row->delivery_date)
                    ->setCellValue('R' . $row, $data_row->delivery_no)
                    ->setCellValue('S' . $row, $data_row->quantity_sent)
                    ->setCellValue('T' . $row, $data_row->delivery_status)
                    ->setCellValue('U' . $row, $data_row->delivery_created_by);
                $row++;
            }

            $filename = 'item_delivered_' . date("Y-m-d_H") . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);
            $this->site->insertOrUpdateDocuments([
                'name' => "Item Delivered",
                'filename' => $filename,
                'url' => $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }

    public function writeExceDelivery($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();

        try {
            $start_date   = $start_date ?? date("Y-m-d", strtotime('-1 month'));
            $end_date     = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $this->db->select("sma_sales.reference_no AS NO_TRANSAKSI,
                               sma_deliveries.do_reference_no AS NO_PENGIRIMAN,
                               IFNULL(customer.cf1, '') AS KD_CUSTOMER,
                               IFNULL(customer.cf2, '') AS KD_TOKO_SAP,
                               IFNULL(UPPER(customer.company), '') AS NM_TOKO_SAP,
                               IFNULL(UPPER(customer.address), '') AS ALAMAT_TOKO_SAP,
                               IFNULL(UPPER(customer.state), '') AS KECAMATAN,
                               IFNULL(UPPER(customer.city), '') AS NM_DISTRIK,
                               IFNULL(UPPER(customer.country), '') AS PROVINSI,
                               sma_warehouses.code AS KD_GUDANG,
                               UPPER(sma_warehouses.name) AS NM_GUDANG,
                               sma_sales.customer_id AS KD_TUJUAN,
                               UPPER(sma_sales.customer) AS NM_TUJUAN,
                               (IF(sma_products.supplier1_part_no != '', sma_products.supplier1_part_no,
                               IF(sma_companies.cf1 IS NOT NULL,sma_companies.cf1,
                               IF(sma_companies.cf2 IS NOT NULL, sma_companies.cf2, sma_companies.cf3)))) as KD_DISTRIBUTOR,
                               UPPER(sma_companies.company) AS NM_DISTRIBUTOR,
                               sma_sales.date AS TGL_TRANSAKSI,
                               IFNULL(sma_deliveries.is_deleted, 0) AS DELETE_MARK,
                               sma_deliveries.date AS TGL_PENGIRIMAN, 
                               '' AS NO_POL, 
                               sma_deliveries.sale_id AS NO_TRANSAKSI_DTL,
                               sma_deliveries.id AS NO_PENGIRIMAN_DTL,
                               sma_delivery_items.product_code AS KD_PRODUK,
                               UPPER(sma_delivery_items.product_name) AS NM_MATERIAL,
                               ROUND (sma_delivery_items.quantity_sent + IF(returnItem.quantity_sent is null, 0, returnItem.quantity_sent )) AS QTY,
                               ROUND (sma_sale_items.unit_price) AS HARGA,
                               sma_delivery_items.product_unit_code AS SATUAN,
                               UPPER(sma_brands.name) AS OPCO,
                               UPPER(sma_deliveries.status) AS STATUS,
                               IF(sma_sales.client_id = 'aksestoko', 1, 0) AS AKSESTOKO_MARK")
                ->from('sma_delivery_items')
                ->join('sma_deliveries', 'sma_delivery_items.delivery_id = sma_deliveries.id', 'left')
                ->join('sma_sale_items', 'sma_sale_items.sale_id = sma_delivery_items.sale_id AND sma_sale_items.product_id = sma_delivery_items.product_id', 'left')
                ->join('sma_delivery_items AS returnItem', 'returnItem.delivery_items_id = sma_delivery_items.id', 'left')
                ->join('sma_sales', 'sma_sale_items.sale_id = sma_sales.id', 'left')
                ->join('sma_products', 'sma_sale_items.product_id = sma_products.id', 'left')
                ->join('sma_brands', 'sma_products.brand = sma_brands.id', 'left')
                ->join('sma_companies', 'sma_sales.company_id = sma_companies.id', 'left')
                ->join('sma_companies AS customer', 'sma_sales.customer_id = customer.id', 'left')
                ->join('sma_warehouses', 'sma_sales.warehouse_id = sma_warehouses.id', 'left')
                ->where("(sma_deliveries.status = 'delivered' OR sma_deliveries.status = 'delivering') 
                          AND ( sma_delivery_items.product_name LIKE '%SEMEN%' OR sma_delivery_items.product_name LIKE '%DYNAMIX%' OR sma_delivery_items.product_name LIKE '%ANDALAS%') 
                          AND IF (sma_products.supplier1_part_no != '', sma_products.supplier1_part_no != '',
                          IF(sma_companies.cf1 IS NOT NULL,sma_companies.cf1 IS NOT NULL,
                          IF(sma_companies.cf2 IS NOT NULL,sma_companies.cf2 IS NOT NULL, sma_companies.cf3 IS NOT NULL)))")
                ->where("date_format(sma_deliveries.date, '%Y-%m-%d') >= ", $start_date)
                ->where("date_format(sma_deliveries.date, '%Y-%m-%d') <= ", $end_date);

            $q = $this->db->get();

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $data           = $q->result();
            $spreadsheet    = new Spreadsheet();
            $sheet          = $spreadsheet->setActiveSheetIndex(0);
            $sheet->setTitle(lang('Delivery'))
                ->setCellValue('A1', lang("NO_TRANSAKSI"))
                ->setCellValue('B1', lang("NO_PENGIRIMAN"))
                ->setCellValue('C1', lang("KD_CUSTOMER"))
                ->setCellValue('D1', lang("KD_TOKO_SAP"))
                ->setCellValue('E1', lang("NM_TOKO_SAP"))
                ->setCellValue('F1', lang("ALAMAT_TOKO_SAP"))
                ->setCellValue('G1', lang("KECAMATAN"))
                ->setCellValue('H1', lang("NM_DISTRIK"))
                ->setCellValue('I1', lang("PROVINSI"))
                ->setCellValue('J1', lang("KD_GUDANG"))
                ->setCellValue('K1', lang("NM_GUDANG"))
                ->setCellValue('L1', lang("KD_TUJUAN"))
                ->setCellValue('M1', lang("NM_TUJUAN"))
                ->setCellValue('N1', lang("KD_DISTRIBUTOR"))
                ->setCellValue('O1', lang("NM_DISTRIBUTOR"))
                ->setCellValue('P1', lang("TGL_TRANSAKSI"))
                ->setCellValue('Q1', lang("DELETE_MARK"))
                ->setCellValue('R1', lang("TGL_PENGIRIMAN"))
                ->setCellValue('S1', lang("NO_POL"))
                ->setCellValue('T1', lang("NO_TRANSAKSI_DTL"))
                ->setCellValue('U1', lang("NO_PENGIRIMAN_DTL"))
                ->setCellValue('V1', lang("KD_PRODUK"))
                ->setCellValue('W1', lang("NM_MATERIAL"))
                ->setCellValue('X1', lang("QTY"))
                ->setCellValue('Y1', lang("HARGA"))
                ->setCellValue('Z1', lang("SATUAN"))
                ->setCellValue('AA1', lang("OPCO"))
                ->setCellValue('AB1', lang("STATUS"))
                ->setCellValue('AC1', lang("AKSESTOKO_MARK"));

            $row = 2;
            foreach ($data as $data_row) {
                $sheet->getStyle('P' . $row)
                    ->getNumberFormat()
                    ->setFormatCode("DD/MM/YYYY");
                $TGL_TRANSAKSI = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->TGL_TRANSAKSI);
                $sheet->getStyle('R' . $row)
                    ->getNumberFormat()
                    ->setFormatCode("DD/MM/YYYY");
                $TGL_PENGIRIMAN = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($data_row->TGL_PENGIRIMAN);

                $KD_CUSTOMER = explode("IDC-", $data_row->KD_CUSTOMER);
                $KD_TOKO_SAP = explode("SAP-", $data_row->KD_TOKO_SAP);

                $sheet->setCellValue('A' . $row, $data_row->NO_TRANSAKSI)
                    ->setCellValue('B' . $row, $data_row->NO_PENGIRIMAN)
                    ->setCellValue('C' . $row, $KD_CUSTOMER[1])
                    ->setCellValue('D' . $row, $KD_TOKO_SAP[1])
                    ->setCellValue('E' . $row, $data_row->NM_TOKO_SAP)
                    ->setCellValue('F' . $row, $data_row->ALAMAT_TOKO_SAP)
                    ->setCellValue('G' . $row, $data_row->KECAMATAN)
                    ->setCellValue('H' . $row, $data_row->NM_DISTRIK)
                    ->setCellValue('I' . $row, $data_row->PROVINSI)
                    ->setCellValue('J' . $row, $data_row->KD_GUDANG)
                    ->setCellValue('K' . $row, $data_row->NM_GUDANG)
                    ->setCellValue('L' . $row, $data_row->KD_TUJUAN)
                    ->setCellValue('M' . $row, $data_row->NM_TUJUAN)
                    ->setCellValue('N' . $row, $data_row->KD_DISTRIBUTOR)
                    ->setCellValue('O' . $row, $data_row->NM_DISTRIBUTOR)
                    ->setCellValue('P' . $row, $TGL_TRANSAKSI)
                    ->setCellValue('Q' . $row, $data_row->DELETE_MARK)
                    ->setCellValue('R' . $row, $TGL_PENGIRIMAN)
                    ->setCellValue('S' . $row, $data_row->NO_POL)
                    ->setCellValue('T' . $row, $data_row->NO_TRANSAKSI_DTL)
                    ->setCellValue('U' . $row, $data_row->NO_PENGIRIMAN_DTL)
                    ->setCellValue('V' . $row, $data_row->KD_PRODUK)
                    ->setCellValue('W' . $row, $data_row->NM_MATERIAL)
                    ->setCellValue('X' . $row, $data_row->QTY)
                    ->setCellValue('Y' . $row, $data_row->HARGA)
                    ->setCellValue('Z' . $row, $data_row->SATUAN)
                    ->setCellValue('AA' . $row, $data_row->OPCO)
                    ->setCellValue('AB' . $row, $data_row->STATUS)
                    ->setCellValue('AC' . $row, $data_row->AKSESTOKO_MARK);
                $row++;
            }

            $filename = 'Delivery ' . date('dmY', strtotime($start_date)) . ' - ' . date('dmY', strtotime($end_date)) . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);
            $this->site->insertOrUpdateDocuments([
                'name'       => "Delivery",
                'filename'   => $filename,
                'url' => $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }

    public function removeOldFiles()
    {
        $documents = $this->site->getDocuments();

        foreach ($documents as $document) {
            // var_dump();die;
            if (diff_two_date($document->created_at) < 7) {
                continue;
            }
            $file = FCPATH . $document->url;
            if (!unlink($file)) {
                echo ("\n Error deleting $file \n");
                // continue;
            } else {
                echo ("\n Deleted $file \n");
            }
            $this->db->update('documents', ['deleted_at' => date('Y-m-d H:i:s'), 'is_deleted' => '1'], ['id' => $document->id]);
        }
    }

    public function assignPromoVoucher50K()
    {
        echo date("Y-m-d H:i:s") . "\n";
        echo "Running : Function " . __FUNCTION__ . "\n";
        try {
            $promo = $this->site->findPromoByCode("VOUCHER50K");
            if(!$promo) {
                throw new \Exception("Promo not found");       
            }
            $sqlSms = "
            select
                vaa.phone `phone`,
                vaa.idbk `idbk`,
                cust.company `company`
            from sma_v_aktivasi_aksestoko vaa
            left join sma_companies cust on cust.cf1 = concat('IDC-', vaa.idbk)
            left join sma_user_promotions up on up.promo_id = ".$promo->id." and up.company_id = cust.id
            where 1=1
                and up.id is null
                and cust.group_name = 'customer'
                and cust.cf1 like 'IDC-_________'
                and vaa.tanggal_aktivasi >= '2020-10-15'
                and vaa.tanggal_aktivasi <= '2020-12-15'
                group by vaa.idbk
            ";
            $sql = "
                insert into sma_user_promotions (id, promo_id, company_id, supplier_id, created_at, created_by, updated_at, updated_by, is_deleted)
                (select null `id`,
                    ".$promo->id." `promo_id`,
                    cust.id `company_id`,
                    cust.company_id `supplier_id`,
                    now() `created_at`,
                    1 `created_by`,
                    now() `updated_at`,
                    1 `updated_by`,
                    null `is_deleted`
                from sma_v_aktivasi_aksestoko vaa
                left join sma_companies cust on cust.cf1 = concat('IDC-', vaa.idbk)
                left join sma_user_promotions up on up.promo_id = ".$promo->id." and up.company_id = cust.id
                where 1=1
                    and up.id is null
                    and cust.group_name = 'customer'
                    and cust.cf1 like 'IDC-_________'
                    and vaa.tanggal_aktivasi >= '2020-10-15'
                    and vaa.tanggal_aktivasi <= '2020-12-15')
            ";
            $qSms = $this->db->query($sqlSms);
            echo ">> Assigning customer to promotion : " . $promo->code_promo ."\n";
            $q = $this->db->query($sql);
            if (!$q) {
                throw new \Exception($this->db->error()['message']);
            }
            $affected_rows = $this->db->affected_rows();
            echo ">> $affected_rows Assigned successfully\n";
            if ($qSms->num_rows() > 0) {
                foreach (($qSms->result()) as $request) {
                    $message = $this->site->makeMessage('sms_notif_voucher50k', [
                        'store' => $request->company . ' (' . $request->idbk . ')'
                    ]);
                    echo ">> Sending sms to '$request->phone' with message '$message'\n";
                    $sms = $this->site->send_sms_otp($request->phone, $message, false, 'notif');
                    echo ">> SMS sent\n";
                    $users = $this->site->findUserByIdBk($request->idbk);
                    
                    $notification   = [
                        'title' => 'AksesToko - Promo',
                        'body'  => $message
                    ];
                    
                    $data = [
                        'click_action'   => 'FLUTTER_NOTIFICATION_CLICK',
                        'title'          => 'AksesToko - Promo',
                        'body'           => $message,
                        'type'           => 'sms_notif_promo',
                        'id_promo'       => $promo->id,
                        'code_promo'     => $promo->code_promo,
                        'tanggal'        => date('d/m/Y'),
                    ];

                    echo ">> Sending notification to firebase for AksesToko Mobile";
                    $notifikasi_atmobiel = $this->integration->notification_atmobile($notification, $data, $users->id);
                    if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                        echo ">> Sending notification to AksesToko Mobile failed " . $notifikasi_atmobiel->results[0]->error . "\n";
                    } else {
                        echo ">> Sending notification to AksesToko Mobile success.\n";
                    }
                    echo ">> Notification sent\n";
                }
            } else {
                throw new Exception("No data found");
            }
            echo "Done\n";
        } catch (\Throwable $th) {
            echo "Error : " . $th->getMessage(). "\n";
        }
    }

    public function assignPromoVoucher16K()
    {
        echo date("Y-m-d H:i:s") . "\n";
        echo "Running : Function " . __FUNCTION__ . "\n";
        try {
            $promo = $this->site->findPromoByCode("VOUCHER16K");
            if(!$promo) {
                throw new \Exception("Promo not found");       
            }
            $sql = "
            insert into sma_user_promotions (id, promo_id, company_id, supplier_id, created_at, created_by, updated_at, updated_by, is_deleted)
            (select
                null `id`,
                ".$promo->id." `promo_id`,
                cust.id `company_id`,
                cust.company_id `supplier_id`,
                now() `created_at`,
                1 `created_by`,
                now() `updated_at`,
                1 `updated_by`,
                null `is_deleted`
            from sma_companies cust
            left join sma_user_promotions up on up.promo_id = ".$promo->id." and up.company_id = cust.id
            where 1=1
                and cust.group_name = 'customer'
                and up.id is null
                and cust.cf1 like 'IDC-_________'
                and cust.is_active = 1
                and cust.is_deleted is null)
            ";
            $q = $this->db->query($sql);
            if (!$q) {
                throw new \Exception($this->db->error()['message']);
            }
            echo "Success\n";
        } catch (\Throwable $th) {
            echo "Error : " . $th->getMessage(). "\n";
        }
    }

    public function writeExcelReportPromo($start_date = null, $end_date = null)
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();

        try {
            $start_date = $start_date ?? "2020-10-15";
            $end_date = $end_date ?? date("Y-m-d", strtotime(date("Y-m-d") . "+ 1 days"));

            $q = $this->db->get("sma_v_report_promo_aksestoko");

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $headers = [];

            $data = $q->result_array();

            foreach ($data[0] as $key => $value) {
                $headers[] = $key;
            }

            array_unshift($data, $headers);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->fromArray($data, NULL, 'A1');
            
            $filename = 'report_promo_' . date("Y-m-d_H") . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);
            $this->site->insertOrUpdateDocuments([
                'name' => "Report Promo",
                'filename' => $filename,
                'url' => $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }

    public function writeExcelSalesperson()
    {
        ini_set('memory_limit', '2048M');
        ob_clean();
        $this->db->trans_begin();

        try {
            $q = $this->db->get("sma_v_salesperson");

            if (!$q || $q->num_rows() == 0) {
                throw new Exception("Data tidak ditemukan");
            }

            $headers = [];

            $data = $q->result_array();

            foreach ($data[0] as $key => $value) {
                $headers[] = $key;
            }

            array_unshift($data, $headers);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->setActiveSheetIndex(0);
            $sheet->fromArray($data, NULL, 'A1');
            
            $filename = 'salesperson_' . date("Y-m-d_H") . ".xlsx";
            $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);

            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
            $tmpHandle = tmpfile();
            $metaDatas = stream_get_meta_data($tmpHandle);
            $tmpFilename = $metaDatas['uri'];
            $writer->save($tmpFilename);
            $upload = $this->integration->upload_files(['tmp_name' => $tmpFilename, 'name' => $filename]);
            $this->site->insertOrUpdateDocuments([
                'name' => "Salesperson",
                'filename' => $filename,
                'url' => $upload->url,
                'size' => $upload->size,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $this->db->trans_commit();
            unlink($tmpFilename);
            fclose($tmpHandle);
            echo "success";
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo $th->getMessage();
        }
    }
}

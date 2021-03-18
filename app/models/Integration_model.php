<?php

use GuzzleHttp\Client;
use phpseclib\Net\SFTP;

class Integration_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function _integratedDistributor($type = 'general')
    {
        switch ($type) {
            case 'general':
                $return = [
                    'testing', //hanya untuk testing
                    'erp',
                    'sid',
                    'kwsg',
                    'lbps', //erp 
                    'apw', //erp
                    'ppcp', //erp
                    'mmm', //erp
                    'gsda', //erp
                    'sbp', //erp
                    'pmp', //erp
                    'pas', //erp
                    'big',
                    'snj', //siska
                    'jbu',
                    'igiri',
                    'scsp', //igiri
                    'bpp',
                    'cbn',
                    'mas',
                    'wpu' //siska
                ];
                break;
            case 'register':
                $return = [
                    'testing', //hanya untuk testing
                    'igiri',
                    'scsp', //igiri
                ];
                break;
        }
        return $return;
    }

    public function findApiIntegrationByType($type, $required = true)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => $type,
        ], 1);

        if (!$q || $q->num_rows() == 0) {
            if ($required) {
                throw new \Exception("Tidak dapat mendapatkan API Integration $type");
            } else {
                return false;
            }
        }
        return $q->row();
    }

    public function isIntegrated($application_from, $type = 'general')
    {
        $application_from = strtolower($application_from);
        return in_array($application_from, $this->_integratedDistributor($type));
    }

    private function _writeFile($filename, $data)
    {
        file_put_contents(APPPATH . '/logs/' . $filename, $data, FILE_APPEND | LOCK_EX);
    }

    public function insertApiLog($data)
    {
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->db->insert('api_log_activities', $data) ? $this->db->insert_id() : false;
    }

    private function _post($url, $data, $headers, $ssl = false, $jsonEncode = true, $method = "POST", $build_query = false)
    {
        // ob_start();

        if ($jsonEncode) {
            $data = json_encode($data);
        }
        if ($build_query) {
            $data = http_build_query($data);
        }

        $curlHandle = curl_init($url);

        if ($ssl) {
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
            curl_setopt($curlHandle, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
            curl_setopt($curlHandle, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");
        }
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
        $exec = curl_exec($curlHandle);
        $respon = json_decode($exec);
        $this->_writeFile('integration_' . date('Y-m-d'), [
            'first' => "\n----------\n",
            'time' => date('Y-m-d H:i:s') . "\n",
            'request' => $data . "\n",
            'headers' => implode(" ", $headers) . "\n",
            'response' => $exec . "\n",
            'end' => "----------\n"
        ]);

        $this->insertApiLog([
            'method' => $method,
            'url' => $url,
            'headers' => json_encode(oneToTwoDArray($headers)),
            'body' => $data,
            'parameters' => null,
            'io_type' => 'out',
            'ssl_status' => $ssl == true ? $ssl : null,
            'response' => $exec,
        ]);

        if (curl_error($curlHandle)) {
            throw new \Exception(curl_error($curlHandle));
        }

        curl_close($curlHandle);

        return $respon;
    }



    // {
    //     "tgl_sales": "2019-09-02",
    //     "code_bisko" : "900000002",
    //     "order_code" : "SALE/2019/09/0001",
    //     "catatan" : "kirim catatan di AksesToko gan",
    //     "jenis" : "FRC", //LCO
    //     "detail" : [
    //         {"kodesap" : "121-301-0110", "qty" : "600",  "harga": 50000, "tgl_kiriman" : "2019-09-30"}
    //     ]
    // }
    private function send_order_sid($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail = [];
        foreach ($saleItems as $i => $saleItem) {
            $detail[] = [
                'kodesap' => $saleItem['product_code'],
                'qty' => $saleItem['quantity'],
                'harga' => $saleItem['unit_price'],
                'tgl_kiriman' => $purchase['shipping_date']
            ];
        }

        $data = [
            'tgl_sales' => $sale['date'],
            'code_bisko' => $id_bk,
            'order_code' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'catatan' => $sale['note'] . ' | Alamat Kirim : ' . $sale['device_id'],
            'jenis' => $sale['delivery_method'] == 'pickup' ? 'LCO' : 'FRC',
            'detail' => $detail
        ];

        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        if($promo) {
            $data['catatan'] .= " | Menggunakan promo : " . $promo->code_promo . ".";
        }

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        // return ($this->_post($url, $data, $headers));

        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != '1') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->pesan);
        }
        return $response->reference_no;
    }

    // {
    // "description": "SEMANGAT",
    // "id_customer": "100078876",
    // "dateordered": "2019-09-16 11:28:47",
    // "retail_id": "string",
    // "ref_atoder_id": "SALE/2019/09/0003",
    // "product_code": "SEMEN 40 KG PROYEK",
    // "paymenterm": "15",
    // "qtyordered": "5",
    // "priceentered": "100000",
    // "docstatus": "",
    // "grandtotal": 500000,
    // "promotioncode": "",
    // "movementdate": "2019-09-16 11:28:47",
    // "address": "jl.ahmad yani nganjuk",
    // "doctype": "P"
    // }
    private function send_order_erp($integration, $id_bk, $sale = [], $saleItems = [], $purchase = [])
    {
        $url = $integration->uri;

        $data = [
            'description' => $sale['note'],
            'dateordered' => $sale['date'],
            'id_customer' => $id_bk,
            'retail_id' => 0,
            'docstatus' => 'DR',
            'ref_atoder_id' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'paymenterm' => (int)$purchase['payment_duration'],
            'product_code' => '',
            'qtyordered' => '',
            'priceentered' => '',
            'grandtotal' => (int)$sale['grand_total'],
            "promotioncode" => ($this->site->findPromotionByPurchaseId($purchase['id']))->code_promo,
            "movementdate" => $purchase['shipping_date'],
            "address" => $sale['device_id'],
            "doctype" => $purchase['payment_method'] == 'kredit' ? "P" : "B" //B = cash / P = credit
        ];
        foreach ($saleItems as $i => $saleItem) {
            $data['product_code'] .= $saleItem['product_code'] . ($i != (count($saleItems) - 1) ? '/' : '');
            $data['qtyordered'] .= ((int)$saleItem['quantity']) . ($i != (count($saleItems) - 1) ? '/' : '');
            $data['priceentered'] .= ((int)$saleItem['unit_price']) . ($i != (count($saleItems) - 1) ? '/' : '');
        }

        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        if($promo) {
            $data['description'] .= " Menggunakan promo : " . $promo->code_promo . ".";
        }

        $headers = [
            'Forca-Token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers, true));
        if (!$response->codestatus || $response->codestatus == 'E') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return $response->resultdata->documentno;
    }


    // {
    // "bukti_so": "SALE/2019/09/0001",
    // "tgl_so" : "2019-09-26",
    // "tgl_kirim": "2019-09-27",
    // "kd_bk": "100030828",
    // "t_kwt": "5",
    // "total": "247000",
    // "status": "pending",
    // "detail_so": [
    //     {
    //     "sku": "1213010050",
    //     "quantity": "5",
    //     "unit_price": "49400"
    //     }
    // ]
    // }
    private function send_order_kwsg($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail_so = [];
        $total_items = 0;
        foreach ($saleItems as $i => $saleItem) {
            $detail_so[] = [
                'sku' => $saleItem['product_code'],
                'quantity' => $saleItem['quantity'],
                'unit_price' => $saleItem['unit_price']
            ];
            $total_items = $total_items + (int)$saleItem['quantity'];
        }

        $data = [
            'bukti_so' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'tgl_so' => $sale['date'],
            'tgl_kirim' => $purchase['shipping_date'],
            'kd_bk' => $id_bk,
            't_kwt' => $total_items,
            'total' => $sale['grand_total'],
            'status' => $sale['sale_status'],
            'detail_so' => $detail_so
        ];

        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        if($promo) {
            $data['catatan'] .= " Menggunakan promo : " . $promo->code_promo . ".";
        }

        $headers = [
            'Authorization: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 'success') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*
    {  
    "reference_no":"SO20191120",
    "tanggal":"2019-11-20",
    "id_sg":"100072975",
    "jenis_bayar":"kredit",
    "tgl_kirim":"2019-11-21",
    "status_so":"pending",
    "keterangan":"",
    "kd_barang":["121-301-0110","121-301-0240","121-301-0050"],
    "jml_kwt":["200","50","10"],
    "hrg_sat":["50000","60000","40000"],
    "jml_hrg":["10000000","3000000","400000"]
    } 
*/

    private function send_order_big($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail_so = [
            'kd_barang' => [],
            'jml_kwt' => [],
            'hrg_sat' => [],
            'jml_hrg' => []
        ];
        foreach ($saleItems as $i => $saleItem) {
            $detail_so['kd_barang'][] = $saleItem['product_code'];
            $detail_so['jml_kwt'][] = $saleItem['quantity'];
            $detail_so['hrg_sat'][] = $saleItem['unit_price'];
            $detail_so['jml_hrg'][] = $saleItem['unit_price'] * $saleItem['quantity'];
        }

        $data = [
            'reference_no' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'tanggal' => $sale['date'],
            'id_sg' => $id_bk,
            'jenis_bayar' => $purchase['payment_method'],
            'tgl_kirim' => $purchase['shipping_date'],
            'status_so' => $sale['sale_status'],
            'keterangan' => $sale['note'],
            'kd_barang' => $detail_so['kd_barang'],
            'jml_kwt' => $detail_so['jml_kwt'],
            'hrg_sat' => $detail_so['hrg_sat'],
            'jml_hrg' => $detail_so['jml_hrg'],
        ];

        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        if($promo) {
            $data['keterangan'] .= " Menggunakan promo : " . $promo->code_promo . ".";
        }

        $headers = [
            'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*
    {
    "orderid" : "ORDER0002",
    "customer_code" : "100050001",
    "status" : "pending",
    "bank_code" : "BCA SNJ",
    "top" : "30",
    "salesnote" : "cobacoba",
    "paymentmethod" : "top",
    "detail" : [{
        "product_code" : "121-301-0050",
        "product_price": "50000",
        "qty" : 1
    },{
        "product_code" : "121-301-0060",
        "product_price": "50000",
        "qty" : 90
    }]
    }
    */

    private function send_order_snj($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail = [];

        foreach ($saleItems as $i => $saleItem) {
            $detail[] = [
                'product_code' => $saleItem['product_code'],
                'product_price' => $saleItem['unit_price'],
                'qty' => $saleItem['quantity']
            ];
        }

        $bank = $this->site->getBankByID((int)$purchase['bank_id']);

        $data = [
            'orderid' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'customer_code' => $id_bk,
            'status' => $sale['sale_status'],
            'bank_code' => $bank ? $bank->code : null,
            'top' => (int)$purchase['payment_duration'],
            'salesnote' => $sale['note'],
            'paymentmethod' => $purchase['payment_method'],
            'detail' => $detail,
        ];

        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        if($promo) {
            $data['salesnote'] .= " Menggunakan promo : " . $promo->code_promo . ".";
        }

        $headers = [
            // 'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->success || $response->success != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*
    {
	"tglOrder": "2019-12-03",
	"kodeKokoh" : "7000123",
	"alamatKirim" : "Gresik",
	"tglRencanKirim": "2019-12-13",
	"noOrder" : "SALE/2019/12/0001",
	"catatan" : "Segera Kirim",
	"namaPemesan" : "Toko Makmur",
	"ipKomputer" : "172.20.1.1",
	"namaKomputer" : "si-sutrisno",
	"detail" : [
            {"kodeProduk" : "121-301-0110", "namaProduk":"Semen PPC 40KG", "qtyOrder" : "600",  "hargaJual": 50000},
            {"kodeProduk" : "121-301-0060", "namaProduk":"Semen PPC 50KG", "qtyOrder" : "300",  "hargaJual": 45000}
        ]
    }
    */

    private function send_order_jbu($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail = [];

        foreach ($saleItems as $i => $saleItem) {
            $detail[] = [
                'kodeProduk' => $saleItem['product_code'],
                'namaProduk' => $saleItem['product_name'],
                'hargaJual' => (int)$saleItem['unit_price'],
                'qtyOrder' => (int)$saleItem['quantity']
            ];
        }

        $data = [
            'tglOrder' => $sale['date'],
            'kodeKokoh' => $id_bk,
            'alamatKirim' => $sale['device_id'],
            'tglRencanKirim' => $purchase['shipping_date'],
            'noOrder' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'catatan' => $sale['note'],
            'namaPemesan' => $sale['customer'],
            "ipKomputer" => $this->input->ip_address(),
            "namaKomputer" => "aksestoko",
            "diskon" => $sale['total_discount'],
            'detail' => $detail,
        ];

        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        if($promo) {
            $data['catatan'] .= " Menggunakan promo : " . $promo->code_promo . ".";
        }

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != "0000") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->detPesan);
        }
        return true;
    }

    /*
    {
        "id": "3",
        "userEntry": "56",
        "customerInfo": "Test Again",
        "kode_pabrik": "100012717",
        "order_date": "2019-03-18",
        "customerName": "LANCAR, TB",
        "customerAddress": "JL.TENTARA PELAJAR NO.9 B WONOSARI",
        "order_ref": "SO/2019/08/00001",
        "grand_total": "90000",
        "materials": [
            {
                "id_order_detail": "1",
                "currency": "IDR",
                "kodeBarang": "121-301-0110",
                "namaBarang": "PPC 40 KG",
                "diskon": "1000",
                "harga": "41000",
                "priceInput": "41000",
                "qty": "1",
                "satuan": "ZAK",
                "unitSelected": "ZAK"
            },
            {
                "id_order_detail": "2",
                "currency": "IDR",
                "kodeBarang": "121-301-0220",
                "namaBarang": "PPC 40 KG",
                "diskon": "1000",
                "harga": "51000",
                "priceInput": "51000",
                "qty": "1",
                "satuan": "ZAK",
                "unitSelected": "ZAK"
            }
        ],
        "memo": "Test Order",
        "tipe": "1",
        "type_no": "32"
    }

    */

    private function send_order_igiri($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail = [];

        foreach ($saleItems as $i => $saleItem) {
            $detail[] = [
                'id_order_detail' => $saleItem['id'],
                'currency' => 'IDR',
                'kodeBarang' => $saleItem['product_code'],
                'namaBarang' => $saleItem['product_name'],
                'diskon' => (int)$saleItem['discount'],
                'harga' => (int)$saleItem['unit_price'],
                'priceInput' => (int)$saleItem['unit_price'],
                'qty' => (int)$saleItem['quantity'],
                'satuan' => $saleItem['product_unit_code'],
                'unitSelected' => $saleItem['product_unit_code']
            ];
        }

        $data = [
            'id' => $sale['id'],
            'userEntry' => $sale['created_by'],
            'customerInfo' => null,
            'kode_pabrik' => $id_bk,
            'order_date' => $sale['date'],
            'customerName' => $sale['customer'],
            'customerAddress' => $sale['device_id'],
            'order_ref' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'grand_total' => (int)$sale['grand_total'],
            'memo' => $sale['note'],
            'tipe' => null,
            'type_no' => null,
            'materials' => $detail,
        ];

        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        if($promo) {
            $data['memo'] .= " Menggunakan promo : " . $promo->code_promo . ".";
        }

        $headers = [
            'Key-Access: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != "200") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return $response->noTransaksiTersimpan;
    }

    private function send_order_bpp($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail = [];

        
        $promo = $this->site->findPromoByPurchaseId($purchase['id']);
        
        
        foreach ($saleItems as $i => $saleItem) {
            $detail[] = [
                'status_order' => $sale['sale_status'],
                'cust_id' => $id_bk,
                'product_id' => $saleItem['product_code'],
                'price' => $saleItem['unit_price'],
                'qty' => $saleItem['quantity'],
                'note' => $sale['note']
            ];

            if ($promo) {
                $detail[$i]['note'] .= " Menggunakan promo : " . $promo->code_promo . ".";
            }
        }

        $data = [
            'order_id' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'trx_date' => $sale['date'],
            'data' => $detail,
        ];

        $headers = [
            'Authorization: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!property_exists($response, 'status_code') || $response->status_code != "0") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return $response->kodenota;
    }

    private function send_order_mas($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        // $detail = [];

        // foreach ($saleItems as $i => $saleItem) {
        //     $detail [] = [
        //         'status_order' => $sale['sale_status'],
        //         'cust_id' => $id_bk,
        //         'product_id' => $saleItem['product_code'],
        //         'price' => $saleItem['unit_price'],
        //         'qty' => $saleItem['quantity'],
        //         'note' => $sale['note']
        //     ];
        // }

        // $data = [
        //     'order_id' => $sale['reference_no'] . '-' . $sale['biller_id'],
        //     'trx_date' => $sale['date'],
        //     'data' => $detail,
        // ];

        // $headers = [
        //     'Authorization: ' . $integration->token,
        //     'Content-Type: application/json'
        // ];

        // $response = ($this->_post($url, $data, $headers));
        // if (!property_exists($response, 'status_code') || $response->status_code != "0") {
        //     throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        // }
        // return $response->kodenota;
    }


    public function create_order_integration($application_from, $id_bk, $sale = [], $saleItems = [], $purchase = [])
    {
        $application_from = strtolower($application_from);
        $integration = $this->findApiIntegrationByType($application_from . '_create_order');
        switch ($application_from) {
            case 'testing':
                return true;
                break;
            case 'sid':
                return $this->send_order_sid($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'kwsg':
                return $this->send_order_kwsg($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'lbps': //erp 
            case 'apw': //erp
            case 'ppcp': //erp
            case 'mmm': //erp
            case 'gsda': //erp
            case 'sbp': //erp
            case 'pmp': //erp
            case 'pas': //erp
            case 'erp':
                return $this->send_order_erp($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'big':
                return $this->send_order_big($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'snj':
                return $this->send_order_snj($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'jbu':
                return $this->send_order_jbu($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'igiri':
            case 'scsp':
                return $this->send_order_igiri($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'bpp':
                return $this->send_order_bpp($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'cbn':
                return true;
                break;
            case 'wpu':
                return $this->send_order_snj($integration, $id_bk, $sale, $saleItems, $purchase);
            default:
                return false;
                break;
        }
        return false;
    }

    // {
    //     "payment_ref" : "PAY/2019/08/0019",
    //     "payment_order_ref" : "SO/107/201909/0111",
    //     "payment_status" : "accept",
    //     "payment_receipt_image" : "https://i.ibb.co/1TQjNMs/65019ebcbc1d.jpg",
    //     "payment_nominal" : "10000",
    //     "rek_bank_tujuan" : "1780001690359"
    // }
    private function send_payment_sid($integration, $id_bk, $sale, $payment, $bank)
    {
        $url = $integration->uri;

        $data = [
            'payment_ref' => $payment['reference_no'],
            'payment_order_ref' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'payment_status' => $payment['status'],
            'payment_receipt_image' => $payment['url_image'],
            'payment_nominal' => $payment['nominal'],
            'rek_bank_tujuan' => $bank['no_rekening']
        ];

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        // return ($this->_post($url, $data, $headers))->reference_no;
        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != '1') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->pesan);
        }
        return $response->reference_no;
    }

    // {
    // "id_customer":"100050867",
    // "retail_id": "1234",
    // "ref_atpayment_id": "PAY/2019/08/0001",
    // "payamt": "800000",
    // "datetrx": "2019-09-06",
    // "imageurl": "https://qa.forca.id:9494/resources/templates/white/images/logo_forca.png",
    // "bankname": "Bank AksesToko",
    // "ref_atorder_id": "SO/2019/08/00001"
    // }
    private function send_payment_erp($integration, $id_bk, $sale, $payment, $bank, $application_from = "")
    {
        $url = $integration->uri;

        $data = [
            'id_customer' => $id_bk,
            'retail_id' => 0,
            'ref_atpayment_id' => $payment['reference_no'],
            'payamt' => $payment['nominal'],
            'datetrx' => $payment['created_at'],
            'imageurl' => $payment['url_image'],
            'bankname' => $bank['bank_name'],
            'ref_atorder_id' => $sale['reference_no'] . '-' . $sale['biller_id']
        ];

        $headers = [
            'Forca-Token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers, true));
        if (!$response->codestatus || $response->codestatus == 'E') {
            throw new \Exception("Gagal mengirim payment ke distributor. " . $response->message);
        }
        return $response->resultdata->payment_ref;
    }

    // {
    // "bukti_so": "SALE/2019/09/0001",
    // "bukti_payment": "PAY/XXX",
    // "link_bukti_payment": "-",
    // "jumlah": "100000"
    // }
    private function send_payment_kwsg($integration, $id_bk, $sale, $payment, $bank)
    {
        $url = $integration->uri;

        $data = [
            'bukti_so' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'bukti_payment' => $payment['reference_no'],
            'link_bukti_payment' => $payment['url_image'],
            'jumlah' => $payment['nominal']
        ];

        $headers = [
            'Authorization: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 'success') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /* {  
        "reference_no":"SO20191120",
        "payment_id":"PAY20191120",
        "payment_receipt_image":"-",
        "payment_nominal":"38160000."
     } */

    private function send_payment_big($integration, $id_bk, $sale, $payment, $bank)
    {
        $url = $integration->uri;

        $data = [
            'reference_no' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'payment_id' => $payment['reference_no'],
            'payment_receipt_image' => $payment['url_image'],
            'payment_nominal' => $payment['nominal']
        ];

        $headers = [
            'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /* 
    {
        "paymentid" : "TES123",
        "orderid" : "ORDER0002",
        "jumlah" : "100",
        "status" : "confirm",
        "gambar" : "ORDER0002"
    }
    */

    private function send_payment_snj($integration, $id_bk, $sale, $payment, $bank)
    {
        $url = $integration->uri;

        $data = [
            'orderid' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'paymentid' => $payment['reference_no'],
            'gambar' => $payment['url_image'],
            'jumlah' => $payment['nominal'],
            'status' => $payment['status']
        ];

        $headers = [
            // 'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->success || $response->success != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }


    /* 
    {
        "noOrder" : "SALE/2019/12/00011",
        "noPayment" : "PAY/TMP/12345"
        "nominal" : "124000000",
        "linkBuktiTransfer" : "buktitrasfer.jpg",
        "norekBank" : "7000123"
    }
    */

    private function send_payment_jbu($integration, $id_bk, $sale, $payment, $bank)
    {
        $url = $integration->uri;

        $data = [
            'noOrder' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'noPayment' => $payment['reference_no'],
            'linkBuktiTransfer' => $payment['url_image'],
            'nominal' => $payment['nominal'],
            'norekBank' => $bank['no_rekening']
        ];

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != "0000") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->detPesan);
        }
        return true;
    }

    /* 
    {
        "tanggal": "2019-11-30",
        "kode_toko": "100012717",
        "payment_ref": "PAY/2019/08/0001",
        "payment_order_ref": "SO/2019/08/00001",
        "payment_status": "accept",
        "Payment_receipt_image": "https://i.ibb.co/1TQjNMs/65019ebcbc1d.jpg",
        "payment_nominal": "800000",
        “id_bank_account”: 1,
        “transfer_to”: “BRI”,
    }
    */

    private function send_payment_igiri($integration, $id_bk, $sale, $payment, $bank)
    {
        $url = $integration->uri;

        $data = [
            'tanggal' => $payment['created_at'],
            'kode_toko' => $id_bk,
            'payment_ref' => $payment['reference_no'],
            'payment_order_ref' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'payment_status' => $payment['status'],
            'payment_receipt_image' => $payment['url_image'],
            'payment_nominal' => $payment['nominal'],
            'id_bank_account' => $bank['code'],
            'transfer_to' => $bank['bank_name'],
        ];

        $headers = [
            'Key-Access: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != "200") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    public function create_payment_integration($application_from, $id_bk, $sale, $payment, $bank)
    {
        $application_from = strtolower($application_from);
        $integration = $this->findApiIntegrationByType($application_from . '_create_payment');
        switch ($application_from) {
            case 'testing':
                return true;
                break;
            case 'sid':
                return $this->send_payment_sid($integration, $id_bk, $sale, $payment, $bank);
                break;
            case 'kwsg':
                return $this->send_payment_kwsg($integration, $id_bk, $sale, $payment, $bank);
                break;
            case 'lbps': //erp 
            case 'apw': //erp
            case 'ppcp': //erp
            case 'mmm': //erp
            case 'gsda': //erp
            case 'sbp': //erp
            case 'pmp': //erp
            case 'pas': //erp
            case 'erp':
                return $this->send_payment_erp($integration, $id_bk, $sale, $payment, $bank);
                break;
            case 'big':
                return $this->send_payment_big($integration, $id_bk, $sale, $payment, $bank);
                break;
            case 'snj':
                return $this->send_payment_snj($integration, $id_bk, $sale, $payment, $bank);
                break;
            case 'jbu':
                return $this->send_payment_jbu($integration, $id_bk, $sale, $payment, $bank);
                break;
            case 'igiri':
            case 'scsp':
                return $this->send_payment_igiri($integration, $id_bk, $sale, $payment, $bank);
                break;
            case 'cbn':
                return true;
                break;
            case 'wpu':
                return $this->send_payment_snj($integration, $id_bk, $sale, $payment, $bank);
                break;
            default:
                return false;
                break;
        }
        return false;
    }

    // {
    // "description": "SEMANGAT",
    // "id_customer": "100078876",
    // "dateordered": "2019-09-16 11:28:47",
    // "retail_id": "string",
    // "ref_atoder_id": "SALE/2019/09/0003",
    // "product_code": "SEMEN 40 KG PROYEK",
    // "paymenterm": "15",
    // "qtyordered": "5",
    // "priceentered": "100000",
    // "docstatus": "",
    // "grandtotal": 500000,
    // "promotioncode": "",
    // "movementdate": "2019-09-16 11:28:47",
    // "address": "jl.ahmad yani nganjuk",
    // "doctype": "P"
    // }
    private function send_update_confirmation_erp($integration, $id_bk, $sale = [], $saleItems = [], $purchase = [])
    {
        $url = $integration->uri;

        $data = [
            'description' => $sale['note'],
            'dateordered' => $sale['date'],
            'id_customer' => $id_bk,
            'retail_id' => 0,
            'docstatus' => $sale['sale_status'] == 'confirmed' ? 'CO' : 'VO',
            'ref_atoder_id' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'paymenterm' => (int)$purchase['payment_duration'],
            'product_code' => '',
            'qtyordered' => '',
            'priceentered' => '',
            'grandtotal' => (int)$sale['grand_total'],
            "promotioncode" => '',
            "movementdate" => $purchase['shipping_date'],
            "address" => $sale['device_id'],
            "doctype" => $purchase['payment_method'] == 'kredit' ? "P" : "B" //B = cash / P = credit
        ];
        foreach ($saleItems as $i => $saleItem) {
            $data['product_code'] .= $saleItem['product_code'] . ($i != (count($saleItems) - 1) ? '/' : '');
            $data['qtyordered'] .= ((int)$saleItem['quantity']) . ($i != (count($saleItems) - 1) ? '/' : '');
            $data['priceentered'] .= ((int)$saleItem['unit_price']) . ($i != (count($saleItems) - 1) ? '/' : '');
        }

        $headers = [
            'Forca-Token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers, true));
        if (!$response->codestatus || $response->codestatus == 'E') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return $response->resultdata->documentno;
    }

    // {
    //     "order_code" : "SALE/2019/09/0051",
    //     "status_so" : "0"
    // }
    private function send_update_confirmation_sid($integration, $id_bk, $sale = [])
    {
        $url = $integration->uri;

        $data = [
            'order_code' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'status_so' => $sale['sale_status'] == 'confirmed' ? '0' : '7'
        ];

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        // return ($this->_post($url, $data, $headers))->reference_no;
        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != '1') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->pesan);
        }
        return $response->reference_no;
    }

    // {
    // "bukti_so": "SALE/2019/09/0001",
    // "tgl_so" : "2019-09-26",
    // "tgl_kirim": "2019-09-27",
    // "kd_bk": "100030828",
    // "t_kwt": "5",
    // "total": "247000",
    // "status": "pending",
    // "detail_so": [
    //     {
    //     "sku": "1213010050",
    //     "quantity": "5",
    //     "unit_price": "49400"
    //     }
    // ]
    // }
    private function send_update_confirmation_kwsg($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail_so = [];
        $total_items = 0;
        foreach ($saleItems as $i => $saleItem) {
            $detail_so[] = [
                'sku' => $saleItem['product_code'],
                'quantity' => $saleItem['quantity'],
                'unit_price' => $saleItem['unit_price']
            ];
            $total_items = $total_items + (int)$saleItem['quantity'];
        }

        $data = [
            'bukti_so' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'tgl_so' => $sale['date'],
            'tgl_kirim' => $purchase['shipping_date'],
            'kd_bk' => $id_bk,
            't_kwt' => $total_items,
            'total' => (int)$sale['grand_total'],
            'status' => $sale['sale_status'],
            'detail_so' => $detail_so
        ];

        $headers = [
            'Authorization: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 'success') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*
        {  
        "reference_no":"SO20191120",
        "tanggal":"2019-11-20",
        "id_sg":"100072975",
        "jenis_bayar":"kredit",
        "tgl_kirim":"2019-11-21",
        "status_so":"pending",
        "keterangan":"",
        "kd_barang":["121-301-0110","121-301-0240","121-301-0050"],
        "jml_kwt":["200","50","10"],
        "hrg_sat":["50000","60000","40000"],
        "jml_hrg":["10000000","3000000","400000"]
        } 
    */

    private function send_update_confirmation_big($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $detail_so = [
            'kd_barang' => [],
            'jml_kwt' => [],
            'hrg_sat' => [],
            'jml_hrg' => []
        ];
        foreach ($saleItems as $i => $saleItem) {
            $detail_so['kd_barang'][] = $saleItem['product_code'];
            $detail_so['jml_kwt'][] = $saleItem['quantity'];
            $detail_so['hrg_sat'][] = $saleItem['unit_price'];
            $detail_so['jml_hrg'][] = $saleItem['unit_price'] * $saleItem['quantity'];
        }

        $data = [
            'reference_no' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'tanggal' => $sale['date'],
            'id_sg' => $id_bk,
            'jenis_bayar' => $purchase['payment_method'],
            'tgl_kirim' => $purchase['shipping_date'],
            'status_so' => $sale['sale_status'],
            'keterangan' => $sale['note'],
            'kd_barang' => $detail_so['kd_barang'],
            'jml_kwt' => $detail_so['jml_kwt'],
            'hrg_sat' => $detail_so['hrg_sat'],
            'jml_hrg' => $detail_so['jml_hrg'],
        ];

        $headers = [
            'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*
        {
        "orderid" : "ORDER0002",
        "charge" : "100",
        "status" : "confirm"
        }
    */

    private function send_update_confirmation_snj($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $data = [
            'orderid' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'charge' => $sale['charge'],
            'status' => $sale['sale_status'],
        ];

        $headers = [
            // 'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->success || $response->success != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*
    {
        "status": "confirmed",
        "noOrder": "SALE/2019/12/0005-33403"
    }
    */

    private function send_update_confirmation_jbu($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $data = [
            'noOrder' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'status' => $sale['sale_status'],
        ];

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != "0000") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->detPesan);
        }
        return true;
    }

    /*
    {
        "order_ref": "SO/2019/08/00001",
        "status": "confirmed"
    }
    */

    private function send_update_confirmation_igiri($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $data = [
            'order_ref' => $sale['reference_no'] . '-' . $sale['biller_id'],
            'status' => $sale['sale_status'],
        ];

        $headers = [
            'Key-Access: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers, false, true, "PUT"));
        if (!$response->status || $response->status != "200") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    private function send_update_confirmation_bpp($integration, $id_bk, $sale = [], $saleItems = [], $purchase)
    {
        $url = $integration->uri;

        $data = [
            'kodenota'  => $sale['cf1'],
            'status' => $sale['sale_status']
        ];

        $headers = [
            'Authorization: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers, false, true));
        if (!property_exists($response, 'status_code') || $response->status_code != "0") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }


    public function update_confirmation_integration($application_from, $id_bk, $sale = [], $saleItems = [], $purchase = [])
    {
        $application_from = strtolower($application_from);
        $integration = $this->findApiIntegrationByType($application_from . '_update_confirmation');
        switch ($application_from) {
            case 'testing':
                return true;
                break;
            case 'sid':
                return $this->send_update_confirmation_sid($integration, $id_bk, $sale);
                break;
            case 'kwsg':
                return $this->send_update_confirmation_kwsg($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'lbps': //erp 
            case 'apw': //erp
            case 'ppcp': //erp
            case 'mmm': //erp
            case 'gsda': //erp
            case 'sbp': //erp
            case 'pmp': //erp
            case 'pas': //erp
            case 'erp':
                return $this->send_update_confirmation_erp($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'big':
                return $this->send_update_confirmation_big($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'snj':
                return $this->send_update_confirmation_snj($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'jbu':
                return $this->send_update_confirmation_jbu($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'igiri':
            case 'scsp':
                return $this->send_update_confirmation_igiri($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'bpp':
                return $this->send_update_confirmation_bpp($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            case 'cbn':
                return true;
                break;
            case 'wpu':
                return $this->send_update_confirmation_snj($integration, $id_bk, $sale, $saleItems, $purchase);
                break;
            default:
                return false;
                break;
        }
        return false;
    }

    // {
    // "bukti_do" : "BO0819000015",
    // "bukti_so": "SALE/2019/09/0001",
    // "link_bukti_do" : "",
    // "detail_do": [
    //     {
    //     "sku": "1213010050",
    //     "jumlah_baik": "5",
    //     "jumlah_jelek": "0"
    //     }
    // ]
    // }
    private function send_confirm_received_kwsg($integration, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $url = $integration->uri;

        $detail_do = [];
        foreach ($deliveryItems as $i => $deliveryItem) {
            $detail_do[] = [
                'sku' => $deliveryItem->product_code,
                'jumlah_baik' => (int)$deliveryItem->good_quantity,
                'jumlah_jelek' => (int)$deliveryItem->bad_quantity
            ];
        }

        $data = [
            'bukti_do' => $delivery->do_reference_no,
            'bukti_so' => $sale->reference_no . '-' . $sale->biller_id,
            'link_bukti_do' => $delivery->spj_file ?? "-",
            'detail_do' => $detail_do
        ];

        $headers = [
            'Authorization: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 'success') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    //    {
    //
    //      "detail" : [
    //            {
    //            "no_spj" : "SPJ/107/201909/0029",
    //            "id_barang" : "2",
    //            "qty_baik_toko" :"9",
    //            "qty_jelek_toko" : "3",
    //            "link_gambar" :"172.30.111.111xxx.jpg"
    //            }
    //        ]
    //    }
    private function send_confirm_received_sid($integration, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $url = $integration->uri;

        $detail = [];
        foreach ($deliveryItems as $i => $deliveryItem) {
            $detail[] = [
                'no_spj' => $delivery->do_reference_no,
                'id_barang' => $deliveryItem->product_code,
                'qty_baik_toko' => (int)$deliveryItem->good_quantity,
                'qty_jelek_toko' => (int)$deliveryItem->bad_quantity,
                'link_gambar' => $delivery->spj_file ?? "-",
            ];
        }

        $data = [
            'detail' => $detail
        ];

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        // return ($this->_post($url, $data, $headers))->reference_no;
        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != '1') {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->pesan);
        }
        return $response->reference_no;
    }

    /*     {  
        "reference_no":"SO20191120",
        "kd_barang":["121-301-0110","121-301-0240","121-301-0050"],
        "jml_baik":["197","49","9"],
        "jml_buruk":["3","1","1"]
     }
 */
    private function send_confirm_received_big($integration, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $url = $integration->uri;

        $detail = [
            'kd_barang' => [],
            'jml_baik' => [],
            'jml_buruk' => []
        ];
        foreach ($deliveryItems as $i => $deliveryItem) {
            $detail['kd_barang'][] = $deliveryItem->product_code;
            $detail['jml_baik'][] = (int)$deliveryItem->good_quantity;
            $detail['jml_buruk'][] = (int)$deliveryItem->bad_quantity;
        }

        $data = [
            'reference_no' => $sale->reference_no . '-' . $sale->biller_id,
            'delivery_no' => $delivery->do_reference_no,
            'link_bukti' => $delivery->spj_file ?? "-",
            'kd_barang' => $detail['kd_barang'],
            'jml_baik' => $detail['jml_baik'],
            'jml_buruk' => $detail['jml_buruk'],
        ];

        $headers = [
            'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        // return ($this->_post($url, $data, $headers))->reference_no;
        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*   
    {
        "doid" : "TES123",
        "qtygood" : "100",
        "qtybad" : "0",
        "status" : "confirm",
        "gambar" : "TES123.jpg"
    }
    */
    private function send_confirm_received_snj($integration, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $url = $integration->uri;

        $detail = [
            'jml_baik' => 0,
            'jml_buruk' => 0
        ];
        foreach ($deliveryItems as $i => $deliveryItem) {
            $detail['jml_baik'] += (int)$deliveryItem->good_quantity;
            $detail['jml_buruk'] += (int)$deliveryItem->bad_quantity;
        }

        $data = [
            // 'reference_no' => $sale->reference_no . '-' . $sale->biller_id,
            'doid' => $delivery->do_reference_no,
            'link_bukti' => $delivery->spj_file ?? "-",
            // 'kd_barang' => $detail['kd_barang'],
            'qtygood' => $detail['jml_baik'],
            'qtybad' => $detail['jml_buruk'],
            'status' => $delivery->status
        ];

        $headers = [
            // 'appToken: ' . $integration->token,
            'Content-Type: application/json'
        ];

        // return ($this->_post($url, $data, $headers))->reference_no;
        $response = ($this->_post($url, $data, $headers));
        if (!$response->success || $response->success != 1) {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /*   
    {
        "kode_pabrik": "100013020",
        "no_so": "SO/2019/08/00001",
        "no_do": "DO/2019/08/00001",
        "tgl_do": "2019-12-01",
        "materials": [
            {
                "product_code": "121-111-222",
                "qty_good": "2",
                "qty_damage": "1"
            },
            {
                "product_code": "123-333-222",
                "qty_good": "3",
                "qty_damage": "1"
            }
        ]
    }
    */
    private function send_confirm_received_igiri($integration, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $url = $integration->uri;

        $details = [];
        foreach ($deliveryItems as $i => $deliveryItem) {
            $details[] = [
                'product_code' => (int) $deliveryItem->product_code,
                'qty_good' => (int) $deliveryItem->good_quantity,
                'qty_damage' => (int) $deliveryItem->bad_quantity,
            ];
        }

        $data = [
            'kode_pabrik' => $id_bk,
            'no_so' => $sale->reference_no . '-' . $sale->biller_id,
            'no_do' => $delivery->do_reference_no,
            'tgl_do' => $delivery->date,
            'materials' => $details,
        ];

        $headers = [
            'Key-Access: ' . $integration->token,
            'Content-Type: application/json'
        ];

        // return ($this->_post($url, $data, $headers))->reference_no;
        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != "200") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
        }
        return true;
    }

    /* {
    "receive_order_ref" : "SALE/2019/11/0020-6",
    "receive_delivery_ref" : "DO/2019/11/0005",
    "receive_date_top" : "2019-11-30",
    "receive_top" : "30",
    "receive_details" : [
        {
            "product_code" : "123456789",
            "prodct_bad" : "5",
            "product_good" : "195"
        },
        {
            "product_code" : "123451234",
            "prodct_bad" : "3",
            "product_good" : "197"
        }
    ]
    } */

    private function send_confirm_received_jbu($integration, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $url = $integration->uri;

        $details = [];
        foreach ($deliveryItems as $i => $deliveryItem) {
            $details[] = [
                'product_code' => (int) $deliveryItem->product_code,
                'product_good' => (int) $deliveryItem->good_quantity,
                'prodct_bad' => (int) $deliveryItem->bad_quantity,
            ];
        }

        $data = [
            'receive_order_ref' => $sale->reference_no . '-' . $sale->biller_id,
            'receive_delivery_ref' => $delivery->do_reference_no,
            'receive_date' => date('Y-m-d H:i:s'),
            'receive_image' => $delivery->spj_file ?? "-",
            'receive_note' => $delivery->note,
            'receive_details' => $details
        ];

        $headers = [
            'token: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->kode || $response->kode != "0000") {
            throw new \Exception("Gagal mengirim order ke distributor. " . $response->detPesan);
        }
        return true;
    }

    private function send_confirm_received_bpp($integration, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $url = $integration->uri;

        foreach ($deliveryItems as $i => $deliveryItem) {
            $data = [
                'kodenota' => $delivery->do_reference_no,
                'tgl' => $delivery->date,
                'product' => $deliveryItem->product_code,
                'goodstok' => (int) $deliveryItem->good_quantity,
                'badstok' => (int) $deliveryItem->bad_quantity,
                'link_img' => $delivery->spj_file ?? "-",
                'note' => $delivery->note
            ];

            $headers = [
                'Authorization: ' . $integration->token,
                'Content-Type: application/json'
            ];

            // return ($this->_post($url, $data, $headers))->reference_no;
            $response = ($this->_post($url, $data, $headers));
            if (!property_exists($response, 'status_code') || $response->status_code != "0") {
                throw new \Exception("Gagal mengirim order ke distributor. " . $response->message);
            }
        }
        return true;
    }


    public function confirm_received_integration($application_from, $id_bk, $sale, $delivery, $deliveryItems)
    {
        $application_from = strtolower($application_from);
        $integration = $this->findApiIntegrationByType($application_from . '_confirm_received');
        switch ($application_from) {
            case 'testing':
                return true;
                break;
            case 'sid':
                return $this->send_confirm_received_sid($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            case 'kwsg':
                return $this->send_confirm_received_kwsg($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            case 'lbps': //erp 
            case 'apw': //erp
            case 'ppcp': //erp
            case 'mmm': //erp
            case 'gsda': //erp
            case 'sbp': //erp
            case 'pmp': //erp
            case 'pas': //erp
            case 'erp':
                return true;
                break;
            case 'big':
                return $this->send_confirm_received_big($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            case 'snj':
                return $this->send_confirm_received_snj($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            case 'igiri':
            case 'scsp':
                return $this->send_confirm_received_igiri($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            case 'jbu':
                return $this->send_confirm_received_jbu($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            case 'bpp':
                return $this->send_confirm_received_bpp($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            case 'cbn':
                return true;
                break;
            case 'wpu':
                return $this->send_confirm_received_snj($integration, $id_bk, $sale, $delivery, $deliveryItems);
                break;
            default:
                return false;
                break;
        }
        return false;
    }

    public function encryptKreditpro($data)
    {
        $q = $this->db->get_where(
            'api_integration',
            ['type' => "kreditpro_encrypt"],
            1
        );
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();

        $url = $integration->uri;

        $headers = [
            'Token: ' . $integration->token,
            'X-Environment:' . $integration->cf10,
            'Content-Type: application/json'
        ];

        return ($this->_post($url, $data, $headers))->data;
    }

    public function decryptKreditpro($data)
    {
        $q = $this->db->get_where(
            'api_integration',
            ['type' => "kreditpro_decrypt"],
            1
        );
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        $url = $integration->uri;

        $headers = [
            'Token: ' . $integration->token,
            'X-Environment:' . $integration->cf10,
            'Content-Type: application/json'
        ];
        return ($this->_post($url, $data, $headers, false, false))->data;
    }

    public function getUrlKreditPro()
    {
        $q = $this->db->get_where(
            'api_integration',
            ['type' => "kreditpro_url"],
            1
        );
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        return $integration->uri;
    }


    private function send_registered_customer_igiri($integration, $id_bk)
    {
        $url = $integration->uri;

        $data = [
            'debtor_no' => $id_bk,
        ];

        $headers = [
            'Key-Access: ' . $integration->token,
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response->status || $response->status != "200") {
            throw new \Exception("Gagal mengirim data ke distributor. " . $response->status);
        }
        return true;
    }

    public function registered_customer_integration($application_from, $id_bk)
    {
        $application_from = strtolower($application_from);
        $integration = $this->findApiIntegrationByType($application_from . '_registered_customer');
        switch ($application_from) {
            case 'testing':
                return true;
                break;
            case 'igiri':
            case 'scsp':
                return $this->send_registered_customer_igiri($integration, $id_bk);
                break;
        }
        return false;
    }

    public function upload_files($files, $type = null)
    {
        $integration = $this->findApiIntegrationByType('forca_upload_file');
        $client = new Client();
        switch ($type) {
            case 'base64':
                $contents = $files;
                $filename = null;
                break;
            default:
                $contents = fopen($files['tmp_name'], 'r');
                $filename = $files['name'];
        }
        $request = $client->post($integration->uri, [
            'headers' => ['Authorization' => 'Bearer ' . $integration->token],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => $contents,
                    'filename' => $filename
                ]
            ]
        ]);
        $response = json_decode($request->getBody());
        if (!$response) {
            throw new Exception("Request error!");
        } else if ($response && $response->code != 200) {
            throw new Exception("Error : " . $response->message);
        }
        return $response->data[0];
    }

    public function mandiri_param($data)
    {
        switch ($data) {
            case 'timestamp':
                $date = date('Y-m-d');
                $time = date('H:i:s.v');
                $zone = date('O');
                $timestamp = $date . 'T' . $time . 'T' . $zone;
                return $timestamp;
                break;
        }
        return false;
    }

    public function mandiri_signature($timestamp = null)
    {
        $integration = $this->findApiIntegrationByType('kredit_mandiri_generate_signature');
        $url = $integration->uri;
        $data = [
            'date'      => ($timestamp ?? $this->mandiri_param('timestamp')),
            'clientId'  => $integration->cf9
        ];
        $headers = [
            'Token: ' . $integration->token,
            'X-Environment:' . $integration->cf10,
        ];

        $response = $this->_post($url, $data, $headers, true, false);
        if (!$response) {
            throw new \Exception("Tidak dapat membaca respon data");
        } elseif ($response && $response->status == 'ERROR') {
            throw new \Exception($response->message);
        }
        return $response->data;
    }

    public function mandiri_authentication($timestamp = null)
    {
        $integration = $this->findApiIntegrationByType('kredit_mandiri_authentication');
        $url = $integration->uri;

        $headers = [
            'X-Mandiri-Key: ' . $integration->cf9,
            'X-TIMESTAMP: ' . ($timestamp ?? $this->mandiri_param('timestamp')),
            'X-SIGNATURE: ' . $this->mandiri_signature($timestamp),
            'Content-Type: application/x-www-form-urlencoded'
        ];
        $data = [
            'grant_type' => 'client_credentials'
        ];

        $response = $this->_post($url, $data, $headers, true, false, 'POST', true);
        if (!$response) {
            throw new \Exception("Tidak dapat membaca respon data");
        } elseif ($response && $response->accessToken == null) {
            throw new \Exception($response->message);
        }
        return $response->accessToken;
    }

    public function mandiri_hmac($method, $apiName, $accessToken, $json, $timestamp)
    {
        $integration = $this->findApiIntegrationByType('kredit_mandiri_generate_hmac');
        $url = $integration->uri;
        $headers = [
            'Token: ' . $integration->token,
            'X-Environment:' . $integration->cf10,
        ];
        $data = [
            'method'        => $method,
            'apiName'       => $apiName,
            'accessToken'   => $accessToken,
            'json'          => $json,
            'date'          => $timestamp,
        ];

        $response = $this->_post($url, $data, $headers, true, false);

        if (!$response) {
            throw new \Exception("Tidak dapat membaca respon data");
        } elseif ($response && $response->status == 'ERROR') {
            throw new \Exception($response->message);
        }
        return strtoupper($response->data);
    }

    public function mandiri_loanRequest($data)
    {
        $integration = $this->findApiIntegrationByType('kredit_mandiri_loan_confirmation');
        $url = $integration->uri;
        $endpoint = $integration->cf8;
        $timestamp = $this->mandiri_param('timestamp');
        $accesstoken = $this->mandiri_authentication($timestamp);
        $headers = [
            'Authorization: Bearer ' . $accesstoken,
            'X-TIMESTAMP: ' . $timestamp,
            'X-SIGNATURE: ' . $this->mandiri_hmac('POST', $endpoint, $accesstoken, json_encode($data), $timestamp),
            'Content-Type: application/json'
        ];

        $response = $this->_post($url, $data, $headers, true, true);

        if (!$response) {
            throw new \Exception("Tidak dapat membaca respon data");
        } elseif ($response && property_exists($response, 'Exception')) {
            throw new \Exception($response->Exception);
        } elseif ($response && $response->StatusCode != '00') {
            throw new \Exception("Gagal mengirim pengajuan. " . $response->Deskripsi);
        }
        return $response;
    }

    public function mandiri_loadInvoice($data)
    {
        $integration = $this->findApiIntegrationByType('kredit_mandiri_load_invoice');
        $url = $integration->uri;
        $endpoint = $integration->cf8;
        $timestamp = $this->mandiri_param('timestamp');
        $accesstoken = $this->mandiri_authentication($timestamp);
        $headers = [
            'Authorization: Bearer ' . $accesstoken,
            'Content-Type: application/json',
            'requestId: ' . getUuid(),
            'applicationId: scm',
            'X-TIMESTAMP: ' . $timestamp,
            'X-SIGNATURE: ' . $this->mandiri_hmac('POST', $endpoint, $accesstoken, json_encode($data), $timestamp),
            'accessTime: ' . round(microtime(1) * 1000),
            'Accept: application/json',
        ];

        $response = $this->_post($url, $data, $headers, true, true);

        if (!$response) {
            throw new \Exception("Tidak dapat membaca respon data");
        } elseif ($response && property_exists($response, 'Exception')) {
            throw new \Exception($response->Exception);
        } elseif ($response && $response->responseCode != '000') {
            $err = '';
            foreach ($response->errors as $k => $v) {
                $err .= $v->errorCode . ', ' . $v->errorMessage . '<br>';
            }
            throw new \Exception("Gagal mengirim Invoice. " . $err);
        }
        return $response;
    }

    // public function mandiri_loanInquiry($data)
    // {
    //     $integration = $this->findApiIntegrationByType('kredit_mandiri_loan_inquiry');
    //     $url = $integration->uri;
    //     $endpoint = $integration->cf8;
    //     $timestamp = $this->mandiri_param('timestamp');
    //     $accesstoken = $this->mandiri_authentication($timestamp);

    //     $headers = [
    //         'Authorization: Bearer '.$this->mandiri_authentication($timestamp),
    //         'Content-Type: application/json',
    //         'requestId: '.getUuid(),
    //         'applicationId: scm',
    //         'X-TIMESTAMP: '.$timestamp,
    //         'X-SIGNATURE: '.$this->mandiri_hmac('POST', $endpoint, $accesstoken, json_encode($data), $timestamp),
    //         'accessTime: '.date('Y-m-d').' '.date('H:i:s.v'),
    //         'Accept: application/json',
    //     ];
    //     $response = $this->_post($url, $data, $headers, true, true);

    // if (!$response) {
    //     throw new \Exception("Tidak dapat membaca respon data");
    // } elseif ($response && property_exists($response, 'Exception')) {
    //     throw new \Exception($response->Exception);
    // } elseif ($response && $response->responseCode != '000') {
    //     $err = '';
    //     foreach ($response->errors as $k => $v) {
    //         $err .= $v->errorCode . ', ' . $v->errorMessage . '<br>';
    //     }
    //     throw new \Exception("Gagal mendapatkan data. " . $err);
    // }
    // return $response;
    // }

    public function mandiri_limitInquiry($data)
    {
        $integration = $this->findApiIntegrationByType('kredit_mandiri_limit_inquiry');
        $url = $integration->uri;
        $endpoint = $integration->cf8;
        $timestamp = $this->mandiri_param('timestamp');
        $accesstoken = $this->mandiri_authentication($timestamp);

        $headers = [
            'Authorization: Bearer ' . $accesstoken,
            'Content-Type: application/json',
            'X-TIMESTAMP: ' . $timestamp,
            'X-SIGNATURE: ' . $this->mandiri_hmac('POST', $endpoint, $accesstoken, json_encode($data), $timestamp),
            'Accept: application/json',
        ];
        $response = $this->_post($url, $data, $headers, true, true);
        // var_dump($url, $data, $headers,$response);die;

        if (!$response) {
            throw new \Exception("Tidak dapat membaca respon data");
        } elseif ($response && property_exists($response, 'Exception')) {
            throw new \Exception($response->Exception);
        } elseif ($response && ($response->limitInquiryServiceResponse->return->errorCode == '0001' || $response->limitInquiryServiceResponse->return->errorCode == '0002')) {
            throw new \Exception("Gagal mengecek limit. " . $response->limitInquiryServiceResponse->return->errorMessage);
        }
        return $response->limitInquiryServiceResponse->return;
    }

    private function putFileToMft($sftp, $name, $imageUrl){
        $pathMft = "/opt/seeasown/SEEBURGERBISLinkPLUS/Mandiri/SEMEN01/Outbound/";
        $tmpHandle = tmpfile();
        $metaDatas = stream_get_meta_data($tmpHandle);
        $tmpFilename = $metaDatas['uri'];
        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
        fwrite($tmpHandle, file_get_contents($imageUrl));
        $sftp->put($pathMft . $name . "." . $extension, $tmpFilename, SFTP::SOURCE_LOCAL_FILE);
        unlink($tmpFilename); 
    }

    public function sendFileToMft($loan_id)
    {
        $integration = $this->integration->findApiIntegrationByType('kredit_mandiri_mft');
        $loan = $this->at_site->getLoanRequest(['id' => $loan_id]);
        $sftp = new SFTP($integration->uri);
        if (!$sftp->login($integration->username, $integration->password)) {
            throw new Exception("Login SFTP Failed");
        }
        $this->putFileToMft($sftp, $loan->SellerID . "_Selfie", $loan->foto);
        $this->putFileToMft($sftp, $loan->SellerID . "_KTP", $loan->foto_ktp);
        $this->putFileToMft($sftp, $loan->SellerID . "_NPWP", $loan->foto_npwp);
        return true;
    }

    public function kurBtnPengajuanKredit($data=[])
    {
        $type = 'kur_bank_btn_pengajuan_kredit';
        $integration = $this->findApiIntegrationByType($type);
        $url = $integration->uri;
        $data['access_key'] = $integration->token;
        $headers = [
             'Content-Type: application/json',
        ];
        $result = $this->_post($url, $data, $headers);
        if(!$result){
            throw new Exception('Tidak mendapatkan respon yang valid');
        } else if($result->error){
            throw new Exception('Gagal mengirimkan data ke Bank BTN : '.$result->message);
        }
        return $result;
    }

    private function getTokenNotifikasi($where)
    {
        $q = $this->db->get_where('notifications', $where);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function notification_atmobile($notification, $data, $user_id)
    {
        $integration    = $this->integration->findApiIntegrationByType('notifikasi_aksestoko_mobile');
        $token_user     = $this->getTokenNotifikasi(['created_by' => $user_id]);
        foreach($token_user as $key){
            $android = [
                'priority' => 'high',
                'ttl'      => '3600s'
            ];

            $json = [
                'to'                => $key->token,
                'priority'          => 'high',
                'content_available' => true,
                'android'           => $android,
                'time_to_live'      => 3600,
                'notification'      => $notification,
                'data'              => $data
            ];

            $headers = [
                'Authorization : key=' . $integration->token,
                'Content-Type  : application/json'
            ];

            $response = $this->_post($integration->uri, $json, $headers);
        }
        return $response;
    }
    
    public function add_jira_issue($data)
    {
        $integration = $this->findApiIntegrationByType('jira_add_issue');
        $url = $integration->uri;
        $data['fields']['assignee']['name'] = $integration->cf8;
        
        $headers = [
            'Authorization: Basic ' . base64_encode($integration->username . ":" . $integration->password),
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $response = $this->_post($url, $data, $headers);
        if(!$response){
            throw new Exception('Tidak mendapatkan respon yang valid');
        } else if(property_exists($response, 'errors')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errors));
        } else if(property_exists($response, 'errorMessages')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errorMessages));
        }
        return $response;
    }

    public function add_jira_comment($data)
    {
        $integration = $this->findApiIntegrationByType('jira_add_issue');
        $url = $integration->uri;
        $url = $url . "/" . $data['issueId'] . "/comment";
        
        $headers = [
            'Authorization: Basic ' . base64_encode($integration->username . ":" . $integration->password),
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $response = $this->_post($url, $data, $headers);
        if(!$response){
            throw new Exception('Tidak mendapatkan respon yang valid');
        } else if(property_exists($response, 'errors')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errors));
        } else if(property_exists($response, 'errorMessages')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errorMessages));
        }
        return $response;
    }

    public function search_jira_issues($data)
    {
        $integration = $this->findApiIntegrationByType('jira_search_issues');
        $url = $integration->uri;
        $url = $url . '?jql=cf[10801]~'.$data['username'].'%20AND%20cf[10800]="' . $data['source_url'] . '"+order+by+createddate&maxResults=5';
        $headers = [
            'Authorization: Basic ' . base64_encode($integration->username . ":" . $integration->password),
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $response = $this->_post($url, [], $headers, false, false, "GET");
        if(!$response){
            throw new Exception('Tidak mendapatkan respon yang valid');
        } else if(property_exists($response, 'errors')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errors));
        } else if(property_exists($response, 'errorMessages')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errorMessages));
        }
        return $response->issues;
    }

    public function get_jira_issue($data)
    {
        $integration = $this->findApiIntegrationByType('jira_add_issue');
        $url = $integration->uri . '/' . $data['issueId'];

        $headers = [
            'Authorization: Basic ' . base64_encode($integration->username . ":" . $integration->password),
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $response = $this->_post($url, [], $headers, false, false, "GET");
        if(!$response){
            throw new Exception('Tidak mendapatkan respon yang valid');
        } else if(property_exists($response, 'errors')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errors));
        } else if(property_exists($response, 'errorMessages')){
            throw new Exception('Gagal mengirimkan data ke Jira : ' . current($response->errorMessages));
        }
        return $response;
    }
}

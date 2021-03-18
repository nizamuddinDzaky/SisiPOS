<?php

use \Firebase\JWT\JWT;

class Integration_atl_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Companies_model', 'companies');
        $this->load->model('Sales_model', 'sales');
        $this->load->model('Site', 'site');
        $this->load->model('settings_model');
        $this->key = ATL_TOKEN;
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

    private function _post($url, $data, $headers, $ssl = false, $jsonEncode = true, $method = "POST")
    {
        // ob_start();

        if ($jsonEncode) {
            $data = json_encode($data);
        }

        $curlHandle = curl_init($url);

        if ($ssl) {
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
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

    function convertStatusReverse($type, $status)
    {
        switch ($type) {
            case 'order':
                switch ($status) {
                    case 'pending':
                        return "111";
                        break;
                    case 'confirmed':
                    case 'reserved':
                        return "112";
                        break;
                    case 'canceled':
                        return "113";
                        break;
                    case 'closed':
                        return "116";
                        break;
                }
                break;
            case 'payment':
                switch ($status) {
                    case 'pending':
                        return "101";
                        break;
                    case 'partial':
                        return "102";
                        break;
                    case 'paid':
                        return "103";
                        break;
                    case 'accept':
                        return "311";
                        break;
                    case 'reject':
                        return "312";
                        break;
                }
                break;
            case 'delivery':
                switch ($status) {
                    case 'packing':
                        return "117";
                        break;
                    case 'delivering':
                        return "115";
                        break;
                    case 'delivered':
                        return "116";
                        break;
                    case 'delivered':
                        return "114";
                        break;
                }
                break;
        }
        return "0";
    }

    function encrypt($data = [])
    {
        $payload = [
            "iss" => base_url(),
            "aud" => "https://www.aksestoko.com",
            "sub" => "token_integrasi_aksestoko_liferay",
            "name" => "ForcaPOS",
            "iat" => time(),
            "username" => ATL_USERNAME,
            "password" => ATL_PASSWORD
        ];

        $payload = array_merge($payload, $data);

        return JWT::encode($payload, $this->key, 'HS256');
    }

    function decrypt($token)
    {
        return JWT::decode($token, $this->key, ['HS256']);
    }

    // {
    //     "orderid": "1000",
    //     "statusorderid": "102",
    //     "perubahanharga": "1001000",
    //     "datetime": "2020-06-18 20:00:00"
    // }
    public function update_order_atl($sale_id)
    {
        $integration = $this->findApiIntegrationByType('atl_update_order');
        $url = $integration->uri;

        $sale = $this->sales->getSalesById($sale_id);
        $order_atl = $this->sales->getOrderAtlBySaleId($sale_id);
        $distributor = $this->site->getCompanyByID($sale->biller_id);

        $data = [
            'orderid' => $order_atl->orderid,
            'statusorderid' => $this->convertStatusReverse('order', $sale->sale_status),
            'perubahanharga' => (int) $sale->grand_total,
            'datetime' => $sale->updated_at
        ];

        $payload = [
            "dist_code" => $distributor->cf1
        ];

        $headers = [
            'Authorization: Bearer ' . $this->encrypt($payload),
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response) {
            throw new \Exception("Gagal memperbarui pesanan ke AksesToko. Server tidak memberikan response yang benar.");
        } else if (!$response->status || $response->status != '200') {
            throw new \Exception("Gagal memperbarui pesanan ke AksesToko. " . $response->message);
        }
        return $response;
    }

    // {
    //     "paymentid": "P1000",
    //     "statuspaymentid": "201"
    // }
    public function confirm_payment_atl($payment_atl_id)
    {
        $integration = $this->findApiIntegrationByType('atl_confirm_payment');
        $url = $integration->uri;

        $payment_atl = $this->sales->getPaymentTmpAtlById($payment_atl_id);
        $sale = $this->sales->getSalesById($payment_atl->sale_id);
        $distributor = $this->site->getCompanyByID($sale->biller_id);

        $data = [
            'paymentid' => $payment_atl->paymentid,
            'statuspaymentid' => $this->convertStatusReverse('payment', $payment_atl->status)
        ];

        $payload = [
            "dist_code" => $distributor->cf1
        ];

        $headers = [
            'Authorization: Bearer ' . $this->encrypt($payload),
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response) {
            throw new \Exception("Gagal mengonfirmasi pembayaran ke AksesToko. Server tidak memberikan response yang benar.");
        } else if (!$response->status || $response->status != '200') {
            throw new \Exception("Gagal mengonfirmasi pembayaran ke AksesToko. " . $response->message);
        }
        return $response;
    }

    // {
    //     "doid": "D100",
    //     "docode": "DO/AT/2000/2093",
    //     "orderid": "1000",
    //     "driver": "Budi Susanti",
    //     "contactpersondriver": "08212892391",
    //     "metodepengiriman": "301",
    //     "tanggalpengiriman": "2020-06-20 20:00:00",
    //     "order_detail": [
    //        {
    //           "dodetailid": "DT001",
    //           "doid": "D100",
    //           "productcode": "123-821213",
    //           "qty": "1"
    //        }
    //     ]
    // }
    public function insert_delivery_atl($delivery_id)
    {
        $integration = $this->findApiIntegrationByType('atl_insert_delivery');
        $url = $integration->uri;

        $delivery = $this->sales->getDeliveryByID($delivery_id);
        $delivery_items = $this->sales->getDeliveryItemsByDeliveryId($delivery_id);
        $sale = $this->sales->getSalesById($delivery->sale_id);
        $order_atl = $this->sales->getOrderAtlBySaleId($delivery->sale_id);
        $distributor = $this->site->getCompanyByID($sale->biller_id);

        $data = [
            'doid' => $delivery->id,
            'docode' => $delivery->do_reference_no . '-' . $distributor->id,
            'orderid' => $order_atl->orderid,
            'driver' => $delivery->delivered_by && $delivery->delivered_by != "" ? $delivery->delivered_by : '-',
            'contactpersondriver' => '0',
            'metodepengiriman' => $this->convertStatusReverse('delivery', $delivery->status),
            'tanggalpengiriman' => $delivery->delivering_date,
            'orderdetail' => []
        ];

        foreach ($delivery_items as $i => $di) {
            $data['orderdetail'][] = [
                'dodetailid' => $di->id,
                'doid' => $delivery->id,
                'productcode' => $di->product_code,
                'qty' => (int) $di->quantity_sent
            ];
        }

        $payload = [
            "dist_code" => $distributor->cf1
        ];

        $headers = [
            'Authorization: Bearer ' . $this->encrypt($payload),
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response) {
            throw new \Exception("Gagal mengirimkan pengiriman ke AksesToko. Server tidak memberikan response yang benar.");
        } else if (!$response->status || $response->status != '200') {
            throw new \Exception("Gagal mengirimkan pengiriman ke AksesToko. " . $response->message);
        }
        return $response;
    }

    // {
    //     "paymentid": "P1000",
    //      "paymentmethodid": "1",
    //      "orderid": "1000",
    //      "bank": "Mandiri",
    //      "no_rek": "4004021232",
    //      "paymentamount": "150000",
    //      "debtamount": "100000",
    //      "totalharga": "250000",
    //      "statuspaymentid": "302",
    //      "active_": true,
    //      "image": "https://i.ibb.co/kGtkb0n/6439a775903f.png",
    //      "tempo": "30",
    //      "createddate": "2020-06-21 09:00:21"
    // }      
    public function insert_payment_atl($payment_id)
    {
        $integration = $this->findApiIntegrationByType('atl_insert_payment');
        $url = $integration->uri;

        $payment = $this->sales->getPaymentByID($payment_id);
        $sale = $this->sales->getSalesById($payment->sale_id);
        $order_atl = $this->sales->getOrderAtlBySaleId($sale->id);
        $distributor = $this->site->getCompanyByID($sale->biller_id);

        $image = filter_var($payment->attachment, FILTER_VALIDATE_URL) ? $payment->attachment : base_url('files/' . $payment->attachment);

        $data = [
            'paymentid' => $payment->id,
            'paymentmethodid' => $order_atl->paymentmethodid,
            'orderid' => $order_atl->orderid,
            'bank' => $order_atl->bank,
            'no_rek' => $order_atl->no_rek,
            'paymentamount' => (int) $payment->amount,
            'debtamount' => (int) ($sale->grand_total - $sale->paid),
            'totalharga' => (int) $sale->grand_total,
            'statuspaymentid' => '311',
            'active_' => true,
            'image' => $image,
            'tempo' => $order_atl->tempo,
            'createddate' => $payment->date
        ];

        $payload = [
            "dist_code" => $distributor->cf1
        ];

        $headers = [
            'Authorization: Bearer ' . $this->encrypt($payload),
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response) {
            throw new \Exception("Gagal mengirimkan pembayaran ke AksesToko. Server tidak memberikan response yang benar.");
        } else if (!$response->status || $response->status != '200') {
            throw new \Exception("Gagal mengirimkan pembayaran ke AksesToko. " . $response->message);
        }

        $data['sale_id'] = 'accept';
        $data['company_id'] = $distributor->id;
        $data['payment_id'] = $payment->id;
        $data['paymentid'] = $response->paymentid;
        $data['status'] = 'accept';
        $insert_payment_atl = $this->sales_model->insert_payment_atl($data);
        if (!$insert_payment_atl) {
            throw new \Exception("Gagal menambahkan data pembayaran ke tabel `atl_payments`");
        }

        return $response;
    }

    public function insert_or_update_price_group_atl($company_id, $id_pg = null)
    {
        if ($id_pg) {
            $id_pg = $id_pg == 'null' ? null : $id_pg;
            $where = ['id' => $id_pg];
        }
        $integration    = $this->findApiIntegrationByType('atl_insert_or_update_price_group');
        $url            = $integration->uri;
        $distributor    = $this->site->getCompanyByID($company_id);
        $data           = [];
        $price_groups   = $this->site->getPriceGroups($company_id, $where);
        $products       = $this->site->getProducts($company_id);
        foreach ($products as $p) {
            $unit = $this->site->getUnitByID($p->sale_unit);
            $p->sale_unit_code = $unit->code;
        }
        foreach ($price_groups as $pg) {

            $customers = $this->site->getCompanyByPriceGroup($pg->id);
            $customers_response = [];
            foreach ($customers as $c) {
                $isIdc = substr($c->cf1, 0, 4);
                if ($isIdc == 'IDC-') {
                    $customer_code = substr($c->cf1, 4);
                    $customers_response[] = [
                        'customer_id'         => $c->id,
                        'customer_name'       => $c->name,
                        'customer_company'    => $c->company,
                        'customer_code'       => $customer_code
                    ];
                }
            }

            $products_response = [];
            foreach ($products as $p) {
                $product_price = $this->site->getProductGroupPrice($p->id, $pg->id);
                $products_response[] = [
                    'product_id'              => $p->id,
                    'product_code'            => $p->code,
                    'product_name'            => $p->name,
                    'product_uom'             => $p->sale_unit_code,
                    'product_price'           => (int) ($product_price->price && $product_price->price != 0 ? $product_price->price : $p->price),
                    'product_credit_price'    => (int) ($product_price->price_kredit && $product_price->price_kredit != 0 ? $product_price->price_kredit : $p->credit_price),
                    'product_multiple'        => $product_price->is_multiple ? true : false,
                    'product_min_order'       => (int) ($product_price->min_order && $product_price->min_order != 0 ? $product_price->min_order : 1)
                ];
            }

            $data[] = [
                'dist_code'         => $distributor->cf1,
                'price_group_id'    => $pg->id,
                'price_group_name'  => $pg->name,
                'customers_rows'    => count($customers_response),
                'customers'         => $customers_response,
                'products_rows'     => count($products_response),
                'products'          => $products_response
            ];
        }

        $customers_pg_null = $this->site->getCompanyByPriceGroup(null, $company_id);
        if (count($customers_pg_null) > 0 && !$id_pg) {

            $customers_response = [];
            foreach ($customers_pg_null as $i => $c) {
                $isIdc = substr($c->cf1, 0, 4);
                if ($isIdc == 'IDC-') {
                    $customer_code = substr($c->cf1, 4);
                    $customers_response[] = [
                        'customer_id' => $c->id,
                        'customer_name' => $c->name,
                        'customer_company' => $c->company,
                        'customer_code' => $customer_code
                    ];
                }
            }

            $products_response = [];
            foreach ($products as $i => $p) {
                $products_response[] = [
                    'product_id' => $p->id,
                    'product_code' => $p->code,
                    'product_name' => $p->name,
                    'product_uom' => $p->sale_unit_code,
                    'product_price' => (int) $p->price,
                    'product_credit_price' => (int) $p->credit_price,
                    'product_multiple' => false,
                    'product_min_order' => 1,
                ];
            }

            $data[] = [
                'dist_code'         => $distributor->cf1,
                'price_group_id'    => null,
                'price_group_name'  => null,
                'customers_rows'    => count($customers_response),
                'customers'         => $customers_response,
                'products_rows'     => count($products_response),
                'products'          => $products_response
            ];
        }

        $payload = [
            "dist_code" => $distributor->cf1
        ];

        $headers = [
            'Authorization: Bearer ' . $this->encrypt($payload),
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        if (!$response) {
            throw new \Exception("Gagal mengirimkan data price group ke AksesToko. Server tidak memberikan response yang benar.");
        } else if (!$response->status || $response->status != '200') {
            throw new \Exception("Gagal mengirimkan data price group ke AksesToko. " . $response->message . ' ' . $response->datas[0] . ' ' . $response->datas[1]);
        }

        return $response;
    }

    public function insert_or_edit_bank_atl($id_bank, $flag)
    {
        if ($flag == 'insert') {
            $integration    = $this->findApiIntegrationByType('atl_insert_bank');
        } else if ($flag == 'update') {
            $integration    = $this->findApiIntegrationByType('atl_update_bank');
        }
        $url            = $integration->uri;
        $bank           = $this->site->getBankByID($id_bank);
        $distributor    = $this->site->getCompanyByID($bank->company_id);

        if ($bank->bank_name == 'bni') {
            $bank->logo = 'https://i.ibb.co/8bc8ghN/bni.png';
        } else if ($bank->bank_name == 'mandiri') {
            $bank->logo = 'https://i.ibb.co/p1Wh48P/mandiri.png';
        } else if ($bank->bank_name == 'bca') {
            $bank->logo = 'https://i.ibb.co/xDrwZdK/bca.png';
        } else if ($bank->bank_name == 'bri') {
            $bank->logo = 'https://i.ibb.co/r3cd718/bri.png';
        } else {
            $bank->logo = base_url() . $bank->logo;
        }

        $data = [
            'bankid'                => $id_bank,
            'bankname'              => $bank->bank_name,
            'bisniskokohid'         => $distributor->cf1,
            'distributor'           => $distributor->name,
            'image'                 => $bank->logo,
            'namapemilikrekening'   => $bank->name,
            'rekening'              => $bank->no_rekening,
            'active'                => $bank->is_active = 1 ? true : false
        ];

        $payload = [
            "dist_code" => $distributor->cf1
        ];

        $headers = [
            'Authorization: Bearer ' . $this->encrypt($payload),
            'Content-Type: application/json'
        ];

        $response = ($this->_post($url, $data, $headers));
        return $response;
    }
}

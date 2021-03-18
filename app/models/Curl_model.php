<?php

/*
 * Copyright (c) 2018 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */

class Curl_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->config('ion_auth', true);
    }

    public function addEcomerce($old)
    {
        $companies = $this->site->getCompanyByID($this->session->userdata('biller_id'));
//        $url = 'http://10.15.3.190/bangunan/merchantapp/api/_toko';
        $url = $this->MateriaLink . 'merchantapp/api/_toko';
        $params = array(
            "company" => $companies->company,
            "nama_toko" => $companies->company,
            "image1" => base_url() . "assets/uploads/avatars/thumbs/" . $companies->logo,
            "image2" => '',
            "image3" => '',
            "image4" => '',
            "image5" => '',
            "email" => $companies->email,
            "address" => $companies->address,
            "phone" => $companies->phone,
            "npwp" => $companies->vat_no,
            "prov" => $companies->country,
            "kota" => $companies->city,
            "kec" => $companies->state,
            "latitude" => $companies->latitude,
            "longitude" => $companies->longitude,
            "biller_id" => $companies->id,
            "mtid" => $companies->mtid,
            "password" => $old
        );

        $data = $this->_post($url, $params);
        $json = json_decode($data, true);
        if ($json['code'] = '1') {
            $this->db->where('id', $json['details']['biller_id']);
            if ($this->db->update('companies', array('mtid' => $json['details']['mtid']))) {
                return true;
            }
        } else {
            $getmtid = $url . '?biller_id=' . $this->session->userdata('biller_id');
            $json = json_decode($getmtid, true);
            $this->db->where('id', $json['details']['biller_id']);
            if ($this->db->update('companies', array('mtid' => $json['details']['mtid']))) {
                return true;
            }
        }
        return false;
    }

    public function updateEcomerce($biller, $password = null)
    {
        $companies = $this->site->getCompanyByID($biller);
//                $url = 'http://10.15.3.190/bangunan/merchantapp/api/_toko';
        $url = $this->MateriaLink . 'merchantapp/api/_toko';
        $params = array("company" => $companies->company,
            "nama_toko" => $companies->company,
            "image1" => base_url() . "assets/uploads/avatars/thumbs/" . $companies->logo,
            "image2" => '',
            "image3" => '',
            "image4" => '',
            "image5" => '',
            "email" => $companies->email,
            "address" => $companies->address,
            "phone" => $companies->phone,
            "npwp" => $companies->vat_no,
            "prov" => $companies->country,
            "kota" => $companies->city,
            "kec" => $companies->state,
            "latitude" => $companies->latitude,
            "longitude" => $companies->longitude,
            "biller_id" => $companies->id,
            "mtid" => $companies->mtid,
            "password" => $password
        );
        if ($companies->mtid != null) {
            $data = $this->_put($url, $params);
            $json = json_decode($data, true);
            return true;
        }
        return false;
    }

    public function get_EProduct($product_id)
    {
        $companies = $this->site->getCompanyByID($this->session->userdata('biller_id'));
        $product = $this->site->getProductByID($product_id);
        $uomsales = $this->site->getUnitByID($product->sale_unit);
        $wh0qty = $this->site->getWarehouseProducts($product_id);
        $brand = $this->site->getBrandByID($product->brand);
//        $url = 'http://10.15.3.190/bangunan/merchantapp/api/_product';
        $url = $this->MateriaLink . 'merchantapp/api/_product';
        $params = array(
            'name' => $product->name,
            'public_price' => $product->price_public,
            'image' => base_url() . 'assets/uploads/' . $product->image,
            'min_quantity' => $product->e_minqty,
            'sub_category' => $product->subcategory_id ? $product->subcategory_id : $product->category_id,
            'description' => $product->product_details ? $product->product_details : 'No Description',
            'weight' => $product->weight,
            'product_id' => $product->id,
            'mtid' => $companies->mtid,
            'base_uom' => $uomsales->name,
            'stock' => $this->sma->formatDecimal($wh0qty[0]->quantity) ? $this->sma->formatDecimal($wh0qty[0]->quantity) : $this->sma->formatDecimal(0),
            'brand_id' => $brand->id,
            'brand_name' => $brand->name
        );
        if ($companies->mtid != null && $product->public == 1) {
            $getmtid = $url . '?product_id=' . $product_id;
            $json = json_decode($getmtid, true);
            $this->add_EProduct($url, $params);
            $this->update_EProduct($url, $params);
        }
        return false;
    }

    public function add_EProduct($url, $params)
    {
        $data = $this->_post($url, $params);
        $json = json_decode($data, true);
        if ($json['code'] = '1') {
            $this->db->where('id', $json['details']['product_id']);
            $this->db->update('products', array('item_id' => $json['details']['item_id']));
            return true;
        }
        return true;
    }

    public function update_EProduct($url, $params)
    {
        $data = $this->_put($url, $params);
        $json = json_decode($data, true);
        if ($json['code'] = '1') {
            $this->db->where('id', $json['details']['product_id']);
            $this->db->update('products', array('item_id' => $json['details']['item_id']));
            return true;
        }
        return false;
    }

    public function supplier_userinfo($supplier, $code, $type)
    {
        $json = array('code' => 'error', 'message' => 'Supplier Belum Terintegrasi');
        $json = json_encode($json);
        $json = json_decode($json, true);
        $this->db->where('supplier_id', $supplier);
        $this->db->where('type', $type);
        $q = $this->db->get('companies_supplier');
        if ($q->num_rows() > 0) {
            $res = $q->row();

            switch ($res->type) {
                case "forca_userinfo":
                    $json = $this->erp_userinfo($res, $code);
                    break;
                case "forca_userregister":
                    $json = $this->erp_userinsert($res, $code);
                    break;
                case "forca_userorder":
                    $json = $this->erp_userorder($res, $code);
                    break;
                case "forca_userinvoice":
                    $json = $this->erp_userinvoice($res, $code);
                    break;
            }
        }
        return $json;
    }

    public function erp_gettoken($res, $code = null)
    {
        $this->db->where('supplier_id', $res->supplier_id);
        $this->db->where('type', 'forca_token');
        $q = $this->db->get('api_integration');
        $res = $q->row();
        $url = $res->uri;
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $body = "username=$res->username&password=$res->password&ad_client_id=$res->cf1&ad_role_id=$res->cf2&ad_org_id=$res->cf3&m_warehouse_id=$res->cf4";
        $data = $this->_posterp($url, $header, $body);
        $token = json_decode($data, true);
        $token = $token['resultdata']['token'];
        return $token;
    }

    public function erp_userinfo($res, $code)
    {
        $url = $res->uri;
        $body = "sap_code=$code&id_customer=$code";
        $token = $this->erp_gettoken($res);

        $header = array('Content-Type: application/x-www-form-urlencoded', 'Forca-Token: ' . $token);
        $data = $this->_posterp($url, $header, $body);
        $json = json_decode($data, true);
        return $json;
    }

    public function erp_userinsert($res, $code)
    {
        $url = $res->uri;
        $body = "sap_code=$code&id_customer=10002100&name_cust= &pos_customer_id= &name_address= &address= &phone=";
        $token = $this->erp_gettoken($res);
        $header = array('Content-Type: application/x-www-form-urlencoded', 'Forca-Token: ' . $token);
        $data = $this->_posterp($url, $header, $body);
        $json = json_decode($data, true);
        return $json;
    }

    public function erp_userorder($res, $code)
    {
        $url = $res->uri;
        $body = "c_bpartner_id=$code&product_code=10002100&qty= &pos_order_id= &dateorder= &retail_id= ";
        $token = $this->erp_gettoken($res);
        $header = array('Content-Type: application/x-www-form-urlencoded', 'Forca-Token: ' . $token);
        $data = $this->_posterp($url, $header, $body);
        $json = json_decode($data, true);
        return $json;
    }

    public function erp_userinvoice($res, $code)
    {
        $url = $res->uri;
        $body = "retail_id";
        $token = $this->erp_gettoken($res);
        $header = array('Content-Type: application/x-www-form-urlencoded', 'Forca-Token: ' . $token);
        $data = $this->_posterp($url, $header, $body);
        $json = json_decode($data, true);
        return $json;
    }

    public function erp_getproduct($name, $supplier)
    {
        $q = $this->db->get_where('api_integration', array('supplier_id' => $supplier, 'type' => 'forca_userproduct'), 1);
        if ($q->num_rows() > 0) {
            $res = $q->row();
            $url = $res->uri;
            $token = $this->erp_gettoken($res);
            $header = array('Content-Type: application/x-www-form-urlencoded', 'Forca-Token: ' . $token);
            $body = "name=" . $name;
            $data = $this->_posterp($url, $header, $body);
            $result = json_decode($data, true);
            if ($result['resultdata'][0]['M_Product_ID']) {
                return $result['resultdata'][0]['M_Product_ID'];
            }
            return false;
        }
        return false;
    }

    public function _posterp($url, $header, $body)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function _post($url, $params, $ssl = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
            curl_setopt($ch, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");
        }

        $data = curl_exec($ch);

        curl_close($ch);
        return $data;
    }

    public function _get($url, $header = null)
    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        if($header){
//            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//        }
//        $data = curl_exec($ch);
//        curl_close($ch);
//
//        return $data;
        if ($header) {
            $opts = array(
                'http' => array(
                    'method' => "GET",
                    'header' => $header
                )
            );
        } else {
            $opts = array(
                'http' => array(
                    'method' => "GET"
                )
            );
        }
        $context = stream_context_create($opts);
        $data = file_get_contents($url, false, $context);

        return $data;
    }

    public function _put($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, 'Content-Type: application/json');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function _delete($params)
    {
    }
}

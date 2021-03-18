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

/**
 * Description of Official_model
 *
 * @author adminSISI
 */
class Official_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->config('ion_auth', true);
        $this->load->model('Curl_model');
    }
    
    public function getPartner($group_name)
    {
        $q = $this->db->get_where('companies', array('group_name' => $group_name, 'company_id' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getAllParnerNumber()
    {
        $q = $this->db->get_where('partner', array('compay_id' => $this->session->userdata('company_id')));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->supplier_id] = $row->reference_code_1;
            }
            return $data;
        }
        return false;
    }
    public function getParnerNumberbyID($id=null)
    {
        $q = $this->db->get_where('partner', array('compay_id' => $this->session->userdata('company_id'),'supplier_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row->reference_code_1;
            }
            return $data;
        }
        return false;
    }
    
    public function getPurchaseOffByPurchaseID($id)
    {
        $q = $this->db->get_where('purchases_official', array('purchase_items_id'=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    /* R E G I S T E R   T O   P A R T N E R */
    public function add_partner($return)
    {
        $reference = $this->db->get_where('partner', array('compay_id' => $this->session->userdata('company_id'),'supplier_id' => $return["supplier_id"]));
        if ($reference->num_rows() > 0 && $return["reference_code_1"] != "daftar") {// update number reference
            $this->db->update('partner', array("reference_code_1"=> $return["reference_code_1"], 'update'=>date('Y-m-d H:i:s')), array('compay_id' => $this->session->userdata('company_id'),'supplier_id' => $return["supplier_id"]));
        } else { // insert when no exist
            $data = array(
                "compay_id"=> $this->session->userdata('company_id'),
                "supplier_id"=>$return["supplier_id"],
                "reference_code_1"=> $return["reference_code_1"],
                "update" => date('Y-m-d H:i:s')
            );
            $this->db->insert('partner', $data); // insert into partner API
        }
        
        $json = $this->register_to_partner($return);
        return $json;
    }
    
    public function register_to_partner($return)
    {
        // cek api link to partner if null not send to partner
        if ($return["reference_code_1"] == "daftar") { // kondisi new partner register
            $q = $this->db->get_where('api_integration', array('supplier_id' => $return["supplier_id"],"type" => "forca_userregister"));
            if ($q->num_rows() > 0) {
                $res = $q->row();
                $url = $res->uri;
                $pos_company=$this->site->getCompanyByID($this->session->userdata('company_id'));
                $body = "sap_code=". str_replace("SAP-", "", $pos_company->cf2)."&id_customer=".str_replace("IDC-", "", $pos_company->cf1)."&name=".$pos_company->name."&ref_customer_id=".$this->session->userdata('company_id')."&name_address=".$pos_company->city."&phone=".$pos_company->phone."&address_customer=".$pos_company->address." ";
                $token = $this->Curl_model->erp_gettoken($res);

                $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
                $data = $this->Curl_model->_posterp($url, $header, $body);
                $json = json_decode($data, true);

                if ($json['codestatus'] == "S" && $json['resultdata']['ref_customer_request_id']) {
                    $this->db->update('partner', array("waiting_code_1"=> $json['resultdata']['ref_customer_request_id'], 'update'=> date('Y-m-d H:i:s')), array('compay_id' => $this->session->userdata('company_id'),'supplier_id' => $return["supplier_id"]));
                }
                return $json;
            }
        } else {
            $json = $this->check_to_partner($return);
            return $json;
        }
        return true;
    }
    /* E N D   R E G I S T E R   T O   P A R T N E R */
    
    public function check_to_partner($return)
    {
        // kondisi Partner Check
        $q = $this->db->get_where('api_integration', array('supplier_id' =>$return["supplier_id"],"type" => "forca_userinfo"));
        if ($q->num_rows() > 0) {
            $res = $q->row();
            $url = $res->uri;
            $body = "c_bpartner_id=".$return["reference_code_1"]."";
            $token = $this->Curl_model->erp_gettoken($res);
            
            $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
            $data = $this->Curl_model->_posterp($url, $header, $body);
            $json = json_decode($data, true);
            return $json;
        }
        return true;
    }
    
    public function order_to_partner($data, $items, $order_id)
    {
        // cek partner code
        if ($data['status'] == "ordered") {
            $partners = $this->db->get_where('partner', array('compay_id' => $this->session->userdata('company_id'),'supplier_id' => $data["supplier_id"]));
            $q = $this->db->get_where('api_integration', array('supplier_id' => $data["supplier_id"],"type" => "forca_userorder"));
            $body="multiple_qty=";
            foreach ($items as $item) {
                $body.=$item['quantity']."/";
            }
            $body=substr($body, 0, -1);
            $body.="&multiple_m_product_id=";
            foreach ($items as $item) {
                $product = $this->site->getProductByID($item['product_id']);
                if ($product->uuid) {
                    $body.=$product->uuid."/";
                }
            }
            $body=substr($body, 0, -1);
            if ($partners->num_rows() > 0 && $q->num_rows() > 0) {
                $res = $q->row();
                $partner = $partners->row();
                $url = $res->uri;
                $body .= "&c_bpartner_id=".$partner->reference_code_1."&ref_order_id=".$order_id."&dateorder=".date('Y-m-d', strtotime($data['date']))."&retail_id=".$this->session->userdata('company_id')."";
                $token = $this->Curl_model->erp_gettoken($res);
                $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
                $data = $this->Curl_model->_posterp($url, $header, $body);
                $json = json_decode($data, true);
                if ($json['codestatus'] == "S") {
                    $this->db->insert('purchases_official', array("purchase_items_id"=> $order_id,"pos_order_request_id" => $json['resultdata']['ref_order_request_id']));
                }
                return $json;
            }
        }
        return true;
    }
    
    /* C H E C K   S A L E S   O R D E R */
    public function status_order_partner($order_id, $supplier)
    {
        $q = $this->db->get_where('purchases_official', array('purchase_items_id' => $order_id));
        if ($q->num_rows() > 0) {
            $res = $q->row();
//            if(empty($res->invoice_reference)){
            $json = $this->invoice_partner($order_id, $supplier);
            $q = $this->db->get_where('purchases_official', array('purchase_items_id' => $order_id));
            if ($q->num_rows() > 0) {
                $res = $q->row();
            }
//            }
            return $res;
        }
    }
    
    /* N E W  I N T E G R A T I O N  F O R C A  E R P  G E T  O R D E R */
    
    public function invoice_partner($order_id, $supplier)
    {
        $q = $this->db->get_where('api_integration', array('supplier_id' => $supplier,"type" => "forca_userinvoice"));
        if ($q->num_rows() > 0) {
            $res = $q->row();
            $url = $res->uri;
            $body = "ref_order_id=".$order_id;
            $token = $this->Curl_model->erp_gettoken($res);
            
            $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
            $data = $this->Curl_model->_posterp($url, $header, $body);
            $json = json_decode($data, true);
            
            if ($json['codestatus'] == "S") {
                if ($json['resultdata'][0]['c_order_id'] != null) {
                    $q = $this->db->get_where('api_integration', array('supplier_id' => $supplier,"type" => "forca_getorder_new"));
                    if ($q->num_rows() > 0) {
                        $res = $q->row();
                        $url = $res->uri;
                        $body = "c_order_id=".$json['resultdata'][0]['c_order_id'];
                        $token = $this->Curl_model->erp_gettoken($res);

                        $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
                        $data = $this->Curl_model->_posterp($url, $header, $body);
                        $json = json_decode($data, true);
//                        var_dump($json);
//                        die();
                        if ($json['codestatus'] == "S") {
                            $total=0;
                            if ($json['resultdata']['docstatus'] == 'CO') {
                                $this->db->update(
                                    'purchases_official',
                                    array(
                                        "sales_order_id" => $json['resultdata']['c_order_id'],
                                        "order_reference" => $json['resultdata']['documentno'],
                                        "date_so" => date('Y-m-d', strtotime($json['resultdata']['dateordered'])),
                                        "shipment_reference" => $json['resultdata']['m_inout'][0]['documentno'],
                                        "date_shipment" => date('Y-m-d', strtotime($json['resultdata']['m_inout'][0]['movementdate'])),
                                        "invoice_reference" => $json['resultdata']['c_invoice'][0]['documentno'],
                                        "date_invoice" => date('Y-m-d', strtotime($json['resultdata']['c_invoice'][0]['dateinvoiced'])),
                                        "return_shipment_id"=>$json['resultdata']['m_inout'][0]['m_inout_id'],
                                        "return_invoice_id"=>$json['resultdata']['c_invoice'][0]['c_invoice_id']
                                    ),
                                    array("purchase_items_id"=>$order_id)
                                );
                            
                                foreach ($json['resultdata']['c_orderline'] as $item) {
                                    $product=$this->db->get_where('products', array('uuid'=>$item['m_product_id'],'company_id'=> $this->session->userdata('company_id')));

                                    $pi=$this->db->get_where('purchase_items', array('purchase_id'=>$order_id,'product_id'=>$product->row()->id));
                                    if ($pi->num_rows > 0 && ($pi->row()->quanity_balance == $pi->row()->quantity)) {
                                        $this->db->update('purchase_items', array('quantity_balance'=>$item['qtyinvoiced']), array('purchase_id'=>$order_id,'product_id'=>$product->row()->id));
                                    }
                                    $this->db->update('purchase_items', array('unit_cost'=>$item['priceentered'],'net_unit_cost'=>$item['priceentered'],'real_unit_cost'=>$item['priceentered'],'subtotal'=>$item['total'],'quantity'=>$item['qtyinvoiced'],'quantity_received'=>$item['qtydelivered'],'unit_quantity'=>$item['qtyinvoiced']), array('purchase_id'=>$order_id,'product_id'=>$product->row()->id));
                                    $total = $total + $item['total'];
                                }
                                
                                if ($total) {
                                    $purchase=$this->site->getPurchaseByID($order_id);
                                    $this->db->update('purchases', array('total'=>$total,'grand_total'=>($total + $purchase->total_tax + $purchase->shipping - $purchase->order_discount)), array('id'=>$order_id));
                                }
                            }
                        }
                    }
                }
            }
            return $json;
        }
    }
    
    /* O L D  I N T E G R A T I O N  F O R C A  E R P  G E T  O R D E R */
    
//    public function invoice_partner($order_id,$supplier){
//        $q = $this->db->get_where('api_integration', array('supplier_id' => $supplier,"type" => "forca_userinvoice"));
//        if($q->num_rows() > 0 ){
//            $res = $q->row();
//            $url = $res->uri;
//            $body = "ref_order_id=".$order_id;
//            $token = $this->Curl_model->erp_gettoken($res);
//
//            $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
//            $data = $this->Curl_model->_posterp($url,$header,$body);
//            $json = json_decode($data,true);
//            var_dump($json);
//            die();
//            if($json['codestatus'] == "S"){
//                $total=0;
//                foreach($json['resultdata'] as $item){
//                    $product=$this->db->get_where('products',array('uuid'=>$item['m_product_id'],'company_id'=> $this->session->userdata('company_id')));
//                    $inv_date=$item['dateinvoiced'];
//                    $shipment_date=$item['movementdate'];
//                    $so_date=$item['dateordered'];
//                    if($item['inv_documentstatus'] == 'CO'){
//                        $this->db->update('purchases_official',
//                            array(
//                                "sales_order_id" => $item['c_order_id'],
//                                "order_reference" => $item['so_documentno'],
//                                "date_so" => date('Y-m-d',strtotime($so_date)),
//                                "shipment_reference" => $item['spj_documentno'],
//                                "date_shipment" => date('Y-m-d',strtotime($shipment_date)),
//                                "invoice_reference" => $item['inv_documentno'],
//                                "date_invoice" => date('Y-m-d',strtotime($inv_date)),
//                                "return_shipment_id"=>0,
//                                "return_invoice_id"=>' '
//                            ),
//                            array("purchase_items_id"=>$order_id));
//                        $subtotal = $item['Invoice_Line_price'] * $item['QtyInvoiced'];
//                        $pi=$this->db->get_where('purchase_items',array('purchase_id'=>$order_id,'product_id'=>$product->row()->id));
//                        if($pi->num_rows > 0 && ($pi->row()->quanity_balance == $pi->row()->quantity)){
//                            $this->db->update('purchase_items',array('quantity_balance'=>$item['QtyInvoiced']),array('purchase_id'=>$order_id,'product_id'=>$product->row()->id));
//                        }
//                        $this->db->update('purchase_items',array('unit_cost'=>$item['Invoice_Line_price'],'net_unit_cost'=>$item['Invoice_Line_price'],'real_unit_cost'=>$item['Invoice_Line_price'],'subtotal'=>$subtotal,'quantity'=>$item['QtyInvoiced'],'quantity_received'=>$item['QtyInvoiced'],'unit_quantity'=>$item['QtyInvoiced']),array('purchase_id'=>$order_id,'product_id'=>$product->row()->id));
//                        $total = $total + $subtotal;
//                    }
//                }
//                if($total){
//                    $purchase=$this->site->getPurchaseByID($order_id);
//                    $this->db->update('purchases',array('total'=>$total,'grand_total'=>($total + $purchase->total_tax + $purchase->shipping - $purchase->order_discount)),array('id'=>$order_id));
//                }
//            }
//            return $json;
//        }
//    }
    
    /* O L D   I N T E G R A T E   P R O D U C T */
    public function update_product_erp($name, $supplier_id, $id)
    {
        $split_name=explode(' ', $name);
        if (sizeof($split_name)==4) {
            $size = substr($split_name[3], 0, -2);
            $unit = substr($split_name[3], 2);
            $split_name[3]=$size;
            $split_name[4]=$unit;
        }
        
        $name_product = $split_name[1].' '.$split_name[3];
        $product_id_erp=$this->Curl_model->erp_getproduct($name_product, $supplier_id);
        
        if ($product_id_erp) {
            if ($this->db->update('products', array('uuid'=>$product_id_erp), array('id'=>$id))) {
                return $product_id_erp;
            }
        }
        return false;
    }
    
    /* N E W   I N T E G R A T E   P R O D U C T */
    public function get_product_erp($name, $supplier, $m_productid=null)
    {
        $q = $this->db->get_where('api_integration', array('supplier_id'=>$supplier,'type'=>'forca_userproduct'), 1);
        if ($q->num_rows() > 0) {
            $body="";
            $res = $q->row();
            $url = $res->uri;
            $token = $this->Curl_model->erp_gettoken($res);
            $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
            if ($name) {
                $body = "name=".$name;
            } elseif ($m_productid) {
                $body = "m_product_id=".$m_productid;
            }
            $data = $this->Curl_model->_posterp($url, $header, $body);
            $result = json_decode($data, true);
            if ($result['resultdata'][0]['m_product_id']) {
                return $result['resultdata'];
            }
        }
        return false;
    }

    public function check_order($order_id)
    {
        $q = $this->db->get_where('purchases_official', array('purchase_items_id' => $order_id));
        if ($q->num_rows() > 0) {
            $res=$q->row();
            $purchase=$this->site->getPurchaseByID($order_id);
            if ($res->invoice_reference && $purchase) {
                $json=$this->update_received($res->purchase_items_id, $purchase->supplier_id);
                if ($json['codestatus']=='S') {
                    return $json['message'];
                }
            }
        }
        return false;
    }
    
    public function update_received($order_id, $supplier_id)
    {
        $q = $this->db->get_where('api_integration', array('supplier_id' => $supplier_id,"type" => "forca_productreceived"));
        if ($q->num_rows() > 0) {
            $res = $q->row();
            $url = $res->uri;
            $body = "pos_order_id=".$order_id;
            $token = $this->Curl_model->erp_gettoken($res);
            
            $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
            $data = $this->Curl_model->_posterp($url, $header, $body);
            $json = json_decode($data, true);
            return $json;
        }
    }
    
    public function payment_to_partner($payment)
    {
        $official = $this->db->get_where('purchases_official', array('purchase_items_id' => $payment['purchase_id']));
        $purchase = $this->site->getPurchaseByID($payment['purchase_id']);
        $q = $this->db->get_where('api_integration', array('supplier_id' => $purchase->supplier_id,"type" => "forca_userpayment"));
        if ($q->num_rows() > 0  && $official->num_rows() > 0) {
            $res = $q->row();
            $url = $res->uri;
            $body = "pos_order_id=".$payment['purchase_id']."&pos_payment_id=".$payment['id']."&PayAmt=".$payment['amount'];
            $token = $this->Curl_model->erp_gettoken($res);
            
            $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
            $data = $this->Curl_model->_posterp($url, $header, $body);
            $json = json_decode($data, true);
            if ($json['codestatus'] = "S") {
                $this->db->update('payments', array("sales_order_id"=> $official->row()->sales_order_id,"pos_payment_request_id" => $json['resultdata']['ref_payment_request_id']), array("purchase_id"=>$payment['purchase_id'],"pos_payment_request_id"=>null));
            }
            return $json;
        }
    }
    
    public function update_sync_product($product_id, $data)
    {
        if ($this->db->update('products', $data, array('id'=>$product_id))) {
            return true;
        }
        return false;
    }
    
    /* C H E C K   P A Y M E N T */
    public function check_payment_partner($purchase_id)
    {
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if (empty($row->c_payment_id) && !empty($row->pos_payment_request_id)) {
                    $purchase = $this->site->getPurchaseByID($purchase_id);
                    $response[]=$this->get_payment_partner($purchase->supplier_id, $row->id);
                }
                $response[]=$row;
            }
            return $response;
        }
        return false;
    }
    
    public function get_payment_partner($supplier_id, $payment_id)
    {
        $q = $this->db->get_where('api_integration', array('supplier_id' => $supplier_id,"type" => "forca_getpayment"));
        if ($q->num_rows() > 0) {
            $res = $q->row();
            $url = $res->uri;
            $body = "pos_payment_id=".$payment_id;
            $token = $this->Curl_model->erp_gettoken($res);
            
            $header = array('Content-Type: application/x-www-form-urlencoded','Forca-Token: '.$token);
            $data = $this->Curl_model->_posterp($url, $header, $body);
            $json = json_decode($data, true);
            if ($json['codestatus'] == "S" && $json['resultdata'][0]['C_Payment_ID']) {
                $this->db->update(
                    'payments',
                    array("c_payment_id"=>$json['resultdata'][0]['C_Payment_ID'],
                    "reference_dist"=>$json['resultdata'][0]['DocumentNo'],
                    "date_dist"=>$json['resultdata'][0]['Date'],"amount_dist"=>$json['resultdata'][0]['Payment Amount']),
                    array("id"=>$payment_id)
                );
            }
            return $json;
        }
    }
}

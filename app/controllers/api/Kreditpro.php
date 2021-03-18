<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_REST_Controller.php';

/** 
 * KreditPro - AksesToko
 **/ 
class Kreditpro extends MY_REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->data = $this->getTokenValue();

        $this->load->model('integration_model', 'integration');
        $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->load->model('sales_model');
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $this->load->model('aksestoko/payment_model');
    }    

    public function token_post()
    {
        try {
            $token = json_encode($this->post());
            $token = $this->encrypt($token, $this->key);
            $this->buildResponse("success", REST_Controller::HTTP_OK, "token generated", ["token" => $token]);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode(), $th->getMessage(), null);
        }
    }

    function update_orders_post(){
        $this->db->trans_begin();
        try{
            if(!$this->input->post('param')){
                throw new Exception('Undefined param', 400);
            }

            $response = json_decode($this->integration->decryptKreditpro($this->input->post('param')));

            if (!property_exists($response, 'orderId'))
                throw new Exception('Undefined Property orderId', 400);
            if (!property_exists($response, 'status'))
                throw new Exception('Undefined Property status', 400);

            $orderId = $response->orderId;

            $arrayOrderId = explode('-', $orderId);
            $purchase_data = $this->sales_model->getPurchasesByRefNo(trim($arrayOrderId[0]), trim($arrayOrderId[1]));

            if($purchase_data->payment_status == 'pending'){
                throw new Exception("Error! Customer has not submitted for KreditPro yet. ($orderId)", 400);
            } else if($purchase_data->payment_status != 'waiting'){
                throw new Exception("Error! KreditPro status has been updated before. ($orderId)", 400);
            }

            $data = [
                'payment_status' => $response->status,
            ];

            if ($response->status == 'reject') {
                $data['grand_total'] =($purchase_data->grand_total - $purchase_data->charge_third_party);
                $data['payment_duration'] = null;
                $data['charge_third_party'] = 0;
                $data['payment_type'] = '';
                $data['status'] = 'canceled';

                $sale_data = $this->sales_model->getSalesByRefNo(trim($arrayOrderId[0]), trim($arrayOrderId[1]));
            
                if(!$this->sales_model->updateStatus($sale_data->id, 'canceled', 'Di Tolak Kredit Pro', '')){
                    throw new \Exception("Update Orders failed");
                }
                
                $sale_items = $this->site->getAllSaleItems($sale_data->id);
                foreach ($sale_items as $item) {
                    if ($item->product_type == 'standard') {
                        $this->site->syncProductQtyBooking($item->product_id, $item->warehouse_id, trim($arrayOrderId[1]));
                        if (isset($item->option_id) && !empty($item->option_id)) {
                            $this->site->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->site->syncProductQtyBooking($combo_item->id, $item->warehouse_id, trim($arrayOrderId[1]));
                            }
                        }
                    } elseif ($item->product_type == 'consignment') {
                        $this->site->syncConsignmentQty($item->product_id, $item->warehouse_id);
                    }
                }
            }

            if(!$this->at_purchase->updatePurchaseById($purchase_data->id, $data)){
                throw new \Exception("Update Orders failed");
            }

            $result = [
                'orderId' => $orderId,
            ];
            
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'Update Orders success', $result);
        }catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode(), $th->getMessage(), null);
        }
    }

    public function payment_distributor_post(){
        $this->db->trans_begin();
        try{
            if(!$this->input->post('param')){
                throw new Exception('Undefined param', 400);
            }

            $response = json_decode($this->integration->decryptKreditpro($this->input->post('param')));

            if (!property_exists($response, 'orderId'))
                throw new Exception('Undefined Property orderId', 400);
            if (!property_exists($response, 'image'))
                throw new Exception('Undefined Property image', 400);

            $orderId = $response->orderId;
            $arrayOrderId = explode('-', $orderId);
            $purchase_data = $this->sales_model->getPurchasesByRefNo(trim($arrayOrderId[0]), trim($arrayOrderId[1]));
            $sales_data = $this->at_sale->findSalesByReferenceNo($purchase_data->cf1, $purchase_data->supplier_id);

            if($sales_data->paid >= $sales_data->grand_total){
                throw new Exception('Sales have been paid', 400);
            }

            $dataPaymentTemp = [
                'purchase_id' => $purchase_data->id,
                'sale_id' => $sales_data->id,
                'nominal' => $sales_data->grand_total,
                'url_image' => '',
                'status' => 'pending',
                'reference_no' => payment_tmp_ref()
            ];

            if(property_exists($response, 'image')){
                $uploadedImg = $this->integration->upload_files($response->image, 'base64');
                if(!$uploadedImg){
                    throw new \Exception("Upload file failed");
                }
                $dataPaymentTemp['url_image'] = $uploadedImg->url;
            }

            $id = $this->payment_model->addPaymentTemp($dataPaymentTemp, true);
            if (!$id) {
                throw new \Exception("Add Payment (Distributor) failed");
            }
            
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'Add Payment (Distributor) success', $result);
        }catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode(), $th->getMessage(), null);
        }
    }

    public function payment_aksestoko_post(){
        $this->db->trans_begin();
        try{
            if(!$this->input->post('param')){
                throw new Exception('Undefined param', 400);
            }

            $response = json_decode($this->integration->decryptKreditpro($this->input->post('param')));
            if (!property_exists($response, 'orderId'))
                throw new Exception('Undefined Property orderId', 400);
            
            if (!property_exists($response, 'amount') || $response->amount == 0)
                throw new Exception('Undefined Property amount or Amount is 0', 400);
            if (!property_exists($response, 'image'))
                throw new Exception('Undefined Property image', 400);
            
            $orderId = $response->orderId;
            $arrayOrderId = explode('-', $orderId);
            $purchase_data = $this->sales_model->getPurchasesByRefNo(trim($arrayOrderId[0]), trim($arrayOrderId[1]));
            $sales_data = $this->at_sale->findSalesByReferenceNo($purchase_data->cf1, $purchase_data->supplier_id);

            if($purchase_data->grand_total < ($purchase_data->paid + $response->amount)){
                throw new Exception('Amount is too much. (Max : ' . abs($purchase_data->grand_total - $purchase_data->paid) .')', 400);
            }

            $dataPaymentTemp = [
                'purchase_id' => $purchase_data->id,
                'sale_id' => $sales_data->id,
                'nominal' => $response->amount,
                'url_image' => '',
                'status' => 'pending',
                'reference_no'=> payment_tmp_ref(),
                'third_party' => $purchase_data->payment_method
            ];

            if(property_exists($response, 'image')){
                $uploadedImg = $this->integration->upload_files($response->image, 'base64');
                if(!$uploadedImg){
                    throw new \Exception("Upload file failed");
                }
                $dataPaymentTemp['url_image'] = $uploadedImg->url;
            }
            
            $id = $this->payment_model->addPaymentTemp($dataPaymentTemp);
            
            if (!$id) {
                throw new \Exception("Add Payment (Retail) failed");
            }

            if (!$this->sales_model->addPaymentFromThirdParty($id, trim($arrayOrderId[1]), false)) {
                throw new \Exception("Confirm Payment (Retail) failed");
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'Add Payment (Retail) Success', $result);

        }catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode(), $th->getMessage(), null);
        }
    }
}

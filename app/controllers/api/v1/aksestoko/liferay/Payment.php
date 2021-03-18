<?php defined('BASEPATH') or exit('No direct script access allowed');

require 'MainController.php';

class Payment extends MainController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Sales_model', 'sales_model');
    }

    public function insert_payment_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'paymentid',
                    'label' => 'paymentid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'paymentmethodid',
                    'label' => 'paymentmethodid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'orderid',
                    'label' => 'orderid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'bank',
                    'label' => 'bank',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'no_rek',
                    'label' => 'no_rek',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'paymentamount',
                    'label' => 'paymentamount',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'debtamount',
                    'label' => 'debtamount',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'totalharga',
                    'label' => 'totalharga',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'statuspaymentid',
                    'label' => 'statuspaymentid',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'active_',
                    'label' => 'active_',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'image',
                    'label' => 'image',
                    'rules' => 'required|valid_url',
                    'errors' => $this->errors
                ], [
                    'field' => 'tempo',
                    'label' => 'tempo',
                    'rules' => 'required',
                    'errors' => $this->errors
                ], [
                    'field' => 'createddate',
                    'label' => 'createddate',
                    'rules' => 'required',
                    'errors' => $this->errors
                ],
            ];

            $this->validate_form($config);

            $this->load->model('integration_model', 'integration');

            $paymentid          = $this->body('paymentid');
            $paymentmethodid    = $this->body('paymentmethodid');
            $orderid            = $this->body('orderid');
            $bank               = $this->body('bank');
            $no_rek             = $this->body('no_rek');
            $paymentamount      = $this->body('paymentamount');
            $debtamount         = $this->body('debtamount');
            $totalharga         = $this->body('totalharga');
            $statuspaymentid    = $this->body('statuspaymentid');
            $active_            = $this->body('active_');
            $image              = $this->body('image');
            $tempo              = $this->body('tempo');
            $createddate        = $this->body('createddate');
            $saleatl            = $this->sales_model->getSaleidAtl($orderid);
            $sale               = $this->sales_model->getSalesBySalesId($saleatl->sale_id);
            $biller_id          = $auth->company->id;
            $biller             = $this->site->getCompanyByID($biller_id);
            $idbk_toko          = $saleatl->bisniskokohidtoko;
            $tempo              = $saleatl->tempo;

            if (!$sale) {
                throw new Exception("Pemesanan dengan `orderid=$orderid` tidak ditemukan", 404);
            }

            $paymentAtl = $this->sales_model->findPaymentAtl(["paymentid" => $paymentid]);
            if($paymentAtl) {
                throw new \Exception("Telah ada pembayaran dengan `paymentid=$paymentid`", 400);
            }

            if( (float) $sale->grand_total != (float) $totalharga ) {
                throw new \Exception("`totalharga` tidak sesuai, seharusnya " . (int) $sale->grand_total . ".", 400);
            }

            $dataTmpPayment = [
                'paymentid'          => $paymentid,
                'sale_id'            => $sale->id,
                'company_id'         => $auth->company->id,
                'paymentmethodid'    => $paymentmethodid,
                'orderid'            => $orderid,
                'bank'               => $bank,
                'no_rek'             => $no_rek,
                'paymentamount'      => $paymentamount,
                'debtamount'         => $debtamount,
                'totalharga'         => $totalharga,
                'status'             => 'pending',
                'statuspaymentid'    => $statuspaymentid,
                'active_'            => $active_,
                'image'              => $image,
                'tempo'              => $tempo,
                'createddate'        => $createddate,
            ];

            $payment_id = $this->sales_model->insert_payment_atl($dataTmpPayment);

            if (!$payment_id) {
                throw new Exception("Gagal menambahkan data pembayaran ke tabel `atl_payments`");
            }

            if ($this->integration->isIntegrated($biller->cf2)) {
                $this->load->model('aksestoko/Payment_model', 'payment');
                $get_payment = $this->sales_model->findPaymentAtl(["paymentid" => $paymentid]);
                $get_bank = $this->site->getBankByName($bank, $get_payment->company_id);
                $pay_data = [
                    'reference_no'  => payment_tmp_ref(),
                    'nominal'       => $get_payment->paymentamount,
                    'created_at'    => $get_payment->createddate,
                    'url_image'     => $get_payment->image,
                    'status'        => $get_payment->status,
                    'sale_id'       => $saleatl->sale_id
                ];
                $bank_data = [
                    'no_rekening'   => $get_payment->no_rek,
                    'bank_name'     => $get_payment->bank,
                    'code'          => $get_bank->code
                ];
                $call_api = $this->integration->create_payment_integration($biller->cf2, $idbk_toko, (array)$sale, $pay_data, $bank_data);
                if (!$call_api) {
                    throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                }
                if (!$this->payment->updatePaymentTemp(['cf1' => $call_api, 'cf2' => $biller->cf2], ['reference_no' => $pay_data['reference_no']])) {
                    throw new \Exception("Tidak dapat memperbarui reference number pembayaran dari distributor");
                }
            }
            $response = [
                'paymentid'        => $paymentid
            ];

            $this->db->trans_commit();
            $this->buildResponse(true, 200, "Berhasil menambahkan pembayaran", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse(false, $th->getCode(), $th->getMessage());
        }
    }
}

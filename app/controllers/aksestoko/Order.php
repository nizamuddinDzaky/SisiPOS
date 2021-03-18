<?php defined('BASEPATH') or exit('No direct script access allowed');

class Order extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->insertLogActivities();
        $this->load->library('pagination');
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/at_company_model', 'at_company');
        $this->load->model('aksestoko/bank_model', 'bank');
        $this->load->model('aksestoko/Payment_model', 'payment');
        $this->load->model('aksestoko/promotion_model', 'promotion');
        $this->load->model('aksestoko/product_model', 'product');
        $this->load->model('integration_model', 'integration');
        $this->load->model('site', 'site');
        $this->load->model('audittrail_model', 'audittrail');
        $this->load->model('Sales_model', 'sales_model');
        $this->load->model('companies_model');
        $this->data['logo'] = true;
        $this->data['array_payment_method'] = [
            'cash on delivery', 'kredit'
        ];
    }

    public function __status($status, $param = 0)
    {
        switch ($status) {
            case "ordered":
                return ["Menunggu Konfirmasi", "warning"];
            case "confirmed":
                return ["Dikonfirmasi", "success"];
            case "packing":
                return ["Sedang Dikemas", "warning"];
            case "delivering":
                return ["Dalam Pengiriman", "info"];
                // case "reject":
                //     if ($param == 1) {
                //         return ["Kredit Ditolak", "primary"];
                //     } elseif ($param == 0) {
                //         return ["Ditolak", "primary"];
                //     }
            case "delivered":
                return ["Barang Telah Dikirim", "success"];
            case "partial":
                if ($param == 0) {
                    return ["Diterima Sebagian", "info"];
                } elseif ($param == 1) {
                    return ["Dibayar Sebagian", "info"];
                } elseif ($param == 2) {
                    return ["Menunggu Pelunasan", "info"];
                }
                // no break
            case "received":
                return ["Diterima", "success"];
            case "pending":
                if ($param == 0) {
                    return ["Belum Bayar", "warning"];
                } elseif ($param == 1) {
                    return ["Belum Lunas", "warning"];
                } elseif ($param == 2) {
                    return ["Menunggu Konfirmasi", "warning"];
                }

                // no break
            case "waiting":
                if ($param == 1) {
                    return ["Kredit Ditinjau", "info"];
                } elseif ($param == 0) {
                    return ["Menunggu Konfirmasi", "warning"];
                } elseif ($param == 2) {
                    return ["Menunggu Pelunasan", "info"];
                }

                // no break
            case "paid":
                return ["Telah Dibayar", "success"];
            case "canceled":
                return ["Dibatalkan", "danger"];
            case "accept":
                if ($param == 2) {
                    return ["Kredit Diterima", "success"];
                } elseif ($param == 1) {
                    return ["Kredit Diterima", "info"];
                } elseif ($param == 0) {
                    return ["Diterima", "success"];
                } elseif ($param == 1001) {
                    return ["Diterima", "success"];
                }
                // no break
            case "reject":
                if ($param == 1) {
                    return ["Kredit Ditolak", "danger"];
                } elseif ($param == 0) {
                    return ["Ditolak", "danger"];
                } elseif ($param == 2) {
                    return ["Ditolak", "danger"];
                }
                // no break
            case "cash before delivery":
                return ["Bayar Sebelum Dikirim", ""];
            case "kredit":
                return ["Tempo dengan Distributor", ""];
            case "kredit_pro":
                return ["Kredit Pro", ""];
            case "cash on delivery":
                return ["Bayar Di Tempat", ""];
            case "kredit_mandiri":
                return ["Kredit Mandiri", ""];

            case 'pickup':
                return ["Pengambilan Sendiri", ""];
            case 'delivery':
                return ["Pengiriman Distributor", ""];
        }
        return ["Status Tidak Diketahui", "danger"];
    }

    public function email()
    {
        // print_r($this->Settings);die;
        // $this->load->library('encrypt');
        // $config =  array(
        //     'protocol'  => 'smtp',
        //     'smtp_host' => 'smtp.gmail.com',
        //     'smtp_port' => 587,
        //     'smtp_user' => 'adm.aksestoko@gmail.com',
        //     'smtp_pass' => 'Indonesia1',
        //     'smtp_crypto'=>'ssl'
        // );

        // $this->load->library('email');
        // $this->email->initialize($config);

        // $this->email->from('adm.aksestoko@gmail.com');
        // $this->email->to('abdullahfahmi1997@gmail.com');
        // $this->email->cc('nizamuddin.dzaky@gmail.com');
        // // $this->email->bcc('them@their-example.com');
        // $this->email->subject('Email Test');
        // $this->email->message('Testing the email class.');
        $this->load->helper("file");
        unlink('assets/uploads/INVOICE_-_SALE_2019_10_0033.pdf');
        // ;
        // var_dump($this->email->send());
        // echo ;die;
        $mail = new PHPMailer(true);
        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'adm.aksestoko@gmail.com';                     // SMTP username
            $mail->Password   = 'Indonesia1';                               // SMTP password
            $mail->SMTPSecure = 'tls';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('adm.aksestoko@gmail.com', 'AksesToko.id');
            // $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
            $mail->addAddress('nizamuddin.dzaky@gmail.com');               // Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            // Attachments
            $mail->addAttachment('assets/uploads/INVOICE_-_SALE_2019_10_0033.pdf');         // Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function __company($id)
    {
        $company = $this->at_site->getCompanyByID($id);
        return $company;
    }

    private function __date($date) // MM/DD/YYYY
    {
        $newDate = date("Y-m-d", strtotime($date));
        return $newDate;
    }

    private function __delivery_date($date) // MM/DD/YYYY
    {
        $newDate =  strtr($date, '/', '-');
        $newDate = date("Y-m-d", strtotime($newDate));
        return $newDate;
    }

    //GET
    public function index()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        //menagmbil data order yang pernah dilakukan
        $this->data['title_at'] = "Pesanan Saya - AksesToko";

        // $this->data['orders'] = $this->at_purchase->getOrders($this->session->userdata('user_id'));

        // $this->data['orders_on_going'] = [];
        // $this->data['orders_completed'] = [];
        // foreach ($this->data['orders'] as $order) {
        //     if($order->status == "delivered" || $order->status == "received"){
        //         $this->data['orders_completed'][] = $order;
        //     } else {
        //         $this->data['orders_on_going'][] = $order;
        //     }
        // }
        $this->data['object'] = $this;

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/order', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function orders_on_going_data()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $config['query_string_segment'] = 'start';
        $config['full_tag_open'] = '<nav aria-label="pagination-order" class="pagination my-3"><ul style="margin:auto;" class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['first_link'] = 'Pertama';
        $config['first_tag_open'] = '<li class="page-item" style="display: none;">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Terakhir';
        $config['last_tag_open'] = '<li class="page-item" style="display: none;">';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = '<span aria-hidden="true">»</span>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '<span aria-hidden="true">«</span>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active disabled"><a class="page-link" href="javascript:void(0)">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['base_url'] = base_url(aksestoko_route("aksestoko/order/orders_on_going_data"));
        $config['total_rows'] = $this->at_purchase->getRowsOrdersOnGoing($this->session->userdata('user_id'), $this->input->get('search'));
        $config['per_page'] = 3;
        $config['num_link'] = 1;
        $config['uri_segment'] = count($this->uri->segments);
        $config['use_page_numbers'] = true;
        $page = $this->uri->segment(count($this->uri->segments));
        $start = ($page - 1) * $config['per_page'];

        $this->pagination->initialize($config);
        $pagination = $this->pagination->create_links();
        $orders_on_going = $this->at_purchase->getOrdersOnGoing($this->session->userdata('user_id'), $config['per_page'], $start, $this->input->get('search') ?? null);

        $output = "<div>";
        foreach ($orders_on_going as $key => $order) {
            $payment_total = $this->payment->getTotalPaymentByPoId($order->id);
            $company = $this->__company($order->company_id);
            $output .= $this->list_order_card($order, $company, $payment_total->total, 'on going');
        }
        $output .= '</div>';

        $res = array(
            'pagination' => $pagination,
            'orders' => $output
        );
        if (count($orders_on_going) == 0) {
            http_response_code(400);
        }
        echo json_encode($res);
    }

    public function orders_complete_data()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $config['query_string_segment'] = 'start';
        $config['full_tag_open'] = '<nav aria-label="pagination-order" class="pagination my-3"><ul class="pagination justify-content-center" style="margin:auto;">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['first_link'] = 'First';
        $config['first_tag_open'] = '<li class="page-item pertama" style="display: none;">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Last';
        $config['last_tag_open'] = '<li class="page-item terakhir" style="display: none;">';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = '<span aria-hidden="true">»</span>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '<span aria-hidden="true">«</span>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active disabled"><a class="page-link" href="javascript:void(0)">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li  class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['base_url'] = base_url(aksestoko_route("aksestoko/order/orders_complete_data"));
        $config['total_rows'] = $this->at_purchase->getRowsOrdersComplete($this->session->userdata('user_id'), $this->input->get('search') ?? null);
        $config['per_page'] = 3;
        $config['num_link'] = 1;
        $config['uri_segment'] = count($this->uri->segments);
        $config['use_page_numbers'] = true;
        $page = $this->uri->segment(count($this->uri->segments));
        $start = ($page - 1) * $config['per_page'];

        $this->pagination->initialize($config);
        $pagination = $this->pagination->create_links();
        $orders_completed = $this->at_purchase->getOrdersComplete($this->session->userdata('user_id'), $config['per_page'], $start, $this->input->get('search') ?? null);

        $output = '
          </div>';

        foreach ($orders_completed as $key => $order) {
            $payment_total = $this->payment->getTotalPaymentByPoId($order->id);
            $company = $this->__company($order->company_id);
            $output .= $this->list_order_card($order, $company, $payment_total, 'complete');
        }
        $output .= '</div>';

        $res = array(
            'pagination' => $pagination,
            'orders' => $output
        );
        if (count($orders_completed) == 0) {
            http_response_code(400);
        }
        echo json_encode($res);
    }


    public function list_order_card($order, $company, $payment_total, $status = '')
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $sale = $this->at_sale->findSalesByReferenceNo($order->cf1, $order->supplier_id);
        $payment_pending = $this->payment->getPaymentPending($order->id);
        $now = time();
        $end_date = strtotime(date('Y-m-d', strtotime($order->payment_deadline)));
        $datediff = $now - $end_date;
        $duration = round($datediff / (60 * 60 * 24));

        $deliveries = $this->at_sale->getDeliveriesItems($order->supplier_id, $order->cf1);
        $product = $this->product->getProductByCodeAndSupplierId($order->items[0]->product_code, $order->supplier_id);

        $supplier = $this->at_company->getCompanyByID($order->supplier_id);

        if ($duration < -3 && $duration > -7) {
            $bg = 'bg-warning';
        } elseif ($duration > -3) {
            $bg = 'bg-danger';
        } else {
            $bg = 'bg-info';
        }
        $str = '
            <div class="box box-order-details p-box mb-3 ">';
        if ($order->payment_status != 'paid' && $order->status != 'canceled' && in_array($order->payment_method, array_merge($this->data['array_payment_method'], ["kredit_pro"])) && $order->payment_deadline != null) {
            $str .= ' <div class="box-header ' . $bg . '" id="batas-waktu">
                  <span class="text-white"><i>Sisa Durasi Waktu Pembayaran :</i>  </span> <strong class="text-white">' . $duration . ' Hari</strong>
                </div>';
        }
        $notifCharge = "Distributor telah memperbarui total harga. ";
        if ($order->is_updated_price == 1) {
            if ($order->charge < 0) {
                $notifCharge .= 'Terdapat potongan harga sebesar - Rp ' . number_format(abs($order->charge), 0, ',', '.');
            } else {
                $notifCharge .= 'Terdapat biaya tambahan sebesar Rp ' . number_format($order->charge, 0, ',', '.');
            }

            $str .= '<div class="box-header bg-info id="batas-waktu"">
                    <div class="row justify-content-between">
                        <div class="col-auto" style="max-width:100%;">
                        <span class="text-white">' . $notifCharge . '</span>
                        </div>
                        <div class="col-auto" style="text-align: right">
                        <a data-toggle="modal" href="' . base_url(aksestoko_route('aksestoko/order/view/')) . $order->id . '" class="btn-sm btn-success py-2 px-3" style="font-size: 12px; border-radius: 40px;">Lihat Detail</a> 
                        </div>
                    </div>
                
                </div>';
        }
        // if($order->payment_status != 'pending'){
        //     $param = ($order->payment_method == 'kredit_pro'? (($order->grand_total - $order->paid) > 0 ? 2 : 1) : 0);
        // }else{
        $param = $order->payment_method == 'kredit_pro' || $order->payment_method == 'kredit_mandiri' ? 1 : 0;

        // }
        $str .= '<div class="order-details-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID Pemesanan <span class="text-primary">(' . $supplier->company . ')</span></label>
                                <p id="id-pemesanan" class="h5"><a href="' . base_url(aksestoko_route('aksestoko/order/view/')) . $order->id . '">' . $order->cf1 . '</a></p>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Pemesanan</label>
                                <p id="tanggal-pemesanan" class="h5">' . $this->__convertDate($order->date) . '</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status Pesanan</label>
                                <p id="status-pemesanan" class="h5 text-' . $this->__status($order->status)[1] . '">' . $this->__status($order->status)[0] . '</p>
                            </div>
                            <div class="form-group">
                                <label>Status Pembayaran</label>
                                <p id="status-pemesanan" class="h5 text-' . $this->__status($order->payment_status, $param)[1] . '">
                                  ' . $this->__status($order->payment_status, $param)[0] . '
                                </p>
                                ';
        // if($order->payment_method == 'kredit_pro' && $order->payment_status != 'reject'){
        //     $urlPaymet = 'aksestoko/order/payment_kreditpro';
        // }else{
        //     $urlPaymet = 'aksestoko/order/payment';
        // }

        // if (($order->grand_total > $payment_total && $order->status !="canceled")&& $payment_pending) {
        //     if ($order->payment_method == 'kredit') {
        //         if ($order->status =="received") {
        //             $str .= '<a id="konfirmasi_pembayaran" href="'. base_url(aksestoko_route($urlPaymet)).'/'.$order->id.'" class="btn btn-success small mt-1">Selesaikan Pembayaran </a>';
        //         }
        //     } elseif (($order->status == 'confirmed' || $order->status == 'received' || $order->status == 'partial') && $order->payment_method != 'kredit_pro') {
        //         $str .= '<a id="konfirmasi_pembayaran" href="'. base_url(aksestoko_route($urlPaymet)).'/'.$order->id.'" class="btn btn-success small mt-1">Selesaikan Pembayaran </a>';
        //     } elseif($order->payment_method == 'kredit_pro' && ($order->status == 'confirmed' || $order->status == 'received') && ($order->payment_status == 'pending' || $order->payment_status == 'reject')){
        //         $str .= '<a id="konfirmasi_pembayaran" href="'. base_url(aksestoko_route($urlPaymet)).'/'.$order->id.'" class="btn btn-success small mt-1">'.($order->payment_status=='reject' ? 'Selesaikan Pembayaran' : 'Ajukan Kredit').' </a>';
        //     }
        // }
        $str .= '
                            </div>
                        </div>    
                    </div>
                </div>';

        $str .= '
                <div class="body-detail-order px-3 py-3">
                    <div class="box">
                        <div class="subheading py-0 px-0 my-0 mx-0">
                            <div class="product-list">
                                <img class="img-fluid product-list-img px-2 py-2" src="' . url_image_thumb($product->thumb_image) . '" onerror="this.src=\'' . base_url("assets/uploads/no_image.png") . '\'" alt="Product">
                                <div class="product-content">
                                    <div class="row">
                                        <div class="col-md-8">
                                        <h4 class="card-title mb-0">
                                            <a href="' . base_url(aksestoko_route('aksestoko/product/view/')) . $product->id . '?supplier_id=' . $order->supplier_id . '">
                                                ' . $order->items[0]->product_name . '
                                            </a>
                                        </h4>
                                        <small class="text-muted font-weight-light ">' . $order->items[0]->product_code . '</small>';
        if ($order->items[0]->unit_cost > 0) {
            $str .= '<h6 class="">Rp ' . number_format($order->items[0]->unit_cost, 0, ',', '.') . '</h6>';
        }
        $str .= '<div class="row">
                                            <div class="col-6">
                                                <label class="d-sm-block">Jumlah</label>
                                                <h5 class="jmlh-barang" style="color:black">' . (int) $order->items[0]->quantity . " " . convert_unit($this->__unit($product->unit)) . '</5>
                                            </div>';
        if ($order->items[0]->subtotal > 0) {
            $str .= '<div class="col-6">
                                            <label class="d-sm-block">Harga</label>
                                            <h5 class="hrg-barang" style="color:black">Rp ' . penyebut($order->items[0]->subtotal) . '</h5>
                                        </div>';
        }
        $str .= '</div>
                                        

                                        </div>';
        $counter = 0;
        foreach ($deliveries as $i => $delivery) {
            $counter = $delivery->receive_status != "received" && $delivery->status != "packing" ? $counter + 1 : $counter;
        }
        if ($counter > 0) {
            $str .=        '<div class="col-md-4 text-right">
                                            <button style="font-size: 11px; border-radius: 40px; padding: .4rem 1.5rem;" id="terima-barang" class="btn btn-primary btn-received" data-id="' . $order->id . '">Konfirmasi Penerimaan</button></div>';
        }
        $str .=        '
                                    </div>

                                </div>';
        if (count($order->items) > 1) {
            $str .= '<hr class="mt-0">
                           <p class="text-center">+' . (count($order->items) - 1) . ' barang lainnya</p>';
        }

        $str .=    '</div>
                        </div>
                    </div>
                </div>
                <div class="order-details-check" style="display: flex; justify-content: space-between;">';
        if ($order->grand_total > 0) {
            $str .= '<span id="total-harga-order">Total <b>Rp ' . penyebut($order->grand_total) . '</b></span>';
        } else {
            $str .= '<span id="total-harga-order"></span>';
        }
        $str .= '<a id="lihat_detail" href="' . base_url(aksestoko_route('aksestoko/order/view/')) . $order->id . '">Lihat Detail <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            ';
        return $str;
    }

    /**
     * POST
     *
     * Request :
     * - delivery_date -> date
     * - company_id -> int (id_alamat)
     * - note -> text
     */
    public function save_checkout()
    {

        // var_dump($this->input);die;
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $userdata = [
                    'delivery_date' => $this->input->post('delivery_date'),
                    'company_address_id' => $this->input->post('company_id'),
                    'note' => $this->input->post('note'),
                    'delivery_method' => $this->input->post('delivery_method'),
                    'is_checkout' => true,
                ];
                $this->session->set_userdata($userdata);
                $this->db->trans_commit();
                redirect(aksestoko_route('aksestoko/order/payment'));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/order/checkout'));
    }

    /**
     * POST
     *
     * Request :
     * - delivery_date -> date
     * - company_id -> int (id_alamat)
     * - supplier_id -> int (id_supplier)
     * - product_id -> array
     * - quantity -> array
     * - note -> text
     * - shipping_by -> text
     */
    public function store()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        // print_r($this->input->post());die;
        //mengecek apakah cart ada isinya
        if (!$this->data['cart'] || count($this->data['cart']) == 0) {
            $this->session->set_flashdata('error', "Tidak dapat membuat pesanan. Keranjang Belanja kosong.");
            redirect(aksestoko_route('aksestoko/home/main'));
        }

        if ($this->isPost()) {
            $supplier_id = $this->session->userdata('supplier_id');
            $supplier = $this->at_site->getCompanyByID($supplier_id);
            $this->Owner = true;

            $this->db->trans_begin();
            try {
                $uuid_sales = $this->input->post('uuid');

                if ($uuid = $this->site->isUuidExist($uuid_sales, 'sales')) {
                    throw new Exception("UUID $uuid is exist.");
                }

                $total = 0;
                $total_items = 0;
                $countProduct = count($this->data['cart']);
                $price_type = 'cash';

                $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
                $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

                if ($get_customer_warehouse) {
                    $warehouse_id         = $get_customer_warehouse->default;
                } else {
                    $warehouse_id         = $this->at_site->findCompanyWarehouse($this->session->userdata('supplier_id'))->id;
                }

                $warehouse                = $this->at_site->getWarehouseByID($warehouse_id, $this->session->userdata('supplier_id'));
                for ($i = 0; $i < $countProduct; $i++) {
                    $supplierProduct = $this->product->getProductByID($this->data['cart'][$i]->id, $this->session->userdata('supplier_id'), $this->session->userdata('price_group_id'), $this->session->userdata('company_id'));

                    $product = $this->at_site->findRelationProduct($supplierProduct, $this->at_site->getCompanyByID($this->session->userdata('company_id')));

                    $supplierProduct->price = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_price && $supplierProduct->group_price > 0 ? $supplierProduct->group_price : $supplierProduct->price);
                    if ($this->input->post('payment_method') == 'kredit') {
                        // $supplierProduct->price = $supplierProduct->group_kredit && $supplierProduct->group_kredit > 0 ? $supplierProduct->group_kredit : $supplierProduct->credit_price;
                        $supplierProduct->price = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_kredit && $supplierProduct->group_kredit > 0 ? $supplierProduct->group_kredit : ($supplierProduct->credit_price && $supplierProduct->credit_price > 0 ? $supplierProduct->credit_price : $supplierProduct->price));
                        $price_type = 'credit';
                    }

                    $unit = $this->product->getUnit($supplierProduct->sale_unit);

                    $quantity = $this->data['cart'][$i]->cart_qty;

                    $quantity = $this->__operate($quantity, $unit->operation_value, $unit->operator);

                    $price = $supplierProduct->price;
                    $subtotal = ($quantity * $price);

                    // if($this->session->userdata('delivery_method') == 'pickup'){
                    //     $shipmentPrice = $this->at_site->getShipmentProductPriceByShipmentPriceGroupIdAndProductId($warehouse->shipment_price_group_id, $supplierProduct->id);


                    // }

                    $shipmentPrice = 0;
                    if ($warehouse->shipment_price_group_id) {
                        $objShipmentPrice = $this->at_site->getShipmentProductPriceByShipmentPriceGroupIdAndProductId($warehouse->shipment_price_group_id, $supplierProduct->id);
                        if ($this->session->userdata('delivery_method') == 'pickup') {
                            $shipmentPrice = $objShipmentPrice->price_pickup;
                        } elseif ($this->session->userdata('delivery_method') == 'delivery') {
                            $shipmentPrice = $objShipmentPrice->price_delivery;
                        }
                    }
                    $price += $shipmentPrice;
                    $subtotal += ($shipmentPrice * $quantity);
                    $total_items += $quantity;
                    $total += $subtotal;
                    //For Sales Order
                    $requestProductsSO[] = [
                        'sale_id' => null,
                        'product_id' => $supplierProduct->id,
                        'product_code' => $supplierProduct->code,
                        'product_name' => $supplierProduct->name,
                        'product_type' => $supplierProduct->type,
                        'option_id' => null,
                        'net_unit_price' => $price,
                        'unit_price' => (int) $price,
                        'quantity' => (int) $quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => 0,
                        'tax_rate_id' => 0,
                        'tax' => 0,
                        'discount' => null,
                        'item_discount' => 0,
                        'subtotal' => $subtotal,
                        'serial_no' => null,
                        'real_unit_price' => $price,
                        'sale_item_id' => null,
                        'product_unit_id' => $supplierProduct->unit,
                        'product_unit_code' => ($this->at_site->findUnit($supplierProduct->unit))->code,
                        'unit_quantity' => $quantity,
                        'client_id' => null,
                        'flag' => null,
                        'is_deleted' => null,
                        'device_id' => null,
                        'uuid' => null,
                        'uuid_app' => null,
                    ];
                    //For Purchase Order
                    $requestProductsPO[] = [
                        'purchase_id' => null,
                        'transfer_id' => null,
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'option_id' => null,
                        'net_unit_cost' => $price,
                        'quantity' => $quantity,
                        'warehouse_id' => $this->at_site->findCompanyWarehouse($this->session->userdata('company_id'))->id,
                        'item_tax' => 0,
                        'tax_rate_id' => 0,
                        'tax' => 0,
                        'discount' => null,
                        'item_discount' => 0,
                        'expiry' => null,
                        'subtotal' => $subtotal,
                        'quantity_balance' => 0,
                        'date' => date('Y-m-d H:i:s'),
                        'status' => 'ordered',
                        'unit_cost' => $price,
                        'real_unit_cost' => $price,
                        'quantity_received' => 0,
                        'supplier_part_no' => null,
                        'purchase_item_id' => null,
                        'product_unit_id' => $product->unit,
                        'product_unit_code' => ($this->at_site->findUnit($product->unit))->code,
                        'unit_quantity' => $quantity,
                        'client_id' => null,
                        'flag' => null,
                        'is_deleted' => null,
                        'device_id' => null,
                        'uuid' => null,
                        'uuid_app' => null,
                    ];
                }
                // print_r($requestProductsPO);die;
                $promo_data = $this->session->userdata('promo');
                $disc = 0;
                if ($promo_data->tipe == 0) { //jika persentase
                    $disc = ($promo_data->value * $total) / 100;
                    if ($disc > $promo_data->max_total_disc) {
                        $disc = $promo_data->max_total_disc;
                    }
                } else {
                    $disc = (float) $promo_data->value;
                }
                $company = ($this->at_site->getCompanyByID($this->session->userdata('company_address_id')));

                //if ($this->integration->isIntegrated($supplier->cf2)) {
                //    $sale_type = null;
                //} else {
                $sale_type = 'booking';
                //}

                $so_reference_no = substr_replace($this->at_site->getReference('so', $supplier_id), "/AT", 4, 0);

                $requestSO = [
                    'date' => date('Y-m-d H:i:s'),
                    'reference_no' => $so_reference_no,
                    'customer_id' => $this->session->userdata('company_address_id'),
                    'customer' => $company->company,
                    'biller_id' => $supplier_id,
                    'biller' => $supplier->company,
                    'warehouse_id' => $warehouse_id,
                    'note' => $this->sma->clear_tags($this->session->userdata('note')),
                    'staff_note' => null,
                    'total' => $total,
                    'product_discount' => null,
                    'order_discount_id' => $disc,
                    'total_discount' => $disc,
                    'order_discount' => $disc,
                    'product_tax' => null,
                    'order_tax_id' => null,
                    'order_tax' => null,
                    'total_tax' => null,
                    'shipping' => null,
                    'grand_total' => $total - $disc,
                    'sale_status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_term' => null,
                    'due_date' => null,
                    'created_by' => $this->session->userdata('user_id'),
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'total_items' => $total_items,
                    'pos' => 0,
                    'paid' => 0,
                    'return_id' => null,
                    'surcharge' => 0,
                    'attachment' => null,
                    'return_sale_ref' => null,
                    'sale_id' => null,
                    'return_sale_total' => 0,
                    'rounding' => null,
                    'client_id' => 'aksestoko',
                    'flag' => null,
                    'is_deleted' => null,
                    'device_id' => trim($company->address) . ", " . ucwords(strtolower($company->village)) . ", " . ucwords(strtolower($company->state)) . ", " . ucwords(strtolower($company->city)) . ", " . ucwords(strtolower($company->country)) . " - " . $company->postal_code,
                    'uuid' => $uuid_sales,
                    'uuid_app' => null,
                    'order_id' => null,
                    'mtid' => null,
                    'company_id' => $supplier_id,
                    'delivery_date' => $this->__delivery_date($this->session->userdata('delivery_date')),
                    'delivery_method' => $this->session->userdata('delivery_method'),
                    'sale_type' => $sale_type,
                    'price_type' => $price_type
                ];

                $requestPO = [
                    'reference_no' => $this->at_site->getReference('po'),
                    'date' => date('Y-m-d H:i:s'),
                    'supplier_id' => $supplier_id,
                    'supplier' => $supplier->company,
                    'warehouse_id' => $this->at_site->getFirstWarehouseOfCompany($this->session->userdata('company_id'))->id,
                    'note' => $this->sma->clear_tags($this->input->post('note')),
                    'total' => $total,
                    'product_discount' => null,
                    'order_discount_id' => $disc,
                    'order_discount' => $disc,
                    'total_discount' => $disc,
                    'product_tax' => null,
                    'order_tax_id' => null,
                    'order_tax' => null,
                    'total_tax' => null,
                    'shipping' => null,
                    'grand_total' => $total - $disc,
                    'paid' => 0,
                    'status' => 'ordered',
                    'payment_status' => 'pending',
                    'created_by' => $this->session->userdata('user_id'),
                    'updated_by' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'attachment' => null,
                    'payment_term' => null,
                    'due_date' => null,
                    'return_id' => null,
                    'surcharge' => 0,
                    'return_purchase_ref' => null,
                    'purchase_id' => null,
                    'return_purchase_total' => 0,
                    'client_id' => null,
                    'flag' => null,
                    'is_deleted' => null,
                    'device_id' => null,
                    'uuid' => null,
                    'uuid_app' => null,
                    'company_id' => $this->session->userdata('company_address_id'),
                    'company_head_id' => $this->session->userdata('company_id'),
                    'sino_spj' => null,
                    'sino_do' => null,
                    'shipping_by' => null,
                    'shipping_date' => $this->__delivery_date($this->session->userdata('delivery_date')),
                    'receiver' => null,
                    'is_watched' => null,
                    'cf1' => $so_reference_no,
                    'cf2' => 'POS',
                    'bank_id' => $this->input->post('bank_id'),
                    'payment_method' => $this->input->post('payment_method'),
                    // 'payment_duration' => $this->input->post('payment_durasi') == 'other' ? $this->input->post('input_payment_durasi') : $this->input->post('payment_durasi'),
                    'delivery_method' => $this->session->userdata('delivery_method')
                ];
                if ($this->input->post('payment_method') == 'kredit') {
                    $kredit_limit = $this->payment->getKreditLimit($this->session->userdata('customer_group_id'));
                    $debt = $this->payment->getTotalDebt($this->session->userdata('company_id'), $purchase_id, $this->session->userdata('supplier_id'));
                    $sisa_kredit = $kredit_limit->kredit_limit - (int) $debt->total;
                    // if (($total-$disc) > $sisa_kredit) {
                    //     throw new \Exception("Kredit Limit Anda Telah Mencapai Batas");
                    // }
                    $requestPO['payment_duration'] = $this->input->post('payment_durasi') == 'other' ? $this->input->post('input_payment_durasi') : $this->input->post('payment_durasi');
                }

                if ($this->input->post('payment_method') == 'kredit_mandiri') {
                    $kredit_mandiri = $this->payment->getLimitMandiri();
                    $sisa_limit = ($kredit_mandiri == '-' ? 0 : (float)$kredit_mandiri);
                    if (($sisa_limit - $requestPO['grand_total']) < 0) {
                        throw new \Exception("Limit Anda tidak mencukupi untuk dapat memilih metode ini.");
                    }
                }

                if ($sale_type == 'booking') {
                    $sales_id = $this->at_sale->addSaleATBooking($requestSO, $requestProductsSO);
                } else {
                    $sales_id = $this->at_sale->addSaleAT($requestSO, $requestProductsSO);
                }

                if (!$sales_id) {
                    throw new \Exception("Tidak bisa membuat sale");
                }

                $requestPO['cf2'] = 'POS-SALE-' . $sales_id;

                $purchase_id = $this->at_purchase->addPurchaseAT($requestPO, $requestProductsPO);

                if (!$purchase_id) {
                    throw new \Exception("Tidak bisa membuat purchase");
                }

                if (count($promo_data) > 0) {
                    $requestPromo = [
                        'promo_id' => $promo_data->id,
                        'company_id' => $this->session->userdata('company_id'),
                        'date' => date('Y-m-d H:i:s'),
                        'purchase_id' => $purchase_id
                    ];

                    if (!$this->promotion->addPromotion($requestPromo)) {
                        throw new \Exception("Tidak bisa memakai promo");
                    }
                }

                /*IBK 900000003 => SID*/
                /*IBK 100078876 => erp*/
                if ($this->integration->isIntegrated($supplier->cf2)) {
                    $requestPO['id'] = $purchase_id;
                    $requestSO['id'] = $sales_id;
                    $saleItems = $this->at_sale->getSaleItemsBySaleId($sales_id, true);
                    $response = $this->integration->create_order_integration($supplier->cf2, $this->session->userdata('username'), $requestSO, $saleItems, $requestPO);
                    if (!$response) {
                        throw new \Exception("Tidak dapat mengirim order ke distributor");
                    }

                    $dataSale['cf1'] = $response;
                    $dataSale['cf2'] = $supplier->cf2;
                    $dataSale['id'] = $sales_id;
                    if (!$this->at_sale->updateOrders($dataSale, ['id' => $purchase_id])) {
                        throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                    }
                }
                // print_r($this->session->userdata());die;
                if (!$this->audittrail->insertCustomerCreateOrder($this->session->userdata('user_id'), $this->session->userdata('company_id'), $this->session->userdata('supplier_id'), $sales_id, $purchase_id)) {
                    throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_create_order");
                }

                if (!$this->at_site->emptyCart($supplier_id, $this->session->userdata('user_id'))) {
                    throw new \Exception("Tidak bisa mengosongkan keranjang belanja");
                }

                $this->session->set_flashdata('message', 'Berhasil membuat pesanan');
                $this->session->set_flashdata('notif_order_created', 'Notifikasi pesanan');

                if (!$this->save_payment($purchase_id, true)) {
                    throw new \Exception("Pembayaran Gagal");
                }

                $this->load->model('socket_notification_model');
                $data_socket_notification = [
                    'company_id'        => $supplier_id,
                    'transaction_id'    => 'SALE-' . $sales_id,
                    'customer_name'     => $company->company,
                    'reference_no'      => $requestPO['cf1'],
                    'price'             => '',
                    'type'              => 'new_order',
                    'to'                => 'pos',
                    'note'              => '',
                    'created_at'        => date('Y-m-d H:i:s')
                ];
                $this->socket_notification_model->addNotification($data_socket_notification);


                /* start-cekID - melakukan pengecekan kembali apakah sales dan purchase sudah masuk ke dalam database */
                $new_sale = $this->at_sale->getSalesById($sales_id);
                if (!$new_sale) {
                    throw new \Exception("Tidak dapat membuat pesanan. SO dengan ID $sales_id tidak ditemukan.");
                }

                $new_purchase = $this->at_purchase->getPurchaseByID($purchase_id);
                if (!$new_purchase) {
                    throw new \Exception("Tidak dapat membuat pesanan. PO dengan ID $purchase_id tidak ditemukan.");
                }
                /* end-cekID */

                $this->db->trans_commit();

                /* Start-CekDuplicateNoRef - Fungsi ini sengaja diluar transaction, karena ada case tersendiri.*/
                if (!$this->at_sale->checkDupplicateNoSaleRef($new_sale, $new_purchase, true)) {
                    $this->session->set_flashdata('message', 'Berhasil membuat pesanan | Terjadi kesalahan pada saat cek duplikat SO');
                };
                /* End-CekDuplicateNoRef */

                $this->session->unset_userdata('company_address_id');
                $this->session->unset_userdata('note');
                $this->session->unset_userdata('delivery_date');
                $this->session->unset_userdata('delivery_method');
                $this->session->unset_userdata('promo');
                $this->session->unset_userdata('is_checkout');
                redirect(aksestoko_route('aksestoko/order/success/' . $purchase_id));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/order/payment'));
    }

    public function payment_kreditpro($purchase_id)
    {
        try {
            $purchase_data = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $sales_data = $this->at_sale->findSalesByReferenceNo($purchase_data->cf1, $purchase_data->supplier_id);

            $this->load->model('aksestoko/at_auth_model', 'at_auth');
            $user = $this->at_auth->find($sales_data->created_by);

            $dataKreditPro = [
                'orderId' => $sales_data->reference_no . '-' . $sales_data->biller_id,
                'msisdn' => $user->phone,
                'amount' => (string) (int) $sales_data->grand_total,
                'redirect' => base_url(aksestoko_route('aksestoko/order/success_kreditPro?purchase_id=' . $purchase_id))
            ];

            $param = $this->integration->encryptKreditpro($dataKreditPro);

            $url = $this->integration->getUrlKreditPro();
            if (!$url) {
                throw new \Exception("Url Kredit Pro Doesn't Exist");
            }

            redirect($url . $param);
        } catch (\Throwable $th) {
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function success_kreditPro()
    {
        try {
            if ($this->input->get('paymentstatus') !== 'success') {
                throw new \Exception("Batal Mengajukan Kredit Ke Kredit Pro");
            }
            if (!$this->input->get('param')) {
                throw new \Exception("Undefine Param");
            }
            $param = json_decode($this->integration->decryptKreditpro($this->input->get('param')));

            if (!property_exists($param, 'price')) {
                throw new Exception("Undefine price", 1);
            }
            if (!property_exists($param, 'payment_type')) {
                throw new Exception("Undefine payment_type", 1);
            }
            if (!property_exists($param, 'orderId')) {
                throw new Exception("Undefine orderId", 1);
            }

            // if(!$this->input->get('payment_type'))
            //     throw new Exception("Undefine payment_type", 1);
            // if(!$this->input->get('price'))
            //     throw new Exception("Undefine price", 1);
            $orderId = $param->orderId;
            $arrayOrderId = explode('-', $orderId);
            $payment_type = $param->payment_type;
            $price = $param->price;
            preg_match_all('!\d+!', $payment_type, $matches);
            $duration = implode('', $matches[0]);
            $purchases = $this->at_sale->getPurchasesByRefNo(trim($arrayOrderId[0]), trim($arrayOrderId[1]));
            $charge_third_party = $price - $purchases->grand_total;

            $data = [
                'payment_status'        => 'waiting',
                'grand_total'           => $price,
                'payment_duration'      => $duration,
                'charge_third_party'    => $charge_third_party,
                'payment_type'          => $payment_type
            ];

            $this->db->trans_begin();

            if (!$this->at_purchase->updatePurchaseById($purchases->id, $data)) {
                throw new \Exception("Failed");
            }
            $this->db->trans_commit();
            redirect(aksestoko_route('aksestoko/order/success/' . $purchases->id));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(base_url(aksestoko_route('aksestoko/order/view/' . $this->input->get('purchase_id'))));
        }
    }

    public function payment($purchase_id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->data['title_at']                 = "Pembayaran - AksesToko";
        $this->data['payment_methods']          = $this->payment->getPaymentMethodByCompanyId($this->session->userdata('supplier_id'));
        $this->data['payment_methods_reject']   = $this->payment->getPaymentMethodByCompanyIdreject($this->session->userdata('supplier_id'));
        $term_payment_kredit_pro  = $this->payment->getActiveTermKreditProByCompanyId($this->session->userdata('supplier_id'));
        $this->data['term_payment_kredit_pro'] = array_column($term_payment_kredit_pro, 'term');
        $this->data['default_term_payment_kredit_pro'] = array(
            '30'       => '30 Hari',
            '45'    => '45 Hari',
            '60'       => '60 Hari',
        );
        $this->data['current_kreditpro'] = $this->data['term_payment_kredit_pro'][0];
        // print_r($this->data['current_kreditpro']);die;
        if ($purchase_id) {
            if ($this->payment->getPaymentPending($purchase_id)) {
                $this->data['supplier'] = $this->at_site->getCompanyByID($this->session->userdata('supplier_id'));
                if (in_array($this->data['supplier']->cf2, ['BIG', 'JBU'])) {
                    $this->session->set_flashdata('error', 'Tidak dapat melakukan pembayaran melalui AksesToko pada Distributor ' . $this->data['supplier']->cf2);
                    redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
                }
                $this->load->model('aksestoko/home_model', 'home');
                $this->data['purchase'] = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);

                if ($this->data['purchase']->grand_total - $this->data['purchase']->paid == 0) {
                    $this->session->set_flashdata('error', 'Pembayaran telah selesai');
                    redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
                }
                $company        = $this->at_site->findCompanyByCf1AndCompanyId($this->data['purchase']->supplier_id, $this->session->userdata('cf1'));
                $kredit_limit   = $this->payment->getKreditLimit($company->customer_group_id);
                $debt           = $this->payment->getTotalDebt($this->session->userdata('company_id'), $purchase_id, $this->data['purchase']->supplier_id);

                $payment_temp                 = $this->payment->getPaymentTempByPurchaseId($purchase_id);
                $this->data['banks']          = $this->bank->getAllBank($this->data['purchase']->supplier_id);
                $this->data['kredit_limit']   = $kredit_limit;
                $this->data['debt']           = $debt;
                $total_payment                = $this->payment->getTotalPaymentByPoId($purchase_id);

                if ($this->data['purchase']->payment_method == 'kredit_pro') {
                    $this->data['url_save'] = base_url(aksestoko_route('aksestoko/order/reset_payment/' . $purchase_id));
                } else {
                    $this->data['url_save'] = base_url(aksestoko_route('aksestoko/order/save_payment/' . $purchase_id));
                }

                $this->data['purchase']->balance = $this->data['purchase']->grand_total - $total_payment->total;

                $this->data['TOP'] = $this->payment->getTOP();

                if ($this->session->userdata('group_customer') == 'lt') {
                    $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
                    $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
                }

                $this->load->view('aksestoko/header', $this->data);
                $this->load->view('aksestoko/payment', $this->data);
                $this->load->view('aksestoko/footer', $this->data);
            } else {
                $this->session->set_flashdata('warning', 'Terdapat Pembayaran yang Masih Di Proses');
                redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
            }
        } else {
            if ($this->session->userdata('is_checkout')) {
                $totalAmount        = 0;
                $totalAmountTempo   = 0;
                $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
                $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

                if ($get_customer_warehouse) {
                    $warehouse_id         = $get_customer_warehouse->default;
                } else {
                    $warehouse_id         = $this->at_site->findCompanyWarehouse($this->session->userdata('supplier_id'))->id;
                }
                // $warehouse_id       = $this->at_site->findCompanyWarehouseByPriceGroup($this->session->userdata('price_group_id'), $this->session->userdata('supplier_id'));
                $warehouse          = $this->at_site->getWarehouseByID($warehouse_id, $this->session->userdata('supplier_id'));
                $totalShipmentPrice = 0;

                foreach ($this->data['cart'] as $item) {
                    $shipmentPrice = 0;
                    if ($warehouse->shipment_price_group_id) {
                        $objShipmentPrice = $this->at_site->getShipmentProductPriceByShipmentPriceGroupIdAndProductId($warehouse->shipment_price_group_id, $item->id);
                        if ($this->session->userdata('delivery_method') == 'pickup') {
                            $shipmentPrice = $objShipmentPrice->price_pickup;
                        } elseif ($this->session->userdata('delivery_method') == 'delivery') {
                            $shipmentPrice = $objShipmentPrice->price_delivery;
                        }
                    }

                    $supplierProduct = $this->product->getProductByID($item->id, $this->session->userdata('supplier_id'), $this->session->userdata('price_group_id'), $this->session->userdata('company_id'));
                    // var_dump($supplierProduct);
                    $price = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_price && $supplierProduct->group_price > 0 ? $supplierProduct->group_price : $supplierProduct->price);
                    $totalAmount += ($price) * $item->cart_qty;

                    $priceTempo = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_kredit && $supplierProduct->group_kredit > 0 ? $supplierProduct->group_kredit : ($supplierProduct->credit_price && $supplierProduct->credit_price > 0 ? $supplierProduct->credit_price : $supplierProduct->price));

                    $totalAmountTempo += $priceTempo * $item->cart_qty;
                    $totalShipmentPrice += $shipmentPrice * $item->cart_qty;
                };

                $promo_data = $this->session->userdata('promo');
                $disc = 0;
                if ($promo_data->tipe == 0) { //jika persentase
                    $disc = ($promo_data->value * $totalAmount) / 100;
                    $discTempo = ($promo_data->value * $totalAmountTempo) / 100;
                    if ($disc > $promo_data->max_total_disc) {
                        $disc = $promo_data->max_total_disc;
                    }

                    if ($discTempo > $promo_data->max_total_disc) {
                        $discTempo = $promo_data->max_total_disc;
                    }
                } else {
                    $disc = (float) $promo_data->value;
                    $discTempo = $disc;
                }
                $this->data['kredit_limit'] = $this->payment->getKreditLimit($this->session->userdata('customer_group_id'));
                $this->data['debt'] = $this->payment->getTotalDebt($this->session->userdata('company_id'), null, $this->session->userdata('supplier_id'));

                $this->data['purchase'] = (object) [
                    'grand_total'           => $totalAmount,
                    'grand_total_tempo'     => $totalAmountTempo,
                    'total_discount'        => $disc,
                    'total_discount_tempo'  => $discTempo,
                    'charge'                => $totalShipmentPrice,
                    'paid'                  => 0,
                    'reference_no' => $this->at_site->getReference('po')
                ];
                // print_r($this->data['purchase']);die;

                $this->data['purchase']->balance = $this->data['purchase']->grand_total;
                $this->data['banks'] = $this->bank->getAllBank($this->session->userdata('supplier_id'));
                $this->data['url_save'] = base_url(aksestoko_route('aksestoko/order/store'));
                $this->data['TOP'] = $this->payment->getTOP();
                $this->data['kredit_mandiri'] = $this->payment->getLimitMandiri();


                if ($this->session->userdata('group_customer') == 'lt') {
                    $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
                    $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
                }

                $this->load->view('aksestoko/header', $this->data);
                $this->load->view('aksestoko/payment', $this->data);
                $this->load->view('aksestoko/footer', $this->data);
            } else {
                $this->session->set_flashdata('warning', 'Lakukan Checkout terlebih dahulu');
                redirect(aksestoko_route('aksestoko/order/checkout'));
            }
        }
    }

    // public function payment_new($purchase_id = null){
    //     if ($purchase_id) {
    //         # code...
    //     }else{
    //         if ($this->session->userdata('is_checkout')) {

    //         }
    //     }

    //     // $this->load->view('aksestoko/header', $this->data);
    //     // $this->load->view('aksestoko/payment_new', $this->data);
    //     // $this->load->view('aksestoko/footer', $this->data);
    // }

    public function save_payment($purchase_id, $first = false)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            $this->data['purchase'] = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);

            if (!$first && $this->data['purchase']->payment_method != 'kredit_pro') {
                // var_dump($this->input->post());die;
                if ($this->input->post('payment_nominal') == 0 || $this->input->post('payment_nominal') == '0') {
                    $this->session->set_flashdata('error', 'Nominal Pembayaran Harus Lebih Dari 0');
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $this->db->trans_begin();
                try {
                    $data = [
                        'bank_id' => $this->input->post('bank_id') ?? $this->data['purchase']->bank_id,
                    ];

                    if (!$this->at_purchase->updatePurchaseById($purchase_id, $data)) {
                        throw new \Exception("Gagal memperbarui data Pesanan");
                    }
                    if ($this->input->post('btn_value') == 'unggah') {
                        $responseUploadImage = $this->upload_bukti_transfer($purchase_id);
                        // print_r($responseUploadImage);die;
                        if (!$responseUploadImage) {
                            throw new \Exception("Gagal Menyimpan Bukti Pembayaran");
                        }

                        if (!$this->audittrail->insertCustomerCreatePayment($this->session->userdata('user_id'), $this->session->userdata('company_id'), $this->session->userdata('supplier_id'), $responseUploadImage['sale_id'], $purchase_id, $responseUploadImage['payment_temp_id'])) {
                            throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_create_payment");
                        }

                        $purchase = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
                        $sale = $this->sales_model->getSalesByRefNo($purchase->cf1, $purchase->supplier_id);

                        $this->load->model('socket_notification_model');
                        $data_socket_notification = [
                            'company_id'        => $sale->biller_id,
                            'transaction_id'    => 'PAY-' . $sale->id,
                            'customer_name'     => $sale->customer,
                            'reference_no'      => $purchase->cf1,
                            'price'             => $this->input->post('payment_nominal'),
                            'type'              => 'new_payment',
                            'to'                => 'pos',
                            'note'              => '',
                            'created_at'        => date('Y-m-d H:i:s')
                        ];
                        $this->socket_notification_model->addNotification($data_socket_notification);

                        $this->db->trans_commit();
                        redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
                    }
                    // }
                } catch (\Throwable $th) {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('error', $th->getMessage());
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $bank_id = $this->input->post('bank_id') == null ? $this->data['purchase']->bank_id : $this->input->post('bank_id');
                $payment_method = $this->input->post('payment_method') == null ? $this->data['purchase']->payment_method : $this->input->post('payment_method');
                $data = [
                    'payment_method' => $payment_method,
                    'bank_id' => $bank_id,
                ];

                if ($payment_method == 'kredit') {
                    if ($this->input->post('payment_durasi') == 'other') {
                        $data['payment_duration'] = $this->input->post('input_payment_durasi');
                    } else {
                        $data['payment_duration'] = $this->input->post('payment_durasi');
                    }
                }

                if ($this->at_purchase->updatePurchaseById($purchase_id, $data)) {
                    if ($this->input->post('btn_value') == 'unggah') {
                        $this->upload_bukti_transfer($purchase_id);
                        return true;
                    } elseif ($this->input->post('btn_value') == 'pending') {
                        return true;
                    }
                }
            }
        }
    }

    public function reset_payment($purchase_id)
    {
        $this->checkATLogged();
        try {
            $this->data['purchase'] = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);

            $this->db->trans_begin();
            $bank_id = $this->input->post('bank_id') == null ? $this->data['purchase']->bank_id : $this->input->post('bank_id');
            $payment_method = $this->input->post('payment_method') == null ? $this->data['purchase']->payment_method : $this->input->post('payment_method');
            $data = [
                'payment_method' => $payment_method,
                'bank_id' => $bank_id,
            ];
            $data['payment_status'] = 'pending';
            if (!$this->at_purchase->updatePurchaseById($purchase_id, $data)) {
                throw new \Exception("Update Gagal");
            }
            $this->db->trans_commit();
            redirect(aksestoko_route('aksestoko/order/success/' . $purchase_id));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function get_kredit_payment()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        // echo ;die;
        // print_r($this->session->userdata('delivery_method'));die;
        $total = 0;
        // echo "sadsad";die;
        // $warehouse_id = $this->at_site->findCompanyWarehouseByPriceGroup($this->session->userdata('price_group_id'), $this->session->userdata('supplier_id'));
        $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
        $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

        if ($get_customer_warehouse) {
            $warehouse_id         = $get_customer_warehouse->default;
        } else {
            $warehouse_id         = $this->at_site->findCompanyWarehouse($this->session->userdata('supplier_id'))->id;
        }
        $warehouse = $this->at_site->getWarehouseByID($warehouse_id, $this->session->userdata('supplier_id'));

        foreach ($this->data['cart'] as $item) {
            $product = $this->product->getProductByID($item->id, $this->session->userdata('supplier_id'), $this->session->userdata('price_group_id'), $this->session->userdata('company_id'));

            $product->price = $product->price_sale && $product->price_sale > 0 ? $product->price_sale : ($product->group_kredit && $product->group_kredit > 0 ? $product->group_kredit : ($product->credit_price && $product->credit_price > 0 ? $product->credit_price : $product->price));

            $unit = $this->product->getUnit($item->sale_unit);

            $quantity = $item->cart_qty;

            $quantity = $this->__operate($quantity, $unit->operation_value, $unit->operator);

            $shipmentPrice = 0;
            if ($warehouse->shipment_price_group_id) {
                $objShipmentPrice = $this->at_site->getShipmentProductPriceByShipmentPriceGroupIdAndProductId($warehouse->shipment_price_group_id, $item->id);
                if ($this->session->userdata('delivery_method') == 'pickup') {
                    $shipmentPrice = $objShipmentPrice->price_pickup;
                } elseif ($this->session->userdata('delivery_method') == 'delivery') {
                    $shipmentPrice = $objShipmentPrice->price_delivery;
                }
            }
            $total += (($product->price + $shipmentPrice) * $quantity);
        }

        $promo_data = $this->session->userdata('promo');
        $disc = 0;

        if ($promo_data->tipe == 0) { //jika persentase
            $disc = ($promo_data->value * $total) / 100;
            if ($disc > $promo_data->max_total_disc) {
                $disc = $promo_data->max_total_disc;
            }
        } else {
            $disc = (float) $promo_data->value;
        }

        $data = [
            'promo_data' => $promo_data,
            'total' => $total,
            'disc' => $disc,
            'grand_total' => $total - $disc
        ];

        echo json_encode($data);
    }

    public function get_detail_bank($bank_id, $type = "")
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $bank_data = $this->bank->getBankById($bank_id);
        $output = '';
        if ($bank_id && $bank_data) {
            $output = "
            <label>Ke nomor rekening tujuan</label>
            <p class=\"h5 mb-1\">
               <input type=\"text\" class=\"rekBank\" id=\"rekBank" . $type . "\" value='" . $bank_data->no_rekening . "' readOnly>
             </p>
             <p class=\"mb-1\">a/n " . $bank_data->name . "</p>
             <a id=\"salinRekening\" href=\"javascript:void(0)\" onclick=\"copyNorek('" . $type . "')\" href=\"\" class=\"text-blue\"><i class=\"fal fa-copy mr-1\"></i> Salin rekening</a>
             <img src=\"" . base_url() . "/assets/uploads/" . $bank_data->logo . "\" class=\"img-fluid mt-3\" width=\"100\" alt=\"Logo\">
            ";
        }

        // echo $output;
        $res = array(
            // 'pagination'=>$pagination,
            'output' => $output
        );

        echo json_encode($res);
    }

    public function bukti_transfer_form($purchase_id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $purchase = $this->site->getPurchaseByID($purchase_id);
        $total_payment = $this->payment->getTotalPaymentByPoId($purchase_id);
        if ($total_payment->total < $purchase->grand_total) {
            $this->data['purchase'] = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $this->data['purchase']->balance = $purchase->grand_total - $total_payment->total;

            if ($this->session->userdata('group_customer') == 'lt') {
                $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
                $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
            }

            $this->load->view('aksestoko/header', $this->data);
            $this->load->view('aksestoko/bukti-pembayaran', $this->data);
            $this->load->view('aksestoko/footer', $this->data);
        } else {
            $this->session->set_flashdata('warning', 'Maaf Anda Telah Upload Bukti Bayar');
            redirect(aksestoko_route('aksestoko/order/'));
        }
    }

    public function add_promo()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            // $total_pembelian = $this->input->post('total_pembelian');
            $company = $this->companies_model->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
            $promo_data = $this->at_site->findPromoByCode($this->input->post('code_promo'), $this->session->userdata('supplier_id'), $company->id);
            if ($promo_data) {
                $total_pembelian = 0;
                foreach ($this->data['cart'] as $key => $value) {
                    $total_pembelian += ($value->price * $value->cart_qty);
                }
                $arr = $this->check_promo($promo_data, $total_pembelian);

                $this->session->set_flashdata($arr['type'], $arr['msg']);
            } else {
                $this->session->set_flashdata('error', 'Kode Promo tidak tersedia');
            }
        }
        redirect(aksestoko_route('aksestoko/order/cart'));
    }

    public function delete_promo($value = '')
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->session->unset_userdata('promo');
        $this->session->set_flashdata('message', 'Kode Promo Telah Dihapus');
        redirect(aksestoko_route('aksestoko/order/cart'));
    }

    public function success($purchase_id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->data['title_at'] = "Pemesanan Berhasil - AksesToko";
        $this->data['purchase_data'] = $this->site->getPurchaseByID($purchase_id);
        $this->data['sale'] = $this->at_sale->findSalesByReferenceNo($this->data['purchase_data']->cf1, $this->data['purchase_data']->supplier_id);
        if (!$this->data['purchase_data'] || !$purchase_id) {
            redirect(aksestoko_route('aksestoko/home/main'));
        }
        // $this->data['purchase_id']=$purchase_id;
        $this->data['bank_data'] = $this->bank->getBankById($this->data['purchase_data']->bank_id);
        // print_r($this->data['bank_data']);die;
        $this->data['object'] = $this;

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/success', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    /**
     * POST
     *
     * Request :
     * - purchase_id -> int
     * - payment_receipt -> file
     */

    public function upload_bukti_transfer($purchase_id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            // $purchase_id = $this->input->post('purchase_id');
            // echo $this->input->post('purchase_id');die;
            $purchase_data = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $sales_data = $this->at_sale->findSalesByReferenceNo($purchase_data->cf1, $purchase_data->supplier_id);
            $supplier = $this->at_site->getCompanyByID($sales_data->biller_id);
            $bank = $this->bank->getBankById($purchase_data->bank_id);
            // if ($purchase_data->payment_method != 'kredit') {
            //     $nominal = $purchase_data->grand_total;
            // }else{

            $nominal = $this->input->post('payment_nominal');
            // }
            // echo $nominal
            // $config['upload_path']          = 'assets/uploads/payment_receipt';
            // $config['allowed_types']        = 'gif|jpeg|png|jpg';
            // $config['file_name']            = $purchase_id . "-" . str_replace("/", "", $purchase_data->reference_no) . "." . pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION);
            // $config['overwrite']            = true;
            // $config['max_size']             = 1024; // 1MB

            // $this->load->library('upload', $config);

            // if ($this->upload->do_upload('payment_receipt')) {
            if ($_FILES['payment_receipt']['error'] == 0) {
                $check = getimagesize($_FILES['payment_receipt']["tmp_name"]);

                if (!$check) {
                    throw new \Exception("File tidak valid");
                }
                if ($_FILES['payment_receipt']["size"] > 16000000) { //15mb
                    throw new \Exception("Ukuran File terlalu besar");
                }

                // $image = base64_encode(file_get_contents($_FILES['payment_receipt']["tmp_name"]));
                // $uploadedImg = json_decode($this->at_site->uploadImage($image));
                $uploadedImg = $this->integration->upload_files($_FILES['payment_receipt']);
            }
            if ($uploadedImg) {
                $dataPaymentTemp = [
                    'purchase_id' => $purchase_id,
                    'sale_id' => $sales_data->id,
                    'nominal' => $nominal,
                    'url_image' => $uploadedImg->url,
                    'status' => 'pending',
                    'reference_no' => payment_tmp_ref()
                ];
                $payment_temp_id = $this->payment->addPaymentTemp($dataPaymentTemp);
                if (!$payment_temp_id) {
                    throw new \Exception("Gagal Upload Bukti Pembayaran");
                }

                if ($this->integration->isIntegrated($supplier->cf2)) {
                    $dataPaymentTemp['created_at'] = date('Y-m-d H:i:s');
                    $response = $this->integration->create_payment_integration($supplier->cf2, $this->session->userdata('username'), (array) $sales_data, $dataPaymentTemp, (array) $bank);
                    if (!$response) {
                        throw new \Exception("Tidak dapat mengirim pembayaran ke distributor");
                    }
                    if (!$this->payment->updatePaymentTemp(['cf1' => $response, 'cf2' => $supplier->cf2], ['reference_no' => $dataPaymentTemp['reference_no']])) {
                        throw new \Exception("Tidak dapat memperbarui reference number pembayaran dari distributor");
                    }
                }

                $this->session->set_flashdata('message', 'Upload Bukti Pembayaran Berhasil');
                return ['payment_temp_id' => $payment_temp_id, 'sale_id' => $sales_data->id];
            } else {
                // $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', "Gagal mengunggah gambar");
                return false;
            }
            // redirect('aksestoko/order/payment/'.$purchase_id);
        }
    }

    /**
     * POST
     *
     * Request :
     * - product_id -> int
     * - quantity -> decimal
     * - supplier_id -> int
     * - user_id -> int
     *
     */
    public function add_cart()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $requestCart = [
                    'product_id' => $this->input->post('product_id'),
                    'quantity' => $this->input->post('quantity'),
                    'supplier_id' => $this->input->post('supplier_id'),
                    'user_id' => $this->input->post('user_id'),
                ];
                $redirect_to_cart = $this->input->post('beli_sekarang') == 1;
                $product_id = $this->input->post('product_id');
                $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
                $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

                if ($get_customer_warehouse) {
                    $warehouse_id         = $get_customer_warehouse->default;
                } else {
                    $warehouse_id         = $this->at_site->findCompanyWarehouse($this->session->userdata('supplier_id'))->id;
                }
                // $warehouse_id = $this->at_site->findCompanyWarehouseByPriceGroup($this->session->userdata('price_group_id'), $this->input->post('supplier_id'));
                $quantity = $this->input->post('quantity');

                $addCart = $this->at_site->insertCart($requestCart);
                if (!$addCart) {
                    throw new \Exception("Gagal menambahkan item");
                }

                $this->session->set_flashdata('message', 'Berhasil menambah item');
                $this->db->trans_commit();

                if ($redirect_to_cart) {
                    redirect(aksestoko_route('aksestoko/order/cart'));
                }
            } catch (\Throwable $th) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        // redirect($_SERVER['HTTP_REFERER']);
        redirect(aksestoko_route('aksestoko/home/main'));
    }

    /**
     * POST
     *
     * Request :
     * - id -> int
     * - qty -> decimal
     *
     */
    public function update_cart()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            $this->db->trans_begin();
            // print_r($this->input->post());
            try {
                $id = $this->input->post('id');
                $qty = $this->input->post('qty');
                // $count = count($id);
                // echo $qty;die;
                // for ($i=0; $i < $count; $i++) {
                $get_cart = $this->db->get_where('carts', ['id' => $id])->row();
                $product_id = $get_cart->product_id;
                $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
                $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

                if ($get_customer_warehouse) {
                    $warehouse_id         = $get_customer_warehouse->default;
                } else {
                    $warehouse_id         = $this->at_site->findCompanyWarehouse($this->session->userdata('supplier_id'))->id;
                }
                // $warehouse_id = $this->at_site->findCompanyWarehouseByPriceGroup($this->session->userdata('price_group_id'), $this->session->userdata('supplier_id'));
                $alert = '';
                /*$cek_stok = $this->at_site->cek_booking_item($product_id, $warehouse_id, $qty);
                if(!empty($cek_stok)){
                    $alert = $cek_stok;
                    $res['max_stok'] = $alert;
                    $res['qty_before'] = $get_cart->quantity;
                    echo json_encode($res);
                    return false;
                }*/

                $updateCart = $this->at_site->updateProductInCart($id, $this->session->userdata('supplier_id'), $this->session->userdata('user_id'), $qty);
                if (!$updateCart) {
                    throw new \Exception("Gagal memperbarui item");
                }
                // }

                // $this->session->set_flashdata('message', 'Berhasil memperbarui keranjang belanja');
                $this->db->trans_commit();

                $cart = $this->at_site->getProductInCart($this->session->userdata('supplier_id'), $this->session->userdata('user_id'), $this->session->userdata('price_group_id'));

                $totalQty = 0;
                $totalAmount = 0;
                $totalPoint = 0;
                foreach ($cart as $item) {
                    $totalQty += $item->cart_qty;
                    $totalAmount += $item->price * $item->cart_qty;
                    $totalPoint = +0;
                }

                $company = $this->companies_model->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
                $promo_data = $this->at_site->findPromoByCode($this->input->post('code_promo'), $this->session->userdata('supplier_id'), $company->id);

                $res = [
                    'totalQty' => $totalQty,
                    'totalAmount' => $totalAmount,
                    'totalPoint' => $totalPoint,
                    'max_stok' => $alert
                ];
                if ($promo_data) {
                    $arr = $this->check_promo($promo_data, $totalAmount);
                    if ($arr['status'] == true) {
                        $res['status_promo'] = true;
                        $res['promo_data'] = $promo_data;
                    } else {
                        $res['status_promo'] = false;
                    }
                } else {
                    $res['status_promo'] = false;
                }
                echo json_encode($res);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        // redirect($_SERVER['HTTP_REFERER']);
    }

    public function view($id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->data['order'] = $this->at_purchase->getOrderItems($id, $this->session->userdata('user_id'));

        if (!$this->data['order']) {
            $this->session->set_flashdata('error', "Pesanan tidak ditemukan atau tidak memiliki akses untuk melihat pesanan tersebut.");
            redirect(aksestoko_route('aksestoko/order'));
        }

        $this->data['payment_pending'] = $this->payment->getPaymentPending($id);
        $this->data['sale'] = $this->at_sale->findSalesByReferenceNo($this->data['order']->cf1, $this->data['order']->supplier_id);
        $this->data['payment_total'] = $this->payment->getTotalPaymentByPoId($this->data['order']->id)->total;
        $this->data['payments_temp'] = $this->payment->getListPaymentTemp($id);

        $this->data['deliveries'] = $this->at_sale->getDeliveriesItems($this->data['order']->supplier_id, $this->data['order']->cf1);
        // var_dump($this->data['deliveries'], $this->data['deliveries'][0]->items);die;
        $this->data['title_at'] = "Lihat Pesanan - AksesToko";
        $this->data['object'] = $this;
        // print_r();die;

        if ($this->data['order']->payment_method == 'kredit_pro' && $this->data['order']->payment_status != 'reject') {
            $this->data['url_payment'] = 'aksestoko/order/payment_kreditpro';
        } else {
            $this->data['url_payment'] = 'aksestoko/order/payment';
        }

        $this->data['company'] = $this->at_site->findCompany($this->data['order']->company_id);
        $this->data['distributor'] = $this->at_site->findCompany($this->data['order']->supplier_id);

        // var_dump($this->payment->getPaymentTempByPurchaseId($this->data['order']->id));die;

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/detail-order', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    //GET
    public function cart()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        //mengecek apakah cart ada isinya
        if (!$this->data['cart'] || count($this->data['cart']) == 0) {
            $this->session->set_flashdata('warning', "Keranjang Belanja kosong, masukkan item terlebih dahulu");
            redirect(aksestoko_route('aksestoko/home/main'));
        }

        $total_pembelian = 0;
        foreach ($this->data['cart'] as $key => $value) {
            $value->min_order = $value->min_order && $value->min_order > 0 ? (int) $value->min_order : 1;
            $value->is_multiple = ($value->is_multiple == 1);

            $total_pembelian += ($value->price * $value->cart_qty);
        }

        $promo_data = $this->session->userdata('promo');
        // var_dump($this->data['cart']);die;
        if ($promo_data) {
            $arr = $this->check_promo($promo_data, $total_pembelian);
            // var_dump($arr);die;
            $this->session->set_flashdata($arr['type'], $arr['msg']);
        }

        $this->data['promo_data'] = $this->session->userdata('promo');
        $this->data['object'] = $this;
        $this->data['title_at'] = "Keranjang Belanja - AksesToko";

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/cart', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    //GET
    public function remove_item_cart($id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->at_site->removeProductInCart($id, $this->session->userdata('supplier_id'), $this->session->userdata('user_id'))) {
            $this->session->set_flashdata('message', "Berhasil menghapus item dari keranjang belanja");
        } else {
            $this->session->set_flashdata('error', "Gagal menghapus item");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    //GET
    public function checkout()
    {
        $this->session->unset_userdata('delivery_date');
        // echo $this->at_site->findCompanyWarehouseByPriceGroup($this->session->userdata('price_group_id'), $this->session->userdata('supplier_id'));die;
        // pr($this->data['cart']);die;
        $this->checkATLogged(); // seharusnya di paling atas baris

        //mengecek apakah cart ada isinya
        if (!$this->data['cart'] || count($this->data['cart']) == 0) {
            $this->session->set_flashdata('warning', "Keranjang Belanja kosong, masukkan item terlebih dahulu");
            redirect(aksestoko_route('aksestoko/home/main'));
        }


        //membayar dari keranjang belanjaan (cart)

        $this->data['title_at'] = "Periksa Belanjaan - AksesToko";
        $this->data['addresses'] = array_merge([$this->at_site->findCompany($this->session->userdata('company_id'))], $this->at_site->getCompaniesAddress($this->session->userdata('company_id')));
        $this->data['company'] = $this->at_site->findCompany($this->session->userdata('company_address_id') == null ? $this->session->userdata('company_id') : $this->session->userdata('company_address_id'));
        $this->data['supplier'] = $this->at_site->findCompany($this->session->userdata('supplier_id'));
        $this->data['promo_data'] = $this->session->userdata('promo');
        $this->data['object'] = $this;

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/checkout', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    //GET
    public function set_address($id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->at_site->findCompanyAddress($id, $this->session->userdata('company_id'))) {
            $this->session->set_userdata(['company_address_id' => $id]);
        }

        redirect(aksestoko_route('aksestoko/order/checkout'));
    }

    /**
     * POST
     *
     * Request :
     * - purchase_id -> int -> v
     * - product_code -> array | string -> v
     * - quantity_received -> array | decimal -> v
     * - do_ref -> string -> v
     * - do_id -> int -> v
     * - delivery_item_id -> array | int
     * - good -> array | decimal
     * - bad -> array | decimal
     * - note -> text
     * - fileUpload -> file
     */
    public function confirm_received()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        // var_dump($this->input->post());die;
        if ($this->isPost()) {
            // print_r($_FILES["payment_receipt"]);
            // var_dump($this->input->post());
            // die;

            $this->db->trans_begin();

            try {
                $delivery           = $this->at_sale->getDeliveryByID($this->input->post('do_id'));
                $sale               = $this->at_sale->getSalesById($delivery->sale_id);
                $cek_qty_delivery   = $this->at_sale->findDeliveryItems($this->input->post('do_id'));
                $purchase           = $this->at_purchase->getPurchaseByID($this->input->post('purchase_id'));

                $jumlah             = count($this->input->post('product_id'));
                for ($i = 0; $i < $jumlah; $i++) {
                    $key = array_search($this->input->post('product_id')[$i], array_column($cek_qty_delivery->items, 'product_id'));
                    if ($this->input->post('quantity_received')[$i] != $cek_qty_delivery->items[$key]->quantity_sent) {
                        throw new \Exception("Maaf !! Terjadi perubahan kuantitas pada " . $this->input->post('product_code')[$i] . " " . $this->input->post('product_name')[$i] . " dari " . $this->input->post('quantity_received')[$i] . " menjadi " . (int) $cek_qty_delivery->items[$key]->quantity_sent);
                    }
                }

                if ($delivery->receive_status == 'received') {
                    if ($delivery->is_reject == 1 && $this->input->post('bad') > 0) {
                        $this->db->update('deliveries', ['is_reject' => 2, 'is_confirm' => 1], ['id' => $this->input->post('do_id')]);
                    } elseif ($delivery->is_reject == 1 && $this->input->post('bad') <= 0) {
                        $this->db->update('deliveries', ['is_confirm' => 1], ['id' => $this->input->post('do_id')]);
                    } elseif (is_null($delivery->is_reject) && is_null($delivery->is_confirm) && is_null($delivery->is_approval)) {
                        throw new \Exception("DO telah diterima toko");
                    }
                }

                $data = [
                    'purchase_id' => $this->input->post('purchase_id'),
                    'product_code' => $this->input->post('product_code'),
                    'quantity_received' => $this->input->post('quantity_received'),
                    'do_ref' => $this->input->post('do_ref'),
                    'do_id' => $this->input->post('do_id'),
                    'delivery_item_id' => $this->input->post('delivery_item_id'),
                    'good' => $this->input->post('good'),
                    'bad' => $this->input->post('bad'),
                    'note' => $this->input->post('note'),
                    'file' => $_FILES["fileUpload"],
                ];
                // var_dump($data);die;
                if ($sale->sale_type == 'booking') {
                    $confirm = $this->at_purchase->confirmReceivedBooking($data, $this->session->userdata('user_id'), $delivery->sale_id);
                } else {
                    $confirm = $this->at_purchase->confirmReceived($data, $this->session->userdata('user_id'), $delivery->sale_id);
                }
                if (!$confirm) {
                    throw new \Exception("Gagal konfirmasi penerimaan");
                }

                if ($purchase->payment_method == 'kredit_mandiri') {
                    $reference_no = $sale->reference_no;
                    $reff = str_replace("/", "", $sale->reference_no);
                    $grandtotal = (float)$purchase->grand_total;
                    $dataMandiri = [
                        "requestHeader"     => [
                            "referenceNo"   => $reff . $sale->biller_id,
                            "siteCode"      => "BMRIID"
                        ],
                        "requestData"       => [
                            "communityCode" => "",
                            "memberCode"    => "",
                            "InvoiceDetail" => [
                                "invoiceAmount" => $grandtotal,
                                "invoiceCcy"    => "IDR",
                                "invoiceDate"   => date('Y-m-d', strtotime($sale->date)),
                                "invoiceMaturityDate" => date('Y-m-d'),
                                "invoiceNo"     => $reference_no . '-' . $sale->biller_id
                            ]
                        ]
                    ];

                    // $loadInvoice = $this->integration->mandiri_loadInvoice($dataMandiri);
                    // if(!$loadInvoice){
                    //     throw new \Exception("Gagal mengirim data ke Bank Mandiri");
                    // }

                    $get_purchase_credit = $this->at_purchase->getPurchaseByID($this->input->post('purchase_id'));
                    if ($get_purchase_credit->status == 'received') {
                        $dataPaymentTemp = [
                            'purchase_id' => $purchase->id,
                            'sale_id' =>  $sale->id,
                            'nominal' => $grandtotal,
                            'url_image' => '-',
                            'status' => 'pending',
                            'reference_no' => payment_tmp_ref()
                        ];
                        $payment_temp_id = $this->payment->addPaymentTemp($dataPaymentTemp);
                        if (!$payment_temp_id) {
                            throw new \Exception("Gagal membuat pembayaran");
                        }
                    }
                }

                // print_r($this->session->userdata());die;
                if (!$this->audittrail->insertCustomerConfirmDelivery($this->session->userdata('user_id'), $this->session->userdata('company_id'), $this->session->userdata('supplier_id'), $delivery->sale_id, $this->input->post('purchase_id'), $delivery->id)) {
                    throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_confirm_delivery");
                }
                //get newest delivery after update (confirmReceived)
                $delivery = $this->at_sale->getDeliveryByID($this->input->post('do_id'));
                $deliveryItems = $this->at_sale->getDeliveryItemsByDeliveryId($delivery->id);

                // $this->save_payment($this->input->post('purchase_id'), false);

                $supplier = $this->at_site->getCompanyByID($sale->biller_id);

                if ($this->integration->isIntegrated($supplier->cf2)) {
                    $response = $this->integration->confirm_received_integration($supplier->cf2, $this->session->userdata('username'), $sale, $delivery, $deliveryItems);
                    if (!$response) {
                        throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                    }
                }

                $this->load->model('socket_notification_model');
                $data_socket_notification = [
                    'company_id'        => $sale->biller_id,
                    'transaction_id'    => 'DO-' . $delivery->id,
                    'customer_name'     => $sale->customer,
                    'reference_no'      => $sale->reference_no . ' (' . $delivery->do_reference_no . ')',
                    'price'             => '',
                    'type'              => array_sum($this->input->post('bad')) > 0 ? 'confirm_received_partial_delivery' : 'confirm_received_all_delivery',
                    'to'                => 'pos',
                    'note'              => '',
                    'created_at'        => date('Y-m-d H:i:s')
                ];

                $this->socket_notification_model->addNotification($data_socket_notification);

                $this->session->set_flashdata('message', 'Berhasil mengonfirmasi penerimaan');

                if ($sale->sale_type == 'booking') {
                    if ($this->site->checkAutoClose($sale->id)) {
                        $this->sales_model->closeSale($sale->id);
                    }
                }

                $this->send_email($data['purchase_id'], $sale);
                $this->db->trans_commit();

                redirect(aksestoko_route('aksestoko/order/view/' . $this->input->post('purchase_id')));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    private function send_email($purchas_id, $sale)
    {
        $purchase = $this->at_purchase->getPurchaseByID($purchas_id);
        $bank = $this->site->findThirdPartyBankByCompanyId($sale->biller_id);
        // print_r($sale);die;
        if ($purchase->payment_method == 'kredit_pro' && $purchase->status == 'received') {
            $attachment = [];
            $attachment = $this->generatePDFDeliv($sale);
            $pathPDFInv = $this->generatePDFInv($sale, $purchase);
            array_push($attachment, $pathPDFInv);
            $receiver = $this->at_purchase->getEmailReceiverThirdParty($purchase->payment_method, 'receiver');
            $sender = $this->at_purchase->getEmailSenderThirdParty($purchase->payment_method, 'sender');
            // print_r($sender);die;
            // $receiver=[
            //     'nizamuddin.dzaky@gmail.com',
            //     'diosuryaputra95@gmail.com',
            //     'abdullahfahmi1997@gmail.com'
            // ];
            // $sender = [
            //     'email'     =>'adm.aksestoko@gmail.com',
            //     'password'  =>'Indonesia1',
            //     'name'      => 'AksesToko.id'
            // ];
            $toko =  $this->site->getUser($sale->created_by);
            $subject = "AksesToko.id Order Details : " . $sale->reference_no . "-" . $sale->biller_id;
            $body = 'Dear KreditPro Team,<br>
                        Following is the details of the Order from <b>AksesToko.id</b>:<br>
                        - OrderID       : ' . $sale->reference_no . "-" . $sale->biller_id . '<br>
                        - Owner Name    : ' . $toko->first_name . ' ' . $toko->last_name . '<br>
                        - Store         : ' . $toko->company . '<br>
                        - Phone Number  : ' . $toko->phone . '<br>
                        - Amount        :  Rp ' . number_format(abs($sale->grand_total), 0, ',', '.') . '<br><br>

                        Following is the detail of distributor accounts<br>
                        - Name              : ' . strtoupper($bank->name) . ' <br>
                        - Bank              : ' . strtoupper($bank->bank_name) . ' <br>
                        - Account Number    : ' . $bank->no_rekening . '<br><br>

                        Best Regards,<br><br>

                        AksesToko';
            if ($this->sma->send_email_php_mailer($sender, $receiver, $attachment, $subject, $body)) {
                $this->deleteFileAttachment($attachment);
                $this->at_purchase->updatePurchaseById($purchase->id, ['third_party_sent_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function deleteFileAttachment($attachments)
    {
        if (count($attachments) > 0) {
            foreach ($attachments as $key => $attachment) {
                unlink($attachment);
            }
        }
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
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->at_sale->getPaymentsForSale($inv->id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id, $inv->biller_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->at_sale->getAllInvoiceItems($inv->id);
        $this->data['return_sale'] = $inv->return_id ? $this->at_sale->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->at_sale->getAllInvoiceItems($inv->return_id) : null;
        $this->data['po'] = $purchase;
        $name = "INVOICE_-_" . str_replace('/', '_', $inv->reference_no) . "-" . $inv->biller_id . ".pdf";
        $html = $this->load->view($this->theme . 'sales/sale_pdf_kredit_pro', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        return $this->sma->generate_pdf($html, $name, 'S', $this->data['biller']->invoice_footer);
    }

    //GET
    public function review($id = null, $delivery_id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->data['order'] = $this->at_purchase->getOrderItems($id, $this->session->userdata('user_id'));

        // $this->data['sum_received_item_purchase'] = $this->at_purchase->getSumReceiveItemByPurchaseId($id);
        // $this->data['sum_order_item_purchase'] = $this->at_purchase->getSumOrderItemByPurchaseId($id);
        // print_r($this->data['sum_order_item_purchase']);die;

        $this->data['delivery'] = $this->at_sale->findDeliveryItems($delivery_id);
        if (!$this->data['order'] || !$this->data['delivery'] || ($this->data['delivery']->receive_status == "received" && $this->data['delivery']->is_approval == 1)) {
            redirect(aksestoko_route('aksestoko/order/view/' . $id));
        }
        if ($this->data['delivery']->status == 'packing') {
            $this->session->set_flashdata('error', 'Status pengiriman masih sedang dikemas.');
            redirect(aksestoko_route('aksestoko/order/view/' . $id));
        }
        $this->data['title_at'] = "Ulasan Penerimaan Barang - AksesToko";
        $this->data['object'] = $this;

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/review-product', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    /**
     * POST
     *
     * Request :
     * - note -> string
     * - purchase_item_id -> array | int
     * - good -> array | decimal
     * - bad -> array | decimal
     *
     */
    public function update_review($purchase_id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        // var_dump($this->input->post());die;
        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $data = [];
                $data['note'] = $this->input->post('note');
                $purchase_item_id = $this->input->post('purchase_item_id');
                $good = $this->input->post('good');
                $bad = $this->input->post('bad');

                foreach ($purchase_item_id as $i => $pid) {
                    $data['items'][] = [
                        'purchase_item_id' => $pid,
                        'good' => $good[$i],
                        'bad' => $bad[$i]
                    ];
                }

                $update = $this->at_purchase->updateReview($purchase_id, $data);

                if (!$update) {
                    throw new \Exception("Gagal memberi review");
                }

                $this->session->set_flashdata('message', 'Berhasil memberi review');
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
    }



    public function get_delivery($order_id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $order = $this->at_purchase->findPurchase($order_id, $this->session->userdata('user_id'));
        if (!$order) {
            echo null;
            return;
        }
        $res = "";
        $deliveries = $this->at_sale->getDeliveriesItems($order->supplier_id, $order->cf1);
        if ($deliveries) {
            foreach ($deliveries as $i => $delivery) {
                if ($delivery->receive_status != "received" && $delivery->status != 'packing') {
                    $res .= '
                    <tr>
                        <td class="terimaBarang" style="vertical-align:middle; text-align: center;">' . $delivery->do_reference_no . '</td>
                        <td class="terimaBarang" style="vertical-align:middle; text-align: center;" class="text-' . $this->__status($delivery->status)[1] . '">' . $this->__status($delivery->status)[0] . '</td>
                        <td class="terimaBarang" style="vertical-align:middle; text-align: center;">' . $this->__convertDate($delivery->date) . '</td>
                        <td class="terimaBarang" style="vertical-align:middle; text-align: center;">' . $delivery->delivered_by . '</td>
                        <td class="terimaBarang" style="vertical-align:middle; text-align: center;"><a id="terima-barang" href="' . base_url(aksestoko_route("aksestoko/order/review/")) . $order->id . '/' . $delivery->id . '" class="btn-sm btn-success" style="border-radius: 40px;">Terima</a></td>
                    </tr>';
                }
            }
        }
        if (strlen($res) == 0) {
            $res = '
                <tr>
                    <td colspan="5">Tidak ada data pengiriman untuk diterima</td>
                </tr>
            ';
        }

        echo json_encode(['data_delivery' => $res]);
    }

    public function check_promo($promo_data, $total_pembelian)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $tot_trans = $this->promotion->getTransactionByPromo($promo_data->id);
        $tot_trans_comp = $this->promotion->getTransactionByCompany($promo_data->id, $this->session->userdata('company_id'));
        if ($promo_data->end_date >= date('Y-m-d') && $promo_data->start_date <= date('Y-m-d')) {
            if ($total_pembelian >= $promo_data->min_pembelian) {
                if ($tot_trans_comp < $promo_data->max_toko) {
                    if ($tot_trans < $promo_data->quota) {
                        $session = array('promo' => $promo_data);

                        $this->session->set_userdata($session);
                        return ['type' => 'message', 'msg' => 'Kode Promo Berhasil Diterapkan', 'status' => true];
                    } else {
                        $this->session->unset_userdata('promo');
                        return ['type' => 'warning', 'msg' => 'Tidak Bisa Menggunakan Kode Promo Ini, Kuota Telah Habis', 'status' => false];
                    }
                } else {
                    $this->session->unset_userdata('promo');
                    return ['type' => 'warning', 'msg' => 'Tidak Bisa Menggunakan Kode Promo Ini, Anda Telah Mencapai Limit Kuota', 'status' => false];
                }
            } else {
                $this->session->unset_userdata('promo');
                return ['type' => 'warning', 'msg' => 'Tidak Bisa Menggunakan Kode Promo Ini, Minimal Pembelian Anda Kurang', 'status' => false];
            }
        } else {
            $this->session->unset_userdata('promo');
            return ['type' => 'warning', 'msg' => 'Batas Waktu Pemakaian Promo Ini Telah Habis atau Diluar Ketentuan', 'status' => false];
        }
    }

    public function invoice($id = null, $id_p = null, $view = null, $save_bufffer = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->load->model('sales_model');
        $this->lang->load('sales', $this->Settings->user_language);
        $this->Owner                  = true;
        // $inv                       = $this->at_sale->getInvoiceByID($id);
        $inv                          = $this->at_sale->getInvoiceAtByID($id_p);
        $this->data['barcode']        = "<img src='" . site_url('products/gen_barcode/' . $inv->cf1) . "' alt='" . $inv->cf1 . "' class='pull-left' />";
        $this->data['customer']       = $this->site->getCompanyByID($inv->company_id);
        $this->data['payments']       = $this->at_sale->getPaymentsForPurchase($id_p);
        $this->data['biller']         = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['user']           = $this->site->getUser($inv->created_by);
        $this->data['warehouse']      = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']            = $inv;
        $this->data['rows']           = $this->at_sale->getAllInvoiceItemsFromPurchase($id_p);
        $this->data['return_sale']    = $inv->return_id ? $this->at_sale->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']    = $inv->return_id ? $this->at_sale->getAllInvoiceItemsFromPurchase($inv->return_id) : null;
        $this->data['po']             =  $this->sales_model->getPurchasesByRefNo($this->data['inv']->cf1, $this->data['inv']->supplier_id);
        $name = "INVOICE_-_" . str_replace('/', '_', $inv->cf1) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
    }

    /**
     * GET
     */
    public function cancel_order($purchase_id = null)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($purchase_id) {
            $this->db->trans_begin();

            try {
                $cancel = $this->at_purchase->cancelOrder($purchase_id, $this->session->userdata('user_id'));

                if (!$cancel) {
                    throw new \Exception("Gagal membatalkan pesanan");
                }

                $purchase = (array) $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
                $sale = (array) $this->at_sale->findSalesByReferenceNo($purchase['cf1'], $purchase['supplier_id']);
                $saleItems = [];
                foreach ($this->at_sale->getSaleItemsBySaleId($sale['id']) as $i => $saleItem) {
                    $saleItems[] = (array) $saleItem;
                }

                $supplier = $this->at_site->getCompanyByID($sale['biller_id']);

                if ($this->integration->isIntegrated($supplier->cf2)) {
                    $response = $this->integration->update_confirmation_integration($supplier->cf2, $this->session->userdata('username'), $sale, $saleItems, $purchase);
                    if (!$response) {
                        throw new \Exception("Tidak dapat membatalkan pesanan ke distributor");
                    }

                    $dataSale['cf1'] = $response;
                    $dataSale['cf2'] = $supplier->cf2;
                    $dataSale['id'] = $sales_id;
                    if (!$this->at_sale->updateOrders($dataSale, ['id' => $purchase_id])) {
                        throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                    }
                }

                $this->load->model('socket_notification_model');
                $data_socket_notification = [
                    'company_id'        => $purchase['supplier_id'],
                    'transaction_id'    => 'SALE-' . $sale['id'],
                    'customer_name'     => $sale['customer'],
                    'reference_no'      => $purchase['cf1'],
                    'price'             => '',
                    'type'              => 'canceled_order',
                    'to'                => 'pos',
                    'note'              => '',
                    'created_at'        => date('Y-m-d H:i:s')
                ];

                $this->socket_notification_model->addNotification($data_socket_notification);

                $this->session->set_flashdata('message', 'Pesanan berhasil dibatalkan');
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
        }
        redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
    }

    public function cancel_update_price($purchase_id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->db->trans_begin();
        try {
            $purchase = (array) $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $sale = (array) $this->at_sale->findSalesByReferenceNo($purchase['cf1'], $purchase['supplier_id']);
            $saleItems = [];
            foreach ($this->at_sale->getSaleItemsBySaleId($sale['id']) as $i => $saleItem) {
                $saleItems[] = (array) $saleItem;
            }
            $sale['sale_status'] = 'canceled';
            $sale['is_updated_price'] = null;
            $purchase['status'] = 'canceled';
            // print_r($sale);die;

            if (!$this->at_sale->updateOrders($sale, $purchase)) {
                throw new \Exception("Gagal membatalkan pesanan");
            }

            $supplier = $this->at_site->getCompanyByID($sale['biller_id']);

            if ($this->integration->isIntegrated($supplier->cf2)) {
                $response = $this->integration->update_confirmation_integration($supplier->cf2, $this->session->userdata('username'), $sale, $saleItems, $purchase);
                if (!$response) {
                    throw new \Exception("Tidak dapat membatalkan pesanan ke distributor");
                }

                $dataSale['cf1'] = $response;
                $dataSale['cf2'] = $supplier->cf2;
                $dataSale['id'] = $sales_id;
                if (!$this->at_sale->updateOrders($dataSale, ['id' => $purchase_id])) {
                    throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                }
            }

            if (!$this->audittrail->insertCustomerRejectPrice($this->session->userdata('user_id'), $this->session->userdata('company_id'), $this->session->userdata('supplier_id'), $sale->id, $purchase_id)) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_reject_price");
            }

            $this->session->set_flashdata('message', 'Pesanan berhasil dibatalkan');
            $this->db->trans_commit();
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
        }
        redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
    }

    public function confirm_update_price($purchase_id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->db->trans_begin();
        try {
            $purchase = (array) $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $sale = (array) $this->at_sale->findSalesByReferenceNo($purchase['cf1'], $purchase['supplier_id']);
            $saleItems = [];
            foreach ($this->at_sale->getSaleItemsBySaleId($sale['id']) as $i => $saleItem) {
                $saleItems[] = (array) $saleItem;
            }
            $sale['sale_status'] = 'confirmed';
            $sale['is_updated_price'] = null;
            $purchase['status'] = 'confirmed';

            if (!$this->at_sale->updateOrders($sale, $purchase)) {
                throw new \Exception("Gagal mengonfirmasi pesanan");
            }

            $supplier = $this->at_site->getCompanyByID($sale['biller_id']);

            if ($this->integration->isIntegrated($supplier->cf2)) {
                $response = $this->integration->update_confirmation_integration($supplier->cf2, $this->session->userdata('username'), $sale, $saleItems, $purchase);
                if (!$response) {
                    throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                }

                $dataSale['cf1'] = $response;
                $dataSale['cf2'] = $supplier->cf2;
                $dataSale['id'] = $sales_id;
                if (!$this->at_sale->updateOrders($dataSale, ['id' => $purchase_id])) {
                    throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                }
            }
            if (!$this->audittrail->insertCustomerApprovePrice($this->session->userdata('user_id'), $this->session->userdata('company_id'), $this->session->userdata('supplier_id'), $sale->id, $purchase_id)) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_approve_price");
            }

            $this->load->model('socket_notification_model');
            $data_socket_notification = [
                'company_id'        => $purchase['supplier_id'],
                'transaction_id'    => 'SALE-' . $sale['id'],
                'customer_name'     => $sale['customer'],
                'reference_no'      => $purchase['cf1'],
                'price'             => '',
                'type'              => 'confirm_update_price',
                'to'                => 'pos',
                'note'              => '',
                'created_at'        => date('Y-m-d H:i:s')
            ];

            $this->socket_notification_model->addNotification($data_socket_notification);

            $this->session->set_flashdata('message', 'Pesanan berhasil dikonfirmasi');
            $this->db->trans_commit();
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
        }
        redirect(aksestoko_route('aksestoko/order/view/' . $purchase_id));
    }

    public function getTOP()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->data['TOP'] = $this->payment->getTOP();

        $this->load->view('aksestoko/payment', $this->data);
    }

    public function shipment_group_price($delivery_method = null)
    {
        if ($delivery_method) {
            $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
            $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

            if ($get_customer_warehouse) {
                $warehouse_id         = $get_customer_warehouse->default;
            } else {
                $warehouse_id         = $this->at_site->findCompanyWarehouse($this->session->userdata('supplier_id'))->id;
            }
            // $warehouse_id = $this->at_site->findCompanyWarehouseByPriceGroup($this->session->userdata('price_group_id'), $this->session->userdata('supplier_id'));
            $warehouse = $this->at_site->getWarehouseByID($warehouse_id, $this->session->userdata('supplier_id'));
            if ($warehouse->shipment_price_group_id != null) {
                $shipment_price = $this->at_site->getShipmentProductPriceByShipmentPriceGroupId($warehouse->shipment_price_group_id);
                $res = [];
                foreach ($shipment_price as $shipment) {
                    $res[] = [
                        'product_id' => $shipment->product_id,
                        'price' => $delivery_method == 'pickup' ? $shipment->price_pickup : $shipment->price_delivery
                    ];
                }
                // print_r($res);die;
                echo json_encode($res);
            } else {
                echo json_encode([]);
            }
        } else {
            echo json_encode([]);
        }
    }
}

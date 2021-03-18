<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Deliveries_smig extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('deliveries_smig', $this->Settings->user_language);
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->library('form_validation');
        // $this->insertLogActivities();
        $this->load->model('deliveries_smig_model');
        $this->digital_upload_path    = 'files/';
        $this->digital_file_types     = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size      = '1024';
        $this->data['logo']           = true;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error']            = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Confirmation_Delivery')));
        $meta = array('page_title' => lang('Confirmation_Delivery'), 'bc' => $bc);
        $this->page_construct('deliveries_smig/index', $meta, $this->data);
    }

    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function search_delivery()
    {
        $this->sma->checkPermissions();
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('so_number', lang("so_number"));
            $this->form_validation->set_rules('spj_number', lang("spj_number"), 'required');
            $this->form_validation->set_rules('ekspeditor', lang("ekspeditor"));
            $this->form_validation->set_rules('plant', lang("plant"), 'required');
            $this->form_validation->set_rules('bulan', lang("bulan"), 'required');
            $this->form_validation->set_rules('distrik', lang("distrik"));

            if ($this->form_validation->run() == true) {

                $distrik          = $this->input->post('distrik') ?? '';
                $so_number        = $this->input->post('so_number') ?? '';
                $spj_number       = $this->input->post('spj_number') ?? '';
                $ekspeditor       = $this->input->post('ekspeditor') ?? '';
                $month            = $this->input->post('bulan');
                $split            = explode('/', $month);
                $date_form_param  = $month ? $split[2] . '' . $split[1] . '' . $split[0] : date('Ymd');
                $date_to_param    = $month ? $split[2] . '' . $split[1] . '' . $split[0] : date('Ymd');
                $kode_plant       = $this->input->post('plant') ?? '';
                $response_makasar = $this->deliveries_smig_model->search_data_deliveries_smig_makasar($distrik, $so_number, $spj_number, $ekspeditor, $date_form_param, $date_to_param, $kode_plant);
                if ($response_makasar) {
                    $response[]     = $response_makasar;
                }
                $response_smig    = $this->deliveries_smig_model->search_data_deliveries_smig($distrik, $so_number, $spj_number, $ekspeditor, $date_form_param, $date_to_param, $kode_plant);
                if ($response_smig) {
                    $response[]     = $response_smig;
                }
                $response_padang  = $this->deliveries_smig_model->search_data_deliveries_smig_padang($distrik, $so_number, $spj_number, $ekspeditor, $date_form_param, $date_to_param, $kode_plant);
                if ($response_padang) {
                    $response[]     = $response_padang;
                }
                if (!$response) {
                    throw new Exception(lang("not_found"));
                }
                $count = count($response);
                if ($count > 0) {
                    for ($x = 0; $x < $count; $x++) {
                        $wh  = $this->deliveries_smig_model->setDeliveriesSmigByWarhouse($response[$x]->kodeShipto);
                        $sp  = $this->deliveries_smig_model->setDeliveriesSmigBySupplier($response[$x]->com);
                        $bl  = $this->deliveries_smig_model->setDeliveriesSmigBySupplier((int) $response[$x]->kodeDistributor);
                        if (!$bl) {
                            continue;
                        }
                        $pd  = $this->deliveries_smig_model->getProductByCode($response[$x]->kodeproduk);
                        if (!$pd) {
                            continue;
                        }
                        $tx  = $this->deliveries_smig_model->getTaxRateByID($pd->tax_rate);

                        $pr_discount      = 0;
                        $item_tax_rate    = $tx->rate;
                        $unit_price       = $this->sma->formatDecimal($pd->cost - $pr_discount);
                        $item_net_price   = $pd->cost;
                        $pr_item_discount = $this->sma->formatDecimal($pr_discount * $response[$x]->qtyDO);
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
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $response[$x]->qtyDO, 4);
                        }

                        $product_tax     += $pr_item_tax;
                        $subtotal         = (($item_net_price * $response[$x]->qtyDO) + $pr_item_tax);

                        $products = array(
                            'product_id'        => $pd->id,
                            'product_code'      => $response[$x]->kodeproduk,
                            'product_name'      => $response[$x]->produk,
                            'product_type'      => $pd->type,
                            'net_unit_price'    => $item_net_price,
                            'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                            'quantity'          => $response[$x]->qtyDO,
                            'product_unit_id'   => $pd->unit,
                            'product_unit_code' => $response[$x]->uom,
                            'unit_quantity'     => $response[$x]->qtyDO,
                            'warehouse_id'      => $wh->id,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $pr_tax,
                            'tax'               => $tax,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->sma->formatDecimal($subtotal),
                            'real_unit_price'   => $pd->cost,
                        );

                        $total += $this->sma->formatDecimal(($item_net_price * $response[$x]->qtyDO), 4);
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

                        $data = array(
                            'company_code'      => $response[$x]->com,
                            'company_name'      => $response[$x]->com_name,
                            'no_so'             => $response[$x]->noSO,
                            'line_so'           => $response[$x]->lineSO,
                            'tipe_order'        => $response[$x]->tipeOrder,
                            'tanggal_so'        => $response[$x]->tglSO,
                            'incotrem'          => $response[$x]->incoterm,
                            'no_do'             => $response[$x]->noDO,
                            'tanggal_do'        => $response[$x]->tglDO,
                            'kode_produk'       => $response[$x]->kodeproduk,
                            'nama_produk'       => $response[$x]->produk,
                            'qty_do'            => $response[$x]->qtyDO,
                            'uom'               => $response[$x]->uom,
                            'no_spj'            => $response[$x]->noSPJ,
                            'tanggal_spj'       => $response[$x]->tglSPJ,
                            'jam_spj'           => $response[$x]->jamSPJ,
                            'no_spss'           => $response[$x]->noSPPS,
                            'no_polisi'         => $response[$x]->noPolisi,
                            'nama_sopir'        => $response[$x]->namaSupir,
                            'kode_distributor'  => $response[$x]->kodeDistributor,
                            'distributor'       => $response[$x]->distributor,
                            'kode_shipto'       => $response[$x]->kodeShipto,
                            'nama_shipto'       => $response[$x]->namaShipto,
                            'alamat_shipto'     => $response[$x]->alamatShipto,
                            'kode_distrik'      => $response[$x]->kodeDistrik,
                            'distrik'           => $response[$x]->distrik,
                            'kode_kecamatan'    => $response[$x]->kodeKecamatan,
                            'nama_kecamatan'    => $response[$x]->namaKecamatan,
                            'kode_ekspeditur'   => $response[$x]->kodeEkspeditur,
                            'ekspeditur'        => $response[$x]->ekspeditur,
                            'kode_plant'        => $response[$x]->kodePlant,
                            'nama_plant'        => $response[$x]->plant,
                            'nama_kapal'        => $response[$x]->namaKapal,
                            'status'            => $response[$x]->status,
                            'nomer_po'          => $response[$x]->nomerPO,
                            'no_transaksi'      => $response[$x]->noTransaksi,
                            'no_pp'             => $response[$x]->noPP,
                            'tanggal_pp'        => $response[$x]->tglPP,
                            'tanggal_antri'     => $response[$x]->tglAntri,
                            'jam_antri'         => $response[$x]->jamAntri,
                            'tanggal_masuk'     => $response[$x]->tglMasuk,
                            'jam_masuk'         => $response[$x]->jamMasuk,
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
                        );
                        $no      = $this->deliveries_smig_model->getDeliveriesSmigByDO($response[$x]->noDO);
                        if ($no != NULL) {
                            $data['updated_at'] = date('Y-m-d h:i:s'); //date('Ymd')
                            if (!$this->deliveries_smig_model->updateDeliveriesSmig($no->id, $data, $products)) {
                                throw new Exception(lang("synchron_failed"));
                            }
                        } else {
                            $data['created_at']        = date('Y-m-d h:i:s'); //date('Ymd')
                            $data['status_penerimaan'] = 'delivering';
                            if (!$this->deliveries_smig_model->addDeliveriesSmig($data, $products)) {
                                throw new Exception(lang("synchron_failed"));
                            }
                        }
                    }
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("synchron_success"));
                    redirect("deliveries_smig");
                }
            } elseif ($this->input->post('search_delivery')) {
                throw new Exception(validation_errors());
            } else {
                $this->data['plant']    = $this->deliveries_smig_model->getAllMasterPlant();
                $this->data['distrik']  = $this->deliveries_smig_model->getDistrik($this->session->userdata('biller_id'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'deliveries_smig/search_delivery', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getdeliveries_smig($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link   = anchor('deliveries_smig/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Details_Deliveries'));
        // $live_tracking = anchor('deliveries_smig/tracking/$1','<i class="fa fa-truck"></i>' . lang('Live_Tracking'));
        // $email_link    = anchor('deliveries_smig/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_deliveries'), 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"');
        $pdf_link      = anchor('deliveries_smig/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $pc_link       = anchor('purchases/add/$1/smig', '<i class="fa fa-star"></i> ' . lang('create_purchase'));
        // $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_quote") . "</b>' data-content=\"<p>"
        // . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('deliveries_smig/delete/$1') . "'>"
        // . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        // . lang('Delete_Deliveries') . "</a>";
        $action      = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $pc_link . '</li>
            <li>' . $pdf_link . '</li>
        </ul>
        </div></div>';
        // <li>' . $live_tracking . '</li>
        // <li>' . $email_link . '</li>
        // <li>' . $live_tracking . '</li>
        // <li>' . $delete_link . '</li>
        // $pdf_link = anchor('deliveries_smig/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));

        // $convert_link = anchor('sales/add/$1', '<i class="fa fa-heart"></i> ' . lang('create_sale'));
        // <li>' . $convert_link . '</li>
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("deliveries_smig.id as id, deliveries_smig.tanggal_do, deliveries_smig.no_so, deliveries_smig.no_do, deliveries_smig.no_spj, deliveries_smig.qty_do, deliveries_smig.no_polisi, deliveries_smig.nama_sopir, deliveries_smig.status_penerimaan")
                ->from('deliveries_smig')
                ->join('warehouses', 'deliveries_smig.warehouse_id = warehouses.id', 'left')
                ->where('deliveries_smig.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("deliveries_smig.id as id, deliveries_smig.tanggal_do, deliveries_smig.no_so, deliveries_smig.no_do, deliveries_smig.no_spj, deliveries_smig.qty_do, deliveries_smig.no_polisi, deliveries_smig.nama_sopir, deliveries_smig.status_penerimaan")
                ->from('deliveries_smig');
        }
        if ($this->Admin) {
            $this->datatables->where('deliveries_smig.biller_id', $this->session->userdata('company_id'));
        }
        $this->datatables->group_by('sma_deliveries_smig.id');
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissionsDelivery('deliveries_smig', $id);
        $this->data['error']        = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                        = $this->deliveries_smig_model->getDeliveriesSmigByID($id);
        $this->data['rows']         = $this->deliveries_smig_model->getItemDeliveriesSmig($id);
        $this->data['supplier']     = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller']       = $this->site->getCompanyByID($inv->biller_id);
        $this->data['warehouse']    = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']          = $inv;
        $tracking                   = $this->deliveries_smig_model->live_tracking($inv->no_polisi, $inv->no_do);
        $this->data['url']          = $tracking['url_track'];
        $this->load->view($this->theme . 'deliveries_smig/modal_view', $this->data);
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function view($id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissionsDelivery('deliveries_smig', $id);
        $this->data['error']     = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                     = $this->deliveries_smig_model->getDeliveriesSmigByID($id);
        $this->data['rows']      = $this->deliveries_smig_model->getItemDeliveriesSmig($id);
        $this->data['supplier']  = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller']    = $this->site->getCompanyByID($inv->biller_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']       = $inv;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('deliveries_smig'), 'page' => lang('Confirmation_Delivery')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_details_confirmation_delivery'), 'bc' => $bc);
        $this->page_construct('deliveries_smig/view', $meta, $this->data);
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->sma->transactionPermissionsDelivery('deliveries_smig', $id);
        $this->data['error']        = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                        = $this->deliveries_smig_model->getDeliveriesSmigByID($id);
        $this->data['rows']         = $this->deliveries_smig_model->getItemDeliveriesSmig($id);
        $this->data['supplier']     = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['biller']       = $this->site->getCompanyByID($inv->biller_id);
        $this->data['warehouse']    = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']          = $inv;
        $name = $this->lang->line("confirmation") . "_" . str_replace('/', '_', $inv->no_do) . "_" . date('Ymd') . ".pdf";
        $html = $this->load->view($this->theme . 'deliveries_smig/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'deliveries_smig/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, 'S');
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function combine_pdf($id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($id as $id) {
            $this->data['error']        = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv                        = $this->deliveries_smig_model->getDeliveriesSmigByID($id);
            $this->data['rows']         = $this->deliveries_smig_model->getAllDeliveriesSmig($id);
            $this->data['supplier']     = $this->site->getCompanyByID($inv->customer_id);
            $this->data['biller']       = $this->site->getCompanyByID($inv->biller_id);
            $this->data['warehouse']    = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv']          = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'deliveries_smig/pdf', $this->data, true),
                'footer'  => '',
            );
        }

        $name = lang("delivery_confirmation") . ".pdf";
        $this->sma->generate_pdf($html, $name);
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function deliveries_smig_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('deliveries_smig'))
                        ->SetCellValue('A1', lang('Do_Date'))
                        ->SetCellValue('B1', lang('So_number'))
                        ->SetCellValue('C1', lang('So_Date'))
                        ->SetCellValue('D1', lang('PP_Number'))
                        ->SetCellValue('E1', lang('PP_Date'))
                        ->SetCellValue('F1', lang('Incotrem'))
                        ->SetCellValue('G1', lang('Do_number'))
                        ->SetCellValue('H1', lang('Product_Code'))
                        ->SetCellValue('I1', lang('Product_Name'))
                        ->SetCellValue('J1', lang('Quantity'))
                        ->SetCellValue('K1', lang('UOM'))
                        ->SetCellValue('L1', lang('Transaction_Number'))
                        ->SetCellValue('M1', lang('Spj_Number'))
                        ->SetCellValue('N1', lang('Spj_Date'))
                        ->SetCellValue('O1', lang('Police_Number'))
                        ->SetCellValue('P1', lang('Drivers_Name'))
                        ->SetCellValue('Q1', lang('Expeditor'))
                        ->SetCellValue('R1', lang('Plant_Name'))
                        ->SetCellValue('S1', lang('Biller'))
                        ->SetCellValue('T1', lang('Supplier'))
                        ->SetCellValue('U1', lang('total'))
                        ->SetCellValue('V1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->deliveries_smig_model->getDeliveriesSmigByID($id);
                        $sheet->SetCellValue('A' . $row, $this->sma->hrsd($qu->tanggal_do))
                            ->SetCellValue('B' . $row, $qu->no_so)
                            ->SetCellValue('C' . $row, $this->sma->hrsd($qu->tanggal_so))
                            ->SetCellValue('D' . $row, $qu->no_pp)
                            ->SetCellValue('E' . $row, $this->sma->hrsd($qu->tanggal_pp))
                            ->SetCellValue('F' . $row, $qu->incotrem)
                            ->SetCellValue('G' . $row, $qu->no_do)
                            ->SetCellValue('H' . $row, $qu->kode_produk)
                            ->SetCellValue('I' . $row, $qu->nama_produk)
                            ->SetCellValue('J' . $row, $qu->qty_do)
                            ->SetCellValue('K' . $row, $qu->uom)
                            ->SetCellValue('L' . $row, $qu->no_transaksi)
                            ->SetCellValue('M' . $row, $qu->no_spj)
                            ->SetCellValue('N' . $row, $qu->tanggal_spj)
                            ->SetCellValue('O' . $row, $qu->no_polisi)
                            ->SetCellValue('P' . $row, $qu->nama_sopir)
                            ->SetCellValue('Q' . $row, $qu->ekspeditur)
                            ->SetCellValue('R' . $row, $qu->nama_plant)
                            ->SetCellValue('S' . $row, $qu->distributor)
                            ->SetCellValue('T' . $row, $qu->company_name)
                            ->SetCellValue('U' . $row, $qu->total)
                            ->SetCellValue('V' . $row, $qu->status_penerimaan);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'delivery_confirmation_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                        $sheet->getDefaultStyle()->applyFromArray($styleArray);
                        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = IOFactory::createWriter($sheet, 'Pdf');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                        header('Cache-Control: max-age=0');

                        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
                        ob_end_clean();
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_deliveries_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
}

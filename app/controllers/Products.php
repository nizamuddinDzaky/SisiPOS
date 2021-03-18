<?php defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Laminas\Barcode\Barcode;

class Products extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        // $this->insertLogActivities();
        $this->lang->load('products', $this->Settings->user_language);
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('products_model');
        $this->load->model('settings_model');
        $this->load->model('companies_model');
        $this->load->model('Official_model');
        $this->load->model('authorized_model');
        $this->load->model('integration_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
    }

    public function index($warehouse_id = null)
    {

        // echo url_image_thumb("https://i.ibb.co/h8xMm2x/hosting.png");die;
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $link_type = ['mb_product','mb_edit_product','mb_import_csv_product','mb_export_excel_product','mb_export_pdf_product'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
        $meta = array('page_title' => lang('products'), 'bc' => $bc);
        $this->page_construct('products/index', $meta, $this->data);
    }

    public function getProducts($cons, $warehouse_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if (!$this->Owner && !$this->Admin && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $detail_link = anchor('products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . site_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . "</a> -->";
        $single_barcode = anchor('products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . site_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . site_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . site_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"  data-backdrop="static"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . site_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';

        $this->load->library('datatables');
        if ($cons=='all') {
            $this->datatables->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, ".($warehouse_id?"wp.avg_cost":"''")." as avg_cost, price as price, COALESCE(cons.qty, 0)+COALESCE(".($warehouse_id?"wp":"{$this->db->dbprefix('products')}").".quantity,0) as quantity, {$this->db->dbprefix('units')}.code as unit, ".($warehouse_id?"wp.rack":"''")." as rack, alert_quantity, COALESCE(".($warehouse_id ? "wp.quantity_booking" : 0).", 0) as quantity_booking", false);
        } elseif ($cons=='yes') {
            $this->datatables->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, ".($warehouse_id?"wp.avg_cost":"''")." as avg_cost, price as price, COALESCE(cons.qty, 0) as quantity, {$this->db->dbprefix('units')}.code as unit, ".($warehouse_id?"wp.rack":"''")." as rack, alert_quantity, COALESCE(".($warehouse_id ? "wp.quantity_booking" : 0).", 0) as quantity_booking", false);
        } else {
            $this->datatables->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.thumb_image as thumb_image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost,  ".($warehouse_id?"wp.avg_cost":"''")." as avg_cost, price as price, COALESCE(".($warehouse_id?"wp":"{$this->db->dbprefix('products')}").".quantity, 0) as quantity, {$this->db->dbprefix('units')}.code as unit, ".($warehouse_id?"wp.rack":"''")." as rack, alert_quantity, COALESCE(".($warehouse_id?"wp":"{$this->db->dbprefix('products')}").".quantity_booking, 0) as quantity_booking, {$this->db->dbprefix('products')}.image as image", false);
        }

        if ($warehouse_id) {
            $table="(SELECT product_id as pid, product_name as pname, quantity as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} WHERE warehouse_id=".$warehouse_id.") as cons";

//            $this->datatables->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, COALESCE(wp.quantity, 0) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", FALSE);
            $this->datatables->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join("( SELECT product_id, quantity, quantity_booking, avg_cost, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                ->where('wp.warehouse_id', $warehouse_id);
//                ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->where('products.is_deleted', null);
        //->group_by("products.id");
        } else {
            $table="(SELECT product_id as pid, product_name as pname, sum(quantity) as qty, company_id, warehouse_id as wid, is_deleted FROM {$this->db->dbprefix('consignment_products')} GROUP BY product_id, company_id) as cons";

//            $this->datatables->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, COALESCE({$this->db->dbprefix('products')}.quantity, 0) as quantity, {$this->db->dbprefix('units')}.code as unit, '' as rack, alert_quantity", FALSE);
            $this->datatables->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.is_deleted', null);

            if ($this->Admin) {
                $this->datatables->where('products.company_id', $this->session->userdata('company_id'));
            }
            $this->datatables->group_by("products.id");
            // ->join('warehouses_products ware_prod', 'ware_prod.product_id=products.id', 'left')
                // ->where('ware_prod.company_id !=',$this->session->userdata('company_id'));
        }
        if ($cons=='yes') {
            $this->datatables->join($table, 'products.id=cons.pid', 'right')
                ->where('cons.is_deleted', null);
        } elseif ($cons=='no') {
            $this->datatables->where("products.type!='consignment'");
        } else {
            $this->datatables->join($table, 'products.id=cons.pid', 'left')
                ->where('cons.is_deleted', null);
        }

        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column("cost");
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column("price");
            }
        }

        if (!$this->Owner) {
            $this->datatables->where("{$this->db->dbprefix('products')}.company_id", $this->session->userdata('company_id'));
        }
        $this->datatables->edit_column("thumb_image", "$1___$2", 'url_image_thumb(thumb_image), url_image_thumb(image, 0)');
        $this->datatables->add_column("Actions", $action, "productid, image, code, name");
        echo $this->datatables->generate();
    }

    public function set_rack($product_id = null, $warehouse_id = null)
    {
        $this->sma->checkPermissions('edit', true);

        $this->form_validation->set_rules('rack', lang("rack_location"), 'trim|required');
        $this->db->trans_begin();
        try {
            if ($this->form_validation->run() == true) {
                $data = array('rack' => $this->input->post('rack'),
                    'product_id' => $product_id,
                    'warehouse_id' => $warehouse_id,
                );
            } elseif ($this->input->post('set_rack')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("products");
            }

            if ($this->form_validation->run() == true && $this->products_model->setRack($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("rack_set"));
                redirect("products/" . $warehouse_id);
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['warehouse_id'] = $warehouse_id;
                $this->data['product'] = $this->site->getProductByID($product_id);
                $wh_pr = $this->products_model->getProductQuantity($product_id, $warehouse_id);
                $this->data['rack'] = $wh_pr['rack'];
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'products/set_rack', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function product_barcode($product_code = null, $bcs = 'code128', $height = 60)
    {
        // if ($this->Settings->barcode_img) {
        return "<img src='" . site_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height) . "' alt='{$product_code}' class='bcimg' />";
        // } else {
        //     return $this->gen_barcode($product_code, $bcs, $height);
        // }
    }

    public function barcode($product_code = null, $bcs = 'code128', $height = 60)
    {
        return site_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height);
    }

    public function gen_barcode($product_code = null, $bcs = 'code128', $height = 60, $text = 1)
    {
        $drawText = ($text != 1) ? false : true;
        // $this->load->library('zend');
        // $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $product_code, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1.0);
        if ($this->Settings->barcode_img) {
            $rendererOptions = array('imageType' => 'jpg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
            return $imageResource;
        } else {
            $rendererOptions = array('renderer' => 'svg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            header("Content-Type: image/svg+xml");
            echo $imageResource;
        }
    }

    public function print_barcodes($product_id = null)
    {
        $this->sma->checkPermissions('barcode', true);

        $this->form_validation->set_rules('style', lang("style"), 'required');

        if ($this->form_validation->run() == true) {
            $style = $this->input->post('style');
            $bci_size = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                redirect("products/print_barcodes");
            }
            for ($m = 0; $m < $s; $m++) {
                $pid = $_POST['product'][$m];
                $quantity = $_POST['quantity'][$m];
                $product = $this->products_model->getProductWithCategory($pid);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                $pr_unit=$this->site->getUnitByID($product->unit);
                $discount=$this->settings_model->getMultipleDiscountByPID($pid);
                $bonus=$this->settings_model->getBonusByPID($pid);
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_'.$product->id.'_'.$option->id)) {
                            $barcodes[] = array(
                                'site' => $this->input->post('site_name') ? $this->Settings->site_name : false,
                                'name' => $this->input->post('product_name') ? $product->name.' - '.$option->name : false,
                                'image' => $this->input->post('product_image') ? $product->image : false,
                                'barcode' => $this->sma->save_barcode($product->code. $this->Settings->barcode_separator . $option->id, $product->barcode_symbology, $bci_size, false), 
                                // $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price' => $this->input->post('price') ?  $this->sma->formatMoney($option->price != 0 ? $option->price : $product->price) : false,
                                'unit' => $this->input->post('unit') ? $pr_unit->name : false,
                                'category' => $this->input->post('category') ? $product->category : false,
                                'currencies' => $this->input->post('currencies'),
                                'variants' => $this->input->post('variants') ? $variants : false,
                                'quantity' => $quantity,
                                'discount' => $this->input->post('discount') ? $discount : false,
                                'bonus' => $this->input->post('bonus') ? $bonus : false,
                            );
                        }
                    }
                } else {
                    $barcodes[] = array(
                        'site' => $this->input->post('site_name') ? $this->Settings->site_name : false,
                        'name' => $this->input->post('product_name') ? $product->name : false,
                        'image' => $this->input->post('product_image') ? $product->image : false,
                        'barcode' => $this->sma->save_barcode($product->code, $product->barcode_symbology, $bci_size, false),
                        // $this->product_barcode($product->code, , ),
                        'price' => $this->input->post('price') ?  $this->sma->formatMoney($product->price) : false,
                        'unit' => $this->input->post('unit') ? $pr_unit->name : false,
                        'category' => $this->input->post('category') ? $product->category : false,
                        'currencies' => $this->input->post('currencies'),
                        'variants' => false,
                        'quantity' => $quantity,
                        'discount' => $this->input->post('discount') ? $discount : false,
                        'bonus' => $this->input->post('bonus') ? $bonus : false,
                    );
                }
            }
            $this->data['barcodes'] = $barcodes;
            $this->data['currencies'] = $currencies;
            $this->data['style'] = $style;
            $this->data['items'] = false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
            $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        } else {
            if ($this->input->get('purchase') || $this->input->get('transfer')) {
                if ($this->input->get('purchase')) {
                    $purchase_id = $this->input->get('purchase', true);
                    $items = $this->products_model->getPurchaseItems($purchase_id);
                } elseif ($this->input->get('transfer')) {
                    $transfer_id = $this->input->get('transfer', true);
                    $items = $this->products_model->getTransferItems($transfer_id);
                }
                if ($items) {
                    foreach ($items as $item) {
                        if ($row = $this->products_model->getProductByID($item->product_id)) {
                            $selected_variants = false;
                            if ($variants = $this->products_model->getProductOptions($row->id)) {
                                foreach ($variants as $variant) {
                                    $selected_variants[$variant->id] = isset($pr[$row->id]['selected_variants'][$variant->id]) && !empty($pr[$row->id]['selected_variants'][$variant->id]) ? 1 : ($variant->id == $item->option_id ? 1 : 0);
                                }
                            }
                            $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $item->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                        }
                    }
                    $this->data['message'] = lang('products_added_to_list');
                }
            }

            if ($product_id) {
                if ($row = $this->site->getProductByID($product_id)) {
                    $selected_variants = false;
                    if ($variants = $this->products_model->getProductOptions($row->id)) {
                        foreach ($variants as $variant) {
                            $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                        }
                    }
                    $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);

                    $this->data['message'] = lang('product_added_to_list');
                }
            }

            if ($this->input->get('category')) {
                if ($products = $this->products_model->getCategoryProducts($this->input->get('category'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            if ($this->input->get('subcategory')) {
                if ($products = $this->products_model->getSubCategoryProducts($this->input->get('subcategory'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
            $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        }
    }


    /* ------------------------------------------------------- */

    public function add($id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions();
        $this->load->helper('security');
        try {
            $warehouses = $this->Owner ? $this->site->getAllWarehouses(null, ["company_id" => 1]) : $this->site->getAllWarehouses();
            if ($this->input->post('type') == 'standard') {
                $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
                $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
            }
            if ($this->input->post('type') == 'consignment') {
                $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
            }
            if ($this->input->post('barcode_symbology') == 'ean13') {
                $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
            }

            $this->form_validation->set_rules('code', lang("product_code"), 'callback_check_company['.$this->session->userdata('company_id').']|alpha_dash|trim');

            $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
            $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
            $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
            if ($this->input->post('type') == 'standard') {
                $this->form_validation->set_rules('supplier', lang('supplier'), 'required');
            }
            if ($this->form_validation->run() == true) {
                $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : null;
                $data = array(
                    'code' => $this->input->post('code'),
                    'barcode_symbology' => $this->input->post('barcode_symbology'),
                    'name' => $this->input->post('name'),
                    'type' => $this->input->post('type'),
                    'brand' => $this->input->post('brand'),
                    'category_id' => $this->input->post('category'),
                    'company_id' => $this->session->userdata('company_id'),
                    'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                    'cost' => $this->sma->formatDecimal($this->input->post('cost')),
                    'price' => $this->sma->formatDecimal($this->input->post('price')),
                    'credit_price' => $this->sma->formatDecimal($this->input->post('credit_price')),
                    'unit' => $this->input->post('unit'),
                    'sale_unit' => $this->input->post('default_sale_unit'),
                    'purchase_unit' => $this->input->post('default_purchase_unit'),
                    'tax_rate' => $this->input->post('tax_rate'),
                    'tax_method' => $this->input->post('tax_method'),
                    'alert_quantity' => $this->input->post('alert_quantity'),
                    'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                    'details' => $this->input->post('details'),
                    'product_details' => $this->input->post('product_details'),
                    'supplier1' => $this->input->post('supplier'),
                    'supplier1price' => $this->sma->formatDecimal($this->input->post('supplier_price')),
                    'supplier2' => $this->input->post('supplier_2'),
                    'supplier2price' => $this->sma->formatDecimal($this->input->post('supplier_2_price')),
                    'supplier3' => $this->input->post('supplier_3'),
                    'supplier3price' => $this->sma->formatDecimal($this->input->post('supplier_3_price')),
                    'supplier4' => $this->input->post('supplier_4'),
                    'supplier4price' => $this->sma->formatDecimal($this->input->post('supplier_4_price')),
                    'supplier5' => $this->input->post('supplier_5'),
                    'supplier5price' => $this->sma->formatDecimal($this->input->post('supplier_5_price')),
                    'cf1' => $this->input->post('cf1'),
                    'cf2' => $this->input->post('cf2'),
                    'cf3' => $this->input->post('cf3'),
                    'cf4' => $this->input->post('cf4'),
                    'cf5' => $this->input->post('cf5'),
                    'cf6' => $this->input->post('cf6'),
                    'promotion' => $this->input->post('promotion'),
                    'promo_price' => $this->sma->formatDecimal($this->input->post('promo_price')),
                    'start_date' => $this->input->post('start_date') ? $this->sma->fsd($this->input->post('start_date')) : null,
                    'end_date' => $this->input->post('end_date') ? $this->sma->fsd($this->input->post('end_date')) : null,
                    'supplier1_part_no' => $this->input->post('supplier_part_no'),
                    'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                    'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                    'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                    'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                    'public' => $this->input->post('public'),
                    'is_retail' => $this->input->post('is_retail') ? 1 : 0,
                    'price_public' => $this->input->post('price_online'),
                    'weight' => $this->input->post('weight'),
                    'e_minqty' => $this->input->post('minqty'),
                );
                // print_r($data);die;
                //            foreach ($warehouses as $warehouse) {
                //                $consignment[] = array(
                //                    'quantity' => $this->input->post('qty_csgm_' . $warehouse->id),
                //                    'warehouse_id' => $warehouse->id,
                //                    'created_by' => $this->session->userdata('user_id'),
                //                    'company_id' => $this->session->userdata('company_id'),
                //                    'created_on' => date('Y-m-d')
                //                );
                //            }

                $this->load->library('upload');
                if ($this->input->post('type') == 'standard' || $this->input->post('type') == 'consignment') {
                    $wh_total_quantity = 0;
                    $pv_total_quantity = 0;
                    if ($this->input->post('type') == 'standard') {
                        for ($s = 2; $s > 5; $s++) {
                            $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                            $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                        }
                    }
                    foreach ($warehouses as $warehouse) {
                        //if ($this->input->post('wh_qty_'.$warehouse->id)) {
                        if (!$this->owner) {
                            $warehouse_qty[] = array(
                                    'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                                    'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                                    'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null
                                );
                            $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                        }
                        //}
                    }

                    if ($this->input->post('attributes')) {
                        $a = sizeof($_POST['attr_name']);
                        for ($r = 0; $r <= $a; $r++) {
                            if (isset($_POST['attr_name'][$r])) {
                                $product_attributes[] = array(
                                    'name' => $_POST['attr_name'][$r],
                                    'warehouse_id' => $_POST['attr_warehouse'][$r],
                                    'quantity' => $_POST['attr_quantity'][$r],
                                    'price' => $_POST['attr_price'][$r],
                                );
                                $pv_total_quantity += $_POST['attr_quantity'][$r];
                            }
                        }
                    } else {
                        $product_attributes = null;
                    }

                    if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                        $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                        $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                    }
                } else {
                    $warehouse_qty = null;
                    $product_attributes = null;
                }

                if ($this->input->post('type') == 'service') {
                    $data['track_quantity'] = 0;
                } elseif ($this->input->post('type') == 'combo') {
                    $total_price = 0;
                    $c = sizeof($_POST['combo_item_code']) - 1;
                    for ($r = 0; $r <= $c; $r++) {
                        if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                            $items[] = array(
                                'item_code' => $_POST['combo_item_code'][$r],
                                'quantity' => $_POST['combo_item_quantity'][$r],
                                'unit_price' => $_POST['combo_item_price'][$r],
                            );
                        }
                        $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                    }
                    if ($this->sma->formatDecimal($total_price) != $this->sma->formatDecimal($this->input->post('price'))) {
                        $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                        $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                    }
                    $data['track_quantity'] = 0;
                } elseif ($this->input->post('type') == 'digital') {
                    if ($_FILES['digital_file']['size'] > 0) {
                        /*$config['upload_path'] = $this->digital_upload_path;
                        $config['allowed_types'] = $this->digital_file_types;
                        $config['max_size'] = $this->allowed_file_size;
                        $config['overwrite'] = false;
                        $config['encrypt_name'] = true;
                        $config['max_filename'] = 25;
                        $this->upload->initialize($config);
                        if (!$this->upload->do_upload('digital_file')) {
                            $error = $this->upload->display_errors();
                            throw new \Exception($error);
                            // $this->session->set_flashdata('error', $error);
                            // redirect("products/add");
                        }
                        $file = $this->upload->file_name;*/
                        $uploadedImg = $this->integration_model->upload_files($_FILES['digital_file']);
                        $file = $uploadedImg->url;
                        $data['file'] = $file;
                    } else {
                        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'required');
                    }
                    $config = null;
                    $data['track_quantity'] = 0;
                }
                if (!isset($items)) {
                    $items = null;
                }
                if ($_FILES['product_image']['size'] > 0) {
                    /*$check = getimagesize($_FILES['product_image']["tmp_name"]);
                
                    if (!$check) {
                        throw new \Exception("File tidak valid");
                    }
                    if ($_FILES['product_image']["size"] > 16000000) { //15mb
                        throw new \Exception("Ukuran File terlalu besar");
                    }
                    
                    $image = base64_encode(file_get_contents($_FILES['product_image']["tmp_name"]));
                    $uploadedImg = json_decode($this->site->uploadImage($image));*/
                    $uploadedImg = $this->integration_model->upload_files($_FILES['product_image']);
                    if ($uploadedImg) {
                        $data['image'] = $uploadedImg->url;
                        $data['thumb_image'] = $uploadedImg->url;
                    } else {
                        $this->session->set_flashdata('error', "Gagal mengunggah gambar");
                    }
                    // $config['upload_path'] = $this->upload_path;
                    // $config['allowed_types'] = $this->image_types;
                    // $config['max_size'] = $this->allowed_file_size;
                    // $config['max_width'] = $this->Settings->iwidth;
                    // $config['max_height'] = $this->Settings->iheight;
                    // $config['overwrite'] = FALSE;
                    // $config['max_filename'] = 25;
                    // $config['encrypt_name'] = TRUE;
                    // $this->upload->initialize($config);
                    // if (!$this->upload->do_upload('product_image')) {
                    //     $error = $this->upload->display_errors();
                    //     $this->session->set_flashdata('error', $error);
                    //     redirect("products/add");
                    // }
                    // $photo = $this->upload->file_name;
                    // $data['image'] = $photo;
                    // $this->load->library('image_lib');
                    // $config['image_library'] = 'gd2';
                    // $config['source_image'] = $this->upload_path . $photo;
                    // $config['new_image'] = $this->thumbs_path . $photo;
                    // $config['maintain_ratio'] = TRUE;
                    // $config['width'] = $this->Settings->twidth;
                    // $config['height'] = $this->Settings->theight;
                    // $this->image_lib->clear();
                    // $this->image_lib->initialize($config);
                    // if (!$this->image_lib->resize()) {
                    //     echo $this->image_lib->display_errors();
                    // }
                    // if ($this->Settings->watermark) {
                    //     $this->image_lib->clear();
                    //     $wm['source_image'] = $this->upload_path . $photo;
                    //     $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    //     $wm['wm_type'] = 'text';
                    //     $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    //     $wm['quality'] = '100';
                    //     $wm['wm_font_size'] = '16';
                    //     $wm['wm_font_color'] = '999999';
                    //     $wm['wm_shadow_color'] = 'CCCCCC';
                    //     $wm['wm_vrt_alignment'] = 'top';
                    //     $wm['wm_hor_alignment'] = 'left';
                    //     $wm['wm_padding'] = '10';
                    //     $this->image_lib->initialize($wm);
                    //     $this->image_lib->watermark();
                    // }
                    // $this->image_lib->clear();
                    // $config = NULL;
                }

                if ($_FILES['userfile']['name'][0] != "") {
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $files = $_FILES;
                    $cpt = count($_FILES['userfile']['name']);
                    for ($i = 0; $i < $cpt; $i++) {
                        $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                        $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                        $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                        $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                        $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                        /*$this->upload->initialize($config);

                        if (!$this->upload->do_upload()) {
                            $error = $this->upload->display_errors();
                            throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                            // redirect("products/add");
                        } else {
                            $pho = $this->upload->file_name;

                            $photos[] = $pho;

                            $this->load->library('image_lib');
                            $config['image_library'] = 'gd2';
                            $config['source_image'] = $this->upload_path . $pho;
                            $config['new_image'] = $this->thumbs_path . $pho;
                            $config['maintain_ratio'] = true;
                            $config['width'] = $this->Settings->twidth;
                            $config['height'] = $this->Settings->theight;

                            $this->image_lib->initialize($config);

                            if (!$this->image_lib->resize()) {
                                echo $this->image_lib->display_errors();
                            }

                            if ($this->Settings->watermark) {
                                $this->image_lib->clear();
                                $wm['source_image'] = $this->upload_path . $pho;
                                $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                                $wm['wm_type'] = 'text';
                                $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                                $wm['quality'] = '100';
                                $wm['wm_font_size'] = '10';
                                $wm['wm_font_color'] = '999999';
                                $wm['wm_shadow_color'] = 'None';
                                $wm['wm_vrt_alignment'] = 'top';
                                $wm['wm_hor_alignment'] = 'left';
                                $wm['wm_padding'] = '0';
                                $this->image_lib->initialize($wm);
                                $this->image_lib->watermark();
                            }

                            $this->image_lib->clear();
                        }*/
                        $uploadedImg = $this->integration_model->upload_files($_FILES['userfile']);
                        $photos[] = $uploadedImg->url;
                    }
                    $config = null;
                } else {
                    $photos = null;
                }
                $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
                // $this->sma->print_arrays($data, $warehouse_qty, $product_attributes);
            }
            //        echo json_encode($consignment);die();
            // var_dump($this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos));die;
            if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("product_added"));
                redirect('products');
            } else {
                
                //nge cek apakah jumlah Products telah limit
                $isLimited = $this->authorized_model->isProductLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_master"));
                    $message = str_replace("yyy", lang("products"), $message);

                    $this->session->set_flashdata('error', $message);
                    redirect("products");
                }
                // akhir cek
                
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                //            $this->data['categories'] = $this->site->getAllCategories();
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                //            $this->data['brands'] = $this->site->getAllBrands();
                //            $this->data['base_units'] = $this->site->getAllBaseUnits();
                $this->data['warehouses'] = $warehouses;
                $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : null;
                $this->data['product'] = $id ? $this->products_model->getProductByID($id) : null;
                $this->data['variants'] = $this->products_model->getAllVariants();
                $this->data['combo_items'] = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : null;
                $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : null;

                $link_type = ['mb_add_product'];
                $this->load->model('db_model');
                $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
                foreach ($get_link as $val) {
                    $this->data[$val->type] = $val->uri;
                }

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_product')));
                $meta = array('page_title' => lang('add_product'), 'bc' => $bc);
                $this->page_construct('products/add', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        // $rows = $this->products_model->getProductNames($term);
        $rows = $this->products_model->getDataProducts($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => round($row->price), 'qty' => 1,'barcode_symbology' => $row->barcode_symbology,'brand' => $row->brand, 'category' => $row->category_id,'subcategory'=>$row->subcategory_id,'unit'=> $row->unit,'sale_unit'=>$row->sale_unit,'purchase_unit'=>$row->purchase_unit);
//                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'unit' => $row->unit, 'category' => $row->category, 'type'=>$row->type, 'barcode'=>$row->barcode, 'brand'=>$row->brand, 'brand_code'=>$row->brand_code, 'category_id' => $row->category_id, 'unit_id' => $row->unit_id);
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function get_suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductsForPrinting($term);
        if ($rows) {
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'variants' => $variants);
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function addByAjax()
    {
        if (!$this->mPermissions('add')) {
            exit(json_encode(array('msg' => lang('access_denied'))));
        }
        if ($this->input->get('token') && $this->input->get('token') == $this->session->userdata('user_csrf') && $this->input->is_ajax_request()) {
            $product = $this->input->get('product');
            if (!isset($product['code']) || empty($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_is_required'))));
            }
            if (!isset($product['name']) || empty($product['name'])) {
                exit(json_encode(array('msg' => lang('product_name_is_required'))));
            }
            if (!isset($product['category_id']) || empty($product['category_id'])) {
                exit(json_encode(array('msg' => lang('product_category_is_required'))));
            }
            if (!isset($product['unit']) || empty($product['unit'])) {
                exit(json_encode(array('msg' => lang('product_unit_is_required'))));
            }
            if (!isset($product['price']) || empty($product['price'])) {
                exit(json_encode(array('msg' => lang('product_price_is_required'))));
            }
            if (!isset($product['cost']) || empty($product['cost'])) {
                exit(json_encode(array('msg' => lang('product_cost_is_required'))));
            }
            if ($this->products_model->getProductByCode($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_already_exist'))));
            }
            if ($row = $this->products_model->addAjaxProduct($product)) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'qty' => 1, 'cost' => $row->cost, 'name' => $row->name, 'tax_method' => $row->tax_method, 'tax_rate' => $tax_rate, 'discount' => '0');
                $this->sma->send_json(array('msg' => 'success', 'result' => $pr));
            } else {
                exit(json_encode(array('msg' => lang('failed_to_add_product'))));
            }
        } else {
            json_encode(array('msg' => 'Invalid token'));
        }
    }


    /* -------------------------------------------------------- */

    public function edit($id = null)
    {
        $this->db->trans_begin();
        $this->sma->checkPermissions();
        $this->load->helper('security');
        try {
            if ($this->input->post('id')) {
                $id = $this->input->post('id');
            }
            //        $csg=$this->products_model->getConsignmentByPID($id);
            $warehouses = $this->Owner ? $this->site->getAllWarehouses(null, ["company_id" => 1]) : $this->site->getAllWarehouses();
            $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
            $product = $this->site->getProductByID($id);
            if (!$id || !$product) {
                $this->session->set_flashdata('error', lang('prduct_not_found'));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if ($this->input->post('type') == 'standard') {
                $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
                $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
            }
            if ($this->input->post('type') == 'consignment') {
                $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
            }
            $this->form_validation->set_rules('code', lang("product_code"), 'alpha_dash');
            if ($this->input->post('code') !== $product->code) {
                $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
            }
            if ($this->input->post('barcode_symbology') == 'ean13') {
                $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
            }
            $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
            $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
            $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');

            if ($this->form_validation->run('products/add') == true) {
                $data = array(
                    'code' => $this->input->post('code'),
                    'barcode_symbology' => $this->input->post('barcode_symbology'),
                    'name' => $this->input->post('name'),
                    'type' => $this->input->post('type'),
                    'brand' => $this->input->post('brand'),
                    'category_id' => $this->input->post('category'),
                    'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                    'cost' => $this->sma->formatDecimal($this->input->post('cost')),
                    'price' => $this->sma->formatDecimal($this->input->post('price')),
                    'credit_price' => $this->sma->formatDecimal($this->input->post('credit_price')),
                    'unit' => $this->input->post('unit'),
                    'sale_unit' => $this->input->post('default_sale_unit'),
                    'purchase_unit' => $this->input->post('default_purchase_unit'),
                    'tax_rate' => $this->input->post('tax_rate'),
                    'tax_method' => $this->input->post('tax_method'),
                    'alert_quantity' => $this->input->post('alert_quantity'),
                    'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                    'details' => $this->input->post('details'),
                    'product_details' => $this->input->post('product_details'),
                    'supplier1' => $this->input->post('supplier'),
                    'supplier1price' => $this->sma->formatDecimal($this->input->post('supplier_price')),
                    'supplier2' => $this->input->post('supplier_2'),
                    'supplier2price' => $this->sma->formatDecimal($this->input->post('supplier_2_price')),
                    'supplier3' => $this->input->post('supplier_3'),
                    'supplier3price' => $this->sma->formatDecimal($this->input->post('supplier_3_price')),
                    'supplier4' => $this->input->post('supplier_4'),
                    'supplier4price' => $this->sma->formatDecimal($this->input->post('supplier_4_price')),
                    'supplier5' => $this->input->post('supplier_5'),
                    'supplier5price' => $this->sma->formatDecimal($this->input->post('supplier_5_price')),
                    'cf1' => $this->input->post('cf1'),
                    'cf2' => $this->input->post('cf2'),
                    'cf3' => $this->input->post('cf3'),
                    'cf4' => $this->input->post('cf4'),
                    'cf5' => $this->input->post('cf5'),
                    'cf6' => $this->input->post('cf6'),
                    'promotion' => $this->input->post('promotion'),
                    'promo_price' => $this->sma->formatDecimal($this->input->post('promo_price')),
                    'start_date' => $this->input->post('start_date') ? $this->sma->fsd($this->input->post('start_date')) : null,
                    'end_date' => $this->input->post('end_date') ? $this->sma->fsd($this->input->post('end_date')) : null,
                    'supplier1_part_no' => $this->input->post('supplier_part_no'),
                    'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                    'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                    'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                    'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                    'public' => $this->input->post('public'),
                    'is_retail' => $this->input->post('is_retail') ? 1 : 0,
                    'price_public' => $this->input->post('price_online'),
                    'weight' => $this->input->post('weight'),
                    'e_minqty' => $this->input->post('minqty'),
                );

                //            if($this->input->post('n_qty_csgm[]')){
                //                foreach($csg as $c){
                //                    $consignment[]=array(
                //                        'c_id' => $c->id,
                //                        'quantity' => $this->input->post('n_qty_csgm['.$c->id.']'),
                //                    );
                //                }
                //            }else{
                //                foreach ($warehouses as $warehouse) {
                //                    $consignment[] = array(
                //                        'quantity' => $this->input->post('qty_csgm_' . $warehouse->id),
                //                        'warehouse_id' => $warehouse->id,
                //                        'created_by' => $this->session->userdata('user_id'),
                //                        'company_id' => $this->session->userdata('company_id'),
                //                        'created_on' => date('Y-m-d')
                //                    );
                //                }
                //            }
                
                $this->load->library('upload');
                if ($this->input->post('type') == 'standard' || $this->input->post('type') == 'consignment') {
                    if ($product_variants = $this->products_model->getProductOptions($id)) {
                        foreach ($product_variants as $pv) {
                            $update_variants[] = array(
                                'id' => $this->input->post('variant_id_'.$pv->id),
                                'name' => $this->input->post('variant_name_'.$pv->id),
                                'cost' => $this->input->post('variant_cost_'.$pv->id),
                                'price' => $this->input->post('variant_price_'.$pv->id),
                            );
                        }
                    } else {
                        $update_variants = null;
                    }
                    if ($this->input->post('type') == 'standard') {
                        for ($s = 2; $s > 5; $s++) {
                            $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                            $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                        }
                    }
                    foreach ($warehouses as $warehouse) {
                        $warehouse_qty[] = array(
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null
                        );
                    }

                    if ($this->input->post('attributes')) {
                        $a = sizeof($_POST['attr_name']);
                        for ($r = 0; $r <= $a; $r++) {
                            if (isset($_POST['attr_name'][$r])) {
                                if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                    $this->form_validation->set_message('required', lang("product_already_has_variant").' ('.$_POST['attr_name'][$r].')');
                                    $this->form_validation->set_rules('new_product_variant', lang("new_product_variant"), 'required');
                                } else {
                                    $product_attributes[] = array(
                                        'name' => $_POST['attr_name'][$r],
                                        'warehouse_id' => $_POST['attr_warehouse'][$r],
                                        'quantity' => $_POST['attr_quantity'][$r],
                                        'price' => $_POST['attr_price'][$r],
                                    );
                                }
                            }
                        }
                    } else {
                        $product_attributes = null;
                    }
                } else {
                    $warehouse_qty = null;
                    $product_attributes = null;
                }

                if ($this->input->post('type') == 'service') {
                    $data['track_quantity'] = 0;
                } elseif ($this->input->post('type') == 'combo') {
                    $total_price = 0;
                    $c = sizeof($_POST['combo_item_code']) - 1;
                    for ($r = 0; $r <= $c; $r++) {
                        if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                            $items[] = array(
                                'item_code' => $_POST['combo_item_code'][$r],
                                'quantity' => $_POST['combo_item_quantity'][$r],
                                'unit_price' => $_POST['combo_item_price'][$r],
                            );
                        }
                        $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                    }
                    if ($this->sma->formatDecimal($total_price) != $this->sma->formatDecimal($this->input->post('price'))) {
                        $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                        $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                    }
                    $data['track_quantity'] = 0;
                } elseif ($this->input->post('type') == 'digital') {
                    if ($_FILES['digital_file']['size'] > 0) {
                        /*$config['upload_path'] = $this->digital_upload_path;
                        $config['allowed_types'] = $this->digital_file_types;
                        $config['max_size'] = $this->allowed_file_size;
                        $config['overwrite'] = false;
                        $config['encrypt_name'] = true;
                        $config['max_filename'] = 25;
                        $this->upload->initialize($config);
                        if (!$this->upload->do_upload('digital_file')) {
                            $error = $this->upload->display_errors();
                            throw new \Exception($error);
                            // $this->session->set_flashdata('error', $error);
                            // redirect("products/add");
                        }
                        $file = $this->upload->file_name;*/
                        $uploadedImg = $this->integration_model->upload_files($_FILES['digital_file']);
                        $file = $uploadedImg->url;
                        $data['file'] = $file;
                    } else {
                        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'required');
                    }
                    $config = null;
                    $data['track_quantity'] = 0;
                }
                if (!isset($items)) {
                    $items = null;
                }
                if ($_FILES['product_image']['size'] > 0) {
                    /*$check = getimagesize($_FILES['product_image']["tmp_name"]);
                
                    if (!$check) {
                        throw new \Exception("File tidak valid");
                    }
                    if ($_FILES['product_image']["size"] > 16000000) { //15mb
                        throw new \Exception("Ukuran File terlalu besar");
                    }
                    
                    $image = base64_encode(file_get_contents($_FILES['product_image']["tmp_name"]));
                    $uploadedImg = json_decode($this->site->uploadImage($image));*/
                    $uploadedImg = $this->integration_model->upload_files($_FILES['product_image']);
                    if ($uploadedImg) {
                        $data['image'] = $uploadedImg->url;
                        $data['thumb_image'] = $uploadedImg->url;
                    } else {
                        $this->session->set_flashdata('error', "Gagal mengunggah gambar");
                    }
                    // $config['upload_path'] = $this->upload_path;
                    // $config['allowed_types'] = $this->image_types;
                    // $config['max_size'] = $this->allowed_file_size;
                    // $config['max_width'] = $this->Settings->iwidth;
                    // $config['max_height'] = $this->Settings->iheight;
                    // $config['overwrite'] = FALSE;
                    // $config['encrypt_name'] = TRUE;
                    // $config['max_filename'] = 25;
                    // $this->upload->initialize($config);
                    // if (!$this->upload->do_upload('product_image')) {
                    //     $error = $this->upload->display_errors();
                    //     $this->session->set_flashdata('error', $error);
                    //     redirect("products/edit/" . $id);
                    // }
                    // $photo = $this->upload->file_name;
                    // $data['image'] = $photo;
                    // $this->load->library('image_lib');
                    // $config['image_library'] = 'gd2';
                    // $config['source_image'] = $this->upload_path . $photo;
                    // $config['new_image'] = $this->thumbs_path . $photo;
                    // $config['maintain_ratio'] = TRUE;
                    // $config['width'] = $this->Settings->twidth;
                    // $config['height'] = $this->Settings->theight;
                    // $this->image_lib->clear();
                    // $this->image_lib->initialize($config);
                    // if (!$this->image_lib->resize()) {
                    //     echo $this->image_lib->display_errors();
                    // }
                    // if ($this->Settings->watermark) {
                    //     $this->image_lib->clear();
                    //     $wm['source_image'] = $this->upload_path . $photo;
                    //     $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    //     $wm['wm_type'] = 'text';
                    //     $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    //     $wm['quality'] = '100';
                    //     $wm['wm_font_size'] = '16';
                    //     $wm['wm_font_color'] = '999999';
                    //     $wm['wm_shadow_color'] = 'CCCCCC';
                    //     $wm['wm_vrt_alignment'] = 'top';
                    //     $wm['wm_hor_alignment'] = 'left';
                    //     $wm['wm_padding'] = '10';
                    //     $this->image_lib->initialize($wm);
                    //     $this->image_lib->watermark();
                    // }
                    // $this->image_lib->clear();
                    // $config = NULL;
                }

                if ($_FILES['userfile']['name'][0] != "") {
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $files = $_FILES;
                    $cpt = count($_FILES['userfile']['name']);
                    for ($i = 0; $i < $cpt; $i++) {
                        $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                        $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                        $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                        $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                        $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                        /*$this->upload->initialize($config);

                        if (!$this->upload->do_upload()) {
                            $error = $this->upload->display_errors();
                            throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                            // redirect("products/edit/" . $id);
                        } else {
                            $pho = $this->upload->file_name;

                            $photos[] = $pho;

                            $this->load->library('image_lib');
                            $config['image_library'] = 'gd2';
                            $config['source_image'] = $this->upload_path . $pho;
                            $config['new_image'] = $this->thumbs_path . $pho;
                            $config['maintain_ratio'] = true;
                            $config['width'] = $this->Settings->twidth;
                            $config['height'] = $this->Settings->theight;

                            $this->image_lib->initialize($config);

                            if (!$this->image_lib->resize()) {
                                echo $this->image_lib->display_errors();
                            }

                            if ($this->Settings->watermark) {
                                $this->image_lib->clear();
                                $wm['source_image'] = $this->upload_path . $pho;
                                $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                                $wm['wm_type'] = 'text';
                                $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                                $wm['quality'] = '100';
                                $wm['wm_font_size'] = '16';
                                $wm['wm_font_color'] = '999999';
                                $wm['wm_shadow_color'] = 'CCCCCC';
                                $wm['wm_vrt_alignment'] = 'top';
                                $wm['wm_hor_alignment'] = 'left';
                                $wm['wm_padding'] = '10';
                                $this->image_lib->initialize($wm);
                                $this->image_lib->watermark();
                            }

                            $this->image_lib->clear();
                        }*/
                        $uploadedImg = $this->integration_model->upload_files($_FILES['userfile']);
                        $photos[] = $uploadedImg->url;
                    }
                    $config = null;
                } else {
                    $photos = null;
                }
                $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
                // $this->sma->print_arrays($data, $warehouse_qty, $update_variants, $product_attributes, $photos, $items);
            }
            if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("product_updated"));
                redirect('products');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                //            $this->data['consignment'] = $csg;
                $this->data['categories'] = $this->site->getAllCategories();
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                $this->data['brands'] = $this->site->getAllBrands();
                $this->data['base_units'] = $this->site->getAllBaseUnits();
                $this->data['warehouses'] = $warehouses;
                $this->data['warehouses_products'] = $warehouses_products;
                $this->data['product'] = $product;
                $this->data['variants'] = $this->products_model->getAllVariants();
                $this->data['subunits'] = $this->site->getUnitsByBUID($product->unit);
                $this->data['product_variants'] = $this->products_model->getProductOptions($id);
                $this->data['combo_items'] = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : null;
                $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_product')));
                $meta = array('page_title' => lang('edit_product'), 'bc' => $bc);
                $this->page_construct('products/edit', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* ---------------------------------------------------------------- */

    public function import_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/import_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");

            
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('name', 'code', 'barcode_symbology', 'brand', 'category_code', 'unit', 'sale_unit', 'purchase_unit', 'cost', 'price', 'alert_quantity', 'tax_rate', 'tax_method', 'image', 'subcategory_code', 'variants', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $suppliers=$this->companies_model->getAllSupplierCompanies();
                foreach ($suppliers as $row) {
                    $supp=($row->name=="Undefined"?$row->id:null);
                }
                //$this->sma->print_arrays($final);
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (! $this->products_model->getProductByCode(trim($csv_pr['code']))) {
                        if ($catd = $this->products_model->getCategoryByCode(trim($csv_pr['category_code']))) {
                            $brand = $this->products_model->getBrandByName(trim($csv_pr['brand']));
                            $unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));
                            $base_unit = $unit ? $unit->id : null;
                            $sale_unit = $base_unit;
                            $purcahse_unit = $base_unit;
                            if ($base_unit) {
                                $units = $this->site->getUnitsByBUID($base_unit);
                                foreach ($units as $u) {
                                    if ($u->code == trim($csv_pr['sale_unit'])) {
                                        $sale_unit = $u->id;
                                    }
                                    if ($u->code == trim($csv_pr['purchase_unit'])) {
                                        $purcahse_unit = $u->id;
                                    }
                                }
                            } else {
                                $this->session->set_flashdata('error', lang("check_unit") . " (" . $csv_pr['unit'] . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                redirect("products/import_csv");
                            }
                            $pr_code[] = trim($csv_pr['code']);
                            $pr_name[] = trim($csv_pr['name']);
                            $pr_cat[] = $catd->id;
                            $pr_variants[] = trim($csv_pr['variants']);
                            $pr_brand[] = $brand ? $brand->id : null;
                            $pr_unit[] = $base_unit;
                            $sale_units[] = $sale_unit;
                            $purcahse_units[] = $purcahse_unit;
                            $tax_method[] = $csv_pr['tax_method'] == 'exclusive' ? 1 : 0;
                            $prsubcat = $this->products_model->getCategoryByCode(trim($csv_pr['subcategory_code']));
                            $pr_subcat[] = $prsubcat ? $prsubcat->id : null;
                            $pr_cost[] = trim($csv_pr['cost']);
                            $pr_price[] = trim($csv_pr['price']);
                            $pr_aq[] = trim($csv_pr['alert_quantity']);
                            $tax_details = $this->products_model->getTaxRateByName(trim($csv_pr['tax_rate']));
                            $pr_tax[] = $tax_details ? $tax_details->id : null;
                            $bs[] = mb_strtolower(trim($csv_pr['barcode_symbology']), 'UTF-8');
                            $cf1[] = trim($csv_pr['cf1']);
                            $cf2[] = trim($csv_pr['cf2']);
                            $cf3[] = trim($csv_pr['cf3']);
                            $cf4[] = trim($csv_pr['cf4']);
                            $cf5[] = trim($csv_pr['cf5']);
                            $cf6[] = trim($csv_pr['cf6']);
                            $supplier[]= 1;//$supp;
                            $comp_id[]=$this->session->userdata('company_id');
                        } else {
                            $this->session->set_flashdata('error', lang("check_category_code") . " (" . $csv_pr['category_code'] . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                            redirect("products/import_csv");
                        }
                    }

                    $rw++;
                }
            }

            $ikeys = array('code', 'barcode_symbology', 'name', 'brand', 'category_id', 'unit', 'sale_unit', 'purchase_unit', 'cost', 'price', 'alert_quantity', 'tax_rate', 'tax_method', 'subcategory_id', 'variants', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6','supplier1','company_id');

            $items = array();
            foreach (array_map(null, $pr_code, $bs, $pr_name, $pr_brand, $pr_cat, $pr_unit, $sale_units, $purcahse_units, $pr_cost, $pr_price, $pr_aq, $pr_tax, $tax_method, $pr_subcat, $pr_variants, $cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $supplier, $comp_id) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }

            // $this->sma->print_arrays($items);
        }

        if ($this->form_validation->run() == true && $prs = $this->products_model->add_products($items)) {
            $this->session->set_flashdata('message', sprintf(lang("products_added"), $prs));
            redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_products_by_csv')));
            $meta = array('page_title' => lang('import_products_by_csv'), 'bc' => $bc);
            $this->page_construct('products/import_csv', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------ */

    public function update_price()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('message', lang("disabled_in_demo"));
                redirect('welcome');
            }

            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");

                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'price');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (!$this->products_model->getProductByCode(trim($csv_pr['code']))) {
                        $this->session->set_flashdata('message', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
                        redirect("products");
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/group_product_prices/".$group_id);
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            $this->products_model->updatePrice($final);
            $this->session->set_flashdata('message', lang("price_updated"));
            redirect('products');
        } else {
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'products/update_price', $this->data);
        }
    }

    /* ------------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("product_deleted");
                die();
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            redirect('welcome');
        }
    }

    /* ----------------------------------------------------------------------------- */

    public function quantity_adjustments($warehouse_id = null)
    {
        $this->sma->checkPermissions('adjustments');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $link_type = ['mb_quantity_adjustments'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('quantity_adjustments')));
        $meta = array('page_title' => lang('quantity_adjustments'), 'bc' => $bc);
        $this->page_construct('products/quantity_adjustments', $meta, $this->data);
    }

    public function getadjustments($warehouse_id = null)
    {
        $this->sma->checkPermissions('adjustments');

        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_adjustment/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('adjustments')}.id as id, date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, attachment")
            ->from('adjustments')
            ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left')
            ->join('users', 'users.id=adjustments.created_by', 'left')
            ->where('adjustments.is_deleted', null)
            ->group_by("adjustments.id");
        if ($warehouse_id) {
            $this->datatables->where('adjustments.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('products/edit_adjustment/$1') . "' class='tip' title='" . lang("edit_adjustment") . "'><i class='fa fa-edit'></i></a> " . $delete_link . "</div>", "id");

        echo $this->datatables->generate();
    }

    public function view_adjustment($id)
    {
        $this->sma->checkPermissions('adjustments', true);

        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->sma->md();
        }

        $this->data['inv'] = $adjustment;
        $this->data['rows'] = $this->products_model->getAdjustmentItems($id);
        $this->data['created_by'] = $this->site->getUser($adjustment->created_by);
        $this->data['updated_by'] = $this->site->getUser($adjustment->updated_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($adjustment->warehouse_id);
        $this->load->view($this->theme.'products/view_adjustment', $this->data);
    }

    public function add_adjustment($count_id = null)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;

                if (!$this->Settings->overselling && $type == 'subtraction') {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if ($wh_qty['quantity'] < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                }

                $products[] = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    );
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => $this->input->post('count_id') ? $this->input->post('count_id') : null,
                );

            if ($_FILES['document']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;*/

                $uploadedImg = $this->integration_model->upload_files($_FILES['document']);
                $photo = $uploadedImg->url;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            redirect('products/quantity_adjustments');
        } else {
            if ($count_id) {
                $stock_count = $this->products_model->getStouckCountByID($count_id);
                $items = $this->products_model->getStockCountItems($count_id);
                $c = rand(100000, 9999999);
                $x=0;
                foreach ($items as $item) {
                    if ($item->counted != $item->expected) {
                        $product = $this->site->getProductByID($item->product_id);
                        $row = json_decode('{}');
                        $row->id = $item->product_id;
                        $row->code = $product->code;
                        $row->name = $product->name;
                        $row->qty = $item->counted-$item->expected;
                        $row->type = $row->qty > 0 ? 'addition' : 'subtraction';
                        $row->qty = $row->qty > 0 ? $row->qty : (0-$row->qty);
                        $options = $this->products_model->getProductOptions($product->id);
                        $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                        $row->serial = '';
                        $ri = $this->Settings->item_addition ? $product->id : $c;

                        $pr[$ri] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'options' => $options);
                        $id_rand_temp[$x] = array('trx_id' => $c, 'product_id'=>$row->id);
                        $x++;
                        $c++;
                    }
                }
                $this->data['rand_id']=json_encode($id_rand_temp);
            }
            $this->data['adjustment_items'] = $count_id ? json_encode($pr) : false;
            // $this->data['adjustment_items'] = json_encode($pr);
            $this->data['warehouse_id'] = $count_id ? $stock_count->warehouse_id : false;
            $this->data['count_id'] = $count_id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();

            $link_type = ['mb_add_adjustment'];
            $this->load->model('db_model');
            $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
            foreach ($get_link as $val) {
                $this->data[$val->type] = $val->uri;
            }

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_adjustment')));
            $meta = array('page_title' => lang('add_adjustment'), 'bc' => $bc);
            $this->page_construct('products/add_adjustment', $meta, $this->data);
        }
    }

    public function edit_adjustment($id)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->sma->transactionPermissions('adjustments', $id);
        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = $adjustment->date;
            }

            $reference_no = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;

                if (!$this->Settings->overselling && $type == 'subtraction') {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if ($wh_qty['quantity'] < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                }

                $products[] = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    );
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id')
                );

            if ($_FILES['document']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;*/

                $uploadedImg = $this->integration_model->upload_files($_FILES['document']);
                $photo = $uploadedImg->url;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateAdjustment($id, $data, $products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            redirect('products/quantity_adjustments');
        } else {
            $inv_items = $this->products_model->getAdjustmentItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->qty = $item->quantity;
                $row->type = $item->type;
                $options = $this->products_model->getProductOptions($product->id);
                $row->option = $item->option_id ? $item->option_id : 0;
                $row->serial = $item->serial_no ? $item->serial_no : '';
                $ri = $this->Settings->item_addition ? $product->id : $c;

                $pr[$ri] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options);
                $c++;
            }

            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_adjustment')));
            $meta = array('page_title' => lang('edit_adjustment'), 'bc' => $bc);
            $this->page_construct('products/edit_adjustment', $meta, $this->data);
        }
    }

    public function add_adjustment_by_csv()
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => null,
                );

            if ($_FILES['csv_file']['size'] > 0) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $csv = $this->upload->file_name;
                $data['attachment'] = $csv;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");

                
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('code', 'quantity', 'variant');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->sma->print_arrays($final);
                $rw = 2;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['code']))) {
                        $csv_variant = trim($pr['variant']);
                        $variant = !empty($csv_variant) ? $this->products_model->getProductVariantID($product->id, $csv_variant) : false;

                        $csv_quantity = trim($pr['quantity']);
                        $type = $csv_quantity > 0 ? 'addition' : 'subtraction';
                        $quantity = $csv_quantity > 0 ? $csv_quantity : (0-$csv_quantity);

                        if (!$this->Settings->overselling && $type == 'subtraction') {
                            if ($variant) {
                                if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                                    if ($op_wh_qty->quantity < $quantity) {
                                        $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                        redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            }
                            if ($wh_qty = $this->products_model->getProductQuantity($product->id, $warehouse_id)) {
                                if ($wh_qty['quantity'] < $quantity) {
                                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'). ' - ' . lang('line_no') . ' ' . $rw);
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        
                        $products[] = array(
                            'product_id' => $product->id,
                            'type' => $type,
                            'quantity' => $quantity,
                            'warehouse_id' => $warehouse_id,
                            'option_id' => $variant,
                            );
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $rw++;
                }
            } else {
                $this->form_validation->set_rules('csv_file', lang("upload_file"), 'required');
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            redirect('products/quantity_adjustments');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_adjustment')));
            $meta = array('page_title' => lang('add_adjustment_by_csv'), 'bc' => $bc);
            $this->page_construct('products/add_adjustment_by_csv', $meta, $this->data);
        }
    }

    public function delete_adjustment($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->products_model->deleteAdjustment($id)) {
            echo lang("adjustment_deleted");
        }
    }

    /* --------------------------------------------------------------------------------------------- */

    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);

        $pr_details = $this->site->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->sma->md();
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['suppliers'] =$this->products_model->getSupplierById($pr_details->supplier1, $pr_details->supplier2, $pr_details->supplier3, $pr_details->supplier4, $pr_details->supplier5);
        $this->load->library('encrypt');
        $enc_id=$this->encrypt->encode($id);
        $enc_company_id=$this->encrypt->encode($this->session->userdata('company_id'));
        $this->data['enc_id']=str_replace(array('+', '/', '='), array('-', '_', '~'), $enc_id);
        $this->data['enc_company_id']=str_replace(array('+', '/', '='), array('-', '_', '~'), $enc_company_id);
        
        $this->load->view($this->theme.'products/modal_view', $this->data);
    }

    public function view($id = null)
    {
        $this->load->library('encrypt');
        if (!is_numeric($id)) {
            $id=str_replace(array('-', '_', '~'), array('+', '/', '='), $id);
            $id=$this->encrypt->decode($id);
        }
        
        $this->sma->checkPermissions('index');
        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['sold'] = $this->products_model->getSoldQty($id);
        $this->data['purchased'] = $this->products_model->getPurchasedQty($id);
        $enc_id=$this->encrypt->encode($id);
        $enc_company_id=$this->encrypt->encode($this->session->userdata('company_id'));
        $this->data['enc_id']=str_replace(array('+', '/', '='), array('-', '_', '~'), $enc_id);
        $this->data['enc_company_id']=str_replace(array('+', '/', '='), array('-', '_', '~'), $enc_company_id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => $pr_details->name));
        $meta = array('page_title' => $pr_details->name, 'bc' => $bc);
        $this->page_construct('products/view', $meta, $this->data);
    }

    public function pdf($id = null, $view = null)
    {
        $this->sma->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $name = $pr_details->code . '_' . str_replace('/', '_', $pr_details->name) . ".pdf";
        if ($view) {
            $this->load->view($this->theme . 'products/pdf', $this->data);
        } else {
            $html = $this->load->view($this->theme . 'products/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $this->sma->generate_pdf($html, $name);
        }
    }

    public function getSubCategories($category_id = null)
    {
        if ($rows = $this->products_model->getSubCategories($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = json_encode([]);
        }
        echo $data;
    }

    public function product_actions($wh = null)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {
                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(null, null, null, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_quantity_sync"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'labels') {
                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
                    $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
                    $this->page_construct('products/print_barcodes', $meta, $this->data);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle('Products')
                          ->SetCellValue('A1', lang('name'))
                          ->SetCellValue('B1', lang('code'))
                          ->SetCellValue('C1', lang('barcode_symbology'))
                          ->SetCellValue('D1', lang('brand'))
                          ->SetCellValue('E1', lang('category_code'))
                          ->SetCellValue('F1', lang('unit_code'))
                          ->SetCellValue('G1', lang('sale').' '.lang('unit_code'))
                          ->SetCellValue('H1', lang('purchase').' '.lang('unit_code'))
                          ->SetCellValue('I1', lang('cost'))
                          ->SetCellValue('J1', lang('price'))
                          ->SetCellValue('K1', lang('alert_quantity'))
                          ->SetCellValue('L1', lang('tax_rate'))
                          ->SetCellValue('M1', lang('tax_method'))
                          ->SetCellValue('N1', lang('image'))
                          ->SetCellValue('O1', lang('subcategory_code'))
                          ->SetCellValue('P1', lang('product_variants'))
                          ->SetCellValue('Q1', lang('pcf1'))
                          ->SetCellValue('R1', lang('pcf2'))
                          ->SetCellValue('S1', lang('pcf3'))
                          ->SetCellValue('T1', lang('pcf4'))
                          ->SetCellValue('U1', lang('pcf5'))
                          ->SetCellValue('V1', lang('pcf6'))
                          ->SetCellValue('W1', lang('quantity'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->products_model->getProductDetail($id);
                        $brand = $this->site->getBrandByID($product->brand);
                        if ($units = $this->site->getUnitsByBUID($product->unit)) {
                            foreach ($units as $u) {
                                if ($u->id == $product->unit) {
                                    $base_unit = $u->code;
                                }
                                if ($u->id == $product->sale_unit) {
                                    $sale_unit = $u->code;
                                }
                                if ($u->id == $product->purchase_unit) {
                                    $purchase_unit = $u->code;
                                }
                            }
                        } else {
                            $base_unit = '';
                            $sale_unit = '';
                            $purchase_unit = '';
                        }
                        $variants = $this->products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . '|';
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if ($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }
                        $sheet->SetCellValue('A' . $row, $product->name)
                              ->SetCellValue('B' . $row, $product->code)
                              ->SetCellValue('C' . $row, $product->barcode_symbology)
                              ->SetCellValue('D' . $row, ($brand ? $brand->name : ''))
                              ->SetCellValue('E' . $row, $product->category_code)
                              ->SetCellValue('F' . $row, $base_unit)
                              ->SetCellValue('G' . $row, $sale_unit)
                              ->SetCellValue('H' . $row, $purchase_unit);
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
                            $sheet->SetCellValue('I' . $row, $product->cost);
                        }
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_price')) {
                            $sheet->SetCellValue('J' . $row, $product->price);
                        }
                        $sheet->SetCellValue('K' . $row, $product->alert_quantity)
                              ->SetCellValue('L' . $row, $product->tax_rate_name)
                              ->SetCellValue('M' . $row, $product->tax_method ? lang('exclusive') : lang('inclusive'))
                              ->SetCellValue('N' . $row, $product->image)
                              ->SetCellValue('O' . $row, $product->subcategory_code)
                              ->SetCellValue('P' . $row, $product_variants)
                              ->SetCellValue('Q' . $row, $product->cf1)
                              ->SetCellValue('R' . $row, $product->cf2)
                              ->SetCellValue('S' . $row, $product->cf3)
                              ->SetCellValue('T' . $row, $product->cf4)
                              ->SetCellValue('U' . $row, $product->cf5)
                              ->SetCellValue('V' . $row, $product->cf6)
                              ->SetCellValue('W' . $row, $quantity);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(30);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(15);
                    $sheet->getColumnDimension('E')->setWidth(20);
                    $sheet->getColumnDimension('N')->setWidth(40);
                    $sheet->getColumnDimension('O')->setWidth(30);
                    $sheet->getColumnDimension('P')->setWidth(30);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray( ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER] );
                    $filename = 'products_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                        $sheet->getDefaultStyle()->applyFromArray($styleArray);
                        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
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
                } elseif ($this->input->post('form_action') == 'sync_quantity_booking') {
                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantityBooking(null, null, null, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_quantity_booking_sync"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_image($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $id || die(json_encode(array('error' => 1, 'msg' => lang('no_image_selected'))));
            $this->db->delete('product_photos', array('id' => $id));
            die(json_encode(array('error' => 0, 'msg' => lang('image_deleted'))));
        }
        die(json_encode(array('error' => 1, 'msg' => lang('ajax_error'))));
    }

    public function getSubUnits($unit_id)
    {
        $unit = $this->site->getUnitByID($unit_id);
        if ($units = $this->site->getUnitsByBUID($unit_id)) {
//            array_push($units, $unit);
        } else {
            $units = array($unit);
        }
        $this->sma->send_json($units);
    }

    public function qa_suggestions()
    {
        $term = $this->input->get('term', true);
       
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->products_model->getQASuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';

                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options);
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function adjustment_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteAdjustment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("adjustment_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle('quantity_adjustments')
                          ->SetCellValue('A1', lang('date'))
                          ->SetCellValue('B1', lang('reference_no'))
                          ->SetCellValue('C1', lang('warehouse'))
                          ->SetCellValue('D1', lang('created_by'))
                          ->SetCellValue('E1', lang('note'))
                          ->SetCellValue('F1', lang('items'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $adjustment = $this->products_model->getAdjustmentByID($id);
                        $created_by = $this->site->getUser($adjustment->created_by);
                        $warehouse = $this->site->getWarehouseByID($adjustment->warehouse_id);
                        $items = $this->products_model->getAdjustmentItems($id);
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->product_name.'('.$this->sma->formatQuantity($item->type == 'subtraction' ? -$item->quantity : $item->quantity).')'."\n";
                            }
                        }
                        $sheet->SetCellValue('A' . $row, $this->sma->hrld($adjustment->date))
                              ->SetCellValue('B' . $row, $adjustment->reference_no)
                              ->SetCellValue('C' . $row, $warehouse->name)
                              ->SetCellValue('D' . $row, $created_by->first_name.' ' .$created_by->last_name)
                              ->SetCellValue('E' . $row, $this->sma->decode_html($adjustment->note))
                              ->SetCellValue('F' . $row, $products);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('C')->setWidth(15);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getColumnDimension('E')->setWidth(40);
                    $sheet->getColumnDimension('F')->setWidth(30);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray( ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER] );
                    $filename = 'quantity_adjustments_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)));
                        $sheet->getDefaultStyle()->applyFromArray($styleArray);
                        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
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
                        $sheet->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                        $sheet->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
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
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function stock_counts($warehouse_id = null)
    {
        $this->sma->checkPermissions('stock_count');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('stock_counts')));
        $meta = array('page_title' => lang('stock_counts'), 'bc' => $bc);
        $this->page_construct('products/stock_counts', $meta, $this->data);
    }

    public function getCounts($warehouse_id = null)
    {
        $this->sma->checkPermissions('stock_count', true);

        if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
            $user = $this->site->getUser();
            // $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('products/view_count/$1', '<label class="label label-primary pointer">'.lang('details').'</label>', 'class="tip" title="'.lang('details').'" data-toggle="modal" data-target="#myModal"  data-backdrop="static"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('stock_counts')}.id as id, date, reference_no, {$this->db->dbprefix('warehouses')}.name as wh_name, type, brand_names, category_names, initial_file, final_file")
            ->from('stock_counts')
            ->join('warehouses', 'warehouses.id=stock_counts.warehouse_id', 'left');
        if ($warehouse_id) {
            $this->datatables->where('warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $this->datatables->add_column('Actions', '<div class="text-center">'.$detail_link.'</div>', "id");
        echo $this->datatables->generate();
    }

    public function view_count($id)
    {
        $this->sma->checkPermissions('stock_count', true);
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (! $stock_count->finalized) {
            $this->sma->md('products/finalize_count/'.$id);
        }

        $this->data['stock_count'] = $stock_count;
        $this->data['stock_count_items'] = $this->products_model->getStockCountItems($id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
        $this->data['adjustment'] = $this->products_model->getAdjustmentByCountID($id);
        $this->load->view($this->theme.'products/view_count', $this->data);
    }

    public function count_stock($page = null)
    {
        $this->sma->checkPermissions('stock_count');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        $this->form_validation->set_rules('type', lang("type"), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sc');
            $warehouse_id = $this->input->post('warehouse');
            $type = $this->input->post('type');
            $categories = $this->input->post('category') ? $this->input->post('category') : null;
            $brands = $this->input->post('brand') ? $this->input->post('brand') : null;
            $this->load->helper('string');
            $name = random_string('md5').'.csv';
            $products = $this->products_model->getStockCountProducts($warehouse_id, $type, $categories, $brands);
            $pr = 0;
            $rw = 0;
            foreach ($products as $product) {
                if ($variants = $this->products_model->getStockCountProductVariants($warehouse_id, $product->id)) {
                    foreach ($variants as $variant) {
                        $items[] = array(
                            'product_code' => $product->code,
                            'product_name' => $product->name,
                            'variant' => $variant->name,
                            'expected' => (int) $variant->quantity,
                            'counted' => ''
                            );
                        $rw++;
                    }
                } else {
                    $items[] = array(
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'variant' => '',
                        'expected' => (int) $product->quantity,
                        'counted' => ''
                        );
                    $rw++;
                }
                $pr++;
            }
            if (! empty($items)) {
                $csv_file = fopen('./files/'.$name, 'w');
                fputcsv($csv_file, array(lang('product_code'), lang('product_name'), lang('variant'), lang('expected'), lang('counted')));
                foreach ($items as $item) {
                    fputcsv($csv_file, $item);
                }
                // file_put_contents('./files/'.$name, $csv_file);
                // fwrite($csv_file, $txt);
                fclose($csv_file);
            } else {
                $this->session->set_flashdata('error', lang('no_product_found'));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }
            $category_ids = '';
            $brand_ids = '';
            $category_names = '';
            $brand_names = '';
            if ($categories) {
                $r = 1;
                $s = sizeof($categories);
                foreach ($categories as $category_id) {
                    $category = $this->site->getCategoryByID($category_id);
                    if ($r == $s) {
                        $category_names .= $category->name;
                        $category_ids .= $category->id;
                    } else {
                        $category_names .= $category->name.', ';
                        $category_ids .= $category->id.', ';
                    }
                    $r++;
                }
            }
            if ($brands) {
                $r = 1;
                $s = sizeof($brands);
                foreach ($brands as $brand_id) {
                    $brand = $this->site->getBrandByID($brand_id);
                    if ($r == $s) {
                        $brand_names .= $brand->name;
                        $brand_ids .= $brand->id;
                    } else {
                        $brand_names .= $brand->name.', ';
                        $brand_ids .= $brand->id.', ';
                    }
                    $r++;
                }
            }
            $data = array(
                'reference_no'=>$reference,
                'date' => $date,
                'warehouse_id' => $warehouse_id,
//                'reference_no' => $this->input->post('reference_no'),
                'type' => $type,
                'categories' => $category_ids,
                'category_names' => $category_names,
                'brands' => $brand_ids,
                'brand_names' => $brand_names,
                'initial_file' => $name,
                'products' => $pr,
                'rows' => $rw,
                'created_by' => $this->session->userdata('user_id')
            );

        }
        
        if ($this->form_validation->run() == true && $this->products_model->addStockCount($data)) {
            $this->session->set_flashdata('message', lang("stock_count_intiated"));
            redirect('products/stock_counts');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('count_stock')));
            $meta = array('page_title' => lang('count_stock'), 'bc' => $bc);
            $this->page_construct('products/count_stock', $meta, $this->data);
        }
    }

    public function finalize_count($id)
    {
        $this->sma->checkPermissions('stock_count');
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (! $stock_count || $stock_count->finalized) {
            $this->session->set_flashdata('error', lang("stock_count_finalized"));
            redirect('products/stock_counts');
        }

        $this->form_validation->set_rules('count_id', lang("count_stock"), 'required');

        if ($this->form_validation->run() == true) {
            if ($_FILES['csv_file']['size'] > 0) {
                $note = $this->sma->clear_tags($this->input->post('note'));
                $data = array(
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:s:i'),
                    'note' => $note
                );
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");

                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('product_code', 'product_name', 'product_variant', 'expected', 'counted');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                //$this->sma->print_arrays($final);
                $rw = 2;
                $differences = 0;
                $matches = 0;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['product_code']))) {
                        $pr['counted'] = !empty($pr['counted']) ? $pr['counted'] : 0;
                        if ($pr['expected'] == $pr['counted']) {
                            $matches++;
                        } else {
                            $pr['stock_count_id'] = $id;
                            $pr['product_id'] = $product->id;
                            $pr['cost'] = $product->cost;
                            $pr['product_variant_id'] = empty($pr['product_variant']) ? null : $this->products_model->getProductVariantID($pr['product_id'], $pr['product_variant']);
                            $products[] = $pr;
                            $differences++;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['product_code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        redirect('products/finalize_count/'.$id);
                    }
                    $rw++;
                }

                $data['final_file'] = $csv;
                $data['differences'] = $differences;
                $data['matches'] = $matches;
                $data['missing'] = $stock_count->rows-($rw-2);
                $data['finalized'] = 1;
            }

            // $this->sma->print_arrays($data, $products);
        }
        
        if ($this->form_validation->run() == true && $this->products_model->finalizeStockCount($id, $data, $products)) {
            $this->session->set_flashdata('message', lang("stock_count_finalized"));
            redirect('products/stock_counts');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stock_count'] = $stock_count;
            $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => site_url('products/stock_counts'), 'page' => lang('stock_counts')), array('link' => '#', 'page' => lang('finalize_count')));
            $meta = array('page_title' => lang('finalize_count'), 'bc' => $bc);
            $this->page_construct('products/finalize_count', $meta, $this->data);
        }
    }

    public function check_company($pk1, $pk2)
    {
        $this->db->where('code', $pk1);
        $this->db->where('company_id', $pk2);
        $result = $this->db->get('products');
        if ($result->num_rows() > 0) {
            // $this->form_validation->set_message('combpk','something'); // set your message
            return false;
        } else {
            return true;
        }
    }
    
    public function add_brand()
    {
        $this->form_validation->set_rules('name', lang("brand_name"), 'trim|required|alpha_numeric_spaces');

        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
        'client_id' => $this->session->userdata('company_id'),
                );

            if ($_FILES['userfile']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();*/
                $uploadedImg = $this->integration_model->upload_files($_FILES['userfile']);
                $data['image'] = $uploadedImg->url;
            }
        } elseif ($this->input->post('add_brand')) {
            echo json_encode(validation_errors());
            return true;
        }
      
        if ($this->form_validation->run() == true && $this->settings_model->addBrand($data)) {
            $DataBrand = $this->settings_model->getBrandByName($this->input->post('name'));
            $hasil = array("message" => lang("brand_added")." : ".$this->input->post('name') ,"BrandID" => $DataBrand->id);
            return json_encode($hasil);
//            return true;
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/add_brand', $this->data);
        }
    }
    public function add_category($sub=null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("category_code"), 'trim|required');
        $this->form_validation->set_rules('name', lang("name"), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang("category_image"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'parent_id' => $this->input->post('parent'),
                'company_id' => $this->session->userdata('company_id'),
                );

            if ($_FILES['userfile']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;*/
                $uploadedImg = $this->integration_model->upload_files($_FILES['userfile']);
                $data['image'] = $uploadedImg->url;
            }
        } elseif ($this->input->post('add_category')) {
            echo json_encode(validation_errors());
            return true;
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategory($data)) {
            $DataCategory = $this->settings_model->getCategoryByCode($this->input->post('code'));
            $hasil = array("message" => lang("category_added")." : ".$this->input->post('name') , "CategoryID" => $DataCategory->id,"SubCategoryID" => $DataCategory->parent_id);
            echo json_encode($hasil);
            return true;
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories'] = $this->settings_model->getParentCategories();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['sub_category']= $sub ? $sub: null ;
            $this->load->view($this->theme . 'products/add_category', $this->data);
        }
    }
    public function add_unit()
    {
        $this->form_validation->set_rules('code', lang("unit_code"), 'trim|required');
        $this->form_validation->set_rules('name', lang("unit_name"), 'trim|required');
        if ($this->input->post('base_unit')) {
            $this->form_validation->set_rules('operator', lang("operator"), 'required');
            $this->form_validation->set_rules('operation_value', lang("operation_value"), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'base_unit' => $this->input->post('base_unit') ? $this->input->post('base_unit') : null,
                'operator' => $this->input->post('base_unit') ? $this->input->post('operator') : null,
                'operation_value' => $this->input->post('operation_value') ? $this->input->post('operation_value') : null,
                'client_id' => $this->session->userdata('company_id'),
                );
        } elseif ($this->input->post('add_unit')) {
            $this->session->set_flashdata('error', validation_errors());
            echo json_encode(validation_errors());
            return true;
        }

        if ($this->form_validation->run() == true && $this->settings_model->addUnit($data)) {
            echo json_encode(lang("unit_added")." : ".$this->input->post('name'));
            return true;
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/add_unit', $this->data);
        }
    }
    public function getAllBrands()
    {
        $data=$this->site->getAllBrands();
        echo json_encode($data);
    }
    public function getAllCategories()
    {
        $data=$this->site->getAllCategories();
        echo json_encode($data);
    }
    
    public function getAllBaseUnits()
    {
        $data=$this->site->getAllBaseUnits();
        echo json_encode($data);
    }

    public function ecomerce()
    {
        $data = $this->site->getCompanyByID($this->session->userdata('biller_id'));
        echo json_encode($data);
    }
    
    public function getProduct($id=null)
    {
        $row = $this->site->getProductByID($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => $row->name . " (" . $row->code . ")")));
    }
    
    public function view_consignment($id)
    {
        $this->sma->checkPermissions('consignments', true);

        $consignment = $this->products_model->getConsignmentByID($id);
        if (!$id || !$consignment) {
            $this->session->set_flashdata('error', lang('consignment_not_found'));
            $this->sma->md();
        }

        $this->data['consignment'] = $consignment;
        $this->data['rows'] = $this->products_model->getConsignmentItems($id);
        $this->data['created_by'] = $this->site->getUser($consignment->created_by);
        $this->data['updated_by'] = $this->site->getUser($consignment->updated_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($consignment->warehouse_id);
        $this->load->view($this->theme.'products/view_consignment', $this->data);
    }
    
    public function add_consignment()
    {
        $this->sma->checkPermissions('consignments', true);
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('csg');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $supplier_id = $this->input->post('supplier_id');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $code = $_POST['item_code'][$r];
                $quantity = $_POST['quantity'][$r];
                $net_price= $_POST['price'][$r];
                $expire=$_POST['expire'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;

                $subtotal = ($net_price * $quantity);

                $products[] = array(
                    'product_id' => $product_id,
                    'product_code' => $code,
                    'option_id' => $variant,
                    'net_unit_price' => $net_price,
                    'quantity' => $quantity,
                    'expiry' => $expire,
                    'subtotal' => $this->sma->formatDecimal($subtotal),
                    'warehouse_id' => $warehouse_id,
                );
                $total += $this->sma->formatDecimal(($net_price * $quantity), 4);
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'reference_no' => $reference_no,
                'total' => $total,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'company_id'=>$this->session->userdata('company_id'),
                'payment_status' => 'pending',
            );
        }

        if ($this->form_validation->run() == true && $this->products_model->addConsignment($data, $products)) {
            $this->session->set_userdata('remove_csgls', 1);
            $this->session->set_flashdata('message', lang("consignment_added"));
            redirect('products/consignments');
        } else {
//            if ($count_id) {
//                $stock_count = $this->products_model->getStouckCountByID($count_id);
//                $items = $this->products_model->getStockCountItems($count_id);
//                $c = rand(100000, 9999999);
//                $x=0;
//                foreach ($items as $item) {
//                    if ($item->counted != $item->expected) {
//                        $product = $this->site->getProductByID($item->product_id);
//                        $row = json_decode('{}');
//                        $row->id = $item->product_id;
//                        $row->code = $product->code;
//                        $row->name = $product->name;
//                        $row->qty = $item->counted-$item->expected;
//                        $row->type = $row->qty > 0 ? 'addition' : 'subtraction';
//                        $row->qty = $row->qty > 0 ? $row->qty : (0-$row->qty);
//                        $options = $this->products_model->getProductOptions($product->id);
//                        $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
//                        $row->serial = '';
//                        $ri = $this->Settings->item_addition ? $product->id : $c;
//
//                        $pr[$ri] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
//                            'row' => $row, 'options' => $options);
//                        $x++;
//                        $c++;
//                    }
//                }
//            }

//            $this->data['consignment_items'] = $count_id ? json_encode($pr) : FALSE;
//            $this->data['warehouse_id'] = $count_id ? $stock_count->warehouse_id : FALSE;
//            $this->data['count_id'] = $count_id;

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_consignment')));
            $meta = array('page_title' => lang('add_consignment'), 'bc' => $bc);
            $this->page_construct('products/add_consignment', $meta, $this->data);
        }
    }
    
    public function edit_consignment($id = null)
    {
//        $this->sma->checkPermissions('adjustments', true);
//        $this->sma->transactionPermissions('adjustments',$id);
        $consignment = $this->products_model->getConsignmentByID($id);
        if (!$id || !$consignment) {
            $this->session->set_flashdata('error', lang('consignment_not_found'));
            $this->sma->md();
        } elseif ($consignment->payment_status=='partial' || $consignment->payment_status=='paid') {
            $this->session->set_flashdata('error', lang('consignment_already_paid'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        $this->form_validation->set_rules('date', lang("date"), 'required');
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('supplier', lang("supplier"), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = $consignment->date;
            }

            $reference_no = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $supplier_id = $this->input->post('supplier_id');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $code = $_POST['item_code'][$r];
                $quantity = $_POST['quantity'][$r];
                $net_price= $_POST['price'][$r];
                $expire=$_POST['expire'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;

                $subtotal = ($net_price * $quantity);

                $products[] = array(
                    'product_id' => $product_id,
                    'product_code' => $code,
                    'option_id' => $variant,
                    'net_unit_price' => $net_price,
                    'quantity' => $quantity,
                    'expiry' => $expire,
                    'subtotal' => $this->sma->formatDecimal($subtotal),
                    'warehouse_id' => $warehouse_id,
                );
                $total += $this->sma->formatDecimal(($net_price * $quantity), 4);
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'reference_no' => $reference_no,
                'total' => $total,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
            );
        }

        if ($this->form_validation->run() == true && $this->products_model->updateConsignment($id, $data, $products)) {
            $this->session->set_userdata('remove_csgls', 1);
            $this->session->set_flashdata('message', lang("consignment_updated"));
            redirect('products/consignments');
        } else {
            $inv_items = $this->products_model->getConsignmentItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->qty = $item->quantity;
                $row->supplier1price= $item->net_unit_price;
                $options = $this->products_model->getProductOptions($product->id);
                $row->option = $item->option_id ? $item->option_id : 0;
                $row->serial = $item->serial_no ? $item->serial_no : '';
                $ri = $this->Settings->item_addition ? $product->id : $c;

                $pr[$ri] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options);
                $c++;
            }
            
            $this->data['consignment'] = $consignment;
            $this->data['consignment_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_consignment')));
            $meta = array('page_title' => lang('edit_consignment'), 'bc' => $bc);
            $this->page_construct('products/edit_consignment', $meta, $this->data);
        }
    }
    
    public function consignments($warehouse_id=null)
    {
        $this->sma->checkPermissions('consignments');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('consignments')));
        $meta = array('page_title' => lang('consignments'), 'bc' => $bc);
        $this->page_construct('products/consignments', $meta, $this->data);
    }
    
    public function getConsignments($warehouse_id=null)
    {
        $add_payment_link = "<a href='".site_url('products/add_payment/$1')."' class='tip' title='".lang("add_payment")."' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-money\"></i></a>";
        $edit_link = "<a href='".site_url('products/edit_consignment/$1')."' class='tip' title='".lang("edit_consignment")."'><i class=\"fa fa-edit\"></i></a>";
        $delete_link = "<a href='#' class='tip po' title='" . lang("delete_bonus") . "' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_bonus/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        $actions="<div class=\"text-center\">".$add_payment_link." ".$edit_link."</div>";
        
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('consignment')}.id, {$this->db->dbprefix('consignment')}.date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, total, paid, (total-paid) as balance, payment_status")
            ->from("consignment")
            ->join("users", "users.id=consignment.created_by", "left")
            ->join("warehouses", "consignment.warehouse_id=warehouses.id", "left");
        $this->datatables->where("consignment.is_deleted !=", 1)->or_where("consignment.is_deleted", null);
        if ($warehouse_id) {
            $this->datatables->where('consignment.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->datatables->where('consignment.company_id', $this->session->userdata('company_id'));
        }
        
        $this->datatables->add_column("Actions", $actions, "{$this->db->dbprefix('consignment')}.id");
        echo $this->datatables->generate();
    }
    
    public function csg_suggestions()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);
        
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->products_model->getCSGSuggestions($sr, $supplier_id);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->expiry = '';
                $units = $this->site->getUnitsByBUID($row->unit);
                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options, 'units'=>$units);
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    
    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->sma->transactionPermissions('consignment', $id);
        $consignment = $this->products_model->getConsignmentByID($id);
        if ($consignment->payment_status == 'paid' && $consignment->total == $consignment->paid) {
            $this->session->set_flashdata('error', lang("consignment_already_paid"));
            $this->sma->md();
        }

        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'consignment_id' => $this->input->post('consignment_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cpay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->sma->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
                'company_id' => $this->session->userdata('company_id'),
            );

            if ($_FILES['userfile']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;*/
                $uploadedImg = $this->integration_model->upload_files($_FILES['userfile']);
                $photo = $uploadedImg->url;
                $payment['attachment'] = $photo;
            }
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->products_model->addPayment($payment)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $consignment;
            $this->data['payment_ref'] = ''; //$this->site->getReference('ppay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'products/add_payment', $this->data);
        }
    }
    
    public function sync_product($id)
    {
        $this->form_validation->set_rules('name', lang("product_name"), 'required');
        $this->form_validation->set_rules('supplier', lang("supplier"), 'required');
        
        $product=$this->site->getProductByID($id);
        
        if ($this->form_validation->run() == true) {
            if ($product) {
                $data=array(
                    'uuid'  => $this->input->post('name'),
                );
            }
        } elseif ($this->input->post('synchronize_product')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("products");
        }
        
        if ($this->form_validation->run() == true && $this->Official_model->update_sync_product($id, $data)) {
            $this->session->set_flashdata('message', lang("synchronized"));
            redirect("products/view/".$id);
        } else {
            $suppliers=array($product->supplier1, $product->supplier2, $product->supplier3, $product->supplier4, $product->supplier5);
            $merchandiser=array();
            for ($i=0; $i<5; $i++) {
                if ($suppliers[$i]) {
                    $result=$this->site->getCompanyByID($suppliers[$i]);
                    if ($this->Official_model->getParnerNumberbyID($suppliers[$i])) {
                        $merchandiser[$result->id]=$result->name;
                    }
                }
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $this->data['product']= $product;
            $this->data['id'] = $id;
            $this->data['suppliers'] = $merchandiser;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/sync_product', $this->data);
        }
    }
}

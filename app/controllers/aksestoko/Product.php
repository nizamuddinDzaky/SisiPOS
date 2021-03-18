<?php defined('BASEPATH') or exit('No direct script access allowed');

class Product extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->insertLogActivities();
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/promotion_model', 'promotion');
        $this->load->model('aksestoko/product_model', 'product');
    }

    public function __reverseOperate($a, $b, $char)
    {
        switch ($char) {
            case '-':
                return $a + $b;
            case '*':
                return $a / $b;
            case '+':
                return $a - $b;
            case '/':
                return $a * $b;
        }
        return $a;
    }


    public function index($id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->data['title_at'] = "Daftar Produk - AksesToko";

        echo $this->promotion->index();
        return "haha";
    }

    public function view($product_id)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        if($supplier_id_get = $this->input->get('supplier_id')){
            redirect(aksestoko_route('aksestoko/home/select_supplier/'.$supplier_id_get.'?redirect='.urlencode('aksestoko/product/view/'.$product_id)));
        }
        
        $supplier_id = $this->session->userdata('supplier_id');
        $this->data['product'] = $this->product->getProductByID($product_id, $supplier_id, $this->session->userdata('price_group_id'));

        if (!$this->data['product']) {
            $this->session->set_flashdata('warning', "Tidak dapat melihat produk");
            redirect(aksestoko_route('aksestoko/home/main'));
        }

        $this->data['product']->price = $this->data['product']->group_price && $this->data['product']->group_price > 0 ? $this->data['product']->group_price : $this->data['product']->price;

        $unit = $this->product->getUnit($this->data['product']->sale_unit);

        $price_value = $this->__operate($this->data['product']->price, $unit->operation_value, $unit->operator);

        $this->data['product']->price = $price_value;
        $this->data['product']->is_multiple = ($this->data['product']->is_multiple == 1);
        $this->data['unit'] = $unit;

        $this->data['product']->min_order = $this->data['product']->min_order && $this->data['product']->min_order > 0 ? (int) $this->data['product']->min_order : 1;

        if (!$this->data['product']->is_multiple) {
            if ($this->data['cart'] && count($this->data['cart']) > 0) {
                foreach ($this->data['cart'] as $item) {
                    if ($product_id == $item->id) {
                        $diff = $this->data['product']->min_order - $item->cart_qty;
                        $this->data['product']->min_order =  $diff <= 0 ? 1 : $diff;
                        break;
                    }
                }
            }
        }
        $this->data['object'] = $this;
        $this->data['images'] = $this->product->getProductPhotos($product_id);
        $this->data['product_id'] = $product_id;
        $this->data['supplier_id'] = $supplier_id;
        $this->data['supplier'] = $this->site->getCompanyByID($supplier_id);
        $this->data['user_id'] = $this->session->userdata('user_id');
        $this->data['title_at'] = "{$this->data['product']->name} - Produk - AksesToko";

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/detail-product', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }
}

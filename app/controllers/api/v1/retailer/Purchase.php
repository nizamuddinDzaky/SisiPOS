<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Retailer_Controller.php';

class Purchase extends MY_API_Retailer_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/product_model', 'product');
        $this->load->model('aksestoko/bank_model', 'bank');
        $this->load->model('aksestoko/Payment_model', 'payment');
        $this->load->model('aksestoko/at_purchase_model', 'at_purchase');
        $this->load->model('aksestoko/at_sale_model', 'at_sale');
        $this->load->model('Sales_model', 'sales_model');
        $this->load->model('Site', 'site');
        $this->load->model('aksestoko/at_company_model', 'at_company');
        $this->load->model('aksestoko/promotion_model', 'promotion');
        $this->load->model('aksestoko/at_auth_model', 'at_auth');
        $this->load->model('integration_model', 'integration');
        $this->load->model('audittrail_model', 'audittrail');
        $this->load->model('socket_notification_model');
        $this->data['array_payment_method'] = ['cash on delivery', 'kredit'];
    }

    public function list_cart_get()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $supplier_id    = $this->input->get('id_distributor');
            $price_group_id = $this->input->get('price_group_id');

            if (!$supplier_id) {
                throw new Exception("Params `id_distributor` is required", 404);
            }

            $cart           = $this->at_site->getProductInCart($supplier_id, $auth->user->id, $price_group_id);

            if (!$cart) {
                throw new \Exception("Keranjang Belanja kosong, masukkan item terlebih dahulu", 400);
            }

            $totalQty       = 0;
            $totalAmount    = 0;
            foreach ($cart as $item) {
                $totalQty       += $item->cart_qty;
                $totalAmount    += $item->price * $item->cart_qty;
                $data[] = [
                    'id_cart'       => $item->id_cart,
                    'product_id'    => $item->id,
                    'image'         => url_image_thumb($item->thumb_image) ?? base_url('assets/uploads/no_image.png'),
                    'nama_product'  => $item->name,
                    'code_product'  => $item->code,
                    'harga_product' => (int)$item->price,
                    'satuan'        => convert_unit($this->__unit($item->sale_unit)),
                    'total_harga'   => $item->price * $item->cart_qty,
                    'quantity'      => (int)$item->cart_qty,
                    'multiple'      => $item->is_multiple,
                    'min_order'     => $item->min_order
                ];
            }
            $response = [
                "total_product" => count($cart),
                "jumlah_barang" => $totalQty,
                "total_harga"   => $totalAmount,
                "list_product"  => $data
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar keranjang", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_to_cart_batch_post()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();
            // var_dump($this->input->post());die;
            $config = [
                [
                    'field' => 'id_distributor',
                    'label' => 'ID Distributor',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $config_item_cart = [
                [
                    'field' => 'product_id',
                    'label' => 'Product ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'quantity',
                    'label' => 'Quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ],
            ];

            $this->validate_form($config);
            $item_cart = $this->body('item_cart');

            if (!$item_cart) {
                throw new Exception("Params `item_cart` is required", 404);
            }

            $id_distributor   = $this->body('id_distributor');

            if (!$this->at_site->emptyCart($id_distributor, $auth->user->id)) {
                throw new \Exception("Tidak bisa mengosongkan keranjang belanja");
            }

            $id_cart = [];
            foreach ($item_cart as $item) {
                $this->validate_form($config_item_cart, $item);

                $requestCart = [
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'supplier_id' => $id_distributor,
                    'user_id'     => $auth->user->id,
                ];

                $addCart = $this->at_site->insertCartMobile($requestCart);
                if (!$addCart) {
                    throw new \Exception("Gagal melakukan penambahan item pada keranjang");
                }

                $id_cart[] = $addCart;
            }

            $response = [
                'id_cart' => $id_cart,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan penambahan item pada keranjang", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_to_cart_post()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();
            $config = [
                [
                    'field' => 'product_id',
                    'label' => 'Product ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'quantity',
                    'label' => 'Quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'id_distributor',
                    'label' => 'Distributor ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $product_id       = $this->body('product_id');
            $quantity         = $this->body('quantity');
            $id_distributor   = $this->body('id_distributor');

            $requestCart = [
                'product_id'  => $product_id,
                'quantity'    => $quantity,
                'supplier_id' => $id_distributor,
                'user_id'     => $auth->user->id,
            ];

            $addCart = $this->at_site->insertCartMobile($requestCart);

            if (!$addCart) {
                throw new \Exception("Gagal melakukan penambahan item pada keranjang");
            }

            $q = $this->db->get_where('carts', ['id' => $addCart], 1);
            if ($q->num_rows() > 0) {
                $row = $q->row();
            }

            $data = [
                'product_id'  => $product_id,
                'quantity'    => (int)$row->quantity,
                'supplier_id' => $id_distributor,
                'user_id'     => $auth->user->id,
            ];

            $response = [
                'id_cart' => (int)$addCart,
                'data'    => $data
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan penambahan item pada keranjang", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function delete_item_cart_post()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();
            $config = [
                [
                    'field' => 'id_cart',
                    'label' => 'Cart ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'id_distributor',
                    'label' => 'Distributor ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $id_cart        = $this->body('id_cart');
            $supplier_id    = $this->body('id_distributor');

            $delete         = $this->at_site->removeProductInCart($id_cart, $supplier_id, $auth->user->id);
            if (!$delete) {
                throw new \Exception("Gagal menghapus item pada cart");
            }

            $response = [
                'id_cart'        => $id_cart,
                'id_distributor' => $supplier_id
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil menghapus item dari keranjang", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_cart_put()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();
            $config = [
                [
                    'field' => 'id_cart',
                    'label' => 'Cart ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'id_distributor',
                    'label' => 'Distributor ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'quantity',
                    'label' => 'Quantity',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $id             = $this->body('id_cart');
            $supplier_id    = $this->body('id_distributor');
            $qty            = $this->body('quantity');
            $price_group_id = $this->body('price_group_id');
            $code_promo     = $this->body('promo');

            if ($qty == 0) {
                throw new \Exception("Gagal melakukan pembaruan pada item, quantity tidak boleh 0");
            }

            $updateCart   = $this->at_site->updateProductInCart($id, $supplier_id, $auth->user->id, $qty);
            if (!$updateCart) {
                throw new \Exception("Gagal melakukan pembaruan pada item");
            }

            $cart               = $this->at_site->getProductInCart($supplier_id, $auth->user->id, $price_group_id);
            $totalQty           = 0;
            $totalAmount        = 0;
            $totalPoint         = 0;
            foreach ($cart as $item) {
                $totalQty       += $item->cart_qty;
                $totalAmount    += ($item->price * $item->cart_qty);
                $totalPoint     = +0;
            }

            $company    = $this->at_site->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);
            $promo_data = $this->at_site->findPromoByCode($code_promo, $supplier_id, $company->id);

            $res = [
                'totalQty'    => $totalQty,
                'totalAmount' => $totalAmount,
                'totalPoint'  => $totalPoint
            ];

            if ($promo_data) {
                $arr  = $this->check_promo($promo_data, $totalAmount, $auth->company->id, true);
                if ($arr['status'] == true) {
                    $disc = 0;
                    if ($promo_data->tipe == 0) { //jika persentase
                        $disc       = ($promo_data->value * $totalAmount) / 100;
                        if ($disc > $promo_data->max_total_disc) {
                            $disc   = $promo_data->max_total_disc;
                        }
                    } else {
                        $disc       = (float) $promo_data->value;
                    }
                    $promo_data->value      = $disc;
                    (int)$total_pembayaran  = ($totalAmount - $disc);
                } else {
                    $status         = $arr['msg'];
                    $promo_data     = null;
                }
            } else {
                $status             = 'Kode promo ' . $code_promo . 'tidak tersedia !';
            }

            $response = [
                'data'             => $res,
                'status_promo'     => $status,
                'promo_data'       => $promo_data,
                'total_pembayaran' => $total_pembayaran
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan perubahan pada item dari keranjang", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_checkout_get()
    {
        $this->db->trans_begin();
        try {
            $auth                 = $this->authorize();

            $supplier_id          = $this->input->get('id_distributor');
            $price_group_id       = $this->input->get('price_group_id');
            $company_address_id   = $this->input->get('company_address_id');
            $code_promo           = $this->input->get('promo');

            if (!$supplier_id) {
                throw new Exception("Params `id_distributor` is required", 404);
            }

            $cart           = $this->at_site->getProductInCart($supplier_id, $auth->user->id, $price_group_id);

            if (!$cart) {
                throw new \Exception("Keranjang Belanja kosong, masukkan item terlebih dahulu", 400);
            }

            $company      = $this->at_site->findCompany($company_address_id == null ? $auth->user->company_id : $company_address_id);
            $supplier     = $this->at_site->findCompany($supplier_id);

            if (!$company && !$supplier) {
                throw new \Exception("Gagal melakukan pengambilan detail checkout", 400);
            }

            $data_company = [
                'id'          => $company->id,
                'nama_toko'   => $company->company,
                'nama'        => $company->name,
                'no_tlp'      => $company->phone,
                'alamat'      => trim($company->address),
                'desa'        => ucwords(strtolower($company->village)),
                'kecamatan'   => ucwords(strtolower($company->state)),
                'kabupaten'   => ucwords(strtolower($company->city)),
                'provinsi'    => ucwords(strtolower($company->country)),
                'kode_pos'    => $company->postal_code
            ];

            $distributor = [
                'id_distributor'    => $supplier->id,
                'kode_distributor'  => $supplier->cf1 && is_numeric($supplier->cf1) ? str_pad($supplier->cf1, 10, '0', STR_PAD_LEFT) : $supplier->id,
                'nama_distributor'  => $supplier->company
            ];

            $delivery_methode = ['delivery', 'pickup'];

            $totalQty       = 0;
            $totalAmount    = 0;
            $arrJson        = [];
            foreach ($cart as $item) {
                $arrJson[]    = (array)$item;
                $totalQty     += $item->cart_qty;
                $totalAmount  += $item->price * $item->cart_qty;
                if ($item->price > 0) {
                    $price = $item->price;
                }
                $product[] = [
                    'product_id'    => $item->id,
                    'image'         => url_image_thumb($item->thumb_image) ?? base_url('assets/uploads/no_image.png'),
                    'nama_product'  => $item->name,
                    'code_product'  => $item->code,
                    'harga_product' => (int)$price,
                    'quantity'      => (int)$item->cart_qty,
                    'satuan'        => convert_unit($this->__unit($item->sale_unit)),
                    'jumlah_harga'  => $item->price * $item->cart_qty
                ];
            }

            $ringkasan = [
                'jumlah_barang' => $totalQty,
                'total_harga'   => $totalAmount
            ];

            $company      = $this->at_company->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);
            $promo_data   = $this->at_site->findPromoByCode($code_promo, $supplier_id, $company->id);

            if (count($promo_data) > 0) {
                $arr  = $this->check_promo($promo_data, $totalAmount, $auth->company->id, true);
                if ($arr['status'] == true) {
                    $disc = 0;
                    if ($promo_data->tipe == 0) { //jika persentase
                        $disc = ($promo_data->value * $totalAmount) / 100;
                        if ($disc > $promo_data->max_total_disc) {
                            $disc = $promo_data->max_total_disc;
                        }
                    } else {
                        $disc = (float)$promo_data->value;
                    }
                    $diskon = [
                        'code_promo'        => $promo_data->code_promo,
                        'potongan_harga'    => $disc,
                        'total_pebayaran'   => (int)($totalAmount - $disc)
                    ];
                }
            }

            $response = [
                'alamat_pengiriman' => $data_company,
                'distributor'       => $distributor,
                'pengiriman'        => $delivery_methode,
                'ringkasan'         => $ringkasan,
                'diskon'            => $diskon,
                'list_product'      => $product
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan detail checkout", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function checkout_post()
    {
        $this->db->trans_begin();
        try {
            $this->authorize();

            $config = [
                [
                    'field' => 'delivery_date',
                    'label' => 'Delivery Date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'delivery_method',
                    'label' => 'Delivery Method',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $delivery_date        = $this->body('delivery_date');
            $company_address_id   = $this->body('company_id');
            $note                 = $this->body('note');
            $delivery_method      = $this->body('delivery_method');

            $response = [
                'delivery_date'      => $delivery_date,
                'company_address_id' => $company_address_id,
                'note'               => $note,
                'delivery_method'    => $delivery_method,
                'is_checkout'        => true
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan aksi checkout", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_payment_get()
    {
        $this->db->trans_begin();
        try {
            $auth                     = $this->authorize();
            $is_checkout              = $this->input->get('is_checkout');
            if (!$is_checkout) {
                throw new Exception("Params `is_checkout` is required", 404);
            }
            $supplier_id              = $this->input->get('id_distributor');
            if (!$supplier_id) {
                throw new Exception("Params `id_distributor` is required", 404);
            }
            $price_group_id           = $this->input->get('price_group_id');

            $delivery_method          = $this->input->get('delivery_method');
            if (!$delivery_method) {
                throw new Exception("Params `delivery_method` is required", 404);
            }
            $promo                    = $this->input->get('promo');
            $totalAmount              = 0;
            $totalAmountTempo         = 0;
            $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);
            $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

            if ($get_customer_warehouse) {
                $warehouse_id         = $get_customer_warehouse->default;
            } else {
                $warehouse_id         = $this->at_site->findCompanyWarehouse($supplier_id)->id;
            }
            $warehouse                = $this->at_site->getWarehouseByID($warehouse_id, $supplier_id);
            $totalShipmentPrice       = 0;

            $cart                     = $this->at_site->getProductInCart($supplier_id, $auth->user->id, $price_group_id);

            if (!$cart) {
                throw new \Exception("Keranjang Belanja kosong, masukkan item terlebih dahulu", 400);
            }

            foreach ($cart as $item) {
                $shipmentPrice = 0;
                if ($warehouse->shipment_price_group_id) {
                    $objShipmentPrice = $this->at_site->getShipmentProductPriceByShipmentPriceGroupIdAndProductId($warehouse->shipment_price_group_id, $item->id);
                    if ($delivery_method == 'pickup') {
                        $shipmentPrice = $objShipmentPrice->price_pickup;
                    } elseif ($delivery_method == 'delivery') {
                        $shipmentPrice = $objShipmentPrice->price_delivery;
                    }
                }

                $supplierProduct        = $this->product->getProductByID($item->id, $supplier_id, $price_group_id, $auth->company->id);

                $price                  = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_price && $supplierProduct->group_price > 0 ? $supplierProduct->group_price : $supplierProduct->price);
                $totalAmount            += ($price) * $item->cart_qty;

                $priceTempo             = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_kredit && $supplierProduct->group_kredit > 0 ? $supplierProduct->group_kredit : ($supplierProduct->credit_price && $supplierProduct->credit_price > 0 ? $supplierProduct->credit_price : $supplierProduct->price));

                $totalAmountTempo       += $priceTempo * $item->cart_qty;
                if ($shipmentPrice != 0) {
                    $totalShipmentPrice = $shipmentPrice * $item->cart_qty;
                }
            };

            $company    = $this->at_company->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);
            $promo_data = $this->at_site->findPromoByCode($promo, $supplier_id, $company->id);
            $disc       = 0;
            $arr        = $this->check_promo($promo_data, $totalAmount, $auth->company->id, true);

            if ($arr['status'] == true) {
                if ($promo_data->tipe == 0) {
                    $disc = ($promo_data->value * $totalAmount) / 100;
                    $discTempo = ($promo_data->value * $totalAmountTempo) / 100;
                    if ($disc > $promo_data->max_total_disc) {
                        $disc = $promo_data->max_total_disc;
                    }

                    if ($discTempo > $promo_data->max_total_disc) {
                        $discTempo = $promo_data->max_total_disc;
                    }
                } else {
                    $disc         = (float) $promo_data->value;
                    $discTempo    = $disc;
                }
            }

            $purchase = [
                'grand_total'           => $totalAmount,
                'grand_total_tempo'     => $totalAmountTempo,
                'total_discount'        => $disc,
                'total_discount_tempo'  => $discTempo,
                'charge'                => $totalShipmentPrice,
                'paid'                  => 0,
                'reference_no'          => $this->at_site->getReference('po')
            ];

            $purchase['balance']        = $purchase['grand_total'];
            $banks                      = $this->bank->getAllBank($supplier_id);

            $bayar_ditempat             = $this->bayar_ditempat($purchase);
            if (!$bayar_ditempat) {
                throw new \Exception("Gagal melakukan pengambilan data pembayaran bayar ditempat", 400);
            }
            $tempo_dengan_distributor   = $this->tempo_dengan_distributor($purchase, $banks, $supplier_id);
            if (!$tempo_dengan_distributor) {
                throw new \Exception("Gagal melakukan pengambilan data pembayaran bayar sebelum dikirm", 400);
            }
            $bayar_sebelum_dikirim      = $this->bayar_sebelum_dikirim($purchase, $banks);
            if (!$bayar_sebelum_dikirim) {
                throw new \Exception("Gagal melakukan pengambilan data pembayaran bayar sebelum dikirm", 400);
            }
            $kredit_pro                 = $this->kredit_pro($purchase, $supplier_id);
            if (!$kredit_pro) {
                throw new \Exception("Gagal melakukan pengambilan data pembayaran kredit pro", 400);
            }

            $payment_methods            = $this->payment->getPaymentMethodByCompanyId($supplier_id);
            foreach ($payment_methods as $key) {
                if ($key->value == 'cash on delivery') {
                    $ditempat           = $bayar_ditempat;
                } else if ($key->value == 'kredit') {
                    $tempo              = $tempo_dengan_distributor;
                } else if ($key->value == 'cash before delivery') {
                    $sebelum_dikirim    = $bayar_sebelum_dikirim;
                } else if ($key->value == 'kredit_pro') {
                    $kreditpro          = $kredit_pro;
                }
            }

            $response = [
                'uuid'                      => getUuid(),
                'bayar_ditempat'            => $ditempat,
                'tempo_dengan_distributor'  => $tempo,
                'bayar_sebelum_dikirim'     => $sebelum_dikirim,
                'kredit_pro'                => $kreditpro
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar pembayaran", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_order_get()
    {
        $this->db->trans_begin();
        try {
            $auth   = $this->authorize();
            $limit  = $this->input->get('limit');
            $offset = $this->input->get('offset');
            $search = $this->input->get('search') ?? null;
            $type   = $this->input->get('status');

            if ($type) {
                if (strtoupper($type) == 'PROSES') {
                    $rows_ongoing       = $this->at_purchase->getRowsOrdersOnGoing($auth->user->id, $search);
                    $orders_on_going    = $this->at_purchase->getOrdersOnGoing($auth->user->id, $limit, $offset, $search);
                    if (!$orders_on_going) {
                        $data_ongoing = "Tidak ada pesanan dalam proses";
                    } else {
                        $output_proses = [];
                        foreach ($orders_on_going as $order) {
                            $output_proses[]    = $this->list_order_card($order);
                        }
                        $data_ongoing = [
                            'rows'                     => $rows_ongoing,
                            'jumlah_pesanan_proses'    => count($orders_on_going),
                            'list_order_dalam_proses'  => $output_proses
                        ];
                    }
                    $response = [
                        'order_dalam_proses' => $data_ongoing
                    ];
                } else if (strtoupper($type) == 'SELESAI') {
                    $rows_complete    = $this->at_purchase->getRowsOrdersComplete($auth->user->id, $search);
                    $orders_completed = $this->at_purchase->getOrdersComplete($auth->user->id, $limit, $offset, $search);
                    if (!$orders_completed) {
                        $data_complete = "Tidak ada pesanan yang telah selesai";
                    } else {
                        $output_selesai = [];
                        foreach ($orders_completed as $order) {
                            $output_selesai[]   = $this->list_order_card($order);
                        }
                        $data_complete = [
                            'rows'                     => $rows_complete,
                            'jumlah_pesanan_selesai'   => count($output_selesai),
                            'list_order_dalam_proses'  => $output_selesai
                        ];
                    }
                    $response = [
                        'order_selesai'      => $data_complete
                    ];
                } else {
                    throw new \Exception("Gagal melakukan pengambilan daftar pemesanan status tidak ditemukan", 400);
                }
            } else {
                $rows_ongoing       = $this->at_purchase->getRowsOrdersOnGoing($auth->user->id, $search);
                $orders_on_going    = $this->at_purchase->getOrdersOnGoing($auth->user->id, $limit, $offset, $search);
                if (!$orders_on_going) {
                    $data_ongoing = "Tidak ada pesanan dalam proses";
                } else {
                    $output_proses = [];
                    foreach ($orders_on_going as $order) {
                        $output_proses[]    = $this->list_order_card($order);
                    }
                    $data_ongoing = [
                        'rows'                     => $rows_ongoing,
                        'jumlah_pesanan_proses'    => count($orders_on_going),
                        'list_order_dalam_proses'  => $output_proses
                    ];
                }
                $rows_complete    = $this->at_purchase->getRowsOrdersComplete($auth->user->id, $search);
                $orders_completed = $this->at_purchase->getOrdersComplete($auth->user->id, $limit, $offset, $search);
                if (!$orders_completed) {
                    $data_complete = "Tidak ada pesanan yang telah selesai";
                } else {
                    $output_selesai = [];
                    foreach ($orders_completed as $order) {
                        $output_selesai[]   = $this->list_order_card($order);
                    }
                    $data_complete = [
                        'rows'                     => $rows_complete,
                        'jumlah_pesanan_selesai'   => count($output_selesai),
                        'list_order_dalam_proses'  => $output_selesai
                    ];
                }

                $response = [
                    'order_dalam_proses' => $data_ongoing,
                    'order_selesai'      => $data_complete
                ];
            }
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar pemesanan", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_order_get()
    {
        $this->db->trans_begin();
        try {
            $auth   = $this->authorize();
            $id_pemesanan = $this->input->get('id_pemesanan');

            if (!$id_pemesanan) {
                throw new Exception("Params `id_pemesanan` is required", 404);
            }

            $order = $this->at_purchase->getOrderItems($id_pemesanan, $auth->user->id);
            if (!$order) {
                throw new \Exception("Pesanan tidak ditemukan atau tidak memiliki akses untuk melihat pesanan tersebut.", 400);
            }

            $payment_pending        = $this->payment->getPaymentPending($id_pemesanan);
            $sale                   = $this->at_sale->findSalesByReferenceNo($order->cf1, $order->supplier_id);
            $payment_total          = $this->payment->getTotalPaymentByPoId($order->id)->total;
            $payments_temp          = $this->payment->getListPaymentTemp($id_pemesanan);

            $deliveries             = $this->at_sale->getDeliveriesItems($order->supplier_id, $order->cf1);
            $company                = $this->at_site->findCompany($order->company_id);
            $distributor            = $this->at_site->findCompany($order->supplier_id);
            $param                  = $order->payment_method == 'kredit_pro' ? 1 : 0;

            if ($order->payment_type != '' && $order->payment_type != null) {
                $jenis_pembayaran   = $order->payment_type;
            }

            if ($order->status == "received" && $order->payment_status == "paid") {
                $url_invoice        = base_url(aksestoko_route("aksestoko/order/invoice/")) . $sale->id . "/" . $order->id;
            }

            if ($distributor->cf2 == 'SID') {
                if ($sale->is_updated_price == 2) {
                    if (($order->grand_total > 0) && ($order->grand_total > $payment_total && $order->status != "canceled") && $payment_pending) {
                        if (in_array($order->payment_method, $this->data['array_payment_method'])) {
                            if ($order->status == "received") {
                                $konfirm_pembayaran = true;
                            } else if (($order->status == 'confirmed' || $order->status == 'received' || $order->status == 'partial') && $order->payment_method != 'kredit_pro') {
                                $konfirm_pembayaran = true;
                            } elseif ($order->payment_method == 'kredit_pro' && ($order->status == 'confirmed' || $order->status == 'received') && ($order->payment_status == 'pending' || $order->payment_status == 'reject')) {
                                $konfirm_pembayaran = true;
                            }
                        }
                    }
                }
            } else if (in_array($distributor->cf2, ['BIG', 'JBU', 'BPP'])) {
                $konfirm_pembayaran = false;
            } else {
                if (($order->grand_total > 0) && ($order->grand_total > $payment_total && $order->status != "canceled") && $payment_pending) {
                    if (in_array($order->payment_method, $this->data['array_payment_method'])) {
                        if ($order->status == "received") {
                            $konfirm_pembayaran = true;
                        }
                    } else if (($order->status == 'confirmed' || $order->status == 'received' || $order->status == 'partial') && $order->payment_method != 'kredit_pro') {
                        $konfirm_pembayaran = true;
                    } elseif ($order->payment_method == 'kredit_pro' && ($order->status == 'confirmed' || $order->status == 'received') /*&& ($order->payment_status == 'pending' || $order->payment_status == 'reject')*/) {
                        if ($order->payment_status == 'pending') {
                            $ajukan_kredit = true;
                        } else if ($order->payment_status == 'reject') {
                            // $pilih_metode_pembayaran = true;
                        }
                        /**/
                    }
                }
            }
            // var_dump($order->payment_status);die;

            if ($this->payment->getPaymentTempByPurchaseId($order->id)) {
                $daftar_pembayaran = true;
            }

            $totalPaymentAccepted = 0;
            foreach ($payments_temp as $i => $payment_temp) {
                $totalPaymentAccepted += ($payment_temp->status == "accept" ? (int) $payment_temp->nominal : 0);
            }
            $kredit_pro = [];

            if ($order->payment_method == "kredit_pro" && in_array($order->payment_status, ['accept', 'paid', 'partial'])) {
                $diffPayment = ($totalPaymentAccepted / $order->grand_total) * 100;
                $kredit_pro = [
                    'image'        => base_url('assets/images/kreditpro.png'),
                    'progress'     => $diffPayment . '%',
                    'proses'       => $diffPayment == 100 ? "Lunas" : "",
                    'perbandingan' => (int)$totalPaymentAccepted . '/' . (int)$order->grand_total
                ];
            }

            $totalReceived    = 0;
            $totalOrdered     = 0;
            $totalLeft        = 0;
            $penerimaan = [];
            foreach ($order->items as $r) {
                $unit = $this->__unit($r->product_unit_id);
                $totalOrdered   += $r->quantity;
                $totalReceived  += $r->quantity_received;
                $totalLeft      += ($r->quantity - $r->quantity_received);

                $detail_diterima = [
                    'barang_baik'   => (int) $r->good_quantity,
                    'barang_rusak'  => (int) $r->bad_quantity
                ];

                $penerimaan[] = [
                    'id_barang'         => $r->product_id,
                    'nama_barang'       => $r->product_name,
                    'kode_barang'       => $r->product_code,
                    'jumlah_pesanan'    => (int) $r->quantity,
                    'jumlah_diterima'   => (int) $r->quantity_received,
                    'detail_diterima'   => $detail_diterima,
                    'sisa_pesanan'      => (int) ($r->quantity - $r->quantity_received),
                    'satuan'            => convert_unit($unit)
                ];
            }

            $data_pemesanan = [
                'id_pemesanan'              => $order->id,
                'no_pemesanan'              => $order->cf1,
                'id_bk'                     => str_replace("IDC-", "", $company->cf1),
                'tanggal_peesanan'          => $this->__convertDate($order->date),
                'ekpestasi'                 => $this->__convertDate($order->shipping_date),
                'kode_distributor'          => $distributor->cf1  && is_numeric($distributor->cf1) ? str_pad($distributor->cf1, 10, '0', STR_PAD_LEFT) : $order->supplier_id,
                'nama_distributor'          => $order->supplier,
                'cara_pengiriman'           => $this->__status($order->delivery_method)[0],
                'cara_pembayaran'           => $this->__status($order->payment_method)[0],
                'jenis_pembayaran'          => $jenis_pembayaran,
                'notifikasi_pemesanan'      => $this->__status($order->status)[1],
                'status_pemesanan'          => $this->__status($order->status)[0],
                'notifikasi_pembayaran'     => $this->__status($order->payment_status, $param)[1],
                'status_pembayaran'         => $this->__status($order->payment_status, $param)[0],
                'url_invoice'               => $url_invoice,
                'konfirmasi_pembayaran'     => $konfirm_pembayaran,
                'daftar_pembayaran'         => $daftar_pembayaran,
                'pilih_metode_pembayaran'   => $pilih_metode_pembayaran,
                'ajukan_kredit'             => $ajukan_kredit
            ];

            if ($order->grand_total > 0) {
                $harga = (int)$order->total;
            }
            if ($order->total_discount != 0) {
                $notif_discount = $order->total_discount >= 0 ? 'success' : 'danger';
                $discount = (int)$order->total_discount;
            }
            if ($order->charge < 0) {
                $notifCharge = 'Potongan Harga';
            } else {
                $notifCharge = 'Biaya Lain-lain';
            }
            if ($order->charge != 0) {
                $labelCharge = $order->charge <= 0 ? 'success' : 'danger';
            }
            if ($order->correction_price < 0) {
                $notifCorrection = 'Pengurangan Harga';
            } else {
                $notifCorrection = 'Penambahan Harga';
            }
            if ($order->correction_price != 0) {
                $labelcorrection = $order->correction_price <= 0 ? 'success' : 'danger';
            }
            if ($order->charge_third_party != 0) {
                $labelthirdparty = $order->charge_third_party <= 0 ? 'success' : 'danger';
            }
            $ringkasan = [
                'jumlah_pesanan'        => $totalOrdered,
                'jumlah_diterima'       => $totalReceived,
                'sisa_pesanan'          => $totalLeft,
                'total_harga'           => $harga,
                'label_discount'        => $notif_discount,
                'discount'              => $discount,
                'label_charge'          => $labelCharge,
                'notifikasi_charge'     => $notifCharge,
                'charge'                => ($order->charge < 0 ? '-' : ' ') . (int)abs($order->charge),
                'label_correction'      => $labelcorrection,
                'notifikasi_correction' => $notifCorrection,
                'correction'            => ($order->correction_price < 0 ? '-' : ' ') . (int)(abs($order->correction_price)),
                'label_third_party'     => $labelthirdparty,
                'charge_third_party'    => ($order->charge_third_party < 0 ? '-' : ' ') . (int)(abs($order->charge_third_party)),
                'total_pembayaran'      => (int)$order->grand_total
            ];
            $delivery = [];
            foreach ($deliveries as $delivery) {
                $konfirmasi_penerimaan  = null;
                $konfirmasi_bad_qty     = null;
                if ($delivery->receive_status == "received") {
                    $status         = 'Barang Diterima';
                    $label_status   = 'success';
                } else {
                    $status         = $this->__status($delivery->status)[0];
                    $label_status   = $this->__status($delivery->status)[1];
                }
                if ($delivery->receive_status != "received" && $delivery->status != "packing") {
                    $konfirmasi_penerimaan = true;
                } else if ($delivery->receive_status == "received") {
                    if ($sale->sale_type == "booking" && $delivery->is_reject == 1) {
                        $konfirmasi_bad_qty    = true;
                    } elseif ($delivery->spj_file) {
                        $url_spj = $delivery->spj_file;
                    }
                }
                $detail_delivery = [];
                foreach ($delivery->items as $item) {
                    $good           = null;
                    $bad            = null;
                    $unit           = $this->__unit($item->product_unit_id);
                    $product_name   = $item->product_name;
                    $product_code   = $item->product_code;
                    $jumlah         = (int) $item->quantity_sent;
                    if ($delivery->receive_status == "received") {
                        $good       = (int) $item->good_quantity;
                        $bad        = (int) $item->bad_quantity;
                    }
                    $detail_delivery[] = [
                        'delivery_item_id' => $item->id,
                        'id_product'    => $item->product_id,
                        'nama_product'  => $product_name,
                        'kode_product'  => $product_code,
                        'jumlah'        => $jumlah,
                        'satuan'        => convert_unit($unit),
                        'baik'          => $good,
                        'buruk'         => $bad,
                    ];
                }
                $deliveri[] = [
                    'id_delivery'            => $delivery->id,
                    'no_spj'                 => $delivery->do_reference_no,
                    'label_status'           => $label_status,
                    'statu_pengiriman'       => $status,
                    'tanggal_dikirim'        => $this->__convertDate($delivery->date),
                    'dikirim_oleh'           => strlen($delivery->delivered_by) > 0 ? $delivery->delivered_by : "−",
                    'konfirmasi_penerimaan'  => $konfirmasi_penerimaan,
                    'konfirmasi_bad_qty'     => $konfirmasi_bad_qty,
                    'url_spj'                => $url_spj,
                    'jumlah_detail_delivery' => count($delivery->items),
                    'list_detail_delivery'   => $detail_delivery
                ];
            }
            $pengiriman = [
                'id_toko'           => $company->id,
                'nama_toko'         => $company->company,
                'nama'              => $company->name,
                'no_tlp'            => $company->phone,
                'alamat'            => $company->address,
                'kecamatan'         => ucwords(strtolower($company->state)),
                'kabupaten'         => ucwords(strtolower($company->city)),
                'provinsi'          => ucwords(strtolower($company->country)),
                'kode_pos'          => $company->postal_code,
                'jumlah_deliveries' => count($deliveries),
                'list_deliveries'   => $deliveri
            ];

            $totalQty           = 0;
            $totalAmount        = 0;
            $belanja = [];
            foreach ($order->items as $i => $item) {
                $totalQty       += $item->quantity;
                $totalAmount    += $item->subtotal;
                $product        = $this->product->getProductByCodeAndSupplierId($item->product_code, $order->supplier_id);
                if ($item->unit_cost > 0) {
                    $unit_cost  = $item->unit_cost;
                }
                if ($item->subtotal > 0) {
                    $harga  = $item->subtotal;
                }
                $belanja[] = [
                    'id_produk'     => $item->id,
                    'image_produk'  => url_image_thumb($product->thumb_image) ?? base_url('assets/uploads/no_image.png'),
                    'nama_produk'   => $item->product_name,
                    'kode_produk'   => $item->product_code,
                    'harga'         => (int)$unit_cost,
                    'quantity'      => (int) $item->quantity,
                    'satuan'        => convert_unit($this->__unit($product->unit)),
                    'total_harga'   => (int)$harga,

                ];
            }
            $belanjaan  = [
                'jumlah_belanja' => count($order->items),
                'list_belanja'   => $belanja
            ];

            if ($order->payment_deadline) {

                $now                = now();
                $end_date           = strtotime(date('Y-m-d', strtotime($order->payment_deadline)));
                $datediff           = $now - $end_date;
                $duration           = round($datediff / (60 * 60 * 24));

                if ($duration < -3 && $duration > -7) {
                    $pesan = 'Warning';
                } elseif ($duration > -3) {
                    $pesan = 'Danger';
                } else {
                    $pesan = 'Info';
                }
            }

            if ($order->payment_status != 'paid' && $order->status != 'canceled' && in_array($order->payment_method, array_merge($this->data['array_payment_method'], ["kredit_pro"])) && $order->payment_deadline != null) {
                $info_1 = 'Sisa Durasi Waktu Pembayaran :' . $duration . ' Hari';
            }

            if ($order->grand_total == 0 && $sale->sale_status != 'closed') {
                $info_2 = 'Seluruh harga dan total pembayaran akan ditampilkan ketika Sales/Distibutor telah mengonfirmasi';
            }

            if ($sale->is_updated_price == 1) {
                if ($sale->charge != 0 || $order->total_discount >= 0) {
                    $info_3                     = 'Harga pesanan telah diperbarui oleh distributor. Apakah Anda menyetujuinya?';
                    $konfirmasi_harga_pesanan   = true;
                }
            }

            if ($sale->charge != 0 || $order->status == "canceled") {
                if ($order->status == "canceled") {
                    $info_4 = 'Pemesanan telah dibatalkan oleh ' . $order->created_by != $order->updated_by ? 'distributor' : 'toko';
                    if (strlen($sale->reason)) {
                        $info_4 .= 'Dengan alsan ' . $this->sma->decode_html($sale->reason);
                    }
                } else {
                    $info_4 = 'Total Pembayaran telah diperbarui oleh distributor menjadi Rp ' . (int)$order->grand_total;
                    if ($order->charge != 0) {
                        $info_4 .= $order->charge > 0 ? ' Biaya lain-lain ' : ' Potongan harga ';
                        $info_4 .= 'sebesar ' . (int)abs($order->charge);
                    }
                    if ($order->correction_price != 0) {
                        $info_4 .= $order->correction_price > 0 ? ' Penambahan harga ' : ' Pengurangan harga ';
                        $info_4 .= 'sebesar ' . (int)abs($order->correction_price);
                    }
                    if (strlen($sale->reason)) {
                        $info_4 .= ' Dengan alasan :' . $this->sma->decode_html($sale->reason);
                    }
                    if ($order->charge_third_party != 0) {
                        $info_4 .= ' Biaya Kredit : Rp ' . (int)abs($order->charge_third_party);
                    }
                }
            }

            $response = [
                'detail_pemesanan'          => $data_pemesanan,
                'pembayaran_kredit_pro'     => $kredit_pro,
                'penerimaan'                => $penerimaan,
                'pengiriman'                => $pengiriman,
                'ringkasan'                 => $ringkasan,
                'daftar_belanja'            => $belanjaan,
                'pesan'                     => $pesan,
                'info_1'                    => $info_1,
                'info_2'                    => $info_2,
                'info_3'                    => $info_3,
                'info_4'                    => $info_4,
                'konfirmasi_harga_pesanan'  => $konfirmasi_harga_pesanan
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan detail pemesanan", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_promo_post()
    {
        $this->db->trans_begin();
        try {
            $auth   = $this->authorize();
            $config = [
                [
                    'field' => 'code_promo',
                    'label' => 'Code Promo',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'id_distributor',
                    'label' => 'Distributor ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $code_promo       = $this->body('code_promo');
            $supplier_id      = $this->body('id_distributor');
            $price_group_id   = $this->body('price_group_id');

            $company          = $this->at_company->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);
            $promo_data       = $this->at_site->findPromoByCode($code_promo, $supplier_id, $company->id);
            if (!$promo_data) {
                throw new \Exception('Kode promo ' . $code_promo . 'tidak tersedia !', 400);
            }
            $total_pembelian    = 0;
            $cart               = $this->at_site->getProductInCart($supplier_id, $auth->user->id, $price_group_id);
            if (!$cart) {
                throw new \Exception("Keranjang Belanja kosong, masukkan item terlebih dahulu", 400);
            }
            foreach ($cart as $key => $value) {
                $total_pembelian += ($value->price * $value->cart_qty);
            }

            $this->check_promo($promo_data, $total_pembelian, $auth->company->id);

            $disc = 0;
            if ($promo_data->tipe == 0) { //jika persentase
                $disc           = ($promo_data->value * $total_pembelian) / 100;
                if ($disc > $promo_data->max_total_disc) {
                    $disc       = $promo_data->max_total_disc;
                }
            } else {
                $disc           = (float) $promo_data->value;
            }
            $promo_data->value = $disc;
            $response = [
                "promo_data" => $promo_data
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan penmabahan kode promo", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function action_checkout_post()
    {
        $this->db->trans_begin();
        try {
            $auth   = $this->authorize();
            $config = [
                [
                    'field' => 'id_distributor',
                    'label' => 'Distributor ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'delivery_date',
                    'label' => 'Delivery Date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'delivery_method',
                    'label' => 'Delivery Method',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'company_address_id',
                    'label' => 'Company Address ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'payment_method',
                    'label' => 'Payment Method',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ],
                [
                    'field' => 'uuid',
                    'label' => 'UUID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $supplier_id        = $this->body('id_distributor');
            $price_group_id     = $this->body('price_group_id');
            $payment_method     = $this->body('payment_method');
            $delivery_date      = $this->body('delivery_date');
            $delivery_method    = $this->body('delivery_method');
            $uuid_sales         = $this->body('uuid');
            $code_promo         = $this->body('code_promo');
            $company_address_id = $this->body('company_address_id');
            $note               = $this->body('note');
            $bank_id            = $this->body('bank_id');
            $created_device     = $this->body('created_device') ?? 'Aksestoko Mobile';
            $cart               = $this->at_site->getProductInCart($supplier_id, $auth->user->id, $price_group_id);
            if (!$cart) {
                throw new \Exception("Keranjang Belanja kosong, masukkan item terlebih dahulu", 400);
            }
            $supplier           = $this->at_site->getCompanyByID($supplier_id);
            $this->Owner        = true;

            if ($uuid = $this->site->isUuidExist($uuid_sales, 'sales')) {
                throw new Exception("UUID $uuid is exist.");
            }
            $total                    = 0;
            $total_items              = 0;
            $countProduct             = count($cart);
            $price_type               = 'cash';

            $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);
            $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);
            if ($get_customer_warehouse) {
                $warehouse_id         = $get_customer_warehouse->default;
            } else {
                $warehouse_id         = $this->at_site->findCompanyWarehouse($supplier_id)->id;
            }
            $warehouse                = $this->at_site->getWarehouseByID($warehouse_id, $supplier_id);

            for ($i = 0; $i < $countProduct; $i++) {
                $supplierProduct              = $this->product->getProductByID($cart[$i]->id, $supplier_id, $price_group_id, $auth->company->id);
                $product                      = $this->at_site->findRelationProduct($supplierProduct, $this->at_site->getCompanyByID($auth->company->id));

                $supplierProduct->price       = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_price && $supplierProduct->group_price > 0 ? $supplierProduct->group_price : $supplierProduct->price);
                if ($payment_method == 'kredit') {
                    $supplierProduct->price   = $supplierProduct->price_sale && $supplierProduct->price_sale > 0 ? $supplierProduct->price_sale : ($supplierProduct->group_kredit && $supplierProduct->group_kredit > 0 ? $supplierProduct->group_kredit : ($supplierProduct->credit_price && $supplierProduct->credit_price > 0 ? $supplierProduct->credit_price : $supplierProduct->price));
                    $price_type               = 'credit';
                }
                $unit             = $this->product->getUnit($supplierProduct->sale_unit);
                $quantity         = $cart[$i]->cart_qty;
                $quantity         = $this->__operate($quantity, $unit->operation_value, $unit->operator);

                $price            = $supplierProduct->price;
                $subtotal         = ($quantity * $price);
                $shipmentPrice    = 0;
                if ($warehouse->shipment_price_group_id) {
                    $objShipmentPrice = $this->at_site->getShipmentProductPriceByShipmentPriceGroupIdAndProductId($warehouse->shipment_price_group_id, $supplierProduct->id);
                    if ($delivery_method == 'pickup') {
                        $shipmentPrice = $objShipmentPrice->price_pickup;
                    } elseif ($delivery_method == 'delivery') {
                        $shipmentPrice = $objShipmentPrice->price_delivery;
                    }
                }
                $price          += $shipmentPrice;
                $subtotal       += ($shipmentPrice * $quantity);
                $total_items    += $quantity;
                $total          += $subtotal;

                //For Sales Order
                $requestProductsSO[] = [
                    'sale_id'           => null,
                    'product_id'        => $supplierProduct->id,
                    'product_code'      => $supplierProduct->code,
                    'product_name'      => $supplierProduct->name,
                    'product_type'      => $supplierProduct->type,
                    'option_id'         => null,
                    'net_unit_price'    => $price,
                    'unit_price'        => (int) $price,
                    'quantity'          => (int) $quantity,
                    'warehouse_id'      => $warehouse_id,
                    'item_tax'          => 0,
                    'tax_rate_id'       => 0,
                    'tax'               => 0,
                    'discount'          => null,
                    'item_discount'     => 0,
                    'subtotal'          => $subtotal,
                    'serial_no'         => null,
                    'real_unit_price'   => $price,
                    'sale_item_id'      => null,
                    'product_unit_id'   => $supplierProduct->unit,
                    'product_unit_code' => ($this->at_site->findUnit($supplierProduct->unit))->code,
                    'unit_quantity'     => $quantity,
                    'client_id'         => null,
                    'flag'              => null,
                    'is_deleted'        => null,
                    'device_id'         => null,
                    'uuid'              => null,
                    'uuid_app'          => null,
                ];
                //For Purchase Order
                $requestProductsPO[] = [
                    'purchase_id'         => null,
                    'transfer_id'         => null,
                    'product_id'          => $product->id,
                    'product_code'        => $product->code,
                    'product_name'        => $product->name,
                    'option_id'           => null,
                    'net_unit_cost'       => $price,
                    'quantity'            => $quantity,
                    'warehouse_id'        => $this->at_site->findCompanyWarehouse($auth->company->id)->id,
                    'item_tax'            => 0,
                    'tax_rate_id'         => 0,
                    'tax'                 => 0,
                    'discount'            => null,
                    'item_discount'       => 0,
                    'expiry'              => null,
                    'subtotal'            => $subtotal,
                    'quantity_balance'    => 0,
                    'date'                => date('Y-m-d H:i:s'),
                    'status'              => 'ordered',
                    'unit_cost'           => $price,
                    'real_unit_cost'      => $price,
                    'quantity_received'   => 0,
                    'supplier_part_no'    => null,
                    'purchase_item_id'    => null,
                    'product_unit_id'     => $product->unit,
                    'product_unit_code'   => ($this->at_site->findUnit($product->unit))->code,
                    'unit_quantity'       => $quantity,
                    'client_id'           => null,
                    'flag'                => null,
                    'is_deleted'          => null,
                    'device_id'           => null,
                    'uuid'                => null,
                    'uuid_app'            => null,
                ];
            }

            $promo_data         = $this->at_site->findPromoByCode($code_promo, $supplier_id, $customer_id->id);

            $disc               = 0;

            if ($promo_data) {
                $arr  = $this->check_promo($promo_data, $total, $auth->company->id, true);
                if ($arr['status'] == true) {
                    if ($promo_data->tipe == 0) { //jika persentase
                        $disc           = ($promo_data->value * $total) / 100;
                        if ($disc > $promo_data->max_total_disc) {
                            $disc       = $promo_data->max_total_disc;
                        }
                    } else {
                        $disc           = (float) $promo_data->value;
                    }
                }
            }

            $company            = ($this->at_site->getCompanyByID($company_address_id));
            $sale_type          = 'booking';
            $so_reference_no    = substr_replace($this->at_site->getReference('so', $supplier_id), "/AT", 4, 0);

            $requestSO = [
                'date'                => date('Y-m-d H:i:s'),
                'reference_no'        => $so_reference_no,
                'customer_id'         => $company_address_id,
                'customer'            => $company->company,
                'biller_id'           => $supplier_id,
                'biller'              => $supplier->company,
                'warehouse_id'        => $warehouse_id,
                'note'                => $this->sma->clear_tags($note),
                'staff_note'          => null,
                'total'               => $total,
                'product_discount'    => null,
                'order_discount_id'   => $disc,
                'total_discount'      => $disc,
                'order_discount'      => $disc,
                'product_tax'         => null,
                'order_tax_id'        => null,
                'order_tax'           => null,
                'total_tax'           => null,
                'shipping'            => null,
                'grand_total'         => $total - $disc,
                'sale_status'         => 'pending',
                'payment_status'      => 'pending',
                'payment_term'        => null,
                'due_date'            => null,
                'created_by'          => $auth->user->id,
                'updated_by'          => $auth->user->id,
                'updated_at'          => date('Y-m-d H:i:s'),
                'total_items'         => $total_items,
                'pos'                 => 0,
                'paid'                => 0,
                'return_id'           => null,
                'surcharge'           => 0,
                'attachment'          => null,
                'return_sale_ref'     => null,
                'sale_id'             => null,
                'return_sale_total'   => 0,
                'rounding'            => null,
                'client_id'           => 'aksestoko',
                'flag'                => null,
                'is_deleted'          => null,
                'device_id'           => trim($company->address) . ", " . ucwords(strtolower($company->village)) . ", " . ucwords(strtolower($company->state)) . ", " . ucwords(strtolower($company->city)) . ", " . ucwords(strtolower($company->country)) . " - " . $company->postal_code,
                'uuid'                => $uuid_sales,
                'uuid_app'            => null,
                'order_id'            => null,
                'mtid'                => null,
                'company_id'          => $supplier_id,
                'delivery_date'       => $this->__delivery_date($delivery_date),
                'delivery_method'     => $delivery_method,
                'sale_type'           => $sale_type,
                'price_type'          => $price_type,
                'created_device'      => $created_device,
                'cf1'                 => "Created from API",
                'cf2'                 => $this->token
            ];
            $requestPO = [
                'reference_no'            => $this->at_site->getReference('po'),
                'date'                    => date('Y-m-d H:i:s'),
                'supplier_id'             => $supplier_id,
                'supplier'                => $supplier->company,
                'warehouse_id'            => $this->at_site->getFirstWarehouseOfCompany($auth->company->id)->id,
                'note'                    => $this->sma->clear_tags($note),
                'total'                   => $total,
                'product_discount'        => null,
                'order_discount_id'       => $disc,
                'order_discount'          => $disc,
                'total_discount'          => $disc,
                'product_tax'             => null,
                'order_tax_id'            => null,
                'order_tax'               => null,
                'total_tax'               => null,
                'shipping'                => null,
                'grand_total'             => $total - $disc,
                'paid'                    => 0,
                'status'                  => 'ordered',
                'payment_status'          => 'pending',
                'created_by'              => $auth->user->id,
                'updated_by'              => null,
                'updated_at'              => date('Y-m-d H:i:s'),
                'attachment'              => null,
                'payment_term'            => null,
                'due_date'                => null,
                'return_id'               => null,
                'surcharge'               => 0,
                'return_purchase_ref'     => null,
                'purchase_id'             => null,
                'return_purchase_total'   => 0,
                'client_id'               => null,
                'flag'                    => null,
                'is_deleted'              => null,
                'device_id'               => null,
                'uuid'                    => null,
                'uuid_app'                => null,
                'company_id'              => $company_address_id,
                'company_head_id'         => $auth->company->id,
                'sino_spj'                => null,
                'sino_do'                 => null,
                'shipping_by'             => null,
                'shipping_date'           => $this->__delivery_date($delivery_date),
                'receiver'                => null,
                'is_watched'              => null,
                'cf1'                     => $so_reference_no,
                'cf2'                     => 'POS',
                'bank_id'                 => $bank_id,
                'payment_method'          => $payment_method,
                'delivery_method'         => $delivery_method,
                'created_device'          => $created_device
            ];

            if ($payment_method == 'kredit') {
                $config = [
                    [
                        'field' => 'payment_durasi',
                        'label' => 'Payment Durasi',
                        'rules' => 'required',
                        'errors' => [
                            'required' => '%s is required',
                        ],
                    ]
                ];

                $this->validate_form($config);
                $customer_group_id                = $this->body('customer_group_id');
                $payment_durasi                   = $this->body('payment_durasi');
                $input_payment_durasi             = $this->body('input_payment_durasi');

                // $kredit_limit                     = $this->payment->getKreditLimit($customer_group_id);
                // $debt                             = $this->payment->getTotalDebt($auth->company->id, $purchase_id, $supplier_id);
                // $sisa_kredit                      = $kredit_limit->kredit_limit - (int) $debt->total;
                $requestPO['payment_duration']    = $payment_durasi == 'other' ? $input_payment_durasi : $payment_durasi;
            }
            if ($sale_type == 'booking') {
                $sales_id       = $this->at_sale->addSaleATBooking($requestSO, $requestProductsSO);
            } else {
                $sales_id       = $this->at_sale->addSaleAT($requestSO, $requestProductsSO);
            }

            if (!$sales_id) {
                throw new \Exception("Gagal melakukan pembuatan sale");
            }

            $requestPO['cf2']   = 'POS-SALE-' . $sales_id;

            $purchase_id        = $this->at_purchase->addPurchaseAT($requestPO, $requestProductsPO);

            if (!$purchase_id) {
                throw new \Exception("Gagal melakukan pembuatan purchase");
            }

            if (count($promo_data) > 0) {
                $requestPromo = [
                    'promo_id'    => $promo_data->id,
                    'company_id'  => $auth->company->id,
                    'date'        => date('Y-m-d H:i:s'),
                    'purchase_id' => $purchase_id
                ];

                if (!$this->promotion->addPromotion($requestPromo)) {
                    throw new \Exception("Tidak bisa memakai promo");
                }
            }

            if ($this->integration->isIntegrated($supplier->cf2)) {
                $requestPO['id']    = $purchase_id;
                $requestSO['id']    = $sales_id;
                $saleItems          = $this->at_sale->getSaleItemsBySaleId($sales_id, true);
                $response           = $this->integration->create_order_integration($supplier->cf2, $this->session->userdata('username'), $requestSO, $saleItems, $requestPO);
                if (!$response) {
                    throw new \Exception("Tidak dapat mengirim order ke distributor");
                }

                $dataSale['cf1']    = $response;
                $dataSale['cf2']    = $supplier->cf2;
                $dataSale['id']     = $sales_id;
                if (!$this->at_sale->updateOrders($dataSale, ['id' => $purchase_id])) {
                    throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                }
            }

            if (!$this->audittrail->insertCustomerCreateOrder($auth->user->id, $auth->company->id, $supplier_id, $sales_id, $purchase_id)) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_create_order");
            }

            if (!$this->at_site->emptyCart($supplier_id, $auth->user->id)) {
                throw new \Exception("Tidak bisa mengosongkan keranjang belanja");
            }

            if (!$this->save_payment($purchase_id, $bank_id, $payment_method, $payment_durasi, $input_payment_durasi)) {
                throw new \Exception("Pembayaran Gagal");
            }

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
            $new_sale       = $this->at_sale->getSalesById($sales_id);
            if (!$new_sale) {
                throw new \Exception("Tidak dapat membuat pesanan. SO dengan ID $sales_id tidak ditemukan.");
            }

            $new_purchase   = $this->at_purchase->getPurchaseByID($purchase_id);
            if (!$new_purchase) {
                throw new \Exception("Tidak dapat membuat pesanan. PO dengan ID $purchase_id tidak ditemukan.");
            }
            /* end-cekID */

            $bank_data        = $this->bank->getBankById($new_purchase->bank_id);

            if ($bank_data && $new_purchase->payment_method != 'cash on delivery') {
                $nama_bank    = strtoupper($bank_data->bank_name);
                $no_rekening  = $bank_data->no_rekening . 'a/n' . $bank_data->name;
            }

            $response = [
                'sale_id'         => $sales_id,
                'purchase_id'     => $purchase_id,
                'id_pemesanan'    => $new_purchase->cf1,
                'cara_pembayaran' => $this->__status($new_purchase->payment_method)[0],
                'bank'            => $nama_bank,
                'no_rekening'     => $no_rekening
            ];

            $this->db->trans_commit();
            /* Start-CekDuplicateNoRef - Fungsi ini sengaja diluar transaction, karena ada case tersendiri.*/
            if (!$this->at_sale->checkDupplicateNoSaleRef($new_sale, $new_purchase, true)) {
                $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil membuat pesanan, Terjadi kesalahan pada saat cek duplikat SO", $response);
            };
            /* End-CekDuplicateNoRef */

            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pembuatan pesanan", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function shipment_group_price_get()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $supplier_id    = $this->input->get('id_distributor');
            $price_group_id = $this->input->get('price_group_id');

            if (!$supplier_id) {
                throw new Exception("Params `id_distributor` is required", 404);
            }

            $delivery_method    = $this->input->get('delivery_method');
            if (!$delivery_method) {
                throw new Exception("Params `delivery_method` is required", 404);
            }

            $cart           = $this->at_site->getProductInCart($supplier_id, $auth->user->id, $price_group_id);

            if (!$cart) {
                throw new \Exception("Keranjang Belanja kosong, masukkan item terlebih dahulu", 400);
            }

            $totalQty         = 0;
            $totalAmount      = 0;
            $arrJson          = [];
            foreach ($cart as $item) {
                // $arrJson[]    = [$item];
                $totalQty     += $item->cart_qty;
                $totalAmount  += $item->price * $item->cart_qty;
            }

            $customer_id              = $this->at_site->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);
            $get_customer_warehouse   = $this->at_site->findWarehouseCustomerByCustomerId($customer_id->id);

            if ($get_customer_warehouse) {
                $warehouse_id         = $get_customer_warehouse->default;
            } else {
                $warehouse_id         = $this->at_site->findCompanyWarehouse($supplier_id)->id;
            }

            $warehouse = $this->at_site->getWarehouseByID($warehouse_id, $supplier_id);
            if (!$warehouse->shipment_price_group_id) {
                $ringkasan = [
                    'jumlah_barang' => $totalQty,
                    'operasi'       => '',
                    'label'         => '',
                    'cost'          => '',
                    'total_harga'   => $totalAmount,
                    'total_akhir'   => $totalAmount
                ];
            }

            $shipment_price         = $this->at_site->getShipmentProductPriceByShipmentPriceGroupId($warehouse->shipment_price_group_id);
            $total                  = 0;
            $res                    = [];
            foreach ($shipment_price as $shipment) {
                $res[] = [
                    'product_id' => $shipment->product_id,
                    'price'      => $delivery_method == 'pickup' ? $shipment->price_pickup : $shipment->price_delivery
                ];
            }

            foreach ($cart as $data) {
                foreach ($res as $data2) {
                    if ($data->id == $data2['product_id']) {
                        $qty        = $data->cart_qty;
                        $subtotal   = $qty * $data2['price'];
                        $total      += $subtotal;
                    }
                }
            }

            if ($total != 0) {
                if ($total < 0) {
                    $operasi        = '-';
                    $label          = 'Potongan Harga Pengiriman';
                } else {
                    $operasi        = '+';
                    $label          = 'Penambahan Harga Pengiriman';
                }
                $ringkasan = [
                    'jumlah_barang' => $totalQty,
                    'operasi'       => $operasi,
                    'label'         => $label,
                    'cost'          => $total,
                    'total_harga'   => $totalAmount,
                    'total_akhir'   => $totalAmount + $total
                ];
            } else {
                $ringkasan = [
                    'jumlah_barang' => $totalQty,
                    'operasi'       => '',
                    'label'         => '',
                    'cost'          => '',
                    'total_harga'   => $totalAmount,
                    'total_akhir'   => $totalAmount
                ];
            }

            $response = [
                'ringkasan' => $ringkasan
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan Harga Grup Pengiriman", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_proof_payment_get()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $id_pemesanan   = $this->input->get('id_pemesanan');

            if (!$id_pemesanan) {
                throw new Exception("Params `id_pemesanan` is required", 404);
            }

            $order          = $this->at_purchase->getOrderItems($id_pemesanan, $auth->user->id);

            if (!$order) {
                throw new \Exception("Pesanan tidak ditemukan atau tidak memiliki akses untuk melihat pesanan tersebut.", 400);
            }

            $payment_temp   = $this->payment->getListPaymentTemp($id_pemesanan);

            if (!$payment_temp) {
                $temp = 'Tidak ada data pembayaran';
            }

            $nominalTotal = 0;
            foreach ($payment_temp as $i => $pt) {
                $nominalTotal += ($pt->status != 'reject' ? $pt->nominal : 0);
                $temp[] = [
                    'no'             => ($i + 1),
                    'tanggal_unggah' => $this->__convertDate($pt->created_at),
                    'nominal'        => (int)$pt->nominal,
                    'status'         => $this->__status($pt->status, $order->payment_method == 'kredit_pro' ? 1001 : 0)[0],
                    'label_status'   => $this->__status($pt->status, $order->payment_method == 'kredit_pro' ? 1001 : 0)[1],
                    'foto'           => $pt->url_image
                ];
            }

            $response = [
                'total_pembayaran'          => $nominalTotal,
                'jumlah_yang_harus_dibayar' => (int)$order->grand_total,
                'list_pembayaran' => $temp
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar pembayaran atas ID pemesanan " . $id_pemesanan, $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function apply_credit_post()
    {
        $this->db->trans_begin();
        try {
            $config = [
                [
                    'field' => 'id_pemesanan',
                    'label' => 'Pemesanan ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $purchase_id    = $this->body('id_pemesanan');
            $purchase_data  = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $sales_data     = $this->at_sale->findSalesByReferenceNo($purchase_data->cf1, $purchase_data->supplier_id);
            $user           = $this->at_auth->find($sales_data->created_by);

            $dataKreditPro = [
                'orderId'   => $sales_data->reference_no . '-' . $sales_data->biller_id,
                'msisdn'    => $user->phone,
                'amount'    => (string) (int) $sales_data->grand_total,
                'redirect'  => base_url('api/v1/retailer/Purchase/success_kreditPro?purchase_id=' . $purchase_id)
            ];

            $param    = $this->integration->encryptKreditpro($dataKreditPro);
            $url      = $this->integration->getUrlKreditPro();
            if (!$url) {
                throw new \Exception("Url Kredit Pro Tidak Ada");
            }

            $response = [
                'url'    => $url,
                'param'  => $param
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengajuan kredit", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function success_kreditPro_get()
    {
        $this->db->trans_begin();
        try {
            if ($this->input->get('paymentstatus') !== 'success') {
                redirect(aksestoko_route('aksestoko/home/failed_kreditpro'));
            }

            if (!$this->input->get('param')) {
                throw new \Exception("Undefine Param");
            }

            $param = json_decode($this->integration->decryptKreditpro($this->input->get('param')));

            if (!property_exists($param, 'price')) {
                throw new Exception("Undefine Price", 1);
            }
            if (!property_exists($param, 'payment_type')) {
                throw new Exception("Undefine Payment Type", 1);
            }
            if (!property_exists($param, 'orderId')) {
                throw new Exception("Undefine Order ID", 1);
            }

            $orderId        = $param->orderId;
            $arrayOrderId   = explode('-', $orderId);
            $payment_type   = $param->payment_type;
            $price          = $param->price;
            preg_match_all('!\d+!', $payment_type, $matches);
            $duration             = implode('', $matches[0]);
            $purchases            = $this->at_sale->getPurchasesByRefNo(trim($arrayOrderId[0]), trim($arrayOrderId[1]));
            $charge_third_party   = $price - $purchases->grand_total;

            $data = [
                'payment_status'        => 'waiting',
                'grand_total'           => $price,
                'payment_duration'      => $duration,
                'charge_third_party'    => $charge_third_party,
                'payment_type'          => $payment_type
            ];

            if (!$this->at_purchase->updatePurchaseById($purchases->id, $data)) {
                throw new \Exception("Failed");
            }

            $this->db->trans_commit();
            redirect(aksestoko_route('aksestoko/home/success_kreditpro'));
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan perubahan data pemesanan");
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect(aksestoko_route('aksestoko/home/failed_kreditpro'));
        }
    }

    public function list_promo_get()
    {
        $this->db->trans_begin();
        try {
            $auth         = $this->authorize();

            $supplier_id = $this->input->get('id_distributor');
            if (!$supplier_id) {
                throw new \Exception("Params `id_distributor` is required");
            }

            $company = $this->at_company->findCompanyByCf1AndCompanyId($supplier_id, $auth->company->cf1);

            $list_promo = $this->promotion->listPromotion($company->id, $supplier_id);

            if (!$list_promo) {
                $list_promo = null;
            }

            $response = [
                'list_promo' => $list_promo
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar promo", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_promo_get()
    {
        $this->db->trans_begin();
        try {
            $auth         = $this->authorize();

            $promo_id = $this->input->get('promo_id');
            if (!$promo_id) {
                throw new \Exception("Params `promo_id` is required");
            }

            $company = $this->at_company->findCompanyByCf1($auth->company->cf1);
            $promo   = $this->promotion->GetPromotion($company->id, $promo_id);

            if (!$promo) {
                throw new \Exception("Gagal melakukan pengambilan detail promo, Dikarenakan promo atas ID : " . $promo_id . " tidak ditemukan");
            }

            $validation = $this->input->get('validation');
            if($validation == 1){
                $arr        = $this->check_promotion($promo_id, $company->id);
                if ($arr['status'] == false) {
                    throw new \Exception($arr['msg']);
                }
            }

            $response = [
                'detail_promo' => $promo
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar promo", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function cancel_order_put()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $config = [
                [
                    'field' => 'id_pemesanan',
                    'label' => 'ID Pemesanan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);
            $purchase_id    = $this->body('id_pemesanan');

            $cancel         = $this->at_purchase->cancelOrder($purchase_id, $auth->user->id);

            if (!$cancel) {
                throw new \Exception("Gagal melakukan pembatalan pesanan");
            }

            $purchase   = (array) $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $sale       = (array) $this->at_sale->findSalesByReferenceNo($purchase['cf1'], $purchase['supplier_id']);
            $saleItems  = [];
            foreach ($this->at_sale->getSaleItemsBySaleId($sale['id']) as $i => $saleItem) {
                $saleItems[] = (array) $saleItem;
            }

            $supplier = $this->at_site->getCompanyByID($sale['biller_id']);

            if ($this->integration->isIntegrated($supplier->cf2)) {
                $response = $this->integration->update_confirmation_integration($supplier->cf2, $this->session->userdata('username'), $sale, $saleItems, $purchase);
                if (!$response) {
                    throw new \Exception("Tidak dapat melakukan pembatalan pesanan ke distributor");
                }

                $dataSale['cf1']    = $response;
                $dataSale['cf2']    = $supplier->cf2;
                $dataSale['id']     = $sales_id;
                if (!$this->at_sale->updateOrders($dataSale, ['id' => $purchase_id])) {
                    throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                }
            }
            $response = [
                'id_pemesanan' => $purchase_id
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pembatalan Pesanan", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_payment_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $purchase_id = $this->input->get('id_pemesanan');

            if (!$purchase_id) {
                throw new Exception("Params `purchase_id` is required", 404);
            }
            $purchase = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
            $list_bank      = $this->bank->getAllBank($purchase->supplier_id);

            $via = 'TUNAI';
            $showDetailBank = false;
            $showRencanaPelunasan = false;
            /*if($purchase->payment_method == 'cash on delivery'){
                $showDetailBank = false;    
            }*/

            if ($purchase->payment_method == 'kredit') {
                $showRencanaPelunasan = true;
                $rencanaPelunasan = $purchase->payment_duration . ' Hari';
            }

            if ($purchase->bank_id != 0) {
                $showDetailBank = true;
                foreach ($list_bank as $keyBank => $bank) {
                    if ($bank->id == $purchase->bank_id) {
                        $bank_id = $bank->id;
                        $via = strtoupper($bank->bank_name);
                        $rekening = $bank->no_rekening;
                        $atasNama = $bank->name;
                        $logo = $bank->logo;
                    }
                }
            }

            if ($purchase->id && $purchase->payment_method != 'kredit_pro') {
                if ($purchase->total != 0) {
                    $harga = 'Rp' . number_format($purchase->total, 0, ',', '.');
                }
            } else {
                if ($purchase->grand_total != 0) {
                    $harga = 'Rp' . number_format($purchase->grand_total, 0, ',', '.');
                }
            }

            if ($purchase->total_discount != 0) {
                $diskon = 'Rp ' . number_format($purchase->total_discount, 0, ',', '.');
            }

            if ($purchase->paid != 0) {
                $paid = '- Rp' . number_format($purchase->paid, 0, ',', '.');
            }



            if ($purchase->id && $purchase->payment_method != 'kredit_pro') {
                if ($purchase->charge && $purchase->charge != 0) {
                    $textCharge = $purchase->charge > 0 ? 'Biaya lain-lain' : 'Potongan harga';
                    $labelCharge = $purchase->charge > 0 ? 'danger' : 'success';
                    $charge = ($purchase->charge > 0 ? '' : '-') . ' Rp' . number_format(abs($purchase->charge), 0, ',', '.');
                }

                if ($purchase->correction_price && $purchase->correction_price != 0) {
                    $correction_price = 'Rp ' . number_format($purchase->correction_price, 0, ',', '.');
                }
            }

            if ($purchase->id && $purchase->payment_method != 'kredit_pro') {
                $total = 'Rp ' . number_format($purchase->total + $purchase->charge + $purchase->correction_price - $purchase->total_discount - $purchase->paid);
            } else {
                $total = 'Rp ' . number_format($purchase->grand_total + $purchase->charge - $purchase->total_discount - $purchase->paid);
            }

            $response = [
                'data_pembayaran' => [
                    'metode_pembayaran' => $purchase->payment_method,
                    'melalui' => $via,
                    'show_detail_bank' => $showDetailBank,
                    'show_pelunasan' => $showRencanaPelunasan,
                    'rekening' => $rekening,
                    'atas_nama' => $atasNama,
                    'durasi_pembayaran' => $rencanaPelunasan,
                    'logo' => $logo,
                    'harga' => $harga,
                    'diskon' => $diskon,
                    'telah_dibayar' => $paid,
                    'label_biaya' => $labelCharge,
                    'text_biaya' => $textCharge,
                    'biaya' => $charge,
                    'koreksi_harga' => $correction_price,
                    'total' => $total
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan detail payment", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_payment_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'file',
                    'label' => 'File',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` is required',
                    ],
                ],
                [
                    'field' => 'id_pemesanan',
                    'label' => 'ID Pemesanan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` is required',
                    ],
                ],
                [
                    'field' => 'nominal',
                    'label' => 'Nominal',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` is required',
                    ],
                ]
            ];

            $this->validate_form($config);
            if ($this->body('nominal') == 0 || $this->body('nominal') == '0') {
                throw new \Exception("Nominal Pembayaran Harus Lebih Dari 0");
            }

            $purchase = $this->at_purchase->findPurchaseByPurchaseId($this->body('id_pemesanan'));
            if (!$purchase) {
                throw new \Exception("Data Pesanan Tidak Ditemukan");
            }

            if (!$this->payment->getPaymentPending($this->body('id_pemesanan'))) {
                throw new \Exception("Terdapat Pembayaran Yang masih Di proses");
            }

            $data = [
                'bank_id' => $this->body('bank_id') ?? $purchase->bank_id,
            ];

            if (!$this->at_purchase->updatePurchaseById($this->body('id_pemesanan'), $data)) {
                throw new \Exception("Gagal memperbarui data Pesanan");
            }

            $responseUploadImage = $this->upload_bukti_transfer($this->body('id_pemesanan'));

            if (!$this->audittrail->insertCustomerCreatePayment($auth->user->id, $auth->company->id, $this->body('supplier_id'), $responseUploadImage['sale_id'], $this->body('id_pemesanan'), $responseUploadImage['payment_temp_id'])) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_create_payment");
            }

            $response = [
                'purchase_id' => $purchase->id
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pembayaran pembayaran", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function confirm_delivery_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $config = [
                [
                    'field' => 'date',
                    'label' => 'date',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'id_pemesanan',
                    'label' => 'ID Pemesanan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'id_pengiriman',
                    'label' => 'ID Pengiriman',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'id_distributor',
                    'label' => 'ID Distributor',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ]
            ];

            $config_item_delivery = [
                [
                    'field' => 'id_produk',
                    'label' => 'ID Produk',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'nama_produk',
                    'label' => 'Nama Produk',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'quantity_diterima',
                    'label' => 'Quantity Diterima',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'delivery_item_id',
                    'label' => 'delivery_item_id',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'baik',
                    'label' => 'Baik',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'buruk',
                    'label' => 'Buruk',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $this->validate_form($config);
            $delivery_items = $this->body('produk');
            $product_id = [];
            $product_name = [];
            $product_code = [];
            $quantity_received = [];
            $delivery_item_id = [];
            $good = [];
            $bad = [];
            foreach ($delivery_items as $key => $delivery_item) {
                $this->validate_form($config_item_delivery, $delivery_item);
                $product_id[] = $delivery_item['id_produk'];
                $product_name[] = $delivery_item['nama_produk'];
                $product_code[] = $delivery_item['kode_produk'];
                $quantity_received[] = $delivery_item['quantity_diterima'];
                $delivery_item_id[] = $delivery_item['delivery_item_id'];
                $good[] = $delivery_item['baik'];
                $bad[] = $delivery_item['buruk'];
            }

            $delivery           = $this->at_sale->getDeliveryByID($this->body('id_pengiriman'));

            if (!$delivery) {
                throw new \Exception("Data Delivery Tidak Ditemukan");
            }

            $sale               = $this->at_sale->getSalesById($delivery->sale_id);

            if (!$sale) {
                throw new \Exception("Data Sale Tidak Ditemukan");
            }

            $cek_qty_delivery   = $this->at_sale->findDeliveryItems($this->body('id_pengiriman'));
            // print_r($cek_qty_delivery);die;
            if (!$cek_qty_delivery) {
                throw new \Exception("Data Delivery Tidak Ditemukan");
            }
            $jumlah             = count($delivery_items);

            for ($i = 0; $i < $jumlah; $i++) {
                $key = array_search($product_id[$i], array_column($cek_qty_delivery->items, 'product_id'));
                if ($quantity_received[$i] != $cek_qty_delivery->items[$key]->quantity_sent) {
                    throw new \Exception("Maaf !! Terjadi perubahan kuantitas pada " . $product_code[$i] . " " . $product_name[$i] . " dari " . $quantity_received[$i] . " menjadi " . (int) $cek_qty_delivery->items[$key]->quantity_sent);
                }
            }

            if ($delivery->receive_status == 'received') {
                if ($delivery->is_reject == 1 && $bad > 0) {
                    $this->db->update('deliveries', ['is_reject' => 2, 'is_confirm' => 1], ['id' => $this->body('id_pengiriman')]);
                } elseif ($delivery->is_reject == 1 && $bad <= 0) {
                    $this->db->update('deliveries', ['is_confirm' => 1], ['id' => $this->body('id_pengiriman')]);
                } elseif (is_null($delivery->is_reject) && is_null($delivery->is_confirm) && is_null($delivery->is_approval)) {
                    throw new \Exception("DO telah diterima toko");
                }
            }

            $data = [
                'date' => $this->body('date'),
                'purchase_id' => $this->body('id_pemesanan'),
                'product_code' => $product_code,
                'quantity_received' => $quantity_received,
                'do_ref' => $this->body('no_pengiriman'),
                'do_id' => $this->body('id_pengiriman'),
                'delivery_item_id' => $delivery_item_id,
                'good' => $good,
                'bad' => $bad,
                'note' => $this->body('catatan'),
                'file' => $this->body('file'),
            ];
            // print_r($data);die;

            if ($sale->sale_type == 'booking') {
                $confirm = $this->at_purchase->confirmReceivedBooking($data, $auth->user->id, $delivery->sale_id);
            } else {
                $confirm = $this->at_purchase->confirmReceived($data, $auth->user->id, $delivery->sale_id);
            }
            if (!$confirm) {
                throw new \Exception("Gagal konfirmasi penerimaan");
            }

            if (!$this->audittrail->insertCustomerConfirmDelivery($auth->user->id, $auth->company->id, $this->body('id_distributor'), $delivery->sale_id, $this->body('id_pemesanan'), $delivery->id)) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_confirm_delivery");
            }

            $delivery = $this->at_sale->getDeliveryByID($this->body('id_pengiriman'));

            if (!$delivery) {
                throw new \Exception("Data Delivery Tidak Ditemukan");
            }

            $deliveryItems = $this->at_sale->getDeliveryItemsByDeliveryId($delivery->id);

            $supplier = $this->at_site->getCompanyByID($sale->biller_id);

            if ($this->integration->isIntegrated($supplier->cf2)) {
                $response = $this->integration->confirm_received_integration($supplier->cf2, $this->session->userdata('username'), $sale, $delivery, $deliveryItems);
                if (!$response) {
                    throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                }
            }

            if ($sale->sale_type == 'booking') {
                if ($this->site->checkAutoClose($sale->id)) {
                    $this->sales_model->closeSale($sale->id);
                }
            }
            $response['do_id'] = $this->body('id_pengiriman');
            $response['do_reference_no'] = $delivery->do_reference_no;
            $response['sale_reference_no'] = $delivery->sale_reference_no;
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Konfirmasi Penerimaan Berhasil Disimpan", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function cancel_update_price_put()
    {
        try {
            $auth           = $this->authorize();
            $config = [
                [
                    'field' => 'id_pemesanan',
                    'label' => 'ID Pemesanan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $id_pemesanan = $this->body('id_pemesanan');

            $purchase = $this->at_purchase->findPurchaseByPurchaseId($id_pemesanan);

            if (!$purchase) {
                throw new \Exception("Pesanan Tidak Ditemukan");
            }

            $purchase = (array) $purchase;

            $sale = $this->at_sale->findSalesByReferenceNo($purchase['cf1'], $purchase['supplier_id']);

            if (!$sale) {
                throw new \Exception("Pesanan Tidak Ditemukan");
            }

            $sale = (array) $sale;

            if (!$sale['charge'] || $sale['charge'] == 0) {
                throw new \Exception("Tidak Ada Perubahan Harga");
            }

            if ($purchase['status'] != 'ordered' || $sale['sale_status'] != 'pending') {
                throw new \Exception("Status Pemesanan tidak Sesuai");
            }

            $saleItems = [];
            foreach ($this->at_sale->getSaleItemsBySaleId($sale['id']) as $i => $saleItem) {
                $saleItems[] = (array) $saleItem;
            }

            $sale['sale_status'] = 'canceled';
            $sale['is_updated_price'] = null;
            $purchase['status'] = 'canceled';
            if (!$this->at_sale->updateOrders($sale, $purchase)) {
                throw new \Exception("Gagal mengonfirmasi pesanan");
            }

            $supplier = $this->at_site->getCompanyByID($sale['biller_id']);

            if ($this->integration->isIntegrated($supplier->cf2)) {
                $response = $this->integration->update_confirmation_integration($supplier->cf2, $auth->user->username, $sale, $saleItems, $purchase);
                if (!$response) {
                    throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                }

                $dataSale['cf1'] = $response;
                $dataSale['cf2'] = $supplier->cf2;
                $dataSale['id'] = $sales_id;
                if (!$this->at_sale->updateOrders($dataSale, ['id' => $id_pemesanan])) {
                    throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                }
            }
            if (!$this->audittrail->insertCustomerApprovePrice($auth->user->id, $auth->company->id, $purchase['supplier_id'], $sale->id, $purchase_id)) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_approve_price");
            }

            $response = [
                'id_pemesanan' => $id_pemesanan
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan Membatalkan Perubahan Harga", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function confirm_update_price_put()
    {
        try {
            $auth           = $this->authorize();
            $config = [
                [
                    'field' => 'id_pemesanan',
                    'label' => 'ID Pemesanan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $id_pemesanan = $this->body('id_pemesanan');

            $purchase = $this->at_purchase->findPurchaseByPurchaseId($id_pemesanan);

            if (!$purchase) {
                throw new \Exception("Pesanan Tidak Ditemukan");
            }

            $purchase = (array) $purchase;

            $sale = $this->at_sale->findSalesByReferenceNo($purchase['cf1'], $purchase['supplier_id']);

            if (!$sale) {
                throw new \Exception("Pesanan Tidak Ditemukan");
            }

            $sale = (array) $sale;

            if (!$sale['charge'] || $sale['charge'] == 0) {
                throw new \Exception("Tidak Ada Perubahan Harga");
            }

            if ($purchase['status'] != 'ordered' || $sale['sale_status'] != 'pending') {
                throw new \Exception("Status Pemesanan tidak Sesuai");
            }

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
                $response = $this->integration->update_confirmation_integration($supplier->cf2, $auth->user->username, $sale, $saleItems, $purchase);
                if (!$response) {
                    throw new \Exception("Tidak dapat mengonfirmasi pesanan ke distributor");
                }

                $dataSale['cf1'] = $response;
                $dataSale['cf2'] = $supplier->cf2;
                $dataSale['id'] = $sales_id;
                if (!$this->at_sale->updateOrders($dataSale, ['id' => $id_pemesanan])) {
                    throw new \Exception("Tidak dapat memperbarui reference number dari distributor");
                }
            }
            if (!$this->audittrail->insertCustomerApprovePrice($auth->user->id, $auth->company->id, $purchase['supplier_id'], $sale->id, $purchase_id)) {
                throw new \Exception("Tidak dapat menyimpan rekam jejak audit customer_approve_price");
            }

            $response = [
                'id_pemesanan' => $id_pemesanan
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan Konfirmasi Perubahan Harga", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------------------------------------------//
    public function save_payment($purchase_id, $bank_id = null, $payment_method = null, $payment_durasi = null, $input_payment_durasi = null)
    {
        $this->data['purchase']   = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
        $bank_id                  = $bank_id == null ? $this->data['purchase']->bank_id : $bank_id;
        $payment_method           = $payment_method == null ? $this->data['purchase']->payment_method : $payment_method;
        $data = [
            'payment_method' => $payment_method,
            'bank_id'        => $bank_id,
        ];
        if ($payment_method == 'kredit') {
            if ($payment_durasi == 'other') {
                $data['payment_duration'] = $input_payment_durasi;
            } else {
                $data['payment_duration'] = $payment_durasi;
            }
        }
        if (!$this->at_purchase->updatePurchaseById($purchase_id, $data)) {
            return false;
        }
        return true;
    }

    private function __delivery_date($date) // MM/DD/YYYY
    {
        $newDate =  strtr($date, '/', '-');
        $newDate = date("Y-m-d", strtotime($newDate));
        return $newDate;
    }

    private function check_promo($promo_data, $total_pembelian, $company_id, $message = false)
    {
        $tot_trans        = $this->promotion->getTransactionByPromo($promo_data->id);
        $tot_trans_comp   = $this->promotion->getTransactionByCompany($promo_data->id, $company_id);
        if ($promo_data->end_date >= date('Y-m-d') && $promo_data->start_date <= date('Y-m-d')) {
            if ($total_pembelian >= $promo_data->min_pembelian) {
                if ($tot_trans_comp < $promo_data->max_toko) {
                    if ($tot_trans < $promo_data->quota) {
                        if ($message == true) {
                            $msg = [
                                'status' => true,
                                'msg'    => null
                            ];
                            return $msg;
                        } else {
                            return true;
                        }
                    } else {
                        if ($message == true) {
                            $msg = [
                                'status' => false,
                                'msg'    => 'Tidak Bisa Menggunakan Kode Promo Ini, Kuota Telah Habis'
                            ];
                            return $msg;
                        } else {
                            throw new \Exception('Tidak Bisa Menggunakan Kode Promo Ini, Kuota Telah Habis');
                        }
                    }
                } else {
                    if ($message == true) {
                        $msg = [
                            'status' => false,
                            'msg'    => 'Tidak Bisa Menggunakan Kode Promo Ini, Anda Telah Mencapai Limit Kuota'
                        ];
                        return $msg;
                    } else {
                        throw new \Exception('Tidak Bisa Menggunakan Kode Promo Ini, Anda Telah Mencapai Limit Kuota');
                    }
                }
            } else {
                if ($message == true) {
                    $msg = [
                        'status' => false,
                        'msg'    => 'Tidak Bisa Menggunakan Kode Promo Ini, Minimal Pembelian Anda Kurang'
                    ];
                    return $msg;
                } else {
                    throw new \Exception('Tidak Bisa Menggunakan Kode Promo Ini, Minimal Pembelian Anda Kurang');
                }
            }
        } else {
            if ($message == true) {
                $msg = [
                    'status' => false,
                    'msg'    => 'Batas Waktu Pemakaian Promo Ini Telah Habis atau Diluar Ketentuan'
                ];
                return $msg;
            } else {
                throw new \Exception('Batas Waktu Pemakaian Promo Ini Telah Habis atau Diluar Ketentuan');
            }
        }
    }

    //----------------------------------------------------------------------------------------------------------------------------------------------------------------//
    private function check_promotion($promo_id, $company_id)
    {
        $promo_data       = $this->promotion->GetPromotion($company_id, $promo_id);
        $tot_trans        = $this->promotion->getTransactionByPromo($promo_data->id);
        $tot_trans_comp   = $this->promotion->getTransactionByCompany($promo_data->id, $company_id);
        if ($promo_data->end_date >= date('Y-m-d') && $promo_data->start_date <= date('Y-m-d')) {
            if ($tot_trans_comp < $promo_data->max_toko) {
                if ($tot_trans < $promo_data->quota) {
                    $msg = [
                        'status' => true,
                        'msg'    => null
                    ];
                    return $msg;
                } else {
                    $msg = [
                        'status' => false,
                        'msg'    => 'Maaf Anda Tidak Bisa Menggunakan Kode Promo Ini, Dikarenakan Kuota Telah Habis'
                    ];
                    return $msg;
                }
            } else {
                $msg = [
                    'status' => false,
                    'msg'    => 'Maaf Anda Tidak Bisa Menggunakan Kode Promo Ini, Dikarenakan Kuota Telah Habis'
                ];
                return $msg;
            }
        } else {
            $msg = [
                'status' => false,
                'msg'    => 'Maaf Anda Tidak Bisa Menggunakan Kode Promo Ini, Dikarenakan Batas Waktu Pemakaian Promo Ini Telah Habis atau Diluar Ketentuan'
            ];
            return $msg;
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------------------------//

    private function list_order_card($order)
    {
        if ($order->payment_deadline) {
            $now                = now();
            $end_date           = strtotime(date('Y-m-d', strtotime($order->payment_deadline)));
            $datediff           = $now - $end_date;
            $duration           = round($datediff / (60 * 60 * 24));
            if ($duration < -3 && $duration > -7) {
                $pesan = 'Warning';
            } elseif ($duration > -3) {
                $pesan = 'Danger';
            } else {
                $pesan = 'Info';
            }
        }

        $deliveries         = $this->at_sale->getDeliveriesItems($order->supplier_id, $order->cf1);
        $product            = $this->product->getProductByCodeAndSupplierId($order->items[0]->product_code, $order->supplier_id);

        $supplier           = $this->at_company->getCompanyByID($order->supplier_id);


        if ($order->payment_status != 'paid' && $order->status != 'canceled' && in_array($order->payment_method, array_merge($this->data['array_payment_method'], ["kredit_pro"])) && $order->payment_deadline != null) {
            $notif = 'Sisa Durasi Waktu Pembayaran : ' . $duration . ' Hari';
        }

        if ($order->is_updated_price == 1) {
            $notifCharge = "Distributor telah memperbarui total harga. ";
            if ($order->charge < 0) {
                $notifCharge .= 'Terdapat potongan harga sebesar - Rp ' . (int)abs($order->charge);
            } else {
                $notifCharge .= 'Terdapat biaya tambahan sebesar Rp ' . (int)$order->charge;
            }
        }

        $param                = $order->payment_method == 'kredit_pro' ? 1 : 0;
        $company              = $supplier->company;
        $id_pemesanan         = $order->id;
        $no_pemesanan         = $order->cf1;
        $tanggal_pemensanan   = $this->__convertDate($order->date);
        $status_pemesanan     = $this->__status($order->status)[0];
        $notif_pemesanan      = $this->__status($order->status)[1];
        $status_pembayaran    = $this->__status($order->payment_status, $param);
        $product_image        = url_image_thumb($product->thumb_image) ?? base_url("assets/uploads/no_image.png");
        $product_name         = $order->items[0]->product_name;
        $product_code         = $order->items[0]->product_code;
        if ($order->items[0]->unit_cost > 0) {
            $unit_cost        = (int)$order->items[0]->unit_cost;
        }
        $quantity             = (int) $order->items[0]->quantity;
        $jumlah_barang        = count($order->items);
        $satuan               = convert_unit($this->__unit($product->unit));
        if ($order->items[0]->subtotal > 0) {
            $harga_barang = (int)$order->items[0]->subtotal;
        }
        if ($order->grand_total > 0) {
            $total_harga = (int)$order->grand_total;
        }
        if (count($order->items) > 1) {
            $notifProduct = '+' . (count($order->items) - 1) . ' barang lainnya';
        }
        $counter = 0;
        foreach ($deliveries as $delivery) {
            $counter = $delivery->receive_status != "received" && $delivery->status != "packing" ? $counter + 1 : $counter;
        }
        if ($counter > 0) {
            $notif_terima = true;
        } else {
            $notif_terima = false;
        }

        $data = [
            'id_pemesanan'          => $id_pemesanan,
            'pesan'                 => $pesan,
            'duration'              => $duration,
            'notifikasi'            => $notif,
            'charge'                => (int)$order->charge,
            'notikasi_charge'       => $notifCharge,
            'company'               => $company,
            'no_pemesanan'          => $no_pemesanan,
            'tanggal_pemensanan'    => $tanggal_pemensanan,
            'notikasi_pemesanan'    => $notif_pemesanan,
            'status_pemesanan'      => $status_pemesanan,
            'notikasi_pembayaran'   => $status_pembayaran[1],
            'status_pembayaran'     => $status_pembayaran[0],
            'product_image'         => $product_image,
            'product_name'          => $product_name,
            'product_code'          => $product_code,
            'unit_cost'             => $unit_cost,
            'quantity'              => $quantity,
            'satuan'                => $satuan,
            'jumlah_barang'         => $jumlah_barang,
            'harga_barang'          => $harga_barang,
            'total_harga'           => $total_harga,
            'notifikasi_product'    => $notifProduct,
            'konfirmasi_penerimaan' => $notif_terima
        ];

        return $data;
    }

    private function bayar_ditempat($purchase)
    {
        $total = ($purchase['grand_total'] + $purchase['charge'] - $purchase['total_discount'] - $purchase['paid']);
        if ($purchase['grand_total'] != 0) {
            $harga    = (int)$purchase['grand_total'];
        }
        if ($purchase['charge'] != 0) {
            $charge   = (int)$purchase['charge'];
        }
        if ($purchase['total_discount'] != 0) {
            $discount = $purchase['total_discount'];
        }
        $detail_bayar_ditempat = [
            'harga'    => $harga,
            'charge'   => $charge,
            'disocunt' => $discount,
            'total'    => $total
        ];

        $bayar_ditempat = [
            'logo'      => base_url('assets/uploads/logos/cod.png'),
            'total'     => (int)$total,
            'detail'    => $detail_bayar_ditempat
        ];

        return $bayar_ditempat;
    }

    private function tempo_dengan_distributor($purchase, $banks, $supplier_id)
    {

        $harga_tempo    = 0;
        $total_purchase = $purchase['grand_total'];
        $harga_tempo    = $purchase['grand_total_tempo'] - $purchase['grand_total'];
        $disc           = $purchase['total_discount_tempo'];
        $total          = ($total_purchase + $purchase['charge'] - $disc - $purchase['paid'] + $harga_tempo);

        foreach ($banks as $key) {
            $bank_data  = $this->bank->getBankById($key->id);
            $bank[] = [
                'bank_id'   => $bank_data->id,
                'bank_name' => $bank_data->bank_name,
                'no_rek'    => $bank_data->no_rekening,
                'nama'      => $bank_data->name,
                'logo_bank' => base_url('assets/uploads/') . $bank_data->logo
            ];
        }
        $TOP            = $this->payment->getTOP($supplier_id);
        foreach ($TOP as $row) {
            $duration[] = [
                'duration'    => $row->duration,
                'description' => $row->description
            ];
        }

        if ($total_purchase != 0) {
            $harga    = (int)$total_purchase;
        }
        if ($harga_tempo != 0) {
            $harga_tempo = (int)$harga_tempo;
        }
        if ($purchase['charge'] != 0) {
            $charge   = (int)$purchase['charge'];
        }

        if ($disc != 0) {
            $discount = $disc;
        }

        $detail_tempo = [
            'harga'       => $harga,
            'harga_tempo' => $harga_tempo,
            'charge'      => $charge,
            'disocunt'    => $discount,
            'total'       => $total
        ];

        $tempo = [
            'logo'                => base_url('assets/uploads/logos/credit.png'),
            'total'               => (int)$total,
            'list_bank'           => $bank,
            'list_payment_durasi' => $duration,
            'detail'              => $detail_tempo
        ];

        return $tempo;
    }

    private function bayar_sebelum_dikirim($purchase, $banks)
    {
        $total = ($purchase['grand_total'] + $purchase['charge'] - $purchase['total_discount'] - $purchase['paid']);

        foreach ($banks as $key) {
            $bank_data      = $this->bank->getBankById($key->id);
            $bank[] = [
                'bank_id'   => $bank_data->id,
                'bank_name' => $bank_data->bank_name,
                'no_rek'    => $bank_data->no_rekening,
                'nama'      => $bank_data->name,
                'logo_bank' => base_url('assets/uploads/') . $bank_data->logo
            ];
        }

        if ($purchase['grand_total'] != 0) {
            $harga    = (int)$purchase['grand_total'];
        }
        if ($purchase['charge'] != 0) {
            $charge   = (int)$purchase['charge'];
        }
        if ($purchase['total_discount'] != 0) {
            $discount = $purchase['total_discount'];
        }

        $detail_bayar_sebelum_dikirim = [
            'harga'    => $harga,
            'charge'   => $charge,
            'disocunt' => $discount,
            'total'    => $total
        ];

        $bayar_sebelum_dikirim = [
            'logo'      => base_url('assets/uploads/logos/cbd.png'),
            'total'     => (int)$total,
            'list_bank' => $bank,
            'detail'    => $detail_bayar_sebelum_dikirim
        ];

        return $bayar_sebelum_dikirim;
    }

    private function kredit_pro($purchase, $supplier_id)
    {
        $term_payment_kredit_pro    = $this->payment->getActiveTermKreditProByCompanyId($supplier_id);
        $term_payment               = array_column($term_payment_kredit_pro, 'term');
        $default_term_payment_kredit_pro = [
            '30'       => '30 Hari',
            '45'       => '45 Hari',
            '60'       => '60 Hari',
        ];
        $current_kreditpro    = $term_payment[0];

        $total                = $purchase['grand_total'] + $purchase['charge'] - $purchase['total_discount'];
        $kreditpro30hari      = ($total * 0.9 / 100);
        $kreditpro45hari      = ($total * 1.3 / 100);
        $kreditpro60hari      = ($total * 2 / 100);
        $strCurrentTotal      = 'kreditpro' . $current_kreditpro . 'hari';

        if (count($term_payment_kredit_pro) > 0) {
            $total_harga = $total + ${$strCurrentTotal} . ' - (' . $default_term_payment_kredit_pro[$current_kreditpro] . ')';
        } else {
            $total_harga = 'Durasi Pembayaran Tidak Tersedia';
        }
        if ($purchase['grand_total'] != 0) {
            $harga    = (int)$purchase['grand_total'];
        }
        if ($purchase['charge'] != 0) {
            $charge   = (int)$purchase['charge'];
        }
        if ($purchase['total_discount'] != 0) {
            $discount = $purchase['total_discount'];
        }

        $info         = "Perhatian, Konfirmasi pengajuan kredit oleh KreditPro hanya dapat diproses pada jam kerja. Setiap hari Senin - Jum'at (kecuali hari libur) jam 08:30 - 17:00 (WIB).";

        if ($total < 1000000) {
            $info_1   = "Total pesanan harus melebihi Rp 1.000.000 untuk dapat memilih metode ini.";
        }

        foreach ($default_term_payment_kredit_pro as $key => $value) {
            if ($key == '30') {
                $detail_kredit_pro[] = [
                    'durasi_pembayaran'   => $value,
                    'harga'               => $harga,
                    'charge'              => $charge,
                    'disocunt'            => $discount,
                    'subtotal'            => $total,
                    'interest_rate'       => '(0,9%)',
                    'harga_interest_rate' => $kreditpro30hari,
                    'total'               => (int)($total + $kreditpro30hari),
                    'info'                => $info,
                    'info_1'              => $info_1
                ];
            } else if ($key == '45') {
                $detail_kredit_pro[] = [
                    'durasi_pembayaran'   => $value,
                    'harga'               => $harga,
                    'charge'              => $charge,
                    'disocunt'            => $discount,
                    'subtotal'            => $total,
                    'interest_rate'       => '(1,3%)',
                    'harga_interest_rate' => $kreditpro45hari,
                    'total'               => (int)($total + $kreditpro45hari),
                    'info'                => $info,
                    'info_1'              => $info_1
                ];
            } else if ($key == '60') {
                $detail_kredit_pro[] = [
                    'durasi_pembayaran'   => $value,
                    'harga'               => $harga,
                    'charge'              => $charge,
                    'disocunt'            => $discount,
                    'subtotal'            => $total,
                    'interest_rate'       => '(2%)',
                    'harga_interest_rate' => $kreditpro60hari,
                    'total'               => (int)($total + $kreditpro60hari),
                    'info'                => $info,
                    'info_1'              => $info_1
                ];
            }
        }

        $kredit_pro = [
            'logo'      => base_url('assets/uploads/logos/cod.png'),
            'total'     => $total_harga,
            'detail'    => $detail_kredit_pro
        ];

        return $kredit_pro;
    }

    public function upload_bukti_transfer($purchase_id)
    {
        $purchase_data = $this->at_purchase->findPurchaseByPurchaseId($purchase_id);
        $sales_data = $this->at_sale->findSalesByReferenceNo($purchase_data->cf1, $purchase_data->supplier_id);
        $supplier = $this->at_site->getCompanyByID($sales_data->biller_id);

        if ($purchase_data->bank_id != 0) {
            $bank = $this->bank->getBankById($purchase_data->bank_id);
            if (!$bank) {
                throw new \Exception("Data Bank Tidak Ditemukan");
            }
        }

        if (!$purchase_data) {
            throw new \Exception("Data Pesanan Tidak Ditemukan");
        }

        if (!$sales_data) {
            throw new \Exception("Data Pesanan Tidak Ditemukan");
        }

        if (!$supplier) {
            throw new \Exception("Data Pemasok Tidak Ditemukan");
        }

        $nominal = $this->body('nominal');

        $uploadedImg = $this->integration->upload_files($this->body('file'), 'base64');
        if (!$uploadedImg) {
            throw new \Exception("Gagal mengunggah gambar");
        }

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
            $response = $this->integration->create_payment_integration($supplier->cf2, $this->authorize()->user->username, (array) $sales_data, $dataPaymentTemp, (array) $bank);
            if (!$response) {
                throw new \Exception("Tidak dapat mengirim pembayaran ke distributor");
            }
            if (!$this->payment->updatePaymentTemp(['cf1' => $response, 'cf2' => $supplier->cf2], ['reference_no' => $dataPaymentTemp['reference_no']])) {
                throw new \Exception("Tidak dapat memperbarui reference number pembayaran dari distributor");
            }
        }

        return ['payment_temp_id' => $payment_temp_id, 'sale_id' => $sales_data->id];
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
    //------------------------------------------------------------------------------------------------------------------------------------------------------------------//
}

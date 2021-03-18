<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Retailer_Controller.php';

class Profile extends MY_API_Retailer_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('aksestoko/home_model', 'home');
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/product_model', 'product');
        $this->load->model('aksestoko/at_company_model', 'at_company');
        $this->load->model('aksestoko/at_auth_model', 'at_auth');
    }

    public function __operate($a, $b, $char)
    {
        switch ($char) {
            case '-':
                return $a - $b;
            case '*':
                return $a * $b;
            case '+':
                return $a + $b;
            case '/':
                return $a / $b;
        }
        return $a;
    }

    public function list_distributor_get()
    {
        $this->db->trans_begin();
        try {
            $auth               = $this->authorize();

            $list_distributor   = $this->home->getAllCompany($auth->company->cf1, $auth->user->company_id);
            if (!$list_distributor) {
                throw new \Exception("Gagal melakukan pengambilan daftar distributor", 400);
            }
            foreach ($list_distributor as $comp) {
                if ($comp->avatar) {
                    if (strpos($comp->avatar, 'https://files.forca.id/') !== false) {
                        $avatar = $comp->avatar;
                    } else {
                        $avatar = base_url('assets/uploads/avatars/') . $comp->avatar;
                    }
                } else {
                    $avatar = base_url('assets/uploads/logos/') . $comp->logo;
                }
                $company = $this->at_site->findCompanyByCf1AndCompanyId($comp->company_id, $auth->company->cf1);
                $data[] = [
                    'company_id'         => $comp->company_id,
                    'logo'               => $avatar,
                    'company_name'       => $comp->company,
                    'name'               => $comp->name,
                    'address'            => $comp->address,
                    'phone'              => $comp->phone,
                    'customer_group_id'  => $company->customer_group_id,
                    'price_group_id'     => $company->price_group_id
                ];
            }
            $response = [
                'list_distributor' => $data
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar distributor", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_product_get()
    {
        $this->db->trans_begin();
        try {
            $this->authorize();

            $id_distributor   = $this->input->get('id_distributor');
            $price_group_id   = $this->input->get('price_group_id');
            $limit            = $this->input->get('limit');
            $offset           = $this->input->get('offset');
            $search           = $this->input->get('search') ?? null;

            if (!$id_distributor) {
                throw new Exception("Params `id_distributor` is required", 404);
            }

            $product = $this->product->getCompanyProduct($id_distributor, $limit, $offset, $price_group_id, $search);

            if (!$product) {
                throw new \Exception("Gagal melakukan pengambilan daftar product pada ID Distributor " . $id_distributor, 400);
            }

            foreach ($product as $prod) {
                $prod->price        = $prod->group_price && $prod->group_price > 0 ? $prod->group_price : $prod->price;
                $unit               = $this->product->getUnit($prod->sale_unit);
                $prod->price        = $this->__operate($prod->price, $unit->operation_value, $unit->operator);
                $data[] = [
                    'product_id'    => $prod->id,
                    'images'        => url_image_thumb($prod->thumb_image) ?? base_url('assets/uploads/no_image.png'),
                    'product_name'  => $prod->name,
                    'product_price' => (int)$prod->price,
                    'unit_name'     => $unit->name,
                    'is_multiple'   => (int)$prod->is_multiple,
                    'min_order'     => (int)$prod->min_order
                ];
            }
            $response = [
                'list_product' => $data
            ];
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar product pada ID Distributor " . $id_distributor, $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_address_get()
    {
        $this->db->trans_begin();
        try {
            $auth         = $this->authorize();
            $addresses    = array_merge([$this->at_site->findCompany($auth->company->company_id)], $this->at_site->getCompaniesAddress($auth->company->company_id));
            if (!$addresses) {
                throw new \Exception("Gagal melakukan pengambilan daftar alamat", 400);
            }
            foreach ($addresses as $address) {
                if ($address->group_name != 'address') {
                    $remove = false;
                } else {
                    $remove = true;
                }
                $data[] = [
                    'address_id'          => $address->id,
                    'address_company'     => $address->company,
                    'address_name'        => $address->name,
                    'address_phone'       => $address->phone,
                    'address'             => trim($address->address),
                    'address_state'       => ucwords(strtolower($address->state)),
                    'address_city'        => ucwords(strtolower($address->city)),
                    'address_country'     => ucwords(strtolower($address->country)),
                    'address_postal_code' => $address->postal_code,
                    'can_be_removed'      => $remove
                ];
            }
            $response = [
                'list_alamat' => $data
            ];
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan daftar alamat", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_address_get()
    {
        $this->db->trans_begin();
        try {
            $this->authorize();

            $id_address   = $this->input->get('id_address');
            if (!$id_address) {
                throw new Exception("Params `id_address` is required", 404);
            }

            $address      = $this->at_company->getCompanyByID($id_address);
            if (!$address) {
                throw new \Exception("Gagal melakukan pengambilan detail alamat", 400);
            }

            if ($address->group_name != 'address') {
                $remove = false;
            } else {
                $remove = true;
            }

            $data = [
                'nama_toko'      => $address->company,
                'nama_penerima'  => $address->name,
                'email'          => $address->email,
                'no_telp'        => $address->phone,
                'alamat'         => $address->address,
                'provinsi'       => $address->country,
                'kabupaten'      => $address->city,
                'kecamatan'      => $address->state,
                'desa'           => $address->village,
                'kode_pos'       => $address->postal_code,
                'can_be_removed' => $remove
            ];

            $response = [
                'detail_address' => $data
            ];

            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan detail alamat", $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_sales_person_get()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $sales_person   = $this->site->getSalesPersonByRefNo($auth->user->sales_person_ref);
            if (!$sales_person) {
                $sales_person   = null;
            }

            $response = [
                'detail_sales_person' => $sales_person
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan detail sales person", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_profile_get()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $profile        = $this->at_auth->find($auth->user->id);
            $addresses      = array_merge([$this->at_site->findCompany($auth->company->id)], $this->at_site->getCompaniesAddress($auth->company->id));
            $guide          = $this->at_site->getGuideAT($auth->user->id);

            $salesperson = [];
            if ($profile->sales_person_ref != null) {
                $sales_person   = $this->site->getSalesPersonByRefNo($profile->sales_person_ref);
                $salesperson = [
                    'sales_person_id'   => $sales_person->id,
                    'name'              => $sales_person->name,
                    'refence_no'        => $sales_person->reference_no,
                    'email'             => $sales_person->email,
                    'no_tlp'            => $sales_person->phone,
                    'alamat'            => $sales_person->address,
                    'kecamatan'         => $sales_person->state,
                    'kabupaten'         => $sales_person->city,
                    'provincy'          => $sales_person->country
                ];
            }

            $profile = [
                'user_id'           => $auth->user->id,
                'nama_depan'        => $profile->first_name,
                'nama_belakang'     => $profile->last_name,
                'email'             => $profile->email,
                'kode_bk'           => $profile->username,
                'nama_toko'         => $profile->company,
                'no_tlp'            => $profile->phone,
                'phone_is_verified' => $profile->phone_is_verified ? 'Terverifikasi' : 'Belum Terverifikasi'
            ];

            $alamat = [];
            foreach ($addresses as $address) {
                $alamat[] = [
                    'address_id'          => $address->id,
                    'address_company'     => $address->company,
                    'address_name'        => $address->name,
                    'address_phone'       => $address->phone,
                    'address'             => trim($address->address),
                    'address_state'       => ucwords(strtolower($address->state)),
                    'address_city'        => ucwords(strtolower($address->city)),
                    'address_country'     => ucwords(strtolower($address->country)),
                    'address_postal_code' => $address->postal_code
                ];
            }

            $tour = [
                'pilih_distributor' => $guide->select_distributor == 0 ? 'checked' : '',
                'dashboard'         => $guide->dashboard == 0 ? 'checked' : '',
                'cart'              => $guide->cart == 0 ? 'checked' : '',
                'checkout'          => $guide->checkout == 0 ? 'checked' : '',
                'order'             => $guide->order == 0 ? 'checked' : '',
                'order_detail'      => $guide->order_detail == 0 ? 'checked' : '',
                'payment'           => $guide->payment == 0 ? 'checked' : '',
                'goods_receive'     => $guide->goods_receive == 0 ? 'checked' : ''

            ];

            $term = [
                'dokumen_ketentuan' => base_url('assets/aksestoko/Syarat%20&%20Ketentuan%20AksesToko.pdf'),
                'dokumen_kebijakan' => base_url('assets/aksestoko/Kebijakan%20Privasi%20AksesToko.pdf'),
            ];

            $response = [
                'profile'          => $profile,
                'daftar_alamat'    => $alamat,
                'sales_person'     => $salesperson,
                'tour_guide'       => $tour,
                'syarat_ketentuan' => $term
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pengambilan detail profile", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_profile_put()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $config = [
                [
                    'field' => 'nama_toko',
                    'label' => 'Nama Toko',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'no_tlp',
                    'label' => 'No. Telepon',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'nama_depan',
                    'label' => 'Nama Depan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'nama_belakang',
                    'label' => 'Nama Belakang',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $store_name   = $this->body('nama_toko');
            $email        = $this->body('email');
            $phone        = $this->body('no_tlp');
            $first_name   = $this->body('nama_depan');
            $last_name    = $this->body('nama_belakang');

            $requestProfile = [
                'company'    => $store_name,
                'email'      => $email,
                'phone'      => $phone,
                'first_name' => $first_name,
                'last_name'  => $last_name
            ];

            $updateProfile = $this->at_auth->updateAT($auth->user->id, $requestProfile);

            if (!$updateProfile) {
                throw new \Exception("Gagal memperbarui profil");
            }

            $profile        = $this->at_auth->find($auth->user->id);
            if ($phone != $profile->phone) {
                if (!$profile->phone_is_verified) {
                    $message = 'Lakukan verifikasi pada no telepon';
                } else {
                    $message = 'Jika anda mengganti No Telepon maka dibutuhkan verifikasi ulang';
                }
            }

            $response = [
                'user_id'   => $auth->user->id,
                'profile'   => $requestProfile,
                'message'   => $message
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan peurbahan profile", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function generate_phone_otp_get()
    {
        $this->db->trans_begin();
        try {
            $auth                     = $this->authorize();
            $user_id                  = $auth->user->id;
            $phone_otp_valid_until    = $this->input->get('phone_otp_valid_until'); #jika melakukan pemanggilan fungsi ini untuk selanjutnya harus menngirim parameter berikut yang bisa di dapat pada response
            $valid_until              = strtotime($phone_otp_valid_until);
            $dateNow                  = strtotime('now');

            if ($valid_until) {
                if ($dateNow < $valid_until) {
                    $left_time = (int) abs(($dateNow - $valid_until) / 60);
                    throw new \Exception("Belum bisa mengirim kode verifikasi, tunggu $left_time menit lagi.");
                }
            }

            $gpo = $this->at_auth->generatePhoneOTP($user_id);
            if (!$gpo) {
                throw new \Exception("Tidak dapat membuat kode verifikasi");
            }
            $user       = $this->at_auth->find($user_id);
            $message = $this->site->makeMessage('sms_forget_password', [
                'otp_code' => $user->phone_otp
            ]);

            $send = $this->at_site->send_sms_otp($user->phone, $message, true);
            if (!$send) {
                throw new \Exception("Tidak dapat mengirim kode verifikasi");
            }

            $phone_otp_valid_until    = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime('now')));
            $type                     = "success";
            $message                  = "Kode verifikasi berhasil dikirim";
            $timeleft                 = strtotime('+5 minutes', strtotime('now')) - strtotime('now');

            $response = [
                'type'                  => $type,
                'message'               => $message,
                'timeleft'              => $timeleft,
                'phone_otp_valid_until' => $phone_otp_valid_until
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan generate otp", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function verify_phone_otp_post()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();

            $config = [
                [
                    'field' => 'phone_otp',
                    'label' => 'Otp No. Telepon',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $otp    = $this->body('phone_otp');
            $verify = $this->at_auth->verifyPhoneOTP($auth->user->id, $otp);

            if (!$verify) {
                throw new \Exception("Tidak dapat verifikasi No Telepon, kode verifikasi salah.");
            }

            $response = [
                'user_id' => $auth->user->id
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan verifikasi No Telepon.", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_address_post()
    {
        $this->db->trans_begin();
        try {
            $auth       = $this->authorize();

            $config = [
                [
                    'field' => 'nama',
                    'label' => 'Nama Penerima',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'alamat',
                    'label' => 'Alamat',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'kabupaten',
                    'label' => 'Kabupaten',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'kecamatan',
                    'label' => 'Kecamatan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'desa',
                    'label' => 'Desa',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'kode_pos',
                    'label' => 'Kode Pos',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'provinsi',
                    'label' => 'Provinsi',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'no_tlp',
                    'label' => 'No. Telepon',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'email',
                    'label' => 'email',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $name           = $this->body('nama');
            $address        = $this->body('alamat');
            $city           = $this->body('kabupaten');
            $state          = $this->body('kecamatan');
            $village        = $this->body('desa');
            $postal_code    = $this->body('kode_pos');
            $country        = $this->body('provinsi');
            $phone          = $this->body('no_tlp');
            $email          = $this->body('email');

            $company    = ($this->at_site->getCompanyByID($auth->company->id));

            $requestAddress = [
                'group_id'            => null,
                'group_name'          => 'address',
                'company_id'          => $company->id,
                'customer_group_id'   => $company->customer_group_id,
                'customer_group_name' => $company->customer_group_name,
                'name'                => $name,
                'company'             => $company->company,
                'vat_no'              => $company->vat_no,
                'region'              => $company->region,
                'address'             => $address,
                'city'                => $city,
                'state'               => $state,
                'village'             => $village,
                'postal_code'         => $postal_code,
                'country'             => $country,
                'phone'               => $phone,
                'email'               => $email,
                'cf1'                 => $company->cf1,
                'cf2'                 => $company->cf2,
                'cf3'                 => $company->cf3,
                'cf4'                 => $company->cf4,
                'cf5'                 => $company->cf5,
                'cf6'                 => $company->cf6,
                'invoice_footer'      => null,
                'payment_term'        => 0,
                'logo'                => 'logo.png',
                'award_points'        => 0,
                'deposit_amount'      => 0,
                'price_group_id'      => $company->price_group_id,
                'price_group_name'    => $company->price_group_name,
                'client_id'           => null,
                'flag'                => null,
                'is_deleted'          => null,
                'device_id'           => null,
                'uuid'                => null,
                'uuid_app'            => null,
                'manager_area'        => null,
                'mtid'                => null,
                'latitude'            => null,
                'longitude'           => null,
            ];

            $insertCompany = $this->at_company->addCompany($requestAddress);

            if (!$insertCompany) {
                throw new \Exception("Gagal melakukan penambahan alamat baru");
            }

            $response = [
                'name'    => $name,
                'address' => $$address,
                'company' => $company->company
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan penambahan alamat", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_address_put()
    {
        $this->db->trans_begin();
        try {
            $auth       = $this->authorize();

            $config = [
                [
                    'field' => 'address_id',
                    'label' => 'Address ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'nama',
                    'label' => 'Nama Penerima',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'alamat',
                    'label' => 'Alamat',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'kabupaten',
                    'label' => 'Kabupaten',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'kecamatan',
                    'label' => 'Kecamatan',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'desa',
                    'label' => 'Desa',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'kode_pos',
                    'label' => 'Kode Pos',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'provinsi',
                    'label' => 'Provinsi',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'no_tlp',
                    'label' => 'No. Telepon',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'email',
                    'label' => 'email',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $id             = $this->body('address_id');
            $name           = $this->body('nama');
            $address        = $this->body('alamat');
            $city           = $this->body('kabupaten');
            $state          = $this->body('kecamatan');
            $village        = $this->body('desa');
            $postal_code    = $this->body('kode_pos');
            $country        = $this->body('provinsi');
            $phone          = $this->body('no_tlp');
            $email          = $this->body('email');

            $company        = ($this->at_site->getCompanyByID($auth->company->id));

            $requestAddress = [
                'name'          => $name,
                'company'       => $company->company,
                'address'       => $address,
                'city'          => $city,
                'state'         => $state,
                'village'       => $village,
                'postal_code'   => $postal_code,
                'country'       => $country,
                'phone'         => $phone,
                'email'         => $email
            ];

            $updateCompany = $this->at_company->updateCompany($id, $requestAddress);
            if (!$updateCompany) {
                throw new \Exception("Gagal melakukan pembaruan alamat");
            }

            $response = $requestAddress;

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pembaruan alamat", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function delete_address_post()
    {
        $this->db->trans_begin();
        try {
            $auth       = $this->authorize();

            $config = [
                [
                    'field' => 'address_id',
                    'label' => 'Address ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $id               = $this->body('address_id');
            $address          = $this->at_company->getCompanyByID($id);

            if ($address->group_name != 'address') {
                throw new \Exception("Gagal melakukan penghapusan alamat, " . $address->company . " merupakan alamat utama", 400);
            }

            $deleteCompany    = $this->at_company->softDeleteAddress($id, $auth->company->id);
            if (!$deleteCompany) {
                throw new \Exception("Gagal melakukan penghapusan alamat");
            }

            $response = [
                'address_id'  => $id,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan penghapusan alamat", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_sales_person_put()
    {
        $this->db->trans_begin();
        try {
            $auth       = $this->authorize();

            $config = [
                [
                    'field' => 'sales_person',
                    'label' => 'Sales Person',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $sales_person   = $this->body('sales_person');

            $salesPerson    = $this->site->getSalesPersonByRefNo($sales_person);

            if (!$salesPerson) {
                throw new \Exception("Salesperson dengan Kode Referal tersebut Tidak Ditemukan");
            }

            $idSalesPerson = $salesPerson ? $salesPerson->id : null;
            $salesPersonRef = $salesPerson ? $salesPerson->reference_no : null;

            $requestProfile = [
                'sales_person_id' => $idSalesPerson,
                'sales_person_ref' => $salesPersonRef
            ];

            $updateProfile = $this->at_auth->updateUserSalesPerson($auth->user->id, $requestProfile, $auth->company->id);
            if (!$updateProfile) {
                throw new \Exception("Gagal melakukan pembaruan sales person");
            }

            $response = $requestProfile;

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan pembaruan sales person", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_password_put()
    {
        $this->db->trans_begin();
        try {
            $auth       = $this->authorize();

            $config = [
                [
                    'field' => 'password_lama',
                    'label' => 'Password Lama',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'password_baru',
                    'label' => 'Password Baru',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ], [
                    'field' => 'ulangi_password_baru',
                    'label' => 'Ulangi Password Baru',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s is required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $old_password          = $this->body('password_lama');
            $new_password          = $this->body('password_baru');
            $retype_new_password   = $this->body('ulangi_password_baru');

            if ($new_password !== $retype_new_password) {
                throw new \Exception("Password baru dan Ulangi password baru tidak sama");
            }

            if (!$this->validatePassword($new_password)) {
                throw new \Exception("Kata Sandi minimal 8 karakter kombinasi dari huruf besar, huruf kecil dan angka");
            }

            $changePassword = $this->at_auth->changePasswordAT($auth->user->username, $old_password, $new_password);
            if (!$changePassword) {
                throw new \Exception("Gagal terjadi kesalahan pada saat melakukan perubahan password");
            }

            $response = [
                'user_id'  => $auth->user->id
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Berhasil melakukan perubahan password", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------------------------------------//
    private function validatePassword($string)
    {
        $containsUpper    = preg_match('/[A-Z]/', $string);
        $containsLower    = preg_match('/[a-z]/', $string);
        $containsDigit    = preg_match('/\d/', $string);
        $long8            = strlen($string) >= 8;

        if ($containsUpper && $containsLower && $containsDigit && $long8) {
            return true;
        }
        return false;
    }
    //------------------------------------------------------------------------------------------------------------------------------------------------------------//
}

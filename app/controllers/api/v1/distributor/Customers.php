<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Distributor_Controller.php';

class Customers extends MY_API_Distributor_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('companies_model');
        $this->load->model('settings_model');
        $this->load->model('authorized_model');
        $this->load->model('integration_model');
    }

    public function list_customers_get()
    {
        $this->db->trans_begin();
        try {
            $auth       = $this->authorize();
            $search     = $this->input->get('search');
            $limit      = $this->input->get('limit');
            $offset     = $this->input->get('offset');
            $sortby     = $this->input->get('sortby');
            $sorttype   = $this->input->get('sorttype');

            if ($search) {
                $filter = "(sma_companies.name LIKE '%{$search}%' OR sma_companies.company LIKE '%{$search}%' OR sma_companies.cf1 LIKE '%{$search}%')";
            }
            if ($limit || $offset || $sortby || $sorttype) {
                $company        = $this->companies_model->getAllCustomerPaging($auth->company->id, $filter, $limit, $offset, $sortby, $sorttype);
                $all_company    = $this->companies_model->getCustomerAll($auth->company->id, $filter);
            } else {
                $company = $this->companies_model->getCustomerByDistributorId($auth->company->id, $filter);
            }

            if (!$company) {
                throw new Exception('Sorry, data not found', 404);
            }
            if ($limit != null) {
                $response = [
                    "limit"                => $limit,
                    "offset"               => $offset,
                    "rows"                 => $all_company,
                    "count"                => count($company),
                    "list_customers"       => $company
                ];
            } else {
                $response = [
                    'rows'           => count($company),
                    'list_customers' => $company,
                ];
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Customers success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_customers_get()
    {
        $this->db->trans_begin();
        try {
            $auth         = $this->authorize();
            $id_customers   = $this->input->get('id_customers');

            $company = $this->companies_model->getCompanyByID($id_customers, $auth->company->id);

            if (!$company) {
                throw new Exception('Sorry, data not found', 404);
            }

            $response = [
                'customer' => $company,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Customers success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function customers_suggestions_get()
    {
        $this->db->trans_begin();
        try {

            $auth           = $this->authorize();
            $keywords       = $this->input->get('keywords', true);
            $limit          = $this->input->get('limit', true);
            $warehouse_id   = $this->input->get('warehouse_id', true);

            if (strlen($keywords) < 1) {
                throw new Exception("Get Customers Suggestions failed, because can't get the keywords", 404);
            }

            $company        = $this->companies_model->getSuggestionsCustomers(trim($keywords), $limit, $warehouse_id, $auth->company->id);

            if (!$company) {
                throw new Exception('Sorry, data not found', 404);
            }

            $response = [
                'customer' => $company,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Customers Suggestions success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function customers_groups_get()
    {
        $this->db->trans_begin();
        try {
            $auth                       = $this->authorize();
            $customer_groups            = $this->companies_model->getAllCustomerGroups($auth->company->id);
            $x                          = array_search($auth->company->id, array_column($customer_groups, 'company_id'));
            $default_customer_groups    = $customer_groups[$x];

            if (!$customer_groups) {
                throw new Exception('Sorry, data not found', 404);
            }

            $response = [
                'total_customer_groups'   => count($customer_groups),
                'customer_groups'         => $customer_groups,
                'default_customer_groups' => $default_customer_groups,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Customers Groups success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function price_groups_get()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();
            $price_groups   = $this->companies_model->getAllPriceGroups($auth->user->company_id);

            if (!$price_groups) {
                throw new Exception('Sorry, data not found', 404);
            }

            $response = [
                'total_price_groups'   => count($price_groups),
                'price_groups'         => $price_groups,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Price Groups success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_customers_post()
    {
        $this->db->trans_begin();
        try {
            $auth                 = $this->authorize();
            $config = [
                [
                    'field' => 'company',
                    'label' => 'Company Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'name',
                    'label' => 'Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'phone',
                    'label' => 'Phone',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'address',
                    'label' => 'Address',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'province',
                    'label' => 'Province',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'city',
                    'label' => 'City',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'state',
                    'label' => 'State',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
                [
                    'field' => 'email',
                    'label' => 'email',
                    'rules' => 'trim|is_unique[companies.email]',
                    'errors' => [
                        'is_unique' => '%s has already taken',
                    ],
                ]
            ];
            $this->validate_form($config);

            $name                 = $this->post('name');
            $email                = $this->post('email');
            $customer_group_id    = $this->post('customer_group_id') ?? null;
            $cg                   = $this->site->getCustomerGroupByID($customer_group_id);
            $customer_group_name  = $cg->name ?? null;
            $price_group_id       = $this->post('price_group_id') ?? null;
            $pg                   = $this->site->getPriceGroupByID($price_group_id);
            $price_group_name     = $pg->name ?? null;
            $company              = $this->post('company');
            $address              = $this->post('address');
            $vat_no               = $this->post('vat_no');
            $city                 = $this->post('city');
            $state                = $this->post('state');
            $postal_code          = $this->post('postal_code');
            $country              = $this->post('province');
            $phone                = $this->post('phone');
            $cf1                  = $this->post('cf1');
            $cf2                  = $this->post('cf2');
            $cf3                  = $this->post('cf3');
            $cf4                  = $this->post('cf4');
            $cf5                  = $this->post('cf5');
            $cf6                  = $this->post('cf6');
            $is_active            = $this->post('is_active') ? 1 : 0;
            $distributor          = $auth->company->id;
            $isLimited            = $this->authorized_model->isCustomerLimited($auth->company->id);

            if ($isLimited["status"]) {
                $message = str_replace("xxx", $isLimited["max"], "You have reached the master data limit (Max xxx yyy)");
                $message = str_replace("yyy", "Customer", $message);
                throw new Exception('Post Add Customer failed because' . $message, 503);
            }
            $data = [
                'name'                => $name,
                'email'               => $email,
                'group_id'            => '3',
                'group_name'          => 'customer',
                'customer_group_id'   => $customer_group_id ?? null,
                'customer_group_name' => $customer_group_name ?? null,
                'price_group_id'      => $price_group_id ? $price_group_id : null,
                'price_group_name'    => $price_group_name ? $price_group_name : null,
                'company'             => $company,
                'company_id'          => $distributor,
                'address'             => $address,
                'vat_no'              => $vat_no,
                'city'                => $city,
                'state'               => $state,
                'postal_code'         => $postal_code,
                'country'             => $country,
                'phone'               => $phone,
                'cf1'                 => $cf1,
                'cf2'                 => $cf2,
                'cf3'                 => $cf3,
                'cf4'                 => $cf4,
                'cf5'                 => $cf5,
                'cf6'                 => $cf6,
                'is_active'           => $is_active
            ];

            $addcustomer = $this->companies_model->addCompany($data);

            if (!$addcustomer) {
                throw new Exception("Post Add Customer failed");
            }

            $list_warehouse       = $this->post('warehouses');
            $default_warehouse    = $this->post('default');
            if ($list_warehouse) {
                if (!$default_warehouse) {
                    throw new Exception("Post Add Customer failed because cant get the default warehouse", 404);
                }
            }
            for ($j = 0; $j < count($list_warehouse); $j++) {
                $Cdata                  = [];
                $Cdata['customer_id']   = $addcustomer;
                $Cdata['customer_name'] = $company;
                $Cdata['warehouse_id']  = $list_warehouse[$j];
                $Cdata['default']       = $default_warehouse;
                $Cdata['created_by']    = $Cdata['updated_by'] = $auth->company->id;
                $Cdata['created_at']    = $Cdata['updated_at'] = date('Y-m-d H:i:s');
                if (!$this->site->addWarehouseCustomer($Cdata)) {
                    throw new \Exception('Post Add Customer failed, Because insert warehouses failed');
                }
            }

            $response = [
                "id" => $addcustomer,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Customer success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function upload_file_customer_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();

            $id                   = $this->input->get('id_customer');
            $customer             = $this->companies_model->getCompanyByID($id);
            if (!$customer) {
                throw new Exception('Post upload file customer failed, because data is not found', 404);
            }

            if ($_FILES['logo']['size'] < 0) {
                throw new Exception('Post upload file customer failed, because size less then zero', 404);
            }

            /*$this->load->library('upload');
            $config['upload_path']    = 'assets/uploads/avatars/';
            $config['allowed_types']  = 'gif|jpg|jpeg|png|tif';
            $config['max_size']       = '2000';
            $config['overwrite']      = false;
            $config['max_filename']   = 25;
            $config['encrypt_name']   = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('logo')) {
                $error = $this->upload->display_errors();
                throw new Exception('Post upload logo customer,' . $error, 404);
            }
            $photo                      = $this->upload->file_name;
            $data['logo']               = $photo;

            $this->load->library('image_lib');
            $config['image_library']    = 'gd2';
            $config['source_image']     = 'assets/uploads/avatars/' . $photo;
            $config['new_image']        = 'assets/uploads/avatars/thumbs/' . $photo;
            $config['maintain_ratio']   = true;
            $config['width']            = 150;
            $config['height']           = 150;
            $this->image_lib->clear();
            $this->image_lib->initialize($config);
            if (!$this->image_lib->resize()) {
                throw new Exception('Post upload file customer failed, because ' . $this->image_lib->display_errors(), 404);
            }
            $this->image_lib->clear();*/
            $uploadedImg    = $this->integration_model->upload_files($_FILES['logo']);
            $photo          = $uploadedImg->url;
            $data['logo']   = $photo;
            $config         = null;

            $updatecustomer = $this->companies_model->updateCompany($id, $data);

            if (!$updatecustomer) {
                throw new Exception("Post upload file customer failed");
            }

            $response = [
                "id" => $id,
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post upload file customer success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_customers_put()
    {
        $this->db->trans_begin();
        try {
            $auth                 = $this->authorize();
            $id                   = $this->input->get('id_customer');
            if (!$id) {
                throw new Exception("Put Update Customer failed because cant get the id customer value", 404);
            }
            $config = [
                [
                    'field' => 'company',
                    'label' => 'Company Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'name',
                    'label' => 'Name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'phone',
                    'label' => 'Phone',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'address',
                    'label' => 'Address',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'province',
                    'label' => 'Province',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'city',
                    'label' => 'City',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'state',
                    'label' => 'State',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $customer             = $this->companies_model->getCompanyByID($id);
            if (!$customer) {
                throw new Exception('Put Update Customer failed because data is not found', 404);
            }

            if ($this->body('email') != $customer->email) {
                $config_email = [
                    [
                        'field' => 'email',
                        'label' => 'email',
                        'rules' => "trim|edit_unique[companies.email.$id]",
                        'errors' => [
                            'edit_unique' => '%s has already taken',
                        ],
                    ]
                ];
                $this->validate_form($config_email);
            }

            $name                 = $this->body('name') ?? $customer->name;
            $email                = $this->body('email') ?? $customer->email;
            $customer_group_id    = $this->body('customer_group_id') ?? $customer->customer_group_id;
            $cg                   = $this->site->getCustomerGroupByID($customer_group_id);
            $customer_group_name  = $cg->name ? $cg->name : $customer->customer_group_name;
            $price_group_id       = $this->body('price_group_id') ?? $customer->price_group_id;
            $pg                   = $this->site->getPriceGroupByID($price_group_id);
            $price_group_name     = $pg->name ? $pg->name : $customer->price_group_name;
            $company              = $this->body('company') ?? $customer->company;
            $address              = $this->body('address') ?? $customer->address;
            $vat_no               = $this->body('vat_no') ?? $customer->vat_no;
            $city                 = $this->body('city') ?? $customer->city;
            $state                = $this->body('state') ?? $customer->state;
            $postal_code          = $this->body('postal_code') ?? $customer->postal_code;
            $country              = $this->body('province') ?? $customer->country;
            $phone                = $this->body('phone') ?? $customer->phone;
            $cf1                  = $this->body('cf1') ?? $customer->cf1;
            $cf2                  = $this->body('cf2') ?? $customer->cf2;
            $cf3                  = $this->body('cf3') ?? $customer->cf3;
            $cf4                  = $this->body('cf4') ?? $customer->cf4;
            $cf5                  = $this->body('cf5') ?? $customer->cf5;
            $cf6                  = $this->body('cf6') ?? $customer->cf6;
            $is_active            = $this->body('is_active') ? 1 : 0;
            $distributor          = $this->body('distributor') ?? $auth->company->id;

            $data = [
                'name'                => $name,
                'email'               => $email,
                'group_id'            => '3',
                'group_name'          => 'customer',
                'customer_group_id'   => $customer_group_id ? $customer_group_id : null,
                'customer_group_name' => $customer_group_name ? $customer_group_name : null,
                'price_group_id'      => $price_group_id ? $price_group_id : null,
                'price_group_name'    => $price_group_name ? $price_group_name : null,
                'company'             => $company,
                'company_id'          => $distributor,
                'address'             => $address,
                'vat_no'              => $vat_no,
                'city'                => $city,
                'state'               => $state,
                'postal_code'         => $postal_code,
                'country'             => $country,
                'phone'               => $phone,
                'cf1'                 => $cf1,
                'cf2'                 => $cf2,
                'cf3'                 => $cf3,
                'cf4'                 => $cf4,
                'cf5'                 => $cf5,
                'cf6'                 => $cf6,
                'is_active'           => $is_active
            ];

            $updatecustomer = $this->companies_model->updateCompany($id, $data);

            if (!$updatecustomer) {
                throw new Exception("Put Update Customer failed");
            }

            $list_warehouse       = $this->body('warehouses');
            $default_warehouse    = $this->body('default');
            if ($list_warehouse) {
                if (!$default_warehouse) {
                    throw new Exception("Put Update Customer failed because cant get the default warehouse", 404);
                }
            }
            if ($auth->company->group_id == 5 || $auth->company->group_id == 8) {
                foreach ($this->site->getWarehouseCustomer($auth->user->warehouse_id, $id) as $Customer) {
                    $check_warehouse[$Customer->warehouse_id] = $Customer->warehouse_id;
                }
            } else {
                foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                    $check_warehouse[$Customer->warehouse_id] = $Customer->warehouse_id;
                }
            }

            foreach ($list_warehouse as $warehouse_id) {
                $Cdata                    = [];
                $Cdata['customer_name']   = $company;
                $Cdata['updated_by']      = $auth->company->id;
                $Cdata['updated_at']      = date('Y-m-d H:i:s');
                if ($this->site->getWarehouseCustomer($warehouse_id, $id)) {
                    $Cdata['is_deleted'] = 0;
                    unset($check_warehouse[$warehouse_id]);
                    if (!$this->site->updateWarehouseCustomer($warehouse_id, $id, $Cdata)) {
                        throw new \Exception('Put Update Customer failed, Because update warehouses failed');
                    }
                } else {
                    $Cdata['customer_id']   = $id;
                    $Cdata['warehouse_id']  = $warehouse_id;
                    $Cdata['created_by']    = $auth->company->id;
                    $Cdata['created_at']    = date('Y-m-d H:i:s');
                    if (!$this->site->addWarehouseCustomer($Cdata)) {
                        throw new \Exception('Put Update Customer failed, Because insert warehouses failed');
                    }
                }
            }

            foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                $Cdata              = [];
                $Cdata['default']   = $default_warehouse;
                if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $id, $Cdata)) {
                    throw new \Exception('Put Update Customer failed, Because update default warehouses failed');
                }
            }

            if (sizeOf($check_warehouse) > 0) {
                foreach ($check_warehouse as $w_id) {
                    $Cdata                    = [];
                    $Cdata['customer_name']   = $company;
                    $Cdata['is_deleted']      = 1;
                    $Cdata['updated_by']      = $auth->company->id;
                    $Cdata['updated_at']      = date('Y-m-d H:i:s');
                    if (!$this->site->updateWarehouseCustomer($w_id, $id, $Cdata)) {
                        throw new \Exception('Put Update Customer failed, Because delete warehouses failed');
                    }
                }
            }

            # cek default warehouse dan set default warehouse yang valid
            $getDefault = 0;
            foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                if ($Customer->is_deleted == 0) {
                    $validWarehouse[] = $Customer->warehouse_id;
                }
                $getDefault = $Customer->default;
            }

            $checkDefault = 0;
            for ($i = 0; $i < sizeof($validWarehouse); $i++) {
                if ($validWarehouse[$i] == $getDefault) {
                    $checkDefault++;
                }
            }

            if (sizeof($validWarehouse) > 0) {
                if ($checkDefault == 0) {
                    foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                        $Cdata              = [];
                        $Cdata['default']   = $validWarehouse[0];
                        if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $id, $Cdata)) {
                            throw new \Exception('Put Update Customer failed, Because update validation default warehouses customer failed');
                        }
                    }
                }
            } else {
                foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                    $Cdata              = [];
                    $Cdata['default']   = 0;
                    if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $id, $Cdata)) {
                        throw new \Exception('Put Update Customer failed, Because update validation warehouses customer failed');
                    }
                }
            }

            $response = [
                "id" => $id,
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Update Customer success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
    public function list_customer_warehouse_get()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();
            $id_customers   = $this->input->get('id_customers');

            if (!$id_customers) {
                throw new Exception("Get List Customer Warehouse failed, because can't get the id customer value", 404);
            }

            $warehouses           = $this->site->getAllWarehousesCustomer($auth->company->id);
            if (!$warehouses) {
                $warehouses = [];
            }
            $warehouses_selected  = $this->site->getWarehouseCustomerByCustomer($id_customers);
            if (!$warehouses_selected) {
                $warehouses_selected = [];
            }
            $warehouse_default    = $this->site->getWarehouseCustomerDefault($auth->company->id, $id_customers);
            if (!$warehouse_default) {
                $warehouse_default = [];
            }
            $response = [
                'warehouses'            => $warehouses,
                'warehouses_selected'   => $warehouses_selected,
                'warehouses_default'    => $warehouse_default
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Customer Warehouse success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
    public function add_or_edit_customer_to_customer_group_post()
    {
        $this->db->trans_begin();

        try {
            $auth                 = $this->authorize();

            $id_customer          = $this->post('id_customer');
            $id_customer_group    = $this->input->get('id_customer_group');

            if (!$id_customer_group) {
                throw new Exception("Post Add Or Edit Customer To Customer Group failed because cant get the id customer group value", 404);
            }

            $customer_group = $this->settings_model->getCustomerGroupByID($id_customer_group);
            if (!$customer_group) {
                throw new Exception('Sorry, data not found', 404);
            }

            $this->settings_model->updateAllCustomerByCustomerGroupId($id_customer_group, $auth->company->id);

            foreach ($id_customer as $value) {
                $data = [
                    'customer_group_id'   => $id_customer_group,
                    'customer_group_name' => $customer_group->name
                ];

                if (!$this->companies_model->updateCompany($value, $data)) {
                    throw new Exception('Post Add Or Edit Customer To Customer Group failed');
                }
            }

            $response = [
                "customer_group_id"   => $id_customer_group,
                'customer_group_name' => $customer_group->name,
                "id_customer"         => $id_customer
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Or Edit Customer To Customer Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_customer_member_of_customer_group_get()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();

            $id_customer_group = $this->input->get('id_customer_group');

            if (!$id_customer_group) {
                throw new Exception("Get Customer To Customer Group failed because cant get the id customer group value", 404);
            }

            $customer_group = $this->settings_model->getCustomerGroupByID($id_customer_group);
            if (!$customer_group) {
                throw new Exception('Sorry, data not found', 404);
            }

            $customer_to_customer_group_selected    = $this->settings_model->getCustomerOfCustomerGroup($id_customer_group, $auth->company->id);
            if ($customer_to_customer_group_selected != NULL) {
                foreach ($customer_to_customer_group_selected as $value) {
                    $data_selected[] = [
                        'customer_id'           => $value->id,
                        'customer_company'      => $value->company,
                        'customer_name'         => $value->name,
                        'customer_phone'        => $value->phone,
                        'customer_cf1'          => $value->cf1,
                        'customer_province'     => $value->country,
                        'customer_city'         => $value->city,
                        'customer_state'        => $value->state,
                        'customer_group_id'     => $value->customer_group_id,
                        'customer_group_name'   => $value->customer_group_name
                    ];
                }
            } else {
                $data_selected = [];
            }

            $list_customer_to_customer_group        = $this->settings_model->getListCustomerToCustomerGroups($id_customer_group, $auth->company->id);
            if ($list_customer_to_customer_group != NULL) {
                foreach ($list_customer_to_customer_group as $value) {
                    $list_data_customer[] = [
                        'customer_id'           => $value->id,
                        'customer_company'      => $value->company,
                        'customer_name'         => $value->name,
                        'customer_phone'        => $value->phone,
                        'customer_cf1'          => $value->cf1,
                        'customer_province'     => $value->country,
                        'customer_city'         => $value->city,
                        'customer_state'        => $value->state,
                        'customer_group_id'     => $value->customer_group_id,
                        'customer_group_name'   => $value->customer_group_name
                    ];
                }
            } else {
                $list_data_customer = [];
            }

            $response = [
                "customer_group_id"         => $id_customer_group,
                "customer_group_name"       => $customer_group->name,
                "total_customer_selected"   => count($data_selected),
                "customer_selected"         => $data_selected,
                "total_list_customer"       => count($list_data_customer),
                "list_customer"             => $list_data_customer
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Customer To Cutomer Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_or_edit_customer_to_price_group_post()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();

            $id_customer    = $this->post('id_customer');
            $id_price_group = $this->input->get('id_price_group');

            if (!$id_price_group) {
                throw new Exception("Post Add Or Edit Customer To Price Group failed because cant get the id price group value", 404);
            }

            $price_group = $this->settings_model->getPriceGroupByID($id_price_group);

            if (!$price_group) {
                throw new Exception('Sorry, data not found', 404);
            }

            $this->settings_model->updateAllCustomerByPriceGroup($id_price_group);

            foreach ($id_customer as $value) {
                $data = [
                    'price_group_id'   => $id_price_group,
                    'price_group_name' => $price_group->name
                ];

                if (!$this->companies_model->updateCompany($value, $data)) {
                    throw new Exception('Post Add Or Edit Customer To Price Group failed');
                }
            }
            $response = [
                "price_group_id"   => $id_price_group,
                'price_group_name' => $price_group->name,
                "id_customer"      => $id_customer
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Or Edit Customer To Price Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_customer_member_of_price_group_get()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();

            $id_price_group = $this->input->get('id_price_group');

            if (!$id_price_group) {
                throw new Exception("Get Customer To Price Group failed because cant get the id price group value", 404);
            }

            $price_group            = $this->settings_model->getPriceGroupByID($id_price_group);

            if (!$price_group) {
                throw new Exception('Sorry, data not found', 404);
            }

            $customer_price_group_selected    = $this->settings_model->getCustomerPriceGroup($id_price_group);
            if (!$customer_price_group_selected) {
                $data_selected = [];
            }
            foreach ($customer_price_group_selected as $value) {
                $data_selected[] = [
                    'customer_id'       => $value->id,
                    'customer_company'  => $value->company,
                    'customer_name'     => $value->name,
                    'customer_phone'    => $value->phone,
                    'customer_cf1'      => $value->cf1,
                    'customer_province' => $value->country,
                    'customer_city'     => $value->city,
                    'customer_state'    => $value->state,
                    'price_group_id'    => $value->price_group_id,
                    'price_group_name'  => $value->price_group_name
                ];
            }

            $list_customer_price_group        = $this->settings_model->getListCustomerToPriceGroups($id_price_group, $auth->company->id);

            if ($list_customer_price_group != NULL) {
                foreach ($list_customer_price_group as $value) {
                    $list_data_customer[] = [
                        'customer_id'       => $value->id,
                        'customer_company'  => $value->company,
                        'customer_name'     => $value->name,
                        'customer_phone'    => $value->phone,
                        'customer_cf1'      => $value->cf1,
                        'customer_province' => $value->country,
                        'customer_city'     => $value->city,
                        'customer_state'    => $value->state,
                        'price_group_id'    => $value->price_group_id,
                        'price_group_name'  => $value->price_group_name
                    ];
                }
            } else {
                $list_data_customer = [];
            }

            $response = [
                "price_group_id"          => $id_price_group,
                "price_group_name"        => $price_group->name,
                "total_customer_selected" => count($data_selected),
                "customer_selected"       => $data_selected,
                "total_list_customer"     => count($list_data_customer),
                "list_customer"           => $list_data_customer
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Customer To Price Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_customer_group_post()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();
            $config = [
                [
                    'field' => 'name',
                    'label' => 'Customer group Name',
                    'rules' => 'trim|required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'percentage',
                    'label' => 'Percentage',
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => '%s required is numeric',
                    ],
                ], [
                    'field' => 'credit_limit',
                    'label' => 'Credit Limit',
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => '%s required is numeric',
                    ],
                ]
            ];
            $this->validate_form($config);

            $name         = $this->post('name');
            $percentage   = $this->post('percentage');
            $company_id   = $auth->user->company_id;
            $credit_limit = $this->post('credit_limit');

            if ($this->settings_model->checkCustomerGroupByName($company_id, $name) > 0) {
                throw new Exception('Post Add Customer Group failed because the name in the customer group already exists', 500);
            }

            $data = [
                'name'         => $name,
                'percent'      => $percentage,
                'company_id'   => $company_id,
                'kredit_limit' => $credit_limit,
            ];

            $add_customer_group = $this->settings_model->addCustomerGroup($data);

            if (!$add_customer_group) {
                throw new Exception('Post Add Customer Group failed');
            }
            $response = [
                'name'         => $name,
                "percentage"   => $percentage,
                "company_id"   => $company_id,
                "credit_limit" => $credit_limit
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Customer Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_customer_group_put()
    {
        $this->db->trans_begin();

        try {
            $auth   = $this->authorize();
            $config = [
                [
                    'field' => 'name',
                    'label' => 'Customer group Name',
                    'rules' => 'trim|required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'percentage',
                    'label' => 'Percentage',
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => '%s required is numeric',
                    ],
                ], [
                    'field' => 'credit_limit',
                    'label' => 'Credit Limit',
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => '%s required is numeric',
                    ],
                ]
            ];
            $this->validate_form($config);

            $id               = $this->input->get('id_customer_group');
            if (!$id) {
                throw new Exception("Put Update Customer Group failed because cant get the id customer group value", 404);
            }

            $customer_group   = $this->settings_model->getCustomerGroupByID($id);

            $name             = $this->body('name') ?? $customer_group->name;
            $percentage       = $this->body('percentage') ?? $customer_group->precent;
            $company_id       = $auth->user->company_id;
            $credit_limit     = $this->body('credit_limit') ?? $customer_group->kredit_limit;


            if ($name != $customer_group->name) {
                if ($this->settings_model->checkCustomerGroupByName($company_id, $name) > 0) {
                    throw new Exception('Put Update Customer Group failed because the name in the customer group already exists', 500);
                }
            }

            $data = [
                'name'         => $name,
                'percent'      => $percentage,
                'kredit_limit' => $credit_limit,
            ];

            $update_customer_group = $this->settings_model->updateCustomerGroup($id, $data);

            if (!$update_customer_group) {
                throw new Exception('Put Update Customer Group failed');
            }

            $response = [
                'id'           => $id,
                'name'         => $name,
                "percentage"   => $percentage,
                "company_id"   => $company_id,
                "credit_limit" => $credit_limit
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Update Customer Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_customer_group_get()
    {
        $this->db->trans_begin();

        try {
            $auth   = $this->authorize();
            $id     = $this->input->get('id_customer_group');

            if (!$id) {
                throw new Exception("Get Detail Customer Group failed because cant get the id customer group value", 404);
            }

            $customer_group   = $this->settings_model->getCustomerGroupByID($id);
            $company_id       = $auth->user->company_id;

            $response = [
                'id'           => $id,
                'name'         => $customer_group->name,
                "percentage"   => $customer_group->precent,
                "company_id"   => $company_id,
                "credit_limit" => $customer_group->kredit_limit
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Customer Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_price_group_post()
    {
        $this->db->trans_begin();

        try {

            $auth   = $this->authorize();
            $config = [
                [
                    'field' => 'name',
                    'label' => 'Price group name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ]
            ];
            $this->validate_form($config);

            $name         = $this->post('name');
            $company_id   = $auth->user->company_id;

            if ($this->settings_model->checkPriceGroupByName($company_id, $name) > 0) {
                throw new Exception('Post Add Price Group failed because the name in the price group already exists', 500);
            }

            $data = [
                'name'         => $name,
                'company_id'   => $company_id,
            ];

            $add_price_group = $this->settings_model->addPriceGroup($data);

            if (!$add_price_group) {
                throw new Exception('Post Add Price Group failed');
            }
            $response = [
                'name'         => $name,
                "company_id"   => $company_id,
                "warehouse_id" => $warehouse_id
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Price Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_price_group_put()
    {
        $this->db->trans_begin();

        try {
            $auth   = $this->authorize();
            $config = [
                [
                    'field' => 'name',
                    'label' => 'Price group name',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ]
            ];
            $this->validate_form($config);

            $id               = $this->input->get('id_price_group');
            if (!$id) {
                throw new Exception("Put Update Price Group failed because cant get the id price group value", 404);
            }

            $price_group    = $this->settings_model->getPriceGroupByID($id);

            $name           = $this->body('name') ?? $price_group->name;
            $company_id     = $auth->user->company_id;


            if ($name != $price_group->name) {
                if ($this->settings_model->checkPriceGroupByName($company_id, $name) > 0) {
                    throw new Exception('Put Update Price Group failed because the name in the price group already exists', 500);
                }
            }

            $data = [
                'name'         => $name,
            ];

            $update_price_group = $this->settings_model->updatePriceGroup($id, $data);

            if (!$update_price_group) {
                throw new Exception('Put Update Price Group failed');
            }

            $response = [
                'id'           => $id,
                'name'         => $name,
                "company_id"   => $company_id,
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Update Price Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function group_product_in_prices_group_get()
    {
        $this->db->trans_begin();

        try {
            $auth   = $this->authorize();
            $id     = $this->input->get('id_price_group');
            if (!$id) {
                throw new Exception("Get Group Product In Price Group failed because cant get the id price group value", 404);
            }

            $price_group    = $this->settings_model->getPriceGroupByID($id);

            $group_product  = $this->settings_model->getProductPricesGroup($id, $auth->company->id);

            foreach ($group_product as $value) {
                $data[] = $value;
            }

            $response = [
                'price_group_id'            => $price_group->id,
                'price_group_name'          => $price_group->name,
                'total_group_product_price' => count($data),
                'group_product_price'       => $data
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Group Product In Price Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_product_in_price_group_put()
    {
        $this->db->trans_begin();

        try {
            $auth   = $this->authorize();

            $config = [
                [
                    'field' => 'product_id',
                    'label' => 'Product ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'price',
                    'label' => 'Price',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'price_credit',
                    'label' => 'Price Credit',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'min_order',
                    'label' => 'Minimum Order',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ], [
                    'field' => 'is_multiple',
                    'label' => 'Warehouse ID',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '%s required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $id               = $this->input->get('id_price_group');
            if (!$id) {
                throw new Exception("Put Update Product In Price Group failed because cant get the id price group value", 404);
            }

            $product_id       = $this->body('product_id');

            $group_product    = $this->settings_model->getProductPricesGroup($id, $auth->company->id, $product_id);

            if (!$group_product) {
                throw new Exception('Put Update Product In Price Group failed because cant find the product', 404);
            }

            $price            = $this->body('price') ?? $group_product[0]->price;
            $price_kredit     = $this->body('price_credit') ?? $group_product[0]->price_kredit;
            $min_order        = $this->body('min_order') ?? $group_product[0]->min_order;
            $is_multiple      = $this->body('is_multiple') ?? $group_product[0]->is_multiple;

            $update_price_group = $this->settings_model->setProductPriceForPriceGroup($product_id, $id, $price, $price_kredit, $min_order, $is_multiple);

            if (!$update_price_group) {
                throw new Exception('Put Update Product In Price Group failed');
            }

            $response = [
                'product_id'    => $product_id,
                "product_code"  => $group_product[0]->product_code,
                "product_name"  => $group_product[0]->product_name,
                'price'         => $price,
                "price_kredit"  => $price_kredit,
                "min_order"     => $min_order,
                "unit_name"     => $group_product[0]->unit_name,
                "is_multiple"   => $is_multiple
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Update Product In Price Group success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function sync_customer_to_bk_post()
    {
        $this->db->trans_begin();
        try {
            $auth   = $this->authorize();
            $code   = $auth->company->cf1;
            if ($this->LT) {
                if (explode('-', $auth->company->cf1, -1)[0] == 'IDC') {
                    $code = explode('-', $auth->company->cf1, 2)[1];
                } elseif (explode('-', $auth->company->cf2, -1)[0] == 'IDC') {
                    $code = explode('-', $auth->company->cf2, 2)[1];
                } elseif (explode('-', $auth->company->cf3, -1)[0] == 'IDC') {
                    $code = explode('-', $auth->company->cf3, 2)[1];
                } elseif (explode('-', $auth->company->cf4, -1)[0] == 'IDC') {
                    $code = explode('-', $auth->company->cf4, 2)[1];
                } elseif (explode('-', $auth->company->cf5, -1)[0] == 'IDC') {
                    $code = explode('-', $auth->company->cf5, 2)[1];
                }
                $this->ltsycn($auth->user->company_id, $code);
            } else if (strtoupper($auth->company->cf6) == "SBI") {
                $this->distributorsbisycn($auth->user->company_id, $code);
            } else {
                $this->distributorsycn($auth->user->company_id, $code);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    function randomemail()
    {
        $karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
        $string = '';
        for ($i = 0; $i < 5; $i++) {
            $pos = rand(0, strlen($karakter) - 1);
            $string .= $karakter{
                $pos};
        }
        return $string;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    private function distributorsbisycn($company_id, $kode_distributor)
    {
        $response_1         = $this->companies_model->getDataTokoAktif($kode_distributor);
        $response           = $response_1;
        if (!$response['status'] || $response['status'] == 'empty') {
            $response_2   = $this->companies_model->getDataTokoAktif(str_pad($kode_distributor, 10, '0', STR_PAD_LEFT));
            if (!$response_2['status'] || $response_2['status'] == 'empty') {
                throw new Exception('Post Sync customer to bk failed because data store active not found');
            }
            $response = $response_2;
        }
        $jumlah   = 0;
        $this->db->update('companies', ['flag_bk' => 0], ['company_id' => $company_id, 'group_name' => 'customer']);
        foreach ($response['data'] as $row['data']) {
            $cf1            = 'IDC-' . $row['data']['KD_CUSTOMER'];
            $customer       = $this->companies_model->findCompanyByCf1AndCompanyId($company_id, $cf1);
            if ($customer != NULL) {
                $data = array(
                    'flag_bk'    => '1',
                    'updated_at' => date('Y-m-d H:i:s')
                );
                if ($this->companies_model->updateCompany($customer->id, $data)) {
                    $this->db->trans_commit();
                    $jumlah += 1;
                } else {
                    $this->db->trans_rollback();
                    continue;
                }
            } else {
                $email = $row['data']['KD_CUSTOMER'] . '@' . $this->randomemail() . '.com';
                $data = array(
                    'name'                  => $row['data']['NM_CUSTOMER'],
                    'email'                 => $email,
                    'group_id'              => '3',
                    'group_name'            => 'customer',
                    'customer_group_id'     => '1',
                    'customer_group_name'   => 'General',
                    'company_id'            => $company_id,
                    'company'               => $row['data']['NAMA_TOKO'],
                    'address'               => $row['data']['ALAMAT_TOKO'] ? $row['data']['ALAMAT_TOKO'] : ($row['data']['ADDRESS'] ? $row['data']['ADDRESS'] : '-'),
                    'city'                  => $row['data']['NM_DISTRIK'],
                    'state'                 => $row['data']['KECAMATAN'],
                    'postal_code'           => $row['data']['KD_PROVINSI'],
                    'country'               => $row['data']['PROVINSI'],
                    'phone'                 => $row['data']['NO_HANDPHONE'] ? $row['data']['NO_HANDPHONE'] : ($row['data']['NO_TELP_TOKO'] ? $row['data']['NO_TELP_TOKO'] : '-'),
                    'cf1'                   => $cf1,
                    'is_active'             => '1',
                    'latitude'              => $row['data']['LATITUDE'] ?? '-',
                    'longitude'             => $row['data']['LONGITUDE'] ?? '-',
                    'flag_bk'               => '1',
                    'created_at'            => date('Y-m-d H:i:s')
                );
                if ($this->companies_model->addCompany($data)) {
                    $this->db->trans_commit();
                    $jumlah += 1;
                } else {
                    $this->db->trans_rollback();
                    continue;
                }
            }
        }
        if ($jumlah != $response['num_rows']) {
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'Post Sync customer to bk success ' . $jumlah . ' from ' . $response['num_rows'] . 'data');
        } else {
            $response = [
                'total_customer_data' => $jumlah
            ];
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Sync customer to bk success", $response);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    private function distributorsycn($company_id, $kode_distributor)
    {
        $response_1         = $this->companies_model->getDataTokoAktif($kode_distributor);
        $response           = $response_1;
        if (!$response['status'] || $response['status'] == 'empty') {
            $response_2   = $this->companies_model->getDataTokoAktif(str_pad($kode_distributor, 10, '0', STR_PAD_LEFT));
            if (!$response_2['status'] || $response_2['status'] == 'empty') {
                throw new Exception('Post Sync customer to bk failed because data store active not found');
            }
            $response = $response_2;
        }
        $jumlah   = 0;
        $count    = 0;
        $this->db->update('companies', ['flag_bk' => 0], ['company_id' => $company_id, 'group_name' => 'customer']);
        foreach ($response['data'] as $row['data']) {
            if ($row['data']['GROUP_CUSTOMER'] == 'LT' || $row['data']['KD_LT'] == NULL) {
                $count          += 1;
                $cf1            = 'IDC-' . $row['data']['KD_CUSTOMER'];
                $customer       = $this->companies_model->findCompanyByCf1AndCompanyId($company_id, $cf1);
                if ($customer != NULL) {
                    $data = array(
                        'flag_bk'    => '1',
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->companies_model->updateCompany($customer->id, $data)) {
                        $this->db->trans_commit();
                        $jumlah += 1;
                    } else {
                        $this->db->trans_rollback();
                        continue;
                    }
                } else {
                    $email = $row['data']['KD_CUSTOMER'] . '@' . $this->randomemail() . '.com';
                    $data = array(
                        'name'                  => $row['data']['NM_CUSTOMER'],
                        'email'                 => $email,
                        'group_id'              => '3',
                        'group_name'            => 'customer',
                        'customer_group_id'     => '1',
                        'customer_group_name'   => 'General',
                        'company_id'            => $company_id,
                        'company'               => $row['data']['NAMA_TOKO'],
                        'address'               => $row['data']['ALAMAT_TOKO'] ? $row['data']['ALAMAT_TOKO'] : ($row['data']['ADDRESS'] ? $row['data']['ADDRESS'] : '-'),
                        'city'                  => $row['data']['NM_DISTRIK'],
                        'state'                 => $row['data']['KECAMATAN'],
                        'postal_code'           => $row['data']['KD_PROVINSI'],
                        'country'               => $row['data']['PROVINSI'],
                        'phone'                 => $row['data']['NO_HANDPHONE'] ? $row['data']['NO_HANDPHONE'] : ($row['data']['NO_TELP_TOKO'] ? $row['data']['NO_TELP_TOKO'] : '-'),
                        'cf1'                   => $cf1,
                        'is_active'             => '1',
                        'latitude'              => $row['data']['LATITUDE'] ?? '-',
                        'longitude'             => $row['data']['LONGITUDE'] ?? '-',
                        'flag_bk'               => '1',
                        'created_at'            => date('Y-m-d H:i:s')
                    );
                    if ($this->companies_model->addCompany($data)) {
                        $this->db->trans_commit();
                        $jumlah += 1;
                    } else {
                        $this->db->trans_rollback();
                        continue;
                    }
                }
            } else {
                continue;
            }
        }
        if ($jumlah != $count) {
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'Post Sync customer to bk success ' . $jumlah . ' from ' . $count . 'data');
        } else {
            $response = [
                'total_customer_data' => $jumlah
            ];
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Sync customer to bk success", $response);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    private function ltsycn($company_id, $kode_lt)
    {
        if ($this->LT) {
            $response_1         = $this->companies_model->cekDataLT($kode_lt);
            if (!$response_1['status'] || $response_1['status'] == 'empty') {
                throw new Exception("Post Sync customer to bk failed because ID $kode_lt cannot be found in Bisnis Kokoh");
            }
            $response         = $this->companies_model->getDataTokoAktif($response_1['data'][0]['NOMOR_DISTRIBUTOR']);
            if (!$response['status'] || $response['status'] == 'empty') {
                $response_3   = $this->companies_model->getDataTokoAktif(str_pad($kode_lt, 10, '0', STR_PAD_LEFT));
                if (!$response_3['status'] || $response_3['status'] == 'empty') {
                    throw new Exception('Post Sync customer to bk failed because data store active not found');
                }
                $response = $response_3;
            }
            $jumlah = 0;
            $count  = 0;
            $this->db->update('companies', ['flag_bk' => 0], ['company_id' => $company_id, 'group_name' => 'customer']);
            foreach ($response['data'] as $row['data']) {
                if ($row['data']['KD_LT'] == $response_1['data'][0]['KD_LT'] && $row['data']['KD_CUSTOMER'] != $response_1['data'][0]['KD_CUSTOMER']) {
                    $count          += 1;
                    $cf1            = 'IDC-' . $row['data']['KD_CUSTOMER'];
                    $customer       = $this->companies_model->findCompanyByCf1AndCompanyId($company_id, $cf1);
                    if ($customer != NULL) {
                        $data = array(
                            'flag_bk'    => '1',
                            'updated_at' => date('Y-m-d H:i:s')
                        );
                        if ($this->companies_model->updateCompany($customer->id, $data)) {
                            $this->db->trans_commit();
                            $jumlah += 1;
                        } else {
                            $this->db->trans_rollback();
                            continue;
                        }
                    } else {
                        $email = $row['data']['KD_CUSTOMER'] . '@' . $this->randomemail() . '.com';
                        $data = array(
                            'name'                  => $row['data']['NM_CUSTOMER'],
                            'email'                 => $email,
                            'group_id'              => '3',
                            'group_name'            => 'customer',
                            'customer_group_id'     => '1',
                            'customer_group_name'   => 'General',
                            'company_id'            => $company_id,
                            'company'               => $row['data']['NAMA_TOKO'],
                            'address'               => $row['data']['ALAMAT_TOKO'] ? $row['data']['ALAMAT_TOKO'] : ($row['data']['ADDRESS'] ? $row['data']['ADDRESS'] : '-'),
                            'city'                  => $row['data']['NM_DISTRIK'],
                            'state'                 => $row['data']['KECAMATAN'],
                            'postal_code'           => $row['data']['KD_PROVINSI'],
                            'country'               => $row['data']['PROVINSI'],
                            'phone'                 => $row['data']['NO_HANDPHONE'] ? $row['data']['NO_HANDPHONE'] : ($row['data']['NO_TELP_TOKO'] ? $row['data']['NO_TELP_TOKO'] : '-'),
                            'cf1'                   => $cf1,
                            'is_active'             => '1',
                            'latitude'              => $row['data']['LATITUDE'] ?? '-',
                            'longitude'             => $row['data']['LONGITUDE'] ?? '-',
                            'flag_bk'               => '1',
                            'created_at'            => date('Y-m-d H:i:s')
                        );
                        if ($this->companies_model->addCompany($data)) {
                            $this->db->trans_commit();
                            $jumlah += 1;
                        } else {
                            $this->db->trans_rollback();
                            continue;
                        }
                    }
                } else {
                    continue;
                }
            }
            if ($jumlah != $count) {
                $this->buildResponse("success", REST_Controller::HTTP_OK, 'Post Sync customer to bk success ' . $jumlah . ' from ' . $count . 'data');
            } else {
                $response = [
                    'total_customer_data' => $jumlah
                ];
                $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Sync customer to bk success", $response);
            }
        } else {
            throw new Exception("Post Sync customer to bk failed because ID $kode_lt not included LT");
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Distributor_Controller.php';

class Warehouses extends MY_API_Distributor_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('site');
        $this->load->model('settings_model');
    }

    public function list_warehouses_get()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            $search = $this->input->get('search');


            if ($search) {
                $where = "(`code` LIKE '%{$search}%' OR `name` LIKE '%{$search}%')";
            }

            $warehouses = $this->site->getAllWarehouses($auth->company->id, $where);

            if (!$warehouses) {
                throw new Exception('Sorry, data not found', 404);
            }

            $response = [
                "total_warehouses" => count($warehouses),
                "list_warehouses" => $warehouses
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Warehouses success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function detail_warehouses_get()
    {
        $this->db->trans_begin();
        try {
            $auth             = $this->authorize();
            $id_warehouses    = $this->input->get('id_warehouse');

            $warehouses       = $this->site->getWarehouseByID($id_warehouses, $auth->company->id);
            if (!$warehouses) {
                throw new Exception('Sorry, data not found', 404);
            }

            $response = [
                "detail_warehouses" => $warehouses
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Warehouses success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_customer_in_warehouse_get()
    {
        $this->db->trans_begin();

        try {
            $auth           = $this->authorize();

            $id_warehouses = $this->input->get('id_warehouse');

            if (!$id_warehouses) {
                throw new Exception("Get Customer In Warehouse failed because cant get the id warehouse value", 404);
            }

            $warehouses       = $this->site->getWarehouseByID($id_warehouses, $auth->company->id);

            if (!$warehouses) {
                throw new Exception('Sorry, data not found', 404);
            }

            $customer_warehouse_selected    = $this->site->getCustomerWarehouse($id_warehouses, $auth->company->id);
            if ($customer_warehouse_selected != NULL) {
                foreach ($customer_warehouse_selected as $value) {
                    $data_selected[] = [
                        'customer_id'             => $value->id,
                        'customer_company'        => $value->company,
                        'customer_name'           => $value->name,
                        'customer_phone'          => $value->phone,
                        'customer_cf1'            => $value->cf1,
                        'customer_province'       => $value->country,
                        'customer_city'           => $value->city,
                        'customer_state'          => $value->state,
                        'warehouse_id'            => $warehouses->id,
                        'warehouse_name'          => $warehouses->name,
                        'default'                 => $value->default,
                        'warehouse_default_id'    => $value->default_id,
                        'warehouse_default_name'  => $value->warehouses_name,
                    ];
                }
            } else {
                $data_selected = [];
            }

            $list_customer_warehouse        = $this->site->getListCustomerWarehouse($id_warehouses, $auth->company->id);

            if ($list_customer_warehouse != NULL) {
                foreach ($list_customer_warehouse as $value) {
                    $list_data_customer[] = [
                        'customer_id'             => $value->id,
                        'customer_company'        => $value->company,
                        'customer_name'           => $value->name,
                        'customer_phone'          => $value->phone,
                        'customer_cf1'            => $value->cf1,
                        'customer_province'       => $value->country,
                        'customer_city'           => $value->city,
                        'customer_state'          => $value->state,
                        'warehouse_id'            => $warehouses->id,
                        'warehouse_name'          => $warehouses->name,
                        'default'                 => $value->default,
                        'warehouse_default_id'    => $value->default_id,
                        'warehouse_default_name'  => $value->warehouses_name,
                    ];
                }
            } else {
                $list_data_customer = [];
            }

            $response = [
                "warehouse_id"            => $id_warehouses,
                "warehouse_name"          => $warehouses->name,
                "total_customer_selected" => count($data_selected),
                "customer_selected"       => $data_selected,
                "total_list_customer"     => count($list_data_customer),
                "list_customer"           => $list_data_customer
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Customer In Warehouse success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function add_or_edit_customer_to_warehouse_post()
    {
        $this->db->trans_begin();

        try {
            $auth                 = $this->authorize();
            $warehouse_id         = $this->body('warehouse_id');
            if (!$warehouse_id) {
                throw new \Exception("Post Add Or Edit Customer To Warehouse failed, because can't get the warehouse id");
            }
            $customers            = $this->body('customers');
            $default_warehouse    = $this->body('default_warehouse');

            foreach ($this->settings_model->getWarehouseCustomer($warehouse_id) as $WarehouseCustomer) {
                $check_customer[$WarehouseCustomer->customer_id] = $WarehouseCustomer->customer_id;
            }

            foreach ($customers as $customer) {
                $data = [];
                $data['updated_by'] = $auth->company->id;
                $data['updated_at'] = date('Y-m-d H:i:s');
                if ($this->settings_model->getWarehouseCustomer($warehouse_id, false, $customer['customer_id'])) {
                    $data['is_deleted'] = 0;
                    unset($check_customer[$customer['customer_id']]);
                    if (!$this->settings_model->updateWarehouseCustomer($customer['customer_id'], $warehouse_id, $data)) {
                        throw new \Exception('Post Add Or Edit Customer To Warehouse failed, because failed update warehouse customer');
                    }
                } else {
                    $data['customer_id']    = $customer['customer_id'];
                    $data['customer_name']  = $customer['customer_name'];
                    $data['warehouse_id']   = $warehouse_id;
                    $data['default']        = $customer['customer_default'];
                    $data['created_by']     = $auth->company->id;
                    $data['created_at']     = date('Y-m-d H:i:s');
                    if (!$this->settings_model->addWarehouseCustomer($data)) {
                        throw new \Exception('Post Add Or Edit Customer To Warehouse failed, because failed insert warehouse customer');
                    }
                }
            }

            if (!$default_warehouse == '') {
                foreach ($default_warehouse as $row) {
                    foreach ($this->settings_model->getWarehouseCustomerByCustomer($row) as $Customer) {
                        $Cdata['default'] = $warehouse_id;
                        if (!$this->settings_model->updateWarehouseCustomer($Customer->customer_id, $Customer->warehouse_id, $Cdata)) {
                            throw new \Exception('Post Add Or Edit Customer To Warehouse failed, because failed update warehouse default customer');
                        }
                    }
                }
            }

            if (sizeOf($check_customer) > 0) {
                foreach ($check_customer as $row) {
                    $data                 = [];
                    $data['is_deleted']   = 1;
                    $data['updated_by']   = $auth->company->id;
                    $data['updated_at']   = date('Y-m-d H:i:s');
                    if (!$this->settings_model->updateWarehouseCustomer($row, $warehouse_id, $data)) {
                        throw new \Exception('Post Add Or Edit Customer To Warehouse failed, because failed delete warehouse customer');
                    }
                }
            }

            # cek default warehouse dan set default warehouse yang valid
            foreach ($this->settings_model->getWarehouseCustomer($warehouse_id) as $WarehouseCustomer) {
                $check_customer_default[] = $WarehouseCustomer->customer_id;
            }
            foreach ($check_customer_default as $row) {
                $getDefault = 0;
                foreach ($this->settings_model->getWarehouseCustomerByCustomer($row) as $Customer) {
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
                        foreach ($this->settings_model->getWarehouseCustomerByCustomer($row) as $Customer) {
                            $Cdata = array();
                            $Cdata['default'] = $validWarehouse[0];
                            if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $row, $Cdata)) {
                                throw new \Exception('Post Add Or Edit Customer To Warehouse failed, because failed update customer warehouse default');
                            }
                        }
                    }
                } else {
                    foreach ($this->settings_model->getWarehouseCustomerByCustomer($row) as $Customer) {
                        $Cdata = array();
                        $Cdata['default'] = 0;
                        if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $row, $Cdata)) {
                            throw new \Exception('Post Add Or Edit Customer To Warehouse failed, because failed update customer default warehouse');
                        }
                    }
                }
            }

            $response = [
                "warehouse_id"      => $warehouse_id
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Add Or Edit Customer To Warehouse success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}

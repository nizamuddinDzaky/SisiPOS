<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class Customers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('customers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->model('authorized_model');
        $this->load->model('companies_model');
        $this->load->model('sales_person_model');
        $this->load->model('integration_model');
    }

    public function index($action = null)
    {
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action']   = $action;
        $this->data['billers']  = $this->companies_model->getAllBillerCompanies();

        $link_type = ['mb_customers', 'mb_add_customer', 'mb_edit_customer'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('customers')));
        $meta = array('page_title' => lang('customers'), 'bc' => $bc);

        if ($this->Principal) {
            $this->page_construct('customers/index_principal', $meta, $this->data);
        } else {
            $this->page_construct('customers/index', $meta, $this->data);
        }
    }

    public function getCustomers($distributor = null)
    {
        ini_set('memory_limit', '4096M');
        $this->load->library('datatables');
        $join = "(  SELECT id, cf1, company FROM sma_companies WHERE group_name = 'biller' AND (client_id is null OR client_id != 'aksestoko') ) join_a";
        if ($this->Principal) {
            $this->datatables
                ->select("companies.id as id, companies.company, companies.name, companies.email, companies.phone, companies.price_group_name, companies.country, companies.city, companies.state, companies.customer_group_name, companies.vat_no, companies.deposit_amount, companies.award_points, companies.cf1, join_a.cf1 as distributor_code, join_a.company as distributor_name, companies.is_deleted")
                ->from("companies")
                ->join($join, 'join_a.id = companies.company_id', 'left')
                ->where('companies.group_name', 'customer');
        } else {
            $provinsi   = $this->input->get('provinsi') ? $this->input->get('provinsi') : null;
            $kabupaten  = $this->input->get('kabupaten') ? $this->input->get('kabupaten') : null;
            $kecamatan  = $this->input->get('kecamatan') ? $this->input->get('kecamatan') : null;
            $this->datatables->select("
                sma_companies.id as id,     sma_companies.company,              sma_companies.name,     sma_companies.email,
                sma_companies.phone,        sma_companies.price_group_name,     sma_companies.country,  sma_companies.city,
                sma_companies.state,        sma_companies.customer_group_name,  sma_companies.vat_no,   sma_companies.deposit_amount,
                sma_companies.award_points, sma_companies.cf1");

            $this->datatables->from("sma_companies");
            if ($this->session->userdata('group_id') == 5 || $this->session->userdata('group_id') == 8) {
                $join = "(  SELECT * FROM sma_warehouse_customer WHERE warehouse_id != {$this->session->userdata('warehouse_id')} AND customer_id NOT IN ( SELECT customer_id FROM sma_warehouse_customer WHERE warehouse_id = {$this->session->userdata('warehouse_id')} AND is_deleted = 0) AND is_deleted = 0 ) join_a";
                // $this->datatables->join($join1, 'join_a.customer_id = companies.id', 'inner'); // Hanya customer dari warehousse $this->session->userdata('warehouse_id')
                $this->datatables->join($join, 'join_a.customer_id = companies.id', 'left');
                $this->datatables->where('join_a.customer_id is NULL');
            }

            $this->datatables->where('sma_companies.group_name', 'customer');
            if ($provinsi) {
                $this->datatables->like('sma_companies.country', $provinsi);
            }
            if ($kabupaten) {
                $this->datatables->like('sma_companies.city', $kabupaten);
            }
            if ($kecamatan) {
                $this->datatables->like('sma_companies.state', $kecamatan);
            }
        }

        if (!is_null($distributor)) {
            $this->datatables->where('company_id', $distributor);
        }

        if (!$this->Owner) {
            if (!$this->Principal) {
                $this->datatables->where('company_id', $this->session->userdata('company_id'));
            }
        }
        if (!$this->Principal) {
            $this->datatables->where('sma_companies.is_deleted', null);
        }

        if ($this->Principal) {
            $this->datatables->add_column("Actions", "");
            $this->datatables->add_column("Action_delete", "
                <div class=\"text-center\">
                <a class=\"tip\" title='" . lang("list_addresses") . "' href='" . site_url('customers/addresses/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-location-arrow\"></i></a>
                <a class=\"tip\" title='" . lang("list_users") . "' href='" . site_url('customers/users/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-users\"></i>
                </a>
                <a class=\"tip\" title='" . lang("edit_customer") . "' id='customersEdit' href='" . site_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                <i class=\"fa fa-edit\"></i></a>
                <a href='#' class='tip po' title='<b>" . lang("delete_customer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> </div>", "id");
            $this->datatables->add_column("Action_recover", "
                <div class=\"text-center\">
                <a class=\"tip\" title='" . lang("list_addresses") . "' href='" . site_url('customers/addresses/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-location-arrow\"></i></a>
                <a class=\"tip\" title='" . lang("list_users") . "' href='" . site_url('customers/users/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-users\"></i>
                </a>
                <a class=\"tip\" title='" . lang("edit_customer") . "' id='customersEdit' href='" . site_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                <i class=\"fa fa-edit\"></i></a>
                <a href='#' class='tip po' title='<b>" . lang("recover_customer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('customers/recover/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-recycle\"></i></a> </div>", "id");
        } else {
            $this->datatables->add_column("Actions", "
                <div class=\"text-center\">
                <a class=\"tip\" title='" . lang("list_deposits") . "' href='" . site_url('customers/deposits/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-money\"></i></a> <a class=\"tip\" title='" . lang("add_deposit") . "' href='" . site_url('customers/add_deposit/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-plus\"></i>
                </a> 
                <a class=\"tip\" title='" . lang("list_addresses") . "' href='" . site_url('customers/addresses/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-location-arrow\"></i></a>
                <a class=\"tip\" title='" . lang("list_users") . "' href='" . site_url('customers/users/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-users\"></i>
                </a>
                <a class=\"tip\" title='" . lang("edit_customer") . "' id='customersEdit' href='" . site_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                <i class=\"fa fa-edit\"></i></a>
                </div>", "id");
        }
        echo $this->datatables->generate();
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $customer = $this->companies_model->getCompanyByID($id);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $customer;
        $this->data['warehouse_customer'] = $this->site->getWarehousesCustomer($id);
        $this->data['warehouse_default'] = $this->site->getWarehouseDefault($customer->company_id, $id);
        $this->data['sales_person'] = $this->sales_person_model->getSalesPersonById($customer->sales_person_id);
        $this->load->view($this->theme . 'customers/view', $this->data);
    }

    public function add()
    {
        $this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('provinsi', lang('provinsi'), 'required');

        if ($this->form_validation->run('companies/add') == true) {

            if ($this->Principal) {
                if ($this->input->post('distributor') != '') {
                    $distributor = $this->input->post('distributor');
                } else {
                    $this->session->set_flashdata('error', 'Distributor Harus Diisi');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $distributor = $this->session->userdata('company_id');
            }
            //melakukan pengecekan terhadap customer apakah sudah mencapai limit
            $isLimited = $this->authorized_model->isCustomerLimited($this->session->userdata('company_id'));
            if ($isLimited["status"]) {
                $message = str_replace("xxx", $isLimited["max"], lang("limited_master"));
                $message = str_replace("yyy", lang("customers"), $message);
                $this->session->set_flashdata('error', $message);
                redirect("customers");
            }
            // akhir cek
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $sp = $this->sales_person_model->getSalesPersonById($this->input->post('sales_person'));

            // print_r($sp);die;
            $data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => 3,
                'group_name' => 'customer',
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : null,
                'company' => $this->input->post('company'),
                'company_id' => $distributor,
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('kabupaten'),
                'state' => $this->input->post('kecamatan'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('provinsi'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'sales_person_id' => $this->input->post('sales_person'),
                'sales_person_ref' => $sp->reference_no,
            );
            // print_r($data);die;

            $this->load->library('upload');
            if ($_FILES['logo']['size'] > 0) {
                $file                       = $this->integration_model->upload_files($_FILES['logo']);
                $photo                      = $file->url;
                $data['logo']               = $photo;
                /*$config['upload_path']    = 'assets/uploads/avatars/';
                $config['allowed_types']  = 'gif|jpg|jpeg|png|tif';
                $config['max_size']       = '50';
                $config['max_width']      = $this->Settings->iwidth;
                $config['max_height']     = $this->Settings->iheight;
                $config['overwrite']      = false;
                $config['max_filename']   = 25;
                $config['encrypt_name']   = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("customers");
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
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();*/
                $config = null;
            }
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $list_warehouse = $this->input->post('warehouses');
            for ($j = 0; $j < count($list_warehouse); $j++) {
                $Cdata = array();
                $Cdata['customer_id'] = $cid;
                $Cdata['customer_name'] = $this->input->post('company');
                $Cdata['warehouse_id'] = $list_warehouse[$j];
                $Cdata['default'] = $this->input->post('default');
                $Cdata['created_by'] = $Cdata['updated_by'] = $this->session->userdata('company_id');
                $Cdata['created_at'] = $Cdata['updated_at'] = date('Y-m-d H:i:s');
                if (!$this->site->addWarehouseCustomer($Cdata)) {
                    throw new \Exception('insert failed');
                }
            }

            $this->session->set_flashdata('message', lang("customer_added"));
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : null;
            redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups($this->session->userdata('company_id')) ?? [];
            $x = array_search($this->session->userdata('company_id'), array_column($this->data['customer_groups'], 'company_id'));
            $this->data['default_customer_groups'] = $this->data['customer_groups'][$x];
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups() ?? [];
            $this->data['sales_persons'] = $this->companies_model->getAllSalesPerson($this->session->userdata('company_id')) ?? [];
            $this->data['warehouses'] = $this->site->getAllWarehouses(null, ['company_id' => $this->session->userdata('company_id')]) ?? [];
            $this->load->view($this->theme . 'customers/add', $this->data);
        }
    }

    public function edit($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->findCf1ById($id);
        $distributor = $this->companies_model->findCf1ById($company_details->company_id);

        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');
            $this->data['a'] = true;
        }

        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $sp = $this->sales_person_model->getSalesPersonById($this->input->post('sales_person'));

            $data = array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : null,
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1') ?? $company_details->cf1,
                'cf2' => $this->input->post('cf2') ?? $company_details->cf2,
                'cf3' => $this->input->post('cf3') ?? $company_details->cf3,
                'cf4' => $this->input->post('cf4') ?? $company_details->cf4,
                'cf5' => $this->input->post('cf5') ?? $company_details->cf5,
                'cf6' => $this->input->post('cf6') ?? $company_details->cf6,
                'is_active' => $this->input->post('is_active') ? 1 : 0,
                'award_points' => $this->input->post('award_points'),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'sales_person_id' => $this->input->post('sales_person'),
                'sales_person_ref' => $sp->reference_no,
            );
            $this->load->library('upload');
            if ($_FILES['logo']['size'] > 0) {
                $file                       = $this->integration_model->upload_files($_FILES['logo']);
                $photo                      = $file->url;
                $data['logo']               = $photo;
                /*$config['upload_path']    = 'assets/uploads/avatars/';
                $config['allowed_types']  = 'gif|jpg|jpeg|png|tif';
                $config['max_size']       = '50';
                $config['max_width']      = $this->Settings->iwidth;
                $config['max_height']     = $this->Settings->iheight;
                $config['overwrite']      = false;
                $config['max_filename']   = 25;
                $config['encrypt_name']   = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("customers");
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
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();*/
                $config = null;
            }
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            if ($this->session->userdata('group_id') == 5 || $this->session->userdata('group_id') == 8) {
                foreach ($this->site->getWarehouseCustomer($this->session->userdata('warehouse_id'), $id) as $Customer) {
                    $check_warehouse[$Customer->warehouse_id] = $Customer->warehouse_id;
                }
            } else {
                foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                    $check_warehouse[$Customer->warehouse_id] = $Customer->warehouse_id;
                }
            }

            foreach ($this->input->post("warehouses") as $value => $warehouse_id) {
                $Cdata = array();
                $Cdata['customer_name'] = $this->input->post('company');
                $Cdata['updated_by'] = $this->session->userdata('company_id');
                $Cdata['updated_at'] = date('Y-m-d H:i:s');
                if ($this->site->getWarehouseCustomer($warehouse_id, $id)) {
                    $Cdata['is_deleted'] = 0;
                    unset($check_warehouse[$warehouse_id]);
                    if (!$this->site->updateWarehouseCustomer($warehouse_id, $id, $Cdata)) {
                        throw new \Exception('update failed');
                    }
                } else {
                    $Cdata['customer_id'] = $id;
                    $Cdata['warehouse_id'] = $warehouse_id;
                    $Cdata['created_by'] = $this->session->userdata('company_id');
                    $Cdata['created_at'] = date('Y-m-d H:i:s');
                    if (!$this->site->addWarehouseCustomer($Cdata)) {
                        throw new \Exception('insert failed');
                    }
                }
            }

            foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                $Cdata = array();
                $Cdata['default'] = $this->input->post('default');
                if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $id, $Cdata)) {
                    throw new \Exception('update failed');
                }
            }

            if (sizeOf($check_warehouse) > 0) {
                foreach ($check_warehouse as $deleted => $w_id) {
                    $Cdata = array();
                    $Cdata['customer_name'] = $this->input->post('company');
                    $Cdata['is_deleted'] = 1;
                    $Cdata['updated_by'] = $this->session->userdata('company_id');
                    $Cdata['updated_at'] = date('Y-m-d H:i:s');
                    if (!$this->site->updateWarehouseCustomer($w_id, $id, $Cdata)) {
                        throw new \Exception('delete failed');
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
                        $Cdata = array();
                        $Cdata['default'] = $validWarehouse[0];
                        if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $id, $Cdata)) {
                            throw new \Exception('update failed');
                        }
                    }
                }
            } else {
                foreach ($this->companies_model->getWarehouseCustomerByCustomer($id) as $Customer) {
                    $Cdata = array();
                    $Cdata['default'] = 0;
                    if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $id, $Cdata)) {
                        throw new \Exception('update failed');
                    }
                }
            }

            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['customer']           = $company_details;
            $this->data['distributor']        = $distributor;
            $this->data['error']              = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']           = $this->site->modal_js();
            $this->data['customer_groups']    = $this->companies_model->getAllCustomerGroups($company_details->company_id);
            $this->data['price_groups']       = $this->companies_model->getAllPriceGroups($company_details->company_id);
            $this->data['sales_persons']      = $this->companies_model->getAllSalesPerson($company_details->company_id) ?? [];
            $this->data['salesperson']        = $this->sales_person_model->getSalesPersonById($company_details->sales_person_id);
            $this->data['warehouses']         = $this->site->getAllWarehouses(null, ['company_id' => $company_details->company_id]);
            $this->data['warehousesCustomer'] = $this->companies_model->getWarehouseCustomerByCustomer($id) ?? [];
            $this->data['warehouse_default'] = $this->site->getWarehouseDefault($company_details->company_id, $id) ?? [];
            $this->load->view($this->theme . 'customers/edit', $this->data);
        }
    }

    public function getWarehousesByBiller($distributor_id)
    {
        $data = $this->companies_model->getWarehouseByDistributor($distributor_id);
        echo json_encode($data);
    }

    public function users($company_id = null)
    {
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'customers/users', $this->data);
    }

    public function add_user($company_id = null)
    {
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active                     = $this->input->post('status');
            $notify                     = $this->input->post('notify');
            list($username, $domain)    = explode("@", $this->input->post('email'));
            $email                      = strtolower($this->input->post('email'));
            $password                   = $this->input->post('password');
            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'company_id' => $company->id,
                'company' => $company->company,
                'group_id' => 3
            );
            $this->load->library('ion_auth');
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', lang("user_added"));
            redirect("customers");
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

    public function import_csv()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang("upload_file"), 'xss_clean');
        $file_name = '';

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if (isset($_FILES["csv_file"])) {

                /*$this->load->library('upload');
                $path                     = 'assets/uploads/csv/';

                $config['upload_path']    = $path;
                $config['allowed_types']  = 'csv';
                $config['max_size']       = '2000';
                $config['overwrite']      = false;
                $config['encrypt_name']   = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("customers");
                }

                $csv = $this->upload->file_name;
                $file_name = $csv;
                $arrResult = array();
                $handle = fopen("assets/uploads/csv/" . $csv, "r");*/

                $file       = $this->integration_model->upload_files($_FILES['csv_file']);
                $file_name  = $file->url;
                $handle     = fopen($file_name, "r");

                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                if ($this->Principal) {
                    $keys = array('company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'company_id');
                } else {
                    $keys = array('company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no'); //'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6');
                }

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv) {
                    $company_id = $this->Principal ? $csv['company_id'] : $this->session->userdata('company_id');
                    if ($this->companies_model->getCompanyByEmail($csv['email'] ? $csv['email'] : null, 'customer', $company_id)) {
                        $this->session->set_flashdata('error', lang("check_customer_email") . " (" . $csv['email'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ") ");
                        redirect("customers");
                    }
                    if ($this->Principal) {
                        if (!$this->site->findUserByCompanyId($csv['company_id'])) {
                            $this->session->set_flashdata('error', lang("check_customer_distributor") . " (ID = " . $csv['company_id'] . "). " . lang("distributor_not_found") . " (" . lang("line_no") . " " . $rw . ") ");
                            redirect("customers");
                        }
                    }
                    $rw++;
                }
                foreach ($final as $record) {
                    $record['group_id']             = 3;
                    $record['group_name']           = 'customer';
                    $record['customer_group_id']    = 1;
                    $record['customer_group_name']  = 'General';
                    if (!$this->Principal) {
                        $record['company_id'] = $this->session->userdata('company_id');
                    }
                    $data[] = $record;
                }
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                // unlink($path . $file_name);
                unlink($file_name);
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data);
        }
    }

    public function update_by_excel()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('excel_file', lang("upload_file"), 'xss_clean');
        $file_name = '';

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if (isset($_FILES["excel_file"])) {
                /*$this->load->library('upload');
                $path = 'assets/uploads/csv/';

                $config['upload_path'] = $path;
                $config['allowed_types'] = 'xls|xlsx';
                $config['max_size'] = '3000';
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;

                $this->upload->initialize($config);
                if (!$this->upload->do_upload('excel_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("customers");
                }

                $excel = $path . $this->upload->file_name;
                $file_name = $excel;*/

                //$file       = $this->integration_model->upload_files($_FILES['excel_file']);
                //$file_name  = $file->url;
                //$excel      = $file_name;
                $excel = $_FILES['excel_file']['tmp_name'];

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                if ($reader) {
                    $reader->setReadDataOnly(true);
                    $spreadsheet    = $reader->load($excel);
                    $sheetData      = $spreadsheet->getActiveSheet()->toArray();
                    $arrResult      = array();
                    foreach ($sheetData as $k => $row) {
                        if ($k > 0) {
                            $arrResult[] = $row;
                        }
                    }

                    if ($this->Principal) {
                        $keys = array('id', 'company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'deposit_amount', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'is_deleted', 'distributor_code', 'distributor_name');
                    } else {
                        $keys = array('id', 'company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'deposit_amount', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'distributor_code', 'distributor_name');
                    }

                    $final = [];
                    foreach ($arrResult as $key => $value) {
                        $final[] = array_combine($keys, $value);
                    }

                    //mengambil company_id utk perbandingan
                    $comp_id = $this->session->userdata('company_id');
                    if ($this->Principal || $this->Owner) {
                        $select = 'group_name,id';
                        $get = $this->companies_model->getCompanyWhereNotIn(null, ['group_name' => 'customer'], $select);
                    } else {
                        $get = $this->companies_model->getCompanyByParent($comp_id);
                    }

                    $arr_comp_id = [];
                    foreach ($get as $key => $v) {
                        if ($v->group_name == 'customer') {
                            $arr_comp_id[] = $v->id;
                        }
                    }

                    $arr_id         = [];
                    $arr_company    = [];
                    $arr_email      = [];
                    $arr_id_comp    = [];
                    foreach ($final as $k => $excel) {
                        $arr_id[] = $excel['id'];
                        //mencari perbandingan id_customer yang tidak sama antara excel dan database 
                        if (!in_array($excel['id'], $arr_comp_id)) {
                            $arr_company[] = $excel['company'];
                        }

                        if (!$this->Principal) {
                            $select = 'id';
                            //mengecek jika ada email yang sama pada selain company id parent nya
                            $where_email = ['email' => $excel['email']];
                            if ($this->companies_model->getCompanyNotWhereId($comp_id, $where_email, $select)) {
                                $arr_email[] = $excel['email'];
                            }

                            //mengecek jika ada id yang sama pada selain company id parent nya
                            $where_id = ['id' => $excel['id']];
                            if ($this->companies_model->getCompanyNotWhereId($comp_id, $where_id, $select)) {
                                $arr_id_comp[] = $excel['id'];
                            }
                        }

                        if ($this->Principal) {
                            if ($final[$k]['is_deleted'] != '1') {
                                $final[$k]['is_deleted'] = null;
                            }
                        }

                        unset($final[$k]['distributor_code']);
                        unset($final[$k]['distributor_name']);

                        $final[$k]['updated_at'] = date('Y-m-d H:i:s');
                    }

                    //jika terdapat id_company yang beda dari db dn excel maka error
                    if (count($arr_company) > 0) {
                        $str_comp = '';
                        foreach ($arr_company as $key => $comp) {
                            if ($key == count($arr_company) - 1) {
                                $str_comp .= $comp;
                            } else {
                                $str_comp .= $comp . ', ';
                            }
                        }
                        $this->session->set_flashdata('error', lang("dont_change_id_customer") . ' : ' . $str_comp);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }

                    if (!$this->Principal) {
                        //jika terdapat id_customer yang sama antara customer selain parent dan file excel maka error
                        if (count($arr_id_comp) > 0) {
                            $str_comp_id = '';
                            foreach ($arr_id_comp as $key => $comp_id) {
                                if ($key == count($arr_id_comp) - 1) {
                                    $str_comp_id .= $comp_id;
                                } else {
                                    $str_comp_id .= $comp_id . ', ';
                                }
                            }
                            $this->session->set_flashdata('error', lang("file_has_duplicate_id_customer") . ' : ' . $str_comp_id);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }

                        //jika terdapat email yang sama antara customer selain parent dan file excel maka error
                        if (count($arr_email) > 0) {
                            $str_email = '';
                            foreach ($arr_email as $key => $email) {
                                if ($key == count($arr_email) - 1) {
                                    $str_email .= $email;
                                } else {
                                    $str_email .= $email . ', ';
                                }
                            }
                            $this->session->set_flashdata('error', lang("file_has_duplicate_email") . ' : ' . $email . '. ');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }

                    //jika terdapat id_company yang sama di file excel maka error
                    if (count($arr_id) != count(array_unique($arr_id))) {
                        $arr_diff = array_diff_assoc($arr_id, array_unique($arr_id));
                        $str_id_comp = '';
                        foreach ($arr_diff as $key => $id_comp) {
                            $str_id_comp .= $id_comp . ', ';
                        }
                        $this->session->set_flashdata('error', lang("file_has_duplicate_id_customer") . ' : ' . $str_id_comp);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }

                    /* if ($this->Principal) { // Khusus Principal
                        //jika terdapat email yang sama antara file dan DB selain ID yg di excel
                        //mengambil company berdasarkan email
                        $str_email_comp = '';
                        foreach ($final as $i => $f) {
                            if (isset($f['email']) && $f['email'] != '') {
                                foreach ($final as $j => $fi) {
                                    if ($f['email'] == $fi['email'] && $f['id'] != $fi['id']) {
                                        $str_email_comp .= '[' . $f['id'] . '] ' . $f['email'] . ', ';
                                    }
                                }
                            }
                        }
                        if ($str_email_comp != '') {
                            $this->session->set_flashdata('error', lang("double_email_in_file_db") . ' : ' . $str_email_comp);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    } */
                }
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            if ($this->companies_model->updateCompanyBatch($final)) {
                // unlink(FCPATH . $file_name);
                unlink($file_name);
                $this->session->set_flashdata('message', lang("customer_updated"));
                redirect('customers');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/update_by_excel', $this->data);
        }
    }

    public function delete($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->session->set_flashdata('error', lang('customer_x_deleted'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }

        if ($this->Principal) {
            if ($this->companies_model->deleteCustomerForPrincipal($id)) {
                $this->session->set_flashdata('success', lang('customer_deleted'));
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
            }
        } else {
            if ($this->companies_model->deleteCustomer($id)) {
                echo lang("customer_deleted");
            } else {
                $this->session->set_flashdata('warning', lang('customer_x_deleted_have_sales'));
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
            }
        }
    }

    public function recover($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->companies_model->recoverCustomer($id)) {
            echo lang("customer_recovered");
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        } else {
            $this->session->set_flashdata('error', lang('distributor_cant_recover'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    public function suggestions($term = null, $limit = null, $warehouse_id = null)
    {
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        if (strlen($term) < 1) {
            return false;
        }
        $limit          = $this->input->get('limit', true);
        $warehouse_id   = $this->input->get('warehouse_id', true);
        $results        = $this->companies_model->getCustomerSuggestions(trim($term), $limit, $warehouse_id);

        $rows['results'] = $results;
        $this->sma->send_json($rows);
    }

    public function getCustomer($id = null)
    {
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => ($row->company != '-' ? $row->company : $row->name))));
        $this->datatables->where('companies.is_deleted', null);
    }

    public function get_customer_details($id = null)
    {
        $this->sma->send_json($this->companies_model->getCompanyByID($id));
    }

    public function get_award_points($id = null)
    {
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json(array('ca_points' => $row->award_points));
    }

    public function customer_actions()
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', '4096M');

        if (!$this->Owner && !$this->GP['bulk_actions']) {
            if (!$this->Principal) {
                $this->session->set_flashdata('warning', lang('access_denied'));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->input->post('form_action') == 'export_excel_all') {
                $writer = \Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
                $writer->setShouldCreateNewSheetsAutomatically(true);

                $filename = 'customers_' . date('Y_m_d_H_i_s');
                $writer->openToBrowser($filename . '.xlsx');

                $header = [
                    lang('id'),
                    lang('company'),
                    lang('name'),
                    lang('email'),
                    lang('phone'),
                    lang('address'),
                    lang('city'),
                    lang('state'),
                    lang('postal_code'),
                    lang('country'),
                    lang('vat_no'),
                    lang('deposit_amount'),
                    lang('ccf1'),
                    lang('ccf2'),
                    lang('ccf3'),
                    lang('ccf4'),
                    lang('ccf5'),
                    lang('ccf6'),
                    lang('is_deleted'),
                    lang('kode'),
                    lang('distributor_name')
                ];

                $write_header = WriterEntityFactory::createRowFromArray($header);
                $writer->addRow($write_header);

                $load_data = $this->companies_model->getAllCustomerWithDistributor();
                foreach ($load_data as $val) {
                    $my_data = [
                        $val->id,
                        $val->company,
                        $val->name,
                        $val->email,
                        $val->phone,
                        $val->address,
                        $val->city,
                        $val->state,
                        $val->postal_code,
                        $val->country,
                        $val->vat_no,
                        $val->deposit_amount,
                        $val->cf1,
                        $val->cf2,
                        $val->cf3,
                        $val->cf4,
                        $val->cf5,
                        $val->cf6,
                        $val->is_deleted,
                        $val->kode_distributor,
                        $val->nama_distributor
                    ];

                    $write_data = WriterEntityFactory::createRowFromArray($my_data);
                    $writer->addRow($write_data);
                }

                $writer->close();
            }

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if ($this->Principal) {
                            if ($this->companies_model->deleteCustomerForPrincipal($id)) {
                                $error = false;
                            }
                        } else {
                            if (!$this->companies_model->deleteCustomer($id)) {
                                $error = true;
                            }
                        }
                    }

                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang("customers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'recover') {
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if ($this->Principal) {
                            if (!$this->companies_model->recoverCustomer($id)) {
                                $error = true;
                            }
                        }
                    }

                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customer_cant_recover'));
                    } else {
                        $this->session->set_flashdata('message', lang("customer_recovered"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('customer'))
                        ->SetCellValue('A1', lang('id'))
                        ->SetCellValue('B1', lang('company'))
                        ->SetCellValue('C1', lang('name'))
                        ->SetCellValue('D1', lang('email'))
                        ->SetCellValue('E1', lang('phone'))
                        ->SetCellValue('F1', lang('address'))
                        ->SetCellValue('G1', lang('city'))
                        ->SetCellValue('H1', lang('state'))
                        ->SetCellValue('I1', lang('postal_code'))
                        ->SetCellValue('J1', lang('country'))
                        ->SetCellValue('K1', lang('vat_no'))
                        ->SetCellValue('L1', lang('deposit_amount'))
                        ->SetCellValue('M1', lang('ccf1'))
                        ->SetCellValue('N1', lang('ccf2'))
                        ->SetCellValue('O1', lang('ccf3'))
                        ->SetCellValue('P1', lang('ccf4'))
                        ->SetCellValue('Q1', lang('ccf5'))
                        ->SetCellValue('R1', lang('ccf6'));

                    if ($this->Principal || $this->Owner) {
                        $sheet->SetCellValue('S1', lang('is_deleted'))
                            ->SetCellValue('T1', lang('kode'))
                            ->SetCellValue('U1', lang('distributor_name'));
                    } else {
                        $sheet->SetCellValue('S1', lang('kode'))
                            ->SetCellValue('T1', lang('distributor_name'));
                    }


                    $row = 2;
                    $get_customer = $this->companies_model->getCompanyWhereInId($_POST['val'], 'customer');
                    foreach ($get_customer as $customer) {
                        $sheet->SetCellValue('A' . $row, $customer->id)
                            ->SetCellValue('B' . $row, $customer->company)
                            ->SetCellValue('C' . $row, $customer->name)
                            ->SetCellValue('D' . $row, $customer->email)
                            ->SetCellValue('E' . $row, $customer->phone)
                            ->SetCellValue('F' . $row, $customer->address)
                            ->SetCellValue('G' . $row, $customer->city)
                            ->SetCellValue('H' . $row, $customer->state)
                            ->SetCellValue('I' . $row, $customer->postal_code)
                            ->SetCellValue('J' . $row, $customer->country)
                            ->SetCellValue('K' . $row, $customer->vat_no)
                            ->SetCellValue('L' . $row, $customer->deposit_amount)
                            ->SetCellValue('M' . $row, $customer->cf1)
                            ->SetCellValue('N' . $row, $customer->cf2)
                            ->SetCellValue('O' . $row, $customer->cf3)
                            ->SetCellValue('P' . $row, $customer->cf4)
                            ->SetCellValue('Q' . $row, $customer->cf5)
                            ->SetCellValue('R' . $row, $customer->cf6);

                        if ($this->Principal || $this->Owner) {
                            $sheet->SetCellValue('S' . $row, $customer->is_deleted)
                                ->SetCellValue('T' . $row, $customer->kode_distributor)
                                ->SetCellValue('U' . $row, $customer->nama_distributor);
                        } else {
                            $sheet->SetCellValue('S' . $row, $customer->kode_distributor)
                                ->SetCellValue('T' . $row, $customer->nama_distributor);
                        }
                        $row++;
                    }

                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'customers_' . date('Y_m_d_H_i_s');
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
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function deposits($company_id = null)
    {
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->load->view($this->theme . 'customers/deposits', $this->data);
    }

    public function get_deposits($company_id = null)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from("deposits")
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits') . '.company_id', $company_id)
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("deposit_note") . "' href='" . site_url('customers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
            ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function add_deposit($company_id = null)
    {
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
            );

            $cdata = array(
                'deposit_amount' => ($company->deposit_amount + $this->input->post('amount'))
            );
        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addDeposit($data, $cdata)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_deposit', $this->data);
        }
    }

    public function edit_deposit($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
            );

            $cdata = array(
                'deposit_amount' => (($company->deposit_amount - $deposit->amount) + $this->input->post('amount'))
            );
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateDeposit($id, $data, $cdata)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['deposit'] = $deposit;
            $this->load->view($this->theme . 'customers/edit_deposit', $this->data);
        }
    }

    public function delete_deposit($id)
    {
        if ($this->companies_model->deleteDeposit($id)) {
            echo lang("deposit_deleted");
        }
    }

    public function deposit_note($id = null)
    {
        $deposit                  = $this->companies_model->getDepositByID($id);
        $this->data['customer']   = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit']    = $deposit;
        $this->data['page_title'] = $this->lang->line("deposit_note");
        $this->load->view($this->theme . 'customers/deposit_note', $this->data);
    }

    public function addresses($company_id = null)
    {
        $this->data['modal_js']   = $this->site->modal_js();
        $this->data['company']    = $this->companies_model->getCompanyByID($company_id);
        $this->data['addresses']  = $this->companies_model->getCompanyAddresses($company_id);
        $this->load->view($this->theme . 'customers/addresses', $this->data);
    }

    public function add_address($company_id = null)
    {
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('line1', lang("line1"), 'required');
        $this->form_validation->set_rules('city', lang("city"), 'required');
        $this->form_validation->set_rules('state', lang("state"), 'required');
        $this->form_validation->set_rules('country', lang("country"), 'required');
        $this->form_validation->set_rules('phone', lang("phone"), 'required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'line1' => $this->input->post('line1'),
                'line2' => $this->input->post('line2'),
                'city' => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state' => $this->input->post('state'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'company_id' => $company->id,
            );
        } elseif ($this->input->post('add_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addAddress($data)) {
            $this->session->set_flashdata('message', lang("address_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_address', $this->data);
        }
    }

    public function edit_address($id = null)
    {
        $this->form_validation->set_rules('line1', lang("line1"), 'required');
        $this->form_validation->set_rules('city', lang("city"), 'required');
        $this->form_validation->set_rules('state', lang("state"), 'required');
        $this->form_validation->set_rules('country', lang("country"), 'required');
        $this->form_validation->set_rules('phone', lang("phone"), 'required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'line1' => $this->input->post('line1'),
                'line2' => $this->input->post('line2'),
                'city' => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state' => $this->input->post('state'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'updated_at' => date('Y-m-d H:i:s'),
            );
        } elseif ($this->input->post('edit_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateAddress($id, $data)) {
            $this->session->set_flashdata('message', lang("address_updated"));
            redirect("customers");
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['address'] = $this->companies_model->getAddressByID($id);
            $this->load->view($this->theme . 'customers/edit_address', $this->data);
        }
    }

    public function delete_address($id)
    {
        if ($this->companies_model->deleteAddress($id)) {
            $this->session->set_flashdata('message', lang("address_deleted"));
            redirect("customers");
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function search_toko_aktif()
    {
        try {
            $this->form_validation->set_rules('kode', lang('kode'), 'required');
            $this->form_validation->set_rules('sync_strategy', lang('sync_strategy'), 'required');
            if ($this->form_validation->run() == true) {
                $kode_distributor   = $this->input->post('kode') ?? '';
                $supplier           = $this->input->post('supplier') ?? '';
                $sync_strategy     = $this->input->post('sync_strategy') ?? 'strategy_1';

                if (!$kode_distributor || $kode_distributor == '-') {
                    throw new Exception(lang("code_not_found"));
                }
                if ($this->LT) {
                    $this->ltsycn($kode_distributor, $sync_strategy);
                } else if (strtoupper($supplier) == "SBI") {
                    $this->distributorsbisycn($kode_distributor, $sync_strategy);
                } else {
                    $this->distributorsycn($kode_distributor, $sync_strategy);
                }
                redirect("customers");
            } else {
                $this->data['cf1']        = $this->companies_model->findCf1ById($this->session->userdata('company_id'));
                if ($this->LT) {
                    if (explode('-', $this->data['cf1']->cf1, -1)[0] == 'IDC') {
                        $code_customer = explode('-', $this->data['cf1']->cf1, 2)[1];
                    } elseif (explode('-', $this->data['cf1']->cf2, -1)[0] == 'IDC') {
                        $code_customer = explode('-', $this->data['cf1']->cf2, 2)[1];
                    } elseif (explode('-', $this->data['cf1']->cf3, -1)[0] == 'IDC') {
                        $code_customer = explode('-', $this->data['cf1']->cf3, 2)[1];
                    } elseif (explode('-', $this->data['cf1']->cf4, -1)[0] == 'IDC') {
                        $code_customer = explode('-', $this->data['cf1']->cf4, 2)[1];
                    } elseif (explode('-', $this->data['cf1']->cf5, -1)[0] == 'IDC') {
                        $code_customer = explode('-', $this->data['cf1']->cf5, 2)[1];
                    } elseif (explode('-', $this->data['cf1']->cf4, -1)[0] == 'IDC') {
                        $code_customer = explode('-', $this->data['cf1']->cf6, 2)[1];
                    }
                    $this->data['code'] = $code_customer;
                }
                $this->data['modal_js']   = $this->site->modal_js();
                $this->load->view($this->theme . 'customers/search_toko_aktif', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function find_customer_bk()
    {
        try {
            $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc                     = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')), array('link' => '#', 'page' => lang('find_customer_bk')));
            $meta                   = array('page_title' => lang('find_customer_bk'), 'bc' => $bc);
            $this->page_construct('customers/find_customer_bk', $meta, $this->data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function find_bk()
    {
        $this->form_validation->set_rules('metode', 'Find In', 'trim|required');
        $this->form_validation->set_rules('kode_bk', 'Kode BK', 'trim|required');

        if ($this->form_validation->run() == TRUE) {
            $metode = $this->input->post('metode');
            $kode_bk = $this->input->post('kode_bk');

            if ($metode == 'kd_customer') {
                $data   = $this->companies_model->cekDataLT($kode_bk);
            } else {
                $data   = $this->companies_model->getDataTokoAktif($kode_bk);
            }
            if (!$data['status'] || $data['status'] == 'empty') {
                $response = [];
            }
            foreach ($data['data'] as $row['data']) {
                $status_smi = $row['data']['STATUS_SMI'] ? "<i class='fa fa-check' aria-hidden='true' style='color: green;'></i>" : "<i class='fa fa-times' aria-hidden='true' style='color: red;'></i>";
                $status_sbi = $row['data']['STATUS_SBI'] ? "<i class='fa fa-check' aria-hidden='true' style='color: green;'></i>" : "<i class='fa fa-times' aria-hidden='true' style='color: red;'></i>";

                $response .= '<tr>';
                $response .= '  <td>' . $row['data']['KD_CUSTOMER'] . '</td>';
                $response .= '  <td>' . $row['data']['NM_CUSTOMER'] . '</td>';
                $response .= '  <td>' . $row['data']['NAMA_TOKO'] . '</td>';
                $response .= '  <td>' . $row['data']['KD_LT'] . '</td>';
                $response .= '  <td>' . $row['data']['NAMA_LT'] . '</td>';
                $response .= '  <td>' . $row['data']['NO_HANDPHONE'] . '</td>';
                $response .= '  <td>' . $row['data']['NM_DISTRIK'] . '</td>';
                $response .= '  <td>' . $row['data']['KECAMATAN'] . '</td>';
                $response .= '  <td>' . $row['data']['PROVINSI'] . '</td>';
                $response .= '  <td>' . $row['data']['ALAMAT_TOKO'] . '</td>';
                $response .= '  <td>' . $row['data']['GROUP_CUSTOMER'] . '</td>';
                $response .= '  <td>' . $row['data']['DISTRIBUTOR'] . '</td>';
                $response .= '  <td  style="text-align:center">' . $row['data']['NOMOR_DISTRIBUTOR'] . '</td>';
                $response .= '  <td>' . $row['data']['DISTRIBUTOR2'] . '</td>';
                $response .= '  <td  style="text-align:center">' . $row['data']['NOMOR_DISTRIBUTOR2'] . '</td>';
                $response .= '  <td>' . $row['data']['DISTRIBUTOR3'] . '</td>';
                $response .= '  <td  style="text-align:center">' . $row['data']['NOMOR_DISTRIBUTOR3'] . '</td>';
                $response .= '  <td>' . $row['data']['DISTRIBUTOR4'] . '</td>';
                $response .= '  <td  style="text-align:center">' . $row['data']['NOMOR_DISTRIBUTOR4'] . '</td>';
                $response .= '  <td  style="text-align:center">' . $status_smi . '</td>';
                $response .= '  <td  style="text-align:center">' . $status_sbi . '</td>';
                $response .= '</tr>';
            }
        } else {
            $response = [];
        }

        echo json_encode($response);
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

    public function suggestionsBillerAktif($term = null, $limit = null)
    {

        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        if (strlen($term) < 1) {
            return false;
        }
        $limit = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getBillerSuggestionsAktif(trim($term), $limit);
        $this->sma->send_json($rows);
    }

    public function get_distributor()
    {
        $q = $this->companies_model->getDistributor();
        foreach ($q as $row) {
            $data[] = $row;
        }
        echo json_encode($data);
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    private function distributorsbisycn($kode_distributor, $sync_strategy)
    {
        $response_1   = $this->companies_model->getDataTokoAktif($kode_distributor);
        $response_2   = $this->companies_model->getDataTokoAktif(str_pad($kode_distributor, 10, '0', STR_PAD_LEFT));
        if (!$response_1['status'] || $response_1['status'] == 'empty') {
            $response_1['data'] = [];
        }
        if (!$response_2['status'] || $response_2['status'] == 'empty') {
            $response_2['data'] = [];
        }
        $response = array_merge($response_1['data'], $response_2['data']);
        if (count($response) == 0) {
            throw new Exception(lang("not_found"));
        }
        $jumlah   = 0;
        $this->db->update('companies', ['flag_bk' => 0], ['company_id' => $this->session->userdata('company_id'), 'group_name' => 'customer']);
        foreach ($response as $row) {
            $cf1            = 'IDC-' . $row['KD_CUSTOMER'];
            $customer       = $this->companies_model->findCompanyByCf1AndCompanyId($this->session->userdata('company_id'), $cf1);
            if ($customer != NULL) {
                $data = array(
                    'flag_bk'    => '1',
                    'updated_at' => date('Y-m-d H:i:s')
                );
                if (in_array($sync_strategy, ['strategy_2', 'strategy_3'])) {
                    $data['company'] = $row['NAMA_TOKO'];
                    $data['address'] = $row['ALAMAT_TOKO'] ? $row['ALAMAT_TOKO'] : ($row['ADDRESS'] ? $row['ADDRESS'] : '-');
                    $data['city'] = $row['NM_DISTRIK'];
                    $data['state'] = $row['KECAMATAN'];
                    $data['country'] = $row['PROVINSI'];
                    $data['is_active'] = '1';
                    $data['is_deleted'] = null;
                }
                $this->db->trans_begin();
                if ($this->companies_model->updateCompany($customer->id, $data)) {
                    $this->db->trans_commit();
                    $jumlah += 1;
                } else {
                    $this->db->trans_rollback();
                    continue;
                }
            } else {
                $email = $row['KD_CUSTOMER'] . '@' . $this->randomemail() . '.com';
                $data = array(
                    'name'                  => $row['NM_CUSTOMER'],
                    'email'                 => $email,
                    'group_id'              => '3',
                    'group_name'            => 'customer',
                    'customer_group_id'     => '1',
                    'customer_group_name'   => 'General',
                    'company_id'            => $this->session->userdata('company_id'),
                    'company'               => $row['NAMA_TOKO'],
                    'address'               => $row['ALAMAT_TOKO'] ? $row['ALAMAT_TOKO'] : ($row['ADDRESS'] ? $row['ADDRESS'] : '-'),
                    'city'                  => $row['NM_DISTRIK'],
                    'state'                 => $row['KECAMATAN'],
                    'postal_code'           => $row['KD_PROVINSI'],
                    'country'               => $row['PROVINSI'],
                    'phone'                 => $row['NO_HANDPHONE'] ? $row['NO_HANDPHONE'] : ($row['NO_TELP_TOKO'] ? $row['NO_TELP_TOKO'] : '0'),
                    'cf1'                   => $cf1,
                    'is_active'             => '1',
                    'latitude'              => $row['LATITUDE'] ?? '0',
                    'longitude'             => $row['LONGITUDE'] ?? '0',
                    'flag_bk'               => '1',
                    'created_at'            => date('Y-m-d H:i:s')
                );
                $this->db->trans_begin();
                if ($this->companies_model->addCompany($data)) {
                    $this->db->trans_commit();
                    $jumlah += 1;
                } else {
                    $this->db->trans_rollback();
                    continue;
                }
            }
        }
        if (in_array($sync_strategy, ['strategy_3'])) {
            $this->db->like('cf1', 'IDC-');
            $this->db->update('companies', ['is_deleted' => 1, 'is_active' => 0], ['company_id' => $this->session->userdata('company_id'), 'group_name' => 'customer', 'flag_bk' => 0]);
        }
        if ($jumlah != $response['num_rows']) {
            $this->session->set_flashdata('message', $jumlah . ' ' . lang("synchron_success") . lang("from") . $response['num_rows'] . ' ' . lang("data"));
        } else {
            $this->session->set_flashdata('message', lang("success"));
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    private function distributorsycn($kode_distributor, $sync_strategy)
    {
        $response_1   = $this->companies_model->getDataTokoAktif($kode_distributor);
        $response_2   = $this->companies_model->getDataTokoAktif(str_pad($kode_distributor, 10, '0', STR_PAD_LEFT));
        if (!$response_1['status'] || $response_1['status'] == 'empty') {
            $response_1['data'] = [];
        }
        if (!$response_2['status'] || $response_2['status'] == 'empty') {
            $response_2['data'] = [];
        }
        $response = array_merge($response_1['data'], $response_2['data']);
        if (count($response) == 0) {
            throw new Exception(lang("not_found"));
        }
        $jumlah   = 0;
        $count    = 0;
        $this->db->update('companies', ['flag_bk' => 0], ['company_id' => $this->session->userdata('company_id'), 'group_name' => 'customer']);
        foreach ($response as $row) {
            if ($row['GROUP_CUSTOMER'] == 'LT' || $row['KD_LT'] == NULL || (trim($row['NAMA_TOKO']) == trim($row['NAMA_LT']))) {
                $count          += 1;
                $cf1            = 'IDC-' . $row['KD_CUSTOMER'];
                $customer       = $this->companies_model->findCompanyByCf1AndCompanyId($this->session->userdata('company_id'), $cf1);
                if ($customer != NULL) {
                    $data = array(
                        'flag_bk'    => '1',
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    if (in_array($sync_strategy, ['strategy_2', 'strategy_3'])) {
                        $data['company'] = $row['NAMA_TOKO'];
                        $data['address'] = $row['ALAMAT_TOKO'] ? $row['ALAMAT_TOKO'] : ($row['ADDRESS'] ? $row['ADDRESS'] : '-');
                        $data['city'] = $row['NM_DISTRIK'];
                        $data['state'] = $row['KECAMATAN'];
                        $data['country'] = $row['PROVINSI'];
                        $data['is_active'] = '1';
                        $data['is_deleted'] = null;
                    }
                    $this->db->trans_begin();
                    if ($this->companies_model->updateCompany($customer->id, $data)) {
                        $this->db->trans_commit();
                        $jumlah += 1;
                    } else {
                        $this->db->trans_rollback();
                        continue;
                    }
                } else {
                    $email = $row['KD_CUSTOMER'] . '@' . $this->randomemail() . '.com';
                    $data = array(
                        'name'                  => $row['NM_CUSTOMER'],
                        'email'                 => $email,
                        'group_id'              => '3',
                        'group_name'            => 'customer',
                        'customer_group_id'     => '1',
                        'customer_group_name'   => 'General',
                        'company_id'            => $this->session->userdata('company_id'),
                        'company'               => $row['NAMA_TOKO'],
                        'address'               => $row['ALAMAT_TOKO'] ? $row['ALAMAT_TOKO'] : ($row['ADDRESS'] ? $row['ADDRESS'] : '-'),
                        'city'                  => $row['NM_DISTRIK'],
                        'state'                 => $row['KECAMATAN'],
                        'postal_code'           => $row['KD_PROVINSI'],
                        'country'               => $row['PROVINSI'],
                        'phone'                 => $row['NO_HANDPHONE'] ? $row['NO_HANDPHONE'] : ($row['NO_TELP_TOKO'] ? $row['NO_TELP_TOKO'] : '0'),
                        'cf1'                   => $cf1,
                        'is_active'             => '1',
                        'latitude'              => $row['LATITUDE'] ?? '0',
                        'longitude'             => $row['LONGITUDE'] ?? '0',
                        'flag_bk'               => '1',
                        'created_at'            => date('Y-m-d H:i:s')
                    );
                    $this->db->trans_begin();
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
        if (in_array($sync_strategy, ['strategy_3'])) {
            $this->db->like('cf1', 'IDC-');
            $this->db->update('companies', ['is_deleted' => 1, 'is_active' => 0], ['company_id' => $this->session->userdata('company_id'), 'group_name' => 'customer', 'flag_bk' => 0]);
        }
        if ($jumlah != $count) {
            $this->session->set_flashdata('message', $jumlah . ' ' . lang("synchron_success") . lang("from") . $count . ' ' . lang("data"));
        } else {
            $this->session->set_flashdata('message', lang("success"));
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    private function ltsycn($kode_lt, $sync_strategy)
    {
        if ($this->LT) {
            $response_1         = $this->companies_model->cekDataLT($kode_lt);
            if (!$response_1['status'] || $response_1['status'] == 'empty') {
                throw new Exception(lang("Maaf ID $kode_lt tidak dapat ditemukan di Bisnis Kokoh."));
            }
            $response         = $this->companies_model->getDataTokoAktif($response_1['data'][0]['NOMOR_DISTRIBUTOR']);
            if (!$response['status'] || $response['status'] == 'empty') {
                $response_3   = $this->companies_model->getDataTokoAktif(str_pad($kode_lt, 10, '0', STR_PAD_LEFT));
                if (!$response_3['status'] || $response_3['status'] == 'empty') {
                    throw new Exception(lang("not_found"));
                }
                $response = $response_3;
            }
            $jumlah = 0;
            $count  = 0;
            $this->db->update('companies', ['flag_bk' => 0], ['company_id' => $this->session->userdata('company_id'), 'group_name' => 'customer']);
            foreach ($response['data'] as $row) {
                if ($row['KD_LT'] == $response_1['data'][0]['KD_LT'] && $row['KD_CUSTOMER'] != $response_1['data'][0]['KD_CUSTOMER']) {
                    $count          += 1;
                    $cf1            = 'IDC-' . $row['KD_CUSTOMER'];
                    $customer       = $this->companies_model->findCompanyByCf1AndCompanyId($this->session->userdata('company_id'), $cf1);
                    if ($customer != NULL) {
                        $data = array(
                            'flag_bk'    => '1',
                            'updated_at' => date('Y-m-d H:i:s')
                        );
                        if (in_array($sync_strategy, ['strategy_2', 'strategy_3'])) {
                            $data['company'] = $row['NAMA_TOKO'];
                            $data['address'] = $row['ALAMAT_TOKO'] ? $row['ALAMAT_TOKO'] : ($row['ADDRESS'] ? $row['ADDRESS'] : '-');
                            $data['city'] = $row['NM_DISTRIK'];
                            $data['state'] = $row['KECAMATAN'];
                            $data['country'] = $row['PROVINSI'];
                            $data['is_active'] = '1';
                            $data['is_deleted'] = null;
                        }
                        $this->db->trans_begin();
                        if ($this->companies_model->updateCompany($customer->id, $data)) {
                            $this->db->trans_commit();
                            $jumlah += 1;
                        } else {
                            $this->db->trans_rollback();
                            continue;
                        }
                    } else {
                        $email = $row['KD_CUSTOMER'] . '@' . $this->randomemail() . '.com';
                        $data = array(
                            'name'                  => $row['NM_CUSTOMER'],
                            'email'                 => $email,
                            'group_id'              => '3',
                            'group_name'            => 'customer',
                            'customer_group_id'     => '1',
                            'customer_group_name'   => 'General',
                            'company_id'            => $this->session->userdata('company_id'),
                            'company'               => $row['NAMA_TOKO'],
                            'address'               => $row['ALAMAT_TOKO'] ? $row['ALAMAT_TOKO'] : ($row['ADDRESS'] ? $row['ADDRESS'] : '-'),
                            'city'                  => $row['NM_DISTRIK'],
                            'state'                 => $row['KECAMATAN'],
                            'postal_code'           => $row['KD_PROVINSI'],
                            'country'               => $row['PROVINSI'],
                            'phone'                 => $row['NO_HANDPHONE'] ? $row['NO_HANDPHONE'] : ($row['NO_TELP_TOKO'] ? $row['NO_TELP_TOKO'] : '0'),
                            'cf1'                   => $cf1,
                            'is_active'             => '1',
                            'latitude'              => $row['LATITUDE'] ?? '0',
                            'longitude'             => $row['LONGITUDE'] ?? '0',
                            'flag_bk'               => '1',
                            'created_at'            => date('Y-m-d H:i:s')
                        );
                        $this->db->trans_begin();
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
            if (in_array($sync_strategy, ['strategy_3'])) {
                $this->db->like('cf1', 'IDC-');
                $this->db->update('companies', ['is_deleted' => 1, 'is_active' => 0], ['company_id' => $this->session->userdata('company_id'), 'group_name' => 'customer', 'flag_bk' => 0]);
            }
            if ($jumlah != $count) {
                $this->session->set_flashdata('message', $jumlah . ' ' . lang("synchron_success") . lang("from") . $count . ' ' . lang("data"));
            } else {
                $this->session->set_flashdata('message', lang("success"));
            }
        } else {
            throw new Exception("Maaf ID $kode_lt bukan termasuk LT");
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
}

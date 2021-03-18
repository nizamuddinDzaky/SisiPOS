<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Distributor_Controller.php';

class Suppliers extends MY_API_Distributor_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('companies_model');
        $this->load->library('ion_auth');
    }

    public function list_suppliers_get()
    {
        $this->db->trans_begin();

        try {
            $auth = $this->authorize();
            $search = $this->input->get('search');

            $where = "(`company_id` = {$auth->company->id} OR `company_id` = 1) AND group_name='supplier'";

            if ($search) {
                $where .= " AND (`name` LIKE '%{$search}%' OR `company` LIKE '%{$search}%')";
            }

            $suppliers = $this->companies_model->getAllSupplierByCompanyId($where);
            if (!$suppliers) {
                throw new Exception(lang('not_found'), 404);
            }

            $response = [
                "total_suppliers" => count($suppliers),
                "list_suppliers" => $suppliers
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get List Supplier success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
    public function detail_suppliers_get()
    {
        $this->db->trans_begin();
        try {
            $id_supplier    = $this->input->get('id_supplier');
            $supplier       = $this->companies_model->getCompanyByID($id_supplier);

            if (!$supplier) {
                throw new Exception(lang('not_found'), 404);
            }

            $response = [
                "supplier" => $supplier
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Detail Suppliers success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}

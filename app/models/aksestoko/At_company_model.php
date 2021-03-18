<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH.'/models/Companies_model.php';

class At_company_model extends Companies_model
{
    public function __construct()
    {
        parent::__construct();
    }
  
    public function softDeleteAddress($id, $company_id)
    {
        if ($this->db->update('companies', array('is_deleted' => 1, 'updated_at' => date('Y-m-d H:i:s')), array('id' => $id, 'group_name' => 'address', 'company_id' => $company_id))) {
            return true;
        }
        return false;
    }

    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', array('id' => $id, 'is_deleted' => null), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}

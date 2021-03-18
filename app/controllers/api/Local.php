<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_REST_Controller.php';

/** 
 * Promotional
 **/
class Local extends MY_REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Daerah_model', 'daerah_model');
    }

    public function list_province_get()
    {
        try {
            $token    = $this->daerah_model->getToken();
            $data     = $this->daerah_model->getProvinsi($token);

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Province success", $data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_city_get()
    {
        try {
            $token    = $this->daerah_model->getToken();
            $province = $this->input->get('province');

            if (!$province) {
                throw new Exception("Get City failed, Because params `province` is required", 404);
            }
            $data = $this->daerah_model->getKabupaten($token, $province);

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get City success", $data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_states_get()
    {
        try {
            $token    = $this->daerah_model->getToken();
            $city     = $this->input->get('city');
            if (!$city) {
                throw new Exception("Get State failed, Because params `city` is required", 404);
            }
            $data = $this->daerah_model->getKecamatan($token, $city);

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get State success", $data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function list_village_get()
    {
        try {
            $token    = $this->daerah_model->getToken();
            $states   = $this->input->get('states');

            if (!$states) {
                throw new Exception("Get State failed, Because params `state` is required", 404);
            }
            $data = $this->daerah_model->getDesa($token, $states);

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Village success", $data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function postal_code_get()
    {
        try {
            $postal_code   = $this->input->get('postal_code');

            if (!$postal_code) {
                throw new Exception("Get By Postal Code failed, Because params `postal_code` is required", 404);
            }
            $data = $this->daerah_model->getByKodePos($postal_code);

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get By Postal Code success", $data);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}

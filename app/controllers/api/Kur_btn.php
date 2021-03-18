<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_REST_Controller.php';

/**
 * Aksestoko
 *
 * @property Integration_model $integration_model
 * */
class Kur_btn extends MY_REST_Controller
{
    /*
     *
INSERT INTO sma_api_integration(supplier_id,username,password,uri, type)
VALUES
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/JenisKredit_ShowAll', 'kur_bank_btn_jenis_kredit_show_all'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/JenisPekerjaan_ShowAll', 'kur_bank_btn_jenis_pekerjaan_show_all'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/JenisPembiayaan_ShowAll', 'kur_bank_btn_jenis_pembiayaan_show_all'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/KantorCabang_Show', 'kur_bank_btn_kantor_cabang_show'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/Kecamatan_ShowAll', 'kur_bank_btn_kecamatan_show_all'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/Kelurahan_ShowAll', 'kur_bank_btn_kelurahan_show_all'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/KodePos_GetLocation', 'kur_bank_btn_kodepos_getlocation'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/Kota_ShowAll', 'kur_bank_btn_kota_show_all'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/KprNonStok_Insert', 'kur_bank_btn_kpr_non_stok_insert'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/Lokasi_Search', 'kur_bank_btn_lokasi_search'),
(0,'','','https://www.btnproperti.co.id/apifrontoffice-65ds4f9d8d3hd/apib2b.asmx/Propinsi_ShowAll', 'kur_bank_btn_propinsi_show_all')
     */
    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->Admin = true;
        $this->load->model('integration_model', 'integration');
    }

    protected function _buildResponse($response, $paging = false)
    {
        if ($response && !$response->IsError) {
            $data = $paging ? $response->Data : [
                'data' => $response->Data,
                'paging' => $response->Paging,
            ];
            $this->buildResponse("success", REST_Controller::HTTP_OK, 'Success', $data);
        } else {
            $this->buildResponse("error", REST_Controller::HTTP_BAD_REQUEST, $response ? '' : 'Error', []);
        }
    }

    public function jenis_kredit()
    {
        $type = 'kur_bank_btn_jenis_kredit_show_all';
        $response = $this->integration_model->btnApiRequest($type, []);
        $this->_buildResponse($response);
    }

    public function jenis_pekerjaan()
    {
        $type = 'kur_bank_btn_jenis_pekerjaan_show_all';
        $response = $this->integration_model->btnApiRequest($type, []);
        $this->_buildResponse($response);
    }

    public function jenis_pembiayaan()
    {
        $type = 'kur_bank_btn_jenis_pembiayaan_show_all';
        $response = $this->integration_model->btnApiRequest($type, []);
        $this->_buildResponse($response);
    }

    public function kantor_cabang()
    {
        $type = 'kur_bank_btn_kantor_cabang_show';
        $params = ['id', 'pos', 'i_prop', 'i_kot', 'jns'];
        $data = [
            'Page' => $this->input->get('page') || 0,
        ];
        foreach ($params as $name) {
            $value = $this->input->get($name);
            if($value){
                $data[$name] = $value;
            }
        }
        $response = $this->integration_model->btnApiRequest($type, $data);
        $this->_buildResponse($response, true);
    }

    public function kecamatan()
    {
        $type = 'kur_bank_btn_kecamatan_show_all';
        $data = [
            'i_kot' => $this->input->get('i_kot'),
        ];
        $response = $this->integration_model->btnApiRequest($type, $data);
        $this->_buildResponse($response);
    }

    public function kelurahan()
    {
        $type = 'kur_bank_btn_kelurahan_show_all';
        $data = [
            'i_kec' => $this->input->get('i_kec'),
        ];
        $response = $this->integration_model->btnApiRequest($type, $data);
        $this->_buildResponse($response);
    }

    public function propinsi()
    {
        $type = 'kur_bank_btn_propinsi_show_all';
        $data = [            
        ];
        $response = $this->integration_model->btnApiRequest($type, $data);
        $this->_buildResponse($response);
    }

    public function kota()
    {
        $type = 'kur_bank_btn_kota_show_all';
        $data = [
            'i_prop' => $this->input->get('i_prop'),
        ];
        $response = $this->integration_model->btnApiRequest($type, $data);
        $this->_buildResponse($response);
    }

    public function kodepos_getlocation()
    {
        $type = 'kur_bank_btn_kodepos_getlocation';
        $data = [
            'pos' => $this->input->get('pos'),
        ];
        $response = $this->integration_model->btnApiRequest($type, $data);
        $this->_buildResponse($response);
    }

    public function lokasi_search()
    {
        $type = 'kur_bank_btn_lokasi_search';
        $data = [
            'n' => $this->input->get('n'),
        ];
        $response = $this->integration_model->btnApiRequest($type, $data);
        $this->_buildResponse($response);
    }

}

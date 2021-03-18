<?php defined('BASEPATH') or exit('No direct script access allowed');

class Deliveries_smig_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getAllDeliveriesSmig($id)
    {
        $this->db->select('*')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('deliveries_smig', array('id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getDeliveriesSmig($id_soldto)
    {
        $this->db->select('*')->group_by('id');
        $this->db->where("deliveries_smig.kode_distributor", $id_soldto);
        $q = $this->db->get('deliveries_smig');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function setDeliveriesSmigByWarhouse($code)
    {
        $q = $this->db->get_where('warehouses', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function setDeliveriesSmigBySupplier($code)
    {
        $where = "cf1 = '" . $code . "' OR cf6 = '" . $code . "'";
        $this->db->where($where);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getDeliveriesSmigByID($id)
    {
        $q = $this->db->get_where('deliveries_smig', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getDeliveriesSmigByDO($no_do)
    {
        $q = $this->db->get_where('deliveries_smig', array('no_do' => $no_do), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getItemDeliveriesSmig($id)
    {
        $this->db->select('deliveries_smig_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details')
            ->join('products', 'products.id = deliveries_smig_items.product_id', 'left')
            ->join('tax_rates', 'tax_rates.id = deliveries_smig_items.tax_rate_id', 'left')
            ->group_by('deliveries_smig_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('deliveries_smig_items', array('deliveries_smig_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getMasterPlant()
    {
        $this->db->where("is_active", "1");
        $q = $this->db->get("master_plant");
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getAllMasterPlant()
    {
        $this->db->group_by("name");
        $q = $this->db->get("master_plant");
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function getDistrik($biller_id)
    {
        $this->db->select('deliveries_smig.kode_distrik , deliveries_smig.distrik');
        $this->db->where('deliveries_smig.biller_id', $biller_id)->group_by('deliveries_smig.kode_distrik');
        $q = $this->db->get("deliveries_smig");
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function addDeliveriesSmig($data = [], $items = [])
    {
        if ($this->db->insert('deliveries_smig', $data)) {
            $id = $this->db->insert_id();
            $items['deliveries_smig_id'] = $id;
            if (!$this->db->insert('deliveries_smig_items', $items)) {
                throw new \Exception($this->db->error()['message']);
            }
            return true;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function updateDeliveriesSmig($id, $data = [], $items = [])
    {
        if ($this->db->update('deliveries_smig', $data, ['id' => $id]) && $this->db->delete('deliveries_smig_items', ['deliveries_smig_id' => $id])) {
            $items['deliveries_smig_id'] = $id;
            if (!$this->db->insert('deliveries_smig_items', $items)) {
                throw new \Exception($this->db->error()['message']);
            }
            return true;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function updateStatus($id, $status, $note)
    {
        if ($this->db->update('deliveries_smig', array('status_penerimaan' => $status, 'note' => $note), array('id' => $id))) {
            return true;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function deleteDeliveriesSmig($id)
    {
        if ($this->db->delete("deliveries_smig", array('id' => $id))) {
            return true;
        }
        return false;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function send_data_deliveries_smig($kode_plant)
    {
        $q = $this->db->get_where('api_integration', ['type' => "deliveries_smig"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();

        ini_set('memory_limit', '56000M');
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'cURL User Agent'
                ), 'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $context = stream_context_create($opts);
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE
            );

            libxml_disable_entity_loader(false);
            $client = new SoapClient($integration->uri, $soapClientOptions);

            $checkVatParameters = array(
                'token'        => $integration->token,
                'distrik'      => '',
                'soNumber'     => '',
                'noSPJ'        => '',
                'spjStatus'    => '70',
                'noEkspeditur' => '',
                'plant'        => $kode_plant,
                'dateFrom'     => date('Ymd', strtotime(' -1 day ')),
                'dateTo'       => date('Ymd', strtotime(' -1 day ')),
                // 'curahbag'     => '10'
            );

            $result = $client->getListRelease3pl($checkVatParameters);
            $arr    = json_decode(json_encode($result));
            $data   = $arr->detailData;
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function search_data_deliveries_smig($distrik, $so_number, $spj_number, $ekspeditor, $date_form_param, $date_to_param, $kode_plant)
    {
        $q = $this->db->get_where('api_integration', ['type' => "deliveries_smig"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        ini_set('memory_limit', '56000M');
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'cURL User Agent'
                ), 'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $context = stream_context_create($opts);
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE
            );

            libxml_disable_entity_loader(false);
            $client = new SoapClient($integration->uri, $soapClientOptions);

            $checkVatParameters = array(
                'token'        => $integration->token,
                'distrik'      => $distrik,
                'soNumber'     => $so_number,
                'noSPJ'        => $spj_number,
                'spjStatus'    => '70',
                'noEkspeditur' => $ekspeditor,
                'plant'        => $kode_plant,
                'dateFrom'     => $date_form_param,
                'dateTo'       => $date_to_param,
                // 'curahbag'     => '10'
            );
            $result = $client->getListRelease3pl($checkVatParameters);
            $arr    = json_decode(json_encode($result));
            $data   = $arr->detailData;
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function search_data_deliveries_smig_padang($distrik, $so_number, $spj_number, $ekspeditor, $date_form_param, $date_to_param, $kode_plant)
    {
        $q = $this->db->get_where('api_integration', ['type' => "deliveries_smig_padang"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        ini_set('memory_limit', '56000M');
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'cURL User Agent'
                ), 'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $context = stream_context_create($opts);
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE
            );

            libxml_disable_entity_loader(false);
            $client = new SoapClient($integration->uri, $soapClientOptions);

            $checkVatParameters = array(
                'token'        => $integration->token,
                'distrik'      => $distrik,
                'soNumber'     => $so_number,
                'noSPJ'        => $spj_number,
                'spjStatus'    => '70',
                'noEkspeditur' => $ekspeditor,
                'plant'        => $kode_plant,
                'dateFrom'     => $date_form_param,
                'dateTo'       => $date_to_param,
                // 'curahbag'     => '10'
            );
            $result = $client->getListRelease3pl($checkVatParameters);
            $arr    = json_decode(json_encode($result));
            $data   = $arr->detailData;
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function search_data_deliveries_smig_makasar($distrik, $so_number, $spj_number, $ekspeditor, $date_form_param, $date_to_param, $kode_plant)
    {
        $q = $this->db->get_where('api_integration', ['type' => "deliveries_smig_makasar"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        ini_set('memory_limit', '56000M');
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'cURL User Agent'
                ), 'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $context = stream_context_create($opts);
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE
            );

            libxml_disable_entity_loader(false);
            $client = new SoapClient($integration->uri, $soapClientOptions);

            $checkVatParameters = array(
                'token'        => $integration->token,
                'distrik'      => $distrik,
                'soNumber'     => $so_number,
                'noSPJ'        => $spj_number,
                'spjStatus'    => '70',
                'noEkspeditur' => $ekspeditor,
                'plant'        => $kode_plant,
                'dateFrom'     => $date_form_param,
                'dateTo'       => $date_to_param,
                // 'curahbag'     => '10'
            );
            $result = $client->getListRelease3pl($checkVatParameters);
            $arr    = json_decode(json_encode($result));
            $data   = $arr->detailData;
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function send_data_deliveries_smig_padang($kode_plant)
    {
        $q = $this->db->get_where('api_integration', ['type' => "deliveries_smig_padang"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();

        ini_set('memory_limit', '56000M');
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'cURL User Agent'
                ), 'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $context = stream_context_create($opts);
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE
            );

            libxml_disable_entity_loader(false);
            $client = new SoapClient($integration->uri, $soapClientOptions);

            $checkVatParameters = array(
                'token'        => $integration->token,
                'distrik'      => '',
                'soNumber'     => '',
                'noSPJ'        => '',
                'spjStatus'    => '70',
                'noEkspeditur' => '',
                'plant'        => $kode_plant,
                'dateFrom'     => date('Ymd', strtotime(' -1 day ')),
                'dateTo'       => date('Ymd', strtotime(' -1 day ')),
                // 'curahbag'     => '10'
            );

            $result = $client->getListRelease3pl($checkVatParameters);
            $arr    = json_decode(json_encode($result));
            $data   = $arr->detailData;
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }
    //----------------------------------------------------------------------------------------------------------------------------------------------//
    public function send_data_deliveries_smig_makasar($kode_plant)
    {
        $q = $this->db->get_where('api_integration', ['type' => "deliveries_smig_makasar"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();

        ini_set('memory_limit', '56000M');
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'cURL User Agent'
                ), 'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $context = stream_context_create($opts);
            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE
            );

            libxml_disable_entity_loader(false);
            $client = new SoapClient($integration->uri, $soapClientOptions);

            $checkVatParameters = array(
                'token'        => $integration->token,
                'distrik'      => '',
                'soNumber'     => '',
                'noSPJ'        => '',
                'spjStatus'    => '70',
                'noEkspeditur' => '',
                'plant'        => $kode_plant,
                'dateFrom'     => date('Ymd', strtotime(' -1 day ')),
                'dateTo'       => date('Ymd', strtotime(' -1 day ')),
                // 'curahbag'     => '10'
            );

            $result = $client->getListRelease3pl($checkVatParameters);
            $arr    = json_decode(json_encode($result));
            $data   = $arr->detailData;
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    private function CallAPI($method, $url, $data = false, $ssl = false)
    {
        $curl = curl_init($url);

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_CAINFO, FCPATH . "assets/certificate/cacert.pem");
            curl_setopt($curl, CURLOPT_CAPATH, FCPATH . "assets/certificate/cacert.pem");
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        if (curl_error($curl)) {
            throw new \Exception(curl_error($curl));
        }

        curl_close($curl);

        return $result;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function live_tracking($no_pol, $no_do)
    {
        $q = $this->db->get_where('api_integration', ['type' => "live_tracking"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        $url = $integration->uri;

        $data = [
            'key'   => $integration->token,
            'nopol' => $no_pol,
            'no_do' => $no_do
        ];

        $get_data = ($this->CallAPI('GET', $url, $data));
        $response = json_decode($get_data, true);
        return $response;
    }
}

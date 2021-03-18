<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Daerah_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProv()
    {
        $this->db->order_by('province_name');
        $this->db->group_by('province_name');
        $q = $this->db->get('indonesia');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getKab($name_prov)
    {
        $this->db->order_by('kabupaten_name');
        $this->db->group_by('kabupaten_name');
        $this->db->where('province_name', str_replace(["%20", "_"], [" ", " "], $name_prov));
        $q = $this->db->get('indonesia');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getKec($id_kab)
    {
        $this->db->order_by('kecamatan_name');
        $this->db->group_by('kecamatan_name');
        $this->db->where('kabupaten_name', str_replace(["%20", "_"], [" ", " "], $id_kab));
        $q = $this->db->get('indonesia');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getLocation($province, $kabupaten, $kecamatan)
    {
        $this->db->where('province_name', str_replace("_", " ", $province));
        $this->db->where('kabupaten_name', str_replace("_", " ", $kabupaten));
        $this->db->where('kecamatan_name', str_replace("_", " ", $kecamatan));
        $q = $this->db->get('indonesia');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function all_id($prop, $city, $state)
    {
        $this->db->where('province.nama', $prop);
        $this->db->where('regency.nama', $city);
        $this->db->where('district.nama', $state);
        $this->db->join('regency', 'province.id_prov=regency.id_prov', 'left');
        $this->db->join('district', 'regency.id_kab=district.id_kab', 'left');
        $q = $this->db->get('province');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }


    public function getKel($id_kec)
    {
        $sql = "SELECT * FROM kelurahan WHERE id_kec={$id_kec} ORDER BY nama";
        $query = $this->db->query($sql);
        return $query->result();
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
    public function getToken()
    {
        $url        = "https://x.rajaapi.com/poe";
        $get_data   = ($this->CallAPI('GET', $url));
        $response   = json_decode($get_data, true);
        return $response['token'];
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function getProvinsi($token)
    {
        $url        = "https://x.rajaapi.com/MeP7c5ne" . $token . "/m/wilayah/provinsi";
        $get_data   = ($this->CallAPI('GET', $url));
        $response   = json_decode($get_data, true);
        return $response;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function getKabupaten($token, $provinsi)
    {
        $url        = "https://x.rajaapi.com/MeP7c5ne" . $token . "/m/wilayah/kabupaten?idpropinsi=" . $provinsi . "";
        $get_data   = ($this->CallAPI('GET', $url));
        $response   = json_decode($get_data, true);
        return $response;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function getKecamatan($token, $kabupaten)
    {
        $url        = "https://x.rajaapi.com/MeP7c5ne" . $token . "/m/wilayah/kecamatan?idkabupaten=" . $kabupaten . "";
        $get_data   = ($this->CallAPI('GET', $url));
        $response   = json_decode($get_data, true);
        return $response;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function getDesa($token, $kecamatan)
    {
        $url        = "https://x.rajaapi.com/MeP7c5ne" . $token . "/m/wilayah/kelurahan?idkecamatan=" . $kecamatan . "";
        $get_data   = ($this->CallAPI('GET', $url));
        $response   = json_decode($get_data, true);
        return $response;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------------//
    public function getByKodePos($kodePos)
    {
        $url        = "https://nbc.vanmason.web.id/service/kodepos/$kodePos";
        $get_data   = ($this->CallAPI('GET', $url));
        $response   = json_decode($get_data, true);
        return $response;
    }
}

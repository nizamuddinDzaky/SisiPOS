<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Daerah extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Daerah_model', 'daerah_model');
        // $this->load->database();
    }
    public function getProvinsi()
    {
        $data = $this->daerah_model->getProv();
        echo json_encode($data);
    }
        
    public function getKabupaten($name_prov)
    {
        $data=$this->daerah_model->getKab($name_prov);
        echo json_encode($data);
    }
    public function getnameKab()
    {
        $name_prov = $this->input->post("text");
        $prov = $this->daerah_model->getNameProv($name_prov);
         
        echo json_encode($prov);
    }
    
    public function getKecamatan($id_kab)
    {
        $data = $this->daerah_model->getKec($id_kab);
        echo json_encode($data);
    }
    public function getlocation($province, $kabupaten, $kecamatan)
    {
        $data = $this->daerah_model->getLocation($province, $kabupaten, $kecamatan);
        echo json_encode($data);
    }
    public function getKel($id_kec)
    {
        $kel=$this->daerah_model->getKel($id_kec);
        echo"<option value=''>Pilih Kelurahan/Desa</option>";
        foreach ($kel as $k) {
            echo "<option value='{$k->id_kel}'>{$k->nama}</option>";
        }
    }
}

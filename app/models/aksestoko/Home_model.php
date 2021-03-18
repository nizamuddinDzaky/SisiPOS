<?php defined('BASEPATH') or exit('No direct script access allowed');

class Home_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return "Home Index";
    }

    public function getAllCompany($cf1, $company_id)
    {
        if ($cf1 == null) {
            return [];
        }
        $sql_join = "(SELECT * FROM sma_companies WHERE sma_companies.cf1 = \"".$cf1."\" AND sma_companies.id != ".$company_id." AND sma_companies.is_deleted IS NULL AND group_name = \"customer\" AND is_active=1) comp";
        $this->db->select('sma_companies.*, sma_users.avatar');
        $this->db->join($sql_join, "comp.company_id = sma_companies.id");
        $this->db->join('users', 'users.company_id = sma_companies.id', 'inner');
        $this->db->group_by('sma_companies.id');
        $q = $this->db->get('sma_companies');

        if ($q->num_rows() > 0) {
            return $q->result();
        }
    }
    
    public function isSupplierUser($supplier_id, $user_id)
    {
        $this->db->where('sma_companies.company_id', $supplier_id);
        $this->db->where('sma_companies.cf1', $this->session->userdata('cf1'));
        $q = $this->db->get('sma_companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function findCompanyByCf1AndCompanyId($supplier_id, $cf1)
    {
        $this->db->where('sma_companies.company_id', $supplier_id);
        $this->db->where('sma_companies.cf1', $cf1);
        $q = $this->db->get('sma_companies');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function insertIssue($subject, $description, $priority = 3)
    {
        $q = $this->db->get_where('api_integration', [
            'type' => "redmine_issues",
        ], 1);
        
        if ($q->num_rows() == 0) {
            return false;
        }

        $q = $q->row();

        //API URL
        $url = $q->uri;

        //create a new cURL resource
        $ch = curl_init($url);

        //setup request to send json via POST
        $data = [
            'project_id' => 145,
            'subject' => $subject,
            'priority_id' => $priority,
            'tracker_id' => 3,
            'description' => $description
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($q->username . ":" . $q->password)
        ];

        $payload = json_encode(["issue" => $data]);

        // var_dump($payload);die;

        //attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute the POST request
        $result = curl_exec($ch);

        if (curl_error($ch)) {
            throw new \Exception(curl_error($ch));
        }

        //close cURL resource
        curl_close($ch);

        return true;
    }


    public function getActivationCmsFaq()
    {
        $q = $this->db->get_where('cms_faq', ['is_active' => "1", 'is_deleted' => '0']);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
    public function getPoint($kd_customer)
    {
        $q = $this->db->get_where('api_integration', ['type' => "point_bisniskokoh"], 1);
        if ($q && $q->num_rows() == 0) {
            return false;
        }
        $integration = $q->row();
        try {
            $URL    = $integration->uri;                                                  //API URL
            $ch     = curl_init($URL);                                                    //buat sumber daya CURL baru
            $point  = json_encode(['kdcustomer' => $kd_customer]);                        //pengaturan permintaan untuk mengirim json melalui POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $point);                                 //lampirkan string JSON yang disandikan ke bidang POST
            curl_setopt($ch, CURLOPT_FAILONERROR, true);                                  //mengembalikan respons alih-alih mengeluarkan
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);      //setel jenis konten ke aplikasi / json
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                               //mengembalikan respons alih-alih mengeluarkan
            $result = curl_exec($ch);                                                     //jalankan permintaan POST
            $data   = json_decode($result, true);
            if (curl_error($ch)) {
                throw new \Exception(curl_error($ch));
            }                                                 
            curl_close($ch);                                                              //tutup sumber daya CURL
        } catch(Exception $e) {
            return false;
        }
        return $data['data'];
    }
    //---------------------------------------------------------------------------------------------------------------------------------------//
}

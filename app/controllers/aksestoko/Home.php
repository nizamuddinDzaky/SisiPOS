<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends MY_Controller
{

    public function __operate($a, $b, $char)
    {
        switch ($char) {
            case '-':
                return $a - $b;
            case '*':
                return $a * $b;
            case '+':
                return $a + $b;
            case '/':
                return $a / $b;
        }
        return $a;
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        // $this->insertLogActivities();
        $this->load->model('aksestoko/home_model', 'home');
        $this->load->model('aksestoko/promotion_model', 'promotion');
        $this->load->model('aksestoko/product_model', 'product');
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->load->model('aksestoko/at_auth_model', 'at_auth');
        $this->load->model('aksestoko/payment_model', 'at_payment');
        $this->load->model('integration_model', 'integration');
        $this->load->model('daerah_model', 'daerah');
        $this->load->model('companies_model');
    }

    // Coba tampil landing
    public function index()
    {
        if (get_domain() != AKSESTOKO_DOMAIN && AKSESTOKO_REDIRECT) {
            redirect("//" . AKSESTOKO_DOMAIN);
        }
        $this->data['title_at'] = "Selamat Datang di AksesToko";
        $this->load->view('aksestoko/landing', $this->data);
    }

    public function select_supplier($supplier_id = "")
    {
        $this->checkATLogged(); // seharusnya ada di paling atas baris
        $this->data['title_at'] = "Pilih Distributor - AksesToko";
        $this->data['company'] = $this->data['list_distributor'];
        if (count($this->data['company']) == 1) {
            $supplier_id = $this->data['company'][0]->id;
        }
        if ($supplier_id) {
            $company = $this->at_site->findCompanyByCf1AndCompanyId($supplier_id, $this->session->userdata('cf1'));
            if ($company) {
                $arr = [
                    'customer_group_id' => $company->customer_group_id,
                    'price_group_id' => $company->price_group_id,
                    'supplier_id' => $supplier_id
                ];

                $this->session->set_userdata($arr);
                $this->session->unset_userdata('is_checkout');
                if ($redirect = $this->input->get('redirect')) {
                    redirect(aksestoko_route($redirect));
                } else {
                    redirect(aksestoko_route('aksestoko/home/main'));
                }
            } else {
                $this->session->set_flashdata('error', "Tidak dapat memilih distributor tersebut");

                redirect(aksestoko_route('aksestoko/home/select_supplier'));
            }
        }
        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/pilih_supplier', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function main()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->data['title_at'] = "Halaman Utama - AksesToko";
        $company_id = $this->session->userdata('supplier_id');
        if (!$company_id) {
            $this->session->set_flashdata('error', "Pilih distributor terlebih dahulu");
            redirect(aksestoko_route('aksestoko/home/select_supplier'));
        }

        $company = $this->companies_model->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
        $this->data['promotion'] = $this->promotion->listPromotion($company->id, $this->session->userdata('supplier_id'));
        $this->data['supplier'] = $this->at_site->findCompany($company_id);
        $this->data['poin'] = $this->at_auth->getUserPoint($this->session->userdata('user_id'));
        $this->data['promo_popup'] = $this->promotion->listPromotionPopup($this->session->userdata('company_id'), $this->session->userdata('supplier_id'));
        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/main', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function product_data($p)
    {
        $company_id = $this->session->userdata('supplier_id');
        $config['query_string_segment'] = 'start';
        $config['full_tag_open'] = '<nav aria-label="pagination-order" class="pagination my-3"><ul class="pagination justify-content-center" style="margin:auto;">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['first_link'] = 'Pertama';
        $config['first_tag_open'] = '<li class="page-item pertama" style="display: none;">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Terakhir';
        $config['last_tag_open'] = '<li class="page-item terakhir" style="display: none;">';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = '<span aria-hidden="true">»</span>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '<span aria-hidden="true">«</span>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active disabled"><a class="page-link" href="javascript:void(0)">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['base_url'] = base_url(aksestoko_route("aksestoko/home/product_data"));
        $config['total_rows'] = $this->product->getRowCompanyProduct($company_id, $this->input->get('search'));
        $config['per_page'] = 8;
        $config['num_link'] = 1;
        $config['uri_segment'] = count($this->uri->segments);
        $config['use_page_numbers'] = true;
        $page = $this->uri->segment(count($this->uri->segments));
        $start = ($page - 1) * $config['per_page'];

        $this->pagination->initialize($config);
        $pagination = $this->pagination->create_links();

        $product = $this->product->getCompanyProduct($company_id, $config['per_page'], $start, $this->session->userdata('price_group_id'), $this->input->get('search')
                ?? null);
        $output = "";
        // print_r($product);die;
        foreach ($product as $key => $prod) {
            $prod->price = $prod->group_price && $prod->group_price > 0 ? $prod->group_price : $prod->price;
            $unit = $this->product->getUnit($prod->sale_unit);
            $prod->price = $this->__operate($prod->price, $unit->operation_value, $unit->operator);

            $output .= "
            <div class='col-sm-6 col-md-4 col-lg-3 mb-2'>
            <a href='" . base_url(aksestoko_route("aksestoko/product/view/" . $prod->id)) . " ' class='product-item box animated fadeInUp delayp1'>
                <div class='product-content clearfix'>
                <img src='" . url_image_thumb($prod->thumb_image) . "' onerror=\"this.src='" . base_url('assets/uploads/no_image.png') . "' \" class='product-img img-fluid' alt='Product'>
                    <div class='product-info'>
                        <h4>" . $prod->name . "</h4>";
            if ($prod->price > 0) {
                $output .= "<span class='price d-block'>Rp " . number_format($prod->price, 0, ',', '.') . " / " . convert_unit($unit->name) . "</span>";
            }
            $output .= "<button class='btn btn-sm btn-success font-button'>Beli</button>
                    </div>    
                </div>
            </a>
            </div>
            ";
        }

        $res = array(
            'pagination' => $pagination,
            'product' => $output
        );

        echo json_encode($res);
    }

    public function faqOld()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/faq', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function faq()
    {
        // $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->data['faq'] = $this->home->getActivationCmsFaq();
        $this->load->view('aksestoko/faqNew/faq', $this->data);
    }

    public function reward()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->session->set_flashdata('error', 'Halaman dalam pengembangan');
        redirect(aksestoko_route('aksestoko/home/main'));

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/reward', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function getJiraIssue($issueId)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        
        try {
            $issue = $this->integration->get_jira_issue(['issueId' => $issueId]);
            $issue->status = true;
        } catch (\Throwable $th) {
            $issue = (object) [
                'status' => false
            ];
        }
        header('Content-type: application/json');
        echo json_encode($issue);
    }

    //GET
    public function cs()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->data['title_at'] = "Layanan Pelanggan - AksesToko";
        $issues = $this->integration->search_jira_issues([
            'username' => $this->session->userdata("username"),
            'source_url' => base_url()
        ]);
        $listIssues = [];
        $statusIssue = [
            'To Do' => 'Dalam Antrean',
            'In Progress' => 'Sedang Diproses',
            'Done' => 'Selesai'
        ];
        foreach ($issues as $key => $issue) {
            $listIssues [] = [
                'id' => $issue->id,
                'key' => $issue->key,
                'type' => $issue->fields->issuetype->name,
                'status' => $statusIssue[$issue->fields->status->name],
                'subject' => $issue->fields->summary,
                'description' => $issue->fields->description,
                'assignee' => $issue->fields->assignee->displayName
            ];
        }

        $this->data['listIssues'] = $listIssues;

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/customer-service', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    /**
     * POST
     *
     * Request :
     * - subject -> text
     * - description -> text
     */
    public function add_issue()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $subject = $this->input->post('subject') . " - " . $this->session->userdata("username");
                $user = [
                    "user_id" => $this->session->userdata("user_id"),
                    "company_id" => $this->session->userdata("company_id"),
                    "username" => $this->session->userdata("username"),
                    "description" => $this->input->post('description')
                ];
                $description = '<pre><code class="json">';
                $description .= json_encode($user);
                // $description .= "<br><---><br>";
                // $description .= $this->input->post('description');
                $description .= '</code></pre>';

                $insertIssue = $this->home->insertIssue($subject, $description);
                if (!$insertIssue) {
                    throw new \Exception("Tidak dapat membuat issue");
                }

                $this->session->set_flashdata('message', 'Berhasil membuat issue');
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('error', $th->getMessage());
                $this->session->set_flashdata('value', [
                    'subject' => $this->input->post('subject'),
                    'description' => $this->input->post('description')
                ]);
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * POST
     *
     * Request :
     * - subject -> text
     * - description -> text
     */
    public function add_issue_jira()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        if ($this->isPost()) {
            $this->db->trans_begin();

            try {
                $user = $this->at_site->getUser();
                $data = [
                    "fields" => [
                        "project" => [
                            "key" => "ACS"
                        ],
                        "summary" => "Layanan Pelanggan " . $this->session->userdata("username") . " - " . $this->input->post('subject'),
                        "description" => $this->input->post('description'),
                        "issuetype" => [
                            "name" => "Support"
                        ],
                        "priority" => [
                            "name" => "High"
                        ],
                        "duedate" => date("Y-m-d"),
                        "labels" => ["support", str_replace(" ", "-", $this->input->post('subject')) ],
                        "assignee" => [
                            "name" => "abdullah.fahmi"
                        ],
                        "customfield_10801" => $this->session->userdata("username"),
                        "customfield_10802" => $this->session->userdata("company_name"),
                        "customfield_10800" => base_url(),
                        "customfield_10803" => $user->phone,
                    ]
                ];

                $insertIssue = $this->integration->add_jira_issue($data);
                if (!$insertIssue) {
                    throw new \Exception("Tidak dapat membuat issue");
                }

                $this->session->set_flashdata('message', 'Berhasil membuat issue ('.$insertIssue->key.'). Kami akan menghubungi Anda segera.');
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('error', $th->getMessage());
                $this->session->set_flashdata('value', [
                    'subject' => $this->input->post('subject'),
                    'description' => $this->input->post('description')
                ]);
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * GET
     *
     */
    public function add_comment_jira($issueId)
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        try {
            $user = $this->at_site->getUser();
            $body = $this->input->get('body');
            $data = [
                "body" => $user->company . " (" . $user->username . ") ||||| " . urldecode($body),
                "issueId" => $issueId
            ];

            $insertIssue = $this->integration->add_jira_comment($data);
            if (!$insertIssue) {
                throw new \Exception("Tidak dapat membuat komentar");
            }

            $response = [
                'status' => true,
                'message' => 'Berhasil menambahkan komentar'
            ];
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => 'Gagal menambahkan komentar'
            ];
        }
        header('Content-type: application/json');
        echo json_encode($response);
    }

    // -----------------------------Start Point----------------------------- //

    public function point()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris

        $this->session->set_flashdata('error', 'Halaman ini tidak bisa diakses');
        redirect(aksestoko_route('aksestoko/home/main'));

        $this->data['title_at'] = "Poin Toko - AksesToko";
        $kd_customer = $this->session->userdata("identity");
        $this->data['poin'] = $this->home->getPoint($kd_customer);
        $this->data['company'] = $this->session->userdata("company_name");
        $this->data['identity'] = $this->session->userdata("identity");

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/point', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function allnotif()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $this->data['title_at'] = "Pemberitahuan - AksesToko";

        if ($this->session->userdata('group_customer') == 'lt') {
            $this->data['sales_booking_pending_total'] = $this->at_site->getCountPendingSalesBooking();
            $this->data['get_bad_qty_confirm_pending'] = $this->at_site->get_bad_qty_confirm_pending();
        }

        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/allnotif', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function kredit_bank_mandiri()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $param = $this->input->post();
                $where = ['id' => $param['loanID']];
                $loan = $this->at_site->getLoanRequest($where);

                $data = [
                    'SellerID' => $loan->SellerID,
                    'NoRekMandiri' => $loan->NoRekMandiri,
                    'NoKTP' => $loan->NoKTP,
                    'Limit' => (float) $loan->Limit,
                    'Tenor' => (float) $loan->Tenor,
                    'NamaLengkap' => $loan->NamaLengkap,
                    'JenisKelamin' => $loan->JenisKelamin,
                    'TempatLahir' => $loan->TempatLahir,
                    'TanggalLahir' => $loan->TanggalLahir,
                    'NoHP' => $loan->NoHP,
                    'Email' => $loan->Email,
                    'MasaBerlakuKTP' => $loan->MasaBerlakuKTP,
                    'AlamatKTP' => $loan->AlamatKTP,
                    'KodePosKTP' => $loan->KodePosKTP,
                    'ProvinsiKTP' => $loan->ProvinsiKTP,
                    'KabupatenKotaKTP' => $loan->KabupatenKotaKTP,
                    'KecamatanKTP' => $loan->KecamatanKTP,
                    'KelurahanKTP' => $loan->KelurahanKTP,
                    'RTKTP' => $loan->RTKTP,
                    'RWKTP' => $loan->RWKTP,
                    'AlamatTinggal' => $loan->AlamatTinggal,
                    'KodeposTinggal' => $loan->KodeposTinggal,
                    'ProvinsiTinggal' => $loan->ProvinsiTinggal,
                    'KabupatenKotaTinggal' => $loan->KabupatenKotaTinggal,
                    'KecamatanTinggal' => $loan->KecamatanTinggal,
                    'KelurahanTinggal' => $loan->KelurahanTinggal,
                    'RTTinggal' => $loan->RTTinggal,
                    'RWTinggal' => $loan->RWTinggal,
                    'NPWP' => $loan->NPWP,
                    'NamaIbuKandung' => $loan->NamaIbuKandung,
                    'eCommID' => $loan->eCommID,
                ];

                $loanRequest = $this->integration->mandiri_loanRequest($data);
                if (!$loanRequest) {
                    throw new \Exception("Gagal mengirim data loan");
                }

                $sendFile = $this->integration->sendFileToMft($param['loanID']);
                if (!$sendFile) {
                    throw new \Exception("Gagal mengirim file ke sftp");
                }

                $dataLoan = [
                    'loanID' => $loanRequest->LoanID,
                    'StatusCode' => $loanRequest->StatusCode,
                    'Deskripsi' => $loanRequest->Deskripsi,
                    'dateRequest' => date('Y-m-d H:i:s'),
                    'statusLoan' => 'on_process',
                    'user_id' => $this->session->userdata('user_id'),
                ];
                // $dataLoan = [
                //     'loanID'        =>'201',
                //     'StatusCode'    => '00',
                //     'Deskripsi'     => 'Success',
                //     'dateRequest'       => date('Y-m-d H:i:s'),
                //     'statusLoan'        => 'on_process',
                //     'user_id'           => $this->session->userdata('user_id'),
                // ];

                $updateLoan = $this->at_site->updateLoanRequest($dataLoan, ['id' => $loan->id]);
                if (!$updateLoan) {
                    throw new \Exception("Gagal memperbarui data loan");
                }

                $this->session->set_flashdata('message', 'Berhasil mengajukan kredit');
                $this->db->trans_commit();
                redirect(aksestoko_route('aksestoko/home/kredit_bank_mandiri/'));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect(aksestoko_route('aksestoko/home/kredit_bank_mandiri'));
            }
        } else {
            $where = ['company_id' => $this->session->userdata('company_id')];
            $this->data['loan'] = $loan = $this->at_site->getLoanRequest($where);
            $statusLoan = [
                'pending' => 'Belum Diajukan',
                'on_process' => 'Dalam Pengajuan',
                'Canceled by Bank' => 'Dibatalkan oleh Bank',
                'Reject by Bank' => 'Ditolak oleh Bank',
                'Invalid Data' => 'Data Tidak Valid',
                'On Progress' => 'Sedang Diproses oleh Bank',
                'Request PK Signing' => 'Permintaan Tanda tangan',
                'Approve' => 'Diterima'
            ];
            if ($loan != null) {
                $this->data['statusLoan'] = $statusLoan[$loan->statusLoan];
                $this->data['status'] = $this->status_kredit_mandiri($loan);
                $this->data['limit'] = $this->at_payment->getLimitMandiri();
            } else {
                $this->session->set_flashdata('error', 'Anda belum memenuhi syarat pengajuan Kredit Bank Mandiri');
                redirect(aksestoko_route('aksestoko/home/programs'));
            }

            $this->data['title_at'] = "Kredit Bank Mandiri - AksesToko";
            $this->load->view('aksestoko/header', $this->data);
            $this->load->view('aksestoko/kredit_bank_mandiri/menu', $this->data);
            $this->load->view('aksestoko/footer', $this->data);
        }
    }

    private function status_kredit_mandiri($loan)
    {
        $return = [];
        $arr = [
            'NoRekMandiri',
            'SellerID',
            'NoKTP',
            'Limit',
            'Tenor',
            'NamaLengkap',
            'JenisKelamin',
            'TempatLahir',
            'TanggalLahir',
            'NoHP',
            'Email',
            'AlamatKTP',
            'KodePosKTP',
            'ProvinsiKTP',
            'KabupatenKotaKTP',
            'KecamatanKTP',
            'KelurahanKTP',
            'RTKTP',
            'RWKTP',
            'AlamatTinggal',
            'KodeposTinggal',
            'ProvinsiTinggal',
            'KabupatenKotaTinggal',
            'KecamatanTinggal',
            'KelurahanTinggal',
            'RTTinggal',
            'RWTinggal',
            'NPWP',
            'NamaIbuKandung',
            'foto',
            'foto_ktp',
            'foto_npwp',
        ];
        if ($loan->statusLoan == 'pending') {
            foreach ($arr as $key => $value) {
                if (is_null($loan->{$value}) || empty($loan->{$value})) {
                    $return['message'] = 'Lengkapi data Anda untuk mengajukan kredit';
                    $return['type'] = 'text-danger';
                    $return['button_ajukan'] = 'disabled';
                    $return['button_perbarui'] = '';
                    return $return;
                }
            }
            $return['message'] = 'Data telah diisi lengkap, silahkan ajukan kredit';
            $return['type'] = 'text-success';
            $return['button_ajukan'] = '';
            $return['button_perbarui'] = '';
            return $return;
        }

        if ($loan->statusLoan == 'on_process') {
            $return['message'] = 'Data Anda telah diajukan ke Bank Mandiri, mohon tunggu informasi selanjutnya';
            $return['type'] = 'text-info';
            $return['button_ajukan'] = 'd-none';
            $return['button_perbarui'] = 'd-none';
            return $return;
        }

        if ($loan->statusLoan == 'Approve') {
            $return['message'] = 'Kredit Anda telah diterima oleh Bank Mandiri';
            $return['type'] = 'text-success';
            $return['button_ajukan'] = 'd-none';
            $return['button_perbarui'] = 'd-none';
            return $return;
        }

        if (in_array($loan->statusLoan, ['Reject by Bank', 'Canceled by Bank', 'Invalid Data'])) {
            $loanStatus = $this->at_site->getLoanStatus(['LoanId' => $loan->loanID, 'Status' => $loan->statusLoan]);
            $return['message'] = 'Kredit Anda belum disetujui oleh Bank Mandiri. ' . @$loanStatus->Keterangan;
            $return['type'] = 'text-danger';
            $return['button_ajukan'] = '';
            $return['button_perbarui'] = '';
            return $return;
        }

        if (in_array($loan->statusLoan, ['On Progress', 'Request PK Signing'])) {
            $return['message'] = 'Kredit Anda sedang diproses oleh Bank Mandiri';
            $return['type'] = 'text-info';
            $return['button_ajukan'] = 'd-none';
            $return['button_perbarui'] = 'd-none';
            return $return;
        }
    }

    public function form_kredit_bank_mandiri()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $param = $this->input->post();
                $sameAddress = $param['sameAddress'] == 'on';
                $data = [
                    'user_id' => $this->session->userdata('user_id'),
                    'NoRekMandiri' => $param['NoRekMandiri'],
                    'NoKTP' => $param['NoKTP'],
                    'NamaLengkap' => $param['NamaLengkap'],
                    'JenisKelamin' => $param['JenisKelamin'],
                    'TempatLahir' => $param['TempatLahir'],
                    'TanggalLahir' => $param['TanggalLahir'],
                    'NoHP' => $param['NoHP'],
                    'Email' => $param['Email'],
                    'MasaBerlakuKTP' => $param['MasaBerlakuKTP'],
                    'AlamatKTP' => $param['AlamatKTP'],
                    'KodePosKTP' => $param['KodePosKTP'],
                    'ProvinsiKTP' => $param['ProvinsiKTP'],
                    'KabupatenKotaKTP' => $param['KabupatenKotaKTP'],
                    'KecamatanKTP' => $param['KecamatanKTP'],
                    'KelurahanKTP' => $param['KelurahanKTP'],
                    'RTKTP' => $param['RTKTP'],
                    'RWKTP' => $param['RWKTP'],
                    'AlamatTinggal' => $sameAddress ? $param['AlamatKTP'] : $param['AlamatTinggal'],
                    'KodeposTinggal' => $sameAddress ? $param['KodePosKTP'] : $param['KodeposTinggal'],
                    'ProvinsiTinggal' => $sameAddress ? $param['ProvinsiKTP'] : $param['ProvinsiTinggal'],
                    'KabupatenKotaTinggal' => $sameAddress ? $param['KabupatenKotaKTP'] : $param['KabupatenKotaTinggal'],
                    'KecamatanTinggal' => $sameAddress ? $param['KecamatanKTP'] : $param['KecamatanTinggal'],
                    'KelurahanTinggal' => $sameAddress ? $param['KelurahanKTP'] : $param['KelurahanTinggal'],
                    'RTTinggal' => $sameAddress ? $param['RTKTP'] : $param['RTTinggal'],
                    'RWTinggal' => $sameAddress ? $param['RWKTP'] : $param['RWTinggal'],
                    'NPWP' => $param['NPWP'],
                    'NamaIbuKandung' => $param['NamaIbuKandung'],
                    'sameAddress' => $param['sameAddress']
                ];


                if ($_FILES['uploadFoto']['size'] > 0) {
                    $uploadedImg = $this->integration->upload_files($_FILES['uploadFoto']);
                    $data['foto'] = $uploadedImg->url;
                }

                if ($_FILES['uploadKTP']['size'] > 0) {
                    $uploadedImg = $this->integration->upload_files($_FILES['uploadKTP']);
                    $data['foto_ktp'] = $uploadedImg->url;
                }

                if ($_FILES['uploadNPWP']['size'] > 0) {
                    $uploadedImg = $this->integration->upload_files($_FILES['uploadNPWP']);
                    $data['foto_npwp'] = $uploadedImg->url;
                }

                $where = ['id' => $param['loanID']];
                $loan = $this->at_site->updateLoanRequest($data, $where);
                if (!$loan) {
                    throw new \Exception("Gagal menyimpan data loan");
                }

                $this->session->set_flashdata('message', 'Berhasil memperbarui data');
                $this->db->trans_commit();
                redirect(aksestoko_route('aksestoko/home/kredit_bank_mandiri/'));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect(aksestoko_route('aksestoko/home/form_kredit_bank_mandiri/'));
            }
        } else {
            $where = ['company_id' => $this->session->userdata('company_id')];
            $this->data['loan'] = $loan = $this->at_site->getLoanRequest($where);

            $this->data['title_at'] = "Formulir Kredit Bank Mandiri - AksesToko";
            $this->load->view('aksestoko/header', $this->data);
            $this->load->view('aksestoko/kredit_bank_mandiri/formulir', $this->data);
            $this->load->view('aksestoko/footer', $this->data);
        }
    }

    // -----------------------------End Point----------------------------- //
    // -----------------------------Test----------------------------- //
    // function sendotp()
    //  {
    //      // $this->load->view('aksestoko/header', $this->data);
    //      $this->load->view('aksestoko/send_otp', $this->data);
    //      // $this->load->view('aksestoko/footer', $this->data);
    //  }

    function kodepos($kodepos)
    {
        echo json_encode($this->daerah->getByKodePos($kodepos));
    }

    public function kur_bank_btn()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $company_id = $this->session->userdata('company_id');
        $pengajuan = $this->at_site->getKurBtnRequest($company_id);

        if(!$pengajuan) {
            $this->session->set_flashdata('error', 'Anda belum memenuhi syarat-syarat pengajuan KUR Bank BTN');
            redirect(aksestoko_route('aksestoko/home/programs'));
        }

        if ($this->isPost()) {
            try {
                $dataPengajuan = json_decode(json_encode($pengajuan), true);
                $dataPengajuan['omset_u'] = (float)$dataPengajuan['omset_u'];
                $dataPengajuan['plafon_kur'] = (float)$dataPengajuan['plafon_kur'];
                foreach (['foto_debitur','foto_ktp','foto_npwp','foto_izinUsaha',] as $field) {
                    if($dataPengajuan[$field]){
                        $dataPengajuan[$field] = base64_encode(file_get_contents($dataPengajuan[$field]));
                    }
                }
                unset($dataPengajuan['created_at'], $dataPengajuan['updated_at'], $dataPengajuan['pengajuan_at']);
                unset($dataPengajuan['respon_id'], $dataPengajuan['company_id']);
                unset($dataPengajuan['id'], $dataPengajuan['status']);
                $dataPengajuan['channel'] = 'SEING';
                $result = $this->integration->kurBtnPengajuanKredit($dataPengajuan);
                if(!$result){
                    throw new Exception("Gagal mengajukan KUR Bank BTN");
                }
                $updateValue = [
                    'status' => 'on_process',
                    'respon_id' => $result->id,
                    'pengajuan_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if (!$this->db->update('btn_pengajuan_kur', $updateValue, ['id'=>$pengajuan->id])) {
                    $error = $this->db->error();
                    throw new Exception('Update db error: '.$error['message']);
                }
                $this->session->set_flashdata('message', 'Berhasil mengajukan data');
                redirect(aksestoko_route('aksestoko/home/kur_bank_btn'));
            } catch (\Throwable $exc) {
                $this->session->set_flashdata('error', $exc->getMessage());
                redirect(aksestoko_route('aksestoko/home/kur_bank_btn'));
            }
        } else {
            $syarat = $this->at_site->checkPersyaratanKurBtn($company_id);
            $this->data['syarat'] = $syarat;
            $this->data['syaratMemenuhi'] = $syarat['jumlah'] >= 10 && $syarat['tonase'] >= 100;
            $this->data['pengajuan'] = $pengajuan;
            $this->data['pengajuan']->cabang = $this->at_site->findBtnBranchs($pengajuan->cabang);
            $statusPengajuan = [
                'none' => 'Belum Mengisi',
                'pending' => 'Belum Diajukan',
                'on_process' => 'Telah Diajukan',
            ];
            if ($pengajuan != null) {
                $this->data['status'] = $statusPengajuan[$pengajuan->status];
                $this->data['notif_type'] = $pengajuan->status == 'pending' ? 'text-info' : ($pengajuan->status == 'on_process' ? 'text-success' : 'text-danger');
                $this->data['button_perbarui'] = $pengajuan->status == 'pending' ? '' : ($pengajuan->status == 'on_process' ? 'd-none' : '');
                $this->data['button_ajukan'] = $pengajuan->status == 'pending' ? '' : ($pengajuan->status == 'on_process' ? 'd-none' : '');
                $this->data['notif'] = $pengajuan->status == 'pending' ? 'Lengkapi data Anda untuk mengajukan KUR' : ($pengajuan->status == 'on_process' ? 'Pengajuan KUR telah berhasil dikirimkan ke Bank BTN' : '');
            } else {
                $this->data['notif_type'] = 'text-danger';
                $this->data['status'] = 'Belum Mengisi';
                $this->data['button_ajukan'] = 'disabled';
                $this->data['notif'] = 'Tekan Perbarui Data untuk mengisi formulir pengajuan';
            }
    
            $this->data['title_at'] = "KUR Bank BTN - AksesToko";
            $this->load->view('aksestoko/header', $this->data);
            $this->load->view('aksestoko/kur_bank_btn/kur_bank_btn', $this->data);
            $this->load->view('aksestoko/footer', $this->data);        
        }
    }

    public function form_kur_bank_btn()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        $company_id = $this->session->userdata('company_id');
        $pengajuan = $this->at_site->getKurBtnRequest($company_id);
        
        if(!$pengajuan){
            $this->session->set_flashdata('error', 'Anda tidak dizinkan mengajukan KUR Bank BTN');
            redirect(aksestoko_route('aksestoko/home/kur_bank_btn'));
        }
        $syarat = $this->at_site->checkPersyaratanKurBtn($company_id);
        if(!($syarat['jumlah'] >= 10 && $syarat['tonase'] >= 100)) {
            $this->session->set_flashdata('error', 'Anda belum memenuhi syarat mengajukan KUR Bank BTN');
            redirect(aksestoko_route('aksestoko/home/kur_bank_btn'));
        }
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $params = $this->input->post();
                $data = [
                    'company_id'        => $company_id,
                    'channel'           => 'SEING',
                    'ktp'               => $params['ktp'],
                    'cabang'            => $params['cabang'],
                    'nama'              => $params['nama'],
                    'tempat_lahir'      => $params['tempat_lahir'],
                    'tanggal_lahir'     => $params['tanggal_lahir'],
                    'jenis_kelamin'     => $params['jenis_kelamin'],
                    'hp'                => $params['hp'],
                    'email'             => $params['email'],
                    'alamat_tt'         => $params['alamat_tt'],
                    'rt_tt'             => $params['rt_tt'],
                    'rw_tt'             => $params['rw_tt'],
                    'kelurahan_tt'      => $params['kelurahan_tt'],
                    'kecamatan_tt'      => $params['kecamatan_tt'],
                    'kota_tt'           => $params['kota_tt'],
                    'provinsi_tt'       => $params['provinsi_tt'],
                    'kodepos_tt'        => $params['kodepos_tt'],
                    'status_tt'         => $params['status_tt'],
                    'alamat_u'          => $params['alamat_u'],
                    'rt_u'              => $params['rt_u'],
                    'rw_u'              => $params['rw_u'],
                    'kelurahan_u'       => $params['kelurahan_u'],
                    'kecamatan_u'       => $params['kecamatan_u'],
                    'kota_u'            => $params['kota_u'],
                    'provinsi_u'        => $params['provinsi_u'],
                    'kodepos_u'         => $params['kodepos_u'],
                    'status_tu'         => $params['status_tu'],
                    'lama_u'            => $params['lama_u'],
                    'nama_u'            => $params['nama_u'],
                    'sektor_u'          => $params['sektor_u'],
                    'omset_u'           => $params['omset_u'],
                    'jangka_waktu'      => $params['jangka_waktu'],
                    'plafon_kur'        => $params['plafon_kur'],
                    'tujuan_kur'        => $params['tujuan_kur'],
                    'tujuan_detail'     => $params['tujuan_detail'],
                    'status_menikah'    => $params['status_menikah'],
                    'nama_pasangan'     => $params['status_menikah'] == '1' ? $params['nama_pasangan'] : null,
                    'ktp_pasangan'      => $params['status_menikah'] == '1' ? $params['ktp_pasangan'] : null,
                    'tmptlhr_pasangan'  => $params['status_menikah'] == '1' ? $params['tmptlhr_pasangan'] : null,
                    'tgllhr_pasangan'   => $params['status_menikah'] == '1' ? $params['tgllhr_pasangan'] : null,
                    'hp_pasangan'       => $params['status_menikah'] == '1' ? $params['hp_pasangan'] : null,
                    'email_pasangan'    => $params['status_menikah'] == '1' ? $params['email_pasangan'] : null,
                ];

                if ($_FILES['foto_debitur']['size'] > 0) {
                    $uploadedImg = $this->integration->upload_files($_FILES['foto_debitur']);
                    $data['foto_debitur'] = $uploadedImg->url;
                    $data['fDebitur_ext'] = $_FILES['foto_debitur']['type'];
                }
                if ($_FILES['foto_ktp']['size'] > 0) {
                    $uploadedImg = $this->integration->upload_files($_FILES['foto_ktp']);
                    $data['foto_ktp'] = $uploadedImg->url;
                    $data['fKtp_ext'] = $_FILES['foto_ktp']['type'];
                }
                if ($_FILES['foto_npwp']['size'] > 0) {
                    $uploadedImg = $this->integration->upload_files($_FILES['foto_npwp']);
                    $data['foto_npwp'] = $uploadedImg->url;
                    $data['fNpwp_ext'] = $_FILES['foto_npwp']['type'];
                }
                if ($_FILES['foto_izin_usaha']['size'] > 0) {
                    $uploadedImg = $this->integration->upload_files($_FILES['foto_izin_usaha']);
                    $data['foto_izinUsaha'] = $uploadedImg->url;
                    $data['fIzinUsaha_ext'] = $_FILES['foto_izin_usaha']['type'];
                }


                $this->data['pengajuan'] = (object) $data;
                $this->at_site->insertKurBtnRequest($data);

                $this->session->set_flashdata('message', 'Berhasil menyimpan data');
                $this->db->trans_commit();
                redirect(aksestoko_route('aksestoko/home/kur_bank_btn'));
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect(aksestoko_route('aksestoko/home/kur_bank_btn'));
            }
        } else {
            $this->data['pengajuan'] = $pengajuan;
            $branchs = $this->at_site->getBtnBranchs();
            $optionBranchs = ['' => 'Pilih Cabang'];
            foreach ($branchs as $key => $branch) {
                $optionBranchs [$branch->code] = $branch->name;
            }
            $this->data['branchs'] = $optionBranchs;
        }

        $this->data['title_at'] = "Formulir KUR Bank BTN - AksesToko";
        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/kur_bank_btn/formulir', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function success_kreditpro(){
        $this->data['title_at'] = "Berhasil Kredit Pro";
        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/success_kreditpro', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function failed_kreditpro(){
        $this->data['title_at'] = "Pengajuan Kredit Pro";
        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/failed_kreditpro', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function programs()
    {
        $this->checkATLogged(); // seharusnya di paling atas baris
        // check allow kur btn
        $company_id = $this->session->userdata('company_id');
        $pengajuan = $this->at_site->getKurBtnRequest($company_id);

        $programs = [];
        if($pengajuan != null){
            $programs [] = [
                'title' => 'Kredit Usaha Rakyat Bank BTN',
                'description' => 'KUR hadir untuk memberikan solusi pembiayaan modal kerja dan investasi untuk meningkatkan kemampuan usaha skala Mikro, Kecil, dan Menengah (UMKM).',
                'image' => $this->data['assets_at'] . "img/kur_bank_btn.jpg",
                'link' => base_url(aksestoko_route("aksestoko/home/kur_bank_btn")),
                'provided_by' => 'Bank Tabungan Negara',
            ];
        }

        $loan = $this->at_site->getLoanRequest(['company_id' => $company_id]);
        if($loan != null){
            $programs [] = [
                'title' => 'Kredit Bank Mandiri',
                'description' => 'Kredit Bank Mandiri memberikan layanan pinjaman berupa saldo kredit sebagai metode pembayaran yang diterima distributor di AksesToko.',
                'image' => $this->data['assets_at'] . "img/bank-mandiri-2-1.png",
                'link' => base_url(aksestoko_route("aksestoko/home/kredit_bank_mandiri")),
                'provided_by' => 'Bank Mandiri',
            ];
        }

        $this->data['programs'] = $programs;
        $this->data['title_at'] = "Program Kredit - AksesToko";
        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/program', $this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }
}

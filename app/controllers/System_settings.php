<?php defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class system_settings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Admin && !$this->Owner && !$this->LT && !$this->Principal) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->lang->load('settings', $this->Settings->user_language);
        $this->lang->load('bank', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('settings_model');
        $this->load->model('Site', 'site');
        $this->load->model('companies_model');
        $this->load->model('sales_model');
        $this->load->model('companies_model');
        $this->load->model('promo_model');
        $this->load->model('user_promotion_model');
        $this->load->model('integration_model', 'integration');
        $this->lang->load('notifications', $this->Settings->user_language);
        $this->load->model('authorized_model');
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif';
        $this->allowed_file_size = '1024';
        // $this->insertLogActivities();
    }

    public function index()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->form_validation->set_rules('site_name', lang('site_name'), 'trim|required');
        $this->form_validation->set_rules('dateformat', lang('dateformat'), 'trim|required');
        $this->form_validation->set_rules('timezone', lang('timezone'), 'trim|required');
        $this->form_validation->set_rules('mmode', lang('maintenance_mode'), 'trim|required');
        //$this->form_validation->set_rules('logo', lang('logo'), 'trim');
        $this->form_validation->set_rules('iwidth', lang('image_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('iheight', lang('image_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('twidth', lang('thumbnail_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('theight', lang('thumbnail_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('display_all_products', lang('display_all_products'), 'trim|numeric|required');
        $this->form_validation->set_rules('watermark', lang('watermark'), 'trim|required');
        $this->form_validation->set_rules('currency', lang('default_currency'), 'trim|required');
        $this->form_validation->set_rules('email', lang('default_email'), 'trim|required');
        $this->form_validation->set_rules('language', lang('language'), 'trim|required');
        $this->form_validation->set_rules('warehouse', lang('default_warehouse'), 'trim|required');
        $this->form_validation->set_rules('biller', lang('default_biller'), 'trim|required');
        $this->form_validation->set_rules('tax_rate', lang('product_tax'), 'trim|required');
        $this->form_validation->set_rules('tax_rate2', lang('invoice_tax'), 'trim|required');
        $this->form_validation->set_rules('sales_prefix', lang('sales_prefix'), 'trim');
        $this->form_validation->set_rules('quote_prefix', lang('quote_prefix'), 'trim');
        $this->form_validation->set_rules('purchase_prefix', lang('purchase_prefix'), 'trim');
        $this->form_validation->set_rules('transfer_prefix', lang('transfer_prefix'), 'trim');
        $this->form_validation->set_rules('delivery_prefix', lang('delivery_prefix'), 'trim');
        $this->form_validation->set_rules('payment_prefix', lang('payment_prefix'), 'trim');
        $this->form_validation->set_rules('return_prefix', lang('return_prefix'), 'trim');
        $this->form_validation->set_rules('expense_prefix', lang('expense_prefix'), 'trim');
        $this->form_validation->set_rules('sc_prefix', lang('sc_prefix'), 'trim');
        $this->form_validation->set_rules('consignment_prefix', lang('consignment_prefix'), 'trim');
        $this->form_validation->set_rules('cpayment_prefix', lang('cpayment_prefix'), 'trim');
        $this->form_validation->set_rules('binvoice_prefix', lang('binvoice_prefix'), 'trim');
        $this->form_validation->set_rules('bpayment_prefix', lang('bpayment_prefix'), 'trim');
        $this->form_validation->set_rules('detect_barcode', lang('detect_barcode'), 'trim|required');
        $this->form_validation->set_rules('theme', lang('theme'), 'trim|required');
        $this->form_validation->set_rules('rows_per_page', lang('rows_per_page'), 'trim|required|greater_than[9]|less_than[501]');
        $this->form_validation->set_rules('accounting_method', lang('accounting_method'), 'trim|required');
        $this->form_validation->set_rules('product_serial', lang('product_serial'), 'trim|required');
        $this->form_validation->set_rules('product_discount', lang('product_discount'), 'trim|required');
        $this->form_validation->set_rules('bc_fix', lang('bc_fix'), 'trim|numeric|required');
        $this->form_validation->set_rules('protocol', lang('email_protocol'), 'trim|required');
        if ($this->input->post('protocol') == 'smtp') {
            $this->form_validation->set_rules('smtp_host', lang('smtp_host'), 'required');
            $this->form_validation->set_rules('smtp_user', lang('smtp_user'), 'required');
            $this->form_validation->set_rules('smtp_pass', lang('smtp_pass'), 'required');
            $this->form_validation->set_rules('smtp_port', lang('smtp_port'), 'required');
        }
        if ($this->input->post('protocol') == 'sendmail') {
            $this->form_validation->set_rules('mailpath', lang('mailpath'), 'required');
        }
        $this->form_validation->set_rules('decimals', lang('decimals'), 'trim|required');
        $this->form_validation->set_rules('decimals_sep', lang('decimals_sep'), 'trim|required');
        $this->form_validation->set_rules('thousands_sep', lang('thousands_sep'), 'trim|required');
        $this->load->library('encrypt');

        if ($this->form_validation->run() == true) {
            $language = $this->input->post('language');

            if ((file_exists(APPPATH . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'sma_lang.php') && is_dir(APPPATH . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language)) || $language == 'english') {
                $lang = $language;
            } else {
                $this->session->set_flashdata('error', lang('language_x_found'));
                redirect("system_settings");
                $lang = 'english';
            }

            $tax1 = ($this->input->post('tax_rate') != 0) ? 1 : 0;
            $tax2 = ($this->input->post('tax_rate2') != 0) ? 1 : 0;

            $data = array(
                'site_name' => DEMO ? 'Forca Pos' : $this->input->post('site_name'),
                'rows_per_page' => $this->input->post('rows_per_page'),
                'dateformat' => $this->input->post('dateformat'),
                'timezone' => DEMO ? 'Asia/Kuala_Lumpur' : $this->input->post('timezone'),
                'mmode' => trim($this->input->post('mmode')),
                'iwidth' => $this->input->post('iwidth'),
                'iheight' => $this->input->post('iheight'),
                'twidth' => $this->input->post('twidth'),
                'theight' => $this->input->post('theight'),
                'watermark' => $this->input->post('watermark'),
                // 'reg_ver' => $this->input->post('reg_ver'),
                // 'allow_reg' => $this->input->post('allow_reg'),
                // 'reg_notification' => $this->input->post('reg_notification'),
                'accounting_method' => $this->input->post('accounting_method'),
                'default_email' => DEMO ? 'noreply@sisi.id' : $this->input->post('email'),
                'language' => $lang,
                'default_warehouse' => $this->input->post('warehouse'),
                'default_tax_rate' => $this->input->post('tax_rate'),
                'default_tax_rate2' => $this->input->post('tax_rate2'),
                'sales_prefix' => $this->input->post('sales_prefix'),
                'quote_prefix' => $this->input->post('quote_prefix'),
                'purchase_prefix' => $this->input->post('purchase_prefix'),
                'transfer_prefix' => $this->input->post('transfer_prefix'),
                'delivery_prefix' => $this->input->post('delivery_prefix'),
                'payment_prefix' => $this->input->post('payment_prefix'),
                'ppayment_prefix' => $this->input->post('ppayment_prefix'),
                'qa_prefix' => $this->input->post('qa_prefix'),
                'return_prefix' => $this->input->post('return_prefix'),
                'expense_prefix' => $this->input->post('expense_prefix'),
                'stock_prefix' => $this->input->post('stock_prefix'),
                'consignment_prefix' => $this->input->post('consignment_prefix'),
                'cpayment_prefix' => $this->input->post('cpayment_prefix'),
                'binvoice_prefix' => $this->input->post('binvoice_prefix'),
                'bpayment_prefix' => $this->input->post('bpayment_prefix'),
                'auto_detect_barcode' => trim($this->input->post('detect_barcode')),
                'theme' => trim($this->input->post('theme')),
                'product_serial' => $this->input->post('product_serial'),
                'customer_group' => $this->input->post('customer_group'),
                'product_expiry' => $this->input->post('product_expiry'),
                'product_discount' => $this->input->post('product_discount'),
                'default_currency' => $this->input->post('currency'),
                'bc_fix' => $this->input->post('bc_fix'),
                'tax1' => $tax1,
                'tax2' => $tax2,
                'overselling' => $this->input->post('restrict_sale'),
                'reference_format' => $this->input->post('reference_format'),
                'racks' => $this->input->post('racks'),
                'attributes' => $this->input->post('attributes'),
                'restrict_calendar' => $this->input->post('restrict_calendar'),
                'captcha' => $this->input->post('captcha'),
                'item_addition' => $this->input->post('item_addition'),
                'protocol' => DEMO ? 'mail' : $this->input->post('protocol'),
                'mailpath' => $this->input->post('mailpath'),
                'smtp_host' => $this->input->post('smtp_host'),
                'smtp_user' => $this->input->post('smtp_user'),
                'smtp_port' => $this->input->post('smtp_port'),
                'smtp_crypto' => $this->input->post('smtp_crypto') ? $this->input->post('smtp_crypto') : null,
                'decimals' => $this->input->post('decimals'),
                'decimals_sep' => $this->input->post('decimals_sep'),
                'thousands_sep' => $this->input->post('thousands_sep'),
                'default_biller' => $this->input->post('biller'),
                'invoice_view' => $this->input->post('invoice_view'),
                'rtl' => $this->input->post('rtl'),
                'each_spent' => $this->input->post('each_spent') ? $this->input->post('each_spent') : null,
                'ca_point' => $this->input->post('ca_point') ? $this->input->post('ca_point') : null,
                'each_sale' => $this->input->post('each_sale') ? $this->input->post('each_sale') : null,
                'sa_point' => $this->input->post('sa_point') ? $this->input->post('sa_point') : null,
                'sac' => $this->input->post('sac'),
                'qty_decimals' => $this->input->post('qty_decimals'),
                'display_all_products' => $this->input->post('display_all_products'),
                'display_symbol' => $this->input->post('display_symbol'),
                'symbol' => $this->input->post('symbol'),
                'remove_expired' => $this->input->post('remove_expired'),
                'barcode_separator' => $this->input->post('barcode_separator'),
                'set_focus' => $this->input->post('set_focus'),
                'disable_editing' => $this->input->post('disable_editing'),
                'price_group' => $this->input->post('price_group'),
                'barcode_img' => $this->input->post('barcode_renderer'),
                'update_cost' => $this->input->post('update_cost'),
            );
            if ($this->input->post('smtp_pass')) {
                $data['smtp_pass'] = $this->encrypt->encode($this->input->post('smtp_pass'));
            }
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSetting($data)) {
            if (!DEMO && TIMEZONE != $data['timezone']) {
                if (!$this->write_index($data['timezone'])) {
                    $this->session->set_flashdata('error', lang('setting_updated_timezone_failed'));
                    redirect('system_settings');
                }
            }

            $this->session->set_flashdata('message', lang('setting_updated'));
            redirect("system_settings");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers'] = [(object) ['id' => 1, 'name' => 'Undefined', 'company' => 'Undefined']]; //$this->site->getAllCompanies('biller');
            $this->data['settings'] = $this->settings_model->getSettings();
            $this->data['currencies'] = $this->settings_model->getAllCurrencies();
            $this->data['date_formats'] = $this->settings_model->getDateFormats();
            $this->data['tax_rates'] = $this->settings_model->getAllTaxRates();
            $this->data['customer_groups'] = [(object) ['id' => 1, 'name' => 'General']]; //$this->settings_model->getAllCustomerGroups();
            $this->data['price_groups'] = [(object) ['id' => 1, 'name' => 'Undefined']]; //$this->settings_model->getAllPriceGroups();
            $this->data['warehouses'] = [(object) ['id' => 1, 'name' => 'Undefined', 'code' => 'Undefined']]; //$this->settings_model->getAllWarehouses();
            $this->data['smtp_pass'] = $this->encrypt->decode($this->data['settings']->smtp_pass);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('system_settings')));
            $meta = array('page_title' => lang('system_settings'), 'bc' => $bc);
            $this->page_construct('settings/index', $meta, $this->data);
        }
    }

    public function paypal()
    {
        $this->db->trans_begin();
        try {
            if (!$this->Owner) {
                throw new \Exception(lang('access_denied'));
            }
            $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
            if ($this->input->post('active')) {
                $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
            }
            $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
            $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
            $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'active' => $this->input->post('active'),
                    'account_email' => $this->input->post('account_email'),
                    'fixed_charges' => $this->input->post('fixed_charges'),
                    'extra_charges_my' => $this->input->post('extra_charges_my'),
                    'extra_charges_other' => $this->input->post('extra_charges_other')
                );
            }

            if ($this->form_validation->run() == true) {
                if (!$this->settings_model->updatePaypal($data)) {
                    throw new \Exception(lang('failed_to_save_message'));
                }

                $this->session->set_flashdata('message', $this->lang->line('paypal_setting_updated'));
                $this->db->trans_commit();
                redirect("system_settings/paypal");
            } else {
                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

                $this->data['paypal'] = $this->settings_model->getPaypalSettings();

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('paypal_settings')));
                $meta = array('page_title' => lang('paypal_settings'), 'bc' => $bc);
                $this->page_construct('settings/paypal', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
        }
        redirect('welcome');
    }

    public function skrill()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
            if ($this->input->post('active')) {
                $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
            }
            $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
            $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
            $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'active' => $this->input->post('active'),
                    'account_email' => $this->input->post('account_email'),
                    'fixed_charges' => $this->input->post('fixed_charges'),
                    'extra_charges_my' => $this->input->post('extra_charges_my'),
                    'extra_charges_other' => $this->input->post('extra_charges_other')
                );
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateSkrill($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', $this->lang->line('skrill_setting_updated'));
                redirect("system_settings/skrill");
            } else {
                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

                $this->data['skrill'] = $this->settings_model->getSkrillSettings();

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('skrill_settings')));
                $meta = array('page_title' => lang('skrill_settings'), 'bc' => $bc);
                $this->page_construct('settings/skrill', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect('welcome');
        }
    }

    public function change_logo()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            $this->sma->md();
        }
        $this->load->helper('security');
        try {
            $this->form_validation->set_rules('site_logo', lang("site_logo"), 'xss_clean');
            $this->form_validation->set_rules('login_logo', lang("login_logo"), 'xss_clean');
            $this->form_validation->set_rules('biller_logo', lang("biller_logo"), 'xss_clean');
            if ($this->form_validation->run() == true) {
                if ($_FILES['site_logo']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path . 'logos/';
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = 300;
                    $config['max_height'] = 80;
                    $config['overwrite'] = false;
                    $config['max_filename'] = 25;
                    //$config['encrypt_name'] = TRUE;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('site_logo')) {
                        $error = $this->upload->display_errors();
                        throw new \Exception("$error");
                    }
                    $site_logo = $this->upload->file_name;*/
                    $uploadedImg    = $this->integration->upload_files($_FILES['site_logo']);
                    $site_logo      = $uploadedImg->url;
                    $this->db->update('settings', array('logo' => $site_logo), array('setting_id' => 1));
                }

                if ($_FILES['login_logo']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path . 'logos/';
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = 300;
                    $config['max_height'] = 80;
                    $config['overwrite'] = false;
                    $config['max_filename'] = 25;
                    //$config['encrypt_name'] = TRUE;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('login_logo')) {
                        $error = $this->upload->display_errors();
                        throw new \Exception("$error");
                    }
                    $login_logo = $this->upload->file_name;*/
                    $uploadedImg    = $this->integration->upload_files($_FILES['login_logo']);
                    $login_logo     = $uploadedImg->url;
                    $this->db->update('settings', array('logo2' => $login_logo), array('setting_id' => 1));
                }

                if ($_FILES['biller_logo']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path . 'logos/';
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = 300;
                    $config['max_height'] = 80;
                    $config['overwrite'] = false;
                    $config['max_filename'] = 25;
                    //$config['encrypt_name'] = TRUE;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('biller_logo')) {
                        $error = $this->upload->display_errors();
                        throw new \Exception("$error");
                    }
                    $photo = $this->upload->file_name;*/
                    $uploadedImg = $this->integration->upload_files($_FILES['biller_logo']);
                    $photo = $uploadedImg->url;
                }

                $this->session->set_flashdata('message', lang('logo_uploaded'));
                redirect($_SERVER["HTTP_REFERER"]);
            } elseif ($this->input->post('upload_logo')) {
                throw new \Exception(validation_errors());
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/change_logo', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function write_index($timezone)
    {
        $template_path = './assets/config_dumps/index.php';
        $output_path = SELF;
        $index_file = file_get_contents($template_path);
        $new = str_replace("%TIMEZONE%", $timezone, $index_file);
        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new)) {
                @chmod($output_path, 0644);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_rules('purchase_code', lang("purchase_code"), 'required');
        $this->form_validation->set_rules('envato_username', lang("envato_username"), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('settings', array('purchase_code' => $this->input->post('purchase_code', true), 'envato_username' => $this->input->post('envato_username', true)), array('setting_id' => 1));
            redirect('system_settings/updates');
        } else {
            $fields = array('version' => $this->Settings->version, 'code' => $this->Settings->purchase_code, 'username' => $this->Settings->envato_username, 'site' => base_url());
            $this->load->helper('update');
            $protocol = is_https() ? 'https://' : 'http://';
            $updates = get_remote_contents($protocol . 'api.tecdiary.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
            $meta = array('page_title' => lang('updates'), 'bc' => $bc);
            $this->page_construct('settings/updates', $meta, $this->data);
        }
    }

    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->sma->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                redirect("system_settings/updates");
            }
        }
        $this->db->update('settings', array('version' => $version, 'update' => 0), array('setting_id' => 1));
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        redirect("system_settings/updates");
    }

    public function API()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('API_Integration')));
        $meta = array('page_title' => lang('API_Integration'), 'bc' => $bc);
        $this->page_construct('settings/API', $meta, $this->data);
    }

    public function getAPI()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("id, uri, username, password, type")
            ->from("sma_api_integration")
            ->add_column("Actions", "<div class=\"text-center\">
                <a href='" . site_url('system_settings/view_API/$1') . "' class='tip' title='" . lang("Detail_API") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> 
                <a href='" . site_url('system_settings/edit_API/$1') . "' class='tip' title='" . lang('edit_API') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-pencil\"></i></a>
            </div>", "id");
        echo $this->datatables->generate();
    }

    public function add_API()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'supplier_id' => $this->input->post('supplier_id'),
                    'uri' => $this->input->post('uri'),
                    'username' => $this->input->post('username'),
                    'password' => $this->input->post('password'),
                    'type' => $this->input->post('type'),
                    'token' => $this->input->post('token'),

                    'cf1' => $this->input->post('cf1'),
                    'cf2' => $this->input->post('cf2'),
                    'cf3' => $this->input->post('cf3'),
                    'cf4' => $this->input->post('cf4'),
                    'cf5' => $this->input->post('cf5'),
                    'cf6' => $this->input->post('cf6'),
                    'cf7' => $this->input->post('cf7'),
                    'cf8' => $this->input->post('cf8'),
                    'cf9' => $this->input->post('cf9'),
                    'cf10' => $this->input->post('cf10')
                ];
                $id = $this->settings_model->addAPI($data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("API_added"));
                redirect($_SERVER['HTTP_REFERER']);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_API', $this->data);
        }
    }

    public function edit_API($API_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['api'] = $this->settings_model->getAPIByID($API_id);
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'supplier_id' => $this->input->post('supplier_id'),
                    'uri' => $this->input->post('uri'),
                    'username' => $this->input->post('username'),
                    'password' => $this->input->post('password'),
                    'type' => $this->input->post('type'),
                    'token' => $this->input->post('token'),

                    'cf1' => $this->input->post('cf1'),
                    'cf2' => $this->input->post('cf2'),
                    'cf3' => $this->input->post('cf3'),
                    'cf4' => $this->input->post('cf4'),
                    'cf5' => $this->input->post('cf5'),
                    'cf6' => $this->input->post('cf6'),
                    'cf7' => $this->input->post('cf7'),
                    'cf8' => $this->input->post('cf8'),
                    'cf9' => $this->input->post('cf9'),
                    'cf10' => $this->input->post('cf10')
                ];
                $id = $this->settings_model->updateAPI($API_id, $data);
                if (!$id) {
                    throw new \Exception('Failed');
                    $this->session->set_flashdata('message', lang("failed"));
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("API_added"));
                redirect($_SERVER['HTTP_REFERER']);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_API', $this->data);
        }
    }

    public function view_API($API_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['api'] = $this->settings_model->getAPIByID($API_id);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'settings/view_API', $this->data);
    }

    public function backups()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->data['files'] = glob('./files/backups/*.zip', GLOB_BRACE);
        $this->data['dbs'] = glob('./files/backups/*.txt', GLOB_BRACE);
        krsort($this->data['files']);
        krsort($this->data['dbs']);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('backups')));
        $meta = array('page_title' => lang('backups'), 'bc' => $bc);
        $this->page_construct('settings/backups', $meta, $this->data);
    }

    public function backup_database()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->dbutil();
        $prefs = array(
            'format' => 'txt',
            'filename' => 'sma_db_backup.sql'
        );
        $back = $this->dbutil->backup($prefs);
        $backup = &$back;
        $db_name = 'db-backup-on-' . date("Y-m-d-H-i-s") . '.txt';
        $save = './files/backups/' . $db_name;
        $this->load->helper('file');
        write_file($save, $backup);
        $this->session->set_flashdata('messgae', lang('db_saved'));
        redirect("system_settings/backups");
    }

    public function backup_files()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $name = 'file-backup-' . date("Y-m-d-H-i-s");
        $this->sma->zip("./", './files/backups/', $name);
        $this->session->set_flashdata('messgae', lang('backup_saved'));
        redirect("system_settings/backups");
        exit();
    }

    public function restore_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $file = file_get_contents('./files/backups/' . $dbfile . '.txt');
        $this->db->conn_id->multi_query($file);
        $this->db->conn_id->close();
        redirect('logout/db');
    }

    public function download_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->library('zip');
        $this->zip->read_file('./files/backups/' . $dbfile . '.txt');
        $name = $dbfile . '.zip';
        $this->zip->download($name);
        exit();
    }

    public function download_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->helper('download');
        force_download('./files/backups/' . $zipfile . '.zip', null);
        exit();
    }

    public function restore_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $file = './files/backups/' . $zipfile . '.zip';
        $this->sma->unzip($file, './');
        $this->session->set_flashdata('success', lang('files_restored'));
        redirect("system_settings/backups");
        exit();
    }

    public function delete_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        unlink('./files/backups/' . $dbfile . '.txt');
        $this->session->set_flashdata('messgae', lang('db_deleted'));
        redirect("system_settings/backups");
    }

    public function delete_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        unlink('./files/backups/' . $zipfile . '.zip');
        $this->session->set_flashdata('messgae', lang('backup_deleted'));
        redirect("system_settings/backups");
    }

    public function email_templates($template = "credentials")
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->form_validation->set_rules('mail_body', lang('mail_message'), 'trim|required');
        $this->load->helper('file');
        $temp_path = is_dir('./themes/' . $this->theme . 'email_templates/');
        $theme = $temp_path ? $this->theme : 'default';
        if ($this->form_validation->run() == true) {
            $data = $_POST["mail_body"];
            if (write_file('./themes/' . $this->theme . 'email_templates/' . $template . '.html', $data)) {
                $this->session->set_flashdata('message', lang('message_successfully_saved'));
                redirect('system_settings/email_templates#' . $template);
            } else {
                $this->session->set_flashdata('error', lang('failed_to_save_message'));
                redirect('system_settings/email_templates#' . $template);
            }
        } else {
            $this->data['credentials'] = file_get_contents('./themes/' . $this->theme . 'email_templates/credentials.html');
            $this->data['sale'] = file_get_contents('./themes/' . $this->theme . 'email_templates/sale.html');
            $this->data['quote'] = file_get_contents('./themes/' . $this->theme . 'email_templates/quote.html');
            $this->data['purchase'] = file_get_contents('./themes/' . $this->theme . 'email_templates/purchase.html');
            $this->data['transfer'] = file_get_contents('./themes/' . $this->theme . 'email_templates/transfer.html');
            $this->data['payment'] = file_get_contents('./themes/' . $this->theme . 'email_templates/payment.html');
            $this->data['forgot_password'] = file_get_contents('./themes/' . $this->theme . 'email_templates/forgot_password.html');
            $this->data['activate_email'] = file_get_contents('./themes/' . $this->theme . 'email_templates/activate_email.html');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('email_templates')));
            $meta = array('page_title' => lang('email_templates'), 'bc' => $bc);
            $this->page_construct('settings/email_templates', $meta, $this->data);
        }
    }

    public function create_group()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('group_name', lang('group_name'), 'required|alpha_dash|is_unique[groups.name]');

            if ($this->form_validation->run() == true) {
                $data = array('name' => strtolower($this->input->post('group_name')), 'description' => $this->input->post('description'));
            } elseif ($this->input->post('create_group')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/user_groups");
            }

            if ($this->form_validation->run() == true && ($new_group_id = $this->settings_model->addGroup($data))) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang('group_added'));
                redirect("system_settings/permissions/" . $new_group_id);
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['group_name'] = array(
                    'name' => 'group_name',
                    'id' => 'group_name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('group_name'),
                );
                $this->data['description'] = array(
                    'name' => 'description',
                    'id' => 'description',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('description'),
                );
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/create_group', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_group($id)
    {
        $this->db->trans_begin();
        try {
            if (!$id || empty($id)) {
                throw new \Exception("");
            }
            $group = $this->settings_model->getGroupByID($id);

            $this->form_validation->set_rules('group_name', lang('group_name'), 'required|alpha_dash');

            if ($this->form_validation->run() === true) {
                $data = array('name' => strtolower($this->input->post('group_name')), 'description' => $this->input->post('description'));
                if (!$this->settings_model->updateGroup($id, $data)) {
                    throw new \Exception(lang('attempt_failed'));
                }
                $this->session->set_flashdata('message', lang('group_udpated'));
                $this->db->trans_commit();
                redirect("system_settings/user_groups");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['group'] = $group;

                $this->data['group_name'] = array(
                    'name' => 'group_name',
                    'id' => 'group_name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('group_name', $group->name),
                );
                $this->data['group_description'] = array(
                    'name' => 'group_description',
                    'id' => 'group_description',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('group_description', $group->description),
                );
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_group', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function permissions($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('group', lang("group"), 'is_natural_no_zero');
            if ($this->form_validation->run() == true) {
                $data = array(
                    'products-index' => $this->input->post('products-index'),
                    'products-edit' => $this->input->post('products-edit'),
                    'products-add' => $this->input->post('products-add'),
                    'products-delete' => $this->input->post('products-delete'),
                    'products-cost' => $this->input->post('products-cost'),
                    'products-price' => $this->input->post('products-price'),
                    'customers-index' => $this->input->post('customers-index'),
                    'customers-edit' => $this->input->post('customers-edit'),
                    'customers-add' => $this->input->post('customers-add'),
                    'customers-delete' => $this->input->post('customers-delete'),
                    'suppliers-index' => $this->input->post('suppliers-index'),
                    'suppliers-edit' => $this->input->post('suppliers-edit'),
                    'suppliers-add' => $this->input->post('suppliers-add'),
                    'suppliers-delete' => $this->input->post('suppliers-delete'),
                    'sales-index' => $this->input->post('sales-index'),
                    'sales-edit' => $this->input->post('sales-edit'),
                    'sales-add' => $this->input->post('sales-add'),
                    'sales-delete' => $this->input->post('sales-delete'),
                    'sales-email' => $this->input->post('sales-email'),
                    'sales-pdf' => $this->input->post('sales-pdf'),
                    'sales-deliveries' => $this->input->post('sales-deliveries'),
                    'sales-edit_delivery' => $this->input->post('sales-edit_delivery'),
                    'sales-add_delivery' => $this->input->post('sales-add_delivery'),
                    'sales-delete_delivery' => $this->input->post('sales-delete_delivery'),
                    'sales-email_delivery' => $this->input->post('sales-email_delivery'),
                    'sales-pdf_delivery' => $this->input->post('sales-pdf_delivery'),
                    'sales-gift_cards' => $this->input->post('sales-gift_cards'),
                    'sales-edit_gift_card' => $this->input->post('sales-edit_gift_card'),
                    'sales-add_gift_card' => $this->input->post('sales-add_gift_card'),
                    'sales-delete_gift_card' => $this->input->post('sales-delete_gift_card'),
                    'quotes-index' => $this->input->post('quotes-index'),
                    'quotes-edit' => $this->input->post('quotes-edit'),
                    'quotes-add' => $this->input->post('quotes-add'),
                    'quotes-delete' => $this->input->post('quotes-delete'),
                    'quotes-email' => $this->input->post('quotes-email'),
                    'quotes-pdf' => $this->input->post('quotes-pdf'),
                    'purchases-index' => $this->input->post('purchases-index'),
                    'purchases-edit' => $this->input->post('purchases-edit'),
                    'purchases-add' => $this->input->post('purchases-add'),
                    'purchases-delete' => $this->input->post('purchases-delete'),
                    'purchases-email' => $this->input->post('purchases-email'),
                    'purchases-pdf' => $this->input->post('purchases-pdf'),
                    'transfers-index' => $this->input->post('transfers-index'),
                    'transfers-edit' => $this->input->post('transfers-edit'),
                    'transfers-add' => $this->input->post('transfers-add'),
                    'transfers-delete' => $this->input->post('transfers-delete'),
                    'transfers-email' => $this->input->post('transfers-email'),
                    'transfers-pdf' => $this->input->post('transfers-pdf'),
                    'sales-return_sales' => $this->input->post('sales-return_sales'),
                    'reports-quantity_alerts' => $this->input->post('reports-quantity_alerts'),
                    'reports-expiry_alerts' => $this->input->post('reports-expiry_alerts'),
                    'reports-products' => $this->input->post('reports-products'),
                    'reports-daily_sales' => $this->input->post('reports-daily_sales'),
                    'reports-monthly_sales' => $this->input->post('reports-monthly_sales'),
                    'reports-payments' => $this->input->post('reports-payments'),
                    'reports-sales' => $this->input->post('reports-sales'),
                    'reports-purchases' => $this->input->post('reports-purchases'),
                    'reports-customers' => $this->input->post('reports-customers'),
                    'reports-suppliers' => $this->input->post('reports-suppliers'),
                    'sales-payments' => $this->input->post('sales-payments'),
                    'purchases-payments' => $this->input->post('purchases-payments'),
                    'purchases-expenses' => $this->input->post('purchases-expenses'),
                    'products-adjustments' => $this->input->post('products-adjustments'),
                    'bulk_actions' => $this->input->post('bulk_actions'),
                    'customers-deposits' => $this->input->post('customers-deposits'),
                    'customers-delete_deposit' => $this->input->post('customers-delete_deposit'),
                    'products-barcode' => $this->input->post('products-barcode'),
                    'purchases-return_purchases' => $this->input->post('purchases-return_purchases'),
                    'reports-expenses' => $this->input->post('reports-expenses'),
                    'reports-daily_purchases' => $this->input->post('reports-daily_purchases'),
                    'reports-monthly_purchases' => $this->input->post('reports-monthly_purchases'),
                    'products-stock_count' => $this->input->post('products-stock_count'),
                    'edit_price' => $this->input->post('edit_price'),
                );

                if (POS) {
                    $data['pos-index'] = $this->input->post('pos-index');
                }

                //$this->sma->print_arrays($data);
            }


            if ($this->form_validation->run() == true && $this->settings_model->updatePermissions($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("group_permissions_updated"));
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['id'] = $id;
                $this->data['p'] = $this->settings_model->getGroupPermissions($id);
                $this->data['group'] = $this->settings_model->getGroupByID($id);
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('group_permissions')));
                $meta = array('page_title' => lang('group_permissions'), 'bc' => $bc);
                $this->page_construct('settings/permissions', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();

            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function user_groups()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang("access_denied"));
            redirect('auth');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['groups'] = $this->settings_model->getGroups();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('groups')));
        $meta = array('page_title' => lang('groups'), 'bc' => $bc);
        $this->page_construct('settings/user_groups', $meta, $this->data);
    }

    public function delete_group($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect('welcome', 'refresh');
        }

        if ($this->settings_model->checkGroupUsers($id)) {
            $this->session->set_flashdata('error', lang("group_x_b_deleted"));
            redirect("system_settings/user_groups");
        }

        if ($this->settings_model->deleteGroup($id)) {
            $this->session->set_flashdata('message', lang("group_deleted"));
            redirect("system_settings/user_groups");
        }
    }

    public function currencies()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('currencies')));
        $meta = array('page_title' => lang('currencies'), 'bc' => $bc);
        $this->page_construct('settings/currencies', $meta, $this->data);
    }

    public function getCurrencies()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("id, code, name, rate")
            ->from("currencies")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_currency/$1') . "' class='tip' title='" . lang("edit_currency") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_currency") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_currency/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function add_currency()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->db->trans_begin();

        try {
            $this->form_validation->set_rules('code', lang("currency_code"), 'trim|is_unique[currencies.code]|required');
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('rate', lang("exchange_rate"), 'required|numeric');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'code' => $this->input->post('code'),
                    'name' => $this->input->post('name'),
                    'rate' => $this->input->post('rate'),
                );
            } elseif ($this->input->post('add_currency')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/currencies");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addCurrency($data)) { //check to see if we are creating the customer
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("currency_added"));
                redirect("system_settings/currencies");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['page_title'] = lang("new_currency");
                $this->load->view($this->theme . 'settings/add_currency', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_currency($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('code', lang("currency_code"), 'trim|required');
            $cur_details = $this->settings_model->getCurrencyByID($id);
            if ($this->input->post('code') != $cur_details->code) {
                $this->form_validation->set_rules('code', lang("currency_code"), 'is_unique[currencies.code]');
            }
            $this->form_validation->set_rules('name', lang("currency_name"), 'required');
            $this->form_validation->set_rules('rate', lang("exchange_rate"), 'required|numeric');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'code' => $this->input->post('code'),
                    'name' => $this->input->post('name'),
                    'rate' => $this->input->post('rate'),
                );
            } elseif ($this->input->post('edit_currency')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/currencies");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateCurrency($id, $data)) { //check to see if we are updateing the customer
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("currency_updated"));
                redirect("system_settings/currencies");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['currency'] = $this->settings_model->getCurrencyByID($id);
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_currency', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_currency($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->settings_model->deleteCurrency($id)) {
            echo lang("currency_deleted");
        }
    }

    public function currency_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('currencies'))
                        ->SetCellValue('A1', lang('code'))
                        ->SetCellValue('B1', lang('name'))
                        ->SetCellValue('C1', lang('rate'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCurrencyByID($id);
                        $sheet->SetCellValue('A' . $row, $sc->code)
                            ->SetCellValue('B' . $row, $sc->name)
                            ->SetCellValue('B' . $row, $sc->rate);
                        $row++;
                    }

                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'currencies_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function categories()
    {
        $link_type = ['mb_categories'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('categories')));
        $meta = array('page_title' => lang('categories'), 'bc' => $bc);
        $this->page_construct('settings/categories', $meta, $this->data);
    }

    public function getCategories()
    {
        $print_barcode = anchor('products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>', 'title="' . lang('print_barcodes') . '" class="tip"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('categories')}.id as id, {$this->db->dbprefix('categories')}.image, {$this->db->dbprefix('categories')}.code, {$this->db->dbprefix('categories')}.name, c.name as parent", false)
            ->from("categories")
            ->join("categories c", 'c.id=categories.parent_id', 'left')
            ->where('categories.company_id = 1 or categories.company_id = ' . $this->session->userdata('company_id'))
            ->where('categories.is_deleted', null)
            ->group_by('categories.id')
            ->add_column("Actions", "<div class=\"text-center\">" . $print_barcode . " <a href='" . site_url('system_settings/edit_category/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_category") . "'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_category") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id");

        echo $this->datatables->generate();
    }

    public function add_category()
    {
        $this->sma->checkPermissions(false, true);

        $this->load->helper('security');
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('code', lang("category_code"), 'trim|required');
            $this->form_validation->set_rules('name', lang("name"), 'required|min_length[3]');
            $this->form_validation->set_rules('userfile', lang("category_image"), 'xss_clean');

            if ($this->form_validation->run() == true) {

                //nge cek apakah jumlah Category telah limit
                $isLimited = $this->authorized_model->isCategoryLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_master"));
                    $message = str_replace("yyy", lang("categories"), $message);
                    throw new \Exception($message);

                    // $this->session->set_flashdata('warning', $message);
                    // redirect("system_settings/categories");
                }
                // akhir cek

                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'parent_id' => $this->input->post('parent'),
                    'company_id' => $this->session->userdata('company_id'),
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo = $this->upload->file_name;
                    $data['image'] = $photo;
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->upload_path . $photo;
                    $config['new_image'] = $this->thumbs_path . $photo;
                    $config['maintain_ratio'] = true;
                    $config['width'] = $this->Settings->twidth;
                    $config['height'] = $this->Settings->theight;
                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);
                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }
                    if ($this->Settings->watermark) {
                        $this->image_lib->clear();
                        $wm['source_image'] = $this->upload_path . $photo;
                        $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                        $wm['wm_type'] = 'text';
                        $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                        $wm['quality'] = '100';
                        $wm['wm_font_size'] = '16';
                        $wm['wm_font_color'] = '999999';
                        $wm['wm_shadow_color'] = 'CCCCCC';
                        $wm['wm_vrt_alignment'] = 'top';
                        $wm['wm_hor_alignment'] = 'right';
                        $wm['wm_padding'] = '10';
                        $this->image_lib->initialize($wm);
                        $this->image_lib->watermark();
                    }
                    $this->image_lib->clear();
                    $config = null;*/
                    $uploadedImg    = $this->integration->upload_files($_FILES['userfile']);
                    $data['image']  = $uploadedImg->url;
                }
            } elseif ($this->input->post('add_category')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/categories");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addCategory($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("category_added"));
                redirect("system_settings/categories");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['categories'] = $this->settings_model->getParentCategories();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_category', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_category($id = null)
    {
        $this->db->trans_begin();
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("category_code"), 'trim|required');
        try {
            $pr_details = $this->settings_model->getCategoryByID($id);
            if ($this->input->post('code') != $pr_details->code) {
                $this->form_validation->set_rules('code', lang("category_code"), 'is_unique[categories.code]');
            }
            $this->form_validation->set_rules('name', lang("category_name"), 'required|min_length[3]');
            $this->form_validation->set_rules('userfile', lang("category_image"), 'xss_clean');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'parent_id' => $this->input->post('parent'),
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo = $this->upload->file_name;
                    $data['image'] = $photo;
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->upload_path . $photo;
                    $config['new_image'] = $this->thumbs_path . $photo;
                    $config['maintain_ratio'] = true;
                    $config['width'] = $this->Settings->twidth;
                    $config['height'] = $this->Settings->theight;
                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);
                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }
                    if ($this->Settings->watermark) {
                        $this->image_lib->clear();
                        $wm['source_image'] = $this->upload_path . $photo;
                        $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                        $wm['wm_type'] = 'text';
                        $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                        $wm['quality'] = '100';
                        $wm['wm_font_size'] = '16';
                        $wm['wm_font_color'] = '999999';
                        $wm['wm_shadow_color'] = 'CCCCCC';
                        $wm['wm_vrt_alignment'] = 'top';
                        $wm['wm_hor_alignment'] = 'right';
                        $wm['wm_padding'] = '10';
                        $this->image_lib->initialize($wm);
                        $this->image_lib->watermark();
                    }
                    $this->image_lib->clear();*/
                    $uploadedImg    = $this->integration->upload_files($_FILES['userfile']);
                    $data['image']  = $uploadedImg->url;
                    $config = null;
                }
            } elseif ($this->input->post('edit_category')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/categories");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateCategory($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("category_updated"));
                redirect("system_settings/categories");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['category'] = $this->settings_model->getCategoryByID($id);
                $this->data['categories'] = $this->settings_model->getParentCategories();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_category', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_category($id = null)
    {
        if ($this->site->getSubCategories($id)) {
            $this->session->set_flashdata('error', lang("category_has_subcategory"));
            redirect("system_settings/categories");
        }

        if ($this->settings_model->deleteCategory($id)) {
            echo lang("category_deleted");
        }
    }

    public function category_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCategory($id);
                    }
                    $this->session->set_flashdata('message', lang("categories_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('categories'))
                        ->SetCellValue('A1', lang('code'))
                        ->SetCellValue('B1', lang('name'))
                        ->SetCellValue('C1', lang('image'))
                        ->SetCellValue('D1', lang('parent_actegory'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCategoryByID($id);
                        $parent_actegory = '';
                        if ($sc->parent_id) {
                            $pc = $this->settings_model->getCategoryByID($sc->parent_id);
                            $parent_actegory = $pc->code;
                        }
                        $sheet->SetCellValue('A' . $row, $sc->code)
                            ->SetCellValue('B' . $row, $sc->name)
                            ->SetCellValue('C' . $row, $sc->image)
                            ->SetCellValue('D' . $row, $parent_actegory);
                        $row++;
                    }

                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function tax_rates()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('tax_rates')));
        $meta = array('page_title' => lang('tax_rates'), 'bc' => $bc);
        $this->page_construct('settings/tax_rates', $meta, $this->data);
    }

    public function getTaxRates()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, name, code, rate, type")
            ->from("tax_rates")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_tax_rate/$1') . "' class='tip' title='" . lang("edit_tax_rate") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_tax_rate") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_tax_rate/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function add_tax_rate()
    {
        $this->db->trans_begin();
        $this->form_validation->set_rules('name', lang("name"), 'trim|is_unique[tax_rates.name]|required');
        $this->form_validation->set_rules('type', lang("type"), 'required');
        $this->form_validation->set_rules('rate', lang("tax_rate"), 'required|numeric');
        try {
            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'type' => $this->input->post('type'),
                    'rate' => $this->input->post('rate'),
                );
            } elseif ($this->input->post('add_tax_rate')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/tax_rates");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addTaxRate($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("tax_rate_added"));
                redirect("system_settings/tax_rates");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_tax_rate', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_tax_rate($id = null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("name"), 'trim|required');
            $tax_details = $this->settings_model->getTaxRateByID($id);
            if ($this->input->post('name') != $tax_details->name) {
                $this->form_validation->set_rules('name', lang("name"), 'is_unique[tax_rates.name]');
            }
            $this->form_validation->set_rules('type', lang("type"), 'required');
            $this->form_validation->set_rules('rate', lang("tax_rate"), 'required|numeric');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'type' => $this->input->post('type'),
                    'rate' => $this->input->post('rate'),
                );
            } elseif ($this->input->post('edit_tax_rate')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/tax_rates");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateTaxRate($id, $data)) { //check to see if we are updateing the customer
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("tax_rate_updated"));
                redirect("system_settings/tax_rates");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['tax_rate'] = $this->settings_model->getTaxRateByID($id);

                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_tax_rate', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_tax_rate($id = null)
    {
        if ($this->settings_model->deleteTaxRate($id)) {
            echo lang("tax_rate_deleted");
        }
    }

    public function tax_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTaxRate($id);
                    }
                    $this->session->set_flashdata('message', lang("tax_rates_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('tax_rates'))
                        ->SetCellValue('A1', lang('name'))
                        ->SetCellValue('B1', lang('code'))
                        ->SetCellValue('C1', lang('tax_rate'))
                        ->SetCellValue('D1', lang('type'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->settings_model->getTaxRateByID($id);
                        $sheet->SetCellValue('A' . $row, $tax->name)
                            ->SetCellValue('B' . $row, $tax->code)
                            ->SetCellValue('C' . $row, $tax->rate)
                            ->SetCellValue('D' . $row, ($tax->type == 1) ? lang('percentage') : lang('fixed'));
                        $row++;
                    }
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('C')->setWidth(20);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'tax_rates_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function customer_groups()
    {
        // echo ;die;
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $link_type = ['mb_customer_groups'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('customer_groups')));
        $meta = array('page_title' => lang('customer_groups'), 'bc' => $bc);
        $this->page_construct('settings/customer_groups', $meta, $this->data);
    }

    public function getCustomerGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, name, percent, kredit_limit, company_id")
            ->from("customer_groups")
            ->add_column("Actions", site_url('system_settings/edit_customer_group/$1') . '||' . site_url('system_settings/add_customer_to_customer_group/$1') . '||' . '$2', "id, company_id");
        // "<div class=\"text-center\">
        //     <a href='" . site_url('system_settings/edit_customer_group/$1') . "' class='tip' title='" . lang("edit_customer_group") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
        //         <i class=\"fa fa-edit\"></i>
        //     </a> 
        //     <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_customer_group") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_customer_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> -->
        //     <a href='" . site_url('system_settings/add_customer_to_customer_group/$1') . "' class='tip' title='" . lang("add_customer_to_customer_group") . "'>
        //         <i class=\"fa fa-users\"></i>
        //     </a>
        // </div>"
        //->unset_column('id');

        if (!$this->Owner) {
            $this->datatables->where('customer_groups.company_id = ' . $this->session->userdata('company_id') . ' OR customer_groups.company_id = 1 OR customer_groups.company_id IS NULL');
        }
        echo $this->datatables->generate();
    }

    public function add_customer_to_customer_group($id)
    {
        $this->data['customers'] = $this->companies_model->getCompanyByParent($this->session->userdata('company_id'));
        $this->data['customer_of_customer_group'] = $this->settings_model->getCustomerOfCustomerGroup($id, $this->session->userdata('company_id'));
        $this->data['customer_group'] = $this->settings_model->getCustomerGroupByID($id);
        $this->data['id'] = $id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => base_url('system_settings/customer_groups/'), 'page' => lang('customer_groups')), array('link' => '#', 'page' => lang('edit_cutomer_price_group')));
        $meta = array('page_title' => lang('edit_cutomer_price_group'), 'bc' => $bc);
        $this->page_construct('settings/add_customer_to_customer_group', $meta, $this->data);
    }

    public function getCustomers_cg()
    {
        $add_user = "<a class=\"tip\" title='" . lang("add_user") . "' id='customersAdd' href='" . site_url('customers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-user-plus\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            //            ->select($this->db->dbprefix('companies').".id, {$this->db->dbprefix('companies')}.company, {$this->db->dbprefix('companies')}.name, {$this->db->dbprefix('companies')}.email, {$this->db->dbprefix('companies')}.phone, {$this->db->dbprefix('companies')}.price_group_name, {$this->db->dbprefix('companies')}.customer_group_name, {$this->db->dbprefix('companies')}.vat_no, {$this->db->dbprefix('companies')}.deposit_amount, {$this->db->dbprefix('companies')}.award_points")
            ->select("CONCAT(id, CONCAT('~', company)), company, name, phone, cf1")
            ->from("companies")
            ->where('group_name', 'customer')
            ->where('(customer_group_id IS NULL OR customer_group_id = ' . $this->input->get('id_cg') . ')');
        if (!$this->Owner) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }

        if ($this->input->get('provinsi') != '') {
            $this->datatables->where('country', $this->input->get('provinsi'));
        }

        if ($this->input->get('provinsi') != '') {
            $this->datatables->where('country', $this->input->get('provinsi'));
        }
        // $this->db->get();
        // echo $this->db->last_query();die;
        $this->datatables->where('is_deleted', null);
        echo $this->datatables->generate();
    }

    public function save_customer_to_customer_group($id_cg)
    {
        $this->db->trans_begin();
        try {
            $this->settings_model->updateAllCustomerByCustomerGroupId($id_cg);
            foreach ($this->input->post("list_toko") as $key => $value) {
                $data = array();
                $data['customer_group_id'] = $id_cg;
                $data['customer_group_name'] = $this->input->post('cg_name');
                if (!$this->companies_model->updateCompany($value, $data)) {
                    throw new \Exception('update failed');
                }
            }
            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang("customer_added"));
            redirect(base_url('system_settings/customer_groups/'));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function add_customer_group()
    {
        $this->form_validation->set_rules('name', lang("group_name"), 'trim|required');
        $this->form_validation->set_rules('percent', lang("group_percentage"), 'required|numeric');
        $this->form_validation->set_rules('kredit_limit', "Kredit Limit", 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'percent' => $this->input->post('percent'),
                'company_id' => $this->session->userdata('company_id'),
                'kredit_limit' => $this->input->post('kredit_limit'),
            );
        } elseif ($this->input->post('add_customer_group')) {
            throw new \Exception(validation_errors());
            // $this->session->set_flashdata('error', validation_errors());
            // redirect("system_settings/customer_groups");
        }

        if ($this->form_validation->run() == true) {
            try {
                if ($this->settings_model->checkCustomerGroupByName($this->session->userdata('company_id'), $this->input->post('name')) > 0) {
                    throw new Exception(lang("group_name"));
                } else {
                    $this->settings_model->addCustomerGroup($data);
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("customer_group_added"));
                }
                redirect("system_settings/customer_groups");
            } catch (\Throwable $th) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('error', $th->getMessage());
                redirect("system_settings/customer_groups");
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_customer_group', $this->data);
        }
    }

    public function edit_customer_group($id = null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("group_name"), 'trim|required');
            $pg_details = $this->settings_model->getCustomerGroupByID($id);
            if ($this->input->post('name') != $pg_details->name) {
                $this->form_validation->set_rules('name', lang("group_name"), 'is_unique[tax_rates.name]');
            }
            $this->form_validation->set_rules('percent', lang("group_percentage"), 'required|numeric');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    'percent' => $this->input->post('percent'),
                    'kredit_limit' => $this->input->post('kredit_limit'),
                );
            } elseif ($this->input->post('edit_customer_group')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/customer_groups");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateCustomerGroup($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("customer_group_updated"));
                redirect("system_settings/customer_groups");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['customer_group'] = $this->settings_model->getCustomerGroupByID($id);

                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_customer_group', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_customer_group($id = null)
    {
        if ($this->settings_model->deleteCustomerGroup($id)) {
            echo lang("customer_group_deleted");
        }
    }

    public function customer_group_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCustomerGroup($id);
                    }
                    $this->session->set_flashdata('message', lang("customer_groups_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('tax_rates'))
                        ->SetCellValue('A1', lang('group_name'))
                        ->SetCellValue('B1', lang('group_percentage'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pg = $this->settings_model->getCustomerGroupByID($id);
                        $sheet->SetCellValue('A' . $row, $pg->name)
                            ->SetCellValue('B' . $row, $pg->percent);
                        $row++;
                    }
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'customer_groups_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_customer_group_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function warehouses()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $link_type = ['mb_warehouses'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('warehouses')));
        $meta = array('page_title' => lang('warehouses'), 'bc' => $bc);
        if ($this->Principal) {
            $this->page_construct('settings/warehouse_principal', $meta, $this->data);
        } else {
            $this->page_construct('settings/warehouses', $meta, $this->data);
        }
    }

    public function getWarehouses()
    {
        $this->load->library('datatables');
        $this->datatables
            ->from("warehouses")
            ->join('price_groups', 'price_groups.id=warehouses.price_group_id', 'left');
        if (!$this->Owner && !$this->Principal) {
            $this->datatables->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        if ($this->Principal) {
            $this->datatables->select("{$this->db->dbprefix('warehouses')}.id as id, map, code, {$this->db->dbprefix('warehouses')}.name as name, {$this->db->dbprefix('price_groups')}.name as price_group, {$this->db->dbprefix('warehouses')}.phone, {$this->db->dbprefix('warehouses')}.email, {$this->db->dbprefix('warehouses')}.address, {$this->db->dbprefix('companies')}.cf1 as distributor_code, {$this->db->dbprefix('companies')}.company as distributor_name,{$this->db->dbprefix('warehouses')}.is_deleted as is_deleted")
                ->join('companies', "{$this->db->dbprefix('companies')}.id = {$this->db->dbprefix('warehouses')}.company_id")
                ->join('users', "{$this->db->dbprefix('companies')}.id = {$this->db->dbprefix('users')}.company_id")
                ->where("{$this->db->dbprefix('users')}.active", "1")
                ->where("{$this->db->dbprefix('users')}.group_id", "2")
                ->where("{$this->db->dbprefix('companies')}.group_name", "biller")
                ->where("({$this->db->dbprefix('companies')}.client_id is null OR {$this->db->dbprefix('companies')}.client_id != 'aksestoko')");
            $this->datatables->add_column("Actions", "$2" . "|" . site_url('system_settings/edit_warehouse/$1') . "|" . site_url('system_settings/delete_warehouse/$1') . "|" . site_url('system_settings/recover_warehouse/$1'), "id, is_deleted");
        } else {
            $this->datatables->select("{$this->db->dbprefix('warehouses')}.id as id, map, code, {$this->db->dbprefix('warehouses')}.name as name, {$this->db->dbprefix('price_groups')}.name as price_group, phone, email, address")
                ->where("({$this->db->dbprefix('warehouses')}.is_deleted != 1 OR {$this->db->dbprefix('warehouses')}.is_deleted IS NULL )");
            $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_warehouse/$1') . "' class='tip' title='" . lang("edit_warehouse") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i>  <a href='" . site_url('system_settings/edit_warehouse_customer/$1') . "' class='tip' title='" . lang("add_customer_to_warehouse") . "'><i class=\"fa fa-users\"></i></a>  </a> <a href='#' class='tip po' title='<b>" . lang("delete_warehouse") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_warehouse/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o hidden\"></i></a></div>", "id");
        }
        echo $this->datatables->generate();
    }

    public function getWarehousesJson()
    {
        $this->sma->send_json($this->site->getAllWarehouses());
    }

    public function add_warehouse()
    {
        $this->db->trans_begin();
        try {
            if (!$this->Principal && !$this->Owner) {
                if (!$this->sma->CreatedPermissions('warehouses') || (!$this->Admin && !$this->LT)) {
                    $this->session->set_flashdata('error', lang('access_denied'));
                    $this->sma->md();
                }
            }

            $this->load->helper('security');
            // $this->sma->CreatedPermissions('warehouses');
            $this->form_validation->set_rules('code', lang("code"), 'trim|is_unique[warehouses.code]|required');
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('address', lang("address"), 'required');
            $this->form_validation->set_rules('userfile', lang("map_image"), 'xss_clean');

            if ($this->form_validation->run() == true) {
                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');

                    $config['upload_path'] = 'assets/uploads/';
                    $config['allowed_types'] = 'gif|jpg|png|jpeg';
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = '2000';
                    $config['max_height'] = '2000';
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('message', $error);
                        // redirect("system_settings/warehouses");
                    }

                    $map = $this->upload->file_name;

                    $this->load->helper('file');
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = 'assets/uploads/' . $map;
                    $config['new_image'] = 'assets/uploads/thumbs/' . $map;
                    $config['maintain_ratio'] = true;
                    $config['width'] = 76;
                    $config['height'] = 76;

                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);

                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }*/
                    $uploadedImg = $this->integration->upload_files($_FILES['userfile']);
                    $map = $uploadedImg->url;
                } else {
                    $map = null;
                }
                $data = array(
                    'code' => $this->input->post('code'),
                    'name' => $this->input->post('name'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                    'address' => $this->input->post('address'),
                    'company_id' => $this->input->post("distributor") ?? $this->session->userdata('company_id'),
                    'price_group_id' => $this->input->post('price_group'),
                    'map' => $map,
                    'shipment_price_group_id' => $this->input->post('shipment_price_group')
                );
            } elseif ($this->input->post('add_warehouse')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/warehouses");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addWarehouse($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("warehouse_added"));
                redirect("system_settings/warehouses");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
                $this->data['shipment_group_prices'] = $this->settings_model->getShipmentGroupPriceByCompanyId($this->session->userdata('company_id'));
                if ($this->Principal) {
                    $this->data['distributors'] = $this->site->getAllDistributor();
                }
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_warehouse', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function getPriceGroup($company_id)
    {
        $data = $this->settings_model->getPriceGroupsByCompanyId($company_id);
        echo json_encode($data);
    }

    public function getShipmentGroupPriceByCompanyId($company_id)
    {
        $data = $this->settings_model->getShipmentGroupPriceByCompanyId($company_id);
        echo json_encode($data);
    }

    public function edit_warehouse($id = null)
    {
        $this->db->trans_begin();
        try {
            $this->load->helper('security');
            // $this->form_validation->set_rules('code', lang("code"), 'trim|required');
            // $wh_details = $this->settings_model->getWarehouseByID($id);
            // if ($this->input->post('code') != $wh_details->code) {
            //     $this->form_validation->set_rules('code', lang("code"), 'is_unique[warehouses.code]');
            // }
            $this->form_validation->set_rules('address', lang("address"), 'required');
            $this->form_validation->set_rules('map', lang("map_image"), 'xss_clean');

            if ($this->form_validation->run() == true) {
                $data = [
                    'name'                    => $this->input->post('name'),
                    'phone'                   => $this->input->post('phone'),
                    'email'                   => $this->input->post('email'),
                    'address'                 => $this->input->post('address'),
                    'price_group_id'          => $this->input->post('price_group'),
                    'shipment_price_group_id' => $this->input->post('shipment_price_group')
                ];

                if ($this->sma->UpdateAutorizedPermissions('warehouses') || $this->input->post('status') == '0') {
                    $data['active'] = $this->input->post('status') == '' ? null : $this->input->post('status');
                }

                if ($this->Principal) {
                    $data['company_id'] = $this->input->post('distrbutor');
                    $data['code'] = $this->input->post('code');
                }

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');

                    $config['upload_path'] = 'assets/uploads/';
                    $config['allowed_types'] = 'gif|jpg|png|jpeg';
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = '2000';
                    $config['max_height'] = '2000';
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('message', $error);
                        // redirect("system_settings/warehouses");
                    }

                    $data['map'] = $this->upload->file_name;

                    $this->load->helper('file');
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = 'assets/uploads/' . $data['map'];
                    $config['new_image'] = 'assets/uploads/thumbs/' . $data['map'];
                    $config['maintain_ratio'] = true;
                    $config['width'] = 76;
                    $config['height'] = 76;

                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);

                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }*/
                    $uploadedImg = $this->integration->upload_files($_FILES['userfile']);
                    $data['map'] = $uploadedImg->url;
                }
            } elseif ($this->input->post('edit_warehouse')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/warehouses");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateWarehouse($id, $data)) { //check to see if we are updateing the customer
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("warehouse_updated"));
                redirect("system_settings/warehouses");
            } else {
                $this->data['error']                 = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['shipment_group_prices'] = $this->settings_model->getShipmentGroupPriceByCompanyId($this->session->userdata('company_id'));
                $this->data['warehouse']             = $this->settings_model->getWarehouseByID($id);
                $this->data['price_groups']          = $this->settings_model->getAllPriceGroups();
                $this->data['id']                    = $id;
                if ($this->Principal) {
                    $this->data['distributors'] = $this->site->getAllDistributor();
                }

                $this->data['modal_js']              = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_warehouse', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_warehouse($id = null)
    {
        if ($this->settings_model->deleteWarehouse($id)) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    public function edit_warehouse_customer($warehouse_id = null)
    {
        $this->data['warehouse'] = $this->settings_model->getWarehouseByID($warehouse_id);
        $this->data['all_customer'] = $this->settings_model->getCustomerByBiller();
        $this->data['warehouse_customer'] = $this->settings_model->getWarehouseCustomer($warehouse_id, true);
        $this->data['warehouse_default'] = $this->site->getWarehouseDefault($this->session->userdata('company_id'));
        if (!$this->data['warehouse_customer']) $this->data['warehouse_customer'] = [];
        $list_wh_cust = [];
        if (count($this->data['warehouse_customer']) > 0) {
            foreach ($this->data['all_customer'] as $i => $all_cust) {
                $indexes = array_keys(array_column($this->data['warehouse_customer'], 'customer_id'), $all_cust->id);
                foreach ($indexes as $index) {
                    $value1 = $this->data['warehouse_customer'][$index];
                    $default_warehouse = lang('unassigned');
                    $checked = 'checked';
                    $readonly = 'onclick="return false;"';
                    if ($value1->default > 0) {
                        $indexes2 = array_keys(array_column($this->data['warehouse_default'], 'customer_id'), $all_cust->id);
                        foreach ($indexes2 as $index2) {
                            $value2 = $this->data['warehouse_default'][$index2];
                            $default_warehouse = $value2->warehouse_name;
                            $checked = '';
                            if ($value2->warehouse_id == $warehouse_id) {
                                $checked = 'checked';
                            } else {
                                $readonly = '';
                            }
                        }
                    }
                    $list_wh_cust[] = [
                        'all_cust_id' => $all_cust->id,
                        'all_cust_company' => $all_cust->company,
                        'checked' => $checked,
                        'readonly' => $readonly,
                        'all_cust_custom_id' => $all_cust->custom_id,
                        'default_warehouse' => $default_warehouse,
                        'value1_default' => $value1->default
                    ];
                }
            }
        }
        $this->data['list_wh_cust'] = $list_wh_cust;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => base_url('system_settings/warehouses/'), 'page' => lang('warehouses')), array('link' => '#', 'page' => lang('add_customer_to_warehouse')));
        $meta = array('page_title' => lang('add_customer_to_warehouse'), 'bc' => $bc);
        $this->page_construct('settings/edit_warehouse_customer', $meta, $this->data);
    }

    public function get_warehouse_customer()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("CONCAT(id, CONCAT('~', company)), company, name, country, city, state, cf1")
            ->from("companies")
            ->where('group_name', 'customer');

        if (!$this->Owner) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }
        if ($this->input->get('provinsi') != '') {
            $this->datatables->where('country', $this->input->get('provinsi'));
        }
        if ($this->input->get('kabupaten') != '') {
            $this->datatables->where('city', $this->input->get('kabupaten'));
        }
        $this->datatables->where('is_deleted', null);
        echo $this->datatables->generate();
    }

    public function save_warehouse_customer($warehouse_id)
    {
        $this->db->trans_begin();
        try {
            foreach ($this->settings_model->getWarehouseCustomer($warehouse_id) as $WarehouseCustomer) {
                $check_customer[$WarehouseCustomer->customer_id] = $WarehouseCustomer->customer_id;
            }

            $default_warehouse_list = [];
            foreach ($this->input->post("customer_list") as $value => $id) {
                $data = array();
                $data['updated_by'] = $this->session->userdata('company_id');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $default_warehouse = 'warehouse_default_' . $id;
                if (!$this->input->post($default_warehouse) == '') {
                    $default_warehouse_list[] = $id;
                }
                if ($this->settings_model->getWarehouseCustomer($warehouse_id, false, $id)) {
                    $data['is_deleted'] = 0;
                    unset($check_customer[$id]);
                    if (!$this->settings_model->updateWarehouseCustomer($id, $warehouse_id, $data)) {
                        throw new \Exception('update failed');
                    }
                } else {
                    $data['customer_id'] = $id;
                    $data['customer_name'] = $this->input->post("customer_name")[$value];
                    $data['warehouse_id'] = $warehouse_id;
                    $data['default'] = $this->input->post("customer_default")[$value];
                    $data['created_by'] = $this->session->userdata('company_id');
                    $data['created_at'] = date('Y-m-d H:i:s');
                    if (!$this->settings_model->addWarehouseCustomer($data)) {
                        throw new \Exception('insert failed');
                    }
                }
            }
            foreach ($default_warehouse_list as $iid) {
                foreach ($this->settings_model->getWarehouseCustomerByCustomer($iid) as $Customer) {
                    $Cdata['default'] = $warehouse_id;
                    if (!$this->settings_model->updateWarehouseCustomer($Customer->customer_id, $Customer->warehouse_id, $Cdata)) {
                        throw new \Exception('update failed');
                    }
                }
            }

            if (sizeOf($check_customer) > 0) {
                foreach ($check_customer as $deleted => $id) {
                    $data = array();
                    $data['is_deleted'] = 1;
                    $data['updated_by'] = $this->session->userdata('company_id');
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    if (!$this->settings_model->updateWarehouseCustomer($id, $warehouse_id, $data)) {
                        throw new \Exception('delete failed');
                    }
                }
            }

            # cek default warehouse dan set default warehouse yang valid
            foreach ($this->settings_model->getWarehouseCustomer($warehouse_id) as $WarehouseCustomer) {
                $check_customer_default[] = $WarehouseCustomer->customer_id;
            }
            foreach ($check_customer_default as $iid) {
                $getDefault = 0;
                foreach ($this->settings_model->getWarehouseCustomerByCustomer($iid) as $Customer) {
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
                        foreach ($this->settings_model->getWarehouseCustomerByCustomer($iid) as $Customer) {
                            $Cdata = array();
                            $Cdata['default'] = $validWarehouse[0];
                            if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $iid, $Cdata)) {
                                throw new \Exception('update failed');
                            }
                        }
                    }
                } else {
                    foreach ($this->settings_model->getWarehouseCustomerByCustomer($iid) as $Customer) {
                        $Cdata = array();
                        $Cdata['default'] = 0;
                        if (!$this->site->updateWarehouseCustomer($Customer->warehouse_id, $iid, $Cdata)) {
                            throw new \Exception('update failed');
                        }
                    }
                }
            }

            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang("warehouse_customer_saved"));
            redirect(base_url('system_settings/warehouses/'));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function recover_warehouse($id = null)
    {
        $data['is_deleted'] = 0;
        if ($this->settings_model->updateWarehouse($id, $data)) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    public function warehouse_actions()
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', '4096M');

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->input->post('form_action') == 'export_excel_all') {
                $writer = \Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
                $writer->setShouldCreateNewSheetsAutomatically(true);

                $filename = 'warehouses_' . date('Y_m_d_H_i_s');
                $writer->openToBrowser($filename . '.xlsx');

                $header = [
                    lang('id'),
                    lang('code'),
                    lang('name'),
                    lang('address'),
                    lang('city'),
                    lang('is_deleted'),
                    lang('disbutor_code'),
                    lang('distibutor_name')
                ];

                $write_header = WriterEntityFactory::createRowFromArray($header);
                $writer->addRow($write_header);

                $load_data = $this->site->getWarehousesAndCompany();
                foreach ($load_data as $wh) {

                    $my_data = [
                        $wh->id,
                        $wh->code,
                        $wh->name,
                        $wh->address,
                        $wh->city,
                        $wh->is_deleted,
                        $wh->cf1,
                        $wh->company
                    ];

                    $write_data = WriterEntityFactory::createRowFromArray($my_data);
                    $writer->addRow($write_data);
                }

                $writer->close();
            }

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteWarehouse($id);
                    }
                    $this->session->set_flashdata('message', lang("warehouses_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'recover') {
                    foreach ($_POST['val'] as $id) {
                        $data['is_deleted'] = 0;
                        $this->settings_model->updateWarehouse($id, $data);
                    }
                    $this->session->set_flashdata('message', lang("warehouses_recovered"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('warehouses'))
                        ->SetCellValue('A1', lang('id'))
                        ->SetCellValue('B1', lang('code'))
                        ->SetCellValue('C1', lang('name'))
                        ->SetCellValue('D1', lang('address'))
                        ->SetCellValue('E1', lang('city'))
                        ->SetCellValue('F1', lang('is_deleted'))
                        ->SetCellValue('G1', lang('disbutor_code'))
                        ->SetCellValue('H1', lang('distibutor_name'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $wh = $this->settings_model->getWarehouseAndCompanyByWarehouseID($id);
                        $sheet->SetCellValue('A' . $row, $wh->id)
                            ->SetCellValue('B' . $row, $wh->code)
                            ->SetCellValue('C' . $row, $wh->name)
                            ->SetCellValue('D' . $row, $wh->address)
                            ->SetCellValue('E' . $row, $wh->city)
                            ->SetCellValue('F' . $row, $wh->is_deleted)
                            ->SetCellValue('G' . $row, $wh->cf1)
                            ->SetCellValue('H' . $row, $wh->company);
                        $row++;
                    }
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getColumnDimension('C')->setWidth(25);
                    $sheet->getColumnDimension('D')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'warehouses_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_warehouse_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function update_warehouse_by_excel()
    {

        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'xlsx';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/warehouses");
                }

                $csv = $config['upload_path'] . $this->upload->file_name;
                $file_name = $csv;

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                if ($reader) {
                    $reader->setReadDataOnly(true);
                    $spreadsheet    = $reader->load($csv);
                    $sheetData      = $spreadsheet->getActiveSheet()->toArray();
                    $arrResult      = array();
                    foreach ($sheetData as $k => $row) {
                        if ($k > 0) {
                            $arrResult[] = $row;
                        }
                    }
                }
                $keys = array(lang('id'), lang('code'), lang('name'), lang('address'), lang('city'), lang('is_deleted'), lang('disbutor_code'), lang('distibutor_name'));
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $warehouse = $this->settings_model->getWarehouseByCode($value[1]);
                    if ($warehouse && $warehouse->id != $value[0]) {
                        $this->session->set_flashdata('error', 'Duplicate warehouse code ' . $value[1]);
                        redirect('system_settings/warehouses');
                    }
                    $final[] = array_combine($keys, $value);
                }
                $data = [];
                $row = 0;
                foreach ($final as $csv_ct) {
                    $data[$row]['id'] = $csv_ct[lang('id')];
                    $data[$row]['code'] = $csv_ct[lang('code')];
                    $data[$row]['name'] = $csv_ct[lang('name')];
                    $data[$row]['address'] = $csv_ct[lang('address')];
                    $data[$row]['is_deleted'] = $csv_ct[lang('is_deleted')];
                    $row++;
                }
            }
        }
        // var_dump($this->form_validation->run());
        // var_dump($this->settings_model->updateWareHouseBatch($data));
        // die;
        if ($this->form_validation->run() == true && $this->settings_model->updateWareHouseBatch($data)) {
            // unlink($config['upload_path'] . $file_name);
            unlink($csv);
            $this->session->set_flashdata('message', lang("warehouses_added"));
            redirect('system_settings/warehouses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/update_warehouse_by_excel', $this->data);
        }
    }

    public function import_warehouse()
    {

        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/warehouses");
                }
                $csv = $this->upload->file_name;
                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");


                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('Warehouse Code', 'Warehouse Name', 'Phone', 'Email', 'Address', 'ID Distributor');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                $data = [];
                $row = 0;
                foreach ($final as $csv_ct) {
                    $data[$row]['code'] = $csv_ct['Warehouse Code'];
                    $data[$row]['name'] = $csv_ct['Warehouse Name'];
                    $data[$row]['phone'] = $csv_ct['Phone'];
                    $data[$row]['email'] = $csv_ct['Email'];
                    $data[$row]['address'] = $csv_ct['Address'];
                    $data[$row]['company_id'] = $csv_ct['ID Distributor'];
                    $row++;
                }
            }

            // $this->sma->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addWarehouses($data)) {
            $this->session->set_flashdata('message', lang("warehouses_added"));
            redirect('system_settings/warehouses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_warehouse', $this->data);
        }
    }

    public function variants()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('variants')));
        $meta = array('page_title' => lang('variants'), 'bc' => $bc);
        $this->page_construct('settings/variants', $meta, $this->data);
    }

    public function getVariants()
    {
        $this->load->library('datatables');
        $this->datatables->select("id, name");
        $this->datatables->from("variants");
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_variant/$1') . "' class='tip' title='" . lang("edit_variant") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_variant") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_variant/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --></div>", "id");

        $this->datatables->where("({$this->db->dbprefix('variants')}.is_deleted != 1 or {$this->db->dbprefix('variants')}.is_deleted IS NULL)");
        if (!$this->Owner) {
            $this->datatables->where("{$this->db->dbprefix('variants')}.company_id", $this->session->userdata('company_id'));
        }

        echo $this->datatables->generate();
    }

    public function add_variant()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("name"), 'trim|is_unique[variants.name]|required');

            if ($this->form_validation->run() == true) {
                $data = [
                    'name'          => $this->input->post('name'),
                    'company_id'    => $this->session->userdata('company_id')
                ];
            } elseif ($this->input->post('add_variant')) {
                throw new \Exception(validation_errors());
                //$this->session->set_flashdata('error', validation_errors());
                //redirect("system_settings/variants");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addVariant($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("variant_added"));
                redirect("system_settings/variants");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_variant', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_variant($id = null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("name"), 'trim|required');
            $tax_details = $this->settings_model->getVariantByID($id);
            if ($this->input->post('name') != $tax_details->name) {
                $this->form_validation->set_rules('name', lang("name"), 'is_unique[variants.name]');
            }

            if ($this->form_validation->run() == true) {
                $data = array('name' => $this->input->post('name'));
            } elseif ($this->input->post('edit_variant')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/variants");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateVariant($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("variant_updated"));
                redirect("system_settings/variants");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['variant'] = $tax_details;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_variant', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_variant($id = null)
    {
        if ($this->settings_model->deleteVariant($id)) {
            echo lang("variant_deleted");
        }
    }

    public function expense_categories()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('expense_categories')));
        $meta = array('page_title' => lang('categories'), 'bc' => $bc);
        $this->page_construct('settings/expense_categories', $meta, $this->data);
    }

    public function getExpenseCategories()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, code, name")
            ->from("expense_categories")
            ->where("company_id", $this->session->userdata('company_id'))
            ->where("is_deleted IS NULL")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_expense_category/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_expense_category") . "'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_expense_category") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_expense_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id");

        echo $this->datatables->generate();
    }

    public function add_expense_category()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('code', lang("category_code"), 'trim|is_unique[categories.code]|required');
            $this->form_validation->set_rules('name', lang("name"), 'required|min_length[3]');

            if ($this->form_validation->run() == true) {

                //nge cek apakah jumlah Expense Category telah limit
                $isLimited = $this->authorized_model->isExpenseCategoryLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_master"));
                    $message = str_replace("yyy", lang("expense_categories"), $message);

                    throw new \Exception($message);
                    // $this->session->set_flashdata('warning', $message);
                    // redirect("system_settings/expense_categories");
                }
                // akhir cek

                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'client_id' => $this->session->userdata('company_id'),
                    'company_id' => $this->session->userdata('company_id'),
                );
            } elseif ($this->input->post('add_expense_category')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/expense_categories");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategory($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("expense_category_added"));
                redirect("system_settings/expense_categories");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_expense_category', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_expense_category($id = null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('code', lang("category_code"), 'trim|required');
            $category = $this->settings_model->getExpenseCategoryByID($id);
            if ($this->input->post('code') != $category->code) {
                $this->form_validation->set_rules('code', lang("category_code"), 'is_unique[expense_categories.code]');
            }
            $this->form_validation->set_rules('name', lang("category_name"), 'required|min_length[3]');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'code' => $this->input->post('code'),
                    'name' => $this->input->post('name'),
                );
            } elseif ($this->input->post('edit_expense_category')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/expense_categories");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateExpenseCategory($id, $data, $photo)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("expense_category_updated"));
                redirect("system_settings/expense_categories");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['category'] = $category;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_expense_category', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_expense_category($id = null)
    {
        if ($this->settings_model->hasExpenseCategoryRecord($id)) {
            $this->session->set_flashdata('error', lang("category_has_expenses"));
            redirect("system_settings/expense_categories", 'refresh');
        }

        if ($this->settings_model->deleteExpenseCategory($id)) {
            echo lang("expense_category_deleted");
        }
    }

    public function expense_category_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCategory($id);
                    }
                    $this->session->set_flashdata('message', lang("categories_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('categories'))
                        ->SetCellValue('A1', lang('code'))
                        ->SetCellValue('B1', lang('name'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getExpenseCategoryByID($id);
                        $sheet->SetCellValue('A' . $row, $sc->code)
                            ->SetCellValue('B' . $row, $sc->name);
                        $row++;
                    }

                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function import_categories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');


        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/categories");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");

                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('code', 'name', 'image', 'pcode');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getCategoryByCode(trim($csv_ct['code']))) {
                        $pcat = null;
                        $pcode = trim($csv_ct['pcode']);
                        if (!empty($pcode)) {
                            if ($pcategory = $this->settings_model->getCategoryByCode(trim($csv_ct['pcode']))) {
                                $data[] = array(
                                    'code' => trim($csv_ct['code']),
                                    'name' => trim($csv_ct['name']),
                                    'image' => trim($csv_ct['image']),
                                    'parent_id' => $pcategory->id,
                                    'company_id' => $this->session->userdata('company_id')
                                );
                            }
                        } else {
                            $data[] = array(
                                'code' => trim($csv_ct['code']),
                                'name' => trim($csv_ct['name']),
                                'image' => trim($csv_ct['image']),
                                'company_id' => $this->session->userdata('company_id')
                            );
                        }
                    }
                }
            }
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategories($data)) {
            $this->session->set_flashdata('message', lang("categories_added"));
            redirect('system_settings/categories');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_categories', $this->data);
        }
    }

    public function import_subcategories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/categories");
                }
                $csv = $this->upload->file_name;
                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");


                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('code', 'name', 'category_code', 'image');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                $rw = 2;
                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getSubcategoryByCode(trim($csv_ct['code']))) {
                        if ($parent_actegory = $this->settings_model->getCategoryByCode(trim($csv_ct['category_code']))) {
                            $data[] = array(
                                'code' => trim($csv_ct['code']),
                                'name' => trim($csv_ct['name']),
                                'image' => trim($csv_ct['image']),
                                'category_id' => $parent_actegory->id,
                                'company_id' => $this->session->userdata('company_id')
                            );
                        } else {
                            $this->session->set_flashdata('error', lang("check_category_code") . " (" . $csv_ct['category_code'] . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                            redirect("system_settings/categories");
                        }
                    }
                    $rw++;
                }
            }

            // $this->sma->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSubCategories($data)) {
            $this->session->set_flashdata('message', lang("subcategories_added"));
            redirect('system_settings/categories');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_subcategories', $this->data);
        }
    }

    public function import_expense_categories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/expense_categories");
                }
                $csv = $this->upload->file_name;
                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");

                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('code', 'name');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getExpenseCategoryByCode(trim($csv_ct['code']))) {
                        $data[] = array(
                            'code' => trim($csv_ct['code']),
                            'name' => trim($csv_ct['name']),
                        );
                    }
                }
            }

            // $this->sma->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategories($data)) {
            $this->session->set_flashdata('message', lang("categories_added"));
            redirect('system_settings/expense_categories');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_expense_categories', $this->data);
        }
    }

    public function units()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('units')));
        $meta = array('page_title' => lang('units'), 'bc' => $bc);
        $this->page_construct('settings/units', $meta, $this->data);
    }

    public function getUnits()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('units')}.id as id, {$this->db->dbprefix('units')}.code, {$this->db->dbprefix('units')}.name, b.name as base_unit, {$this->db->dbprefix('units')}.operator, {$this->db->dbprefix('units')}.operation_value", false)
            ->from("units")
            ->join("units b", 'b.id=units.base_unit', 'left');

        if (!$this->Owner) {
            $this->datatables->where("(units.client_id=1 OR units.client_id={$this->session->userdata('company_id')})");
        }

        $this->datatables->group_by('units.id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_unit/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_unit") . "'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_unit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_unit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id");

        echo $this->datatables->generate();
    }

    public function add_unit()
    {
        $this->db->trans_begin();
        try {
            // $this->form_validation->set_rules('code', lang("unit_code"), 'trim|is_unique[units.code]|required');
            $this->form_validation->set_rules('name', lang("unit_name"), 'trim|required');
            if ($this->input->post('base_unit')) {
                $this->form_validation->set_rules('operator', lang("operator"), 'required');
                $this->form_validation->set_rules('operation_value', lang("operation_value"), 'trim|required');
            }

            if ($this->form_validation->run() == true) {

                //nge cek apakah jumlah Unit telah limit
                $isLimited = $this->authorized_model->isUnitLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_master"));
                    $message = str_replace("yyy", lang("units"), $message);
                    throw new \Exception($message);
                    // $this->session->set_flashdata('warning', $message);
                    // redirect("system_settings/units");
                }
                // akhir cek
                if (!$this->settings_model->getUnitByCompanyIdAndCode($this->session->userdata('company_id'), $this->input->post('code'))) {
                    throw new \Exception(lang("unit_code"));
                    // $this->session->set_flashdata('warning', lang("unit_code"));
                    // redirect("system_settings/units");
                }
                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'base_unit' => $this->input->post('base_unit') ? $this->input->post('base_unit') : null,
                    'operator' => $this->input->post('base_unit') ? $this->input->post('operator') : null,
                    'operation_value' => $this->input->post('operation_value') ? $this->input->post('operation_value') : null,
                    'client_id' => $this->session->userdata('company_id'),
                );
                // var_dump();die;
            } elseif ($this->input->post('add_unit')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/units");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addUnit($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("unit_added"));
                redirect("system_settings/units");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['base_units'] = $this->site->getAllBaseUnits();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_unit', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_unit($id = null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('code', lang("code"), 'trim|required');
            $unit_details = $this->site->getUnitByID($id);
            if ($this->input->post('code') != $unit_details->code) {
                $this->form_validation->set_rules('code', lang("code"), 'is_unique[units.code]');
            }
            $this->form_validation->set_rules('name', lang("name"), 'trim|required');
            if ($this->input->post('base_unit')) {
                $this->form_validation->set_rules('operator', lang("operator"), 'required');
                $this->form_validation->set_rules('operation_value', lang("operation_value"), 'trim|required');
            }

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'base_unit' => $this->input->post('base_unit') ? $this->input->post('base_unit') : null,
                    'operator' => $this->input->post('base_unit') ? $this->input->post('operator') : null,
                    'operation_value' => $this->input->post('operation_value') ? $this->input->post('operation_value') : null,
                );
            } elseif ($this->input->post('edit_unit')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/units");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateUnit($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("unit_updated"));
                redirect("system_settings/units");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['unit'] = $unit_details;
                $this->data['base_units'] = $this->site->getAllBaseUnits();
                $this->load->view($this->theme . 'settings/edit_unit', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_unit($id = null)
    {
        if ($this->site->getUnitsByBUID($id)) {
            $this->session->set_flashdata('error', lang("unit_has_subunit"));
            redirect("system_settings/units");
        }

        if ($this->settings_model->deleteUnit($id)) {
            echo lang("unit_deleted");
        }
    }

    public function unit_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteUnit($id);
                    }
                    $this->session->set_flashdata('message', lang("units_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('categories'))
                        ->SetCellValue('A1', lang('code'))
                        ->SetCellValue('B1', lang('name'))
                        ->SetCellValue('C1', lang('base_unit'))
                        ->SetCellValue('D1', lang('operator'))
                        ->SetCellValue('E1', lang('operation_value'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $unit = $this->site->getUnitByID($id);
                        $sheet->SetCellValue('A' . $row, $unit->code)
                            ->SetCellValue('B' . $row, $unit->name)
                            ->SetCellValue('C' . $row, $unit->base_unit)
                            ->SetCellValue('D' . $row, $unit->operator)
                            ->SetCellValue('E' . $row, $unit->operation_value);
                        $row++;
                    }

                    $sheet->getColumnDimension('B')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    // -------------------------------BANK----------------------------------//
    public function bank()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $link_type = ['mb_bank'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => "Bank"));
        $meta = array('page_title' => "Bank", 'bc' => $bc);
        $this->page_construct('settings/bank', $meta, $this->data);
    }

    public function get_bank()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('bank')}.id,{$this->db->dbprefix('bank')}.logo,{$this->db->dbprefix('bank')}.code,UPPER({$this->db->dbprefix('bank')}.bank_name), {$this->db->dbprefix('bank')}.no_rekening, {$this->db->dbprefix('bank')}.name, {$this->db->dbprefix('bank')}.is_active")
            ->from("bank")
            ->where("{$this->db->dbprefix('bank')}.company_id", $this->session->userdata('company_id'))
            ->where("({$this->db->dbprefix('bank')}.is_deleted !=1 OR {$this->db->dbprefix('bank')}.is_deleted IS NULL)")
            // ->where("({$this->db->dbprefix('bank')}.is_active =1)")
            ->add_column("Actions", "<div class=\"text-center\">  <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>Are You Sure Delete Bank ?</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_bank/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> -->
                <a href='" . site_url('system_settings/edit_bank/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_bank") . "'><i class=\"fa fa-edit\"></i></a>
                <a href='" . site_url('system_settings/sycn_bank/$1') . "' class='tip' title='" . lang("sycn_bank") . "'><i class=\"icon fa fa-refresh tip\"></i></a>
                </div>", "{$this->db->dbprefix('bank')}.id");
        echo $this->datatables->generate();
    }

    public function sycn_bank($id = null)
    {
        $this->load->model('Integration_atl_model', 'integration_atl');
        $this->db->trans_begin();
        try {
            $bank   = $this->site->getBankByID($id);
            if ($bank->falg_atl == 1) {
                throw new \Exception(lang('data_bank_sudah_sycn'));
            }
            $call_api_insert_bank_atl = $this->integration_atl->insert_or_edit_bank_atl($id, 'insert');
            if (!$call_api_insert_bank_atl) {
                throw new \Exception("Gagal mengirimkan data bank ke AksesToko. Server tidak memberikan response yang benar.");
            } else if (!$call_api_insert_bank_atl->status || $call_api_insert_bank_atl->status != '200') {
                throw new \Exception("Gagal mengirimkan data bank ke AksesToko. " . $call_api_insert_bank_atl->message);
            } else if (strtoupper($call_api_insert_bank_atl->message) == 'DATA TIDAK VALID') {
                $call_api_update_bank_atl = $this->integration_atl->insert_or_edit_bank_atl($id, 'update');
                if (!$call_api_update_bank_atl) {
                    throw new \Exception("Gagal mengirimkan data bank ke AksesToko. Server tidak memberikan response yang benar.");
                } else if (!$call_api_update_bank_atl->status || $call_api_update_bank_atl->status != '200') {
                    throw new \Exception("Gagal mengirimkan data bank ke AksesToko. " . $call_api_update_bank_atl->message);
                } else if ($call_api_update_bank_atl->message == 'Data tidak Valid') {
                    throw new \Exception("Gagal mengirimkan data bank ke AksesToko. " . $call_api_update_bank_atl->message . ' ' . $call_api_update_bank_atl->datas[0] . ' ' . $call_api_update_bank_atl->datas[1]);
                }
            }
            $datas = ['falg_atl' => 1];
            $this->settings_model->updateBank($id, $datas);
            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang("data_bank_succses_insert"));
            redirect("system_settings/bank");
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect("system_settings/bank");
        }
    }

    public function add_bank()
    {
        $this->data['options_bank'] = array(
            'bni'       => 'BNI',
            'mandiri'   => 'MANDIRI',
            'bca'       => 'BCA',
            'bri'       => 'BRI',
            'other'     => 'Other'
        );

        if ($this->isPost()) {
            if ($this->input->post('dropdown_bank_name') == 'other') {
                $bank_name = $this->input->post('input_bank_name');
                $photo = '';
            } else {
                $bank_name = $this->input->post('dropdown_bank_name');
                $photo = '/bank_logo/' . $bank_name . '.png';
            }

            $len = strlen($bank_name);
            $code = strtoupper($bank_name) . substr(code_generator(), 0, (20 - $len));
            if ($_FILES['userfile']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path']    = $this->upload_path . '/bank_logo/';
                $config['allowed_types']  = $this->image_types;
                $config['max_size']       = $this->allowed_file_size;
                $config['overwrite']      = false;
                $config['encrypt_name']   = true;
                $config['max_filename']   = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo                      = '/bank_logo/' . $this->upload->file_name;

                $this->load->library('image_lib');
                $config['image_library']    = 'gd2';
                $config['source_image']     = $this->upload_path . $photo;
                $config['new_image']        = $this->thumbs_path . $photo;
                $config['maintain_ratio']   = true;
                $config['width']            = $this->Settings->twidth;
                $config['height']           = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();*/
                $uploadedImg = $this->integration->upload_files($_FILES['userfile']);
                $photo = $uploadedImg->url;
            }


            $data = [
                'bank_name'         => $bank_name,
                'no_rekening'       => $this->input->post('rekening_number'),
                'name'              => $this->input->post('user'),
                'company_id'        => $this->session->userdata('company_id'),
                'logo'              => $photo,
                'code'              => $code,
                'is_active'         => $this->input->post('is_active') ? 1 : 0,
                'is_third_party'    => $this->input->post('is_third_party') ? 1 : 0
            ];

            // print_r($data);
            // die;
            $this->db->trans_begin();

            try {
                $add_bank = $this->settings_model->addBank($data);
                $this->db->trans_commit();
                $this->load->model('Integration_atl_model', 'integration_atl');
                $call_api_insert_bank_atl = $this->integration_atl->insert_or_edit_bank_atl($add_bank, 'insert');
                if (!$call_api_insert_bank_atl) {
                    $this->session->set_flashdata('warning', "Gagal mengirimkan data bank ke AksesToko. Server tidak memberikan response yang benar.");
                } else if (!$call_api_insert_bank_atl->status || $call_api_insert_bank_atl->status != '200') {
                    $this->session->set_flashdata('warning', "Gagal mengirimkan data bank ke AksesToko. " . $call_api_insert_bank_atl->message . ' ' . $call_api_insert_bank_atl->datas[0] . ' ' . $call_api_insert_bank_atl->datas[1]);
                }
                $datas = ['falg_atl' => 1];
                $this->settings_model->updateBank($add_bank, $datas);
                $this->session->set_flashdata('message', "Data Bank Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/bank");
            // if ($this->settings_model->addBank($data)) {
            //     $this->session->set_flashdata('message', "Data Bank Telah Tersimpan");
            //     redirect("system_settings/bank");
            // }else{
            //     $this->session->set_flashdata('warning', "Data Gagal Tersimpan");
            //     redirect("system_settings/bank");
            // }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_bank', $this->data);
        }
    }

    public function edit_bank($id = null)
    {
        $this->data['options_bank'] = array(
            'bni'       => 'BNI',
            'mandiri'   => 'MANDIRI',
            'bca'       => 'BCA',
            'bri'       => 'BRI',
            'other'     =>  'Other'
        );
        $bank_details = $this->site->getBankByID($id);
        if ($this->isPost()) {
            $data = [
                'bank_name' => $this->input->post('bank_name'),
                'no_rekening' => $this->input->post('rekening_number'),
                'name' => $this->input->post('user'),
                'company_id' => $this->session->userdata('company_id'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
                'is_third_party' => $this->input->post('is_third_party') ? 1 : 0
            ];
            if ($_FILES['userfile']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . '/bank_logo/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = '/bank_logo/' . $this->upload->file_name;

                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $this->image_lib->clear();*/
                $uploadedImg    = $this->integration->upload_files($_FILES['userfile']);
                $photo          = $uploadedImg->url;
                $data['logo'] = $photo;
            }

            if ($this->settings_model->updateBank($id, $data)) {
                $this->load->model('Integration_atl_model', 'integration_atl');
                $call_api_update_bank_atl = $this->integration_atl->insert_or_edit_bank_atl($id, 'update');
                if (!$call_api_update_bank_atl) {
                    $this->session->set_flashdata('warning', "Gagal mengirimkan data bank ke AksesToko. Server tidak memberikan response yang benar.");
                    $datas = ['falg_atl' => 0];
                } else if (!$call_api_update_bank_atl->status || $call_api_update_bank_atl->status != '200') {
                    $this->session->set_flashdata('warning', "Gagal mengirimkan data bank ke AksesToko. " . $call_api_update_bank_atl->message . ' ' . $call_api_insert_bank_atl->datas[0] . ' ' . $call_api_insert_bank_atl->datas[1]);
                    $datas = ['falg_atl' => 0];
                } else {
                    $datas = ['falg_atl' => 1];
                }
                $this->settings_model->updateBank($id, $datas);
                $this->session->set_flashdata('message', "Data Bank Telah Tersimpan");
                redirect("system_settings/bank");
            } else {
                $this->session->set_flashdata('warning', "Data Gagal Tersimpan");
                redirect("system_settings/bank");
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['bank'] = $bank_details;
            $this->load->view($this->theme . 'settings/edit_bank', $this->data);
        }
    }

    public function delete_bank($id = null)
    {

        // if ($this->settings_model->brandHasProducts($id)) {
        //     $this->session->set_flashdata('error', lang("brand_has_products"));
        //     redirect("system_settings/brands");
        // }

        if ($this->settings_model->deleteBank($id)) {
            echo lang("brand_deleted");
        }
    }
    // --------------------------END OF BANK -------------------------------//


    public function gross_price()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('gross_price')));
        $meta = array('page_title' => lang('gross_price'), 'bc' => $bc);
        $this->page_construct('settings/gross', $meta, $this->data);
    }

    public function get_gross_price()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('gross')}.id, {$this->db->dbprefix('products')}.name as product, {$this->db->dbprefix('warehouses')}.name as warehouse, {$this->db->dbprefix('gross')}.quantity, operation, {$this->db->dbprefix('gross')}.price")
            ->from("gross")
            ->join("products", "gross.product_id=products.id", "left")
            ->join("warehouses", "gross.warehouse_id=warehouses.id", "left")
            ->where("{$this->db->dbprefix('gross')}.is_deleted !=", 1)
            ->add_column("Actions", "<div class=\"text-center\"> <a href='" . site_url('system_settings/edit_gross_price/$1') . "' class='tip' title='" . lang("edit_gross_price") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_gross_price") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_gross_price/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "{$this->db->dbprefix('gross')}.id");
        //        ->unset_column('gross.id');
        if (!$this->Owner) {
            $this->datatables->where('gross.company_id', $this->session->userdata('company_id'));
        }
        echo $this->datatables->generate();
    }

    public function add_gross_price()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required');
            $this->form_validation->set_rules('product', lang("product"), 'trim|required');
            $this->form_validation->set_rules('operator', lang("operation"), 'trim|required');
            $this->form_validation->set_rules('quantity', lang("quantity"), 'trim|required');

            if ($this->form_validation->run() == true) {
                $product = $this->site->getProductByID($this->input->post('product'));

                if ($product && ($product->cost < $this->input->post('price'))) {
                    $data = array(
                        'warehouse_id'  => $this->input->post('warehouse'),
                        'product_id'    => $this->input->post('product'),
                        'price'         => $this->input->post('price'),
                        'operation'     => $this->input->post('operator'),
                        'quantity'      => $this->input->post('quantity'),
                        'created_on'    => date('Y-m-d'),
                        'created_by'    => $this->session->userdata('user_id'),
                        'company_id'    => $this->session->userdata('company_id'),
                        'start_date'    => $this->sma->fsd(trim($this->input->post('start_date'))),
                        'end_date'      => $this->input->post('end_date') ? $this->sma->fsd(trim($this->input->post('end_date'))) : null,
                    );
                } else {
                    throw new \Exception('price less than cost');
                    // $this->session->set_flashdata('error', 'price less than cost');
                    // redirect("system_settings/gross_price");
                }
            } elseif ($this->input->post('add_gross_price')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/gross_price");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addGrossPrice($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("gross_price_added"));
                redirect("system_settings/gross_price");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_gross_price', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_gross_price($id)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('product', lang("group_name"), 'trim|required');
            $this->form_validation->set_rules('warehouse', lang("group_name"), 'trim|required');

            $gross_details = $this->settings_model->getGrossPriceByID($id);
            if ($this->form_validation->run() == true) {
                $data = array(
                    'warehouse_id'  => $this->input->post('warehouse'),
                    'product_id'    => $this->input->post('product'),
                    'price'         => $this->input->post('price'),
                    'operation'     => $this->input->post('operator'),
                    'quantity'      => $this->input->post('quantity'),
                    'start_date'    => $this->sma->fsd(trim($this->input->post('start_date'))),
                    'end_date'      => $this->sma->fsd(trim($this->input->post('end_date'))),
                );
            } elseif ($this->input->post('edit_gross_price')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/gross_price");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateGrossPrice($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("gross_price_updated"));
                redirect("system_settings/gross_price");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['gross_price'] = $gross_details;
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_gross_price', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_gross_price($id)
    {
        if ($this->settings_model->deleteGrossPrice($id)) {
            echo lang("gross_price_deleted");
        }
    }

    // Tempo
    public function tempo()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $link_type = ['mb_tempo'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('tempo')));
        $meta = array('page_title' => lang('top_management'), 'bc' => $bc);
        $this->page_construct('settings/tempo', $meta, $this->data);
    }

    public function getTempo()
    {
        $this->load->library('datatables');
        $days = lang('day');
        $this->datatables
            ->select("{$this->db->dbprefix('top')}.id as id,{$this->db->dbprefix('top')}.duration as hid_duration, CONCAT({$this->db->dbprefix('top')}.duration, ' ','{$days}') as duration, {$this->db->dbprefix('top')}.description as desc, {$this->db->dbprefix('top')}.is_active")
            ->from("top")
            ->where("{$this->db->dbprefix('top')}.company_id", $this->session->userdata('company_id'))
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_tempo/$1') . "' class='tip' title='" . lang("edit_tempo") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <!-- <a href='#' class='tip po' title='<b>" . lang("delete_tempo") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_tempo/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id");
        echo $this->datatables->generate();
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------//
    public function add_tempo()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('duration', lang("duration"), 'trim|required|numeric');
            if ($this->form_validation->run() == true) {
                $data = [
                    'duration'      => $this->input->post('duration'),
                    'description'   => $this->input->post('description'),
                    'is_active'     => $this->input->post('is_active') ? 1 : 0,
                    'company_id'    => $this->session->userdata('company_id')
                ];
                if ($this->settings_model->addTempo($data)) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("tempo_added"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } else {
                    $this->session->set_flashdata('error', lang("unique"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else if ($this->input->post('add_tempo')) {
                throw new \Exception(validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_tempo', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------//
    public function edit_tempo($id = null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('duration', lang("duration"), 'trim|required|numeric');
            $pg_details = $this->settings_model->getTempoByID($id);
            if ($this->input->post('duration') != $pg_details->duration) {
                $this->form_validation->set_rules('duration', lang("duration"), '');
            }
            if ($this->form_validation->run() == true) {
                $data = [
                    'duration'    => $this->input->post('duration'),
                    'description' => $this->input->post('description'),
                    'is_active'   => $this->input->post('is_active') ? 1 : 0,
                    'company_id'  => $this->session->userdata('company_id')
                ];
                if ($this->settings_model->updateTempo($id, $data)) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("tempo_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } else {
                    throw new \Exception(validation_errors());
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else if ($this->input->post('edit_tempo')) {
                throw new \Exception(validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['top']      = $pg_details;
                $this->data['id']       = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_tempo', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------//
    public function delete_tempo($id = null)
    {
        if ($this->settings_model->deletePriceGroup($id)) {
            echo lang("price_group_deleted");
        }
    }
    // end Tempo

    public function price_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $link_type = ['mb_price_groups'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('price_groups')));
        $meta = array('page_title' => lang('price_groups'), 'bc' => $bc);
        $this->page_construct('settings/price_groups', $meta, $this->data);
    }

    public function getPriceGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('price_groups')}.id as price_id, {$this->db->dbprefix('price_groups')}.name")
            ->from("price_groups")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/group_product_prices/$1') . "' class='tip' title='" . lang("group_product_prices") . "'><i class=\"fa fa-barcode\"></i></a>  <a href='" . site_url('system_settings/edit_price_group/$1') . "' class='tip' title='" . lang("edit_price_group") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='" . site_url('system_settings/edit_cutomer_price_group/$1') . "' class='tip' title='" . lang("edit_cutomer_price_group") . "'><i class=\"fa fa-users\"></i></a> <!-- <a href='#' class='tip po' title='<b>" . lang("delete_price_group") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_price_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "price_id");
        // {$this->db->dbprefix('warehouses')}.name as warehouse
        // ->join('warehouses', 'price_groups.warehouse_id = warehouses.id', 'left')
        //->unset_column('id');
        $this->datatables->where('price_groups.is_deleted', null);
        if (!$this->Owner) {
            $this->datatables->where('price_groups.company_id', $this->session->userdata('company_id'));
        }
        echo $this->datatables->generate();
    }

    public function sycn_price_group()
    {
        $this->db->trans_begin();
        try {
            $this->load->model('Integration_atl_model', 'integration_atl');
            $call_api_insert_or_update_price_group_atl = $this->integration_atl->insert_or_update_price_group_atl($this->session->userdata('company_id'));
            if (!$call_api_insert_or_update_price_group_atl) {
                throw new \Exception(lang('failed_sync'));
            }
            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang("sycn_succsess"));
            redirect("system_settings/price_groups");
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function add_price_group()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("group_name"), 'trim|is_unique[price_groups.name]|required|alpha_numeric_spaces');
            // $this->data['warehouses'] = $this->site->getAllWarehouses();

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    'company_id' => $this->session->userdata('company_id'),
                    // 'warehouse_id' => $this->input->post('warehouse'),
                );
            } elseif ($this->input->post('add_price_group')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/price_groups");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addPriceGroup($data)) {
                $id_pg = $this->db->insert_id();
                $this->db->trans_commit();
                // $this->load->model('Integration_atl_model', 'integration_atl');
                // $this->integration_atl->insert_or_update_price_group_atl($id_pg, $this->session->userdata('company_id'));
                $this->session->set_flashdata('message', lang("price_group_added"));
                redirect("system_settings/price_groups");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_price_group', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_price_group($id = null)
    {
        $this->db->trans_begin();

        try {
            $this->form_validation->set_rules('name', lang("group_name"), 'trim|required|alpha_numeric_spaces');
            // $this->data['warehouses'] = $this->site->getAllWarehouses();
            $pg_details = $this->settings_model->getPriceGroupByID($id);
            if ($this->input->post('name') != $pg_details->name) {
                $this->form_validation->set_rules('name', lang("group_name"), 'is_unique[price_groups.name]');
            }

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    // 'warehouse_id' => $this->input->post('warehouse')
                );
            } elseif ($this->input->post('edit_price_group')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/price_groups");
            }

            $data_company = ['price_group_name' => $this->input->post('name')];
            $update_company = $this->settings_model->updateCompanyByPriceGroup($id, $data_company);

            if ($this->form_validation->run() == true && $this->settings_model->updatePriceGroup($id, $data) && $update_company) {
                $this->db->trans_commit();
                // $this->load->model('Integration_atl_model', 'integration_atl');
                // $this->integration_atl->insert_or_update_price_group_atl($id, $this->session->userdata('company_id'));
                $this->session->set_flashdata('message', lang("price_group_updated"));
                redirect("system_settings/price_groups");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['price_group'] = $pg_details;
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_price_group', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_cutomer_price_group($id = null)
    {

        $this->data['customers'] = $this->companies_model->getCompanyByParent($this->session->userdata('company_id'));
        $this->data['customer_price_group'] = $this->settings_model->getCustomerPriceGroup($id);
        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($id);
        $this->data['id'] = $id;
        $this->data['warehouses'] = $this->site->getNameAndIdWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => base_url('system_settings/price_groups/'), 'page' => lang('price_groups')), array('link' => '#', 'page' => lang('edit_cutomer_price_group')));
        $meta = array('page_title' => lang('edit_cutomer_price_group'), 'bc' => $bc);
        $this->page_construct('settings/edit_cutomer_price_group', $meta, $this->data);
    }

    public function getCustomers_pg()
    {
        $add_user = "<a class=\"tip\" title='" . lang("add_user") . "' id='customersAdd' href='" . site_url('customers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'>
                    <i class=\"fa fa-user-plus\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            //            ->select($this->db->dbprefix('companies').".id, {$this->db->dbprefix('companies')}.company, {$this->db->dbprefix('companies')}.name, {$this->db->dbprefix('companies')}.email, {$this->db->dbprefix('companies')}.phone, {$this->db->dbprefix('companies')}.price_group_name, {$this->db->dbprefix('companies')}.customer_group_name, {$this->db->dbprefix('companies')}.vat_no, {$this->db->dbprefix('companies')}.deposit_amount, {$this->db->dbprefix('companies')}.award_points")
            ->select("CONCAT(id, CONCAT('~', company)), company, name, phone, cf1")
            ->from("companies")
            ->where('group_name', 'customer')
            ->where('(price_group_id IS NULL OR price_group_id = ' . $this->input->get('id_pg') . ')');
        if (!$this->Owner) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }

        if ($this->input->get('provinsi') != '') {
            $this->datatables->where('country', $this->input->get('provinsi'));
        }

        if ($this->input->get('kabupaten') != '') {
            $this->datatables->where('city', $this->input->get('kabupaten'));
        }
        $this->datatables->where('is_deleted', null);
        echo $this->datatables->generate();
    }

    public function save_company_price_group($id_pg)
    {
        $this->db->trans_begin();
        try {
            // $this->settings_model->updateAllCustomerByPriceGroup($id_pg);
            $list_toko = $this->input->post("list_toko");
            $list_toko_awal = $this->input->post("list_toko_awal");
            $list_toko_awal = substr($list_toko_awal, 0, -1);
            $list_toko_awal = explode(',', $list_toko_awal);
            $diff = array_diff($list_toko_awal, $list_toko);

            if (count($list_toko) > count($diff)) {
                foreach ($diff as $val) {
                    $data                     = [];
                    $data['price_group_id']   = null;
                    $data['price_group_name'] = null;
                    if (!$this->companies_model->updateCompany($val, $data)) {
                        throw new \Exception('Update Failed');
                    }
                }
            }
            foreach ($list_toko as $value) {
                $data                     = [];
                $data['price_group_id']   = $id_pg;
                $data['price_group_name'] = $this->input->post('pg_name');
                if (!$this->companies_model->updateCompany($value, $data)) {
                    throw new \Exception('Update Failed');
                }
            }
            $this->db->trans_commit();
            // $this->load->model('Integration_atl_model', 'integration_atl');
            // $this->integration_atl->insert_or_update_price_group_atl($id_pg, $this->session->userdata('company_id'));
            $this->session->set_flashdata('message', lang("customer_added"));
            redirect(base_url('system_settings/price_groups/'));
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_price_group($id = null)
    {
        if ($this->settings_model->deletePriceGroup($id)) {
            echo lang("price_group_deleted");
        }
    }

    public function product_group_price_actions($group_id)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->setProductPriceForPriceGroup($id, $group_id, $this->input->post('price' . $id));
                    }
                    $this->session->set_flashdata('message', lang("products_group_price_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteProductGroupPrice($id, $group_id);
                    }
                    $this->session->set_flashdata('message', lang("products_group_price_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('tax_rates'))
                        ->SetCellValue('A1', lang('product_code'))
                        ->SetCellValue('B1', lang('product_name'))
                        ->SetCellValue('C1', lang('price'))
                        ->SetCellValue('D1', lang('price_kredit'))
                        ->SetCellValue('E1', lang('group_name'));
                    $row = 2;
                    $group = $this->settings_model->getPriceGroupByID($group_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getProductGroupPriceByPID($id, $group_id);
                        $sheet->SetCellValue('A' . $row, $pgp->code)
                            ->SetCellValue('B' . $row, $pgp->name)
                            ->SetCellValue('C' . $row, $pgp->price)
                            ->SetCellValue('D' . $row, $pgp->price_kredit)
                            ->SetCellValue('E' . $row, $group->name);
                        $row++;
                    }
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(30);
                    $sheet->getColumnDimension('C')->setWidth(15);
                    $sheet->getColumnDimension('D')->setWidth(15);
                    $sheet->getColumnDimension('E')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'price_groups_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_price_group_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function group_product_prices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }

        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/price_groups'), 'page' => lang('price_groups')), array('link' => '#', 'page' => lang('group_product_prices')));
        $meta = array('page_title' => lang('group_product_prices'), 'bc' => $bc);
        $this->page_construct('settings/group_product_prices', $meta, $this->data);
    }

    public function getProductPrices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect('system_settings/price_groups');
        }

        $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.price_kredit as price_kredit, {$this->db->dbprefix('product_prices')}.min_order as min_order, {$this->db->dbprefix('product_prices')}.is_multiple as is_multiple FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$group_id} ) PP";
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, PP.price as price , PP.price_kredit as price_kredit, PP.min_order as min_order, PP.is_multiple,  unit.name as default_unit, {$this->db->dbprefix('units')}.name as unit_name")
            ->from("products")
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->join('sma_units', 'sma_products.sale_unit = sma_units.id', 'inner')
            ->join('sma_units as unit', 'sma_products.unit = unit.id', 'inner')
            ->edit_column("price", "$1__$2__$3", 'id, price,default_unit')
            ->edit_column("price_kredit", "$1__$2__$3", 'id, price_kredit, default_unit')
            ->edit_column("min_order", "$1__$2__$3", 'id, min_order, unit_name')
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-pencil\"></i></button></div>", "id");

        if (!$this->Owner) {
            $this->datatables->where("products.company_id", $this->session->userdata('company_id'));
        }

        $this->datatables->where("products.is_deleted IS NULL");
        echo $this->datatables->generate();
    }

    public function update_product_group_price($group_id = null)
    {
        if (!$group_id) {
            $this->sma->send_json(array('status' => 0));
        }

        $product_id   = $this->input->post('product_id', true);
        $price        = $this->input->post('price', true);
        $price_kredit = $this->input->post('price_kredit', true);
        $min_order    = $this->input->post('min_order', true);
        $is_multiple  = $this->input->post('is_multiple');

        if ($is_multiple == 'false') {
            $is_multiple = 0;
        } else {
            $is_multiple = 1;
        }
        if (!empty($product_id)) {
            if ($this->settings_model->setProductPriceForPriceGroup($product_id, $group_id, $price, $price_kredit, $min_order, $is_multiple)) {
                // $this->load->model('Integration_atl_model', 'integration_atl');
                // $this->integration_atl->insert_or_update_price_group_atl($group_id, $this->session->userdata('company_id'));
                $this->sma->send_json(array('status' => 1));
            }
        }
        $this->sma->send_json(array('status' => 0));
    }

    public function update_prices_csv($group_id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('message', lang("disabled_in_demo"));
                redirect('welcome');
            }

            if (isset($_FILES["userfile"])) {
                // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                $this->load->library('upload');
                $config['upload_path'] = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("system_settings/group_product_prices/" . $group_id);
                }
                $csv = $this->upload->file_name;
                $arrResult = array();
                $handle = fopen('files/' . $csv, "r");

                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'price');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if ($product = $this->site->getProductByCode(trim($csv_pr['code']))) {
                        $data[] = array(
                            'product_id' => $product->id,
                            'price' => $csv_pr['price'],
                            'price_group_id' => $group_id
                        );
                    } else {
                        $this->session->set_flashdata('message', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
                        redirect("system_settings/group_product_prices/" . $group_id);
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("system_settings/group_product_prices/" . $group_id);
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            $this->settings_model->updateGroupPrices($data);
            $this->session->set_flashdata('message', lang("price_updated"));
            redirect("system_settings/group_product_prices/" . $group_id);
        } else {
            $this->data['userfile'] = array(
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['group'] = $this->site->getPriceGroupByID($group_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/update_price', $this->data);
        }
    }

    public function multiple_discount()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('multiple_discount')));
        $meta = array('page_title' => lang('multiple_discount'), 'bc' => $bc);
        $this->page_construct('settings/multiple_discount', $meta, $this->data);
    }

    public function getMultipleDiscount()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('multiple_discount')}.id, {$this->db->dbprefix('products')}.name as product, {$this->db->dbprefix('warehouses')}.name as warehouse, {$this->db->dbprefix('multiple_discount')}.quantity, operation, {$this->db->dbprefix('multiple_discount')}.discount, sub_discount", false)
            ->from("multiple_discount")
            ->join("products", "multiple_discount.product_id=products.id", "left")
            ->join("warehouses", "multiple_discount.warehouse_id=warehouses.id", "left")
            ->where("{$this->db->dbprefix('multiple_discount')}.is_deleted !=", 1)
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_multiple_discount/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_discount") . "'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_discount") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_multiple_discount/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "{$this->db->dbprefix('multiple_discount')}.id");

        if (!$this->Owner) {
            $this->datatables->where("multiple_discount.company_id", $this->session->userdata('company_id'));
        }
        echo $this->datatables->generate();
    }

    public function add_multiple_discount()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required|alpha_numeric_spaces');
            $this->form_validation->set_rules('product', lang("product"), 'trim|required|alpha_numeric_spaces');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'warehouse_id' => $this->input->post('warehouse'),
                    'product_id' => $this->input->post('product'),
                    'quantity' => $this->input->post('quantity'),
                    'operation' => $this->input->post('operator'),
                    'discount' => $this->input->post('discount'),
                    'sub_discount' => $this->input->post('sub_discount'),
                    'company_id' => $this->session->userdata('company_id'),
                    'start_date' => $this->sma->fsd(trim($this->input->post('start_date'))),
                    'end_date' => $this->input->post('end_date') ? $this->sma->fsd(trim($this->input->post('end_date'))) : null,
                    'created_by' => $this->session->userdata('user_id'),
                    'created_on' => date('Y-m-d'),
                );
            } elseif ($this->input->post('add_discount')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/multiple_discount");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addMultipleDiscount($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("multiple_discount_added"));
                redirect("system_settings/multiple_discount");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_multiple_discount', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_multiple_discount($id)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('product', lang("group_name"), 'trim|required');
            $this->form_validation->set_rules('warehouse', lang("group_name"), 'trim|required');

            $multiple_disc = $this->settings_model->getMultipleDiscountByID($id);
            if ($this->form_validation->run() == true) {
                $data = array(
                    'warehouse_id' => $this->input->post('warehouse'),
                    'product_id' => $this->input->post('product'),
                    'quantity' => $this->input->post('quantity'),
                    'operation' => $this->input->post('operator'),
                    'discount' => $this->input->post('discount'),
                    'sub_discount' => $this->input->post('sub_discount'),
                    'start_date' => $this->sma->fsd(trim($this->input->post('start_date'))),
                    'end_date' => $this->sma->fsd(trim($this->input->post('end_date'))),
                );
            } elseif ($this->input->post('edit_multiple_discount')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/mutiple_discount");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateMultipleDiscount($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("multiple_discount_updated"));
                redirect("system_settings/multiple_discount");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['md_details'] = $multiple_disc;
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_multiple_discount', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_multiple_discount($id)
    {
        if ($this->settings_model->deleteMultipleDiscount($id)) {
            echo lang("multiple_discount_deleted");
        }
    }

    public function brands()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('brands')));
        $meta = array('page_title' => lang('brands'), 'bc' => $bc);
        $this->page_construct('settings/brands', $meta, $this->data);
    }

    public function getBrands()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, image, code, name", false)
            ->from("brands")
            ->where("(client_id = " . $this->session->userdata('company_id') . " or client_id = 1 )")
            ->where("is_deleted", null)
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_brand/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_brand") . "'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_brand") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_brand/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id");

        echo $this->datatables->generate();
    }

    public function add_brand()
    {
        $this->sma->checkPermissions(false, true);
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("brand_name"), 'trim|required|alpha_numeric_spaces');

            if ($this->form_validation->run() == true) {

                //nge cek apakah jumlah Brand telah limit
                $isLimited = $this->authorized_model->isBrandLimited($this->session->userdata('company_id'));
                if ($isLimited["status"]) {
                    $message = str_replace("xxx", $isLimited["max"], lang("limited_master"));
                    $message = str_replace("yyy", lang("brands"), $message);
                    throw new \Exception($message);
                    // $this->session->set_flashdata('warning', $message);
                    // redirect("system_settings/brands");
                }
                // akhir cek

                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'client_id' => $this->session->userdata('company_id'),
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo = $this->upload->file_name;
                    $data['image'] = $photo;
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->upload_path . $photo;
                    $config['new_image'] = $this->thumbs_path . $photo;
                    $config['maintain_ratio'] = true;
                    $config['width'] = $this->Settings->twidth;
                    $config['height'] = $this->Settings->theight;
                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);
                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }
                    $this->image_lib->clear();*/
                    $uploadedImg    = $this->integration->upload_files($_FILES['userfile']);
                    $photo          = $uploadedImg->url;
                    $data['image'] = $photo;
                }
            } elseif ($this->input->post('add_brand')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/brands");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addBrand($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("brand_added"));
                redirect("system_settings/brands");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_brand', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_brand($id = null)
    {
        $this->db->trans_begin();

        try {
            $this->form_validation->set_rules('name', lang("brand_name"), 'trim|required|alpha_numeric_spaces');
            $brand_details = $this->site->getBrandByID($id);
            if ($this->input->post('name') != $brand_details->name) {
                $this->form_validation->set_rules('name', lang("brand_name"), 'is_unique[brands.name]');
            }

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                );

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo = $this->upload->file_name;
                    $data['image'] = $photo;
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->upload_path . $photo;
                    $config['new_image'] = $this->thumbs_path . $photo;
                    $config['maintain_ratio'] = true;
                    $config['width'] = $this->Settings->twidth;
                    $config['height'] = $this->Settings->theight;
                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);
                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }
                    $this->image_lib->clear();*/
                    $uploadedImg = $this->integration->upload_files($_FILES['userfile']);
                    $photo = $uploadedImg->url;
                    $data['image'] = $photo;
                }
            } elseif ($this->input->post('edit_brand')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/brands");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateBrand($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("brand_updated"));
                redirect("system_settings/brands");
            } else {
                $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['brand'] = $brand_details;
                $this->load->view($this->theme . 'settings/edit_brand', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_brand($id = null)
    {
        if ($this->settings_model->brandHasProducts($id)) {
            $this->session->set_flashdata('error', lang("brand_has_products"));
            redirect("system_settings/brands");
        }

        if ($this->settings_model->deleteBrand($id)) {
            echo lang("brand_deleted");
        }
    }

    public function import_brands()
    {
        $this->db->trans_begin();
        try {
            $this->load->helper('security');
            $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

            if ($this->form_validation->run() == true) {
                if (isset($_FILES["userfile"])) {
                    // Upload files excel untuk kebutuhan import csv/excel, jangan diupload ke files.forca.id
                    $this->load->library('upload');
                    $config['upload_path'] = 'files/';
                    $config['allowed_types'] = 'csv';
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = true;
                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("system_settings/brands");
                    }
                    $csv = $this->upload->file_name;
                    $arrResult = array();
                    $handle = fopen('files/' . $csv, "r");

                    if ($handle) {
                        while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                            $arrResult[] = $row;
                        }
                        fclose($handle);
                    }
                    $titles = array_shift($arrResult);
                    $keys = array('name', 'code', 'image');
                    $final = array();
                    foreach ($arrResult as $key => $value) {
                        $final[] = array_combine($keys, $value);
                    }

                    foreach ($final as $csv_ct) {
                        if (!$this->settings_model->getBrandByName(trim($csv_ct['name']))) {
                            $data[] = array(
                                'code' => trim($csv_ct['code']),
                                'name' => trim($csv_ct['name']),
                                'image' => trim($csv_ct['image']),
                            );
                        }
                    }
                }

                // $this->sma->print_arrays($data);
            }

            if ($this->form_validation->run() == true && !empty($data) && $this->settings_model->addBrands($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("brands_added"));
                redirect('system_settings/brands');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['userfile'] = array(
                    'name' => 'userfile',
                    'id' => 'userfile',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('userfile')
                );
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/import_brands', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function brand_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteBrand($id);
                    }
                    $this->session->set_flashdata('message', lang("brands_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('brands'));
                    $sheet->SetCellValue('A1', lang('name'))
                        ->SetCellValue('B1', lang('code'))
                        ->SetCellValue('C1', lang('image'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $brand = $this->site->getBrandByID($id);
                        $sheet->SetCellValue('A' . $row, $brand->name)
                            ->SetCellValue('B' . $row, $brand->code)
                            ->SetCellValue('C' . $row, $brand->image);
                        $row++;
                    }

                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function promo()
    {
        $bc = array(array('link' => base_url(), 'page' => lang('Setting')), array('link' => '#', 'page' => lang('Purchases Promo')));
        $meta = array('page_title' => lang('Promo'), 'bc' => $bc);
        $this->page_construct('settings/promo', $meta, $this->data);
    }
    public function getPromo()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, date, name, code_promo,start_date,end_date", false)
            ->from("promo")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_promo/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit Promo") . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("Delete Promo") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_Promo/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }
    public function add_promo($quote_id = null)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('Judul', lang("judul"), 'required');
            $this->form_validation->set_rules('start_date', lang("startdate"), 'required');
            $this->form_validation->set_rules('end_date', lang("enddate"), 'required');
            if ($this->form_validation->run() == true) {
                $supplier_id = $this->input->post('supplier');
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
                $note = $this->sma->clear_tags($this->input->post('note'));
                $total = 0;
                $product_tax = 0;
                $order_tax = 0;
                $product_discount = 0;
                $order_discount = 0;
                $percentage = '%';
                $i = sizeof($_POST['product']);
                for ($r = 0; $r < $i; $r++) {
                    $item_code = $_POST['product'][$r];
                    $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                    $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                    $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                    $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                    $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                    $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                    $item_unit = $_POST['product_unit'][$r];
                    $item_quantity = $_POST['product_base_quantity'][$r];
                    $this->load->model('purchases_model');
                    if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                        $product_details = $this->purchases_model->getProductByCode($item_code);
                        $pr_discount = 0;

                        if (isset($item_discount)) {
                            $discount = $item_discount;
                            $dpos = strpos($discount, $percentage);
                            if ($dpos !== false) {
                                $pds = explode("%", $discount);
                                $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (float) ($pds[0])) / 100), 4);
                            } else {
                                $pr_discount = $this->sma->formatDecimal($discount);
                            }
                        }
                        $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                        $item_net_cost = $unit_cost;
                        $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount += $pr_item_discount;
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $item_tax = 0;
                        $tax = "";

                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $pr_tax = $item_tax_rate;
                            $tax_details = $this->site->getTaxRateByID($pr_tax);
                            if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_cost = $unit_cost - $item_tax;
                                }
                            } elseif ($tax_details->type == 2) {
                                if ($product_details && $product_details->tax_method == 1) {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                    $tax = $tax_details->rate . "%";
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                    $tax = $tax_details->rate . "%";
                                    $item_net_cost = $unit_cost - $item_tax;
                                }

                                $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                $tax = $tax_details->rate;
                            }
                            $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        }

                        $product_tax += $pr_item_tax;
                        $unit = $this->site->getUnitByID($item_unit);

                        $products[] = array(
                            'product_code' => $item_code,
                            'product_name' => $product_details->name,
                            'net_unit_cost' => $item_net_cost,
                            'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                            'quantity' => $item_quantity,
                            'product_unit_id' => $item_unit,
                            'product_unit_code' => $unit->code,
                            'unit_quantity' => $item_unit_quantity,
                            'item_tax' => $pr_item_tax,
                            'tax_rate_id' => $pr_tax,
                            'tax' => $tax,
                            'discount' => $item_discount,
                            'item_discount' => $pr_item_discount,
                            'real_unit_cost' => $real_unit_cost,

                        );
                    }
                }
                $data = array(
                    "name" => $this->input->post('Judul'),
                    "description" => $this->input->post('description'),
                    "link_promo" => $this->input->post('linkpromo'), // link akses detail
                    "code_promo" => $this->input->post('kodepromo'),
                    "start_date" => $this->input->post('start_date'),
                    "end_date" => $this->input->post('end_date'),
                    "syarat" =>  $note,
                    "supplier_id" => $supplier_id,
                    "supplier" => $supplier,
                    "created_by" => $this->session->userdata('user_id'),
                    "link_outsite" => $this->input->post('directoutside'),
                    "url_outsite" => $this->input->post('linkout'),     // link external validation
                    "total_items" => $i
                );
                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['max_width'] = $this->Settings->iwidth;
                    $config['max_height'] = $this->Settings->iheight;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        throw new \Exception($error);
                        // $this->session->set_flashdata('error', $error);
                        // redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo = $this->upload->file_name;
                    $data['url_image'] = $photo;
                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->upload_path . $photo;
                    $config['new_image'] = $this->thumbs_path . $photo;
                    $config['maintain_ratio'] = true;
                    $config['width'] = $this->Settings->twidth;
                    $config['height'] = $this->Settings->theight;
                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);
                    if (!$this->image_lib->resize()) {
                        echo $this->image_lib->display_errors();
                    }
                    $this->image_lib->clear();*/
                    $uploadedImg        = $this->integration->upload_files($_FILES['userfile']);
                    $photo              = $uploadedImg->url;
                    $data['url_image'] = $photo;
                }
            }
            if ($this->form_validation->run() == true && $this->settings_model->addPromo($data, $products)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("Sukses Tambah Promosi"));
                redirect('promo');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                $this->load->helper('string');
                $value = random_string('alnum', 20);
                $this->session->set_userdata('user_csrf', $value);
                $this->data['csrf'] = $this->session->userdata('user_csrf');
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('add_promo')));
                $meta = array('page_title' => lang('add_promo'), 'bc' => $bc);
                $this->page_construct('settings/add_promo', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function authorized()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('authorized')));
        $meta = array('page_title' => lang('authorized'), 'bc' => $bc);
        $this->page_construct('settings/authorized', $meta, $this->data);
    }

    public function getAuthorized()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('authorized.id, companies.company, companies.email, users, warehouses, biller, create_on')
            ->join('companies', 'authorized.company_id = companies.id', 'left')
            ->from("authorized");
        //        ->unset_column('id');
        if (!$this->Owner) {
            $this->datatables->where('authorized.company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_authorized/$1') . "' class='tip' title='" . lang("edit_authorized") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_authorized") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_authorized/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "authorized.id");
        //            $this->datatables->unset_column('authorized.id');
        echo $this->datatables->generate();
    }

    public function edit_authorized($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        // $this->form_validation->set_rules('company', lang("company"));
        //  $authorized_details = $this->settings_model->getAuthorizedByID($id);
        //  if ($this->input->post('company') != $authorized_details->company) {
        // $this->form_validation->set_rules('company', lang("company"), 'is_unique[companies.company]');
        //  }
        // $this->form_validation->set_rules('email', lang("email"));
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('users', lang("users"), 'required');
            $this->form_validation->set_rules('warehouses', lang("warehouses"), 'required|numeric');
            $this->form_validation->set_rules('biller', lang("biller"));
            $this->form_validation->set_rules('create_on', lang("create_on"));

            if ($this->form_validation->run() == true) {
                $data = array(
                    'users' => $this->input->post('users'),
                    'warehouses' => $this->input->post('warehouses'),
                    'biller' => $this->input->post('biller')
                );
            } elseif ($this->input->post('edit_authorized')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/authorized");
            }
            if ($this->form_validation->run() == true && $this->settings_model->updateAuthorized($id, $data)) { //check to see if we are updateing the customer
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("authorized_updated"));
                redirect("system_settings/authorized");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['authorized'] = $this->settings_model->getAuthorizedByID($id);
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_authorized', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function Delete_Authorized($id = null)
    {
        if ($this->settings_model->deleteAuthorized($id)) {
            echo lang("authorized_deleted");
        }
    }

    public function bonus()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('bonus')));
        $meta = array('page_title' => lang('bonus'), 'bc' => $bc);
        $this->page_construct('settings/bonus', $meta, $this->data);
    }

    public function getBonus()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('bonus')}.id, {$this->db->dbprefix('warehouses')}.name as warehouse, {$this->db->dbprefix('products')}.name as product, p.name as bonus, {$this->db->dbprefix('bonus')}.quantity")
            ->from("bonus")
            ->join("products", "bonus.product_id=products.id", "left")
            ->join("warehouses", "bonus.warehouse_id=warehouses.id", "left")
            ->join("products as p", "bonus.product_bonus=p.id", "left");

        $this->datatables->where("({$this->db->dbprefix('bonus')}.is_deleted != 1 or {$this->db->dbprefix('bonus')}.is_deleted IS NULL)");
        if (!$this->Owner) {
            $this->datatables->where("{$this->db->dbprefix('bonus')}.company_id", $this->session->userdata('company_id'));
        }

        $this->datatables->add_column("Actions", "<div class=\"text-center\"> <a href='" . site_url('system_settings/edit_bonus/$1') . "' class='tip' title='" . lang("edit_bonus") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_bonus") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_bonus/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "{$this->db->dbprefix('bonus')}.id");
        echo $this->datatables->generate();
    }

    public function add_bonus()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
            $this->form_validation->set_rules('product', lang("product"), 'required');
            $this->form_validation->set_rules('bonus', lang("bonus"), 'required');
            $this->form_validation->set_rules('quantity', lang("quantity"), 'required');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'warehouse_id'  => $this->input->post('warehouse'),
                    'product_id'    => $this->input->post('product'),
                    'product_bonus' => $this->input->post('bonus'),
                    'quantity'      => $this->input->post('quantity'),
                    'created_on'    => date('Y-m-d'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'company_id'    => $this->session->userdata('company_id'),
                    'start_date'    => $this->sma->fsd(trim($this->input->post('start_date'))),
                    'end_date'      => $this->input->post('end_date') ? $this->sma->fsd(trim($this->input->post('end_date'))) : null,
                    'multiply'      => $this->input->post('multiply') ? 1 : null,
                );
            } elseif ($this->input->post('add_bonus')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/bonus");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addBonus($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("bonus_added"));
                redirect("system_settings/bonus");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_bonus', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_bonus($id)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('product', lang("product"), 'required');
            $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');

            $bonus_details = $this->settings_model->getBonusByID($id);
            if ($this->form_validation->run() == true) {
                $data = array(
                    'warehouse_id'  => $this->input->post('warehouse'),
                    'product_id'    => $this->input->post('product'),
                    'product_bonus' => $this->input->post('bonus'),
                    'quantity'      => $this->input->post('quantity'),
                    'start_date'    => $this->sma->fsd(trim($this->input->post('start_date'))),
                    'end_date'      => $this->sma->fsd(trim($this->input->post('end_date'))),
                    'multiply'      => $this->input->post('multiply') ? 1 : null,
                );
            } elseif ($this->input->post('edit_bonus')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/bonus");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateBonus($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("bonus_updated"));
                redirect("system_settings/bonus");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['bonus'] = $bonus_details;
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_bonus', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_bonus($id)
    {
        if ($this->settings_model->deleteBonus($id)) {
            echo lang("bonus_deleted");
        }
    }

    public function pricing()
    {
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => 'pricing'));
        $meta = array('page_title' => 'pricing', 'bc' => $bc);
        $this->page_construct('settings/pricing', $meta, $this->data);
    }

    public function add_bonus_dt($data = array())
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
            $this->form_validation->set_rules('product', lang("product"), 'required');
            $this->form_validation->set_rules('bonus', lang("bonus"), 'required');
            $this->form_validation->set_rules('quantity', lang("quantity"), 'required');

            if ($this->form_validation->run() == true) {
                $data[] = array(
                    'warehouse_id'  => $this->input->post('warehouse'),
                    'product_id'    => $this->input->post('product'),
                    'product_bonus' => $this->input->post('bonus'),
                    'quantity'      => $this->input->post('quantity'),
                    'created_on'    => date('Y-m-d'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'company_id'    => $this->session->userdata('company_id'),
                );
            } elseif ($this->input->post('add_bonus')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/pricing");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addBonuses($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("bonus_added"));
                redirect("system_settings/pricing");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                //            $this->data['warehouses']= $this->site->getAllWarehouses();
                //            $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/pricing', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function shipping_charges()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $link_type = ['mb_shipping_charges'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('shipping_charges')));
        $meta = array('page_title' => lang('shipping_charges'), 'bc' => $bc);
        $this->page_construct('settings/shipping_charges', $meta, $this->data);
    }

    public function getShipCharges()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id,CONCAT(COALESCE( min_distance, 0 ), '__', COALESCE( max_distance, 0 )) as distance, cost_member, cost")
            ->from("shipping_charges");
        $this->datatables->where("is_deleted !=", 1)->or_where("is_deleted", null);
        $this->datatables->add_column("Actions", "<div class=\"text-center\"> <a href='" . site_url('system_settings/edit_shipping_charges/$1') . "' class='tip' title='" . lang("edit_shipping_charges") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_shipping_charges") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_shipping_charges/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id")
            ->unset_column('id');

        if (!$this->Owner) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }
        echo $this->datatables->generate();
    }

    public function add_shipping_charges()
    {
        $this->db->trans_begin();

        try {
            $this->form_validation->set_rules('min_distance', lang("minimal_distance"), 'required');
            $this->form_validation->set_rules('max_distance', lang("maximum_distance"), 'required');
            $this->form_validation->set_rules('cost_regular', lang("cost_regular"), 'required');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'min_distance'  => $this->input->post('min_distance'),
                    'max_distance'  => $this->input->post('max_distance'),
                    'cost'          => $this->input->post('cost_regular'),
                    'cost_member'   => $this->input->post('cost_member'),
                    'company_id'    => $this->session->userdata('company_id'),
                );
            } elseif ($this->input->post('add_shipping_charges')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/shipping_charges");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addShippingCharges($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("shipping_charges_added"));
                redirect("system_settings/shipping_charges");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_shipping_charges', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_shipping_charges($id)
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('min_distance', lang("minimal_distance"), 'required');
            $this->form_validation->set_rules('max_distance', lang("maximum_distance"), 'required');
            $this->form_validation->set_rules('cost_regular', lang("cost_regular"), 'required');

            $shipping = $this->settings_model->getShippingChargesByID($id);
            if ($this->form_validation->run() == true) {
                $data = array(
                    'min_distance'  => $this->input->post('min_distance'),
                    'max_distance'  => $this->input->post('max_distance'),
                    'cost'  => $this->input->post('cost_regular'),
                    'cost_member'  => $this->input->post('cost_member'),
                );
            } elseif ($this->input->post('edit_shipping_charges')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/shipping_charges");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateShippingCharges($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("shipping_charges_updated"));
                redirect("system_settings/shipping_charges");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['id'] = $id;
                $this->data['shipping'] = $shipping;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_shipping_charges', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_shipping_charges($id)
    {
        if ($this->settings_model->deleteShippingCharges($id)) {
            echo lang("shipping_charges_deleted");
        }
    }

    public function gap()
    {
        $term = $this->input->get('term', true);

        $row = $this->site->getShippingChargesData($term);
        $this->sma->send_json($row);
    }

    public function points()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['pts'] = $this->site->getPoints();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('points')), array('link' => '#', 'page' => lang('points')));
        $meta = array('page_title' => lang('points'), 'bc' => $bc);
        $this->page_construct('settings/points', $meta, $this->data);
    }

    public function getPoints()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("id, spent, customer_award_point, price_exchange, point_exchange")
            ->from("points")
            ->where('is_deleted', null)
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_points/$1') . "' class='tip' title='" . lang("edit_points") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <!-- Sementara tombol delete disembunyikan  <a href='#' class='tip po' title='<b>" . lang("delete_points") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_points/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "id");
        if (!$this->Owner) {
            $this->datatables->where('company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->unset_column('id');
        echo $this->datatables->generate();
    }

    public function add_points()
    {
        $this->db->trans_begin();
        try {
            if ($this->input->post('each_spent')) {
                $this->form_validation->set_rules('ca_point', "Award Points for Customer", 'required');
            } elseif ($this->input->post('price_exchange')) {
                $this->form_validation->set_rules('point_exchange', "Point Exchange", 'required');
            }

            if ($this->form_validation->run() == true) {
                $data = array(
                    'spent'                 => $this->input->post('each_spent'),
                    'customer_award_point'  => $this->input->post('ca_point'),
                    'price_exchange'                  => $this->input->post('price_exchange'),
                    'point_exchange'      => $this->input->post('point_exchange'),
                    'created_on'            => date('Y-m-d'),
                    'created_by'            => $this->session->userdata('user_id'),
                    'company_id'            => $this->session->userdata('company_id'),
                );
            } elseif ($this->input->post('add_points')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/points");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addPoints($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("points_added"));
                redirect("system_settings/points");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                //            $this->data['warehouses']= $this->site->getAllWarehouses();
                $this->data['pts'] = $this->site->getPoints();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_points', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_points($id)
    {
        $this->db->trans_begin();
        try {
            if ($this->input->post('each_spent')) {
                $this->form_validation->set_rules('ca_point', "Award Points for Customer", 'required');
            } elseif ($this->input->post('price_exchange')) {
                $this->form_validation->set_rules('point_exchange', "Point Exchange", 'required');
            }

            if ($this->form_validation->run() == true) {
                $data = array(
                    'spent'                 => $this->input->post('each_spent'),
                    'customer_award_point'  => $this->input->post('ca_point'),
                    'price_exchange'                  => $this->input->post('price_exchange'),
                    'point_exchange'      => $this->input->post('point_exchange'),
                );
            } elseif ($this->input->post('edit_points')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/points");
            }

            if ($this->form_validation->run() == true && $this->settings_model->updatePoints($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("points_updated"));
                redirect("system_settings/points");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['pts'] = $this->site->getPoints();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['id'] = $id;
                $this->load->view($this->theme . 'settings/edit_points', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_points($id)
    {
        if ($this->settings_model->deletePoints($id)) {
            echo lang("points_deleted");
        }
    }

    public function plans()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        //        $this->data['pts'] = $this->site->getPoints();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('plans')), array('link' => '#', 'page' => lang('plans')));
        $meta = array('page_title' => lang('plans'), 'bc' => $bc);
        $this->page_construct('settings/plans', $meta, $this->data);
    }

    public function getPlansPricing()
    {
        $this->load->library('datatables');

        $this->datatables->select("id, name, description, price")
            ->from("plans")->where("is_deleted", null);
        $this->datatables->add_column("Actions", "<div class=\"text-center\"> <a href='" . site_url('system_settings/edit_plans/$1') . "' class='tip' title='" . lang("edit_plans") . "' ><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_plans") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_plans/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }

    public function add_plan()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('warehouses_plan', lang("warehouse"), 'required');
            $this->form_validation->set_rules('users_plan', lang("user"), 'required');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name'          => $this->input->post('name_plan'),
                    'description'   => $this->input->post('description_plan'),
                    'price'         => $this->input->post('price_plan'),
                    'warehouses'    => $this->input->post('warehouses_plan'),
                    'users'         => $this->input->post('users_plan'),
                );
            } elseif ($this->input->post('add_plan')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/plans");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addPlan($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("plan_added"));
                redirect('system_settings/plans');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_plan', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_plans($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('price', lang("price"), 'required');
            if ($this->form_validation->run() == true) {
                $data = array(
                    'master' => $this->input->post('master_data'),
                    'pos' => $this->input->post('pos'),
                    'purchases' => $this->input->post('purchases'),
                    'sales' => $this->input->post('sales'),
                    'quotes' => $this->input->post('quotes'),
                    'expenses' => $this->input->post('expenses'),
                    'reports' => $this->input->post('reports'),
                    'transfers' => $this->input->post('transfers'),
                    'limitation' => $this->input->post('limitation'),
                    'users' => $this->input->post('users'),
                    'warehouses' => $this->input->post('warehouses'),
                    'price' => $this->input->post('price'),
                );
            }

            if ($this->form_validation->run() == true && $this->settings_model->updatePlan($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("plan_updated"));
                redirect('system_settings/plans');
            } else {
                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

                $this->data['id'] = $id;
                $this->data['plan'] = $this->site->getPlanPricingByID($id);

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('edit_plans')));
                $meta = array('page_title' => lang('edit_plans'), 'bc' => $bc);
                $this->page_construct('settings/edit_plans', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_plans($id)
    {
        if ($this->settings_model->deletePlan($id)) {
            echo lang("plan_deleted");
        }
    }

    public function addons()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('addons')), array('link' => '#', 'page' => lang('addons')));
        $meta = array('page_title' => lang('addons'), 'bc' => $bc);
        $this->page_construct('settings/addons', $meta, $this->data);
    }

    public function getAddons()
    {
        $this->load->library('datatables');

        $this->datatables->select("id, name, price")
            ->from("addons")->where("is_deleted", null);
        $this->datatables->add_column("Actions", "<div class=\"text-center\"> <a href='" . site_url('system_settings/edit_addon/$1') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static' class='tip' title='" . lang("edit_addon") . "' ><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_addon") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_addon/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
            ->unset_column("id");

        echo $this->datatables->generate();
    }

    public function add_addon()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("name"), 'required');
            $this->form_validation->set_rules('price', lang("price"), 'required');

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name'  => $this->input->post('name'),
                    'price' => $this->input->post('price'),
                );
            } elseif ($this->input->post('add_addon')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/addons");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addAddon($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("addon_added"));
                redirect('system_settings/addons');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_addon', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_addon($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('price', lang("price"), 'required');
            if ($this->form_validation->run() == true) {
                $data = array(
                    'price' => $this->input->post('price'),
                );
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateAddon($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("addon_updated"));
                redirect('system_settings/addons');
            } else {
                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['id'] = $id;
                $this->data['addon'] = $this->settings_model->getAddonByID($id);
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_addon', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_addon($id)
    {
        if ($this->settings_model->deleteAddon($id)) {
            echo lang("addon_deleted");
        }
    }
    // ---------------------CMS FAQ------------------//

    public function cms_faq()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => "CMS Faq"));
        $meta = array('page_title' => 'CMS Faq', 'bc' => $bc);
        $this->page_construct('settings/cms_faq', $meta, $this->data);
    }

    public function add_cms_faq()
    {
        if ($this->isPost()) {
            $data = [
                'title' => $this->input->post('title'),
                'caption' => $this->input->post('caption'),
                'is_deleted' => 0,
                'is_active' => $this->input->post('active') != null ? '1' : '0',
                'created_by' => $this->session->userdata('user_id')
            ];
            try {
                $this->settings_model->add_cms_faq($data);
                $this->session->set_flashdata('message', "Tambah FAQ Telah Berhasil");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms_faq");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_cms_faq', $this->data);
        }
    }

    public function get_cms_faq()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('cms_faq')}.id as id,{$this->db->dbprefix('cms_faq')}.title,{$this->db->dbprefix('cms_faq')}.is_active as active")
            ->from("cms_faq")
            ->edit_column("active", "$1__$2", 'id, active')
            ->where("({$this->db->dbprefix('cms_faq')}.is_deleted !=1 OR {$this->db->dbprefix('cms_faq')}.is_deleted IS NULL)")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_cms_faq/$1') . "' class='tip' title='" . lang("show_&_edit") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> <a href='#' class='tip po' title='<b>Are You Sure Delete Template ?</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_cms_faq/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

    public function delete_cms_faq($id)
    {
        if ($this->settings_model->deleteCmsFaq($id)) {
            echo lang("brand_deleted");
        }
    }

    public function edit_cms_faq($id)
    {
        if ($this->isPost()) {
            $data = [
                'title' => $this->input->post('title'),
                'caption' => $this->input->post('caption'),
                'is_active' => $this->input->post('active') != null ? '1' : '0',
                'created_by' => $this->session->userdata('user_id')
            ];
            try {
                $this->settings_model->updateCmsFaq($id, $data);
                // if ($this->input->post('active') != null) {
                //     $this->settings_model->non_active_cms_faq($id);
                // }
                $this->session->set_flashdata('message', "CMS Faq Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms_faq");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cms_faq'] = $this->settings_model->getCmsFaqById($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_cms_faq', $this->data);
        }
    }


    // CMS Q&A POS //


    public function cms_faq_pos()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array(
            'link' => site_url('system_settings'),
            'page' => lang('system_settings')
        ), array('link' => '#', 'page' => "CMS Faq POS"));
        $meta = array('page_title' => 'CMS Faq POS', 'bc' => $bc);
        $this->page_construct('settings/cms_faq_pos', $meta, $this->data);
    }

    public function get_cms_faq_pos()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('cms_faq_pos')}.id as id,{$this->db->dbprefix('cms_faq_pos')}.menu,{$this->db->dbprefix('cms_faq_pos')}.title,{$this->db->dbprefix('cms_faq_pos')}.is_active as active")
            ->from("cms_faq_pos")
            ->edit_column("active", "$1__$2", 'id, active')
            ->where("({$this->db->dbprefix('cms_faq_pos')}.is_deleted !=1 OR {$this->db->dbprefix('cms_faq_pos')}.is_deleted IS NULL)")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_cms_faq_pos/$1') . "' class='tip' title='" . lang("show_&_edit") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> <a href='#' class='tip po' title='<b>Are You Sure Delete Template ?</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_cms_faq_pos/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

    public function delete_cms_faq_pos($id)
    {
        if ($this->settings_model->deleteCmsFaqPos($id)) {
            echo lang("brand_deleted");
        }
    }

    public function add_cms_faq_pos()
    {
        if ($this->isPost()) {
            $data = [
                'title' => $this->input->post('title'),
                'parent_id' => $this->input->post('parent_id'),
                'menu' => $this->input->post('menu'),
                'caption' => $this->input->post('caption'),
                'is_deleted' => 0,
                'is_active' => $this->input->post('active') != null ? '1' : '0',
                'created_by' => $this->session->userdata('user_id')
            ];
            try {
                $this->settings_model->add_cms_faq_pos($data);
                $this->session->set_flashdata('message', "Tambah FAQ Telah Berhasil");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms_faq_pos");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['category'] = $this->settings_model->getCategoryFaqPos();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_cms_faq_pos', $this->data);
        }
    }
    public function edit_cms_faq_pos($id)
    {
        if ($this->isPost()) {
            $data = [
                'title' => $this->input->post('title'),
                'parent_id' => $this->input->post('parent_id'),
                'menu' => $this->input->post('menu'),
                'caption' => $this->input->post('caption'),
                'is_deleted' => 0,
                'is_active' => $this->input->post('active') != null ? '1' : '0',
                'created_by' => $this->session->userdata('user_id')
            ];
            try {
                $this->settings_model->updateCmsFaqPos($id, $data);
                // if ($this->input->post('active') != null) {
                //     $this->settings_model->non_active_cms_faq($id);
                // }
                $this->session->set_flashdata('message', "CMS Faq Pos Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms_faq_pos");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['category'] = $this->settings_model->getCategoryFaqPos();
            $this->data['cms_faq_pos'] = $this->settings_model->getCmsFaqPosById($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_cms_faq_pos', $this->data);
        }
    }


    // ---------------------END Q&A------------------//

    // ---------------------Category Faq------------------//
    public function category_faq()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array(
            'link' => site_url('system_settings'),
            'page' => lang('system_settings')
        ), array('link' => '#', 'page' => "CMS Faq POS"));
        $meta = array('page_title' => 'Category Faq POS', 'bc' => $bc);
        $this->page_construct('settings/category_faq', $meta, $this->data);
    }

    public function get_category_faq()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('parent_menu_faq_pos')}.parent_id as id,{$this->db->dbprefix('parent_menu_faq_pos')}.menu,{$this->db->dbprefix('parent_menu_faq_pos')}.is_active as active")
            ->from("parent_menu_faq_pos")
            ->edit_column("active", "$1__$2", 'id, active')
            ->where("({$this->db->dbprefix('parent_menu_faq_pos')}.is_deleted !=1 OR {$this->db->dbprefix('parent_menu_faq_pos')}.is_deleted IS NULL)")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/edit_category_faq/$1') . "' class='tip' title='" . lang("show_&_edit") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> <a href='#' class='tip po' title='<b>Are You Sure Delete Template ?</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_category_faq/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

    public function add_category_faq()
    {
        if ($this->isPost()) {
            if ($_FILES['image']['name'] != '') {
                /*$this->load->library('upload');
                $config['upload_path'] = 'themes/default/assets/images/helps/';
                $config['allowed_types'] = 'gif|jpg|png';
                //$config['max_size'] = '500';
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('image')) {
                    $error = $this->upload->display_errors();
                    throw new Exception($error);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = 'themes/default/assets/images/helps/' . $photo;
                $config['new_image'] = 'themes/default/assets/images/helps/' . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = 150;
                $config['height'] = 150;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }*/
                $uploadedImg = $this->integration->upload_files($_FILES['image']);
                $photo      = $uploadedImg->url;
                $data = [
                    'menu' => $this->input->post('menu'),
                    'is_deleted' => 0,
                    'is_active' => $this->input->post('active') != null ? '1' : '0',
                    'created_by' => $this->session->userdata('user_id'),
                    'image' => $photo
                ];
            } else {
                $data = [
                    'menu' => $this->input->post('menu'),
                    'is_deleted' => 0,
                    'is_active' => $this->input->post('active') != null ? '1' : '0',
                    'created_by' => $this->session->userdata('user_id')
                ];
            }
            try {
                $this->settings_model->add_category_faq($data);
                $this->session->set_flashdata('message', "Tambah Category Telah Berhasil");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/category_faq");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_category_faq', $this->data);
        }
    }

    public function delete_category_faq($id)
    {
        if ($this->settings_model->deleteCategoryFaq($id)) {
            echo lang("brand_deleted");
        }
    }

    public function edit_category_faq($id)
    {
        if ($this->isPost()) {
            if ($_FILES['image']['name'] != '') {
                /*$this->load->library('upload');
                $config['upload_path'] = 'themes/default/assets/images/helps/';
                $config['allowed_types'] = 'gif|jpg|png';
                //$config['max_size'] = '500';
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('image')) {
                    $error = $this->upload->display_errors();
                    throw new Exception($error);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = 'themes/default/assets/images/helps/' . $photo;
                $config['new_image'] = 'themes/default/assets/images/helps/' . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = 150;
                $config['height'] = 150;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }*/
                $uploadedImg = $this->integration->upload_files($_FILES['image']);
                $photo = $uploadedImg->url;
                $data = [
                    'menu' => $this->input->post('menu'),
                    'is_deleted' => 0,
                    'is_active' => $this->input->post('active') != null ? '1' : '0',
                    'created_by' => $this->session->userdata('user_id'),
                    'image' => $photo
                ];
            } else {
                $data = [
                    'menu' => $this->input->post('menu'),
                    'is_deleted' => 0,
                    'is_active' => $this->input->post('active') != null ? '1' : '0',
                    'created_by' => $this->session->userdata('user_id')
                ];
            }
            try {
                $this->settings_model->updateCategoryFaq($id, $data);
                $this->session->set_flashdata('message', "Category Faq Pos Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/category_faq");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['category_faq'] = $this->settings_model->getCategoryFaqById($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_category_faq', $this->data);
        }
    }



    // ---------------------End Category Faq------------------//


    // ---------------------CMS------------------//
    public function cms()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => "CMS"));
        $meta = array('page_title' => "CMS", 'bc' => $bc);
        $this->page_construct('settings/cms', $meta, $this->data);
    }

    public function get_cms()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('cms_retail')}.id as id,{$this->db->dbprefix('cms_retail')}.name, {$this->db->dbprefix('cms_retail')}.is_active as active")
            ->from("cms_retail")
            ->edit_column("active", "$1__$2", 'id, active')
            ->where("({$this->db->dbprefix('cms_retail')}.is_deleted !=1 OR {$this->db->dbprefix('cms_retail')}.is_deleted IS NULL)")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/cms_detail/$1') . "' class='tip' title='" . lang("show_&_edit_content_cms") . "'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('system_settings/edit_cms_name/$1') . "' class='tip' title='" . lang("edit_cms_name") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>Are You Sure Delete Template ?</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_cms/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

    public function delete_cms($id)
    {
        if ($this->settings_model->deleteCms($id)) {
            echo lang("brand_deleted");
        }
    }

    public function add_cms_name()
    {
        if ($this->isPost()) {
            $data = [
                'name' => $this->input->post('name'),
                'is_deleted' => 0,
                'is_active' => $this->input->post('active') != null ? '1' : '0'
            ];
            try {
                $this->settings_model->add_cms($data);
                $this->session->set_flashdata('message', "Nama Cms Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_cms_name', $this->data);
        }
    }

    public function edit_cms_name($id = null)
    {
        if ($this->isPost()) {
            $data = [
                'name' => $this->input->post('name'),
                'is_active' => $this->input->post('active') != null ? '1' : '0',
            ];
            try {
                $this->settings_model->updateCms($id, $data);
                if ($this->input->post('active') != null) {
                    $this->settings_model->non_active_cms($id);
                }
                $this->session->set_flashdata('message', "Data Bank Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cms'] = $this->settings_model->getCmsById($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_cms_name', $this->data);
        }
    }

    public function cms_detail($id = null)
    {
        $this->data['cms'] = $this->settings_model->getCmsById($id);
        // print_r($this->data['cms']);die;
        $bc = array(array('link' => base_url(), 'page' => lang('Setting')), array('link' => '#', 'page' => lang('CMS')));
        $meta = array('page_title' => lang('CMS'), 'bc' => $bc);
        $this->page_construct('settings/cms_form', $meta, $this->data);
    }

    public function cms_update_header($id)
    {
        if ($this->isPost()) {
            $data = [
                'header_title' => $this->input->post('header-title'),
                'header_caption' => $this->input->post('header-caption')
            ];
            if ($_FILES['header-image']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('header-image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration->upload_files($_FILES['header-image']);
                $site_logo          = $uploadedImg->url;
                $data['header_bg'] = $site_logo;
            }

            if ($_FILES['logo-berwarna']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('logo-berwarna')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration->upload_files($_FILES['logo-berwarna']);
                $site_logo          = $uploadedImg->url;
                $data['logo_1']     = $site_logo;
            }

            if ($_FILES['logo-putih']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('logo-putih')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg    = $this->integration->upload_files($_FILES['logo-putih']);
                $site_logo      = $uploadedImg->url;
                $data['logo_2'] = $site_logo;
            }
            try {
                $this->settings_model->updateCms($id, $data);
                $this->session->set_flashdata('message', "Data Template Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms");
        }
    }

    public function cms_update_about($id)
    {
        if ($this->isPost()) {
            $data = [
                'about_title' => $this->input->post('about-title'),
                'about_caption' => $this->input->post('about-caption'),
            ];
            try {
                $this->settings_model->updateCms($id, $data);
                $this->session->set_flashdata('message', "Data Template Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms");
        }
    }

    public function cms_update_cara_penggunaan($id = '')
    {
        if ($this->isPost()) {
            $data = [
                'how_title' => $this->input->post('title-cara-penggunaan'),
                'how_title_1' => $this->input->post('cara-penggunaan-title-1'),
                'how_title_2' => $this->input->post('cara-penggunaan-title-2'),
                'how_title_3' => $this->input->post('cara-penggunaan-title-3'),
                'how_caption_1' => $this->input->post('caption-penggunaan-1'),
                'how_caption_2' => $this->input->post('caption-penggunaan-2'),
                'how_caption_3' => $this->input->post('caption-penggunaan-3')
            ];

            if ($_FILES['icon-penggunaan-1']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('icon-penggunaan-1')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error . '-1');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration->upload_files($_FILES['icon-penggunaan-1']);
                $site_logo          = $uploadedImg->url;
                $data['how_image_1'] = $site_logo;
            }

            if ($_FILES['icon-penggunaan-2']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('icon-penggunaan-2')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error . '-2');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration->upload_files($_FILES['icon-penggunaan-2']);
                $site_logo          = $uploadedImg->url;
                $data['how_image_2'] = $site_logo;
            }

            if ($_FILES['icon-penggunaan-3']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('icon-penggunaan-3')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error . '-3');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg        = $this->integration->upload_files($_FILES['icon-penggunaan-3']);
                $site_logo          = $uploadedImg->url;
                $data['how_image_3'] = $site_logo;
            }

            try {
                $this->settings_model->updateCms($id, $data);
                $this->session->set_flashdata('message', "Data Template Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms");
        }
    }

    public function cms_update_benefit($id)
    {
        if ($this->isPost()) {
            $data = [
                'benefit_title' => $this->input->post('title-keuntungan-element'),
                'benefit_title_1' => $this->input->post('benefit-title-1'),
                'benefit_caption_1' => $this->input->post('caption-keuntungan-1'),
                'benefit_title_2' => $this->input->post('benefit-title-2'),
                'benefit_caption_2' => $this->input->post('caption-keuntungan-2'),
                'benefit_title_3' => $this->input->post('benefit-title-3'),
                'benefit_caption_3' => $this->input->post('caption-keuntungan-3')
            ];

            if ($_FILES['icon-keuntungan-1']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('icon-keuntungan-1')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error . '-1');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg            = $this->integration->upload_files($_FILES['icon-keuntungan-1']);
                $site_logo              = $uploadedImg->url;
                $data['benefit_image_1'] = $site_logo;
            }

            if ($_FILES['icon-keuntungan-2']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('icon-keuntungan-2')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error . '-2');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg            = $this->integration->upload_files($_FILES['icon-keuntungan-2']);
                $site_logo              = $uploadedImg->url;
                $data['benefit_image_2'] = $site_logo;
            }

            if ($_FILES['icon-keuntungan-3']['size'] > 0) {
                /*$this->load->library('upload');
                $config['upload_path'] = $this->upload_path . 'cms/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('icon-keuntungan-3')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error . '-3');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $site_logo = $this->upload->file_name;*/
                $uploadedImg            = $this->integration->upload_files($_FILES['icon-keuntungan-3']);
                $site_logo              = $uploadedImg->url;
                $data['benefit_image_3'] = $site_logo;
            }

            try {
                $this->settings_model->updateCms($id, $data);
                $this->session->set_flashdata('message', "Data Template Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms");
        }
    }

    public function cms_update_footer($id)
    {
        if ($this->isPost()) {
            $data = [
                'footer_link_wa' => $this->input->post('link-wa'),
                'footer_link_fb' => $this->input->post('facebook'),
                'footer_link_twitter' => $this->input->post('twitter'),
                'footer_link_ig' => $this->input->post('instagram'),
                'footer_cs_wa' => $this->input->post('no-wa'),
                'footer_right' => $this->input->post('about-us-footer'),
            ];

            try {
                $this->settings_model->updateCms($id, $data);
                $this->session->set_flashdata('message', "Data Template Telah Tersimpan");
            } catch (\Throwable $th) {
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect("system_settings/cms");
        }
    }
    // shipment price group

    public function shipment_price_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $link_type = ['mb_shipment_price_groups'];
        $this->load->model('db_model');
        $get_link = $this->db_model->get_link_manualbook('api_integration', $link_type);
        foreach ($get_link as $val) {
            $this->data[$val->type] = $val->uri;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('price_groups')));
        $meta = array('page_title' => lang('price_groups'), 'bc' => $bc);
        $this->page_construct('settings/shipment_price_groups', $meta, $this->data);
    }

    public function get_shipment_price_groups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('shipment_price_group')}.id as price_id, {$this->db->dbprefix('shipment_price_group')}.name")
            ->from("shipment_price_group")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/shipment_product_prices/$1') . "' class='tip' title='" . lang("group_product_prices") . "'><i class=\"fa fa-eye\"></i></a>  <a href='" . site_url('system_settings/edit_shipment_price_group/$1') . "' class='tip' title='" . lang("edit_price_group") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <!-- <a href='#' class='tip po' title='<b>" . lang("delete_price_group") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('system_settings/delete_price_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> --> </div>", "price_id");
        if (!$this->Owner) {
            $this->datatables->where('shipment_price_group.company_id', $this->session->userdata('company_id'));
        }
        $this->datatables->where('shipment_price_group.is_deleted IS NULL');
        echo $this->datatables->generate();
    }

    public function add_shipment_price_group()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('name', lang("group_name"), 'trim|is_unique[price_groups.name]|required|alpha_numeric_spaces');
            $this->data['warehouses'] = $this->site->getAllWarehouses();

            if ($this->form_validation->run() == true) {
                $data = [
                    'name' => $this->input->post('name'),
                    'company_id' => $this->session->userdata('company_id'),
                ];
            } elseif ($this->input->post('add_price_group')) {
                throw new \Exception(validation_errors());
                // $this->session->set_flashdata('error', validation_errors());
                // redirect("system_settings/price_groups");
            }

            if ($this->form_validation->run() == true && $this->settings_model->addShipmentPriceGroup($data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("price_group_added"));
                redirect("system_settings/shipment_price_groups");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/add_shipment_price_group', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function edit_shipment_price_group($id = null)
    {
        $this->db->trans_begin();

        try {
            $this->form_validation->set_rules('name', lang("group_name"), 'trim|required|alpha_numeric_spaces');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $pg_details = $this->settings_model->getShipmentPriceGroupByID($id);
            if ($this->input->post('name') != $pg_details->name) {
                $this->form_validation->set_rules('name', lang("group_name"), 'is_unique[price_groups.name]');
            }

            if ($this->form_validation->run() == true) {
                $data = array(
                    'name' => $this->input->post('name'),
                );
            } elseif ($this->input->post('edit_price_group')) {
                throw new \Exception(validation_errors());
            }

            if ($this->form_validation->run() == true && $this->settings_model->updateShipmentPriceGroupByID($id, $data)) {
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("price_group_updated"));
                redirect("system_settings/shipment_price_groups");
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['price_group'] = $pg_details;
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_shipment_price_group', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function shipment_product_prices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['price_group'] = $this->settings_model->getShipmentPriceGroupByID($group_id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),  array('link' => site_url('system_settings/price_groups'), 'page' => lang('price_groups')), array('link' => '#', 'page' => lang('group_product_prices')));
        $meta = array('page_title' => lang('shipment_product_prices'), 'bc' => $bc);
        $this->page_construct('settings/shipment_product_prices', $meta, $this->data);
    }

    public function get_shipment_product_prices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $pp = "( SELECT {$this->db->dbprefix('shipment_product_price')}.product_id as product_id, {$this->db->dbprefix('shipment_product_price')}.price_pickup as price_pickup, {$this->db->dbprefix('shipment_product_price')}.price_delivery as price_delivery FROM {$this->db->dbprefix('shipment_product_price')} WHERE shipment_price_group_id = {$group_id} ) PP";
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as product_code, {$this->db->dbprefix('products')}.name as product_name, PP.price_pickup as price_pickup, PP.price_delivery as price_delivery,  unit.name as default_unit, {$this->db->dbprefix('units')}.name as unit_name, {$this->db->dbprefix('products')}.price as product_price")
            ->from("products")
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->join('sma_units', 'sma_products.sale_unit = sma_units.id', 'inner')
            ->join('sma_units as unit', 'sma_products.unit = unit.id', 'inner')
            ->edit_column("price_pickup", "$1__$2__$3__$4", 'id, price_pickup,default_unit, product_price')
            ->edit_column("price_delivery", "$1__$2__$3__$4", 'id, price_delivery,default_unit, product_price')
            ->add_column("Actions", "<div class=\"text-center\"><button class=\"btn btn-primary btn-xs form-submit\" type=\"button\"><i class=\"fa fa-pencil\"></i></button></div>", "id");

        if (!$this->Owner) {
            $this->datatables->where("products.company_id", $this->session->userdata('company_id'));
        }

        $this->datatables->where("products.is_deleted IS NULL");
        echo $this->datatables->generate();
    }

    public function update_shipment_product_price($group_id = null)
    {
        if (!$group_id) {
            $this->sma->send_json(array('status' => 0));
        }

        // print_r($this->input->post());die;
        $product_id = $this->input->post('product_id', true);
        $price_pickup = $this->input->post('price_pickup', true);
        $price_delivery = $this->input->post('price_delivery', true);
        // var_dump(empty($price), $price);die;
        if (!empty($product_id)) {
            if ($this->settings_model->setProductPriceForShipmentPriceGroup($product_id, $group_id, $price_pickup, $price_delivery)) {
                $this->sma->send_json(array('status' => 1));
            }
        }
        $this->sma->send_json(array('status' => 0));
    }

    public function shipment_product_prices_actions($group_id)
    {
        // echo "asd";die;
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->setProductPriceForShipmentPriceGroup($id, $group_id, $this->input->post('price_pickup' . $id), $this->input->post('price_delivery' . $id));
                    }
                    $this->session->set_flashdata('message', lang("products_group_price_updated"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteShipmentProductGroupPrice($id, $group_id);
                    }
                    $this->session->set_flashdata('message', lang("products_group_price_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->setActiveSheetIndex(0);
                    $sheet->setTitle(lang('tax_rates'))
                        ->SetCellValue('A1', lang('product_code'))
                        ->SetCellValue('B1', lang('product_name'))
                        ->SetCellValue('C1', lang('price_pickup'))
                        ->SetCellValue('D1', lang('price_delivery'))
                        ->SetCellValue('E1', lang('group_name'));
                    $row = 2;
                    $group = $this->settings_model->getShipmentPriceGroupByID($group_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getShipmentProductGroupPriceByPID($id, $group_id);
                        $sheet->SetCellValue('A' . $row, $pgp->code)
                            ->SetCellValue('B' . $row, $pgp->name)
                            ->SetCellValue('C' . $row, $pgp->price_pickup)
                            ->SetCellValue('D' . $row, $pgp->price_delivery)
                            ->SetCellValue('E' . $row, $group->name);
                        $row++;
                    }
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $sheet->getColumnDimension('B')->setWidth(30);
                    $sheet->getColumnDimension('C')->setWidth(15);
                    $sheet->getColumnDimension('D')->setWidth(15);
                    $sheet->getColumnDimension('E')->setWidth(15);
                    $sheet->getParent()->getDefaultStyle()->getAlignment()->applyFromArray(['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]);
                    $filename = 'shipment_price_groups_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_price_group_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function distributor_payment_method()
    {
        $bc = array(array('link' => base_url(), 'page' => lang('Setting')), array('link' => '#', 'page' => lang('distributor_payment_method')));
        $meta = array('page_title' => lang('distributor_payment_method'), 'bc' => $bc);
        $this->page_construct('settings/payment_method', $meta, $this->data);
    }

    public function get_payment_method()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }

        $this->load->library('datatables');
        $this->datatables
            ->select("sma_users.username, sma_companies.name, sma_companies.company, sma_companies.id")
            ->from("sma_companies")
            ->join("sma_users", "sma_users.company_id = sma_companies.id AND sma_users.group_id = 2")
            ->where('sma_companies.group_name', 'biller')
            ->where('sma_companies.client_id IS NULL')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/list_payment_method/$1') . "' class='tip' title='" . lang("edit_payment_methode") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a></div>", "sma_companies.id");
        //->unset_column('id');
        // '<a href="' . site_url('system_settings/list_payment_method/$1') . '" data-toggle="modal" data-target="#myModal"  data-backdrop="static"><span class="label label-success"><i class="fa fa-check"></i> ' . lang("edit_payment_methode") . '</span></a>'

        echo $this->datatables->generate();
    }

    public function list_payment_method($company_id)
    {
        if ($this->input->post()) {

            if ($this->input->post('active')) {
                $this->db->trans_begin();
                try {
                    if (!$this->settings_model->updateCompanyPaymentMethodByCompanyId($company_id, ['is_active' => 0])) {
                        throw new \Exception("Data Gagal Disimpan");
                    }
                    foreach ($this->input->post('active') as $payment_method_id => $value) {
                        $cek = $this->settings_model->getPaymentMethodByCompanyIdAndPaymentMethodId($company_id, $payment_method_id);
                        if ($cek) {
                            if (!$this->settings_model->updateCompanyPaymentMethodByCompanyIdAndPaymentMethodId($company_id, $payment_method_id, ['is_active' => 1])) {
                                throw new \Exception("Data Gagal Disimpan");
                            }
                        } else {
                            $data = [
                                'company_id' => $company_id,
                                'payment_method_id' => $payment_method_id,
                                'is_active' => 1
                            ];
                            // print_r($data);
                            $this->settings_model->insertCompanyPaymentMethod($data);
                        }
                    }
                    $this->session->set_flashdata('message', 'Berhasil menambah item');
                    $this->db->trans_commit();
                } catch (\Throwable $th) {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('error', $th->getMessage());
                }
            } else {
                $this->session->set_flashdata('error', '');
            }
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['company'] = $this->site->getCompanyByID($company_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['payment_method'] = $this->site->getAllPaymentMethod();
            $this->data['company_payment_method'] = $this->settings_model->getCompanyPaymentMethodByCompanyId($company_id);
            $this->load->view($this->theme . 'settings/list_payment_method', $this->data);
        }
    }

    public function promotion_aksestoko()
    {
        // if (!$this->sma->checkPermissions()) {
        //     $this->session->set_flashdata('warning', lang('access_denied'));
        //     $this->sma->md();
        // }


        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('Promotion')));
        $meta = array('page_title' => 'Hot News', 'bc' => $bc);
        $this->page_construct('settings/promotion', $meta, $this->data);
    }

    public function add_promotion_aksestoko($value = '')
    {
        // if (!$this->sma->checkPermissions()) {
        //     $this->session->set_flashdata('warning', lang('access_denied'));
        //     $this->sma->md();
        // }

        $this->db->trans_begin();
        try {
            // $this->sma->checkPermissions(false, true);

            $this->data['options_region'] = array(
                '1' => 'Region 1',
                '2' => 'Region 2',
                '3' => 'Region 3',
            );

            if ($this->isPost()) {
                $data = [
                    'type_news' => $this->input->post('type_news'),
                    'min_pembelian' => $this->input->post('min_pembelian'),
                    'value' => $this->input->post('value'),
                    'supplier_id' => $this->session->userdata('company_id'),
                    'supplier' => $this->session->userdata('company_name'),
                    'name' => $this->input->post('name'),
                    'description' => strip_tags($this->input->post('description')),
                    'code_promo' => $this->input->post('card_no'),
                    'start_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->input->post('start_date')))),
                    'end_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->input->post('end_date')))),
                    'created_by' => $this->session->userdata('user_id'),
                    'quota' => $this->input->post('quota'),
                    'region' => '0', //$this->input->post('region'),
                    'tipe' => $this->input->post('tipe'),
                    'max_total_disc' => $this->input->post('max_discount'),
                    'max_toko' => $this->input->post('max_tiap_toko'),
                    'status' => (!$this->Principal) ? '0' : '1',
                ];

                if ($this->Principal) {
                    $data['is_popup'] = $this->input->post('is_popup') ? 1 : null;
                    $data['video_popup'] = $this->input->post('video_popup') ? $this->input->post('video_popup') : null;
                    $data['supplier_id'] = 0;
                    $data['supplier'] = 'Principal';
                    if ($_FILES['img_popup']['size'] > 0) {
                        if ($_FILES['img_popup']['size'] > 2097152) {
                            throw new \Exception("Ukuran File melebihi 2 MB");
                        } else {
                            /*$image_p = base64_encode(file_get_contents($_FILES['img_popup']["tmp_name"]));
                            $uploadedImg_p = json_decode($this->site->uploadImage($image_p));*/
                            $uploadedImg_p = $this->integration->upload_files($_FILES['img_popup']);
                            if ($uploadedImg_p) {
                                // $data['image_popup'] = $uploadedImg_p->data->image->url;
                                $data['image_popup'] = $uploadedImg_p->url;
                            } else {
                                throw new \Exception("Gagal mengunggah gambar image pop-up");
                            }
                        }
                    }
                    // $data['status'] = '1';
                }

                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path . '/promotion/';
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }

                    $photo = '/promotion/' . $this->upload->file_name;
                    $data['url_image'] = $photo;*/

                    /*$image = base64_encode(file_get_contents($_FILES['userfile']["tmp_name"]));
                    $uploadedImg = json_decode($this->site->uploadImage($image));*/
                    $uploadedImg = $this->integration->upload_files($_FILES['userfile']);
                    if ($uploadedImg) {
                        // $data['url_image'] = $uploadedImg->data->image->url;
                        $data['url_image'] = $uploadedImg->url;
                    } else {
                        throw new \Exception("Gagal mengunggah gambar");
                    }
                }

                if ($this->sales_model->add_promotion($data)) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("promotion_aded"));
                    redirect("system_settings/promotion_aksestoko/");
                }
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['users'] = $this->sales_model->getStaff();
                $this->data['page_title'] = lang("new_gift_card");
                $this->load->view($this->theme . 'settings/add_promotion_aksestoko', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function getPromotionsAksestoko()
    {
        $this->load->library('datatables');

        $this->datatables
            ->select("id, name, type_news, supplier, code_promo , start_date, end_date, quota, max_toko, status", false)
            ->from("promo");
        if (!$this->Principal) {
            $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('system_settings/view_promotion_aksestoko/$1') . "' class='tip' title='" . lang("view_news") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('system_settings/edit_promotion_aksestoko/$1') . "' class='tip' title='" . lang("edit_news") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("disable_news") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger promo-delete' href='" . site_url('system_settings/delete_promo/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        } else {
            $this->datatables->add_column("Actions", "<div class=\"text-center\" style=\"width: 100px;\"><a href='" . site_url('system_settings/view_promotion_aksestoko/$1') . "' class='tip' title='" . lang("view_news") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('system_settings/add_promotion_toko/$1') . "' class='tip' title='" . lang("list_store") . "'><i class=\"fa fa-gift\"></i></a> <a href='" . site_url('system_settings/edit_promotion_aksestoko/$1') . "' class='tip' title='" . lang("edit_news") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("disable_news") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger promo-delete' href='" . site_url('system_settings/delete_promo/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a><a href='#' class='tip po' title='<b>" . lang("active_news") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger promo-delete' href='" . site_url('system_settings/active_promo_aksestoko/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> </a><a href='" . site_url('system_settings/send_notification_promo/$1') . "' class='tip' title='" . lang("send_notification") . "'><i class=\"fa fa-bell\"></i></a> </div>", "id");
        }
        if (!$this->Principal) {
            $this->datatables->where("{$this->db->dbprefix('promo')}.supplier_id", $this->session->userdata('company_id'));
        }
        echo $this->datatables->generate();
    }

    public function add_promotion_toko($id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['id'] = $promo_id = $id;

        $this->data['detail_promo'] = $this->promo_model->getPromoDetail($promo_id);
        $this->data['distributor']  = $this->companies_model->getCompanyByID($this->data['detail_promo']->supplier_id);

        $data_toko_selected = $this->user_promotion_model->getUserPromotions($promo_id);
        $this->data['list_toko_selected'] = [];
        $this->data['list_company_id_toko_selected'] = [];
        foreach ($data_toko_selected as $row) {
            $this->data['list_company_id_toko_selected'][] = $row['company_id'] . '~' . $row['company'] . '~' . $row['supplier_id'];
            $this->data['list_toko_selected'][] = ['company_id' => $row['company_id'], 'company' => $row['company'], 'supplier_id' => $row['supplier_id']];
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('add_promotion_toko')));
        $meta = array('page_title' => lang('add_promotion_toko'), 'bc' => $bc);
        $this->page_construct('settings/add_promotion_toko', $meta, $this->data);
    }

    public function send_notification_promo($id = null)
    {
        $this->sma->checkPermissions();

        try {
            $this->db->trans_begin();

            $detail_promo         = $this->promo_model->getPromoDetail($id);
            $data_toko_selected   = $this->user_promotion_model->getUserPromotions($id);

            if ($detail_promo->status != 1) {
                throw new \Exception(lang("cannot_active"));
            }

            $j = 0;
            foreach ($data_toko_selected as $row) {
                $cf1    = explode("IDC-", $row['cf1'], 2);
                $users  = $this->site->findUserByIdBk($cf1[1]);

                $message = $this->site->makeMessage('sms_notif_promo', [
                    'store'         => $row['company'] . ' (' . $cf1[1] . ')',
                    'value'         => $detail_promo->tipe ? 'Rp.' . number_format($detail_promo->value, 0, ".", ".") : (int)$detail_promo->value . ' %',
                    'min_pembelian' => number_format($detail_promo->min_pembelian, 0, ".", "."),
                    'kode_voucher'  => $detail_promo->code_promo,
                    'start_date'    => date('d F Y', strtotime($detail_promo->start_date)),
                    'end_date'      => date('d F Y', strtotime($detail_promo->start_date))
                ]);
                
                $notification   = [
                    'title' => $detail_promo->type_news = 'promo ' ? 'AksesToko - Promo' : 'AksesToko - Informasi',
                    'body'  => $detail_promo->type_news = 'promo ' ? $message : $detail_promo->description
                ];

                $data = [
                    'click_action'   => 'FLUTTER_NOTIFICATION_CLICK',
                    'title'          => $detail_promo->type_news = 'promo ' ? 'AksesToko - Promo' : 'AksesToko - Informasi',
                    'body'           => $detail_promo->type_news = 'promo ' ? $message : $detail_promo->description,
                    'type'           => $detail_promo->type_news = 'promo ' ? 'sms_notif_promo' : 'sms_notif_promo',
                    'id_promo'       => $id,
                    'code_promo'     => $detail_promo->type_news = 'promo ' ? $detail_promo->code_promo : null,
                    'tanggal'        => date('d/m/Y'),
                ];

                $notifikasi_atmobiel = $this->integration->notification_atmobile($notification, $data, $users->id);
                if ($notifikasi_atmobiel->success == 0 || $notifikasi_atmobiel->failure == '1') {
                    $j += 1;
                }
            }
            
            if ($j == count($data_toko_selected)) {
                $this->session->set_flashdata('error', lang("sending_failed"));
            } else if ($j != count($data_toko_selected) && $j <= 1) {
                $this->session->set_flashdata('warning', lang("sent") . (count($data_toko_selected) - $j) . lang("notifi") . count($data_toko_selected) . lang("selecte"));
            } else {
                $this->session->set_flashdata('message', lang("mobile_success"));
            }
            $this->db->trans_commit();
            redirect($_SERVER["HTTP_REFERER"]);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function getAllCustomers()
    {
        ini_set('memory_limit', '4096M');

        $this->sma->checkPermissions('index');

        $provinsi       = $this->input->get('provinsi');
        $kabupaten      = $this->input->get('kabupaten');
        $company_id     = $this->input->get('company_id');
        $promo_id       = $this->input->get('promo_id');
        $detail_promo   = $this->promo_model->getPromoDetail($promo_id);

        $this->db->save_queries = true;

        $this->load->library('datatables');
        $this->datatables->select("CONCAT(sma_companies.id, CONCAT(CONCAT('~', sma_companies.company), CONCAT('~', sma_companies.company_id))), sma_companies.company, sma_companies.name, css.company as distributor, sma_companies.phone, sma_companies.cf1")
            ->from("companies")
            ->join('companies css', "css.id = sma_companies.company_id")
            ->where('sma_companies.group_name =', 'customer')
            ->where('sma_companies.cf1 != ""')
            ->where('sma_companies.cf1 IS NOT NULL')
            ->where('(sma_companies.client_id IS NULL OR sma_companies.client_id != "aksestoko")')
            ->where('sma_companies.id != sma_companies.company_id')
            ->where('sma_companies.is_deleted IS NULL');

        if ($provinsi)
            $this->datatables->like('sma_companies.country', $provinsi);

        if ($kabupaten)
            $this->datatables->like('sma_companies.city', $kabupaten);

        if ($detail_promo->supplier_id > 0)
            $company_id = $detail_promo->supplier_id;

        if ($company_id)
            $this->datatables->where('sma_companies.company_id', $company_id);

        echo $this->datatables->generate();
    }

    public function getDistributorPerdaerah($provinsi = null, $kabupaten = null, $term = null, $limit = 10)
    {
        $this->sma->checkPermissions('index');

        $provinsi       = $this->input->get('provinsi');
        $kabupaten      = $this->input->get('kabupaten');
        $term           = $this->input->get('term');
        $limit          = $this->input->get('limit') ?? 10;

        $query = $this->companies_model->getDistributorPerdaerah($provinsi, $kabupaten, $term, $limit);
        if (count($query) > 0) {
            $rows['results'][] = ['id' => '', 'text' => 'Choose Distributor'];
            foreach ($query as $row) {
                $rows['results'][] = ['id' => $row['id'], 'text' => $row['company'] . ($row['cf1'] ? " (" . $row['cf1'] . ")" : '')];
            }
        } else {
            $rows['results'][] = ['id' => '', 'text' => 'No Match Found'];
        }

        $this->sma->send_json($rows);
    }

    public function save_selected_toko($promo_id = null)
    {
        try {
            $this->db->trans_begin();
            $list_toko = $this->input->post('list_toko');
            $data = [
                "updated_at" => date('Y-m-d H:i:s'),
                "updated_by" => $this->session->userdata('user_id'),
                "is_deleted" => 1
            ];
            $this->user_promotion_model->setDeleteUserPromotion($promo_id, $data);
            foreach ($list_toko as $row) {
                $company_id    = explode('~', $row)[0];
                $supplier_id    = explode('~', $row)[1];
                if ($this->user_promotion_model->checkUserPromotion($promo_id, $company_id, $supplier_id)) {
                    if (!$this->user_promotion_model->updateUserPromotion($promo_id, $company_id, $supplier_id, ['is_deleted' => null]))
                        throw new \Exception("Failed to update the entire list of registered stores 401", 401);
                } else {
                    $data = [
                        "promo_id"   => $promo_id,
                        "company_id" => $company_id,
                        "supplier_id" => $supplier_id,
                        "created_by" => $this->session->userdata('user_id'),
                        "updated_by" => $this->session->userdata('user_id'),
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ];
                    if (!$this->user_promotion_model->addUserPromotion($data))
                        throw new \Exception("Failed to update the entire list of registered stores 402", 402);
                }
            }

            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang("user_promotions_updated"));
            redirect("system_settings/add_promotion_toko/" . $promo_id);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view_promotion_aksestoko($id)
    {
        $this->data['page_title'] = lang('promotion');
        $promotion = $this->site->getPromotionById($id);
        $this->data['promotion'] = $promotion;
        $this->load->view($this->theme . 'settings/view_promotion_aksestoko', $this->data);
    }

    public function edit_promotion_aksestoko($id)
    {
        // $this->sma->checkPermissions(false, true);
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
            if ($this->isPost()) {
                $data = [
                    'supplier_id' => $this->session->userdata('company_id'),
                    'supplier' => $this->session->userdata('company_name'),
                    'name' => $this->input->post('name'),
                    'description' => strip_tags($this->input->post('description')),
                    'start_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->input->post('start_date')))),
                    'end_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->input->post('end_date')))),
                    'created_by' => $this->session->userdata('user_id'),
                    'region' => $this->input->post('region'),
                    'max_total_disc' => $this->input->post('max_discount'),
                ];

                if ($this->Principal) {
                    $data['is_popup'] = $this->input->post('is_popup') ? 1 : null;
                    $data['video_popup'] = $this->input->post('video_popup') ? $this->input->post('video_popup') : null;
                    $data['supplier_id'] = 0;
                    $data['supplier'] = 'Principal';
                    if ($_FILES['img_popup']['size'] > 0) {
                        if ($_FILES['img_popup']['size'] > 2097152) {
                            throw new \Exception("Ukuran File melebihi 2 MB");
                        } else {
                            /*$image_p = base64_encode(file_get_contents($_FILES['img_popup']["tmp_name"]));
                            $uploadedImg_p = json_decode($this->site->uploadImage($image_p));*/
                            $uploadedImg_p = $this->integration->upload_files($_FILES['img_popup']);
                            if ($uploadedImg_p) {
                                // $data['image_popup'] = $uploadedImg_p->data->image->url;
                                $data['image_popup'] = $uploadedImg_p->url;
                            } else {
                                $this->session->set_flashdata('error', "Gagal mengunggah gambar");
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                    }
                    // $data['status'] = '1';
                }
                if ($_FILES['userfile']['size'] > 0) {
                    /*$this->load->library('upload');
                    $config['upload_path'] = $this->upload_path . '/promotion/';
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }

                    $photo = '/promotion/' . $this->upload->file_name;
                    $data['url_image'] = $photo;*/

                    /*$image = base64_encode(file_get_contents($_FILES['userfile']["tmp_name"]));
                    $uploadedImg = json_decode($this->site->uploadImage($image));*/
                    $uploadedImg = $this->integration->upload_files($_FILES['userfile']);

                    if ($uploadedImg) {
                        // $data['url_image'] = $uploadedImg->data->image->url;
                        $data['url_image'] = $uploadedImg->url;
                    } else {
                        $this->session->set_flashdata('error', "Gagal mengunggah gambar");
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                }

                if ($this->sales_model->edit_promotion($id, $data)) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("promotion_updated"));
                    redirect("system_settings/promotion_aksestoko/");
                }
            } else {
                $this->data['options_region'] = array(
                    '1'       => 'Region 1',
                    '2'   => 'Region 2',
                    '3'       => 'Region 3',
                );
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['promotion'] = $this->site->getPromotionById($id);
                // print_r($this->data['promotion']);die;
                $this->data['id'] = $id;
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'settings/edit_promotion_aksestoko', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_promo($id = null)
    {
        // $this->sma->checkPermissions();

        if ($this->sales_model->delete_promotion($id)) {
            echo lang("promotion_disabled");
        }
    }

    public function active_promo_aksestoko($id = null)
    {
        // $this->sma->checkPermissions();
        if (!$this->Principal) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->sales_model->active_promotion($id)) {
            echo lang("promotion_activated");
        }
    }

    public function updates_notif()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates_notif')));
        $meta = array('page_title' => lang('updates_notif'), 'bc' => $bc);
        $this->page_construct('settings/updates_notif', $meta, $this->data);
    }
    public function get_updates_notif()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("id, type, name, link, version, version_num, release_at, is_active")
            ->from("sma_updates")
            ->add_column("Actions", "<div class=\"text-center\">
                <a href='" . site_url('system_settings/view_updates_notif/$1') . "' class='tip' title='" . lang("view_updates_notif") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> 
                <a href='" . site_url('system_settings/edit_updates_notif/$1') . "' class='tip' title='" . lang('edit_updates_notif') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-pencil\"></i></a>
            </div>", "id");
        echo $this->datatables->generate();
    }
    public function add_updates_notif()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'type' => $this->input->post('type'),
                    'name' => $this->input->post('name'),
                    'version' => $this->input->post('version'),
                    'version_num' => $this->input->post('version_num'),
                    'link' => $this->input->post('link'),
                    'release_at' => $this->sma->fld($this->input->post('release_at')),
                    'desc' => $this->input->post('desc'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0
                ];
                $id = $this->settings_model->addUpdatesNotif($data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("updates_notif_added"));
                redirect($_SERVER['HTTP_REFERER']);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_updates_notif', $this->data);
        }
    }
    public function edit_updates_notif($updates_notif_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['updates_notif'] = $this->settings_model->getUpdatesNotifByID($updates_notif_id);
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'type' => $this->input->post('type'),
                    'name' => $this->input->post('name'),
                    'version' => $this->input->post('version'),
                    'version_num' => $this->input->post('version_num'),
                    'link' => $this->input->post('link'),
                    'release_at' => $this->sma->fld($this->input->post('release_at')),
                    'desc' => $this->input->post('desc'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0
                ];
                if (!$this->settings_model->updateUpdatesNotif($updates_notif_id, $data)) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("updates_notif_edited"));
                redirect($_SERVER["HTTP_REFERER"]);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_updates_notif', $this->data);
        }
    }
    public function view_updates_notif($updates_notif_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['updates_notif'] = $this->settings_model->getUpdatesNotifByID($updates_notif_id);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'settings/view_updates_notif', $this->data);
    }

    public function feedback()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('feedback_question')));
        $meta = array('page_title' => lang('feedback_question'), 'bc' => $bc);
        $this->page_construct('settings/feedback', $meta, $this->data);
    }

    public function getFeedback()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("sma_feedback_question.id as id, sma_feedback_question.question, sma_feedback_category.category, sma_feedback_question.is_active")
            ->from("sma_feedback_category, sma_feedback_question")
            ->where("sma_feedback_question.category_id = sma_feedback_category.id")
            ->add_column("Actions", "<div class=\"text-center\">
                <a href='" . site_url('system_settings/edit_feedback/$1') . "' class='tip' title='" . lang('edit_feedback_statement') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-pencil\"></i></a>
            </div>", "id");
        echo $this->datatables->generate();
    }

    public function add_feedback()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'category_id'   => $this->input->post('category'),
                    'type'          => $this->input->post('type'),
                    'question'      => $this->input->post('question'),
                    'is_active'     => $this->input->post('is_active') == 'on' ? 1 : 0
                ];
                $id = $this->settings_model->addFeedback($data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();

                $data = [];
                $option = $this->input->post('option');
                for ($i = 0; $i < count($this->input->post('option')); $i++) {
                    $question = $this->settings_model->getFeedbackLastId();
                    $data = [
                        'question_id' => $question->id,
                        'option' => $option[$i],
                        'is_active' => 1
                    ];

                    $id = $this->settings_model->addOptions($data);
                    if (!$id)
                        throw new \Exception('Failed');
                }

                $this->db->trans_commit();

                $this->session->set_flashdata('message', lang("question_added"));
                redirect('system_settings/feedback');
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect('system_settings/feedback');
            }
        } else {
            $this->data['category'] = $this->settings_model->getFeedbackCategoryList();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_feedback', $this->data);
        }
    }

    public function edit_feedback($question_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['category'] = $this->settings_model->getFeedbackCategoryList();
        $this->data['feedback'] = $this->settings_model->getFeedbackByID($question_id);
        $this->data['disabler'] = $this->settings_model->getResponseByQuestion($question_id);
        $this->data['options']  = $this->settings_model->getOptionsByID(['question_id' => $question_id, 'is_deleted !=' => 1]);
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'category_id'   => $this->input->post('category'),
                    'type'          => $this->input->post('type'),
                    'question'      => $this->input->post('question'),
                    'is_active'     => $this->input->post('is_active') == 'on' ? 1 : 0
                ];
                $id = $this->settings_model->updateFeedback($question_id, $data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();

                $data       = [];
                $option     = $this->input->post('option');
                $option_id  = $this->input->post('option_id');

                $is_deleted = $this->settings_model->deleteAllOptionsWithQuestionID($question_id);
                for ($i = 0; $i < count($this->input->post('option')); $i++) {
                    if ($option_id && $option_id[$i] && $this->settings_model->getOptionsByID(['id' => $option_id[$i]])) {
                        $data = [
                            'option' => $option[$i],
                            'is_active' => 1,
                            'is_deleted' => null,
                        ];
                        $id = $this->settings_model->updateOptions($option_id[$i], $data);
                    } else {
                        $data = [
                            'question_id' => $question_id,
                            'option' => $option[$i],
                            'is_active' => 1
                        ];

                        $id = $this->settings_model->addOptions($data);
                        if (!$id)
                            throw new \Exception('Failed');
                    }
                }

                $this->db->trans_commit();

                $this->session->set_flashdata('message', lang("feedback_question_added"));
                redirect('system_settings/feedback');
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect('system_settings/feedback');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_feedback', $this->data);
        }
    }

    public function feedback_category()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('feedback_category')));
        $meta = array('page_title' => lang('feedback_category'), 'bc' => $bc);
        $this->page_construct('settings/feedback_category', $meta, $this->data);
    }

    public function getFeedbackCategory()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("id, category, is_active, flag")
            ->from("sma_feedback_category")
            ->add_column("Actions", "<div class=\"text-center\">
                <a href='" . site_url('system_settings/view_feedback_category/$1') . "' class='tip' title='" . lang("view_feedback_category") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-eye\"></i></a> 
                <a href='" . site_url('system_settings/edit_feedback_category/$1') . "' class='tip' title='" . lang('edit_feedback_category') . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-pencil\"></i></a>
            </div>", "id");
        echo $this->datatables->generate();
    }

    public function add_feedback_category()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                if ($this->input->post('is_active') == 'on' && $this->input->post('flag')) {
                    $this->settings_model->setNonActiveFeedbackCategoryAT();
                } elseif ($this->input->post('is_active') == 'on') {
                    $this->settings_model->setNonActiveFeedbackCategory();
                }

                $data = [
                    'category'  => $this->input->post('category'),
                    'is_active' => $this->input->post('is_active') == 'on' ? 1 : 0,
                    'flag'      => $this->input->post('flag') ? 1 : 0,
                ];
                $id = $this->settings_model->addFeedbackCategory($data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("feedback_category_added"));
                redirect('system_settings/feedback_category');
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect('system_settings/feedback_category');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_feedback_category', $this->data);
        }
    }

    public function edit_feedback_category($category_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['category'] = $category = $this->settings_model->getFeedbackCategoryByID($category_id);
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                if ($this->input->post('is_active') == 'on' && $this->input->post('flag')) {
                    $this->settings_model->setNonActiveFeedbackCategoryAT();
                } elseif ($this->input->post('is_active') == 'on') {
                    $this->settings_model->setNonActiveFeedbackCategory();
                }
                $data = [
                    'category' => $this->input->post('category'),
                    'repeat' => $this->input->post('repeat') == 'on' ? $category->repeat + 1 : $category->repeat,
                    'is_active' => $this->input->post('is_active') == 'on' ? 1 : 0,
                    'flag'      => $this->input->post('flag') ? 1 : 0,
                ];
                $id = $this->settings_model->updateFeedbackCategory($category_id, $data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("feedback_category_edited"));
                redirect('system_settings/feedback_category');
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect('system_settings/feedback_category');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_feedback_category', $this->data);
        }
    }
    public function view_feedback_category($category_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['category'] = $this->settings_model->getFeedbackCategoryByID($category_id);
        $this->data['question'] = $this->settings_model->getFeedbackByCategory($category_id);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'settings/view_feedback_category', $this->data);
    }

    public function term_payment_kreditpro()
    {
        $this->db->trans_begin();
        try {
            $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
            if ($this->isPost()) {


                $cek = $this->settings_model->getTermKreditProByCompanyIdAndTerm($this->session->userdata('company_id'), $this->input->post('duration'));
                if ($this->input->post('is_active') == 'false') {
                    $cekJumlahAktif = $this->settings_model->getActiveTermKreditProByCompanyId($this->session->userdata('company_id'));
                    if (count($cekJumlahAktif) <= 1 && $cek[0]->is_active == 1) {
                        $this->sma->send_json(array('status' => 0));
                        exit();
                    }
                }
                $data = [
                    'term' => $this->input->post('duration'),
                    'company_id' => $this->session->userdata('company_id'),
                    'created_by' => $this->session->userdata('user_id'),
                    'updated_by' => $this->session->userdata('user_id'),
                    'is_active' => $this->input->post('is_active') == 'true' ? 1 : 0
                ];
                if (!$cek) {
                    $result = $this->settings_model->addTermKreditpro($data);
                } else {
                    $result = $this->settings_model->updateTermKreditproByCompanyIdAndTerm($data);
                }
                $this->db->trans_commit();
                if ($result) {
                    $this->sma->send_json(array('status' => 1));
                } else {
                    $this->sma->send_json(array('status' => 0));
                }
            } else {
                $this->data['duration'] = array(
                    '30'       => '30 Hari',
                    '45'   => '45 Hari',
                    '60'       => '60 Hari',
                );
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['term_kreditpro'] = $this->settings_model->getTermKreditProByCompanyId($this->session->userdata('company_id'));

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('system_settings')));
                $meta = array('page_title' => lang('term_payment_kreditpro'), 'bc' => $bc);
                $this->page_construct('settings/term_payment_kreditpro', $meta, $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function markup()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $warehouses = $this->Owner ? $this->site->getAllWarehouses(null, ["company_id" => 1]) : $this->site->getAllWarehouses();
        $this->data['warehouses'] = $warehouses;
        $this->data['assets_ab'] = base_url() . 'themes/billing_portal/assets/';

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('markup')));
        $meta = array('page_title' => lang('markup'), 'bc' => $bc);
        $this->page_construct('settings/markup', $meta, $this->data);
    }

    public function get_markup($warehouse_id = null)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('warehouses_products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.cost as cost")
            ->select("{$this->db->dbprefix('warehouses_products')}.avg_cost as avg_cost, {$this->db->dbprefix('warehouses_products')}.markup as markup, {$this->db->dbprefix('warehouses_products')}.avg_cost as price, '0' as profit")
            ->from("{$this->db->dbprefix('products')}")
            ->join("{$this->db->dbprefix('warehouses_products')}", "{$this->db->dbprefix('products')}.id={$this->db->dbprefix('warehouses_products')}.product_id", 'left');

        $this->datatables
            ->where("{$this->db->dbprefix('warehouses_products')}.warehouse_id", $warehouse_id)
            ->where("{$this->db->dbprefix('products')}.is_deleted", null);

        if (!$this->Owner) {
            $this->datatables->where("{$this->db->dbprefix('products')}.company_id", $this->session->userdata('company_id'));
        }
        echo $this->datatables->generate();
    }

    public function update_markup()
    {
        $this->db->trans_begin();
        try {
            $post = $this->input->post();
            if ($post['type'] == 'each') {
                $where = ['id' => $post['id_wp']];
                $data = [
                    'markup' => ($post['markup'] == '' || $post['markup'] == 0) ? null : $post['markup'],
                ];
            } else {
                $where = [
                    'company_id' => $this->session->userdata('company_id'),
                    'warehouse_id' => $post['wh_id'],
                ];
                $data = [
                    'markup' => ($post['markup'] == '' || $post['markup'] == 0) ? null : $post['markup'],
                ];
            }
            $update = $this->settings_model->update_markup($data, $where);
            if (!$update) {
                $res['message'] = 'Update Failed';
                $res['notif']   = 'danger';
            } else {
                $res['message'] = 'Update Markup Success';
                $res['notif']   = 'success';
            }
            $this->db->trans_commit();
            echo json_encode($res);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Update Failed');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    // kur_bank_btn

    public function limit_credit()
    {
        // if (!$this->sma->checkPermissions()) {
        //     $this->session->set_flashdata('warning', lang('access_denied'));
        //     $this->sma->md();
        // }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('setting')), array('link' => '#', 'page' => lang('Limit_Credit')));
        $meta = array('page_title' => lang('Credit_Mandiri'), 'bc' => $bc);
        $this->page_construct('settings/limit_credit', $meta, $this->data);
    }

    public function getLimitCustomer()
    {
        ini_set('memory_limit', '-1');

        // $this->db->save_queries = true;

        $this->load->library('datatables');
        $this->datatables->select("sma_companies.id as id, sma_companies.company, sma_companies.name, css.company as distributor, sma_companies.phone, sma_companies.cf1, lr.Limit, lr.Tenor, lr.statusLoan")
            ->from("companies")
            ->join('companies css', "css.id = sma_companies.company_id")
            ->join('sma_mandiri_loan_request lr', "lr.company_id = sma_companies.id");
        // ->where('sma_companies.group_name =', 'biller')
        // ->where('sma_companies.cf1 != ""')
        // ->where('sma_companies.cf1 IS NOT NULL')
        // ->where('(sma_companies.client_id = "aksestoko")')
        // ->where('sma_companies.is_deleted IS NULL');

        $this->datatables->add_column("Actions", "<div class=\"text-center\">
                <a href='" . site_url('system_settings/edit_limit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='static' class='tip' title='" . lang("Edit Limit") . "'><i class=\"fa fa-pencil\"></i></a>
            </div>", "id");

        echo $this->datatables->generate();
    }

    public function add_limit()
    {
        $this->db->trans_begin();
        try {
            // $this->sma->checkPermissions(false, true);
            if ($this->isPost()) {
                $company = $this->site->getCompanyByID($this->input->post('company_id'));
                $cf1 = explode("IDC-", $company->cf1, 2);
                $bk = trim($cf1[1]);
                $data = [
                    'company_id'        => $this->input->post('company_id'),
                    'SellerID'          => $bk,
                    'NoRekMandiri'      => $this->input->post('NoRekMandiri'),
                    'NoKTP'             => $this->input->post('NoKTP'),
                    'limit'             => $this->input->post('limit'),
                    'tenor'             => $this->input->post('tenor'),
                    'NamaLengkap'       => $this->input->post('NamaLengkap'),
                    'JenisKelamin'      => $this->input->post('JenisKelamin'),
                    'TempatLahir'       => $this->input->post('TempatLahir'),
                    'TanggalLahir'      => $this->input->post('TanggalLahir'),
                    'NoHP'              => $this->input->post('NoHP'),
                    'Email'             => $this->input->post('Email'),
                    'MasaBerlakuKTP'    => $this->input->post('MasaBerlakuKTP'),
                    'AlamatKTP'         => $this->input->post('AlamatKTP'),
                    'KodePosKTP'        => $this->input->post('KodePosKTP'),
                    'ProvinsiKTP'       => $this->input->post('ProvinsiKTP'),
                    'KabupatenKotaKTP'  => $this->input->post('KabupatenKotaKTP'),
                    'KecamatanKTP'      => $this->input->post('KecamatanKTP'),
                    'KelurahanKTP'      => $this->input->post('KelurahanKTP'),
                    'RTKTP'             => $this->input->post('RTKTP'),
                    'RWKTP'             => $this->input->post('RWKTP'),
                    'AlamatTinggal'     => $this->input->post('AlamatTinggal'),
                    'KodeposTinggal'    => $this->input->post('KodeposTinggal'),
                    'ProvinsiTinggal'   => $this->input->post('ProvinsiTinggal'),
                    'KabupatenKotaTinggal' => $this->input->post('KabupatenKotaTinggal'),
                    'KecamatanTinggal'  => $this->input->post('KecamatanTinggal'),
                    'KelurahanTinggal'  => $this->input->post('KelurahanTinggal'),
                    'RTTinggal'         => $this->input->post('RTTinggal'),
                    'RWTinggal'         => $this->input->post('RWTinggal'),
                    'NPWP'              => $this->input->post('NPWP'),
                    'NamaIbuKandung'    => $this->input->post('NamaIbuKandung'),
                    'eCommID'           => 'SI',
                    'statusLoan'        => 'pending',
                ];

                $getLimit = $this->settings_model->getLimit($this->input->post('company_id'));
                if ($getLimit) {
                    throw new \Exception('Customer telah diset');
                }

                if ($this->settings_model->add_limit($data)) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("Limit_Added"));
                    redirect("system_settings/limit_credit/");
                }
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['page_title'] = lang("Add_Limit");
                $this->load->view($this->theme . 'settings/add_limit', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function edit_limit($id)
    {
        // $this->sma->checkPermissions(false, true);
        $this->db->trans_begin();
        try {
            if ($this->isPost()) {
                $data = [
                    'NoRekMandiri'      => $this->input->post('NoRekMandiri'),
                    'NoKTP'             => $this->input->post('NoKTP'),
                    'limit'             => $this->input->post('limit'),
                    'tenor'             => $this->input->post('tenor'),
                    'NamaLengkap'       => $this->input->post('NamaLengkap'),
                    'JenisKelamin'      => $this->input->post('JenisKelamin'),
                    'TempatLahir'       => $this->input->post('TempatLahir'),
                    'TanggalLahir'      => $this->input->post('TanggalLahir'),
                    'NoHP'              => $this->input->post('NoHP'),
                    'Email'             => $this->input->post('Email'),
                    'MasaBerlakuKTP'    => $this->input->post('MasaBerlakuKTP'),
                    'AlamatKTP'         => $this->input->post('AlamatKTP'),
                    'KodePosKTP'        => $this->input->post('KodePosKTP'),
                    'ProvinsiKTP'       => $this->input->post('ProvinsiKTP'),
                    'KabupatenKotaKTP'  => $this->input->post('KabupatenKotaKTP'),
                    'KecamatanKTP'      => $this->input->post('KecamatanKTP'),
                    'KelurahanKTP'      => $this->input->post('KelurahanKTP'),
                    'RTKTP'             => $this->input->post('RTKTP'),
                    'RWKTP'             => $this->input->post('RWKTP'),
                    'AlamatTinggal'     => $this->input->post('AlamatTinggal'),
                    'KodeposTinggal'    => $this->input->post('KodeposTinggal'),
                    'ProvinsiTinggal'   => $this->input->post('ProvinsiTinggal'),
                    'KabupatenKotaTinggal' => $this->input->post('KabupatenKotaTinggal'),
                    'KecamatanTinggal'  => $this->input->post('KecamatanTinggal'),
                    'KelurahanTinggal'  => $this->input->post('KelurahanTinggal'),
                    'RTTinggal'         => $this->input->post('RTTinggal'),
                    'RWTinggal'         => $this->input->post('RWTinggal'),
                    'NPWP'              => $this->input->post('NPWP'),
                    'NamaIbuKandung'    => $this->input->post('NamaIbuKandung'),
                    'eCommID'           => 'SI',
                ];

                if ($this->settings_model->edit_limit($data, ['id' => $this->input->post('loanID')])) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("Limit_Updated"));
                    redirect("system_settings/limit_credit/");
                }
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['limit'] = $this->settings_model->getCustomerLimit($id);
                $arr = [
                    'on_process', 'On Progress', 'Request PK Signing', 'Approve'
                ];
                $this->data['disable'] = (in_array($this->data['limit']->statusLoan, $arr) ? 'disabled' : '');
                $this->data['id'] = $id;
                $this->data['page_title'] = lang("Edit_Limit");
                $this->load->view($this->theme . 'settings/edit_limit', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function kur_bank_btn()
    {
        // if (!$this->sma->checkPermissions()) {
        //     $this->session->set_flashdata('warning', lang('access_denied'));
        //     $this->sma->md();
        // }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('setting')), array('link' => '#', 'page' => lang('Kur_Bank_Btn')));
        $meta = array('page_title' => lang('Kur_bank_BTN'), 'bc' => $bc);
        $this->page_construct('settings/kur_bank_btn', $meta, $this->data);
    }

    public function getKurBtn()
    {
        ini_set('memory_limit', '-1');

        // $this->db->save_queries = true;

        $this->load->library('datatables');
        $this->datatables->select("sma_companies.id as id, sma_companies.company, sma_companies.name, css.company as distributor, sma_companies.phone, sma_companies.cf1, btn.plafon_kur, btn.jangka_waktu, btn.status")
            ->from("companies")
            ->join('companies css', "css.id = sma_companies.company_id")
            ->join('sma_btn_pengajuan_kur btn', "btn.company_id = sma_companies.id");
        // ->where('sma_companies.group_name =', 'biller')
        // ->where('sma_companies.cf1 != ""')
        // ->where('sma_companies.cf1 IS NOT NULL')
        // ->where('(sma_companies.client_id = "aksestoko")')
        // ->where('sma_companies.is_deleted IS NULL');

        $this->datatables->add_column("Actions", "<div class=\"text-center\">
                <a href='" . site_url('system_settings/edit_kur_btn/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='static' class='tip' title='" . lang("Edit Kur Btn") . "'><i class=\"fa fa-pencil\"></i></a>
            </div>", "id");

        echo $this->datatables->generate();
    }

    public function add_kur_btn()
    {
        $this->db->trans_begin();
        try {
            // $this->sma->checkPermissions(false, true);
            if ($this->isPost()) {
                $params = $this->input->post();
                $data = [
                    'company_id'        => $params['company_id'],
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

                if ($this->at_site->insertKurBtnRequest($data)) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("Kur_Btn_Added"));
                    redirect("system_settings/kur_bank_btn/");
                }
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $this->data['page_title'] = lang("Add_Kur_Btn");
                $this->load->view($this->theme . 'settings/add_kur_btn', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function edit_kur_btn($id)
    {
        // $this->sma->checkPermissions(false, true);
        $this->db->trans_begin();
        $pengajuan = $this->at_site->getKurBtnRequest($id, false);
        try {
            if ($this->isPost()) {
                $_params = $this->input->post();
                $params = array_merge((array)$pengajuan, $_params);
                $data = [
                    'company_id'        => $id,
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

                if ($this->at_site->insertKurBtnRequest($data)) {
                    $this->db->trans_commit();
                    $this->session->set_flashdata('message', lang("Kur_Btn_Updated"));
                    redirect("system_settings/kur_bank_btn/");
                }
            } else {
                $this->data['pengajuan'] = $pengajuan;
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['modal_js'] = $this->site->modal_js();
                $arr = [
                    'on_process', 'On Progress', 'Request PK Signing', 'Approve'
                ];
                $this->data['disable'] = (in_array($this->data['limit']->statusLoan, $arr) ? 'disabled' : '');
                $this->data['id'] = $id;
                $this->data['page_title'] = lang("Edit_Kur_btn");
                $this->load->view($this->theme . 'settings/edit_kur_btn', $this->data);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', $th->getMessage());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function getCustomer($term = null, $limit = null)
    {
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        if (strlen($term) < 1) {
            return false;
        }
        $limit          = $this->input->get('limit', true);
        $results        = $this->companies_model->getCustomerLimitSugestions(trim($term), $limit);

        $rows['results'] = $results;
        $this->sma->send_json($rows);
    }
}

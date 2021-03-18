<?php defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->Settings = $this->site->get_setting();
        if ($sma_language = $this->input->cookie('sma_language', TRUE)) {
            $this->config->set_item('language', $sma_language);
            $this->lang->load('sma', $sma_language);
            $this->lang->load('pos', $sma_language);
            $this->Settings->user_language = $sma_language;
        } else {
            $this->config->set_item('language', $this->Settings->language);
            $this->lang->load('sma', $this->Settings->language);
            $this->lang->load('pos', $this->Settings->language);
            $this->Settings->user_language = $this->Settings->language;
        }
        if ($rtl_support = $this->input->cookie('sma_rtl_support', TRUE)) {
            $this->Settings->user_rtl = $rtl_support;
        } else {
            $this->Settings->user_rtl = $this->Settings->rtl;
        }
        $this->theme = $this->Settings->theme . '/views/';
        if (is_dir(VIEWPATH . $this->Settings->theme . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR)) {
            $this->data['assets'] = base_url() . 'themes/' . $this->Settings->theme . '/assets/';
        } else {
            $this->data['assets'] = base_url() . 'themes/default/assets/';
        }

        // START - untuk aksestoko
        $this->load->model('aksestoko/at_site_model', 'at_site');
        $this->data['assets_at'] = base_url() . 'themes/aksestoko/assets/';
        $this->data['cms'] = $this->at_site->getActiveCMS();
        // END - untuk aksestoko

        // START - untuk billing
        $this->data['assets_ab'] = base_url() . 'themes/billing_portal/assets/';
        // END - untuk billing

        $this->data['Settings'] = $this->Settings;
        $this->logAsAT = $this->sma->logged_as_aksestoko();
        $this->loggedIn = $this->logAsAT ? false : $this->sma->logged_in();

        if ($this->loggedIn) {
            $this->default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
            $this->data['default_currency'] = $this->default_currency;
            $this->Owner = $this->sma->in_group('owner') ? TRUE : NULL;
            $this->data['Owner'] = $this->Owner;
            $this->Principal = $this->sma->in_group('principal') ? TRUE : NULL;
            $this->data['Principal'] = $this->Principal;
            $this->Customer = $this->sma->in_group('customer') ? TRUE : NULL;
            $this->data['Customer'] = $this->Customer;
            $this->Supplier = $this->sma->in_group('supplier') ? TRUE : NULL;
            $this->data['Supplier'] = $this->Supplier;
            $this->Admin = $this->sma->in_group('admin') ? TRUE : NULL;
            $this->data['Admin'] = $this->Admin;
            $this->Manager = $this->sma->in_group('areamanager') ? TRUE : NULL;
            $this->data['Manager'] = $this->Manager;
            $this->Reseller = $this->sma->in_group('reseller') ? TRUE : NULL;
            $this->data['Reseller'] = $this->Reseller;
            $this->LT = $this->sma->in_group('toko besar') ? TRUE : NULL;
            $this->data['LT'] = $this->LT;
            $this->AdminBilling = $this->sma->in_group('admin_billing') ? TRUE : NULL;
            $this->data['AdminBilling'] = $this->AdminBilling;

            if ($sd = $this->site->getDateFormat($this->Settings->dateformat)) {
                $dateFormats = array(
                    'js_sdate' => $sd->js,
                    'php_sdate' => $sd->php,
                    'mysq_sdate' => $sd->sql,
                    'js_ldate' => $sd->js . ' hh:ii',
                    'php_ldate' => $sd->php . ' H:i',
                    'mysql_ldate' => $sd->sql . ' %H:%i'
                );
            } else {
                $dateFormats = array(
                    'js_sdate' => 'mm-dd-yyyy',
                    'php_sdate' => 'm-d-Y',
                    'mysq_sdate' => '%m-%d-%Y',
                    'js_ldate' => 'mm-dd-yyyy hh:ii:ss',
                    'php_ldate' => 'm-d-Y H:i:s',
                    'mysql_ldate' => '%m-%d-%Y %T'
                );
            }
            if (file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Pos.php')) {
                define("POS", 1);
            } else {
                define("POS", 0);
            }
            if (!$this->Owner) {
                $gp = $this->site->checkPermissions();
                $this->GP = $gp[0];
                $this->data['GP'] = $gp[0];
            } else {
                $this->data['GP'] = NULL;
            }
            $this->dateFormats = $dateFormats;
            $this->data['dateFormats'] = $dateFormats;
            $this->load->language('calendar');
            $this->m = strtolower($this->router->fetch_class());
            $this->v = strtolower($this->router->fetch_method());
            if (!$this->sma->checkMenuPermissions()) {
                $this->session->set_flashdata('error', lang('access_denied'));
                $this->sma->md();
            }

            $this->data['m'] = $this->m;
            $this->data['v'] = $this->v;
            $this->data['dt_lang'] = json_encode(lang('datatables_lang'));
            $this->data['dp_lang'] = json_encode(array('days' => array(lang('cal_sunday'), lang('cal_monday'), lang('cal_tuesday'), lang('cal_wednesday'), lang('cal_thursday'), lang('cal_friday'), lang('cal_saturday'), lang('cal_sunday')), 'daysShort' => array(lang('cal_sun'), lang('cal_mon'), lang('cal_tue'), lang('cal_wed'), lang('cal_thu'), lang('cal_fri'), lang('cal_sat'), lang('cal_sun')), 'daysMin' => array(lang('cal_su'), lang('cal_mo'), lang('cal_tu'), lang('cal_we'), lang('cal_th'), lang('cal_fr'), lang('cal_sa'), lang('cal_su')), 'months' => array(lang('cal_january'), lang('cal_february'), lang('cal_march'), lang('cal_april'), lang('cal_may'), lang('cal_june'), lang('cal_july'), lang('cal_august'), lang('cal_september'), lang('cal_october'), lang('cal_november'), lang('cal_december')), 'monthsShort' => array(lang('cal_jan'), lang('cal_feb'), lang('cal_mar'), lang('cal_apr'), lang('cal_may'), lang('cal_jun'), lang('cal_jul'), lang('cal_aug'), lang('cal_sep'), lang('cal_oct'), lang('cal_nov'), lang('cal_dec')), 'today' => lang('today'), 'suffix' => array(), 'meridiem' => array()));
            $this->load->model('db_model');
            $this->data['last_version'] = $this->db_model->getLastVersionUpdate();
            $this->data['activeSurvey'] = $this->db_model->getActiveSurvey();
            $this->data['customerResponse'] = $this->db_model->checkCustomerResponse();
        } else if ($this->logAsAT) {
            $this->load->model('aksestoko/home_model', 'home');  
            $this->load->model('aksestoko/promotion_model', 'promotion');

            $this->default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
            $this->data['default_currency'] = $this->default_currency;
            $this->data['cart'] = $this->at_site->getProductInCart($this->session->userdata('supplier_id'), $this->session->userdata('user_id'), $this->session->userdata('price_group_id'));
            $this->data['popup_promo'] = [];
            if ($this->session->userdata('supplier_id')) {
                $company = $this->at_site->findCompanyByCf1AndCompanyId($this->session->userdata('supplier_id'), $this->session->userdata('cf1'));
                $arr = [
                    'price_group_id' => $company->price_group_id,
                ];
                $this->session->set_userdata($arr);
                $this->data['popup_promo']  = $this->promotion->listPromotionPopup($company->id, $this->session->userdata('supplier_id'));
            }
            $this->m = strtolower($this->router->fetch_class());
            $this->v = strtolower($this->router->fetch_method());
            $this->data['m'] = $this->m;
            $this->data['v'] = $this->v;

            $this->data['guide'] = $this->at_site->findGuide($this->session->userdata('user_id'));
            $this->data['list_distributor'] = $this->home->getAllCompany($this->session->userdata('cf1'), $this->session->userdata('company_id'));
            $this->data['my_controller'] = $this;
        }

        $this->data['title_at'] = "AksesToko";

        $this->MateriaLink = (($_SERVER['HTTP_HOST'] == 'pos.forca.id') ? 'http://materia.id/' : 'http://10.15.4.190/dev/bangunan/');
        $this->CSMSLink = (($_SERVER['HTTP_HOST'] == 'pos.forca.id') ? 'http://csms.id/' : 'http://10.15.5.150/dev/sd/');
        $this->APIXLink = (($_SERVER['HTTP_HOST'] == 'pos.forca.id') ? 'https://apix.semenindonesia.com/' : 'http://10.15.3.109/dev/apix/public/');
    }

    function page_construct($page, $meta = array(), $data = array())
    {
        $this->load->model('menu_model');
        $menu_sidebar = $this->menu_model->getMenuActiveByGroupId($this->session->userdata('group_id'));

        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');
        $meta['info'] = $this->site->getNotifications();
        $meta['events'] = $this->site->getUpcomingEvents();
        $meta['ip_address'] = $this->input->ip_address();
        $meta['Owner'] = $data['Owner'];
        $meta['Admin'] = $data['Admin'];
        $meta['Supplier'] = $data['Supplier'];
        $meta['Customer'] = $data['Customer'];
        $meta['Manager'] = $data['Manager'];
        $meta['Reseller'] = $data['Reseller'];
        $meta['LT'] = $data['LT'];
        $meta['Principal'] = $data['Principal'];
        $meta['AdminBilling'] = $data['AdminBilling'];
        $meta['Settings'] = $data['Settings'];
        $meta['dateFormats'] = $data['dateFormats'];
        $meta['assets'] = $data['assets'];
        $meta['GP'] = $data['GP'];
        $meta['qty_alert_num'] = $this->site->get_total_qty_alerts();
        $meta['exp_alert_num'] = $this->site->get_expiring_qty_alerts();

        $this->load->model('sales_model');
        $this->load->model('purchases_model');
        $this->load->model('auth_model');

        $sales_pending_total = $this->sales_model->getCountPendingSales();
        $sales_booking_pending_total = $this->sales_model->getCountPendingSalesBooking();
        $bad_qty_confirm_pending = $this->sales_model->get_bad_qty_confirm_pending();
        $purchases_unwatched_total = $this->purchases_model->getCountUnwatchedPurchases();
        $getExpiredBill = $this->auth_model->getExpiredBill();
        $getPaymentReject = $this->auth_model->getPaymentReject();

        $classModule =  array();
        $module = array();
        $meta['menu_sidebars'] = '';
        foreach ($menu_sidebar as $keyMenu => $valueMenu) {
            $keys = array_keys(array_column($menu_sidebar, 'parent_id'), $valueMenu->parent_id);
            $classModule[$valueMenu->name] = array();

            if (!in_array($valueMenu->parent_id, $module)) {
                $subMenu = '';
                $moduleCounter = 0;
                foreach ($keys as $k => $value) {
                    if ($menu_sidebar[$value]->parent_id != $menu_sidebar[$value]->menu_id) {
                        $arrIdSubMenu = explode('/', $menu_sidebar[$value]->menu_url);
                        $id = $arrIdSubMenu[0] . '_' . $arrIdSubMenu[1];
                        $attributeModal = '';
                        if ($menu_sidebar[$value]->is_modal == 1) {
                            $attributeModal = 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"';
                        }
                        $counter = '';
                        if ($menu_sidebar[$value]->counter_variable && $menu_sidebar[$value]->counter_variable != '' && ${$menu_sidebar[$value]->counter_variable} > 0) {
                            $counter = '<span class="label label-default pull-right" style="margin-top: 10px; margin-right: 10px; right: 0; position: absolute;">' . ${$menu_sidebar[$value]->counter_variable} . '</span>';
                            $moduleCounter += (int) ${$menu_sidebar[$value]->counter_variable};
                        }

                        $strNewFitur = '';
                        if ($menu_sidebar[$value]->is_new_feature == 1) {
                            $strNewFitur = '<span class="label label-warning pull-right" style="margin-top: 10px; margin-right: 10px; right: 0; position: absolute;">' . lang('new') . '</span>';
                        }
                        $subMenu .=
                            '<li id="' . $id . '">
                            <a class="submenu have_left_padding" href="' . site_url($menu_sidebar[$value]->menu_url) . '?v='.FORCAPOS_VERSION.'" ' . $attributeModal . ' style="text-overflow: ellipsis;white-space: nowrap;overflow: hidden; padding-right:25px;">
                                <i class="' . $menu_sidebar[$value]->icon . '"></i>
                                <span class="text" title="' . lang($menu_sidebar[$value]->code) . '"> ' . lang($menu_sidebar[$value]->code) . '</span>
                                ' . $counter . $strNewFitur . '
                            </a>
                        </li>';
                    }
                    if ($menu_sidebar[$value]->menu_url != '') {
                        $x = explode('/', $menu_sidebar[$value]->menu_url);
                        if (!in_array('mm_' . $x[0], $classModule[$valueMenu->name])) {
                            array_push($classModule[$valueMenu->name], 'mm_' . $x[0]);
                        }
                    }
                }
                $strCounterSubMenu = '';
                if ($moduleCounter > 0) {
                    $strCounterSubMenu = '<span class="label label-danger" style="position: absolute; margin-top: 2px; margin-left: 25px;">' . $moduleCounter . '</span>';
                }

                $class = implode(" ", $classModule[$valueMenu->name]);
                if (count($keys) > 1) {
                    $meta['menu_sidebars'] .=
                        '<li class="' . $class . '">
                            <a class="dropmenu" href="#">
                            ' . $strCounterSubMenu . '
                                <i class="' . $valueMenu->module_icon . '"></i>
                                <span class="text"> ' . lang($valueMenu->module_code) . ' </span>
                                <span class="chevron closed"></span>
                            </a>
                            <ul>
                            ' . $subMenu . '
                            </ul>
                        </li>
                        ';
                }
                array_push($module, $valueMenu->parent_id);
            }
        }

        $meta['getExpiredBill'] = $getExpiredBill;
        $meta['getPaymentReject'] = $getPaymentReject;
        $meta['last_version'] = (int)$data['last_version']->last_version;
        $meta['activeSurvey'] = $data['activeSurvey'];
        $meta['customerResponse'] = $data['customerResponse'];

        $this->load->view($this->theme . 'header', $meta);
        $this->load->view($this->theme . $page, $data);
        $this->load->view($this->theme . 'footer');
    }

    function page_construct_at($page, $meta = array(), $data = array())
    {
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');

        $meta['LT'] = $data['LT'];
        $meta['Settings'] = $data['Settings'];
        $meta['dateFormats'] = $data['dateFormats'];
        $meta['assets'] = $data['assets'];

        $this->load->view('aksestoko/views/header', $meta);
        $this->load->view('aksestoko/views/' . $page, $data);
        $this->load->view('aksestoko/views/footer');
    }

    function page_construct_helps_land($page, $meta = array(), $data = array())
    {
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');

        $meta['Settings'] = $data['Settings'];
        $meta['dateFormats'] = $data['dateFormats'];
        $meta['assets'] = $data['assets'];

        $this->load->view('default/views/help/' . $page, $data);
    }

    function page_construct_helps($page, $meta = array(), $data = array())
    {
        $menu_sidebar = $this->help_model->getMenus();
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');

        $meta['Settings'] = $data['Settings'];
        $meta['dateFormats'] = $data['dateFormats'];
        $meta['assets'] = $data['assets'];

        $menu_active_sidebar = $this->help_model->getActiveMenu($this->uri->segment(3));
        $res = '';
        foreach ($menu_sidebar as $menu) {
            $get_subMenu = $this->help_model->getSubMenu($menu->parent_id);
            $res .= '<li class="article-menus_has-child">';
            if ($menu->parent_id != $menu_active_sidebar->parent_id) {
                $res .= '<div class="article-menus-head ">
                        ' . $menu->menu . '
                                </div>';
                $res .= '<ul class="">';
            } else {
                $res .= '<div class="article-menus-head active " >
                           ' . $menu->menu . '
                        </div>';
                $res .= '<ul class="article-menus_has-child--active buka" style="padding-left:20px;">';
            }
            foreach ($get_subMenu as $submenu) {
                $res .= '<li class="article-link" data-pgid="pg-11" data-id="st-1001">';
                if ($submenu->id != $menu_active_sidebar->id) {
                    $res .= '<a href="' . $submenu->id . '">' . $submenu->menu . '</a>
                        </li>';
                } else {
                    $res .= '<a style="color:#ffffff; font-weight: 600;" href="' . $submenu->id . '">' . $submenu->menu . '</a>
                        </li>';
                }
            }
            $res .= '</ul>
                     </li>';
        }
        $meta['menu_sidebars'] = $res;
        $this->load->view('default/views/help/header', $meta);
        $this->load->view('default/views/help/' . $page, $data);
        $this->load->view('default/views/help/footer');
    }
    
    function page_construct_feedback($page, $meta = array(), $data = array())
    {
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');

        $meta['Settings'] = $data['Settings'];
        $meta['dateFormats'] = $data['dateFormats'];
        $meta['assets'] = $data['assets'];

        $this->load->view('default/views/feedback/' . $page, $data);
    }

    function page_construct_feedback_at($page, $meta = array(), $data = array())
    {
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');

        $meta['Settings'] = $data['Settings'];
        $meta['dateFormats'] = $data['dateFormats'];
        $meta['assets'] = $data['assets'];

        $this->load->view('aksestoko/feedback/' . $page, $data);
    }

    function page_construct_ab($page, $meta = array(), $data = array())
    {
        $this->load->model('auth_model');
                                
        $meta['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
        $meta['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
        $meta['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');

        $meta['get_waiting'] = $this->auth_model->getWaitingBill();
        $meta['assets'] = $data['assets'];
        $meta['assets_ab'] = $data['assets_ab'];
        $meta['AdminBilling'] = $data['AdminBilling'];
        $meta['Settings'] = $data['Settings'];

        $this->load->view($this->theme .'billing_portal/header', $meta);
        $this->load->view($this->theme .'billing_portal/' . $page, $data);
        $this->load->view($this->theme .'billing_portal/footer');
    }
    
    function isPost()
    {
        return $this->input->method() == "post";
    }

    function checkATLogged()
    {
        if (!$this->logAsAT) {
            $this->session->set_userdata(['redirect' => str_replace(base_url(), "", current_url())]);
            $this->session->set_flashdata('error', "Perlu login untuk melihat halaman");
            redirect('aksestoko/auth/signin');
        }
    }

    function __convertDate($date)
    {
        $date = strtotime($date);
        $year = date('Y', $date);
        $month = date('m', $date);
        $number = date('d', $date);
        $time = date('H:i', $date);

        switch ($month) {
            case "01":
                $month = "Januari";
                break;
            case "02":
                $month = "Februari";
                break;
            case "03":
                $month = "Maret";
                break;
            case "04":
                $month = "April";
                break;
            case "05":
                $month = "Mei";
                break;
            case "06":
                $month = "Juni";
                break;
            case "07":
                $month = "Juli";
                break;
            case "08":
                $month = "Agustus";
                break;
            case "09":
                $month = "September";
                break;
            case "10":
                $month = "Oktober";
                break;
            case "11":
                $month = "November";
                break;
            case "12":
                $month = "Desember";
                break;
        }

        return "$number $month $year";
    }

    function __unit($id_unit)
    {
        $unit = $this->at_site->findUnit($id_unit);
        return $unit->name;
    }

    function __operate($a, $b, $char)
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

    public function insertLogActivities()
    {
        $activities = str_replace(base_url(), "", current_url());
        $dataLogActivities = [
            'user_id'   => $this->session->userdata('user_id'),
            'company_id' => $this->session->userdata('company_id'),
            'activity' => $activities,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address(),
        ];
        $this->site->insertLogActivities($dataLogActivities);
    }
}

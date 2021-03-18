<?php defined('BASEPATH') or exit('No direct script access allowed');

class Menu_permissions extends MY_Controller
{
    public function __construct()
    {

        parent::__construct();
        $this->load->model('menu_model');
        $this->lang->load('settings', $this->Settings->user_language);
        $this->load->model('settings_model');
    }

    public function index()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('master_menu')));
        $meta = array('page_title' => lang('master_menu'), 'bc' => $bc);
        $this->page_construct('menu/index', $meta, $this->data);
    }

    public function getMenu()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("modules.name as module, sma_menus.name as menu, sma_menus.icon, sma_menus.id as menu_id")
            ->from("sma_menus")
            ->join("sma_menus modules", 'modules.id=sma_menus.parent_id', 'inner')
            ->where("sma_menus.id != sma_menus.parent_id")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('menu_permissions/edit_menu/$1') . "' class='tip' title='" . lang("edit_menu") . "' data-toggle='modal' data-target='#myModal'  data-backdrop='static'><i class=\"fa fa-pencil\"></i></a></div>", "menu_id");
        echo $this->datatables->generate();
    }

    public function add_module()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'is_active' => $this->input->post('active') != '' ? 1 : 0,
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'icon' => $this->input->post('icon'),
                ];
                $id = $this->menu_model->insertMenu($data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $data['parent_id'] = $id;

                if (!$this->menu_model->updateMenuById($id, $data)) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("price_group_added"));
                redirect($_SERVER['HTTP_REFERER']);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'menu/add_module', $this->data);
        }
    }

    public function add_menu()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->isPost()) {
            // print_r($this->input->post());die;
            $this->db->trans_begin();
            try {
                $data = [
                    'is_displayed' => $this->input->post('is_display') != null ? 1 : 0,
                    'is_active' => $this->input->post('active') != null ? 1 : 0,
                    'is_new_feature' => $this->input->post('is_new_feature') != null ? 1 : 0,
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'icon' => $this->input->post('icon'),
                    'url' => $this->input->post('url'),
                    'parent_id' => $this->input->post('parent_id'),
                    'priority' => $this->input->post('priority'),
                ];
                $id = $this->menu_model->insertMenu($data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("menu_added"));
                redirect($_SERVER['HTTP_REFERER']);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['modules'] = $this->menu_model->getModules();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'menu/add_menu', $this->data);
        }
    }

    public function edit_menu($menu_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        $this->data['menus'] = $this->menu_model->getMenuById($menu_id, false);
        if ($this->isPost()) {
            $this->db->trans_begin();
            try {
                $data = [
                    'is_displayed' => $this->input->post('is_display') != null ? 1 : 0,
                    'is_active' => $this->input->post('active') != null ? 1 : 0,
                    'is_new_feature' => $this->input->post('is_new_feature') != null ? 1 : 0,
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'icon' => $this->input->post('icon'),
                    'url' => $this->input->post('url'),
                    'parent_id' => $this->input->post('parent_id'),
                ];
                $id = $this->menu_model->updateMenuById($menu_id, $data);
                if (!$id) {
                    throw new \Exception('Failed');
                }
                $this->db->trans_commit();
                $this->session->set_flashdata('message', lang("menu_permission_updated"));
                redirect($_SERVER['HTTP_REFERER']);
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['modules'] = $this->menu_model->getModules();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'menu/edit_menu', $this->data);
        }
    }

    public function search_menu()
    {
        $menu = $this->menu_model->getMenuActiveByGroupId($this->session->userdata('group_id'));
        foreach ($menu as $key => $value) {
            $menu[$key]->name = lang($value->code);
            $menu[$key]->module_code = lang($value->module_code);
        }
        echo json_encode($menu);
    }

    public function group_permission($group_id)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('welcome');
        }
        if ($this->input->post()) {

            $this->db->trans_begin();
            try {
                if (!$this->menu_model->updateMenupermissionByGroupId($group_id, ['is_active' => 0])) {
                    throw new \Exception("Data Gagal Disimpan");
                }
                if ($this->input->post('active')) {
                    // print_r($this->input->post('active'));die;
                    $arrParentID = array();
                    foreach ($this->input->post('active') as $menu_id => $value) {
                        $arr = explode('_', $menu_id);
                        if (!in_array($arr[1], $arrParentID)) {
                            $cek_module = $this->menu_model->getMenuPermissionByMenuIdAndGroupId($arr[1], $group_id);
                            if ($cek_module) {
                                if (!$this->menu_model->updateMenupermissionByGroupIdAndMenuId($arr[1], $group_id, ['is_active' => 1])) {
                                    throw new \Exception("Data Gagal Disimpan");
                                }
                            } else {
                                $data = [
                                    'menu_id' => $arr[1],
                                    'group_id' => $group_id,
                                    'is_active' => 1
                                ];
                                $this->menu_model->insertMenuPermission($data);
                            }
                            array_push($arrParentID, $arr[1]);
                        }
                        $cek_menu = $this->menu_model->getMenuPermissionByMenuIdAndGroupId($arr[0], $group_id);
                        if ($cek_menu) {
                            if (!$this->menu_model->updateMenupermissionByGroupIdAndMenuId($arr[0], $group_id, ['is_active' => 1])) {
                                throw new \Exception("Data Gagal Disimpan");
                            }
                        } else {
                            $data = [
                                'menu_id' => $arr[0],
                                'group_id' => $group_id,
                                'is_active' => 1
                            ];
                            $this->menu_model->insertMenuPermission($data);
                        }
                    }
                }
                $this->session->set_flashdata('message', 'Berhasil menambah item');
                $this->db->trans_commit();
            } catch (\Throwable $th) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $th->getMessage());
            }
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['menus'] = $this->menu_model->getAllMenuActive();
            $this->data['permission'] = $this->menu_model->getPermissionGroupId($group_id);
            $this->data['group'] = $this->settings_model->getGroupByID($group_id);
            $this->data['rowspan'] = array_count_values(array_column($this->data['menus'], 'module'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('menu_permissions')));
            $meta = array('page_title' => lang('menu_permissions'), 'bc' => $bc);
            $this->page_construct('menu/group_permission', $meta, $this->data);
        }
    }
}

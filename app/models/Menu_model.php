<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Menu_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getMenuActiveByGroupId($group_id, $url = false)
    {
        $this->db->save_queries = true;
        $this->db->select('module.code AS module_code, module.icon as module_icon, sma_menus.name, sma_menus.icon as icon, sma_menus.id as menu_id, module.id as parent_id, sma_menus.url as menu_url, sma_menus.code, sma_menu_permissions.is_active, sma_menus.is_modal, sma_menus.counter_variable, sma_menus.is_new_feature');
        $this->db->where('group_id='.$group_id);
        if(!$url){
            $this->db->where('sma_menus.is_displayed=1');
            $this->db->where('sma_menu_permissions.is_active=1');
        }
        if ($url) {
            $this->db->where("sma_menus.url LIKE '%".$url."%'");
        }
        $this->db->where('sma_menus.is_active=1');
        $this->db->join('sma_menus', 'sma_menus.id=sma_menu_permissions.menu_id', 'left');
        $this->db->join('sma_menus as module', 'module.id=sma_menus.parent_id', 'left');
        $this->db->order_by('module.id ASC, module.priority ASC, sma_menus.priority ASC');
        $q = $this->db->get('sma_menu_permissions');
        // echo $this->db->last_query();die;

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function insertMenu($data)
    {
        if ($this->db->insert('sma_menus', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function updateMenuById($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("sma_menus", $data)) {
            return true;
        }
        return false;
    }

    public function getModules()
    {
        $this->db->where("sma_menus.id = sma_menus.parent_id");
        $this->db->where('sma_menus.is_active', 1);
        $q = $this->db->get('sma_menus');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getMenuById($menu_id, $is_active = true)
    {
        $this->db->where('id', $menu_id);
        if($is_active){
            $this->db->where('sma_menus.is_active', 1);
        }
        $q = $this->db->get('sma_menus',1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllMenuActive()
    {
        $this->db
            ->select("sma_menus.id, modules.name as module, sma_menus.name, sma_menus.parent_id as parent_id")
            ->from("sma_menus")
            ->join("sma_menus modules", 'modules.id=sma_menus.parent_id', 'inner')
            ->where("sma_menus.id != sma_menus.parent_id")
            ->order_by('sma_menus.parent_id ASC, sma_menus.id ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPermissionGroupId($group_id)
    {
        $this->db->where('group_id', $group_id);
        $this->db->where('is_active', 1);
        $q = $this->db->get('sma_menu_permissions');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateMenupermissionByGroupId($group_id, $data)
    {
        if ($this->db->update('sma_menu_permissions', $data, array('group_id'=>$group_id))) {
            return true;
        }
        return false;
    }

    public function getMenuPermissionByMenuIdAndGroupId($menu_id,$group_id)
    {
        $q = $this->db->get_where('sma_menu_permissions', array('menu_id'=>$menu_id, 'group_id'=>$group_id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function updateMenupermissionByGroupIdAndMenuId($menu_id, $group_id, $data)
    {
        if ($this->db->update('sma_menu_permissions', $data, array('menu_id'=>$menu_id, 'group_id'=>$group_id))) {
            return true;
        }
        return false;
    }

    public function insertMenuPermission($data)
    {
        if ($this->db->insert('sma_menu_permissions', $data)) {
            return true;
        }
        return false;
    }
}

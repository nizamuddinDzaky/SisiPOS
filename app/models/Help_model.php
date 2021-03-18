<?php defined('BASEPATH') or exit('No direct script access allowed');

class Help_model extends CI_Model
{

    public function getActivationCmsFaq($id)
    {
        $q = $this->db->get_where('cms_faq_pos', ['is_active' => '1', 'is_deleted' => '0', 'id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getMenuActiveByGroupId()
    {
        $q = $this->db->get_where('cms_faq_pos', ['is_active' => '1', 'is_deleted' => '0']);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getMenus()
    {
        // $this->db->select("sma_cms_faq_pos.id, sma_cms_faq_pos.menu AS sub_menu, sma_cms_faq_pos.parent_id, 
        // sma_parent_menu_faq_pos.menu AS parent_menu");
        // $this->db->join("sma_parent_menu_faq_pos", "sma_cms_faq_pos.parent_id = sma_parent_menu_faq_pos.parent_id", "left");
        $q = $this->db->get_where("sma_parent_menu_faq_pos", ['is_active' => '1', 'is_deleted' => '0']);

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getSubMenu($id_parent)
    {
        // $this->db->select("sma_cms_faq_pos.id, sma_cms_faq_pos.menu AS sub_menu, sma_cms_faq_pos.parent_id, 
        // sma_parent_menu_faq_pos.menu AS parent_menu");
        // $this->db->join("sma_parent_menu_faq_pos", "sma_cms_faq_pos.parent_id = sma_parent_menu_faq_pos.parent_id", "left");
        $q = $this->db->get_where("sma_cms_faq_pos", ['parent_id' => $id_parent, 'is_active' => '1', 'is_deleted' => '0']);

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getHomeMenu()
    {
        $sql = "SELECT sma_parent_menu_faq_pos.menu AS menus, sma_parent_menu_faq_pos.image AS image, sma_cms_faq_pos.id AS id FROM sma_parent_menu_faq_pos 
                JOIN sma_cms_faq_pos ON sma_cms_faq_pos.parent_id = sma_parent_menu_faq_pos.parent_id 
                WHERE (sma_parent_menu_faq_pos.image IS NOT NULL AND sma_parent_menu_faq_pos.image != '') AND sma_cms_faq_pos.is_active = 1 AND sma_cms_faq_pos.is_deleted = 0
                GROUP BY sma_parent_menu_faq_pos.parent_id LIMIT 12";
        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getActiveMenu($id)
    {
        if ($id) {
            $sql = "SELECT sma_parent_menu_faq_pos.parent_id, sma_cms_faq_pos.id FROM sma_cms_faq_pos
                    JOIN sma_parent_menu_faq_pos ON sma_cms_faq_pos.parent_id = sma_parent_menu_faq_pos.parent_id
                    WHERE id = ? LIMIT 1";
            $query = $this->db->query($sql, array($id));
            if ($query->num_rows() > 0) {
                return $query->row();
            }
            return false;
        }
    }
}

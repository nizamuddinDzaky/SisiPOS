<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Copyright (c) 2018 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */

class Promo_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getPromo()
    {
        $this->db->where('sma_promo.status >=', 1);
        $this->db->where('CURDATE()-sma_promo.end_date <= 1');
        $this->db->select('sma_promo.*');

        $q = $this->db->get('sma_promo');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getPromoDetail($link)
    {
        $q = $this->db->get_where('promo', array('id' => $link), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTransactionPromo($month = null, $year = null)
    {
        $this->db->select("{$this->db->dbprefix('promo')}.name,{$this->db->dbprefix('companies')}.company,{$this->db->dbprefix('purchases')}.supplier, {$this->db->dbprefix('transaction_promo')}.date")
            ->join('companies', 'companies.id=transaction_promo.company_id', 'left')
            ->join('promo', 'promo.id=transaction_promo.promo_id', 'left')
            ->join('purchases', 'purchases.id=transaction_promo.purchase_id', 'left');
        if ($month != null || $year=null) {
            $this->db->where('month(sma_transaction_promo.date)', $month);
            $this->db->where('year(sma_transaction_promo.date)', $year);
        }
        $q =$this->db->get("transaction_promo");
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
}

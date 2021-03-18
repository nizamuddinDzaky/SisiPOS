<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Copyright (c) 2017 adminSISI.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    adminSISI - initial API and implementation and/or initial documentation
 */

class Promo extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->insertLogActivities();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->library('form_validation');
        $this->load->model('promo_model');
    }

    public function index()
    {
        $this->data['promo'] = $this->promo_model->getPromo();
        $bc = array(array('link' => '#', 'page' => lang('Promotion News')));
        $meta = array('page_title' => lang('Promotion'), 'bc' => $bc);
        $this->page_construct('promotion/index', $meta, $this->data);
    }
    
    public function detail()
    {
        $link  =  $this->uri->segment(3);
        $meta = array('page_title' => lang('detail promo'));
        $this->data['promo'] = $this->promo_model->getPromoDetail($link);
        $this->page_construct('promotion/detail', $meta, $this->data);
    }
}

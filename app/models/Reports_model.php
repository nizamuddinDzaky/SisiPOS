<?php defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $limit = 5, $company_id = null)
    {
        $this->db->select('id, code, name')
            ->where('company_id', $company_id ?? $this->session->userdata('company_id'))
            ->group_start()
            ->like('name', $term, 'both')
            ->or_like('code', $term, 'both')
            ->group_end();

        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaff()
    {
        if ($this->Admin) {
            $this->db->where('group_id !=', 1);
        }
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function getStaffName()
    {
        $this->db->select('id, first_name, last_name');
        if ($this->Admin) {
            $this->db->where('group_id !=', 1);
        }
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSalesTotals($customer_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false);
        // ->where('customer_id', $customer_id);
        $join = "(SELECT sma_companies.id FROM sma_companies JOIN (
                            SELECT cf1, id FROM sma_companies WHERE id = " . $customer_id . "
                        )cmp ON cmp.cf1 = sma_companies.cf1
                        WHERE group_name = 'biller' OR group_name = 'customer' OR group_name = 'address') comp";
        $this->db->join($join, 'sma_sales.customer_id = comp.id', 'inner');
        $this->db->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
        $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesTotalsRev($customer_id)
    {
        $sql = "SELECT SUM(COALESCE(total_amount, 0)) AS total_amount, SUM(COALESCE(paid, 0)) AS paid
                FROM (SELECT customer_id, 
                             COUNT(sma_sales.id) AS total, 
                             COALESCE (SUM(grand_total), 0) AS total_amount, 
                             COALESCE (SUM(paid), 0) AS paid, 
                             COALESCE (SUM(grand_total), 0) - COALESCE (SUM(paid), 0) AS balance
                FROM sma_sales 
                WHERE sma_sales.client_id != 'aksestoko' OR sma_sales.client_id IS NULL
                GROUP BY sma_sales.customer_id 
                UNION
                SELECT cmp.id AS customer_id,
                       comp.total AS total,
                       comp.total_amount AS total_amount,
                       comp.paid AS paid,
                       comp.balance AS balance
                FROM sma_companies JOIN ( SELECT sma_companies.id AS customer_id, 
                                                 COUNT(sma_sales.id) AS total, 
                                                 COALESCE (SUM(grand_total), 0) AS total_amount, 
                                                 COALESCE (SUM(paid), 0) AS paid, COALESCE (SUM(grand_total), 0) - COALESCE (SUM(paid), 0) AS balance
                                          FROM sma_sales 
                                          JOIN sma_users ON sma_users.id = sma_sales.created_by
                                          JOIN sma_companies ON sma_users.company_id = sma_companies.id 
                                          WHERE sma_sales.client_id = 'aksestoko'
                                          GROUP BY sma_sales.created_by) comp 
                                        ON comp.customer_id = sma_companies.id
                JOIN sma_companies AS cmp ON cmp.cf1 = sma_companies.cf1 AND cmp.group_name = 'customer') x
                JOIN sma_companies ON x.customer_id = sma_companies.id AND sma_companies.company_id = " . $this->session->userdata('company_id') . " WHERE x.customer_id = ? GROUP BY x.customer_id";
        $query = $this->db->query($sql, $customer_id);
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function getCustomerSales($customer_id)
    {
        $this->db->from('sales')
            ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
        $join = "(SELECT sma_companies.id FROM sma_companies JOIN (
                            SELECT cf1, id FROM sma_companies WHERE id = " . $customer_id . "
                        )cmp ON cmp.cf1 = sma_companies.cf1
                        WHERE group_name = 'biller' OR group_name = 'customer' OR group_name = 'address') comp";
        $this->db->join($join, 'sma_sales.customer_id = comp.id', 'inner');
        // ->where('customer_id', $customer_id);
        $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        return $this->db->count_all_results();
    }

    public function getCustomerSalesRev($customer_id)
    {
        $sql = "SELECT SUM(COALESCE(total, 0)) AS total
                FROM (SELECT customer_id, 
                             COUNT(sma_sales.id) AS total, 
                             COALESCE (SUM(grand_total), 0) AS total_amount, 
                             COALESCE (SUM(paid), 0) AS paid, 
                             COALESCE (SUM(grand_total), 0) - COALESCE (SUM(paid), 0) AS balance
                FROM sma_sales 
                WHERE sma_sales.client_id != 'aksestoko' OR sma_sales.client_id IS NULL
                GROUP BY sma_sales.customer_id 
                UNION
                SELECT cmp.id AS customer_id,
                       comp.total AS total,
                       comp.total_amount AS total_amount,
                       comp.paid AS paid,
                       comp.balance AS balance
                FROM sma_companies JOIN ( SELECT sma_companies.id AS customer_id, 
                                                 COUNT(sma_sales.id) AS total, 
                                                 COALESCE (SUM(grand_total), 0) AS total_amount, 
                                                 COALESCE (SUM(paid), 0) AS paid, COALESCE (SUM(grand_total), 0) - COALESCE (SUM(paid), 0) AS balance
                                          FROM sma_sales 
                                          JOIN sma_users ON sma_users.id = sma_sales.created_by
                                          JOIN sma_companies ON sma_users.company_id = sma_companies.id 
                                          WHERE sma_sales.client_id = 'aksestoko'
                                          GROUP BY sma_sales.created_by) comp 
                                        ON comp.customer_id = sma_companies.id
                JOIN sma_companies AS cmp ON cmp.cf1 = sma_companies.cf1 AND cmp.group_name = 'customer') x
                JOIN sma_companies ON x.customer_id = sma_companies.id AND sma_companies.company_id = " . $this->session->userdata('company_id') . " WHERE x.customer_id = ? GROUP BY x.customer_id";
        $query = $this->db->query($sql, $customer_id);
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function getCustomerQuotes($customer_id)
    {
        $this->db->from('quotes')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerReturns($customer_id)
    {
        $this->db->from('sales')->where('customer_id', $customer_id)->where('sale_status', 'returned');
        return $this->db->count_all_results();
    }

    public function getCustomerReturnsRev($customer_id)
    {
        $sql = "SELECT SUM(COALESCE(total, 0)) AS total
                FROM (SELECT customer_id, 
                             COUNT(sma_sales.id) AS total, 
                             COALESCE (SUM(grand_total), 0) AS total_amount, 
                             COALESCE (SUM(paid), 0) AS paid, 
                             COALESCE (SUM(grand_total), 0) - COALESCE (SUM(paid), 0) AS balance
                FROM sma_sales 
                WHERE sma_sales.client_id != 'aksestoko' OR sma_sales.client_id IS NULL AND sma_sales.sale_status = 'returned'
                GROUP BY sma_sales.customer_id 
                UNION
                SELECT cmp.id AS customer_id,
                       comp.total AS total,
                       comp.total_amount AS total_amount,
                       comp.paid AS paid,
                       comp.balance AS balance
                FROM sma_companies JOIN ( SELECT sma_companies.id AS customer_id, 
                                                 COUNT(sma_sales.id) AS total, 
                                                 COALESCE (SUM(grand_total), 0) AS total_amount, 
                                                 COALESCE (SUM(paid), 0) AS paid, COALESCE (SUM(grand_total), 0) - COALESCE (SUM(paid), 0) AS balance
                                          FROM sma_sales 
                                          JOIN sma_users ON sma_users.id = sma_sales.created_by
                                          JOIN sma_companies ON sma_users.company_id = sma_companies.id 
                                          WHERE sma_sales.client_id = 'aksestoko' AND sma_sales.sale_status = 'returned'
                                          GROUP BY sma_sales.created_by) comp 
                                        ON comp.customer_id = sma_companies.id
                JOIN sma_companies AS cmp ON cmp.cf1 = sma_companies.cf1 AND cmp.group_name = 'customer') x
                JOIN sma_companies ON x.customer_id = sma_companies.id AND sma_companies.company_id = " . $this->session->userdata('company_id') . " WHERE x.customer_id = ? GROUP BY x.customer_id";
        $query = $this->db->query($sql, $customer_id);
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function getStockValue()
    {
        $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*price as by_price, COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id GROUP BY " . $this->db->dbprefix('products') . ".id )a");

        if ($this->Owner) {
            $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*price as by_price, COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id GROUP BY " . $this->db->dbprefix('products') . ".id )a");
        } else {
            $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*price as by_price, COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id and " . $this->db->dbprefix('products') . ".company_id = " . $this->session->userdata('company_id') . " GROUP BY " . $this->db->dbprefix('products') . ".id )a");
        }

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseStockValue($id)
    {
        $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0))*price as by_price, sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0))*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id WHERE " . $this->db->dbprefix('warehouses_products') . ".warehouse_id = ? GROUP BY " . $this->db->dbprefix('products') . ".id )a", array($id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    // public function getmonthlyPurchases()
    // {
    //     $myQuery = "SELECT (CASE WHEN date_format( date, '%b' ) Is Null THEN 0 ELSE date_format( date, '%b' ) END) as month, SUM( COALESCE( total, 0 ) ) AS purchases FROM purchases WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH ) GROUP BY date_format( date, '%b' ) ORDER BY date_format( date, '%m' ) ASC";
    //     $q = $this->db->query($myQuery);
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }
    //     return FALSE;
    // }

    public function getChartData()
    {
        $myQuery = "SELECT S.month,
        COALESCE(S.sales, 0) as sales,
        COALESCE( P.purchases, 0 ) as purchases,
        COALESCE(S.tax1, 0) as tax1,
        COALESCE(S.tax2, 0) as tax2,
        COALESCE( P.ptax, 0 ) as ptax
        FROM (  SELECT  date_format(date, '%Y-%m') Month,
                SUM(total) Sales,
                SUM(product_tax) tax1,
                SUM(order_tax) tax2
                FROM " . $this->db->dbprefix('sales') . "
                WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )
                AND " . $this->db->dbprefix('sales') . ".company_id = " . $this->session->userdata('company_id') . "
                GROUP BY date_format(date, '%Y-%m')) S
            LEFT JOIN ( SELECT  date_format(date, '%Y-%m') Month,
                        SUM(product_tax) ptax,
                        SUM(order_tax) otax,
                        SUM(total) purchases
                        FROM " . $this->db->dbprefix('purchases') . " WHERE " . $this->db->dbprefix('purchases') . ".company_id = " . $this->session->userdata('company_id') . "
                        GROUP BY date_format(date, '%Y-%m')) P
            ON S.Month = P.Month
            ORDER BY S.Month";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDailySales($year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
			FROM " . $this->db->dbprefix('sales') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('sales') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
			GROUP BY DATE_FORMAT( date,  '%e' )";

        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getMonthlySales($year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
			FROM " . $this->db->dbprefix('sales') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('sales') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y' ) =  '{$year}'
			GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffDailySales($user_id, $year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('sales') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffMonthlySales($user_id, $year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . " WHERE ";

        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('sales') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }

        $myQuery .= $this->db->dbprefix('sales') . ".created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";

        //        if(!$this->Owner){
        //            $this->db->join('warehouses','warehouses.id=sales.warehouse_id','left');
        ////            $this->db->where('warehouses.company_id',$this->session->userdata('company_id'));
        //        }
        $q = $this->db->query($myQuery, false);

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasesTotals($supplier_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('supplier_id', $supplier_id)
            ->where('company_id', $this->session->userdata('company_id'));
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSupplierPurchases($supplier_id)
    {
        $this->db->from('purchases')->where("company_id = '" . $this->session->userdata('company_id') . "' and supplier_id='" . $supplier_id . "'");
        return $this->db->count_all_results();
    }

    public function getStaffPurchases($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('created_by', $user_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStaffSales($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('created_by', $user_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalSales($start, $end, $warehouse_id = null, $cons = null)
    {
        $saleitems_flag = "(SELECT sale_id, subtotal, quantity, product_id, product_name FROM sma_sale_items WHERE flag=1) as si_consignment";
        $select_cons = ("paid > subtotal" ? "subtotal" : "paid");

        if ($cons == 1) {
            $this->db->select("count({$this->db->dbprefix('sales')}.id) as total, sum(COALESCE(subtotal, 0)) as total_amount, SUM(COALESCE(" . $select_cons . ", 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax", false);
        } elseif ($cons == 2) {
            $this->db->select("count({$this->db->dbprefix('sales')}.id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax", false);
        } else {
            $this->db->select("count({$this->db->dbprefix('sales')}.id) as total, sum(COALESCE(grand_total, 0)-COALESCE(si_consignment.subtotal, 0)) as total_amount, SUM(COALESCE(paid, 0)-COALESCE(si_consignment.subtotal, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax", false);
        }
        $this->db->where('sale_status !=', 'pending')
            ->where('date BETWEEN ' . $start . ' and ' . $end);

        // if ($cons == 1) {
            // $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
            // $this->db->where('sale_items.flag', 1);
        // } elseif (!$cons) {
            // $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
            $this->db->join($saleitems_flag, 'sales.id=si_consignment.sale_id', 'left');
                // ->where('sale_items.flag', null);
        // }

        if ($warehouse_id) {
            $this->db->where('sales.warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->db->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPurchases($start, $end, $warehouse_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('purchases')}.id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax", false)
            ->where('status !=', 'pending')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->db->join('warehouses', 'purchases.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        $this->db->from('purchases');
        $q = $this->db->get();
        // $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalExpenses($start, $end, $warehouse_id = null)
    {
        $this->db->select("count({$this->db->dbprefix('expenses')}.id) as total, sum(COALESCE(amount, 0)) as total_amount", false)
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if (!$this->Owner) {
            $this->db->join('warehouses', 'expenses.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPaidAmount($start, $end)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->where('type', 'sent')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);
        if (!$this->Owner) {
            $this->db->join('sales', 'payments.sale_id=sales.id', 'left');
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedAmount($start, $end, $cons = null)
    {
        $saleitems_flag = "(SELECT sale_id, subtotal, quantity, product_id, product_name FROM sma_sale_items WHERE flag=1)as other";
        $select_cons = ("{$this->db->dbprefix('payments')}.amount > {$this->db->dbprefix('sale_items')}.subtotal" ? "{$this->db->dbprefix('sale_items')}.subtotal" : "{$this->db->dbprefix('payments')}.amount");

        if ($cons == 1) {
            $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE(" . $select_cons . ", 0)) as total_amount", false);
        } elseif ($cons == 2) {
            $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false);
        } else {
            $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)-COALESCE(other.subtotal, 0)) as total_amount", false);
        }
        $this->db->where('type', 'received')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);

        $this->db->join('sales', 'payments.sale_id=sales.id', 'left');
        // if ($cons == 1) {
        //     $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
        //     $this->db->where('sale_items.flag', 1);
        // } elseif (!$cons) {
        //     $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
        //     $this->db->where('sale_items.flag', null);
            $this->db->join($saleitems_flag, 'payments.sale_id=other.sale_id', 'left');
        // }

        if (!$this->Owner) {
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedCashAmount($start, $end, $cons = null)
    {
        $saleitems_flag = "(SELECT sale_id, subtotal, quantity, product_id, product_name FROM sma_sale_items WHERE flag=1) as si_consignment";
        $select_cons = ("{$this->db->dbprefix('payments')}.amount > {$this->db->dbprefix('sale_items')}.subtotal" ? "{$this->db->dbprefix('sale_items')}.subtotal" : "{$this->db->dbprefix('payments')}.amount");

        if ($cons == 1) {
            $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE(" . $select_cons . ", 0)) as total_amount", false);
        } elseif ($cons == 2) {
            $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false);
        } else {
            $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)-COALESCE(si_consignment.subtotal,0)) as total_amount", false);
        }
        $this->db->where('type', 'received')->where('paid_by', 'cash')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);
        $this->db->join('sales', 'payments.sale_id=sales.id', 'left');

        // if ($cons == 1) {
        //     $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');

        //     $this->db->where('sale_items.flag', 1);
        // } elseif (!$cons) {
        //     $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');

            $this->db->join($saleitems_flag, 'payments.sale_id=si_consignment.sale_id', 'left');
        //         ->where('sale_items.flag', null);
        // }

        if (!$this->Owner) {
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedCCAmount($start, $end)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->where('type', 'received')->where('paid_by', 'CC')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);

        if (!$this->Owner) {
            $this->db->join('sales', 'payments.sale_id=sales.id', 'left');
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedChequeAmount($start, $end)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->where('type', 'received')->where('paid_by', 'Cheque')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);

        if (!$this->Owner) {
            $this->db->join('sales', 'payments.sale_id=sales.id', 'left');
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedPPPAmount($start, $end)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->where('type', 'received')->where('paid_by', 'ppp')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);

        if (!$this->Owner) {
            $this->db->join('sales', 'payments.sale_id=sales.id', 'left');
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedStripeAmount($start, $end)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE({$this->db->dbprefix('payments')}.amount, 0)) as total_amount", false)
            ->where('type', 'received')->where('paid_by', 'stripe')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);

        if (!$this->Owner) {
            $this->db->join('sales', 'payments.sale_id=sales.id', 'left');
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReturnedAmount($start, $end)
    {
        $this->db->select("count({$this->db->dbprefix('payments')}.id) as total, SUM(COALESCE(amount, 0)) as total_amount", false)
            ->where('type', 'returned')
            ->where("{$this->db->dbprefix('payments')}.date BETWEEN " . $start . " and " . $end);

        $this->db->join('sales', 'payments.sale_id=sales.id', 'left');
        $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left')->where('sale_items.flag', null);

        if (!$this->Owner) {
            $this->db->join('warehouses', 'sales.warehouse_id=warehouses.id', 'left');
            $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        }

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseTotals($warehouse_id = null)
    {
        $this->db->select('sum(quantity) as total_quantity, count(id) as total_items', false);
        $this->db->where('quantity !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($this->Admin) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('warehouses_products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCosting($date, $warehouse_id = null, $year = null, $month = null)
    {
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost, SUM( COALESCE( sale_unit_price, 0 ) * quantity ) AS sales, SUM( COALESCE( purchase_net_unit_cost, 0 ) * quantity ) AS net_cost, SUM( COALESCE( sale_net_unit_price, 0 ) * quantity ) AS net_sales', false);
        if ($date) {
            $this->db->where('costing.date', $date);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('costing.date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('costing.date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->join('sales', 'sales.id=costing.sale_id')
                ->where('sales.warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpenses($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }


        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturns($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
            ->where('sale_status', 'returned');
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getOrderDiscount($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( order_discount, 0 ) ) AS order_discount', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpenseCategories($company_id)
    {
        $this->db->where("`company_id` = '$company_id' AND is_deleted IS NULL");
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDailyPurchases($year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('purchases') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getMonthlyPurchases($year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('purchases') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffDailyPurchases($user_id, $year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('purchases') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffMonthlyPurchases($user_id, $year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        if (!$this->Owner) {
            $myQuery .= $this->db->dbprefix('purchases') . ".company_id = " . $this->session->userdata('company_id') . " AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $this->db->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
        $this->db->where('warehouses.company_id', $this->session->userdata('company_id'));
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBestSeller($start_date, $end_date, $warehouse_id = null)
    {
        $this->db
            ->select("product_name, product_code")->select_sum('quantity')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('sales.date >=', $start_date)->where('sales.date <=', $end_date)
            ->group_by('product_name, product_code')->order_by('sum(quantity)', 'desc')->limit(10);
        if ($warehouse_id) {
            $this->db->where('sale_items.warehouse_id', $warehouse_id);
        }

        if (!$this->Owner) {
            $this->db->where('sales.company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSales($bid, $p_code, $begin, $end)
    {
        $this->db->select('CAST(date as DATE) as date, SUM(COALESCE(quantity,0)) as qty, product_name, product_code', false);
        $this->db->from('sales');
        $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
        if ($bid) {
            $this->db->where('sales.biller_id', $bid);
        }
        if ($begin) {
            $this->db->where('sales.date >=', $begin);
        }
        if ($end) {
            $this->db->where('sales.date <', $end);
        }
        $this->db->where('product_code', $p_code);
        $this->db->group_by('CAST(date as DATE)');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function getPurchases($cid, $p_code, $begin, $end)
    {
        $this->db->select("CAST(sma_purchases.date as DATE) as date, SUM(COALESCE(quantity,0)) as qty, product_name, product_code", false);
        $this->db->from('purchases');
        $this->db->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left');
        if ($cid) {
            $this->db->where('purchases.company_id', $cid);
        }
        if ($begin) {
            $this->db->where('purchases.date >=', $begin);
        }
        if ($end) {
            $this->db->where('purchases.date <', $end);
        }
        $this->db->where('product_code', $p_code);
        $this->db->group_by("CAST(sma_purchases.date as DATE)");
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTotalConsignments($start, $end, $warehouse_id = null)
    {
        $sale = "(SELECT product_name, product_id, sum(quantity) as qty_sales FROM {$this->db->dbprefix('sale_items')} WHERE flag=1 GROUP BY product_id) as sale_tab";
        $consignment = "(SELECT ci.consignment_id, (sale_tab.qty_sales*ci.net_unit_price) as price_supplier FROM {$this->db->dbprefix('consignment_items')} as ci RIGHT JOIN " . $sale . " ON sale_tab.product_id=ci.product_id GROUP BY ci.product_id) as result";

        $query = "SELECT COUNT(result.consignment_id) as total, sum(COALESCE(result.price_supplier,0)) as total_amount, NULL as paid, NULL as tax
                FROM {$this->db->dbprefix('consignment')}
                RIGHT JOIN " . $consignment . " ON result.consignment_id={$this->db->dbprefix('consignment')}.id";
        $query .= " WHERE date BETWEEN " . $start . " AND " . $end . "";

        $check_where = substr($query, strpos($query, "consignment_id=") + 1);
        if ($warehouse_id) {
            $query = $query . (strpos($check_where, "WHERE") ? " AND " : " WHERE ") . "warehouse_id={$warehouse_id}";
        }
        if (!$this->Owner) {
            $query = $query . (strpos($check_where, "WHERE") ? " AND " : " WHERE ") . "company_id=" . $this->session->userdata('company_id') . "";
        }

        $q = $this->db->query($query);
        //        $this->db->select("COUNT(result.consignment_id) as total, sum(COALESCE(result.price_supplier,0)) as total_amount, NULL as paid, NULL as tax");
        //            ->where('date BETWEEN ' . $start . ' and ' . $end);
        //        $this->db->join($consignment,'result.consignment_id=consignment.id','right');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getFeedbackByID($id)
    {
        $q = $this->db->get_where('feedback_question', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getResponseBy($id)
    {
        $this->db->select('sma_feedback_question.question `question`, sma_feedback_response.answer `answer`');
        $this->db->from('sma_feedback_response, sma_feedback_question');
        $this->db->where('sma_feedback_response.question_id = sma_feedback_question.id AND sma_feedback_response.survey_id = ' . $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRespondenName()
    {
        $this->db->from('feedback, users');
        $this->db->where('feedback.user_id = users.id');
        $q=$this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSurveyCategories()
    {
        $q = $this->db->get('feedback_category');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCompaniesName()
    {
        $this->db->from('feedback, companies');
        $this->db->where('feedback.company = companies.company');
        $q=$this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesPersonById($id)
    {
        $this->db->select('sma_sales_person.id, sma_sales_person.name, sma_sales_person.reference_no, sma_sales_person.phone, sma_sales_person.email, COUNT(sma_users.sales_person_id) AS total_customer');
        $this->db->join('sma_users', 'sma_users.sales_person_id = sma_sales_person.id', 'left');
        if (!$this->Owner) {
            $this->db->where('sma_sales_person.company_id', $this->session->userdata('company_id'));
        }
        $this->db->where('sma_sales_person.id', $id);
        $this->db->where('sma_sales_person.is_deleted', NULL);
        $q = $this->db->get('sma_sales_person');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getListUsersSalesPersonById($id)
    {
        $this->db->select('sma_users.id, sma_users.company, sma_companies.name, sma_users.phone, sma_companies.cf1, sma_users.created_on, sma_users.active, sma_users.sales_person_id');
        $this->db->join('sma_companies', 'sma_users.company_id = sma_companies.id', 'left');
        $this->db->where('sma_users.sales_person_id', $id);
        $q = $this->db->get('sma_users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}

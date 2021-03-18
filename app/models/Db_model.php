<?php defined('BASEPATH') or exit('No direct script access allowed');

class Db_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getLatestSales()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin && !$this->Principal) {
            $this->db->where("sales.created_by", $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }

        $this->db->order_by("sales.id", 'desc');
        $q = $this->db->get("sales", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLastestQuotes()
    {
        $this->db->select('quotes.*');
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin && !$this->Principal) {
            $this->db->where('quotes.created_by', $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Principal) {
            $this->db->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left');
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->order_by('quotes.id', 'desc');
        $q = $this->db->get("quotes", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLastestDeliveriesSmig()
    {
        $this->db->select('deliveries_smig.*');
        // if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin && !$this->Principal) {
        //     $this->db->where('quotes.created_by', $this->session->userdata('user_id'));
        // }
        if (!$this->Owner && !$this->Principal) {
            $this->db->join('warehouses', 'warehouses.id = deliveries_smig.warehouse_id', 'left');
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->order_by('deliveries_smig.id', 'desc');
        $q = $this->db->get("deliveries_smig", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestPurchases()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin && !$this->Principal) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->order_by('purchases.id', 'desc');
        $q = $this->db->get("purchases", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestTransfers()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin && !$this->Principal) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("transfers", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestCustomers()
    {
        $Kasir_n_Gudang = null;
        if ($this->session->userdata('group_id') == 5 || $this->session->userdata('group_id') == 8) {
            $Kasir_n_Gudang = true;
        }
        $table_db = "sma_companies";
        if ($Kasir_n_Gudang) {
            $table_db = $table_db . ", sma_warehouse_customer";
        }
        $this->db->from($table_db);
        if ($Kasir_n_Gudang) {
            $this->db->where('sma_companies.id = sma_warehouse_customer.customer_id');
            $this->db->where('sma_warehouse_customer.warehouse_id', $this->session->userdata('warehouse_id'));
        }

        $this->db->order_by('sma_companies.id', 'desc');
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('sma_companies.company_id', $this->session->userdata('company_id'));
            if ($Kasir_n_Gudang) {
                $this->db->where('sma_warehouse_customer.is_deleted', 0);
            }
        }

        $this->db->limit(5);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestSuppliers()
    {
        $this->db->order_by('id', 'desc');
        if (!$this->Owner && !$this->Principal) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        }
        $q = $this->db->get_where("companies", array('group_name' => 'supplier'), 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getChartData($prod, $brand)
    {
        $myQuery = "SELECT date_format(s.datess, '%b %Y') as month ,
        COALESCE(s.sales, 0) as sales,
        COALESCE(p.purchases, 0 ) as purchases,
        COALESCE(s.tax1, 0) as tax1,
        COALESCE(s.tax2, 0) as tax2,
        COALESCE(p.ptax, 0 ) as ptax,
        COALESCE((COALESCE(s.unit_qty, 0) ) / 1000,0) AS unit_qty,
        COALESCE((COALESCE(p.unit_qtyz, 0)) / 1000,0) AS unit_qtyz
        FROM (  SELECT 
                DATE_FORMAT(sma_sales.DATE, '%Y-%m') MONTH,
                sma_sales.id,
                sma_sales.DATE AS datess,
                SUM(sma_sale_items.subtotal) Sales,
                SUM(sma_sales.product_tax) tax1,
                SUM(sma_sales.order_tax) tax2,
                SUM((CAST(sma_products.weight AS INT))*(sma_sale_items.quantity)) unit_qty
                FROM " . $this->db->dbprefix('sales') . " ";
        $myQuery .= "   LEFT JOIN sma_sale_items ON sma_sales.id = sma_sale_items.sale_id
                        INNER JOIN sma_products ON sma_sale_items.product_id = sma_products.id";
        $myQuery .= " WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )";
        if ($prod != null) {
            $myQuery .= " and sma_sale_items.product_code = '$prod'";
        }
        if ($brand != null) {
            $myQuery .= " AND sma_products.brand = '$brand'";
        }
        if ($this->Settings->restrict_user && !$this->Principal) {
            $myQuery .= "AND " . $this->db->dbprefix('sales') . ".company_id = " . $this->session->userdata('company_id') . "";
        }
        $myQuery .= " GROUP BY date_format(date, '%Y-%m')) s
                      LEFT JOIN (   SELECT sma_purchases.id,
                                    DATE_FORMAT(sma_purchases.DATE, '%Y-%m') MONTH,
                                    SUM(sma_purchases.product_tax) ptax,
                                    SUM(sma_purchases.order_tax) otax,
                                    SUM(sma_purchase_items.subtotal) purchases,
                                    SUM(ABS(CAST(sma_products.weight AS INT)) * ABS(sma_purchase_items.quantity)) AS unit_qtyz
                                    FROM " . $this->db->dbprefix('purchases');
        $myQuery .= "   LEFT JOIN sma_purchase_items ON sma_purchases.id = sma_purchase_items.purchase_id
                        INNER JOIN sma_products ON sma_purchase_items.product_id = sma_products.id";
        if ($prod != null) {
            $myQuery .= " WHERE sma_purchase_items.product_code = '$prod'";
        }
        if ($brand != null) {
            $myQuery .= " AND sma_products.brand = '$brand'";
        }
        if ($this->Settings->restrict_user  && !$this->Principal) {
            $myQuery .= " WHERE " . $this->db->dbprefix('purchases') . ".company_id = " . $this->session->userdata('company_id');
        }
        $myQuery .= " GROUP BY date_format(date, '%Y-%m')) p ON s.Month = p.Month";
        $myQuery .= " ORDER BY s.Month";

        $q = $this->db->query($myQuery);
        // echo $this->db->last_query();
        // die;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getChartData2($prod)
    {
        $myQuery = "SELECT date_format(S.datess, '%b %Y') as month ,
        COALESCE(S.sales, 0) as sales,
        COALESCE( P.purchases, 0 ) as purchases,
        COALESCE(S.tax1, 0) as tax1,
        COALESCE(S.tax2, 0) as tax2,
        COALESCE( P.ptax, 0 ) as ptax
        FROM (  
                SELECT 
                DATE_FORMAT(sma_sales.DATE, '%Y-%m') MONTH,
                sma_sales.id,
                sma_sales.DATE AS datess,
                SUM(A.subtotal) Sales,
                SUM(sma_sales.product_tax) tax1,
                SUM(sma_sales.order_tax) tax2
                FROM " . $this->db->dbprefix('sales') . " ";
        $myQuery .= "LEFT JOIN
                    (
                        SELECT
                            sale_id,
                            SUM(subtotal) AS subtotal
                        FROM
                            sma_sale_items
                        WHERE quantity >= 0 
                        GROUP BY
                            sale_id
                    ) A
                    ON
                        sma_sales.id = A.sale_id";

        $myQuery .= " WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )";

        if ($this->Settings->restrict_user && !$this->Principal) {
            $myQuery .= "AND " . $this->db->dbprefix('sales') . ".company_id = " . $this->session->userdata('company_id') . "";
        }
        $myQuery .= " GROUP BY date_format(date, '%Y-%m')) S
            LEFT JOIN ( SELECT sma_purchases.id,
                        DATE_FORMAT(sma_purchases.DATE, '%Y-%m') MONTH,
                        SUM(sma_purchases.product_tax) ptax,
                        SUM(sma_purchases.order_tax) otax,
                        SUM(Z.subtotal) purchases
                        FROM " . $this->db->dbprefix('purchases');
        $myQuery .= " LEFT JOIN
            (
                SELECT
                    purchase_id,
                   
                    SUM(subtotal) AS subtotal
                FROM
                    sma_purchase_items
                GROUP BY
                    purchase_id
            ) Z
            ON
                sma_purchases.id = Z.purchase_id";
        if ($this->Settings->restrict_user  && !$this->Principal) {
            $myQuery .= " WHERE " . $this->db->dbprefix('purchases') . ".company_id = " . $this->session->userdata('company_id');
        }
        $myQuery .= " GROUP BY date_format(date, '%Y-%m')) P
            ON S.Month = P.Month";
        //        $myQuery.=" LEFT JOIN
        //                    (
        //                    SELECT
        //                        sale_id,
        //                        SUM(unit_quantity) AS unit_qty
        //                    FROM
        //                        sma_sale_items";
        //        if ($prod !=null){
        //            $myQuery.=" where product_name = '$prod'";
        //        }
        //        $myQuery.=" GROUP BY
        //                        sale_id
        //                ) Q
        //                ON
        //                    S.id = Q.sale_id";
        $myQuery .= " ORDER BY S.Month";

        //         echo $myQuery;die;
        $q = $this->db->query($myQuery);
        //        var_dump($myQuery);die;

        //        var_dump('asd');die;
        //        $this->db->select_sum('quantity')
        //            ->group_by(date_format('tanggal_transaksi', '%Y-%m'))
        //            ->from('sma_v_sales_aksestoko');
        //        var_dump($q);die;
        //        $d = $this->db->get();
        //
        //        foreach (($d->result()) as $row) {
        //            $data2[] = $row;
        //        }
        //        var_dump($data2);die;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockValue()
    {
        $q = $this->db->query("SELECT SUM(qty*price) as stock_by_price, SUM(qty*cost) as stock_by_cost
        FROM (
            Select sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0)) as qty, price, cost
            FROM " . $this->db->dbprefix('products') . "
            JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id
            GROUP BY " . $this->db->dbprefix('warehouses_products') . ".id ) a");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBestSeller($start_date = null, $end_date = null, $company_id = null)
    {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('first day of this month')) . ' 00:00:00';
        }
        if (!$end_date) {
            $end_date = date('Y-m-d', strtotime('last day of this month')) . ' 23:59:59';
        }

        $this->db
            ->select("product_name, product_code")
            ->select_sum('quantity')
            ->from('sale_items')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('date >=', $start_date)
            ->where('date <', $end_date)
            ->group_by('product_name, product_code')
            ->order_by('sum(quantity)', 'desc')
            ->limit(10);
        if ($company_id && $company_id != 1) {
            $this->db->where('sales.biller_id', $company_id);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getDataDistributor($dist)
    {
        $tr = "(SELECT distributor, COUNT(distinct(ibk)) AS transaksi FROM sma_v_sales_aksestoko where sale_status = 'completed' GROUP BY distributor) tr";
        $this->db
            ->select("atv.distributor, COUNT(atv.distributor) as registrasi, tr.transaksi")
            ->from('sma_v_aktivasi_aksestoko atv')
            ->join($tr, 'atv.distributor = tr.distributor ', 'left')
            ->group_by('atv.distributor')
            ->order_by('registrasi desc');
        if ($dist) {
            $this->db->where_in('atv.distributor', $dist);
        } else {
            $this->db->limit(10);
        }

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDataProvinsi($prov)
    {
        $tr = "(SELECT distributor.country, count(distinct(akun.username)) AS transaksi FROM sma_sales sales
        JOIN sma_users akun ON akun.id = sales.created_by
        JOIN sma_warehouses gudang ON sales.warehouse_id = gudang.id
        LEFT JOIN sma_companies distributor ON sales.biller_id = distributor.id 
    WHERE
         ( ( sales.client_id = 'aksestoko' ) AND ( sales.biller_id <> " . $this->session->userdata('biller_id') . " ) AND sales.sale_status = 'completed' ) 
    group by distributor.country) tr";

        $this->db
            ->select("atv.provinsi, COUNT(atv.provinsi) as registrasi, IF(tr.transaksi is null,0,tr.transaksi) as transaksi")
            ->from('sma_v_aktivasi_aksestoko atv')
            ->join($tr, 'atv.provinsi = tr.country', 'left')
            ->group_by('atv.provinsi')
            ->order_by('registrasi desc');
        if ($prov) {
            $this->db->where_in('atv.provinsi', $prov);
        } else {
            $this->db->limit(10);
        }

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDataByDate($start = null, $end = null)
    {
        if (!$start && !$end) {
            $start = date('Y-m-d', strtotime('-30 day'));
            $end = date("Y-m-d");
        }
        $tr = "(select T2.tanggal_aktivasi, count(T2.tanggal_aktivasi) as aktivasi from sma_v_aktivasi_aksestoko T2 group by T2.tanggal_aktivasi) T3";
        $this->db
            ->select("T1.tanggal_transaksi, count(T1.tanggal_transaksi) as transaksi, T3.aktivasi as registrasi")
            ->from('sma_v_sales_aksestoko T1')
            ->join($tr, 'T1.tanggal_transaksi = T3.tanggal_aktivasi', 'left')
            ->group_by('T1.tanggal_transaksi')
            ->where('T1.sale_status ', 'completed')
            ->order_by('T1.tanggal_transaksi asc')
            ->where("T1.tanggal_transaksi between '$start' and '$end' ");
        //->where('T1.tanggal_transaksi <', $end);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getDataProvince()
    {
        $this->db
            ->select("province_name")
            ->from('sma_indonesia')
            ->group_by('province_name');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDataProduct()
    {
        $this->db
            ->select("name,code")
            ->from('sma_products')
            ->where('company_id != ', 6)
            ->where('name like ', 'semen%')
            ->order_by('name')
            ->group_by('code');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDataBrand()
    {
        $this->db
            ->select("name,id")
            ->from('sma_brands')
            ->where('client_id', 1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDataMap($prod)
    {

        if ($prod == null) {
            //menghitung companies dalam suatu provinsi
            $this->db
                ->select("country as id,COUNT(id) as value")
                ->where_not_in('country', ['-', ''])
                ->from('sma_companies')
                ->group_by('country')
                ->order_by('COUNT(id)', 'desc');
        } else {
            $query = "
                SELECT
                    sma_users.country as id,
                    cast(sum(A.qty)/1000 as INTEGER) as value
                FROM
                    sma_users
                LEFT JOIN
                    (
                        SELECT
                            sma_sales.id AS id,
                            sma_sales.created_by as created_by,
                            SUM(B.qty) AS qty
                        FROM
                            sma_sales
                        LEFT JOIN
                            (
                                SELECT 
                                    SUM(
                                        ExtractNumber(sma_sale_items.product_name) * ABS(sma_sale_items.quantity)
                                    ) AS qty,
                                    sma_sale_items.sale_id
                                FROM
                                    sma_sale_items
                                WHERE
                                    sma_sale_items.quantity >= 0 
                                    AND";
            $query .= " sma_sale_items.product_code = '$prod'
                                GROUP BY
                                    sma_sale_items.sale_id
                            ) B ON sma_sales.id = B.sale_id
                        GROUP BY
                            sma_sales.created_by
                    ) A ON sma_users.id = A.created_by
                WHERE
                    sma_users.country NOT IN('', '-')
                GROUP BY
                    country
                ORDER BY value DESC
                ";
            //menghitung barang dalam suatu provinsi
            //            echo $query;die();
            $q = $this->db->query($query);
            return $q->result();
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDataDistrib()
    {
        // $this->db
        //     ->select("distributor")
        //     ->from('sma_v_sales_aksestoko')
        //     ->group_by('distributor');
        // $q = $this->db->get();

        $q = $this->db->query('select distributor from sma_v_sales_aksestoko union select distributor from sma_v_aktivasi_aksestoko group by distributor');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTotal($tipe)
    {
        if ($tipe == 'total_aktivasi') {
            $this->db
                ->select("count(*) as jumlah")
                ->from('sma_v_aktivasi_aksestoko');
        }
        if ($tipe == 'total_transaksi') {
            $this->db
                ->select("count(tanggal_transaksi) as jumlah")
                ->from('sma_v_sales_aksestoko')
                ->where('sale_status', 'completed');
        }
        if ($tipe == 'toko_transaksi') {
            $this->db
                ->select("count(distinct(ibk)) as jumlah")
                ->from('sma_v_sales_aksestoko')
                ->where('sale_status', 'completed');
        }
        if ($tipe == 'toko_repeat') {
            $sql = "(select count(ibk) total from sma_v_sales_aksestoko where sale_status = 'completed' group by ibk) count";
            $this->db->select("count(total) as jumlah")
                ->from($sql)
                ->where('total > 1');
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllUserAksestokoForPromotional()
    {
        $q = $this->db->query("
            select
            companies.id `customer_id`,
            upper(users.username) `customer_code`,
            upper(companies.company) `customer_store`,
            upper(concat(users.first_name, ' ', users.last_name)) `customer_name`,
            upper(users.phone) `customer_phone`,
            upper(companies.address) `customer_address`,
            upper(companies.country) `customer_province`,
            upper(companies.city) `customer_city`,
            upper(companies.state) `customer_district`,
            distributors.id `distributor_id`,
            upper(distributors.cf1) `distributor_code`,
            upper(distributors.company) `distributor_name`
            from sma_users users
            inner join sma_companies companies on users.company_id = companies.id
            left join sma_companies customers on companies.cf1 = customers.cf1 and customers.is_active = 1 and customers.is_deleted is null
            left join sma_companies distributors on distributors.id = customers.company_id
            where 
            companies.client_id = 'aksestoko'
            and companies.group_name = 'biller'
            and customers.group_name = 'customer'
            and distributors.id != 6
            and users.active = 1
            order by users.username asc
        ");
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getDistributorAksestokoForPromotional()
    {
        $q = $this->db->query("
            select
            distributors.id `distributor_id`,
            akun_dist.username `distributor_username`,
            upper(distributors.cf1) `distributor_code`,
            upper(distributors.company) `distributor_name`,
            upper(distributors.address) `distributor_address`,
            upper(distributors.country) `distributor_province`,
            upper(distributors.city) `distributor_city`,
            upper(distributors.state) `distributor_district`
            from sma_users users
            inner join sma_companies companies on users.company_id = companies.id
            left join sma_companies customers on companies.cf1 = customers.cf1 and customers.is_active = 1 and customers.is_deleted is null
            left join sma_companies distributors on distributors.id = customers.company_id
            left join sma_users akun_dist on akun_dist.company_id = distributors.id
            where 
            companies.client_id = 'aksestoko'
            and companies.group_name = 'biller'
            and customers.group_name = 'customer'
            and distributors.id != 6
            and distributors.is_deleted is null
            and users.active = 1
            group by distributors.id
            order by distributors.id asc
        ");
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getAksestokoSalesByIdbk($idbk)
    {
        $q = $this->db->query("select * from sma_v_sales_aksestoko where ibk = '$idbk'");
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function get_link_manualbook($table, $type)
    {

        if (count($type) == 1) {
            $this->db->where('type', $type[0]);
            $q = $this->db->get($table);
        } else if (count($type) > 1) {
            $this->db->where('type', $type[0]);
            for ($i = 1; $i < count($type); $i++) {
                $this->db->or_where('type', $type[$i]);
            }
            $q = $this->db->get($table);
        }

        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getLastVersionUpdate()
    {
        $q = $this->db->query("SELECT MAX({$this->db->dbprefix('updates')}.version_num) as last_version FROM {$this->db->dbprefix('updates')} WHERE is_active = 1");
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getChangeLog()
    {
        $q = $this->db->query("SELECT  
            updates.type as type,
            updates.name as name,
            DATE_FORMAT(updates.release_at, '%d %M %Y') as release_at,
            DATE_FORMAT(updates.release_at, '%k:%i') as clock_at,
            updates.release_at as sort,
            updates.version as version,
            updates.version_num as version_num,
            updates.desc as description
        FROM {$this->db->dbprefix('updates')} updates
        WHERE 
            updates.is_active = 1
        ORDER BY sort DESC");

        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getUpdateNotif()
    {
        $q = $this->db->query("SELECT  
            updates.type as type,
            updates.name as name,
            DATE_FORMAT(updates.release_at, '%d %M %Y') as release_at,
            DATE_FORMAT(updates.release_at, '%k:%i') as clock_at,
            updates.release_at as sort,
            updates.version as version,
            updates.version_num as version_num,
            updates.desc as description
        FROM {$this->db->dbprefix('updates')} updates, {$this->db->dbprefix('users')} users
        WHERE 
            updates.is_active = 1 AND
            updates.version_num > users.last_update AND
            users.id = {$this->session->userdata('user_id')}
        ORDER BY sort DESC");

        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getActiveSurvey()
    {
        $this->db->select('id, repeat');
        $this->db->from('sma_feedback_category');
        $this->db->where('is_active = 1');
        $this->db->where('flag !=', 1);
        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function checkCustomerResponse()
    {
        $this->db->select('sma_feedback.user_id');
        $this->db->from('sma_feedback, sma_feedback_category');
        $this->db->where('sma_feedback.category_id = sma_feedback_category.id and sma_feedback.repeat >= sma_feedback_category.repeat and sma_feedback_category.is_active = 1 and sma_feedback.user_id = ' . $this->session->userdata('user_id'));
        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getActiveSurveyAT()
    {
        $this->db->select('id, repeat');
        $this->db->from('sma_feedback_category');
        $this->db->where('is_active = 1');
        $this->db->where('flag', 1);
        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getQuestion($survey_id)
    {
        $q = $this->db->where('category_id = ', $survey_id)
            ->where('is_active', 1)
            ->get('feedback_question');
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function addFeedback($data = array())
    {
        if ($this->db->insert('feedback', $data)) {
            return true;
        }
        return false;
    }

    public function getLastResponseID()
    {
        $q = $this->db->query("SELECT MAX(id) AS id FROM sma_feedback");
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addFeedbackResponse($data = array())
    {
        if ($this->db->insert_batch('feedback_response', $data)) {
            return true;
        }
        return false;
    }

    public function getFeedbackOption($question_id)
    {
        $q = $this->db->where('question_id', $question_id)
            ->where('is_active', 1)
            ->get('feedback_option');
        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
}

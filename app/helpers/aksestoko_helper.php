<?php defined('BASEPATH') OR exit('No direct script access allowed');
if(! function_exists('get_domain')) {
    function get_domain() {
        $url = $_SERVER['HTTP_HOST'];
        $host = (parse_url($url, PHP_URL_HOST) != '') ? (parse_url($url, PHP_URL_PORT) != '' ? parse_url($url, PHP_URL_HOST).':'.parse_url($url, PHP_URL_PORT) : parse_url($url, PHP_URL_HOST) )  : $url;
        return preg_replace('/^www\./', '', $host);
    }
}

if(! function_exists('aksestoko_route')) {
    function aksestoko_route($route) {
        if(get_domain() == AKSESTOKO_DOMAIN){
            $count = 1;
            $route = str_replace("aksestoko/", "", $route, $count);
        }
        return $route;
    }
}

if(! function_exists('convert_unit')) {
    function convert_unit($unit_name) {
        $new_unit_name = strtolower($unit_name);
        if(strpos($new_unit_name, 'sak') !== false){
            return 'SAK';
        } else if (strpos($new_unit_name, 'ton') !== false){
            return 'TON';
        }
        return $unit_name;
    }
}

if(! function_exists('convert_date')) {
    function convert_date($date) {
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
}

if (! function_exists('is_https')) {
    function is_https()
    {
        if (array_key_exists("HTTPS", $_SERVER) && 'on' === $_SERVER["HTTPS"]) {
            return true;
        }
        if (array_key_exists("SERVER_PORT", $_SERVER) && 443 === (int)$_SERVER["SERVER_PORT"]) {
            return true;
        }
        if (array_key_exists("HTTP_X_FORWARDED_SSL", $_SERVER) && 'on' === $_SERVER["HTTP_X_FORWARDED_SSL"]) {
            return true;
        }
        if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && 'https' === $_SERVER["HTTP_X_FORWARDED_PROTO"]) {
            return true;
        }
        return false;
    }
}

if (! function_exists('terbilang')) {
    function terbilang($nilai) {
        if($nilai<0) {
            $hasil = "minus ". trim(penyebut($nilai));
        } else {
            $hasil = trim(penyebut($nilai));
        }           
        return $hasil;
    }
}

if (! function_exists('penyebut')) {
    function penyebut($nilai){
        if($nilai > 999999999){
            if ($nilai < 1000000000000) {
                return round($nilai /1000000000, 2) . "M";
            }else if ($nilai >999999999) {
                return round($nilai /1000000000000, 2)."T";
            }
        }

        return number_format($nilai, 0, ',', '.');
    }
}

if (! function_exists('payment_tmp_ref')) {
    function payment_tmp_ref(){
        
        $ref = sprintf( '%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
        $date = date('Y/m');
        return 'PAY/TMP/'.$date.'/'.strtoupper($ref);
    }
}
if (! function_exists('status_retailer')) {
    function status_retailer($status, $param = 0)
    {
        switch ($status) {
        case "ordered":
            return ["Menunggu Konfirmasi", "warning"];
        case "confirmed":
            return ["Dikonfirmasi", "success"];
        case "packing":
            return ["Sedang Dikemas", "warning"];
        case "delivering":
            return ["Dalam Pengiriman", "info"];
        case "delivered":
            return ["Barang Telah Dikirim", "success"];
        case "partial":
            if ($param == 0) {
                return ["Diterima Sebagian", "info"];
            } elseif ($param == 1) {
                return ["Dibayar Sebagian", "info"];
            } elseif ($param == 2) {
                return ["Menunggu Pelunasan", "info"];
            }
            // no break
        case "received":
            return ["Diterima", "success"];
        case "pending":
            if ($param == 0) {
                return ["Belum Bayar", "warning"];
            } elseif ($param == 1) {
                return ["Belum Lunas", "warning"];
            } elseif ($param == 2) {
                return ["Menunggu Konfirmasi", "warning"];
            }

            // no break
        case "waiting":
            if ($param == 1) {
                return ["Kredit Ditinjau", "info"];
            } elseif ($param == 0) {
                return ["Menunggu Konfirmasi", "warning"];
            } elseif ($param == 2) {
                return ["Menunggu Pelunasan", "info"];
            }

            // no break
        case "paid":
            return ["Telah Dibayar", "success"];
        case "canceled":
            return ["Dibatalkan", "danger"];
        case "accept":
            if ($param == 2) {
                return ["Kredit Diterima", "success"];
            } elseif ($param == 1) {
                return ["Kredit Diterima", "info"];
            } elseif ($param == 0) {
                return ["Diterima", "success"];
            } elseif ($param == 1001) {
                return ["Diterima", "success"];
            }
            // no break
        case "reject":
            if ($param == 1) {
                return ["Kredit Ditolak", "danger"];
            } elseif ($param == 0) {
                return ["Ditolak", "danger"];
            } elseif ($param == 2) {
                return ["Ditolak", "danger"];
            }
            // no break
        case "cash before delivery":
            return ["Bayar Sebelum Dikirim", ""];
        case "kredit":
            return ["Tempo dengan Distributor", ""];
        case "kredit_pro":
            return ["Kredit Pro", ""];
        case "cash on delivery":
            return ["Bayar Di Tempat", ""];

        case 'pickup':
            return ["Pengambilan Sendiri", ""];
        case 'delivery':
            return ["Pengiriman Distributor", ""];
    }
        return ["Status Tidak Diketahui", "danger"];
    }
}
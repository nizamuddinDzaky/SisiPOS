
<?php defined('BASEPATH') or exit('No direct script access allowed');

use Ramsey\Uuid\Uuid;

if (!function_exists('getUuid')) {
    function getUuid()
    {
        return Uuid::uuid4();
    }
}

if (!function_exists('oneToTwoDArray')) {
    function oneToTwoDArray($oneArray, $delimiter = ":")
    {
        $twoArray = [];
        foreach ($oneArray as $st) {
            $explode = explode($delimiter, $st);
            $twoArray[trim($explode[0])] = trim($explode[1]);
        }
        return $twoArray;
    }
}

if (!function_exists('url_image_thumb')) {
    function url_image_thumb($url, $thumb = true)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else {
            return  $thumb ? base_url("assets/uploads/thumbs/" . $url) : base_url("assets/uploads/" . $url);
        }
    }
}

if (!function_exists('avatar_image')) {
    function avatar_image($url, $gender)
    {
        if(!$url) {
            return base_url('assets/images/' . $gender . '.png');
        }else if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else {
            return base_url('assets/uploads/avatars/'. $url);
        }
    }
}

if (!function_exists('avatar_image_logo')) {
    function avatar_image_logo($url, $logo)
    {
        if(!$url) {
            return base_url('assets/uploads/logos/' . $logo);
        }else if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else {
            return base_url('assets/uploads/avatars/'. $url);
        }
    }
}

if (!function_exists('validate_url')) {
    function validate_url($url, $url_assets = null)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else {
            return $url_assets ?? base_url($url);
        }
    }
}

if (!function_exists('customer_logo_thumb')) {
    function customer_logo_thumb($url, $thumb = true)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else {
            return  $thumb ? base_url("assets/uploads/avatar/thumbs/" . $url) : base_url("assets/uploads/avatar/" . $url);
        }
    }
}

if (!function_exists('code_generator')) {
    function code_generator()
    {

        $ref = sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
        return strtoupper($ref);
    }
}
if (!function_exists('human_filesize')) {
    function human_filesize($bytes, $decimals = 2)
    {
        // var_dump($bytes);die;
        $bytes = (float) $bytes;
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}
if (!function_exists('diff_two_date')) {
    function diff_two_date($start, $end = null)
    {
        $your_date = strtotime($start);
        $now = $end ? strtotime($end) : time();
        $datediff = $now - $your_date;

        return round($datediff / (60 * 60 * 24));
    }
}
?>
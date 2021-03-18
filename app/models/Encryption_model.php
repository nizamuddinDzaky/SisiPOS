<?php defined('BASEPATH') or exit('No direct script access allowed');

class Encryption_model extends CI_Model
{

    function encrypt($text, $key)
    {
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($text, $cipher, $key, $options=0, $iv, $tag);
        return base64_encode($ciphertext.'::'.$tag.'::'.$iv);
    }

    function decrypt($text, $key)
    {
        $data = explode('::', base64_decode($text));
        $cipher = "aes-128-gcm";
        $tag = $data[1];
        $original_plaintext = openssl_decrypt($data[0], $cipher, $key, $options=0, $data[2] , $tag);
        return $original_plaintext;
    }
}

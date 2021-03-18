<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Language: English
 * Module: Notifications
 * 
 * Last edited:
 * 30th April 2015
 *
 * Package:
 * Stock Manage Advance v3.0
 * 
 * You can translate this file to your language. 
 * For instruction on new language setup, please visit the documentations. 
 * You also can share your language files by emailing to saleem@tecdiary.com 
 * Thank you 
 */

$lang['notification']               = "Pemberitahuan/Notifikasi";
$lang['add_notification']           = "Menambahkan Notifikasi";
$lang['edit_notification']          = "Mengedit Notifikasi";
$lang['delete_notification']        = "Menghapus Notifikasi";
$lang['delete_notifications']       = "Menghapus beberapa notifikasi ";
$lang['notification_added']         = "Berhasil menambahkan Notifikasi";
$lang['notification_updated']       = "berhasil merubah notifikasi";
$lang['notification_deleted']       = "Berhasil menghapus notifikasi";
$lang['notifications_deleted']      = "Berhasil menghapus beberapa notifikasi";
$lang['submitted_at']               = "Diisikan pada bagian";
$lang['till']                       = "Sampai";
$lang['comment']                    = "Komentar";
$lang['for_customers_only']         = "Hanya untuk pelanggan";
$lang['for_staff_only']             = "Hanya untuk pegawai";
$lang['for_both']                   = "hanya bisa digunakan 2";
$lang['till']                       = "Sampai";
$lang['limited_trx']                = "Anda telah mencapai batas transaksi (Maks xxx yyy / bulan)";
$lang['limited_master']             = "Anda telah mencapai batas master data (Maks xxx yyy)";


// Order Notifications
$lang['new_order_title']                        = "Pesanan Baru";
$lang['new_order_pos']                          = "Pesanan baru dari [[customer_name]] dengan nomor referensi [[reference_no]]";
$lang['confirm_order_title']                    = "Pesanan Dikonfirmasi";
$lang['confirm_order_aksestoko']                = "Pesanan anda dengan nomor referensi [[reference_no]] telah dikonfirmasi";
$lang['accept_order_title']                     = "Pesanan Disetujui";
$lang['accept_order_aksestoko']                 = "Pesanan anda dengan nomor referensi [[reference_no]] telah disetujui";
$lang['canceled_order_title']                   = "Pesanan Dibatalkan";
$lang['canceled_order_pos']                     = "Pesanan dari [[customer_name]] dengan nomor referensi [[reference_no]] telah dibatalkan";
$lang['canceled_order_aksestoko']               = "Pesanan anda dengan nomor referensi [[reference_no]] ditolak karena alasan berikut : [[note]]";

// Payment Notifications
$lang['new_payment_title']                      = "Pembayaran Dikonfirmasi";
$lang['new_payment_pos']                        = "Pesanan dari [[customer_name]] dengan nomor referensi [[reference_no]] telah dilakukan pembayaran sebanyak Rp.[[price]]";
$lang['new_payment_aksestoko']                  = "Pesanan atas order dengan nomor referensi [[reference_no]] telah dilakukan pembayaran sebanyak Rp.[[price]] oleh distributor";
$lang['reject_payment_title']                   = "Pembayaran Ditolak";
$lang['reject_payment_aksestoko']               = "Pembayaran anda atas order dengan nomor referensi [[reference_no]] ditolak, silahkan hubungi Distributor";
$lang['confirm_payment_title']                  = "Pembayaran Dikonfirmasi";
$lang['confirm_payment_aksestoko']              = "Pembayaran anda atas order dengan nomor refernsi [[reference_no]] telah dikonfirmasi";

// Delivery Notifications
$lang['packing_delivery_title']                 = "Pesanan Dikemas";
$lang['packing_delivery_aksestoko']             = "Pesanan anda dengan nomor referensi [[reference_no]] sedang dikemas";
$lang['delivering_delivery_title']              = "Pesanan Dikirim";
$lang['delivering_delivery_aksestoko']          = "Pesanan anda dengan nomor referensi [[reference_no]] dalam proses pengiriman";
$lang['confirm_received_all_delivery_title']    = "Konfirmasi Pengiriman";
$lang['confirm_received_all_delivery_pos']      = "Pengiriman dengan nomor referensi [[reference_no]] telah diterima oleh [[customer_name]]";
$lang['confirm_received_partial_delivery_title']= "Konfirmasi Pengiriman";
$lang['confirm_received_partial_delivery_pos']  = "Pengiriman dengan nomor referensi [[reference_no]] telah diterima sebagian, karena ada barang rusak";
$lang['confirm_return_delivery_title']          = "Pengembalian Barang";
$lang['confirm_return_delivery_aksestoko']      = "Pengembalian barang atas order dengan nomor referensi [[reference_no]] disetujui oleh distributor";
$lang['confirm_reject_delivery_title']          = "Pengembalian Barang";
$lang['confirm_reject_delivery_aksestoko']      = "Pengembalian barang atas order dengan nomor referensi [[reference_no]] ditolak oleh distributor";


// Price Update Notifications
$lang['new_update_price_title']                 = "Perubahan harga";
$lang['new_update_price_aksestoko']             = "Perubahan harga atas order dengan nomor referensi [[reference_no]], oleh distributor";
$lang['confirm_update_price_title']             = "Perubahan harga";
$lang['confirm_update_price_pos']               = "Perubahan harga atas order dengan nomor referensi [[reference_no]] telah disetujui oleh [[customer_name]]";

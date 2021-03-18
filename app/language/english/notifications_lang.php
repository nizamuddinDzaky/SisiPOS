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

$lang['notification']               = "Notifications";
$lang['add_notification']           = "Add Notifications";
$lang['edit_notification']          = "Edit Notifications";
$lang['delete_notification']        = "Delete Notifications";
$lang['delete_notifications']       = "Delete Notifications";
$lang['notification_added']         = "Notifications successfully added";
$lang['notification_updated']       = "Notifications successfully updated";
$lang['notification_deleted']       = "Notifications successfully deleted";
$lang['notifications_deleted']      = "Notifications successfully deleted";
$lang['submitted_at']               = "Submitted at";
$lang['till']                       = "Till";
$lang['comment']                    = "comment";
$lang['for_customers_only']         = "For Customers only";
$lang['for_staff_only']             = "For Staff only";
$lang['for_both']                   = "For Both";
$lang['till']                       = "Till";
$lang['limited_trx']                = "You have reached limited transaction (Max xxx yyy / month)";
$lang['limited_master']             = "You have reached limited master data (Max xxx yyy)";

// Order Notifications
$lang['new_order_title']                        = "New Sales Order";
$lang['new_order_pos']                          = "New Sales from [[customer_name]] with reference number [[reference_no]]";
$lang['confirm_order_title']                    = "Confirmed Order";
$lang['confirm_order_aksestoko']                = "Order with reference number [[reference_no]] has been Confirmed";
$lang['accept_order_title']                     = "Reserved Order";
$lang['accept_order_aksestoko']                 = "Order with reference number [[reference_no]] has been Reserved";
$lang['canceled_order_title']                   = "Canceled Sales Order";
$lang['canceled_order_pos']                     = "Sales Order from [[customer_name]] with reference number [[reference_no]] has been Canceled";
$lang['canceled_order_aksestoko']               = "Order with reference number [[reference_no]] has been Canceled because : [[note]]";

// Payment Notifications
$lang['new_payment_title']                      = "Confirmed Payment";
$lang['new_payment_pos']                        = "Sales Order from [[customer_name]] with reference number [[reference_no]] has been paid Rp. [[price]]";
$lang['new_payment_aksestoko']                  = "Order with reference number [[reference_no]] has been paid Rp. [[price]] from Distributor";
$lang['reject_payment_title']                   = "Canceled Payment";
$lang['reject_payment_aksestoko']               = "Payment for Order with reference number [[reference_no]] has been Canceled";
$lang['confirm_payment_title']                  = "Confirmed Payment";
$lang['confirm_payment_aksestoko']              = "Payment for Order with reference number [[reference_no]] has been Confirmed";

// Delivery Notifications
$lang['packing_delivery_title']                 = "Packing Order";
$lang['packing_delivery_aksestoko']             = "Order with reference number [[reference_no]] has been packing";
$lang['delivering_delivery_title']              = "Delivery Order";
$lang['delivering_delivery_aksestoko']          = "Order with reference number [[reference_no]] has been delivering";
$lang['confirm_received_all_delivery_title']    = "Confirmed Delivery";
$lang['confirm_received_all_delivery_pos']      = "Delivery with reference number [[reference_no]] has been delivered";
$lang['confirm_received_partial_delivery_title']= "Confirmed Delivery";
$lang['confirm_received_partial_delivery_pos']  = "Delivery with reference number [[reference_no]] has been partially accepted because bad product";
$lang['confirm_return_delivery_title']          = "Returned Delivery";
$lang['confirm_return_delivery_aksestoko']      = "Return for order with reference number [[reference_no]] has been Approved";
$lang['confirm_reject_delivery_title']          = "Returned Delivery";
$lang['confirm_reject_delivery_aksestoko']      = "Return for order with reference number [[reference_no]] has been Canceled";


// Price Update Notifications
$lang['new_update_price_title']                 = "Update Price";
$lang['new_update_price_aksestoko']             = "Price for Order with reference number [[reference_no]] has been Updated";
$lang['confirm_update_price_title']             = "Update Price";
$lang['confirm_update_price_pos']               = "Price for Sales Order with reference number [[reference_no]] has been confirmed";

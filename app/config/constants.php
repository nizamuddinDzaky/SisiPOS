<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
define('EXIT_SUCCESS', 0); // no errors
define('EXIT_ERROR', 1); // generic error
define('EXIT_CONFIG', 3); // configuration error
define('EXIT_UNKNOWN_FILE', 4); // file not found
define('EXIT_UNKNOWN_CLASS', 5); // unknown class
define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
define('EXIT_USER_INPUT', 7); // invalid user input
define('EXIT_DATABASE', 8); // database error
define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
define('_KEY_MAP', 'AIzaSyBP3-7OMYP0ZpTuc2Rj5m184oFwqprDC2o');
define('SB_CLIENT', 'SB-Mid-client-Z68UQBR6XWJ-H_-7');
define('SB_SERVER', 'SB-Mid-server-feCkI-2iv1MlomlnFfnXodOk');
define('_PAYMENT_TERM', 1440); // in minutes

define('AKSESTOKO_DOMAIN', getenv('AKSESTOKO_DOMAIN') ? getenv('AKSESTOKO_DOMAIN') : "www.aksestoko.id"); // aksestoko domain
define('AKSESTOKO_REDIRECT', getenv('AKSESTOKO_REDIRECT') ? (getenv('AKSESTOKO_REDIRECT') == "true" ? true : false ) : false); // aksestoko redirect

define('FORCAPOS_DOMAIN', getenv('FORCAPOS_DOMAIN') ? getenv('FORCAPOS_DOMAIN') : "pos.forca.id"); // forcapos domain
define('FORCAPOS_VERSION', getenv('FORCAPOS_VERSION') ? getenv('FORCAPOS_VERSION') : "4.4.0.0"); // forcapos version
define('FORCAPOS_COPYRIGHT', getenv('FORCAPOS_COPYRIGHT') ? getenv('FORCAPOS_COPYRIGHT') : "2017 - 2019"); // forcapos copyright

define('APP_TOKEN', getenv('APP_TOKEN') ? getenv('APP_TOKEN') : "IkGZciBEn2yeIUt6"); // app token for dekrip - enkrip
define('APP_API_TOKEN', getenv('APP_API_TOKEN') ? getenv('APP_API_TOKEN') : "J@NcRfUjXnZr4u7x"); // app api token for dekrip - enkrip

define('ATL_USERNAME', getenv('ATL_USERNAME') ? getenv('ATL_USERNAME') : "nellakharisma"); // aksestoko liferay username
define('ATL_PASSWORD', getenv('ATL_PASSWORD') ? getenv('ATL_PASSWORD') : "nellalovers"); // app api token for dekrip - enkrip
define('ATL_TOKEN', getenv('ATL_TOKEN') ? getenv('ATL_TOKEN') : "v5NDWKCPjcJJ5BXF6yvcDgi72RLB7MPhgyWvQ2euHiukTYaj"); // app api token for dekrip - enkrip

define('SERVER_QA', getenv('SERVER_QA') && getenv('SERVER_QA') == '1' ? true : false); // Untuk menandai Server QA

define('BK_INTEGRATION', getenv('BK_INTEGRATION') && getenv('BK_INTEGRATION') == '1' ? true : false); // Untuk memberi flag apakah menggunakan integrasi BK atau tidak
define('SOCKET_NOTIFICATION', getenv('SOCKET_NOTIFICATION') && getenv('SOCKET_NOTIFICATION') == '1' ? true : false); // Untuk memberi flag apakah menggunakan notification socket
define('SOCKET_TOKEN', getenv('SOCKET_TOKEN') ? getenv('SOCKET_TOKEN') : 'fCHWSrMbabUmb9OHeZwy9k5HPSPnHdiS'); // token untuk call notifikasi
define('SMS_SERVER', getenv('SMS_SERVER') ? getenv('SMS_SERVER') : 'rajasms'); // kondisi call sms
define('SMS_NOTIF', getenv('SMS_NOTIF') && getenv('SMS_NOTIF') == '1' ? true : false); // kondisi sms notif pesanan pada aksestoko
define('WA_NOTIF', getenv('WA_NOTIF') && getenv('WA_NOTIF') == '1' ? true : false); // kondisi wa notif pesanan pada aksestoko
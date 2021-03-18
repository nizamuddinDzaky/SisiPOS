<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

require_once( APPPATH .'helpers/aksestoko_helper.php');

switch ( get_domain() ) {
    case AKSESTOKO_DOMAIN:
        $route['default_controller'] = "home";
        $route['404_override'] = 'errors/error_404_at';
        $route['translate_uri_dashes'] = FALSE;
        // $route['home'] = 'aksestoko/home';
        
        $route['api'] = 'api';
        $route['api/(.*)'] = 'api/$1';

        $route['aksestoko'] = 'aksestoko';
        $route['aksestoko/(.*)'] = 'aksestoko/$1';
        $route['(.*)'] = 'aksestoko/$1';

        break;
    default:
        
        $route['default_controller'] = 'home';
        $route['404_override'] = 'errors/error_404';
        $route['translate_uri_dashes'] = FALSE;

        $route['users'] = 'auth/users';
        $route['users/create_user'] = 'auth/create_user';
        $route['users/profile/(:num)'] = 'auth/profile/$1';
        $route['login'] = 'auth/login';
        $route['login/(:any)'] = 'auth/login/$1';
        $route['logout'] = 'auth/logout';
        $route['logout/(:any)'] = 'auth/logout/$1';
        $route['register'] = 'auth/register';
        $route['forgot_password'] = 'auth/forgot_password';
        $route['sales/(:num)'] = 'sales/index/$1';
        $route['orders/(:num)'] = 'orders/index/$1';
        $route['products/(:num)'] = 'products/index/$1';
        $route['purchases/(:num)'] = 'purchases/index/$1';
        $route['quotes/(:num)'] = 'quotes/index/$1';
        $route['deliveries_smig/(:num)'] = 'deliveries_smig/index/$1';
        $route['billing_portal'] = 'billing_portal/subscription';

    break;
}
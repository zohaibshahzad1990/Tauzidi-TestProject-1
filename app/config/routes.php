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
|	https://codeigniter.com/user_guide/general/routing.html
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
$route['default_controller'] = 'authentication';
// $route['default_controller'] = 'welcome';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['admin/([a-zA-Z_-]+)/(:any)'] = '$1/admin/$2';
$route['admin/([a-zA-Z_-]+)/(:any)/(:any)'] = '$1/admin/$2/$3';
$route['admin/([a-zA-Z_-]+)/(:any)/(:any)/(:any)'] = '$1/admin/$2/$3/$4';
$route['admin/([a-zA-Z_-]+)/(:any)/(:any)/(:any)/(:any)'] = '$1/admin/$2/$3/$4/$5';
$route['admin/([a-zA-Z_-]+)/(:any)'] = '$1/admin/$2';
$route['admin/([a-zA-Z_-]+)'] = '$1/admin/index';
$route['admin/([a-zA-Z_-]+)'] = '$1/admin/index';

$route['manager/([a-zA-Z_-]+)/(:any)'] = '$1/manager/$2';
$route['manager/([a-zA-Z_-]+)/(:any)/(:any)'] = '$1/manager/$2/$3';
$route['manager/([a-zA-Z_-]+)/(:any)/(:any)/(:any)'] = '$1/manager/$2/$3/$4';
$route['manager/([a-zA-Z_-]+)/(:any)/(:any)/(:any)/(:any)'] = '$1/manager/$2/$3/$4/$5';
$route['manager/([a-zA-Z_-]+)/(:any)'] = '$1/manager/$2';
$route['manager/([a-zA-Z_-]+)'] = '$1/manager/index';
$route['manager/([a-zA-Z_-]+)'] = '$1/manager/index';

$route['ajax/([a-zA-Z_-]+)/(:any)'] = '$1/ajax/$2';
$route['ajax/([a-zA-Z_-]+)/(:any)/(:any)'] = '$1/ajax/$2/$3';
$route['ajax/([a-zA-Z_-]+)/(:any)/(:any)/(:any)'] = '$1/ajax/$2/$3/$4';
$route['ajax/([a-zA-Z_-]+)/(:any)/(:any)/(:any)/(:any)'] = '$1/ajax/$2/$3/$4/$5';
$route['ajax/([a-zA-Z_-]+)/(:any)/(:any)/(:any)/(:any)/(:any)'] = '$1/ajax/$2/$3/$4/$5/$6';
$route['ajax/([a-zA-Z_-]+)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = '$1/ajax/$2/$3/$4/$5/$6/$7';
$route['ajax/([a-zA-Z_-]+)/(:any)'] = '$1/ajax/$2';
$route['ajax/([a-zA-Z_-]+)'] = '$1/ajax/index';
$route['ajax/([a-zA-Z_-]+)'] = '$1/ajax/index';

$route['api/v1/auth/([a-zA-Z_-]+)'] = 'api/$1';
$route['api/v1/dashboard/([a-zA-Z_-]+)'] = 'dashboard/$1';
$route['api/v1/db_backdoor/([a-zA-Z_-]+)'] = 'db_backdoor/api/$1';
$route['api/v1/users/([a-zA-Z_-]+)'] = 'users/api/$1';
$route['api/v1/admin/([a-zA-Z_-]+)'] = 'users/api/$1';
$route['api/v1/roles/([a-zA-Z_-]+)'] = 'user_groups/api/$1';
$route['api/v1/teachers/([a-zA-Z_-]+)'] = 'schools/api/$1';
$route['api/v1/resources/([a-zA-Z_-]+)'] = 'resources/api/$1';
$route['api/v1/teachers/([a-zA-Z_-]+)'] = 'schools/api/$1';
$route['api/v1/teachers/invite/([a-zA-Z_-]+)'] = 'teachers/api/$1';
$route['api/v1/settings/([a-zA-Z_-]+)'] = 'settings/api/$1';
$route['api/v1/students/([a-zA-Z_-]+)'] = 'students/api/$1';
$route['api/v1/trips/([a-zA-Z_-]+)'] = 'trips/api/$1';
$route['api/v1/([a-zA-Z_-]+)/'] = '$1/api/$2';
$route['api/v1/([a-zA-Z_-]+)/(:any)'] = '$1/api/$2';
//$route['api/v1/email_templates/([a-zA-Z_-]+)'] = 'email_templates/api/$1';

$route['ajax/signin'] = 'ajax/signin';

$route['login'] = 'authentication/login';
$route['signin'] = 'authentication/signin';
$route['forgot_password'] = 'authentication/forgot_password';
$route['signup'] = 'authentication/signup';
$route['resend_verification_code'] = 'authentication/resend_verification_code';
$route['activate'] = 'authentication/activate';
$route['logout'] = 'authentication/logout';
$route['signout'] = 'authentication/signout';
$route['otp_login'] = 'authentication/otp_login';
$route['confirm_code'] = 'authentication/confirm_code';
$route['resend_activation_code'] = 'authentication/resend_activation_code';
$route['confirm_code/(:any)'] = 'authentication/confirm_code/$1';
$route['reset_password'] = 'authentication/reset_password';
$route['reset_password/(:any)'] = 'authentication/reset_password/$1';
$route['signup/(:any)'] = 'authentication/signup/$1';
$route['terms_and_conditions'] = 'pages/terms_and_conditions';
$route['privacy'] = 'pages/privacy';




<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
  DESARROLLADO POR JORGE MENDEZ
  programandoweb.net
  info@programandoweb.net
  Colombia - Venezuela - Chile
*/

$route['ApiRest/(get|post|push|delete)']  = 'ApiRest/apirequest';
$route['default_controller']   = 'ApiRest';
$route['404_override'] 				 = 'ApiRest';
$route['translate_uri_dashes'] = FALSE;

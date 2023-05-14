<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Store your secret key here
// Make sure you use better, long, more random key than this
$url = APPPATH .'third_party/certs/api_private.key';
$privateKey = file_get_contents($url);
$config['jwt_key'] = $privateKey;
//require_once APPPATH . 'third_party/stream.php';
/* end JWT secret token key */
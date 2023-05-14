<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| SendGrid Setup
|--------------------------------------------------------------------------
*/
$config['protocol']	= 'smtp';
$config['smtp_port']	= '587';
$config['smtp_host']	= 'smtp.sendgrid.net';
$config['smtp_user']	= '';
$config['smtp_pass']	= '';
$config['newline']	= '\r\n';
$config['mailtype']	= 'html';
$config['charset']	= 'iso-8859-1';
$config['crlf']	= '\r\n';

// sendgrid token
$config['apiKey']	= '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';
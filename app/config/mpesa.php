<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * MPESA API (**DO NOT EDIT**)
 */
$config['live_url'] = "https://api.safaricom.co.ke/mpesa/";
$config['sandbox_url'] = "https://sandbox.safaricom.co.ke/mpesa/";

/**
 * MPESA Oauth Token generator URL (**DO NOT EDIT**)
 */
$config['live_token_url'] = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$config['sandbox_token_url'] = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

/**
 * MPESA API credentials(**EDIT VALUES**)
 */
$config['consumer_key'] = "";
$config['consumer_secret'] = "";
/* checkout */
$config['checkout_consumer_key'] = "";
$config['checkout_consumer_secret'] = "";
$config['lipa_na_mpesa_passkey'] = "";
$config['lipa_na_live_mpesa_passkey'] = "";

/**
 * APP settings
 */
$config['application_status'] = "sandbox"; //values: live, sandbox

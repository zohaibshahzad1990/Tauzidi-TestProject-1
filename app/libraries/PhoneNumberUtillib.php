<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "./assets/vendor/autoload.php"; 

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;



class PhoneNumberUtillib
{
    protected $ci;

    public function __construct()
    {
       $this->ci= & get_instance();
       $this->ci->load->model('countries/countries_m');
    }

    public function parse($phoneNumber = '', $countrycode = 'KE')
    {
        return ( PhoneNumberUtil::getInstance()->parse($phoneNumber, $countrycode));
    }

    public function formatNationalNumber($phoneNumber = '', $countryID = '')
    {
        $country_code = $this->ci->countries_m->get($countryID)->country_code;
        $phoneNumberInstance = $this->parse($phoneNumber, $country_code);
        return ( PhoneNumberUtil::getInstance()->formatNationalNumberWithCarrierCode($phoneNumberInstance, '') );
    }

    public function isvalidnumber($phoneNumber = '', $countryID = '')
    {
        if($country = $this->ci->countries_m->get($countryID)){
            $countrycode = $country->countrycode;
        }else{
            $countrycode = 'KE'; //set default to kenya
        }
        $phoneNumberInstance = $this->parse($phoneNumber,$countrycode);
        return ( PhoneNumberUtil::getInstance()->isValidNumber($phoneNumberInstance) );

    }
}

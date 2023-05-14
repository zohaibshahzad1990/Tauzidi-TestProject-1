<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Billing extends Public_Controller{

	function __construct(){
        parent::__construct();
        $this->load->model('billing/billing_m');
        $this->load->library('billing_manager');
    }

    function automated_subscription_billing_invoice($date=0,$limit=0){
        $this->billing_manager->automated_user_billing_invoice($date,$limit);
    }

    function automated_past_subscription_billing_invoice($date=0,$limit=0){
        $this->billing_manager->automate_past_user_billing_invoices($date,$limit);
    }

    function set_billing_dates(){
        die();
        $this->users_m->fix_billing_date_fields();
    }


    function fix_user_arrears($limit=0){
        die();
        $this->billing_manager->fix_user_billing_arrears($limit);
    }

    function pay_invoices_from_overpayments($date=0,$limit=0){
        $this->billing_manager->pay_invoices_from_overpayments($date,$limit);        
    }


    

}?>
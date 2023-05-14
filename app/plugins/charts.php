<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session Plugin
 *
 * Read and write session data
 *
 * @package		PyroCMS
 * @author		PyroCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, PyroCMS
 *
 */

class Plugin_Charts extends Plugin {

        /**
         * Current uri string
         *
         * Usage:
         * {pyro:url:current}
         *
         * @param	array
         * @return	array
         */
        function current()
        {
                return site_url($this -> uri -> uri_string());
        }

        /**
         *
         * site URL of the install
         *
         * Usage:
         * {pyro:url:site}
         *
         * @param	array
         * @return	array
         */
        function site()
        {
                $uri = $this -> attribute('uri');

                return $uri ? site_url($uri) : rtrim(site_url(), '/') . '/';
        }

        /**
         *
         * base URL of the install
         *
         * Usage:
         * {pyro:url:base}
         *
         * @param	array
         * @return	array
         */
        function base()
        {
                return base_url();
        }

        /**
         *
         * Pick a segment and provide a default if nothing there
         *
         * Usage:
         * {pyro:url:segments segment="1" default="home"}
         *
         * @param	array
         * @return	array
         */
        function segments()
        {
                $default = $this -> attribute('default');
                $segment = $this -> attribute('segment');

                return $this -> uri -> segment($segment, $default);
        }

        /**
         * build an anchor tag
         *
         * Usage:
         * {pyro:url:anchor segments="users/login" title="Login" class="login"}
         *
         * @param	array
         * @return	string
         */

	//Get Total of the withdrawals

	function get_total_withdrawals()
	{
 		$this->db->select_sum('bank_withdrawals.amount')
				->where('member_groups.slug', $this->chamaslug)
				->join('member_groups', 'member_groups.id=bank_withdrawals.chama_id')
				->get('bank_withdrawals')
				->row_array();
	}

        /*function anchor()
        {
                $segments = $this -> attribute('segments');
                $title = $this -> attribute('title', '');
                $class = $this -> attribute('class', '');

                $class = ! empty($class) ? 'class="' . $class . '"' : '';

                return anchor($segments, $title, $class);
        }

        function posts($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');




                return $this -> db
                        -> select('chama_member_contribution.*,chama_contribution_types.title as chama_contribution_type_title,first_name,last_name')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('users', 'chama_member_contribution.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_member_contribution.chama_id')
                        -> join('chama_contribution_types', 'chama_contribution_types.id=chama_member_contribution.chama_contribution_type')
                        -> limit($limit)
                        -> get('chama_member_contribution')
                        -> result_array();
        }

        function chart_data($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');

                return $this -> db
                        -> select('FROM_UNIXTIME(contribution_date,\'' . "%M" . '\') as contribution_month,sum(contributed_amount) as total_amount, chama_contribution_types.title as chama_contribution_type_title', FALSE)
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('users', 'chama_member_contribution.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_member_contribution.chama_id')
                        -> join('chama_contribution_types', 'chama_contribution_types.id=chama_member_contribution.chama_contribution_type')
                        -> limit($limit)
                        -> group_by('contribution_month')
                        -> order_by('total_amount', 'ASC')
                        -> get('chama_member_contribution')
                        -> result_array();
        }

        function chart_data2($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');

                return $this -> db
                        -> select('FROM_UNIXTIME(event_date,\'' . "%M" . '\') as event_month,sum(investment_amount) as total_amount, chama_investments.title', FALSE)
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('member_groups', 'member_groups.id=chama_investments.chama_id')
                        -> limit($limit)
                        -> group_by('event_month')
                        -> order_by('total_amount', 'ASC')
                        -> get('chama_investments')
                        -> result_array();
        }

        function chart_data3($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');

                return $this -> db
                        -> select('FROM_UNIXTIME(event_date,\'' . "%M" . '\') as event_month,FROM_UNIXTIME(event_date,\'' . "%Y" . '\') as event_year,sum(expense_amount) as total_amount', FALSE)
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('member_groups', 'member_groups.id=chama_expenses.chama_id')
                        -> where('FROM_UNIXTIME(event_date,\'' . "%Y" . '\')', "'" . date('Y') . "'", FALSE)
                        -> limit($limit)
                        -> group_by('event_month')
                        -> order_by('total_amount', 'ASC')
                        -> get('chama_expenses')
                        -> result_array();
        }

        function mine($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $this -> load -> library('ion_auth');
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');
                $user_id = $this -> ion_auth -> get_user() -> id;


                return $this -> db
                        -> select('chama_member_contribution.*,chama_contribution_types.title as chama_contribution_type_title,first_name,last_name')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> where('users.id', $user_id)
                        -> join('users', 'chama_member_contribution.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_member_contribution.chama_id')
                        -> join('chama_contribution_types', 'chama_contribution_types.id=chama_member_contribution.chama_contribution_type')
                        -> limit($limit)
                        -> get('chama_member_contribution')
                        -> result_array();
        }

        function balance($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $this -> load -> library('ion_auth');
                $order = $this -> attribute('order', 'id');
                $order = 'chama_member_profile.' . $order;
                $this -> chamaslug = $this -> attribute('chamaslug');
                $user_id = $this -> ion_auth -> get_user() -> id;


                return $this -> db
                        -> select('chama_member_profile.*,first_name,last_name')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('member_groups', 'member_groups.id=chama_member_profile.chama_id')
                        -> join('users', 'chama_member_profile.user_id=users.id')
                        -> order_by($order)
                        -> limit($limit)
                        -> get('chama_member_profile')
                        -> result_array();
        }

        function total_contributions()
        {

                return $this -> db -> select_sum('regular_con.balance')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=regular_con.chama_id')
                        -> get('regular_con')
                        -> row_array();
        }

        function bank_balance()
        {
                return $this -> db -> select_sum('bank_accounts.balance')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=bank_accounts.chama_id')
                        -> get('bank_accounts')
                        -> row_array();
        }

        function active_chama()
        {
                return $this -> db -> select('title')
                        -> where('id', $this -> chama -> id)
                        -> get('member_groups')
                        -> row_array();
        }
*/
      /*  function pending_payments()
        {
                $this -> chamaslug = $this -> attribute('chamaslug');

                return $this -> db -> select_sum('amount')
                        -> where('invoice_con.status', 0)
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('member_groups', 'member_groups.id=invoice_con.chama_id')
                        -> get('invoice_con')
                        -> row_array();
        }*/
/*
        function current_assets()
        {

                return $this -> db -> select_sum('chama_accounts.starting_balance')
                        -> where('chama_accounts.account_type_id', 1)
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=chama_accounts.chama_id')
                        -> get('chama_accounts')
                        -> result_array();
        }


        function fixed_assets()
        {

                return $this -> db -> select_sum('chama_accounts.starting_balance')
                        -> where('chama_accounts.account_type_id', 2)
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=chama_accounts.chama_id')
                        -> get('chama_accounts')
                        -> result_array();
        }

        function current_liabilities()
        {

                return $this -> db -> select_sum('chama_accounts.starting_balance')
                        -> where('chama_accounts.account_type_id', 3)
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=chama_accounts.chama_id')
                        -> get('chama_accounts')
                        -> result_array();
        }

        function long_term_liabilities()
        {

                return $this -> db -> select_sum('chama_accounts.starting_balance')
                        -> where('chama_accounts.account_type_id', 5)
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=chama_accounts.chama_id')
                        -> get('chama_accounts')
                        -> result_array();
        }

        function bank_accounts()
        {
                return $this -> db -> select('bank_accounts.*')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=bank_accounts.chama_id')
                        -> get('bank_accounts')
                        -> result_array();
        }
		function contribution_accounts()
        {
                return $this -> db -> select('regular_con.*')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=regular_con.chama_id')
                        -> get('regular_con')
                        -> result_array();
        }*/
/*
        function pending_invoices()
        {

                return $this -> db -> select_sum('invoices.balance')
                        -> where('invoices.status', 'UNPAID')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=invoices.chama_id')
                        -> get('invoices')
                        -> row_array();
        }*/
/*
        function invoices_due_today()
        {

                return $this -> db -> select_sum('invoices.balance')
                        -> where('invoices.status', 'UNPAID')
                        -> where("DATE_FORMAT(FROM_UNIXTIME(due_date),'%y-%m-%d') = DATE_FORMAT(NOW(),'%y-%m-%d' ) ", NULL, FALSE)
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=invoices.chama_id')
                        -> get('invoices')
                        -> row_array();
        }

        function next_30_days()
        {
                $type = $this -> attribute('stype');
                if ($type == 'inv')
                {
                        return $this -> db -> select_sum('invoices.balance')
                                -> where('invoices.status', 'UNPAID')
                                -> where("due_date < NOW() + INTERVAL 30 DAY ", NULL, FALSE)
                                -> where('member_groups.id', $this -> chama -> id)
                                -> join('member_groups', 'member_groups.id=invoices.chama_id')
                                -> get('invoices')
                                -> row_array();
                }
                else
                {

                        return $this -> db -> select_sum('bills.balance')
                                -> where('bills.status', 'UNPAID')
                                -> where("date_due < NOW() + INTERVAL 30 DAY ", NULL, FALSE)
                                -> where('member_groups.id', $this -> chama -> id)
                                -> join('member_groups', 'member_groups.id=bills.chama_id')
                                -> get('bills')
                                -> row_array();
                }
        }
        function overdue_months()
        {
                $type = $this -> attribute('stype');
                $mon = $this -> attribute('period');
                if ($type == 'inv')
                {
                        return $this -> db -> select_sum('invoices.balance')
                                -> where('invoices.status', 'UNPAID')
                                -> where("due_date > NOW() - INTERVAL " .$mon." DAY ", NULL, FALSE)
                                -> where('member_groups.id', $this -> chama -> id)
                                -> join('member_groups', 'member_groups.id=invoices.chama_id')
                                -> get('invoices')
                                -> row_array();
                }
                else
                {

                        return $this -> db -> select_sum('bills.balance')
                                -> where('bills.status', 'UNPAID')
                                -> where("date_due > NOW() - INTERVAL  " .$mon." DAY ", NULL, FALSE)
                                -> where('member_groups.id', $this -> chama -> id)
                                -> join('member_groups', 'member_groups.id=bills.chama_id')
                                -> get('bills')
                                -> row_array();
                }
        }

        function overdue_invoices()
        {

                return $this -> db -> select_sum('invoices.balance')
                        -> where('invoices.status', 'UNPAID')
                        -> where('invoices.due_date <', time())
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=invoices.chama_id')
                        -> get('invoices')
                        -> row_array();
        }

        function pending_bills()
        {

                return $this -> db -> select_sum('bills.balance')
                        -> where('bills.status', 'UNPAID')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=bills.chama_id')
                        -> get('bills')
                        -> row_array();
        }

        function overdue_bills()
        {
                return $this -> db -> select_sum('bills.balance')
                        -> where('bills.status', 'UNPAID')
                        -> where('bills.date_due <', time())
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=bills.chama_id')
                        -> get('bills')
                        -> row_array();
        }


        function unpaid_penalties()
        {

                return $this -> db -> select_sum('amount')
                        -> where('invoice_con.status', 0)
                        -> where('invoice_con.type', 2)
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=invoice_con.chama_id')
                        -> get('invoice_con')
                        -> row_array();
        }

        function count_active_members()
        {
                $this -> chamaslug = $this -> attribute('chamaslug');

                return $this -> db
                        -> where('chama_member_profile.status', 'active')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('member_groups', 'member_groups.id=chama_member_profile.chama_id')
                        -> get('chama_member_profile')
                        -> num_rows();
        }

        function count_inactive_members()
        {
                $this -> chamaslug = $this -> attribute('chamaslug');

                return $this -> db
                        -> where('chama_member_profile.status', 'inactive')
                        -> or_where('chama_member_profile.status', 'deleted')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('member_groups', 'member_groups.id=chama_member_profile.chama_id')
                        -> get('chama_member_profile')
                        -> num_rows();
        }

        function count_by_role()
        {
                $this -> chamaslug = $this -> attribute('chamaslug');
                $role_id = $this -> attribute('role');

                return $this -> db
                        -> where('chama_member_profile.role', $role_id)
                        -> where('chama_member_profile.status', 'active')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('member_groups', 'member_groups.id=chama_member_profile.chama_id')
                        -> get('chama_member_profile')
                        -> num_rows();
        }

        function penalties_old($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $this -> load -> library('ion_auth');
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');
                $user_id = $this -> ion_auth -> get_user() -> id;


                return $this -> db
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> where('users.id', $user_id)
                        -> join('users', 'chama_penalties.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_penalties.chama_id')
                        -> limit($limit)
                        -> order_by('chama_penalties.created_on', 'DESC')
                        -> get('chama_penalties')
                        -> result_array();
        }

        function allpenalties_old($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $this -> load -> library('ion_auth');
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');
                $user_id = $this -> ion_auth -> get_user() -> id;


                return $this -> db
                        -> select('chama_penalties.*,first_name,last_name')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('users', 'chama_penalties.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_penalties.chama_id')
                        -> limit($limit)
                        -> order_by('chama_penalties.created_on', 'DESC')
                        -> get('chama_penalties')
                        -> result_array();
        }

        function users($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');

                return $this -> db
                        -> select('chama_member_profile.*,first_name,last_name,email,phone')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('users', 'chama_member_profile.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_member_profile.chama_id')
                        -> limit($limit)
                        -> get('chama_member_profile')
                        -> result_array();
        }
*/
        /*
         * Chama Invoices
         */
        /*
        function invoices($data = array())
        {

                $limit = $this -> attribute('limit', 5);
                $this -> load -> helper('date');
                return $this -> db
                        -> select('invoice_con.*,first_name,last_name')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id=invoice_con.chama_id')
                        -> join('users', 'users.id = invoice_con.user_id')
                        -> order_by('invoice_con.id', 'desc')
                        -> limit($limit)
                        -> get('invoice_con')
                        -> result_array();
        }*/

        /*
         * Chama Payments
         */
/*
        function payments($data = array())
        {

                $limit = $this -> attribute('limit', 5);
                $this -> load -> helper('date');


                return $this -> db
                        -> select('payments_con.*,first_name,last_name,invoice_no')
                        -> where('member_groups.id', $this -> chama -> id)
                        -> join('member_groups', 'member_groups.id = payments_con.chama_id')
                        -> join('invoice_con', 'invoice_con.id = payments_con.invoice_id')
                        -> join('users', 'users.id = payments_con.user_id')
                        //-> join('regular_con','regular_con.id = payments_con.con_id')
                        -> order_by('payments_con.id', 'desc')
                        -> limit($limit)
                        -> get('payments_con')
                        -> result();
        }
*/
       /* function users_count($data = array())
        {
                $limit = $this -> attribute('limit', 10);
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');


                return $this -> db
                        -> select('chama_member_profile.*,first_name,last_name,email,phone')
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('users', 'chama_member_profile.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_member_profile.chama_id')
                        -> limit($limit)
                        -> get('chama_member_profile')
                        -> num_rows();
        }

        function me($data = array())
        {
                $limit = $this -> attribute('limit', 1);
                $this -> load -> library('ion_auth');
                $order = $this -> attribute('order');
                $this -> chamaslug = $this -> attribute('chamaslug');
                $user_id = $this -> ion_auth -> get_user() -> id;
                return $this -> db
                        -> select('chama_member_profile.*,first_name,last_name,email,last_login,phone')
                        -> where('chama_member_profile.user_id', $user_id)
                        -> where('member_groups.slug', $this -> chamaslug)
                        -> join('users', 'chama_member_profile.user_id=users.id')
                        -> join('member_groups', 'member_groups.id=chama_member_profile.chama_id')
                        -> limit($limit)
                        -> get('chama_member_profile')
                        -> result_array();
        }
*/
}

/* End of file theme.php */
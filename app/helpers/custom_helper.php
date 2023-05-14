<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$key = md5('tuzidi_version_one');
if (!defined('SALT')) define('SALT',$key);

if(defined('CALLING_CODE')){

}else{
    define('CALLING_CODE',"254");
}

function number_to_currency($int=0){
    if(abs(round($int,3)) == 0){
        $int = abs($int);
    }
	if(floatval(str_replace(',','',$int))){		
		return number_format(floatval(str_replace(',','',$int)),2);
	}else{
		return number_format(0,2);
	}
}

function timestamp_to_datetime($timestamp = 0){
	if(is_numeric($timestamp)){
    	return '<span class="tooltips" data-original-title="'.date('l, jS F Y, g:i A',$timestamp?$timestamp:0).'" >'.date(' jS F Y, g:i A',$timestamp?$timestamp:0).'</span>'; 
	}
}

function timestamp_to_date($timestamp = 0,$value=FALSE){
	if(is_numeric($timestamp)){
        if($value){
            return date('d-m-Y',$timestamp?$timestamp:0); 
        }else{
            return '<span class="tooltips" data-original-title="'.date('l, jS F Y',$timestamp?$timestamp:0).'" >'.date('d-m-Y',$timestamp?$timestamp:0).'</span>'; 
        }
    }
}

function _valid_identity()
    {
        $identity = $this->input->post('identity');

        if(!valid_email($identity))
        {
            if(!valid_phone($identity))
            {
                $this->form_validation->set_message('_valid_identity','Enter a valid Email or Phone Number');
                return FALSE;
            }
            return TRUE;
        }
        else
        {
            return TRUE;
        }
    }

function timestamp_to_daytime($timestamp = 0)
{
    return date('l, d-m-Y',$timestamp?$timestamp:0);
}

function timestamp_to_message_time($timestamp=0){
    return date('H:iA, d-m-Y',$timestamp?:0);
}

function timestamp_to_monthtime($timestamp=0)
{
    return date('M. Y',$timestamp?$timestamp:0);
}

function timestamp_to_date_and_time($timestamp = 0)
{
    return date('d-m-Y, g:i A',$timestamp?$timestamp:0);

}


function timestamp_to_receipt($timestamp = 0)
{
    return date('D, M d, Y',$timestamp?$timestamp:0);
}

function timestamp_to_report_time($timestamp){
     return date("jS F, Y ",$timestamp?:0);
}

function timestamp_to_datepicker($timestamp = 0)
{
	if(is_numeric($timestamp))
	{
		return date('d-m-Y',$timestamp?$timestamp:time());
	}
	else
	{
		$times = strtotime($timestamp);
		if(is_numeric($times))
		{
			return date('d-m-Y',$times?$times:time());
		}
		else
		{
			return date('d-m-Y',time());
		}
	}
}

function remove_zero($number = 0){
    if(strpos($number,"0")==0){
        return substr($number,1);
    }else{
        return $number;
    }
}


function valid_phone($phone=0,$strlen=TRUE,$set_calling_code_prefix=FALSE){

    $phone = preg_replace('/[\s\s+\-\(|\)]/','', $phone);
    $ci =& get_instance();

    if($phone){
        //checks whether its a valid phone number
        if(preg_match("/^[\+0-9\-\(\)\s]*$/", $phone)){
            if(preg_match("/[\+]/", $phone)){
                //phone has a plus at the beginning of the string e.g. +254721106625
                if($strlen==TRUE && $strlen>=10){
                    return $phone;
                }else{
                    if($strlen==FALSE){
                        if(strlen($phone)<=10){
                            if($set_calling_code_prefix){
                                $code = calling_code_prefix();
                                if($code){
                                    return $code.remove_zero($phone);
                                }else{
                                    if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                        return  $phone = $ci->group_calling_code.substr($phone,-9);
                                    }else{
                                        return  $phone = CALLING_CODE.remove_zero($phone);
                                    } 
                                }
                            }else{
                                if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                    return  $phone = $ci->group_calling_code.substr($phone,-9);
                                }else{
                                    return  $phone = CALLING_CODE.remove_zero($phone);
                                }  
                            }
                       }else{
                            return $phone;
                       }
                    }else{
                        return FALSE;
                    }
                }
            }else{
                if(strlen($phone)<14 && strlen($phone)>8){
                    if(strlen($phone)<10){
                       if($set_calling_code_prefix){
                            $code = calling_code_prefix();
                            if($code){
                                return $code.remove_zero($phone);
                            }else{
                                if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                    return  $phone = $ci->group_calling_code.substr($phone,-9);
                                }else{
                                    return  $phone = CALLING_CODE.remove_zero($phone);
                                } 
                            }
                        }else{
                            if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                return  $phone = $ci->group_calling_code.substr($phone,-9);
                            }else{
                                return  $phone = CALLING_CODE.remove_zero($phone);
                            } 
                        } 
                    }else{
                        if(substr($phone,0,1)==0){
                            if($set_calling_code_prefix){
                                $code = calling_code_prefix();
                                if($code){
                                    return $code.remove_zero($phone);
                                }else{
                                   if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                        return  $phone = $ci->group_calling_code.remove_zero($phone);
                                    }else{
                                        return  $phone = CALLING_CODE.remove_zero($phone);
                                    } 
                                }
                            }else{
                                if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                    return  $phone = $ci->group_calling_code.remove_zero($phone);
                                }else{
                                    return  $phone = CALLING_CODE.remove_zero($phone);
                                } 
                            }
                        }else{
                            return $phone;
                        }
                    }
                }else{
                    if($strlen==FALSE){
                        if(strlen($phone)<=10){
                           if($set_calling_code_prefix){
                                $code = calling_code_prefix();
                                if($code){
                                    return $code.remove_zero($phone);
                                }else{
                                    if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                        return  $phone = $ci->group_calling_code.substr($phone,-9);
                                    }else{
                                        return  $phone = CALLING_CODE.remove_zero($phone);
                                    } 
                                }
                            }else{
                                if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                    return  $phone = $ci->group_calling_code.substr($phone,-9);
                                }else{
                                    return  $phone = CALLING_CODE.remove_zero($phone);
                                } 
                            }
                       }else{
                            return $phone;
                       }
                    }else{
                         if(substr($phone,0,1)==0){
                            if($set_calling_code_prefix){
                                $code = calling_code_prefix();
                                if($code){
                                    return $code.remove_zero($phone);
                                }else{
                                    if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                        return  $phone = $ci->group_calling_code.substr($phone,-9);
                                    }else{
                                        return  $phone = CALLING_CODE.remove_zero($phone);
                                    } 
                                }
                            }else{
                                if(isset($ci->group_calling_code) && !empty($ci->group_calling_code)){
                                    return  $phone = $ci->group_calling_code.substr($phone,-9);
                                }else{
                                    return  $phone = CALLING_CODE.remove_zero($phone);
                                }  
                            } 
                        }else if(strlen($phone)>9){
                            return $phone;
                        }else{
                            return FALSE;
                        }
                    }
                }
            }
        }
        else
        {
            return FALSE;
        }
    }else{
        return FALSE;
    }
}


function calling_code_prefix(){
    $ip = $_SERVER['REMOTE_ADDR'];
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
    if($details){
        if(isset($details->country)){
            $code =$details->country;
            $ci =& get_instance(); 
            $current_country = $ci->countries_m->get_country_by_code($code);
            if($current_country){
                return $current_country->calling_code;
            }else{
                return FALSE;
            }
        }
    }else{
        return FALSE;
    }
}


function currency($str){
    if(preg_match('/^[0-9,.]+$/', $str)){
        return str_replace(',','',$str);
    }else{
        return FALSE;
    }
}



function number_to_words($number) {
    
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );
    
    if (!is_numeric($number)) {
    	$number = str_replace(',', '', $number);
    	if(!is_numeric($number))
    	{
    		return false;	
    	}
    }
    
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . number_to_words(abs($number));
    }
    
    $string = $fraction = null;
    
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
    
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= number_to_words($remainder);
            }
            break;
    }
    
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
    
    return $string;
}

function remove_subdomain_from_url($url = 'tuzidi.net',$protocol = "http://"){
    $domains = explode(".",$_SERVER['HTTP_HOST']);
    $dots = 3;
    if(strlen($domains[(count($domains) - 1)]) == 2){
       $dots = 4;
    }
    if(isset($_SERVER['REQUEST_URI'])){
        $request_uri = $_SERVER['REQUEST_URI'];
    }else{
        $request_uri = '';
    }
    if(count($domains) == $dots && $domains[0] != "www"){
        if(preg_match('/(http)/',$url)){
            redirect($url.$request_uri,'refresh');
        }else{
            redirect($protocol.$url.$request_uri,'refresh');
        }
    }
} 
    
function timestamp_to_time_elapsed($ptime)
{
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}

function separate_account($account_id_type=0)
{
    if($account_id_type)
    {
        $type = '';
        $account_id = '';
        if(preg_match('/bank/',$account_id_type))
        {
            $exploded_account = explode('-', $account_id_type);
            $type = 1;
            $account_id = trim($exploded_account[1]); 
        }
        else if(preg_match('/sacco/', $account_id_type))
        {
            $exploded_account = explode('-', $account_id_type);
            $type = 2;
            $account_id = trim($exploded_account[1]);
        }
        else if(preg_match('/mobile/', $account_id_type))
        {
            $exploded_account = explode('-', $account_id_type);
            $type = 3;
            $account_id = trim($exploded_account[1]);
        }
        else if(preg_match('/petty/', $account_id_type))
        {
            $exploded_account = explode('-', $account_id_type);
            $type = 4;
            $account_id = trim($exploded_account[1]);
        }

       return (object)array('account_type'=>$type,'account_id'=>$account_id);

    }
    else
    {
        return FALSE;
    }
}

function reconstruct_account($account_type=0,$account_id=0)
{
    if($account_id && $account_type)
    {
        $account_id= trim($account_id);
        if($account_type==1)
        {
            return 'bank-'.$account_id;
        }
        else if($account_type==2)
        {
            return 'sacco-'.$account_id;
        }else if($account_type==3){
            return 'mobile-'.$account_id;
        }else if($account_type==4){
            return 'petty-'.$account_id;
        }
    }
    else
    {
        return FALSE;
    }
}

function valid_currency($str=''){
    if(preg_match('/^[0-9,.]+$/', $str)){
        if((is_numeric($str) || is_float($str))&&(round($str)==0)){
            return TRUE;
        }else{
            return str_replace(',','',$str);
        }
    }else{
        if((is_numeric($str) || is_float($str))&&(round($str)==0)){
            return TRUE;
        }else{
            return FALSE;
        }
    }
}


function group_account($account_id , $accounts = array()){
    $account = '';
    if(empty($accounts[$account_id]))
    {
        foreach ($accounts as $key => $value) {
            if(is_array($value))
            {
                if(array_key_exists($account_id, $value))
                {
                    $account = $value[$account_id];
                }
            }
        }
    }
    else
    {
        $account = $accounts[$account_id];
    }

    return $account;
}



function is_character_allowed($character = ''){
    $allowed_special_characters = array(
            '\'',
            '`',
            ' ',
            '-',
        );
    
    $character = str_replace(' ','', trim($character));
    if($character){
        if(preg_match('/[\W]+/', $character)){
            if(in_array($character, $allowed_special_characters)){
                return TRUE;
            }else{
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }else{
        return TRUE;
    }
}
function month_name_to_name($month_number = 1){
    $dateObj   = DateTime::createFromFormat('!m', $month_number);
    return $dateObj->format('F'); 
}


function days_ago($timestamp=0){
    $timeago = time() - $timestamp;
    if($timeago>0){
        return ($timeago/86400);
    }
}

function daysAgo($timestamp = 0,$markupdate=0){
    $markupdate = $markupdate?:time();
    $daysAgo = '0 days';
    $elapsedTime = abs(round($markupdate- $timestamp));
    $secondsArray = array(
        365 * 24 * 60 * 60,
        30 * 24 * 60 * 60,
        // 7 * 24 * 60 * 60,
        24 * 60 * 60,
        60 * 60,
        60,
        1
    );
    $timeDescriptions = array(
        "year",
        "month",
        //"week",
        "day",
        "hour",
        "minute",
        "second"
    );
    for ($i=0; $i<count($secondsArray);$i++){
        $convertedTime =$elapsedTime/$secondsArray[$i];
        if($convertedTime>=1){
            $time = round($convertedTime);
            $daysAgo = $time." ".(($time>1)?$timeDescriptions[$i]."s":$timeDescriptions[$i]);
            break;
        }else{
            continue;
        }
    }

    return $daysAgo;
}

function generate_slug($name= ''){
    if($name){
        $name = str_replace(' ','-', $name);
        $name = str_replace('.','-', $name);
        return strtolower(trim($name));
    }else{
        return 'false';
    }
}

function escape_single_qoutes($string = ""){
    //return preg_replace_all("/([^\])'/","$1\'",$string);
}
function limit_text($text, $limit) {
  if (str_word_count($text, 0) > $limit) {
      $words = str_word_count($text, 2);
      $pos = array_keys($words);
      $text = substr($text, 0, $pos[$limit]) . '...';
  }
  return $text;
}

function generate_random_string($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generate_refferal_code($length = 5) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generate_join_code($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generate_email_verification_code($length = 30) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generate_forgot_password_code($length = 40) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if(!function_exists('apache_request_headers')){
    function apache_request_headers() {
          $arrCasedHeaders = array(
              // HTTP
              'Dasl'             => 'DASL',
              'Dav'              => 'DAV',
              'Etag'             => 'ETag',
              'Mime-Version'     => 'MIME-Version',
              'Slug'             => 'SLUG',
              'Te'               => 'TE',
              'Www-Authenticate' => 'WWW-Authenticate',
              // MIME
              'Content-Md5'      => 'Content-MD5',
              'Content-Id'       => 'Content-ID',
              'Content-Features' => 'Content-features',
          );
          $arrHttpHeaders = array();
          foreach($_SERVER as $strKey => $mixValue) {
              if('HTTP_' !== substr($strKey, 0, 5)) {
                  continue;
              }

              $strHeaderKey = strtolower(substr($strKey, 5));

              if(0 < substr_count($strHeaderKey, '_')) {
                  $arrHeaderKey = explode('_', $strHeaderKey);
                  $arrHeaderKey = array_map('ucfirst', $arrHeaderKey);
                  $strHeaderKey = implode('-', $arrHeaderKey);
              }
              else {
                  $strHeaderKey = ucfirst($strHeaderKey);
              }

              if(array_key_exists($strHeaderKey, $arrCasedHeaders)) {
                  $strHeaderKey = $arrCasedHeaders[$strHeaderKey];
              }

              $arrHttpHeaders[$strHeaderKey] = $mixValue;
          }

          return $arrHttpHeaders;
      }
}

function facebook_time_ago($timestamp)  
 {  
      $time_ago = $timestamp;  
      $current_time = time();  
      $time_difference = $current_time - $time_ago;  
      $seconds = $time_difference;  
      $minutes      = round($seconds / 60 );           // value 60 is seconds  
      $hours           = round($seconds / 3600);           //value 3600 is 60 minutes * 60 sec  
      $days          = round($seconds / 86400);          //86400 = 24 * 60 * 60;  
      $weeks          = round($seconds / 604800);          // 7*24*60*60;  
      $months          = round($seconds / 2629440);     //((365+365+365+365+366)/5/12)*24*60*60  
      $years          = round($seconds / 31553280);     //(365+365+365+365+366)/5 * 24 * 60 * 60  
      if($seconds <= 60)  
      {  
     return "Just Now";  
   }  
      else if($minutes <=60)  
      {  
     if($minutes==1)  
           {  
       return "one minute ago";  
     }  
     else  
           {  
       return "$minutes minutes ago";  
     }  
   }  
      else if($hours <=24)  
      {  
     if($hours==1)  
           {  
       return "an hour ago";  
     }  
           else  
           {  
       return "$hours hrs ago";  
     }  
   }  
      else if($days <= 7)  
      {  
     if($days==1)  
           {  
       return "yesterday";  
     }  
           else  
           {  
       return "$days days ago";  
     }  
   }  
      else if($weeks <= 4.3) //4.3 == 52/12  
      {  
     if($weeks==1)  
           {  
       return "a week ago";  
     }  
           else  
           {  
       return "$weeks weeks ago";  
     }  
   }  
       else if($months <=12)  
      {  
     if($months==1)  
           {  
       return "a month ago";  
     }  
           else  
           {  
       return "$months months ago";  
     }  
   }  
      else  
      {  
     if($years==1)  
           {  
       return "one year ago";  
     }  
           else  
           {  
       return "$years years ago";  
     }  
   }  
 }

  function formatSizeUnits($bytes){
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
}

function array_unique_case($array) {
    sort($array);
    $tmp = array();
    $callback = function ($a) use (&$tmp) {
        if (in_array(strtolower($a), $tmp))
            return false;
        $tmp[] = strtolower($a);
        return true;
    };
    return array_filter($array, $callback);
}

function resizeImage($originalImage,$toWidth,$toHeight){

    list($width, $height) = getimagesize($originalImage);
    $xscale=$width/$toWidth;
    $yscale=$height/$toHeight;

    if ($yscale>$xscale){
        $new_width = round($width * (1/$yscale));
        $new_height = round($height * (1/$yscale));
    }
    else {
        $new_width = round($width * (1/$xscale));
        $new_height = round($height * (1/$xscale));
    }


    $imageResized = imagecreatetruecolor($new_width, $new_height);
    $imageTmp     = imagecreatefromjpeg ($originalImage);
    imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    return $imageResized;
}

function videoType($url) {
    if (strpos($url, 'youtube') > 0) {
        return TRUE;
    }else if(strpos($url, 'youtu.be') > 0){
       return TRUE; 
    } elseif (strpos($url, 'vimeo') > 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])){
        parse_str($parts['query'], $qs);
        if(isset($qs['v'])){
            return $qs['v'];
        }else if(isset($qs['vi'])){
            return $qs['vi'];
        }
    }
    if(isset($parts['path'])){
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path)-1];
    }
    return false;
}

function is_file_type($file = ''){
    $valid_types = array(
        "image/jpg", 
        "image/jpeg",
        "image/bmp", 
        "image/gif",
        "image/png",
        "application/pdf",
        'image/png', 
        'image/jpeg', 
        'image/gif', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' , 
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', 
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.template',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',          
        'application/x-zip', 'application/zip'
    );
    $type = isset($file['type'])?$file['type']:'';
    if($type){
        $string = trim(preg_replace('/\s+/', ' ', $type));
        //print_r($string); die;
        if (in_array($string, $valid_types)){
            return TRUE;
        }else{
            return FALSE;
        }
    }else{
        return FALSE;
    }
}


function get_divisible($number = 0){
    $divisible = 1; 
    if( $number > 25){
        $divisible = (int) $number/25;
    }
    return $divisible;
    //return 2;
}




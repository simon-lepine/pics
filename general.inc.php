<?php

/*
 * //class for general purpose, all files
 */
class general {

/*
 * define vars
 */
public $message_array=array();
public $last_message='';
public $last_result=array();
public $success=0;
public $internal_hash='';
public $old_internal_hash='';
public $curl;
public $default_next_url='/account/index.php';//stored/set here so we can overwrite in /forbidden_dir/settings.inc.php
public $microservice_url='';

/*
 * create cache for storing microservice calls
 */
public $skip_cache=0;
public $cache=array();

/*
 * //function to construct
 */
function __construct(){

/*
 * set internal hash
 */
$this->set_internal_hash();

/*
 * new setup curl
 */
if (
	(!$this->get_curl())
){
if (
	(!is_object($this->curl))
	&&
	(!is_resource($this->curl))
){
	echo 'Something went terribly wrong :(';
	die;
}
}

/*
 * load settings
 */
$this->load_settings();

/*
 * return success
 */
return true;

/*
 * done //function
 */
}

/*
 * //function to load settings
 */
function load_settings(){

/*
 * get settings
 */
if (
	(!file_exists($_SERVER['class']['constants']->forbidden_dir . $_SERVER['class']['constants']->separator . 'settings.inc.php'))
	||
	(!include($_SERVER['class']['constants']->forbidden_dir . $_SERVER['class']['constants']->separator . 'settings.inc.php'))
){
	return false;
}

/*
 * return success
 */
return true;

/*
 * done //function
 */
}

/*
 * //function to set internal hash
 */
function set_internal_hash(){

/*
 * set internal hash
 */
$this->internal_hash =
	md5(date('--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--') . $_SERVER['class']['constants']->docroot)
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--') . $_SERVER['class']['constants']->docroot)
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--') . $_SERVER['class']['constants']->docroot)
;

/*
 * set OLD internal hash
 * we do this in case a query crosses the hour, this allows a 60 second buffer
 */
$this->old_internal_hash =
	md5(date('--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--', time()-120) . $_SERVER['class']['constants']->docroot)
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--') . $_SERVER['class']['constants']->docroot)
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--', time()-120) . $_SERVER['class']['constants']->docroot)
;


/*
 * return hash
 */
return $this->internal_hash;

/*
 * done //function
 */
}


/*
 * //function to get/set reusable curl pointer
 */
function get_curl(){

/*
 * if not set then set
 */
if (
	(!isset($this->curl))
	||
	(!$this->curl)
){
if (
	(!is_object($this->curl))
	&&
	(!is_resource($this->curl))
){
	$this->curl = curl_init();
}
}

/*
 * ensure it is set now
 */
if (
	(!is_object($this->curl))
	&&
	(!is_resource($this->curl))
){
	return false;
}

/*
 * wipe curl options so we never send internal_hash or other secret data off-server
 */
curl_reset($this->curl);
curl_setopt_array($this->curl, array(
	CURLOPT_URL => '', 
	CURLOPT_REFERER => $_SERVER['REQUEST_URI'], 
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_USERAGENT => $_SERVER['HTTP_HOST'],
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => array(), 
	CURLOPT_HEADER => 0,
	CURLOPT_FOLLOWLOCATION => true, //note safe because these are only internal calls
	CURLOPT_SSL_VERIFYPEER => 0, //note safe because these are only internal calls
	CURLOPT_SSL_VERIFYHOST => 0, //note safe because these are only internal calls
));

/*
 * return success
 */
return $this->curl;

/*
 * done //function
 */
}

/*
 * //function to log errors
 */
function log(
	$message=0, // string
	$title=0, // string
	$level=0, // low | medium | high | extreme 
	$type=0, // info | help | warning | error | fatal
	$admin=0, // string or array
	$date_run=0, // date for sched_jobs
	$add=0, // string or array
	$us_id=0, // int or defaults to JWT id
	$cand_id=0, // int, id of applicant applying
	$post_id=0, //int, id of application applying to
	$ip=0 // string
){

$this->success=1;
$this->last_message = $message;
return true;
/*
 * //future get rid of this function and switch everything to [class][log]->add->entry()

/*
 * done //function
 */
}

/*
 * //function to handle all text
 */
function san_text($text=''){

/*
 * if array
 * split out and process array keys and values
 * return resulting array
 */
if (is_array($text)){
foreach ($text AS $key=>$val){
	unset($text[ $key ]);
	$key = $this->san_text($key);
	$val = $this->san_text($val);
	$text[ $key ] = $val;
/*
 * done foreach
 * and if
 */
}
return $text;
}

/*
 * confirm we have text
 */
if (
	(!isset($text))
	||
	(!$text)
){
	return '';
}

/*
 * confirm we have data
 */
if (
	(!isset($text))
	||
	(!is_string($text))
	||
	(!strlen($text . ''))
){
	return '';
}

/*
 * strip non-print chars
 */
$text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
$text = iconv('UTF-8', "ASCII//IGNORE", $text);

/*
 * sanatize text
 */
$rep = array(
	DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR,
	'..'.DIRECTORY_SEPARATOR,
	DIRECTORY_SEPARATOR.'..',
	'<?',
	'?>'
);
$text = str_replace($rep, '', $text);

/*
 * replace ASCII
 */
$text = str_replace("'", '&#39;', $text);
$text = str_replace('"', '&#34;', $text);
$text = str_replace('$', '&#36;', $text);
$text = str_replace(';', '&#59;', $text);
$text = str_replace('<', '&#60;', $text);
$text = str_replace('>', '&#62;', $text);
$text = str_replace('?', '&#63;', $text);
$text = str_replace('[', '&#91;', $text);
$text = str_replace(']', '&#93;', $text);
$text = str_replace('\\', '&#92;', $text);
$text = str_replace('^', '&#94;', $text);
$text = str_replace('`', '&#96;', $text);
$text = str_replace('=', '&#61;', $text);
$text = str_replace('{', '&#123;', $text);
$text = str_replace('}', '&#125;', $text);
$text = str_replace('|', '&#124;', $text);

/*
 * trim spaces on either side
 */
$text = trim($text);

/*
 * return sanatized text
 */
return $text;

/*
 * done //function
 */
}

/*
 * //function to make microservice calls
 */
function microservice_call(
	$microservice = '', //input_required - string, the microservice (SERVER_URL/API/microservices is prepended)
	$post_fields='', //input_required - array, the post fields to send to the microservice. if CSRF isn't set its added
	$await_response=1
){

/*
 * confirm we have post_fields
 */
if (
	(!isset($post_fields))
	||
	(!is_array($post_fields))
	||
	(!count($post_fields))
){
	$post_fields=array();
}

/*
 * confirm we have csrf
 */
if (
	(!isset($post_fields['csrf']))
	||
	(!is_string($post_fields['csrf']))
	||
	(!$post_fields['csrf'])
){
	$post_fields['csrf'] = $this->internal_hash;
}

/*
 * check if curl is currently in use and if so create a new handle
 * //future might have to use curl_multi_init/exec for this?
 */
if (!$curl = $this->curl){
	return false;
}

/*
 * init timeout
 */
if (
	(!$timeout = ini_get('max_execution_time'))
	||
	(!is_numeric($timeout))
	||
	($timeout > 1000)
){
	$timeout = 300;
}


/*
 * confirm we have a microservice
 * and set url
 */
if (
	(!isset($microservice))
	||
	(!is_string($microservice))
	||
	(!$microservice)
){
	$this->last_message = $this->message_array[] = 'No microservice provided.';
	return false;
}
if (
	(!isset($this->microservice_url))
	||
	(!is_string($this->microservice_url))
	||
	(!$this->microservice_url)
){
	$this->microservice_url = "{$_SERVER['class']['constants']->server_url}/API/microservices/";
}

/*
 * check if microservice has http in it
 * and ://
 */
if (
	(stripos($microservice, 'http') === false)
	&&
	(stripos($microservice, '://') === false)
){
	$microservice = "{$this->microservice_url}/{$microservice}";
}

/*
 * set cache ID
 */
$cache_id = md5(json_encode($post_fields)) . md5($microservice);

/*
 * check if cache is set
 * and if so return
 */
if (
	(!isset($this->skip_cache))
	||
	(!$this->skip_cache)
){
if (
	(isset($await_response))
	&&
	($await_response)
	&&
	(isset($this->cache[ $cache_id ]))
	&&
	(is_array($this->cache[ $cache_id ]))
	&&
	(count($this->cache[ $cache_id ]))
	&&
	(isset($this->cache[ $cache_id ]['success']))
	&&
	($this->cache[ $cache_id ]['success'])
){
	return $this->cache[ $cache_id ];
}
}


/*
 * set cURL options
 */
curl_setopt_array($curl, array(
	CURLOPT_URL => $microservice, 
	CURLOPT_POSTFIELDS => $post_fields, 
));

/*
 * get/set await_response
 */
if (
	(isset($await_response))
	&&
	($await_response === 0)
){
curl_setopt_array($curl, array(
	CURLOPT_TIMEOUT => 1,
	CURLOPT_HEADER => 0,
	CURLOPT_FORBID_REUSE => 1,
	CURLOPT_CONNECTTIMEOUT => 1,
	CURLOPT_DNS_CACHE_TIMEOUT => 10,
	CURLOPT_FRESH_CONNECT => 1
));
}

/*
 * exec curl and return result
 */
$this->last_result = $result = curl_exec($this->curl);
if (
	($tmp = json_decode($result, true))
	&&
	(is_array($tmp))
	&&
	(count($tmp))
	&&
	($result = $tmp)
){
	$this->cache[ $cache_id ] = $result;
	return $result;
}

/*
 * if we're here the curl call failed
 * return failure
 */
$this->message_array[] = curl_getinfo($curl);
$this->message_array[] = $this->last_message = "cURL call to {$microservice} microservice failed.";
return false;

/*
 * done //function
 */
}

/*
 * //function to cleanup urls
 */
function cleanup_url($url=''){

/*
 * confirm data
 */
if (
	(!isset($url))
	||
	(!is_string($url))
	||
	(!$url)
){
	return '';
}

/*
 * handle https
 */
$url = str_ireplace('https://', '???HTTPS_URL_REPLACE???', $url, $https_count);

/*
 * handle http
 */
$url = str_ireplace('http://', '???HTTP_URL_REPLACE???', $url, $http_count);

/*
 * remove double
 */
while (strpos($url, '//') !== false){
	$url = str_replace('//', '/', $url);
}

/*
 * add http back in
 */
if ($https_count){
	$url = str_ireplace('???HTTPS_URL_REPLACE???', 'https://', $url);
}
if ($http_count){
	$url = str_ireplace('???HTTP_URL_REPLACE???', 'http://', $url);
}

/*
 * return success
 */
return $url;

/*
 * done //func
 */
}

/*
 * //func to confirm next_url exists and is allowed
 */
function check_next_url($values=''){

/*
 * set/default values
 */
if (
	(!isset($values))
	||
	(!is_array($values))
	||
	(!count($values))
){
	$values=array(
		'next_url' => $this->default_next_url
	);
}

/*
 * set/default url
 */
if (
	(!isset($values['next_url']))
	||
	(!is_string($values['next_url']))
	||
	(!$values['next_url'])
){
	$values['next_url'] = $this->default_next_url;
}

/*
 * remove server_url
 * and webroot
 */
$tmp = strpos($values['next_url'], $_SERVER['class']['constants']->server_url);
if (
	($tmp !== false)
	&&
	($tmp < 5)
){
	$values['next_url'] = $this->cleanup_url($values['next_url']);
    $values['next_url'] = '/' . substr_replace($values['next_url'], '', $tmp, strlen($_SERVER['class']['constants']->server_url));
}
$tmp = strpos($values['next_url'], $_SERVER['class']['constants']->webroot);
if (
	($tmp !== false)
	&&
	($tmp < 5)
){
	$values['next_url'] = $this->cleanup_url($values['next_url']);
    $values['next_url'] = '/' . substr_replace($values['next_url'], '', $tmp, strlen($_SERVER['class']['constants']->webroot));
} 

/*
 * init return
 */
$return='';

/*
 * split url
 * and confirm file exists
 * or url is valid
 * or url/index.php is valid
 */
$url_split = explode('?', $values['next_url']);
if (
	(!$return)
	&&
	(file_exists("{$_SERVER['class']['constants']->docroot}{$url_split[0]}"))
){
	$return = "{$_SERVER['class']['constants']->server_url}{$values['next_url']}";
}
if (
	(!$return)
	&&
	($tmp = get_headers("{$_SERVER['class']['constants']->server_url}{$url_split[0]}"))
	&&
	(strpos($tmp[0], ' 200') === false)
){
	$return = "{$_SERVER['class']['constants']->server_url}{$values['next_url']}";
}
if (
	(!$return)
	&&
	($tmp = get_headers("{$_SERVER['class']['constants']->server_url}{$url_split[0]}/index.php"))
	&&
	(strpos($tmp[0], ' 200') === false)
){
	$return = "{$_SERVER['class']['constants']->server_url}{$values['next_url']}";
}

/*
 * default if nothing else
 */
if (!$return){
	$this->success=0;
	$this->last_message = $this->message_array[] = 'Next URL was set to default since it was not valid.';
	$return = "{$_SERVER['class']['constants']->server_url}{$this->default_next_url}";
}

/*
 * return result
 */
return $this->cleanup_url($return);

/*
 * done //function
 */
}

/*
 * //function to trigger scheduled jobs
 */
function trigger_scheduled_jobs(){

/*
 * init api_token
 */
$simon_lepines_schedule_jobs_api_token = '';
if (
	(isset($this->schedule_jobs_api_token))
	&&
	(is_string($this->schedule_jobs_api_token))
	&&
	($this->schedule_jobs_api_token)
){
	$simon_lepines_schedule_jobs_api_token = $this->schedule_jobs_api_token;
}
if (
	(!$simon_lepines_schedule_jobs_api_token)
	&&
	(file_exists("{$_SERVER['class']['constants']->docroot}scheduled_jobs{$_SERVER['class']['constants']->separator}api_token.php"))
){
	include("{$_SERVER['class']['constants']->docroot}scheduled_jobs{$_SERVER['class']['constants']->separator}api_token.php");
	$this->schedule_jobs_api_token=$simon_lepines_schedule_jobs_api_token;
}

/*
 * check if curl is currently in use and if so create a new handle
 * //future might have to use curl_multi_init/exec for this?
 */
if (!$curl = $this->get_curl()){
	return false;
}

/*
 * set cURL options
 */
curl_setopt_array($curl, array(
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_USERAGENT => 'lakebed.io and hiring.cat',
	CURLOPT_REFERER => $_SERVER['REQUEST_URI'], 
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => array(
		'api_token' => $simon_lepines_schedule_jobs_api_token,
	),
	CURLOPT_SSL_VERIFYPEER => 0, 
	CURLOPT_SSL_VERIFYHOST => 0, 
	CURLOPT_URL => "{$_SERVER['class']['constants']->server_url}/scheduled_jobs/trigger.php",
	CURLOPT_TIMEOUT => 1,
	CURLOPT_HEADER => 0,
	CURLOPT_FORBID_REUSE => 1,
	CURLOPT_CONNECTTIMEOUT => 1,
	CURLOPT_DNS_CACHE_TIMEOUT => 10,
	CURLOPT_FRESH_CONNECT => 1,
));

/*
 * exec cURL
 */
$result = curl_exec($curl);

/*
 * return success
 */
return true;

/*
 * done //function
 */
}

/*
 * done //class
 */
}

/*
 * init //class
 */
$_SERVER['class']['general'] = new general;

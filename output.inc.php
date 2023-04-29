<?php

/*
 * Check if file was called directly and error out because file should never be called directly
 */
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])){
	$tmp = basename(__FILE__) . ':' . __LINE__;
	error_log("[ LAKEBED ][ {$tmp} ] File was accessed directly, which should never happen.");
	echo 'Something went terribly wrong. :(';
	
	header('Status: 404 Not Found', false);
	header('Location: https://lakebed.io', false);
	die;
}

/*
 * //class for everything ERRor related
 */
class output {

/*
 * setup data stores
 */
public $browser_flag=0;

/*
 * //func to error OUT
 */
function send(
	$next_url=0, // string, server_url is automatically prepended. Used to direct to another front-end microservice
	$http_code=401 // int, to control http code
){

/*
 * disconnect from DB if connected
 */
if (!empty($_SERVER['class']['db'])){
	unset($_SERVER['class']['db']);
}

/*
 * init ret
 */
if (
	(empty($_SERVER['return']))
	||
	(!is_array($_SERVER['return']))
){
	$_SERVER['return']=array();
}

/*
 * handle next URL
 * and make sure its on the same server
 */
if (empty($_SERVER['return']['next_url'])){
	$_SERVER['return']['next_url']='';
}
if (
	(!empty($next_url))
	&&
	($next_url = trim($next_url, '/'))
){
	$_SERVER['return']['next_url'] = $next_url;
	//$_SERVER['return']['next_url'] = str_replace($_SERVER['class']['constants']->server_url, '', $_SERVER['return']['next_url']);
}
if (strpos($_SERVER['return']['next_url'], $_SERVER['class']['constants']->server_url) === 0){
	$_SERVER['return']['next_url'] = substr_replace(
		$_SERVER['return']['next_url'], 
		'', 
		0, 
		strlen($_SERVER['class']['constants']->server_url)
	);
}


/*
 * default success
 */
if (
	(!isset($_SERVER['return']['success']))
	||
	($_SERVER['return']['success'] != 1)
){
	$_SERVER['return']['success']=0;
}


/*
 * header redirect if browser flag is set
 */
if (
	(isset($this->browser_flag))
	&&
	($this->browser_flag)
){
	if (
		(!isset($_SERVER['return']['next_url']))
		||
		(!$_SERVER['return']['next_url'])
	){
		$_SERVER['return']['next_url']='/error';
	}
	$_SESSION['message']=$_SERVER['return']['message'];
	header("Location:{$_SERVER['class']['constants']->server_url}/{$_SERVER['return']['next_url']}", false);
	die;
}

/**
 * handle http_code
 */
if (
	(empty($http_code))
	||
	(!$http_code = ($http_code * 1))
	||
	(empty($http_code))
){
	$http_code = 401;
}

/*
 * output header info
 */
http_response_code($http_code);
header('Content-Type: application/json; charset=UTF-8', false);
header("HTTP/1.0 {$http_code} Found", false);
echo json_encode($_SERVER['return']);
die;


/*
 * done //func
 */
}

/*
 * done //class
 */
}

/*
 * init //class
 */
if (
	(!isset($_SERVER['class']))
	||
	(!is_array($_SERVER['class']))
){
	$_SERVER['class']=array();
}
if (
	(!isset($_SERVER['class']['output']))
	||
	(!$_SERVER['class']['output'])
){
	$_SERVER['class']['output'] = new output;
}

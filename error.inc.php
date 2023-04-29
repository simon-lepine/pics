<?php

/*
 * Check if file was called directly and error out because file should never be called directly
 */
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])){
	$_SERVER['return']['message']['error'][] = 'System file called directly :(';
	echo json_encode($_SERVER['return']);
	
	header('Status: 404 Not Found', false);
	header('Location: simon_lepine_FROM_lakebed.io_AND_applicant.fit_is_awesome', false);
	die;
}

/*
 * //class for everything ERRor related
 */
class err {

/*
 * setup data stores
 */
public $browser_flag=0;
public $dropzone_flag=0;

/*
 * //func to error OUT
 */
function out(
	$next_url=0, // string, server_url is automatically prepended. Used to direct to another front-end microservice
	$http_code=401 // int, to control http code
){

/*
 * disconnect from DB if connected
 */
if (isset($_SERVER['class']['db'])){
	unset($_SERVER['class']['db']);
}

/*
 * init ret
 */
if (!is_array($_SERVER['return'])){$_SERVER['return']=array();}

/*
 * return dropzone if set
 */
if (
	(isset($this->dropzone_flag))
	&&
	($this->dropzone_flag)
){
	return $this->dropzone_output();
}

/*
 * handle ERRor URL
 */
if (
	(isset($next_url))
	&&
	(is_string($next_url))
	&&
	($next_url = trim($next_url, '/'))
){
	$_SERVER['return']['next_url'] = "$next_url";
	//$_SERVER['return']['next_url'] = str_replace($_SERVER['class']['constants']->server_url, '', $_SERVER['return']['next_url']);
}
if (strpos($_SERVER['return']['next_url'], $_SERVER['class']['constants']->server_url) === 0){
	$_SERVER['return']['next_url'] = substr_replace($_SERVER['return']['next_url'], '', 0, strlen($_SERVER['class']['constants']->server_url));
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

/*
 * output header info
 */
http_response_code($http_code);//note this is the DropZone HTTP code
header('Content-Type: application/json; charset=UTF-8', false);
header("HTTP/1.0 {$http_code} Found", false);
echo json_encode($_SERVER['return']);
die;


/*
 * done //func
 */
}

/*
 * //func for dropzone output
 */
function dropzone_output(){

/*
 * output messages
 */
foreach ($_SERVER['return']['message'] AS $type=>$message_array){
	$type_html = strtoupper($type);
	foreach ($message_array AS $message){
		echo <<<m_echo
{$type_html}: {$message}


m_echo;
	}
}

http_response_code(401);//note this is the DropZone HTTP code
header("HTTP/1.0 401 Found");
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
 * //note actual class is called err because the 'error' class already exists in PHP and is a reserved word 
 */
if (
	(!isset($_SERVER['class']['error']))
	||
	(!$_SERVER['class']['error'])
){
	$_SERVER['class']['error'] = new err;
}

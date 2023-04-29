<?php

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
{"docu": {"Type": "snippet",
		"Short Purpose": "PHP File Header","Version": "2022.02.05",
		"Old Versions": "2019.07.11,2019.08.23"}}
 */

/*
 * make accessible from anywhere with CORS 
 * //todo API only
header('Access-Control-Allow-Origin:*');

/*
{"docu": {"Type": "snippet",
		"Short Purpose": "Scheduled Jobs Ignore Abort", "Version": "2019.06.29",
		"Old Versions": ""}}
 */
/*
 * if user is not waiting for a return then continue running even if user disconnects
 * and increase mex execution time to 60 minutes
 *
ob_end_clean();
header('Connection: close\r\n');
header('Content-Encoding: none\r\n');
header('Content-Length: 1');
ignore_user_abort(true);
ini_set('max_execution_time', 3600);

/*
 * Check if file was called directly and error out because file should never be called directly
 * 
 * //todo class files only
 * 
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])){
	echo 'Something went terribly wrong. :(';
	die;
}

/*
 * get file head
 */
if (
	(!file_exists('file_head.inc.php'))
	||
	(!include_once('file_head.inc.php'))
){
	$tmp = basename(__FILE__) . ':' . __LINE__;
	error_log("[ LAKEBED ][ {$tmp} ] Failed to include file_head.inc.php.");
	echo 'Something went terribly wrong. :(';
	die;
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * SETUP CLASSES CODE BLOCK
 * Everything below this point is setting up classes used in this file
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * Classes used in this file
 */
if (
	(!$_SERVER['class']['sanitize'] = new sanitize)
	||
	(!$_SERVER['class']['microservices'] = new microservices)
	||
	(!$_SERVER['class']['csrf'] = new csrf_new)
){
	$tmp = basename(__FILE__) . ':' . __LINE__;
	error_log("[ LAKEBED ][ {$tmp} ] Failed to init classes.");
	echo 'Something went terribly wrong. :(';
	die;
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * INIT  LOG CODE BLOCK
 * Ensure log security token is init'd for today so we can write to log after this point 
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

//todo change everything in file_head.inc.php and base.inc.php to error_log()

if (!file_exists("{$_SERVER['class']['constants']->docroot}/API/microservices/log/{$_SERVER['class']['constants']->day['today']}.security.php")){
	$_SERVER['class']['microservices']->call(array(
		'url' => "{$_SERVER['class']['constants']->server_url}/API/microservices/log/add.php", 
		'security_token' => '',
		'await_response' => 0, 
	));
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * CSRF/TOKEN CODE BLOCK
 * Everything below this point is checking CSRF/API token
 * No point going further if token is not provided or not valid
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */


/**
 * check simple security token just to reduce robots
 */
if (
	(empty($_GET['simple_security_token']))
	||
	($_GET['simple_security_token'] != gmdate('ymdH'))
){
	$_SERVER['class']['microservices']->call(array(
		'url' => "{$_SERVER['class']['constants']->server_url}/API/microservices/log/add.php", 
		'security_token' => md5(file_get_contents("{$_SERVER['class']['constants']->docroot}/API/microservices/log/{$_SERVER['class']['constants']->day['today']}.security.php")),
		'message' => 'Something went terribly wrong.', 
		'admin_only_message' => 'Simple security token is not set or not valid.', 
		'await_response' => 0, 
	));

	header('Status: 404 Not Found', false);
	header("Refresh:2;url=https://lakebed.io", false);

	echo $_SERVER['class']['microservices']->last_request['message'];

	die;
}


/**
 * confirm referer matches
 * //note we do this first b/c there's no point going further if HTTP_REF doesn't match
 * //note no point waiting for log response since we have to error out anyway
 */
if (
	(empty($_SERVER['HTTP_REFERER']))
	||
	(!$tmp = str_replace('://', '/', $_SERVER['class']['constants']->server_url))
	||
	(!$tmp = explode('/', $tmp))
	||
	(!count($tmp))
	||
	(empty($tmp[1]))
	||
	(strpos($_SERVER['HTTP_REFERER'], $tmp[1]) === false)
){
	$_SERVER['class']['microservices']->call(array(
		'url' => "{$_SERVER['class']['constants']->server_url}/API/microservices/log/add.php", 
		'security_token' => md5(file_get_contents("{$_SERVER['class']['constants']->docroot}/API/microservices/log/{$_SERVER['class']['constants']->day['today']}.security.php")),
		'message' => 'Something went terribly wrong.', 
		'admin_only_message' => 'HTTP Referer does not match.', 
		'await_response' => 0, 
	));

	header('Status: 404 Not Found', false);
	header("Refresh:2;url=https://lakebed.io", false);

	echo $_SERVER['class']['microservices']->last_request['message'];

	die;
}

/**
 * check simple security token
 */
//leftoff handle this after we setup redirect

/**
 * set CSRF browser flag
 * generate a new token if none available
 * and renew if valid and expired
 */
$_SERVER['class']['csrf']->browser_flag=1;
if (!$_SERVER['class']['csrf']->check_if_valid()){
	$new_token = $_SERVER['class']['csrf']->new_hash();
	$_SERVER['class']['csrf']->write_and_save($new_token);
}
$_SERVER['class']['csrf']->renew_if_expired();

/**
 * get/set next_url
 * //todo switch everything to _SESSION[next_url]
 * //future if JWT valid set next_url to /account
 */
$next_url = 'login/';
if (!empty($_SESSION['next_url'])){
	$next_url = $_SESSION['next_url'];
}

/*
{"docu": {"Type": 			"ChangeLog", 
		"2021-10-20 12:14:49": {
			"Time": "2021-10-20 12:14:49", 
			"Type": "Added",
			"Components": "write_csrf",
			"Description": "added HTTPS check since cookies are assumed to be secure if over https.",
			"Made By": "slepine"
		}
}}
 */
if (stripos($_SERVER['class']['constants']->server_url, 'https://') === false){
	header("Set-Cookie:PHPSESSID={$_COOKIE['PHPSESSID']};{$_SERVER['class']['constants']->cookie}", false);
}

/*
 * header redirect in case JS failed
 */
if (strpos($next_url, $_SERVER['class']['constants']->server_url) === false){
	$next_url = "{$_SERVER['class']['constants']->server_url}/{$next_url}";
}
header("Refresh:2;url={$next_url}", false);

/*
 * init _COOKIE if not
 */
if (
	(!isset($_COOKIE['csrf']))
	||
	(!is_string($_COOKIE['csrf']))
){
	$_COOKIE['csrf']='';
}

/*
 * handle hide_text
 */
$text_html = '<p>Getting your security ready</p>';
if (
	(isset($_GET['hide_text']))
	&&
	(strlen($_GET['hide_text']))
	&&
	($_GET['hide_text'])
){
	$text_html = '';
}

/*
 * output iframe check
 */
echo <<<m_echo
<html>
<script id='{$_COOKIE['csrf']}' >
if (
	(typeof window.self != 'object')
	||
	(typeof window.top != 'object')
	||
	(window.self !== window.top)
	||
	(window.frameElement !== null)
){
	alert('This site is loaded in an iFrame. This is likely a hacking attempt!!!');
	window.location.replace('{$_SERVER['class']['constants']->server_url}/login/logout.php');
}

if (
	(!'{$_COOKIE['csrf']}')
	||
	('{$_COOKIE['csrf']}' == '0')
){
	window.location.reload();
}

</script>

{$text_html}

</html>
m_echo;

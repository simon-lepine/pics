<?php
/*
{
	'docu': {
		'Type': 'file',
		
		'Short Purpose': 'File HEADer for easy code reuse.',
		'Long Purpose': 'Loops through each parent directory looking for af_head.inc.php to figure out Lakebed web/doc roots.',
		'Tags': '',
		'Intranet Tag': '',
		'Accepts': '',
		'Returns': '',
		'Input Required': '',
		'Input Optional': '',
		'Special Dev': ''
	}
}
*/

/*
 * handle errors
 * We do not set log errors here as the sysadmin needs to make that decision
 */
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

/*
 * start session before we do anything else so there is no browser output
 */
if (!$tmp = session_status()){
	$tmp = 1;
}
if (
	($tmp != 2)
	&&
	($tmp != PHP_SESSION_ACTIVE)
	&&
	(!isset($_SESSION))
){
	session_set_cookie_params(0, '/', $_SERVER['SERVER_NAME'], false, false);
	session_start();
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below is init/set _SERVER[return]
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * init _SERVER[ return ]
 */
if (
	(!isset($_SERVER['return']))
	||
	(!is_array($_SERVER['return']))
	||
	(!$_SERVER['return'])
){
$_SERVER['return'] = array(
	'success' => 0,
	'next_url' => '', 
	'result' => array(), 
	'message' => array(
		'error' => array(),
		'info' => array(),
		'success' => array(),
	)
);
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below is init/set _SERVER[class]
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * init _SERVER[ return ]
 */
if (
	(!isset($_SERVER['class']))
	||
	(empty($_SERVER['class']))
	||
	(!is_array($_SERVER['class']))
	||
	(!$_SERVER['class'])
){
	$_SERVER['class'] = array();
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * get constants/base and include
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

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
 * Get docroot
 */
if (
	(empty($_SERVER['class']))
	&&
	(empty($_SESSION['docroot']))
){
$tmp = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
while (
	($tmp)
	&&
	(strlen($tmp) > 3)
){	
	if (file_exists($tmp . DIRECTORY_SEPARATOR . 'base.inc.php')){
		$_SESSION['docroot'] = $tmp . DIRECTORY_SEPARATOR;
		break;
	}
	$tmp = dirname($tmp);
}
}

/*
 * get/set constants
 */
if (
	(file_exists($_SESSION['docroot'] . DIRECTORY_SEPARATOR . 'constants.inc.php'))
	&&
	(!include($_SESSION['docroot'] . DIRECTORY_SEPARATOR . 'constants.inc.php'))
){
	$tmp = basename(__FILE__) . ':' . __LINE__;
	error_log("[ LAKEBED ][ {$tmp} ] Failed to include constants.inc.php.");
	echo 'Something went terribly wrong. :(';
	
	header('Status: 404 Not Found', false);
	header('Location: https://lakebed.io', false);
	die;
}

/*
 * get/include base code
 */
if (
	(!file_exists($_SESSION['docroot'] . DIRECTORY_SEPARATOR . 'base.inc.php'))
	||
	(!include($_SESSION['docroot'] . DIRECTORY_SEPARATOR . 'base.inc.php'))
){
	$tmp = basename(__FILE__) . ':' . __LINE__;
	error_log("[ LAKEBED ][ {$tmp} ] Failed to include base.inc.php.");
	echo 'Something went terribly wrong. :(';
	
	header('Status: 404 Not Found', false);
	header('Location: https://lakebed.io', false);
	die;
}

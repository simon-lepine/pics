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
 * //class to handle app constants
 */
class constants {

/*
 * set data store
 */
public $separator = DIRECTORY_SEPARATOR;
public $server_url='';
public $error_url='/error/index.php';
public $cookie='';
public $docroot='';
public $webroot='/';
public $mail_api_key='';
public $forbidden_dir='';
public $last_message='';
public $message_array=array();

public $remote_addr='';

public $internal_hash='';
public $old_internal_hash='';

public $day = array();

/*
 * settings storage for various server-wide settings
 */
public $settings=array();
public $style=array();

/*
 * disable verious things
 * this can be used as feature flags
 */
public $disable = array(
	'login' => 0,
	'register' => 0,
	'development' => 0
);

/*
 * setup version and name
 */
public $version_number='';
public $version_name='';
public $server_name='';


/*
 * //function to construct
 */
function __construct(){

/*
 * set days
 */
$this->day = array(
	'yesterday' => gmdate('d', time() - 86400), 
	'today' => gmdate('d'), 
	'tomorrow' => gmdate('d', time() + 86400),
);

/*
 * set cookie string
 */
$this->cookie = ";SameSite=lax;Secure;path=/;domain={$_SERVER['HTTP_HOST']};";
$this->cookie = ";SameSite=lax;path=/;domain={$_SERVER['HTTP_HOST']};";//live remove for production

/*
 * set remote IP address for web app firewalls and other "forwarded" IPs
 */
if (
	(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	&&
	($_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR'])
){
	$this->remote_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
	$this->remote_addr = $_SERVER['REMOTE_ADDR'];
}

/*
 * update document root
 */
$this->docroot = dirname(__FILE__);
if (
	(!isset($this->docroot))
	||
	(!is_string($this->docroot))
	||
	(!isset($_SERVER['DOCUMENT_ROOT']))
	||
	(!is_string($_SERVER['DOCUMENT_ROOT']))
	||
	(strpos($this->docroot, $_SERVER['DOCUMENT_ROOT']) === false)
){
	echo 'Doc Root is not in DOCUMENT_ROOT.';
	die;
}

/*
 * update forbidden_dir
 */
if (!$this->set_forbidden_dir()){
	echo 'Failed to determine forbidden directory.';
	die;
}

/*
 * update web root
 */
$this->webroot = str_replace(
	$_SERVER['DOCUMENT_ROOT'], 
	'', 
	dirname(__FILE__)
) . '/';
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $this->separator . $this->webroot)){
	echo 'Web Root is not in Doc Root.';
	die;
}

/*
 * set server_url
 */
$this->set_server_url();

/*
 * cleanup directories
 */
$this->cleanup_directories();

/*
 * 

/*
 * return success
 */
return true;

/*
 * done //function
 */
}

/*
 * //function to get/store js/html constants
 * //note we store this here so we have a single source of truth for PHP, HTML, React, and JS
 */
function get_js(){

/*
 * set styles
 * //note space at the end of each in case we forget when we append classes
 * 
	<button
		className={$this->style['blue_button'] + 'w-full'}
	>
			TEXT HERE
	</button>

	<button
		className={$this->style['grey_button']}
	>
		TEXT HERE
	</button>

	<a
		href='#'
		className={$this->style['grey_button']}
	>
		TEXT HERE
	</a>
 */
$this->style['button'] = ' ml-0 text-white px-3 py-2 font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 border-0 ';
$this->style['green_button'] = $this->style['button'] . ' bg-green-600 focus:ring-green-700 ';
$this->style['blue_button'] = $this->style['button'] . ' text-white lakebed_background hover:bg-blue-400 focus:ring-blue-600 ';
$this->style['grey_button'] = $this->style['button'] . ' bg-gray-100 hover:bg-grey-200 text-gray-700 ';
$this->style['red_button'] = $this->style['button'] . ' bg-red-500 hover:bg-red-600 ';
$this->style['close_button'] = $this->style['red_button'] . ' close_button ';

/*
 * section border
 */
$this->style['section_border'] = ' section_border border-gray-200 p-4 ';

/*
<label
	className={$this->style['label}
>

</label>
 */
$this->style['label'] = ' w-full block text-sm font-bold text-gray-700';

/*
<input
	name=""
	type="" 
	autoComplete=""
	required
	className={$this->style['input}
/>
 */
$this->style['input'] = $this->style['checkbox'] = ' block px-3 py-2 border border-gray-300 rounded-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm ';
$this->style['input'] = "{$this->style['input']} w-full";


/*
<a
	href='#'
	className={$this->style['a_href}
	onClick={function(element){
		FUNCTION(element);
	}}
>

</a>
 */
$this->style['a_href'] = ' font-medium text-blue-600 hover:text-blue-500 ';

/*
 * active tab
 */
$this->style['active_tab'] = ' bg-gray-200 text-gray-900 ';

/*
 * h1/2/3
 */
$this->style['h1'] = ' text-3xl pb-4 font-medium leading-8 text-gray-900 sm:truncate ';
$this->style['h2'] = ' text-2xl pb-2 font-medium leading-8 text-gray-900 sm:truncate ';
$this->style['h3'] = ' text-1xl pb-1 font-medium leading-8 text-gray-900 sm:truncate font-bold ';

/*
 * content div
 */
$this->style['content_div'] = ' main-content flex-1 mt-2 pb-5 py-6 px-8 ';

/*
 * return true
 */
return true;

/*
 * done //function
 */
}

/*
 * //function to output JS/html constants
 */
function output_js(){

/*
 * init return
 */
$return = array();

/*
 * add server vars
 */
$return[] = <<<m_var

if (typeof window['class'] != 'object'){
	window['class'] = {};
}
if (typeof window['class']['constants'] != 'object'){
	window['class']['constants'] = {};
}
window['class']['constants']['webroot'] = ' {$this->webroot} ';
window['class']['constants']['server_url'] = ' {$this->server_url} ';

m_var;

/*
 * build style
 */
foreach ($this->style AS $key=>$value){
	$return[] = <<<m_var

window['class']['constants']['style']['{$key}'] = ' {$value} ';

m_var;
}

/*
 * implode out output
 */
echo '<script>' . implode('', $return) . '</script>';


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
	(!file_exists("{$this->forbidden_dir}{$this->separator}settings.inc.php"))
	||
	(!include("{$this->forbidden_dir}{$this->separator}settings.inc.php"))
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
function get_hash(){

/*
 * set internal hash
 */
$this->internal_hash =
	md5(date('--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--') . $_SERVER['SERVER_ADMIN'])
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--') . $_SERVER['SERVER_ADMIN'])
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--') . $_SERVER['SERVER_ADMIN'])
;

/*
 * set OLD internal hash
 * we do this in case a query crosses the hour, this allows a 60 second buffer
 */
$this->old_internal_hash =
	md5(date('--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--Y-m-d H--', time()-120) . $_SERVER['SERVER_ADMIN'])
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--') . $_SERVER['SERVER_ADMIN'])
	.
	md5(date('--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--H Y-m-d--', time()-120) . $_SERVER['SERVER_ADMIN'])
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
 * //function to set forbidden_dir based on docroot
 */
function set_forbidden_dir(
	$forbidden_dir=''
){

//future accept forbidden dir arg and set based on that
$tmp = dirname(__FILE__);
$tmp =  basename($tmp);
if (
	($tmp != 'public_html')
	&&
	($tmp = str_replace('public_html', '', $tmp))
	&&
	(file_exists("{$this->docroot}{$this->separator}..{$this->separator}{$tmp}forbidden_dir"))
	&&
	(file_exists("{$this->docroot}{$this->separator}..{$this->separator}{$tmp}forbidden_dir{$this->separator}settings.inc.php"))
){
	$this->forbidden_dir = "{$this->docroot}{$this->separator}..{$this->separator}{$tmp}forbidden_dir";
	return $this->forbidden_dir;
}

/*
 * get/set production
 */
if (
	(file_exists("{$this->docroot}{$this->separator}..{$this->separator}forbidden_dir"))
	&&
	(file_exists("{$this->docroot}{$this->separator}..{$this->separator}forbidden_dir{$this->separator}settings.inc.php"))
){
	$this->forbidden_dir = "{$this->docroot}{$this->separator}..{$this->separator}forbidden_dir";
	return $this->forbidden_dir;
}

/*
 * get/set production
 */
if (
	(file_exists("{$this->docroot}{$this->separator}..{$this->separator}..{$this->separator}forbidden_dir"))
	&&
	(file_exists("{$this->docroot}{$this->separator}..{$this->separator}..{$this->separator}forbidden_dir{$this->separator}settings.inc.php"))
){
	$this->forbidden_dir = "{$this->docroot}{$this->separator}..{$this->separator}..{$this->separator}forbidden_dir";
	return $this->forbidden_dir;
}

/*
 * get/set dev
 * we do this so forbidden_dir is within Git directory
 */
if (file_exists("{$this->docroot}{$this->separator}{$this->separator}forbidden_dir")){
	$this->forbidden_dir = "{$this->docroot}{$this->separator}{$this->separator}forbidden_dir";
	return $this->forbidden_dir;
}

/*
 * done //function
 */
}

/*
 * //function to cleanup (trim) directories
 */
function cleanup_directories(){

/*
 * docroot
 */
$this->docroot = realpath($this->docroot);
$this->docroot = rtrim($this->docroot, '\/');
$this->docroot = str_replace(
	array('//', '\\\\'), 
	$this->separator, 
	$this->docroot
);
$this->docroot = $this->docroot . $this->separator;

/*
 * webroot
 */
$this->webroot = realpath($this->webroot);
$this->webroot = rtrim($this->webroot, '/');
$this->webroot = str_replace(
	array('//', '\\'), 
	'/', 
	$this->webroot
);
$this->webroot = $this->webroot . '/';

/*
 * forbidden_dir
 */
$this->forbidden_dir = realpath($this->forbidden_dir);
$this->forbidden_dir = rtrim($this->forbidden_dir, '\/') . $this->separator;

/*
 * server url
 */
$this->server_url = str_replace('://', 'SERVER_URL_SEPARATOR', $this->server_url);
$this->server_url = str_replace('//', '/', $this->server_url);
$this->server_url = trim($this->server_url, '/');
$this->server_url = str_replace('SERVER_URL_SEPARATOR', '://', $this->server_url) . '/';

return true;

/*
 * done //function
 */
}

/*
 * //function to set server_url (server name + web root)
 */
function set_server_url(){

/*
 * init tmp array
 */
$tmp=array(
	'scheme' => '', 
	'host' => ''
);

/*
 * check if _SESSION is set
 */
if (
	(isset($_SESSION['server_url']))
	&&
	(is_string($_SESSION['server_url']))
	&&
	($_SESSION['server_url'])
){
	$this->server_url = $_SESSION['server_url'];
	return $this->server_url;
}

/*
 * get server name/IP
 */
if (
	(!$tmp['host'])
	&&
	(is_array($_SERVER))
	&&
	(isset($_SERVER['HTTP_HOST']))
	&&
	(is_string($_SERVER['HTTP_HOST']))
	&&
	($_SERVER['HTTP_HOST'])
){
	$tmp['host'] = $_SERVER['HTTP_HOST'];
}
if (
	(!$tmp['host'])
	&&
	(is_array($_SERVER))
	&&
	(isset($_SERVER['SERVER_NAME']))
	&&
	(is_string($_SERVER['SERVER_NAME']))
	&&
	($_SERVER['SERVER_NAME'])
){
	$tmp['host'] = $_SERVER['SERVER_NAME'];
}
if (
	(!$tmp['host'])
	&&
	(is_array($_SERVER))
	&&
	(isset($_SERVER['SERVER_ADDR']))
	&&
	(is_string($_SERVER['SERVER_ADDR']))
	&&
	($_SERVER['SERVER_ADDR'])
){
	$tmp['host'] = $_SERVER['SERVER_ADDR'];
}

/*
 * set/defualt http scheme
 */
if (
	(!isset($tmp['scheme']))
	||
	(!is_string($tmp['scheme']))
	||
	(!$tmp['scheme'])
){
	$tmp['scheme'] = $_SERVER['REQUEST_SCHEME'];
}
if (
	(!isset($tmp['scheme']))
	||
	(!is_string($tmp['scheme']))
	||
	(!$tmp['scheme'])
){
if (
	(isset($_SERVER['SERVER_PORT']))
	&&
	($_SERVER['SERVER_PORT'] === 443)
){
	$tmp['scheme'] = 'https';
}
}
if (
	(!isset($tmp['scheme']))
	||
	(!is_string($tmp['scheme']))
	||
	(!$tmp['scheme'])
){
if (
	(isset($_SERVER['SSL_PROTOCOL']))
	&&
	($_SERVER['SSL_PROTOCOL'])
){
	$tmp['scheme'] = 'https';
}
}
if (
	(!isset($tmp['scheme']))
	||
	(!is_string($tmp['scheme']))
	||
	(!$tmp['scheme'])
){
if (
	(isset($_SERVER['SERVER_PORT']))
	&&
	($_SERVER['SERVER_PORT'])
){
	$tmp['scheme'] = 'http';
}
}

/*
 * re-combine server
 */
if (
	(!isset($_SERVER['server_url']))
	||
	(!$_SERVER['server_url'])
){
	$this->server_url = "{$tmp['scheme']}://{$tmp['host']}/{$this->webroot}/";
	$this->cleanup_directories();
	$_SESSION['server_url'] = $this->server_url;
	return $this->server_url;
}

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
if (
	(!isset($_SERVER['class']))
	||
	(!is_array($_SERVER['class']))
){
	$_SERVER['class']=array();
}
if (
	(!isset($_SERVER['class']['constants']))
	||
	(!$_SERVER['class']['constants'])
){
	$_SERVER['class']['constants'] = new constants;
}

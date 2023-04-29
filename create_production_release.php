<?php

//iicnet_dev the only reason this is in the /inc folder is because .htaccess prevents access to non-listed pages in the root directory

/*
 * //debug //live //special comment in/out to [dis]able file
 */
unset($_GET['pazz']);

/*
 * include FIle HEADer
 */
if (
	(!file_exists('file_head.inc.php'))
	||
	(!include ('file_head.inc.php'))
){
	echo 'Something went terribly wrong :(';
	die;
}

/*
 * confirm pazzword is set
 * //special for this file
 */
if (
	(!isset($_GET['pazz']))
	||
	(!is_string($_GET['pazz']))
	||
	(!$_GET['pazz'])
){
	http_response_code(301);
	header("HTTP/1.0 301 Permanently Moved", false);
	header("Location:{$_SERVER['class']['constants']->server_url}/login/logout.php", false);
}
if ($_GET['pazz'] != date('Y-m-d-m-Y')){
echo <<<m_echo
<form method='GET'>

	<p><input type='text' name=pazz /></p>
	
	<p><button>Submit</button></p>

</form>
m_echo;
die;
}

ini_set('max_execution_time', 3600);
ini_set('display_errors', 1);//debug
error_reporting(E_NOTICE | E_ALL);//debug

/*
 * classes used in file
 * //special for //iicnet_dev since we do not use JWTs yet
if (
	(!$_SERVER['class']['err'] = new err)
	||
	(!$_SERVER['class']['jwt'] = new jwt)
){
	$_SERVER['class']['general']->log(
		'Something went terribly wrong :(',
		'Error',
		'Extreme',
		'Fatal'
	);
	echo $_SERVER['class']['general']->last_mess;
	die;
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below this point is security
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * check REQuired and OPTional vars
 */

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below this point is JWT
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * confirm JWT is valid
 * //special for //iicnet_dev since we do not use JWTs yet
 * 
if (!$_SERVER['class']['jwt']->is_valid()){
	$_SERVER['class']['general']->log(
		'Security check failed.',
		'Error',
		'Extreme',
		'Error'
	);
	echo $_SERVER['class']['general']->last_mess;
	die;
}

/*
 * confirm JWT is valid
 * //special for //iicnet_dev since we do not use JWTs yet
 * 
if (!$_SERVER['class']['jwt']->parse_payload()){
	$_SERVER['class']['general']->log(
		'Security check failed.',
		'Error',
		'Extreme',
		'Error'
	);
	echo $_SERVER['class']['general']->last_mess;
	die;
}

/*
 * renew JWT
 * //special for //iicnet_dev since we do not use JWTs yet
 * 
if (!$_SERVER['class']['jwt']->renew()){
	$_SERVER['class']['general']->log(
		'Security check failed. ',
		'Error',
		'medium',
		'error',
		'Failed to renew JWT.'
	);
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below this point is database
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */


/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below this point is sanitizing user input
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * sanatize //todo
 */


/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything above this point is the same in every file and commented in/out as needed
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * //func to parse/generate form_data from _GET
 */
function generate_form_data(){

$return=array();

foreach ($_GET AS $key=>$value){
	$return[] = <<<m_var

<input type=hidden name='{$key}' value='{$value}' />
m_var;
}

return implode('', $return);

/*
 * done //func
 */
}

/*
 * set directories
 */
$new_docroot=$_SERVER['DOCUMENT_ROOT'] .  $_SERVER['separator'] . '..' . $_SERVER['separator'] . date('Y-m-d-') . 'public_html';
$new_forbidden_dir=$_SERVER['DOCUMENT_ROOT'] .  $_SERVER['separator'] . '..' . $_SERVER['separator'] . date('Y-m-d-') . 'forbidden_dir';
$current_forbidden_dir = basename($_SERVER['DOCUMENT_ROOT']);
$current_forbidden_dir = str_replace('public_html', 'forbidden_dir', $current_forbidden_dir);
$current_forbidden_dir = $_SERVER['DOCUMENT_ROOT'] .  $_SERVER['separator'] . '..' . DIRECTORY_SEPARATOR . $current_forbidden_dir;

/*
 * confirm we have the correct forbidden dir
 */
if (
	(!is_dir($current_forbidden_dir))
	||
	(!file_exists($current_forbidden_dir))
){
	echo "Current forbidden directory ({$current_forbidden_dir}) does not exist.";
	die;
}

/*
 * create new directories
 * first new docroot
 * second forbidden dir
 */
if (
	(!isset($_GET['create_directories']))
	||
	(!$_GET['create_directories'])
){
if (
	(is_dir($new_docroot))
	||
	(file_exists($new_docroot))
){
	echo "New document root ({$new_docroot}) already exists.";
	die;
}
if (
	(!mkdir($new_docroot, 0777, true))
	||
	(!is_dir($new_docroot))
){
	echo "Failed to create new document root ({$new_docroot}).";
	die;
}
}
if (
	(!isset($_GET['create_directories']))
	||
	(!$_GET['create_directories'])
){
if (
	(is_dir($new_forbidden_dir))
	||
	(file_exists($new_forbidden_dir))
){
	echo "New forbidden directory ({$new_forbidden_dir}) already exists.";
	die;
}
if (
	(!mkdir($new_forbidden_dir, 0777, true))
	||
	(!is_dir($new_forbidden_dir))
){
	echo "Failed to create new forbidden directory ({$new_forbidden_dir}).";
	die;
}
}

/*
 * Confirm directories created successfully/correctly
 */
if (
	(!isset($_GET['create_directories']))
	||
	(!$_GET['create_directories'])
){
$form_data = generate_form_data();
echo <<<m_echo
<h3>Backup Current release</h3>

<p>The system will automatically copy the current production release. After this is done, review that folder and confirm success.</p>

<form method='GET' action=''>
<p><button>Yes, new directories were created correctly</button></p>

{$form_data}
<input type=hidden name=create_directories value='1' />

</form>

m_echo;
die;
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below is making a backup copy of current public_html
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * set list of files to ignore
 */
$ignore_files=array(
	'.php',
	'.js',
	'.css',
	'.htaccess',
	'.html',
	'.htm',
	'.sql',
	'.sh',
	'.tff',
	'.dist',
	'.db',
	'.dat',
	'.class',
	'.json',
	'.lock',
	'.log',
	'.md',
	'.phps',
	'.yml',
	'.xml',
	'.svg',
	'fontello',
	'/inc/',
	'.ico',
	'.woff',
	'/dev_tools/',
	'.git',
	'/ps_END_OF_FILE_NAME',
	'/pl_END_OF_FILE_NAME',
	'/DELETE_', 
	'.txt'
);


/*
 * get directories to loop through
 * //future need seperate rii for copy code and search for //todo
 */
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$_SERVER['DOCUMENT_ROOT']}"));

/*
 * Loop through all files
 * and
 * Copy to release folder
 */
if (!is_object($rii)){$rii = array();}
foreach ($rii as $file) {

/*
 * confirm we have data
 */
if (!$file){
	continue;
}

/*
 * sanatize file path
 */
$file_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file->getPathname());
$new_path = "{$new_docroot}/{$file_path}";
$new_path = str_replace('//', '/', $new_path);
$old_path = "{$_SERVER['DOCUMENT_ROOT']}/{$file_path}";
$old_path = str_replace('//', '/', $old_path);

/*
 * skip ignored files
 */
if (basename($old_path) == '.'){continue;}
if (basename($old_path) == '..'){continue;}
if (
	(!is_dir($old_path))
	&&
	($old_path != str_replace($ignore_files, '', $old_path))
){
	continue;
}
if (
	(!is_dir($old_path))
	&&
	("{$old_path}_END_OF_FILE_NAME" != str_replace($ignore_files, '', "{$old_path}_END_OF_FILE_NAME"))
){
	continue;
}

/*
 * create directories
 */
if (
	(!isset($_GET['copy_code']))
	||
	(!$_GET['copy_code'])
){
if (
	(is_dir($old_path))
	&&
	(!is_dir($new_path))
){
	mkdir($new_path, 0777, true);
	echo "<p>Created directory /{$file_path}</p>";
}
}

/*
 * ensure directory exists
 */
if (
	(!isset($_GET['copy_code']))
	||
	(!$_GET['copy_code'])
){
if (
	(is_file($old_path))
	&&
	(!is_dir(dirname($new_path)))
){
	mkdir(dirname($new_path), 0777, true);
}
}

/*
 * copy files
 */
if (
	(!isset($_GET['copy_code']))
	||
	(!$_GET['copy_code'])
){
if (
	(is_file($old_path))
	&&
	(!is_file($new_path))
){
	copy($old_path, $new_path);
	chmod($new_path, 0777);
	echo "<p>Created file {$file_path}</p>";
}
}

/*
 * set permissions
 */
if (
	($new_path)
	&&
	(dirname($new_path))
	&&
	(is_dir(dirname($new_path)))
	&&
	(file_exists($new_path))
){
	chmod(dirname($new_path), 0777);
}
if (
	(is_file($new_path))
	&&
	(file_exists($new_path))
){
	chmod($new_path, 0777);
}

/*
 * build todo, debug, bug, and leftoff arrays
 */
if (is_file($new_path)){
	$file_list[] = $new_path;
}

/*
 * done foreach
 */
}

/*
 * copy under_construction page
 */
if (
	(!isset($_GET['copy_code']))
	||
	(!$_GET['copy_code'])
){
if (!file_exists($new_docroot . $_SERVER['separator'] . 'index.php')){
	copy(
		$_SERVER['DOCUMENT_ROOT'] . $_SERVER['separator'] . 'under_construction.php',
		$new_docroot . $_SERVER['separator'] . 'index.php'
	);
}
}

/*
 * create .htaccess to redirect all 404's and 500s
 */
$tmp = <<<m_var

ErrorDocument 404 https://iicnet.iicpartners.com/index.php
ErrorDocument 500 https://iicnet.iicpartners.com/index.php

m_var;
file_put_contents($new_docroot . $_SERVER['separator'] . '.htaccess', $tmp);


/*
 * copy files to new docroot
 */
if (
	(!isset($_GET['copy_code']))
	||
	(!$_GET['copy_code'])
){
$form_data = generate_form_data();
echo <<<m_echo
<h3>Code Copied to New Document Root</h3>

<p>The system automatically copies the current production release to the new release directory. After this is done:</p>
<ul>
	<li>Confirm {$new_docroot} exists and includes necessary files.</li>
	<li>Confirm {$new_forbidden_dir} exists and includes necessary files.</li>
	<li>Confirm /index.php exists and includes "under construction" text.</li>
	<li>Confirm /.htaccess exists and redirects all 404/500 to /index.php.</li>
	<li>Change the Apache2 docroot to point to the new docroot.</li>
	<li>Copy release code to production. The only file that should be overwritten is /index.php.</li>
</ul>

<form method='GET' action=''>
<p><button>Yes, done!</button></p>

{$form_data}
<input type=hidden name=copy_code value='1' />

</form>

m_echo;
die;
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything below is creating a new public_html
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * copy files to /release_code/
 */
if (
	(!isset($_GET['upload_code']))
	||
	(!$_GET['upload_code'])
){
echo <<<m_echo
<h3>Upload new release</h3>

<p>Upload the new release files to {$release_directory}. </p>

<form method='GET' action=''>
<p><button>Yes, the files are uploaded</button></p>

{$form_data}
<input type=hidden name=upload_code value='1' />

</form>

m_echo;
die;
}

/*
 * Done
 */
echo <<<m_echo
<h3>Done!</h3>

<p>Now you're done and you can QA test that the new release is working as expected.</p>

m_echo;

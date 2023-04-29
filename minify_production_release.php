<?php

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
 * set max exec time
 */
ini_set('max_execution_time', 7200);


/*
 * classes used in file


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


/*
 * confirm JWT is valid


/*
 * renew JWT


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
 * //input_required _GET[api_token]
 */
if (
	(empty($_GET['api_token']))
	||
	(!is_string($_GET['api_token']))
	||
	(!$_GET['api_token'])
	||
	(!$_GET['api_token'] = strtolower($_GET['api_token']))
	||
	(!$_GET['api_token'] = preg_replace("/[^a-z0-9]/", '', $_GET['api_token']))
){
	$_GET['api_token'] = '';
}

/*
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * END HEADER CODE BLOCK
 * Everything above this point is the same in every file and commented in/out as needed
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 */

/*
 * confirm API token is valid
 * //note copy/paste/run the hash() to get the required token
 * //note we do this so this file can be securely auto-run from Git/CI pipelines
 */
if (
	$_GET['api_token']
	!=
	hash('sha512', "Simon Le Pine's super secret key. Created in 2022!")
){
	header('Location: https://lakebed.io', true, 301);
	die;
}

/*
 * get directories to loop through
 * //future need seperate rii for copy code and search for //todo
 */
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));

/*
 * Loop through all files
 * and
 * Copy to release folder
 */
if (!is_object($rii)){$rii = array();}
foreach ($rii as $file) {

/*
 * confirm we have a file and get the pathname
 */
if (
	(!$file)
	||
	($file->isDir())
	||
	(!$file_path = realpath($file->getPathname()))
){
	continue;
}

/*
 * init content
 */
$content = '';

/*
 * skip non-php files
 */
if (
	(strpos("{$file_path}zzzz", '.phpzzzz') === false)
	||
	(strpos($file_path, basename(__FILE__)) !== false)
){
	continue
}

/*
 * strip white space
 */
if (
	(!$content = php_strip_whitespace("{$file_path}"))
	||
	(!$content)
){
	echo "<h1>Failed to get/strip file content ({$file_path})</h1>";
	continue;
}

/*
 * replace multi lines
 */
$content = str_replace("\n\n", "\n", $content);

/*
 * replace Multi line ECHO next to each other for more efficient parsing
 */
$tmp=array();
$tmp[] = <<<m_var
m_echo;
echo <<<m_echo
m_var;
$tmp[] = '	';
$content = str_replace($tmp, '', $content);

/*
 * replace spaces (' = ')
 */
$content = str_replace(' = ', '=', $content);


/*
 * put file content back
 */
if (strlen($content) > 5){
	if (!file_put_contents($file_path, $content)){
		echo "<h1>FAILED to put file contents in {$file_path}</h1>";
		continue;
	}
}

/*
 * output success
 */
echo "<p>Done {$file_path}</p>";

/*
 * done foreach 
 */

}

//leftoff overwrite/delete this file so it cannot continue running
//leftoff write scheduled job to run this file automatically on prod
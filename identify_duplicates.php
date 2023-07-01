<?php

include 'file_head.inc.php';

include 'settings.inc.php';
require "{$composer_dir}autoload.php";
require 'get_all_cache_files.inc.php';
krsort($aws_cache);

/**
 * handle limit
 */
if (empty($_GET['limit'])){
	$_GET['limit'] = 50;
}

/**
 * get cache directory
 */
$cache_dir = dirname(__FILE__) . '/.cache';

/**
 * loop through cache
 */
$year_month='';
$rm_command='';
$prevent_duplicates=array();
$duplicate_count=0;
foreach ($aws_cache AS $key=>$file){

/**
 * if duplicate exists
 */
$sha1 = sha1_file("{$cache_dir}/{$file['file_name']}");
$sha1 = "{$file['year_uploaded']}-{$file['month_uploaded']}-{$file['size']}-{$sha1}";
if (!empty($prevent_duplicates[ $sha1 ])){

/**
 * handle limit
 */
$duplicate_count++;
if ($duplicate_count > $_GET['limit']){
	die;
}

/**
 * get path info
 */
$pathinfo = pathinfo($prevent_duplicates[ $sha1 ]['file_name']);
$pathinfo = $pathinfo['filename'];
$rm_command_1 = "rm {$prevent_duplicates[ $sha1 ]['file_name']}; rm *{$pathinfo}*.php;";

$pathinfo = pathinfo($file['file_name']);
$pathinfo = $pathinfo['filename'];
$rm_command_2 = "rm {$file['file_name']}; rm *{$pathinfo}*.php;";
/**
 * unlink file if 2 cache files but only 1 thumbnail
 */
if (
	($rm_command_1 == $rm_command_2)
	&&
	($prevent_duplicates[ $sha1 ]['file_name'] == $file['file_name'])
){
	unlink(".cache/{$key}.php");
	continue;
}
if ($rm_command_1 == $rm_command_2){
	$rm_command_1 = '#Both file names are the same.';
}

/**
 * output HTML
 */
echo <<<m_echo

<h3>Duplicate Found {$file['file_name']}</h3>

<p>
	{$prevent_duplicates[ $sha1 ]['key']} - {$prevent_duplicates[ $sha1 ]['file_name']}
	<br />
	<img src='.cache/{$prevent_duplicates[ $sha1 ]['file_name']}'/>
</p>

<p>
	{$key} - {$file['file_name']}:
	<br />
	<img src='.cache/{$file['file_name']}' />
</p>


<p>
	Remove with the following command:
	<br />
	<b>DO NOT FORGET TO REMOVE FROM AWS TOO</b>
</p>

<textarea style='width:50%' rows='4' >
{$rm_command_1}

{$rm_command_2}
</textarea>

m_echo;


/**
 * done if
 */
}
$prevent_duplicates[ $sha1 ] = array(
	'key' => $key, 
	'file_name' => $file['file_name'], 
);

/**
 * done foreach
 */
}


if (empty($duplicate_count)){
	echo 'No duplicates found :)';
}
<?php

include 'file_head.inc.php';

include 'settings.inc.php';
require "{$composer_dir}autoload.php";
require 'get_all_cache_files.inc.php';
krsort($aws_cache);

/**
 * get cache directory
 */
$cache_dir = dirname(__FILE__) . '/.cache';

/**
 * //function to output an image
 */
$image_count=0;
$prevent_duplicates=array();
function output_image($values){

/**
 * globals
 */
global $cache_dir, $image_count, $prevent_duplicates;

/**
 * handle limit
 */
$image_count++;
if (
	($_GET['limit'])
	&&
	($image_count > $_GET['limit'])
){
	return false;
}

/**
 * confirm we have a thumbnail
 */
if (!file_exists("{$cache_dir}/{$values['file_name']}")){
	return true;
}

/**
 * prevent duplicates
 */
$tmp = sha1_file("{$cache_dir}/{$values['file_name']}");
$tmp = "{$values['year_uploaded']}-{$values['month_uploaded']}-{$values['size']}-{$tmp}";
if (!empty($prevent_duplicates[ $tmp ])){
	return true;
}
$prevent_duplicates[ $tmp ] = $tmp;

/**
 * return HTML
 */
echo <<<m_echo

<div class='image_container'>
	<a href='{$_SERVER['class']['constants']->server_url}/view.php?file={$values['key']}'>
		<img src='{$_SERVER['class']['constants']->server_url}/.cache/{$values['file_name']}' />
	</a>
</div>

m_echo;

/**
 * return
 */
return true;

/**
 * done //function
 */
}

/**
 * init limit
 */
if (
	(empty($_GET['limit']))
	||
	(!is_numeric($_GET['limit']))
){
	$_GET['limit'] = 500;
}

/**
 * Start output
 */
$get_url = http_build_query($_GET);
echo <<<m_echo

	<style>
		img {
			width: 100%;
			height: 100%;
			object-fit: contain;
		}
		.image_container {
			width:10rem;
			height:7.5rem;
			display:inline-flex;
			border:1px solid #c3c3c3;
			margin:0.1rem;
		}
	</style>

	<h2>Display Limit ({$_GET['limit']} Currently)</h2>
	<ul>
		<li><a href='?{$get_url}&limit=200'>200</a>
		<li><a href='?{$get_url}&limit=500'>500</a>
		<li><a href='?{$get_url}&limit=1000'>1,000</a>
		<li><a href='?{$get_url}&limit=10000'>10,000</a>
		<li><a href='?{$get_url}&limit=99999999'>All</a>
	</ul>

m_echo;

/**
 * loop through cache
 * and output current files
 */
$year_month='';
foreach ($aws_cache AS $key=>$file){

/**
 * skip old files
 */
if ($file['year_uploaded'] < 1982){
	continue;
}

/**
 * output year/month
 */
if ($year_month != "{$file['year_uploaded']}-{$file['month_uploaded']}"){
	$year_month = "{$file['year_uploaded']}-{$file['month_uploaded']}";
	echo "<h2>{$year_month}</h2>";
}

/**
 * output HTML
 */
$file['key'] = $key;
if (!output_image($file)){
	break;
}

/**
 * done foreach
 */
}

/**
 * output OLD heading
 */
if ($image_count < $_GET['limit']){
	echo '<h2>OLD or Unknown Dates</h2>';
}

/**
 * loop through cache
 * and output OLD files
 */
foreach ($aws_cache AS $key=>$file){

/**
 * break if we've hit limit
 */
if ($image_count > $_GET['limit']){
	break;
}

/**
 * skip old files
 */
if ($file['year_uploaded'] > 1982){
	continue;
}

/**
 * output HTML
 */
$file['key'] = $key;
if (!output_image($file)){
	break;
}

/**
 * done foreach
 */
}


/**
 * output we got nothing
 */
if (!$image_count){
	echo "<h1>Sorry, looks like I don't have anything to show you.";
}

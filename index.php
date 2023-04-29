<?php

include 'file_head.inc.php';

echo '<pre>';
print_r($_SERVER);
echo '</pre>';
die;

include 'settings.inc.php';
require "{$composer_dir}autoload.php";
require 'get_all_cache_files.inc.php';
krsort($aws_cache);

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$credentials = new Aws\Credentials\Credentials($aws_key, $aws_secret);

// Instantiate the client.
$s3 = new S3Client([
	'credentials' => $credentials, 
	'version' => 'latest',
	'region'  => $aws_region, 
]);

/**
 * init limit
 */
if (
	(empty($_GET['limit']))
	||
	(!is_numeric($_GET['limit']))
){
	$_GET['limit'] = 250;
}


/**
 * get cache directory
 */
$cache_dir = dirname(__FILE__) . '/.cache';

/**
 * Start output
 */
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

m_echo;

/**
 * loop through cache
 */
$year_month='';
$image_count=0;
foreach ($aws_cache AS $key=>$file){

/**
 * handle limit
 */
$image_count++;
if (
	($_GET['limit'])
	&&
	($image_count > $_GET['limit'])
){
	break;
}

/**
 * output year/month
 */
if ($year_month != "{$file['year_uploaded']}-{$file['month_uploaded']}"){
	$year_month = "{$file['year_uploaded']}-{$file['month_uploaded']}";
	echo "<h2>{$year_month}</h2>";
}

/**
 * confirm we have a thumbnail
 */
if (
	(!empty($_GET['thumbnail_links']))
	&&
	(!file_exists("{$cache_dir}/{$file['file_name']}"))
){
echo <<<m_echo

<div class='image_container'>
	<a href='create_thumbnail.php?file={$key}' target='_BLANK'>
		Generate Thumbnail
	</a>
</div>

m_echo;

continue;
}
if (!file_exists("{$cache_dir}/{$file['file_name']}")){
	continue;
}

echo <<<m_echo

<div class='image_container'>
	<a href='view.php?file={$key}'>
		<img src='.cache/{$file['file_name']}' />
	</a>
</div>

m_echo;

/**
 * done foreach
 */
}

if (!$image_count){
	echo "<h1>Sorry, looks like I don't have anything to show you.";
}
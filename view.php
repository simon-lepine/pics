<?php

include 'settings.inc.php';
require "{$composer_dir}autoload.php";
//include 'aws_cache.php';

require 'get_all_cache_files.inc.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * get/set AWS creds
 */
$credentials = new Aws\Credentials\Credentials($aws_key, $aws_secret);

// Instantiate the client.
$s3 = new S3Client([
	'credentials' => $credentials, 
	'version' => 'latest',
	'region'  => $aws_region, 
]);

/**
 * ensure we have a file
 */
if (
	(empty($_GET['file']))
	||
	(!$_GET['file'] = preg_replace("/[^a-zA-Z0-9-_.]/", "", "{$_GET['file']}"))
	||
	(!file_exists(".cache/{$_GET['file']}.php"))
){
	echo "<h1>Sorry, looks like I don't have anything to show you.";
	die;
}

/**
 * get cache data
 *
$aws_cache=array();
include(".cache/{$_GET['file']}.php");

/**
 * get a short-term URL
 */
$cmd = $s3->getCommand('GetObject', [
	'Bucket' => $aws_bucket,
	'Key' => $aws_cache[ $_GET['file'] ]['file_name']
]);
$request = $s3->createPresignedRequest($cmd, '+10 minutes');

// Get the actual presigned-url
$image_url = $request->getUri();

/**
 * get previous/next urls
 */
$prev='';
$next='';
$tmp=false;
$keys=array_keys($aws_cache);
if (
	($tmp=array_search($_GET['file'], $keys)-1)
	&&
	(!empty($keys[ $tmp ]))
){
$next = <<<m_echo
	<a
		href='?file={$keys[ $tmp ]}'
		class='prev'
	>
		Older Pic
	</a>
m_echo;
}
$tmp=false;
if (
	($tmp=array_search($_GET['file'], $keys)+1)
	&&
	(!empty($keys[ $tmp ]))
){
$prev = <<<m_echo
	<a
		href='?file={$keys[ $tmp ]}'
		class='next'
	>
		Newer Pic
	</a>
m_echo;
}

/**
 * get date
 */
$date_uploaded = '';
if (!empty($aws_cache[ $_GET['file'] ]['timestamp_uploaded'])){
	$date_uploaded = '<p>Taken At: ' . date('Y-m-d H:i', $aws_cache[ $_GET['file'] ]['timestamp_uploaded']) . '</p>';
}

/**
 * build location URL
 */
$location = '';
if (
	(!empty($aws_cache[ $_GET['file'] ]['lat']))
	&&
	(!empty($aws_cache[ $_GET['file'] ]['long']))
){
$location =  <<<m_var
	<p>
		Location: 
		<a href='https://www.openstreetmap.org/?mlat={$aws_cache[ $_GET['file'] ]['lat']}&mlon={$aws_cache[ $_GET['file'] ]['long']}&zoom=12#map=12/{$aws_cache[ $_GET['file'] ]['lat']}/{$aws_cache[ $_GET['file'] ]['long']}' target='_BLANK'>
			{$aws_cache[ $_GET['file'] ]['lat']}/{$aws_cache[ $_GET['file'] ]['long']} (approx)
		</a>
	</p>
m_var;
}

/**
 * get notes/description
 */
$notes = '';
if (!empty($aws_cache[ $_GET['file'] ]['notest'])){
	$notes = "<div>{$aws_cache[ $_GET['file'] ]['notes']}</div>";
}


/**
 * output HTM:
 */
echo <<<m_echo

<style>
img {
	width: 95%;
	height: 95%;
	object-fit: contain;
}
.next, 
.prev {
	display:inline-block;
	width:25%;
	background-color:#2980B9;
	padding:1rem;
	border-radius:0.25rem;
	text-align:center;
	font-weight:bold;
	font-size:125%;
	color:#fff;
	text-decoration:none;
	margin-top:.25rem;
}
.prev {
	float:right;
}
</style>

<p><a href='../'>
	Back To Image Index
</a></p>

<img src='{$image_url}' />

{$next}
{$prev}

<h2>{$aws_cache[ $_GET['file'] ]['file_name']}</h2>

{$date_uploaded}

{$location}

{$notes}

m_echo;

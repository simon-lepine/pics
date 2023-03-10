<?php

include 'settings.inc.php';
require "{$compoer_dir}autoload.php";
include 'aws_cache.php';
ksort($aws_cache);

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
 * Start output
 */
echo <<<m_echo

	<style>
		img {
			width: 100%;
			height: 100%;
			object-fit: contain;
		}
	</style>

m_echo;

/**
 * ensure we have a file
 */
if (
	(empty($_GET['file']))
	||
	(empty($aws_cache[ $_GET['file'] ]))
){
	echo "<h1>Sorry, looks like I don't have anything to show you.";
	die;
}

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

echo <<<m_echo

<h2>{$aws_cache[ $_GET['file'] ]['file_name']}</h2>

<img src='{$image_url}' />

m_echo;


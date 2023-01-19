<?php

include 'settings.inc.php';
require "{$compoer_dir}autoload.php";

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
 * init starting point
 */
$start = '';
if (!empty($_GET['start'])){
	$start = $_GET['start'] . '';
}

// Use the plain API (returns ONLY up to 1000 of your objects).
try {
	$objects = $s3->listObjects([
		'Bucket' => $aws_bucket, 
		'MaxKeys' => 1, 
		'Marker' => $start, 
	]);

} catch (S3Exception $e) {
	echo 'Something went terribly wrong :(';
	die;
}

/**
 * ensure we have something
 */
if (
	(empty($objects))
	||
	(empty($objects['Contents']))
	||
	(empty($objects['Contents'][0]))
	||
	(empty($objects['Contents'][0]['Key']))
){
	echo 'You appear to have reach the end of the gallery.';
	die;
}

/**
 * rename if object is not named for unix timestamp
 * //note this helps us organize from newest to oldest
 */
$new_name = $objects['Contents'][0]['Key'];
if (
	(!is_numeric($objects['Contents'][0]['Key']))
	||
	($objects['Contents'][0]['Key'] < 1000)
	||
	($objects['Contents'][0]['Key'] > time())
){
	$s3->registerStreamWrapper();
	$new_name = $objects['Contents'][0]['LastModified']->format('U');
	rename("s3://{$aws_bucket}/{$objects['Contents'][0]['Key']}", "s3://{$aws_bucket}/{$new_name}");
}

/**
 * get a short-term URL
 */
$cmd = $s3->getCommand('GetObject', [
	'Bucket' => $aws_bucket,
	'Key' => $new_name
]);
$request = $s3->createPresignedRequest($cmd, '+10 minutes');

// Get the actual presigned-url
$image_url = $request->getUri();

/**
 * init delay time
 */
$delay = 120;
if (
	(!empty($_GET['delay']))
	&&
	(is_numeric($_GET['delay']))
	&&
	($_GET['delay'])
){
	$delay = $_GET['delay'];
}

/**
 * setup header refresh to move onto next image
 */
header("Refresh:{$delay};url=?start={$new_name}");

/**
 * output image
 */
$title = date('Y-m-d H:i', $new_name);
echo <<<m_echo

	<style>
		img {
			width: 100vw;
			height: 100vh;
			object-fit: contain;
		}
	</style>
	<h1>{$title}</h1>
	<a href='?start={$new_name}'>
		<img src='{$image_url}' />
	</a>

m_echo;

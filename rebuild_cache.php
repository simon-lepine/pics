<?php

include 'settings.inc.php';
require "{$compoer_dir}autoload.php";
include 'aws_cache.php';

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
 * interator over all objects
 */
$objects = $s3->getIterator('ListObjects', [
	'Bucket' => $aws_bucket,
]);

/**
 * create cache_cache file
 * //note we do this in case someone tries to load the cache while rebuilding the cache
 */
foreach ($objects as $object) {

/**
 * get timestamp_uploaded
 */
$timestamp_uploaded = $object['LastModified']->format('U');
$file_extension = pathinfo($object['Key'], PATHINFO_EXTENSION);
if (
	(stripos($object['Key'], 'IMGG_') !== false)
){
	$tmp = str_ireplace(
		array('IMG_', $file_extension, '.'), 
		'', 
		$object['Key']
	);
	$tmp = str_replace('_', ' ', $tmp);
	$tmp = trim($tmp);
	if (
		(strpos($tmp, ' ') !== false)
		&&
		($tmp = strtotime($tmp))
		&&
		(is_numeric($tmp))
	){
		$timestamp_uploaded = $tmp;
	}
}

/**
 * check if already exists and continue
 * ensuring only 1 entry per file in the cache (not that its a big deal since array keys will overwrite rather than duplicate)
 */
if (!empty($aws_cache[ "{$timestamp_uploaded}-{$object['Key']}" ])){
	echo "<p>{$timestamp_uploaded}-{$object['Key']} already exists.</p>";
	continue;
}


/**
 * get date info
 */
$month_uploaded = date('m', $timestamp_uploaded);
$month_uploaded = str_pad($month_uploaded, 2, '0', STR_PAD_LEFT);
$year_uploaded = date('Y', $timestamp_uploaded);

/**
 * create file content
 * and write to file
 */
$file_content = <<<m_var


\$aws_cache[ '{$timestamp_uploaded}-{$object['Key']}' ] = array(
	'file_name' => '{$object['Key']}', 
	'timestamp_uploaded' => {$timestamp_uploaded}, 
	'size' => {$object['Size']}, 
	'year_uploaded' => '{$year_uploaded}', 
	'month_uploaded' => '{$month_uploaded}', 
	'notes' => '', 
	'tags' => '',
);
m_var;
file_put_contents('aws_cache.php', $file_content, FILE_APPEND);

/**
 * done foreach
 */
}



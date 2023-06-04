<?php

/**
 * set max execution time
 */
ini_set('max_execution_time', 3600);

include 'settings.inc.php';
require "{$composer_dir}autoload.php";

//include 'aws_cache.php';
require 'get_all_cache_files.inc.php';

/**
 * get thumbnail class
 */
require 'thumbnail.inc.php';
$thumbnail = new thumbnail;

/**
 * confirm API key
 */
if (
	(empty($_GET['api_security_key']))
	||
	($api_security_key != $_GET['api_security_key'])
){
	echo 'Something went terribly wrong. :(';
	die;
	
	header('Status: 404 Not Found', false);
	header('Location: https://lakebed.io', false);
	die;
}



$tmp = '2012-12-22-211722_9544711144_o.jpg';
$tmp = str_ireplace('_', '-', $tmp);
$tmp = explode('-', $tmp);
/**
 * $tmp[0] year
 * $tmp[1] month
 * $tmp[2] day
 * tmp[3] time
 */
$tmp[3] = substr_replace($tmp[3], ':', 2, 0);
$tmp[3] = substr_replace($tmp[3], ':', 5, 0);
echo "{$tmp[0]}-{$tmp[1]}-{$tmp[2]} $tmp[3]";
die;


/**
 * use AWS composer packages
 */
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
 * interator over all objects
 */
$objects = $s3->getIterator('ListObjects', [
	'Bucket' => $aws_bucket,
]);

/**
 * get/set force
 */
$force = 0;
if (!empty($_GET['force'])){
	$force = $_GET['force'];
}

/**
 * create cache_cache file
 * //note we do this in case someone tries to load the cache while rebuilding the cache
 */
foreach ($objects as $object) {

/**
 * get timestamp_uploaded
 */
$timestamp_uploaded = $object['LastModified']->format('U');

/**
 * timestamp uploaded of camera pictures
 */
$file_extension = pathinfo($object['Key'], PATHINFO_EXTENSION);
if (
	(stripos($object['Key'], 'IMG_') !== false)
){
	$tmp = str_ireplace(
		array('IMG_', $file_extension, '.'), 
		'', 
		$object['Key']
	);
	$tmp = preg_replace("/[^0-9|_]/", '', $tmp);
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
 * timestamp uploaded of flickr images
 */
if (
	(stripos($object['Key'], '-'))
	&&
	(stripos($object['Key'], '_o.'))
){
	$tmp = str_ireplace('_', '-', $object['Key']);
	$tmp = explode('-', $tmp);
	/**
	 * $tmp[0] year
	 * $tmp[1] month
	 * $tmp[2] day
	 * tmp[3] time
	 */
	$tmp[3] = substr_replace($tmp[3], ':', 2, 0);
	$tmp[3] = substr_replace($tmp[3], ':', 5, 0);
	$timestamp_uploaded = strtotime("{$tmp[0]}-{$tmp[1]}-{$tmp[2]} $tmp[3]");
}

/**
 * check if already exists and continue
 * ensuring only 1 entry per file in the cache (not that its a big deal since array keys will overwrite rather than duplicate)
 */
if (
	(!empty($aws_cache[ "{$timestamp_uploaded}-{$object['Key']}" ]))
	&&
	(file_exists(".cache/{$timestamp_uploaded}-{$object['Key']}.php"))
	&&
	(empty($force))
){
	continue;
}

/**
 * create thumbnail
 */
$result = $thumbnail->create(array(
	'name' => $object['Key'], 
	'force' => $force, 
));

/**
 * get lat/long
 */
if (empty($result['lat'])){
	$result['lat'] = '';
}
if (empty($result['long'])){
	$result['long'] = '';
}

/**
 * get date info
 */
if (!empty($result['original_timestamp'])){
	$timestamp_uploaded = $result['original_timestamp'];
}
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
	'lat' => '{$result['lat']}',
	'long' => '{$result['long']}',
	'notes' => '', 
	'tags' => '',
);
m_var;
//file_put_contents('aws_cache.php', $file_content, FILE_APPEND);//leftoff get rid of this

/**
 * write to .cache/*.php file
 */
$file_content = <<<m_var
<?php

{$file_content}

m_var;
/**
 * //debug
 */
file_put_contents(".cache/{$timestamp_uploaded}-{$object['Key']}.php", $file_content);

/**
 * done foreach
 */
}

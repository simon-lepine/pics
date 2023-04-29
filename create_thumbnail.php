<?php

include 'settings.inc.php';
require "{$composer_dir}autoload.php";
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
 * get cache directory
 */
$cache_dir = dirname(__FILE__) . '/.cache';

/**
 * get file info
 * //note we save file_name for easy use and we want to name each thumbnail-in-progress a different name so as not to overwrite
 */
$file_name = $aws_cache[ $_GET['file'] ]['file_name'];
if (
	(!$file_extension = pathinfo($file_name))
	||
	(!is_array($file_extension))
	||
	(empty($file_extension['extension']))
	||
	(!$file_extension = strtolower($file_extension['extension']))
){
	echo '<h1>Failed to get file path info.</h1>';
	die;
}

/**
 * convert jpeg to jpg
 */
$file_extension = str_ireplace('e', '', $file_extension);
if (
	($file_extension != 'jpg')
	&&
	($file_extension != 'png')
){
	echo "{$file_extension} is an unsupported format.";
	die;
}

/**
 * ensure we have .cache dir
 */
if (
	(!is_dir($cache_dir))
){
	echo '<h1>Cache directory does not exist.</h1>';
	die;
}


/**
 * check if thumbnail already exists
 */
if (
	(empty($_GET['force']))
	&&
	(file_exists("{$cache_dir}/{$file_name}"))
){
	echo '<h1>Thumbnail alrady exists, use force if you wish to force the reload.</h1>';
	die;
}

/**
 * try/catch to get file content
 */
try {
    $file = $s3->getObject([
		'Bucket' => $aws_bucket,
		'Key' => $aws_cache[ $_GET['file'] ]['file_name'], 
		'SaveAs' => "{$cache_dir}/{$file_name}", 
    ]);
} catch (Exception $exception) {
    echo "Failed to download $file_name from $bucket_name with error: " . $exception->getMessage();
    exit("Please fix error with file downloading before continuing.");
}
  
// Maximum width and height
$width = 250;
$height = 250;
  
// Get new dimensions
list($width_orig, $height_orig) = getimagesize("{$cache_dir}/{$file_name}");
  
$ratio_orig = $width_orig/$height_orig;
  
if ($width/$height > $ratio_orig) {
    $width = $height*$ratio_orig;
} else {
    $height = $width/$ratio_orig;
}

/**
 * init and resamble image
 */
$image_p = imagecreatetruecolor($width, $height);
if ($file_extension == 'jpg'){
	$image = imagecreatefromjpeg("{$cache_dir}/{$file_name}");
}
if ($file_extension == 'png'){
	$image = imagecreatefrompng("{$cache_dir}/{$file_name}");
}
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

/**
 * write image to file
 */
if ($file_extension == 'jpg'){
	imagejpeg($image_p, "{$cache_dir}/{$file_name}", 90);
}
if ($file_extension == 'png'){
	imagepng($image_p, "{$cache_dir}/{$file_name}", 90);
}

/**
 * done
 */
echo 'Done';
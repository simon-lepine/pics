<?php

require "{$composer_dir}autoload.php";

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * class for creating image thumbnails
 */
class thumbnail{

public $aws_cache=array();
public $s3=false;
public $cache_dir=false;

function __construct(){
	
	include 'settings.inc.php';

	include 'get_all_cache_files.inc.php';
	$this->aws_cache = $aws_cache;
	
	$credentials = new Aws\Credentials\Credentials($aws_key, $aws_secret);

	// Instantiate the client.
	$this->s3 = new S3Client([
		'credentials' => $credentials, 
		'version' => 'latest',
		'region'  => $aws_region, 
	]);


/**
 * get cache directory
 */
$this->cache_dir = dirname(__FILE__) . '/.cache';
if (!is_dir($this->cache_dir)){
	echo '<h1>Cache directory does not exist.</h1>';
	die;
}

/**
 * done //function
 */
}

function create($values){

/**
 * get settings
 */
include 'settings.inc.php';

/**
 * get/sanitize file extension
 */
if (
	(!$file_extension = pathinfo($values['name']))
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
 * check if thumbnail already exists
 */
if (
	(empty($values['force']))
	&&
	(file_exists("{$this->cache_dir}/{$values['name']}"))
){
	return true;
}

/**
 * try/catch to get file content
 */
try {
    $file = $this->s3->getObject([
		'Bucket' => $aws_bucket,
		'Key' => $values['name'], 
		'SaveAs' => "{$this->cache_dir}/{$values['name']}", 
    ]);
} catch (Exception $exception) {
    echo "Failed to download {$values['name']} from $bucket_name with error: " . $exception->getMessage();
    exit("Please fix the error with file downloading before continuing.");
}

// Maximum width and height
$width = 250;
$height = 250;
  
// Get new dimensions
list($width_orig, $height_orig) = getimagesize("{$this->cache_dir}/{$values['name']}");
  
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
	$image = imagecreatefromjpeg("{$this->cache_dir}/{$values['name']}");
}
if ($file_extension == 'png'){
	$image = imagecreatefrompng("{$this->cache_dir}/{$values['name']}");
}
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

/**
 * write image to file
 */
if ($file_extension == 'jpg'){
	imagejpeg($image_p, "{$this->cache_dir}/{$values['name']}", 90);
}
if ($file_extension == 'png'){
	imagepng($image_p, "{$this->cache_dir}/{$values['name']}", 90);
}


/**
 * return success
 */
return true;


/**
 * done //function
 */
}

/**
 * done class
 */
}

//leftoff write this and impliment in rebuild_cache
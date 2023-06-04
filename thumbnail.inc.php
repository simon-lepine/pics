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

/**
 * //function to get GPS lat/long
 * source: https://stackoverflow.com/questions/2526304/php-extract-gps-exif-data
 */
function gps_lat_long($coordinate, $hemisphere) {
	if (is_string($coordinate)) {
	  $coordinate = array_map("trim", explode(",", $coordinate));
	}
	for ($i = 0; $i < 3; $i++) {
	  $part = explode('/', $coordinate[$i]);
	  if (count($part) == 1) {
		$coordinate[$i] = $part[0];
	  } else if (count($part) == 2) {
		$coordinate[$i] = floatval($part[0])/floatval($part[1]);
	  } else {
		$coordinate[$i] = 0;
	  }
	}
	list($degrees, $minutes, $seconds) = $coordinate;
	$sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
	return $sign * ($degrees + $minutes/60 + $seconds/3600);

/**
 * done //function
 */
}

/**
 * //function to get exif data
 */
function get_exit($file_name=''){

/**
 * confirm we have data
 */
if (
	(empty($file_name))
	||
	(!is_string($file_name))
	||
	(!file_exists($file_name))
){
	return false;
}

/**
 * init reutrn array
 */
$return=array(
	'success' => 1, 
);

/**
 * read exif data and build return
 * //note we must do this BEFORE thumbnail
 */
//$exif = exif_read_data("{$this->cache_dir}/{$values['name']}");
$exif = exif_read_data($file_name);
if (
	(!empty($exif))
	&&
	(!empty($exif["GPSLatitude"]))
	&&
	(!empty($exif['GPSLatitudeRef']))
){
	$return['lat'] = $this->gps_lat_long($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
}
if (
	(!empty($exif))
	&&
	(!empty($exif["GPSLongitude"]))
	&&
	(!empty($exif['GPSLongitudeRef']))
){
	$return['long'] = $this->gps_lat_long($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
}
if (
	(!empty($exif))
	&&
	(!empty($exif["DateTimeOriginal"]))
){
	$return['original_timestamp'] = strtotime($exif['DateTimeOriginal']);
}

/**
 * done //functions
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
	return false;
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
	return false;
}

/**
 * check if thumbnail already exists
 */
if (
	(empty($values['force']))
	&&
	(file_exists("{$this->cache_dir}/{$values['name']}"))
){
	return $this->get_exit("{$this->cache_dir}/{$values['name']}");
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

/**
 * get exif
 */
if (!$return = $this->get_exit("{$this->cache_dir}/{$values['name']}")){
	return false;
}

/**
 * //debug
 *
echo <<<m_echo
<p><a href='https://www.openstreetmap.org/?mlat={$latitude}&mlon={$longitude}&zoom=12#map=18/{$latitude}/{$longitude}' target='_BLANK'>
https://www.openstreetmap.org/?mlat={$latitude}&mlon={$longitude}&zoom=12#map=18/{$latitude}/{$longitude}
</a></p>
m_echo;
die;

/**
 * set max height/width for thumbnail
 */
$width = 175;
$height = 175;
  
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
$return['success']=1;
return $return;


/**
 * done //function
 */
}

/**
 * done class
 */
}

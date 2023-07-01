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
 * init timestamp_uploaded
 */
$timestamp_uploaded = $object['LastModified']->format('U');


/**
 * timestamp uploaded of camera pictures
 */
if (
	(stripos($object['Key'], 'IMG_') === 0)
	&&
	(
		(!$timestamp_uploaded)
		||
		($timestamp_uploaded < 0)
		||
		($timestamp_uploaded == $object['LastModified']->format('U'))
	)
){
	if ($tmp = $thumbnail->string_to_timestamp($object['Key'])){
		$timestamp_uploaded = $tmp;
	}
}

/**
 * create thumbnail
 */
$result = $thumbnail->create(array(
	'name' => $object['Key'], 
	//'force' => $force, 
));
if (!$result){
	continue;
}

/**
 * get date info from exif
 */
if (
	(
		(!$timestamp_uploaded)
		||
		($timestamp_uploaded < 0)
		||
		($timestamp_uploaded == $object['LastModified']->format('U'))
	)
	&&
	(!empty($result['original_timestamp']))
){
	$timestamp_uploaded = $result['original_timestamp'];
}

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
 * timestamp uploaded of flickr images
 */
if (
	(
		(stripos($object['Key'], '_o'))
		||
		(stripos($object['Key'], '_n'))
	)
	&&
	(
		(!$timestamp_uploaded)
		||
		($timestamp_uploaded < 0)
		||
		($timestamp_uploaded == $object['LastModified']->format('U'))
	)
){
	
	if ($tmp = $thumbnail->string_to_timestamp($object['Key'])){
		$timestamp_uploaded = $tmp;
	}
}

/**
 * ensure we have an uploaded timestamp
 */
$tmp = array(
'mped7llq_40886716343_o', 
'almost-there_395063178_o', 
'another-break_762912354_o', 
'another-break_762883600_o', 
'another-self-portrait_2664601426_o', 
'big_458878504_o', 
'buzz-delivery_4359474963_o', 
'business-meetings_395060304_o', 
'bztbsgyiaaabitp_15492978726_o', 
'camp-shurman_763039720_o', 
'camp-at-thumb-rock_762096495_o', 
'cloud-formations_2664591130_o', 
'cold_763003214_o', 
'concentration_395063175_o', 
'damn-the-zoom_2664587828_o', 
'curtin-ridge_762883970_o', 
'danger_763039464_o', 
'damn-weather_395078627_o', 
'dangerous-crossings_762096011_o', 
'dinner_4503939775_o', 
'designing-clothes-the-old-fashioned-way_395056351_o', 
'dscn0336_741165409_o', 
'dscn0337_741165441_o', 
'dscn0339_741165531_o', 
'dscn0338_741165519_o', 
'even-blackberry-cameras-can-be-over-exposed_2664597168_o', 
'dscn0341_741165801_o', 
'everything_2664588440_o', 
'falling-apart_458878532_o', 
'from-the-summit-looking-down_2664600282_o', 
'hanging-ice_763039440_o', 
'hard-work_419724135_o', 
'heading-home_395056347_o', 
'home-for-some_2663762655_o', 
'heading-out_395063172_o', 
'home-is-what-you-make-it_2663767823_o', 
'keep-going_395078631_o', 
'is-it-cold-or-is-it-warm_402479104_o', 
'late-season_458878502_o', 
'liberty-ridge-at-sunrise_762912312_o', 
'looking-back_763003184_o', 
'looking-back-on-it_2664598200_o', 
'looking-up-the-ridge_2664599788_o', 
'looks-ackward_395060302_o', 
'lunch_4357239161_o', 
'looks-hard_395060303_o', 
'lunch_4357986320_o', 
'mmm-spring-skiing_457869679_o', 
'mmm-lunch_458878530_o', 
'mmm-steep_763039534_o', 
'mmm-winter_402479106_o', 
'mmm-warm-rock_402479108_o', 
'moving-in_395063169_o', 
'more-of-the-route_2663777651_o', 
'north-arrete_2663769337_o', 
'northern-exposure_2664599286_o', 
'odd-angle_2663763663_o', 
'office-stress_4379248719_o', 
'on-the-edge_395056352_o', 
'our-tent_762096527_o', 
'picture-perfect_395063174_o', 
'playing-in-the-crack_395078626_o', 
'practice-makes-perfect_395078621_o', 
'reach-for-it_395060299_o', 
'realy-thin_762912400_o', 
'rest-stop_763039556_o', 
'ridge-run_763003230_o', 
'self-portrait_2663775747_o', 
'self-portrait-before-bed_2663770389_o', 
'smile_395078628_o', 
'stabbing-westward_2664589508_o', 
'still-resting_762096061_o', 
'summit_762229433_o', 
'summit-self-portrait_2663772515_o', 
'summit-shot_395056344_o', 
'summit-shot_2663771327_o', 
'summit-shot-looking-down-on-whistler_2664596224_o', 
'sunrise_763003156_o', 
'sunrise_2663776921_o', 
'sunrise_2663777493_o', 
'sunset_2663768847_o', 
'tantalizing_402479117_o', 
'team-pride_2663765263_o', 
'the-back-drop_762096539_o', 
'the-entire-mountain_2664592706_o', 
'the-board-room_395060305_o', 
'the-ice-pitch_763039518_o', 
'the-line_458878498_o', 
'the-north-face_2664593192_o', 
'the-old-sub_458878500_o', 
'theres-snow-up-there_402479112_o', 
'the-route_762884008_o', 
'the-route-at-sunrise_762912132_o', 
'thin_762912380_o', 
'tired_763003164_o', 
'to-do-list_395060307_o', 
'too-hot_395081128_o', 
'unnamed_11591515703_o', 
'up-around-the-corner_457869677_o', 
'wedge-lake_2664590582_o', 
'wedge-lake_2664602634_o', 
'west-ridge_2663771813_o', 
'what-was-i-thinking_2664592146_o', 
'wind-feature_2663774631_o', 
'wow_457869681_o', 
'yet-another-self-portrait_2663768329_o', 
'yet-another-break_762096039_o',
'dscn0340_741165541_o',
'glacier-basin_762883524_o', 
'taking-a-break_762883584_o', 
'theres-some-seracs-up-there_762883576_o', 
);
if ($object['Key'] != str_replace($tmp, '', $object['Key'])){
	$timestamp_uploaded=100;
}
if (
	(stripos("{$object['Key']}@@@@@@@@@@@", '.png@@@@@@@@@@@'))
	&&
	($timestamp_uploaded == $object['LastModified']->format('U'))
){
	$timestamp_uploaded=100;
}
if (
	(empty($timestamp_uploaded))
	||
	($timestamp_uploaded < 0)
	&&
	($timestamp_uploaded == $object['LastModified']->format('U'))
){
	echo "<p>No upload timestamp for {$object['Key']}.</p>";
	$timestamp_uploaded = $thumbnail->string_to_timestamp($object['Key']);
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
 * get additional date info
 */
$month_uploaded = date('m', $timestamp_uploaded);
$month_uploaded = str_pad($month_uploaded, 2, '0', STR_PAD_LEFT);
$year_uploaded = date('Y', $timestamp_uploaded);


/**
 * create file content
 */
$file_content = <<<m_var
<?php

\$aws_cache[ '{$timestamp_uploaded}-{$object['Key']}' ] = array(
	'file_name' => '{$object['Key']}', 
	'timestamp_uploaded' => '{$timestamp_uploaded}', 
	'size' => '{$object['Size']}', 
	'year_uploaded' => '{$year_uploaded}', 
	'month_uploaded' => '{$month_uploaded}', 
	'lat' => '{$result['lat']}',
	'long' => '{$result['long']}',
	'notes' => '', 
	'tags' => '',
	'file_hash' => '{$result['file_hash']}',
);

m_var;

/**
 * //file put contents
 */
file_put_contents(".cache/{$timestamp_uploaded}-{$object['Key']}.php", $file_content);

/**
 * done foreach
 */
}

<?php

/*
 * Check if file was called directly and error out because file should never be called directly
 */
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])){
	echo 'Something went terribly wrong. :(';
	
	header('Status: 404 Not Found', false);
	header('Location: https://lakebed.io', false);
	die;
}

/**
 * get file list
 */
if (
    (!$file_list = glob('.cache/*.php'))
    ||
    (empty($file_list))
){
    echo 'Failed to get file cache.';
    die;
}

/**
 * loop through AWS cache files
 * and include
 */
$aws_cache=array();
foreach ($file_list AS $file_name){
    include $file_name;
}

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
 * init cache
 */
$aws_cache=array();

/**
 * get file list
 */
if (
    (!$file_list = glob('.cache/*.php'))
    ||
    (empty($file_list))
){
    $file_list=array();
}

/**
 * loop through AWS cache files
 * and include
 */
if (!empty($file_list)){
foreach ($file_list AS $file_name){
    include $file_name;
}
}

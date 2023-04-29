<?php

/*
 * Check if file was called directly and error out because file should never be called directly
 */
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])){
	$_SERVER['return']['message']['error'][] = 'Something went terribly wrong :(';
	echo json_encode($_SERVER['return']);
	
	header('Status: 404 Not Found', false);
	header('Location: https://lakebed.io', false);
	die;
}

/*
 * //function used almost everywhere to check input arguments versus allowed
 */
function check_input(
	$post='', 
	$required='', 
	$optional='', 
){

/*
 * set error message title
 */
$title = '';
if (!empty($_SERVER['REQUEST_URI'])){
	$title = trim($_SERVER['REQUEST_URI'], '/');
}
$title = "LAKEBED {$title}: ";

/*
 * confirm we have data
 */
if (is_string($post)){
	$post = array( $post );
}
if (!is_array($post)){
	$post=array();
}
if (is_string($required)){
	$required = array( $required );
}
if (!is_array($required)){
	$required=array();
}
if (is_string($optional)){
	$optional = array( $optional );
}
if (!is_array($optional)){
	$optional=array();
}

/*
 * init vars
 * and set to lowercase
 */
$post = array_change_key_case($post);
$post = array_keys($post);
$post = array_combine($post, $post);
$required = array_combine($required, $required);
$required = array_change_key_case($required);
$optional = array_combine($optional, $optional);
$optional = array_change_key_case($optional);

/*
 * unset security_token and CSRF since any page could/should have it
 */
unset($post['security_token'], $required['security_token'], $post['csrf'], $required['csrf']);

/*
 * check required
 * unset required for this check
 * unset post for optional check
 */
foreach ($post AS $array_key=>$key){
	if (
		(!empty($required[ $key ]))
		&&
		($required[ $key ] = $key)
	){
		unset ($required[ $key ], $post[ $key ], $optional[ $key ]);
	}
	if (
		(!empty($optional[ $key ]))
		&&
		($optional[ $key ] = $key)
	){
		unset ($required[ $key ], $post[ $key ], $optional[ $key ]);
	}
}

/*
 * if there's any required left over then error
 */
if (count($required)){
	$required = implode(',', $required);
	error_log("{$title} Query does not contain REQUIRED arguments ({$required}).");
	return false;
}

/*
 * if there's any post left over it means it was not in required or optional then error
 */
if (count($post)){
	$post = implode(',', array_keys($post));
	error_log("{$title} Query contains EXTRA ARGUMENTS ({$post}) that are not allowed.");
	return false;
}

/*
 * return success
 */
return true;

/*
 * done //function
 */
}

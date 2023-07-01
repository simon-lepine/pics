<?php

include 'file_head.inc.php';

include 'settings.inc.php';
require "{$composer_dir}autoload.php";
require 'get_all_cache_files.inc.php';
krsort($aws_cache);


/**
 * get cache directory
 */
$cache_dir = dirname(__FILE__) . '/.cache';

/**
 * loop through cache
 */
$year_month='';
$rm_command='';
$prevent_duplicates=array();
foreach ($aws_cache AS $key=>$file){

/**
 * confirm we have a thumbnail
 */
if (!file_exists("{$cache_dir}/{$file['file_name']}")){
	continue;
}
$file_size = filesize("{$cache_dir}/{$file['file_name']}");

/**
 * check if thumbnail is big and continue if not
 */
if (
	($file_size < 50000)
	||
	($file_size < ($file['size'] * 0.8))
){
	continue;
}

/**
 * get path info
 */
$tmp = pathinfo($key);
$tmp = $tmp['filename'];
$rm_command .= "rm -rf {$file['file_name']}; rm -rf *{$tmp}*.php;";

/**
 * prevent duplicates
 */
$tmp = sha1_file("{$cache_dir}/{$file['file_name']}");
$tmp = "{$file['year_uploaded']}-{$file['month_uploaded']}-{$file['size']}-{$tmp}";
if (!empty($prevent_duplicates[ $tmp ])){
	//leftoff echo '<p>Duplicate found.</p>';
	continue;
}
$prevent_duplicates[ $tmp ] = $tmp;

echo <<<m_echo

<h3>
	{$file['file_name']}
</h3>
<ul>
	<li>
		Cache Size: {$file_size}
	</li>
	<li>
		File Size: {$file['size']}
	</li>
</ul>

m_echo;

/**
 * done foreach
 */
}

/**
 * output cleanup command
 */
echo <<<m_echo

<p>
	Remove with the following command:
</p>

<textarea>
{$rm_command}
</textarea>

m_echo;

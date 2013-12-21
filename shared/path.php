<?
$website_path = dirname (dirname (dirname (strtr (__FILE__, '\\', '/'))).'/website/index.php').'/';

$current_path = str_replace ('/website/', '/shared/', $website_path);
$textres_path = str_replace ('/website/', '/txtres/', $website_path);
?>
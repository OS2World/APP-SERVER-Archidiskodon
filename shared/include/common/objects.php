<?
include ($current_path.'include/common/libstr.inc');

include ($current_path.'include/objects/config.php');
include ($current_path.'include/objects/query.php');
include ($current_path.'include/objects/methods.php');
include ($current_path.'include/objects/translit.php');
include ($current_path.'include/objects/database.php');
include ($current_path.'include/objects/rexx.php');
include ($current_path.'include/objects/wget.php');

include ($current_path.'4wsite/beans/document.php');

$debug = 1;
$db->connect ();
unset ($debug);

include ($current_path.'include/objects/errors.php');

if ((int) PHP_VERSION >= 5) date_default_timezone_set ($config->param ('timezone'));
?>
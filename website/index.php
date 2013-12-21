<? include (dirname (dirname (strtr (__FILE__, '\\', '/'))).'/shared/path.php'); if (!$PHP_SELF) $PHP_SELF = basename (strtr (__FILE__, '\\', '/'));

include ($current_path.'include/common/objects.php');
include ($current_path.'4wsite/beans/navigator.php');

$navigator = new FTPNavigator ();
$navigator->setDocumentTitle ();
unset ($navigator);

include ($current_path.'4wsite/static/header.php');

include ($current_path.'4wsite/static/body.php');
include ($current_path.'4wsite/static/top.php');

$navigator = new FTPNavigator ();
if (isset ($action)) $navigator->performAction ();
else $navigator->showMainPage ();
unset ($navigator);

include ($current_path.'4wsite/static/bottom.php');
include ($current_path.'4wsite/static/footer.php'); 
?>
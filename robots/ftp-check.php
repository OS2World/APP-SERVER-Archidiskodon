<? include (dirname (__FILE__).'/../shared/path.php');

include ($current_path.'include/common/objects.php');
include ($current_path.'4bots/protector.php');
include ($current_path.'4bots/detector.php');

$debug = 1;

$protector = new RobotProtector ();
$protector->searchProcess ('ftp-check.php');
$protector->rememberProcess ('ftp-check.php');
unset ($protector);

$detector = new FTPDetector ();
$detector->checkKnownServers ();
unset ($detector);

$protector = new RobotProtector ();
$protector->forgetProcess ('ftp-check.php');
unset ($protector);
?>
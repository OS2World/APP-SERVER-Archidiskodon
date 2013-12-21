<? include (dirname (__FILE__).'/../shared/path.php');

include ($current_path.'include/common/objects.php');
include ($current_path.'4bots/protector.php');
include ($current_path.'4bots/reporter.php');

$debug = 1;

$protector = new RobotProtector ();
$protector->searchProcess ('report-media.php');
$protector->rememberProcess ('report-media.php');
unset ($protector);

$reporter = new FTPReporter ();
$reporter->printMediaFiles ();
unset ($reporter);

$protector = new RobotProtector ();
$protector->forgetProcess ('report-media.php');
unset ($protector);
?>
<? include (dirname (__FILE__).'/../shared/path.php');

include ($current_path.'include/common/objects.php');
include ($current_path.'4bots/protector.php');
include ($current_path.'4bots/detector.php');
include ($current_path.'4bots/preparer.php');
include ($current_path.'4bots/explorer.php');
include ($current_path.'4bots/indexer.php');
include ($current_path.'4bots/pgmaker.php');

$debug = 1;

$protector = new RobotProtector ();
$protector->searchProcess ('ftp-browse.php');
$protector->rememberProcess ('ftp-browse.php');
unset ($protector);

if ($config->param ('always_repair_db_tables')) {
    $preparer = new DBPreparer ();
    $preparer->fixTableErrors ();
    unset ($preparer);
}

$pgmaker = new PageMaker ();
$pgmaker->createNavigation ();
unset ($pgmaker);

$something_was_found = 0;

$known_hosts = array ();

$detector = new FTPDetector ();
$known_hosts = $detector->getKnownHosts ('BROWSER');
unset ($detector);

for ($srvr_cntr = 0; $srvr_cntr < count ($known_hosts); $srvr_cntr ++) {
     $hostname = $known_hosts[$srvr_cntr];

     $explorer = new FTPExplorer ();
     $indexer = new FTPIndexer ();
     $explorer->browseFTPServer ($hostname);
     unset ($explorer);
     unset ($indexer);

     $pgmaker = new PageMaker ();
     $pgmaker->createNavigation ('WRITE_TIME');
     unset ($pgmaker);

     $indexer = new FTPIndexer ();
     $indexer->rememberTotals ($hostname);
     unset ($indexer);

     $something_was_found = 1;
}

unset ($known_hosts);

if ($something_was_found) {
    include ($current_path.'4bots/cleaner.php');

    $cleaner = new DBCleaner ();
    $cleaner->deleteOldFiles ();
    $cleaner->reduceDictionary ();
    $cleaner->reduceNavigation ();
    unset ($cleaner);
}

$protector = new RobotProtector ();
$protector->forgetProcess ('ftp-browse.php');
unset ($protector);
?>
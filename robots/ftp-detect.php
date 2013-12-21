<? include (dirname (__FILE__).'/../shared/path.php');

include ($current_path.'include/common/objects.php');

include ($current_path.'4bots/protector.php');
include ($current_path.'4bots/detector.php');
include ($current_path.'4bots/indexer.php');

$debug = 1;

$protector = new RobotProtector ();
$protector->searchProcess ('ftp-detect.php');
$protector->rememberProcess ('ftp-detect.php');
unset ($protector);

for ($network_cntr = 0; $network_cntr < 255; $network_cntr ++) {
     $prefix = $config->param ('network_'.$network_cntr);
     if (!$prefix) continue;

     $lower_limit = (int) $config->param ('limit_L_'.$network_cntr);
     if (!$lower_limit) $lower_limit = 0;

     $upper_limit = (int) $config->param ('limit_U_'.$network_cntr);
     if (!$upper_limit) $upper_limit = 254;

     for ($subnet_number = $lower_limit; $subnet_number <= $upper_limit; $subnet_number ++) {
          $network = $prefix.'.'.$subnet_number;

          print $network.'.*'."\n"; ob_flush ();

          $detector = new FTPDetector ();
          $known_hosts = $detector->detectHosts ($network);
          unset ($detector);

          if (!$known_hosts) continue;
          if (!count ($known_hosts)) continue;

          $detector = new FTPDetector ();
          $known_hosts = $detector->detectFTPServers ($known_hosts);
          unset ($detector);

          if (!$known_hosts) continue;
          if (!count ($known_hosts)) continue;

          for ($host_cntr = 0; $host_cntr < count ($known_hosts); $host_cntr ++) {
               $indexer = new FTPIndexer ();
               $indexer->rememberServer ($known_hosts[$host_cntr]);
               unset ($indexer);
          }

          sleep (5);

          unset ($known_hosts);
     }
}

$protector = new RobotProtector ();
$protector->forgetProcess ('ftp-detect.php');
unset ($protector);
?>
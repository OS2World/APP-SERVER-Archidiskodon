<?
function do_nothing () {}

class FTPDetector {
  function detectHosts ($subnet) { global $methods, $config, $db, $current_path;
     $pinger = $config->param ('exec.pinger_path');
     if (!file_exists ($pinger)) exit;

     $output = array ();
     exec (strreplace ($pinger, '/', '\\').' '.$subnet, $output);

     $hosts = array ();

     for ($cntr = 0; $cntr < count ($output); $cntr ++) {
          $line = $output[$cntr];
          if (!strpos ($line, 'icmp')) continue;

          $words = explode (' ', $line);
          $ipaddr = trim ($words[count ($words) - 1]);
          if (!strpos ($ipaddr, '.')) continue;

          $hosts[] = $ipaddr;
     }

     return $hosts;
  }

  function detectFTPServers ($host_array) { global $methods, $db, $current_path;
     $servers = array ();

     for ($cntr = 0; $cntr < count ($host_array); $cntr ++) {
          $ipaddr = $host_array[$cntr];

          $success = $this->_ping ($ipaddr);
          if (!$success) continue;

          $servers[] = $ipaddr;
          print 'ftp://'.$ipaddr."\n"; ob_flush ();
     }

     return $servers;
  }

  /* * */

  function getKnownHosts ($for_browser = '') { global $methods, $db, $current_path,
                                                      $config;
     $sql = '
          SELECT distinct
                  addr    AS server_address
          FROM 
                  n_detected_servers
     ';

     if ($for_browser) {
         $sql .= '
          WHERE
                  online = "1"
          ORDER BY
                  addr
         ';
     } else {
         $sql .= '
          ORDER BY
                  reindexed_at ASC,
                  addr
         ';
     }

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     $server_limit = $config->param ('explore_n_servers_only');
     if ($server_limit) $server_limit = (int) $server_limit;
     
     if (!$server_limit) $server_limit = $num_rows;

     $host_array = array ();

     for ($cntr = 0; $cntr < $server_limit; $cntr ++) {
          $description = $db->fetch ($res);
          $host_array[] = $description->{'server_address'};
          unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     return $host_array;
  }

  function checkKnownServers () { global $methods, $db, $current_path;
     $host_array = $this->getKnownHosts ();

     for ($cntr = 0; $cntr < count ($host_array); $cntr ++) {
          $ipaddr = $host_array[$cntr];

          $success = $this->_ping ($ipaddr);
          $db->execute ('UPDATE n_detected_servers SET online = \''.addslashes ($success).'\' WHERE addr=\''.addslashes ($ipaddr).'\'');

          $hostname = gethostbyaddr ($ipaddr);
          $db->execute ('UPDATE n_detected_servers SET hostname = \''.addslashes ($hostname).'\' WHERE addr=\''.addslashes ($ipaddr).'\'');

          $servers[] = $ipaddr;
          print '-'.$success.'- '.'ftp://'.$ipaddr."\n"; ob_flush ();
     }

     $db->execute ('DELETE FROM n_detector_report');
     $db->execute ('INSERT INTO n_detector_report (finished_at) VALUES (NOW())');
  }

  /* * */

  private function _ping ($ipaddr) { global $methods, $db, $current_path;
     $success = 0;

     $timeout = 1;
     $retries = 1;

     $sql = 'SELECT id FROM n_detected_servers WHERE addr = \''.addslashes ($ipaddr).'\'';
     $res = $db->query ($sql);
     if ($db->rows ($res)) { $timeout *= 10; $retries ++; }
     unset ($res);

     set_error_handler ('do_nothing');

     for ($step = 0; $step < $retries; $step ++) {
          $errno = $errstr = '';
          $fp = @fsockopen ($ipaddr, 21, $errno, $errstr, $timeout);
          if ($fp) { 
              fclose ($fp); unset ($fp); 
              $success = 1; 

              break;
          }
     }

     restore_error_handler ();

     return $success;
  }
}
?>
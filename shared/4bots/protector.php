<?
class RobotProtector {
  function searchProcess ($robot_name) { global $methods, $db, $config, $current_path;
     $records = array ();

     $sql = '
          SELECT distinct
                  id         AS robot_id,
                  robot_pid  AS robot_pid
          FROM 
                  n_started_robots
          WHERE
                  robot_name = \''.addslashes ($robot_name).'\'
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     for ($cntr = 0; $cntr < $num_rows; $cntr ++) {
          $description = $db->fetch ($res);
          $records[] = $description->{'robot_pid'};
          unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     if (!count ($records)) return;

     for ($cntr = 0; $cntr < count ($records); $cntr ++) {
          $robot_pid = $records[$cntr];

          $checker = $config->param ('exec.checker_path');
          if (!file_exists ($checker)) exit;

          $output = array ();
          exec (strreplace ($checker, '/', '\\').' '.$robot_pid, $output);

          $robot_is_present = (int) $output[0];

          if ($robot_is_present) {
              print '[!] '.$robot_name.' is already running'; ob_flush ();
              exit;
          }
     }
  }

  function rememberProcess ($robot_name) { global $methods, $db, $current_path;
     $db->execute ('INSERT INTO n_started_robots (robot_pid, robot_name) VALUES (\''.posix_getpid ().'\', \''.addslashes ($robot_name).'\')');
  }

  function forgetProcess ($robot_name) { global $methods, $db, $current_path;
     $db->execute ('DELETE FROM n_started_robots WHERE robot_name = \''.addslashes ($robot_name).'\'');
  }

}
?>

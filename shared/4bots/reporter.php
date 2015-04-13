<?
class FTPReporter {
  function printMediaFiles () { global $methods, $db, $current_path;
     $sql = '
          SELECT distinct
                  n_file.name        AS file_name,
                  n_file.type        AS file_type,
                  n_file.location    AS file_location
          FROM 
                  n_file,
                  n_detected_servers
          WHERE
                  n_file.server_id = n_detected_servers.id
          AND
                  n_detected_servers.online = "1"
          AND
              (
                  n_file.type = \'A\'
              OR
                  n_file.type = \'M\'
              OR
                  n_file.type = \'V\'
              OR
                  n_file.type = \'F\'
              )
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     for ($name_cntr = 0; $name_cntr < $num_rows; $name_cntr ++) {
          $description = $db->fetch ($res);
          $location = $description->{'file_location'};
          unset ($description);

          print 'ftp://'.pack('H*', $location)."\n"; ob_flush ();
     }
     unset ($num_rows);
     unset ($res);

     $media_grand_total = '';

     $sql = '
          SELECT distinct
                  SUM(n_file.size)   AS media_grand_total
          FROM 
                  n_file,
                  n_detected_servers
          WHERE
                  n_file.server_id = n_detected_servers.id
          AND
                  online = "1"
          AND
              (
                  type = \'A\'
              OR
                  type = \'M\'
              OR
                  type = \'V\'
              OR
                  type = \'F\'
              )
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     if ($num_rows) {
         $description = $db->fetch ($res);
         $media_grand_total = $description->{'media_grand_total'};
         unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     print "\n".number_format ($media_grand_total, 0, '.', ' ').' B'."\n"; ob_flush ();
  }
}
?>
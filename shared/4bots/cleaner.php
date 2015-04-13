<?
class DBCleaner {
  function deleteOldFiles ($days = 30) { global $methods, $db, $current_path;
     $days = (int) $days;
     if (!$days || $days == 0) exit;

     $records = array ();

     $sql = '
          SELECT distinct
                  n_file.id  AS file_id
          FROM 
                  n_file 
          WHERE
                  DATEDIFF(datetime, NOW()) > \''.addslashes ($days).'\'
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     for ($cntr = 0; $cntr < $num_rows; $cntr ++) {
          $description = $db->fetch ($res);
          $records[] = $description->{'file_id'};
          unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     if (!count ($records)) return;

     for ($cntr = 0; $cntr < count ($records); $cntr ++) {
          $file_id = (int) $records[$cntr];
          if (!$file_id) exit;

          $db->execute ('DELETE FROM n_file                 WHERE      id=\''.addslashes ($file_id).'\'');
          $db->execute ('DELETE FROM n_file_abbrev_link     WHERE file_id=\''.addslashes ($file_id).'\'');
          $db->execute ('DELETE FROM n_file_dictionary_link WHERE file_id=\''.addslashes ($file_id).'\'');
     }
  }

  function reduceDictionary () { global $methods, $db, $current_path;
     $records = array ();

     $sql = '
          SELECT distinct
                  n_dictionary.id                        AS word_id,
                  COUNT(n_file_dictionary_link.file_id)  AS search_results
          FROM 
                  n_dictionary,
                  n_file_dictionary_link
          WHERE
                  n_dictionary.id = n_file_dictionary_link.word_id
          GROUP BY
                  n_dictionary.id
          HAVING
                  search_results = "0"
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     for ($cntr = 0; $cntr < $num_rows; $cntr ++) {
          $description = $db->fetch ($res);
          $records[] = $description->{'word_id'};
          unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     if (!count ($records)) return;

     for ($cntr = 0; $cntr < count ($records); $cntr ++) {
          $word_id = (int) $records[$cntr];
          if (!$word_id) exit;

          $db->execute ('DELETE FROM n_dictionary           WHERE      id=\''.addslashes ($word_id).'\'');
          $db->execute ('DELETE FROM n_file_dictionary_link WHERE word_id=\''.addslashes ($word_id).'\'');
     }
  }

  function reduceNavigation () { global $methods, $db, $current_path;
     $records = array ();

     $sql = '
          SELECT distinct
                  n_abbrev.id                        AS abbr_id,
                  COUNT(n_file_abbrev_link.file_id)  AS search_results
          FROM 
                  n_abbrev,
                  n_file_abbrev_link
          WHERE
                  n_abbrev.id = n_file_abbrev_link.abbrv_id
          GROUP BY
                  n_abbrev.id
          HAVING
                  search_results <= "1"
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     for ($cntr = 0; $cntr < $num_rows; $cntr ++) {
          $description = $db->fetch ($res);
          $records[] = $description->{'abbr_id'};
          unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     if (!count ($records)) return;

     for ($cntr = 0; $cntr < count ($records); $cntr ++) {
          $abbr_id = (int) $records[$cntr];
          if (!$abbr_id) exit;

          $db->execute ('DELETE FROM n_abbrev           WHERE       id=\''.addslashes ($abbr_id).'\'');
          $db->execute ('DELETE FROM n_file_abbrev_link WHERE abbrv_id=\''.addslashes ($abbr_id).'\'');
     }
  }
}
?>
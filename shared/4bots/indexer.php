<?
class FTPIndexer {
  function makeSearchIndex ($file) { global $methods, $db, $current_path;
     $this->_rememberFile ($file);
  }

  private function _rememberFile ($file) { global $methods, $translit, $db, $config, $current_path;
     $server       = $file->{'server'};
     $file_path    = $file->{'path'};
     $file_name    = $file->{'name'};
     $file_type    = $file->{'type'};
     $file_size    = $file->{'size'};
     $location     = $location = strreplace ($server.$file_path.'/'.$file_name, '//', '/');
     $explanation  = $file->{'explanation'};
     unset ($file);     

     print '['.$file_type.'] ftp://'.$location."\n"; ob_flush ();

     $ext = '';
     if ($file_type && $file_type != 'D' && strpos (' '.$file_name, '.')) {
         $ext = trim ($methods->lowerCase (substr ($file_name, strrpos ($file_name, '.'))));
         if (strlen ($ext) > 4) $ext = '';
     }

     $file_name_without_ext = $file_name;

     if ($file_type != 'D' && strrpos ($file_name, '.')) {
         $file_name_without_ext = substr ($file_name, 0, strrpos ($file_name, '.'));
     }

     $file_name = bin2hex ($file_name);
     $location = bin2hex ($location);

     if ($file_size) {
         $file_size = strreplace ($file_size, ' ', '');

         $test_string = $methods->delNumbers ($file_size);
         if ($test_string) $file_size = 0;
         unset ($test_string);

         $file_size = (int) $file_size;
     } else {
         $file_size = 0;
     }

     // -*-

     $server_id = -1;

     for ($step = 0; $step < 2; $step ++) {
          $sql = '
               SELECT distinct
                     id        AS server_id
               FROM
                     n_detected_servers
               WHERE
                     addr = \''.addslashes ($server).'\'
          ';

          $res = $db->query ($sql);
          $num_rows = $db->rows ($res);

          if ($num_rows) {
              $description = $db->fetch ($res);
              $server_id = (int) $description->{'server_id'};
              unset ($description);

              break;
          } else {
              $sql = '
                   INSERT INTO 
                         n_detected_servers (addr, hostname, online)
                   VALUES
                         (\''.addslashes ($server).'\', \''.addslashes ($server).'\', "1")
              ';

              $db->execute ($sql);
          }
     }

     if ($server_id <= 0) return;

     // -*-

     $ext_id = 0;

     if ($ext) {
         for ($step = 0; $step < 2; $step ++) {
              $sql = '
                   SELECT distinct
                         id           AS ext_id
                   FROM
                         n_extension
                   WHERE
                         ext = \''.addslashes ($ext).'\'
              ';

              $res = $db->query ($sql);
              $num_rows = $db->rows ($res);

              if ($num_rows) {
                  $description = $db->fetch ($res);
                  $ext_id = (int) $description->{'ext_id'};
                  unset ($description);

                  break;
              } else {
                  $sql = '
                       INSERT INTO 
                             n_extension (ext, type)
                       VALUES
                             (\''.addslashes ($ext).'\', \''.addslashes ($file_type).'\')
                  ';

                  $db->execute ($sql);
              }
         }
     }

     // -*-

     $file_id = -1;

     for ($step = 0; $step < 2; $step ++) {
          $sql = '
               SELECT distinct
                     id           AS file_id
               FROM
                     n_file
               WHERE
                     location = \''.addslashes ($location).'\'
          ';

          $res = $db->query ($sql);
          $num_rows = $db->rows ($res);

          if ($num_rows) {
              $description = $db->fetch ($res);
              $file_id = (int) $description->{'file_id'};
              unset ($description);

              break;
          } else {
              $sql = '
                   INSERT INTO 
                         n_file (type, ext_id, server_id, name, size, location)
                   VALUES
                         (\''.addslashes ($file_type).'\', \''.addslashes ($ext_id).'\', \''.addslashes ($server_id).'\', \''.addslashes ($file_name).'\', \''.addslashes ($file_size).'\', \''.addslashes ($location).'\')
              ';

              $db->execute ($sql);
          }
     }

     if ($explanation && $file_id > 0) {
         $db->execute ('UPDATE n_file SET explanation = \''.addslashes (bin2hex ($explanation)).'\' WHERE id = \''.addslashes ($file_id).'\'');
     }

     if ($file_id <= 0) return;

     // -*-

     $db->execute ('UPDATE n_file SET datetime = NOW() WHERE id = \''.addslashes ($file_id).'\'');

     // -*-

     for ($insert_step = 0; $insert_step < 3; $insert_step ++) {
          $dictionary_string = '';

          if ($insert_step == 0) $dictionary_string = $file_name_without_ext;
          if ($insert_step == 1) $dictionary_string = $file_path;
          if ($insert_step == 2) $dictionary_string = $explanation;

          $dictionary_string = $methods->delPunctuationMarks ($dictionary_string);
          $dictionary_string = trim ($dictionary_string);

          $original_string_length = strlen ($dictionary_string);
          for ($cleaning_step = 0; $cleaning_step < $original_string_length; $cleaning_step ++) {
               if (ord ($dictionary_string[0]) >= ord ('0') && ord ($dictionary_string[0]) <= ord ('9')) {
                   $dictionary_string = trim (substr ($dictionary_string, 1));
                   if (!strlen ($dictionary_string)) break;
               } else {
                   break;
               }
          }

          $dictionary_string = $methods->lowerCase ($dictionary_string);

          if (!$dictionary_string) continue;

          $words = array ();

          $words_array = explode (' ', $dictionary_string);
          for ($word_cntr = 0; $word_cntr < count ($words_array); $word_cntr ++) {
               $word = $words_array[$word_cntr];
               if (strlen ($methods->delNumbers ($word)) <= 1) continue;

               $words[] = $word;
          }
          unset ($words_array);

          if (!count ($words)) continue;

          if ($insert_step == 0) {
              for ($abbrv_cntr = 0; $abbrv_cntr < 2; $abbrv_cntr ++) {
                   $abbrv = '';
                   if ($abbrv_cntr == 0) $abbrv = substr ($words[0], 0, 1);
                   if ($abbrv_cntr == 1) $abbrv = substr ($words[0], 0, 2);
                   if (!$abbrv) break;

                   $abbrv = bin2hex ($abbrv);
                   $abbrv_id = -1;

                   for ($step = 0; $step < 2; $step ++) {
                        $sql = '
                              SELECT distinct
                                   id         AS abbrv_id
                              FROM
                                   n_abbrev
                              WHERE
                                   abbrv = \''.addslashes ($abbrv).'\'
                        ';

                        $res = $db->query ($sql);
                        $num_rows = $db->rows ($res);

                        if ($num_rows) {
                            $description = $db->fetch ($res);
                            $abbrv_id = (int) $description->{'abbrv_id'}; 
                            unset ($description);

                            break;
                        } else {
                            $sql = '
                                  INSERT INTO
                                       n_abbrev
                                  (
                                       abbrv,
                                       length
                                  )
                                  VALUES
                                  (

                                      \''.addslashes ($abbrv).'\',
                                      \''.addslashes (strlen ($abbrv)).'\'
                                  )';

                            $db->execute ($sql);
                        }
                   }

                   $sql = '
                         SELECT distinct
                              file_id
                         FROM
                              n_file_abbrev_link
                         WHERE
                              file_id = \''.addslashes ($file_id).'\'
                         AND
                              abbrv_id = \''.addslashes ($abbrv_id).'\'
                   ';

                   $res = $db->query ($sql);
                   $num_rows = $db->rows ($res);

                   if (!$num_rows) {
                       $sql = '
                            INSERT INTO
                                 n_file_abbrev_link
                            (
                                 file_id,
                                 abbrv_id
                            )
                            VALUES
                            (
                                 \''.addslashes ($file_id).'\',
                                 \''.addslashes ($abbrv_id).'\'
                            )';

                       $db->execute ($sql);
                   }
              }
          }

          for ($insert_cntr = 0; $insert_cntr < 2; $insert_cntr ++) {
               for ($word_cntr = 0; $word_cntr < count ($words); $word_cntr ++) {
                    $word = $words[$word_cntr];

                    if ($insert_cntr == 1) {
                        if ($config->param ('language') == 'ru') {
                            $word = $translit->createTranslit ($word);
                            $word = strreplace ($word, '\'', '');
                        } else {
                            break;
                        }
                    }

                    $word = bin2hex ($word);
                    $word_id = -1;

                    for ($step = 0; $step < 2; $step ++) {
                         $sql = '
                               SELECT distinct
                                    id         AS word_id
                               FROM
                                    n_dictionary
                               WHERE
                                    word = \''.addslashes ($word).'\'
                         ';

                         $res = $db->query ($sql);
                         $num_rows = $db->rows ($res);

                         if ($num_rows) {
                             $description = $db->fetch ($res);
                             $word_id = (int) $description->{'word_id'}; 
                             unset ($description);

                             break;
                         } else {
                             $sql = '
                                   INSERT INTO
                                        n_dictionary
                                   (
                                        word
                                   )
                                   VALUES
                                   (

                                       \''.addslashes ($word).'\'
                                   )';

                             $db->execute ($sql);
                         }
                    }

                    $sql = '
                          SELECT distinct
                               file_id
                          FROM
                               n_file_dictionary_link
                          WHERE
                               file_id = \''.addslashes ($file_id).'\'
                          AND
                               word_id = \''.addslashes ($word_id).'\'
                    ';

                    $res = $db->query ($sql);
                    $num_rows = $db->rows ($res);

                    if (!$num_rows) {
                        $sql = '
                             INSERT INTO
                                  n_file_dictionary_link
                             (
                                  file_id,
                                  word_id
                             )
                             VALUES
                             (
                                  \''.addslashes ($file_id).'\',
                                  \''.addslashes ($word_id).'\'
                             )';

                        $db->execute ($sql);
                    }
               }
          }
      }
  }

  /* * */

  function rememberServer ($ipaddr) { global $methods, $db, $current_path;
     $sql = '
          SELECT distinct
                id        AS server_id
          FROM
                n_detected_servers
          WHERE
                addr = \''.addslashes ($ipaddr).'\'
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     if (!$num_rows) {
         $sql = '
              INSERT INTO 
                    n_detected_servers (addr, hostname, online)
              VALUES
                    (\''.addslashes ($ipaddr).'\', \''.addslashes ($ipaddr).'\', "1")
         ';

         $db->execute ($sql);
     }
  }

  /* * */

  function rememberTotals ($ipaddr) { global $methods, $db, $current_path;
     $folders_total = '';

     $sql = '
          SELECT distinct
                  COUNT(n_file.id)   AS folders_total
          FROM 
                  n_file,
                  n_detected_servers
          WHERE
                  n_file.server_id = n_detected_servers.id
          AND
                  n_detected_servers.addr = \''.addslashes ($ipaddr).'\'
          AND
                  n_file.type = "D"
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     if ($num_rows) {
         $description = $db->fetch ($res);
         $folders_total = (int) $description->{'folders_total'};
         unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     $files_total = '';

     $sql = '
          SELECT distinct
                  COUNT(n_file.id)   AS files_total
          FROM 
                  n_file,
                  n_detected_servers
          WHERE
                  n_file.server_id = n_detected_servers.id
          AND
                  n_detected_servers.addr = \''.addslashes ($ipaddr).'\'
          AND
                  n_file.type <> "D"
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     if ($num_rows) {
         $description = $db->fetch ($res);
         $files_total = (int) $description->{'files_total'};
         unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     $bytes_total = '';

     $sql = '
          SELECT distinct
                  SUM(n_file.size)   AS bytes_total
          FROM 
                  n_file,
                  n_detected_servers
          WHERE
                  n_file.server_id = n_detected_servers.id
          AND
                  n_detected_servers.addr = \''.addslashes ($ipaddr).'\'
          AND
                  n_file.type <> "D"
     ';

     $res = $db->query ($sql);
     $num_rows = $db->rows ($res);

     if ($num_rows) {
         $description = $db->fetch ($res);
         $bytes_total = (int) $description->{'bytes_total'};
         unset ($description);
     }
     unset ($num_rows);
     unset ($res);

     $db->execute ('UPDATE n_detected_servers SET folders_total = \''.addslashes ($folders_total).'\' WHERE addr=\''.addslashes ($ipaddr).'\'');
     $db->execute ('UPDATE n_detected_servers SET files_total   = \''.addslashes ($files_total).'\'   WHERE addr=\''.addslashes ($ipaddr).'\'');
     $db->execute ('UPDATE n_detected_servers SET bytes_total   = \''.addslashes ($bytes_total).'\'   WHERE addr=\''.addslashes ($ipaddr).'\'');

     $db->execute ('UPDATE n_detected_servers SET reindexed_at  = NOW()                               WHERE addr=\''.addslashes ($ipaddr).'\'');
  }
}
?>
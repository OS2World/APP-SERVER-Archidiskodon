<?
class PageMaker {
  function createNavigation ($write_time = '') { global $methods, $db, $current_path,
                                                        $textres_path;

     for ($type_cntr = 0; $type_cntr < 6; $type_cntr ++) {
          $type = $dir = '';
          if ($type_cntr == 0) { $type = 'A'; $dir = 'audio'; }
          if ($type_cntr == 1) { $type = 'M'; $dir = 'midi';  }
          if ($type_cntr == 2) { $type = 'V'; $dir = 'video'; }
          if ($type_cntr == 3) { $type = 'F'; $dir = 'flash'; }
          if ($type_cntr == 4) { $type = 'D'; $dir = 'dirs';  }
          if ($type_cntr == 5) { $type = 'Z'; $dir = 'zip';   }

          $navigation = $this->_getAbbrNavigation ($type, $write_time);

          if ($navigation) {
              $file_name = $textres_path.'navigation/'.$dir.'/abbr.html';

              if (file_exists ($file_name)) unlink ($file_name);

              $file = fopen ($file_name, 'w');
              fwrite ($file, $navigation);
              fclose ($file); unset ($file);
          }
     }
  }

  private function _getAbbrNavigation ($type, $write_time) { global $methods, $config, $db, $colors, $current_path;
      if (!$type) exit;

      $sql = '
           SELECT distinct
                   n_abbrev.abbrv    AS navigation_item,
                   COUNT(n_file.id)  AS search_results
           FROM 
                   n_abbrev,
                   n_file,
                   n_file_abbrev_link
           WHERE
                   n_abbrev.id = n_file_abbrev_link.abbrv_id
           AND
                   n_file.id = n_file_abbrev_link.file_id
           AND
                   n_file.type = \''.addslashes ($type).'\'
           GROUP BY
                   n_abbrev.abbrv
           HAVING
                   search_results <> "0"
           ORDER BY
                   n_abbrev.abbrv
      ';

      $res = $db->query ($sql);
      $num_rows = $db->rows ($res);

      if (!$num_rows) return '';

      $nav_items = array ();

      for ($cntr = 0; $cntr < $num_rows; $cntr ++) {
           $description = $db->fetch ($res);
           $nav_items[] = $description->{'navigation_item'};
           unset ($description);
      }
      unset ($num_rows);
      unset ($res);

      $html_string = ' - '; $first_letter = '*';

      for ($cntr = 0; $cntr < count ($nav_items); $cntr ++) {
           $string = pack ('H*', $nav_items[$cntr]);

           if ($config->param ('language') == 'ru') {
               $cyrillic_check_ok = 0;
               if (ord ($string[0]) >= ord ('0') && ord ($string[0]) <= ord ('9')) $cyrillic_check_ok = 1;
               if (ord ($string[0]) >= ord ('a') && ord ($string[0]) <= ord ('z')) $cyrillic_check_ok = 1;
               if (ord ($string[0]) >= ord ('à') && ord ($string[0]) <= ord ('ÿ')) $cyrillic_check_ok = 1;

               if (!$cyrillic_check_ok) continue;
           }

           if ($string[0] != $first_letter) {
               if ($cntr != 0) $html_string .= '<br>'."\n";
               $first_letter = $string[0];
           }

           $tstr = trim ($html_string); if ($tstr && $tstr[strlen ($tstr) - 1] != '-') $html_string .= ' - ';

           $html_string .= '<a href="index.php?search='.$string.'&type='.$type.'&action=N">';
           $html_string .= $methods->mixedCase ($string);
           $html_string .= '</a>';

           $tstr = trim ($html_string); if ($tstr && $tstr[strlen ($tstr) - 1] != '-') $html_string .= ' - ';
      }

      if ($write_time) {
          $html_string .= "\n";
          $html_string .= '<!-- '.date ('d.n.Y').' -->';
      }

      $html_string .= "\n";
      $html_string .= '<br><br>';

      return $html_string;
  }
}
?>
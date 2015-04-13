<? 
include_once ($current_path.'4wsite/beans/abstract.php');

class FTPNavigator extends AbstractBean {
    function FTPNavigator () {
        $this->{'limits'}->{'search_words'} = 5;
        $this->{'limits'}->{'lines_on_page'} = 100;

        parent::AbstractBean ();
    }

    function getDocumentTitle () { global $methods, $query, $current_path,
                                          $search;
        if (isset ($search)) return $search;

        return '';
    }

    /* * */

    function showMainPage () { global $methods, $query, $db, $colors, $current_path,
                                      $type;
        $this->_printPageBegin ('SHOW_FORM', '', $type);
        $this->_printAbbrNavigation ($type);
        $this->_printStatLink ();
        $this->_printPageEnd ();
    }

    /* * */

    function performAction () { global $methods, $query, $db, $colors, $current_path,
                                       $action, $search, $type;
        if (!isset ($action) ||
            !isset ($search) ||
            !isset ($type)) { $methods->goBack (); exit; }

        $action_is_correct = 0;
        if ($action == 'S') $action_is_correct = 1;
        if ($action == 'N') $action_is_correct = 1;
        if (!$action_is_correct) { $methods->goBack (); exit; }

        $search_string = $search;
        $search_string = $methods->delPunctuationMarks ($search_string);
        $search_string = $methods->lowerCase ($search_string);

        $required_types = array ();
        $required_types[] = $type;

        $this->_printPageBegin ('SHOW_FORM', $search_string, $required_types[0]);
        if ($action == 'S') $this->_printSearchResults ($search_string, $required_types[0]);
        if ($action == 'N') $this->_printNavigationResults ($search_string, $required_types[0]);
        $this->_printPageEnd ();
    }

    /* * */

    private function _printSearchResults ($search, $required_type) { global $methods, $query, $db, $colors, $current_path;

        $search_string = $search; 
        $words = explode (' ', $search_string);
        unset ($search_string);

        if (!count ($words)) return;
        if (count ($words) > $this->{'limits'}->{'search_words'}) return;

        $result_items_array = array ();

        for ($cntr = 0; $cntr < count ($words); $cntr ++) {
             $result_items = $this->_fetchAllExistingRecords ('dictionary', 'word', $words[$cntr], $required_type);
             if (count ($result_items)) $result_items_array[] = $result_items;
             else return;
             unset ($result_items);
        }

        $this->_printResultsFor ($this->_intersectRecordsAndApplyPageNavigation ($result_items_array), $required_type);
    }

    private function _printNavigationResults ($search, $required_type) { global $methods, $query, $db, $colors, $current_path;

        $result_items_array = array ();
        $result_items_array[] = $this->_fetchAllExistingRecords ('abbrev', 'abbrv', $search, $required_type);

        $this->_printResultsFor ($this->_intersectRecordsAndApplyPageNavigation ($result_items_array), $required_type);
    }

    private function _printResultsFor ($item_list, $required_type) { global $methods, $query, $db, $colors, $current_path;

        $result_items = $item_list->{'result_items'};
        $prev_anchor  = $item_list->{'prev_anchor'};
        $next_anchor  = $item_list->{'next_anchor'};
        $all_anchors  = $item_list->{'all_anchors'};
        unset ($item_list);

        $html_string = '';

        for ($item_cntr = 0; $item_cntr < count ($result_items); $item_cntr ++) {
             $duplicates = 0;

             for ($enumerator = $item_cntr + 1; $enumerator < count ($result_items); $enumerator ++) {
                  if ($result_items[$enumerator]->{'file_name'} == $result_items[$item_cntr]->{'file_name'}) {
                      $duplicates ++;
                  } else {
                      break;
                  }
             }

             $name_string = $this->_decodeString ($result_items[$item_cntr]->{'file_name'});

             $html_string .= '<li>';

             if (!$duplicates) {
                 $location = $this->_decodeLocation ($result_items[$item_cntr]->{'file_location'});
                 $explanation = $this->_decodeString ($result_items[$item_cntr]->{'file_explanation'});
                 $size_string = $this->_decodeSize ($result_items[$item_cntr]->{'file_size'}, $required_type);

                 $html_string .= $this->_constructUniqueResultItem ($location, $explanation, $size_string);
             } else {
                 $html_string .= $this->_constructMultipleResultItem ($name_string, $result_items, $item_cntr, $duplicates, $required_type);

                 $item_cntr += $duplicates;
             }

             $html_string .= '</li>';
             $html_string .= '<br>';
        }

        print '<div align=left><ul type=none>';
        print $html_string;
        print '</ul></div>';

        include ($current_path.'4wsite/beans/static/navigation/pages.php');

        print '<br><br>';
    }

    private function _constructMultipleResultItem ($name_string, $result_items, $item_cntr, $duplicates, $required_type) { global $methods, $query, $db, $colors, $current_path;
        $html_string = '';

        $duplicates_brick_name = $result_items[$item_cntr]->{'file_id'};

        $html_string .= '<table width=100% border=0 cellspacing=0 cellpadding=0><tr>';
        $html_string .= '<td align=right valign=top>';

        $html_string .= '&raquo;&nbsp;';

        $html_string .= '</td>';
        $html_string .= '<td width=100% align=left valign=middle>';

        $html_string .= $this->_addEmphasis ($name_string);

        $html_string .= '<ul type=none id="'.$duplicates_brick_name.'">';

        for ($offset = 0; $offset <= $duplicates; $offset ++) {
             $html_string .= '<li>';
             $html_string .= '<br>';

             $location = $this->_decodeLocation ($result_items[$item_cntr + $offset]->{'file_location'});
             $explanation = $this->_decodeString ($result_items[$item_cntr + $offset]->{'file_explanation'});
             $size_string = $this->_decodeSize ($result_items[$item_cntr + $offset]->{'file_size'}, $required_type);

             $html_string .= $this->_constructUniqueResultItem ($location, $explanation, $size_string);

             $html_string .= '</li>';
        }

        $html_string .= '</ul>';

        $html_string .= '</td>';
        $html_string .= '</tr></table>';

        return $html_string;
    }

    private function _constructUniqueResultItem ($location, $explanation, $size_string, $split_line = 0) { global $methods, $query, $db, $config, $colors, $current_path;
        $html_string = '';

        $html_string .= '<table width=100% border=0 cellspacing=1 cellpadding=1><tr>';
        $html_string .= '<td align=right valign=top>';

        $html_string .= '&raquo;&nbsp;';

        $html_string .= '</td>';
        $html_string .= '<td width=100% align=left valign=middle>';

        $shift_value = 0;

        $chain = split ('/', strreplace ($location, 'ftp://', ''));
        $chain_length = count ($chain);

        for ($chain_cntr = 0; $chain_cntr < $chain_length; $chain_cntr ++) {
             if ($chain_cntr > 0) {
                 if ($config->param ('split_names')) {
                     $html_string .= '<br>'; $shift_value += 2;
                     for ($rsh_cntr = 0; $rsh_cntr < $shift_value; $rsh_cntr ++) $html_string .= '&nbsp;';
                 } else {
                     $html_string .= '<font color="'.$colors->wbrkColor ().'">&nbsp;/ </font>';
                 }
             }

             $link_string = 'ftp:/'; $path_delimeter = chr (0x2F);
             for ($link_cntr = 0; $link_cntr <= $chain_cntr; $link_cntr ++) {
                  $link_string .= $path_delimeter.$chain[$link_cntr];
             }
             if ($chain_cntr != $chain_length - 1) $link_string .= $path_delimeter;

             $html_string .= '<a href="'.$link_string.'">';
             $html_string .= $this->_addEmphasis ($chain[$chain_cntr]);
             $html_string .= '</a>';

             unset ($link_string);
        }
        unset ($chain);
        unset ($chain_cntr);

        if ($explanation) {
            $html_string .= '<br>';
            for ($rsh_cntr = 0; $rsh_cntr < $shift_value; $rsh_cntr ++) $html_string .= '&nbsp;';

            $html_string .= '<font color="'.$colors->wbrkColor ().'"> - </font>';
            $html_string .= $explanation;
        }

        if ($size_string) {
            $html_string .= '<br>';
            for ($rsh_cntr = 0; $rsh_cntr < $shift_value; $rsh_cntr ++) $html_string .= '&nbsp;';

            $size_string = strreplace ($size_string, ' ', '&nbsp;');

            $html_string .= '<font color="'.$colors->wbrkColor ().'"> - </font>';
            $html_string .= $size_string;
        }

        $html_string .= '</td>';
        $html_string .= '</tr></table>';

        return $html_string;
    }

    private function _addEmphasis ($string) { global $methods, $query, $db, $colors, $current_path,
                                             $search;

        $string = $this->_removeURLSymbols ($string);

        for ($cntr = 0; $cntr < 4; $cntr ++) {
             if ($cntr == 0) $search_string = $search;
             if ($cntr == 1) $search_string = $methods->lowerCase ($search);
             if ($cntr == 2) $search_string = $methods->upperCase ($search);
             if ($cntr == 3) $search_string = $methods->mixedCase ($search);

             if (strpos (' '.$string, $search_string)) {
                 $string = strreplace ($string,
                                       $search_string, 
                                       '<*f_o_n_t* *c_o_l_o_r*="'.$colors->markColor ().'">'.$search_string.'</*f_o_n_t*>');
             }
        }        

        $string = strreplace ($string, '*f_o_n_t*', 'font');
        $string = strreplace ($string, '*c_o_l_o_r*', 'color');

        return $string;
    }

    private function _decodeString ($string) { global $methods, $query, $db, $colors, $current_path;
        return pack('H*', $string);
    }

    private function _decodeLocation ($location) { global $methods, $query, $db, $colors, $current_path;
        $location = $this->_decodeString ($location);
        $location = $this->_addURLSymbols ($location);
        $location = 'ftp://'.$location;

        return $location;
    }

    private function _addURLSymbols ($location) { global $methods, $query, $db, $colors, $current_path;
        $location = strreplace ($location, '#', '%23');

        return $location;
    }

    private function _removeURLSymbols ($location) { global $methods, $query, $db, $colors, $current_path;
        $location = strreplace ($location, '%23', '#');

        return $location;
    }

    private function _decodeSize ($size, $required_type) { global $methods, $query, $db, $colors, $current_path;
        $size_string = ''; 

        if ($required_type != 'D') { 
            $size_string = number_format ($size, 0, '.', ' ');
        }

        return $size_string;
    }

    private function _fetchAllExistingRecords ($ndx_table_name, $ndx_field_name, $search, $required_type) { global $methods, $query, $db, $colors, $current_path;
        $add_online_condition = 0;

        $sql = 'SELECT DATEDIFF(finished_at, NOW()) AS date_difference FROM n_detector_report WHERE finished_at <> "0" AND finished_at IS NOT NULL';

        $res = $db->query ($sql);
        $num_rows = $db->rows ($res);

        if ($num_rows) {
            $description = $db->fetch ($res);
            $date_difference = $description->{'date_difference'};
            unset ($description);

            if ($date_difference == 0 || $date_difference == '0') $add_online_condition = 1;
        }
        unset ($num_rows);
        unset ($res);

        $search = bin2hex ($search);

        $sql = '
             SELECT distinct
                     n_file.id        AS file_id
             FROM 
                     n_file,
                     n_detected_servers,
                     n_'.$ndx_table_name.',
                     n_file_'.$ndx_table_name.'_link
             WHERE
                     n_file.server_id = n_detected_servers.id 
             AND
                     n_'.$ndx_table_name.'.id = n_file_'.$ndx_table_name.'_link.'.$ndx_field_name.'_id
             AND
                     n_file.id = n_file_'.$ndx_table_name.'_link.file_id
             AND
                     n_file.type = \''.addslashes ($required_type).'\'
             AND
                     n_'.$ndx_table_name.'.'.$ndx_field_name.' LIKE BINARY \''.addslashes ($search).'%\'
        ';

        if ($add_online_condition) {
            $sql .= '
             AND
                     n_detected_servers.online = "1"
            ';
        }

        $res = $db->query ($sql);
        $num_rows = $db->rows ($res);

        $result_items = array ();

        for ($record_cntr = 0; $record_cntr < $num_rows; $record_cntr ++) {
             $description = $db->fetch ($res);
             $result_items[] = $description->{'file_id'};
             unset ($description);
        }
        unset ($num_rows);
        unset ($res);

        return $result_items;
    }

    private function _intersectRecordsAndApplyPageNavigation ($result_items_array) { global $methods, $query, $db, $colors, $current_path;

        $sql = '
             SELECT distinct
                     n_file.id           AS file_id,
                     n_file.name         AS file_name,
                     n_file.type         AS file_type,
                     n_file.size         AS file_size,
                     n_file.location     AS file_location,
                     n_file.explanation  AS file_explanation
             FROM
                     n_file
             WHERE
        ';

        for ($cntr = 0; $cntr < count ($result_items_array); $cntr ++) {
             if ($cntr != 0) {
                 $sql .= '
             AND
                 ';

             }

             $sql .= '
                     n_file.id IN ('.join (',', $result_items_array[$cntr]).')
             ';
        }

        $sql .= '
             ORDER BY
                     n_file.name,
                     n_file.datetime DESC
        ';

        $res = $db->query ($sql);
        $num_rows = $db->rows ($res);

        if ($num_rows) {
            $pages = new SQLPages ($num_rows, $this->{'limits'}->{'lines_on_page'});
            $prev_anchor = $pages->prevAnchor ('index.php');
            $next_anchor = $pages->nextAnchor ('index.php');
            $all_anchors = $pages->pageAnchors ('index.php');
            $sql .= $pages->getSQLLimits ();
            unset ($pages);

            $res = $db->query ($sql);
            $num_rows = $db->rows ($res);

            if ($num_rows) {
                $result_items = array ();

                for ($cntr = 0; $cntr < $num_rows; $cntr ++) {
                     $description = $db->fetch ($res);
                     $result_items[] = $description;
                     unset ($description);
                }
                unset ($num_rows);
                unset ($res);

                $item_list->{'result_items'} = $result_items;
                $item_list->{'prev_anchor'} = $prev_anchor;
                $item_list->{'next_anchor'} = $next_anchor;
                $item_list->{'all_anchors'} = $all_anchors;

                return $item_list;
            }
        }

        return '';
    }

    /* * */

    private function _printAbbrNavigation ($required_type) { global $methods, $query, $db, $colors, $current_path,
                                                   $textres_path;
        if (!$required_type) exit;

        $dir = '';
        if ($required_type == 'A') $dir = 'audio'; 
        if ($required_type == 'M') $dir = 'midi';  
        if ($required_type == 'V') $dir = 'video'; 
        if ($required_type == 'F') $dir = 'flash'; 
        if ($required_type == 'D') $dir = 'dirs';  
        if ($required_type == 'Z') $dir = 'zip';   

        if ($dir) {
            $file_name = $textres_path.'navigation/'.$dir.'/abbr.html';
            if (file_exists ($file_name)) {
                $file = fopen ($file_name, 'r');
                print fread ($file, filesize ($file_name));
                fclose ($file); unset ($file);
            }
        }
    }

    /* * */

    private function _extractURL ($location, $requirement) { global $methods, $query, $db, $colors, $current_path;
        if (!$location) return '';
        if (!$requirement) return '';
        if (substr ($location, 0, 6) != 'ftp://') return '';

        $path = strreplace ($location, 'ftp://', ''); $root = '';
        $words = split ('/', $path);
        array_splice ($words, count ($words) - 1); 
        $path = join ('/', $words); $root = $words[0];
        unset ($words);

        $location = '';
        if ($requirement == 'F') $location = 'ftp://'.$path.'/';
        if ($requirement == 'S') $location = 'ftp://'.$root.'/';

        return '<a href="'.$location.'">'.$location.'</a>';
    }

    /* * */

    function showServers () { global $methods, $query, $db, $colors, $current_path;

        $this->_printPageBegin ();

        $detected_servers = array ();

        $sql = '
             SELECT distinct
                     addr           AS address,
                     hostname       AS hostname,
                     online         AS online,
                     folders_total  AS folders_total,
                     files_total    AS files_total,
                     bytes_total    AS bytes_total
             FROM 
                     n_detected_servers
             WHERE
                     ( LENGTH(hostname) > "0" AND hostname IS NOT NULL )
             AND
                     files_total > "0"
             ORDER BY
                     online DESC,
                     hostname
        ';

        $res = $db->query ($sql);
        $num_rows = $db->rows ($res);

        for ($record_cntr = 0; $record_cntr < $num_rows; $record_cntr ++) {
             $description = $db->fetch ($res);
             $detected_servers[] = $description;
             unset ($description);
        }
        unset ($num_rows);
        unset ($res);

        print '<br>'; print '<table>';

        for ($cntr = 0; $cntr < count ($detected_servers); $cntr ++) {
             $addr = $detected_servers[$cntr]->{'address'};
             $name = $detected_servers[$cntr]->{'hostname'};
             $online = $detected_servers[$cntr]->{'online'};
             $folders_total = $detected_servers[$cntr]->{'folders_total'};
             $files_total = $detected_servers[$cntr]->{'files_total'};
             $bytes_total = $detected_servers[$cntr]->{'bytes_total'};

             $hostnumber = substr ($addr, strrpos ($addr, '.'));
             if ($hostnumber == '.1') continue;

             print '<tr><td align=right>';

             if ($online) print '<a target=_blank href="ftp://'.$addr.'">';
             if ($name) print $name; else print $addr;
             if ($online) print '</a>';

             print '</td><td align=right>';
             if ($files_total) print number_format ($files_total, 0, '.', ' ').' <font color="'.$colors->markColor ().'">F</font>';
             print '</td><td align=right>';
             if ($folders_total) print number_format ($folders_total, 0, '.', ' ').' <font color="'.$colors->markColor ().'">D</font>';
             print '</td><td align=right>';
             if ($bytes_total) print number_format ($bytes_total, 0, '.', ' ').' <font color="'.$colors->markColor ().'">B</font>';

             print '</td></tr>';
        }

        $servers_total = count ($detected_servers);

        $folders_grand_total = '';

        $sql = '
             SELECT distinct
                     SUM(folders_total)   AS folders_grand_total
             FROM 
                     n_detected_servers
        ';

        $res = $db->query ($sql);
        $num_rows = $db->rows ($res);

        if ($num_rows) {
            $description = $db->fetch ($res);
            $folders_grand_total = $description->{'folders_grand_total'};
            unset ($description);
        }
        unset ($num_rows);
        unset ($res);

        $files_grand_total = '';

        $sql = '
             SELECT distinct
                     SUM(files_total)   AS files_grand_total
             FROM 
                     n_detected_servers
        ';

        $res = $db->query ($sql);
        $num_rows = $db->rows ($res);

        if ($num_rows) {
            $description = $db->fetch ($res);
            $files_grand_total = $description->{'files_grand_total'};
            unset ($description);
        }
        unset ($num_rows);
        unset ($res);

        $bytes_grand_total = '';

        $sql = '
             SELECT distinct
                     SUM(bytes_total)   AS bytes_grand_total
             FROM 
                     n_detected_servers
        ';

        $res = $db->query ($sql);
        $num_rows = $db->rows ($res);

        if ($num_rows) {
            $description = $db->fetch ($res);
            $bytes_grand_total = $description->{'bytes_grand_total'};
            unset ($description);
        }
        unset ($num_rows);
        unset ($res);

        print '<tr><td colspan=4 align=center><hr size=1 color="'.$colors->lbrkColor ().'"></td></tr>';

        print '<tr><td align=right>';
        if ($servers_total) print number_format ($servers_total, 0, '.', ' ');
        print '</td><td align=right>';
        if ($files_grand_total) print number_format ($files_grand_total, 0, '.', ' ').' <font color="'.$colors->markColor ().'">F</font>';
        print '</td><td align=right>';
        if ($folders_grand_total) print number_format ($folders_grand_total, 0, '.', ' ').' <font color="'.$colors->markColor ().'">D</font>';
        print '</td><td align=right>';
        if ($bytes_grand_total) print number_format ($bytes_grand_total, 0, '.', ' ').' <font color="'.$colors->markColor ().'">B</font>';
        print '</td></tr>';

        print '</table>';
        print '<br><br>';

        $this->_printPageEnd ();
    }

    /* * */

    private function _printPageBegin ($show_form = '', $search_string = '', $required_type = '') { global $methods, $query, $db, $colors, $current_path;

        if (!isset ($required_type)) {
            $query->setParameter ('type', 'A');
            $required_type = 'A';
        }

        include ($current_path.'4wsite/beans/navigator/main_begin.php');

        $next_action = 'S';
        include ($current_path.'4wsite/beans/navigator/form_header.php');
        include ($current_path.'4wsite/beans/navigator/form.php');
        include ($current_path.'4wsite/beans/navigator/form_footer.php');
    }

    private function _printStatLink () { global $methods, $query, $db, $colors, $current_path;
        include ($current_path.'4wsite/beans/navigator/stat_link.php');
    }

    private function _printPageEnd () { global $methods, $query, $db, $colors, $current_path;
        include ($current_path.'4wsite/beans/navigator/main_end.php');
    }
}
?>
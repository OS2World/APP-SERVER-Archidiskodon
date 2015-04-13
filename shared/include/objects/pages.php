<?
/**
 *  Листание страниц и построение запросов SQL для выбора записей.
 */

class AbstractPages {
   function AbstractPages ($num_rows, $rows_on_page, $sql_server) { global $query;
        $this->{'rows_on_page'} = (int) $rows_on_page;
        $this->{'min_rows_on_page'} = round ($rows_on_page / 4) + 1;

        $this->{'num_rows'} = (int) $num_rows;
        $this->{'max_page'} = $this->_calculateMaxPageNumber (); 

        $this->{'sql_server'} = $sql_server;
        $this->{'query_object'} = $query;

        $this->{'remainder_placement'} = 'RIGHT';
        $this->{'default_page_is_set'} = 0;

        $this->{'sd_class'} = '';

        $this->{'page'} = -1;
        $this->_rememberPageNumber ();
   }

   private function _calculateMaxPageNumber () {
        $max_page = round ($this->{'num_rows'} / $this->{'rows_on_page'}) - 1;
        if ($max_page < 0) $max_page = 0;

        return $max_page;
   }

   function setStyle ($listing_style, $sd_class = '') {
        if ($listing_style == 'LR') $this->{'remainder_placement'} = 'RIGHT';
        if ($listing_style == 'RL') $this->{'remainder_placement'} = 'LEFT';

        $this->{'sd_class'} = $sd_class;

        if ($this->{'default_page_is_set'}) {
            $this->{'page'} = -1; $this->_rememberPageNumber ();
        }
   }

   private function _rememberPageNumber () { global $page;
        $page_is_known = 1;

        if (!isset ($page) || $page === '' || 
            $page < 0 || $page > $this->{'max_page'} ||
            strval ((int) $page) != strval ($page)) $page_is_known = 0;

        if (!$page_is_known) {
            if ($this->{'remainder_placement'} == 'RIGHT') $this->{'page'} = 0;
            if ($this->{'remainder_placement'} == 'LEFT') $this->{'page'} = $this->{'max_page'};

            $this->{'default_page_is_set'} = 1;
        } else {
            $this->{'page'} = (int) $page;
        }
   }

   function prevAnchor ($script_name = '') { global $PHP_SELF;
        if (!$script_name) $script_name = $PHP_SELF;

        $anchor_string = '';

        $max_page = $this->{'max_page'};

        $place_anchor = 0;
        if ($this->{'remainder_placement'} == 'RIGHT') { if ($this->{'page'} > 0 && $this->{'page'} <= $max_page) $place_anchor = 1; }
        if ($this->{'remainder_placement'} == 'LEFT') { if ($this->{'page'} >= 0 && $this->{'page'} < $max_page) $place_anchor = 1; }

        if ($place_anchor) {
            if ($this->{'remainder_placement'} == 'RIGHT') $previous_page = $this->{'page'} - 1;
            if ($this->{'remainder_placement'} == 'LEFT') $previous_page = $this->{'page'} + 1;

            $previous_link = 'page='.($previous_page);
            $anchor_string .= '<a href="'.$script_name.$this->{'query_object'}->stringFor ('page').$previous_link.'">';
            $anchor_string .= '<b>&lt;&lt;</b>';
            $anchor_string .= '</a>';
        }

        return $anchor_string;
   }

   function nextAnchor ($script_name = '') { global $PHP_SELF;
        if (!$script_name) $script_name = $PHP_SELF;

        $anchor_string = '';

        $max_page = $this->{'max_page'};

        $place_anchor = 0;
        if ($this->{'remainder_placement'} == 'RIGHT') { if ($this->{'page'} >= 0 && $this->{'page'} < $max_page) $place_anchor = 1; }
        if ($this->{'remainder_placement'} == 'LEFT') { if ($this->{'page'} > 0 && $this->{'page'} <= $max_page) $place_anchor = 1; }

        if ($place_anchor) {
            if ($this->{'remainder_placement'} == 'RIGHT') $next_page = $this->{'page'} + 1;
            if ($this->{'remainder_placement'} == 'LEFT') $next_page = $this->{'page'} - 1;

            $next_link = 'page='.($next_page);
            $anchor_string .= '<a href="'.$script_name.$this->{'query_object'}->stringFor ('page').$next_link.'">';
            $anchor_string .= '<b>&gt;&gt;</b>';
            $anchor_string .= '</a>';
        }

        return $anchor_string;
   }

   function pageAnchors ($script_name = '', $space = '&nbsp;&nbsp;&nbsp;') { global $PHP_SELF;
        if (!$script_name) $script_name = $PHP_SELF;

        $anchor_string = "\n";

        $max_page = $this->{'max_page'};

        if ($this->{'page'} >= 0 && $this->{'page'} <= $max_page) {
            for ($cntr = 0; $cntr <= $max_page; $cntr ++) {

                 if ($this->{'remainder_placement'} == 'RIGHT') {
                     $html_page_number = $cntr + 1;
                     $link_page_number = $cntr;
                 }

                 if ($this->{'remainder_placement'} == 'LEFT') {
                     $html_page_number = $cntr + 1;
                     $link_page_number = $max_page - $cntr;
                 }

                 $limits = $this->_limits ($link_page_number);
                 $limit_1 = $limits->{'start'} + 1;
                 $limit_2 = $limits->{'start'} + $limits->{'rows'};

                 if (trim ($anchor_string)) $anchor_string .= $space;

                 if ($link_page_number != $this->{'page'}) {
                     $anchor_string .= '<a href="'.$script_name.$this->{'query_object'}->stringFor ('page').'page='.$link_page_number.'" title="'.$limit_1.' - '.$limit_2.'">';
                     $anchor_string .= $html_page_number;
                     $anchor_string .= '</a>'."\n";
                 } else {
                     $anchor_string .= '<b>';
                     if ($this->{'sd_class'}) $anchor_string .= '<span class="'.$this->{'sd_class'}.'">';
                     $anchor_string .= $html_page_number;
                     if ($this->{'sd_class'}) $anchor_string .= '</span>';
                     $anchor_string .= '</b>'."\n";
                 }
            }
        }

        return $anchor_string;
   }

   protected function _limits ($current_page) {
        $limits = '';

        $max_page = $this->{'max_page'};

        if ($current_page >= 0 && $current_page <= $max_page) {
            $remainder = $this->{'num_rows'} - $this->{'rows_on_page'} * $max_page;
            if ($remainder < $this->{'min_rows_on_page'}) $remainder = $this->{'min_rows_on_page'};

            if ($this->{'remainder_placement'} == 'RIGHT') {
               if ($current_page == $max_page) {
                   $rows = $remainder;
                   $start = $this->{'num_rows'} - $remainder;
               }

               if ($current_page != $max_page) {
                   $rows = $this->{'rows_on_page'};
                   $start = $rows * $current_page;
               }
            }

            if ($this->{'remainder_placement'} == 'LEFT') {
               if ($current_page == $max_page) {
                   $rows = $remainder;
                   $start = 0;
               }

               if ($current_page != $max_page) {
                   $rows = $this->{'rows_on_page'};
                   $start = $remainder + $rows * ($max_page - $current_page - 1);
               }
            }

            if ($start < 0) $start = 0;
            if ($start + $rows > $this->{'num_rows'}) $rows = $this->{'num_rows'} - $start;

            $limits->{'start'} = $start;
            $limits->{'rows'} = $rows;
        }

        return $limits;
   }
}

class SQLPages extends AbstractPages {
   function SQLPages ($num_rows, $rows_on_page = 20) { global $config;
        $sql_server = 'MYSQL';
        if (isset ($config) && $config->param ('dbms')) $sql_server = strtoupper ($config->param ('dbms'));

        parent::AbstractPages ($num_rows, $rows_on_page, $sql_server);
   }

   function getSQLLimits () {
        $limits = $this->_limits ($this->{'page'});
        if (!$limits) return ' LIMIT 0';

        $start = $limits->{'start'};
        $rows = $limits->{'rows'};

        $sql_server = $this->{'sql_server'};
        if ($sql_server == 'PGSQL') return ' LIMIT '.$rows.' OFFSET '.$start;

        return ' LIMIT '.addslashes ($start).', '.addslashes ($rows);
   }
}

class TreePages extends AbstractPages {
   function TreePages ($num_rows, $rows_on_page = 20) {
        parent::AbstractPages ($num_rows, $rows_on_page, '');
   }

   function reduceArray ($nodes) {
        $limits = $this->_limits ($this->{'page'});
        if (!$limits) return $nodes;

        $start = $limits->{'start'};
        $rows = $limits->{'rows'};

        return array_slice ($nodes, $start, $rows);
   }
}
?>
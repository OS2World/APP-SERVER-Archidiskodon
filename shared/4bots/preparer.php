<?
class DBPreparer {
  function fixTableErrors () { global $methods, $db, $current_path;
     $table_names = array ();

     $res = $db->query ('SHOW TABLES');
     $num_rows = $db->rows ($res);

     for ($cntr = 0; $cntr < $num_rows; $cntr ++) {
          $description = $db->fetch ($res);
          list ($name, $value) = each ($description);
          unset ($description);

          $table_names[] = $value;
     }
     unset ($num_rows);
     unset ($res);

     for ($cntr = 0; $cntr < count ($table_names); $cntr ++) {
          $db->execute ('REPAIR TABLE '.$table_names[$cntr]);
     }
  }
}
?>
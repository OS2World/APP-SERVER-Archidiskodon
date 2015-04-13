<?
/**
 *  Методы для работы с базами данных и запросами SQL.
 *
 *  Защита от внедрения кода: параметры должны быть заключены в кавычки и 
 *  пропущены через функцию "addslashes ()". Подробнее - см. "SQL Injection"
 *  в этих ваших интернетах.
 *
 *  2011-09-12: Добавлена поддержка именованных массивов (не только списков).
 *
 */

class DBConnector {
   function DBConnector () { global $cfg;
         if (!function_exists ('mysql_errno')) $this->_printStackTrace ('/SQL!API/');

         $this->{'enable_debug'} = 1;

         $this->{'fetch_mode'} = 'J';
         if (isset ($cfg['db']) && is_array ($cfg['db'])) $this->{'fetch_mode'} = 'A';

         $this->{'total_requests'} = 0;
         $this->{'connected'} = 0;
   }

   function connect ($db_user = '', $db_pass = '', $db_name = '', $mysql_host = '', $mysql_port = '') { global $config;
         if (isset ($config) && $config) {
             if (!$mysql_host) $mysql_host = $config->param ('mysql_host');
             if (!$mysql_port) $mysql_port = $config->param ('mysql_port');

             if (!$db_user) $db_user = $config->param ('db_user');
             if (!$db_pass) $db_pass = $config->param ('db_pass');
             if (!$db_name) $db_name = $config->param ('db_name');
         }

         $link = '';
         if ($mysql_host && $mysql_host != '127.0.0.1' && $mysql_host != 'localhost') {
             if ($mysql_port) {
                 try {
                     $link = mysql_connect ($mysql_host.':'.$mysql_port, $db_user, $db_pass);
                 } catch (Exception $xc) {
                     $this->_handleError ($xc);
                 }
             } else {
                 try {
                     $link = mysql_connect ($mysql_host, $db_user, $db_pass);
                 } catch (Exception $xc) {
                     $this->_handleError ($xc);
                 }
             }
         } else {
             try {
                 $link = mysql_connect ('', $db_user, $db_pass); 
             } catch (Exception $xc) {
                 $this->_handleError ($xc);
             }
         }

         if ($link && $db_name) {
             try {
                 mysql_select_db ($db_name, $link); 
             } catch (Exception $xc) {
                 $this->_handleError ($xc); 
             }
         }

         $this->{'connected'} = 1;
   }

   function set1251 () { 
         try {
             mysql_query ('SET NAMES CP1251');
         } catch (Exception $xc) {
             $this->_handleError ($xc);
         }
   }

   function setUTF8 () { 
         try {
             mysql_query ('SET NAMES UTF8');
         } catch (Exception $xc) {
             $this->_handleError ($xc);
         }
   }

   function disconnect () {
         try {
             mysql_close ();
         } catch (Exception $xc) {
             $this->_handleError ($xc);
         }

         $this->{'total_requests'} = 0;
         $this->{'connected'} = 0;
   }

   /* * */

   function query ($sql) { global $debug;
         if (!$this->{'connected'}) $this->_printStackTrace ('/SQL!DB/');
         if (!$this->_isShowOrSelect ($sql)) $this->_printStackTrace ('/SQL!S/');

         $this->_detectConditionWithoutQuotes ($sql);

         if (isset ($debug) && $debug && $this->{'enable_debug'}) { 
             $t = $this->_getmicrotime (); 
             $this->{'total_requests'} ++; 
         }

         try {
             $res = mysql_query ($sql); 
         } catch (Exception $xc) {
             $this->_handleError ($xc, $sql);
         }

         if (isset ($debug) && $debug && $this->{'enable_debug'}) { 
             $t -= $this->_getmicrotime (); 
             $this->_printDebugMessage ($sql, $t); 
         }

         return $res;
   }

   function execute ($sql) { global $debug;
         if (!$this->{'connected'}) $this->_printStackTrace ('/SQL!DB/');
         if ($this->_isShowOrSelect ($sql)) $this->_printStackTrace ('/SQL!E/');

         $this->_detectConditionWithoutQuotes ($sql);

         if (isset ($debug) && $debug && $this->{'enable_debug'}) { 
             $t = $this->_getmicrotime (); 
             $this->{'total_requests'} ++; 
         }

         try {
             mysql_query ($sql); 
         } catch (Exception $xc) {
             $this->_handleError ($xc, $sql);
         }

         if (isset ($debug) && $debug && $this->{'enable_debug'}) { 
             $t -= $this->_getmicrotime (); 
             $this->_printDebugMessage ($sql, $t); 
         }

         if ($this->_isInsert ($sql)) {
             try {
                 return mysql_insert_id ();
             } catch (Exception $xc) {
                 $this->_handleError ($xc, $sql);
             }
         } else {
             try {
                 return mysql_affected_rows ();
             } catch (Exception $xc) {
                 $this->_handleError ($xc, $sql);
             }
         }
   }

   function rows ($res) {
         if (!$res) return '';

         try {
             $rows = mysql_num_rows ($res);
         } catch (Exception $xc) {
             $this->_handleError ($xc);
         }

         if (!$rows) $rows = 0;

         return $rows;
   }

   function fetch ($res) {
         if (!$res) return '';

         if ($this->{'fetch_mode'} == 'A') {
             try {
                 $rc = mysql_fetch_assoc ($res); 
             } catch (Exception $xc) {
                 $this->_handleError ($xc);
             }
         }
         if ($this->{'fetch_mode'} == 'J') {
             try {
                 $rc = mysql_fetch_object ($res); 
             } catch (Exception $xc) {
                 $this->_handleError ($xc);
             }
         }

         if (!$rc) $rc = '';

         return $rc;
   }

   function getRecords ($res) { 
         $records = array ();
         if (!$res) return $records;

         $max_rows = $this->rows ($res);

         for ($cntr = 0; $cntr < $max_rows; $cntr ++) {
              $records[] = $this->fetch ($res);
         }

         return $records;
   }

   private function _detectConditionWithoutQuotes ($sql) {
         $sql_uc = strtoupper ($sql);

         if (strpos ($sql_uc, 'WHERE')) {
             $sql_w = str_replace ('  ', ' ', 
                      str_replace ("\r", ' ', 
                      str_replace ("\n", ' ', 
                      str_replace (' ', '', substr ($sql_uc, strpos ($sql_uc, 'WHERE'))))));

             for ($number = 0; $number <= 9; $number ++) {
                  if (strpos ($sql_w, '>'.$number) || strpos ($sql_w, '<'.$number) || strpos ($sql_w, '='.$number)) {
                      $this->_printStackTrace ('/SQL!WHERE/', $sql);
                  }
             }
         }
   }

   private function _isShowOrSelect ($sql) {
         return $this->_typeOf ($sql, 'S');
   }

   private function _isInsert ($sql) {
         return $this->_typeOf ($sql, 'I');
   }

   private function _isUpdate ($sql) {
         return $this->_typeOf ($sql, 'U');
   }

   private function _isDelete ($sql) {
         return $this->_typeOf ($sql, 'D');
   }

   private function _typeOf ($sql, $character) {
         if ($sql && $character) {
             $sql = strtoupper (trim ($sql));
             if ($sql && $sql[0] == $character) return 1;
         } 

         return 0;
   }

   /* * */

   private function _handleError ($xc, $sql = '') {
         try {
             if (mysql_errno ()) {
                 $message = mysql_error ();
                 $this->_printStackTrace ($message, $sql);
             }
         } catch (Exception $xc) {
                 $this->_printStackTrace ('/INT!H/', $sql);
         }
   }

   private function _printStackTrace ($message, $sql) { 
         $err_string = '';
         if ($sql && $this->{'enable_debug'}) $this->_printDebugMessage ($sql, -1, 'STDERR');
         else $err_string .= "\n";

         ini_set ('display_errors', 1);

         $err_string .= '<html><body><pre><font color=maroon>'; error_log ($err_string);
         throw new Exception ("\n\n".$message."\n\n");
   }

   private function _printDebugMessage ($sql, $time, $stderr = '') {
         $message = '';
         if ($this->{'total_requests'}) $message = 'Query #'.$this->{'total_requests'};

         if ($message) {
             if ($time != -1) $message .= ', executed in '.round (abs ($time), 4).' seconds ';
             else $message .= ', an error occured ';
         }

         $output  = "\n";
         $output .= '<html><body><pre><font color=navy>'."\n";
         $output .= $message;
         $output .= '--'."\n";
         $output .= $sql."\n";
         $output .= '--'."\n";

         if ($stderr) error_log ($output);
         else print $output;
   }

   private function _getmicrotime () {
         list ($msec, $sec) = explode (' ', microtime ());
         return ((float) $msec + (float) $sec);
   }
}

$db = new DBConnector ();
?>
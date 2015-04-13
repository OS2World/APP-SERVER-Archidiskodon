<?
/**
 *  Обработчик ошибок, вызывающий "throw new Exception()". 
 *  Печатает цепочку вызовов (встроенный обработчик этого делать не умеет).
 */

function php_error_handler ($errno, $errstr, $errfile, $errline, $vars) {
   if (strpos (' '.$errstr, 'mkdir')) return;
   if (strpos (' '.$errstr, 'rmdir')) return;
   if (strpos (' '.$errstr, 'unlink')) return;
   if (strpos (' '.$errstr, 'ob_flush')) return;

   if (strpos (' '.$errstr, 'Trying to get property of non-object')) return;
   if (strpos (' '.$errstr, 'Creating default object from empty value')) return;

   ini_set ('display_errors', 1);

   $err_string = "\n".'<html><body><font color=maroon><pre>'; error_log ($err_string);
   throw new Exception ("\n\n".'['.$errno.'] '.$errstr.' ('.strtr ($errfile, '\\', '/').', '.$errline.')'."\n\n");
}

set_error_handler ('php_error_handler');
error_reporting (E_ALL); ini_set ('display_errors', 0);
?>
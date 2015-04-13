<?
/**
 *  Ìועמהû הכÿ נאבמעû ס Translit-סענמךאלט.
 */

class Translit {
   function createTranslit ($string) { global $methods;
          if (!isset ($methods)) return '';

          for ($case_cntr = 0; $case_cntr < 2; $case_cntr ++) {
               $cyrillic = array ();
               $translit = array ();

               if ($case_cntr == 0) $cyrillic = explode (' ', $methods->delDoubleSpaces ('א ב ג ד ה ו ¸  ז  ח ט י ך כ ל ם מ ן נ ס ע ף פ ץ  צ  ק  ר  ש   ÿ  ‏  ‎  û ü  ת'));
               if ($case_cntr == 0) $translit = explode (' ', $methods->delDoubleSpaces ('a b v g d e yo zh z i y k l m n o p r s t u f kh tz ch sh sch ya yu ae y '."'".' "'));

               if ($case_cntr == 1) $cyrillic = explode (' ', $methods->delDoubleSpaces ('À Á Â Ã Ä Å ¨  Æ  Ç È É Ê Ë Ì Í Î Ï Ð Ñ Ò Ó Ô Õ  Ö  ×  Ø  Ù   ‗  Þ  Ý  Û Ü  Ú'));
               if ($case_cntr == 1) $translit = explode (' ', $methods->delDoubleSpaces ('A B V G D E Yo Zh Z I Y K L M N O P R S T U F Kh Tz Ch Sh Sch Ya Yu Ae Y '."'".' "'));

               if (count ($cyrillic) != count ($translit)) exit;

               for ($character = 0; $character < count ($cyrillic); $character ++) {
                    $string = strreplace ($string, $cyrillic[$character], $translit[$character]);
               }
          }

          $string = strreplace ($string, 'YY', 'Y');
          $string = strreplace ($string, 'yy', 'y');

          $string = strreplace ($string, chr (132), '"');
          $string = strreplace ($string, chr (147), '"');
          $string = strreplace ($string, chr (148), '"');
          $string = strreplace ($string, chr (149), '*');
          $string = strreplace ($string, chr (150), '-');
          $string = strreplace ($string, chr (151), '-');
          $string = strreplace ($string, chr (171), '"');
          $string = strreplace ($string, chr (187), '"');

          $new_string = '';

          for ($cntr = 0; $cntr < strlen ($string); $cntr ++) {
               $char = $string[$cntr];

               if (ord ($char) >= ord (' ') &&
                   ord ($char) <= ord ('~')) $new_string .= $char;
          }

          return $new_string;
   }

   function restoreCyrillic ($string) { global $methods;
          if (!isset ($methods)) return $string;

          $string_lower_case = $methods->lowerCase ($string);
          unset ($string);

          for ($token_length = 4; $token_length >= 1; $token_length --) {
               for ($charset = 1; $charset <= 4; $charset ++) {
                    switch ($charset) {
                            case 1:
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('א ב ג ד ה ו ¸  ¸  ¸  ז  ז  ח ט ך כ ל ם מ ן נ ס ע ף ף  פ פ  ץ  צ  צ  ק  ר  ש   ש    ÿ  ÿ  ÿ  ‏  ‏  ‏  ‎  ‎  ü  ת'));
                                   $translit = explode (' ', $methods->delDoubleSpaces ('a b v g d e yo io jo zh zg z i k l m n o p r s t u oo f ff kh tz tc ch sh sch shch ya ia ja yu iu ju ae ea '."'".' "'));
                            break;

                            case 2:
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('אי אי אי וי וי וי ¸י  ¸י  ¸י  טי טי טי מי מי מי ףי ףי ףי ÿי  ÿי  ÿי  ÿי  ÿי  ÿי  ‏י  ‏י  ‏י  ‏י  ‏י  ‏י  ‎י  ‎י  ‎י  ‎י  ‎י  ‎י  ûי ûי ûי'));
                                   $translit = explode (' ', $methods->delDoubleSpaces ('ay ai aj ey ei ej yoy ioy joy iy ii ij oy oi oj uy ui uj yay yai yaj iay iai iaj yuy yui yuj iuy iui iuj aey aei aej eay eai eaj yy yi yj'));
                            break;

                            case 3:
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('תו ת¸'));
                                   $translit = explode (' ', $methods->delDoubleSpaces ('je je'));
                            break;

                            case 4:
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('ך ץ ז ך ג ךס û י'));
                                   $translit = explode (' ', $methods->delDoubleSpaces ('c h j q w x  y y'));
                            break;
                    }

                    if (count ($cyrillic) != count ($translit)) throw new Exception ('/CT/');

                    for ($character = 0; $character < count ($cyrillic); $character ++) {
                         if (strlen ($translit[$character]) == $token_length) {
                             $string_lower_case = strreplace ($string_lower_case, $translit[$character], $cyrillic[$character]);
                         }
                    }
               }
          }

          return $string_lower_case;
   }
}

$translit = new Translit ();
?>
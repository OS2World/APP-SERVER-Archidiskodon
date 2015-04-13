<?
/**
 *  ������ ��� ������ � Translit-��������.
 */

class Translit {
   function createTranslit ($string) { global $methods;
          if (!isset ($methods)) return '';

          for ($case_cntr = 0; $case_cntr < 2; $case_cntr ++) {
               $cyrillic = array ();
               $translit = array ();

               if ($case_cntr == 0) $cyrillic = explode (' ', $methods->delDoubleSpaces ('� � � � � � �  �  � � � � � � � � � � � � � � �  �  �  �  �   �  �  �  � �  �'));
               if ($case_cntr == 0) $translit = explode (' ', $methods->delDoubleSpaces ('a b v g d e yo zh z i y k l m n o p r s t u f kh tz ch sh sch ya yu ae y '."'".' "'));

               if ($case_cntr == 1) $cyrillic = explode (' ', $methods->delDoubleSpaces ('� � � � � � �  �  � � � � � � � � � � � � � � �  �  �  �  �   �  �  �  � �  �'));
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
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('� � � � � � �  �  �  �  �  � � � � � � � � � � � � �  � �  �  �  �  �  �  �   �    �  �  �  �  �  �  �  �  �  �'));
                                   $translit = explode (' ', $methods->delDoubleSpaces ('a b v g d e yo io jo zh zg z i k l m n o p r s t u oo f ff kh tz tc ch sh sch shch ya ia ja yu iu ju ae ea '."'".' "'));
                            break;

                            case 2:
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('�� �� �� �� �� �� ��  ��  ��  �� �� �� �� �� �� �� �� �� ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  ��  �� �� ��'));
                                   $translit = explode (' ', $methods->delDoubleSpaces ('ay ai aj ey ei ej yoy ioy joy iy ii ij oy oi oj uy ui uj yay yai yaj iay iai iaj yuy yui yuj iuy iui iuj aey aei aej eay eai eaj yy yi yj'));
                            break;

                            case 3:
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('�� ��'));
                                   $translit = explode (' ', $methods->delDoubleSpaces ('je je'));
                            break;

                            case 4:
                                   $cyrillic = explode (' ', $methods->delDoubleSpaces ('� � � � � �� � �'));
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
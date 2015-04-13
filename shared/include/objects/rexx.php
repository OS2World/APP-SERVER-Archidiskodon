<?
/**
 *  Полезные методы, взятые из Rexx (OS/2).
 */

class Rexx {
   function parse ($string, $token_1, $token_2, $token_3 = '', $token_4 = '') {
        if (!strpos ('-+-'.$string, $token_1)) return '';

        if (!$token_3) return $this->_parse_1 ($string, $token_1, $token_2);
        if (!$token_4) return $this->_parse_2 ($string, $token_1, $token_2, $token_3);

        return $this->_parse_3 ($string, $token_1, $token_2, $token_3, $token_4);
   }

   private function _parse_1 ($string, $token_1, $token_2) {
        $position_1 = strpos ($string, $token_1);                  $length_1 = strlen ($token_1);
        $position_2 = strpos ($string, $token_2, $position_1 + 1);

        if ($position_1 === false) return '';
        if ($position_2 === false) return '';

        return substr ($string, $position_1 + $length_1, $position_2 - $position_1 - $length_1);
   }

   private function _parse_2 ($string, $token_1, $token_2, $token_3) {
        $position_1 = strpos ($string, $token_1);                  $length_1 = strlen ($token_1);
        $position_2 = strpos ($string, $token_2, $position_1 + 1); $length_2 = strlen ($token_2);
        $position_3 = strpos ($string, $token_3, $position_2 + 1);

        if ($position_1 === false) return '';
        if ($position_2 === false) return '';
        if ($position_3 === false) return '';

        $values[] = substr ($string, $position_1 + $length_1, $position_2 - $position_1 - $length_1);
        $values[] = substr ($string, $position_2 + $length_2, $position_3 - $position_2 - $length_2);

        return $values;
   }

   private function _parse_3 ($string, $token_1, $token_2, $token_3, $token_4) {
        $position_1 = strpos ($string, $token_1);                  $length_1 = strlen ($token_1);
        $position_2 = strpos ($string, $token_2, $position_1 + 1); $length_2 = strlen ($token_2);
        $position_3 = strpos ($string, $token_3, $position_2 + 1); $length_3 = strlen ($token_3);
        $position_4 = strpos ($string, $token_4, $position_3 + 1);

        if ($position_1 === false) return '';
        if ($position_2 === false) return '';
        if ($position_3 === false) return '';
        if ($position_4 === false) return '';

        $values[] = substr ($string, $position_1 + $length_1, $position_2 - $position_1 - $length_1);
        $values[] = substr ($string, $position_2 + $length_2, $position_3 - $position_2 - $length_2);
        $values[] = substr ($string, $position_3 + $length_3, $position_4 - $position_3 - $length_3);

        return $values;
   }

   function split ($string, $token_1, $token_2) {
        $position_1 = strpos ($string, $token_1);                  $length_1 = strlen ($token_1);
        $position_2 = strpos ($string, $token_2, $position_1 + 1);

        if ($position_1 === false) return '';
        if ($position_2 === false) return '';

        $before = substr ($string, 0, $position_1 + $length_1);
        $text = substr ($string, $position_1 + $length_1, $position_2 - $position_1 - $length_1);
        $after = substr ($string, $position_2, strlen ($string));

        $values[] = $before;
        $values[] = $text;
        $values[] = $after;

        return $values;
   }
}

$rexx = new Rexx ();
?>
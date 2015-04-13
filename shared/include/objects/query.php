<?
/**
 *  Переменная $query содержит строку запроса и может выдать ее для указанного параметра.
 *
 *  Например, если строка запроса задана как "a=b&c=d", то код вида:
 *          <a href="index.php<?= $query->stringFor ('key'); ?>key=123"></a>
 *  напечатает следующую строку:
 *          <a href="index.php?a=b&c=d&key=123"></a>
 *
 *  Точно так же можно выдать набор полей для формы: "<input type=hidden name=... value=...>".
 *
 *  И можно выдать строку запроса или список полей для формы только с указанными параметрами.
 *
 *  При использовании $query обычные "$QUERY_STRING" и "$HTTP_..._VARS" сбрасываются в "array ()".
 *
 *  Внутренняя переменная "parameters_to_delete" задает параметры, которые не надо включать
 *  в строку запроса.
 *
 *  2008.01.12: Добавлена поддержка PHP версии 5.x 
 *  2008.05.05: Добавлена поддержка переменных, зашифрованных через "bin2hex ()".
 *  2011.08.20: Обновлена защита от внедрения кода через "eval ()".
 *
 */

class HTTPWwwQuery {
    function HTTPWwwQuery () { global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $_REQUEST, $_SERVER,
                                      $HTTP_POST_FILES, $QUERY_STRING,
                                      $SERVER_NAME, $PHP_SELF, $ARGC, $ARGV,
                                      $REMOTE_ADDR, $HTTP_USER_AGENT, $HTTP_REFERER,
                                      $PHP_AUTH_USER, $PHP_AUTH_PW,
                                      $config, $current_path;

        $this->{'data'}->{'parameters_to_delete'} = array ();
        $this->{'data'}->{'parameters_to_delete'}[] = 'hotlog';
        $this->{'data'}->{'parameters_to_delete'}[] = 'spylog';
        $this->{'data'}->{'parameters_to_delete'}[] = 'PHPSESID';
        $this->{'data'}->{'parameters_to_delete'}[] = 'mbfcookie';

        $max_var_lists = 4;
        if (isset ($HTTP_COOKIE_VARS['mbfcookie'])) $max_var_lists = 2;

        for ($cntr = 0; $cntr < $max_var_lists; $cntr ++) {
             switch ($cntr) {
                     case 0: $variable_list = $HTTP_GET_VARS;    break;
                     case 1: $variable_list = $HTTP_POST_VARS;   break;
                     case 2: $variable_list = $HTTP_COOKIE_VARS; break;
                     case 3: $variable_list = $_REQUEST;         break;
             }

             if (isset ($variable_list) && count ($variable_list)) {
                 reset ($variable_list);
                 while (list ($name, $value) = each ($variable_list)) {
                        $this->setParameter ($name, $value);
                 }
             }
        }

        if (isset ($HTTP_POST_FILES)) {
            reset ($HTTP_POST_FILES);
            while (list ($name, $value) = each ($HTTP_POST_FILES)) {
                   $this->setParameter ($name.'_name', $HTTP_POST_FILES[$name]['name']);
                   $this->setParameter ($name.'_type', $HTTP_POST_FILES[$name]['type']);
                   $this->setParameter ($name.'_size', $HTTP_POST_FILES[$name]['size']);

                   $this->setParameter ($name, $HTTP_POST_FILES[$name]['tmp_name']);
            }
        }

        if (isset ($QUERY_STRING)) {
            $parameters = preg_split ("/&/", $QUERY_STRING);

            foreach ($parameters as $parameter) {
                 if ($parameter) {
                     $tokens = preg_split ("/=/", $parameter);

                     if ($tokens[0]) $this->setParameter ($tokens[0], $tokens[1]);
                 }
            }
        }

        if (isset ($_SERVER)) {
            $ARGC = ''; if (isset ($_SERVER['argc'])) $ARGC = $_SERVER['argc'];
            $ARGV = ''; if (isset ($_SERVER['argv'])) $ARGV = $_SERVER['argv'];

            if (!isset ($SERVER_NAME) && isset ($_SERVER['SERVER_NAME'])) $SERVER_NAME = $_SERVER['SERVER_NAME'];
            if (!isset ($PHP_SELF)    && isset ($_SERVER['PHP_SELF']))    $PHP_SELF    = $_SERVER['PHP_SELF'];

            if (isset ($_SERVER['REMOTE_ADDR']))     $REMOTE_ADDR     = $_SERVER['REMOTE_ADDR'];
            if (isset ($_SERVER['HTTP_USER_AGENT'])) $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
            if (isset ($_SERVER['HTTP_REFERER']))    $HTTP_REFERER    = $_SERVER['HTTP_REFERER'];

            if (isset ($_SERVER['PHP_AUTH_USER']))   $PHP_AUTH_USER   = $_SERVER['PHP_AUTH_USER'];
            if (isset ($_SERVER['PHP_AUTH_PW']))     $PHP_AUTH_PW     = $_SERVER['PHP_AUTH_PW'];
        }

        if (isset ($SERVER_NAME)) {
            if ($SERVER_NAME == 'localhost') $SERVER_NAME = '127.0.0.1';

            if ($SERVER_NAME != '127.0.0.1') {
                if (isset ($config) && $config->param ('server_name')) $SERVER_NAME = $config->param ('server_name');            
            }
        } else {
            $SERVER_NAME = '127.0.0.1';
        }

        if (isset ($PHP_SELF)) {
            $PHP_SELF = strreplace ($PHP_SELF, '\\', '/');
            $PHP_SELF = strreplace ($PHP_SELF, '\\\\', '/');

            if (strrchr ($PHP_SELF, '/')) $PHP_SELF = substr (strrchr ($PHP_SELF, '/'), 1);
            if (!strstr ($PHP_SELF, '.php')) $PHP_SELF = '';
        }

       $this->{'sd_path'} = './';
       if (isset ($current_path) && is_dir ($current_path)) $this->{'sd_path'} = $current_path;

       $this->{'sd_path'} .= 'include/settings/';
    }

    function string () {
        return $this->stringFor ('*');
    }

    function stringFor ($names) {
        if (!isset($this->{'data'}->{'query_names'})) return '?';

        $settings_file = $this->{'sd_path'}.'temporary_query_names.php';
        if (file_exists ($settings_file)) include ($settings_file);
        if (isset ($document->{'temporary_query_names'})) $names .= ' | '.$document->{'temporary_query_names'};

        $new_string = $this->_combineFor ($this->_splitNames ($names));

        return $this->_setMarks ($new_string);
    }

    function stringWith ($names) {
        if (!isset($this->{'data'}->{'query_names'})) return '';

        $new_string = $this->_combineWithOnly ($this->_splitNames ($names));

        if ($new_string) return '?'.$new_string;

        return '';
    }

    private function _setMarks ($string) {
        if ($string) {
            if (substr ($string, -1) != '&') $string .= '&';
            return '?'.$string;
        }

        return '?';
    }

    function stringWithPermanentNames () {
        $settings_file = $this->{'sd_path'}.'permanent_query_names.php';
        if (file_exists ($settings_file)) include ($settings_file);

        if (isset ($document->{'permanent_query_names'})) return $this->stringWith ($document->{'permanent_query_names'});

        return '?';
    }

    function fields () {
        if (!isset($this->{'data'}->{'query_names'})) return '';

        return $this->_makeFieldsFor ('*');
    }

    function fieldsFor ($names) {
        if (!isset($this->{'data'}->{'query_names'})) return '';

        $settings_file = $this->{'sd_path'}.'temporary_query_names.php';
        if (file_exists ($settings_file)) include ($settings_file);
        if (isset ($document->{'temporary_query_names'})) $names .= ' | '.$document->{'temporary_query_names'};

        return $this->_makeFieldsFor ($this->_splitNames ($names));
    }

    function fieldsWith ($names) {
        if (!isset($this->{'data'}->{'query_names'})) return '';

        return $this->_makeFieldsWithOnly ($this->_splitNames ($names));
    }

    function fieldsWithPermanentNames () {
        $settings_file = $this->{'sd_path'}.'permanent_query_names.php';
        if (file_exists ($settings_file)) include ($settings_file);

        if (isset ($document->{'permanent_query_names'})) return $this->fieldsWith ($document->{'permanent_query_names'});

        return '?';
    }

    private function _setInternalParameter ($x_name_x, $x_value_x) {
        if ($x_name_x[0] == '$') $x_name_x = substr ($x_name_x, 1);

        $x_var_name_x = $x_name_x;
        if (strpos (' '.$x_name_x, '->')) {
            $x_var_name_x = substr ($x_name_x, 0, strpos ($x_name_x, '->'));
        }

        try {
           eval ('global $'.$x_var_name_x.';');
           eval ('$'.$x_name_x.'=\''.$this->_addVSlashes ($x_value_x).'\';');
        } catch (Exception $xc) {
           $this->_printErrMsg ();
           exit;
        }
    }

    private function _addVSlashes ($string) {
        $string = addslashes ($string);
        $string = strreplace ($string, '\"', '"');

        return $string;
    }

    private function _removeInternalParameter ($name) {
        $this->_setInternalParameter ($name, '');
    }

    private function _splitNames ($names) {
        $tokens = array ();

        $splitters = ' ,;|&';
        $token = strtok ($names, $splitters);
        while ($token) {
               $token = trim ($token);

               if ($token) $tokens[] = $token;
               $token = strtok ($splitters);
        }

        return $tokens;
    }

    private function _combineFor ($tokens) {
        if (!$tokens || !count ($tokens)) $tokens = array ();

        $string = ''; $length = count ($this->{'data'}->{'query_names'});
        $tokens_quantity = count ($tokens);

        for ($cntr = 0; $cntr < $length; $cntr ++) {
            if (!$this->_parameterIsSet ($cntr)) continue;
            if ($this->_presentInTokens ($this->{'data'}->{'query_names'}[$cntr], $tokens, $tokens_quantity)) continue;

            $string .= $this->{'data'}->{'query_names'}[$cntr].'='.urlencode ($this->{'data'}->{'query_values'}[$cntr]);

            if (substr ($string, -1) != '&') $string .= '&';
        }

        $string = $this->_decodeCommonSymbols ($string);

        return $string;
    }

    private function _combineWithOnly ($tokens) {
        if (!$tokens || !count ($tokens)) $tokens = array ();

        $string = ''; $length = count ($this->{'data'}->{'query_names'});
        $tokens_quantity = count ($tokens);

        for ($cntr = 0; $cntr < $length; $cntr ++) {
            if (!$this->_parameterIsSet ($cntr)) continue;

            if ($this->_presentInTokens ($this->{'data'}->{'query_names'}[$cntr], $tokens, $tokens_quantity)) {
                if ($string) $string .= '&';
                $string .= $this->{'data'}->{'query_names'}[$cntr].'='.urlencode ($this->{'data'}->{'query_values'}[$cntr]);
            }
        }

        $string = $this->_decodeCommonSymbols ($string);

        return $string;
    }

    private function _decodeCommonSymbols ($string) {
        $string = strreplace ($string, '%21', '!');
        $string = strreplace ($string, '%27', "'");
        $string = strreplace ($string, '%2E', '.');
        $string = strreplace ($string, '%3A', ':');

        return $string;
    }

    private function _makeFieldsFor ($tokens) {
        if (!$tokens || !count ($tokens)) $tokens = array ();

        $string = ''; $length = count ($this->{'data'}->{'query_names'});
        $tokens_quantity = count ($tokens);

        for ($cntr = 0; $cntr < $length; $cntr ++) {
            if (!$this->_parameterIsSet ($cntr)) continue;
            if ($this->_presentInTokens ($this->{'data'}->{'query_names'}[$cntr], $tokens, $tokens_quantity)) continue;

            $string .= '<input type=hidden name="'.$this->{'data'}->{'query_names'}[$cntr].'" value="'.$this->{'data'}->{'query_values'}[$cntr].'">';
            $string .= "\n";
        }

        return $string;
    }

    private function _makeFieldsWithOnly ($tokens) {
        if (!$tokens || !count ($tokens)) $tokens = array ();

        $string = ''; $length = count ($this->{'data'}->{'query_names'});
        $tokens_quantity = count ($tokens);

        for ($cntr = 0; $cntr < $length; $cntr ++) {
            if (!$this->_parameterIsSet ($cntr)) continue;

            if ($this->_presentInTokens ($this->{'data'}->{'query_names'}[$cntr], $tokens, $tokens_quantity)) {
                $string .= '<input type=hidden name="'.$this->{'data'}->{'query_names'}[$cntr].'" value="'.$this->{'data'}->{'query_values'}[$cntr].'">';
                $string .= "\n";
            }
        }

        return $string;
    }

    private function _parameterIsSet ($position) {
        if ($position > count ($this->{'data'}->{'query_names'}) - 1) return 0;

        if ($this->{'data'}->{'query_names'}[$position] && $this->{'data'}->{'query_values'}[$position] != '') return 1;
        else return 0;
    }

    private function _presentInTokens ($name, $tokens, $tokens_quantity) {
        for ($token_cntr = 0; $token_cntr < $tokens_quantity; $token_cntr ++) {
             if ($name[0] != '$' && $tokens[$token_cntr] == $name) return 1;
             if ($name[0] == '$' && $tokens[$token_cntr] == substr ($name, 1, strlen ($tokens[$token_cntr]))) return 1;
        }

        return 0;
    }

    function setParameter ($name, $value) {
        if (!$name || !$value) return;

        if (!isset($this->{'data'}->{'query_names'})) {
            $this->{'data'}->{'query_names'} = array ();
            $this->{'data'}->{'query_values'} = array ();
        }

        for ($cntr = 0; $cntr < count ($this->{'data'}->{'parameters_to_delete'}); $cntr ++) {
             if ($name == $this->{'data'}->{'parameters_to_delete'}[$cntr]) {
                 $this->_removeInternalParameter ($name); 
                 return;
             }
        }

        $name = urldecode ($name);   
        if (strpos (' '.$value, '%3A')) $value = urldecode ($value);

        if ($name[0] == '@') $name = pack ('H*', substr ($name, 1));
        if ($value[0] == '@') $value = pack ('H*', substr ($value, 1));

        $check_report = $this->_checkParameter ($name, $value);
        if ($check_report) { 
            $this->_printErrMsg ($check_report);
            exit;
        }

        $position = $this->_position($name);

        if ($position != -1) {
            $this->{'data'}->{'query_values'}[$position] = $value;
        } else {
            $position = count ($this->{'data'}->{'query_names'});

            $this->{'data'}->{'query_names'}[$position] = $name;
            $this->{'data'}->{'query_values'}[$position] = $value;
        }

        $this->_setInternalParameter ($name, $value);
    }

    function removeParameter ($name) {
        $position = $this->_position($name);

        if ($position != -1) {
            $this->{'data'}->{'query_names'}[$position] = '';
            $this->{'data'}->{'query_values'}[$position] = '';
        }

        $this->_removeInternalParameter ($name);
    }

    function parameterIsPresent ($name) {
        $position = $this->_position($name);

        if ($position != -1) return 1;
        else return 0;
    }

    private function _position ($name) {
        for ($cntr = 0; $cntr < count ($this->{'data'}->{'query_names'}); $cntr ++) {
             if ($this->{'data'}->{'query_names'}[$cntr] == $name) return $cntr;
        }

        return -1;
    }

    private function _checkParameter ($name, $value) {
        if (!trim ($name) || 
            strpos (' '.$name, 'x_') && strpos ($name, '_x')) return 'INCORRECT_NAME';

        if (substr ($name, 0, 1) == '$') $name = substr ($name, 1);

        if ($name[0] == '_') return 'ENVIRONMENT_ATTACK';
        if (strpos (' '.$name, '=') || strpos (' '.$name, '(') || strpos (' '.$name, ';')) return 'EVAL_CODE_INJECTION';

        if (substr ($name, strlen ($name) - 2, 2) == "'}") $name = substr ($name, 0, strlen ($name) - 2);

        $name = strreplace ($name, "'}->{'", '');
        $name = strreplace ($name,   "->{'", '');
        $name = trim (strtr ($name, 'abcdefghijklmnopqrstuvwxyz-_.:!0123456789',
                                    '                                         '));
        if (strlen ($name)) return 'INCORRECT_NAME_OR_CODE_INJECTION';

        $file_include_attack = 0;
        if (strlen ($value) > 1 && ($value[1] == ':')) $file_include_attack = 1;
        if (strlen ($value) > 0 && ($value[0] == '/' || $value[0] == '\\' || $value[0] == '.' || $value[0] == '~')) $file_include_attack = 1;
        if ($file_include_attack && !strpos (' '.strtolower ($value), 'tmp') && !strpos (' '.strtolower ($value), 'temp')) {
            if (file_exists ($value) && !is_dir ($value) && @filesize ($value)) return 'FILE_INCLUDE_ATTACK';
        }

        return '';
    }

    private function _printErrMsg ($check_report = 'X') {
        print '<!DOCTYPE html><html><body><b><tt>';
        print '<!-- ';
        print $check_report[0];
        print ' -->';

        for ($cntr = 0; $cntr < 8; $cntr ++) {
             for ($i = 0; $i < $cntr; $i ++) print '<br>';
             print '.';
        }
    }
}

$query = new HTTPWwwQuery ();
?>
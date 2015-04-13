<?
/**
 *  Методы для чтения и разбора файлов "*.ini" с настройками.
 */

class ConfigReader {
    function ConfigReader () { global $config_path;
        $config_ini = ''; $lines = '';

        if (isset ($config_path)) {
            $config_ini = $config_path.'/config.ini';
            if (file_exists ($config_ini)) $lines = file ($config_ini); 
            if (!$lines) $config_ini = ''; 
        }

        if (!$config_ini) {
            for ($cntr = 0; $cntr < 7; $cntr ++) {
                 if (!$config_ini) {
                     $dir_name = dirname (__FILE__);
                     $level = ''; for ($level_cntr = 0; $level_cntr < $cntr; $level_cntr ++) $level .= '../';

                     $config_ini = $dir_name.'/'.$level.'data/config/config.ini';
                 }

                 if (file_exists ($config_ini)) $lines = file ($config_ini); 

                 if (!$lines) $config_ini = ''; 
                 else break;
            }
        }

        $storage = 'config_array_'.rand ();
        $this->{'storage_var_name'} = $storage;
        $this->{$storage} = '';

        for ($cntr = count ($lines) - 1; $cntr >= 0; $cntr --) {
             $line = trim ($lines [$cntr]);

             if (!$line) continue;

             $splitter = '=';
             if (!strstr ($line, $splitter)) continue;

             if (substr ($line, 0, 1) == '#') continue;
             if (substr ($line, 0, 1) == ';') continue;
             if (substr ($line, 0, 1) == '[') continue;

             if (substr ($line, 0, 2) == '//') continue;
             if (substr ($line, 0, 2) == '/*') continue;

             $config_name = trim (strtok ($line, $splitter));
             if (!$config_name) continue;

             $config_value = trim (strtok ($splitter));

             $this->{$storage}->{$config_name} = $config_value;
        }
    }

    function param ($name) {
        $storage = $this->{'storage_var_name'};

        if ($name && isset ($this->{$storage}->{$name})) return $this->{$storage}->{$name};

        return '';
    }

    function language ($required) {
        if ($this->param ('language') == $required) return 1;

        return 0;
    }
}

$config = new ConfigReader ();
?>
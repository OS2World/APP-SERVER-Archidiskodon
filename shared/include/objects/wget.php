<?
/**
 *  Методы для запуска WGet
 */

class WGetRunner {
    function wgetIsInstalled () {
       $rc = 0;
       passthru ($this->_exeName ().' >'.$this->_nullFile ().' 2>'.$this->_nullFile (), $rc);
       if ($rc == 1 || $rc == 127) return 1;

       return 0;
    }

    function ftpFileList ($hostname, $path = '') {
       $path = $this->_ftpEncodeWGetPathLine ($path);
       $location = '"ftp://'.strreplace ($hostname.'/'.$path.'/', '//', '/').'"';

       $temp_name = $this->_tmpName ();

       $wget_exe_name = $this->_exeName ();
       $wait_ten_seconds = '--timeout=10';
       $only_two_retries = '--tries=2 --waitretry=1';
       $messages_to_file = '--output-file='.$temp_name;
       $output_to_stdout = '--output-document='.$this->_stdoutName ();

       $command = $wget_exe_name.
                  ' --verbose'.
                  ' '.$wait_ten_seconds.
                  ' '.$only_two_retries.
                  ' '.$messages_to_file.
                  ' '.$output_to_stdout.
                  ' '.$location;

       $output = array ();
       exec ($command, $output); $this->_unlinkWorkFiles ();

       if (!$output) return '';
       if (!count ($output)) return '';

       $report = '';

       if (file_exists ($temp_name)) {
           if (filesize ($temp_name)) {
               $file = fopen ($temp_name, 'r');
               $report = fread ($file, filesize ($temp_name));
               fclose ($file); unset ($file);
           }

           unlink ($temp_name); unset ($temp_name);
       }

       $ftp_error_404 = 0;
       if (strpos (' '.$report, 'No such directory')) $ftp_error_404 = 1;

       if ($ftp_error_404) return '';

       $files = array ();

       for ($cntr = 0; $cntr < count ($output); $cntr ++) {
            $mark = strtolower (substr ($output[$cntr], 0, strpos ($output[$cntr], '<')));
            if (strpos (' '.$mark, ' directory ')) $mark = 'D';
            if (strpos (' '.$mark, ' file ')) $mark = 'F';
            if (strlen ($mark) != 1) continue;

            $line = $output[$cntr];

            if (strpos (' '.$line, '<a href="ftp://'.$hostname)) {
                $name = $line;

                $name = substr ($name, strpos ($name, 'ftp://'));
                $name = substr ($name, 0, strpos ($name, '">'));
                $name = $this->_ftpDecodeWGetLine ($name);

                $name = strreplace ($name, dirname ($name), '');
                $name = strreplace ($name, '/', '');

                $size = '';
                if (strpos (' '.$line, ' bytes)')) {
                    $size = $line;

                    $size = substr ($size, strpos ($size, '</a>'));
                    $size = substr ($size, strpos ($size, '('));
                    $size = substr ($size, 1, strpos ($size, ')') - 1);

                    $size = strreplace ($size, ' bytes', '');
                    $size = strreplace ($size, ',', ' ');
                }

                $description = '';
                $description->{'name'} = $name;
                $description->{'size'} = $size;
                $description->{'mark'} = $mark;
                $files[] = $description;
                unset ($description);
            }
       }

       if (!count ($files)) return '';

       return $files;
    }

    function ftpDownload ($hostname, $path, $file_name) { 
       $path = $this->_ftpEncodeWGetPathLine (strreplace ($path.'/'.$file_name, '//', '/'));
       $location = '"ftp://'.strreplace ($hostname.'/'.$path, '//', '/').'"';

       $temp_name = $this->_tmpName ();

       $command = $this->_exeName ().
                  ' --quiet'.
                  ' --output-document='.$temp_name.
                  ' '.$location;

       exec ($command); $this->_unlinkWorkFiles ();

       $data = '';

       if (file_exists ($temp_name)) {
           if (filesize ($temp_name)) {
               $file = fopen ($temp_name, 'r');
               $data = fread ($file, filesize ($temp_name));
               fclose ($file); unset ($file);
           }

           unlink ($temp_name); unset ($temp_name);
       }

       return $data;
    }

    private function _ftpEncodeWGetPathLine ($string)  {
       $words = explode ('/', $string);
       for ($word_cntr = 0; $word_cntr < count ($words); $word_cntr ++) {
            $encoded_word = '';
            for ($char_cntr = 0; $char_cntr < strlen ($words[$word_cntr]); $char_cntr ++) {
                 $encoded_word .= '%'.dechex (ord ($words[$word_cntr][$char_cntr]));
            }
            $words[$word_cntr] = $encoded_word;
       }
       $string = implode ('/', $words);

       return $string;
    }

    private function _ftpDecodeWGetLine ($string)  {
       $trans_tbl = get_html_translation_table (HTML_ENTITIES, ENT_QUOTES);
       $trans_tbl = array_flip ($trans_tbl);
       $ret = strtr ($string, $trans_tbl);

       return preg_replace('/&#(\d+);/me', "chr('\\1')", $ret);
    }

    private function _unlinkWorkFiles ()  {
       if (file_exists ('core')) unlink ('core');
       if (file_exists ('.listing')) unlink ('.listing');
    }

    private function _tmpName () {
       return tempnam ('/tmp', 'wget-');
    }

    private function _stdoutName () {
       return '-';
    }

    private function _nullFile () {
       if (is_dir ('C:')) return 'NUL';
       else return '/dev/null';
    }

    private function _exeName () { global $config;
       $wget_name = 'wget';

       if (isset ($config)) { 
           $name = $config->param ('exec.wget_name');
           if ($name) $wget_name = $name;
       }

       return $wget_name;
    }
}

$wget = new WGetRunner ();
?>
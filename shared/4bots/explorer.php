<?
class FTPExplorer {
  function browseFTPServer ($hostname) {
     $this->_browseTree ($hostname);
  }

  private function _browseTree ($hostname, $path = '') { global $indexer, $wget, $config;
     $file_names = $wget->ftpFileList ($hostname, $path);

     if (!$file_names) return;
     if (!count ($file_names)) return;

     $file_explanations = array ();
     if ($config->param ('parse_bbs_files')) {
         $file_explanations = $this->_getBBSExplanations ($hostname, $path);
     }

     for ($cntr = 0; $cntr < count ($file_names); $cntr ++) {
          $name = $file_names[$cntr]->{'name'};
          if (!$name || $name == '.' || $name == '..') continue;

          if (!$this->_fileIsAcceptable ($path, $name)) continue;

          $description = '';
          $description->{'server'} = $hostname;
          $description->{'path'} = $path;
          $description->{'name'} = $name;

          if ($file_names[$cntr]->{'mark'} == 'D') {
              $next_path = $path.'/'.$name;
              $this->_browseTree ($hostname, $next_path);

              $description->{'size'} = 0;
              $description->{'type'} = 'D';
              $description->{'explanation'} = $this->_selectBBSExplanation ($name, $file_explanations);

              $indexer->makeSearchIndex ($description);
              unset ($description);

              continue;
          }

          $file_size = $file_names[$cntr]->{'size'};

          $media_type = $this->_mediaType ($name);
          if (!$media_type) continue;

          $description->{'size'} = $file_size;
          $description->{'type'} = $media_type;
          $description->{'explanation'} = $this->_selectBBSExplanation ($name, $file_explanations);

          $indexer->makeSearchIndex ($description);
          unset ($description);
     }
  }

  private function _getBBSExplanations ($hostname, $path) { global $methods, $rexx, $wget;
     $text = ''; $format = '';

     for ($iteration = 0; $iteration < 4; $iteration ++) {
          $file_name = '';

          if ($iteration == 0) { $file_name = 'files.bbs';    $format = 'BBS';    }
          if ($iteration == 1) { $file_name = 'dirinfo.txt';  $format = 'BBS';    }
          if ($iteration == 2) { $file_name = 'descript.ion'; $format = 'BBS';    }
          if ($iteration == 3) { $file_name = '00index.txt';  $format = 'HOBBES'; }

          for ($step = 0; $step < 2; $step ++) {
               if ($step == 1) $file_name = $methods->upperCase ($file_name);
               
               $text = $wget->ftpDownload ($hostname, $path, $file_name);
               if ($text) break;
          }

          if ($text) break;
     }

     $explanations = array ();
     if (!$text) return $explanations;

     $lines = split ("\n", $text);
     unset ($text);

     for ($cntr = 0; $cntr < count ($lines); $cntr ++) {
          $file_name = ''; $explanation = '';

          $string = trim ($lines[$cntr]);
          if (!$string) continue;

          $values = $rexx->parse ('|'.$string.'|', '|', ' ', '|');
          $file_name = $values[0];
          $explanation = $values[1];
          unset ($values);

          $file_name = trim ($file_name);
          $explanation = trim ($explanation);
          if (!$file_name || !$explanation) continue;

          if (substr ($file_name, 0, 3) == 'drw') continue;
          if (substr ($file_name, 0, 3) == 'dr-') continue;
          if (substr ($file_name, 0, 3) == '-rw') continue;
          if (substr ($file_name, 0, 3) == '-r-') continue;

          if ($format == 'HOBBES') {
              $values = $rexx->parse ('|'.$explanation.'|', '|', '  ', '|');
              $explanation = $values[1];
              unset ($values);
          }

          $explanation = trim ($explanation);
          if (!$explanation) continue;

          if (substr ($file_name, 0, -1) == '/') $file_name = substr ($file_name, 0, strlen ($file_name) - 1);
          if (substr ($explanation, 0, 1) == '-') $explanation = substr ($explanation, 1);

          $file_name = trim ($file_name);
          $explanation = trim ($explanation);
          if (!$file_name || !$explanation) continue;

          $description = '';
          $description->{'file_name'} = $file_name;
          $description->{'explanation'} = $explanation;
          $explanations[] = $description;
          unset ($description);
     }

     return $explanations;
  }

  private function _selectBBSExplanation ($name, $explanations) { 
     for ($cntr = 0; $cntr < count ($explanations); $cntr ++) {
          if ($name == $explanations[$cntr]->{'file_name'}) return $explanations[$cntr]->{'explanation'};
     }

     return '';
  }

  private function _fileIsAcceptable ($path, $name) { global $methods;
     $path = $methods->lowerCase ($path);
     $name = $methods->lowerCase ($name);

     if (strpos (' '.$name, ':') || strpos (' '.$name, 'no such file')) return 0;

     if (strpos (' '.$path, 'xxx')  || strpos (' '.$name, 'xxx'))  return 0;
     if (strpos (' '.$path, 'porn') || strpos (' '.$name, 'porn')) return 0;
     if (strpos (' '.$path, 'gay')  || strpos (' '.$name, 'gay'))  return 0;
     if (strpos (' '.$path, 'lesb') || strpos (' '.$name, 'lesb')) return 0;

     if (strpos (' '.$name, '.') && !$this->_mediaType ($name)) return 0;

     return 1;
  }

  private function _mediaType ($name) { global $methods;
     $ext = trim ($methods->lowerCase (substr ($name, strrpos ($name, '.'))));
     if (strlen ($ext) > 4) $ext = '';

     if (!$ext) return '';

     {
      if ($ext == '.mp3')  return 'A';
      if ($ext == '.mpc')  return 'A';
      if ($ext == '.ogg')  return 'A';
      if ($ext == '.wma')  return 'A';
     }

     {
      if ($ext == '.mid')  return 'M';
      if ($ext == '.midi') return 'M';

      if ($ext == '.mod')  return 'M';
      if ($ext == '.s3m')  return 'M';
      if ($ext == '.amf')  return 'M';
      if ($ext == '.xm')   return 'M';
     }

     {
      if ($ext == '.mpg')  return 'V';
      if ($ext == '.mpeg') return 'V';
      if ($ext == '.avi')  return 'V';
      if ($ext == '.wmv')  return 'V';
     }

     {
      if ($ext == '.swf')  return 'F';
     }

     {
      if ($ext == '.zip')  return 'Z';
      if ($ext == '.rar')  return 'Z';
      if ($ext == '.arj')  return 'Z';
      if ($ext == '.ace')  return 'Z';
      if ($ext == '.arc')  return 'Z';
      if ($ext == '.lzh')  return 'Z';
      if ($ext == '.ice')  return 'Z';
      if ($ext == '.ha')   return 'Z';
      if ($ext == '.uc2')  return 'Z';
      if ($ext == '.zoo')  return 'Z';

      if ($ext == '.shk')  return 'Z';
      if ($ext == '.sdk')  return 'Z';
      if ($ext == '.bxy')  return 'Z';
      if ($ext == '.hqx')  return 'Z';

      if ($ext == '.z')    return 'Z';
      if ($ext == '.gz')   return 'Z';
      if ($ext == '.tgz')  return 'Z';
      if ($ext == '.bz2')  return 'Z';

      if ($ext == '.iso')  return 'Z';
      if ($ext == '.nrg')  return 'Z';
      if ($ext == '.bin')  return 'Z';
      if ($ext == '.cif')  return 'Z';

      if ($ext == '.jar')  return 'Z';
      if ($ext == '.exe')  return 'Z';
     }

     return '';
  }
}
?>
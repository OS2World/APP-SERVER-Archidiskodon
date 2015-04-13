<?
/**
 *  œÓÎÂÁÌ˚Â ÏÂÚÓ‰˚.
 */

class HTMLMethods {
   function HTMLMethods () { global $current_path, $website_path;
       $this->{'ws_path'} = './';
       if (isset ($current_path) && is_dir ($current_path)) $this->{'ws_path'} = $current_path;
       if (isset ($website_path) && is_dir ($website_path)) $this->{'ws_path'} = $website_path;
   }

   function domainName () { global $SERVER_NAME;
       if (isset($SERVER_NAME)) return strreplace ($SERVER_NAME, 'www.', '');
       else return '';
   }

   function getPointerPath($show) {
       $evil_hacker_gimmick = '..'; $strong_admin_protection = '.';
       $show = strreplace ($show, $evil_hacker_gimmick, $strong_admin_protection);

       $show = strreplace ($show, '.', '/');
       $show = strreplace ($show, ' ', '_');

       $show = strreplace ($show, ')', '');
       $show = strreplace ($show, '(', '');

       $show = $this->_correctFileName ($show);
       $show = $this->delDoubleSpaces ($show);

       return $show;
   }

   private function _correctFileName ($file_name) {
       return strreplace ($file_name, '//', '/');
   }

   function title ($show) {
       $title_file = $this->resolveTitleFile ($show);

       if (file_exists ($title_file)) {
           $file = fopen ($title_file, 'r');
           $title = fread ($file, filesize ($title_file));
           fclose ($file); unset ($file);

           $title = strreplace ($title, '<br>', '');
           $title = strreplace ($title, "\n", '');

           return trim ($title);
       }

       return '';
   }

   function resolveTitleFile ($show) {
       return $this->_resolveFile ($show, 'title');
   }

   function resolveDateFile ($show) {
       return $this->_resolveFile ($show, 'date');
   }

   private function _resolveFile ($show, $suffix) {
       $name = $this->getPointerPath ($show);

       $file_name = '';

       for($step_cntr = 0; $step_cntr < 2; $step_cntr ++) {
           $extension = 'php';

           for ($ext_cntr = 0; $ext_cntr < 2; $ext_cntr ++) {
                $level = '';

                for ($level_cntr = 0; $level_cntr < 2; $level_cntr ++) {
                     $pointer = $this->{'ws_path'}.$name.$level.'/'.$suffix.'.'.$extension;
                     if (file_exists ($pointer)) { $file_name = $pointer; break; }

                     $pointer = $this->{'ws_path'}.$name.'_'.$suffix.'.'.$extension;
                     if (file_exists ($pointer)) { $file_name = $pointer; break; }

                     if (strstr ($name, '_txt')) {
                         $pointer = $this->{'ws_path'}.strreplace ($name, '_txt', '_'.$suffix).'.'.$extension;
                         if (file_exists ($pointer)) { $file_name = $pointer; break; }
                     }

                     $level = '/..';
                }
                if ($file_name) break;

                $extension = 'txt';
           }
           if ($file_name) break;

           $name = 'db/'.$name;
       }

       return $file_name;
   }

   function makePointer ($show) {
       $name = strreplace ($show, '/', '.');
       if (substr ($name, 0, 3) == 'db.') $name = substr ($name, 3);

       return $name;
   }

   function resolvePointer ($show) {
       $name = $this->getPointerPath ($show);

       $pointer = $this->{'ws_path'}.'db/'.$name.'.php';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.txt';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.csv';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.tsv';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.lnk';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.alt';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.jpg';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.png';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       $pointer = $this->{'ws_path'}.'db/'.$name.'.gif';
       if (file_exists ($pointer)) $name = 'db/'.$name;

       return $name;
   }

   function addSpacesToNChars ($string, $n) {
        $spaces = '';
        for ($cntr = 0; $cntr < $n - strlen ($string); $cntr ++) $spaces .= ' ';

        $result = (string) ($string.$spaces);
        return $result;
   }

   function addZerosToNChars ($number, $n = 0) {
        $zeros = '';
        for ($cntr = 0; $cntr < $n - strlen ($number); $cntr ++) $zeros .= '0';

        $result = (string) ($zeros.$number);
        return $result;
   }

   function addZerosTo4Chars ($number) {
        return $this->addZerosToNChars ($number, 4);
   }

   function txt ($name) {
       $file_name = $this->_correctFileName ($this->{'ws_path'}.$name);

       if (strstr ($name, '.php') || strstr ($name, '.txt') || strstr ($name, '.rgb') ||
           strstr ($name, '.lnk') || strstr ($name, '.alt') ||
           strstr ($name, '.csv') || strstr ($name, '.tsv')) { 

           if (!file_exists ($file_name)) return '';

           $size = filesize ($file_name);
           if (!$size) return '';

           $file = fopen ($file_name, 'r');
           if (!$file) return '';

           $text = fread ($file, $size);
           fclose ($file); unset ($file);

           return trim ($text);
       }

       return '';
   }

   function img ($name, $prp = '', $max_width = 0, $max_height = 0, $area = 0) {
       $file_name = $this->_correctFileName ($this->{'ws_path'}.$name);
       if (!file_exists ($file_name)) return '<!-- Not found: '.$name.' -->';

       $image_size_string = '';
       if (!$prp || !strstr ($prp, 'width=')) {
           $image_size_string = $this->_imageSize ($file_name, $max_width, $max_height, $area);
       }

       $image_border_string = '';
       if (!$prp || !strstr ($prp, 'border=')) {
           $image_border_string = 'border="0"';
       }

       $image_alt_text = '';
       if (!$prp || !strstr ($prp, 'alt=')) {
           if ($prp && !strstr ($prp, '=')) { $image_alt_text = $prp; $prp = ''; }

           if (!$image_alt_text) $image_alt_text = $this->_detectImageAltText ($file_name);

           if ($image_alt_text) {
               $image_alt_text = strreplace ($image_alt_text, "\r\n", ' ');
               $image_alt_text = strreplace ($image_alt_text, "\n",   ' ');

               $image_alt_text = strreplace ($image_alt_text, '<br>', ' ');
               $image_alt_text = strreplace ($image_alt_text, '<b>',  '');
               $image_alt_text = strreplace ($image_alt_text, '</b>', '');

               $image_alt_text = strtr ($image_alt_text, "'", '`');

               $image_alt_text = 'alt=\''.$image_alt_text.'\'';
           }
       }

       $string = '<img src="'.$name.'" '.$image_size_string.' '.$image_border_string.' '.$image_alt_text.' '.$prp;
       $string = trim (strreplace ($string, '  ', ' '));
       $string .= '>';

       return $string;
   }

   function imgSize ($name, $max_width = 0, $max_height = 0, $area = 0) {
       $file_name = $this->_correctFileName ($this->{'ws_path'}.$name);
       if (!file_exists ($file_name)) return '[img_not_found:'.$file_name.']';

       return $this->_imageSize ($file_name, $max_width, $max_height, $area);
   }

   private function _imageSize ($file_name, $max_width = 0, $max_height = 0, $area = 0) { global $rexx;
       $size = getImageSize ($file_name);

       if (!$max_width && !$max_height) return $size[3];

       $values = $rexx->parse ($size[3], 'width="', '" height="', '"');

       $width = $values[0];
       $height = $values[1];

       if ($width > $max_width || $height > $max_height) {
           $original_width = $width;
           $original_height = $height;

           for ($zoom = 0; $zoom <= 20; $zoom ++) {
                if ($zoom == 1) {
                    $width = (int) ($original_width / 3 * 2);
                    $height = (int) ($original_height / 3 * 2);
                } 
                if ($zoom >= 2) {
                    $width = (int) ($original_width / $zoom);
                    $height = (int) ($original_height / $zoom);
                }

                if ($width <= $max_width) {
                    if ($max_height) {
                        if (!$area) {
                           if ($height <= $max_height) break;
                        } else {
                           if ($width * $height <= $max_width * $max_height) break;
                        }
                    } else {
                        break;
                    }
                }
           }
       }

       return 'width='.$width.' height='.$height;
   }

   function imageWidth ($name) {
       return $this->_imageDimension ($name, 0);
   }

   function imageHeight ($name) {
       return $this->_imageDimension ($name, 1);
   }

   private function _imageDimension ($name, $selector) {
       $file_name = $this->_correctFileName ($this->{'ws_path'}.$name);
       if (!file_exists ($file_name)) return 0;

       $size = getimagesize ($file_name);
       return $size[$selector];
   }

   private function _detectImageAltText ($file_name) { global $show;
       $alt_text = '';

       $fn_we = '';

       if (strpos (' '.$file_name, '.jpg')) $fn_we = strreplace ($file_name, '.jpg', '');
       if (strpos (' '.$file_name, '.png')) $fn_we = strreplace ($file_name, '.png', '');
       if (strpos (' '.$file_name, '.gif')) $fn_we = strreplace ($file_name, '.gif', '');

       $title_file = '';

       for ($cntr = 0; $cntr < 2; $cntr ++) {
            $title_file = $fn_we.'_alt.php';   if (file_exists ($title_file)) break;
            $title_file = $fn_we.'_alt.txt';   if (file_exists ($title_file)) break;
            $title_file = $fn_we.'_title.php'; if (file_exists ($title_file)) break;
            $title_file = $fn_we.'_title.txt'; if (file_exists ($title_file)) break;
            $title_file = $fn_we.'_h.php';     if (file_exists ($title_file)) break;
            $title_file = $fn_we.'_h.txt';     if (file_exists ($title_file)) break;

            $title_file = $fn_we.'.alt';       if (file_exists ($title_file)) break;
            $title_file = $fn_we.'.php';       if (file_exists ($title_file)) break;
            $title_file = $fn_we.'.txt';       if (file_exists ($title_file)) break;

            $title_file = '';

            if ($cntr == 0) {
                $fn_wp = substr ($fn_we, strrpos ($fn_we, '/') + 1);

                if (strpos (' '.$fn_wp, '-tn')) {
                    $fn_we = substr ($fn_we, 0, strrpos ($fn_we, '-tn')); continue;
                }

                break;
            }
       }

       if (!$title_file) {
           $fp_wn = $file_name; $max_levels = 3;

           for ($cntr = 0; $cntr < $max_levels; $cntr ++) {
                $fp_wn = substr ($fp_wn, 0, strrpos ($fp_wn, '/'));

                $title_file = $fp_wn.'/title.php'; if (file_exists ($title_file)) break;
                $title_file = $fp_wn.'/title.txt'; if (file_exists ($title_file)) break;
                $title_file = $fp_wn.'/name.php';  if (file_exists ($title_file)) break;
                $title_file = $fp_wn.'/name.txt';  if (file_exists ($title_file)) break;

                $title_file = '';
           }
       }

       if (!$title_file && isset ($show)) {
           $title_file = $this->resolveTitleFile ($show);
       }

       if ($title_file) {
           $title_file = strreplace ($title_file, $this->{'ws_path'}, '');
           $alt_text = $this->txt ($title_file);
       }

       if (!$alt_text) {
           $fn_we = strreplace ($fn_we, dirname ($fn_we), '');
           $fn_we = strreplace ($fn_we, 'tn', '');

           if ($fn_we) $alt_text = $this->mixedCase ($this->delPunctuationMarks ($fn_we));

           if ($alt_text) $alt_text = trim ($alt_text);
       }

       return $alt_text;
   }

   function extension ($image_file_or_mime_type) {
       $image_file_or_mime_type = strtolower ($image_file_or_mime_type);

       $ext = '';
       if (strstr ($image_file_or_mime_type, 'jpeg')) $ext = '.jpg';
       if (strstr ($image_file_or_mime_type, 'jpg')) $ext = '.jpg';
       if (strstr ($image_file_or_mime_type, 'png')) $ext = '.png';
       if (strstr ($image_file_or_mime_type, 'gif')) $ext = '.gif';

       return $ext;
   }

   function findImgFile ($show) {
       $file_name = '';

       $name = $this->getPointerPath ($show);

       for($cntr = 0; $cntr < 2; $cntr ++) {
           $pointer = $name.'.jpg';
           if (file_exists ($this->{'ws_path'}.$pointer)) { $file_name = $pointer; break; }

           $pointer = $name.'.png';
           if (file_exists ($this->{'ws_path'}.$pointer)) { $file_name = $pointer; break; }

           $pointer = $name.'.gif';
           if (file_exists ($this->{'ws_path'}.$pointer)) { $file_name = $pointer; break; }

           $name = 'db/'.$name;
       }

       if (strpos ('*'.$file_name, ' ')) $file_name = '';

       return $file_name;
   }

   function htmlchars ($string) {
       return strreplace (strreplace ($string, '<', '&lt;'), '>', '&gt;');
   }

   function nl2br ($string) {
       return strreplace (nl2br ($string), '<br />', '<br>');
   }

   function lowerCase ($string) {
       return $this->_changeCase ($string, 'L');
   }

   function upperCase ($string) {
       return $this->_changeCase ($string, 'U');
   }

   function mixedCase ($string) {
       $string = $this->lowerCase ($string);
       $string = $this->upperCase (substr ($string, 0, 1)).substr ($string, 1);

       return $string;
   }

   private function _changeCase ($string, $direction) {
       $upper_case = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ¿¡¬√ƒ≈®∆«»… ÀÃÕŒœ–—“”‘’÷◊ÿŸ‹€⁄›ﬁﬂ';
       $lower_case = 'abcdefghijklmnopqrstuvwxyz‡·‚„‰Â∏ÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ˜¯˘¸˚˙˝˛ˇ';

       if ($direction == 'L') return strtr ($string, $upper_case, $lower_case);
       if ($direction == 'U') return strtr ($string, $lower_case, $upper_case);

       return '';
   }

   function delPunctuationMarks ($string) {
       $pattern = '~`!@#$%^&*()-_=+[]{};:"|,.<>/?';
       $replace = '                              ';

       $string = strtr ($string, $pattern, $replace);
       $string = strtr ($string, "'",  ' ');
       $string = strtr ($string, '\\', ' ');

       $string = strreplace ($string, "\b", ' ');
       $string = strreplace ($string, "\t", ' ');
       $string = strreplace ($string, "\n", ' ');
       $string = strreplace ($string, "\v", ' ');
       $string = strreplace ($string, "\f", ' ');
       $string = strreplace ($string, "\r", ' ');

       $string = $this->delDoubleSpaces ($string);

       return $string;
   }

   function delNumbers ($string) {
       $pattern = '0123456789';
       $replace = '          ';

       $string = strtr ($string, $pattern, $replace);

       $string = $this->delDoubleSpaces ($string);

       return $string;
   }

   function delSpacesAndBreaks ($string) {
         return $this->delSpaces ($this->delBreaks ($string));
   }

   function delSpaces ($string) {
         return strreplace ($string, ' ', '');
   }

   function delBreaks ($string) {
         $symbols = '';
         $hollows = '';

         for ($cntr = 0; $cntr < ord (' '); $cntr ++) {
              $symbols .= chr ($cntr);
              $hollows .= ' ';
         }

         return strtr ($string, $symbols, $hollows);
   }

   function delDoubleSpaces ($string) {
       for ($cntr = 0; $cntr < 12; $cntr ++) {
            $new_string = trim (strreplace ($string, '  ', ' '));

            if ($new_string != $string) $string = $new_string;
            else break;
       }

       return $string;
   }

   function makeShortString ($string, $length = 25) {
       $string = trim ($string);
       if (!$string) return '';

       $result = '';
       $words = explode (' ', $string);

       for ($cntr = 0; $cntr < count ($words); $cntr ++) {
            $word = trim ($words[$cntr]);

            if (strlen ($result.$word) < $length) {
                $result .= $word.' ';
            } else {
                $result = trim ($result);
                if (substr ($result, -1) == '.' || substr ($result, -1) == ',' || 
                    substr ($result, -1) == ';' || substr ($result, -1) == ':') $result = substr ($result, 0, strlen ($result) - 1);

                $result .= '...';

                break;
            }
       }

       return trim ($result);
   }

   function getNumberCase ($number) {
       if ($number >= 10 && $number <= 20) return 0;

       switch ($number % 10) {
          case 0:  return 0;
          case 1:  return 1;
          case 2:  return 2;
          case 3:  return 2;
          case 4:  return 2;
          default: return 0;
       }
   }

   function makeHashIndex ($string, $base) {
       $result = 0;

       if ($base > 1) {
           for ($cntr = 1; $cntr <= strlen ($string); $cntr ++) {
                $result += ord ($string[$cntr - 1]) * $cntr;
           }

           if ($result > $base) $result = (int) ($result % $base);
       }

       return $result;       
   }

   function alert ($text) {
       print '<script>alert ("'.$text.'");</script>';
   }

   function redirect ($location) {
       print '<script>location.href="'.$location.'";</script>';
   }

   function goBack () {
       print '<script>history.back ();</script>';
   }

   function jumpBack ($steps) {
       if ($steps) print '<script>history.go (-'.$steps.');</script>';
   }
}

$methods = new HTMLMethods ();
?>
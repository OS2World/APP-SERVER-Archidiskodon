<?
include_once ($current_path.'include/objects/pages.php');

class AbstractBean {
    function AbstractBean () {
    }

    function getDocumentTitle () { global $methods, $query, $document;
        return '';
    }

    function setDocumentTitle () { global $methods, $query, $document;
        if (isset ($document)) $document->setTitle ($this->getDocumentTitle ());
    }

    function string ($string_id) { global $methods, $config, $textres_path;
        $language = $config->param ('language');
        if (!$language) $language = 'en';

        $file_name = $textres_path.'strings/'.$language.'/'.$string_id.'.txt';

        $file = fopen ($file_name, 'r');
        $string = fread ($file, filesize ($file_name));
        fclose ($file); unset ($file);

        return trim ($string);
    }
}
?>
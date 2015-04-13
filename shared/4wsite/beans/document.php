<?
class HtmlDocument {
   function HtmlDocument () { global $methods, $query, $show, $current_path;
        if (!isset ($methods)) return;

        if (file_exists ($current_path.'4wsite/beans/document/title.txt')) {
            $this->{'title'} = $methods->txt('4wsite/beans/document/title.txt');
        } else {
            $this->{'title'} = $methods->domainName();
        }

        if (isset ($show)) $this->setTitle ($methods->title ($show));

        $this->{'keywords'} = $methods->txt('4wsite/beans/document/keywords.txt');
   }

   function setTitle ($title) {
        if ($title) {
            $title = htmlspecialchars ($title);

            if ($this->{'title'}) $this->{'title'} = ' : '.$this->{'title'};
            $this->{'title'} = $title.$this->{'title'};
        }
   }
}

$document = new HtmlDocument ();
?>
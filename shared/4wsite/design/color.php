<?
class HTMLColors {
  function lineColor () { return $this->grayColor (); }
  function darkColor () { return $this->grayColor (); }
  function textColor () { return $this->linkColor (); }

  function pageColor () { return '#005500'; }
  function grayColor () { return '#000000'; }
  function headColor () { return '#F0F000'; }
  function linkColor () { return '#D7D7D7'; }
  function markColor () { return '#D7D700'; }
  function wbrkColor () { return '#808080'; }
  function lbrkColor () { return '#707070'; }
}

$colors = new HTMLColors ();
?>
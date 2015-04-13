<? 
$colnum   = 6;
$colspan  = $colnum + ($colnum - 1);
$colwidth = ((int) (100 / $colnum) + 1).'%';
?>

<table border=0 cellspacing="0" cellpadding="0" width="100%"><tr><td bgcolor="<?= $colors->lineColor (); ?>">
<table border=0 cellspacing="1" cellpadding="0" width="100%"><tr><td bgcolor="<?= $colors->pageColor (); ?>">
<table border=0 cellspacing="0" cellpadding="0" width="100%">

<tr>
 <td bgcolor="<? if ($required_type == 'A') print $colors->grayColor (); ?>" width="<?= $colwidth ?>">
  <table border=0 cellspacing="1" cellpadding="1" width="100%"><tr><td align=center valign=middle nowrap>
  &nbsp;<a href="index.php?type=A"><?= $this->string ('navigator/music'); ?></a>&nbsp;
  </td></tr></table>
 </td>
 <td bgcolor="<?= $colors->grayColor (); ?>" width=1>
 <img border=0 width=1 height=1 alt="" src="images/design/1x1.gif">
 </td>

 <td bgcolor="<? if ($required_type == 'M') print $colors->grayColor (); ?>" width="<?= $colwidth ?>">
  <table border=0 cellspacing="1" cellpadding="1" width="100%"><tr><td align=center valign=middle nowrap>
  &nbsp;<a href="index.php?type=M"><?= $this->string ('navigator/ringtones'); ?></a>&nbsp;
  </td></tr></table>
 </td>
 <td bgcolor="<?= $colors->grayColor (); ?>" width=1>
 <img border=0 width=1 height=1 alt="" src="images/design/1x1.gif">
 </td>

 <td bgcolor="<? if ($required_type == 'V') print $colors->grayColor (); ?>" width="<?= $colwidth ?>">
  <table border=0 cellspacing="1" cellpadding="1" width="100%"><tr><td align=center valign=middle nowrap>
  &nbsp;<a href="index.php?type=V"><?= $this->string ('navigator/video'); ?></a>&nbsp;
  </td></tr></table>
 </td>
 <td bgcolor="<?= $colors->grayColor (); ?>" width=1>
 <img border=0 width=1 height=1 alt="" src="images/design/1x1.gif">
 </td>

 <td bgcolor="<? if ($required_type == 'F') print $colors->grayColor (); ?>" width="<?= $colwidth ?>">
  <table border=0 cellspacing="1" cellpadding="1" width="100%"><tr><td align=center valign=middle nowrap>
  &nbsp;<a href="index.php?type=F"><?= $this->string ('navigator/animation'); ?></a>&nbsp;
  </td></tr></table>
 </td>
 <td bgcolor="<?= $colors->grayColor (); ?>" width=1>
 <img border=0 width=1 height=1 alt="" src="images/design/1x1.gif">
 </td>

 <td bgcolor="<? if ($required_type == 'D') print $colors->grayColor (); ?>" width="<?= $colwidth ?>">
  <table border=0 cellspacing="1" cellpadding="1" width="100%"><tr><td align=center valign=middle nowrap>
  &nbsp;<a href="index.php?type=D"><?= $this->string ('navigator/folders'); ?></a>&nbsp;
  </td></tr></table>
 </td>
 <td bgcolor="<?= $colors->grayColor (); ?>" width=1>
 <img border=0 width=1 height=1 alt="" src="images/design/1x1.gif">
 </td>

 <td bgcolor="<? if ($required_type == 'Z') print $colors->grayColor (); ?>" width="<?= $colwidth ?>">
  <table border=0 cellspacing="1" cellpadding="1" width="100%"><tr><td align=center valign=middle nowrap>
  &nbsp;<a href="index.php?type=Z"><?= $this->string ('navigator/software'); ?></a>&nbsp;
  </td></tr></table>
 </td>
</tr>

<tr height="1" bgcolor="<?= $colors->grayColor (); ?>">
 <td colspan=<?= $colspan ?>><img border=0 width=1 height=1 alt="" src="images/design/1x1.gif"></td>
</tr>

<tr>
 <td colspan=<?= $colspan ?>>
  <table border=0 cellspacing="1" cellpadding="1" width="100%"><tr><td align=center valign=middle nowrap>
<? 
unset ($colnum);
unset ($colspan);
unset ($colwidth);
?>
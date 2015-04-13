<? if ((isset ($prev_anchor) || isset ($next_anchor) || isset ($all_anchors)) && trim (strip_tags ($all_anchors)) != '1') { ?>

<br><br><center><table border=0 cellspacing=0 cellpadding=0 width="75%"><tr><td align=center>
<?= $prev_anchor ?>&nbsp;&nbsp;&nbsp;<?= $all_anchors ?>&nbsp;&nbsp;&nbsp;<?= $next_anchor ?>
</td></tr></table></center><br><br>

<? } ?>
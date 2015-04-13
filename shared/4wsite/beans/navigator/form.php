<? if ($show_form) { ?>
   <table border=0 cellspacing="1" cellpadding="10"><tr><td align=center valign=middle>
   <input type=text name=search value="<?= $search_string ?>" size=50 maxlength=50 <? if (!$search_string) print 'id="field2select"'; ?>>
   <input type=button value="<?= $this->string ('navigator/search'); ?>"
    onClick="if (search_form.search.value) submit ();"
   >
   </td></tr></table>
<? } ?>
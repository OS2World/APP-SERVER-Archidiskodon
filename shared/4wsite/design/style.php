<?
$selected_font = 1;

if ($selected_font == 1) { $typeface = 'Helv, Tahoma, Verdana, Geneva, Helvetica, sans-serif'; $typeset = '8pt';  }
if ($selected_font == 2) { $typeface = 'Courier New, Courier, monospace';                      $typeset = '9pt';  }
if ($selected_font == 3) { $typeface = 'System VIO, Lucida Console, monospace';                $typeset = '10pt'; }
if ($selected_font == 4) { $typeface = 'Helv, Tahoma, Verdana, Geneva, Helvetica, sans-serif'; $typeset = '9pt';  }

unset ($selected_font);
?>
<style>
body, p, div, span, a, b, i, strong, em, cite, h1, h2, h3, h4, table, tr, th, td, ul, li, font, small, blockquote, form, input, select, textarea
			{ font-family: <?= $typeface ?>; text-decoration: none; font-size: <?= $typeset ?> }
tt, pre
			{ font-family: Courier New, Courier, monospace; text-decoration: none; font-size: <?= $typeset ?> }

input, select, textarea	{ border: 1px solid; color: <?= $colors->textColor (); ?>; background-color: <?= $colors->pageColor (); ?>; border-color: <?= $colors->lineColor (); ?> }

a:hover
			{ text-decoration: underline }
</style>
<? unset ($typeface); unset ($typeset); ?>
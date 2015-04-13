<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<? if ($config->param ('charset')) print '<meta http-equiv="Content-Type" content="text/html; charset='.$config->param ('charset').'">'; ?>
<title><?= $document->{'title'} ?></title>
<meta name="Keywords" content="<? if (isset ($search)) { print $methods->upperCase ($search).' '; } ?><?= $document->{'keywords'} ?>">
<? include ($current_path.'4wsite/design/color.php'); ?>
<? include ($current_path.'4wsite/design/style.php'); ?>
<link href="images/icon/favicon.png" rel="icon" type="image/png">
</head>

<?php
/* @var $lwp Literally_WordPress */
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."wp-load.php";

if(isset($_GET["name"])){
	$path = $lwp->dir.DIRECTORY_SEPARATOR."help".DIRECTORY_SEPARATOR.str_replace(".", "", str_replace("/", "", $_GET['name'])).".php";
	$dir = plugin_dir_url(__FILE__);
	if(file_exists($path)):?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php $lwp->e("Literally WordPress Help"); ?></title>
	<link rel="stylesheet" href="<?php echo $dir; ?>../assets/style.css" type="text/css">
	<link rel="stylesheet" href="<?php echo $dir; ?>syntax/shCore.css" type="text/css">
	<link rel="stylesheet" href="<?php echo $dir; ?>syntax/shThemeDefault.css" type="text/css">
	<script type="text/javascript" src="<?php echo $dir; ?>syntax/shCore.js"></script>
	<script type="text/javascript" src="<?php echo $dir; ?>syntax/shBrushXml.js"></script>
	<script type="text/javascript" src="<?php echo $dir; ?>syntax/shBrushPhp.js"></script>
	<script type="text/javascript">
		//<![CDATA[
			SyntaxHighlighter.all();
		//]]>
	</script>
</head>
<body>
	<div class="help-wrap">
		<?php require_once $path; ?>
	</div>
</body>
</html>
<?php
	endif;
}

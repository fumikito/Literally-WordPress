<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."wp-load.php";

if(isset($_GET["name"])){
	$path = $lwp->dir.DS."help".DS."{$_GET['name']}.php";
	if(file_exists($path)):?><html>
<head>
	<title>Literally WordPressヘルプ</title>
	<link rel="stylesheet" href="../assets/style.css">
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

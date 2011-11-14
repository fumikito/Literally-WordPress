<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."wp-load.php";

if(isset($_GET["name"])){
	$path = $lwp->dir.DIRECTORY_SEPARATOR."help".DIRECTORY_SEPARATOR.str_replace(".", "", str_replace("/", "", $_GET['name'])).".php";
	if(file_exists($path)):?><html>
<head>
	<title><?php $lwp->e("Literally WordPress Help"); ?></title>
	
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

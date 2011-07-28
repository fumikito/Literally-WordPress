<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php wp_title(); ?></title>
<?php wp_head(); ?>
</head>
<body class="lwp">
	<div id="lwp-invoice" class="lwp-<?php echo $slug;?>">
		<div class="header">
			<h1><?php bloginfo('name'); ?></h1>
		</div>
		<!-- // .header -->
		
		<div class="main">
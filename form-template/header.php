<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php echo esc_html($meta_title); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class('lwp'); ?>>
	<div id="lwp-invoice" class="lwp-<?php echo $slug;?>">
		<div class="header">
			<h1><?php echo apply_filters('lwp_form_title', get_bloginfo('name')); ?></h1>
		</div>
		<!-- // .header -->
		
		<div class="main">
			<?php if(isset($total, $current)) _lwp_show_indicator($total, $current);?>
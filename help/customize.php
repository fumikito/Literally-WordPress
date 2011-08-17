<?php if($_SERVER['SCRIPT_FILENAME'] == __FILE__) die(); /** @var $lwp Literally_WordPress */?>
<h1><?php $lwp->e('How to customize'); ?></h1>
<p>
	<?php $lwp->e('This plugin offers some ways to customize.'); ?>
</p>
<h2><?php $lwp->e('Default Functions'); ?></h2>
<p>
	<?php $lwp->e('First of all, you\'d better to know detail of LWP. Main functions are below.'); ?>
</p>
<ol>
	<li><?php $lwp->e('Display price and &quot;Buy Now&quot; button. This is a gate way to start transaction.'); ?></li>
	<li><?php $lwp->e('Display transaction page. Transaction steps count 3. Confirmation, Complete and Cancel.'); ?></li>
	<li><?php $lwp->e('Show purchase history. User can know what they bought and total cost.'); ?></li>
	<li><?php $lwp->e('Show download link and device types.'); ?></li>
</ol>
<p>
	<?php $lwp->e('You have no need to customize with normal usage, but customize and suit this plugin to your theme will be helpful for your customer.'); ?>
</p>
<h2><?php $lwp->e('Customize Buy Now'); ?></h2>
<p>
	<?php $lwp->e('To customize &quot;Buy Now&quot; button, you have 2 ways.'); ?>
</p>
<ol>
	<li><?php $lwp->e('Use your own CSS. CSS knowledge required.'); ?></li>
	<li><?php $lwp->e('Quit auto output and edit theme files. Knowledge about WordPress theme system required.'); ?></li>
</ol>
<h3><?php $lwp->e('1. Use your own CSS'); ?></h3>
<img class="aligncenter" src="<?php echo $lwp->url?>/help/img/customize-ss1-buynow.png" alt="" width="390" height="396" />
<p>
	<?php $lwp->e('LWP automatically loads CSS file by default. It looks like above.'); ?><br />
	<?php $lwp->e('WordPress has many themes and you may want make this form suit to you own theme.'); ?><br />
	<?php printf($lwp->_('In this case, turn auto load OFF at <a href="%s">LWP Setting</a>. Now &quot;Buy Now&quot; button has no style and you can customize it\'s appearance.'), admin_url('admin.php?page=lwp-setting')); ?>
</p>
<h3><?php $lwp->e('2. Create button by yourself'); ?></h3>
<p>
	<?php $lwp->e('&quot;Buy Now&quot; button will be displayed at bottom of the post content by default.'); ?><br />
	<?php $lwp->e('But you probably want to change the place of button and LWP provides 3 way to do it'); ?>
</p>
<ol>
	<li><?php printf($lwp->_('Sidebar Widgets. If your theme supports sidebar widget system, go to <a href="%s">Theme &gt; Widgets</a> and install LWP sidebar widget.'), admin_url('widgets.php')); ?></li>
	<li><?php printf($lwp->_('Use short code. On visual editor, you can find &quot;%s&quot; list box. It inserts shortcode to display &quot;Buy Now&quot; button.'), $lwp->_('Add Buy Now')); ?></li>
	<li><?php printf($lwp->_('Customize theme. The template tag lwp_buy_now() will display button. See detail at <a href="%s" target="_blank">function reference</a>.'), $lwp->url.'docs/'); ?></li>
</ol>
<h2><?php $lwp->e('Customize Device list and Download list'); ?></h2>
<p>
	<?php $lwp->e('Coming soon...'); ?>
</p>
<h2><?php $lwp->e('Customize Form'); ?></h2>
<p>
	<?php $lwp->e('Coming soon...'); ?>
</p>
<h2><?php $lwp->e('Make purchase history page'); ?></h2>
<p>
	<?php $lwp->e('Coming soon...'); ?>
</p>
<h2><?php $lwp->e('Create custom post type by yourself'); ?></h2>
<p>
	<?php $lwp->e('LWP provides the function to create custom post type because it is supposed that normal user sell perticullar type of contents like eBook or Music file.'); ?><br />
	<?php $lwp->e('But you may think of selling multiple types of contents(ex. music, movie, eBook and photo). In such case, LWP can recognize multiple custom post types as payable.'); ?><br />
	<?php $lwp->e('Furthermore, you may want make some taxonomies for your items(ex. eBook genre).'); ?><br />
	<?php $lwp->e('What LWP do is &quot;making perticular post types payable&quot;. So you can easily add custom post types and make them payable.');?><br />
	<?php $lwp->e('There are lots of plugins which enable you to add custom post types. Belows are list of them.');?>
</p>
<ol>
	<li><a href="http://wordpress.org/extend/plugins/custom-post-type-ui/"><?php $lwp->e('Custom Post Type UI'); ?></a></li>
</ol>
<p class="desc">
	<strong><?php $lwp->e("Notes"); ?></strong>: 
	<?php $lwp->e('They are 3rd party plugin and I don\'t test them all. Everything is at your own risk. Please try out on them carefully.'); ?>
</p>
<?php if($_SERVER['SCRIPT_FILENAME'] == __FILE__) die(); /* @var $lwp Literally_WordPress */ ?>
<h1><?php $lwp->e('Directory'); ?></h1>
<h2><?php $lwp->e('About file uploading system');?></h2>
<p>
	<?php $lwp->e('WordPress has original file uploading system. Nomal process is like below:'); ?>
</p>
<ol>
	<li><?php $lwp->e('Files are uploaded to /wp-content/uploads directory.'); ?></li>
	<li><?php $lwp->e('After uploading has succeeded, files are accessile via HTTP. You can layout your image files to post content.'); ?></li>
</ol>
<h2><?php $lwp->e('Protect you files');?></h2>
<p>
	<?php $lwp->e('This process above is quite proper for blog system. But if you sell your files with this plugin, this system is not enough.'); ?><br />
	<?php $lwp->e('For example, if you uplaod sample.pdf with Media uploader, it is accessible with <em>http://example.com/wp-content/uploads/sample.pdf</em>.'); ?><br />
	<?php $lwp->e('So, LWP has another way of uploading and save files in different directory. It should be inaccessible with HTTP.'); ?><br />
	<?php $lwp->e('Only users who bought your item can access them. LWP has simple authenctication system to judge the user can access file or not.'); ?>
</p>
<h2><?php $lwp->e('Normal setting'); ?></h2>
<p>
	<?php $lwp->e('Let\'s assume the case that your WordPress is installed in directory <em>/home/your-name/public_html</em>.'); ?><br />
	<?php $lwp->e('In this case, the directory accessible via HTTP is only <em>/home/your-name/public_html</em>.'); ?>
</p>
<ol>
	<li><?php $lwp->e('Create directory <em>/home/your-name/files</em>.'); ?></li>
	<li><?php $lwp->e('Set this directory\'s permission to 707 with FTP tools.'); ?></li>
	<li><?php printf($lwp->_('Set directory path at <a href="%s">LWP Setting</a>.'), admin_url('admin.php?page=lwp-setting')); ?></li>
</ol>
<p>
	<?php $lwp->e('Now you finish protecting your files from non-customer.'); ?>
</p>
<h2><?php $lwp->e('No inaccessible directory'); ?></h2>
<p>
	<?php $lwp->e('Situation depends on your hosting service. Some of hosting service don\'t offer inaccessible directory and your root directory may accessible via HTTP.'); ?><br />
	<?php $lwp->e('For example, your root directory is <em>/home/your-name/</em> and your domain is <em>http://your-name.example.com</em>.'); ?><br />
	<?php $lwp->e('This is very typical for hosting sevices which provide free domain. Even if you get your own domain, such hosting service tends to assign your new domain to <em>/home/your-name/new-domain</em>');?><br />
	<?php $lwp->e('So, how can you protect your files? Calm down, LWP provides protect method for such case.'); ?><br />
	<?php $lwp->e('By default, LWP set file directory to <em>/wp-content/plugins/literally-wordpress/contents</em>. This direcotry has .htaccess file to protect your files from HTTP access.'); ?><br />
	<?php $lwp->e('So, if you don\'t have inaccessible directory, set it as default.');?>
</p>
<p class="desc">
	<strong><?php $lwp->e("Notes"); ?></strong>: 
	<?php $lwp->e('If your files are accessible via HTTP, an alert message will be displayed on admin panel.'); ?>
</p>
<?php if($_SERVER['SCRIPT_FILENAME'] == __FILE__) die(); /* @var $lwp Literally_WordPress */ ?>
<h1><?php $lwp->e('Transfer'); ?></h1>
<h2><?php $lwp->e('Aboutn transfer');?></h2>
<p>
	<?php $lwp->e('Transfer is typically bank deposit transfer. If you allow this payment method, transaction will have another process following:'); ?>
</p>
<ol>
	<li><?php $lwp->e('At first, your user finishies his transaction by choosing transfer payment.'); ?></li>
	<li><?php $lwp->e('User see payment instruction on the thank you page and the notification mail.'); ?></li>
	<li><?php $lwp->e('User pais the bill by sending deposit to your account. After that, you must change your user\'s transactional satatus to success.'); ?></li>
	<li><?php $lwp->e('User gets the notification mail which tells his transaciton has been suceeded. Now he can access to your contents.'); ?></li>
</ol>
<h2><?php $lwp->e('What to be care about');?></h2>
<p><?php $lwp->e('Deferent from PayPal transaciton, your user\'s transactional has one more steps to check whether transfer has been done or not. '); ?></p>
<ol>
	<li><?php printf($lwp->_('Transfer transaction is tend to be ignored or forgotten by your user. Literally WordPress will send some notification mail to your user. One is reminder and another is expiration. You can choose each periode in <a href="%1$s" target="_top">%1$s</a>.'), admin_url('admin.php?page=lwp-setting'), $lwp->_('General Setting')); ?>;?></li>
	<li><?php $lwp->e('If transfer transaction will need extra payments such as bank remitting charge, you should inform your user about it.'); ?></li>
	<li><?php $lwp->e('You should use bank account which has notification service to avoid daily account check.'); ?></li>
</ol>
<h2><?php $lwp->e('How to change status'); ?></h2>
<p>
	<?php printf($lwp->_('Once you find your user send the bill to your account, go to <a href="%1$s" target="_top">%2$s</a>. You can change your use\'s status there.'), admin_url('admin.php?page=lwp-transfer'), $lwp->_('Transfer Management')); ?><br />
	<?php $lwp->e('Your use\'s status changed, notification mail will be sent. Now your user know transaction was finished.'); ?>
</p>
<h2><?php $lwp->e('Customizing notification'); ?></h2>
<ul>
	<li><?php printf($lwp->_('Making transfer allowed, you can access to <a href="%1$s" target="_top">%2$s</a>.'), admin_url('edit.php?post_type='.$lwp->notification->post_type), $lwp->_('Notification')); ?></li>
	<li><?php $lwp->e('You can edit each notification message as custom post type.'); ?></li>
	<li><?php $lwp->e('Required messages will be created automatically by this plugin. You don\'t have to create new notification message.');?></li>
	<li><?php $lwp->e('Notifications accept place holder such as bank account, expiration date, item page\'s url and so on. For detail, see each notification\'s edit page.'); ?></li>
</ul>
<p class="desc">
	<strong><?php $lwp->e("Notes"); ?></strong>: 
	<?php $lwp->e('Adding new notification is possible but meaningless.'); ?>
</p>
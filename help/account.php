<?php if($_SERVER['SCRIPT_FILENAME'] == __FILE__) die(); /* @var $lwp Literally_WordPress */?>

<h1><?php $lwp->e('Account');?></h1>
<p>
	<?php printf($lwp->_('First of all, PayPal account is required. Get it <a href="%s">here</a>.'), 'https://www.paypal.com'); ?><br />
	<?php $lwp->e('If you already have an account, see section below.'); ?>
</p>
<h2><?php $lwp->e("Step1 Get API Account, Password, Signature"); ?></h2>
<p>
	<?php $lwp->e('This plugin uses ExpressCheckout API and it requires your user name and password.'); ?><br />
	<?php $lwp->e('For security reason, PayPal offers API credentials consisted of username alias, password and API signature. You have to get it.'); ?><br />
</p>
<img class="aligncenter" src="<?php echo $lwp->url?>/help/img/account-ss1-api.png" alt="" width="450" height="363" />
<ol>
	<li><?php $lwp->e('Log in to PayPal, and go to Seller Preference &gt; Tools &gt; API access.'); ?></li>
	<li><?php $lwp->e('Selecting option 2 and you can get API signature, API user name and API password.');?></li>
	<li><?php printf($lwp->_('Copy and paste it to form on <a href="%s">LWP Setting</a>.'), admin_url('admin.php?page=lwp-setting')); ?></li>
</ol>

<h2><?php $lwp->e('Step2 Get PDT Token'); ?></h2>
<p>
	<?php $lwp->e('Once you get API signatures, now you can get PDT token. It is required for this plugin\'s background process'); ?>
</p>
<img class="aligncenter" src="<?php echo $lwp->url?>/help/img/account-ss2-pdt.png" alt="" width="431" height="463" />
<ol>
	<li><?php $lwp->e('On PayPal, go to Seller Preference &gt; Tools &gt; Web site setting.'); ?></li>
	<li><?php $lwp->e('Check the radio button &quot;Express return&quot; to ON.');?></li>
	<li><?php $lwp->e('Set return URL to your WordPress root. Generally it will be &quot;http://example.com&quot;.');?></li>
	<li><?php $lwp->e('Find section &quot;Payment Data Transfer&quot; and turn it ON.'); ?></li>
	<li><?php $lwp->e('Now you can save canges. Teh other settigs are optional, so you can set them as you like.'); ?></li>
	<li><?php printf($lwp->_('After save settings, you can get Token on &quot;Payment Data Transfer&quot; section. Copy and past it at <a href="%s">LWP Setting</a>.'), admin_url('admin.php?page=lwp-setting')); ?></li>
</ol>

<h2><?php $lwp->e('Step3 Check on Sandbox'); ?></h2>
<p>
	<?php $lwp->e('Sandbox is development environment of PayPal. You can test if your settings are right with Sandbox.'); ?><br />
	<?php $lwp->e('To enable Sandbox, you have to get another account at <a href="https://developer.paypal.com" target="_blank">PayPal Sandbox</a>.');?><br />
	<?php $lwp->e('This process is little bit complicated, but payment transaction is very critical. It\'s worth to test with.'); ?>
</p>
<img class="aligncenter" src="<?php echo $lwp->url?>/help/img/account-ss3-sandbox.png" alt="" width="440" height="340" />
<ol>
	<li><?php $lwp->e('Go to <a href="https://developer.paypal.com" target="_blank">PayPal Sandbox</a> and create account.'); ?></li>
	<li><?php $lwp->e('Log in to Sandbox and create 2 accounts. One for business account, another is customer account.'); ?></li>
	<li><?php printf($lwp->_('You can get account information of business account same as you get below. API user name, API password, API signature and PDT token. Set them on <a href="%1$s">LWP Setting</a> and turn &quot;%2$s&quot; to ON.'), admin_url('admin.php?page=lwp-setting'), $lwp->_('Use Sandbox')); ?></li>
	<li><?php $lwp->e('Keeping loggin in Sandbox, try to buy any of your items. After pushing &quot;Buy Now&quot;, you will be redirected to PayPal Sandbox. Then enter the customer account information you\'ve created below.'); ?></li>
	<li><?php $lwp->e('If everything is fine, you transaction will be completed. Check if you transaction is same as what you supposed.'); ?></li>
</ol>
<p class="desc">
	<strong><?php $lwp->e("Notes"); ?></strong>: 
	<?php $lwp->e('Every transactional information will be treated same in database. If you want know proper transactional statistics, don\'t use same settings both Sandbox and Productional environment on single WrodPress.'); ?><br />
	<?php $lwp->e('Generally, you should make WordPress for development(ex. http//d.example.com) and test it with Sandbox.'); ?><br />
	<?php $lwp->e('If everything is fine, try it on productional WordPress(ex. http://example.ecom). These are typical process for creating transacional Web site. Don\'t drive highway without enough excercise.'); ?>
</p>
<h2><?php $lwp->e('About Micropayments'); ?></h2>
<p>
	<?php $lwp->e('<a href="https://micropayments.paypal-labs.com/" target="_blank">Micropayments</a> is PayPal\'s pricing for small amount. If you sell your items by small amount, consider to signup to Micropayments.'); ?><br />
	<?php $lwp->e('If your item\'s price average is less than 25$, Micropayments is better solution. If not, Use normal pricing.'); ?><br />
	<?php $lwp->e('Anyway, who price your items is you and you are the most familiar with your item.');?>
</p>
<img class="aligncenter" src="<?php echo $lwp->url?>/help/img/account-ss4-micropayments.png" alt="" width="440" height="330" />
<h2><?php $lwp->e('Product slug'); ?></h2>
<p>
	<?php $lwp->e('Product slug will be displayed on PayPal\'s purchase history page. If you use PayPal with other commerce, this slug will helpful for distinguishing them from others.'); ?><br />
	<?php $lwp->e('Your transaction will be displayed like <em>slug-000010</em>. You can easily find, sort, sum LWP transaction.'); ?>
</p>
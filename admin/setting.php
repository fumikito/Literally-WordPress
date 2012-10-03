<?php /* @var $this Literally_WordPress */ ?>
<h2 class="nav-tab-wrapper">
	<a class="nav-tab<?php if(!isset($_REQUEST['view'])) echo ' nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=lwp-setting');?>">
		<?php $this->e("LWP Setting"); ?>
	</a>
	<?php
		foreach(array(
			'payment' => $this->_('Payment Options'),
			'post' => $this->_('Digital Contents'),
			'subscription' => $this->_('Subscription'),
			'event' => $this->_('Event'),
			'reward' => $this->_('Reward'),
			'misc' => $this->_('Miscellaneous')
		) as $key => $val):
	?>
	<a class="nav-tab<?php if(isset($_REQUEST['view']) && $_REQUEST['view'] == $key) echo ' nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=lwp-setting&view='.$key);?>">
		<?php echo $val; ?>
	</a>
	<?php endforeach; ?>
</h2>

<div class="lwp-main-container">
	<div class="lwp-container-inner">	
		<form id="lwp-setting-form" method="post" action="<?php echo admin_url('admin.php?page=lwp-setting'); ?>">
			<?php wp_nonce_field("lwp_update_option"); ?>
			<?php if(isset($_REQUEST['view'])): ?>
			<input type="hidden" name="view" value="<?php echo esc_attr($_REQUEST['view']); ?>" />
			<?php endif; ?>

			<?php
				switch(isset($_REQUEST['view']) ? $_REQUEST['view'] : ''){
					case 'payment':
					case 'post':
					case 'subscription':
					case 'event':
					case 'reward':
					case 'misc':
						$path = $this->dir."/admin/settings/{$_REQUEST['view']}.php";
						if(file_exists($path)){
							include_once $path;
						}else{
							printf('<div class="error"><p>%s</p></div>', sprintf($this->_('Template file does not exists at %s.'), $path) );
						}
						break;
					default:
						include_once $this->dir."/admin/settings/dashboard.php";
						break;
				}
			?>

			<?php if(isset($_REQUEST['view'])): ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php $this->e("Update"); ?>" />
			</p>
			<?php endif; ?>
		</form>
	</div><!-- //.lwp-container-inner -->
</div><!-- //#lwp-status-container -->

<?php include_once dirname(__FILE__).'/settings/sidebar.php';?>

<div class="clear"></div>

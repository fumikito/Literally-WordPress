<?php /* @var $this Literally_WordPress */ ?>

<h3><?php $this->e('Current Setting'); ?></h3>


<h4><?php $this->e('Payment Setting'); ?></h4>

<table class="lwp-form-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Payment method'); ?></th>
			<th scope="col"><?php $this->e('Status'); ?></th>
			<th scope="col"><?php $this->e('Remarks'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3">
				<?php printf($this->_('You can set up %1$s <a href="%2$s">Here</a>.'), $this->_('Payment Setting'), admin_url('admin.php?page=lwp-setting&view=payment')); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php $paypal_warning = $this->paypal_warning(); foreach (array(
			array(
				'PayPal',
				!$paypal_warning,
				sprintf($this->_('Set up your PayPal information <a href="%s">here</a>.'), admin_url('admin.php?page=lwp-setting&view=payment'))
			),
			array(
				$this->_('GMO Payment Gateway'),
				$this->gmo->is_enabled(),
				$this->_('Japanese domestic payment service. Credit card, Web CVS and PayEasy are supported.')
			),
			array(
				$this->_('Softbank Payment'),
				$this->softbank->is_enabled(),
				$this->_('Japanese domestic payment service. Credit card, Web CVS and PayEasy are supported.')
			),
			array(
				$this->_('NTT SmartTrade'),
				false,
				$this->_('Japanese domestic payment service. Credit card, Web CVS and e-money are supported.')
			),
			array(
				$this->_('Transfer'),
				$this->notifier->is_enabled(),
				sprintf($this->_('Transafer is typically bank transfer. You can set up it <a href="%s">here</a>.'),  admin_url('admin.php?page=lwp-setting&view=payment'))
			),
		) as $var): ?>
		<tr<?php if($var[1]) echo ' class="enabled"' ?>>
			<th scope="row"><?php echo esc_html($var[0]); ?></th>
			<td class="status"><?php $this->e($var[1] ? 'Enabled' : 'Disabled' ); ?></td>
			<td><?php echo $var[2]; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h4><?php $this->e('What you sell');  ?></h4>
<table class="lwp-form-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Sales Type'); ?></th>
			<th scope="col"><?php $this->e('Status'); ?></th>
			<th scope="col"><?php $this->e('Remarks'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"><?php $this->e('Each sales type should be set up properly.'); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php foreach(array(
			array(
				$this->_('Digital Contents'),
				$this->post->is_enabled(),
				$this->_('You can sell post content itself or attached downloadble content. Typically you can sell your ebook, music video, how-tos and so on.')
			),
			array(
				$this->_('iOS In App Purchase'),
				$this->ios->is_ios_available(),
				$this->_('You can use this WordPress site as content delivery server for iOS <strong>In App Purchase</strong>.')
			),
			array(
				$this->_('Android In-App Billing'),
				$this->ios->is_android_available(),
				$this->_('You can use this WordPress site as content delivery server for Android <strong>In-App Billing</strong>.')
			),
			array(
				$this->_('Subscription'),
				$this->subscription->is_enabled(),
				$this->_('You can provide member only contents by subscription plan. You can choose any price, any period.')
			),
			array(
				$this->_('Event'),
				$this->event->is_enabled(),
				$this->_('Event means real event like lunch party, poetry reading or else.')
			)
		)as $var): ?>
		<tr<?php if($var[1]) echo ' class="enabled"' ?>>
			<th scope="row"><?php echo $var[0]; ?></th>
			<td class="status"><?php $this->e($var[1] ? 'Enabled' : 'Disabled'); ?></td>
			<td><?php echo $var[2] ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h4><?php $this->e('Marketing'); ?></h4>
<table class="lwp-form-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Marketing Type'); ?></th>
			<th scope="col"><?php $this->e('Status'); ?></th>
			<th scope="col"><?php $this->e('Remarks'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"><?php $this->e('Let\'s make promotion to get more sales.'); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php foreach(array(
			array(
				$this->_('Reward'),
				$this->reward->is_enabled(),
				$this->_('You can allow your users to promote your product and reward for them.')
			)
		) as $var): ?>
		<tr<?php if($var[1]) echo ' class="enabled"' ?>>
			<th scope="row"><?php echo $var[0]; ?></th>
			<td class="status"><?php $this->e($var[1] ? 'Enabled' : 'Disabled'); ?></td>
			<td><?php echo $var[2] ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h4><?php $this->e('Recommended Plugins'); ?></h4>

<table class="lwp-form-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Plugin Name'); ?></th>
			<th scope="col"><?php $this->e('Status'); ?></th>
			<th scope="col"><?php $this->e('Description'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"><?php $this->e('Other plugins can be alternative. These informations are just recommendations.'); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php
		$plugins = get_option('active_plugins');
		foreach(array(
			array(
				'Theme My Login',
				'theme-my-login/theme-my-login.php',
				'theme-my-login',
				$this->_('Themes the WordPress login pages according to your theme.')
			),
			array(
				'Never Let Me Go',
				'never-let-me-go/never-let-me-go.php',
				'never-let-me-go',
				$this->_('Let users unregister by themselves from public page.')
			),
			array(
				'Gianism',
				'gianism/wp-gianism.php',
				'gianism',
				$this->_('Users can login or register with his SNS account like Facebook, Twitter, mixi, etc.')
			)
		) as $var): $activated = (false !== array_search($var[1], $plugins)); ?>
		<tr<?php if($activated) echo ' class="enabled"' ?>>
			<th scope="row"><?php echo $var[0]; ?></th>
			<td class="status">
				<?php if($activated): ?>
					<?php $this->e('Activated'); ?>
				<?php elseif(file_exists(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$var[1])): ?>
					<?php $this->e('Not activated'); ?>&nbsp;<a class="button" href="<?php echo wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.rawurlencode($var[1])), 'activate-plugin_'.$var[1]);?>"><?php _e('Activate') ?></a>
				<?php else: ?>
					<?php $this->e('Not installed'); ?>
					<?php if(current_user_can('install_plugins')): ?>
						&nbsp;<a class="button" href="<?php echo wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin='.$var[2]), 'install-plugin_'.$var[2]); ?>"><?php _e('Install'); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $var[3] ?><br />
				<small><a href="http://wordpress.org/extend/plugins/<?php echo $var[2] ?>/" target="_blank"><?php $this->e('See detail on WordPress.org');?> &raquo;</a></small>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
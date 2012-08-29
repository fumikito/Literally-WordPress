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
				$this->_('Transfer'),
				$this->notifier->is_enabled(),
				sprintf($this->_('Transafer is typically bank transfer. You can set up it <a href="%s">here</a>.'),  admin_url('admin.php?page=lwp-setting&view=payment'))
			)
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
				__('Post'),
				$this->post->is_enabled(),
				$this->_('You can sell post content itself or attached downloadble content. Typically you can sell your ebook, music video, how-tos and so on.')
			),
			array(
				$this->_('iOS non-consumable Product'),
				$this->ios->is_enabled(),
				$this->_('You can manage iOS non-consumable product with WordPress.')
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
			<td colspan="3"><?php $this->e('Each sales type should be set up properly.'); ?></td>
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
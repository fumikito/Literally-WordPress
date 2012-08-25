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
		<?php foreach (array(
			array(
				'PayPal',
				!empty($this->option['username']),
				''
			),
			array(
				$this->_('Transfer'),
				$this->notifier->is_enabled(),
				''
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
				!empty($this->option['payable_post_types']),
				''
			),
			array(
				$this->_('iOS non-consumable Product'),
				false,
				$this->_('You can also sell iOS product via Web site.')
			),
			array(
				$this->_('Subscription'),
				$this->subscription->is_enabled(),
				''
			),
			array(
				$this->_('Event'),
				$this->event->is_enabled(),
				''
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
				''
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
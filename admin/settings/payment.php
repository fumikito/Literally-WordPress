<?php /* @var $this Literally_WordPress */ ?>

<table class="lwp-form-table lwp-ios-status-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Option Name'); ?></th>
			<th scope="col"><?php $this->e('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2"><?php printf($this->_('Setup these at <a href="%s">here</a>.'), admin_url('admin.php?page=lwp-setting&view=post')); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php
			$services = array(
				array(
					'slug' => 'paypal',
					'label' => $this->_('PayPal'),
					'valid' => $this->is_paypal_enbaled()
				),
				array(
					'slug' => 'gmo',
					'label' => $this->gmo->vendor_name(true),
					'valid' => $this->gmo->is_enabled()
				),
				array(
					'slug' => 'softbank',
					'label' => $this->softbank->vendor_name(true),
					'valid' => $this->softbank->is_enabled()
				),
				array(
					'slug' => 'ntt',
					'label' => $this->ntt->vendor_name(true),
					'valid' => $this->ntt->is_enabled()
				),
				array(
					'slug' => 'transfer',
					'label' => $this->_('Transfer'),
					'valid' => $this->notifier->is_enabled()
				));
			foreach($services as $service): ?>
		<tr<?php if($service['valid']) echo ' class="enabled"' ?>>
			<th scope="row"><?php echo $service['label']; ?></th>
			<td class="status"><?php $this->e($service['valid'] ? 'Enabled' : 'Disabled'); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>



<h3><?php printf($this->_('About %s'), $this->_('Payment Options'));?></h3>
<p class="description"><?php $this->e('To enable LWP, you have to set up at leaset one payment method.'); ?></p>

<!-- Paypal -->

<h3><?php $this->e('Common Settings'); ?></h3>
<h4><?php $this->e('Payment Selection'); ?></h4>
<p>
	<label>
		<input type="checkbox" name="show_payment_agency" value="1" <?php if($this->show_payment_agency()) echo 'checked="checked" ';?>/>
		<?php $this->e('Show payment agency name on payment method selection screen.'); ?>
	</label>
</p>

<div style="clear: both;"></div>

<div id="lwp-tab">

	<ul>
		<?php foreach($services as $service): ?>
		<li>
			<a href="#setting-<?php echo $service['slug'] ?>"><?php $this->e($service['label']); ?>
				<?php if(false === array_search($service['slug'], array('paypal', 'transfer'))): ?>
				<small class="experimental"><?php $this->e('ONLY FOR JAPAN'); ?></small>
				<?php endif; ?>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>

	<?php foreach($services as $service): ?>
	<div id="setting-<?php echo $service['slug'] ?>">
		<?php if(($path = dirname(__FILE__).'/payment/'.$service['slug'].'.php') && file_exists($path)): ?>
			<?php include $path;  ?>
		<?php else: ?>
			<p class="invalid"><?php $this->e('Settign screen is not found. The plugin files might be broken.'); ?></p>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
	
</div><!-- //#lwp-tab -->
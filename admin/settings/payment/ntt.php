<?php /* @var $this Literally_WordPress */ ?>
<p class="description">
	<?php $this->e('To use NTT SmartTrade, you have to contract with NTT SmartTrade inc. and get credential infomation. If Credit Card is enabled, PayPal\'s credit card will be override.'); ?>
</p>
<hr />
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top"><?php $this->e('Status'); ?></th>
			<td>
				<?php if($this->ntt->is_enabled()): ?>
					<p class="valid">
						<?php printf($this->_('This payment method is enabled as %s.'),
								($this->ntt->is_sandbox ? $this->_('Sandbox') : $this->_('Productional environment'))); ?>
					</p>
				<?php else: ?>
					<p class="invalid"><?php $this->e('This payment method is invalid.'); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_sandbox"><?php $this->e('Sandbox'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="ntt_sandbox" id="ntt_sandbox" value="1"<?php if($this->ntt->is_sandbox) echo ' checked="checked"';?> />
					<?php $this->e("This is a develop enviorment and needs pseudo transaction.")?>
				</label>
				<p class="info"><?php $this->e('This payment method is very experimenal. <strong>Test on Sandbox is strongly recommended</strong>. If you find some bug please contact to plugin author at <a href="http://lwper.info">LWPper.info</a>'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Stealth mode'); ?></th>
			<td>
				<label>
					<input type="checkbox" name="ntt_stealth" value="1"<?php if($this->ntt->is_stealth) echo ' checked="checked"'; ?> />
					<?php $this->e('Enable stealth mode'); ?>
				</label>
				<p class="description"><?php $this->e('If stealth mode is on, only administrator can see this payment option on transaction form. This is useful for productional test.'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_shop_id"><?php $this->e('Shop ID'); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_shop_id" id="ntt_shop_id" class="regular-text" value="<?php echo esc_attr($this->ntt->shop_id); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="ntt_access_key"><?php $this->e('Access Key'); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input type="text" name="ntt_access_key" id="ntt_access_key" class="regular-text" value="<?php echo esc_attr($this->ntt->access_key); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Notification URL'); ?></th>
			<td>
				<input type="text" readonly="readonly" onclick="this.select(0, this.value.length);" value="<?php echo esc_attr(lwp_endpoint('ntt-smarttrade')); ?>" class="regular-text" />
				<p class="description">
					<?php $this->e('NTT SmartTrade requires contact endpoint on your server. Please notify this URL to your service manager.'); ?>
				</p>
				
			</td>
		</tr>
	</tbody>
</table>
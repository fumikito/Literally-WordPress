<?php /* @var $this Literally_WordPress */ ?>
<p class="description">
	<?php $this->e('To use GMO Payment Gateway, you have to contract with GMO Payment Gateway inc. and get credential infomation. If Credit Card is enabled, PayPal\'s credit card will be override.'); ?>
</p>
<hr />
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top"><?php $this->e('Status'); ?></th>
			<td>
				<?php if($this->gmo->is_enabled()): ?>
					<p class="valid">
						<?php printf($this->_('This payment method is enabled as %s.'),
								($this->gmo->is_sandbox ? $this->_('Sandbox') : $this->_('Productional environment'))); ?>
					</p>
				<?php else: ?>
					<p class="invalid"><?php $this->e('This payment method is invalid.'); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Sandbox'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="gmo_sandbox" id="gmo_sandbox" value="1"<?php if($this->gmo->is_sandbox) echo ' checked="checked"';?> />
					<?php $this->e("This is a develop enviorment and needs pseudo transaction.")?>
				</label>
				<p class="info"><?php $this->e('This payment method is very experimenal. <strong>Test on Sandbox is strongly recommended</strong>. If you find some bug please contact to plugin author at <a href="http://lwper.info">LWPper.info</a>'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Stealth mode'); ?></th>
			<td>
				<label>
					<input type="checkbox" name="gmo_stealth" value="1"<?php if($this->gmo->is_stealth) echo ' checked="checked"'; ?> />
					<?php $this->e('Enable stealth mode'); ?>
				</label>
				<p class="description"><?php $this->e('If stealth mode is on, only administrator can see this payment option on transaction form. This is useful for productional test.'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Credit Card'); ?></label>
			</th>
			<td>
				<?php
					$available_cards = $this->gmo->get_available_cards();
					$cc = $this->gmo->get_available_cards(true);
					foreach($cc as $c):
				?>
				<label>
					<input type="checkbox" name="gmo_creditcard[]" value="<?php echo esc_attr($c); ?>"<?php if(false !== array_search($c, $available_cards)) echo ' checked="checked"'; ?> />
					<?php echo esc_html($this->gmo->get_verbose_name($c)); ?>
				</label>&nbsp;
				<?php endforeach; ?>
				<p class="description">
					<?php $this->e('Checked credit cards are displayed on transaction form. Please check credit cards which you have contracted with.'); ?><br />
					<?php $this->e('Though GMO Payment has many credit cards available(Nicos, Mistui-Sumitomo, JACCS), most of them are aquirer of 5 famous international service above and it loses usability to display all cards available.'); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th valign="top">
				<label><?php $this->e('Web CVS'); ?></label>
			</th>
			<td>
				<?php
					$available_cvs = $this->gmo->get_available_cvs();
					$cvs = $this->gmo->get_available_cvs(true);
					foreach($cvs as $c):
				?>
				<label>
					<input type="checkbox" name="gmo_webcvs[]" value="<?php echo esc_attr($c); ?>"<?php if(false !== array_search($c, $available_cvs)) echo ' checked="checked"'; ?> />
					<?php echo esc_html($this->gmo->get_verbose_name($c)); ?>
				</label>&nbsp;
				<?php endforeach; ?><br />
				<label><?php printf($this->_('Payment must be finished within %s days.'), sprintf('<input type="text" class="small-text" name="gmo_cvs_limit" value="%s" placeholder="1" />', esc_attr(($this->gmo->cvs_limit)))); ?></label>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('PayEasy'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="gmo_payeasy" value="1" <?php if($this->gmo->payeasy) echo 'checked="checked"'; ?> />
					<?php $this->e('Enables PayEasy'); ?> 
				</label><br />
				<label><?php printf($this->_('Payment must be finished within %s days.'), sprintf('<input type="text" class="small-text" name="gmo_payeasy_limit" value="%s" placeholder="1" />', esc_attr(($this->gmo->payeasy_limit)))); ?></label>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="gmo_shop_id"><?php $this->e('Shop ID'); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td><input type="text" name="gmo_shop_id" id="gmo_shop_id" value="<?php echo esc_attr($this->gmo->shop_id); ?>" placeholder="ex. shop10000000" class="regular-text" /></td>
		</tr>
		<tr>
			<th valign="top">
				<label for="gmo_shop_pass"><?php $this->e('Shop Password'); ?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td><input type="text" name="gmo_shop_pass" id="gmo_shop_pass" value="<?php echo esc_attr($this->gmo->shop_pass); ?>" placeholder="ex. abcdefghi" class="regular-text" /></td>
		</tr>
		<tr>
			<th valign="top"><label for="gmo_tel"><?php $this->e('Contact Tel No.'); ?></label></th>
			<td>
				<input type="text" name="gmo_tel" id="gmo_tel" value="<?php echo esc_attr($this->gmo->tel_no); ?>" placeholder="ex. 03-1234-5786" class="regular-text" />
				<?php if(!preg_match("/^[0-9\-]+$/", $this->gmo->tel_no)): ?>
				<p class="invalid">
					<?php $this->e('Wrong Format.'); ?>
				</p>
				<?php endif; ?>
				<p class="description">
					<?php $this->e('Required for Lawson and Family Mart.'); ?>
					<?php $this->e('Allowed letters for Tel No. are <strong>0~9 and hyphen(-)</strong>.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Contact Open Hour'); ?></th>
			<td>
				<label>
					<?php $this->e('Starts: '); ?>
					<input type="text" name="gmo_contact_starts" value="<?php echo esc_attr($this->gmo->contact_starts); ?>" placeholder="ex. 09:00" class="middle-text hour-picker" />
				</label>
				~
				<label>
					<?php $this->e('Ends: '); ?>
					<input type="text" name="gmo_contact_ends" value="<?php echo esc_attr($this->gmo->contact_ends); ?>" placeholder="ex. 17:00" class="middle-text hour-picker" />
				</label>
				<?php if(
						!preg_match("/^[0-9]{2}:[0-9]{2}$/", $this->gmo->contact_starts)
							||
						!preg_match("/^[0-9]{2}:[0-9]{2}$/", $this->gmo->contact_ends)
				): ?>
				<p class="invalid">
					<?php $this->e('Wrong Format.'); ?>
				</p>
				<?php endif; ?>
				<p class="description">
					<?php $this->e('Required for Lawson and Family Mart.'); ?>
					 <?php $this->e('Hour format must be <strong>HH:MM</strong>.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Notification URL'); ?></th>
			<td>
				<input type="text" readonly="readonly" onclick="this.select(0, this.value.length);" value="<?php echo esc_attr(lwp_endpoint('gmo-payment')); ?>" class="regular-text" />
				<p class="description">
					<?php $this->e('If Web CVS or PayEasy is enabled, you must set up this URL as notification URL on GMO Payment Gateway\'s shop admin panel(Go to Manage Shop &gt; Shop Info &gt; Mail/Result notification and click &quot;edit&quot;.). Otherwise you loose payment status change.'); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>

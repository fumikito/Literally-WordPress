<h3><?php printf($this->_('About %s'), $this->_('Payment'));?></h3>
<p class="description"><?php $this->e('To enable LWP, you have to set up at leaset one payment method.'); ?></p>

<!-- Paypal -->


<h3><?php $this->e('PayPal Settings');?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top">
				<label><?php $this->e("Use Sandbox"); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="sandbox" id="sandbox" value="1"<?php if($this->option['sandbox']) echo ' checked="checked"';?> />
					<?php $this->e("This is a develop enviorment.")?>
				</label>
				<p class="description">
					<?php $this->e("Sandbox means develop enviorment. You can test your settings by checking above."); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="country_code"><?php $this->e("Country Code");?></label>
			</th>
			<td>
				<select name="country_code" id="country_code">
					<?php foreach(PayPal_Statics::country_codes() as $code => $country): ?>
					<option value="<?php echo $code; ?>"<?php if($this->option['country_code'] == $code) echo ' selected="selected"';?>><?php echo $country; ?></option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="currency_code"><?php $this->e("Currency Code");?></label>
			</th>
			<td>
				<select name="currency_code" id="currency_code">
					<?php foreach (PayPal_Statics::currency_codes() as $code => $desc): ?>
					<option value="<?php echo $code; ?>"<?php if($this->option['currency_code'] == $code) echo ' selected="selected"';?>><?php echo PayPal_Statics::currency_entity($code); ?> (<?php echo $desc; ?>)</option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="user_name"><?php $this->e("PayPal API User Name");?></label>
			</th>
			<td>
				<input id="user_name" name="user_name" class="regular-text" type="text" value="<?php $this->h($this->option["user_name"]); ?>" />
				<p class="description">
					<?php $this->e("PayPal API User Name issued by PayPal."); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="marchand_pass"><?php $this->e("PayPal Password");?></label>
			</th>
			<td>
				<input id="marchand_pass" name="marchand_pass" class="regular-text" type="password" value="<?php $this->h($this->option["password"]); ?>" />
				<p class="description">
					<?php $this->e("API password issued by PayPal"); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="signature"><?php $this->e("PayPal Signature");?></label>
			</th>
			<td>
				<input id="signature" name="signature" class="regular-text" type="text" value="<?php $this->h($this->option["signature"]); ?>" />
				<p class="description">
					<?php $this->e("API signature issued by PayPal"); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="token"><?php $this->e('PayPal PDT Token'); ?></label>
			</th>
			<td>
				<input id="token" name="token" class="regular-text" type="text" value="<?php $this->h($this->option["token"]); ?>" />
				<p class="description">
					<?php $this->e("Token issued by PayPal. Required for transaction.")?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
				</p>
				<?php if(!preg_match("/^[a-zA-Z0-9]+$/", $this->option["token"])): ?>
				<p class="error">
					<?php printf($this->_('This Token might be incorrect. See %s and get correct PDT Token.'), $this->help('account', $this->_('Help'))); ?>
				</p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="product_slug"><?php $this->e('Product slug'); ?></label>
			</th>
			<td>
				<input id="product_slug" name="product_slug" type="text" value="<?php $this->h($this->option['slug']); ?>" />
				<p class="description">
					<?php $this->e('Slug for product ID displayed on PayPal Account Panel. It is usefull if you have multiple business on singular account.'); ?>
					<small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small><br />
					<?php $this->e('Set <strong>about 10 alphanumeric letters</strong>. Hypen and product ID follow this slug. <small>ex: example-100</small>'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Payment Selection'); ?></th>
			<td>
				<label>
					<input type="checkbox" name="skip_payment_selection" value="1" <?php if($this->option['skip_payment_selection']) echo 'checked="checked" ';?>/>
					<?php $this->e('Skip payment selection form if PayPal is only available payment method.'); ?>
				</label>
			</td>
		</tr>
	</tbody>
</table>



<!-- Transfer -->



<h3><?php $this->e('Transfer Setting'); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top">
				<label><?php $this->e('Accept Transfer'); ?></label>
			</th>
			<td>
				<label><input type="radio" name="transfer" value="0" <?php if(!$this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Disallow'); ?></label><br />
				<label><input type="radio" name="transfer" value="1" <?php if($this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Allow'); ?></label>
				<p class="description">
					<?php $this->e('If you accept transfer, users can pay with bank account or something that is not digital transaction.'); ?>
					<?php $this->e('This helps users, but transactional process has a little bit more complex, because you have to check actual bank account to know whether bank deposit transfer has been made.'); ?>	
					<small>（<?php echo $this->help("transfer", $this->_("More &gt;"))?>）</small>
				</p>
			</td>
		</tr>
		<tr>
			<th><label><?php $this->e('Notification Frequency'); ?></label></th>
			<td>
				<label>
					<?php printf(
							$this->_('Send reminder on every %s days'),
							'<input class="short" type="text" name="notification_frequency" id="notification_frequency" value="'.intval($this->option['notification_frequency']).'" />'
					);?>
				</label><br />
				<label>
					<?php printf(
							$this->_('Transaction expires by %s days'),
							'<input class="short" type="text" name="notification_limit" id="notification_limit" value="'.intval($this->option['notification_limit']).'" />'
					);?>
				</label>
				<p class="description">
					<?php $this->e('If you don\'t want to send reminder, set notification frequency to 0. Transfer transaction will be expired after notification limit days will have been past.'); ?><br />
				</p>
			</td>
		</tr>
	</tbody>
</table>

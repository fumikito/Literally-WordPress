<?php /* @var $this Literally_WordPress */ ?>
<h3><?php printf($this->_('About %s'), $this->_('Payment Options'));?></h3>
<p class="description"><?php $this->e('To enable LWP, you have to set up at leaset one payment method.'); ?></p>

<!-- Paypal -->

<h3><?php $this->e('Common Settings'); ?></h3>
<table class="form-table">
	<tbody>
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
				<input id="user_name" name="user_name" class="regular-text" type="text" value="<?php echo esc_attr($this->option["user_name"]); ?>" />
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
				<input id="marchand_pass" name="marchand_pass" class="regular-text" type="password" value="<?php echo esc_attr($this->option["password"]); ?>" />
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
				<input id="signature" name="signature" class="regular-text" type="text" value="<?php echo esc_attr($this->option["signature"]); ?>" />
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
				<input id="token" name="token" class="regular-text" type="text" value="<?php echo esc_attr($this->option["token"]); ?>" />
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
				<input id="product_slug" name="product_slug" type="text" value="<?php echo esc_attr($this->option['slug']); ?>" />
				<p class="description">
					<?php $this->e('Slug for product ID displayed on PayPal Account Panel. It is usefull if you have multiple business on singular account.'); ?>
					<small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small><br />
					<?php $this->e('Set <strong>about 10 alphanumeric letters</strong>. Hypen and product ID follow this slug. <small>ex: example-100</small>'); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>



<!-- Softbank -->
<h3><?php $this->e('Softbank Payment'); ?><small class="experimental"><?php $this->e('ONLY FOR JAPAN'); ?></small></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top">
				<label><?php $this->e('Sandbox'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="sb_sandbox" id="sb_sandbox" value="1"<?php if($this->option['sb_sandbox']) echo ' checked="checked"';?> />
					<?php $this->e("This is a develop enviorment and needs pseudo transaction.")?>
				</label>
				<p class="description">
					<?php $this->e('To use Softbank Payment, you have to contract with SOFTBANK Payment Service corp. and get credential infomation. If Credit Card is enabled, PayPal\'s credit card will be override.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Credit Card'); ?></label>
			</th>
			<td>
				<?php
					$available_cards = $this->softbank->get_available_cards();
					$cc = $this->softbank->get_available_cards(true);
					foreach($cc as $c):
				?>
				<label>
					<input type="checkbox" name="sb_creditcard[]" value="<?php echo esc_attr($c); ?>"<?php if(false !== array_search($c, $available_cards)) echo ' checked="checked"'; ?> />
					<?php echo esc_html($this->softbank->get_verbose_name($c)); ?>
				</label>&nbsp;
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('PayEasy'); ?></label>
			</th>
			<td>
				<?php
					$available_cvs = $this->softbank->get_available_cvs();
					$cvs = $this->softbank->get_available_cvs(true);
					foreach($cvs as $c):
				?>
				<label>
					<input type="checkbox" name="sb_webcvs[]" value="<?php echo esc_attr($c); ?>"<?php if(false !== array_search($c, $available_cvs)) echo ' checked="checked"'; ?> />
					<?php echo esc_html($this->softbank->get_verbose_name($c)); ?>
				</label>&nbsp;
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label><?php $this->e('Web CVS'); ?></label>
			</th>
			<td>
				<label>
					<input type="checkbox" name="sb_payeasy" value="1" <?php if($this->softbank->payeasy) echo 'checked="checked"'; ?> />
					<?php $this->e('Enables PayEasy'); ?> 
				</label>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="sb_marchant_id"><?php $this->e('Marchant ID'); ?></label></th>
			<td>
				<input type="text" name="sb_marchant_id" id="sb_marchant_id" class="regular-text" value="<?php echo esc_attr($this->softbank->marchant_id(true)); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="sb_service_id"><?php $this->e('Service ID'); ?></label></th>
			<td>
				<input type="text" name="sb_service_id" id="sb_service_id" class="regular-text" value="<?php echo esc_attr($this->softbank->service_id(true)); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="sb_hash_key"><?php $this->e('Hash Key'); ?></label></th>
			<td>
				<input type="text" name="sb_hash_key" id="sb_hash_key" class="regular-text" value="<?php echo esc_attr($this->softbank->hash_key(true)); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="sb_prefix"><?php $this->e('Service Prefix'); ?></label></th>
			<td>
				<input type="text" name="sb_prefix" id="sb_prefix" class="" value="<?php echo esc_attr($this->softbank->prefix); ?>" />
				<p class="description">
					<?php $this->e('Prefix is used to generate transaction ID for Softbank Payment. Must be alphanumeric and less than 9 letters. Once you start your service, please don\'t change.'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Crypt Information'); ?></th>
			<td>
				<label>
					<input class="regular-text" type="text" name="sb_crypt_key" value="<?php echo esc_attr($this->softbank->crypt_key); ?>" />
					<?php $this->e('Crypt Key'); ?>
				</label><br />
				<label>
					<input class="regular-text" type="text" name="sb_iv" value="<?php echo esc_attr($this->softbank->iv) ?>" />
					<?php $this->e('IV (Initival Vector)'); ?>
				</label>
				<p><?php $this->e('These keys are required for productional environment and will be provided <strong>after contract with SOFTBANK Payment Service corp.</strong>'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Other Information'); ?></th>
			<td>
				<?php
					printf($this->_('This server\'s IP Address: <code>%s</code><br />'), $_SERVER['SERVER_ADDR']);
					printf($this->_('Endpoint: <code>%s</code><br />'), lwp_endpoint('sb-payment'));
					printf($this->_('PHP Mcrypt Extentions: <strong>%s</strong>.'), (function_exists('mcrypt_cbc') ? 'OK' : 'NG'));
					if(!function_exists('mcrypt_cbc')){
						echo '<strong style="color:red;">';
						printf($this->e('This server is not ready for productional environment. Please contact to your server admin and ask him to install PHP Mcrypt.'));
						echo '</strong>';
					}
				?>
			</td>
		</tr>
	</tbody>
</table>
<!-- Softbank -->



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

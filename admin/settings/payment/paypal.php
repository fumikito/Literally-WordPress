<?php /* @var $this Literally_WordPress */ ?>
<p class="description">
	<?php $this->e('First of all, you must registere PayPal. PayPal let your site to adopt credit card payemnt.'); ?>
</p>
<hr />
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top"><?php $this->e('Connection Check'); ?></th>
			<td>
				<p id="lwp-paypal-connector">
					<span class="loading"><small><?php echo admin_url('admin-ajax.php?action=lwp_paypal_creds'); ?></small><img src="<?php echo $this->url; ?>assets/indicator-postbox.gif" alt="loading" width="16" height="16" />&nbsp;<?php $this->e('Checking...'); ?></span>
					<span class="valid"><?php $this->e('Credential informations are valid.'); ?></span>
					<span class="invalid"><?php $this->e('Failed to connect with PayPal API. Please check your credential infos.'); ?></span>
				</p>
			</td>
		</tr>
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
					<?php $this->e("Sandbox means develop enviorment. You can test your settings by checking above."); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small><br />
					<?php printf($this->_('<strong>NOTICE: </strong> On sandbox environment, all transactions require <code>payment review</code> in default. This may cause unexpected result, so check your sandobx account setting at <a target="_blank" href="%s">PayPal Developer</a>.'), 'https://developer.paypal.com/webapps/developer/applications/accounts'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="country_code"><?php $this->e("Country Code");?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
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
				<small class="required"><?php $this->e('Required'); ?></small>
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
				<small class="required"><?php $this->e('Required'); ?></small>
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
				<small class="required"><?php $this->e('Required'); ?></small>
			</th>
			<td>
				<input id="marchand_pass" name="marchand_pass" class="regular-text" type="text" value="<?php echo esc_attr($this->option["password"]); ?>" />
				<p class="description">
					<?php $this->e("API password issued by PayPal"); ?><small>（<?php echo $this->help("account", $this->_("More &gt;"))?>）</small>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top">
				<label for="signature"><?php $this->e("PayPal Signature");?></label>
				<small class="required"><?php $this->e('Required'); ?></small>
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
				<small class="required"><?php $this->e('Required'); ?></small>
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
				<small class="required"><?php $this->e('Required'); ?></small>
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
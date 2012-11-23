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
		<?php foreach(array(
			array(
				$this->_('PayPal'),
				$this->is_paypal_enbaled()
			),
			array(
				$this->_('GMO Payment Gateway'),
				$this->gmo->is_enabled()
			),
			array(
				$this->_('Softbank Payment'),
				$this->softbank->is_enabled()
			),
			array(
				$this->_('Transfer'),
				$this->notifier->is_enabled()
			)
		)as $var): ?>
		<tr<?php if($var[1]) echo ' class="enabled"' ?>>
			<th scope="row"><?php echo $var[0]; ?></th>
			<td class="status"><?php $this->e($var[1] ? 'Enabled' : 'Disabled'); ?></td>
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
		<input type="checkbox" name="skip_payment_selection" value="1" <?php if($this->option['skip_payment_selection']) echo 'checked="checked" ';?>/>
		<?php $this->e('Skip payment selection form if PayPal is only available payment method.'); ?>
	</label>
</p>

<div style="clear: both;"></div>

<div id="lwp-tab">

	<ul>
		<li><a href="#setting-paypal"><?php $this->e('PayPal');?></a></li>
		<li><a href="#setting-gmo"><?php $this->e('GMO'); ?><small class="experimental"><?php $this->e('ONLY FOR JAPAN'); ?></small></a></li>
		<li><a href="#setting-softbank"><?php $this->e('Softbank'); ?><small class="experimental"><?php $this->e('ONLY FOR JAPAN'); ?></small></a></li>
		<li><a href="#setting-transfer"><?php $this->e('Transfer'); ?></a></li>
	</ul>


	<!-- PayPal -->
	<div id="setting-paypal">
		<p class="description">
			<?php $this->e('First of all, you must registere PayPal. PayPal let your site to adopt credit card payemnt.'); ?>
		</p>
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
	</div>
	<!-- //PayPal -->

	<!-- GMO -->
	<div id="setting-gmo">
		<p class="description">
			<?php $this->e('To use GMO Payment Gateway, you have to contract with GMO Payment Gateway inc. and get credential infomation. If Credit Card is enabled, PayPal\'s credit card will be override.'); ?>
		</p>
		<table class="form-table">
			<tbody>
				<tr>
					<th valign="top">
						<label><?php $this->e('Sandbox'); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" name="gmo_sandbox" id="gmo_sandbox" value="1"<?php if($this->gmo->is_sandbox) echo ' checked="checked"';?> />
							<?php $this->e("This is a develop enviorment and needs pseudo transaction.")?>
						</label>
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
						<?php endforeach; ?>
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
						</label>
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
	</div>
	<!-- //GMO -->
	
	
	
	<!-- Softbank -->
	<div id="setting-softbank">
		<p class="description">
			<?php $this->e('To use Softbank Payment, you have to contract with SOFTBANK Payment Service corp. and get credential infomation. If Credit Card is enabled, PayPal\'s credit card will be override.'); ?>
		</p>
		<hr />
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
						<p class="description">
							<?php $this->e('Checked credit cards are displayed on transaction form. Please check credit cards which you have contracted with.'); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e('Web CVS'); ?></label>
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
						<br />
						<label><?php printf($this->_('Payment must be finished within %s days.'), sprintf('<input type="text" class="small-text" name="sb_cvs_limit" value="%s" placeholder="1" />', esc_attr(($this->softbank->cvs_limit)))); ?></label>
						<?php if($this->softbank->cvs_limit < 1 || $this->softbank->cvs_limit > 60): ?>
						<p class="invalid"><?php $this->e('PayEasy\'s payment limit must be between 1 and 60.'); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label><?php $this->e('PayEasy'); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" name="sb_payeasy" value="1" <?php if($this->softbank->payeasy) echo 'checked="checked"'; ?> />
							<?php $this->e('Enables PayEasy'); ?> 
						</label>
						<p>
							<label>
								<?php $this->e('Name Kana on Invoice'); ?>
								<small class="required"><?php $this->e('Required'); ?></small><br />
								<input type="text" name="sb_blogname_kana" class="regular-text" value="<?php echo esc_html(($blogname_kana = $this->softbank->blogname_kana)); ?>" placeholder="ex. ワタシノブログ" />
								<small><?php printf($this->_('(%d letters max)'), 48);?></small>
							</label>
						</p>
						<p>
							<label>
								<?php $this->e('Name on Invoice'); ?>
								<small class="required"><?php $this->e('Required'); ?></small><br />
								<input type="text" name="sb_blogname" class="regular-text" value="<?php echo esc_html(($blogname = $this->softbank->blogname)); ?>" placeholder="ex. 私のブログ" />
								<small><?php printf($this->_('(%d letters max)'), 24);?></small>
							</label>

						</p>
						<label><?php printf($this->_('Payment must be finished within %s days.'), sprintf('<input type="text" class="small-text" name="sb_payeasy_limit" value="%s" placeholder="1" />', esc_attr(($this->softbank->payeasy_limit)))); ?></label>
						<p class="description">
							<?php $this->e('These values are required for PayEasy. Kana must be Zenkaku Kana.'); ?>
						</p>
						<?php if($this->softbank->payeasy): ?>
							<?php
								$err = array();
								if(empty($blogname) || mb_strlen($blogname, 'utf-8') > 24 ){
									$err[] = sprintf($this->_('%1$s is required for %2$s and character length must be %3$d and less.'), $this->_('Name on Invoice'), 'PayEasy', 24);
								}
								if(empty($blogname_kana) || mb_strlen($blogname_kana, 'utf-8') > 48 ){
									$err[] = sprintf($this->_('%1$s is required for %2$s and character length must be %3$d and less.'), $this->_('Name Kana on Invoice'), 'PayEasy', 48);
								}
								if(!empty($blogname_kana) && !preg_match("/^[ァ-ヾ]+$/u", $blogname_kana)){
									$err[] = sprintf($this->_('%s must be Zenkaku Kana.'), $this->_('Name Kana on Invoice'));
								}
								if($this->softbank->payeasy_limit > 60 || $this->softbank->payeasy_limit < 1){
									$err[] = $this->_('PayEasy\'s payment limit must be between 1 and 60.');
								}
								if(!empty($err)){
									printf('<p class="invalid">%s</p>', implode('<br />', $err));
								}
							?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="sb_marchant_id"><?php $this->e('Marchant ID'); ?></label>
						<small class="required"><?php $this->e('Required'); ?></small>
					</th>
					<td>
						<?php $marchant_id = $this->softbank->marchant_id(true); ?>
						<input type="text" name="sb_marchant_id" id="sb_marchant_id" class="regular-text" value="<?php echo esc_attr($marchant_id); ?>" />
						<?php if(!$this->softbank->is_sandbox && empty($marchant_id)): ?>
						<p class="invalid"><?php printf($this->_('%s is required for production environment.'), $this->_('Marchant ID')); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="sb_service_id"><?php $this->e('Service ID'); ?></label>
						<small class="required"><?php $this->e('Required'); ?></small>
					</th>
					<td>
						<?php $service_id = $this->softbank->service_id(true); ?>
						<input type="text" name="sb_service_id" id="sb_service_id" class="regular-text" value="<?php echo esc_attr($service_id); ?>" />
						<?php if(!$this->softbank->is_sandbox && empty($service_id)): ?>
						<p class="invalid"><?php printf($this->_('%s is required for production environment.'), $this->_('Service ID')); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="sb_hash_key"><?php $this->e('Hash Key'); ?></label>
						<small class="required"><?php $this->e('Required'); ?></small>
					</th>
					<td>
						<?php $hash_key = $this->softbank->hash_key(true); ?>
						<input type="text" name="sb_hash_key" id="sb_hash_key" class="regular-text" value="<?php echo esc_attr($hash_key); ?>" />
						<?php if(!$this->softbank->is_sandbox && empty($hash_key)): ?>
						<p class="invalid"><?php printf($this->_('%s is required for production environment.'), $this->_('Hash Key')); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<label for="sb_prefix"><?php $this->e('Service Prefix'); ?></label>
						<small class="required"><?php $this->e('Required'); ?></small>
					</th>
					<td>
						<input type="text" name="sb_prefix" id="sb_prefix" class="" value="<?php echo esc_attr($this->softbank->prefix); ?>" />
						<p class="description">
							<?php $this->e('Prefix is used to generate transaction ID for Softbank Payment. Must be alphanumeric and less than 9 letters. Once you start your service, please don\'t change.'); ?>
						</p>
						<?php if(empty($this->softbank->prefix) || strlen($this->softbank->prefix) > 8): ?>
						<p class="invalid"><?php printf($this->_('%1$s is required for %2$s and character length must be %3$d and less.'), $this->_('Service Prefix'), $this->_('Production Environment'), 8); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<?php $this->e('Crypt Information'); ?>
						<small class="required"><?php $this->e('Required'); ?></small>
					</th>
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
						<?php if(!$this->softbank->is_sandbox && (empty($this->softbank->iv) || empty($this->softbank->crypt_key))): ?>
						<p class="invalid"><?php printf($this->_('%s is required for production environment.'), $this->_('Crypt Information')); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php $this->e('Notification URL'); ?></th>
					<td>
						<input type="text" readonly="readonly" onclick="this.select(0, this.value.length);" value="<?php echo esc_attr(lwp_endpoint('sb-payment')); ?>" class="regular-text" />
						<p class="description">
							<?php $this->e('If Web CVS or PayEasy is enabled, you must set up this URL as notification URL. Otherwise you loose payment status change.'); ?>
						</p>
						<a href="<?php echo lwp_endpoint('sb-payment'); ?>" class="button"><?php $this->e('Check endpoint'); ?></a>
					</td>
				</tr>
				<tr>
					<th valign="top"><?php $this->e('Other Information'); ?></th>
					<td>
						<?php
							printf($this->_('This server\'s IP Address: <code>%s</code><br />'), $_SERVER['SERVER_ADDR']);
							printf($this->_('PHP Mcrypt Extentions: <strong>%s</strong>.'), (function_exists('mcrypt_cbc') ? 'OK' : 'NG'));
							if(!function_exists('mcrypt_cbc')){
								printf('<p class="invalid">%s</p>', $this->_('This server is not ready for productional environment. Please contact to your server admin and ask him to install PHP Mcrypt.'));
							}
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!-- Softbank -->



	<!-- Transfer -->
	<div id="setting-transfer">
		<p class="description">
			<?php $this->e('If you accept transfer, users can pay with bank account or something that is not digital transaction.'); ?>
			<?php $this->e('This helps users, but transactional process has a little bit more complex, because you have to check actual bank account to know whether bank deposit transfer has been made.'); ?>	
			<small>（<?php echo $this->help("transfer", $this->_("More &gt;"))?>）</small>
		</p>
		<hr />
		<table class="form-table">
			<tbody>
				<tr>
					<th valign="top">
						<label><?php $this->e('Accept Transfer'); ?></label>
					</th>
					<td>
						<label><input type="radio" name="transfer" value="0" <?php if(!$this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Disallow'); ?></label><br />
						<label><input type="radio" name="transfer" value="1" <?php if($this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Allow'); ?></label>
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
				<tr>
					<th><label><?php $this->e('Notification Message'); ?></label></th>
					<td>
						<p class="description">
							<?php printf($this->_('You can customize notification message <a href="%s">here</a>.'), admin_url('edit.php?post_type='.$this->notifier->post_type)); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!-- //Transfer -->

</div><!-- //#lwp-tab -->
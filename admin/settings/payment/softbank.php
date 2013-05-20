<?php /* @var $this Literally_WordPress */ ?>
<p class="description">
	<?php $this->e('To use Softbank Payment, you have to contract with SOFTBANK Payment Service corp. and get credential infomation. If Credit Card is enabled, PayPal\'s credit card will be override.'); ?>
</p>
<hr />
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top"><?php $this->e('Status'); ?></th>
			<td>
				<?php if($this->softbank->is_enabled()): ?>
					<p class="valid">
						<?php printf($this->_('This payment method is enabled as %s.'),
								($this->softbank->is_sandbox ? $this->_('Sandbox') : $this->_('Productional environment'))); ?>
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
					<input type="checkbox" name="sb_sandbox" id="sb_sandbox" value="1"<?php if($this->option['sb_sandbox']) echo ' checked="checked"';?> />
					<?php $this->e("This is a develop enviorment and needs pseudo transaction.")?>
				</label>
				<p class="info"><?php $this->e('This payment method is very experimenal. <strong>Test on Sandbox is strongly recommended</strong>. If you find some bug please contact to plugin author at <a href="http://lwper.info">LWPper.info</a>'); ?></p>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php $this->e('Stealth mode'); ?></th>
			<td>
				<label>
					<input type="checkbox" name="sb_stealth" value="1"<?php if($this->softbank->is_stealth) echo ' checked="checked"'; ?> />
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
					<?php $this->e('Checked credit cards are displayed on transaction form. Please check credit cards which you have contracted to use.'); ?>
				</p>
				<p>
					<label>
						<input type="checkbox" value="1" name="sb_save_cc_number"<?php if($this->softbank->save_cc) echo ' checked="checked"'; ?> />
						<?php $this->e('Let users order without inputing credit card number after the 2nd time.'); ?>
					</label>
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
				<p class="invalid"><?php $this->e('Web CVS\'s payment limit must be between 1 and 60.'); ?></p>
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
				<?php if(empty($marchant_id)): ?>
				<p class="invalid"><?php printf($this->_('%s is required.'), $this->_('Marchant ID')); ?></p>
				<?php endif; ?>
				<p class="description">
					<?php printf($this->_('If you are not contracted with Softbank Payment, use <code>%s</code> instead.'), LWP_SB_Payment::SANDBOX_MARCHAND_ID); ?>
				</p>
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
				<?php if(empty($service_id)): ?>
				<p class="invalid"><?php printf($this->_('%s is required.'), $this->_('Service ID')); ?></p>
				<?php endif; ?>
				<p class="description">
					<?php printf($this->_('If you are not contracted with Softbank Payment, use <code>%s</code> instead.'), LWP_SB_Payment::SANDBOX_SERVICE_ID); ?>
				</p>
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
				<?php if(empty($hash_key)): ?>
				<p class="invalid"><?php printf($this->_('%s is required.'), $this->_('Hash Key')); ?></p>
				<?php endif; ?>
				<p class="description">
					<?php printf($this->_('If you are not contracted with Softbank Payment, use <code>%s</code> instead.'), LWP_SB_Payment::SANDBOX_HASH_KEY); ?>
				</p>
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
				<p>
					<?php $this->e('These keys are required for productional environment and will be provided <strong>after contract with SOFTBANK Payment Service corp.</strong>'); ?>
					<?php printf($this->_('Before contracting, use <code>%1$s</code> for %2$s, <code>%3$s</code> for %4$s.'),
							'123456789012345678901234', $this->_('Crypt Key'),
							'00000000', $this->_('IV (Initival Vector)')); ?>
				</p>
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
				<?php if(!preg_match("/^https/", lwp_endpoint('sb-payment'))): ?>
				<p class="invalid">
					<?php $this->e('Softbank Payment requires SSL connection, but Notification URL is not over SSL. It depends on constants <code>FORCE_SSL_LOGIN</code> or <code>FORCE_SSL_ADMIN</code> which is defined in wp-config.php. Of course, your server must be set up properly to be accessible over HTTPS protocol. For more details, see <a href="http://codex.wordpress.org/Administration_Over_SSL">Codex</a>.'); ?>
				</p>
				<?php endif; ?>
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
		<tr>
			<th valign="top"><?php $this->e('Test credit card'); ?></th>
			<td>
				<label>
					<input type="text" onclick="this.select(0, this.value.length);" class="regular-text" readonly="readonly" value="<?php echo LWP_SB_Payment::SANDBOX_CC_NUMBER; ?>" />
					<?php $this->e('Card No.'); ?>
				</label><br />
				<label>
					<input type="text" onclick="this.select(0, this.value.length);" class="regular-text" readonly="readonly" value="<?php echo LWP_SB_Payment::SANDBOX_CC_SEC_CODE; ?>" />
					<?php $this->e('Security Code'); ?>
				</label>
				<p class="description">
					<?php $this->e('Before constact, use this credit card information. After contract you will be provided test card information.'); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
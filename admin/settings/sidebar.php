<div class="lwp-sidebar">
	<dl>
		<dt><?php $this->e('Try my ebook'); ?></dt>
		<dd>
			<a class="banner" href="http://takahashifumiki.com/ebook/kitasenju-social-club/">
				<img src="<?php echo $this->url; ?>/assets/ads/social-club-120x160.jpg" alt="no-photo" width="120" height="160" />
			</a>
			<p><?php $this->e('Kitasenju Social club is plugin author\'s novel. Please buy this ebook only for <strong>&yen;100</strong> to know how this plugin works!'); ?></p>
			<p class="center"><a class="button-primary" href="http://takahashifumiki.com/ebook/kitasenju-social-club/"><?php $this->e('Buy Now'); ?></a></p>
			<br class="clear" />
		</dd>
		<dt><?php $this->e('Contact to payment agency'); ?></dt>
		<dd>
			<p>
				<?php $this->e('Belows are Japanese payment agency and official partner of this plugin. There payment API is available via LWP.'); ?>
			</p>
			<p>
				<a href="http://www.gmo-pg.com" target="_blank"><img src="<?php echo $this->url; ?>assets/ads/gmopg-logo.gif" alt="GMO Payment Gateway inc." width="300" height="52" /></a>
			</p>
			<p>
				<a href="http://www.sbpayment.jp/" target="_blank"><img src="<?php echo $this->url; ?>assets/ads/softbankps-logo.png" alt="SOFTBANK Payment Service corp." width="300" height="40" /></a>
			</p>
			<p class="center"><a class="button-primary" href="#" id="contact-opener"><?php $this->e('Contact'); ?></a></p>
			<div id="lwp-pa-contact" title="<?php $this->e('Contact to Payment Agency'); ?>">
				<?php $user = get_userdata(get_current_user_id()); ?>
				<form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
					<input type="hidden" name="action" value="lwp_contact_payment_agency" />
					<?php wp_nonce_field('lwp_payment_agency_contact'); ?>
					<p class="validate-tips">
						<?php printf($this->_('%s is required.'), '<small class="required">*</small>'); ?>
					</p>
					<table>
						<tr>
							<th>
								<?php $this->e('Agency'); ?>
								<small class="required">*</small>
							</th>
							<td class="agency-container">
								<?php foreach(array(
									'gmo' => 'GMOペイメントゲートウェイ',
									'sb' => 'ソフトバンクペイメントサービス'
								) as $key => $label): ?>
								<label>
									<input type="checkbox" name="agency[]" value="<?php echo $key; ?>" />
									<?php echo esc_html($label); ?>
								</label>
								<?php endforeach; ?>
							</td>
						</tr>
						<tr>
							<th>
								<label for="is_company"><?php $this->e('Legal Entity'); ?></label>
								<small class="required">*</small>
							</th>
							<td>
								<select name="is_company" id="is_company">
									<option value="0"><?php $this->e('Individual'); ?></option>
									<option value="1"><?php $this->e('Coporate'); ?></option>
								</select>
								<label>
									<?php $this->e('(Company Name)'); ?>
									<input class="middle-text" type="text" name="company" value="" placeholder="<?php $this->e('Required for corporative.'); ?>" />
								</label>
							</td>
						</tr>
						<tr>
							<th>
								<label for="user_name"><?php $this->e('Name'); ?></label> / 
								<label for="tel"><?php $this->e('Tel'); ?></label>
								<small class="required">*</small>
							</th>
							<td>
								<input class="middle-text" type="text" name="user_name" id="user_name" value="<?php echo esc_attr($user->display_name); ?>" placeholder="<?php $this->e('Name'); ?>" />
								<input class="middle-text" type="text" name="tel" id="tel" value="<?php echo esc_attr(get_user_meta(get_current_user_id(), 'tel', true)); ?>" placeholder="<?php $this->e('Tel'); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<label for="email"><?php $this->e('Email'); ?></label>
								<small class="required">*</small>
							</th>
							<td>
								<input class="regular-text" type="email" name="email" id="email" value="<?php echo esc_attr($user->user_email);?>" />
							</td>
						</tr>
						<tr>
							<th><label for="url"><?php $this->e('Site to contract'); ?></label></th>
							<td>
								<input class="regular-text" type="text" name="url" id="url" value="<?php echo home_url('/', 'http'); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<label for="marchandise">
									<?php $this->e('Marchandise'); ?> / <?php $this->e('Estimated Sales'); ?>
								</label>
								<small class="required">*</small>
							</th>
							<td>
								<select name="marchandise" id="marchandise">
									<option value=""><?php $this->e('Please Select'); ?></option>
									<?php foreach(array(
										$this->_('Digital Contents'),
										$this->_('Event ticket'),
										//$this->_('Real Product Sales'),
										//$this->_('Adaptive Payment'),
										$this->_('Other')
									) as $val): ?>
									<option value="<?php echo esc_attr($val); ?>"><?php echo esc_html($val); ?></option>
									<?php endforeach; ?>
								</select>
								
								<select name="sales" id="sales">
									<?php
										$char = 10000;
										for($i = 0; $i < 4; $i++){
											printf('<option value="%d">%s</opton>', $char,
													sprintf(($i < 3 ? '~&yen;%s' : '&yen;%s~'), number_format($char)));
											$char *= 10;
										}
									?>
								</select> / <?php $this->e('Month'); ?>
							</td>
						</tr>
						<tr>
							<th>
								<?php $this->e('Payment Method'); ?>
								<small class="required">*</small>
							</th>
							<td class="method-container">
								<?php
									foreach(array(
										'cc' => $this->_(LWP_Payment_Methods::GMO_CC),
										'cvs' => $this->_(LWP_Payment_Methods::GMO_WEB_CVS),
										'payeasy' => $this->_(LWP_Payment_Methods::GMO_PAYEASY)
									) as $key => $method):
								?>
								<label>
									<input type="checkbox" name="method[]" value="<?php echo esc_attr($method); ?>" />
									<?php echo esc_html($method); ?>
								</label>
								<?php endforeach; ?>
							</td>
						</tr>
						<tr>
							<th><label for="misc"><?php $this->e('Miselaneous'); ?></th>
							<td>
								<textarea name="misc" rows="3" style="width: 90%;" id="misc" placeholder="<?php $this->e('Please enter questions or notices.'); ?>"></textarea>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</dd>
		<dt><?php $this->e('Create PayPal Account'); ?></dt>
		<dd>
			<?php $this->e('First of all, try to get PayPal <strong>buisiness account</strong> <a href="http://www.paypal.com" target="_blank">here</a>.'); ?>
			<p class="center">
				<a class="paypal" href="http://paypal.com" target="_blank">PayPal</a>
			</p>
		</dd>
		<dt><?php $this->e('Please visit LWPer.info');?></dt>
		<dd>
			<p class="center"><a href="http://lwper.info" target="_blank"><img src="<?php echo $this->url; ?>assets/ads/lwper-info.png" alt="LWPer.info" width="300" height="78" /></a></p>
			<p><?php $this->e('<a href="http://lwper.info">LWPer.info</a> is the only information site for LWP user.'); ?></p>
		</dd>
	</dl>
</div>

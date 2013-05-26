<?php /* @var $this LWP_Form */ ?>
<?php /* @var $lwp Literally_WordPress */ ?>
<?php global $lwp; ?>
<table class="price-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Item'); ?></th>
			<th class="price" scope="col"><?php $this->e('Price'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="row"><?php $this->e('Total'); ?></th>
			<td class="price"><?php echo number_format_i18n($total_price)." ".lwp_currency_code(); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php foreach($items as $id => $item): ?>
		<tr>
			<th scope="row">
				<?php printf('%s x %s', esc_html($item), $quantities[$id]); ?>
			</th>
			<td class="price">
				<?php echo number_format_i18n($prices[$id])." ".lwp_currency_code(); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<p class="message notice"><?php
	printf($this->_("Please enter payment information for %s"), LWP_Payment_Methods::get_label($method));
?></p>

<?php if(!empty($error)): ?>
<p class="message error"><?php echo implode('<br />', array_map('esc_html', $error)); ?></p>
<?php endif; ?>

<p class="required">
	<span class="required">*</span> = <?php $this->e('Required'); ?>
</p>

<form method="post" action="<?php echo $action; ?>">
	<input type="hidden" name="lwp-id" value="<?php echo esc_attr($post_id); ?>" />
	<input type="hidden" name="lwp-method" value="<?php echo esc_attr($method); ?>" />
	<table class="form-table">
		<tbody>
			<?php switch($method): case 'sb-cc': case 'gmo-cc': ?>
			<?php
				switch($method){
					case 'gmo-cc':
						$save_cc = $lwp->gmo->save_cc;
						$cc_info = array();
						$same_card = false;
						break;
					case 'sb-cc':
						$save_cc = $lwp->softbank->save_cc;
						$cc_info = $lwp->softbank->get_cc_information(get_current_user_id());
						$same_card = !empty($cc_info) && !isset($_REQUEST['newcard']);
						break;
				}
			?>
			<tr>
				<th>
					<?php wp_nonce_field('lwp_payment_'.($method == 'gmo-cc' ? 'gmo' : 'sb').'_cc'); ?>
					<label for="cc_number">
						<?php $this->e('Card No.'); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if($same_card): ?>
						<input type="hidden" name="same_card" value="1" />
						<strong><?php echo esc_html($cc_info['number']); ?></strong>
						<p class="description">
							<?php printf($this->_('This is same number as previous order. To order with different card, click <a href="%s">here</a>.'), lwp_endpoint('payment', array('lwp-method' => $method, 'lwp-id' => $post_id, 'newcard' => 'true'))); ?>
						</p>
					<?php else: ?>
						<input type="text" class="middle-text" name="cc_number" id="cc_number" value="<?php if(isset($vars['cc_number'])) echo esc_attr($vars['cc_number']); ?>" placeholder="ex. 0123456789123" />
						<?php
							if($save_cc):
						?>
							<p>
								<label>
									<input type="checkbox" value="1" name="save_cc_number" checked="checked" />
									<?php $this->e('Order without credit card number next time'); ?>
								</label>
							</p>
							<p class="description">
								<?php $this->e('This informatin will be saved on Payment Agency and <strong>never</strong> on this site. '); ?>
							</p>
						<?php endif; ?>
						<p>
							<?php $this->e("You can pay with Credit Cards below.");?><br />
							<?php
								$cards = ($method == 'gmo-cc') ? $lwp->gmo->get_available_cards() : $lwp->softbank->get_available_cards();
								foreach($cards as $card):
							?>
								<i class="lwp-cc-small-icon small-icon-<?php echo $card; ?>"></i>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="cc_expiration">
						<?php $this->e('Expiration'); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php if($same_card): ?>
					<strong><?php echo mysql2date($this->_('M, Y'), $cc_info['expiration']); ?></strong>
					<?php else: ?>
						<select name="cc_month" id="cc_month">
							<?php for($i = 0; $i < 12; $i++): ?>
							<option name="<?php echo $i + 1; ?>"<?php if(isset($vars['cc_month']) && $vars['cc_month'] == $i + 1) echo ' selected="selected"'; ?>>
								<?php printf("%02d", $i + 1); ?>
							</option>
							<?php endfor; ?>
						</select>&nbsp;/&nbsp;
						<select name="cc_year" id="cc_year">
							<?php
								$this_year = date('Y');
								for($i = 0; $i < 10; $i++):
							?>
							<option value="<?php echo $this_year + $i; ?>"<?php if(isset($vars['cc_year']) && $vars['cc_year'] == $this_year + 1) echo ' selected="selected"'; ?>>
								<?php echo $this_year + $i; ?>
							</option>
							<?php endfor; ?>
						</select>
						<span class="description">
							<?php $this->e('(Month / Year)'); ?>
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<?php if(!$same_card): ?>
			<tr>
				<th>
					<label for="cc_sec">
						<?php $this->e('Security Code'); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<input type="text" name="cc_sec" class="small-text" id="cc_sec" value="<?php if(isset($vars['cc_sec'])) echo esc_attr($vars['cc_sec']); ?>" placeholder="ex. 123" />
					<p class="description">
						<?php $this->e('Security code is 3 or 4 digits written near the card number on the credit card.'); ?>
					</p>
					<img src="<?php echo $lwp->url; ?>assets/security-code.png" alt="Where the security code is" width="247" height="80" />
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th><?php $this->e('Dealing Type'); ?></th>
				<td><?php $this->e('At once'); ?></td>
			</tr>
			<?php ?>
			<?php break; case 'sb-cvs': case 'sb-payeasy': case 'gmo-cvs':  case 'gmo-payeasy': ?>
			<?php if(false !== array_search($method, array('sb-cvs', 'gmo-cvs'))): ?>
			<tr>
				<th>
					<?php $this->e('CVS'); ?>
					<span class="required">*</span>
				</th>
				<td>
					<?php
						$cvss = ($method == 'gmo-cvs') ? $lwp->gmo->get_available_cvs() : $lwp->softbank->get_available_cvs();
						foreach($cvss as $cvs):
					?>
					<label class="cvs-container">
						<input type="radio" name="cvs-name" value="<?php echo $cvs; ?>"<?php if(isset($vars['cvs']) && $vars['cvs'] == $cvs) echo ' checked="checked"'; ?> /><br />
						<i class="lwp-cvs-small-icon small-icon-<?php echo $cvs; ?>"></i><br />
						<?php echo $lwp->softbank->get_verbose_name($cvs); ?>
					</label>
					<?php endforeach; ?>
					<p style="clear: both;">
						<?php $this->e('Payment flow is different with eacn CVS. For more detail, see the instruction on next page.'); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
			<?php if(false !== array_search($method, array('sb-payeasy', 'gmo-payeasy'))): ?>
			<tr>
				<th><?php $this->e('PayEasy'); ?></th>
				<td>
					<i class="lwp-cc-icon icon-payeasy"></i>
					<p class="description">
						<?php $this->e('You can pay from your bank account via PayEasy.'); ?>
					</p>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th><?php $this->e('Payment Limit'); ?></th>
				<td>
					<?php
						switch($method){
							case 'sb-payeasy':
								$suffix = 'sb_payeasy';
								break;
							case 'sb-cvs':
								$suffix = 'sb_cvs';
								break;
							case 'gmo-cvs':
								$suffix = 'gmo_cvs';
								break;
							case 'gmo-payeasy':
								$suffix = 'gmo_payeasy';
								break;
						}
						wp_nonce_field('lwp_payment_'.($suffix));
					?>
					<?php printf($this->_('Please finish payment by <strong>%s</strong>.'), $vars['limit']); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php $this->e('Name'); ?>
					<span class="required">*</span>
				</th>
				<td>
					<table class="name-table">
						<tbody>
							<tr>
								<td>
									<label>
										<input type="text" class="small-text" name="last_name_kana" value="<?php echo esc_attr($vars['last_name_kana']); ?>" placeholder="ヤマダ" />
										<?php $this->e('Last Name Kana'); ?>
									</label>
								</td>
								<td>
									<label>
										<input type="text" class="small-text" name="first_name_kana" value="<?php echo esc_attr($vars['first_name_kana']); ?>" placeholder="ハナコ" />
										<?php $this->e('First Name Kana'); ?>
									</label>
								</td>
							</tr>
							<tr>
								<td>
									<label>
										<input type="text" class="small-text" name="last_name" value="<?php echo esc_attr($vars['last_name']); ?>" placeholder="山田" />
										<?php $this->e('Last Name'); ?>
									</label>
								</td>
								<td>
									<label>
										<input type="text" class="small-text" name="first_name" value="<?php echo esc_attr($vars['first_name']); ?>" placeholder="花子" />
										<?php $this->e('First Name'); ?>
									</label>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php if(false !== array_search($method, array('sb-payeasy', 'sb-cvs'))): ?>
			<tr>
				<th>
					<?php $this->e('Address'); ?>
					<span class="required">*</span>
				</th>
				<td>
					<label>
						<input type="text" class="small-text" name="zipcode" value="<?php echo esc_attr($vars['zipcode']); ?>" placeholder="1000001" />
						<?php $this->e('Zip Code') ?>
					</label>
					<?php $this->e('(Only digits)'); ?>
					<?php if(function_exists('zip_search_button')) zip_search_button(); ?>
					<br />
					<label>
						<select name="prefecture">
							<?php foreach(LWP_Address_JP::get_pref_group() as $region => $prefs): ?>
							<optgroup label="<?php echo $region.'地方'; ?>">
								<?php foreach($prefs as $pref): ?>
								<option value="<?php echo $pref; ?>"<?php if($pref == $vars['prefecture']) echo ' selected="selected"'; ?>><?php echo $pref; ?></option>
								<?php endforeach;?>
							</optgroup>
							<?php endforeach; ?>
						</select>
						<?php $this->e('Prefecture'); ?>
					</label>
					<br />
					<label>
						<input type="text" name="city" value="<?php echo esc_attr($vars['city']); ?>" placeholder="千代田区" class="middle-text" />
						<?php $this->e('City'); ?>
					</label>
					<br />
					<label>
						<input type="text" name="street" value="<?php echo esc_attr($vars['street']); ?>" placeholder="千代田1番1号" class="regular-text" />
						<?php $this->e('Street'); ?>
					</label>
					<br />
					<label>
						<input type="text" name="office" value="<?php echo esc_attr($vars['office']); ?>" placeholder="千代田ビル4F" class="regular-text" />
						<?php $this->e('Building, Room No.'); ?>
						<?php $this->e(' (Option)'); ?>
					</label>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th>
					<label for="tel"><?php $this->e('Tel'); ?></label>
					<span class="required">*</span>
				</th>
				<td>
					<input type="text" class="middle-text" name="tel" id="tel" value="<?php echo esc_attr($vars['tel']); ?>" placeholder="ex. 0312345678" />
					<?php $this->e('Only digits.'); ?>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<label>
						<input type="checkbox" name="save_info" value="1" checked="checked" />
						<?php $this->e('Save these information'); ?>
					</label>
				</td>
			</tr>
			<?php break; endswitch; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Checkout &raquo;'); ?>" />
	</p>
</form>

<p>
	<a class="button" href="<?php echo $link; ?>"><?php $this->e("Return"); ?></a>
</p>
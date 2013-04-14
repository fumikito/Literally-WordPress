<?php /* @var $this LWP_Form */?>
<?php /* @var $lwp Literally_WordPress */ global $lwp; ?>
<form method="get" action="<?php echo lwp_endpoint('buy'); ?>" id="lwp-payment-cart">
	<input type="hidden" name="lwp" value="buy" />
	<input type="hidden" name="lwp-id" value="<?php echo $post_id; ?>" />
	<table class="price-table">
		<caption><?php $this->e('Order Detail'); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php $this->e('Item'); ?></th>
				<th scope="col"><?php $this->e('@'); ?></th>
				<th scope="col"><?php $this->e('Quantity'); ?></th>
				<th scope="col">&nbsp;</th>
				<th class="price" scope="col"><?php $this->e('Subtotal'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="recalculate" colspan="3">
					<?php
						$can_select = false;
						foreach($selectable as $s){
							if($s){
								$can_select = true;
								break;
							}
						}
						if($can_select): 
					?>
						<input class="button button-calculate" type="submit" value="<?php $this->e('Recalculate'); ?>" /><br />
						<span class="description"><?php $this->e('If you change quantity, click recalculate.'); ?></span>
					<?php else: ?>
						&nbsp;
					<?php endif; ?>
				</td>
				<th scope="row"><?php $this->e('Total'); ?></th>
				<td class="price"><?php echo number_format_i18n($total_price)." ".lwp_currency_code(); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach($items as $id => $item): ?>
			<tr>
				<th scope="row">
					<?php echo apply_filters('lwp_cart_product_title', esc_html($item), $id); ?>
				</th>
				<td>
					<?php echo number_format_i18n($unit_prices[$id])." ".lwp_currency_code(); ?>
				</td>
				<td class="quantity">
					<?php if($selectable[$id]): ?>
						<input type="hidden" class="current_quantity" value="<?php echo $quantities[$id]; ?>" />
						<select class="quantity-changer" name="quantity[<?php echo $id; ?>]">
							<?php foreach(lwp_option_steps($max_quantities[$id]) as $q): ?>
							<option value="<?php echo $q; ?>"<?php if($q == $quantities[$id]) echo ' selected="selected"';?>>
								<?php echo number_format($q); ?>
							</option>
							<?php endforeach; ?>
						</select>
					<?php else: ?>
						<?php printf('<span>%s</span>', $quantities[$id]); ?>
					<?php endif; ?>
				</td>
				<td class="misc"><?php do_action('lwp_cart_row_desc', '', $id, $prices[$id], $quantities[$id]); ?></td>
				<td class="price">
					<?php echo number_format_i18n($prices[$id])." ".lwp_currency_code(); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>

<p class="message notice">
	<?php $this->e('Please select payment method below.'); ?>
</p>

	
<form method="post" action="<?php echo lwp_endpoint('buy'); ?>" id="lwp-payment-method-form">
	<input type="hidden" name="lwp" value="buy" />
	<input type="hidden" name="lwp-id" value="<?php echo $post_id; ?>" />
	<?php foreach($quantities as $id => $quantity): ?>
	<input type="hidden" name="quantity[<?php echo $id; ?>]" value="<?php echo $quantity ?>" />
	<?php endforeach; ?>
	<?php wp_nonce_field('lwp_buynow', '_wpnonce', false); ?>
	<table class="form-table lwp-method-table">
		<caption><?php $this->e('Payment Method'); ?></caption>
		<tbody>
			<?php
				//--------------------------
				//
				// PayaPal
				//
				//--------------------------
				if($lwp->is_paypal_enbaled()):
			?>
			<tr>
				<th class="lwp-column-method">
					<label>
						<i class="lwp-cc-icon icon-paypal"></i><br />
						<input checked="checked" type="radio" name="lwp-method" value="paypal" />
						<?php $this->e("PayPal"); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					<?php if($lwp->option['sandbox']): ?>
						<p class="sandbox"><?php $this->e('Sandbox'); ?></p>
					<?php endif; ?>
					<?php $this->e("You can pay with PayPal account.");?><br />
					<small>
						<strong><?php $this->e('Note:');  ?></strong><br />
						<?php $this->e('Clicking \'Next\', You will be redirect to PayPal web site. Logging in PayPal, you will be redirected to this site again. And then, by confirming payment on this site, your transaction will be complete.'); ?>
					</small>
					<?php if($lwp->show_payment_agency()): ?>
						<p class="vender">
							<span><?php printf($this->_('Payment Agency: %s'), 'PayPal'); ?></span>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<?php
				//--------------------------
				//
				// If PayaPal is only method for Credit cards
				//
				//--------------------------
				if(!($lwp->gmo->is_cc_enabled() && $lwp->gmo->stealth_check()) && !($lwp->softbank->is_cc_enabled() && $lwp->softbank->stealth_check())):
			?>
			<tr>
				<th class="lwp-column-method">
					<label>
						<i class="lwp-cc-icon icon-creditcard"></i><br />
						<input type="radio" name="lwp-method" value="cc" />
						<?php $this->e("Credit Card"); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					<?php if($lwp->option['sandbox']): ?>
						<p class="sandbox"><?php $this->e('Sandbox'); ?></p>
					<?php endif; ?>
					<?php $this->e("You can pay with credit cards below via PayPal.");?><br />
					<?php foreach(PayPal_Statics::get_available_cards($lwp->option['country_code']) as $card): ?>
						<i class="lwp-cc-small-icon small-icon-<?php echo $card; ?>"></i>
					<?php endforeach; ?>
					<br />
					<small>
						<strong><?php $this->e('Note:');  ?></strong><br />
						<?php $this->e('Clicking \'Next\', You will be redirect to PayPal web site. Entering CC number, you will be redirected to this site again. And then, by confirming payment on this site, your transaction will be complete.'); ?>
					</small>
					<?php if($lwp->show_payment_agency()): ?>
						<p class="vender">
							<span><?php printf($this->_('Payment Agency: %s'), 'PayPal'); ?></span>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<?php
				endif; //PayPal CC
				endif; //PayPal
			?>
			<?php
				//--------------------------
				//
				// Credit Cards
				//
				//--------------------------
				foreach(array(
					'gmo' => ($lwp->gmo->is_cc_enabled() && $lwp->gmo->stealth_check()),
					'sb' => ($lwp->softbank->is_cc_enabled() && $lwp->softbank->stealth_check())
				) as $vender => $available):
					if(!$available){
						continue;
					}
					switch($vender){
						case 'gmo':
							$cards = $lwp->gmo->get_available_cards();
							$vendor_name = $lwp->gmo->vendor_name();
							$sandbox = $lwp->gmo->is_sandbox;
							break;
						case 'sb':
							$cards = $lwp->softbank->get_available_cards();
							$vendor_name = $lwp->softbank->vendor_name();
							$sandbox = $lwp->softbank->is_sandbox;
							break;
					}
			?>
			<tr>
				<th class="lwp-column-method">
					<label>
						<i class="lwp-cc-icon icon-creditcard"></i><br />
						<input type="radio" name="lwp-method" value="<?php echo $vender.'-cc'; ?>" />
						<?php $this->e("Credit Card"); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					<?php if($sandbox): ?>
						<p class="sandbox"><?php $this->e('Sandbox'); ?></p>
					<?php endif; ?>

					<?php $this->e("You can pay with Credit Cards below.");?><br />
					<?php foreach($cards as $card): ?>
						<i class="lwp-cc-small-icon small-icon-<?php echo $card; ?>"></i>
					<?php endforeach; ?>
					<br />
					<small>
						<strong><?php $this->e('Note:');  ?></strong><br />
						<?php $this->e('Clicking \'Next\', you will enter credit card infomation form.'); ?>
					</small>
					<?php if($lwp->show_payment_agency()): ?>
						<p class="vender">
							<span><?php printf($this->_('Payment Agency: %s'), $vendor_name); ?></span>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php
				//--------------------------
				//
				// Web CVS
				//
				//--------------------------
				foreach(array(
					'gmo' => ( $lwp->gmo->is_cvs_enabled() && $lwp->gmo->stealth_check()),
					'sb' => ( $lwp->softbank->is_cvs_enabled() && $lwp->softbank->stealth_check())
				) as $vender => $available):
					if(!$available){
						continue;
					}
					switch($vender){
						case 'gmo':
							$cvss = $lwp->gmo->get_available_cvs();
							$selectable = $lwp->gmo->can_pay_with($post_id, 'gmo-cvs');
							$vendor_name = $lwp->gmo->vendor_name();
							$sandbox = $lwp->gmo->is_sandbox;
							break;
						case 'sb':
							$cvss = $lwp->softbank->get_available_cvs();
							$vendor_name = $lwp->softbank->vendor_name();
							$selectable = $lwp->softbank->can_pay_with($post_id, 'sb-cvs');
							$sandbox = $lwp->softbank->is_sandbox;
							break;
					}
			?>
			<tr<?php if(!$selectable) echo ' class="disabled"'; ?>>
				<th class="lwp-column-method">
					<label>
						<i class="lwp-cc-icon icon-cvs"></i><br />
						<input type="radio" name="lwp-method" value="<?php echo $vender; ?>-cvs"<?php if(!$selectable) echo ' disabled="disabled"';?> />
						<?php $this->e("Web CVS"); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					<?php if($sandbox): ?>
						<p class="sandbox"><?php $this->e('Sandbox'); ?></p>
					<?php endif; ?>
					<?php if($selectable): ?>
					<?php $this->e('You can pay at CVS below.'); ?><br />
					<?php else: ?>
					<p class="invalid"><?php printf($this->_('You can\'t select %s because selling limit is today.'), $this->_('Web CVS')); ?></p>
					<?php endif; ?>
					<?php foreach($cvss as $cvs): ?>
						<i class="lwp-cvs-small-icon small-icon-<?php echo $cvs; ?>"></i>
					<?php endforeach; ?>
					<br />
					<small>
						<strong><?php $this->e('Note:');  ?></strong><br />
						<?php $this->e('To finish transaction, you should follow the instruction on next step.'); ?>
					</small>
					<?php if($lwp->show_payment_agency()): ?>
						<p class="vender">
							<span><?php printf($this->_('Payment Agency: %s'), $vendor_name); ?></span>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach;?>
			<?php
				//--------------------------
				//
				// PayEasy
				//
				//--------------------------
				foreach(array(
					'gmo' => ( $lwp->gmo->payeasy && $lwp->gmo->stealth_check()),
					'sb' => ( $lwp->softbank->payeasy && $lwp->softbank->stealth_check())
				) as $vender => $available):
					if(!$available){
						continue;
					}
					switch($vender){
						case 'gmo':
							$selectable = $lwp->gmo->can_pay_with($post_id, 'gmo-payeasy');
							$vendor_name = $lwp->gmo->vendor_name();
							$sandbox = $lwp->gmo->is_sandbox;
							$limit = $lwp->gmo->payeasy_limit;
							break;
						case 'sb':
							$selectable = $lwp->softbank->can_pay_with($post_id, 'sb-payeasy');
							$vendor_name = $lwp->softbank->vendor_name();
							$limit = $lwp->softbank->payeasy_limit;
							$sandbox = $lwp->softbank->is_sandbox;
							break;
					}
			?>
			<tr<?php if(!$selectable) echo ' class="disabled"'; ?>>
				<th class="lwp-column-method">
					<label>
						<i class="lwp-cc-icon icon-payeasy"></i><br />
						<input type="radio" name="lwp-method" value="<?php echo $vender; ?>-payeasy" <?php if(!$selectable) echo ' disabled="disabled"';?> />
						<?php $this->e("PayEasy"); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					<?php if($sandbox): ?>
						<p class="sandbox"><?php $this->e('Sandbox'); ?></p>
					<?php endif; ?>
					<?php if($selectable): ?>
						<?php $this->e('You can pay from your bank account via PayEasy.'); ?><br />
					<?php else: ?>
						<p class="invalid"><?php printf($this->_('You can\'t select %1$s because %1$s can be available no later than %2$d days before the selling limit.'), 'PayEasy', $limit); ?></p>
					<?php endif; ?>
					<small>
						<strong><?php $this->e('Note:');  ?></strong><br />
						<?php $this->e('To finish transaction, you should follow the instruction on next step.'); ?>
					</small>
					<?php if($lwp->show_payment_agency()): ?>
						<p class="vender">
							<span><?php printf($this->_('Payment Agency: %s'), $vendor_name); ?></span>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php
				//--------------------------
				//
				// Transfer
				//
				//--------------------------
				if($lwp->notifier->is_enabled()):
			?>
			<tr>
				<th class="lwp-column-method">
					<label>
						<i class="lwp-cc-icon icon-transfer"></i><br />
						<input type="radio" name="lwp-method" value="transfer" />
						<?php $this->e("Transfer"); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					<?php $this->e('You can pay through specified bank account. The account will be displayed on next page.'); ?><br />
					<small>
						<strong><?php $this->e('Note:');  ?></strong><br />
						<?php $this->e('Transaction will not have been complete, unless you will send deposit to the specified bank account. This means you can\'t get contents immediately.'); ?>
					</small>
				</td>
			</tr>
			<?php endif; ?>
			
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Next &raquo;'); ?>" />
	</p>
</form>
<p class="cancel">
	<a class="button" href="#" onclick="window.history.back(); return false;"><?php $this->e("Cancel"); ?></a>
</p>
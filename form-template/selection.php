<?php /* @var $this LWP_Form */?>
<?php /* @var $lwp Literally_WordPress */ global $lwp; ?>
<form method="get" action="<?php echo lwp_endpoint('buy'); ?>" id="lwp-payment-cart">
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
	<input type="hidden" name="lwp-id" value="<?php echo $post_id; ?>" />
	<?php foreach($quantities as $id => $quantity): ?>
	<input type="hidden" name="quantity[<?php echo $id; ?>]" value="<?php echo $quantity ?>" />
	<?php endforeach; ?>
	<?php wp_nonce_field('lwp_buynow', '_wpnonce', false); ?>
	<table class="form-table lwp-method-table">
		<caption><?php $this->e('Payment Method'); ?></caption>
		<tbody>
			<?php foreach($payments as $payment): ?>
			<?php if($payment['stealth'] && !current_user_can('manage_options')):
				continue;
			endif; ?>
			<tr>
				<th class="lwp-column-method">
					<label>
						<i class="lwp-cc-icon icon-<?php echo $payment['icon']; ?>"></i><br />
						<input checked="checked" type="radio" name="lwp-method" value="<?php echo $payment['slug'] ?>"<?php if(!$payment['selectable']) echo ' disabled="disabled"';?> />
						<?php echo esc_html($payment['label']); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					
					<?php if($payment['sandbox']): ?>
						<p class="sandbox"><?php $this->e('Sandbox'); ?></p>
					<?php endif; ?>
					
					<?php if($payment['selectable']): ?>
						<?php echo esc_html($payment['title']); ?><br />
					<?php else: ?>
						<p class="invalid"><?php printf($this->_('You can\'t select %s because selling limit for off-line transaction is outdated.'), $payment['label']); ?></p>
					<?php endif; ?>
					
					<?php if(!empty($payment['cc'])): ?>
						<?php foreach($payment['cc'] as $card): ?>
							<i class="lwp-cc-small-icon small-icon-<?php echo $card; ?>"></i>
						<?php endforeach; ?>
						<br />
					<?php endif; ?>
					
					<?php if(!empty($payment['cvs'])): ?>
						<?php foreach($payment['cvs'] as $cvs): ?>
							<i class="lwp-cvs-small-icon small-icon-<?php echo $cvs; ?>"></i>
						<?php endforeach; ?>
						<br />
					<?php endif; ?>
					
					<small>
						<strong><?php $this->e('Description:');  ?></strong><br />
						<?php echo wp_kses($payment['description'], array('a' => array('href' => array(), 'target' => array()))); ?>
					</small>
						
					<?php if($lwp->show_payment_agency() && !empty($payment['vendor'])): ?>
						<p class="vender">
							<span><?php printf($this->_('Payment Agency: %s'), $payment['vendor']); ?></span>
						</p>
					<?php endif; ?>
						
					<?php if($payment['stealth']): ?>
						<p class="info"><?php $this->e('This payment method is on stealth mode. Only administrator can see this payment.'); ?></p>
					<?php endif; ?>
						
				</td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Next &raquo;'); ?>" />
	</p>
</form>
<p class="cancel">
	<a class="button" href="#" onclick="window.history.back(); return false;"><?php $this->e("Cancel"); ?></a>
</p>
<?php /* @var $this LWP_Form */?>
<?php /* @var $lwp Literally_WordPress */ global $lwp; ?>
<form method="get" action="<?php echo lwp_endpoint('buy'); ?>" id="lwp-payment-cart">
	<?php foreach($products as $product): ?>
	<input type="hidden" name="lwp-id" value="<?php echo esc_attr($product->ID); ?>" />
	<?php endforeach; ?>
	<?php $this->the_price_list($products, true); ?>
</form>

<?php if(empty($payments)): ?>
<p class="message error">
	<?php $this->e('Sorry, but there is no payment method available.'); ?>
</p>


<?php else: ?>
<p class="message notice">
	<?php $this->e('Please select payment method below.'); ?>
</p>
	
<form method="post" action="<?php echo lwp_endpoint('buy'); ?>" id="lwp-payment-method-form">
	<?php foreach($products as $product): ?>
	<input type="hidden" name="lwp-id" value="<?php echo esc_attr($product->ID); ?>" />
	<input type="hidden" name="quantity[<?php echo esc_attr($product->ID); ?>]" value="<?php echo esc_attr($this->get_current_quantity($product)); ?>" />
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
<?php endif; ?>

<p class="cancel">
	<a class="button" href="#" onclick="window.history.back(); return false;"><?php $this->e("Cancel"); ?></a>
</p>
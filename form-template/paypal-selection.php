<?php /* @var $this Literally_WordPress */?>
<p class="message notice">
	<?php printf($this->_('You are about to buy <strong>%1$s</strong> in <strong>%2$s</strong>. Please select payment method below.'), $item, number_format($price).'('.$this->option['currency_code'].')' ); ?>
</p>
<form method="get" action="<?php echo lwp_endpoint('buy'); ?>">
	<input type="hidden" name="lwp" value="buy" />
	<input type="hidden" name="lwp-id" value="<?php echo $post_id; ?>" />
	<?php wp_nonce_field('lwp_buynow', '_wpnonce', false); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th class="lwp-column-method">
					<label>
						<img src="<?php echo $this->url; ?>/assets/icon-paypal.png" width="130" height="80" alt="PayPal"><br />
						<input checked="checked" type="radio" name="lwp-method" value="paypal" />
						<?php $this->e("PayPal"); ?>
					</label>
				</th>
				<td class="lwp-column-method-desc">
					<?php $this->e("You can pay with PayPal account or various credit cards.");?><br />
					<small>
						<strong><?php $this->e('Note:');  ?></strong><br />
						<?php $this->e('Clicking \'Next\', You will be redirect to PayPal web site. Logging in PayPal or Enter CC number, you will be redirected to this site again. And then, by confirming payment on this site, your transaction will be complete.'); ?>
					</small>
				</td>
			</tr>
		<?php if($this->option['transfer']): ?>
			<tr>
				<th class="lwp-column-method">
					<label>
						<img src="<?php echo $this->url; ?>/assets/icon-cash.png" width="130" height="80" alt="<?php $this->e('Transfer'); ?>"><br />
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
<p>
	<a class="button" href="#" onclick="window.history.back(); return false;"><?php $this->e("Cancel"); ?></a>
</p>
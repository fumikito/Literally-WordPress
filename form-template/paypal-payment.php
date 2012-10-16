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

<p class="message notice">
	<?php $this->e("Please enter payment information"); ?>
</p>

<?php if(!empty($error)): ?>
<p class="message error"><?php echo implode('<br />', array_map('esc_html', $error)); ?></p>
<?php endif; ?>

<form method="post" action="<?php echo $action; ?>">
	<input type="hidden" name="lwp-id" value="<?php echo esc_attr($post_id); ?>" />
	<input type="hidden" name="lwp-method" value="<?php echo esc_attr($method); ?>" />
	<table class="form-table">
		<tbody>
			<?php switch($method): case 'sb-cc': ?>
			<tr>
				<th>
					<?php wp_nonce_field('lwp_payment_sb_cc'); ?>
					<label for="cc_number"><?php $this->e('Card No.'); ?></label>
				</th>
				<td>
					<input type="text" class="middle-text" name="cc_number" id="cc_number" value="<?php if(isset($vars['cc_number'])) echo esc_attr($vars['cc_number']); ?>" placeholder="ex. 0123456789123" />
					<p class="description">
						<?php $this->e('This informatin will <strong>never</strong> be saved on this site. '); ?>
						<?php $this->e("You can pay with Credit Cards below.");?><br />
						<?php foreach($lwp->softbank->get_available_cards() as $card): ?>
							<i class="lwp-cc-small-icon small-icon-<?php echo $card; ?>"></i>
						<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th><label for="cc_expiration"><?php $this->e('Expiration'); ?></label></th>
				<td>
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
				</td>
			</tr>
			<tr>
				<th><label for="cc_sec"><?php $this->e('Security Code'); ?></label></th>
				<td>
					<input type="text" name="cc_sec" class="small-text" id="cc_sec" value="<?php if(isset($vars['cc_sec'])) echo esc_attr($vars['cc_sec']); ?>" placeholder="ex. 123" />
					<p class="description">
						<?php $this->e('Security code is 3 or 4 digits written near the card number on the credit card.'); ?>
					</p>
					<img src="<?php echo $lwp->url; ?>assets/security-code.png" alt="Where the security code is" width="247" height="80" />
				</td>
			</tr>
			<tr>
				<th><?php $this->e('Dealing Type'); ?></th>
				<td><?php $this->e('At once'); ?></td>
			</tr>
			<?php break; case 'cvs':?>
			
			<?php break; case 'payeasy': ?>
			
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
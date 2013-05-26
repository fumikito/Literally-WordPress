<p class="message notice">
	<?php $this->e("Please confirm the invoice below and accept payment.");?>
</p>

	
<form method="post">
	<?php wp_nonce_field("lwp_confirm"); ?>
	<input type="hidden" name="TOKEN" value="<?php echo $info['TOKEN']; ?>" />
	<input type="hidden" name="PAYERID" value="<?php echo $info['PAYERID']; ?>" />
	<input type="hidden" name="AMT" value="<?php echo $info['AMT']; ?>" />
	<input type="hidden" name="CURRENCYCODE" value="<?php echo $this->option['currency_code']; ?>" />
	<input type="hidden" name="INVNUM" value="<?php echo $info['INVNUM']; ?>" />
	<input type="hidden" name="EMAIL" value="<?php echo $info['EMAIL']; ?>" />
	
	<table class="form-table">
		<caption><?php $this->e('Customer information'); ?></caption>
		<tbody>
			<tr>
				<th>
					<?php $this->e("Invoice No."); ?>
				</th>
				<td>
					<?php echo $info["INVNUM"]; ?>
				</td>
			</tr>
			<tr>
				<th><?php $this->e("Name"); ?></th>
				<td>
					<?php
						echo get_locale() == 'ja'
							? $info["LASTNAME"]." ".$info["FIRSTNAME"]
							: $info["FIRSTNAME"]." ".$info["LASTNAME"];
					?>
				</td>
			</tr>
			<tr>
				<th><?php $this->e('Mail'); ?></th>
				<td>
					<?php echo $info["EMAIL"]; ?><br /> 
					<small class="description"><?php $this->e('This e-mail address has been entered on PayPal by you.') ?></small>
				</td>
			</tr>
		</tbody>
	</table>
	
	<table class="price-table">
		<caption><?php $this->e('Order detail'); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php $this->e('Item'); ?></th>
				<th scope="col"><?php $this->e('Quantity'); ?></th>
				<th scope="col">&nbsp;</th>
				<th class="price" scope="col"><?php $this->e('Subtotal'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="recalculate">&nbsp;</td>
				<th scope="col">&nbsp;</th>
				<th scope="row"><?php $this->e('Total'); ?></th>
				<td class="price"><?php echo number_format_i18n($transaction->price)." ".lwp_currency_code(); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach($items as $item): ?>
			<tr>
				<th scope="row">
					<?php echo apply_filters('lwp_cart_product_title', esc_html($item['name']), $item['post_id']); ?>
				</th>
				<td class="quantity">
					<?php printf('<span>%s</span>', $item['quantity']); ?>
				</td>
				<td class="misc"><?php do_action('lwp_cart_row_desc', '', $item['post_id'], $item['price'], $item['quantity']); ?></td>
				<td class="price">
					<?php echo number_format_i18n($item['price'])." ".lwp_currency_code(); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" id="lwp-submit" class="button-primary" value="<?php $this->e("Confirm"); ?>" />
	</p>
</form>
<p>
	<a class="button" href="<?php echo lwp_endpoint('cancel', array('TOKEN' => $info['TOKEN'])); ?>"><?php $this->e("Cancel");?></a>
</p>
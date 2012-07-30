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
				<th><?php $this->e("Your Name"); ?></th>
				<td>
					<?php echo $info["LASTNAME"].", ".$info["FIRSTNAME"]; ?>
					<small><?php echo $info["EMAIL"]; ?></small>
				</td>
			</tr>
			<tr>
				<th>
					<?php $this->e("Item"); ?>
				</th>
				<td>
					<?php echo $item_name;?>
				</td>
			</tr>
			<tr>
				<th>
					<?php $this->e("Amount"); ?>
				</th>
				<td>
					<?php echo PayPal_Statics::currency_entity($this->option['currency_code']).number_format($info['AMT'])."({$this->option['currency_code']})";?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" id="lwp-submit" class="button-primary" value="<?php $this->e("Confirm"); ?>" />
	</p>
</form>
<p>
	<a class="button" href="<?php echo lwp_endpoint('cancel'); ?>&amp;TOKEN=<?php echo $info['TOKEN']?>"><?php $this->e("Cancel");?></a>
</p>
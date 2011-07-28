<form method="post">
	<?php wp_nonce_field("lwp_confirm"); ?>
	<input type="hidden" name="TOKEN" value="<?php echo $info['TOKEN']; ?>" />
	<input type="hidden" name="PAYERID" value="<?php echo $info['PAYERID']; ?>" />
	<input type="hidden" name="AMT" value="<?php echo $info['AMT']; ?>" />
	<input type="hidden" name="CURRENCYCODE" value="<?php echo $this->option['currency_code']; ?>" />
	<input type="hidden" name="INVNUM" value="<?php echo $info['INVNUM']; ?>" />
	<input type="hidden" name="EMAIL" value="<?php echo $info['EMAIL']; ?>" />
	<table class="form-table">
		<tfoot>
			<tr>
				<td>
					<p>
						<a href="<?php bloginfo('url'); ?>?lwp=cancel&amp;TOKEN=<?php echo $info['token']?>"><?php $this->e("Cancel");?></a>
					</p>
				</td>
				<td>
					<p><?php $this->e("Please confirm the invoice above and accept payment.");?></p>
					<p class="submit">
						<input type="submit" id="lwp-submit" value="<?php $this->e("Confirm"); ?>" />
					</p>
				</td>
			</tr>
		</tfoot>
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
					<?php echo $post->post_title;?>
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
</form>
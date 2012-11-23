<?php /* @var $this LWP_Form */ ?>
<?php echo $message; ?>

<?php if($response): ?>
<h3><?php $this->e('Request Result'); ?></h3>
<pre>
<?php echo esc_html($response); ?>
</pre>
<?php endif; ?>

<form method="get" action="<?php echo esc_attr($action); ?>">
	<input type="hidden" name="lwp" value="sb-payment" />
	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="sb_transaction"><?php $this->e('Transaction to change'); ?></label></th>
				<td>
					<select id="sb_transaction" name="sb_transaction">
						<option value="0"><?php $this->e('Non-existing transaction(For error handling)'); ?></option>
						<?php foreach($transactions as $transaction): ?>
						<option value="<?php echo $transaction->ID; ?>">
							<?php
								$name = $this->get_item_name($transaction->book_id);
								printf('[ID: %d %s] %s %s %s', $transaction->ID, $this->_($transaction->method), $name, number_format($transaction->price), lwp_currency_code());
							?>
						</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php printf($this->_('This is the list of transactions waiting for payment from %s or PayEasy. The specified transaction status will be changed.'), $this->_('Web CVS')); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="sb_status"><?php $this->e('Status'); ?></label></th>
				<td>
					<select id="sb_status" name="sb_status">
						<?php
							foreach(array('支払い期限切れ', '支払い完了') as $index => $status_to){
								echo printf('<option value="%d">%s</option>', $index, $status_to);
							}
						?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>

	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Send'); ?>" />
	</p>
	
</form>


<p>
	<a class="button" href="<?php echo $link;?>"><?php $this->e("Return to Settings");?></a>
</p>
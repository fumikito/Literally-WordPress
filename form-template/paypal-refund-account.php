<p class="message notice">
	<?php $this->e('Please enter the account to which refund will be transfered. This information is <strong>required</strong> to finish refund process. Account should be valid and your own.'); ?>
</p>

<p class="required">
	<span class="required">*</span> = <?php $this->e('Required'); ?>
</p>

<?php if(!empty($error)): ?>
<p class="message error"><?php echo implode('<br />', array_map('esc_html', $error)); ?></p>
<?php endif; ?>
<form method="post" action="<?php echo lwp_refund_account_url(); ?>">
	<?php wp_nonce_field('lwp_update_refund_account_'.get_current_user_id()); ?>
	<table class="form-table lwp-form-table">
		<tbody>
			<tr>
				<th valign="top">
					<label for="bank_name"><?php $this->e('Bank Name'); ?></label>
					<span class="required">*</span>
				</th>
				<td>
					<input type="text" name="bank_name" id="bank_name" class="regular-text" value="<?php echo esc_html($account['bank_name']); ?>" placeholder="ex. <?php $this->e('Bank of Tokyo-Mitsubishi UFJ'); ?>" />
				</td>
			<tr>
				<th valign="top">
					<label for="bank_code"><?php $this->e('Bank Code'); ?></label>
				</th>
				<td>
					<input type="text" name="bank_code" name="bank_code" class="small-text" value="<?php echo esc_html($account['bank_code']); ?>" placeholder="ex. 0005" />
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="branch_name"><?php $this->e('Branch Name'); ?></label>
					<span class="required">*</span>
				</th>
				<td>
					<input type="text" name="branch_name" id="branch_name" class="middle-text" value="<?php echo esc_html($account['branch_name']); ?>" placeholder="ex. <?php $this->e('Aoyama Branch'); ?>" />
				</td>
			<tr>
				<th valign="top">
					<label for="branch_no"><?php $this->e('Branch No.'); ?></label>
				</th>
				<td>
					<input type="text" name="branch_no" id="branch_no" class="small-text" value="<?php echo esc_html($account['branch_no']); ?>" placeholder="ex. 345" />
				</td>
			</tr>
			<tr>
				<th valign="top">
					<?php $this->e('Account Type'); ?>
					<span class="required">*</span>
				</th>
				<td>
					<label>
						<input type="radio" name="account_type" value="normal"<?php if($account['account_type'] == 'normal') echo ' checked="checked"'; ?> />
						&nbsp;<?php $this->e('Normal Account'); ?>
					</label><br />
					<label>
						<input type="radio" name="account_type" value="checking"<?php if($account['account_type'] == 'checking') echo ' checked="checked"'; ?> />
						&nbsp;<?php $this->e('Checking Account'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="account_no"><?php $this->e('Account No.'); ?></label>
					<span class="required">*</span>
				</th>
				<td>
					<input type="text" name="account_no" id="account_no" class="regular-text" value="<?php echo esc_html($account['account_no']); ?>" placeholder="ex. 1234567" />
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="account_holder"><?php $this->e('Account Holder'); ?></label>
					<span class="required">*</span>
				</th>
				<td>
					<input type="text" name="account_holder" id="account_holder" class="regular-text" value="<?php echo esc_html($account['account_holder']); ?>" placeholder="ex. <?php $this->e('James Bond'); ?>" />
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" id="lwp-submit" class="button-primary" value="<?php $this->e("Update"); ?>" />
	</p>
</form>
<p>
	<a class="button" href="<?php echo $back; ?>"><?php printf($this->_("Return to %s"), $this->_('Purchase History')); ?></a>
</p>
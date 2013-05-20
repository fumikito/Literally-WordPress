<?php /* @var $this Literally_WordPress */ ?>
<p class="description">
	<?php $this->e('If you accept transfer, users can pay with bank account or something that is not digital transaction.'); ?>
	<?php $this->e('This helps users, but transactional process has a little bit more complex, because you have to check actual bank account to know whether bank deposit transfer has been made.'); ?>	
	<small>（<?php echo $this->help("transfer", $this->_("More &gt;"))?>）</small>
</p>
<hr />
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top">
				<label><?php $this->e('Accept Transfer'); ?></label>
			</th>
			<td>
				<label><input type="radio" name="transfer" value="0" <?php if(!$this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Disallow'); ?></label><br />
				<label><input type="radio" name="transfer" value="1" <?php if($this->option['transfer']) echo 'checked="checked"'; ?> /><?php $this->e('Allow'); ?></label>
			</td>
		</tr>
		<tr>
			<th><label><?php $this->e('Notification Frequency'); ?></label></th>
			<td>
				<label>
					<?php printf(
							$this->_('Send reminder on every %s days'),
							'<input class="short" type="text" name="notification_frequency" id="notification_frequency" value="'.intval($this->option['notification_frequency']).'" />'
					);?>
				</label><br />
				<label>
					<?php printf(
							$this->_('Transaction expires by %s days'),
							'<input class="short" type="text" name="notification_limit" id="notification_limit" value="'.intval($this->option['notification_limit']).'" />'
					);?>
				</label>
				<p class="description">
					<?php $this->e('If you don\'t want to send reminder, set notification frequency to 0. Transfer transaction will be expired after notification limit days will have been past.'); ?><br />
				</p>
			</td>
		</tr>
		<tr>
			<th><label><?php $this->e('Notification Message'); ?></label></th>
			<td>
				<p class="description">
					<?php printf($this->_('You can customize notification message <a href="%s">here</a>.'), admin_url('edit.php?post_type='.$this->notifier->post_type)); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
<?php /* @var $this LWP_Form */?>
<p class="message notice">
	<?php printf($this->_('You are about to send email to %1$s participants of %2$s <strong>%3$s</strong>. Please be patient till sending process finishes even if number of participants is huge.'), $participants, $post_type, $title); ?>
</p>
<form id="lwp-contact-participants-form" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post">
	<input type="hidden" name="action" value="lwp_contact_participants" />
	<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
	<?php wp_nonce_field('lwp_contact_participants_'.get_current_user_id());?>
	<table class="form-table lwp-ticket-table" id="lwp-ticket-table-list">
		<tbody>
			<tr>
				<th scope="row"><label for="from"><?php $this->e('From'); ?></label></th>
				<td>
					<select name="from" id="from">
						<?php foreach($options as $key => $val): ?>
						<option value="<?php echo $key; ?>"><?php echo esc_html($val); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="subject"><?php $this->e('Subject'); ?></label></th>
				<td><input type="text" class="regular-text input" name="subject" id="subject" value="" placeholder="<?php $this->e('ex. What you need for our event.'); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="body"><?php $this->e('Mail Body'); ?></label></th>
				<td>
					<textarea rows="10" type="text" class="regular-text input" name="body" id="body" value="" placeholder="<?php $this->e('Dear %user_display%'); ?>"></textarea>
					<p class="description"><?php printf($this->_('You can user %1$s and %2$s as placeholder.'), '<strong>%ticket_url%</strong>', '<strong>%code%</strong>'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php $this->e('Signature'); ?></th>
				<td>
					<?php echo $signature?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<?php echo $loader; ?>
		<input type="submit" class="button-primary" value="<?php $this->e('Send'); ?>" />
	</p>
</form>
<p>
	<a class="button" href="<?php echo $link; ?>"><?php printf($this->_("Return to %s"), $title); ?></a>
</p>
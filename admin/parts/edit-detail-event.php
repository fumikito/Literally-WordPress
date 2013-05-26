<?php /* @var $this LWP_Event */ /* @var $post object */ ?>
<?php wp_nonce_field('lwp_event_detail', '_lwpeventnonce'); ?>
<h4><?php $this->e('Event Information'); ?></h4>
<table class="form-table" id="">
	<tr>
		<th valign="top"><?php $this->e('Event Period'); ?></th>
		<td>
			<label>
				<?php $this->e('Start at'); ?>
				<input type="text" class="time-picker" name="event_start_time" id="event_start_time" value="<?php echo esc_attr(get_post_meta($post->ID, $this->meta_start, true)); ?>" />
			</label>
			-
			<label>
				<?php $this->e('Ends at'); ?>
				<input type="text" class="time-picker" name="event_end_time" id="event_end_time" value="<?php echo esc_attr(get_post_meta($post->ID, $this->meta_end, true)); ?>" />
			</label>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php $this->e('Participants'); ?></th>
		<td>
			<?php
				$participants = lwp_participants_number($post);
				printf($this->_('<strong>%s</strong> People'), number_format($participants));
				echo ' / ';
				printf($this->_('%d poeple are waiting for cancellation.'), lwp_participants_number($post, true));
			?>
			<?php if(current_user_can('edit_others_posts')): ?>
				<a class="button" href="<?php echo admin_url('admin.php').'?page=lwp-event&amp;event_id='.$post->ID; ?>"><?php $this->e('Show list'); ?></a>
			<?php endif; ?>
			<?php if($participants): ?>
				<a class="button-primary" href="<?php echo lwp_endpoint('ticket-contact', array('event_id' => $post->ID)); ?>"><?php $this->e('Contact them'); ?></a>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php $this->e('Ticket Sold'); ?></th>
		<td>
			<?php echo number_format($this->get_event_transaction_total($post->ID)).' '.lwp_currency_code();?>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php $this->e('Waiting list'); ?></th>
		<td>
			<label>
				<input type="checkbox" name="event_awaiting" value="1"<?php if($this->has_cancel_list($post->ID)) echo ' checked="checked"'; ?> />
				<?php $this->e('User can wait for cancellation of this event.'); ?>
			</label>
		</td>
	</tr>
</table>

<h4><?php $this->e('Ticket Sale Setting');?></h4>
<table class="form-table" id="lwp-event-setting">
	<tbody>
		<tr>
			<th valign="top">
				<label for="event_selling_limit"><?php $this->e('Limit to sell');  ?></label>
			</th>
			<td>
				<input placeholder="ex. <?php echo date_i18n('Y-m-d'); ?>" type="text" id="event_selling_limit" name="event_selling_limit" class="date-picker" value="<?php echo esc_attr(get_post_meta($post->ID, $this->meta_selling_limit, true)); ?>" />
				<span class="description">YYYY-MM-DD</span>
			</td>
		</tr>
		<tr>
			<th valign="top"><label><?php $this->e('Limit to cancel'); ?></label></th>
			<td>
				<?php $limits = get_post_meta($post->ID, $this->meta_cancel_limits, true); ?>
				<ul id="cancel-date-list"<?php if(empty($limits)) echo 'class="zero"'; ?>>
					<?php if(!empty($limits)) foreach($limits as $limit): ?>
						<li><?php printf(
							$this->_('Cacnelable till %1$s days before, %2$s'),
							'<input type="text" class="small-text" readonly="readonly" name="cancel_limit_day[]" value="'.$limit['days'].'" />',
							'<input type="text" class="small-text" readonly="readonly" name="cancel_limit_ratio[]" value="'. $limit['ratio'] .'" />'
						);?><a class="button" href="#"><?php $this->e('Delete'); ?></a></li>
					<?php endforeach; ?>
					<li class="no-cancelable-date"><?php $this->e('You do not specify cancel limit, so user cannot cancel.'); ?></li>
				</ul>
				<p>
					<?php
						printf(
							$this->_('User can cancel ticket %1$s days before selling limit. Refund is %2$s.'),
							'<input type="text" name="cancel_limit" class="small-text" value="" />',
							'<input type="text" name="cancel_ratio" class="small-text" value="" />'
						);
					?>
					<a href="#" id="lwp-cancel-add" class="button"><?php $this->e('Add'); ?></a>
				</p>
				<p class="description">
					<?php $this->e('If you specify cancelable date, user can cancel transaction after payment.'); ?><br />
					<?php $this->e('You can specify percentage(ex. 50%), number(ex. 100). If you specified positive number, that amount will be refunded. Negative number specified, refund amount results paid price minus it.'); ?><br />
					<?php $this->e('<strong>Example: </strong>');?><br />
					<?php printf($this->_('Price %1$s and Refund %2$s -&gt; %3$s back.'), '$1,000', '50%', '$500'); ?><br />
					<?php printf($this->_('Price %1$s and Refund %2$s -&gt; %3$s back.'), '$1,000', '-200', '$800'); ?><br />
					<?php printf($this->_('Price %1$s and Refund %2$s -&gt; %3$s back.'), '$1,000', '300', '$300'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="event_footer_note"><?php $this->e('Ticket Footer Note'); ?></label></th>
			<td>
				<textarea id="event_footer_note" name="event_footer_note" rows="5" style="width:90%;"><?php echo esc_html($this->get_footer_note($post->ID, true)); ?></textarea><br />
				<label><input type="checkbox" name="event_footer_note_autop" value="1"<?php if($this->footer_note_needs_autop($post->ID)) echo ' checked="checked"'; ?> /><?php $this->e('Auto format'); ?></label>
			</td>
		</tr>
	</tbody>
</table>

<h4><?php $this->e('Ticket List');?></h4>
<table class="form-table" id="ticket-list-table">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Name'); ?></th>
			<th scope="col"><?php $this->e('Description'); ?></th>
			<th scope="col"><?php $this->e('Price'); echo ' <small>('.lwp_currency_code().')</small>'; ?></th>
			<th scope="col"><?php $this->e('Stock'); ?></th>
			<th scope="col">&nbsp;</th>
			<th scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php $tickets = get_posts('post_type='.$this->post_type.'&post_parent='.$post->ID); if(!empty($tickets)): foreach($tickets as $ticket): ?>
		<tr id="ticket-<?php echo $ticket->ID; ?>">
			<th scope="row"><?php echo $ticket->post_title; ?></th>
			<td><?php echo mb_substr(strip_tags(apply_filters('', $ticket->post_content)), 0, 10, 'utf-8'); ?>&hellip;</td>
			<td><?php echo number_format(get_post_meta($ticket->ID, 'lwp_price', true)); ?></td>
			<td><?php echo number_format(get_post_meta($ticket->ID, $this->meta_stock, true)); ?></td>
			<td>
				<a href="#" class="button ticket-edit"><?php $this->e('Edit'); ?></a>
			</td>
			<td>
				<a href="<?php echo admin_url('admin-ajax.php'); ?>" class="button ticket-delete"><?php $this->e('Delete'); ?></a>
			</td>
		</tr>
		<?php endforeach; endif; ?>
	</tbody>
</table>

<h4><?php $this->e('Ticket form'); ?></h4>
<input type="hidden" name="ticket_id" value="0" />
<input type="hidden" name="ticket_parent_id" id="ticket_parent_id" value="<?php echo $post->ID; ?>" />
<?php $presets = $this->get_ticket_prisets($post->post_type); if(!empty($presets) && !$this->presets_registered($post)): ?>
	<p class="description">
		<?php $this->e('This post type has presets: '); ?>
		<?php foreach($presets as $presets): ?>
			<code><?php echo isset($presets['post_title']) ? $presets['post_title'] : $this->_('No name'); ?></code>, 
		<?php endforeach; ?>
		<a class="button" href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=lwp_ticket_presets&event_id='.$post->ID), 'lwp_ticket_presets'); ?>" id="lwp-event-presets"><?php $this->e('Add presets');  ?></a>
		<img style="display:none; vertical-align: middle;" src="<?php echo $this->url ?>assets/indicator-postbox.gif" alt="Loading..." width="16" height="16" />
	</p>
<?php endif; ?>
<table class="form-table" id="lwp-event-edit-form">
	<tbody>
		<tr>
			<th valign="top"><label for="ticket_post_title"><?php $this->e('Ticket Name'); ?></label></th>
			<td>
				<input type="text" name="ticket_post_title" id="ticket_post_title" class="regular-text" value="" />
				<p class="description ticket-status">
					<span class="newly-add"><?php $this->e('Added as new ticket.');  ?></span>
					<span class="editting"><?php $this->e('Editting existing ticket.');  ?></span>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="ticket_post_content"><?php $this->e('Ticket Description'); ?></label></th>
			<td>
				<textarea rows="5" type="text" name="ticket_post_content" id="ticket_post_content"></textarea>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="ticket_price"><?php $this->e('Ticket Price'); ?></label></th>
			<td>
				<input type="text" name="ticket_price" id="ticket_price" value="" /><?php echo lwp_currency_code();?>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="ticket_stock"><?php $this->e('Ticket Stock'); ?></label></th>
			<td>
				<input type="text" name="ticket_stock" id="ticket_stock" value="" />
			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<a id="ticket_cancel" class="button" href="#"><?php $this->e('Clear form'); ?></a>
	<a id="ticket_submit" class="button-primary" href="<?php echo admin_url('admin-ajax.php'); ?>"><?php $this->e('Edit ticket'); ?></a>
</p>
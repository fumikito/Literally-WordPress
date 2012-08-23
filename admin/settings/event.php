<h3><?php printf($this->_('About %s'), $this->_('Event')); ?></h3>
<p class="description">
	<?php $this->e('Event means real event like lunch party, poetry reading or else.'); ?><br />
	<?php $this->e('LWP provide you digital ticket management system.'); ?>
</p>
<h3><?php $this->e("Setting"); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th valign="top">
				<label><?php $this->e('Event Post Type'); ?></label>
			</th>
			<td>
				<?php foreach(get_post_types('', 'object') as $post_type): if(false === array_search($post_type->name, array('revision', 'nav_menu_item', 'page', 'attachment', 'lwp_notification', $this->event->post_type))): ?>
					<label>
						<input type="checkbox" name="event_post_types[]" value="<?php echo $post_type->name; ?>" <?php if(false !== array_search($post_type->name, $this->option['event_post_types'])) echo 'checked="checked" '; ?>/>
						<?php echo $post_type->labels->name; ?>
					</label>&nbsp;
				<?php endif; endforeach; ?>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="event_mail_body"><?php $this->e('Mail Body'); ?></label></th>
			<td>
				<textarea rows="10" style="width:90%;" name="event_mail_body" id="event_mail_body"><?php echo esc_html($this->event->get_mail_body()); ?></textarea>
				<p class="description">
					<?php $this->e('This mail will be sent when transaction completed. You can use place holders: '); ?>
					<?php foreach($this->event->get_place_holders() as $key => $desc): ?>
					<strong>%<?php echo $key; ?>%</strong><small>(<?php echo esc_html($desc); ?>)</small>, 
					<?php endforeach; ?>
				</p>
			</td>
		</tr>
		<tr>
			<th valign="top"><label for="event_signature"><?php $this->e('Mail Signature'); ?></label></th>
			<td>
				<textarea rows="5" style="width:90%;" name="event_signature" id="event_signature"><?php echo esc_html($this->event->get_signature()); ?></textarea>
				<p class="description"><?php $this->e('This text will be used for every mail to participants'); ?></p>
			</td>
		</tr>
	</tbody>
</table>
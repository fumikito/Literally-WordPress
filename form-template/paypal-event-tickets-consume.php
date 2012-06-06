<?php /* @var $this LWP_Form */?>
<?php if($updated): ?>
	<p class="message success">
		<?php $this->e('Successfully updated.'); ?>
	</p>
<?php endif; ?>
<p class="message notice">
	<?php printf($this->_('Below is the tickets %1$s bought for %2$s <strong>&quot;%3$s&quot;</strong>.'), $user->display_name, $post_type, $title); ?>
</p>
<form action="<?php echo $action; ?>" method="post">
	<?php wp_nonce_field('lwp_ticket_consume_'.get_current_user_id());?>
	<table class="form-table lwp-ticket-table" id="lwp-ticket-table-list">
		<thead>
			<tr>
				<th scope="col"><?php $this->e('Ticket Name'); ?></th>
				<th scope="col"><?php $this->e('Bought');?></th>
				<th scope="col"><?php $this->e('Price'); ?></th>
				<th scope="col"><?php $this->e('Number'); ?></th>
				<th scope="col"><?php $this->e('Consumed'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($tickets as $ticket): ?>
				<th scope="row"><?php echo apply_filters('the_title', $ticket->post_title);?></th>
				<td><?php echo mysql2date(get_option('date_format'), $ticket->updated);?></td>
				<td><?php echo number_format_i18n($ticket->price).' '.lwp_currency_code();?></td>
				<td><?php echo number_format_i18n($ticket->num); ?></td>
				<td>
					<select name="ticket[<?php echo $ticket->ID; ?>]">
						<?php for($i = 0; $i <= $ticket->num; $i++){
							echo '<option value="'.$i.'"'.($i == $ticket->consumed ? ' selected="selected"' : '').'>'.$i.'</option>';
						} ?>
					</select>
				</td>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Update'); ?>" />
	</p>
</form>
<p>
	<a class="button" href="<?php echo $link; ?>"><?php printf($this->_("Return to %s"), $title); ?></a>
</p>
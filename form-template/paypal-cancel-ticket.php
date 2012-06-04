<?php /* @var $this LWP_Form */?>

<p class="message notice">
	<?php printf($this->_('You are about to cancel %1$s <strong>&quot;%2$s&quot;</strong>. Please select ticket below. You can cancel in this condition till %3$s'), $event_type->labels->name, get_the_title($event), $limit); ?>
</p>

<form method="post" action="<?php echo lwp_endpoint('ticket-cancel-complete'); ?>">
	<?php wp_nonce_field('lwp_ticket_cancel', '_wpnonce', false); ?>
	<table class="form-table lwp-ticket-table" id="lwp-ticket-table-cancel">
		<thead>
			<tr>
				<th scope="col">&nbsp;</th>
				<th scope="col"><?php $this->e('Ticket Name'); ?></th>
				<th scope="col"><?php $this->e('Bought');?></th>
				<th scope="col"><?php $this->e('Price'); ?></th>
				<th scope="col"><?php $this->e('Refund'); ?></th>
				<th scope="col"><?php $this->e('Ratio'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($tickets as $ticket): ?>
			<tr>
				<th class="lwp-columne-check" scope="row"><input type="radio" name="ticket_id" id="ticket-<?php echo $ticket->ID; ?>" value="<?php echo $ticket->ID; ?>" /></th>
				<td class="lwp-column-title">
					<label for="ticket-<?php echo $ticket->ID;; ?>"><?php echo apply_filters('the_title', $ticket->post_title).' * '.$ticket->num; ?></label>
				</td>
				<td class="lwp-column-bought">
					<?php echo mysql2date(get_option('date_format'), $ticket->updated); ?>
				</td>
				<td class="lwp-column-price">
					<?php echo number_format_i18n($ticket->price)." ".lwp_currency_code();?>
				</td>
				<td class="lwp-column-refund">
					<?php echo number_format_i18n(lwp_ticket_refund_price($ticket->book_id) * $ticket->num).' '.lwp_currency_code();?>
				</td>
				<td class="lwp-column-refund">
					<strong><?php echo $ratio; ?>%</strong>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php $this->e('Cancel'); ?>" />
	</p>
</form>
<p>
	<a class="button" href="<?php echo get_permalink($event); ?>"><?php printf($this->_("Return to %s"), get_the_title($event)); ?></a>
</p>
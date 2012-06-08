<?php /* @var $this LWP_Form */?>

<p class="message notice">
	<?php printf($this->_('Below is the tickets you bought for %1$s <strong>&quot;%2$s&quot;</strong>.'), $post_type, $title, $limit); ?>
</p>

<table class="form-table lwp-ticket-table" id="lwp-ticket-table-list">
	<thead>
		<tr>
			<th scope="col"><?php $this->e('Ticket Name'); ?></th>
			<th scope="col"><?php $this->e('Bought');?></th>
			<th scope="col"><?php $this->e('Price'); ?></th>
			<th scope="col"><?php $this->e('Quantity'); ?></th>
			<th scope="col"><?php $this->e('Consumed'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($tickets as $ticket): ?>
			<th scope="row"><?php echo apply_filters('the_title', $ticket->post_title);?></th>
			<td><?php echo mysql2date(get_option('date_format'), $ticket->updated);?></td>
			<td><?php echo number_format_i18n($ticket->price).' '.lwp_currency_code();?></td>
			<td><?php echo number_format_i18n($ticket->num); ?></td>
			<td><?php echo number_format_i18n($ticket->consumed); ?></td>
		<?php endforeach; ?>
	</tbody>
</table>
<p class="check-url-image center">
	<?php $this->e('Please keep this code private.'); ?><br />
	<strong><?php echo $token; ?></strong><br />
	<img src="<?php echo $qr_src; ?>" alt="<?php echo $check_url; ?>" height="150" width="150" />
</p>
<p class="submit">
	<a id="lwp-submit" class="button-primary" href="#" onclick="window.print(); return false;"><?php $this->e("Print"); ?></a>
</p>
<p>
	<a class="button" href="<?php echo $link; ?>"><?php printf($this->_("Return to %s"), $title); ?></a>
</p>
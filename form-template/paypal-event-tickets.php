<?php /* @var $this LWP_Form */?>

<p class="message notice">
	<?php printf($this->_('Below is the tickets you bought for %1$s <strong>&quot;%2$s&quot;</strong>. Print or bookmark this page and keep it accessible.'), $post_type, $title, $limit); ?>
</p>

<table class="form-table lwp-ticket-table" id="lwp-ticket-table-list">
	<thead>
		<tr>
			<?php foreach($headers as $header): ?>
				<th scope="col"><?php echo esc_html($header); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($tickets as $ticket): ?>
			<tr>
				<?php foreach($headers as $column => $header){
					switch($column){
						case 'name':
							echo '<th scope="row">'.get_the_title($ticket->post_parent).'&nbsp;'.apply_filters('the_title', $ticket->post_title).'</th>';
							break;
						case 'date':
							echo '<td>'.mysql2date(get_option('date_format'), $ticket->updated).'</td>';
							break;
						case 'price':
							echo '<td>'.number_format_i18n($ticket->price).' '.lwp_currency_code().'</td>';
							break;
						case 'quantity':
							echo '<td>'.number_format_i18n($ticket->num).'</td>';
							break;
						case 'consumed':
							echo '<td>'.(($ticket->num <= $ticket->consumed) ? $this->_('Used') : number_format_i18n($ticket->consumed)).'</td>';
							break;
					}
				} ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p class="check-url-image center">
	<?php $this->e('Please keep this code private.'); ?><br />
	<strong><?php echo $token; ?></strong><br />
	<img src="<?php echo $qr_src; ?>" alt="<?php echo $check_url; ?>" height="150" width="150" />
</p>
<?php if(!empty($footer_note)): ?>
<div class="ticket-footer-note"><?php echo $footer_note; ?></div>
<?php endif; ?>
<p class="submit">
	<a id="lwp-submit" class="button-primary" href="#" onclick="window.print(); return false;"><?php $this->e("Print"); ?></a>
</p>
<p>
	<a class="button" href="<?php echo $link; ?>"><?php printf($this->_("Return to %s"), $title); ?></a>
</p>
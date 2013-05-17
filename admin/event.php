<?php /* @var $this Literally_WordPress */?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('admin.php?page=lwp-event'); ?>" class="nav-tab<?php if(!isset($_GET['event_id'])) echo ' nav-tab-active'; ?>">
		<?php $this->e('Event Management'); ?>
	</a>
	<?php if(isset($_GET['event_id'])): ?>
		<a href="<?php echo admin_url('admin.php?page=lwp-event&event_id='.intval($_GET['event_id'])); ?>" class="nav-tab<?php if(isset($_GET['event_id'])) echo ' nav-tab-active';?>">
			<?php printf($this->_('%s Detail'), get_the_title(intval($_GET['event_id']))); ?>
		</a>
	<?php endif; ?>
</h2>

<?php
if(isset($_GET['event_id'])): $event = get_post($_GET['event_id']);
	require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-ticket.php";
	$list_table = new LWP_List_Ticket();

	
?>
<h3><?php $this->e('Event Summary'); ?></h3>

<table class="summary-table align-left">
	<tbody>
		<tr>
			<th><?php $this->e('Event Period');  ?></th>
			<td>
				<?php
					$start = get_post_meta($event->ID, $this->event->meta_start, true);
					$end = get_post_meta($event->ID, $this->event->meta_end, true);
					if(!$start){
						$start = '-';
					}
					if(!$end){
						$end = '-';
						$outdated = '';
					}else{
						if(current_time('timestamp') < strtotime($end)){
							$outdated = '<br />'.human_time_diff(strtotime($end));
						}elseif($end){
							$outdated = '<br /><strong class="error">'.$this->_('Outdated').'</strong>';
						}
					}
					printf($this->_('From: %1$s To: %2$s%3$s'), $start, $end, $outdated);
				?>
			</td>
		</tr>
		<tr>
			<th><?php $this->e('Limit to sell');  ?></th>
			<td><?php
				$limit = get_post_meta($event->ID, $this->event->meta_selling_limit, true);
				if(!$limit){
					$limit = '-';
				}
				echo $limit;
			?></td>
		</tr>
		<tr>
			<th><?php $this->e('Limit to cancel');  ?></th>
			<td><?php
				lwp_list_cancel_condition(array('post' => $event->ID));
			?></td>
		</tr>		
	</tbody>
</table>
<table class="summary-table alignright">
	<thead>
		<tr>
			<th><?php $this->e('Ticket Name'); ?></th>
			<th><?php $this->e('Price'); ?></th>
			<th><?php $this->e('Stock'); ?></th>
			<th><?php $this->e('Sold'); ?></th>
			<th><?php $this->e('Consumed'); ?></th>
			<th><?php $this->e('Sales'); ?></th>
		</tr>
	</thead>
	<?php
		$total_stock = 0;
		$total_consumed = 0;
		$total_sold = 0;
		$total_sales = 0;
		ob_start();
		$tickets = get_children("post_parent={$event->ID}&post_type={$this->event->post_type}&posts_per_page=-1");
		if(!empty($tickets)){
			foreach($tickets as $ticket){
				$stock = lwp_get_ticket_stock(false, $ticket);
				$sold = lwp_get_ticket_sold($ticket);
				$sales = $this->event->get_ticket_total_sales($ticket->ID);
				$consumed = lwp_get_ticket_consumed($ticket);
				$total_stock += $stock;
				$total_consumed += $consumed;
				$total_sold += $sold;
				$total_sales += $sales;
				?>
					<tr>
						<th><?php echo $ticket->post_title; ?></th>
						<td><?php lwp_the_price($ticket);?></td>
						<td><?php echo number_format($stock);?></td>
						<td><?php echo number_format($sold);?></td>
						<td><?php echo number_format($consumed);?></td>
						<td><?php echo number_format($sales)." (".lwp_currency_code().")";?></td>
					</tr>
				<?php
			}
		}else{
			?>
					<tr><td colspan="6"><?php $this->e('No ticket found.'); ?></td></tr>
			<?php
		}
		$contents = ob_get_contents();
		ob_end_clean();
	?>
	<tfoot>
		<tr>
			<th><?php $this->e('Total'); ?></th>
			<td>-</td>
			<td><?php echo number_format($total_stock); ?></td>
			<td><?php echo number_format($total_sold);?></td>
			<td><?php echo number_format($total_consumed);?></td>
			<td><?php echo number_format($total_sales)." (".lwp_currency_code().")";?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php echo $contents; ?>
	</tbody>
</table>
<br style="clear: both;" />
<h3><?php $this->e('Ticket transaction list'); ?></h3>
<?php if($this->caps->current_user_can(LWP_Capabilities::DOWNLOAD_EVENT_CSV)): ?>
	<iframe src="" name="lwp-csv-output" height="0" width="100%"></iframe>
	<form id="lwp-csv-output-form" method="get" action="<?php echo admin_url('admin-ajax.php'); ?>" target="lwp-csv-output">
		<input type="hidden" name="action" value="lwp_event_csv_output" />
		<?php wp_nonce_field('lwp_event_csv_output');?>
		<input type="hidden" name="event_id" value="<?php echo esc_attr($_GET['event_id']); ?>" />
		<input type="hidden" name="status" value="" />
		<input type="hidden" name="ticket" value="" />
		<input type="hidden" name="from" value="" />
		<input type="hidden" name="to" value="" />
		<p class="description">
			<?php printf( $this->_('You can get participants list of <strong>%1$s</strong> in status below.'), $event->post_title); ?>
			<input type="submit" class="button-primary" value="<?php $this->e('Get CSV'); ?>" /><br />
		</p>
	</form>
<?php endif; ?>
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-event" />
	<input type="hidden" name="event_id" value="<?php echo intval($_GET['event_id']); ?>" />
	<?php
		$list_table->prepare_items();
		do_action('admin_notice');
		$list_table->search_box(__('Search'), 's');
		$list_table->display();
	?>
</form>


<?php else: ?>
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-event" />
<?php
require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-event.php";
$list_table = new LWP_List_Event();
$list_table->prepare_items();
do_action('admin_notice');
$list_table->search_box(__('Search'), 's');
$list_table->display();

?>
</form>
<?php endif; ?>
<?php /* @var $this Literally_WordPress */  ?>
<?php
$is_detail = isset($_GET["transaction_id"]) && is_numeric($_REQUEST["transaction_id"]) && ($transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->transaction} WHERE ID = %d", $_REQUEST["transaction_id"])));
?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('admin.php?page=lwp-management'); ?>" class="nav-tab<?php if(!$is_detail && !isset($_GET['view'])) echo ' nav-tab-active';?>">
		<?php $this->e('Transaction Dashboard'); ?>
	</a>
	<a href="<?php echo admin_url('admin.php?page=lwp-management&view=list'); ?>" class="nav-tab<?php if(isset($_GET['view']) && $_GET['view'] == 'list') echo ' nav-tab-active';?>">
		<?php $this->e('List'); ?>
	</a>
	<?php if($is_detail): ?>
	<a href="<?php echo admin_url('admin.php?page=lwp-management&transactin_id='.intval($_REQUEST['transaction_id'])); ?>" class="nav-tab<?php if($is_detail) echo ' nav-tab-active';?>">
		<?php printf($this->_('Detail No.%d'), $_REQUEST['transaction_id']); ?>
	</a>
	<?php endif; ?>
</h2>


<?php
/*---------------------------------
 * 個別表示
 */
if($is_detail):
		$book = get_post($transaction->book_id);
		$user = get_userdata($transaction->user_id);
?>
<h3><?php $this->e('Transaction Detail'); ?></h3>
<table class="form-table detail-table">
	<thead>
		<tr>
			<th scope="row"><?php $this->e('Heading');?></th>
			<th scope="row"><?php $this->e('Value');?></th>
			<th scope="row"><?php $this->e('Action');?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Item Name'); ?></th>
			<td><?php
				switch($book->post_type){
					case $this->event->post_type:
						echo get_the_title($book->post_parent).' - ';
						$post_type = get_post_type($book->post_parent);
						$edit_url = admin_url("post.php?action=edit&post_type={$post_type}&post={$book->post_parent}");
						break;
					case $this->subscription->post_type:
						echo $this->_('Subscription').' - ';
					default:
						$edit_url = admin_url("post.php?action=edit&post_type={$book->post_type}&post={$book->ID}");
						break;
				}
				echo $book->post_title;
				?></td>
			<td><a class="button" href="<?php echo $edit_url; ?>"><?php $this->e('Edit'); ?></a></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('User Name'); ?></th>
			<?php if($user): ?>
				<td><?php echo $user->display_name;  ?></td>
				<td>
					<a class="button" href="<?php echo admin_url("user-edit.php?user_id={$user->ID}");?>"><?php $this->e('Profile'); ?></a>
					<a class="button" href="<?php echo admin_url("admin.php?page=lwp-management&view=list&user_id={$user->ID}");?>"><?php $this->e('See all transactions'); ?></a>
				</td>
			<?php else: ?>
				<td><?php $this->e('Deleted User'); ?></td>
				<td>---</td>
			<?php endif; ?>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Updated'); ?></th>
			<td>
				<?php echo mysql2date(get_option('date_format').' H:i:s', get_date_from_gmt($transaction->updated)); ?><br />
				<small>（<?php $this->e('Registered'); ?>: <?php echo mysql2date(get_option('date_format').' H:i:s', get_date_from_gmt($transaction->registered)); ?>）</small>
			</td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e("Purchased Price"); ?></th>
			<td>
				<strong><?php echo number_format($transaction->price)." ({$this->option['currency_code']})"; ?></strong>
				<p class="description"><?php $this->e('Original Price'); ?>: <?php echo number_format( lwp_original_price($book->ID))." ({$this->option['currency_code']})";?></p>
			</td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Consumed'); ?> / <?php $this->e('Quantity'); ?></th>
			<td><?php echo number_format_i18n($transaction->consumed); ?> / <?php echo number_format_i18n($transaction->num); ?></td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Purchase Method'); ?></th>
			<td>
				<?php if(false !== array_search($transaction->status, array(LWP_Payment_Status::WAITING_CANCELLATION, LWP_Payment_Status::QUIT_WAITNG_CANCELLATION))): ?>
					---
				<?php else: ?>
					<?php $this->e($transaction->method); ?>
				<?php endif; ?>
			</td>
			<td>---</td>
		</tr>
		<?php switch($transaction->method): case LWP_Payment_Methods::PAYPAL: ?>
		<?php if(false !== array_search($transaction->status, array(LWP_Payment_Status::WAITING_CANCELLATION, LWP_Payment_Status::QUIT_WAITNG_CANCELLATION))): ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Waiting No.'); ?></th>
			<td>
				<?php echo esc_html($transaction->transaction_key); ?>
			</td>
			<td>---</td>
		</tr>
		<?php else: ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Invoice Num'); ?></th>
			<td>
				<?php echo esc_html($transaction->transaction_key); ?><br />
				<?php if(false !== array_search($transaction->status, array(LWP_Payment_Status::SUCCESS, LWP_Payment_Status::REFUND, LWP_Payment_Status::REFUND_REQUESTING))): ?>
					<?php printf($this->_('Payer Email: %s'), $transaction->payer_mail); ?>
				<?php endif; ?>
			</td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Transaction ID'); ?></th>
			<td>
				<?php echo esc_html($transaction->transaction_id); ?>
			</td>
			<td>---</td>
		</tr>
		<?php endif; ?>
		<?php break; case LWP_Payment_Methods::APPLE: case LWP_Payment_Methods::ANDROID: ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Transaction ID'); ?></th>
			<td>
				<?php echo esc_html($transaction->transaction_key); ?>
			</td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('UUID'); ?></th>
			<td>
				<?php echo esc_html($transaction->transaction_id); ?>
			</td>
			<td>---</td>
		</tr>
		<?php if($transaction->method == LWP_Payment_Methods::ANDROID): ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Notification ID'); ?></th>
			<td>
				<?php echo esc_html($transaction->payer_mail); ?>
			</td>
			<td>---</td>
		</tr>
		<?php endif; ?>
		<?php break; case LWP_Payment_Methods::GMO_CC:  ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Transaction ID'); ?></th>
			<td><?php echo esc_html($transaction->transaction_id); ?></td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Order ID'); ?></th>
			<td><?php echo esc_html($transaction->transaction_key); ?></td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Approved No.'); ?></th>
			<td><?php echo esc_html($transaction->payer_mail); ?></td>
			<td>---</td>
		</tr>
		<?php break; case LWP_Payment_Methods::GMO_WEB_CVS: case LWP_Payment_Methods::GMO_PAYEASY: $info = unserialize($transaction->misc);?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Payment Limit'); ?></th>
			<td><?php echo mysql2date(get_option('date_format'), $info['bill_date']); ?></td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('CVS'); ?></th>
			<td>
				<?php echo $this->softbank->get_verbose_name($info['cvs']); ?>
				&nbsp;<small>(<?php echo $this->softbank->get_cvs_code($info['cvs']); ?>)</small>
			</td>
			<td>---</td>
		</tr>
		<?php
			$ticket_names = $this->gmo->get_cvs_code_label($info['cvs']);
			foreach(array('receipt_no', 'conf_no') as $no => $key):
				if(!isset($ticket_names[$no])){
					continue;
				}
		?>
		<tr>
			<th scope="row" valign="top"><?php echo $ticket_names[$no]; ?></th>
			<td><?php echo esc_html($info[$key]); ?></td>
			<td>---</td>
		</tr>
		<?php endforeach; ?>
		<?php break; case LWP_Payment_Methods::SOFTBANK_CC: case LWP_Payment_Methods::SOFTBANK_WEB_CVS: case LWP_Payment_Methods::SOFTBANK_PAYEASY: $info = unserialize($transaction->misc); ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('SPS Transaction ID'); ?></th>
			<td><?php echo esc_html($transaction->transaction_id); ?></td>
			<td>---</td>
		</tr>
		<?php if($transaction->method == LWP_Payment_Methods::SOFTBANK_CC) break; ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('CVS'); ?></th>
			<td>
				<?php echo $this->softbank->get_verbose_name($info['cvs']); ?>
				&nbsp;<small>(<?php echo $this->softbank->get_cvs_code($info['cvs']); ?>)</small>
			</td>
			<td>---</td>
		</tr>
		<?php
			$ticket_names = $this->softbank->get_cvs_code_label($info['cvs']);
			for($i = 0, $l = count($ticket_names); $i < $l; $i++):
		?>
		<tr>
			<th scope="row" valign="top"><?php echo $ticket_names[$i]; ?></th>
			<td><?php echo esc_html($info['cvs_pay_data'.($i + 1)]); ?></td>
			<td>---</td>
		</tr>
		<?php endfor; ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Payment Limit'); ?></th>
			<td><?php echo mysql2date(get_option('date_format'), preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "$1-$2-$3 23:59:59", $info['bill_date'])); ?></td>
			<td>---</td>
		</tr>
		<?php break; case LWP_Payment_Methods::TRANSFER: ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Notification Code'); ?></th>
			<td><?php echo $transaction->transaction_key; ?></td>
			<td>---</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Payment Limit'); ?></th>
			<td>
				<?php echo $this->notifier->get_limit_date($transaction->registered, get_option('date_format').' H:i:s'); ?>&nbsp;
				<small>(<?php printf($this->_('%d days left'), $this->notifier->get_left_days($transaction->registered)); ?>)</small>
			</td>
			<td>---</td>
		</tr>
		<?php break; endswitch; ?>
		<tr>
			<th scope="row" valign="top"><?php $this->e("Status"); ?></th>
			<td>
				<?php $this->e($transaction->status);?>
			</td>
			<td>
				<form method="post">
					<?php wp_nonce_field('lwp_update_transaction'); ?>
					<input type="hidden" name="transaction_id" value="<?php echo $transaction->ID; ?>" />
					<p>
					<select name="status">
						<?php foreach(LWP_Payment_Status::get_all_status() as $s): ?>
						<?php if($s == LWP_Payment_Status::REFUND): ?>
							<?php if($transaction->status == LWP_Payment_Status::SUCCESS): ?>
								<?php $disabled = ($transaction->method == LWP_Payment_Methods::PAYPAL && 60 < ceil((current_time('timestamp') - strtotime($transaction->updated)) / 60 / 60 / 24 )) ? ' disabled="disabled"' : '';  ?>
								<option value="<?php echo $s; ?>"<?php echo $disabled; ?>><?php $this->e($s); ?></option>
							<?php elseif($transaction->status == LWP_Payment_Status::REFUND): ?>
								<option value="<?php echo $s; ?>" checked="checked"><?php $this->e($s);?></option>
							<?php endif; ?>
						<?php else: ?>
							<option value="<?php echo $s; ?>"<?php if($s == $transaction->status) echo ' selected="selected"'; ?>><?php $this->e($s);?></option>
						<?php endif; ?>
						<?php endforeach; ?>
					</select>
					<?php submit_button($this->_('Update Status'), 'primary', 'update_transaction', false); ?>
					</p>
					<?php if($transaction->method == LWP_Payment_Methods::PAYPAL): ?>
					<p class="description"><?php $this->e('<strong>Note:</strong> PayPal accepts refund by 60 days.'); ?></p>
					<?php endif; ?>
				</form>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e("Expires"); ?></th>
			<td>
				<?php if($transaction->status == LWP_Payment_Status::SUCCESS): ?>
					<?php if($transaction->expires == '0000-00-00 00:00:00'): ?>
						<?php $this->e('No Limit.'); ?>
					<?php else:?>
						<strong><?php echo (strtotime($transaction->expires) < current_time('timestamp')) ? $this->_('Expired'): $this->_('Valid');?></strong>
						<span class="description">(<?php echo mysql2date(get_option('date_format'), $transaction->expires); ?>)</span>
					<?php endif; ?>
				<?php else: ?>
					<?php $this->e('Not valid.'); ?>
				<?php endif; ?>
			</td>
			<td>
				<?php if($transaction->status == LWP_Payment_Status::SUCCESS || $transaction->expires != '0000-00-00 00:00:00'): ?>
				<form method="post">
					<?php wp_nonce_field('lwp_update_transaction'); ?>
					<input type="hidden" name="transaction_id" value="<?php echo $transaction->ID; ?>" />
					<p>
						<input class="date-picker" type="text" name="expires" value="<?php echo $transaction->expires; ?>" />
						<?php submit_button($this->_('Update Expiration'), 'primary', 'update_expires', false); ?>
					</p>
				</form>
				<?php else: ?>
				---
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
<p>
	<a class="button" href="<?php echo admin_url('admin.php?page=lwp-management'); ?>">&laquo;<?php $this->e('Return to transaction list');?></a>
</p>


<?php
/*---------------------------------
 *  一覧表示
 */
elseif(isset($_GET['view']) && $_GET['view'] == 'list'):
?>
<iframe src="" name="lwp-csv-output" height="0" width="100%"></iframe>
<form id="lwp-csv-output-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" target="lwp-csv-output">
	<input type="hidden" name="action" value="lwp_transaction_csv_output" />
	<?php wp_nonce_field('lwp_transaction_csv_output');?>
	<input type="hidden" name="status" value="" />
	<input type="hidden" name="post_type" value="" />
	<input type="hidden" name="from" value="" />
	<input type="hidden" name="to" value="" />
	<p class="description">
		<?php $this->e('You can get transaction list in status below.'); ?>
		<input type="submit" class="button-primary" value="<?php $this->e('Get CSV'); ?>" /><br />
	</p>
</form>

<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-management" />
	<input type="hidden" name="view" value="list" />
<?php
require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-management.php";
$list_table = new LWP_List_Management();
$list_table->prepare_items();
do_action('admin_notice');
$list_table->search_box(__('Search'), 's');
$list_table->display();

?>
</form>

<?php
/*---------------------------------
 * ダッシュボード
 */
else:
//開始
$from = isset($_GET['from']) ? esc_attr($_GET['from']) : date('Y-m-d', current_time('timestamp') - (60 * 60 * 24 * 30));
//終了
$to = isset($_GET['to']) ? esc_attr($_GET['to']) : date('Y-m-d', current_time('timestamp'));
//ステータス
$status = (isset($_GET['status']) && !empty($_GET['status'])) ? $_GET['status'] : LWP_Payment_Status::SUCCESS;
//投稿タイプ
$post_type = (isset($_GET['post_type']) && !empty($_GET['post_type'])) ? $_GET['post_type'] : 'all';
global $wpdb;
$sql = <<<EOS
	SELECT SUM(t.price) AS total, COALESCE(p.post_type, 'undefined') AS post_type
	FROM {$this->transaction} AS t
	LEFT JOIN {$wpdb->posts} AS p
	ON t.book_id = p.ID
EOS;
$wheres = array(
	$wpdb->prepare("t.updated >= %s", $from),
	$wpdb->prepare("t.updated <= %s", $to),
);
if($status != 'all'){
	$wheres[] = $wpdb->prepare('t.status = %s', $status);
}
if($post_type != 'all'){
	$wheres[] = $wpdb->prepare('p.post_type = %s', $post_type);
}
$sql .= ' WHERE '.implode(' AND ', $wheres);
$sql .= ' GROUP BY p.post_type';
$transactions = $wpdb->get_results($wpdb->prepare($sql, $from, $to));
$total = 0;
foreach($transactions as $t){
	$total += $t->total;
}
$sql = <<<EOS
	SELECT SUM(t.price) AS total, SUM(t.num) AS num, p.post_type, p.ID as post_id, p.post_title, p.post_parent
	FROM {$this->transaction} AS t
	LEFT JOIN {$wpdb->posts} AS p
	ON t.book_id = p.ID
EOS;
$sql .= ' WHERE '.implode(' AND ', $wheres);
$sql .= ' GROUP BY t.book_id ORDER BY total DESC LIMIT 10';
$top_sales = $wpdb->get_results($sql);
$sales_where = array(
	$wpdb->prepare("t.updated >= %s", $from),
	$wpdb->prepare("t.updated <= %s", $to)
);
if($post_type != 'all'){
	$sales_where[] = $wpdb->prepare('p.post_type = %s', $post_type);
}
$where_clause = ' WHERE '.implode(' AND ', $sales_where);
$sql = <<<EOS
	SELECT SUM(t.price) AS total, SUM(t.num) AS num, t.status
	FROM {$this->transaction} AS t
	LEFT JOIN {$wpdb->posts} AS p
	ON t.book_id = p.ID
	{$where_clause}
	GROUP BY t.status
	ORDER BY total DESC
EOS;
$sold = $wpdb->get_results($sql);
$where_clause = ' WHERE '.implode(' AND ', $wheres);
$sql = <<<EOS
	SELECT t.user_id, SUM(t.price) AS total, SUM(t.num) AS num
	FROM {$this->transaction} AS t
	LEFT JOIN {$wpdb->posts} AS p
	ON t.book_id = p.ID
	{$where_clause}
	GROUP BY t.user_id
	ORDER BY total DESC
	LIMIT 10
EOS;
$users = $wpdb->get_results($sql); 
?>
<form id="date-changer" method="get" action="<?php echo admin_url('admin.php');  ?>">
	<input type="hidden" name="page" value="lwp-management" />
	<p class="search-box">
		<input type="text" class="date-picker" name="from" value="<?php echo $from; ?>" />
		~
		<input type="text" class="date-picker" name="to" value="<?php echo $to; ?>" />
		<input type="submit" value="<?php $this->e('Refresh'); ?>" class="button" />
	</p>
	<div style="clear:both"></div>
</form>

<div id="lwp-dashboard-amount">
	<h3><?php $this->e('Amount: '); ?><?php echo number_format($total).' '.  lwp_currency_code(); ?></h3>
	<?php if(!empty($transactions)): ?>
		<?php foreach($transactions as $t): ?>
			<input type="hidden" name="sum_<?php echo $t->post_type; ?>" value="<?php echo esc_attr($t->total); ?>" />
			<input type="hidden" name="post_type_name_<?php echo $t->post_type; ?>" value="<?php echo esc_attr($t->post_type == 'undefined' ? $this->_('Undefined') : get_post_type_object($t->post_type)->labels->name); ?>" />
		<?php endforeach; ?>
	<?php else: ?>
		<p class="error"><?php $this->e('No Data'); ?></p>
	<?php endif; ?>
	<div class="pie-chart"></div>
</div>

<div id="lwp-dashboard-ranking">
	<ul>
		<li><a href="#tabs-sales"><?php $this->e('Top Sales'); ?></a></li>
		<li><a href="#tabs-status"><?php $this->e('Status'); ?></a></li>
		<li><a href="#tabs-royal-user"><?php $this->e('Royal user'); ?></a></li>
	</ul>
	<div id="tabs-sales">
		<?php if(empty($top_sales)):?>
			<p class="error"><?php $this->e('No Data'); ?></p>
		<?php else: ?>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col"><?php $this->e('Title'); ?></th>
					<th scope="col"><?php $this->e('Count'); ?></th>
					<th scope="col"><?php $this->e('Total'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $counter = 0; foreach($top_sales as $sale): $counter++; ?>
					<tr<?php if($counter % 2 == 0) echo ' class="alternate"';?>>
						<th scope="row"><?php echo $counter;?></th>
						<td>
							<strong><?php echo get_post_type_object($sale->post_type)->labels->name; ?></strong>-
							<a href="<?php echo get_permalink($sale->post_id); ?>">
								<?php
									if(false !== array_search($sale->post_type, array($this->event->post_type)) ){
										echo get_the_title($sale->post_parent);
									}
								?>
								<?php echo apply_filters('the_title', $sale->post_title); ?>
							</a>
						</td>
						<td><?php echo number_format($sale->num); ?></td>
						<td><?php echo number_format($sale->total)." (".lwp_currency_code().')';  ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<div id="tabs-status">
		<?php if(empty($sold)):?>
			<p class="error"><?php $this->e('No Data'); ?></p>
		<?php else: ?>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col"><?php $this->e('Status'); ?></th>
					<th scope="col"><?php $this->e('Count'); ?></th>
					<th scope="col"><?php $this->e('Total'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $counter = 0; foreach($sold as $pro): $counter++; ?>
					<tr<?php if($counter % 2 == 0) echo ' class="alternate"';?>>
						<th scope="row"><?php echo $counter;?></th>
						<td><?php $this->e($pro->status); ?></td>
						<td><?php echo number_format($pro->num); ?></td>
						<td><?php echo number_format($pro->total)." (".lwp_currency_code().')';  ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<div id="tabs-royal-user">
		<?php if(empty($users)): ?>
			<p class="error"><?php $this->e('No Data'); ?></p>
		<?php else: ?>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col"><?php $this->e('Name'); ?></th>
					<th scope="col"><?php $this->e('Count'); ?></th>
					<th scope="col"><?php $this->e('Price'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $counter = 0; foreach($users as $user): $counter++; $userdata = get_userdata($user->user_id); ?>
					<tr<?php if($counter % 2 == 0) echo ' class="alternate"';?>>
						<th scope="row"><?php echo $counter;?></th>
						<td>
							<?php if($userdata): ?>
								<a href="<?php echo admin_url('user-edit.php?user_id='.$user->user_id); ?>"><?php echo $userdata->display_name; ?></a>
							<?php else: ?>
								<?php $this->e('Deleted User'); ?>
							<?php endif; ?>
						</td>
						<td><?php echo number_format($user->num); ?></td>
						<td><?php echo number_format($user->total)." (".lwp_currency_code().')';  ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
</div>

<div id="lwp-dashboard-daily">
	<form method="get" action="<?php echo admin_url('admin-ajax.php'); ?>">
		<?php wp_nonce_field('lwp_area_chart'); ?>
		<input type="hidden" name="action" value="lwp_transaction_chart" />
		<input type="hidden" name="from" value="<?php echo $from;?>" />
		<input type="hidden" name="to" value="<?php echo $to;?>" />
		<input type="hidden" name="status" value="<?php echo $status; ?>" />
		<input type="hidden" name="post_type" value="<?php echo $post_type; ?>" />
	</form>
	<div class="area-chart"></div>
</div>

<?php
/*---------------------------------
 * 分岐終了
 */
endif;
<?php /* @var $this Literally_WordPress */  ?>
<h2><?php $this->e('Transaction Summary'); ?></h2>

<?php
//開始
$from = isset($_GET['from']) ? esc_attr($_GET['from']) : date('Y-m-d', time() - (60 * 60 * 24 * 30));
//終了
$to = isset($_GET['to']) ? esc_attr($_GET['to']) : date('Y-m-d');
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
if(!current_user_can('edit_others_posts')){
	$wheres[] = $wpdb->parepare('p.post_author = %d', get_current_user_id());
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
if(!current_user_can('edit_others_posts')){
	$sales_where[] = $wpdb->parepare('p.post_author = %d', get_current_user_id());
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
	<input type="hidden" name="page" value="lwp-summary" />
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
<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e('Customer Management'); ?></h2>
<?php
/*---------------------------------
 * 個別表示
 */
if(isset($_GET["transaction_id"]) && is_numeric($_REQUEST["transaction_id"])):
	$transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->transaction} WHERE ID = %d", $_REQUEST["transaction_id"]));
	if($transaction):
		$book = wp_get_single_post($transaction->book_id);
		$user = get_userdata($transaction->user_id);
?>
<h3><?php $this->e('Update Transaction'); ?></h3>
<form method="post">
	<?php wp_nonce_field("lwp_update_transaction"); ?>
	<input type="hidden" name="transaction_id" value="<?php $this->h($transaction->ID);?>" />
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top"><?php $this->e('Item Name'); ?></th>
				<td><a href="<?php echo admin_url("post.php?action=edit&post_type={$book->post_type}&post={$book->ID}"); ?>"><?php echo $book->post_title; ?></a></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="user_id"><?php $this->e('User Name'); ?></label></th>
				<td>
					<?php if($transaction->user_id): ?>
						<input type="hidden" name="user_id" value="<?php echo $transaction->user_id;?>" />
						<?php if($user): ?>
							<a href="<?php echo admin_url("user-edit.php?user_id={$user->ID}");?>">
								<?php echo $user->display_name;  ?>
							</a>
						<?php else: ?>
							<?php $this->e('Deleted User'); ?>
						<?php endif; ?>
					<?php else: /* TODO: この例外処理はいらない？*/?>
						<select name="user_id" id="user_id">
							<option value="0" selected="selected" disabled="true"><?php $this->e("Select below");?></option>
							<?php
								$sql = <<<EOS
									SELECT * FROM {$wpdb->users} as u
									LEFT JOIN {$wpdb->usermeta} as m
									ON u.ID = m.user_id AND m.meta_key = '{$wpdb->prefix}user_level'
									WHERE m.meta_value = 0 
EOS;
								$users = $wpdb->get_results($sql);
								foreach($users as $u):
							?>
								<option value="<?php echo $u->ID; ?>"><?php echo $u->display_name; ?></option>
							<?php endforeach; ?>
						</select>
						<p class="error"><?php $this->e('This transaction doesn\'t relate to any account.');?><small>（<?php echo $this->help("account", $this->_("More &gt;")); ?>）</small></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="price"><?php $this->e("Purchased Price"); ?></label></th>
				<td>
					<input id="price" type="text" name="price" value="<?php $this->h($transaction->price); ?>" />
					<p class="description"><?php $this->e('Original Price'); ?>: <?php echo number_format( lwp_original_price($book->ID))." ({$this->option['currency_code']})";?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="method"><?php $this->e('Method'); ?></label></th>
				<td>
					<select name="method" id="method">
						<?php foreach(LWP_Payment_Methods::get_all_methods() as $method): ?>
						<option value="<?php echo $method; ?>"<?php if($transaction->method == $method) echo ' selected="selected"';?>><?php $this->e($method); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="payer_mail"><?php $this->e("Mail account for transaction");?></label></th>
				<td>
					<input readonly="readonly" class="regular-text" type="text" name="payer_mail" id="payer_mail" value="<?php $this->h($transaction->payer_mail); ?>" />
					<?php if($transaction->payer_mail == $user->user_email):?>
						<p class="description"><?php $this->e("This mail is same as the account mail."); ?></p>
					<?php elseif($transaction->payer_mail == get_user_meta($transaction->user_id, "paypal")): ?>
						<p class="description"><?php $this->e("This mail is same as PayPal mail of the user account.");?></p>
					<?php else: ?>
						<p class="error"><?php $this->e('This mail doesn\'t related to account information.');  ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="status"><?php $this->e("Status"); ?></label></th>
				<td>
					<select name="status" id="status">
						<option value="CUCCESS"<?php if($transaction->status == "SUCCESS") echo ' selected="selected"';?>><?php $this->e(LWP_Payment_Status::SUCCESS);  ?></option>
						<option value="Cancel"<?php if($transaction->status == "Cancel") echo ' selected="selected"';?>><?php $this->e(LWP_Payment_Status::CANCEL); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><?php $this->e('Updated'); ?></th>
				<td>
					<?php echo mysql2date(get_option('date_format'), $transaction->updated); ?>
					<small>（<?php $this->e('Registered'); ?>: <?php echo mysql2date(get_option('date_format'), $transaction->registered); ?>）</small>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="primary-button" value="更新" />
	</p>
	<p>
		<a href="<?php echo admin_url('admin.php?page=lwp-management'); ?>">&laquo;<?php $this->e('Return to transaction list');?></a>
	</p>
</form>
 
 	<?php endif; ?>
<?php
/*---------------------------------
 *  一覧表示
 */
else:
	
$page = isset($_GET["paged"]) ? $_GET["paged"] - 1 : 0;
$ebook_id = isset($_GET["ebook_id"]) ? $_GET["ebook_id"] : null;
$user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : null;
$status = isset($_GET["status"]) ? $_GET["status"] : null;

$transactions = $this->get_transaction($ebook_id, $user_id, $status, $page, 20);
$length = count($this->get_transaction($ebook_id, $user_id, $status));
?>
<form method="get">
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php printf($this->_('%1$d - %2$d (Total: %3$d)'),  ($page * 20 + 1), min((($page + 1) * 20), $length), $length); ?></span>
			<?php for($i = 0, $l = ceil($length / 20); $i < $l; $i++): ?>
				<?php if($i == $page):?>
				<span class="page-numbers"><?php echo $i + 1; ?></span>
				<?php else: ?>
				<a class="page-numbers" href=""><?php echo $i + 1; ?></a>
				<?php endif; ?>
			<?php endfor; ?>
		</div>
		<div class="clear"></div>
	</div>
	<!-- .tablenav ends -->
	<table class="widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" classs="manage-column"><?php $this->e('Item Name'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('User Name'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Purchased Price'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Status'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Method'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Expires'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Last Updated'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Detail'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" classs="manage-column"><?php $this->e('Item Name'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('User Name'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Purchased Price'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Status'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Method'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Expires'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Last Updated'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Detail'); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php $counter = 0; foreach($transactions as $t): $counter++; $book = wp_get_single_post($t->book_id); $data = get_userdata($t->user_id); ?>
			<tr<?php if($counter % 2 == 0) echo ' class="alternate"'; ?>>
				<td><a href="<?php echo admin_url("post.php?action=edit&post_type={$book->post_type}&post={$book->ID}")?>"><?php echo $book->post_title; ?></a></td>
				<td>
					<?php if($data): ?>
					<a href="<?php echo admin_url("user-edit.php?user_id={$t->user_id}"); ?>"><?php echo $data->display_name; ?></a>
					<?php else: ?>
					<?php $this->e('Deleted User'); ?>
					<?php endif; ?>
				</td>
				<td><?php echo number_format($t->price)."({$this->option['currency_code']})"; ?></td>
				<td><?php $this->e($t->status); ?></td>
				<td><?php $this->e($t->method); ?></td>
				<td>
					<?php if($t->status == LWP_Payment_Status::SUCCESS): ?>
						<?php if($t->expires == '0000-00-00 00:00:00'): ?>
							<?php $this->e('No Limit.'); ?>
						<?php else:?>
							<?php echo mysql2date(get_option('date_format'), $t->expires); ?>
							<stong><?php echo (strtotime($t->expires) < time()) ? $this->_('Expired'): $this->_('Valid');?></strong>
						<?php endif; ?>
					<?php else: ?>
						<?php $this->e('Not valid.'); ?>
					<?php endif; ?>
				</td>
				<td><?php echo mysql2date(get_option('date_format'), $t->updated); ?></td>
				<td><p><a class="button" href="<?php echo admin_url("admin.php?page=lwp-management&transaction_id={$t->ID}"); ?>"><?php $this->e("Edit"); ?></a></p></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>

<?php
/*---------------------------------
 * 分岐終了
 */
endif;
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
<h3><?php $this->e('Transaction Detail'); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Item Name'); ?></th>
			<td><a href="<?php echo admin_url("post.php?action=edit&post_type={$book->post_type}&post={$book->ID}"); ?>"><?php echo $book->post_title; ?></a></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('User Name'); ?></th>
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
			<th scope="row" valign="top"><?php $this->e('Updated'); ?></th>
			<td>
				<?php echo mysql2date(get_option('date_format'), $transaction->updated); ?>
				<small>（<?php $this->e('Registered'); ?>: <?php echo mysql2date(get_option('date_format'), $transaction->registered); ?>）</small>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e("Purchased Price"); ?></th>
			<td>
				<strong><?php echo number_format($transaction->price)." ({$this->option['currency_code']})"; ?></strong>
				<p class="description"><?php $this->e('Original Price'); ?>: <?php echo number_format( lwp_original_price($book->ID))." ({$this->option['currency_code']})";?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e('Purchase Method'); ?></th>
			<td>
				<?php $this->e($transaction->method); ?>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e("Status"); ?></th>
			<td>
				<?php $this->e($transaction->status);?>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><?php $this->e("Expires"); ?></th>
			<td>
				<?php if($transaction->status == LWP_Payment_Status::SUCCESS): ?>
					<?php if($transaction->expires == '0000-00-00 00:00:00'): ?>
						<?php $this->e('No Limit.'); ?>
					<?php else:?>
						<?php echo mysql2date(get_option('date_format'), $transaction->expires); ?>
						<stong><?php echo (strtotime($transaction->expires) < time()) ? $this->_('Expired'): $this->_('Valid');?></strong>
					<?php endif; ?>
				<?php else: ?>
					<?php $this->e('Not valid.'); ?>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
<p>
	<a href="<?php echo admin_url('admin.php?page=lwp-management'); ?>">&laquo;<?php $this->e('Return to transaction list');?></a>
</p>
 
 	<?php endif; ?>
<?php
/*---------------------------------
 *  一覧表示
 */
else:
?>
	
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-management" />
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
 * 分岐終了
 */
endif;
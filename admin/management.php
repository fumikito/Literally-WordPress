<?php /* @var $this Literally_WordPress */ ?>
<h2>電子書籍顧客管理</h2>
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
<h3>取引情報を更新する</h3>
<form method="post">
	<?php wp_nonce_field("lwp_update_transaction"); ?>
	<input type="hidden" name="transaction_id" value="<?php $this->h($transaction->ID);?>" />
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">書籍名</th>
				<td><?php echo $book->post_title; ?></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="user_id">ユーザー名</label></th>
				<td>
					<?php if($transaction->user_id): ?>
						<input type="hidden" name="user_id" value="<?php echo $transaction->user_id;?>" />
						<?php echo $user->display_name;  ?><small>（<a href="<?php echo admin_url();?>user-edit.php?user_id=<?php echo $user->ID;?>">詳細を見る&raquo;</a>）</small>
					<?php else: ?>
						<select name="user_id" id="user_id">
							<option value="0" selected="selected" disabled="true">選択してください</option>
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
						<p class="error">この取引情報は会員と紐づいていません。<small>（<?php echo $this->help("account", $this->_("More &gt;")); ?>）</small></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="price">販売価格</label></th>
				<td>
					<input id="price" type="text" name="price" value="<?php $this->h($transaction->price); ?>" />
					<p class="description">定価 <?php echo money_format('%7n', get_post_meta($book->ID, "lwp_price", true));?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="method">販売方法</label></th>
				<td>
					<select name="method" id="method">
						<option value="PAYPAL"<?php if($transaction->method == "PAYPAL") echo ' selected="selected"';?>>PayPal</option>
						<option value="present"<?php if($transaction->method == "present") echo ' selected="selected"';?>>プレゼント</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="payer_mail">トランザクションメール</label></th>
				<td>
					<input readonly="readonly" class="regular-text" type="text" name="payer_mail" id="payer_mail" value="<?php $this->h($transaction->payer_mail); ?>" />
					<?php if($transaction->payer_mail == $user->user_email):?>
						<p class="description">このメールアドレスはアカウントとして登録されているメールアドレスと同じです。</p>
					<?php elseif($transaction->payer_mail == get_user_meta($transaction->user_id, "paypal")): ?>
						<p class="description">このメールアドレスはPayPalアドレスとして登録されています。</p>
					<?php else: ?>
						<p class="error">このメールアドレスはユーザー情報と紐づいていません。</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="status">ステータス</label></th>
				<td>
					<select name="status" id="status">
						<option value="CUCCESS"<?php if($transaction->status == "CUCCESS") echo ' selected="selected"';?>>完了</option>
						<option value="Cancel"<?php if($transaction->status == "Cancel") echo ' selected="selected"';?>>キャンセル</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">更新日</th>
				<td><?php echo mysql2date('Y年m月d日', $transaction->updated); ?><small>（登録日 <?php echo mysql2date('Y年m月d日', $transaction->registered); ?>）</small></td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="primary-button" value="更新" />
	</p>
	<p>
		<a href="<?php echo admin_url(); ?>edit.php?post_type=ebook&amp;page=lwp-management">&laquo;一覧に戻る</a>
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
			<span class="displaying-num"><?php echo ($page * 20 + 1)."-".(($page + 1) * 20); ?>件（<?php echo $length; ?>件中）を表示中</span>
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
				<th scope="col" classs="manage-column">書籍名</th>
				<th scope="col" classs="manage-column">購入者</th>
				<th scope="col" classs="manage-column">価格</th>
				<th scope="col" classs="manage-column">状態</th>
				<th scope="col" classs="manage-column">購入方法</th>
				<th scope="col" classs="manage-column">購入日</th>
				<th scope="col" classs="manage-column">詳細</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" classs="manage-column">書籍名</th>
				<th scope="col" classs="manage-column">購入者</th>
				<th scope="col" classs="manage-column">価格</th>
				<th scope="col" classs="manage-column">状態</th>
				<th scope="col" classs="manage-column">購入方法</th>
				<th scope="col" classs="manage-column">購入日</th>
				<th scope="col" classs="manage-column">詳細</th>
			</tr>
		</tfoot>
		<tbody>
			<?php $counter = 0; foreach($transactions as $t): $counter++; $book = wp_get_single_post($t->book_id); $data = get_userdata($t->user_id); ?>
			<tr<?php if($counter % 2 == 0) echo ' class="alternate"'; ?>>
				<td><?php echo $book->post_title; ?></td>
				<td><?php echo $data->display_name; ?></td>
				<td><?php echo money_format('%7n', $t->price); ?></td>
				<td><?php echo $t->status; ?></td>
				<td><?php echo $t->method; ?></td>
				<td><?php echo mysql2date("Y年m月d日", $t->registered); ?></td>
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
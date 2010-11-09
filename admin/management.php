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
<form method="post">
	<?php wp_nonce_field("lwp_update_transaction"); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">書籍名</th>
				<td><?php echo $book->post_title; ?></td>
			</tr>
			<tr>
				<th scope="row" valign="top">ユーザー名</th>
				<td><?php echo $user->display_name;  ?></td>
			</tr>
			<tr>
				<th scope="row" valign="top">販売価格</th>
				<td><?php echo money_format('%7n', $transaction->price); ?><small>（定価 <?php echo money_format('%7n', get_post_meta($book->ID, "lwp_price", true));?>）</small></td>
			</tr>
			<tr>
				<th scope="row" valign="top">販売方法</th>
				<td><?php echo $transaction->method; ?></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="status">ステータス</label></th>
				<td>
					<?php echo $transaction->status; ?><br />
					<select name="status" id="status">
						<option value="" selected="selected" disabled="true">変更する場合は選択してください</option>
						<option value="CUCCESS">完了</option>
						<option value="Cancel">キャンセル</option>
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
		<input type="submit" class="primary-button" vlue="更新" />
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
				<td><p><a class="button" href="<?php echo admin_url(); ?>edit.php?post_type=ebook&amp;page=lwp-management&amp;transaction_id=<?php echo $t->ID; ?>">詳細を見る</a></p></td>
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
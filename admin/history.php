<h2>購入履歴</h2>
<?php
global $user_ID;
$page = isset($_GET["paged"]) ? $_GET["paged"] - 1 : 0;

$transactions = $this->get_transaction(null, $user_ID, null, $page, 20);
$length = count($this->get_transaction(null, $user_ID, null));
if(!empty($transactions)):
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
				<th scope="col" classs="manage-column">価格</th>
				<th scope="col" classs="manage-column">購入方法</th>
				<th scope="col" classs="manage-column">購入日</th>
				<th scope="col" classs="manage-column">ダウンロード</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" classs="manage-column">書籍名</th>
				<th scope="col" classs="manage-column">価格</th>
				<th scope="col" classs="manage-column">購入方法</th>
				<th scope="col" classs="manage-column">購入日</th>
				<th scope="col" classs="manage-column">ダウンロード</th>
			</tr>
		</tfoot>
		<tbody>
			<?php $counter = 0; foreach($transactions as $t): $counter++; $book = wp_get_single_post($t->book_id); $data = get_userdata($t->user_id); ?>
			<tr<?php if($counter % 2 == 0) echo ' class="alternate"'; ?>>
				<td><?php echo $book->post_title; ?></td>
				<td><?php echo money_format('%7n', $t->price); ?></td>
				<td><?php
					switch($t->method){
						case "PAYPAL":
							echo "PayPal";
							break;
						case "present":
							echo "プレゼント";
							break;
						default:
							echo "その他";
					}
				?></td>
				<td><?php echo mysql2date("Y年m月d日", $t->registered); ?></td>
				<td><p><a class="button" href="<?php echo get_permalink($book->ID); ?>">ダウンロードページへ</a></p></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>
<?php else: ?>
<p>まだ電子書籍を購入していません。</p>
<?php endif; ?>
<h3>お問い合わせ</h3>
<p>
購入し、請求されているはずなのにこちらへ表示されていない場合は、サイト管理者&lt;<a href="mailto:<?php echo get_option("admin_email");?>"><?php echo get_option("admin_email");?></a>&gt;へご連絡ください。<br />
早急に対応させていただきます。
</p>
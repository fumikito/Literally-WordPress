<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e('Purchase History'); ?></h2>
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
			<span class="displaying-num"><?php printf($this->_('%1$d - %2$d (Total: %3$d)'),  ($page * 20 + 1), min(($page + 1) * 20, $length), $lenght); ?></span>
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
				<th scope="col" classs="manage-column"><?php $this->e('Price'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Method'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Date'); ?></th>
				<th scope="col" classs="manage-column">&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" classs="manage-column"><?php $this->e('Item Name'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Price'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Method'); ?></th>
				<th scope="col" classs="manage-column"><?php $this->e('Date'); ?></th>
				<th scope="col" classs="manage-column">&nbsp;</th>
			</tr>
		</tfoot>
		<tbody>
			<?php $counter = 0; foreach($transactions as $t): $counter++; $book = wp_get_single_post($t->book_id); $data = get_userdata($t->user_id); ?>
			<tr<?php if($counter % 2 == 0) echo ' class="alternate"'; ?>>
				<td><a href="<?php echo get_permalink($book->ID); ?>"><?php echo $book->post_title; ?></a></td>
				<td><?php echo number_format($t->price)."({$this->option['currency_code']})"; ?></td>
				<td><?php
					switch($t->method){
						case "PAYPAL":
							echo $this->e("PayPal");
							break;
						case "present":
							echo $this->e('Present');
							break;
						case "CAMPAIGN":
							$this->e("Campaign");
							break;
						default:
							echo "その他";
					}
				?></td>
				<td><?php echo mysql2date("Y年m月d日", $t->registered); ?></td>
				<td><p><a class="button" href="<?php echo get_permalink($book->ID); ?>"><?php $this->e('View Item');?></a></p></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>
<?php else: ?>
<p><?php $this->e('You have purchased no item.'); ?></p>
<?php endif; ?>
<h3><?php $this->e('Contact'); ?></h3>
<p>
	<?php printf($this->_('If purchased items were not displayed, please contact to Site administrator &lt;<a href="mailto:%1$s">%1$s</a>&gt;. We will reply ASAP.'), get_option('admin_email')); ?>
</p>
<?php /* @var $this Literally_WordPress */ ?>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo admin_url('admin.php?page=lwp-refund'); ?>" class="nav-tab<?php if(!isset($_GET['status']) || $_GET['status'] != LWP_Payment_Status::REFUND) echo ' nav-tab-active';?>">
		<?php $this->e(LWP_Payment_Status::REFUND_REQUESTING); ?>
		<?php if(($count = $this->refund_manager->on_queue_count())):  ?>
		<small class="tab-count"><?php echo $count; ?></small>
		<?php endif; ?>
	</a>
	<a href="<?php echo admin_url('admin.php?page=lwp-refund&status='.LWP_Payment_Status::REFUND); ?>" class="nav-tab<?php if(isset($_GET['status']) && $_GET['status'] == LWP_Payment_Status::REFUND) echo ' nav-tab-active';?>">
		<?php $this->e(LWP_Payment_Status::REFUND); ?>
	</a>

</h2>

<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-refund" />
	<?php if(isset($_GET['status']) && $_GET['status'] == LWP_Payment_Status::REFUND): ?>
	<input type="hidden" name="status" value="<?php echo LWP_Payment_Status::REFUND ?>" />
	<?php endif; ?>
<?php
require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-refund.php";
$list_table = new LWP_List_Refund();
$list_table->prepare_items();
do_action('admin_notice');
$list_table->search_box(__('Search'), 's');
$list_table->display();
?>
</form>
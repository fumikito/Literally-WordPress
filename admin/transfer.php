<?php /* @var $this Literally_WordPress */ ?>
<h2><?php $this->e("Transfer Management"); ?></h2>

<form method="get" action="<?php echo admin_url('admin.php'); ?>">
	<input type="hidden" name="page" value="lwp-transfer" />
<?php
require_once $this->dir.DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."list-transfers.php";
$list_table = new LWP_List_Transfer();
$list_table->prepare_items();
do_action('admin_notice');
$list_table->search_box(__('Search'), 's');
$list_table->display();
?>
</form>